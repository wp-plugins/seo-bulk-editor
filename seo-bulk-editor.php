<?php
	/*
	 * Plugin Name: SEO Bulk Editor
	 * Description: View and edit All-in-One SEO Pack Titles on one page and Meta Descriptions on another.
	 * Author: NCiphers
	 * Author URI: http://www.nciphers.com
	 * Version: 1.0.0
	 */

	class SEO_Bulk_Editor {

		private $title_hook;
		private $desc_hook;

		function __construct() {
			$this->register_actions();
			$this->register_filters();
		}

		function register_actions() {
			add_action( 'admin_menu', array( $this, 'add_pages' ), 30 );
		}

		function register_filters() {
			add_filter( 'set-screen-option', array( $this, 'save_bulk_edit_options' ), 10, 3 );
		}

		function add_pages() {
				
			$this->title_hook = add_submenu_page(
				'options-general.php',//'wpseo_dashboard',
				'SEO Bulk Title Editor',
				'SEO Bulk Title Editor',
				'manage_options',
				'seo_bulk_editor_titles',
				array( $this, 'bulk_title_editor_page' )
			);

			$this->desc_hook = add_submenu_page(
				'options-general.php',//'wpseo_dashboard',
				'SEO Bulk Description Editor',
				'SEO Bulk Description Editor',
				'manage_options',
				'seo_bulk_editor_descriptions',
				array( $this, 'bulk_description_editor_page' )
			);

			add_action( "load-{$this->title_hook}", array( $this, 'bulk_edit_options' ) );
			add_action( "load-{$this->desc_hook}", array( $this, 'bulk_edit_options' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
		}

		function bulk_title_editor_page() {
			include( 'bulk-title-editor-admin-page.php' );
		}

		
		function bulk_description_editor_page() {
			include( 'bulk-description-editor-admin-page.php' );
		}
		

		function bulk_edit_options() {
			$option = 'per_page';
			$args = array(
				'label' => 'Posts',
				'default' => 10,
				'option' => 'seo_bulk_editor_posts_per_page'
			);
			add_screen_option( $option, $args );
		}

		function save_bulk_edit_options( $status, $option, $value ) {
			if( 'seo_bulk_editor_posts_per_page' == $option ) {
				return $value;
			}
		}

		function load_scripts( $hook ) {
			if( $this->title_hook == $hook || $this->desc_hook == $hook ) {

		        wp_register_style( 'seo-bulk-editor', plugins_url( '/seo-bulk-editor.css', __FILE__ ) );
		        wp_enqueue_style( 'seo-bulk-editor' );

				wp_enqueue_script( 'seo-bulk-editor', plugins_url( '/seo-bulk-editor.js', __FILE__ ), array('jquery') );
				wp_localize_script( 'seo-bulk-editor', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

			}
			
		}

	}

	if( is_admin() ) {
		new SEO_Bulk_Editor();
	}

	add_action( 'wp_ajax_seo_bulk_editor_save_title', 'SEO_Bulk_Editor_save_title' );
	add_action( 'wp_ajax_seo_bulk_editor_save_all_titles', 'SEO_Bulk_Editor_save_all_titles' );

	add_action( 'wp_ajax_seo_bulk_editor_save_desc', 'SEO_Bulk_Editor_save_description' );
	add_action( 'wp_ajax_seo_bulk_editor_save_all_descs', 'SEO_Bulk_Editor_save_all_descriptions' );

	function seo_bulk_editor_save_title() {

		$new_title = $_POST['new_title'] ;
		$id = intval( $_POST['seo_bulk_editor_post_id'] );
		$original_title = $_POST['existing_title'];

		$results = seo_bulk_editor_upsert_new_title( $id, $new_title, $original_title );

		echo json_encode( $results );
		die();
	}

	function seo_bulk_editor_save_all_titles() {
		global $wpdb;

		$new_titles = $_POST['titles'];
		$original_titles = $_POST['existing_titles'];

		$results = array();

		foreach( $new_titles as $id => $new_title ) {
			$original_title = $original_titles[ $id ];
			$results[] = seo_bulk_editor_upsert_new_title( $id, $new_title, $original_title );
		}
		echo json_encode( $results );

		die();
	}

	function seo_bulk_editor_upsert_new_title( $post_id, $new_title, $original_title) {

		$meta_key = '_aioseop_title'; //'_yoast_wpseo_title';
		$return_key = 'title';
		return SEO_Bulk_Editor_upsert_meta( $post_id, $new_title, $original_title, $meta_key, $return_key );
	}

	function seo_bulk_editor_save_description() {

		$new_metadesc = $_POST['new_metadesc'] ;
		$id = intval( $_POST['seo_bulk_editor_post_id'] );
		$original_metadesc = $_POST['existing_metadesc'];

		$results = seo_bulk_editor_upsert_new_description( $id, $new_metadesc, $original_metadesc );

		echo json_encode( $results );
		die();
	}

	function seo_bulk_editor_save_all_descriptions() {
		global $wpdb;

		$new_metadescs = $_POST['metadescs'];
		$original_metadescs = $_POST['existing_metadescs'];

		$results = array();

		foreach( $new_metadescs as $id => $new_metadesc ) {
			$original_metadesc = $original_metadescs[ $id ];
			$results[] = seo_bulk_editor_upsert_new_description( $id, $new_metadesc, $original_metadesc );
		}
		echo json_encode( $results );

		die();
	}

	function seo_bulk_editor_upsert_new_description( $post_id, $new_metadesc, $original_metadesc) {

		$meta_key = '_aioseop_description';//'_yoast_wpseo_metadesc';
		$return_key = 'metadesc';
		return seo_bulk_editor_upsert_meta( $post_id, $new_metadesc, $original_metadesc, $meta_key, $return_key );
	}

	function seo_bulk_editor_upsert_meta( $post_id, $new_meta_value, $orig_meta_value, $meta_key, $return_key ) {

		$res = update_post_meta( $post_id, $meta_key, $new_meta_value );

		return array(
			'status' => ( ( $res !== false ) ? 'success' : 'failure'),
			'post_id' => $post_id,
			"new_{$return_key}" => $new_meta_value,
			"original_{$return_key}" => $orig_meta_value,
			'results' => $res
		);
	}