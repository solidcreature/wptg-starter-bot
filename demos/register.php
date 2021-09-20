<?php 
add_action('tg_commands_hook','tg_start_register', 50, 3);
add_action('tg_status_commands_hook','tg_do_register', 50, 4);


function tg_start_register($chat_id, $message, $person_id) {
	if ($message != 'Регистрация') return;
	
	
	//Задаем контент раздела
	$photo = 'AgACAgIAAxkBAAIJMGFIYPy7IQWAsi0077gHVPmQDYOgAAIBtjEb9CRBSshYZDYd64VlAQADAgADeAADIAQ';
	$caption = 'Чтобы приступить нажмите кнопку <b>Начать регистрацию</b> или команду /startreg';
	
	
	//Подготавливаем кнопки для клавиатуры
	$rows = array();
	$rows[] = array('Начать регистрацию');
	$rows[] = array('Отменить регистрацию');


	//Получаем клавиатуру
	$keyboard = tg_get_keyboard($rows);
	
	
	//Меняем статус пользователя на register
	update_field('tg_status','register',$person_id);
	update_field('tg_count',0,$person_id);
	
	
	//Посылаем сообщение
	tg_send_photo($chat_id, $photo, $caption, $keyboard);
	exit('ok');
}


function tg_do_register($chat_id, $message, $person_id, $person_status) {
	if ($person_status != 'register') return;
	
	//Задаем все шаги регистрации
	$register_steps = array();
	$register_steps[] = ['tg_name','Введите ваше имя'];
	$register_steps[] = ['tg_city','Укажите город'];
	$register_steps[] = ['tg_job','Укажите должность'];
	$register_steps[] = ['tg_skills','Перечислите ваши навыки через запятую'];
	$register_steps[] = ['tg_email','Напишите адрес эл. почты'];
	$register_steps[] = ['tg_website','Укажите веб-сайт'];
	$register_steps[] = ['tg_photo','Загрузите фотографию'];
		
	
	
	
	//Отмена тестирования
	if ($message == 'Отменить регистрацию' or $message == 'Вернуться в меню' ) {
		update_field('tg_status','',$person_id);
		update_field('tg_result',0,$person_id);
		update_field('tg_count',0,$person_id);
		
		if ($message == 'Отменить регистрацию') {
			$text = '<em>Вы отменили регистрацию</em>';
			$keyboard = tg_default_keyboard();
			tg_send($chat_id, $text, $keyboard);
			exit('ok');
		}
		
		else {
			tg_do_greetings($chat_id, '/start', $person_id);
		}
	}
	
	
	//Начало тестирования
	if ($message == 'Начать регистрацию' or $message == '/startreg') {
		
		//Отправляем статусное сообщение и обновляем клавиатуру
		$text = __('<em>Вы приступили к регистрации</em>');
		$rows = array();
		$rows[] = array('Отменить регистрацию');
		$keyboard = tg_get_keyboard($rows);
		tg_send($chat_id,$text,$keyboard);
		
		
		//Выводим первую задачу
		$text = '<b>' . $register_steps[0][1] . '</b>';
		
		//Обновляем текущий счетчик пользователя
		update_field('tg_count',1,$person_id);
		
		//Отправляем собранный вопрос пользователю
		tg_send($chat_id,$text);
		exit('ok');
	}
	
	
	$tg_count = (int)get_field('tg_count', $person_id);
	$tg_field = $register_steps[$tg_count-1][0];
	$steps_count = count($register_steps);
	

	if ($tg_count > 0 and $tg_count < $steps_count) {
		
		//Проверяем что мы получили в качестве ответа
		if( $tg_field == 'tg_email' ) {
			$is_email = true;
			if ( strpos($message, '@') === false ) { $is_email = false; }
			if ( strpos($message, '.') === false ) { $is_email = false; }
			
			if (!$is_email) {
				$text = '<em>Вы ввели некорректный e-mail. Повторите ввод.</em>';
				tg_send($chat_id,$text);
				exit('ok');
			}
		}
		
		if( $tg_field == 'tg_photo' ) {
			$message_check = explode('_',$message);

			if ($message_check[0] != 'photo') {
				$text = '<em>Пожалуйста загрузите фотографию</em>';
				tg_send($chat_id,$text);
				exit('ok');
			} else {
				$photo_obj = tg_request('getFile', $data = array('file_id' => $message_check[1]));
				$photo_obj = $photo_obj ->result;
				
				$photo_url = 'https://api.telegram.org/file/bot' . TG_BOT_TOKEN . '/' . $photo_obj ->file_path;
				
				$result = tg_image_upload($photo_url);
				
				if ($result[0] == 'error') {
					$text = '<em>Во время загрузки изображения произошла ошибка, поробуйте еще раз</em>';
					tg_send($chat_id, $text);
					exit('ok');
				} 
				
				if ($result[0] == 'ok') {
					$message = $result[1];
				}
				
			}
		}
		
		//Сохраняем полученные данные
		update_field($tg_field, $message, $person_id);
		
		//Выводим текущую задачу
		$text = '<b>' . $register_steps[$tg_count][1] . '</b>';
		
		//Увеличиваем текущий шаг на 1
		update_field('tg_count', $tg_count + 1, $person_id);
		
		//Отправляем собранный вопрос пользователю
		tg_send($chat_id,$text);
		exit('ok');
		
	}
	
	if ($tg_count == $steps_count) {
		
		//Отправляем фотографию профиля и меняем клавиатуру
		$photo = get_field('tg_photo',$person_id);
		$caption = get_field('tg_name',$person_id);
		
		$rows = array();
		$rows[] = array('Вернуться в меню');
		$keyboard = tg_get_keyboard($rows);
		
		tg_send_photo($chat_id, $photo, $caption, $keyboard);
		
		//Отправляем данные участника
		$text = '<b>Ваши данные:</b>' . PHP_EOL;
		$text .= 'Город: ' . get_field('tg_city',$person_id) . PHP_EOL;
		$text .= 'Должность: ' . get_field('tg_job',$person_id) . PHP_EOL;
		$text .= 'Навыки: ' . get_field('tg_skills',$person_id) . PHP_EOL;	
		$text .= 'Веб-сайт: ' . get_field('tg_website',$person_id) . PHP_EOL;
		$text .= 'Эл. почта: ' . get_field('tg_email',$person_id) . PHP_EOL;

		tg_send($chat_id,$text);
		exit('ok');
	}
	
	
	exit('ok');
}	