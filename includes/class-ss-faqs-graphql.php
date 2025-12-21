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

					// Return the product post for WPGraphQL to resolve
					$product_post = get_post($product_id);

					if (
						!$product_post ||
						$product_post->post_type !== 'product'
					) {
						return null;
					}

					return new \WPGraphQL\Model\Post($product_post);
				},
			]);

			// Register input field for filtering FAQs by product
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

			// Add filter to handle the custom where argument
			add_filter(
				'graphql_post_object_connection_query_args',
				[$this, 'filter_faqs_by_product'],
				10,
				5,
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
	}

	/**
	 * Filter FAQs by product ID
	 */
	public function filter_faqs_by_product(
		$query_args,
		$source,
		$args,
		$context,
		$info
	) {
		if (
			isset($args['where']['relatedProductId']) &&
			$query_args['post_type'] === SS_FAQs_Post_Type::POST_TYPE
		) {
			$product_id = absint($args['where']['relatedProductId']);

			if (!isset($query_args['meta_query'])) {
				$query_args['meta_query'] = [];
			}

			$query_args['meta_query'][] = [
				'key' => 'related_product',
				'value' => $product_id,
				'compare' => '=',
			];
		}

		return $query_args;
	}
}
