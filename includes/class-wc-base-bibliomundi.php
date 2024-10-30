<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Base_BiblioMundi' ) ) :

abstract class WC_Base_BiblioMundi {

	protected $element;
	protected $db;
	protected $plugin_dir_url;

	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;

		$this->plugin_dir_url = plugin_dir_url( dirname( __FILE__ ) );
	}

	public function set_id( $id ) {
		$this->id = $id;
		return $this;
	}

	public function set_element( $element ) {
		$this->element = $element;
		return $this;
	}

	public function get_id() {
		if ( is_numeric( $this->id ) && $this->id ) {
			return absint( $this->id );
		}
	}

	public function get_element() {
		if ( $this->element instanceof SimpleXMLElement ) {
			return $this->element;
		}
	}
	
}

endif;