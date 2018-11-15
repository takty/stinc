<?php
namespace st;

/**
 *
 * Link Picker (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-11-15
 *
 */


require_once __DIR__ . '/../system/field.php';
require_once __DIR__ . '/../tag/url.php';


class LinkPicker {

	const NS = 'st-link-picker';

	const CLS_TABLE     = self::NS . '-table';
	const CLS_ADD       = self::NS . '-add';

	const CLS_ITEM      = self::NS . '-item';
	const CLS_ITEM_TEMP = self::NS . '-item-template';
	const CLS_HANDLE    = self::NS . '-handle';
	const CLS_SEL       = self::NS . '-select';

	const CLS_URL       = self::NS . '-url';
	const CLS_TITLE     = self::NS . '-title';
	const CLS_DEL       = self::NS . '-delete';
	const CLS_POST_ID   = self::NS . '-post-id';

	static private $_instance = [];

	static public function get_instance( $key = false ) {
		if ( $key === false ) return reset( self::$_instance );
		if ( isset( self::$_instance[ $key ] ) ) return self::$_instance[ $key ];
		return new LinkPicker( $key );
	}

	static public function enqueue_script( $url_to = false ) {
		if ( $url_to === false ) $url_to = \st\get_file_uri( __DIR__ );
		$url_to = untrailingslashit( $url_to );
		if ( is_admin() ) {
			wp_enqueue_script( 'picker-link', $url_to . '/asset/lib/picker-link.min.js', [ 'wplink', 'jquery-ui-autocomplete' ] );
			wp_enqueue_script( self::NS, $url_to . '/asset/link-picker.min.js', [ 'picker-media', 'jquery-ui-sortable' ] );
			wp_enqueue_style(  self::NS, $url_to . '/asset/link-picker.min.css' );
		}
	}

	private $_key;
	private $_id;

	private $_is_internal_only = false;
	private $_max_count = false;

	public function __construct( $key ) {
		$this->_key = $key;
		$this->_id  = $key;
		self::$_instance[ $key ] = $this;
	}

	public function set_internal_only( $enabled ) {
		$this->_is_internal_only = $enabled;
		return $this;
	}

	public function set_max_count( $count ) {
		$this->_max_count = $count;
		return $this;
	}

	public function get_items( $post_id = false ) {
		if ( $post_id === false ) $post_id = get_the_ID();

		$skeys = [ 'title', 'url', 'post_id' ];
		$its = \st\field\get_multiple_post_meta( $post_id, $this->_key, $skeys );

		foreach ( $its as &$it ) {
			if ( isset( $it['url'] ) && is_numeric( $it['url'] ) ) {  // for Backward Compatibility
				$it['post_id'] = $it['url'];
			}
			if ( empty( $it['post_id'] ) || ! is_numeric( $it['post_id'] ) ) continue;
			$permalink = get_permalink( intval( $it['post_id'] ) );
			if ( $permalink !== false && $it['url'] !== $permalink ) {
				$it['url'] = $permalink;
			}
		}
		return $its;
	}


	// -----------------------------------------------------------------------------


	public function add_meta_box( $label, $screen, $context = 'advanced' ) {
		\add_meta_box( "{$this->_key}_mb", $label, [ $this, '_cb_output_html' ], $screen, $context );
	}

	public function save_meta_box( $post_id ) {
		if ( ! isset( $_POST["{$this->_key}_nonce"] ) ) return;
		if ( ! wp_verify_nonce( $_POST["{$this->_key}_nonce"], $this->_key ) ) return;
		$this->_save_items( $post_id );
	}


	// -------------------------------------------------------------------------


	public function _cb_output_html( $post ) {  // Private
		wp_nonce_field( $this->_key, "{$this->_key}_nonce" );
		$its = $this->get_items( $post->ID );
		if ( $this->_max_count ) $its = array_slice( $its, 0, min( $this->_max_count, count( $its ) ) );
?>
		<input type="hidden" id="<?php echo $this->_id ?>" name="<?php echo $this->_id ?>" value="" />
		<div class="<?php echo self::CLS_BODY ?>">
			<div class="<?php echo CLS_TABLE ?>">
<?php
		$this->_output_row( [], self::CLS_ITEM_TEMP );
		foreach ( $its as $it ) $this->_output_row( $it, self::CLS_ITEM );
?>
				<div class="<?php echo self::CLS_ADD_ROW ?>"><a href="javascript:void(0);" class="<?php echo self::CLS_ADD ?> button"><?php _e( 'Add Link', 'default' ) ?></a></div>
			</div>
			<script>document.addEventListener('DOMContentLoaded', function () {
				st_link_picker_initialize_admin('<?php echo $this->_id ?>', <?php echo $this->_is_internal_only ? 'true' : 'false' ?>, <?php echo $this->_max_count ? $this->_max_count : 'false' ?>);
			});</script>
		</div>
<?php
	}

	private function _output_row( $it, $cls ) {
		$_url     = isset( $it['url'] )     ? esc_attr( $it['url'] )     : '';
		$_title   = isset( $it['title'] )   ? esc_attr( $it['title'] )   : '';
		$_post_id = isset( $it['post_id'] ) ? esc_attr( $it['post_id'] ) : '';

		$ro = $this->_is_internal_only ? 'readonly="readonly"' : '';
?>
		<div class="<?php echo $cls ?>">
			<div>
				<div class="<?php echo self::CLS_HANDLE ?>">=</div>
				<label class="widget-control-remove <?php echo self::CLS_DEL_LAB ?>"><?php _e( 'Remove', 'default' ) ?><br /><input type="checkbox" class="<?php echo self::CLS_DEL ?>" /></label>
			</div>
			<div>
				<div>
					<span><?php _e( 'Title', 'default' ) ?>:</span>
					<input type="text" class="<?php echo self::CLS_TITLE ?> link-title" value="<?php echo $_title ?>" />
				</div>
				<div>
					<span><a href="javascript:void(0);" class="<?php echo self::CLS_URL_OPENER ?>">URL</a>:</span>
					<span>
						<input type="text" class="<?php echo self::CLS_URL ?> link-url" value="<?php echo $_url ?>" <?php echo $ro ?>/>
						<a href="javascript:void(0);" class="button <?php echo self::CLS_SEL ?>"><?php _e( 'Select', 'default' ) ?></a>
					</span>
				</div>
			</div>
			<input type="hidden" class="<?php echo self::CLS_POST_ID ?> link-post-id" value="<?php echo $_post_id ?>" />
		</div>
<?php
	}


	// -------------------------------------------------------------------------


	private function _save_items( $post_id ) {
		$skeys = [ 'title', 'url', 'post_id', 'delete' ];

		$its = \st\field\get_multiple_post_meta_from_post( $this->_key, $skeys );
		$its = array_filter( $its, function ( $it ) { return ! $it['delete'] && ! empty( $it['url'] ); } );
		$its = array_values( $its );

		if ( $this->_is_internal_only ) {
			foreach ( $its as &$it ) $this->_ensure_internal_link( $it );
		}
		$skeys = [ 'title', 'url', 'post_id' ];
		\st\field\update_multiple_post_meta( $post_id, $this->_key, $its, $skeys );
	}

	private function _ensure_internal_link( &$it ) {
		$pid = url_to_postid( $it['url'] );

		if ( empty( $it['post_id'] ) ) {
			if ( $pid === 0 ) {
				$p = get_page_by_title( $it['title'] );
				if ( $p !== null ) {
					$it['url'] = get_permalink( $p->ID );
					$it['post_id'] = $p->ID;
				}
			} else {
				$it['post_id'] = $pid;
			}
		} else {
			if ( $pid === 0 ) {
				$url = get_permalink( intval( $it['post_id'] ) );
				if ( $url === false ) {
					$p = get_page_by_title( $it['title'] );
					if ( $p !== null ) $it['url'] = get_permalink( $p->ID );
				} else {
					$it['url'] = $url;
				}
			} else if ( $pid !== intval( $it['post_id'] ) ) {
				$it['post_id'] = $pid;
			}
		}
	}
}


// -----------------------------------------------------------------------------


namespace st\link_picker;

function initialize( $key ) { return new \st\LinkPicker( $key ); }
function enqueue_script( $url_to = false ) { \st\LinkPicker::enqueue_script( $url_to ); }

function get_items( $key, $post_id = false ) { return \st\LinkPicker::get_instance( $key )->get_items( $post_id ); }
function set_internal_only( $key, $enabled ) { return \st\LinkPicker::get_instance( $key )->set_internal_only( $enabled ); }
function set_max_count( $key, $count ) { return \st\LinkPicker::get_instance( $key )->set_max_count( $count ); }

function add_meta_box( $key, $label, $screen, $context = 'advanced', $opts = [] ) {
	if ( isset( $opts['is_internal_only'] ) ) set_internal_only( $key, $opts['is_internal_only'] );
	if ( isset( $opts['max_count'] ) ) set_max_count( $key, $opts['max_count'] );
	\st\LinkPicker::get_instance( $key )->add_meta_box( $label, $screen, $context );
}
function save_meta_box( $post_id, $key ) { \st\LinkPicker::get_instance( $key )->save_meta_box( $post_id ); }
