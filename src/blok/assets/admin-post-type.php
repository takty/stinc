<?php
/**
 * Retrieving Post Type in Admin.
 *
 * @package Wpinc
 * @author Takuto Yanagida
 * @version 2022-10-10
 */

namespace wpinc;

if ( ! function_exists( '\wpinc\get_admin_post_type' ) ) {
	/**
	 * Gets current post type.
	 *
	 * @return string|null Current post type.
	 */
	function get_admin_post_type(): ?string {
		$pt = null;

		$id_g = $_GET['post']     ?? null;  // phpcs:ignore
		$id_p = $_POST['post_ID'] ?? null;  // phpcs:ignore

		if ( $id_g || $id_p ) {
			$p = get_post( intval( $id_g ? $id_g : $id_p ) );
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
