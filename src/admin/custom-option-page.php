<?php
/**
 * Custom Option Page
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-22
 */

namespace st;

class CustomOptionPage {

	private $page_title;
	private $menu_title;
	private $menu_slug;
	private $option_key;
	private $sections;
	private $as_menu_page;

	public function __construct( $page_title, $menu_title, $menu_slug, $option_key, $sections, $as_menu_page = false ) {
		$this->page_title   = $page_title;
		$this->menu_title   = $menu_title;
		$this->menu_slug    = $menu_slug;
		$this->option_key   = $option_key;
		$this->sections     = $sections;
		$this->as_menu_page = $as_menu_page;
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
	}

	public function add_plugin_page() {
		if ( $this->as_menu_page ) {
			add_menu_page(
				$this->page_title,
				$this->menu_title,
				'edit_pages',
				$this->menu_slug,
				array( $this, 'create_admin_page' )
			);
		} else {
			add_options_page(
				$this->page_title,
				$this->menu_title,
				'manage_options',
				$this->menu_slug,
				array( $this, 'create_admin_page' )
			);
		}
	}

	public function create_admin_page() {
		$this->options = get_option( $this->option_key );
		?>
		<div class="wrap">
			<h2><?php echo esc_html( $this->page_title ); ?></h2>
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
			array( $this, 'sanitize' )
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
					$key,
					$opts['label'],
					function () use ( $key, $opts ) {
						$desc = isset( $opts['description'] ) ? $opts['description'] : '';
						switch ( $opts['type'] ) {
							case 'checkbox':
								$this->callback_checkbox( $key, $desc );
								break;
							case 'textarea':
								$this->callback_textarea( $key, $desc );
								break;
							default:
								$this->callback_input( $key, $opts['type'], $desc );
								break;
						}
					},
					$this->menu_slug,
					$sid
				);
			}
		}
	}

	public function sanitize( $input ) {
		$new_input = array();

		foreach ( $this->sections as $sid => $cont ) {
			foreach ( $cont['fields'] as $key => $opts ) {
				if ( ! isset( $input[ $key ] ) ) {
					continue;
				}
				$filter = $opts['filter'];
				if ( $filter ) {
					$new_input[ $key ] = $filter( $input[ $key ] );
				} else {
					$new_input[ $key ] = $input[ $key ];
				}
			}
		}
		return $new_input;
	}

	public function callback_input( $key, $type, $desc = '' ) {
		$name = $this->option_key . '[' . $key . ']';
		printf(
			'<input type="' . $type . '" id="' . $key . '" name="' . $name . '" value="%s" class="regular-text" aria-describedby="' . $key . '-description">',
			isset( $this->options[ $key ] ) ? esc_attr( $this->options[ $key ] ) : ''
		);
		if ( ! empty( $desc ) ) {
			echo '<p class="description" id="' . $key . '-description">' . esc_html( $desc ) . '</p>';
		}
	}

	public function callback_checkbox( $key, $desc = '' ) {
		printf(
			'<label for="' . $key . '"><input type="checkbox" id="' . $key . '" name="' . $this->option_key . '[' . $key . ']" value="1" %s> ' . esc_html( $desc ) . '</label>',
			( isset( $this->options[ $key ] ) && '1' === $this->options[ $key ] ) ? 'checked' : ''
		);
	}

	public function callback_textarea( $key, $desc = '' ) {
		if ( ! empty( $desc ) ) {
			echo '<label for="' . $key . '">' . esc_html( $desc ) . '</label>';
		}
		$name = $this->option_key . '[' . $key . ']';
		printf(
			'<p><textarea id="' . $key . '" name="' . $name . '" rows="10" class="large-text" aria-describedby="' . $key . '-description">%s</textarea></p>',
			isset( $this->options[ $key ] ) ? esc_attr( $this->options[ $key ] ) : ''
		);
	}

}
