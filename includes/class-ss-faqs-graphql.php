<?php
/**
 * SS FAQs GraphQL Integration Class
 *
 * @package SS_FAQs
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit();
}

/**
 * WPGraphQL integration class
 */
class SS_FAQs_GraphQL {
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
		if (SS_FAQs::is_wpgraphql_active()) {
			add_action('graphql_register_types', [
				$this,
				'register_graphql_fields',
			]);
		}
	}

	/**
	 * Register custom GraphQL fields
	 */
	public function register_graphql_fields() {
		// Register likes field on SsFaq type
		register_graphql_field('SsFaq', 'likes', [
			'type' => 'Int',
			'description' => __('The number of likes for this FAQ', 'ss-faqs'),
			'resolve' => function ($post) {
				return SS_FAQs_Fields::get_likes($post->databaseId);
			},
		]);

		// Register custom where arguments for FAQ connection
		register_graphql_field(
			'RootQueryToSsFaqConnectionWhereArgs',
			'faqType',
			[
				'type' => 'String',
				'description' => __('Filter FAQs by FAQ type slug', 'ss-faqs'),
			],
		);

		// Register related product field if WooCommerce is active
		if (SS_FAQs::is_woocommerce_active()) {
			register_graphql_field('SsFaq', 'relatedProduct', [
				'type' => 'Product',
				'description' => __(
					'The related WooCommerce product for this FAQ',
					'ss-faqs',
				),
				'resolve' => function ($post, $args, $context, $info) {
					$product_id = SS_FAQs_Fields::get_related_product(
						$post->databaseId,
					);

					if (!$product_id) {
						return null;
					}

					// Return the product for WPGraphQL to resolve
					$product = wc_get_product($product_id);

					if (!$product) {
						return null;
					}

					// Use WooCommerce product model for proper GraphQL resolution
					return new \WPGraphQL\WooCommerce\Model\Product($product);
				},
			]);

			// Register input field for filtering FAQs by product ID
			register_graphql_field(
				'RootQueryToSsFaqConnectionWhereArgs',
				'relatedProductId',
				[
					'type' => 'Int',
					'description' => __(
						'Filter FAQs by related product ID',
						'ss-faqs',
					),
				],
			);

			// Register slug argument for filtering FAQs by product slug
			register_graphql_field(
				'RootQueryToSsFaqConnectionWhereArgs',
				'relatedProductSlug',
				[
					'type' => 'String',
					'description' => __(
						'Filter FAQs by related product slug',
						'ss-faqs',
					),
				],
			);
		}

		// Register mutation for incrementing likes
		register_graphql_mutation('incrementFaqLikes', [
			'inputFields' => [
				'id' => [
					'type' => 'ID',
					'description' => __(
						'The global ID of the FAQ to like',
						'ss-faqs',
					),
				],
				'databaseId' => [
					'type' => 'Int',
					'description' => __(
						'The database ID of the FAQ to like',
						'ss-faqs',
					),
				],
			],
			'outputFields' => [
				'ssFaq' => [
					'type' => 'SsFaq',
					'description' => __(
						'The FAQ with updated likes count',
						'ss-faqs',
					),
					'resolve' => function ($payload) {
						return new \WPGraphQL\Model\Post(
							get_post($payload['post_id']),
						);
					},
				],
				'likes' => [
					'type' => 'Int',
					'description' => __('The new likes count', 'ss-faqs'),
					'resolve' => function ($payload) {
						return $payload['likes'];
					},
				],
			],
			'mutateAndGetPayload' => function ($input) {
				$post_id = null;

				if (!empty($input['databaseId'])) {
					$post_id = absint($input['databaseId']);
				} elseif (!empty($input['id'])) {
					$id_parts = \GraphQLRelay\Relay::fromGlobalId($input['id']);
					$post_id = absint($id_parts['id']);
				}

				if (!$post_id) {
					throw new \GraphQL\Error\UserError(
						__('Invalid FAQ ID provided', 'ss-faqs'),
					);
				}

				$post = get_post($post_id);

				if (
					!$post ||
					$post->post_type !== SS_FAQs_Post_Type::POST_TYPE
				) {
					throw new \GraphQL\Error\UserError(
						__('FAQ not found', 'ss-faqs'),
					);
				}

				$new_likes = SS_FAQs_Fields::increment_likes($post_id);

				return [
					'post_id' => $post_id,
					'likes' => $new_likes,
				];
			},
		]);

		// Add filter to handle custom where arguments
		add_filter(
			'graphql_post_object_connection_query_args',
			[$this, 'filter_faqs_queries'],
			10,
			5,
		);
	}

	/**
	 * Filter FAQ queries by custom arguments
	 */
	public function filter_faqs_queries(
		$query_args,
		$source,
		$args,
		$context,
		$info
	) {
		// Only apply filter to FAQ queries
		// post_type can be either a string or an array from WPGraphQL
		$post_type = $query_args['post_type'] ?? null;
		$is_faq_query = false;

		if (is_array($post_type)) {
			$is_faq_query = in_array(SS_FAQs_Post_Type::POST_TYPE, $post_type, true);
		} elseif (is_string($post_type)) {
			$is_faq_query = ($post_type === SS_FAQs_Post_Type::POST_TYPE);
		}

		if (!$is_faq_query) {
			return $query_args;
		}

		// Initialize meta_query if we need to filter by product
		$product_id = $this->get_product_id_from_args($args);
		if ($product_id) {
			// Ensure meta_query exists
			if (!isset($query_args['meta_query'])) {
				$query_args['meta_query'] = [];
			}

			// Set relation if multiple meta queries exist
			if (!isset($query_args['meta_query']['relation'])) {
				$query_args['meta_query']['relation'] = 'AND';
			}

			// Add the product filter
			$query_args['meta_query'][] = [
				'key' => 'related_product',
				'value' => $product_id,
				'compare' => '=',
				'type' => 'NUMERIC',
			];
		}

		// Handle Taxonomy filtering
		if (!empty($args['where']['faqType'])) {
			// Ensure tax_query exists
			if (!isset($query_args['tax_query'])) {
				$query_args['tax_query'] = [];
			}

			// Set relation if multiple tax queries exist
			if (!isset($query_args['tax_query']['relation'])) {
				$query_args['tax_query']['relation'] = 'AND';
			}

			// Add the taxonomy filter
			$query_args['tax_query'][] = [
				'taxonomy' => SS_FAQs_Taxonomies::TAXONOMY,
				'field' => 'slug',
				'terms' => sanitize_title($args['where']['faqType']),
			];
		}

		return $query_args;
	}

	/**
	 * Get product ID from GraphQL arguments
	 *
	 * @param array $args GraphQL query arguments
	 * @return int|null Product ID or null if not found
	 */
	private function get_product_id_from_args($args) {
		// Check for direct product ID
		if (!empty($args['where']['relatedProductId'])) {
			return absint($args['where']['relatedProductId']);
		}

		// Check for product slug
		if (!empty($args['where']['relatedProductSlug'])) {
			$slug = sanitize_title($args['where']['relatedProductSlug']);
			
			// Query for product by slug
			$products = get_posts([
				'name' => $slug,
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'fields' => 'ids',
				'no_found_rows' => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			]);

			return !empty($products) ? absint($products[0]) : null;
		}

		return null;
	}
}
