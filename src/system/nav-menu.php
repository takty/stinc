<?php
namespace st;
/**
 *
 * Nav Menu (PHP)
 *
 * @author Takuto Yanagida @ Space-Time Inc.
 * @version 2020-03-02
 *
 */


require_once __DIR__ . '/../util/url.php';


class NavMenu {

	const CLS_HOME          = 'home';
	const CLS_CURRENT       = 'current';
	const CLS_MENU_PARENT   = 'menu-parent';
	const CLS_MENU_ANCESTOR = 'menu-ancestor';
	const CLS_PAGE_PARENT   = 'page-parent';
	const CLS_PAGE_ANCESTOR = 'page-ancestor';
	const CLS_MP_ANCESTOR   = 'menu-ancestor-page-ancestor';

	const CACHE_EXPIRATION  = DAY_IN_SECONDS;

	static protected $_is_cache_enabled = false;
	static protected $_is_current_archive_enabled = true;
	static protected $_custom_post_type_archive = [];

	static private function _get_ancestor_terms( $t ) {
		$ret = [];
		while ( $t->parent !== 0 ) {
			$t = get_term( $t->parent, $t->taxonomy );
			$ret[] = $t;
		}
		return $ret;
	}

	static public function set_cache_enabled( $flag ) {
		self::$_is_cache_enabled = $flag;
		add_action( 'wp_update_nav_menu', [ '\st\NavMenu', '_cb_wp_update_nav_menu' ], 10, 2 );
	}

	static public function set_current_archive_enabled( $flag ) {
		self::$_is_current_archive_enabled = $flag;
	}

	static public function add_custom_post_type_archive( $post_type, $slug ) {
		self::$_custom_post_type_archive[ $post_type ] = $slug;
	}

	protected $_cur_url;
	protected $_cur_post_type         = false;
	protected $_cur_tax               = false;
	protected $_cur_term_archive_urls = [];
	protected $_cur_is_archive        = false;

	protected $_home_url;
	protected $_is_page;
	protected $_expanded_page_ids = false;
	protected $_cur_objs = false;
	protected $_menu_id = false;

	protected $_pid_to_menu;
	protected $_pid_to_children_state;
	protected $_id_to_attr;

	public function __construct( $menu_name, $expanded_page_ids = false, $object_type_s = false ) {
		$ml = class_exists( '\st\Multilang' ) ? \st\Multilang::get_instance() : null;
		$mh = class_exists( '\st\Multihome' ) ? \st\Multihome::get_instance() : null;

		$this->_cur_url = trailingslashit( strtok( \st\get_current_uri( true ), '?' ) );
		if ( self::$_is_current_archive_enabled && ( is_single() || is_archive() ) ) {
			$this->_cur_post_type = get_post_type();
			if ( is_tax() ) {
				$queried_object = get_queried_object();
				$this->_cur_tax = $queried_object->taxonomy;
			}
			if ( is_single() ) {
				$tax_names = get_object_taxonomies( $this->_cur_post_type );
				$pid = get_the_ID();
				$terms = [];
				foreach ( $tax_names as $tax_name ) {
					$ts = wp_get_post_terms( $pid, $tax_name );
					foreach ( $ts as $t ) {
						$terms[ $t->taxonomy . ' ' . $t->slug ] = $t;
						$ats = self::_get_ancestor_terms( $t );
						foreach ( $ats as $at ) {
							$terms[ $at->taxonomy . ' ' . $at->slug ] = $at;
						}
					}
				}
				$this->_cur_term_archive_urls = array_map( 'get_term_link', $terms );
			}
			if ( is_archive() ) $this->_cur_is_archive = true;
		}

		$url = $mh ? $mh->home_url() : ( $ml ? $ml->home_url() : home_url() );
		$this->_home_url = trailingslashit( $url );

		$this->_is_page = is_page();
		$this->_expanded_page_ids = $expanded_page_ids;
		if ( $object_type_s !== false ) $this->_cur_objs = is_array( $object_type_s ) ? $object_type_s : [ $object_type_s ];

		$mis = $this->_get_all_items( $menu_name );
		$this->_pid_to_menu = $this->_get_menus( $mis );
		$this->_pid_to_children_state = $this->_get_children_state( $this->_pid_to_menu );
		list( $this->_ancestor_ids, $id2pid ) = $this->_get_menu_ancestors( $mis );
		$this->_id_to_attr = $this->_get_attributes( $mis, $id2pid );
	}

	public function set_expanded_page_ids( $ids ) {
		$this->_expanded_page_ids = $ids;
	}

	public function get_menu_id() {
		return $this->_menu_id;
	}


	// -------------------------------------------------------------------------


	public function echo_main_sub_items( $before = '<ul class="menu">', $after = '</ul>', $filter = 'esc_html', $depth = 2 ) {
		$this->echo_main_sub_items_of( 0, $before, $after, $filter, $depth );
	}

	public function echo_main_sub_items_of( $pid, $before = '<ul class="menu">', $after = '</ul>', $filter = 'esc_html', $depth = 2 ) {
		$this->_menu_before = $before;
		$this->_menu_after  = $after;
		$this->_menu_filter = $filter;
		$this->_echo_items_recursive( $pid, $depth );
	}

	protected function _echo_items_recursive( $pid, $depth ) {
		if ( $depth === 0 ) return;
		$this->echo_items( $pid, $this->_menu_before, $this->_menu_after, $this->_menu_filter, function ( $pid ) use ( $depth ) { $this->_echo_items_recursive( $pid, $depth - 1 ); } );
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
			if ( in_array( self::CLS_CURRENT, $a, true ) ) return $id;
		}
		foreach ( $mis as $mi ) {
			$id = $mi->ID;
			if ( empty( $this->_pid_to_menu[ $id ] ) ) continue;
			$a = $this->_id_to_attr[ $id ];
			if ( in_array( self::CLS_MENU_PARENT, $a, true ) ) return $id;
			if ( in_array( self::CLS_PAGE_PARENT, $a, true ) ) return $id;
		}
		return false;
	}

	public function get_menu_id_with_page_hierarchy( $pid = 0 ) {
		if ( empty( $this->_pid_to_menu[ $pid ] ) ) return false;
		$mis = $this->_pid_to_menu[ $pid ];

		if ( ! $this->_is_page ) return false;
		global $post;
		$as = $post->ancestors;
		if ( ! $as ) return false;
		array_unshift( $as, $post->ID );

		$ids = [];
		foreach ( $mis as $mi ) $ids[ (int) $mi->object_id ] = $mi->ID;

		foreach ( $as as $a ) {
			if ( isset( $ids[ $a ] ) ) return $ids[ $a ];
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

	public function echo_items( $pid, $before = '<ul class="menu">', $after = '</ul>', $filter = 'esc_html', $echo_sub = false ) {
		if ( empty( $this->_pid_to_menu[ $pid ] ) ) return false;
		$mis = $this->_pid_to_menu[ $pid ];

		echo $before;
		foreach( $mis as $mi ) {
			$cs = $this->_id_to_attr[ $mi->ID ];
			$item = $this->_get_item( $mi, $cs, $filter );
			if ( $echo_sub && ! empty( $this->_pid_to_menu[ $mi->ID ] ) ) {
				echo $item['before'];
				$echo_sub( $mi->ID );
				echo $item['after'];
			} else {
				echo $item['before'] . $item['after'];
			}
		}
		echo $after;
		return true;
	}

	protected function _get_item( $mi, $cs, $filter = 'esc_html' ) {
		$cls = empty( $cs ) ? '' : implode( ' ', $cs );
		if ( ! empty( $mi->classes ) ) {
			$opt_cls = trim( implode( ' ', $mi->classes ) );
			if ( ! empty( $opt_cls ) ) {
				if ( ! empty( $cls ) ) $cls .= ' ';
				$cls .= $opt_cls;
			}
		}
		$is_sep = ( $mi->url === '#' ) && mb_ereg_match( '-+', $mi->title );
		if ( $is_sep ) $cls .= 'separator';

		$li_cls  = empty( $cls ) ? '' : " class=\"$cls\"";
		$li_id   = " id=\"menu-item-{$mi->ID}\"";
		$li_attr = $li_id . $li_cls;
		$obj_id  = intval( $mi->object_id );
		$title   = $filter( $mi->title, $mi );
		$cont    = esc_html( trim( $mi->post_content ) );
		$after   = '</li>';

		if ( $is_sep ) {
			$before = "<li$li_attr><div></div>";
		} else if ( $mi->url === '#' ) {
			if ( empty( $cont ) ) {
				$before = "<li$li_attr><label for=\"panel-{$mi->ID}-ctrl\">$title</label>";
			} else {
				$before = "<li$li_attr><label for=\"panel-{$mi->ID}-ctrl\">$title<div class=\"description\">$cont</div></label>";
			}
		} else {
			if ( $this->_expanded_page_ids === false || ! in_array( $obj_id, $this->_expanded_page_ids, true ) ) {
				$href = esc_url( $mi->url );
			} else {
				$href = esc_url( "#post-$obj_id" );
			}
			$target = esc_attr( $mi->target );
			if ( empty( $cont ) ) {
				$before = "<li$li_attr><a href=\"$href\" target=\"$target\">$title</a>";
			} else {
				$before = "<li$li_attr><a href=\"$href\" target=\"$target\">$title<div class=\"description\">$cont</div></a>";
			}
		}
		return compact( 'before', 'after' );
	}


	// -------------------------------------------------------------------------


	public function get_self_attributes( $id ) {
		return $this->_id_to_attr[ $id ];
	}


	// -------------------------------------------------------------------------


	protected function _get_all_items( $menu_name ) {
		$ls = get_nav_menu_locations();
		if ( ! $ls || ! isset( $ls[ $menu_name ] ) ) return [];

		$menu = wp_get_nav_menu_object( $ls[ $menu_name ] );
		if ( $menu === false ) return [];
		$this->_menu_id = $menu->term_id;
		$ret = [];
		if ( self::$_is_cache_enabled ) {
			$ret = self::get_nav_menu_items( $menu->term_id );
		} else {
			$ret = wp_get_nav_menu_items( $menu->term_id );
		}
		if ( $ret === false ) return [];
		return $ret;
	}

	protected function _get_menus( $mis ) {
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

	protected function _get_children_state( $p2m ) {
		$ret = [];
		foreach ( $p2m as $pid => $mis ) {
			$ret[ $pid ] = $this->_has_current_url( $mis );
		}
		return $ret;
	}

	protected function _has_current_url( $mis ) {
		foreach ( $mis as $mi ) {
			if ( $this->_is_current( $mi ) ) return true;
		}
		return false;
	}


	// -------------------------------------------------------------------------


	static public function _cb_wp_update_nav_menu( $menu_id, $menu_data = null ) {
		if ( is_array( $menu_data ) && isset( $menu_data['menu-name'] ) ) {
			$menu = wp_get_nav_menu_object( $menu_data['menu-name'] );
			if ( isset( $menu->term_id ) ) {
				$key = 'cache-menu-id-' . $menu->term_id;
				delete_transient( $key );
			}
		}
	}

	static public function get_nav_menu_items( $id ) {
		$key = 'cache-menu-id-' . $id;
		$items = get_transient( $key );
		if ( $items !== false ) return $items;
		$items = wp_get_nav_menu_items( $id );
		set_transient( $key, $items, self::CACHE_EXPIRATION );
		return $items;
	}


	// -------------------------------------------------------------------------


	protected function _get_menu_ancestors( $mis ) {
		$id2pid  = [];
		$curs    = [];
		$dummy_n = 0;

		foreach ( $mis as $mi ) {
			$url_ps = explode( '/', untrailingslashit( $mi->url ) );
			$is_custom_pta = (
				isset( self::$_custom_post_type_archive[ $this->_cur_post_type ] ) &&
				self::$_custom_post_type_archive[ $this->_cur_post_type ] === $url_ps[ count( $url_ps ) - 1 ]
			);
			$is_archive = (
				$mi->object === $this->_cur_tax ||
				$mi->object === $this->_cur_post_type ||
				( ! $this->_cur_is_archive && in_array( $mi->url, $this->_cur_term_archive_urls, true ) )
			);
			if ( $is_custom_pta || $is_archive ) {
				$curs[] = "dummy_$dummy_n";
				$id2pid[ "dummy_$dummy_n" ] = $mi->ID;
				++$dummy_n;
			}
			if ( $this->_is_current( $mi ) ) $curs[] = $mi->ID;
			$id2pid[ $mi->ID ] = (int) $mi->menu_item_parent;
		}
		$ret = [];
		foreach ( $curs as $cur ) {
			$id = $id2pid[ $cur ];
			while ( $id !== 0 ) {
				$ret[] = $id;
				if ( ! isset( $id2pid[ $id ] ) ) break;
				$id = $id2pid[ $id ];
			}
		}
		return [ $ret, $id2pid ];
	}

	protected function _get_attributes( $mis, $id2pid ) {
		$ret = [];
		$pas = [];
		foreach ( $mis as $mi ) {
			$cs = [];

			$url = trailingslashit( $mi->url );
			if ( $url === $this->_home_url ) $cs[] = self::CLS_HOME;
			if ( $this->_is_current( $mi ) )  $cs[] = self::CLS_CURRENT;

			if ( $this->_is_menu_parent( $mi ) ) {
				$cs[] = self::CLS_MENU_PARENT;
			}
			if ( $this->_is_menu_ancestor( $mi ) ) {
				$cs[] = self::CLS_MENU_ANCESTOR;
			}
			if ( $this->_is_page_parent( $mi ) ) {
				$cs[] = self::CLS_PAGE_PARENT;
			}
			if ( $this->_is_page_ancestor( $mi ) ) {
				$cs[] = self::CLS_PAGE_ANCESTOR;
				$pas[] = $mi;
			}
			$ret[ $mi->ID ] = $cs;
		}
		foreach ( $pas as $pa ) {
			$id = $id2pid[ $pa->ID ];
			while ( $id !== 0 ) {
				$ret[ $id ][] = self::CLS_MP_ANCESTOR;
				if ( ! isset( $id2pid[ $id ] ) ) break;
				$id = $id2pid[ $id ];
			}
		}
		return $ret;
	}

	protected function _is_menu_parent( $mi ) {
		$id = $mi->ID;
		return ( isset( $this->_pid_to_children_state[ $id ] ) && $this->_pid_to_children_state[ $id ] );
	}

	protected function _is_menu_ancestor( $mi ) {
		return ( in_array( $mi->ID, $this->_ancestor_ids, true ) );
	}

	protected function _is_page_parent( $mi ) {
		if ( ! $this->_is_page ) return false;
		global $post;
		return ( $post->post_parent === (int) $mi->object_id );
	}

	protected function _is_page_ancestor( $mi ) {
		if ( ! $this->_is_page ) return false;
		global $post;
		return ( $post->ancestors && in_array( (int) $mi->object_id, $post->ancestors, true ) );
	}

	protected function _is_current( $mi ) {
		$url = trailingslashit( $mi->url );
		if ( $url !== $this->_cur_url ) return false;
		if ( $this->_cur_objs && ! in_array( $mi->object, $this->_cur_objs, true ) ) return false;
		return true;
	}

}
