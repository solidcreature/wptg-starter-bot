<?php 
add_action('tg_commands_hook','tg_do_about', 10, 3);

function tg_do_about($chat_id, $message, $person_id) {
	if ($message != 'Инфрмация о плагине') return;
	
	$text = 'Плагин WP Starter Bot, версия 1.0.0' . PHP_EOL;
	$text .= 'Автор плагина: Николай Миронов (@solidcreature)' . PHP_EOL;
	$text .= 'Сайт автора: www.wpfolio.ru';
	
	$row = array();
	$row[] = array('text' => 'Скачать плагин', 'url' => 'https://github.com/solidcreature/wp-starter-bot' );
	$buttons = array($row);
	$keyboard = tg_inline_keyboard($buttons);
	
	tg_send($chat_id, $text, $keyboard);
	exit('ok');
}