<?php
/*
Plugin Name: Best Rating & Pageviews
Description: Add Star rating, pageviews and adds a tool for analyzing the effectiveness of content. Also this plugin adds a widget which shows popular posts and pages based on the rating and pageviews.
Tags: rating, star, stars, ratings, pageviews, widget, popular, analyzing, effectiveness, content, рейтинг, звездочки, звездный, просмотры, виджет, статистика, популярное, анализ, аналитика, эффективность, контент
Author: Maxim Glazunov
Author URI: http://icopydoc.ru
License: GPLv2
Version: 1.0.1
Text Domain: best-rating-pageviews
Domain Path: /languages/
*/
/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : djdiplomat@yandex.ru)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/* АКТИВАЦИЯ ПЛАГИНА */
 register_activation_hook(__FILE__, 'brpv_set_options'); // Хук при активации плагина
 function brpv_set_options() { // срабатывает при активации плагина
	global $wpdb; // класс wordpress для работы с БД
	// Устанавливаем опции по умолчанию (они будут храниться в таблице настроек WP)
	add_option('brpv_version', '1.0.0');
	add_option('brpv_debug', 'true'); // включает режим отладки
	add_option('brpv_not_count_bots', 'yes'); // Учитывать ботов?
 } 
/* END АКТИВАЦИЯ ПЛАГИНА */
/* -------------------------------------------------------------------------------------- */
/* ПОДКЛЮЧАЕМ ФАЙЛЫ ПЕРЕВОДА */
 add_action( 'plugins_loaded', 'brpv_load_plugin_textdomain' );
 function brpv_load_plugin_textdomain() {
	load_plugin_textdomain( 'brpv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 
 }
/* END ПОДКЛЮЧАЕМ ФАЙЛЫ ПЕРЕВОДА */
/* -------------------------------------------------------------------------------------- */
/* СИСТЕМНЫЙ БЛОК */
 define('brpv_DIR', plugin_dir_path(__FILE__)); // brpv_DIR содержит /home/p13555/www/site.ru/wp-content/plugins/crm-realtor/
 define('brpv_URL', plugin_dir_url(__FILE__)); // brpv_URL содержит http://site.ru/wp-content/plugins/crm-realtor/
/* END СИСТЕМНЫЙ БЛОК */
/* -------------------------------------------------------------------------------------- */
/* ПОДКЛЮЧЕНИЕ МОДУЛЕЙ */
 require_once brpv_DIR.'/widgets.php'; // Подключаем файл виджетов
// require_once brpv_DIR.'/shortcodes.php'; // Подключаем файл шорткодов
/* END ПОДКЛЮЧЕНИЕ МОДУЛЕЙ */
/* -------------------------------------------------------------------------------------- */
/* ПОДКЛЮЧЕНИЕ СКРИПТОВ 
 if (is_admin()) { // хуки для админки
 } else { // хуки для фронт-енда
	wp_enqueue_script( 'jquery' );
//	wp_register_script( 'brpv_rating', plugins_url('js/rating.js', __FILE__));
//	wp_enqueue_script( 'brpv_rating' );	
 }
/* END ПОДКЛЮЧЕНИЕ СКРИПТОВ */
/* -------------------------------------------------------------------------------------- */
/* РЕЙТИНГ */
/* Подключаем Аякс скрипт рейтинга */
add_action( 'wp_enqueue_scripts', function(){
 wp_enqueue_script( 'brpv_do_something', brpv_URL . 'js/rating.js', array( 'jquery' )); 
 wp_localize_script( 'brpv_do_something', 'brpvajax', array('brpvajaxurl' => admin_url( 'admin-ajax.php' )));
 wp_enqueue_script( 'brpv_jquery_cookiess', brpv_URL . 'js/jquery.cookies.js', array( 'jquery' ));
});
add_action('wp_ajax_brpv_do_something', 'brpv_do_something');
add_action('wp_ajax_nopriv_brpv_do_something', 'brpv_do_something');
/* end Подключаем Аякс скрипт рейтинга */
/* ------------------------------------------------------ */
/* Подключение таблицы стилей только для фронтенда */
function brpv_register_style_frontend() {
 wp_register_style( 'brpv_style', brpv_URL . 'css/rating.css', '', null, 'all' );
}
add_action( 'wp_enqueue_scripts', 'brpv_register_style_frontend' );
/* end Подключение таблицы стилей только для фронтенда */ 
/* ------------------------------------------------------ */
/* Функция Аякс обработчика рейтинга */
function brpv_do_something(){		
 $result = array(); //массив с результатами для функции обратного вызвова
 global $wpdb; // если обработчик работает с базой - нужно не забывать подключать этот класс
 $user_votes = $_REQUEST['user_votes']; // получаем оценку, которую поставил пользователь
 $postId = $_REQUEST['postId']; // id поста, которому поставили оценку
 
 if (get_post_meta($postId, 'brpv_golosov', true)) { 
	$golosov = (int)get_post_meta($postId, 'brpv_golosov', true); 
 } else {$golosov = (int)0;}
 if (get_post_meta($postId, 'brpv_ballov', true)) { 
	$ballov = (int)get_post_meta($postId, 'brpv_ballov', true); 
 } else {$ballov = (int)0;}
 if (get_post_meta($postId, 'brpv_total_rating', true)) {
	$total_rating = (int)get_post_meta($postId, 'brpv_total_rating', true);
 } else {$total_rating = (int)0;}
 
 $golosov_new = $golosov + 1; // нове значение проголосовавших
 $ballov_new = $ballov + $user_votes; // нове значение баллов
 $total_rating_new = $ballov_new / $golosov_new; // общая оценка
 $total_rating_new = round($total_rating_new, 2); // округляем до сотых
 
 update_post_meta($postId, 'brpv_golosov', $golosov_new);
 update_post_meta($postId, 'brpv_ballov', $ballov_new);
 update_post_meta($postId, 'brpv_total_rating', $total_rating_new);

 $result['user_votes'] = $user_votes;
 $result['postId'] = $postId;
 $result['golosov_old'] = $golosov;
 $result['golosov_new'] = $golosov_new;
 $result['ballov_old'] = $ballov;
 $result['ballov_new'] = $ballov_new;
 $result['total_rating_new'] = $total_rating_new;
 $result['total_rating_old'] = $total_rating;
 $result['status'] = "success";
 
 $result = json_encode($result);
 echo $result;
 die();
}
/* end Функция Аякс обработчика рейтинга */
/* ------------------------------------------------------ */
/* шорткод рейтинг поста */
 add_action('init', 'brpv_add_shortcode_ratings');
 function brpv_add_shortcode_ratings(){ 
	// [pageratings] - шорктод будет выглядеть так.
	add_shortcode('pageratings', 'brpv_pageratings_func');
 }
 function brpv_pageratings_func(){ 
	global $post;
	$postId = (int)$post->ID; // получаем id поста
	$ratingValue = get_post_meta($postId, 'brpv_total_rating', true);
	$ratingCount = get_post_meta($postId, 'brpv_golosov', true); // число голосов 
	wp_enqueue_style('brpv_style'); // вызываем таблицу стилей только там, где есть шорткод
	?>	
	<div style="display: none;" itemprop="aggregateRating" itemscope="" itemtype="http://schema.org/AggregateRating"><meta itemprop="bestRating" content="5"><meta itemprop="ratingValue" content="<?php echo $ratingValue; ?>"><meta itemprop="ratingCount" content="<?php echo $ratingCount; ?>"></div>	
	<div class="brpv_raiting_star_<?php echo $postId; ?>">
		<div class="raiting">
			<div class="raiting_blank"></div>
			<div class="raiting_hover"></div>
			<div class="raiting_votes"></div>
		</div>
		<div class="raiting_info"><img src="<?php echo brpv_URL.'img/'; ?>load.gif" /><strong><?php _e('Raiting', 'brpv'); ?>:</strong><span class="brpv_raiting_value"></span></div>
		<div style="display: none;" class="hidden" postid="<?php echo $postId; ?>" ratingvalue="<?php echo $ratingValue; ?>"></div>
	</div>	
 <?php }
/* end шорткод рейтинг поста */
/* END РЕЙТИНГ */
/* -------------------------------------------------------------------------------------- */
/* СЧЕТЧИК ПОСЕЩЕНИЙ СТРАНИЦЫ */
add_action('wp_head', 'brpv_pageviews');
function brpv_pageviews() {
 if (is_singular()) { // Функция объединяет в себе : is_single(), is_page(), is_attachment() и и произвольные типы записей.
	// если не учитываем ботов
	if (get_option('brpv_not_count_bots') == 'yes') {
		$useragent = $_SERVER['HTTP_USER_AGENT']; 
		$bot = "Bot\|robot\|Slurp\|yahoo";
		$notbot = "Mozilla\|Opera"; /* Браузеры кроме Opera представляются как Mozilla
		*	Напримерм, если к нам на сайт зашел человек, а не бот, то $useragent будет таким:
		*	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/51.0.2704.101 Safari/537.31 
		*/
		if (!preg_match("|$notbot|U", $useragent) || preg_match("|$bot|U", $useragent)) {
			brpv_add_pageviews(); 
		}
	} else {
		// если ботов пропускать не нужно - добавляем счетчик
		brpv_add_pageviews();
	}
 }
 return true;
}
/* Функция увеличения счетчика просмотров */
function brpv_add_pageviews() {
 global $user_ID, $post;		
 $postId = (int)$post->ID; // получаем id поста
 $pageviews = (int)get_post_meta($postId, 'brpv_pageviews', true); // получаем число постов
 update_post_meta($postId, 'brpv_pageviews', ($pageviews+1));
}
/* end Функция увеличения счетчика просмотров */
/* шорткод вывод счетчика просмотров */
 add_action('init', 'brpv_add_shortcode_pageviews');
 function brpv_add_shortcode_pageviews(){ 
	// [pageviews] - шорктод будет выглядеть так:
	add_shortcode('pageviews', 'brpv_pageviews_func');
 }
 function brpv_pageviews_func(){
	global $post;
	if (get_post_meta($post->ID, 'brpv_pageviews', true)) {
		echo get_post_meta($post->ID, 'brpv_pageviews', true); /*.$_SERVER['HTTP_USER_AGENT'];*/
	} else {
		echo "0";
	}
 }
/* end шорткод вывод счетчика просмотров */
/* END СЧЕТЧИК ПОСЕЩЕНИЙ СТРАНИЦЫ */
/* -------------------------------------------------------------------------------------- */
?>