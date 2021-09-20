<?php 
add_action('tg_commands_hook','tg_start_quest', 50, 3);
add_action('tg_status_commands_hook','tg_do_quest', 50, 4);


function tg_start_quest($chat_id, $message, $person_id) {
	if ($message != 'Квест-игра') return;
	
	//Задаем контент раздела
	$photo = 'AgACAgIAAxkBAAIJNGFIYVVsZDQHxkbe6AS_xiHl_eOoAAIDtjEb9CRBSgeDz_os_I8RAQADAgADeAADIAQ';
	$caption = 'Чтобы приступить нажмите на кноку "Начать игру" или команду /startgame';
	
	
	//Подготавливаем кнопки для клавиатуры
	$rows = array();
	$rows[] = array('Начать игру');
	$rows[] = array('Вернуться в меню');


	//Получаем клавиатуру
	$keyboard = tg_get_keyboard($rows);
	
	
	//Меняем статус пользователя на quest
	update_field('tg_status','quest',$person_id);
	update_field('tg_count',0,$person_id);
	
	
	//Посылаем сообщение
	tg_send_photo($chat_id, $photo, $caption, $keyboard);
	exit('ok');
}

function tg_do_quest($chat_id, $message, $person_id, $status) {
	if ($status != 'quest') return;
	
	//Задаем сеттинг игры, то, что может попасться в каждой комнате
	$quest = array();
	$quest[] = array('boss_1','boss_2','boss_3');
	$quest[] = array('monster_1','monster_2','monster_3','monster_4','monster_5');
	$quest[] = array('trap_1','trap_2','trap_3');
	$quest[] = array('oneway_1','oneway_2','oneway_3','oneway_4','oneway_5','oneway_6');
	$quest[] = array('fork_1','fork_2','fork_3');
	$quest[] = array('hall_1','hall_2','hall_3');
	$quest[] = array('stuff_1','stuff_2','stuff_3','stuff_4','stuff_5','stuff_6','stuff_7','stuff_8');
	$quest[] = array('chest_1','chest_2','chest_3','chest_4');
	$quest[] = array('skill_1','skill_2','skill_3');
	
	//Выбор следующей комнаты
	$numbers = range(0, count($quest)-1);
	shuffle($numbers);
	
	
	//Направления движения
	/*
	$directions = ['идти налево', 'пойти направо', 'пойти вперед', 'спуститься вниз', 'забраться наверх', 'завернуть за угол', 'разобрать стену', 'перепрыгнуть расщелину', 'проползти через узкий проход', 'двинуться в темноту', 'двинуться на свет','подняться по лестнице','спуститься по лестнице'];
	shuffle($directions);
	*/

	if ( $message == 'Вернуться в меню' ) {
		update_field('tg_status','',$person_id);
		$text = 'Вы вернулись в основное меню, ваш прогресс в игре сохранен';
		$keyboard = tg_default_keyboard();
		tg_send($chat_id, $text, $keyboard);
		exit('ok');
	}	
	
	if ( $message == 'Параметры' ) {
		tg_show_params($chat_id, $person_id);
		exit('ok');
	}
	
	if ( $message == 'Инвентарь' ) {
		tg_show_inventory($chat_id, $person_id);
		exit('ok');
	}
	
	if ( $message == 'Начать игру' or $message == '/restart' or $message == '/startgame') {
		
		//Сбрасываем текущий прогресс игрока
		tg_quest_reset_progress($person_id);
		
		//Задаем первый пост и обще-игровую клавиатуру
		$text = 'Вход в подземелье остался далеко позади, стихли звуки внешнего мира. В сгущающихся сумерках собственные шаги кажутся оглушающе громкими. Каменные стены штольни сужаются и вы уже следите, чтобы не задеть их плечами. Длинный каменный коридор постепенно уводит все ниже и ниже, вы стараетесь ступать осторожнее, но вдруг подошвы начинают предательски скользить и вы, словно на лыжах, несетесь в абсолютною темноту...';
		
		$rows = array();
		$rows[] = array('Параметры','Инвентарь');
		$rows[] = array('Вернуться в меню');
		$keyboard = tg_get_keyboard($rows);		
		
		tg_send($chat_id, $text, $keyboard);
		

		//Посылаем еще один пост, теперь уже с инлайн-клавиатурой
		$text ='... вы приходите в себя после падения. Перед вами просторная пещера, стены которой покрыты известняковыми наплывами. Многочисленные игольчатые сталактиты бахромой свисают с потолка, а навстречу им поднимается сталагмиты, похожие на обледеневшие деревья. За каменным лесом видны несколько входов в глубину подземелья.';
		
		//Готовим специальную клавиатуру
		$rows = array();
		$rows[] = array('text' => 'Идти вперед', 'callback_data' => 8);
		$rows[] = array('text' => 'Пойти налево', 'callback_data' => 8);
		$rows[] = array('text' => 'Пойти направо', 'callback_data' => 8);
		
		$buttons = array();
		foreach ($rows as $row) {
			$buttons[] = array($row);	
		}
		
		$keyboard = tg_inline_keyboard($buttons);	
		
		$dungeon_data = tg_quest_dungeon_walk(5, 'hall_2');
		tg_send($chat_id, $text, $keyboard);
		exit('ok');
	}
	
	if ( in_array($message, ['0','1','2','3','4','5','6','7','8']) ) {
		$monsters = $quest[$message];
		shuffle($monsters);
		
		$step_data = tg_quest_dungeon_walk($message, $monsters[0]);
		tg_send($chat_id, $step_data[0], $step_data[1]);
		exit('ok');
	}
	
	//Если дошли до этого момента, значит пришла определенная команда относящаяся к текущей комнате
	$command = explode('-',$message);
	
	if ($command[0] == 'fight') {
		$step_data = tg_quest_fight($person_id,$command[1]);
		tg_send($chat_id, $step_data[0], $step_data[1]);
		exit('ok');
	}
	
	if ($command[0] == 'run') {
		$step_data = tg_quest_run($person_id,$command[1]);
		tg_send($chat_id, $step_data[0], $step_data[1]);
		exit('ok');
	}
	
	if ($command[0] == 'luck') {
		$step_data = tg_quest_luck($person_id,$command[1]);
		tg_send($chat_id, $step_data[0], $step_data[1]);
		exit('ok');
	}
	
	if ($command[0] == 'take') {
		$step_data = tg_quest_take($person_id,$command[1]);
		tg_send($chat_id, $step_data[0], $step_data[1]);
		exit('ok');
	}
	
	if ($command[0] == 'open') {
		$step_data = tg_quest_open($person_id,$command[1]);
		tg_send($chat_id, $step_data[0], $step_data[1]);
		exit('ok');
	}
	
	if ($command[0] == 'train') {
		$step_data = tg_quest_train($person_id,$command[1]);
		tg_send($chat_id, $step_data[0], $step_data[1]);
		exit('ok');
	}
	
}


//Получить параметры монстра на основе его id
function tg_get_monster_params($monster_id) {
	$monster = explode('_',$monster_id);
	
	
	
	$strength = 0;
	$catch = 0;
	
	if ($monster[0] == 'monster') {
		$strength = (int)$monster[1] * 2 - 2;
		$catch = (int)$monster[1];
	}
	
	if ($monster[0] == 'boss') {
		$strength = 5 + (int)$monster[1] * 2;
		$catch = (int)$monster[1];
	}

	$monster_params = array($strength, $catch);
	
	return $monster_params;
}



//Вывести основные параметры героя
function tg_show_params($chat_id, $person_id) {
	$text = '<b>Основные параметры:</b>' . PHP_EOL;
	$text .= 'Уровень:' . get_field('quest_level',$person_id) . PHP_EOL;
	$text .= 'Крутизна:' . get_field('quest_coolness',$person_id) . PHP_EOL . PHP_EOL;
	$text .= '<b>Дополнительные навыки:</b>' . PHP_EOL;
	$text .= 'Сбежать от монстра: ' . get_field('quest_sneak',$person_id) . PHP_EOL;
	$text .= 'Взлом замков: ' . get_field('quest_lockpick',$person_id) . PHP_EOL;
	$text .= 'Уворот от ловушки: ' . get_field('quest_avoidtraps',$person_id) . PHP_EOL;
	
	tg_send($chat_id, $text);
}

//Вывести основные параметры героя
function tg_show_inventory($chat_id, $person_id) {
	$text = '<b>Ваша экипировка:</b>' . PHP_EOL;
	$text .= 'Голова:' . get_field('quest_head',$person_id) . PHP_EOL;
	$text .= 'Туловище:' . get_field('quest_armor',$person_id) . PHP_EOL;
	$text .= 'Оружие: ' . get_field('quest_weapon',$person_id) . PHP_EOL;
	$text .= 'Руки: ' . get_field('quest_hands',$person_id) . PHP_EOL;
	$text .= 'Ноги: ' . get_field('quest_legs',$person_id) . PHP_EOL;
	
	tg_send($chat_id, $text);
}


//Функция получает результаты и текст боя
function tg_quest_fight($person_id,$monster_id) {
	$monster = tg_get_monster_params($monster_id);
	$monster_attack = $monster[0];
	$person_level = (int)get_field('quest_level',$person_id);
	$person_coolness = (int)get_field('quest_coolness',$person_id);
	
	$person_attack = $person_level + $person_coolness;
	
	$text = 'Ваша сила: ' . $person_attack . PHP_EOL;
	$text .= 'Сила монстра: ' . $monster_attack . PHP_EOL . PHP_EOL;
	
	$rows = array();
	
	if ($person_attack >= $monster_attack) {
		
		//Пишем текст победы
		$text .= 'Вы победили ' . $monster_id;	
		
		//Увеличиваем уровень
		$person_level++;
		
		//Готовим кнопки под сообщениес
		$rows[] = array('text' => 'Возьмите награду', 'callback_data' => 'take-' . $selection);
		$rows[] = array('text' => 'Идти дальше', 'callback_data' => random_int(3, 5));
	}
	
	else {
		//Уменьшаем уровень
		$person_level--;
		
		//Пишем текст поражения
		$text .= 'Вы проиграли бой, ваш уровень уменьшился до ' . $person_level; 
		
		if ($person_level <= 0) {
			
			//Добавляем текст о проигрышые
			$text .= PHP_EOL . PHP_EOL . '<em>К сожалению, вы проиграли. Начните сначала</em> /restart';
			$rows[] = array('text' => 'Начать сначала', 'callback_data' => '/restart');
			
		} else {
			
			$rows[] = array('text' => 'Продолжить путь', 'callback_data' => random_int(3, 5));
		}
	}
	
	update_field('quest_level', $person_level, $person_id);
	
	$buttons = array();
	foreach ($rows as $row) {
		$buttons[] = array($row);	
	}

	$keyboard = tg_inline_keyboard($buttons);
	
	return array($text, $keyboard);
}


//Функция получает результаты и текст боя
function tg_quest_run($person_id,$monster_id) {
	$person_sneak = get_field('quest_sneak',$person_id);
	$monster_catch = explode('_', $monster_id);
	$monster_catch = floor((int)$monster_catch[1]/2);
		 
	
	$rows = array();
	
	if ($person_sneak >= $monster_catch) {
		$text = 'Вам удалось скрыться от страшного монстра';
		$rows[] = array('text' => 'Идти дальше', 'callback_data' => random_int(3, 8));		
	}
	else {
		$text = 'Монстр не дал вам убежать, вы теряете 1 единицу крутости';	
		$rows[] = array('text' => 'Сражаться', 'callback_data' => 'fight-' . $monster_id);
		$coolness = get_field('quest_coolness', $person_id);
		if ($coolness > 0) $coolness--;
		update_field('quest_coolness',$coolness,$person_id);
	}

	$buttons = array($rows);

	$keyboard = tg_inline_keyboard($buttons);
	
	return array($text, $keyboard);
}


//Функция подсчитывает результат попадания в ловушку
function tg_quest_luck($person_id,$trap_id) {
	
	//Умение игрока избегать ловушки и ее сложность
	$person_luck = get_field('quest_avoidtraps',$person_id);
	$trap = explode('_',$trap_id);
	
	
	if ($person_luck >= $trap) {
		$text = 'Вам удалось избежать страшной ловушки';
	} else {
		$text = 'Ловушка сработала. Ваша крутость уменьшилась на 1';
		$coolness = get_field('quest_coolness', $person_id);
		if ($coolness > 0) $coolness--;
		update_field('quest_coolness',$coolness,$person_id);		
	}	
	
	$rows = array();
	$rows[] = array('text' => 'Идти дальше', 'callback_data' => random_int(1, 8));
	
	$buttons = array();
	foreach ($rows as $row) {
		$buttons[] = array($row);	
	}
	
	$buttons = array($rows);

	$keyboard = tg_inline_keyboard($buttons);
	
	return array($text, $keyboard);
}


//Функция кладет предмет в инвентарь
function tg_quest_take($person_id,$item_id) {
	$item_data = tg_get_quest_text(6, $item_id);
	$field = 'quest_' . $item_data[0];
	$amount = $item_data[1];
	$item = $item_data[2];
	
	//Убираем предмет, если данная позиция есть в инвернтаре
	if ( get_field($field,$person_id) ) tg_quest_put($person_id, $field) ; 
	
	//Добавляем предмет в инвентарь пользователя
	update_field($field, $amount . ',' . $item);
	
	//Добавляем крутость равную крутости предмета
	$coolness = get_field('quest_coolness',$person_id);
	$coolness += $amount;
	update_field('quest_coolness', $coolness, $person_id);
	
	//Составляем текст сообщения
	$text = '<em>Вы взяли предмет <b>' . $item . '</b></em>';
	
	//Готовим клавиатуру
	$rows = array();
	$rows[] = array('text' => 'Идти дальше', 'callback_data' => random_int(3, 5));
	$buttons = array($rows);
	$keyboard = tg_inline_keyboard($buttons);
	
	return array($text, $keyboard);
}


//Функция убираем предмет из инвентаря
function tg_quest_put($person_id, $field) {
	if ( !get_field($field,$person_id) )  return; 

	//Получаем данные о предмете
	$item = get_field($field,$person_id);
	$item = explode(',',$item);
	
	//Уменьшаем крутизну игрока на крутизну предмета
	$coolness = get_field('quest_coolness',$person_id);
	$coolness -= $item[0];
	update_field('quest_coolness',$coolness,$person_id);
	
	//Удаляем предмет
	update_field($field,'',$person_id);
	
	//Сообщаем об этом игроку
	$chat_id = get_field('chat_id',$person_id);
	$text = '<em>Вы избавились от предмета <b>' . $item[1] . '</b></em>';
	tg_send($chat_id, $text);
}


//Функция получает результаты и текст боя
function tg_quest_open($person_id,$item_id) {
	
	//Получаем силу игрока и силу замка
	$person_lockpick = get_field('quest_lockpick',$person_id);
	$lock_strength = explode('_',$item_id);
	$lock_strength = $lock_strength[1];
	
	//Получаем клавиатуру
	$rows = array();
	$rows[] = array('text' => 'Идти дальше', 'callback_data' => random_int(3, 5));
	$buttons = array($rows);
	$keyboard = tg_inline_keyboard($buttons);
	
	if ($lock_strength > $person_lockpick) {
		$text = 'К сожалению замок на сундуке оказался слишком сложным, вам не удалось открыть сундук';
		return array($text, $keyboard);
	}
	
	$item_data = tg_get_quest_text(7, $item_id);
	$field = 'quest_' . $item_data[0];
	$amount = $item_data[1];
	$item = $item_data[2];
	
	//Отправляем сообщение игроку
	$text = 'Поздравляем! Вам удалось открыть сундук. В сундуке вы нашли <em>' . $item . '</em>' . PHP_EOL;
	
	//Сравниваем силу меча в сундуке и в руке
	$current_strength = get_field('quest_weapon',$person_id);
	if (!$current_strength) { 
		$current_strength = 0; 
	}
	else {
		$current_strength = explode(',',$current_strength);
		$current_strength = $current_strength[0];
	}
	
	if ($current_strength > $amount) {
		$text .= 'Оружие в вашей руке сильнее, вы не стали брать оружие из сундука';
		return array($text, $keyboard);
	}
	
	
	if ( get_field($field,$person_id) ) tg_quest_put($person_id, $field) ; 
	
	//Добавляем предмет в инвентарь пользователя
	update_field($field, $amount . ',' . $item);
	
	//Добавляем крутость равную крутости предмета
	$coolness = get_field('quest_coolness',$person_id);
	$coolness += $amount;
	update_field('quest_coolness', $coolness, $person_id);
	
	//Составляем текст сообщения
	$text .= '<em>Вы взяли предмет <b>' . $item . '</b></em>';
	
	return array($text, $keyboard);
}


//Функция получает результаты и текст боя
function tg_quest_train($person_id,$item_id) {

	//Получаем данные комнаты
	$room_data = tg_get_quest_text(8, $item_id);
	$field = 'quest_' . $room_data[0];
	
	//Увеличиваем навык на 1
	$skill = get_field($field,$person_id);
	$skill++;
	update_field($field, $skill, $person_id);
	
	//Собираем текст
	$text = $room_data[4] . $skill;
	
	//Собираем клавиатуру
	$rows = array();
	$rows[] = array('text' => 'Идти дальше', 'callback_data' => random_int(3, 5));
	$buttons = array($rows);
	$keyboard = tg_inline_keyboard($buttons);
	
	return array($text, $keyboard);
}




//Подбираем текст и кнопки в зависимости от текущей локации
function tg_quest_dungeon_walk($room_num, $selection) {
	
	if ($room_num == 0)	{
		$text = tg_get_quest_text($room_num, $selection);
		
		$rows = array();
		$rows[] = array('text' => 'Сражаться', 'callback_data' => 'fight-' . $selection);
		$rows[] = array('text' => 'Убежать', 'callback_data' => 'run-' . $selection);
		
		$buttons = array();
		foreach ($rows as $row) {
			$buttons[] = array($row);	
		}
		
		$keyboard = tg_inline_keyboard($buttons);
	}
	
	if ($room_num == 1)	{
		$text = tg_get_quest_text($room_num, $selection);
		
		$rows = array();
		$rows[] = array('text' => 'Сражаться', 'callback_data' => 'fight-' . $selection);
		$rows[] = array('text' => 'Убежать', 'callback_data' => 'run-' . $selection);
		
		$buttons = array();
		foreach ($rows as $row) {
			$buttons[] = array($row);	
		}
		
		$keyboard = tg_inline_keyboard($buttons);
	}
	
	if ($room_num == 2)	{
		$text = tg_get_quest_text($room_num, $selection);
		
		$rows = array();
		$rows[] = array('text' => 'Испытать удачу', 'callback_data' => 'luck-' . $selection);
		
		$buttons = array($rows);

		$keyboard = tg_inline_keyboard($buttons);
	}
	
		
	if ($room_num == 3)	{
		$text = tg_get_quest_text($room_num, $selection);
		
		$numbers = range(0, 8);
		shuffle($numbers);
		
		$directions = tg_quest_get_directions();
		
		$rows = array();
		$rows[] = array('text' => $directions[0], 'callback_data' => $numbers[0]);
		
		$buttons = array($rows);

		$keyboard = tg_inline_keyboard($buttons);
	}
	
	if ($room_num == 4)	{
		$text = tg_get_quest_text($room_num, $selection);
		
		$numbers = range(0, 8);
		shuffle($numbers);
		
		$directions = tg_quest_get_directions();
		
		$rows = array();
		$rows[] = array('text' => $directions[0], 'callback_data' => $numbers[0]);
		$rows[] = array('text' => $directions[1], 'callback_data' => $numbers[1]);
		
		$buttons = array();
		foreach ($rows as $row) {
			$buttons[] = array($row);	
		}
		
		$keyboard = tg_inline_keyboard($buttons);
	}
	
	if ($room_num == 5)	{
		$text = tg_get_quest_text($room_num, $selection);
		
		$numbers = range(0, 8);
		shuffle($numbers);
		
		$directions = tg_quest_get_directions();
		
		$rows = array();
		$rows[] = array('text' => $directions[0], 'callback_data' => $numbers[0]);
		$rows[] = array('text' => $directions[1], 'callback_data' => $numbers[1]);
		$rows[] = array('text' => $directions[2], 'callback_data' => $numbers[2]);
		
		$buttons = array();
		foreach ($rows as $row) {
			$buttons[] = array($row);	
		}
		
		$keyboard = tg_inline_keyboard($buttons);
	}
	
	if ($room_num == 6)	{
		$room_data = tg_get_quest_text($room_num, $selection);
		
		$text = 'Удача! Вы нашли предмет <em>' . $room_data[2] . '</em>' . PHP_EOL;
		$text .= '<b>Взять предмет?</b>';
		
		$numbers = range(3, 5);
		shuffle($numbers);
		
		$directions = tg_quest_get_directions();
		
		$rows = array();
		$rows[] = array('text' => 'Взять предмет', 'callback_data' => 'take-' . $selection);
		$rows[] = array('text' => 'Идти дальше', 'callback_data' => $numbers[0]);
		
		$buttons = array();
		foreach ($rows as $row) {
			$buttons[] = array($row);	
		}
		
		$buttons = array($rows);

		$keyboard = tg_inline_keyboard($buttons);
	}
	
	if ($room_num == 7)	{
		$complexity = explode('_',$selection);
		
		$text = 'Удача! Вы нашли сундук, но к сожалению он закрыт на массивный замок. <em>Сложность замка (' . $complexity[1] . ')  </em>' . PHP_EOL;
		$text .= '<b>Попытаетесь открыть сундук?</b>';
		
		$numbers = range(3, 5);
		shuffle($numbers);
		
		$directions = tg_quest_get_directions();
		
		$rows = array();
		$rows[] = array('text' => 'Открыть сундук', 'callback_data' => 'open-' . $selection);
		$rows[] = array('text' => 'Идти дальше', 'callback_data' => $numbers[0]);
		
		$buttons = array();
		foreach ($rows as $row) {
			$buttons[] = array($row);	
		}
		
		$buttons = array($rows);

		$keyboard = tg_inline_keyboard($buttons);
	}
	
	if ($room_num == 8)	{
		$room_data = tg_get_quest_text($room_num, $selection);
		$text = $room_data[2];
		
		$variants = $room_data[3];
		$variants = explode(',',$variants);
		
		$numbers = range(3, 5);
		shuffle($numbers);
		
		$rows = array();
		$rows[] = array('text' => $variants[0], 'callback_data' => 'train-' . $selection);
		$rows[] = array('text' => $variants[1], 'callback_data' => $numbers[0]);
		
		$buttons = array();
		foreach ($rows as $row) {
			$buttons[] = array($row);	
		}
		
		$buttons = array($rows);

		$keyboard = tg_inline_keyboard($buttons);

	}
	
	return array($text, $keyboard);
}


//Получить список направлений для кнопок движения
function tg_quest_get_directions() {
	$directions = ['идти налево', 'пойти направо', 'пойти вперед', 'спуститься вниз', 'забраться наверх', 'завернуть за угол', 'разобрать стену', 'перепрыгнуть расщелину', 'проползти через узкий проход', 'двинуться в темноту', 'двинуться на свет','подняться по лестнице','спуститься по лестнице'];
	shuffle($directions);	
	return $directions;
}

//Получить список направлений для кнопок движения
function tg_quest_reset_progress($person_id) {
	update_field('quest_level',1,$person_id);
	update_field('quest_coolness',1,$person_id);
	update_field('quest_sneak',1,$person_id);
	update_field('quest_lockpick',1,$person_id);
	update_field('quest_avoidtraps',1,$person_id);
	update_field('quest_head','',$person_id);
	update_field('quest_armor','',$person_id);
	update_field('quest_weapon','',$person_id);
	update_field('quest_hands','',$person_id);
	update_field('quest_legs','',$person_id);
}


//Здесь содержится весь описательный текст квеста
function tg_get_quest_text($index, $variant) {
	$quest_text = array();
	
	//Описание боссов
	$quest_text[0] = array(
		'boss_1' => 'Где-то впереди раздаются равномерные гулкие удары. Дойдя до поворота, вы заглядываете за угол и видите трехметрового тролля, который лупит громадной дубиной по стене и запихивает в рот отколовшиеся куски плит, перетирая их зубами в мелкую крошку. Надеясь, что великан вас не заметил, вы отскакиваете назад, но поздно. Неповоротливый на вид тролль-камнеед оказывается очень шустрым и спустя краткий миг предстает перед вами в полный рост, угрожающе помахивая дубинкой.',	
		'boss_2' => 'Очередной коридор закончился и вы вышли на берег подземного озера. Прекрасная возможность утолить жажду и привести себя в порядок. Вы подходите к самой кромке воды, как спустя миг рядом из воды поднимается разъяренный монстр. Мускулистое собачье тело на мощных раскоряченных лапах стоит по брюхо в воде, тонкий хвост свирепо хлещет по впалым бокам, многочисленные змеиные головы подземной Гидры, злобно шипя, пытаются добраться до вас.',
		'boss_3' => 'Вы выходите в большой зал, посередине которого на возвышении стоит массивный ледяной трон. Зеленоватое мертвенное свечение исходит от облаченного в стальные доспехи скелета-гиганта, восседающего на нем. Призрачные фигуры прислужников склонились в почтительном поклоне перед Королем-личем. Вы замираете, когда слышите стальной лязг выдвигаемого из ножен меча, а мертвец встает и делает первый шаг вам навстречу.',
		);
	

	//Описание монстров
	$quest_text[1] = array(
	'monster_1' => 'Подскользнувшись в темноте вы падаете в какую-то жижу. Пытаетесь встать, но не так-то просто выбраться из липкой субстанции, которая волнообразно подрагивает под вашим телом. С трудом сгруппировавшись, вы все же умудряетесь сползти вниз. Поднимаясь во весь рост, вы видите подземного слизня, который медленно разворачивается к вам.',	
	'monster_2' => 'Через некоторое время перед вами возникает препятствие – покрытый коротким бархатистым мехом холм, перегородивший штольню. Вы застываете в раздумье, а у холма вдруг появляется голова с вытянутой лысоватой мордой, нос которой начинает слишком активно обнюхивать все вокруг. Вы отступаете назад и понимаете, что наткнулись на гигантского крота и он не прочь вами позавтракать.',	
	'monster_3' => 'Вы заворачиваете за угол и успеваете сделать пару шагов, как замечаете движение в глубине пещеры – ее обитатель, глухо ворча, поднимается со своего ложа. Вы успеваете разглядеть, что это троглодит, напоминающий огромного волосатого орангутанга, и понимаете, что он направляется в вашу сторону.',	
	'monster_4' => 'Рассматривая очередные массивные сталактиты вы замечаете свисающий сверху большой мешок сероватого цвета. Вам интересно, что там может быть. Вы подходите поближе, раскрываете его и видите уйму только что вылупившихся паучков. И тут прямо на вас падают липкие упругие нити, которые вы с трудом отрываете от одежды. Подняв голову, вы обнаруживаете в верхнем темном углу пещеры плохо различимую тень громадного паука, который вдруг одним прыжком оказывается прямо перед вами.',	
	'monster_5' => 'Вы прислушиваетесь: шумное дыхание и гулкий топот копыт доносятся из самого дальнего прохода. В легкой панике вы делаете несколько шагов назад, прижимаетесь спиной к ближайшей стене – на середину пещеры выскакивает чудовище с телом человека и головой быка. Раздувая ноздри от ярости, Минотавр жаждет утолить вами свой голод.'
	);	

	$quest_text[2] = array(
		'trap_1' => 'Тоннель приобрел более ровные формы, здесь явно поработали секиры гномов. Идти стало намного приятней. Вы так увлеклись неспешной прогулкой, что не услышали слабый щелчок, но успели увидеть, как часть пола перед вами вдруг провалилась, и застыли перед открывшейся дырой.',
		'trap_2' => 'Осматривая очередной орнамент на полу вы замечаете гранитный блок с выложенной из мелких красных камешков фигуркой. Вы подходите ближе, касаетесь камней рукой, пытаясь понять кто и зачем это сделал. Внезапно впереди и позади вас из пола выдвигаются металлические решетки, которые перекрывают проход. Над головой раздается странный скрежет и вы видите, как одна из тяжелых плит медленно опускается, грозя раздавить вас.',
		'trap_3' => 'Вы уже давно заметили, что с каждым шагом становится холоднее. Как вдруг пол под ногами превратился в круг чистого и очень скользкого льда, по которому невозможно идти. Из пустоты появляются ледяные звездочки с острыми краями и смертельным хороводом начинают кружить вокруг вас.'
		);
	
	//Длинные коридоры с одним выходом
	$quest_text[3] = array(
	'oneway_1' => 'Вы идете по неимоверно длинной и извилистой штольне – приходится то и дело сворачивать. Вы устали от бесконечных поворотов и вам уже кажется, что вы идете по кругу и скоро снова окажетесь у входа. Сверху изредка сыплются комья земли, штольня сужается, кажется, что сводчатый потолок ощутимо давит на плечи.',
	'oneway_2' => 'Коридор, по которому вы идете, уводит вас в глубину подземелья и вам приходится цепляться за шершавые стены руками и отклонять тело назад, чтобы крутой спуск не превратился в неконтролируемое падение.',
	'oneway_3' => 'Этот коридор сильно отличается от тех, по которым вы шли раньше. Под ногами появились небольшие пятна мха, которых становится все больше, и скоро моховая поросль заплетает стены штольни до самого верха. Среди зелени видна россыпь мелких бледных цветов, а темноту впереди разгоняет бледное светлое пятно. Вы ускоряете шаг и вскоре выбираетесь под яркие лучи солнца, которое светит через большое отверстие, находящееся высоко над головой.',
	'oneway_4' => 'Ваш путь продолжается – коридор, который вы выбрали так широк и высок, что в нем без труда можно кататься на слоне. Ровные стены и потолок выложены светящимися полированными плитами, которые неплохо освещают дорогу.',
	'oneway_5' => 'Вы выходите на узкий уступ над пропастью, перейти которую можно по каменному мосту. Жутковато идти над бездной без перил, но на другой стороне моста виден большой зал с многочисленными колоннами. Старательно отводя глаза от пугающих красноватых всполохов в глубине бездны, вы медленно бредете над пропастью.',
	'oneway_6' => 'Зайдя в проход, вы слышите журчание воды и чуть ли не бегом устремляетесь вперед. В небольшой пещерке по одной из стен тонкими струйками сочатся струйки воды и скапливаются в углублении в полу. Умывшись и с трудом очистив одежду, вы усаживаетесь рядом с источником, намереваясь немного отдохнуть.'
	);
	
	//Развилки
	$quest_text[4] = array(
		'fork_1' => 'Данная часть пещеры оказалась более широкой чем обычно. Вы осматриваетесь в поисках выхода и видите, что в стенах на разной высоте находятся несколько круглых отверстий, через которые можно выбраться отсюда.',
		'fork_2' => 'Вы стоите на развилке, вслушиваясь в тишину подземелья, и решаете какую из штолен выбрать в этот раз.',
		'fork_3' => 'Вы слышите треск, страшный грохот, стены прохода рушатся, преграждая вам путь назад. Вы выбираете  лаз в стене пещеры и ползете, с трудом протискиваясь через узкие места. Скоро лаз расширяется, вы встаете в полный рост и через некоторое время оказываетесь на развилке.',
		);
	
	
	//Большие залы и несколько выходов
	$quest_text[5] = array(
		'hall_1' => 'Вы стоите у входа в большой круглый зал: высокий свод пещеры усыпан переливающимися голубыми огоньками, вертикальные каменные складки стен цвета индиго, бирюзы и лазури похожи на застывшие водопады. Несколько входов в новые тоннели едва видны в этом царстве синего цвета.',
		'hall_2' => 'Серая мгла подземелья постепенно отступает. Вы двигаетесь вперед и оказываетесь посреди необычного зала, явно созданного искусными мастерами. Разглядываете колонны, украшенные сложной резьбой: причудливый орнамент из спирально закручивающихся линий и геометрических фигур не дает отвести взгляд.',
		'hall_3' => 'Становится заметно холоднее, под ногами похрустывает снег. Вы выходите к отвесной каменной стене с массивной полукруглой аркой входа, сложенной из обтесанных снежных глыб. По ровному слою снега вы входите в огромный зал, уставленный вертикальными ледяными саркофагами. В них неподвижно застыли фигуры людей, эльфов, гномов',
		);
	
	$quest_text[6] = array(
		'stuff_1' => array('head',1,'шлем'),
		'stuff_2' => array('armor',1,'кольчуга'),
		'stuff_3' => array('weapon',1,'меч'),
		'stuff_4' => array('hands',1,'перчатки'),
		'stuff_5' => array('legs',1, 'сапоги'),
		'stuff_6' => array('head',2,'добротный шлем'),
		'stuff_7' => array('armor',2,'добротная кольчуга'),
		'stuff_8' => array('weapon',2,'добротный меч'),
	);
				
	$quest_text[7] = array(
		'chest_1' => array('weapon',2,'сверкающий меч'),
		'chest_2' => array('weapon',3,'боевая секира'),
		'chest_3' => array('weapon',4,'адская булава'),
		'chest_4' => array('weapon',5,'меч "Убийца подземелий"'),
	);
	
	$quest_text[8] = array(
		'skill_1' => array('sneak', 1,'Вы попадаете в удивительную комнату, полную необычного вида статуй, изображающих подземных монстров. Приглядевшись, вы замечаете, что это не просто статичные изваяния, а часть большого древнего механизма. Вы почему-то уверены, что поросший мхом рубильник в конце комнаты приведет все в движение.', 'Дёрнуть рубильник, Пойти дальше','С поворотом рубильника статуи приходят в движение. Вам приходится проявить всю свою ловкость чтобы увернуться и без потерь выйти и комнаты. Ваш навык <b>Сбежать от монстра</b> увеличен до '),
		'skill_2' => array('lockpick', 1, 'Вы попадаете в необычную комнату правильной формы, большую часть которой занимают каменные столы-тумбы. На каждом столе находится по сундуку отличающимся формой и размером. Комната давно заброшена, столы поросли мхом, а через прогнившие доски видно, что сундуки пустые', 'Изучить сундуки, Пойти дальше','В некоторых сундуках ржавчина не до конца съела запорный механизм и вы стали лучше понимать их устройство. Ваше навык <strong>Взлом замков</strong> увеличен до'),
		'skill_3' => array('avoidtraps',1,'Вы входите в просторный зал и оглядываетесь по-сторонам. Больше всего он напоминает заброшенную тренировочную площадку, тут есть ямы с кольями, крутящиеся столбы, утыканные шипами, маятниковый механизм с подвешанными секирами', 'Начать тренировку, Пойти дальше','Это была жаркая тренировка! Пару раз вас чуть не спалило огнем, один раз почти разрезало пополам. Но все не зря, ваш навык <strong>Уворот от ловушки</strong> увеличен до '),
	);	
	
	
	
	if ( isset($quest_text[$index][$variant]) ) return $quest_text[$index][$variant];
	
	return 'Description ' . $index . '-' . $variant . ' is missing';
	
	
	
}
