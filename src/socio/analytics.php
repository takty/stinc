<?php
/**
 * Analytics
 *
 * @package Wpinc Socio
 * @author Takuto Yanagida
 * @version 2023-01-21
 */

namespace wpinc\socio;

require_once __DIR__ . '/assets/asset-url.php';

/**
 * Outputs google analytics code.
 *
 * @param array  $args {
 *     Arguments.
 *
 *     @type string 'url_to'            URL to this script.
 *     @type string 'site_verification' The site verification code.
 *     @type string 'tag_id'            The google tag ID.
 *     @type bool   'do_show_dialog'    Whether to show the dialog.
 *     @type int    'expired_day'       The length of keeping settings.
 *     @type string 'id_dialog'         Element ID of the dialog. Defaults 'wpinc-socio-analytics-dialog'.
 *     @type string 'id_accept'         Element ID of the accept button. Defaults 'wpinc-socio-analytics-accept'.
 *     @type string 'id_reject'         Element ID of the reject button. Defaults 'wpinc-socio-analytics-reject'.
 * }
 * @param string $site_ver (Optional) The site verification code.
 */
function the_google_analytics_code( $args = array(), ?string $site_ver = null ): void {
	if ( is_array( $args ) ) {
		$args += array(
			'site_verification' => null,
			'tag_id'            => null,
			'do_show_dialog'    => false,
		);
	} else {  // For backward compatibility.
		$args = array(
			'site_verification' => $site_ver,
			'tag_id'            => (string) $args,
			'do_show_dialog'    => false,
		);
	}
	$url_to   = untrailingslashit( $args['url_to'] ?? \wpinc\get_file_uri( __DIR__ ) );
	$site_ver = $args['site_verification'];

	unset( $args['url_to'] );
	unset( $args['site_verification'] );

	if ( ! $args['tag_id'] ) {
		if ( is_user_logged_in() ) {
			_echo_analytics_warning();
		}
	} else {
		_echo_google_analytics_code( $url_to, $args );
		if ( $site_ver ) {
			_echo_google_site_verification( $site_ver );
		}
	}
}

/**
 * Outputs warning that indicates any analytics codes are assigned.
 *
 * @access private
 */
function _echo_analytics_warning(): void {
	?>
	<script>
	window.addEventListener('load', ()=>{
		const e=document.body.appendChild(document.createElement('div'));
		console.log(e.innerText='No google tag ID is set!');
		e.style='position:fixed;z-index:9999;inset:auto auto 0 0;padding:4px;line-height:1;background:red;color:#fff;';
	});
	</script>
	<?php
}

/**
 * Outputs google analytics code.
 *
 * @access private
 *
 * @param string $url_to URL to this script.
 * @param array  $args   Arguments or google tag ID.
 */
function _echo_google_analytics_code( string $url_to, array $args ): void {
	?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $args['tag_id'] ); ?>"></script><?php // phpcs:ignore ?>
	<?php
	wp_enqueue_script(
		'wpinc-socio-analytics',
		\wpinc\abs_url( $url_to, './assets/js/analytics.min.js' ),
		array(),
		filemtime( __DIR__ . '/assets/js/analytics.min.js' ),
		true
	);
	$json = wp_json_encode( $args );
	$data = "wpinc_socio_analytics_initialize($json);";
	wp_add_inline_script( 'wpinc-socio-analytics', $data, 'after' );
}

/**
 * Outputs site verification.
 *
 * @access private
 *
 * @param string $site_ver The verification code.
 */
function _echo_google_site_verification( string $site_ver ): void {
	?>
	<meta name="google-site-verification" content="<?php echo esc_attr( $site_ver ); ?>">
	<?php
}
