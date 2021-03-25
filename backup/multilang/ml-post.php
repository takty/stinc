<?php
namespace st;
/**
 *
 * Multi-Language Site with Single Site (Post)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-02-27
 *
 */


class Multilang_Post {

	private $_core;
	private $_key_title = '';
	private $_key_content = '';
	private $_post_types = [];

	public function __construct( $core,  $key_prefix = '_' ) {
		$this->_core = $core;
		$this->_key_title   = $key_prefix . 'post_title_';
		$this->_key_content = $key_prefix . 'post_content_';
	}

	public function add_post_type( $post_type_s ) {
		if ( ! is_array( $post_type_s ) ) $post_type_s = [ $post_type_s ];

		if ( empty( $this->_post_types ) ) $this->_add_hooks();
		foreach ( $post_type_s as $post_type ) {
			add_action( "save_post_$post_type", [$this, '_cb_save_post_lang'], 10, 2 );
		}
		$this->_post_types = array_merge( $this->_post_types, $post_type_s );
	}


	// Private Functions -------------------------------------------------------

	private function _add_hooks() {
		add_action( 'admin_head', [ $this, '_cb_admin_head' ] );
		add_action( 'admin_menu', [ $this, '_cb_admin_menu' ] );

		add_filter( 'single_post_title', [ $this, '_cb_single_post_title' ], 10, 2 );
		add_filter( 'the_title',         [ $this, '_cb_the_title' ], 10, 2 );
		add_filter( 'the_content',       [ $this, '_cb_the_content' ] );
	}

	public function _cb_save_post_lang( $post_ID, $post ) {  // Private
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_ID ) ) return;

		foreach ( $this->_core->get_site_langs( false ) as $lang ) {
			if ( ! isset( $_POST[ "post_{$lang}_nonce" ] ) ) continue;
			if ( ! wp_verify_nonce( $_POST[ "post_{$lang}_nonce" ], "post_$lang" ) ) continue;

			$title   = $_POST[ $this->_key_title   . $lang ];
			$content = $_POST[ $this->_key_content . $lang ];
			$title   = apply_filters(   'title_save_pre', $title );
			$content = apply_filters( 'content_save_pre', $content );
			update_post_meta( $post_ID, $this->_key_title   . $lang, $title );
			update_post_meta( $post_ID, $this->_key_content . $lang, $content );
		}
	}

	public function _cb_admin_head() {  // Private
	?><style>
		.st-multilang-title input {
			margin: 0 0 6px; padding: 3px 8px;
			width: 100%; height: 1.7em;
			font-size: 1.7em; line-height: 100%;
			background-color: #fff; outline: none;
		}
	</style><?php
	}

	public function _cb_admin_menu() {  // Private
		foreach ( $this->_post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			if ( $post_type_object === null ) continue;
			$post_type_name = $post_type_object->labels->name;
			$self = $this;
			foreach ( $this->_core->get_site_langs( false ) as $lang ) {
				add_meta_box(
					"post_$lang", "$post_type_name [$lang]",
					function () use ( $self, $lang ) { $self->_output_html( $lang ); },
					$post_type, 'advanced', 'high'
				);
			}
		}
	}

	public function _output_html( $lang ) {  // Private
		global $post;
		$title = esc_attr( get_post_meta( $post->ID, $this->_key_title . $lang, true ) );
		$title_name = $this->_key_title . $lang;
		$title_ph = apply_filters( 'enter_title_here', __( 'Add title' ), $post );
		wp_nonce_field( "post_$lang", "post_{$lang}_nonce" );
?>
	<div class="st-multilang-title" id="titlewrap_<?php echo $lang ?>">
		<input name="<?php echo $title_name ?>" id="title_<?php echo $lang ?>" type="text" size="30" value="<?php echo $title ?>"
			placeholder="<?php echo $title_ph ?>" spellcheck="true" autocomplete="off">
	</div>
<?php
		$content = get_post_meta( $post->ID, $this->_key_content . $lang, true );
		wp_editor( $content, $this->_key_content . $lang );
	}

	public function _cb_single_post_title( $title, $post ) {  // Private
		return $this->_get_title( $title, $post->ID, $post );
	}

	public function _cb_the_title( $title, $id ) {  // Private
		return $this->_get_title( $title, $id, get_post( $id ) );
	}

	private function _get_title( $title, $id, $post ) {
		if ( $post === null ) return $title;  // When $id is 0
		if ( ! in_array( $post->post_type, $this->_post_types, true ) ) return $title;
		$lang = $this->_core->get_site_lang();
		if ( $lang === $this->_core->get_default_site_lang() ) return $title;

		$t = get_post_meta( $id, $this->_key_title . $lang, true );
		if ( empty( $t ) ) return $title;

		$basic_title = $post->post_title;
		$basic_title = \capital_P_dangit( $basic_title );
		$basic_title = \wptexturize( $basic_title );
		$basic_title = \convert_chars( $basic_title );
		$basic_title = \trim( $basic_title );
		if ( empty( $basic_title ) ) return "$title $t";
		return preg_replace( '/' . preg_quote( $basic_title, '/' ) . '/u', $t, $title );
	}

	public function _cb_the_content( $content ) {  // Private
		$post = get_post();
		if ( ! in_array( $post->post_type, $this->_post_types, true ) ) return $content;
		$lang = $this->_core->get_site_lang();
		if ( $lang === $this->_core->get_default_site_lang() ) return $content;

		$c = get_post_meta( $post->ID, $this->_key_content . $lang, true );
		if ( empty( $c ) ) return $content;

		if ( post_password_required( $post ) ) {
			return get_the_password_form( $post );
		}

		remove_filter( 'the_content', [$this, '_cb_the_content'] );
		$c = apply_filters( 'the_content', $c );
		add_filter( 'the_content', [$this, '_cb_the_content'] );
		return str_replace( ']]>', ']]&gt;', $c );
	}

}
