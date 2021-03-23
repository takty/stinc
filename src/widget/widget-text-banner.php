<?php
/**
 * Text Banner Widget
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2021-03-23
 */

namespace st;

class Widget_Text_Banner extends \WP_Widget {

	static private $_template     = '';
	static private $_use_color    = true;
	static private $_use_bg_color = true;

	static public function set_template( $template ) {
		self::$_template = $template;
	}

	static public function set_color_used( $flag ) {
		self::$_use_color = $flag;
	}

	static public function set_background_color_used( $flag ) {
		self::$_use_bg_color = $flag;
	}

	protected $registered = false;

	public function __construct() {
		parent::__construct(
			'widget_text_banner',
			__( 'Text Banner' ),
			array(
				'classname'   => 'widget_text_banner',
				'description' => __( 'Text Banner' ),
			)
		);
	}

	public function _register_one( $number = -1 ) {
		parent::_register_one( $number );
		if ( $this->registered ) {
			return;
		}
		$this->registered = true;
		add_action( 'admin_print_scripts-widgets.php', array( $this, 'enqueue_admin_scripts' ) );
	}

	public function widget( $args, $instance ) {
		global $post;

		$title    = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$title    = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$link_url = ! empty( $instance['link_url'] ) ? $instance['link_url'] : '';
		$color    = ! empty( $instance['color'] ) ? $instance['color'] : '';
		$color_bg = ! empty( $instance['color_bg'] ) ? $instance['color_bg'] : '';

		$_title    = \st\separate_line( $title, 'br' );
		$_link_url = esc_attr( $link_url );
		$_color    = esc_attr( $color );
		$_color_bg = esc_attr( $color_bg );

		$output = str_replace(
			array( '%title%', '%link_url%', '%color%', '%color_bg%' ),
			array(
				$args['before_title'] . $_title . $args['after_title'],
				$_link_url,
				$_color,
				$_color_bg,
			),
			self::$_template
		);

		echo $args['before_widget'];
		echo $output;
		echo $args['after_widget'];
	}

	static private function is_color_code( $color ) {
		if ( preg_match( "/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/", $color ) ) {
			return true;
		}
		return false;
	}

	public function update( $new_instance, $old_instance ) {
		$new_instance = wp_parse_args(
			$new_instance,
			array(
				'title'    => '',
				'link_url' => '',
				'color'    => '',
				'color_bg' => '',
			)
		);
		$instance     = $old_instance;

		$instance['title']    = sanitize_text_field( $new_instance['title'] );
		$instance['link_url'] = sanitize_text_field( $new_instance['link_url'] );
		$instance['color']    = sanitize_text_field( $new_instance['color'] );
		$instance['color_bg'] = sanitize_text_field( $new_instance['color_bg'] );

		return $instance;
	}

	public function enqueue_admin_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	public function form( $instance ) {
		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'    => '',
				'link_url' => '',
				'color'    => '',
				'color_bg' => '',
			)
		);

		$id_title      = $this->get_field_id( 'title' );
		$id_link_url   = $this->get_field_id( 'link_url' );
		$id_color      = $this->get_field_id( 'color' );
		$id_color_bg   = $this->get_field_id( 'color_bg' );
		$name_title    = $this->get_field_name( 'title' );
		$name_link_url = $this->get_field_name( 'link_url' );
		$name_color    = $this->get_field_name( 'color' );
		$name_color_bg = $this->get_field_name( 'color_bg' );

		$_title    = esc_attr( $instance['title'] );
		$_link_url = esc_attr( $instance['link_url'] );
		$_color    = esc_attr( empty( $instance['color'] ) ? '#ffffff' : $instance['color'] );
		$_color_bg = esc_attr( empty( $instance['color_bg'] ) ? '#ffffff' : $instance['color_bg'] );

		?>
		<p>
			<label for="<?php echo $id_title; ?>"><?php _e( 'Title' ); ?>:</label>
			<input id="<?php echo $id_title; ?>" name="<?php echo $name_title; ?>" class="widefat title sync-input" type="text" value="<?php echo $_title; ?>">
		</p>
		<p>
			<label for="<?php echo $id_link_url; ?>"><?php _e( 'Link To' ); ?>:</label>
			<input id="<?php echo $id_link_url; ?>" name="<?php echo $name_link_url; ?>" class="widefat link sync-input" type="text" value="<?php echo $_link_url; ?>" placeholder="http://" pattern="((\w+:)?\/\/\w.*|\w+:(?!\/\/$)|\/|\?|#).*">
		</p>
		<?php
		if ( self::$_use_color ) {
			?>
			<p>
				<label for="<?php echo $id_color; ?>"><?php echo _e( 'Color' ); ?>:</label>
				<input id="<?php echo $id_color; ?>" name="<?php echo $name_color; ?>" class="widefat color-picker" type="text" value="<?php echo $_color; ?>">
			</p>
			<?php
		}
		if ( self::$_use_bg_color ) {
			?>
			<p>
				<label for="<?php echo $id_color_bg; ?>"><?php echo _e( 'Background Color' ); ?>:</label>
				<input id="<?php echo $id_color_bg; ?>" name="<?php echo $name_color_bg; ?>" class="widefat color-picker" type="text" value="<?php echo $_color_bg; ?>">
			</p>
			<?php
		}

		$code = "(function ($) {
			$(function () {
				function initColorPicker(widget) {
					var opts = {
						mode: 'hsl',
						defaultColor: false,
						change: function (e, ui) {
							$(e.target).val(ui.color.toString());
							$(e.target).trigger('change');
						},
						clear: function (e) { $(e.target).trigger('change'); }
					};
					widget.find('.color-picker').wpColorPicker(opts);
				}
				function onFormUpdate(event, widget) { initColorPicker(widget); }
				$(document).on('widget-added widget-updated', onFormUpdate);
				$(document).ready(function () {
					$('#widgets-right .widget:has(.color-picker)').each(function () { initColorPicker($(this)); });
				});
			});
		})(jQuery);";
		wp_add_inline_script( 'wp-color-picker', $code, 'after' );
	}

}
