<?php
	/*
	 *
	 *
	 *
	 *
	 */

	if( !class_exists( 'SEO_Bulk_Editor_Title_List_Table' ) ) {
		require_once( 'class-seo-bulk-editor-title-list-table.php' );
	}

	$seo_bulk_editor_list_table = new SEO_Bulk_Editor_Title_List_Table();

	if ( ! empty($_REQUEST['_wp_http_referer']) ) {
		wp_redirect( remove_query_arg( array('_wp_http_referer', '_wpnonce'), stripslashes($_SERVER['REQUEST_URI']) ) );
		exit;
	}

	$seo_bulk_editor_list_table->prepare_items();
?>

<div class="wrap seo_bulk_editor_table_page">
	<div id="icon-edit-pages" class="icon32 icon32-posts-page"></div>
	<h2>SEO Bulk Title Editor (for All-in-One SEO)</h2>

	<?php $seo_bulk_editor_list_table->views(); ?>

	<?php $seo_bulk_editor_list_table->display(); ?>

</div>