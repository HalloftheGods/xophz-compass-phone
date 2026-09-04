<?php
/**
 * Plugin Name:       My Compass Phone App
 * Description:       Standalone backend and router for the My Compass Phone web app.
 * Version:           26.9.3-1329
 * Author:            Hall of the Gods, Inc.
 * Category:          Castle Walls
 * Text Domain:       xophz-compass-phone
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'XOPHZ_COMPASS_PHONE_VERSION', '26.9.3-1329' );
define( 'XOPHZ_COMPASS_PHONE_PATH', plugin_dir_path( __FILE__ ) );
define( 'XOPHZ_COMPASS_PHONE_URL', plugin_dir_url( __FILE__ ) );

require_once XOPHZ_COMPASS_PHONE_PATH . 'includes/class-xophz-compass-phone-auth-rest.php';

class Xophz_Compass_Phone {

    /**
     * Consolidated Dev Proxy instance.
     *
     * @var Xophz_Compass_Dev_Proxy|null
     */
    protected $dev_proxy = null;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        
        // Flush rewrites when setting is saved
        add_action( 'update_option_xophz_compass_phone_custom_slug', array( $this, 'flush_rewrites_on_save' ), 10, 2 );

        // Consolidated Dev Proxy & Production Dist Loader
        if ( class_exists( 'Xophz_Compass_Dev_Proxy' ) ) {
            $this->dev_proxy = new Xophz_Compass_Dev_Proxy( array(
                'slug'                 => 'phone',
                'default_slug'         => 'my-compass-phone',
                'dev_port'             => 8082,
                'query_var'            => 'xophz_compass_phone',
                'plugin_path'          => XOPHZ_COMPASS_PHONE_PATH,
                'plugin_url'           => XOPHZ_COMPASS_PHONE_URL,
                'version'              => XOPHZ_COMPASS_PHONE_VERSION,
                'candidate_dist_paths' => array(
                    XOPHZ_COMPASS_PHONE_PATH . 'public/dist/index.html',
                ),
            ) );
        }
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

    public function register_rewrites() {
        if ( $this->dev_proxy ) {
            $this->dev_proxy->register_rewrites();
        }
    }

    public function get_dev_proxy() {
        return $this->dev_proxy;
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

function xophz_compass_phone_action_links( $links ) {
    foreach ( $links as $link ) {
        if ( stripos( $link, '>Settings<' ) !== false ) {
            return $links;
        }
    }
    $settings_link = '<a href="options-general.php?page=xophz-compass-phone">' . __( 'Settings', 'xophz-compass-phone' ) . '</a>';
    $new_links = array( 'settings' => $settings_link );
    foreach ( $links as $key => $value ) {
        $new_links[ $key ] = $value;
    }
    return $new_links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'xophz_compass_phone_action_links' );
