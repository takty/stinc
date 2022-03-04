<?php
/**
 * Analytics
 *
 * @package Wpinc Socio
 * @author Takuto Yanagida
 * @version 2022-02-14
 */

namespace wpinc\socio;

/**
 * Outputs google analytics code.
 *
 * @param string $tracking     The tracking ID of analytics code.
 * @param string $verification The verification code.
 */
function the_google_analytics_code( string $tracking = '', string $verification = '' ): void {
	if ( empty( $tracking ) ) {
		if ( is_user_logged_in() ) {
			_echo_analytics_warning();
		}
	} else {
		_echo_google_analytics_code( $tracking, $verification );
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
 * Outputs google analytics code.
 *
 * @access private
 *
 * @param string $tracking     The tracking ID of analytics code.
 * @param string $verification The verification code.
 */
function _echo_google_analytics_code( string $tracking, string $verification ): void {
	?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $tracking ); ?>"></script><?php // phpcs:ignore ?>
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
