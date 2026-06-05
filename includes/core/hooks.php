<?php
/**
 * Hooks Class
 * 
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class hooks {

    private static $instance = null;
    private $actions = [];
    private $filters = [];
    private $registered = false;

    private function __construct() {
        $this->init_core_hooks();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init() {
        $this->register_hooks();
    }

    private function init_core_hooks() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('enqueue_block_assets', [$this, 'enqueue_block_assets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('after_setup_theme', [$this, 'theme_setup']);
        add_filter('block_categories_all', [$this, 'register_block_categories'], 10, 2);
        add_filter('render_block', [$this, 'modify_block_render'], 10, 2);
    }

    private function register_hooks() {
        if ($this->registered) {
            return;
        }

        $this->register_actions();
        $this->register_filters();
        $this->registered = true;
    }

    public function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        $this->actions[] = compact('hook', 'callback', 'priority', 'accepted_args');
        return $this;
    }

    public function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        $this->filters[] = compact('hook', 'callback', 'priority', 'accepted_args');
        return $this;
    }

    private function register_actions() {
        foreach ($this->actions as $action) {
            $cb = $this->resolve_callback($action['callback']);
            if ($cb) {
                add_action($action['hook'], $cb, $action['priority'], $action['accepted_args']);
            }
        }
    }

    private function register_filters() {
        foreach ($this->filters as $filter) {
            $cb = $this->resolve_callback($filter['callback']);
            if ($cb) {
                add_filter($filter['hook'], $cb, $filter['priority'], $filter['accepted_args']);
            }
        }
    }

    private function resolve_callback($callback) {
        if (is_callable($callback)) {
            return $callback;
        }

        if (is_array($callback) && count($callback) === 2) {
            list($class, $method) = $callback;

            if (is_object($class) && method_exists($class, $method)) {
                return [$class, $method];
            }

            if (is_string($class) && class_exists($class)) {
                if (method_exists($class, 'get_instance')) {
                    $instance = $class::get_instance();
                    return method_exists($instance, $method) ? [$instance, $method] : null;
                }
                
                if (method_exists($class, $method)) {
                    return [$class, $method];
                }
            }
        }

        return null;
    }

    public function theme_setup() {
        load_theme_textdomain(OMEGA_DESIGN_TEXTDOMAIN, OMEGA_DESIGN_DIR . '/languages');
        add_theme_support('wp-block-styles');
        add_theme_support('responsive-embeds');
        add_theme_support('editor-styles');
    }

    public function register_assets() {
        wp_register_style('omega-design-style', OMEGA_DESIGN_CSS_URI . '/style.css', [], OMEGA_DESIGN_VERSION);
        wp_register_script('omega-design-script', OMEGA_DESIGN_JS_URI . '/main.js', ['jquery'], OMEGA_DESIGN_VERSION, true);
    }

    public function enqueue_frontend_assets() {
        $this->register_assets();
        wp_enqueue_style('omega-design-style');
        wp_enqueue_script('omega-design-script');
    }

    public function enqueue_admin_assets($hook) {
        wp_enqueue_style('omega-design-admin', OMEGA_DESIGN_CSS_URI . '/admin.css', [], OMEGA_DESIGN_VERSION);
    }

    public function enqueue_block_assets() {
        // Block assets that load everywhere
    }

    public function enqueue_editor_assets() {
        wp_enqueue_script('omega-design-editor', OMEGA_DESIGN_JS_URI . '/editor.js', ['wp-blocks', 'wp-dom-ready', 'wp-edit-post'], OMEGA_DESIGN_VERSION, true);
    }

    public function register_block_categories($categories, $post) {
        $categories[] = [
            'slug'  => 'omega-design',
            'title' => __('Omega Design', 'omega-design'),
        ];
        return $categories;
    }

    public function modify_block_render($block_content, $block) {
        if ($block['blockName'] === 'core/group') {
            // Add custom classes if needed
        }
        return $block_content;
    }
}