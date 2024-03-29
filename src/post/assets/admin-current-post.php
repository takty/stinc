<?php
/**
 * Utilities of Current Post in Admin.
 *
 * @package Wpinc
 * @author Takuto Yanagida
 * @version 2022-10-10
 */

namespace wpinc;

if ( ! function_exists( '\wpinc\get_admin_post_id' ) ) {
	/**
	 * Gets the post ID.
	 *
	 * @return int Post ID.
	 */
	function get_admin_post_id(): int {
		$id_g = $_GET['post']     ?? null;  // phpcs:ignore
		$id_p = $_POST['post_ID'] ?? null;  // phpcs:ignore

		if ( $id_g || $id_p ) {
			return intval( $id_g ? $id_g : $id_p );
		}
		return 0;
	}
}

if ( ! function_exists( '\wpinc\get_admin_post_type' ) ) {
	/**
	 * Gets current post type.
	 *
	 * @return string|null Current post type.
	 */
	function get_admin_post_type(): ?string {
		$pt = null;

		$id = get_admin_post_id();
		if ( $id ) {
			$p = get_post( $id );
			if ( $p ) {
				$pt = $p->post_type;
			}
		}
		if ( ! $pt ) {
			$pt = $_GET['post_type'] ?? null;  // phpcs:ignore
		}
		return $pt;
	}
}

if ( ! function_exists( '\wpinc\is_admin_post_type' ) ) {
	/**
	 * Checks current post type.
	 *
	 * @param string $post_type Post type.
	 * @return bool True if the current post type is $post_type.
	 */
	function is_admin_post_type( string $post_type ): bool {
		return get_admin_post_type() === $post_type;
	}
}
