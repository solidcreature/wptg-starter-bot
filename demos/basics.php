<?php 
add_action('tg_commands_hook','tg_start_basics', 50, 3);
add_action('tg_commands_hook','tg_do_basics', 50, 3);


function tg_start_basics($chat_id, $message, $person_id) {
	if ($message != 'Базовые функции') return;
	
	//Задаем контент раздела
	$photo = 'AgACAgIAAxkBAAIJLmFIYNqed4Qoh0iLOSLfnRdpM-dFAAL-tTEb9CRBSiKzcJZ9CwW_AQADAgADeAADIAQ';
	$caption = 'В данном разделе доступны базовые функции бота: отправить ролик, локацию или ссылку на внеший ресурс';
	
	
	//Подготавливаем кнопки для клавиатуры
	$rows = array();
	$rows[] = array('text' => 'Получить информацию о боте', 'callback_data' => 'basic-info');
	$rows[] = array('text' => 'Узнать свой Chat ID', 'callback_data' => 'basic-chatid');
	$rows[] = array('text' => 'Случайное действие', 'callback_data' => 'basic-random');


	//Размещаем каждую кнопку на отдельной строке
	$buttons = array();
	foreach ($rows as $row) {
		$buttons[] = array($row);	
	}
	
	
	//Получаем клавиатуру
	$keyboard = tg_inline_keyboard($buttons);
	
	
	//Посылаем сообщение
	tg_send_photo($chat_id, $photo, $caption, $keyboard);
	exit('ok');
}


function tg_do_basics ($chat_id, $message, $person_id) {
	
	//Проверяем что команда относится к данной функции, иначе завершаем работу
	$message = explode('-',$message);
	if ( $message[0] != 'basic' ) return;
	
	
	//Выполняем нужное действие в зависимости от 2-й части команды
	if ( $message[1] == 'info' ) {
		$text = tg_get_info();
		tg_send($chat_id, $text);
	}

	elseif ( $message[1] == 'chatid' ) {
		$text = 'Ваш Telegram Chat ID: <code>' . $chat_id . '</code>';	
		tg_send($chat_id, $text);
	}

	else {
		$variants = array('photo','audio','location','venue','dice','animation','video','contact');
		shuffle($variants);
		
		$variants[0] = 'photo';
		
		if ( $variants[0] == 'photo' ) {
			$text = '<em>Отправка фотографии и подписи:</em>';
			tg_send($chat_id,$text);
			
			$photos = array();
			$photos[] = array('AgACAgIAAxkBAAIFm2FEKfAfu1u6Ulf7W5viIFuf7AwSAAIUtjEbC7chSvc6HrnEodbTAQADAgADeQADIAQ','Обзор подоходов и инструментов для создания гутенберг-блоков. Смотреть видео: https://youtu.be/DO5rGvdLqy8');
			$photos[] = array('AgACAgIAAxkBAAIFnWFEKvJDrlueBtG7OfsfsELMDNPmAAIXtjEbC7chSsUxAoxCtXvpAQADAgADeQADIAQ', ' Видео с практическими советами и примерами оптимизации скорости загрузки сайта. Смотреть: https://youtu.be/wD0FcLokcZ4');
			$photos[] = array('AgACAgIAAxkBAAIFo2FEMBi0fU26ERgc4atZAlc5wMoKAAIatjEbC7chSho-d9FQkaV2AQADAgADeQADIAQ','Как продавать свои wordpress-темы на ThemeForest. Дмитрий Дьяконов делится практическим опытом. Смотреть видео: https://youtu.be/nrGFnEnRPz4');
			$photos[] = array('AgACAgIAAxkBAAIGcWFEiqmwM5Vwv6ayWhsXCg3fR3GgAAIdtjEbC7chSt_wagPAC-tlAQADAgADeQADIAQ','Отличный обзорный доклад по технологии WebGL, смотреть: https://youtu.be/BTnpB5KPbYg');
			
			$index = random_int(0, count($photos)-1);
			
			tg_send_photo($chat_id, $photos[$index][0],$photos[$index][1]);	
		}

		elseif ( $variants[0] == 'voice' ) {
			$text = '<em>Отправка аудио-сообщения</em>';
			tg_send($chat_id,$text);	
			
			$audio = 'AwACAgIAAxkBAAIFmWFEJT9OHBxcUe7LV_HAnV9rd6_CAALBDwACC7chSt-oQFHWpxfKIAQ';
			tg_send_voice($chat_id, $audio);	
		}
		
		elseif ( $variants[0] == 'location' ) {
			$text = '<em>Отправка локации:</em>';
			tg_send($chat_id,$text);
			
			$latitude = 55.822643; 
			$longitude = 37.327454; 
			tg_send_location($chat_id, $latitude, $longitude, $live_period=3600);
			
			$text = 'Если знаешь, что это за локация — возьми с полки пирожок';
			tg_send($chat_id,$text);
		}
		
		elseif ( $variants[0] == 'venue' ) {
			$text = '<em>Отправка информации о месте стречи</em>';
			tg_send($chat_id,$text);
			
			$latitude = 55.826630;
			$longitude = 37.447800;
			$title = 'Студия ProstoSpace';
			$address = 'Тушинская ул. 11';
			tg_send_venue($chat_id, $latitude, $longitude, $title, $address);	
		}
		
		elseif ( $variants[0] == 'dice' ) {
			$text = '<em>Отправка анимированных эмодзи:</em>';
			tg_send($chat_id,$text);
			
			$emojis = array('🎲', '🎯','🏀');
			shuffle($emojis);
			tg_send_dice($chat_id, $emojis[0]);	
		}
		
		elseif ( $variants[0] == 'animation') {
			$text = '<em>Отправка  gif-анимации:</em>';
			tg_send($chat_id,$text);
			
			$animation = 'CgACAgIAAxkBAAIFd2FDxTzpe7zDw21oNCjSCWfiPJ-fAAJ4DQACVwohSlr5VjvvCUa_IAQ';
			$caption = 'Как делать подобные цветовые схемы — рассказываю в ролике. https://youtu.be/nlUz_kgcpRk';
			tg_send_animation($chat_id, $animation, $caption);
		}
		
		elseif ( $variants[0] == 'video' ) {
			$text = '<em>Отправка видео-ролика:</em>';
			tg_send($chat_id,$text);
			
			$text = 'BAACAgIAAxkBAAIFdGFDw8DGRY_3U_KcFd2m-wABCdChdgACdg0AAlcKIUqx2sqW3AS5LiAE';
			tg_send_video($chat_id, $text);	
		}
		
		elseif ( $variants[0] == 'contact' ) {
			$text = '<em>Отправка контактной информации:</em>';
			tg_send($chat_id,$text);
			
			tg_send_contact($chat_id, '+79779855526', 'Студия', 'Prosto Space');
		}
		
	}



	
	exit('ok');
}	