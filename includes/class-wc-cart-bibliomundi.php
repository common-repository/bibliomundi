<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Cart_BiblioMundi' ) ) :

class WC_Cart_BiblioMundi {

	private static $MAX = 1;

	public function __construct() {
		add_filter( 'woocommerce_quantity_input_args', array( __CLASS__, 'quantity_input_args' ), 10, 2 );
		add_action( 'woocommerce_add_to_cart_validation', array( __CLASS__, 'add_to_cart_validation' ), 1, 3 );
		add_action( 'woocommerce_update_cart_validation', array( __CLASS__, 'update_cart_validation' ), 1, 4 );
	}

	public static function quantity_input_args( $args, $product ) {		
		if ( self::is_bbm_ebook( $product->ID ) ) {
			$args['max_value'] = self::$MAX;
		}
		
		return $args;
	}

	public static function add_to_cart_validation( $passed, $product_id, $quantity ) {
		return self::add_notice( $product_id, $quantity );
	}

	public static function update_cart_validation( $passed, $cart_item_key, $values, $quantity ) {
		return self::add_notice( $values['product_id'], $quantity );
	}

	private static function is_bbm_ebook( $ebook_id ) {
		$id_bibliomundi = get_post_meta( $ebook_id, 'id_bibliomundi', true );
		return $ebook_id && $id_bibliomundi;
	}

	private static function is_valid( $ebook_id, $quantity ) {
		global $woocommerce;

		$items = $woocommerce->cart->get_cart();
		if ( is_array( $items ) && sizeof( $items ) > 0 ) {
			foreach( $items as $item ) {
				if ( $item['product_id'] == $ebook_id ) {
					return $item['quantity'] > self::$MAX;
				}
			}
		}
		return $quantity <= self::$MAX;
	}

	private static function add_notice( $ebook_id, $quantity ) {
		if ( ! self::is_bbm_ebook( $ebook_id ) ) {
			return true;
		}

		$passed = self::is_valid( $ebook_id, $quantity );
		
		if ( ! $passed ) {
			global $woocommerce;
			
			$product = get_product( $ebook_id );
			if ( ! is_wp_error( $product ) ) {
				$product_title = $product->post->post_title;
				$message = sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s.', 'woocommerce-bibliomundi' ),
					self::$MAX,
					$product_title,
					'<a href="' . $woocommerce->cart->get_cart_url() . '" title="' . __( 'Go to cart', 'woocommerce-bibliomundi' ) . '">' . __( 'your cart', 'woocommerce-bibliomundi' ) . '</a>'
				);

				wc_add_notice( $message, 'error' );				
			}
		}

		return $passed;
	}

}

return new WC_Cart_BiblioMundi;

endif;