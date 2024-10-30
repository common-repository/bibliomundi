<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Customer_BiblioMundi' ) ) :

class WC_Customer_BiblioMundi {
	
	public function __construct() {
		if ( WC_Admin_BiblioMundi::add_extra_fields() ) {
			add_filter( 'woocommerce_customer_meta_fields', array( $this, 'customer_meta_fields' ) );
		}
	}

	public function customer_meta_fields() {		
		$new_fields['billing']['title'] = __( 'Customer Billing', 'woocommerce-bibliomundi' );

		$new_fields['billing']['fields']['billing_birthdate'] = array(
			'label' => __( 'Birthdate', 'woocommerce-bibliomundi' ),
			'description' => ''
		);
		
		$new_fields['billing']['fields']['billing_sex'] = array(
			'label' => __( 'Sex', 'woocommerce-bibliomundi' ),
			'description' => ''
		);

		return apply_filters( 'woocommerce_bibliomuni_customer_meta_fields', $new_fields );		
	}

}

return new WC_Customer_BiblioMundi;

endif;