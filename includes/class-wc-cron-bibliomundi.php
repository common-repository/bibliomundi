<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Cron_Bibliomundi {

    public function __construct() {
        add_action('init', array($this, 'flush_rules'));
        add_filter('query_vars', array($this, 'query_vars'));
        add_action('wp_loaded', array($this, 'add_rules'));
        add_action('template_redirect', array($this, 'template_redirect'));
    }

    public function query_vars($query_vars) {
        $query_vars[] = 'route';
        return $query_vars;
    }

    public function add_rules() {
        add_rewrite_rule('cron_bibliomundi/?$', 'index.php?route=cron_bibliomundi', 'top');
    }

    public function flush_rules() {
        if (!empty($_GET['flush'])) {
            $this->add_rules();
            flush_rewrite_rules();
        }
    }

    public function template_redirect() {
        $route = get_query_var('route', false);
        if (empty($route) && ($route !== 'cron_bibliomundi')) {
            return;
        } else {            
            wp_send_json(self::exec());
        }
    }

    static public function exec() {
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        if ( ! class_exists( 'WC_Catalog_BiblioMundi' ) ) {
			$bbm_plugin = dirname( __FILE__ ) . '/../woocommerce-bibliomundi.php';
			if ( file_exists( $bbm_plugin ) ) {
				require_once $bbm_plugin;
			}
		}
		WC_Catalog_Bibliomundi::import( 'updates' );
        return true;
    }
}