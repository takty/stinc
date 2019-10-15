<?php
namespace st\basic;
/**
 *
 * Default Options
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-09
 *
 */


function update_reading_options() {
	update_option( 'show_on_front', 'page' );
	if ( empty( get_option( 'page_on_front' ) ) ) {
		$pages = get_pages( ['sort_column'  => 'post_id'] );
		if ( ! empty( $pages ) ) update_option( 'page_on_front', $pages[0]->ID );
	}
	update_option( 'page_for_posts', '' );
}

function update_discussion_options() {
	update_option( 'default_pingback_flag', 0 );
	update_option( 'default_ping_status', 0 );
	update_option( 'default_comment_status', 0 );
}

function update_media_options() {
	update_option( 'thumbnail_size_w', 320 );
	update_option( 'thumbnail_size_h', 320 );
	update_option( 'thumbnail_crop', 1 );
	update_option( 'medium_size_w', 640 );
	update_option( 'medium_size_h', 9999 );
	update_option( 'medium_large_size_w', 960 );
	update_option( 'medium_large_size_h', 9999 );
	update_option( 'large_size_w', 1280 );
	update_option( 'large_size_h', 9999 );
	update_option( 'uploads_use_yearmonth_folders', 1 );
}
