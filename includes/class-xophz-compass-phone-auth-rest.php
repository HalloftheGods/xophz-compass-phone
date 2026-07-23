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

	public function handle_login( WP_REST_Request $request ) {
		$login = trim( (string) $request->get_param( 'email' ) );
		$password = (string) $request->get_param( 'password' );

		if ( empty( $login ) || empty( $password ) ) {
			return new WP_Error( 'missing_credentials', 'Email/username and password are required.', array( 'status' => 400 ) );
		}

		$user = wp_authenticate_username_password( null, $login, $password );
		if ( is_wp_error( $user ) ) {
			$user = wp_authenticate_email_password( null, $login, $password );
		}

		if ( is_wp_error( $user ) ) {
			return new WP_Error( 'invalid_login', 'Invalid password. Please check your credentials and try again.', array( 'status' => 401 ) );
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

		$subject = 'Your Verification Code & Magic Link for COMPASS Phone';
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
}

new Xophz_Compass_Phone_Auth_Rest();
