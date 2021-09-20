<?php

//Получить объект клавиатуры на основе кнопок и параметров
function tg_get_keyboard($rows, $one_time=false, $resize=true) {
	
	$keyboard = array(
		'one_time_keyboard' => $one_time,
		'resize_keyboard' => $resize,
	  	'keyboard' => $rows
	);
	
	return $keyboard;	
}



//Получить дефолтную клавиатуру
//статическими клавиатурами из одного места
function tg_default_keyboard( $type='default', $one_time=false, $resize=true ) {

	$rows = array();
	$rows[] = array('Базовые функции','Квест-игра');
	$rows[] = array('Регистрация','Тестирование');
	$rows[] = array('Инфрмация о плагине');

	$keyboard = array(
		'one_time_keyboard' => $one_time,
		'resize_keyboard' => $resize,
	  	'keyboard' => $rows
	);
	
	return $keyboard;
}	



//Получить инлайн-клавиатуру
//$row = array();
//$row[] = array('text' => 'Кнопка-действие', 'callback_data' => 'check');
//$row[] = array('text' => 'Кнопка-url', 'url' => 'http://ya.ru' );
//$buttons = array($row);
function tg_inline_keyboard( $buttons ) {
	$keyboard = array(
		'inline_keyboard' => $buttons,
	);
	
	return $keyboard;
}



//Убрать кастомную клавиатуру
function tg_remove_keyboard($selective=false) {
	$remove_keyboard = array(
		'remove_keyboard' => true,
		'selective' => $selective
	);
	
	return $remove_keyboard;
}



//Обязательный ответ
function tg_force_reply($selective=false) {
	$force_reply = array(
		'force_reply' => true,
		'selective' => $selective
	);
	
	return $force_reply;
}