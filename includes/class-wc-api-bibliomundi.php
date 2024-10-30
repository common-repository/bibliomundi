<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_API_BiblioMundi' ) ) :

class WC_API_BiblioMundi {

	private static $ENDPOINT             = 'http://connect.bibliomundi.com/';
	private static $ACCEPTED_SCOPES      = array( 'complete', 'updates' );
	private static $CLIENT_ID_LENGTH     = 40;
	private static $CLIENT_SECRET_LENGTH = 40;
	private static $INSTANCE;

	private $sandbox;
	private $client_id;
	private $client_secret;
	private $credentials;	
	private $catalog;

	private function __construct() {
		$this->sandbox       = get_option( '_bibliomundi_sandbox' );
		$this->client_id     = get_option( '_bibliomundi_client_id' );
		$this->client_secret = get_option( '_bibliomundi_client_secret' );
	}

	public static function get_instance() {
		if ( null == self::$INSTANCE ) {
			self::$INSTANCE = new self;
		}

		return self::$INSTANCE;
	}

	protected function get_url( $uri = null ) {
		return self::$ENDPOINT . $uri;
	}

	protected function sandbox( &$args ) {
		if ( isset( $args['body'] ) ) {
			$args['body']['environment'] = 'yes' == $this->sandbox ? 'sandbox' : 'production';
		}
		return $args;
	}

	public function validate_credentials() {
		$this->credentials = false;
		if ( strlen( $this->client_id ) != self::$CLIENT_ID_LENGTH || strlen( $this->client_secret ) != self::$CLIENT_SECRET_LENGTH ) {
			$error = new WP_Error( 400, __( 'Invalid credentials.', 'woocomerce-bibliomundi' ) );
		} else {
			$args = array(
				'headers' => array(
					'Authorization' => "Basic " . base64_encode( $this->client_id . ":" . $this->client_secret ),
				),
				'body' => array(
					'grant_type' => 'client_credentials',
				)
			);
			$this->sandbox( $args );
			$response = wp_safe_remote_post( $this->get_url( 'token.php' ), $args );
			$error    = is_wp_error( $response );
			if ( ! $error ) {
				$body = self::parse( wp_remote_retrieve_body( $response ) );
				if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
					$error = new WP_Error( $body->error, $body->error_description );
				} else {
					$this->credentials = $body;
					return $body;
				}
			}
		}
		return $error;
	}

	private static function parse( $data ) {
		if ( $data ) {
		    $return = json_decode( $data );
		    if ((json_last_error() == JSON_ERROR_NONE)) {
		        return $return;
            }
			return $data;
		}
		return false;
	}

	public function get_catalog( $scope = 'complete' ) {
		if ( ! function_exists( 'simplexml_load_string' ) ) {
			_doing_it_wrong( 'simplexml_load_string', __( 'You must enable the SimpleXML', 'bibliomundi' ), 'WC-BiblioMundi-' . WC_BiblioMundi::VERSION );
		}

		if ( ! in_array( $scope, self::$ACCEPTED_SCOPES ) ) {
			$this->catalog = new WP_Error( 403, __( 'Invalid scope!', 'woocomerce-bibliomundi' ) );
		} else {
			$this->catalog = $this->validate_credentials();
			if( ! is_wp_error( $this->catalog ) && $this->credentials ) {
				$args = array(
					'body' => array(
						'transaction_time' => time(),
						'client_id'        => $this->client_id,
						'scope'            => $scope,
						'client_secret'    => $this->client_secret,
						'access_token'     => $this->credentials->access_token,
					),
					// 'timeout' => 86400
				);
				$this->sandbox( $args );
                add_filter( 'http_request_timeout', array( $this, 'bm_add_http_request_timeout') );
				$response = wp_safe_remote_post( $this->get_url( 'ebook/list.php' ), $args );
                remove_filter( 'http_request_timeout', array( $this, 'bm_add_http_request_timeout') );
				$this->catalog = is_wp_error( $response );
				if ( ! $this->catalog ) {
					$data = self::parse( wp_remote_retrieve_body( $response ) );
					if ( is_null( $data ) ) {
						$this->catalog = new WP_Error( 400, __( 'Nothing to import.', 'woocommerce-bibliomundi' ) );
					} elseif ( $data->code != 200 ) {
						$this->catalog = new WP_Error( $data->code, __( $data->message, 'woocommerce-bibliomundi' ) );
					} elseif ( isset( $data->message ) && ! empty( $data->message ) ) {						
						$xml = simplexml_load_string( $data->message );
						if ( isset( $xml->Product ) && 0 < sizeof( $xml->Product ) ) {
							$this->catalog = $xml->Product;
						}
					}
				}
			}			
		}
		
		return $this->catalog;
	}

	public function validate_order( $atts ) {
		$return = false;
		if ( is_array( $atts ) && sizeof( $atts ) > 0 ) {
			$return = $this->validate_credentials();
			if ( ! is_wp_error( $return ) && $this->credentials ) {
				$default = array(
					'clientID'      => $this->client_id,
					'access_token'  => $this->credentials->access_token,
				);
				$args = array( 'body' => $default + $atts );
				$this->sandbox( $args );
				$response = wp_safe_remote_post( $this->get_url( 'ebook/validate.php' ), $args );		
				return ! is_wp_error( $response ) && 200 == $response['response']['code'];
			}			
		}
		return $return;
	}

	public function checkout_order( $data ) {
		$return = false;
		if ( is_array( $data ) && sizeof( $data ) ) {
			$return = $this->validate_credentials();
			if ( ! is_wp_error( $return ) && $this->credentials ) {
				$default = array(
					'clientID'     => $this->client_id,
					'access_token' => $this->credentials->access_token,
					'saleDate'     => date( 'Y-m-d H:i:s' ),
				);
				$args = array( 'body' => $default + $data );
				$this->sandbox( $args );
				$response = wp_safe_remote_post( $this->get_url( 'ebook/purchase.php' ), $args );
				return ! is_wp_error( $response ) && 200 == $response['response']['code'];
			}
		}
		return $return;
	}

	public function validate_download( $data ) {
		if ( is_array( $data ) && count( $data ) > 0 ) {
			$return = $this->validate_credentials();
			if ( ! is_wp_error( $return ) && $this->credentials ) {
				$default = array(
					'client_id'        => $this->client_id,
					'access_token'     => $this->credentials->access_token,
					'transaction_time' => time(),
				);
				$args = array( 
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
					),
					'body' => $default + $data 
				);				
				$this->sandbox( $args );

				add_filter( 'http_request_timeout', array( $this, 'bm_add_http_request_timeout') );

				$response = wp_safe_remote_post( $this->get_url( 'ebook/get.php' ), $args );

				remove_filter( 'http_request_timeout', array( $this, 'bm_add_http_request_timeout') );

				if ( ! is_wp_error( $response ) && 200 == $response['response']['code'] ) {
					return self::parse( wp_remote_retrieve_body( $response ) );
				}
			}
		}
		return false;
	}

	public function bm_add_http_request_timeout() {
		return 3600;	// 1h
	}

	public function download( $data ) {
		$response = $this->validate_download( $data );
		if ( $response ) {			
			$msg = !empty($response->message)? $response->message : $response;
			if ( !strpos( $msg, "urn:uuid:" ) ) {
			    header( 'Content-Type: application/epub+zip' );
			    header( 'Content-Disposition: attachment; filename="' . md5( time() ) . '.epub"' );
			} else {
			    header( 'Content-Type: application/vnd.adobe.adept+xml' );
			    header( 'Content-Disposition: attachment; filename="' . md5( time() ) . '.acsm"' );
                $msg = utf8_decode($msg);
			}
			header( 'Content-Transfer-Encoding: binary' );
			header( 'Expires: 0' );
			header( 'Pragma: no-cache' );
			header( 'Content-Length: ' . strlen( $msg ) );
			die( $msg );
		}
		wp_die( __( 'Invalid download!', 'woocomerce-bibliomundi' ) );
	}
}

if ( ! function_exists( 'bbm_api' ) ) {
	function bbm_api() {
		return WC_API_BiblioMundi::get_instance();
	}
}

endif;