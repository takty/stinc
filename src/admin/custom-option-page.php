<?php
namespace st;
/**
 *
 * Custom Option Page
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-01-21
 *
 */


class CustomOptionPage {

	private $page_title;
	private $menu_title;
	private $menu_slug;
	private $option_key;
	private $sections;

	public function __construct( $page_title, $menu_title, $menu_slug, $option_key, $sections ) {
		$this->page_title = $page_title;
		$this->menu_title = $menu_title;
		$this->menu_slug  = $menu_slug;
		$this->option_key = $option_key;
		$this->sections   = $sections;
		add_action( 'admin_menu', [ $this, 'add_plugin_page' ] );
		add_action( 'admin_init', [ $this, 'page_init' ] );
	}

	public function add_plugin_page() {
		add_options_page(
			$this->page_title, 
			$this->menu_title, 
			'manage_options', 
			$this->menu_slug, 
			[ $this, 'create_admin_page' ]
		);
	}

	public function create_admin_page() {
		$this->options = get_option( $this->option_key );
?>
		<div class="wrap">
			<h2><?php echo $this->page_title ?></h2>
			<form method="post" action="options.php">
<?php
				settings_fields( $this->option_key );   
				do_settings_sections( $this->menu_slug );
				submit_button(); 
?>
			</form>
		</div>
<?php
	}

	public function page_init() {
		register_setting(
			$this->option_key,
			$this->option_key,
			[ $this, 'sanitize' ]
		);
		foreach ( $this->sections as $sid => $cont ) {
			add_settings_section(
				$sid,
				$cont['label'],
				null,
				$this->menu_slug
			);
			foreach ( $cont['fields'] as $key => $opts ) {
				add_settings_field(
					$key, $opts['label'],
					function () use ( $key, $opts ) {
						switch ( $opts['type'] ) {
							case 'text'    : $this->callback_input( $key, $opts['type'] ); break;
							case 'textarea': $this->callback_textarea( $key );             break;
						}
					}, 
					$this->menu_slug, $sid
				);
			}
		}
	}

	public function sanitize( $input ) {
		$new_input = [];

		foreach ( $this->sections as $sid => $cont ) {
			foreach ( $cont['fields'] as $key => $opts ) {
				$filter = $opts['filter'];
				if ( isset( $input[ $key ] ) ) {
					$new_input[ $key ] = $filter( $input[ $key ] );
				}
			}
		}
		return $new_input;
	}

	public function callback_input( $key, $type ) {
		printf(
			'<input type="' . $type . '" id="' . $key . '" name="' . $this->option_key . '[' . $key . ']" value="%s" size="100"/>',
			isset( $this->options[ $key ] ) ? esc_attr( $this->options[ $key ]) : ''
		);
	}

	public function callback_textarea( $key ) {
		printf(
			'<textarea  id="' . $key . '" name="' . $this->option_key . '[' . $key . ']" rows="10" cols="100">%s</textarea>',
			isset( $this->options[ $key ] ) ? esc_attr( $this->options[ $key ] ) : ''
		);
	}

}
