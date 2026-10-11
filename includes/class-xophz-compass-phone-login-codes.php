<?php
/**
 * One-time sign-in codes for My Compass Phone: issue, verify, and limit guessing.
 *
 * Each email has at most one live 6-digit code and one magic link token (64 hex characters),
 * both valid for 15 minutes and stored only as hashes. A code is checked against one email:
 * the verify request names it with an `email` field, or with the HttpOnly cookie that the send
 * request set in the same browser. A code is never looked up by its value alone, which let any
 * guess match any outstanding code for any account.
 *
 * Limits (#5982):
 * - 5 wrong codes for an email invalidate that email's code and link; the user requests a new one.
 * - 5 wrong codes or links from one REMOTE_ADDR block verification from it for 15 minutes.
 *   X-Forwarded-For is not trusted. Behind a reverse proxy every visitor shares the proxy's
 *   address, so this limit then applies to everyone at once.
 * - 5 codes per email per hour, so re-sending cannot reset the per-email limit without end.
 * - 10 account lookups (POST /check-email) per REMOTE_ADDR per 15 minutes (#5992).
 * Counters are updated under MySQL GET_LOCK, so parallel requests cannot all read the same count.
 *
 * @package Xophz_Compass_Phone
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Xophz_Compass_Phone_Login_Codes {

	/** Lifetime of a code and its link, in seconds (15 minutes). */
	const TTL = 900;

	/** Wrong codes allowed per email (per code) and per IP before they stop working. */
	const MAX_WRONG = 5;

	/** Codes that may be sent to one email in an hour. */
	const MAX_SENDS_PER_HOUR = 5;

	/** Account lookups (POST /check-email) allowed per IP in TTL seconds. */
	const MAX_LOOKUPS = 10;

	/** HttpOnly cookie naming the email a code was requested for in this browser. */
	const COOKIE = 'compass_phone_login';

	/** Transient name prefix. */
	const PREFIX = 'compass_login_';

	/**
	 * Issue a new code and magic link token for an email, replacing any earlier one.
	 *
	 * @return array{code: string, link_token: string}|WP_Error 429 after MAX_SENDS_PER_HOUR sends.
	 */
	public static function issue( string $email ) {
		$email_key = self::email_key( $email );
		$lock      = self::lock( 'e' . $email_key );
		if ( is_wp_error( $lock ) ) {
			return $lock;
		}

		$sends_name = self::PREFIX . 'sends_' . $email_key;
		$sends      = (int) get_transient( $sends_name );
		if ( $sends >= self::MAX_SENDS_PER_HOUR ) {
			self::unlock( $lock );
			return new WP_Error(
				'too_many_codes',
				'Too many codes were requested for this email. Please try again in an hour.',
				array( 'status' => 429 )
			);
		}
		set_transient( $sends_name, $sends + 1, HOUR_IN_SECONDS );

		self::invalidate( $email_key );

		$code = sprintf( '%06d', wp_rand( 100000, 999999 ) );
		$link = bin2hex( random_bytes( 32 ) );
		$bind = bin2hex( random_bytes( 16 ) );

		$record = array(
			'email'     => $email,
			'code_hash' => self::hmac( $code ),
			'link_hash' => hash( 'sha256', $link ),
			'bind_hash' => hash( 'sha256', $bind ),
			'attempts'  => 0,
			'created'   => time(),
		);
		set_transient( self::PREFIX . 'req_' . $email_key, $record, self::TTL );
		set_transient( self::PREFIX . 'link_' . $record['link_hash'], $email_key, self::TTL );
		set_transient( self::PREFIX . 'bind_' . $record['bind_hash'], $email_key, self::TTL );
		self::unlock( $lock );

		self::set_cookie( $bind, time() + self::TTL );

		return array(
			'code'       => $code,
			'link_token' => $link,
		);
	}

	/**
	 * Check a 6-digit code (for an email) or a magic link token. Success uses up the code.
	 *
	 * @param string $token The code or link token the user presented.
	 * @param string $email Optional: the email the code was sent to (else this browser's cookie).
	 * @return array{email: string}|WP_Error
	 */
	public static function verify( string $token, string $email = '' ) {
		$ip_lock = self::lock( 'i' . self::client_ip_key() );
		if ( is_wp_error( $ip_lock ) ) {
			return $ip_lock;
		}
		$result = self::verify_from_ip( trim( $token ), $email );
		self::unlock( $ip_lock );
		return $result;
	}

	/** Verification body; the caller holds this IP's lock. */
	private static function verify_from_ip( string $token, string $email ) {
		$ip_name  = self::PREFIX . 'ipfail_' . self::client_ip_key();
		$ip_fails = (int) get_transient( $ip_name );
		if ( $ip_fails >= self::MAX_WRONG ) {
			return new WP_Error(
				'too_many_attempts',
				'Too many incorrect codes from this network. Please wait 15 minutes, then request a new code.',
				array( 'status' => 429 )
			);
		}

		if ( preg_match( '/^[a-f0-9]{64}$/i', $token ) ) {
			return self::verify_link( strtolower( $token ), $ip_name, $ip_fails );
		}

		if ( ! preg_match( '/^\d{6}$/', $token ) ) {
			set_transient( $ip_name, $ip_fails + 1, self::TTL );
			return new WP_Error( 'invalid_token', 'Enter the 6-digit code from your email.', array( 'status' => 400 ) );
		}

		$email_key = self::resolve_email_key( $email );
		if ( '' === $email_key ) {
			return new WP_Error(
				'email_required',
				'Enter the email address the code was sent to, or request a new code in this browser.',
				array( 'status' => 400 )
			);
		}

		$lock = self::lock( 'e' . $email_key );
		if ( is_wp_error( $lock ) ) {
			return $lock;
		}
		$result = self::verify_code( $token, $email_key, $ip_name, $ip_fails );
		self::unlock( $lock );
		return $result;
	}

	/** Check a magic link token. */
	private static function verify_link( string $token, string $ip_name, int $ip_fails ) {
		$link_hash = hash( 'sha256', $token );
		$email_key = (string) get_transient( self::PREFIX . 'link_' . $link_hash );
		$record    = '' === $email_key ? false : get_transient( self::PREFIX . 'req_' . $email_key );

		if ( ! is_array( $record ) || ! hash_equals( (string) ( $record['link_hash'] ?? '' ), $link_hash ) ) {
			set_transient( $ip_name, $ip_fails + 1, self::TTL );
			return new WP_Error( 'invalid_token', 'This sign-in link is invalid or has expired. Please request a new one.', array( 'status' => 400 ) );
		}

		self::invalidate( $email_key );
		self::set_cookie( '', time() - HOUR_IN_SECONDS );
		return array( 'email' => (string) $record['email'] );
	}

	/** Check a 6-digit code for one email; the caller holds that email's lock. */
	private static function verify_code( string $code, string $email_key, string $ip_name, int $ip_fails ) {
		$req_name = self::PREFIX . 'req_' . $email_key;
		$record   = get_transient( $req_name );

		if ( ! is_array( $record ) ) {
			set_transient( $ip_name, $ip_fails + 1, self::TTL );
			return new WP_Error( 'invalid_token', 'This code is invalid or has expired. Please request a new one.', array( 'status' => 400 ) );
		}

		if ( ! hash_equals( (string) ( $record['code_hash'] ?? '' ), self::hmac( $code ) ) ) {
			set_transient( $ip_name, $ip_fails + 1, self::TTL );
			$record['attempts'] = (int) ( $record['attempts'] ?? 0 ) + 1;

			if ( $record['attempts'] >= self::MAX_WRONG ) {
				self::invalidate( $email_key );
				return new WP_Error(
					'code_invalidated',
					'Too many incorrect codes. This code no longer works; please request a new one.',
					array( 'status' => 429 )
				);
			}

			$remaining = max( 1, (int) ( $record['created'] ?? 0 ) + self::TTL - time() );
			set_transient( $req_name, $record, $remaining );
			return new WP_Error( 'invalid_token', 'That code is incorrect. Please check your email and try again.', array( 'status' => 400 ) );
		}

		self::invalidate( $email_key );
		self::set_cookie( '', time() - HOUR_IN_SECONDS );
		return array( 'email' => (string) $record['email'] );
	}

	/**
	 * Count one account lookup from this REMOTE_ADDR.
	 *
	 * @return true|WP_Error 429 `too_many_lookups` after MAX_LOOKUPS lookups in TTL seconds.
	 */
	public static function allow_lookup() {
		$ip_key  = self::client_ip_key();
		$ip_lock = self::lock( 'l' . $ip_key );
		if ( is_wp_error( $ip_lock ) ) {
			return $ip_lock;
		}

		$name    = self::PREFIX . 'lookup_' . $ip_key;
		$lookups = (int) get_transient( $name );
		if ( $lookups >= self::MAX_LOOKUPS ) {
			self::unlock( $ip_lock );
			return new WP_Error(
				'too_many_lookups',
				'Too many sign-in attempts from this network. Please wait 15 minutes and try again.',
				array( 'status' => 429 )
			);
		}
		set_transient( $name, $lookups + 1, self::TTL );
		self::unlock( $ip_lock );
		return true;
	}

	/** Delete an email's code, link and cookie binding. */
	private static function invalidate( string $email_key ): void {
		$req_name = self::PREFIX . 'req_' . $email_key;
		$record   = get_transient( $req_name );
		if ( is_array( $record ) ) {
			delete_transient( self::PREFIX . 'link_' . (string) ( $record['link_hash'] ?? '' ) );
			delete_transient( self::PREFIX . 'bind_' . (string) ( $record['bind_hash'] ?? '' ) );
		}
		delete_transient( $req_name );
	}

	/** The email a code is for: the `email` field, else this browser's cookie, else ''. */
	private static function resolve_email_key( string $email ): string {
		$email = sanitize_email( trim( $email ) );
		if ( '' !== $email && is_email( $email ) ) {
			return self::email_key( $email );
		}

		$bind = isset( $_COOKIE[ self::COOKIE ] ) ? (string) wp_unslash( $_COOKIE[ self::COOKIE ] ) : '';
		if ( ! preg_match( '/^[a-f0-9]{32}$/', $bind ) ) {
			return '';
		}
		return (string) get_transient( self::PREFIX . 'bind_' . hash( 'sha256', $bind ) );
	}

	public static function email_key( string $email ): string {
		return md5( strtolower( trim( $email ) ) );
	}

	private static function hmac( string $value ): string {
		return hash_hmac( 'sha256', $value, wp_salt( 'auth' ) );
	}

	/** Hash of REMOTE_ADDR only; forwarded-for headers are client-controlled. */
	private static function client_ip_key(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
		return md5( false !== filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : 'unknown' );
	}

	/**
	 * Take a MySQL named lock (released on unlock() or when the request's connection closes).
	 *
	 * @return string|WP_Error The lock name, or 429 when it stays busy.
	 */
	private static function lock( string $suffix ) {
		global $wpdb;
		$name = 'cpl_' . $suffix;
		if ( '1' === (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, %d)', $name, 5 ) ) ) {
			return $name;
		}
		return new WP_Error( 'busy', 'Please try again in a moment.', array( 'status' => 429 ) );
	}

	private static function unlock( string $name ): void {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $name ) );
	}

	private static function set_cookie( string $value, int $expires ): void {
		// Visible to the rest of this request too, as WordPress does for its auth cookies.
		if ( '' === $value ) {
			unset( $_COOKIE[ self::COOKIE ] );
		} else {
			$_COOKIE[ self::COOKIE ] = $value;
		}
		if ( headers_sent() ) {
			return;
		}
		setcookie(
			self::COOKIE,
			$value,
			array(
				'expires'  => $expires,
				'path'     => ( defined( 'COOKIEPATH' ) && COOKIEPATH ) ? COOKIEPATH : '/',
				'domain'   => ( defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ) ? COOKIE_DOMAIN : '',
				'secure'   => is_ssl(),
				'httponly' => true,
				'samesite' => 'Lax',
			)
		);
	}
}
