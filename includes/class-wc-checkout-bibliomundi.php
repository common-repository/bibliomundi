<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Checkout_BiblioMundi' ) ) :

class WC_Checkout_BiblioMundi {

	public function __construct() {
		if ( WC_Admin_BiblioMundi::add_extra_fields() ) {
			add_filter( 'woocommerce_load_order_data', array( $this, 'load_order_data' ) );
			add_filter( 'woocommerce_billing_fields', array( $this, 'checkout_billing_fields' ), 15 );
			add_action( 'woocommerce_checkout_process', array( $this, 'checkout_process' ) );			
		}
		add_action( 'woocommerce_checkout_process', array( $this, 'validate' ) );			
	}

	public function load_order_data( $data ) {
		$data['billing_birthdate'] = '';
		$data['billing_sex']       = '';
		return $data;
	}

	public function checkout_billing_fields( $fields ) {

		$new_fields = array();
		if ( is_array( $fields ) && sizeof( $fields ) ) {
			foreach ( $fields as $key => $value ) {
				$new_fields[$key] = $value;
				if ( 'billing_last_name' == $key ) {
					$new_fields['billing_birthdate'] = array(
						'label'       => __( 'Birthdate', 'woocommerce-bibliomundi' ),
						'placeholder' => _x( 'Birthdate', 'placeholder', 'woocommerce-bibliomundi' ),
						'class'       => array( 'form-row-first' ),
						'clear'       => false,
						'required'    => true
					);

					$new_fields['billing_sex'] = array(
						'type'        => 'select',
						'label'       => __( 'Sex', 'woocommerce-bibliomundi' ),
						'class'       => array( 'form-row-last' ),
						'clear'       => true,
						'required'    => true,
						'options'     => array(
							'0'                                       => __( 'Select', 'woocommerce-bibliomundi' ),
							__( 'Female', 'woocommerce-bibliomundi' ) => __( 'Female', 'woocommerce-bibliomundi' ),
							__( 'Male', 'woocommerce-bibliomundi' )   => __( 'Male', 'woocommerce-bibliomundi' )
						)
					);
				}
			}
		} 
		
		return apply_filters( 'woocommerce_bibliomundi_billing_fields', $new_fields );
	}

	public function checkout_process() {
		if ( ! isset( $_POST['billing_birthdate'] ) || empty( $_POST['billing_birthdate'] ) ) {
			wc_add_notice( sprintf( '<strong>%s</strong> %s.', __( 'Birthdate', 'woocommerce-bibliomundi' ), __( 'is a required field', 'woocommerce-bibliomundi' ) ), 'error' );
		}

		if ( ! isset( $_POST['billing_sex'] ) || empty( $_POST['billing_sex'] ) ) {
			wc_add_notice( sprintf( '<strong>%s</strong> %s.', __( 'Sex', 'woocommerce-bibliomundi' ), __( 'is a required field', 'woocommerce-bibliomundi' ) ), 'error' );
		}
	}

	public function validate() {
		$data    = array();
		$user_ID = get_current_user_ID();
		if ( ! $user_ID ) {
			wc_add_notice( __( 'Please, sign in to continue.', 'woocommerce-bibliomundi' ), 'error' );
		} else {
			$cart    = WC()->cart->get_cart();
			if ( $cart ) {
				$first_name = wc_clean( $_POST['billing_first_name'] );
				$last_name  = wc_clean( $_POST['billing_last_name'] );
				$gender     = strtolower( substr( wc_clean( $_POST['billing_sex'] ), 0, 1 ) );
				$birthdate  = wc_clean( $_POST['billing_birthdate'] ) ? date( 'Y/m/d', strtotime( $_POST['billing_birthdate'] ) ) : null;

				$data = array(
					'customerIdentificationNumber' => $user_ID,
					'customerFullname'             => $first_name . ' ' . $last_name,
					'customerEmail'                => is_email( $_POST['billing_email'] ),
					'customerGender'               => $gender,
					'customerBirthday'             => $birthdate,
					'customerCountry'              => wc_clean( $_POST['billing_country'] ),
					'customerState'                => wc_clean( $_POST['billing_state'] ),
				);

				foreach( WC()->cart->get_cart() as $item ){
					$product = new WC_Product( $item['product_id'] );					
					$data['items'][] = array(
						'bibliomundiEbookID' => get_post_meta( $item['product_id'], 'id_bibliomundi', true ),
						'price'              => $product->price,
					);
				}
			}	

			$data = apply_filters( 'woocommerce_bibliomundi_checkout_validate', $data );

			$return = bbm_api()->validate_order( $data );
			
			if ( ! $return ) {
				wc_add_notice( __( 'Please, verify your data.', 'woocommerce-bibliomundi' ), 'error' );
			}
		}
	}


}

return new WC_Checkout_BiblioMundi;

endif;