<?php
/**
 * SS FAQs Fields Class
 *
 * @package SS_FAQs
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit();
}

/**
 * Custom fields registration class using SCF/ACF
 */
class SS_FAQs_Fields {
	/**
	 * Singleton instance
	 */
	private static $instance = null;

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
		add_action('acf/init', [$this, 'register_fields']);
	}

	/**
	 * Register custom fields
	 */
	public function register_fields() {
		if (!function_exists('acf_add_local_field_group')) {
			return;
		}

		$fields = [
			[
				'key' => 'field_ss_faqs_likes',
				'label' => __('Likes', 'ss-faqs'),
				'name' => 'likes',
				'type' => 'number',
				'instructions' => __('Number of likes for this FAQ', 'ss-faqs'),
				'required' => 0,
				'default_value' => 0,
				'min' => 0,
				'step' => 1,
			],
		];

		// Add product relationship field if WooCommerce is active
		if (SS_FAQs::is_woocommerce_active()) {
			$fields[] = [
				'key' => 'field_ss_faqs_related_product',
				'label' => __('Related Product', 'ss-faqs'),
				'name' => 'related_product',
				'type' => 'post_object',
				'instructions' => __(
					'Select a product related to this FAQ (only for "Producto" type FAQs)',
					'ss-faqs',
				),
				'required' => 0,
				'post_type' => ['product'],
				'taxonomy' => '',
				'allow_null' => 1,
				'multiple' => 0,
				'return_format' => 'id',
				'ui' => 1,
			];
		}

		acf_add_local_field_group([
			'key' => 'group_ss_faqs_fields',
			'title' => __('FAQ Settings', 'ss-faqs'),
			'fields' => $fields,
			'location' => [
				[
					[
						'param' => 'post_type',
						'operator' => '==',
						'value' => SS_FAQs_Post_Type::POST_TYPE,
					],
				],
			],
			'menu_order' => 0,
			'position' => 'side',
			'style' => 'default',
			'label_placement' => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'show_in_graphql' => 1,
			'graphql_field_name' => 'faqSettings',
		]);
	}

	/**
	 * Get the likes count for a FAQ
	 */
	public static function get_likes($post_id) {
		return (int) get_field('likes', $post_id) ?: 0;
	}

	/**
	 * Increment the likes count for a FAQ
	 */
	public static function increment_likes($post_id) {
		$current_likes = self::get_likes($post_id);
		$new_likes = $current_likes + 1;
		update_field('likes', $new_likes, $post_id);
		return $new_likes;
	}

	/**
	 * Get the related product ID for a FAQ
	 */
	public static function get_related_product($post_id) {
		return get_field('related_product', $post_id);
	}
}
