<?php 
//Based on umbu_download() function from https://ru.wordpress.org/plugins/upload-media-by-url/

function tg_image_upload($imgurl) { 

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$new_url = media_sideload_image( $imgurl, 0, 'telegram upload', 'src' ); 
	
	if( is_wp_error($new_url) ){
		$result = array('error', $new_url->get_error_message());
	}
	else {
		$result = array('ok', $new_url);
	}
	
	return $result;
}