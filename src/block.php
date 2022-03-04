<?php
/**
 * Block
 *
 * @package Sample
 * @author Takuto Yanagida
 * @version 2022-03-03
 */

namespace st\block {
	require_once __DIR__ . '/blok/block.php';

	/**
	 * Initializes.
	 *
	 * @param string|null $category_title Title of added category.
	 */
	function initialize( ?string $category_title = null ): void {
		\wpinc\blok\initialize( $category_title );
	}
}
