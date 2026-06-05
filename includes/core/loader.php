<?php
/**
 * Theme Loader Class
 * 
 * @package OmegaDesign\core
 */

namespace OmegaDesign\core;

defined('ABSPATH') || exit;

class loader {

    private static $instance = null;
    private $modules = [];
    private $configs = [];

    private function __construct() {
        $this->load_configs();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init() {
        $this->load_modules();
        
        if (is_admin()) {
            $this->init_admin();
        } else {
            $this->init_public();
        }

        do_action('omega_design_loader_initialized', $this);
        
        return $this;
    }

    private function init_admin() {
        $admin_class = 'OmegaDesign\\admin\\menu';
        if (class_exists($admin_class) && method_exists($admin_class, 'get_instance')) {
            $admin_class::get_instance();
        }
    }

    private function init_public() {
        $public_class = 'OmegaDesign\\public\\public';
        if (class_exists($public_class) && method_exists($public_class, 'get_instance')) {
            $public_class::get_instance();
        }
    }

    private function load_configs() {
    $this->configs = [
        'hooks' => [
            'class'    => 'OmegaDesign\\core\\hooks',
            'priority' => 10,
            'required' => true,
            'deps'     => [],
            'enabled'  => true,
        ],
        'top_bar_menu' => [
            'class'    => 'OmegaDesign\\customizer\\menus',
            'priority' => 20,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => true,
        ],
        'sidebar_menu' => [
            'class'    => 'OmegaDesign\\admin\\menu',
            'priority' => 30,
            'required' => false,
            'deps'     => ['hooks'],
            'enabled'  => is_admin(),
        ],
    ];
}

    private function load_modules() {
        uasort($this->configs, function($a, $b) {
            return ($a['priority'] ?? 100) <=> ($b['priority'] ?? 100);
        });

        foreach ($this->configs as $id => $config) {
            if (!$config['enabled']) {
                continue;
            }

            if (!$this->check_dependencies($config['deps'])) {
                if ($config['required']) {
                    $this->log_error("Required module {$id} dependencies missing.");
                    return;
                }
                continue;
            }

            $this->initialize_module($id, $config);
        }
    }

    private function initialize_module($id, $config) {
        $class = $config['class'];

        if (!class_exists($class)) {
            if ($config['required']) {
                $this->log_error("Required class not found: {$class}");
            }
            return;
        }

        try {
            $module = method_exists($class, 'get_instance') ? $class::get_instance() : new $class();
            $this->modules[$id] = $module;

            if (method_exists($module, 'init')) {
                $module->init();
            }
        } catch (\Throwable $e) {
            $this->log_error("Module {$id} failed: " . $e->getMessage());
        }
    }

    private function check_dependencies($deps) {
        foreach ($deps as $dep) {
            if (!isset($this->modules[$dep])) {
                return false;
            }
        }
        return true;
    }

    public function get_module($id) {
        return $this->modules[$id] ?? null;
    }

    public function is_loaded($id) {
        return isset($this->modules[$id]);
    }

    public function get_loaded_modules() {
        return $this->modules;
    }

    private function log_error($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[OmegaDesign] ' . $message);
        }
    }
}