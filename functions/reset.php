<?php 
add_action('tg_reset_hook','tg_do_reset', 10, 3);

function tg_do_reset($chat_id, $message, $person_id) {
	update_field('tg_status','',$person_id);
	update_field('tg_result',0,$person_id);
	update_field('tg_count',0,$person_id);	
	
	$text = '<em>Вы начали заново</em>';
	$keyboard = tg_default_keyboard();
	
	tg_send($chat_id,$text,$keyboard);
	tg_do_greetings($chat_id, '/start', $person_id);
	exit('ok');
}