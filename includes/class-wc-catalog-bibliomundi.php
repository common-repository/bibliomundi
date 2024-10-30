<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Catalog_BiblioMundi' ) ) :

class WC_Catalog_BiblioMundi {

	public function __construct() {
		add_action( 'wp_ajax_bibliomundi_import_catalog', array( $this, 'ajax_import' ) );
		add_action( 'wp_ajax_bibliomundi_import_status', array( $this, 'ajax_status' ) );
        add_action( 'wp_ajax_bibliomundi_remove_products', array( $this, 'ajax_remove' ) );
	}

	public function ajax_status() {
		wp_send_json(json_decode(file_get_contents(dirname(__FILE__) . '/../log/import.lock')));
	}

	public function ajax_import() {
		$return = array( 
			'error' => true, 
			'msg'   => __( 'Invalid operation', 'woocommerce-bibliomundi' ),
		);

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			$return['msg'] = __( "You don't have permission to manage woocommerce.", 'woocommerce-bibliomundi' );
		} else {
			$security = isset( $_POST['security'] ) && $_POST['security'] ? $_POST['security'] : NULL;
			$scope    = isset( $_POST['scope'] ) && $_POST['scope'] ? $_POST['scope'] : NULL;

			if ( $security && wp_verify_nonce( $security, 'bbm_nonce' ) ) {
				if ( 'complete' == $scope ) {
					$action = __( 'import', 'woocommerce-bibliomundi' );
				} else {
					$action = __( 'update', 'woocommerce-bibliomundi' );
				}
				$response = self::import( $scope );
				if ( ! is_wp_error( $response ) || ! $response ) {
					$return = array(
						'error' => false,
						'msg'   => sprintf( esc_html__( 'Success to %s catalog.', 'woocommerce-bibliomundi' ), $action ),
					);
				} else{
					if ( is_wp_error( $response ) ) {
						$msg = $response->get_error_message();
					} else {
						esc_html__( 'Error to %s catalog.', 'woocommerce-bibliomundi' );
					}
					$return['msg'] = sprintf( $msg, $action );
				}
			}			
		}

		wp_send_json( $return );	
	}

	public static function write_lock($lockfile = null, $content) {
		$lockfile = ($lockfile) ? $lockfile : dirname(__FILE__) . '/../log/import.lock';
		$lock = fopen($lockfile, 'w');
		//ftruncate($lock, 0);
        if (flock($lock, LOCK_EX)) {
            ftruncate($lock, 0);
            fwrite($lock, json_encode($content).PHP_EOL);
            fflush($lock);
            flock($lock, LOCK_UN);
        }
        fclose($lock);
	}

	public static function import( $scope ) {
	    $addEbooksCat = get_option( '_bibliomundi_ebook_cat' );
		$result = array('status' => 'progress');					
		$lockfile = dirname(__FILE__) . '/../log/import.lock';
		self::write_lock($lockfile, $result);

		$catalog = bbm_api()->get_catalog( $scope );
		if ( ! is_wp_error( $catalog ) && $catalog instanceof SimpleXMLElement ) {

			$result['total'] = count($catalog);
			$result['current'] = 0;

			$disAllowIncrement = false;
			$post = new WC_Post_BiblioMundi($disAllowIncrement);
            $ebook_term_id = 0;
			if ($addEbooksCat) {
                // insert all items to ebook category
                $ebook_term = get_term_by('name', 'ebooks', 'product_cat');
                if (!empty($ebook_term)) {
                    $ebook_term_id = $ebook_term->term_id;
                } else {
                    $ebook_term = wp_insert_term('eBooks', 'product_cat');
                    $ebook_term_id = $ebook_term['term_id'];
                }
            }

            $post->set_ebook_term_id($ebook_term_id);
            $post->set_total_products($result['total']);

			foreach ( $catalog as $product ) {
                if (!$disAllowIncrement) {
                    $resultCurrent = file_get_contents(dirname(__FILE__).'/../log/import.lock');
                    $resultCurrent = json_decode($resultCurrent, true);
                    $result['current'] = ($resultCurrent['current'] >= $result['total']) ? $result['total'] : $resultCurrent['current'] + 1;

                    self::write_lock($lockfile, $result);
                }
			    $post->set_element( $product )->insert();
			}
		}
		if (isset($result['current']) && $result['current'] == $result['total']) {
		    $result['status'] = 'complete';
            self::write_lock($lockfile, $result);
		}

		return $catalog;
	}

    public function ajax_remove() {
        $return = array(
            'error' => true,
            'msg'   => __( 'Invalid operation', 'woocommerce-bibliomundi' ),
        );

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            $return['msg'] = __( "You don't have permission to manage woocommerce.", 'woocommerce-bibliomundi' );
        } else {
            $security = isset( $_POST['security'] ) && $_POST['security'] ? $_POST['security'] : NULL;

            if ( $security && wp_verify_nonce( $security, 'bbm_nonce' ) ) {

                // Get all products that imported through the plugin
                $args = array(
                    'post_type'  => 'product',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => 'id_bibliomundi',
                            'value'   => 1,
                            'compare' => '='
                        )
                    )
                );
                $postIds = [];
                $posts = new WP_Query( $args );
                if ($posts->have_posts()) {
                    while ($posts->have_posts()) {
                        $posts->the_post();
                        $postIds[] = get_the_ID();
                    }
                }

                // Remove all previous imported products
                foreach ($postIds as $id) {
                    //wp_update_post( array( 'ID' => $id, 'post_status' => 'trash' ) );
                    wp_delete_post( $id, true );
                }

                $return = array(
                    'error' => false,
                    'msg'   => esc_html__( 'All products were deleted successfully', 'woocommerce-bibliomundi' ),
                );
            }
        }
        wp_send_json( $return );
    }
}

return new WC_Catalog_BiblioMundi;

endif;