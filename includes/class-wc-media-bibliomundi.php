<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Media_BiblioMundi' ) ) :

class WC_Media_BiblioMundi {

	public static function insert( $post_id, $file, $name = false ) {
		$upload_dir = wp_upload_dir();
		
		$file      = esc_url( $file );
		$extension = pathinfo( $file, PATHINFO_EXTENSION );
		$basename  = $name ? $name : pathinfo( $file, PATHINFO_FILENAME );
		$basename  = sanitize_title( $basename ) . '.' . $extension;
		$filename  = $upload_dir['path'] . '/' . $basename;
				
		if ( self::save( $file, $filename ) ) {
			$filetype   = wp_check_filetype( $basename, null );
			$attachment = array(
				'guid'           => $upload_dir['url'] . '/' . $basename, 
				'post_mime_type' => $filetype['type'],
				'post_title'     => $basename,
				'post_content'   => '',
				'post_status'    => 'inherit'
			);			
			$attach_id = wp_insert_attachment( $attachment, $filename, $post_id );			
			require_once( ABSPATH . 'wp-admin/includes/image.php' );			
			$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
			wp_update_attachment_metadata( $attach_id, $attach_data );
			update_post_meta( $post_id, '_thumbnail_id', $attach_id );			
			return $attach_id;
		}
	}

	// http://stackoverflow.com/questions/6476212/save-image-from-url-with-curl-php
	public static function save( $url, $file ) {
		if ( ini_get( 'allow_url_fopen' ) && function_exists( 'file_get_contents' ) && function_exists( 'file_put_contents' ) ) {
			$content = file_get_contents( $url );
			if ( $content ) {
				return file_put_contents( $file, $content );
			}			
		} else {
		    $fp = fopen( $file, 'w+' );
		    $ch = curl_init( $url );
		    curl_setopt( $ch, CURLOPT_FILE, $fp );
		    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		    curl_setopt( $ch, CURLOPT_TIMEOUT, 1000 );
		    curl_setopt( $ch, CURLOPT_USERAGENT, 'Mozilla/5.0' );
		    curl_exec( $ch );
		    curl_close( $ch );
		    fclose( $fp );
		    return true;
		}
		return false;
	}
	
}

endif;