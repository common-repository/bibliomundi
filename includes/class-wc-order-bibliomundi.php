<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Order_BiblioMundi' ) ) :

class WC_Order_BiblioMundi {

	public function __construct() {
		if ( WC_Admin_BiblioMundi::add_extra_fields() ) {
			add_filter( 'woocommerce_admin_billing_fields', array( $this, 'admin_billing_fields' ) );
			add_filter( 'woocommerce_process_shop_order_meta', array( $this, 'process_shop_order_meta' ) );
		}

		add_action( 'woocommerce_order_status_completed', array( $this, 'order_status_completed' ) );
		add_action( 'woocommerce_download_product', array( $this, 'download_product' ), 10, 6 );
	}

	public function admin_billing_fields() {
		$fields['birthdate'] = array(
			'label' => __( 'Birthdate', 'woocommerce-bibliomundi' )
		);

		$fields['sex'] = array(
			'label' => __( 'Sex', 'woocommerce-bibliomundi' )
		);

		return apply_filters( 'woocommerce_bibliomundi_admin_billing_fields', $fields );
	}

	public function order_data_after_billing_address( $order ) {
		$html = '<strong>' . __( 'Birthdate', 'woocommerce-bibliomundi' ) . ': </strong>' . esc_html( $order->billing_birthdate ) . '<br />';
		$html .= '<strong>' . __( 'Sex', 'woocommerce-bibliomundi' ) . ': </strong>' . esc_html( $order->billing_sex ) . '<br />';
		echo $html;
	}

	public function process_shop_order_meta( $post_id ) {
		update_post_meta( $post_id, '_billing_birthdate', woocommerce_clean( $_POST['_billing_birthdate'] ) );
		update_post_meta( $post_id, '_billing_sex', woocommerce_clean( $_POST['_billing_sex'] ) );
	}

	public function order_status_completed( $order_id ) {
		$order = new WC_Order( $order_id );
		if ( $order ) {
			$data = array(
				'transactionKey'               => $order_id,
				'customerIdentificationNumber' => $order->get_user_id(),
				'customerFullname'             => $order->billing_first_name . ' ' . $order->billing_last_name,
				'customerEmail'                => $order->billing_email,
				'customerGender'               => strtolower( substr( $order->billing_sex, 0, 1 ) ),
				'customerBirthday'             => date( 'Y/m/d', strtotime( $order->billing_birthdate ) ),
				'customerCountry'              => $order->billing_country,
				'customerState'                => !empty($order->billing_state) ? $order->billing_state : 'RJ',
			);
			$items = $order->get_items();
			if ( is_array( $items ) && sizeof( $items ) > 0 ) {
				foreach ( $items as $item ) {
					if ( is_array( $item ) && array_key_exists( 'item_meta_array', $item ) ) {
						foreach ( $item['item_meta_array'] as $meta ) {
							if ( '_product_id' == $meta->key ) {
								$product_id = absint( $meta->value );
								break;
							}
						}
					}
					elseif ( $item instanceof WC_Order_Item_Product ) {	// Woocemmerce 3.x.x
						$product_id = $item->get_product_id();
					}
					
					if ( $product_id ) {
						$product = new WC_Product( $product_id );
						$data['items'][] = array(
							'bibliomundiEbookID' => get_post_meta( $product_id, 'id_ebook', true ),
							'price'              => $product->price,
							'currency'			 => get_post_meta( $product_id, 'currency', true ),
						);	
					}
				}	
				bbm_api()->checkout_order( $data );
			}
		}		
	}

	public function download_product( $user_email, $order_key, $product_id, $user_id, $download_id, $order_id ){
		$id_ebook = absint( get_post_meta( $product_id, 'id_ebook', true ) );

		if ( ! $id_ebook ) {
			wp_die( __( 'Invalid Ebook ID.', 'woocommerce-bibliomundi' ) );
		}

		$data = array(
			'ebook_id'        => $id_ebook,
			'transaction_key' => $order_id,
		);

		bbm_api()->download( $data );
	}

}

return new WC_Order_BiblioMundi;

endif;