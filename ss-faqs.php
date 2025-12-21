<?php
/**
 * Plugin Name: Sakura Store FAQs
 * Plugin URI: https://sakuranic.store
 * Description: Custom FAQs plugin for Sakura Store with WPGraphQL support
 * Version: 1.0.0
 * Author: Sakura Store
 * Author URI: https://sakuranic.store
 * Text Domain: ss-faqs
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit();
}

// Plugin constants
define('SS_FAQS_VERSION', '1.0.0');
define('SS_FAQS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SS_FAQS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SS_FAQS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
final class SS_FAQs {
	/**
	 * Singleton instance
	 */
	private static $instance = null;

	/**
	 * Dependencies met flag
	 */
	private $dependencies_met = true;

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
		$this->check_dependencies();

		if ($this->dependencies_met) {
			$this->load_includes();
			$this->init_hooks();
		}
	}

	/**
	 * Check plugin dependencies
	 */
	private function check_dependencies() {
		// Check for Secure Custom Fields (SCF) or ACF
		if (
			!class_exists('ACF') &&
			!function_exists('acf_add_local_field_group')
		) {
			$this->dependencies_met = false;
			add_action('admin_notices', [$this, 'scf_missing_notice']);
		}
	}

	/**
	 * Display admin notice when SCF is missing
	 */
	public function scf_missing_notice() {
		?>
        <div class="notice notice-error">
            <p>
                <strong><?php esc_html_e(
                	'Sakura Store FAQs',
                	'ss-faqs',
                ); ?></strong>: 
                <?php esc_html_e(
                	'This plugin requires Secure Custom Fields (SCF) or Advanced Custom Fields (ACF) to be installed and activated.',
                	'ss-faqs',
                ); ?>
            </p>
        </div>
        <?php
	}

	/**
	 * Load include files
	 */
	private function load_includes() {
		require_once SS_FAQS_PLUGIN_DIR .
			'includes/class-ss-faqs-post-type.php';
		require_once SS_FAQS_PLUGIN_DIR .
			'includes/class-ss-faqs-taxonomies.php';
		require_once SS_FAQS_PLUGIN_DIR . 'includes/class-ss-faqs-fields.php';
		require_once SS_FAQS_PLUGIN_DIR .
			'includes/class-ss-faqs-woocommerce.php';
		require_once SS_FAQS_PLUGIN_DIR . 'includes/class-ss-faqs-graphql.php';
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Initialize components
		add_action('init', [$this, 'init_components'], 0);

		// Activation hook
		register_activation_hook(__FILE__, [$this, 'activate']);

		// Deactivation hook
		register_deactivation_hook(__FILE__, [$this, 'deactivate']);
	}

	/**
	 * Initialize plugin components
	 */
	public function init_components() {
		// Initialize post type
		SS_FAQs_Post_Type::get_instance();

		// Initialize taxonomies
		SS_FAQs_Taxonomies::get_instance();

		// Initialize fields
		SS_FAQs_Fields::get_instance();

		// Initialize WooCommerce integration
		SS_FAQs_WooCommerce::get_instance();

		// Initialize GraphQL support
		SS_FAQs_GraphQL::get_instance();
	}

	/**
	 * Plugin activation
	 */
	public function activate() {
		// Flush rewrite rules on activation
		flush_rewrite_rules();
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		// Flush rewrite rules on deactivation
		flush_rewrite_rules();
	}

	/**
	 * Check if WooCommerce is active
	 */
	public static function is_woocommerce_active() {
		return class_exists('WooCommerce');
	}

	/**
	 * Check if WPGraphQL is active
	 */
	public static function is_wpgraphql_active() {
		return class_exists('WPGraphQL');
	}
}

/**
 * Initialize the plugin
 */
function ss_faqs_init() {
	return SS_FAQs::get_instance();
}

// Start the plugin after plugins are loaded
add_action('plugins_loaded', 'ss_faqs_init');
