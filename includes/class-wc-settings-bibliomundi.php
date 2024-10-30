<?php
/**
 * BiblioMundi Settings
 *
 * @author   BiblioMundi
 * @category Admin
 * @package  BiblioMundi/Admin
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Settings_BiblioMundi' ) ) :
	
class WC_Settings_BiblioMundi extends WC_Settings_Page {

	private $api;

	public function __construct() {
		$this->id    = 'bibliomundi';
		$this->label = __( 'BiblioMundi', 'woocommerce-bibliomundi' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );

		add_action( 'woocommerce_admin_field_bbm_buttons', array( $this, 'render_buttons' ) );
	}

	public function get_settings() {
		$settings = apply_filters( 'woocommerce_' . $this->id . '_settings', array(
			array( 
				'type'  => 'title', 
				'id'    => $this->id . '_options_credentials', 
				'title' => __( 'Credentials', 'woocommerce-bibliomundi' ),
			),
			array(
				'type'    => 'checkbox',
				'id'      => '_bibliomundi_sandbox',
				'name'    => __( 'Sandbox', 'woocommerce-bibliomundi' ),
				'default' => 'no',
			),
			array( 
				'type'              => 'text',
				'id'                => '_bibliomundi_client_id',
				'name'              => __( 'Client ID', 'woocommerce-bibliomundi' ),
				'css'               => 'min-width:340px;',
				'custom_attributes' => array(
					'maxlength' => 40,
				),
			),
			array( 
				'type'              => 'text',
				'id'                => '_bibliomundi_client_secret',
				'name'              => __( 'Client Secret', 'woocommerce-bibliomundi' ),
				'css'               => 'min-width:340px;',
				'custom_attributes' => array(
					'maxlength' => 40,
				),
			),
            array(
                'type'    => 'checkbox',
                'id'      => '_bibliomundi_ebook_cat',
                'name'    => __( 'Add ebook category', 'woocommerce-bibliomundi' ),
                'default' => 'no',
            ),
			array( 
				'type' => 'sectionend', 
				'id'   => $this->id . '_recipient_options_credentials' ,
			),
			array(
				'title'    => __( 'Actions', 'woocommerce-bibliomundi' ),
				'id'       => 'bbm_buttons',
				'type'     => 'bbm_buttons',
			),
			array( 
				'type' => 'sectionend', 
				'id'   => $this->id . '_recipient_options_import',
			),
		) );

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings );
	}

	public function output() {
		WC_Admin_Settings::output_fields( $this->get_settings() );
	}

	public function save() {
		WC_Admin_Settings::save_fields( $this->get_settings() );
		
		$response = bbm_api()->validate_credentials();

		if( is_wp_error( $response ) ) {
			delete_option( '_bibliomundi_client_id' );
			delete_option( '_bibliomundi_client_secret' );
			WC_Admin_Settings::add_error( $response->get_error_message() );
		}
	}

	private static function get_taxonomies() {
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		if( ! is_wp_error( $taxonomies ) ) {
			$options[]  = __( '-- select --', 'woocommerce-bibliomundi' );
			foreach( $taxonomies as $name => $tax ) {
				$options[$name] = $tax->labels->singular_name;
			}
		}
		return apply_filters( 'woocomerce_settings_bibliomundi_taxonomies', $options );
	}

	public function render_buttons( $field ) { ?>
	<table class="form-table">
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label><?php echo wp_kses_post( $field['title'] ); ?></label>
			</th>
			<td class="forminp forminp-text">
				<fieldset>
					<?php wp_nonce_field( 'bbm_nonce', 'bbm-nonce' ); ?>
					<a href="javascript:;" class="button bibliomundi-button complete"><?php _e( 'Import', 'woocommerce-bibliomundi' ); ?></a>
					<a href="javascript:;" class="button bibliomundi-button updates"><?php _e( 'Update', 'woocommerce-bibliomundi' ); ?></a>
                    <a href="javascript:;" class="button bibliomundi-button remove"><?php _e( 'Remove all ebooks', 'woocommerce-bibliomundi' ); ?></a>
					<div class="bibliomundi-alert"></div>
				</fieldset>
			</td>
		</tr>
	</table>
	<?php }

}

return new WC_Settings_BiblioMundi;

endif;