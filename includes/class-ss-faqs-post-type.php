<?php
/**
 * SS FAQs Post Type Class
 *
 * @package SS_FAQs
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit();
}

/**
 * Post Type registration class
 */
class SS_FAQs_Post_Type {
	/**
	 * Singleton instance
	 */
	private static $instance = null;

	/**
	 * Post type name
	 */
	const POST_TYPE = 'ss-faqs';

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
		add_action('init', [$this, 'register_post_type']);
	}

	/**
	 * Register the custom post type
	 */
	public function register_post_type() {
		$labels = [
			'name' => _x('FAQs', 'Post Type General Name', 'ss-faqs'),
			'singular_name' => _x('FAQ', 'Post Type Singular Name', 'ss-faqs'),
			'menu_name' => __('FAQs', 'ss-faqs'),
			'name_admin_bar' => __('FAQ', 'ss-faqs'),
			'archives' => __('FAQ Archives', 'ss-faqs'),
			'attributes' => __('FAQ Attributes', 'ss-faqs'),
			'parent_item_colon' => __('Parent FAQ:', 'ss-faqs'),
			'all_items' => __('All FAQs', 'ss-faqs'),
			'add_new_item' => __('Add New FAQ', 'ss-faqs'),
			'add_new' => __('Add New', 'ss-faqs'),
			'new_item' => __('New FAQ', 'ss-faqs'),
			'edit_item' => __('Edit FAQ', 'ss-faqs'),
			'update_item' => __('Update FAQ', 'ss-faqs'),
			'view_item' => __('View FAQ', 'ss-faqs'),
			'view_items' => __('View FAQs', 'ss-faqs'),
			'search_items' => __('Search FAQ', 'ss-faqs'),
			'not_found' => __('Not found', 'ss-faqs'),
			'not_found_in_trash' => __('Not found in Trash', 'ss-faqs'),
			'featured_image' => __('Featured Image', 'ss-faqs'),
			'set_featured_image' => __('Set featured image', 'ss-faqs'),
			'remove_featured_image' => __('Remove featured image', 'ss-faqs'),
			'use_featured_image' => __('Use as featured image', 'ss-faqs'),
			'insert_into_item' => __('Insert into FAQ', 'ss-faqs'),
			'uploaded_to_this_item' => __('Uploaded to this FAQ', 'ss-faqs'),
			'items_list' => __('FAQs list', 'ss-faqs'),
			'items_list_navigation' => __('FAQs list navigation', 'ss-faqs'),
			'filter_items_list' => __('Filter FAQs list', 'ss-faqs'),
		];

		$args = [
			'label' => __('FAQ', 'ss-faqs'),
			'description' => __('Frequently Asked Questions', 'ss-faqs'),
			'labels' => $labels,
			'supports' => ['title', 'editor', 'revisions'],
			'hierarchical' => false,
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 25,
			'menu_icon' => 'dashicons-editor-help',
			'show_in_admin_bar' => true,
			'show_in_nav_menus' => false,
			'can_export' => true,
			'has_archive' => false,
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'capability_type' => 'post',
			'show_in_rest' => true,
			'show_in_graphql' => true,
			'graphql_single_name' => 'ssFaq',
			'graphql_plural_name' => 'ssFaqs',
			'taxonomies' => [SS_FAQs_Taxonomies::TAXONOMY],
		];

		register_post_type(self::POST_TYPE, $args);
	}

	/**
	 * Get the post type name
	 */
	public static function get_post_type() {
		return self::POST_TYPE;
	}
}
