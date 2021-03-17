<?php
namespace st\shortcode;
/**
 *
 * Shortcode
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-17
 *
 */


require_once __DIR__ . '/../tag/navigation.php';


function add_page_navigation_shortcode() {
	add_action( 'init', function () {

		add_shortcode( 'child-page-nav', function ( $as ) {
			$as = shortcode_atts( [ 'style' => false ], $as );
			return \st\get_the_child_page_navigation( [ 'class' => $as['style'] ] );
		} );

		add_shortcode( 'sibling-page-nav', function ( $as ) {
			$as = shortcode_atts( [ 'style' => false ], $as );
			return \st\get_the_sibling_page_navigation( [ 'class' => $as['style'] ] );
		} );

	} );
}


// -----------------------------------------------------------------------------


function add_youtube_shortcode() {
	add_shortcode( 'youtube', function ( $atts ) {
		extract( shortcode_atts( [ 'id' => '', 'width' => '', 'aspect' => '16:9' ], $atts ) );
		if ( empty( $id ) ) return;
		$_id = esc_attr( $id );
		list( $defw, $defh ) = _extract_aspect_size( $aspect );

		ob_start();
		if ( ! empty( $width ) ) echo "<div style=\"max-width:{$width}px\">";
?>
		<iframe
			src="https://www.youtube.com/embed/<?php echo $_id ?>"
			width="<?php echo $defw ?>"
			height="<?php echo $defh ?>"
			frameborder="0"
			allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
			allowfullscreen
		></iframe>
<?php
		if ( ! empty( $width ) ) echo '</div>';
		return ob_get_clean();
	} );
}

function add_vimeo_shortcode() {
	add_shortcode( 'vimeo', function ( $atts ) {
		extract( shortcode_atts( [ 'id' => '', 'width' => '', 'aspect' => '16:9' ], $atts ) );
		if ( empty( $id ) ) return;
		$_id = esc_attr( $id );
		list( $defw, $defh ) = _extract_aspect_size( $aspect );

		ob_start();
		if ( ! empty( $width ) ) echo "<div style=\"max-width:{$width}px\">";
?>
		<iframe
			src="https://player.vimeo.com/video/<?php echo $_id ?>"
			width="<?php echo $defw ?>"
			height="<?php echo $defh ?>"
			frameborder="0"
			allow="autoplay; fullscreen"
			allowfullscreen
		></iframe>
<?php
		if ( ! empty( $width ) ) echo '</div>';
		return ob_get_clean();
	} );
}

function _extract_aspect_size( $aspect ) {
	$aw = 16;
	$ah = 9;
	if ( ! empty( $aspect ) ) {
		$as = explode( ':', $aspect );
		if ( count( $as ) === 2 ) {
			$w = floatval( $as[0] );
			$h = floatval( $as[1] );
			if ( $w !== 0 && $h !== 0 ) {
				$aw = $w;
				$ah = $h;
			}
		}
	}
	$defw = 1920;
	$defh = intval( $defw * $ah / $aw );
	return [ $defw, $defh ];
}

function add_instagram_shortcode() {
	add_shortcode( 'instagram', function ( $atts ) {
		extract( shortcode_atts( [ 'url' => '', 'width' => '' ], $atts ) );
		ob_start();
		if ( isset( $width ) && $width !== '' ) {
			echo "<div style=\"max-width:{$width}px\">";
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
}


// -----------------------------------------------------------------------------


function add_post_type_list_shortcode( $post_type, $taxonomy = false, $args = [] ) {
	if ( ! is_array( $args ) ) {  // for backward compatibility
		$args = [ 'year_date_function' => $args ];
	}
	$args = array_merge( [
		'year_date_function' => '\st\shortcode\get_item_year_date_news',
		'year_format'        => false,
	], $args );
	add_shortcode( $post_type . '-list', function ( $atts, $content ) use ( $post_type, $taxonomy, $args ) {
		$atts = shortcode_atts( [
			'term'                  => '',
			'taxonomy'              => $taxonomy,
			'style'                 => '',
			'heading'               => false,
			'year-heading'          => false,
			'latest'                => false,
			'sticky'                => false,
			'order'                 => 'desc',
			'date-after'            => '',
			'date-before'           => '',
			'echo-content-on-empty' => false,
		], $atts );
		$atts['order'] = strtolower( $atts['order'] );
		if ( ! empty( $atts['date-after'] ) ) {
			$atts['date-after'] = preg_replace( '/[^0-9]/', '', $atts['date-after'] );
			$atts['date-after'] = str_pad( $atts['date-after'], 8, '0' );
		}
		if ( ! empty( $atts['date-before'] ) ) {
			$atts['date-before'] = preg_replace( '/[^0-9]/', '', $atts['date-before'] );
			$atts['date-before'] = str_pad( $atts['date-before'], 8, '9' );
		}

		$terms = empty( $atts['term'] ) ? false : $atts['term'];
		$items = get_item_list( $post_type, $taxonomy, $terms, $atts['latest'], $atts['sticky'], $args['year_date_function'], $atts['date-after'], $atts['date-before'] );
		if ( empty( $items ) ) {
			if ( false !== $atts['echo-content-on-empty'] && ! empty( $content ) ) {
				return $content;
			}
			return '';
		}

		if ( $atts['order'] === 'asc' ) $items = array_reverse( $items );
		return echo_list( $atts, $items, $post_type, $args['year_format'] );
	} );
}

function get_item_list( $post_type, $taxonomy, $term_slug, $latest_count, $sticky, $year_date, $after, $before ) {
	$args = [];

	if ( $latest_count !== false && is_numeric( $latest_count ) ) {
		$latest_count = intval( $latest_count );
		if ( $term_slug ) $args = \st\append_tax_query( $taxonomy, $term_slug, $args );
		if ( $sticky ) {
			$ps = \st\get_custom_sticky_and_latest_posts( $post_type, $latest_count, true, $args );
		} else {
			$ps = \st\get_latest_posts( $post_type, $latest_count, true, $args );
		}
	} else {
		$args = \st\append_post_type_query( $post_type, -1 );
		$args = \st\append_ml_tag_query( $args );
		if ( $term_slug ) $args = \st\append_tax_query( $taxonomy, $term_slug, $args );
		$ps = get_posts( $args );
	}
	if ( count( $ps ) === 0 ) return [];

	$items = [];
	foreach ( $ps as $p ) {
		$title = esc_html( strip_tags( get_the_title( $p->ID ) ) );
		$cats  = \st\get_the_term_names( $p->ID, $taxonomy );
		$url   = esc_attr( get_the_permalink( $p->ID ) );
		list( $year, $date ) = call_user_func( $year_date, $p->ID );

		if ( $after && $date < $after ) continue;
		if ( $before && $before < $date ) continue;

		$type  = $post_type;
		$items[] = compact( 'title', 'cats', 'url', 'year', 'date', 'type', 'p' );
	}
	return $items;
}

function echo_list( $atts, $items, $pt, $year_format = false ) {
	ob_start();
	if ( $atts['heading'] !== false ) {
		$tag = get_item_list_heading( $atts['heading'] );
		$t = get_term_by( 'slug', $atts['term'], $atts['taxonomy'] );
		if ( $t !== false ) {
			echo "<$tag>" . esc_html( \st\get_term_name( $t ) ) . "</$tag>";
		}
	}
	if ( $atts['year-heading'] ) {
		$ac = [];
		foreach ( $items as $it ) {
			$year = $it['year'];
			if ( $year === false ) $year = '-';
			if ( ! isset( $ac[ $year ] ) ) $ac[ $year ] = [];
			$ac[ $year ][] = $it;
		}

		$subtag = get_item_list_heading( $atts['year-heading'] );

		if ( $year_format === false ) {
			if ( class_exists( '\st\Multilang' ) ) {
				$year_format = \st\Multilang::get_instance()->get_date_format( 'year' );
			} else {
				$year_format = _x( 'Y', 'yearly archives date format' );
			}
		}

		foreach ( $ac as $year => $items ) {
			if ( $subtag !== false ) {
				$year = $items[0]['year'];
				if ( $year !== false ) {
					$date = date_create_from_format( 'Y', $year );
					echo "<$subtag>" . esc_html( date_format( $date, $year_format ) ) . "</$subtag>";
				}
			}
			echo_item_list( $items, $atts['style'], $pt );
		}
	} else {
		echo_item_list( $items, $atts['style'], $pt );
	}
	return ob_get_clean();
}

function get_item_list_heading( $tag ) {
	if ( is_numeric( $tag ) ) {
		$l = intval( $tag );
		if ( 3 <= $l && $l <= 6 ) return "h$l";
	} else {
		return 'h3';
	}
}

function echo_item_list( $items, $style = '', $pt = '' ) {
	if ( $style === 'full' ) {
		$posts = array_map( function ( $it ) { return $it['p']; }, $items );
?>
	<ul class="list-item list-item-<?php echo $pt ?> shortcode">
		<?php \st\the_loop_posts( 'template-parts/item', $pt, $posts ); ?>
	</ul>
<?php
	} else {
		echo '<ul>';
		foreach ( $items as $it ) {
?>
		<li><a href="<?php echo $it['url'] ?>"><?php echo $it['title'] ?></a></li>
<?php
		}
		echo '</ul>';
	}
}


// -----------------------------------------------------------------------------


function get_item_year_date_news( $post_id ) {
	$year = intval( get_the_date( 'Y', $post_id ) );
	$date = intval( get_the_date( 'Ymd', $post_id ) );
	return [ $year, $date ];
}

function get_item_year_date_event( $post_id ) {
	$date_bgn = get_post_meta( $post_id, \st\event\PMK_DATE_BGN, true );
	$date_end = get_post_meta( $post_id, \st\event\PMK_DATE_END, true );
	if ( empty( $date_bgn ) && ! empty( $date_end ) ) {
		$date_bgn = $date_end;
	}
	if ( $date_bgn ) {
		$year = explode( '-', $date_bgn )[0];
		$date = intval( str_replace( '-', '', $date_bgn ) );
	} else {
		$year  = intval( get_the_date( 'Y', $post_id ) );
		$date  = intval( get_the_date( 'Ymd', $post_id ) );
	}
	return [ $year, $date ];
}
