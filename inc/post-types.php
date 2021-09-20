<?php

//Новый тип записи -- Участник
//Registering Custom Post Type -- Участник
function tg_register_post_types() {

	$labels = array(
		'name'                  => _x( 'Участники', 'Post Type General Name', 'tg_starter' ),
		'singular_name'         => _x( 'Участник', 'Post Type Singular Name', 'tg_starter' ),
		'menu_name'             => __( 'Участники', 'tg_starter' ),
		'name_admin_bar'        => __( 'Участники', 'tg_starter' ),
		'archives'              => __( 'Архив участников', 'tg_starter' ),
		'attributes'            => __( 'Атрибуты участника', 'tg_starter' ),
		'parent_item_colon'     => __( 'Родительский элемент', 'tg_starter' ),
		'all_items'             => __( 'Все участники', 'tg_starter' ),
		'add_new_item'          => __( 'Добавить нового участника', 'tg_starter' ),
		'add_new'               => __( 'Добавить нового', 'tg_starter' ),
		'new_item'              => __( 'Новый участник', 'tg_starter' ),
		'edit_item'             => __( 'Редактировать участника', 'tg_starter' ),
		'update_item'           => __( 'Обновить участника', 'tg_starter' ),
		'view_item'             => __( 'Посмотреть участника', 'tg_starter' ),
		'view_items'            => __( 'Посмотреть участников', 'tg_starter' ),
		'search_items'          => __( 'Искать участника', 'tg_starter' ),
		'not_found'             => __( 'Не найдены', 'tg_starter' ),
		'not_found_in_trash'    => __( 'Не найдены в удаленных', 'tg_starter' ),
		'featured_image'        => __( 'Фотография участника', 'tg_starter' ),
		'set_featured_image'    => __( 'Задать фотографию', 'tg_starter' ),
		'remove_featured_image' => __( 'Удалить фотографию', 'tg_starter' ),
		'use_featured_image'    => __( 'Использовать', 'tg_starter' ),
		'insert_into_item'      => __( 'Использовать для участника', 'tg_starter' ),
		'uploaded_to_this_item' => __( 'Загружено для участника', 'tg_starter' ),
		'items_list'            => __( 'Список участников', 'tg_starter' ),
		'items_list_navigation' => __( 'Навигация по участникам', 'tg_starter' ),
		'filter_items_list'     => __( 'Отсортировать список участников', 'tg_starter' ),
	);
	$args = array(
		'label'                 => __( 'Участник', 'tg_starter' ),
		'description'           => __( 'Post Type Description', 'tg_starter' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'thumbnail' ),
		'taxonomies'            => array( ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'tg_person', $args );

}
add_action( 'init', 'tg_register_post_types', 0 );
