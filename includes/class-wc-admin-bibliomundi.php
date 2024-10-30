<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Admin_BiblioMundi' ) ) :

class WC_Admin_BiblioMundi extends WC_Base_BiblioMundi {
	
	public function __construct() {
		parent::__construct();
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
	}

	public function scripts( $hook ) {
		if ( 'woocommerce_page_wc-settings' != $hook ) {
			return;
		}

		wp_enqueue_script( 'bibliomundi_script', $this->plugin_dir_url . 'assets/js/admin.js', array( 'jquery' ) );
		wp_register_style( 'bibliomundi_css', $this->plugin_dir_url . 'assets/css/admin.css' );
		wp_enqueue_style( 'bibliomundi_css' );
	}

	public static function add_extra_fields() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		$plugin = 'woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php';

		$add_fields = true;
		if ( is_plugin_active( $plugin ) ) {
			$settings   = get_option( 'wcbcf_settings' );
			$add_fields = ! isset( $settings['birthdate_sex'] );
		}

		return $add_fields;
	}

}

return new WC_Admin_BiblioMundi;

endif;