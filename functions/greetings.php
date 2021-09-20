<?php 
add_action('tg_commands_hook','tg_do_greetings', 10, 3);

function tg_do_greetings($chat_id, $message, $person_id) {
	if ($message != '/start') return;
	
	$photo = 'AgACAgIAAxkBAAIJLGFIYLckslnqTSfI8WNsQb6Z5EU2AAL_tTEb9CRBSpEdKwnwfa5iAQADAgADeQADIAQ';
	$caption = __('Привет, я Starter Bot. Изменяя и добавляя код ты можешь создать все что угодно. Посмотри что я умею из коробки','tg_starter');
	$keyboard = tg_default_keyboard();
	
	tg_send_photo($chat_id, $photo, $caption, $keyboard);
	exit('ok');
}
