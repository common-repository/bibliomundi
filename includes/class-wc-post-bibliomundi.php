<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Post_BiblioMundi' ) ) :
	
class WC_Post_BiblioMundi extends WC_Base_BiblioMundi {
	
	private static $ID_TYPE_ISBN      = 15;
	private static $VISIBILITY_STATUS = 20;
	private static $INSTANCE;

	private $post_metas;
	private $post_data;
	private $product_attributes;
	private $disAllowIncrement;
	private $visible_attributes = array('language', 'extent', 'publishers', 'epub_technical_protection');
    private $attributes_name = array('epub_technical_protection' => 'DRM');
    private $ebook_term_id, $total_products;

	public static function get_instance() {
		if ( is_null( self::$INSTANCE ) ) {
			self::$INSTANCE = new self;
		}
		return self::$INSTANCE;
	}

	public function __construct(&$disAllowIncrement) {
	    parent::__construct();
	    $this->disAllowIncrement = &$disAllowIncrement;
	}


	protected function exists( $id = null ) {
		if ( ! $id && array_key_exists( 'id_ebook', $this->post_metas ) ) {
			$id = $this->post_metas['id_ebook'];
		}

		if ( $id ) {
			return $this->db->get_var( $this->db->prepare( "SELECT post_id FROM {$this->db->postmeta} WHERE meta_key = %s AND meta_value = %d LIMIT 1", 'id_ebook', $id ) );
		}
	}

	protected function get_post_data() {
		$this->post_data = array();
		
		if ( $this->get_element() ) {
			if(isset($this->element->DescriptiveDetail->TitleDetail->TitleElement->TitleText))
				$title = strval($this->element->DescriptiveDetail->TitleDetail->TitleElement->TitleText);
			else
				$title = strval($this->element->DescriptiveDetail->TitleDetail->TitleElement->TitlePrefix . ' ' . $this->element->DescriptiveDetail->TitleDetail->TitleElement->TitleWithoutPrefix);

			$this->post_data = array(
				'post_type'    => 'product',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_content' => (string) $this->element->CollateralDetail->TextContent->Text,
			);
		}

		return apply_filters( 'woocommerce_post_data_bibliomundi', $this->post_data );
	}

	protected function get_post_metas() {
		$this->post_metas = array();

		if( $this->get_element() ) {

			$prices = array();
			if(is_object($this->element->ProductSupply->SupplyDetail->Price))//is object
			{
				foreach ($this->element->ProductSupply->SupplyDetail->Price as $xmlPrice) 
				{
					$prices[strval($xmlPrice->CurrencyCode)] = strval($xmlPrice->PriceAmount);
				}
			}
			else
			{
				$prices[strval($this->element->ProductSupply->SupplyDetail->Price->CurrencyCode)] = strval($this->element->ProductSupply->SupplyDetail->Price->PriceAmount);
			}

			$currency = get_option('woocommerce_currency');
			if(in_array($currency, array_keys($prices))){
			    $price = (float)$prices[$currency];
			    $iso_code = $currency;
			}else{
			    $price = (float)$prices['BRL'];
			    $iso_code = 'BRL';
			}

			// $price = (float) $this->element->ProductSupply->SupplyDetail->Price->PriceAmount;
			$visibility = (int) $this->element->ProductSupply->SupplyDetail->ProductAvailability;

			$url_file = 'http://www.bibliomundi.com/ebook.epub';
			$downloadable_files[ md5( $url_file ) ] = array(
				'name' => 'Bibliomundi',
				'file' => $url_file,
			);

            switch ((string)$this->element->DescriptiveDetail->ePubTechnicalProtection) {
                case '01' :
                    $epub_technical_protection = 'Social DRM';
                    break;
                case '02' :
                    $epub_technical_protection = 'Adobe DRM';
                    break;
                default:
                    $epub_technical_protection = 'No DRM';

            }
			$this->post_metas = array(
				'notification_type'   => (int) $this->element->NotificationType,
				'id_bibliomundi'      => (int) $this->element->ProductIdentifier->ProductIDType,
				'id_ebook'            => (int) $this->element->ProductIdentifier->IDValue,
				'subtitle'            => (string) $this->element->DescriptiveDetail->TitleDetail->TitleElement->Subtitle,
				'edition_number'      => (string) $this->element->DescriptiveDetail->EditionNumber,
				'iso_code'			  => $iso_code,
				'_visibility'         => self::$VISIBILITY_STATUS == $visibility ? 'visible' : 'hidden',
				'_manage_stock'       => 'no',
				'_stock_status'       => 'instock',
				'_regular_price'      => $price,
				'_price'              => $price,
				'publishers'          => (string) $this->element->PublishingDetail->Imprint->ImprintName,
				'_virtual'            => 'yes',
				'_downloadable'       => 'yes',
				'_downloadable_files' => $downloadable_files,
				'currency'			  => $iso_code,
                'contributor'         => (string)$this->element->DescriptiveDetail->Contributor->PersonName,
                'language'            => (string)$this->element->DescriptiveDetail->Language->LanguageCode,
                'extent'              => (int)$this->element->DescriptiveDetail->Extent->ExtentValue,
                'subject'             => (string)$this->element->DescriptiveDetail->Subject->MainSubject,
                'epub_technical_protection'              => $epub_technical_protection,
			);

			if ( self::$ID_TYPE_ISBN === ( int ) $this->element->ProductIdentifier[1]->ProductIDType ) {
				$this->post_metas['isbn'] = (string) $this->element->ProductIdentifier[1]->IDValue;
			}

			$this->product_attributes = array(
				'currency'			  => $this->post_metas['currency'],
				'edition_number'      => $this->post_metas['edition_number'],
				'id_bibliomundi'      => $this->post_metas['id_bibliomundi'],
				'id_ebook'            => $this->post_metas['id_ebook'],
				'isbn'            	  => $this->post_metas['isbn'],
                'contributor'         => $this->post_metas['contributor'],
				'iso_code'			  => $this->post_metas['iso_code'],
				'notification_type'   => $this->post_metas['notification_type'],
				'publishers'          => $this->post_metas['publishers'],
				'subtitle'            => $this->post_metas['subtitle'],
				'language'            => $this->post_metas['language'],
				'extent'            => $this->post_metas['extent'],
				'subject'            => $this->post_metas['extent'],
				'epub_technical_protection'            => $this->post_metas['epub_technical_protection'],
			);
		}

		return apply_filters( 'woocommerce_post_metas_bibliomundi', $this->post_metas );
	}

	public function can_insert() {
		return $this->get_element() && self::$VISIBILITY_STATUS == $this->element->ProductSupply->SupplyDetail->ProductAvailability;
	}

	public function insert() {
		$this->get_post_data();
		$this->get_post_metas();
		$insert  = $this->can_insert();
		$post_id = $this->exists();
		if ( ! $insert && $post_id ) {
			wp_update_post( array( 'ID' => $post_id, 'post_status' => 'trash' ) );
			$this->disAllowIncrement = false;
		} elseif ( $insert && ! empty( $this->post_data ) && ! empty( $this->post_metas ) ) {
			if ( $post_id ) {
				$this->post_data['ID'] = $post_id;
				switch( $this->post_metas['notification_type'] ) {
					case WC_Notification_Type_BiblioMundi::UPDATE:
						wp_update_post( $this->post_data );
						break;
					case WC_Notification_Type_BiblioMundi::DELETE:
						$insert = false;
					    $this->disAllowIncrement = false;
						$this->post_data['post_status'] = 'trash';
						wp_update_post( $this->post_data );
						break;
				}				
			} else {
				$post_id = wp_insert_post( $this->post_data, true );
				
			}

			if ( $insert && $post_id ) {
                $this->disAllowIncrement = true;
				$this->insert_metas( $post_id );
				$this->insert_thumbnail( $post_id );
				$this->insert_categories( $post_id );
			}
			return $post_id;
		}
		return false;
	}

	protected function insert_metas( $post_id ) {
		if ( $this->post_metas ) {
			foreach( $this->post_metas as $key => $value ) {
				update_post_meta( $post_id, $key, $value );
			}
		}

		if ( $this->product_attributes ) {

			$product_atts = array();

		   	// Loop through the attributes array
		   	foreach ($this->product_attributes as $name => $value) {
		   	    $is_visible = 0;
		   	    if (in_array(htmlspecialchars( stripslashes( $name ) ), $this->visible_attributes)) {
                    $is_visible = 1;
                }
                $name = htmlspecialchars( stripslashes( $name ) );
                $name = !empty($this->attributes_name[$name]) ? $this->attributes_name[$name] : $name;
		       	$product_atts[] = array (
		           	'name' => $name, // set attribute name
		           	'value' => $value, // set attribute value
		           	'position' => 1,
		           	'is_visible' => $is_visible,
		           	'is_variation' => 1,
		           	'is_taxonomy' => 0
		       	);
		   	}

		   	// Now update the post with its new attributes
		   	update_post_meta($post_id, '_product_attributes', $product_atts);
		}
	}

	protected function insert_thumbnail( $post_id ) {
		if ( $this->element && $this->post_data ) {
			$file = (string) $this->element->CollateralDetail->SupportingResource->ResourceVersion->ResourceLink;
			//return WC_Media_BiblioMundi::insert( $post_id, $file, $this->post_data['post_title'] );

			$cmd = "php " . BBL_INCLUDE_DIR . "downloadImage.php " . $post_id . " \"" . $this->post_data['post_title'] . "\" \"" . $file . "\" ". $this->total_products ." &";
			if (substr(php_uname(), 0, 7) == "Windows") {
			    pclose(popen("start ". $cmd, "r"));
			} else {
			    pclose(popen($cmd, "r"));
			}
		}
	}

	protected function insert_categories( $post_id ) {
		if ( $this->element ) {
			$subject = $this->element->DescriptiveDetail->Subject;
			foreach( $subject as $s ) {
				$identifier = (string) $s->SubjectSchemeIdentifier;
				$code       = (string) $s->SubjectCode;
				WC_Category_BiblioMundi::add_relationship( $post_id, $code, $identifier, 'product_cat', $this->ebook_term_id);
			}
		}
	}

	public function set_ebook_term_id($term_id = 0)
    {
        $this->ebook_term_id = $term_id;
    }

    public function set_total_products($total)
    {
        $this->total_products = $total;
    }
}

endif;