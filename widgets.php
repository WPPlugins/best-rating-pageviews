<?php if ( ! defined('ABSPATH') ) { exit; } // Защита от прямого вызова скрипта
/* ВИДЖЕТ РЕЙТИНГА ПОСТОВ В КОНСЛОЕ */
/* 
*	brpv_pageviews - число просмотров страницы
*	brpv_total_rating - итоговая оценка
*	brpv_golosov - число проголосовавших
*	brpv_ballov - сумма баллов
*/
add_action('wp_dashboard_setup', 'brpvrating_widgets');
function brpvrating_widgets() {
 global $wp_meta_boxes;
 wp_add_dashboard_widget('brpvrating_widget', __( 'Rating & PageViews', 'brpv'), 'brpv_rating_widgets_info');
}
function brpv_rating_widgets_info() {
 if (isset($_GET['meta_key']) and ($_GET['meta_key'] !== '')) {$brpv_meta_key = strip_tags($_GET['meta_key']);} else {$brpv_meta_key = 'brpv_total_rating';}
 if (isset($_GET['orderby']) and ($_GET['orderby'] !== '')) {$brpv_orderby = strip_tags($_GET['orderby']);
	if ($_GET['orderby'] == 'brpv_pageviews') {
		$brpv_orderby = 'meta_value_num'; // чтобы работала числовая сортировка
	} else {$brpv_orderby = $brpv_meta_key;} 
 } else {$brpv_orderby = 'brpv_total_rating';}
 if (isset($_GET['order']) and ($_GET['order'] !== '')) {$brpv_order = strip_tags($_GET['order']);} else {$brpv_order = 'asc';}
 $args = array(
	'meta_key' => $brpv_meta_key,
	'post_type' => array('post', 'page'),
	'posts_per_page' => 25,
	'orderby' => $brpv_orderby,
	'order' => strtoupper($brpv_order), // 'ASC',		
	'post_status' => 'publish',
 );
 $brpv = new WP_Query($args);
 if($brpv->have_posts()) : ?>
	<style>#brpv th.sortable a span {float: right;}</style>
	<table id="brpv" class="wp-list-table widefat fixed striped pages">	
		<thead>
			<tr>
				<th class="column-title"><?php _e('Title', 'brpv'); ?>:</th>
				<th class="sortable <?php if ($brpv_order == "desc") {echo "asc";} else {echo 'desc';} ?>"><a href="?meta_key=brpv_total_rating&orderby=brpv_total_rating&order=<?php if ($brpv_order == "desc") {echo "asc";} else {echo 'desc';} ?>"><span class="sorting-indicator"></span><?php _e('Rating', 'brpv'); ?>:</a>
				</th>
				<th class="sortable <?php if ($brpv_order == "desc") {echo "asc";} else {echo 'desc';} ?>">			
				<a href="?meta_key=brpv_pageviews&orderby=brpv_pageviews&order=<?php if ($brpv_order == "desc") {echo "asc";} else {echo 'desc';} ?>"><?php _e('PageViews', 'brpv'); ?>:<span class="sorting-indicator"></span></a>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php while($brpv->have_posts()){ 
			$brpv->the_post(); 
			$postId = get_the_ID(); ?>
			<tr>
				<td><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					<div class="row-actions"><span class="edit">
						<a href="/wp-admin/post.php?post=<?php echo $postId ?>&action=edit"><?php _e('Edit', 'brpv'); ?></a>
					</span></div>				
				</td>
				<td><?php echo get_post_meta($postId, 'brpv_total_rating', true); ?> / <?php echo get_post_meta($postId, 'brpv_golosov', true); ?></td>
				<td><?php echo get_post_meta($postId, 'brpv_pageviews', true); ?></td>
			</tr><?php
		} ?>
		</tbody>
	</table>
 <?php endif;
 wp_reset_postdata(); // восстанавливаем глобальную переменную $post 
}
/* END ВИДЖЕТ РЕЙТИНГА ПОСТОВ В КОНСЛОЕ */
/* -------------------------------------------------------------------------------------- */
/* ВИДЖЕТ ПОПУЛЯРНОЕ */
class brpv_widget_popular extends WP_Widget {
 public function __construct() {
	parent::__construct("text_widget", __( 'Popular', 'brpv' ),
		array( 'description' => __( 'Shows popular posts and pages based on the rating and pageviews', 'brpv' ), ));
 }	
 //Метод form() (отвечает за внешний вид виджета в админке)
 public function form($instance) {
	$title = __( 'Popular', 'brpv' ); // дефольный заголовок
	$NumPostov = "5"; // дефолтное число постов
	$WhatShows = "post";
	$order = "ASC";
	$orderby = "brpv_pageviews";
	// если instance не пустой, достанем значения
	if (!empty($instance)) {
		$title = $instance["title"];
		$NumPostov = $instance["NumPostovId"];
		$WhatShows = $instance["WhatShowsId"];
		$order = $instance["orderId"];
		$orderby = $instance["orderbyId"];
	}
		
	/* вытаскиваем первый параметр (заголовок виджета) */
	$tableId = $this->get_field_id("title");
	$tableName = $this->get_field_name("title");
	echo '<p><label for="' . $tableId . '">'.__( "Title", "brpv" ).':</label>';
	echo '<input class="widefat" id="' . $tableId . '" type="text" name="' .
	$tableName . '" value="' . $title . '"></p>';
		
	/* вытаскиваем второй параметр (число постов в виджете) */
	$NumPostovId = $this->get_field_id("NumPostovId");
	$NumPostovName = $this->get_field_name("NumPostovId");
	echo '<p><label for="' . $NumPostovId . '">'.__( "Num Posts", "brpv" ).': </label><input class="tiny-text" size="3" step="1" min="1" id="' . $NumPostovId . '" type="number" name="' .
	$NumPostovName . '" value="' . $NumPostov . '"></p>';
	
	/* вытаскиваем третий параметр (что выводить) */
	$WhatShowsId = $this->get_field_id("WhatShowsId");
	$WhatShowsName = $this->get_field_name("WhatShowsId");?>
	<p><label for="<?php echo $WhatShowsId; ?>"><?php _e( 'Show', 'brpv' ); ?>:</label>
	<select id="<?php $WhatShowsId; ?>" class="widefat" name="<?php
	echo $WhatShowsName; ?>">
		<option value="post" <?php echo ($WhatShows == 'post') ? ' selected="selected"' : '' ?>><?php _e( 'Post', 'brpv' ); ?></option>
		<option value="page" <?php echo ($WhatShows == 'page') ? ' selected="selected"' : '' ?>><?php _e( 'Page', 'brpv'); ?></option>
	</select></p>	
	<?php
	
	/* вытаскиваем четвертый параметр (сортировка) */
	$orderId = $this->get_field_id("orderId");
	$orderName = $this->get_field_name("orderId"); ?>
	<p><label for="<?php echo $orderId; ?>"><?php _e( 'Order', 'brpv' ); ?>:</label>
	<select id="<?php $orderId; ?>" class="widefat" name="<?php
	echo $orderName; ?>">
		<option value="ASC" <?php echo ($order == 'ASC') ? ' selected="selected"' : '' ?>><?php _e( 'ASC', 'brpv' ); ?></option>
		<option value="DESC" <?php echo ($order == 'DESC') ? ' selected="selected"' : '' ?>><?php _e( 'DESC', 'brpv'); ?></option>
	</select></p>	
	<?php 
	
	/* вытаскиваем пятый параметр (ключ сортировки) */
	$orderbyId = $this->get_field_id("orderbyId");
	$orderbyName = $this->get_field_name("orderbyId"); ?>
	<p><label for="<?php echo $orderbyId; ?>"><?php _e( 'Order by', 'brpv' ); ?>:</label>
	<select id="<?php $orderbyId; ?>" class="widefat" name="<?php
	echo $orderbyName; ?>">
		<option value="brpv_pageviews" <?php echo ($orderby == 'brpv_pageviews') ? ' selected="selected"' : '' ?>><?php _e( 'PageViews', 'brpv' ); ?></option>
		<option value="brpv_total_rating" <?php echo ($orderby == 'brpv_total_rating') ? ' selected="selected"' : '' ?>><?php _e( 'Rating', 'brpv'); ?></option>
	</select></p>	
	<?php 
 }
 //Метод update() (отвечает за обновление параметров)
 public function update($newInstance, $oldInstance) {
	$values = array();
	$values["title"] = htmlentities($newInstance["title"]); // обновляем заголовок
	$values["NumPostovId"] = htmlentities($newInstance["NumPostovId"]); // обновляем число постов
	$values["WhatShowsId"] = htmlentities($newInstance["WhatShowsId"]); // обновляем что выводить
	$values["orderId"] = htmlentities($newInstance["orderId"]); // обновляем сортировку
	$values["orderbyId"] = htmlentities($newInstance["orderbyId"]); // обновляем ключ сортировки
	return $values;
 }
	
 //Метод widget() (отвечает за вывод виджета на сайте)
 public function widget($args, $instance) {
	/* получение параметров */
	$title = $instance["title"]; // получаем заголовок
	$NumPostov = $instance["NumPostovId"]; //получаем число постов
	$WhatShows = $instance["WhatShowsId"]; // что выводить
	$order = $instance["orderId"]; // сортировка
	$orderby = $instance["orderbyId"]; // ключ сортировки
		
	echo $args['before_widget']; // вывод обертки виджета (открывающий тег)
	/* Выводт виджета */
	if (!empty( $title )) { echo $args['before_title'] . $title . $args['after_title'];} // выводим заголовок виджета в оберткие $args['after_title']
	
	$args = array(
		'meta_key' => $orderby,
		'post_type' => array($WhatShows),
		'showposts' => $NumPostov,
		'posts_per_page' => -1,
		'orderby' => $orderby,
		'order' => $order,
		'post_status' => 'publish',
	);
	$t_dir = get_bloginfo('template_directory'); // в $t_dir храним урл директории шаблона
	query_posts($args);
	$brpv = new WP_Query($args);
	if($brpv->have_posts()) : ?>
		<ul>
			<?php while($brpv->have_posts()):
				$brpv->the_post();
				$postId = get_the_ID(); ?>
				<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
			<?php endwhile; ?>
		</ul>
	<?php endif;
	wp_reset_postdata(); // восстанавливаем глобальную переменную $post
	/* End Выводт виджета*/
	echo $args['after_widget']; // вывод обертки виджета (закрывающий тег)
 }
}
add_action("widgets_init", function () {
 register_widget("brpv_widget_popular");
});
/* END ВИДЖЕТ ПОПУЛЯРНОЕ */
?>