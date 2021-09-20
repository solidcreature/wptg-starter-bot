<?php 
add_action('tg_commands_hook','tg_start_testing', 50, 3);
add_action('tg_status_commands_hook','tg_do_testing', 50, 4);


function tg_start_testing($chat_id, $message, $person_id) {
	if ($message != 'Тестирование') return;
	
	
	//Задаем контент раздела
	$photo = 'AgACAgIAAxkBAAIJMmFIYSZDDPLEA1rrMtP2L5CfNII2AAICtjEb9CRBSpEJ6mfRoSL2AQADAgADeAADIAQ';
	$caption = 'Демо-тест из 3-х вопросов на знание WordPress /starttest';
	
	
	//Подготавливаем кнопки для клавиатуры
	$rows = array();
	$rows[] = array('Начать тестирование');
	$rows[] = array('Отменить тестирование');


	//Получаем клавиатуру
	$keyboard = tg_get_keyboard($rows);
	
	
	//Меняем статус пользователя на testing
	update_field('tg_status','testing',$person_id);
	update_field('tg_count',0,$person_id);
	
	
	//Посылаем сообщение
	tg_send_photo($chat_id, $photo, $caption, $keyboard);
	exit('ok');
}


function tg_do_testing($chat_id, $message, $person_id, $person_status) {
	if ($person_status != 'testing') return;
	
	//Чтобы код плагина был максимально простым и универсальным вопросы и ответы прописаны в самом плагине
	//В реальном проекте вопросы и ответы нужно выносить в отдельные типы записей и / или acf-поля
	$test = array();
	
	$test[0] = array(
			'image' => 'AgACAgIAAxkBAAIJSmFIZeGq7ipn4ag9AZ5j0SVNGO2XAAIGtjEb9CRBSsvbzHSEIo8kAQADAgADeQADIAQ',
			'question' => 'Какой логотип правильный?',
			'answers' => array(
				array(0, 'Первый'),
				array(0, 'Второй'),
				array(20, 'Третий'),
				array(0, 'Ни один из вариантов'),
			)
		);
		
	$test[1] = array(
			'image' => 'AgACAgIAAxkBAAIJTGFIZjSELXFbuOgeKYedGpLfef_kAAIHtjEb9CRBSlxYuhPb7xuVAQADAgADeQADIAQ',
			'question' => 'C каким блочным редактором ассоциируется данная картинка?',
			'answers' => array(
				array(20, 'Gutenberg'),
				array(0, 'Tilda'),
				array(0, 'Wix'),
				array(0, 'Elementor'),
			)
		);
		
	$test[2] = array(
			'image' => 'AgACAgIAAxkBAAIJTmFIZscb3OOhSCX1quba-dvYPgpLAAIItjEb9CRBSqXJBJUyJDtZAQADAgADeQADIAQ',
			'question' => 'Как зовут этого улыбчивого парня?',
			'answers' => array(
				array(0, 'Джимми Уэйлс'),
				array(0, 'Джек Дорси'),
				array(20, 'Мэтт Мулленвег'),
				array(0, 'Дрис Бёйтарт'),
			)
		);
	
	
	//Индексы для номерации ответов	
	$indexes = array('A','B','C','D','E','F','G');	
	
	
	//Отмена тестирования
	if ($message == 'Отменить тестирование') {
		update_field('tg_status','',$person_id);
		update_field('tg_result',0,$person_id);
		update_field('tg_count',0,$person_id);
		
		$text = '<em>Вы отменили тестирование</em>';
		$keyboard = tg_default_keyboard();
		tg_send($chat_id, $text, $keyboard);
		exit('ok');
	}
	
	
	//Начало тестирования
	if ($message == 'Начать тестирование' or $message == '/starttest') {
		
		//Отправляем статусное сообщение и обновляем клавиатуру
		$text = __('<em>Вы приступили к тестированию</em>');
		$rows = array();
		$rows[] = array('Отменить тестирование');
		$keyboard = tg_get_keyboard($rows);
		tg_send($chat_id,$text,$keyboard);
		
		
		//Собираем текст первого вопроса и варианты ответов
		$photo = $test[0]['image'];
		$text = '<b>Вопрос №1:</b> ' . $test[0]['question'] . PHP_EOL;
		$answers_text = '';
		
		$answers = $test[0]['answers']; 
		$num = 0;
		
		foreach ( $answers as $answer ) {
			$answers_text .= $indexes[$num] . '). ' . $answer[1] . PHP_EOL;
			$num++;
		}
		$text .= $answers_text;
		
		//Собираем клавиатуру с вариантами ответов
		$count = 0;
		$row = array();
		while ($count < $num) {
			$row[] = array('text' => $indexes[$count], 'callback_data' => 'answer_' . $count);	
			$count++;
		}
		$keyboard = tg_inline_keyboard(array($row));
		
		//Обновляем текущий счетчик пользователя
		update_field('tg_count',1,$person_id);
		
		//Отправляем собранный вопрос пользователю
		tg_send_photo($chat_id,$photo,$text,$keyboard);
		exit('ok');
	}
	
	
	$tg_count = (int)get_field('tg_count', $person_id);
	
	//Так как в нашем тесте только 3 вопроса, то ставим вот такие условия
	if ($tg_count > 0 and $tg_count < 3) {
		
		//Проверяем что мы получили в качестве ответа
		if( strpos($message, 'answer') === false ) {
			$text = '<em>Некорректный ответ. Пожалуйста выберите один из предложенных вариантов</em>';
			tg_send($chat_id,$text);
		} else {
			$text ='<em>Спасибо, мы записали ответ</em>';
			tg_send($chat_id,$text);
		}
		
		//Проверяем правильность ответа и записываем результат
		$message = explode('_',$message);
		$result = get_field('tg_result',$person_id);
		if (!$result) $result = 0;
		
		$previous_question_num = $tg_count-1;
		$previous_answer_num = $message[1];
		
		$question_result = $test[$previous_question_num]['answers'][$previous_answer_num][0];
		if (!$question_result) $question_result = 0;
		
		$result += $question_result;
		update_field('tg_result', $result, $person_id);
		
		
		//Собираем текст первого вопроса и варианты ответов
		$question_num = $tg_count+1;
		$photo = $test[$tg_count]['image'];
		$text = '<b>Вопрос №'. $question_num .':</b> ' . $test[$tg_count]['question'] . PHP_EOL;
		$answers_text = '';
		
		$answers = $test[$tg_count]['answers']; 
		$num = 0;
		
		foreach ( $answers as $answer ) {
			$answers_text .= $indexes[$num] . '). ' . $answer[1] . PHP_EOL;
			$num++;
		}
		$text .= $answers_text;
		
		//Собираем клавиатуру с вариантами ответов
		$count = 0;
		$row = array();
		while ($count < $num) {
			$row[] = array('text' => $indexes[$count], 'callback_data' => 'answer_' . $count);	
			$count++;
		}
		$keyboard = tg_inline_keyboard(array($row));
		
		//Обновляем текущий счетчик пользователя
		update_field('tg_count', $question_num, $person_id);
		
		//Отправляем собранный вопрос пользователю
		tg_send_photo($chat_id,$photo,$text,$keyboard);
		exit('ok');
		
	}
	
	if ($tg_count == 3) {
		//Проверяем что мы получили в качестве ответа
		if( strpos($message, 'answer') === false ) {
			$text = '<em>Некорректный ответ. Пожалуйста выберите один из предложенных вариантов</em>';
			tg_send($chat_id,$text);
		} else {
			$text ='<em>Спасибо, мы записали ответ</em>';
			tg_send($chat_id,$text);
		}	
		
		//Проверяем правильность ответа и записываем результат
		$message = explode('_',$message);
		$result = (int)get_field('tg_result',$person_id);
		if (!$result) $result = 0;
		
		$previous_question_num = $tg_count-1;
		$previous_answer_num = $message[1];
		
		$question_result = (int)$test[$previous_question_num]['answers'][$previous_answer_num][0];
		if (!$question_result) $question_result = 0;
		
		$result += $question_result;
		update_field('tg_result', $result, $person_id);
		
		//Получаем финальный текст в зависимости от результата
		$text = '<b>Вы завершили тест</b>' . PHP_EOL;
		if (!$result) $text .= 'Сегодня вам что-то не повезло, попробуйти пройти еще раз';
		if ($result == 20) $text .= 'Ваш результат: ' . $result . ' баллов. Так себе результат';
		if ($result == 40) $text .= 'Ваш результат: ' . $result . ' баллов. Хороший результат';
		if ($result == 60) $text .= 'Ваш результат: ' . $result . ' баллов. Отличный результат';
		
		//Загружаем дефолтную клавиатуру
		$keyboard = tg_default_keyboard();
		
		//Возрващаем участника в дефолтное состояние
		update_field('tg_status','0',$person_id);
		update_field('tg_result',0,$person_id);
		update_field('tg_count',0,$person_id);		
		
		//Отправляем сообщение
		tg_send($chat_id,$text,$keyboard);
		exit('ok');
	}
	
	
	exit('ok');
}	