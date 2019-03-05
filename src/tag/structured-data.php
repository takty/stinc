<?php
namespace st;

/**
 *
 * Structured Data
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2019-03-05
 *
 */


require_once __DIR__ . '/text.php';


function the_structured_data( $args = [], $same_as = [] ) {
	$args = array_merge( [
		'@context' => 'http://schema.org',
		'@type'    => 'Organization',
		'name'     => get_structured_data_name(),
		'url'      => get_structured_data_url(),
		'logo'     => '',
		'sameAs'   => $same_as
	], $args );
?>
<script type="application/ld+json"><?php echo json_encode( $args, JSON_UNESCAPED_UNICODE ); ?></script>
<?php
}

function get_structured_data_name() {
	if ( class_exists( '\st\Multilang' ) ) {
		$site_name = \st\Multilang::get_instance()->get_bloginfo( 'name' );
	} else {
		$site_name = get_bloginfo( 'name' );
	}
	$site_name = implode( ' ', \st\separate_line( $site_name ) );
	return $site_name;
}

function get_structured_data_url() {
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
