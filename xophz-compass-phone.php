<?php
/**
 * Plugin Name:       My Compass Phone App
 * Description:       Standalone backend and router for the My Compass Phone web app.
 * Version:           26.9.2
 * Author:            Hall of the Gods, Inc.
 * Category:          Castle Walls
 * Text Domain:       xophz-compass-phone
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'XOPHZ_COMPASS_PHONE_VERSION', '26.9.2' );
define( 'XOPHZ_COMPASS_PHONE_PATH', plugin_dir_path( __FILE__ ) );
define( 'XOPHZ_COMPASS_PHONE_URL', plugin_dir_url( __FILE__ ) );

require_once XOPHZ_COMPASS_PHONE_PATH . 'includes/class-xophz-compass-phone-auth-rest.php';

class Xophz_Compass_Phone {
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        
        // Flush rewrites when setting is saved
        add_action( 'update_option_xophz_compass_phone_custom_slug', array( $this, 'flush_rewrites_on_save' ), 10, 2 );

        // Public rewrite and template
        add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
        add_action( 'init', array( $this, 'register_rewrites' ) );
        add_action( 'template_redirect', array( $this, 'template_redirect' ) );
    }

    public function add_plugin_admin_menu() {
        add_options_page(
            'My Compass Phone Settings',
            'My Compass Phone',
            'manage_options',
            'xophz-compass-phone',
            array( $this, 'display_plugin_setup_page' )
        );
    }

    public function register_settings() {
        register_setting( 'xophz_compass_phone_options', 'xophz_compass_phone_custom_slug' );
    }

    public function display_plugin_setup_page() {
        ?>
        <div class="wrap">
            <h2>My Compass Phone Settings</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields( 'xophz_compass_phone_options' );
                do_settings_sections( 'xophz_compass_phone_options' );
                $slug = get_option( 'xophz_compass_phone_custom_slug', 'my-compass-phone' );
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Deployment Slug</th>
                        <td>
                            <input type="text" name="xophz_compass_phone_custom_slug" value="<?php echo esc_attr( $slug ); ?>" class="regular-text" />
                            <p class="description">The URL slug where the phone app will be loaded (e.g. <code>my-compass-phone</code> for <code>/my-compass-phone</code>). Leave blank to disable standalone rendering.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function flush_rewrites_on_save( $old_value, $new_value ) {
        if ( $old_value !== $new_value ) {
            $this->register_rewrites();
            flush_rewrite_rules();
        }
    }

    public function register_query_vars( $vars ) {
        $vars[] = 'xophz_compass_phone';
        return $vars;
    }

    public function register_rewrites() {
        $slug = get_option( 'xophz_compass_phone_custom_slug', 'my-compass-phone' );
        
        if ( ! empty( $slug ) ) {
            add_rewrite_rule(
                '^' . $slug . '/?$',
                'index.php?xophz_compass_phone=1',
                'top'
            );
            // Catch-all for frontend routing (e.g. vue-router)
            add_rewrite_rule(
                '^' . $slug . '/(.*)?$',
                'index.php?xophz_compass_phone=1',
                'top'
            );
        }
    }

    private function is_dev_mode() {
        return ( defined( 'WP_ENV' ) && WP_ENV === 'development' ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG );
    }

    public function template_redirect() {
        if ( get_query_var( 'xophz_compass_phone' ) ) {
            $is_dev = $this->is_dev_mode();
            $vite_port = '8082';
            if ( isset( $_SERVER['HTTP_HOST'] ) ) {
                $host_parts = explode(':', $_SERVER['HTTP_HOST']);
                $wp_host = $host_parts[0];
            } else {
                $wp_host = wp_parse_url( home_url(), PHP_URL_HOST );
            }
            $vite_url = "//" . $wp_host . ":" . $vite_port;

            if ( $is_dev ) {
                $internal_host = 'compass';
                $dev_html = @file_get_contents("http://{$internal_host}:{$vite_port}/");
                if ($dev_html) {
                    // Rewrite relative src/href/import for dev server
                    $dev_html = str_replace('src="/', 'src="' . $vite_url . '/', $dev_html);
                    $dev_html = str_replace('href="/', 'href="' . $vite_url . '/', $dev_html);
                    $dev_html = str_replace('import("/', 'import("' . $vite_url . '/', $dev_html);

                    // Inject Vite client
                    if (strpos($dev_html, '/@vite/client') === false) {
                        $vite_client = '<script type="module" src="' . esc_url($vite_url) . '/@vite/client"></script>';
                        $dev_html = str_replace('</head>', $vite_client . "\n</head>", $dev_html);
                    }

                    $nonce = wp_create_nonce('wp_rest');
                    $user_id = get_current_user_id();
                    $wp_api_settings = "<script>window.wpApiSettings = { root: '" . esc_url_raw(rest_url()) . "', nonce: '" . $nonce . "', pluginUrl: '" . esc_url_raw(XOPHZ_COMPASS_PHONE_URL) . "', version: '" . esc_js(XOPHZ_COMPASS_PHONE_VERSION) . "', userId: " . $user_id . " };</script>";
                    $dev_html = str_replace('</head>', $wp_api_settings . "\n</head>", $dev_html);

                    echo $dev_html;
                    exit;
                }
            }

            // Load the Vite build's index.html
            $index_path = XOPHZ_COMPASS_PHONE_PATH . 'public/dist/index.html';
            
            if ( file_exists( $index_path ) ) {
                $content = file_get_contents( $index_path );
                $dist_url = XOPHZ_COMPASS_PHONE_URL . 'public/dist/';
                
                // Rewrite absolute paths for production assets
                $content = str_replace( '"/assets/', '"' . $dist_url . 'assets/', $content );
                $content = str_replace( "'/assets/", "'" . $dist_url . "assets/", $content );
                $content = str_replace( '"/registerSW.js"', '"' . $dist_url . 'registerSW.js"', $content );
                $content = str_replace( '"/manifest.webmanifest"', '"' . $dist_url . 'manifest.webmanifest"', $content );
                $content = str_replace( '"/vite.svg"', '"' . $dist_url . 'vite.svg"', $content );
                
                // Inject wpApiSettings for production so API requests have the nonce
                $nonce = wp_create_nonce('wp_rest');
                $user_id = get_current_user_id();
                $wp_api_settings = "<script>window.wpApiSettings = { root: '" . esc_url_raw(rest_url()) . "', nonce: '" . $nonce . "', pluginUrl: '" . esc_url_raw(XOPHZ_COMPASS_PHONE_URL) . "', version: '" . esc_js(XOPHZ_COMPASS_PHONE_VERSION) . "', userId: " . $user_id . " };</script>";
                $content = str_replace('</head>', $wp_api_settings . "\n</head>", $content);
                
                echo $content;
            } else {
                echo '<h2>My Compass Phone is not built yet.</h2><p>Please run <code>npm run build</code> in the <code>apps/my-compass-phone</code> directory.</p>';
            }
            exit;
        }
    }
}

function run_xophz_compass_phone() {
    new Xophz_Compass_Phone();
}
add_action( 'plugins_loaded', 'run_xophz_compass_phone' );

function xophz_compass_phone_activate() {
    $plugin = new Xophz_Compass_Phone();
    $plugin->register_rewrites();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'xophz_compass_phone_activate' );
