<?php
namespace st\shortcode;

/**
 *
 * Shortcode
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-01-14
 *
 */


add_action( 'init', function () {

	add_shortcode( 'child-page-nav', function () {
	?>
		<div>
			<ul class="child-page-nav">
				<li class="current"><span><?php the_title() ?></span></li>
				<?php \st\the_child_page_list( '', '', 'child-page-nav-link' ); ?>
			</ul>
		</div>
	<?php
	} );

	add_shortcode( 'sibling-page-nav', function () {
		global $post;
		$pid = $post->post_parent;
		$e_href = esc_attr( get_permalink( $pid ) );
		$e_title = esc_html( get_the_title( $pid ) )
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
	} );

	add_shortcode( 'child-page-list', function () {
		\st\the_child_page_list( '<ul class="list-item-page">', '</ul>', 'item-page' );
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

		echo '<ul class="list-item-post">';
		\st\the_loop_posts( 'template-parts/item', 'post', $ps );
		echo '</ul>';
	} );

} );
