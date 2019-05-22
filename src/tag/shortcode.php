<?php
namespace st\shortcode;

/**
 *
 * Shortcode
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-05-22
 *
 */


add_action( 'init', function () {

	add_shortcode( 'child-page-nav', function () {
		ob_start();
?>
		<div>
			<ul class="child-page-nav">
				<li class="current"><span><?php the_title() ?></span></li>
				<?php \st\the_child_page_list( '', '', 'child-page-nav-link' ); ?>
			</ul>
		</div>
<?php
		return ob_get_clean();
	} );

	add_shortcode( 'sibling-page-nav', function () {
		global $post;
		$pid = $post->post_parent;
		$e_href = esc_attr( get_permalink( $pid ) );
		$e_title = esc_html( get_the_title( $pid ) );
		ob_start();
?>
		<nav class="navigation sibling-page-navigation">
			<div class="nav-links">
				<ul class="sibling-page-nav">
					<li><a class="sibling-page-nav-link" href="<?php echo $e_href ?>"><?php echo $e_title ?></a></li>
					<?php \st\the_sibling_page_list( '', '', 'sibling-page-nav-link' ); ?>
				</ul>
			</div>
		</nav>
<?php
		return ob_get_clean();
	} );

	add_shortcode( 'child-page-list', function () {
		ob_start();
		\st\the_child_page_list( '<ul class="list-item-page">', '</ul>', 'item-page' );
		return ob_get_clean();
	} );

	add_shortcode( 'latest-post-list', function ( $atts ) {
		$atts = shortcode_atts([
			'taxonomy' => 'category',
			'term' => '',
			'post_type' => 'post',
		], $atts );

		$tq = [ [
			'taxonomy' => $atts['taxonomy'],
			'field' => 'slug',
			'terms' => $atts['term']
		] ];
		if ( class_exists( '\st\Multilang' ) ) {
			$ml = \st\Multilang::get_instance();
			$tq[] = [
				'taxonomy' => 'post_lang',
				'field' => 'slug',
				'terms' => $ml->get_site_lang()
			];
		}
		$ps = get_posts( [
			'post_type' => $atts['post_type'],
			'posts_per_page' => 6,
			'tax_query' => $tq
		] );
		if ( count( $ps ) === 0 ) return;

		ob_start();
		echo '<ul class="list-item-post">';
		\st\the_loop_posts( 'template-parts/item', 'post', $ps );
		echo '</ul>';
		return ob_get_clean();
	} );




	// -------------------------------------------------------------------------




	add_shortcode( 'instagram', function ( $atts ) {
		extract( shortcode_atts( [ 'url' => '', 'width' => '' ], $atts ) );
		ob_start();
		if ( isset( $width ) && $width !== '' ) {
			echo '<div style="max-width:' . $width . 'px">';
			echo '<style>iframe.instagram-media{min-width:initial!important;}</style>';
		}
?>
		<blockquote class="instagram-media" data-instgrm-version="12" style="max-width:99.5%;min-width:300px;width:calc(100% - 2px);display:none;">
			<a href="<?php echo $url ?>"></a>
		</blockquote>
<?php
		if ( isset( $width ) && $width !== '' ) echo '</div>';
		return ob_get_clean();
	} );

	add_action( 'wp_enqueue_scripts', function () {
		global $post;
		if ( $post && has_shortcode( $post->post_content, 'instagram' ) ) {
			wp_enqueue_script( 'instagramjs', '//platform.instagram.com/en_US/embeds.js' );
		}
	} );
} );
