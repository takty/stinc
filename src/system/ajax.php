<?php
namespace st;

/**
 *
 * Ajax
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2017-09-10
 *
 */


if (!class_exists('Ajax')) :

class Ajax {

	private $action;
	private $response;
	private $nonce;

	function __construct( $action, $response, $public = false, $nonce = null ) {
		if ( ! preg_match( '/^[a-zA-Z0-9_\-]+$/', $action ) ) {
			wp_die( "Invalid string for \$action." );
		}
		$this->action   = $action;
		$this->response = $response;
		$this->nonce    = ( $nonce === null ) ? $action : $nonce;

		add_action( 'wp_ajax_' . $action, array( $this, 'do_ajax_action' ) );
		if ( $public ) {
			add_action( 'wp_ajax_nopriv_' . $action, array( $this, 'do_ajax_action' ) );
		}
	}

	public function get_url( $query = array() ) {
		$query['action'] = $this->action;
		$query['nonce']  = wp_create_nonce( $this->nonce );

		$url = admin_url( 'admin-ajax.php' );
		foreach ( $query as $key => $value ) {
			$url = add_query_arg( $key, $value, $url );
		}
		return $url;
	}

	public function do_ajax_action() {  // must be public for the use of action
		check_ajax_referer( $this->nonce, 'nonce' );
		nocache_headers();

		$res = call_user_func( $this->response );
		if ( is_array( $res ) ) {
			wp_send_json( $res );
		} else {
			echo $res;
			die;
		}
	}

}

endif;
