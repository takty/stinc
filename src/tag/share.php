<?php
namespace st;
/**
 *
 * Share
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-10-15
 *
 */


require_once __DIR__ . '/image.php';
require_once __DIR__ . '/../util/text.php';
require_once __DIR__ . '/../util/url.php';


// Google Analytics ------------------------------------------------------------


function google_analytics_code( $id = '' ) {
	if ( empty( $id ) ) {
		if ( is_user_logged_in() ) _echo_no_analytics_warning();
	} else {
		_echo_analytics_code( $id );
	}
}

function _echo_no_analytics_warning() {
?>
<script>
document.addEventListener("DOMContentLoaded",function(){var e=document.createElement("div");
e.innerText="Google Analytics ID is not assigned!",e.style.position="fixed",
e.style.right="0",e.style.bottom="0",e.style.background="red",e.style.color="white",e.style.padding="4px",
e.style.zIndex=9999,document.body.appendChild(e),console.log("Google Analytics ID is not assigned!")});
</script>
<?php
}

function _echo_analytics_code( $id ) {
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $id ?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?php echo $id ?>');
</script>
<?php
}


// Structured Data -------------------------------------------------------------


function the_structured_data( $args = [], $same_as = [] ) {
	$args = array_merge( [
		'@context' => 'http://schema.org',
		'@type'    => 'Organization',
		'name'     => _get_structured_data_name(),
		'url'      => _get_structured_data_url(),
		'logo'     => '',
		'sameAs'   => $same_as
	], $args );
?>
<script type="application/ld+json"><?php echo json_encode( $args, JSON_UNESCAPED_UNICODE ); ?></script>
<?php
}

function _get_structured_data_name() {
	if ( class_exists( '\st\Multilang' ) ) {
		$site_name = \st\Multilang::get_instance()->get_bloginfo( 'name' );
	} else {
		$site_name = get_bloginfo( 'name' );
	}
	$site_name = implode( ' ', \st\separate_line( $site_name ) );
	return $site_name;
}

function _get_structured_data_url() {
	$ml = null;
	if ( class_exists( '\st\Multilang' ) ) {
		$ml = \st\Multilang::get_instance();
	}
	$mh = null;
	if ( class_exists( '\st\Multihome' ) ) {
		$mh = \st\Multihome::get_instance();
	}
	if ( ! isset( $ml ) && ! isset( $mh ) ) {
		return home_url();
	}
	if ( ! isset( $ml ) && isset( $mh ) ) {
		return home_url( $mh->get_site_slug() );
	}
	if ( isset( $ml ) && ! isset( $mh ) ) {
		return $ml->home_url();
	}
	return $ml->home_url( $mh->get_site_slug() );
}


// Open Graph Protocol ---------------------------------------------------------


function the_ogp( $logo_src = '', $image_meta_key = false, $alt_image_src = false ) {
	echo '<meta property="og:type" content="' . esc_attr( is_single() ? 'article' : 'website' ) . "\">\n";
	the_ogp_url();
	the_ogp_title();
	the_ogp_description();
	the_ogp_site_name();
	the_ogp_image( $logo_src, $image_meta_key, $alt_image_src );
}

function the_ogp_url() {
	echo '<meta property="og:url" content="' . esc_attr( get_the_ogp_url() ) . "\">\n";
}

function the_ogp_title() {
	echo '<meta property="og:title" content="' . esc_attr( get_the_ogp_title() ) . "\">\n";
}

function the_ogp_description() {
	echo '<meta property="og:description" content="' . esc_attr( get_the_ogp_description() ) . "\">\n";
}

function the_ogp_site_name() {
	echo '<meta property="og:site_name" content="' . esc_attr( get_the_ogp_site_name() ) . "\">\n";
}

function the_ogp_image( $logo_src, $image_meta_key, $alt_image_src ) {
	$src = get_the_ogp_image( $logo_src, $image_meta_key, $alt_image_src );
	if ( empty( $src ) ) return;
	echo '<meta property="og:image" content="' . esc_attr( $src ) . "\">\n";
}

function get_the_ogp_url() {
	if ( is_singular() ) {
		$url = get_permalink();
	} else {
		$url = \st\get_current_uri();
	}
	return $url;
}

function get_the_ogp_title( $append_site_name = false ) {
	$site_name = get_the_ogp_site_name();
	if ( _ogp_is_singular() ) {
		$title = implode( ' ', \st\separate_line( get_the_title() ) );
		if ( $append_site_name ) {
			$title .= ' - ' . $site_name;
		}
	} else if ( is_archive() ) {
		$title = post_type_archive_title( '', false );
		if ( $append_site_name ) {
			$title .= ' - ' . $site_name;
		}
	} else {
		$title = $site_name;
	}
	return $title;
}

function get_the_ogp_description() {
	$desc = '';
	if ( _ogp_is_singular() ) {
		if ( has_excerpt() ) {
			$desc = strip_tags( get_the_excerpt() );
		} else {
			global $post;
			$cont = strip_tags( strip_shortcodes( $post->post_content ) );
			$desc = str_replace( "\r\n", ' ', mb_substr( $cont, 0, 100 ) );
			if ( ! empty( trim( $desc ) ) && mb_strlen( $cont ) > 100 ) $desc .= '...';
		}
	}
	if ( empty( trim( $desc ) ) ) {
		if ( class_exists( '\st\Multihome' ) ) {
			$desc = \st\Multihome::get_instance()->get_bloginfo( 'description' );
		} else if ( class_exists( '\st\Multilang' ) ) {
			$desc = \st\Multilang::get_instance()->get_bloginfo( 'description' );
		} else {
			$desc = get_bloginfo( 'description' );
		}
	}
	if ( empty( trim( $desc ) ) ) {
		$desc = get_the_ogp_site_name();
	}
	return $desc;
}

function _ogp_is_singular() {
	$is_singular = false;

	if ( class_exists( '\st\Multihome' ) ) {
		$is_singular = is_singular() && ! \st\Multihome::get_instance()->is_front_page();
	} else if ( class_exists( '\st\Multilang' ) ) {
		$is_singular = is_singular() && ! \st\Multilang::get_instance()->is_front_page();
	} else {
		$is_singular = is_singular() && ! is_front_page();
	}
	return $is_singular;
}

function get_the_ogp_site_name() {
	if ( class_exists( '\st\Multihome' ) ) {
		$site_name = \st\Multihome::get_instance()->get_bloginfo( 'name' );
	} else if ( class_exists( '\st\Multilang' ) ) {
		$site_name = \st\Multilang::get_instance()->get_bloginfo( 'name' );
	} else {
		$site_name = get_bloginfo( 'name' );
	}
	$site_name = implode( ' ', \st\separate_line( $site_name ) );
	return $site_name;
}

function get_the_ogp_image( $logo_src = '', $meta_key = false, $alt_image_src = false ) {
	if ( $alt_image_src !== false ) return $alt_image_src;
	if ( ! is_singular() ) return $logo_src;
	global $post;
	$src = \st\get_thumbnail_src( 'large', $post->ID, $meta_key );
	if ( ! empty( $src ) ) return $src;

	$ais = \st\get_first_image_src( 'large' );
	if ( ! empty( $ais ) ) return $ais;
	return $logo_src;
}
