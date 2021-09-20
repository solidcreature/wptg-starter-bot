<?php
add_action('tg_commands_hook','tg_show_photos_id', 10, 3);

function tg_show_photos_id($chat_id, $message, $person_id) {
	
	//Проверяем что команда относится к данной функции, иначе завершаем работу
	$check = explode('_',$message);
	if ( $check[0] != 'photo' ) return;
	
	$text = __('ID загруженного изображения: ');
	$photo_id = str_replace('photo_','',$message);
	$text .= $photo_id;
	
	tg_send($chat_id, $text);

	exit('ok');
}