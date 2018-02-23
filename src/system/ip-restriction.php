<?php
/**
 *
 * IP Restriction (IPv4)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-02-23
 *
 */


namespace st;


require_once __DIR__ . '/field.php';


class IpRestriction {

	const PMK_IP_RESTRICTION = '_ip_restriction';

	static private $_instance = null;
	static public function get_instance() {
		if ( self::$_instance === null ) self::$_instance = new IpRestriction();
		return self::$_instance;
	}


	// -------------------------------------------------------------------------

	private $_whites = [];
	private $_is_allowed = false;
	private $_added_body_classes = [];
	private $_checked = false;
	private $_post_types = [];

	private function __construct() {
		if ( is_admin() ) {
			add_action( 'post_submitbox_misc_actions', [ $this, '_cb_post_submitbox_misc_actions' ] );
			add_action( 'save_post',                   [ $this, '_cb_save_post' ], 10, 2 );
		} else {
			add_filter( 'body_class',    [ $this, '_cb_body_class' ] );
			add_action( 'pre_get_posts', [ $this, '_cb_pre_get_posts' ] );
		}
	}

	public function add_allowed_cidr( $cidr, $cls = false ) {
		$this->_whites[] = compact( 'cidr', 'cls' );
	}

	public function add_post_type( $post_type_s ) {
		if ( ! is_array( $post_type_s ) ) $post_type_s = [ $post_type_s ];
		foreach ( $post_type_s as $ps ) {
			if ( ! in_array( $ps, $this->_post_types, true ) ) {
				$this->_post_types[] = $ps;
			}
		}
	}

	public function is_allowed() {
		$this->_check_allowed();
		return $this->_is_allowed;
	}

	private function _check_allowed() {
		if ( $this->_checked ) return;

		$ip = $_SERVER['REMOTE_ADDR'];
		foreach ( $this->_whites as $w ) {
			$cls = $w['cls'];
			if ( $cls === false ) continue;
			$cidr = $w['cidr'];
			if ( $this->_in_cidr( $ip, $cidr ) ) {
				$this->_is_allowed = true;
				$this->_added_body_classes[] = $cls;
			}
		}
		$this->_checked = true;
	}

	private function _in_cidr( $ip, $cidr ) {
		list( $network, $mask_bit_len ) = explode( '/', $cidr );
		$host = 32 - $mask_bit_len;
		$net    = ip2long( $network ) >> $host << $host;
		$ip_net = ip2long( $ip )      >> $host << $host;
		return $net === $ip_net;
	}

	public function _cb_pre_get_posts( $query ) {
		if ( is_user_logged_in() || $this->is_allowed() ) return;

		$pts = $query->get( 'post_type', false );
		if ( $pts !== false ) {
			$filter = false;
			if ( ! is_array( $pts ) ) $pts = [ $pts ];
			foreach ( $pts as $pt ) {
				if ( in_array( $pt, $this->_post_types, true ) ) {
					$filter = true;
					break;
				}
			}
			if ( ! $filter ) return;
		}
		$meta_query = $query->get( 'meta_query', false );
		if ( $meta_query === false ) $meta_query = [];
		$meta_query[] = [
			'key'     => self::PMK_IP_RESTRICTION,
			'compare' => 'NOT EXISTS'
		];
		$query->set( 'meta_query', $meta_query );
	}

	public function _cb_body_class( $classes ) {  // Private
		$this->_check_allowed();

		foreach ( $this->_added_body_classes as $cls ) {
			$classes[] = $cls;
		}
		return $classes;
	}

	public function _cb_post_submitbox_misc_actions( $post ) {
		if ( ! in_array( $post->post_type, $this->_post_types, true ) ) return;

		wp_nonce_field( self::PMK_IP_RESTRICTION, self::PMK_IP_RESTRICTION . '_nonce' );
		$is_restricted = get_post_meta( $post->ID, self::PMK_IP_RESTRICTION, true );
		$_name = esc_attr( self::PMK_IP_RESTRICTION );
?>
		<div class="misc-pub-section">
			<span style="margin-left: 26px;">
				<label><input type="checkbox" name="<?php echo $_name ?>"<?php checked( $is_restricted, 'on' ) ?>/><?php esc_html_e( 'IP Restriction' ) ?></label>
			</span>
		</div>
<?php
	}

	public function _cb_save_post( $post_id, $post ) {
		if ( ! in_array( $post->post_type, $this->_post_types, true ) ) return;

		if ( ! isset( $_POST[ self::PMK_IP_RESTRICTION . '_nonce' ] ) ) return;
		if ( ! wp_verify_nonce( $_POST[ self::PMK_IP_RESTRICTION . '_nonce' ], self::PMK_IP_RESTRICTION ) ) return;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

		\st\field\save_post_meta( $post_id, self::PMK_IP_RESTRICTION );
	}

}
