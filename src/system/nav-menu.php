<?php
namespace st;

/**
 *
 * Nav Menu (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2018-03-10
 *
 * require tag/url.php
 *
 */


class NavMenu {

	const CLS_HOME     = 'home';
	const CLS_OPENED   = 'opened';
	const CLS_CURRENT  = 'current';
	const CLS_ANCESTOR = 'ancestor';

	private $_cur_url;
	private $_home_url;
	private $_is_page;
	private $_expanded_page_ids = false;

	private $_pid_to_menu;
	private $_pid_to_children_state;
	private $_id_to_attr;

	public function __construct( $menu_name, $expanded_page_ids = false ) {
		$this->_cur_url = trailingslashit( strtok( \st\get_current_uri(), '?' ) );

		$ml = null;
		if ( class_exists( '\st\Multilang' ) ) {
			$ml = \st\Multilang::get_instance();
		}
		$mh = null;
		if ( class_exists( '\st\Multihome' ) ) {
			$mh = \st\Multihome::get_instance();
		}
		$url = $mh ? $mh->home_url() : ( $ml ? $ml->home_url() : home_url() );
		$this->_home_url = trailingslashit( $url );

		$this->_is_page = is_page();
		$this->_expanded_page_ids = $expanded_page_ids;

		$mis = $this->_get_all_items( $menu_name );
		$this->_pid_to_menu = $this->_get_menus( $mis );
		$this->_pid_to_children_state = $this->_get_children_state( $this->_pid_to_menu );
		$this->_id_to_attr = $this->_get_attributes( $mis, $this->_pid_to_children_state );
	}

	public function set_expanded_page_ids( $ids ) {
		$this->_expanded_page_ids = $ids;
	}


	// -------------------------------------------------------------------------

	public function has_main_items() {
		return $this->has_items( 0 );
	}

	public function has_sub_items() {
		if ( empty( $this->_pid_to_menu[ 0 ] ) ) return false;
		$mis = $this->_pid_to_menu[ 0 ];

		foreach ( $mis as $mi ) {
			if ( ! empty( $this->_pid_to_menu[ $mi->ID ] ) ) return true;
		}
		return false;
	}

	public function get_main_item_ids() {
		return $this->get_item_ids( 0 );
	}

	public function echo_main_items( $before = '<ul class="menu">', $after = '</ul>', $filter = 'esc_html' ) {
		$this->echo_items( 0, $before, $after, $filter );
	}

	public function get_menu_id_with_current_url( $pid = 0 ) {
		if ( empty( $this->_pid_to_menu[ $pid ] ) ) return false;
		$mis = $this->_pid_to_menu[ $pid ];

		foreach ( $mis as $mi ) {
			$id = $mi->ID;
			if ( empty( $this->_pid_to_menu[ $id ] ) ) continue;
			if ( $this->_pid_to_children_state[ $id ] ) return $id;
		}
		return false;
	}

	public function get_menu_id_with_current_main_menu( $pid = 0 ) {
		if ( empty( $this->_pid_to_menu[ $pid ] ) ) return false;
		$mis = $this->_pid_to_menu[ $pid ];

		foreach ( $mis as $mi ) {
			$id = $mi->ID;
			if ( empty( $this->_pid_to_menu[ $id ] ) ) continue;
			$a = $this->_id_to_attr[ $id ];
			if ( in_array( self::CLS_OPENED, $a, true ) ) return $id;
			if ( in_array( self::CLS_CURRENT, $a, true ) ) return $id;
			if ( in_array( self::CLS_ANCESTOR, $a, true ) ) return $id;
		}
		return false;
	}


	// -------------------------------------------------------------------------

	public function has_items( $pid ) {
		if ( empty( $this->_pid_to_menu[ $pid ] ) ) return false;
		return true;
	}

	public function get_item_ids( $pid ) {
		if ( empty( $this->_pid_to_menu[ $pid ] ) ) return [];
		return array_map( function ( $e ) { return $e->ID; }, $this->_pid_to_menu[ $pid ] );
	}

	public function echo_items( $pid, $before = '<ul class="menu">', $after = '</ul>', $filter = 'esc_html' ) {
		if ( empty( $this->_pid_to_menu[ $pid ] ) ) return;
		$mis = $this->_pid_to_menu[ $pid ];

		echo $before;
		foreach( $mis as $mi ) {
			$cs = $this->_id_to_attr[ $mi->ID ];
			$this->_echo_item( $mi, $cs, $filter );
		}
		echo $after;
	}

	private function _echo_item( $mi, $cs, $filter = 'esc_html' ) {
		$li_attr = empty( $cs ) ? '' : (' class="' . implode( ' ', $cs ) . '"');
		$obj_id  = intval( $mi->object_id );
		$title   = $filter( $mi->title );

		if ( $mi->url === '#' ) {
			echo "<li$li_attr><label for=\"panel-{$mi->ID}-ctrl\">$title</label></li>";
			return;
		}
		if ( $this->_expanded_page_ids === false || ! in_array( $obj_id, $this->_expanded_page_ids, true ) ) {
			$href = esc_url( $mi->url );
		} else {
			$href = esc_url( "#post-$obj_id" );
		}
		$target = esc_attr( $mi->target );
		echo "<li$li_attr><a href=\"$href\" target=\"$target\">$title</a></li>";
	}


	// -------------------------------------------------------------------------

	private function _get_all_items( $menu_name ) {
		$ls = get_nav_menu_locations();
		if ( ! $ls || ! isset( $ls[ $menu_name ] ) ) return [];

		$menu = wp_get_nav_menu_object( $ls[ $menu_name ] );
		if ( $menu === false ) return [];
		$ret = wp_get_nav_menu_items( $menu->term_id );
		if ( $ret === false ) return [];
		return $ret;
	}

	private function _get_menus( $mis ) {
		$ret = [];
		foreach ( $mis as $mi ) {
			$pid = intval( $mi->menu_item_parent );
			if ( isset( $ret[ $pid ] ) ) {
				$ret[ $pid ][] = $mi;
			} else {
				$ret[ $pid ] = [ $mi ];
			}
		}
		return $ret;
	}

	private function _get_children_state( $p2m ) {
		$ret = [];
		foreach ( $p2m as $pid => $mis ) {
			$ret[ $pid ] = $this->_has_current_url( $mis );
		}
		return $ret;
	}

	private function _has_current_url( $mis ) {
		foreach ( $mis as $mi ) {
			$url = trailingslashit( $mi->url );
			if ( $url === $this->_cur_url ) return true;
		}
		return false;
	}

	private function _get_attributes( $mis, $p2cs ) {
		$ret = [];
		foreach( $mis as $mi ) {
			$url = trailingslashit( $mi->url );
			$id = $mi->ID;
			$cs = [];
			if ( $url === $this->_home_url )             $cs[] = self::CLS_HOME;
			if ( $url === $this->_cur_url )              $cs[] = self::CLS_CURRENT;
			if ( isset( $p2cs[ $id ] ) && $p2cs[ $id ] ) $cs[] = self::CLS_OPENED;
			if ( $this->_is_ancestor_page( $mi ) )       $cs[] = self::CLS_ANCESTOR;
			$ret[ $id ] = $cs;
		}
		return $ret;
	}

	private function _is_ancestor_page( $mi ) {
		if ( ! $this->_is_page ) return false;
		global $post;
		if ( $post->ancestors && in_array( (int) $mi->object_id, $post->ancestors, true ) ) {
			return true;
		}
		return false;
	}

}
