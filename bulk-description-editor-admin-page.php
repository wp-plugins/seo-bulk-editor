<?php
	/*
	 *
	 *
	 *
	 *
	 */

	if( !class_exists( 'SEO_Bulk_Editor_Description_List_Table' ) ) {
		require_once( 'class-seo-bulk-editor-description-list-table.php' );
	}

	$seo_bulk_editor_description_table = new SEO_Bulk_Editor_Description_List_Table();

	if ( ! empty($_REQUEST['_wp_http_referer']) ) {
		wp_redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI']) ) );
		exit;
	}

	$seo_bulk_editor_description_table->prepare_items();
?>

<div class="wrap seo_bulk_editor_table_page">
	<div id="icon-edit-pages" class="icon32 icon32-posts-page"></div>
	<h2>SEO Bulk Description Editor (for All-in-One SEO)</h2>

	<?php $seo_bulk_editor_description_table->views(); ?>

	<?php $seo_bulk_editor_description_table->display(); ?>

</div>