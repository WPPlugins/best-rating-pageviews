<?php
if( ! defined('WP_UNINSTALL_PLUGIN') ) exit;
// проверка пройдена успешно. Начиная от сюда удаляем опции и все остальное.
function brpv_delete_plugin() {
	global $wpdb; // подключаем класс wordpress для работы с БД

	delete_option('brpv_version');
	delete_option('brpv_debug'); // включает режим отладки
	delete_option('brpv_not_count_bots'); // опция не показывать ботов
}
brpv_delete_plugin(); // стартуем функцию удаления плагина
?>