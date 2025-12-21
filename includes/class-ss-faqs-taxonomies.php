<?php
/**
 * SS FAQs Taxonomies Class
 *
 * @package SS_FAQs
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit();
}

/**
 * Taxonomy registration class
 */
class SS_FAQs_Taxonomies {
	/**
	 * Singleton instance
	 */
	private static $instance = null;

	/**
	 * Taxonomy name
	 */
	const TAXONOMY = 'faq-type';

	/**
	 * Default terms
	 */
	private $default_terms = [
		'general' => 'General',
		'usuario' => 'Usuario',
		'envios-y-entregas' => 'Envios y Entregas',
		'devoluciones-y-cambios' => 'Devoluciones y Cambios',
	];

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
		add_action('init', [$this, 'register_taxonomy']);
		add_action('init', [$this, 'insert_default_terms'], 20);
	}

	/**
	 * Register the taxonomy
	 */
	public function register_taxonomy() {
		$labels = [
			'name' => _x('FAQ Types', 'Taxonomy General Name', 'ss-faqs'),
			'singular_name' => _x(
				'FAQ Type',
				'Taxonomy Singular Name',
				'ss-faqs',
			),
			'menu_name' => __('FAQ Types', 'ss-faqs'),
			'all_items' => __('All FAQ Types', 'ss-faqs'),
			'parent_item' => __('Parent FAQ Type', 'ss-faqs'),
			'parent_item_colon' => __('Parent FAQ Type:', 'ss-faqs'),
			'new_item_name' => __('New FAQ Type Name', 'ss-faqs'),
			'add_new_item' => __('Add New FAQ Type', 'ss-faqs'),
			'edit_item' => __('Edit FAQ Type', 'ss-faqs'),
			'update_item' => __('Update FAQ Type', 'ss-faqs'),
			'view_item' => __('View FAQ Type', 'ss-faqs'),
			'separate_items_with_commas' => __(
				'Separate FAQ types with commas',
				'ss-faqs',
			),
			'add_or_remove_items' => __('Add or remove FAQ types', 'ss-faqs'),
			'choose_from_most_used' => __(
				'Choose from the most used',
				'ss-faqs',
			),
			'popular_items' => __('Popular FAQ Types', 'ss-faqs'),
			'search_items' => __('Search FAQ Types', 'ss-faqs'),
			'not_found' => __('Not Found', 'ss-faqs'),
			'no_terms' => __('No FAQ types', 'ss-faqs'),
			'items_list' => __('FAQ Types list', 'ss-faqs'),
			'items_list_navigation' => __(
				'FAQ Types list navigation',
				'ss-faqs',
			),
		];

		$args = [
			'labels' => $labels,
			'hierarchical' => true,
			'public' => true,
			'show_ui' => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud' => false,
			'show_in_rest' => true,
			'show_in_graphql' => true,
			'graphql_single_name' => 'faqType',
			'graphql_plural_name' => 'faqTypes',
		];

		register_taxonomy(self::TAXONOMY, SS_FAQs_Post_Type::POST_TYPE, $args);
	}

	/**
	 * Insert default terms
	 */
	public function insert_default_terms() {
		foreach ($this->default_terms as $slug => $name) {
			if (!term_exists($slug, self::TAXONOMY)) {
				wp_insert_term($name, self::TAXONOMY, ['slug' => $slug]);
			}
		}
	}

	/**
	 * Get the taxonomy name
	 */
	public static function get_taxonomy() {
		return self::TAXONOMY;
	}

	/**
	 * Add a new term to the taxonomy
	 */
	public static function add_term($name, $slug = '') {
		$args = [];
		if (!empty($slug)) {
			$args['slug'] = $slug;
		}

		if (!term_exists($slug ?: $name, self::TAXONOMY)) {
			return wp_insert_term($name, self::TAXONOMY, $args);
		}

		return false;
	}
}
