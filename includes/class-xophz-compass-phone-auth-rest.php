<?php
/**
 * REST API Auth Handler for My Compass Phone
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class Xophz_Compass_Phone_Auth_Rest {
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		add_action( 'set_logged_in_cookie', function( $logged_in_cookie ) {
			if ( defined( 'LOGGED_IN_COOKIE' ) ) {
				$_COOKIE[ LOGGED_IN_COOKIE ] = $logged_in_cookie;
			}
			if ( defined( 'TEST_COOKIE' ) ) {
				$_COOKIE[ TEST_COOKIE ] = 'WP Cookie check';
			}
		}, 10, 1 );

		add_action( 'set_auth_cookie', function( $auth_cookie ) {
			if ( defined( 'AUTH_COOKIE' ) ) {
				$_COOKIE[ AUTH_COOKIE ] = $auth_cookie;
			}
		}, 10, 1 );

		$namespace = 'compass-phone/v1';

		register_rest_route(
			$namespace,
			'/check-email',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_check_email' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$namespace,
			'/login',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_login' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$namespace,
			'/send-magic-link',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_send_magic_link' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$namespace,
			'/verify-magic-link',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_verify_magic_link' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$namespace,
			'/me',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'handle_get_me' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$namespace,
			'/logout',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_logout' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$namespace,
			'/settings',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'handle_get_settings' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'handle_update_settings' ),
					'permission_callback' => array( $this, 'admin_permissions_check' ),
				)
			)
		);

		register_rest_route(
			$namespace,
			'/stats',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'handle_get_stats' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'handle_reset_stats' ),
					'permission_callback' => array( $this, 'admin_permissions_check' ),
				)
			)
		);

		register_rest_route(
			$namespace,
			'/track',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_track_event' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$namespace,
			'/stripe/checkout',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_stripe_checkout' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'xophz/v1',
			'/stripe/checkout',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_stripe_checkout' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function handle_check_email( WP_REST_Request $request ) {
		$email_input = trim( (string) $request->get_param( 'email' ) );

		if ( empty( $email_input ) ) {
			return new WP_Error( 'missing_email', 'Please enter a valid email address.', array( 'status' => 400 ) );
		}

		$user = get_user_by( 'email', $email_input );
		if ( ! $user ) {
			$user = get_user_by( 'login', $email_input );
		}

		if ( $user ) {
			return rest_ensure_response( array(
				'exists'       => true,
				'email'        => $user->user_email,
				'username'     => $user->user_login,
				'display_name' => $user->display_name,
				'avatar_url'   => get_avatar_url( $user->ID, array( 'size' => 128 ) ),
			) );
		}

		return rest_ensure_response( array(
			'exists' => false,
			'email'  => $email_input,
		) );
	}

	private function strip_captcha_filters() {
		remove_filter( 'authenticate', 'wp_authenticate_application_password', 20 );

		global $wp_filter;
		$filters_to_clean = array( 'authenticate', 'wp_authenticate_user', 'wp_authenticate' );
		foreach ( $filters_to_clean as $filter_name ) {
			if ( ! isset( $wp_filter[ $filter_name ] ) ) {
				continue;
			}

			$callbacks_by_priority = $wp_filter[ $filter_name ]->callbacks;
			foreach ( $callbacks_by_priority as $priority => $callbacks ) {
				foreach ( $callbacks as $id => $callback ) {
					$is_captcha = false;
					$func = $callback['function'] ?? null;

					if ( is_array( $func ) ) {
						$class_name  = is_object( $func[0] ) ? get_class( $func[0] ) : ( is_string( $func[0] ) ? $func[0] : '' );
						$method_name = is_string( $func[1] ) ? $func[1] : '';

						if ( preg_match( '/turnstile|captcha|recaptcha|hcaptcha|defender|wordfence|cloudflare/i', $class_name ) ||
						     preg_match( '/turnstile|captcha|recaptcha|hcaptcha|defender/i', $method_name ) ) {
							$is_captcha = true;
						}
					} elseif ( is_string( $func ) ) {
						if ( preg_match( '/turnstile|captcha|recaptcha|hcaptcha|defender|wordfence|cloudflare/i', $func ) ) {
							$is_captcha = true;
						}
					} elseif ( is_object( $func ) && ! ( $func instanceof \Closure ) ) {
						$class_name = get_class( $func );
						if ( preg_match( '/turnstile|captcha|recaptcha|hcaptcha|defender|wordfence|cloudflare/i', $class_name ) ) {
							$is_captcha = true;
						}
					}

					if ( $is_captcha ) {
						remove_filter( $filter_name, $callback['function'], $priority );
					}
				}
			}
		}
	}

	public function handle_login( WP_REST_Request $request ) {
		$login = trim( (string) ( $request->get_param( 'email' ) ?: $request->get_param( 'login' ) ?: $request->get_param( 'username' ) ) );
		$password = (string) $request->get_param( 'password' );

		if ( empty( $login ) || empty( $password ) ) {
			return new WP_Error( 'missing_credentials', 'Email/username and password are required.', array( 'status' => 400 ) );
		}

		if ( defined( 'TEST_COOKIE' ) ) {
			$_COOKIE[ TEST_COOKIE ] = 'WP Cookie check';
		}

		$this->strip_captcha_filters();

		$user = null;
		if ( is_email( $login ) ) {
			$user = get_user_by( 'email', $login );
		}
		if ( ! $user ) {
			$user = get_user_by( 'login', $login );
		}
		if ( ! $user && ! is_email( $login ) ) {
			$user = get_user_by( 'email', $login );
		}

		if ( ! $user ) {
			return new WP_Error( 'invalid_login', 'Invalid username or email address.', array( 'status' => 401 ) );
		}

		if ( ! wp_check_password( $password, $user->user_pass, $user->ID ) ) {
			return new WP_Error( 'invalid_login', 'Invalid password. Please check your credentials and try again.', array( 'status' => 401 ) );
		}

		$filtered_user = apply_filters( 'wp_authenticate_user', $user, $password );
		if ( is_wp_error( $filtered_user ) ) {
			$error_code = $filtered_user->get_error_code();
			if ( stripos( $error_code, 'turnstile' ) === false && stripos( $error_code, 'captcha' ) === false && stripos( $error_code, 'invalid_captcha' ) === false ) {
				$err_msg = $filtered_user->get_error_message();
				return new WP_Error( 'invalid_login', ! empty( $err_msg ) ? $err_msg : 'Authentication failed.', array( 'status' => 401 ) );
			}
		} else {
			$user = $filtered_user;
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true, is_ssl() );
		do_action( 'wp_login', $user->user_login, $user );

		$nonce = wp_create_nonce( 'wp_rest' );

		return rest_ensure_response( array(
			'success' => true,
			'user'    => array(
				'id'           => $user->ID,
				'email'        => $user->user_email,
				'username'     => $user->user_login,
				'display_name' => $user->display_name,
				'avatar_url'   => get_avatar_url( $user->ID, array( 'size' => 128 ) ),
				'roles'        => (array) $user->roles,
			),
			'nonce'   => $nonce,
		) );
	}

	public function handle_send_magic_link( WP_REST_Request $request ) {
		$email = sanitize_email( trim( (string) $request->get_param( 'email' ) ) );
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', 'Please provide a valid email address.', array( 'status' => 400 ) );
		}

		$user = get_user_by( 'email', $email );

		$token = sprintf( '%06d', wp_rand( 100000, 999999 ) );
		$token_data = array(
			'email'   => $email,
			'user_id' => $user ? $user->ID : 0,
			'created' => time(),
		);

		set_transient( 'compass_magic_' . $token, $token_data, 15 * MINUTE_IN_SECONDS );

		$slug = get_option( 'xophz_compass_phone_custom_slug', 'my-compass-phone' );
		$magic_url = home_url( '/' . $slug . '?magic_token=' . $token );

		$subject = 'Your Verification Magic Key  for COMPASS Phone';
		$message = "Hello,\n\nYour 6-digit verification code is: " . $token . "\n\nOr click the link below to sign in directly:\n" . $magic_url . "\n\nThis code and link will expire in 15 minutes.\n\nIf you didn't request this, please ignore this email.";
		$headers = array('Content-Type: text/plain; charset=UTF-8');
		@wp_mail( $email, $subject, $message, $headers );

		$response = array(
			'success'     => true,
			'is_new_user' => ! (bool) $user,
			'email'       => $email,
			'message'     => 'Verification code & magic link sent! Please check your email inbox.',
		);

		if ( ( defined( 'WP_ENV' ) && WP_ENV === 'development' ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			$response['dev_magic_url'] = $magic_url;
			$response['dev_token']     = $token;
		}

		return rest_ensure_response( $response );
	}

	public function handle_verify_magic_link( WP_REST_Request $request ) {
		$token = strtoupper( trim( sanitize_text_field( (string) $request->get_param( 'token' ) ) ) );
		if ( empty( $token ) ) {
			return new WP_Error( 'missing_token', 'Verification code or magic link token is required.', array( 'status' => 400 ) );
		}

		$token_data = get_transient( 'compass_magic_' . $token );
		if ( ! $token_data || empty( $token_data['email'] ) ) {
			return new WP_Error( 'invalid_token', 'Magic link is invalid or has expired. Please request a new one.', array( 'status' => 400 ) );
		}

		$email = $token_data['email'];
		$user = get_user_by( 'email', $email );

		if ( ! $user ) {
			$email_parts = explode( '@', $email );
			$username_base = sanitize_user( $email_parts[0], true );
			if ( empty( $username_base ) ) {
				$username_base = 'compass_user';
			}

			$username = $username_base;
			$counter = 1;
			while ( username_exists( $username ) ) {
				$username = $username_base . $counter;
				$counter++;
			}

			$random_password = wp_generate_password( 24, true, true );
			$user_id = wp_create_user( $username, $random_password, $email );

			if ( is_wp_error( $user_id ) ) {
				return new WP_Error( 'user_creation_failed', 'Failed to create user account: ' . $user_id->get_error_message(), array( 'status' => 500 ) );
			}

			$user = get_user_by( 'id', $user_id );
		}

		if ( defined( 'TEST_COOKIE' ) ) {
			$_COOKIE[ TEST_COOKIE ] = 'WP Cookie check';
		}

		delete_transient( 'compass_magic_' . $token );

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true, is_ssl() );
		do_action( 'wp_login', $user->user_login, $user );

		$nonce = wp_create_nonce( 'wp_rest' );

		return rest_ensure_response( array(
			'success' => true,
			'user'    => array(
				'id'           => $user->ID,
				'email'        => $user->user_email,
				'username'     => $user->user_login,
				'display_name' => $user->display_name,
				'avatar_url'   => get_avatar_url( $user->ID, array( 'size' => 128 ) ),
				'roles'        => (array) $user->roles,
			),
			'nonce'   => $nonce,
		) );
	}

	public function handle_get_me() {
		$current_user = wp_get_current_user();
		$logged_in = is_user_logged_in() && $current_user && $current_user->ID > 0;

		if ( ! $logged_in ) {
			return rest_ensure_response( array(
				'logged_in' => false,
				'user'      => null,
				'nonce'     => wp_create_nonce( 'wp_rest' ),
			) );
		}

		return rest_ensure_response( array(
			'logged_in' => true,
			'user'      => array(
				'id'           => $current_user->ID,
				'email'        => $current_user->user_email,
				'username'     => $current_user->user_login,
				'display_name' => $current_user->display_name,
				'avatar_url'   => get_avatar_url( $current_user->ID, array( 'size' => 128 ) ),
				'roles'        => (array) $current_user->roles,
			),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
		) );
	}

	public function handle_logout() {
		wp_logout();
		return rest_ensure_response( array(
			'success' => true,
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );
	}

	public function handle_stripe_checkout( WP_REST_Request $request ) {
		$price = (float) $request->get_param( 'price' );
		$license = sanitize_text_field( (string) $request->get_param( 'license' ) );
		$success_url = esc_url_raw( (string) $request->get_param( 'success_url' ) );
		$cancel_url = esc_url_raw( (string) $request->get_param( 'cancel_url' ) );

		if ( empty( $price ) || empty( $license ) ) {
			return new WP_Error( 'invalid_params', 'Price and license parameters are required.', array( 'status' => 400 ) );
		}

		// Retrieve Stripe Secret Key from WP options or constants
		$stripe_secret_key = get_option( 'compass_stripe_secret_key' );
		if ( empty( $stripe_secret_key ) ) {
			$stripe_secret_key = get_option( 'xophz_compass_stripe_secret_key' );
		}
		if ( empty( $stripe_secret_key ) && defined( 'STRIPE_SECRET_KEY' ) ) {
			$stripe_secret_key = STRIPE_SECRET_KEY;
		}

		if ( empty( $stripe_secret_key ) ) {
			return new WP_Error( 'missing_stripe_key', 'Stripe Secret Key is missing in WordPress Settings (compass_stripe_secret_key).', array( 'status' => 500 ) );
		}

		$unit_amount = (int) round( $price * 100 ); // Amount in cents
		$license_lower = strtolower( $license );
		$is_subscription = ( strpos( $license_lower, 'monthly' ) !== false || strpos( $license_lower, 'engine' ) !== false || strpos( $license_lower, 'castle' ) !== false || strpos( $license_lower, 'sovereign' ) !== false || $price >= 99 );
		$is_castle = ( strpos( $license_lower, 'castle' ) !== false || strpos( $license_lower, 'enterprise' ) !== false || $price >= 3000 );
		$mode = $is_subscription ? 'subscription' : 'payment';

		$body = array(
			'line_items[0][price_data][currency]' => 'usd',
			'line_items[0][price_data][product_data][name]' => 'My Compass - ' . $license,
			'line_items[0][price_data][product_data][tax_code]' => 'txcd_10103000',
			'line_items[0][price_data][unit_amount]' => $unit_amount,
			'line_items[0][quantity]' => 1,
			'mode' => $mode,
			'success_url' => ! empty( $success_url ) ? $success_url : home_url( '/#/checkout_success' ),
			'cancel_url' => ! empty( $cancel_url ) ? $cancel_url : home_url( '/#/pricing' ),
		);

		if ( $is_subscription ) {
			$body['line_items[0][price_data][recurring][interval]'] = 'month';
		}

		if ( $is_castle && $is_subscription ) {
			$body['subscription_data[metadata][contract_term]'] = '6_months_enterprise';
		}

		$response = wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . trim( $stripe_secret_key ),
				'Content-Type'  => 'application/x-www-form-urlencoded',
			),
			'body'    => http_build_query( $body ),
			'timeout' => 15,
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'stripe_connection_error', 'Failed to connect to Stripe API: ' . $response->get_error_message(), array( 'status' => 500 ) );
		}

		$body_res = wp_remote_retrieve_body( $response );
		$data = json_decode( $body_res, true );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 || empty( $data['url'] ) ) {
			$err_msg = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Stripe API returned an invalid response.';
			return new WP_Error( 'stripe_api_error', 'Stripe error: ' . $err_msg, array( 'status' => 500 ) );
		}

		return rest_ensure_response( array(
			'url' => $data['url'],
		) );
	}

	public function admin_permissions_check() {
		return current_user_can( 'manage_options' );
	}

	public function handle_get_settings() {
		$slug = get_option( 'xophz_compass_phone_custom_slug', 'my-compass-phone' );
		$allowed = get_option( '_xophz_compass_phone_allowed_apps', array(
			'glowitheflow', 'xp', 'yellow-links', 'enchiridion', 'lead-magnet',
			'magic-formula', 'questbook', 'treasure-map', 'gale-boomerang',
			'titans-mitt', 'midnight-nerd', 'my-planner', 'pixie-dust', 'phantom-zone'
		) );
		$theme = get_option( '_xophz_compass_phone_theme', 'starship' );

		return rest_ensure_response( array(
			'success' => true,
			'data'    => array(
				'slug'         => $slug,
				'allowed_apps' => is_array( $allowed ) ? $allowed : array(),
				'theme'        => $theme,
			)
		) );
	}

	public function handle_update_settings( WP_REST_Request $request ) {
		$slug = sanitize_title( (string) $request->get_param( 'slug' ) );
		$allowed_apps = $request->get_param( 'allowed_apps' );
		$theme = sanitize_text_field( (string) $request->get_param( 'theme' ) );

		if ( ! empty( $slug ) ) {
			$old_slug = get_option( 'xophz_compass_phone_custom_slug', 'my-compass-phone' );
			update_option( 'xophz_compass_phone_custom_slug', $slug );
			if ( $old_slug !== $slug ) {
				flush_rewrite_rules();
			}
		}

		if ( is_array( $allowed_apps ) ) {
			$clean_apps = array_map( 'sanitize_text_field', $allowed_apps );
			update_option( '_xophz_compass_phone_allowed_apps', $clean_apps );
		}

		if ( ! empty( $theme ) ) {
			update_option( '_xophz_compass_phone_theme', $theme );
		}

		return $this->handle_get_settings();
	}

	public function handle_get_stats() {
		$stats = get_option( '_xophz_compass_phone_stats', array(
			'total_views'  => 0,
			'total_clicks' => 0,
			'app_clicks'   => array(),
			'daily'        => array()
		) );

		if ( ! is_array( $stats ) ) {
			$stats = array(
				'total_views'  => 0,
				'total_clicks' => 0,
				'app_clicks'   => array(),
				'daily'        => array()
			);
		}

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $stats
		) );
	}

	public function handle_track_event( WP_REST_Request $request ) {
		$event = sanitize_text_field( (string) $request->get_param( 'event' ) );
		$app = sanitize_text_field( (string) $request->get_param( 'app' ) );
		$today = date( 'Y-m-d' );

		$stats = get_option( '_xophz_compass_phone_stats', array(
			'total_views'  => 0,
			'total_clicks' => 0,
			'app_clicks'   => array(),
			'daily'        => array()
		) );

		if ( ! is_array( $stats ) ) {
			$stats = array(
				'total_views'  => 0,
				'total_clicks' => 0,
				'app_clicks'   => array(),
				'daily'        => array()
			);
		}

		if ( ! isset( $stats['daily'][ $today ] ) ) {
			$stats['daily'][ $today ] = array( 'views' => 0, 'clicks' => 0 );
		}

		if ( $event === 'view' ) {
			$stats['total_views']++;
			$stats['daily'][ $today ]['views']++;
		} elseif ( $event === 'click' ) {
			$stats['total_clicks']++;
			$stats['daily'][ $today ]['clicks']++;
			if ( ! empty( $app ) ) {
				if ( ! isset( $stats['app_clicks'][ $app ] ) ) {
					$stats['app_clicks'][ $app ] = 0;
				}
				$stats['app_clicks'][ $app ]++;
			}
		}

		// Keep 30 days of daily history
		if ( count( $stats['daily'] ) > 30 ) {
			$stats['daily'] = array_slice( $stats['daily'], -30, 30, true );
		}

		update_option( '_xophz_compass_phone_stats', $stats );

		return rest_ensure_response( array(
			'success' => true,
			'data'    => $stats
		) );
	}

	public function handle_reset_stats() {
		delete_option( '_xophz_compass_phone_stats' );
		return $this->handle_get_stats();
	}
}

new Xophz_Compass_Phone_Auth_Rest();
