<?php
/**
 * Left Sidebar Admin Menu
 * 
 * @package OmegaDesign\admin
 */

namespace OmegaDesign\admin;

defined('ABSPATH') || exit;

class menu {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'register_admin_menu']);
    }
    
    public function init() {}
    
    public function register_admin_menu() {
        add_menu_page(
            __('Omega Design', 'omega-design'),
            __('Omega Design', 'omega-design'),
            'manage_options',
            'omega-dashboard',
            [$this, 'dashboard_page'],
            'dashicons-layout',
            2
        );
        
        add_submenu_page(
            'omega-dashboard',
            __('Dashboard', 'omega-design'),
            __('Dashboard', 'omega-design'),
            'manage_options',
            'omega-dashboard',
            [$this, 'dashboard_page']
        );
        
        add_submenu_page(
            'omega-dashboard',
            __('Site Builder', 'omega-design'),
            __('Site Builder', 'omega-design'),
            'manage_options',
            'site-editor.php'
        );
        
        add_submenu_page(
            'omega-dashboard',
            __('Settings', 'omega-design'),
            __('Settings', 'omega-design'),
            'manage_options',
            'omega-settings',
            [$this, 'settings_page']
        );
        
        add_submenu_page(
            'omega-dashboard',
            __('Appearance', 'omega-design'),
            __('Appearance', 'omega-design'),
            'manage_options',
            'customize.php'
        );
        
        add_submenu_page(
            'omega-dashboard',
            __('Menus', 'omega-design'),
            __('Menus', 'omega-design'),
            'manage_options',
            'nav-menus.php'
        );
        
        add_submenu_page(
            'omega-dashboard',
            __('Widgets', 'omega-design'),
            __('Widgets', 'omega-design'),
            'manage_options',
            'widgets.php'
        );
        
        add_submenu_page(
            'omega-dashboard',
            __('Plugins', 'omega-design'),
            __('Plugins', 'omega-design'),
            'manage_options',
            'plugins.php'
        );
    }
    
    public function dashboard_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Omega Design Dashboard', 'omega-design'); ?></h1>
            <div class="omega-dashboard-content">
                <p><?php esc_html_e('Welcome to Omega Design Theme Dashboard', 'omega-design'); ?></p>
            </div>
        </div>
        <?php
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Theme Settings', 'omega-design'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('omega_settings_group');
                do_settings_sections('omega_settings_page');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Example Setting', 'omega-design'); ?></th>
                        <td>
                            <input type="text" name="omega_example_setting" value="<?php echo esc_attr(get_option('omega_example_setting')); ?>" />
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}