<?php
//Список поддерживаемых методов телеграма
//getMe
//setMyCommands
//getMyCommands
//sendMessage
//forwardMessage
//sendPhoto
//sendAudio
//sendDocument
//sendVideo
//sendAnimation
//sendVoice
//sendVideoNote
//sendMediaGroup
//sendLocation
//sendVenue
//sendContact
//sendPoll
//sendDice
//sendChatAction

//Редактирование сообщений
//editMessageText
//editMessageCaption
//editMessageMedia
//editMessageReplyMarkup
//stopPoll
//deleteMessage




//Основной запрос к АПИ телеграма, можно использовать любой метод, даже если для него нет отдельной функции
//Все функции ниже используют в качестве основы tg_request()
function tg_request($method, $data = array()) {
	$url = 'https://api.telegram.org/bot' . TG_BOT_TOKEN .  '/' . $method;
	$args = array(
		'body'   => $data
	);

    $raw_out = wp_remote_post( $url, $args );
	$out = json_decode($raw_out['body']);
	
	update_field('from_bot',print_r($out,true),1);
	
    return $out; 
}


//Функция  получает информацию о боте и возвращает ее в текстовом виде
//https://core.telegram.org/bots/api#getme
function tg_get_info() {
	$out = tg_request('getMe');
	$out = $out -> result;
	
	$text = '<b>Информация о боте:</b>' . PHP_EOL;
	$text .= 'ID: ' . $out -> id . PHP_EOL;
	$text .= 'Name: ' . $out -> first_name . PHP_EOL;
	$text .= 'Username: @' . $out -> username . PHP_EOL;
	
	return($text);
}


//Функция  получает информацию о настройках вебхука
//https://core.telegram.org/bots/api#getwebhookinfo
function tg_get_webhook_info() {
	$out = tg_request('getWebhookInfo');
	$out = $out -> result;
	
	return($out);
}


//Отправка простого сообщения по умолчанию без клавиатуры
//https://core.telegram.org/bots/api#sendmessage
function tg_send($chat_id, $text, $keyboard='', $parse_mode='HTML', $disable_preview = false, $silent = false, $reply = '' ) { 
	$text = tg_clear_tags($text); 
	$data = array(
		'chat_id'      => $chat_id,
		'text'     => $text,
		'parse_mode' => $parse_mode,
		'disable_web_page_preview' => $disable_preview,
		'disable_notification' => $silent,
		'reply_to_message_id' => $reply
	);
	
	if (is_array($keyboard)) { 
		$replyMarkup = json_encode($keyboard);
		$data['reply_markup'] = $replyMarkup; 
	}
	
	
	$out = tg_request('sendMessage', $data);
	return $out;
}
     

//Отправка изображения
//https://core.telegram.org/bots/api#sendphoto   
function tg_send_photo($chat_id, $photo, $caption='', $keyboard='', $parse_mode='HTML', $disable_preview = false, $silent = false, $reply = '') { 
		
	$data = array(
		'chat_id'      => $chat_id,
		'photo'     => $photo,
		'caption' => $caption,
		'parse_mode' => $parse_mode,
		'disable_web_page_preview' => $disable_preview,
		'disable_notification' => $silent,
		'reply_to_message_id' => $reply					
	);

	if (is_array($keyboard)) { 
		$replyMarkup = json_encode($keyboard);
		$data['reply_markup'] = $replyMarkup; 
	}

	$out = tg_request('sendPhoto', $data);
	return $out;	
}	

//Отправка аудио-файла   
//https://core.telegram.org/bots/api#sendaudio
function tg_send_audio($chat_id, $audio, $caption='', $keyboard='', $duration='', $performer='', $title='', $thumb='', $parse_mode = 'HTML', $silent = false, $reply = '') { 
		
        $data = array(
            'chat_id'      => $chat_id,
            'audio'     => $audio,
			'caption' => $caption,
			'duration' => $duration,
			'performer' => $performer,
			'title' => $title,
			'thumb' => $thumb,
			'parse_mode' => $parse_mode,
			'disable_notification' => $silent,
			'reply_to_message_id' => $reply					
        );
	
		if (is_array($keyboard)) { 
			$replyMarkup = json_encode($keyboard);
			$data['reply_markup'] = $replyMarkup; 
		}
	
        $out = tg_request('sendAudio', $data);
        return $out;	
}	



//Отправка документа   
//https://core.telegram.org/bots/api#senddocument
function tg_send_document($chat_id, $document, $caption='', $keyboard='', $thumb='', $parse_mode = 'HTML', $silent = false, $reply = '') { 
		
		if ($thumb) {
			$thumb = get_file_multipart($thumb);
		}
	
        $data = array(
            'chat_id' => $chat_id,
            'document' => $document,
			'caption' => $caption,
			'thumb' => $thumb,
			'parse_mode' => $parse_mode,
			'disable_notification' => $silent,
			'reply_to_message_id' => $reply					
        );
	
		if (is_array($keyboard)) { 
			$replyMarkup = json_encode($keyboard);
			$data['reply_markup'] = $replyMarkup; 
		}
	
        $out = tg_request('sendDocument', $data);
        return $out;	
}	



//Отправка видео-файла
//https://core.telegram.org/bots/api#sendvideo
function tg_send_video($chat_id, $video, $caption='', $keyboard='', $thumb='', $duration='', $width=320, $height=240, $streaming = false, $parse_mode = 'HTML', $silent = false, $reply = '') { 
	
		if ($thumb) {
			$thumb = get_file_multipart($thumb);
		}
	
        $data = array(
            'chat_id' => $chat_id,
            'video'   => $video,
            'caption' => $caption,
			'thumb' => $thumb,
			'duration' => $duration,
			'width' => $width,
			'height'=> $height,
			'supports_streaming' => $streaming,
			'parse_mode' => $parse_mode,
			'disable_notification' => $silent,
			'reply_to_message_id' => $reply					
        );
	
		if (is_array($keyboard)) { 
			$replyMarkup = json_encode($keyboard);
			$data['reply_markup'] = $replyMarkup; 
		}
	
        $out = tg_request('sendVideo', $data);
        return $out;	
}


//Отправка анимации
//https://core.telegram.org/bots/api#sendanimation
function tg_send_animation($chat_id, $animation, $caption='', $keyboard='', $thumb='', $duration='', $width=320, $height=240, $parse_mode = 'HTML', $silent = false, $reply = '') { 
	
		if ($thumb) {
			$thumb = get_file_multipart($thumb);
		}
	
        $data = array(
            'chat_id'      => $chat_id,
            'animation'     => $animation,
            'caption' => $caption,
			'thumb' => $thumb,
			'duration' => $duration,
			'width' => $width,
			'height' => $height,
			'parse_mode' => $parse_mode,
			'disable_notification' => $silent,
			'reply_to_message_id' => $reply					
        );
	
		if (is_array($keyboard)) { 
			$replyMarkup = json_encode($keyboard);
			$data['reply_markup'] = $replyMarkup; 
		}
	
        $out = tg_request('sendAnimation', $data);
        return $out;	
}


//Отправка аудио-сообщения
//https://core.telegram.org/bots/api#sendvoice
function tg_send_voice($chat_id, $voice, $caption='', $keyboard='', $duration='', $parse_mode = 'HTML', $silent = false, $reply = '') { 
		
        $data = array(
            'chat_id'      => $chat_id,
            'voice'     => $voice,
			'caption' => $caption,
			'duration' => $duration,
			'parse_mode' => $parse_mode,
			'disable_notification' => $silent,
			'reply_to_message_id' => $reply					
        );
	
		if (is_array($keyboard)) { 
			$replyMarkup = json_encode($keyboard);
			$data['reply_markup'] = $replyMarkup; 
		}
	
        $out = tg_request('sendVoice', $data);
        return $out;	
}	


//Отправка видео-сообщения
//https://core.telegram.org/bots/api#sendvideonote
function tg_send_videonote($chat_id, $video_note, $keyboard='', $thumb='',$duration='', $length='', $silent = false, $reply = '') { 
		
		if ($thumb) {
			$thumb = get_file_multipart($thumb);
		}
	
        $data = array(
            'chat_id'      => $chat_id,
            'video_note'     => $video_note,
			'duration' => $duration,
			'length' => $length,
			'thumb' => $thumb,
			'disable_notification' => $silent,
			'reply_to_message_id' => $reply					
        );
	
		if (is_array($keyboard)) { 
			$replyMarkup = json_encode($keyboard);
			$data['reply_markup'] = $replyMarkup; 
		}
	
        $out = tg_request('sendVideoNote', $data);
        return $out;	
}	


//Отправка медиа-группы видео / фото
//https://core.telegram.org/bots/api#sendmediagroup
//$media = array();
//$media[] = array('type'=>'photo', 'media' => 'BAACAgIAAxkBAAIFeV67Jvuu5eG3qOx6FKehrZzydgqfAAIcCAAC3BvYSY8iKIiB5un2GQQ', 'caption' => '', 'parse_mode' => '');
//2-10 items
function tg_send_mediagroup($chat_id, $media, $silent = false, $reply = '') {

	$data = array(
		'chat_id'      => $chat_id,
		'media'     =>  json_encode($media),
		'disable_notification' => $silent,
		'reply_to_message_id' => $reply					
	);

	$out = tg_request('sendMediaGroup', $data);
	return $out;	
}


//Отправка местоположения
//https://core.telegram.org/bots/api#sendlocation
function tg_send_location($chat_id, $latitude, $longitude, $live_period=3600, $keyboard='', $silent = false, $reply = '') { 
        $data = array(
            'chat_id'      => $chat_id,
			'latitude' => $latitude,
			'longitude' => $longitude,
			'live_period' => $live_period,
			'disable_notification' => $silent,
			'reply_to_message_id' => $reply
        );
	
		if (is_array($keyboard)) { 
			$replyMarkup = json_encode($keyboard);
			$data['reply_markup'] = $replyMarkup; 
		}
	
        $out = tg_request('sendLocation', $data);
        return $out;	
}


//Отправка места на карте с подписью
//https://core.telegram.org/bots/api#sendvenue
function tg_send_venue($chat_id, $latitude, $longitude, $title, $address, $foursquare_id='', $foursquare_type='', $keyboard='', $silent = false, $reply = '') {
        $data = array(
            'chat_id'      => $chat_id,
			'latitude' => $latitude,
			'longitude' => $longitude,
			'title' => $title,
			'address' => $address,
			'foursquare_id' => $foursquare_id,
			'foursquare_type' => $foursquare_type,
			'disable_notification' => $silent,
			'reply_to_message_id' => $reply
        );
	
		if (is_array($keyboard)) { 
			$replyMarkup = json_encode($keyboard);
			$data['reply_markup'] = $replyMarkup; 
		}
	
        $out = tg_request('sendVenue', $data);
        return $out;		
}


//Отправка контактов человека
//https://core.telegram.org/bots/api#sendcontact
function tg_send_contact($chat_id, $phone, $f_name, $l_name='', $vcard='', $keyboard='', $silent=false, $reply='') {
	$data = array(
		'chat_id'      => $chat_id,
		'phone_number' => $phone,
		'first_name' => $f_name,
		'last_name' => $l_name,
		'vcard' => $vcard,
		'disable_notification' => $silent,
		'reply_to_message_id' => $reply
	);

	if (is_array($keyboard)) { 
		$replyMarkup = json_encode($keyboard);
		$data['reply_markup'] = $replyMarkup; 
	}

	$out = tg_request('sendContact', $data);
	return $out;		
}


//Отправка опросника
//https://core.telegram.org/bots/api#sendpoll
function tg_send_poll($chat_id, $question, $options, $keyboard='', $anonymous=false, $type='regular', $multiple=false, $correct='', $explanation='', $parse_mode='HTML', $open_period='', $close_date='', $is_closed=false, $silent=false, $reply='') {
	
	$data = array(
		'chat_id'      => $chat_id,
		'question' => $question,
		'options' => json_encode($options),
		'is_anonymous' => $anonymous,
		'type' => $type,
		'allows_multiple_answers' => $multiple,
		'correct_option_id' => $correct,
		'explanation' => $explanation,
		'explanation_parse_mode' => $parse_mode,
		'open_period' => $open_period,
		'close_date' => $close_date,
		'is_closed' => $is_closed,
		'disable_notification' => $silent,
		'reply_to_message_id' => $reply
	);

	if (is_array($keyboard)) { 
		$replyMarkup = json_encode($keyboard);
		$data['reply_markup'] = $replyMarkup; 
	}

	$out = tg_request('sendPoll', $data);
	return $out;	
}



//Отправка Dice
//https://core.telegram.org/bots/api#senddice
//“🎲”, “🎯”, or “🏀”
function tg_send_dice($chat_id, $emoji, $keyboard='', $silent='', $reply='') { 
        $data = array(
            'chat_id'      => $chat_id,
            'emoji'     => $emoji,
			'disable_notification' => $silent,
			'reply_to_message_id' => $reply
        );
	
		if (is_array($keyboard)) { 
			$replyMarkup = json_encode($keyboard);
			$data['reply_markup'] = $replyMarkup; 
		}
	
        $out = tg_request('sendDice', $data);
        return $out;	
}



//Отправка Чат Экшенс
//https://core.telegram.org/bots/api#sendchataction
//Actions: typing, upload_photo, record_video, upload_video, record_audio, upload_audio, upload_document, find_location, record_video_note, upload_video_note
function tg_send_action($chat_id, $action) { 
        $data = array(
            'chat_id'      => $chat_id,
            'action'     => $action
        );
        $out = tg_request('sendChatAction', $data);
        return $out;	
}




//Назначение специальных команд боту
//https://core.telegram.org/bots/api#setmycommands
//array of arrays ('command'=> , 'description' => )
function tg_set_commands($commands) {
        $data = array(
            'commands'      => json_encode($commands)
        );
        $out = tg_request('setMyCommands', $data);
        return $out;		
}

//Вывести список актуальных команды бота
//https://core.telegram.org/bots/api#getmycommands
function tg_get_commands($chat_id='') {
        $out = tg_request('getMyCommands');

		$out = $out['body'];
		$out = json_decode($out);
		$out = $out->result;
	
		$text = '<b>Доступные команды:</b>' . PHP_EOL;
		
		foreach ($out as $command) {
			$text .= '/' . $command -> command . PHP_EOL;
			$text .= '<em>' . $command -> description . '</em>' . PHP_EOL . PHP_EOL;
		}
	
		if ($chat_id) {
			tg_send($chat_id, $text);		
		} else {
	        return $text;		
		}	
}



 
 
//Изменить сообщение
//Please note, that it is currently only possible to edit messages without reply_markup or with inline keyboards.
function tg_change_message($chat_id, $message_id, $text, $keyboard='', $parse_mode='HTML') {   
    	$text = tg_clear_tags($text); 
    	$replyMarkup = json_encode($keyboard);
    	
        $data = array(
            'chat_id'      => $chat_id,
			'message_id' => $message_id,
            'text'     => $text,
            'parse_mode' => $parse_mode,
            'reply_markup' => $replyMarkup
        );

        $out = tg_request('editMessageText', $data);
        return $out;
}  

//Изменить подпись сообщения
function tg_change_caption($chat_id, $message_id, $caption, $keyboard='', $parse_mode='HTML') {  
    	$caption = tg_clear_tags($caption); 
    	$replyMarkup = json_encode($keyboard);
    	
        $data = array(
            'chat_id'      => $chat_id,
			'message_id' => $message_id,
            'caption'     => $caption,
            'parse_mode' => $parse_mode,
            'reply_markup' => $replyMarkup
        );

        $out = tg_request('editMessageCaption', $data);
        return $out;
}	

//Изменить медиа-файл сообщения
function tg_change_media($chat_id, $message_id, $media_object, $keyboard='') {  
 		$media = json_encode($media_object);
    	$replyMarkup = json_encode($keyboard);
    	
        $data = array(
            'chat_id'      => $chat_id,
			'message_id' => $message_id,
            'media'     => $media,
            'reply_markup' => $replyMarkup
        );

        $out = tg_request('editMessageMedia', $data);
        return $out;
}	

//Изменить инлайн-клавиатуру сообщения
function tg_change_keyboard($chat_id, $message_id, $keyboard) {  
    	$replyMarkup = json_encode($keyboard);
    	
        $data = array(
            'chat_id'      => $chat_id,
			'message_id' => $message_id,
            'reply_markup' => $replyMarkup
        );

        $out = tg_request('editMessageReplyMarkup', $data);
        return $out;
}

//Остановить голосование
function tg_stop_poll($chat_id, $message_id, $keyboard='') {
    	$replyMarkup = json_encode($keyboard);
    	
        $data = array(
            'chat_id'      => $chat_id,
			'message_id' => $message_id,
            'reply_markup' => $replyMarkup
        );

        $out = tg_request('stopPoll', $data);
        return $out;		
}


//Удалить сообщение
function tg_delete_message($chat_id, $message_id) {    	
        $data = array(
            'chat_id'      => $chat_id,
			'message_id' => $message_id
        );

        $out = tg_request('deleteMessage', $data);
        return $out;		
}

