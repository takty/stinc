<?php
/**
 * Analytics
 *
 * @package Wpinc Social
 * @author Takuto Yanagida
 * @version 2021-03-28
 */

namespace wpinc\social\analytics;

/**
 * Output google analytics code.
 *
 * @param string $tracking     The tracking ID of analytics code.
 * @param string $verification The verification code.
 */
function the_google_analytics_code( string $tracking = '', string $verification = '' ) {
	if ( empty( $tracking ) ) {
		if ( is_user_logged_in() ) {
			_echo_warning();
		}
	} else {
		_echo_google_analytics_code( $tracking, $verification );
	}
}

/**
 * Output warning.
 *
 * @access private
 */
function _echo_warning() {
	?>
	<script>
	window.addEventListener('load',()=>{
		const e=document.body.appendChild(document.createElement('div'));
		e.innerText='No analytics code is set!';const s=e.style;
		s.position='fixed';s.right='0';s.bottom='0';s.background='red';
		s.color='white';s.padding='4px';s.zIndex=9999;console.log(e.innerText);
	});
	</script>
	<?php
}

/**
 * Output google analytics code.
 *
 * @access private
 *
 * @param string $tracking     The tracking ID of analytics code.
 * @param string $verification The verification code.
 */
function _echo_google_analytics_code( string $tracking, string $verification ) {
	// phpcs:disable
	?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $tracking ); ?>"></script>
	<?php
	// phpcs:enable
	?>
	<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('config', '<?php echo esc_attr( $tracking ); ?>');
	</script>
	<?php if ( ! empty( $verification ) ) : ?>
	<meta name="google-site-verification" content="<?php echo esc_attr( $verification ); ?>">
	<?php endif; ?>
	<?php
}
