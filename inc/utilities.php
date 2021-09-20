<?php

//Получить ID участника по идентификатору тг-чата, если нет, создать нового
function tg_get_person_id($chat_id, $name) {
	$args = array(
		'post_type' => 'tg_person',
		'meta_key' => 'chat_id',
		'meta_value' => $chat_id
	);
			
	$query = new WP_Query($args);
	
	if ( $query->have_posts() )	{
		while ( $query->have_posts() ) : $query->the_post();
			$person_id = get_the_ID();
		endwhile; 
		
		return $person_id;
		
	} else {
		$post_data = array(
		'post_title'    => $name,
		'post_content' => '',
		'post_status'   => 'publish',
		'post_author'   => 1,
		'post_type' => 'tg_person'
		);

		// Вставляем запись в базу данных
		$person_id = wp_insert_post( $post_data );
		
		update_field('chat_id', $chat_id, $person_id);
		update_field('reg_step', 0, $person_id);
		
		return $person_id;
	}
	
	wp_reset_postdata();
}

//Очистить текст от лишних html-тегов, чтобы подготовить текст к отправки
function tg_clear_tags($text) {
	$clear = strip_tags($text, '<b><strong><i><em><u><ins><s><strike><del><a><code><pre>');
	$clear = str_replace('&nbsp;', '', $clear);
	return $clear;
}  

//Передать картинку в формате multipart/form-data 
function get_file_multipart($thumb) {
	//Временная заглушка, пока не разобрался с мультипартом
	return $thumb;
}