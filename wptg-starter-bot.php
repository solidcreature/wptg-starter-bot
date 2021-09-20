<?php
/*
Plugin Name: Telegram Starter Bot for WordPress
Description: Create bots, services, games, apps and more with WordPress and Telegram
Version: 1.0.0
Author: Nikolay Mironov
*/

//Задаем базовые константы для плагина
define( 'TG_BOT_DIR', plugin_dir_path( __FILE__ ) );
define( 'TG_BOT_URL', plugin_dir_url( __FILE__ ) );
define( 'TG_ROUTE', 'tg_starter_bot' );
define( 'TG_ROUTE_MAIN', 'main' );

//Получаем токен чат-бота из настроек плагина 
$bot_options = get_option('tg_bot_options');
$tg_bot_token = trim($bot_options['tg_bot_token']);
define( 'TG_BOT_TOKEN', $tg_bot_token );

//Подключаем необходимые файлы для работы плагина
include TG_BOT_DIR . '/inc/telegram-api.php';
include TG_BOT_DIR . '/inc/plugin-options.php';
include TG_BOT_DIR . '/inc/post-types.php';
include TG_BOT_DIR . '/inc/acf-groups.php';
include TG_BOT_DIR . '/inc/utilities.php';
include TG_BOT_DIR . '/inc/upload.php';

//Подключаем дополнительные функции, определяющие базовую логику плагина
include TG_BOT_DIR . '/functions/keyboards.php';
include TG_BOT_DIR . '/functions/greetings.php';
include TG_BOT_DIR . '/functions/about.php';
include TG_BOT_DIR . '/functions/reset.php';
include TG_BOT_DIR . '/functions/photos.php';

//Демо-контент
include TG_BOT_DIR . '/demos/basics.php';
include TG_BOT_DIR . '/demos/testing.php';
include TG_BOT_DIR . '/demos/register.php';
include TG_BOT_DIR . '/demos/quest.php';


//Задаем end-поинт для общения между сайтом и чат-ботом
add_action( 'rest_api_init', function(){

	//Основной маршрут для работы с ботом	
	register_rest_route( TG_ROUTE, '/' . TG_ROUTE_MAIN, [
		'methods'  => 'post',
		'callback' => 'tg_main_function',
		'permission_callback' => null,
	] );

} );



//Ключевая функция, которая обрабатывает данные, полученные из телеграма
function tg_main_function($request) {
	//Получаем необходимые данные из запроса
	$data = $request->get_json_params();
	
	update_field('data',print_r($data,true),1);

	//По-разному получаем базовые параметры, в зависимости пришел текстовый запрос или колбек кнопки или фото
	if ( $data['message']['text'] ) {
		$message = $data['message']['text']; 
		$chat_id = $data['message']['from']['id'];
		$name = $data['message']['from']['first_name'] . ' ' . $l_name = $data['message']['from']['last_name'];	
	}
	
	elseif ( $data['callback_query']['data'] or $data['callback_query']['data'] == '0' ) {
		$message = $data['callback_query']['data'];
		$chat_id = $data['callback_query']['from']['id'];
		$name = $data['callback_query']['from']['first_name'] . ' ' . $data['callback_query']['from']['last_name'];
	}
	
	elseif ( isset($data['message']['photo']) ) {
		$count = count($data['message']['photo']);
		$message = 'photo_' . $data['message']['photo'][$count-1]['file_id'];
		$chat_id = $data['message']['from']['id'];
		$name = $data['message']['from']['first_name'] . ' ' . $l_name = $data['message']['from']['last_name'];	
	}
	
	//Идентифицируем или создаем новую запись Участника с уникальным $chat_id
	$person_id = tg_get_person_id($chat_id, $name);
	$person_status = get_field('tg_status',$person_id);	


	//На этот хук вешаем обновление команд бота и сброс статуса участника
	if ($message == '/start') {
		do_action('tg_reset_hook', $chat_id, $message, $person_id);	
	}


	//Даем возможность изменить сообщение, если того потребует будущая логика бота
	$message = apply_filters('tg_convert_message_filter', $message);

	
	//В зависимости от статуса выполняем экшен, всю дальнейшую логику вешаем на отдельные функции
	if ($person_status) {
		do_action('tg_status_commands_hook', $chat_id, $message, $person_id, $person_status);
	} else {
		do_action('tg_commands_hook', $chat_id, $message, $person_id);
	}


	//Если команда не распознана, выполняем данный блок
	$text = __('К сожалению мы не поняли сообщение','tg_starter') . ' ' . $message;		
	tg_send($chat_id, $text);	


	//В конце возвращаем "ok", чтобы сообщить Телеграму, что запрос обработан
	exit('ok'); 
}



