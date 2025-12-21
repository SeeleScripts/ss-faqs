<?php
/**
 * SS FAQs WooCommerce Integration Class
 *
 * @package SS_FAQs
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit();
}

/**
 * WooCommerce integration class
 */
class SS_FAQs_WooCommerce {
	/**
	 * Singleton instance
	 */
	private static $instance = null;

	/**
	 * Product term slug
	 */
	const PRODUCT_TERM_SLUG = 'producto';

	/**
	 * Get singleton instance
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		if (SS_FAQs::is_woocommerce_active()) {
			add_action('init', [$this, 'add_product_term'], 25);
		}
	}

	/**
	 * Add "Producto" term to the FAQ Type taxonomy
	 */
	public function add_product_term() {
		SS_FAQs_Taxonomies::add_term(
			__('Producto', 'ss-faqs'),
			self::PRODUCT_TERM_SLUG,
		);
	}

	/**
	 * Check if a FAQ is of type "Producto"
	 */
	public static function is_product_faq($post_id) {
		return has_term(
			self::PRODUCT_TERM_SLUG,
			SS_FAQs_Taxonomies::TAXONOMY,
			$post_id,
		);
	}

	/**
	 * Get FAQs by product ID
	 */
	public static function get_faqs_by_product($product_id) {
		$args = [
			'post_type' => SS_FAQs_Post_Type::POST_TYPE,
			'posts_per_page' => -1,
			'meta_query' => [
				[
					'key' => 'related_product',
					'value' => $product_id,
					'compare' => '=',
				],
			],
			'tax_query' => [
				[
					'taxonomy' => SS_FAQs_Taxonomies::TAXONOMY,
					'field' => 'slug',
					'terms' => self::PRODUCT_TERM_SLUG,
				],
			],
		];

		return get_posts($args);
	}
}
