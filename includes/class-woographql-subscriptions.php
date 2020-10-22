<?php
/**
 * Initializes a singleton instance of WooGraphQL_Subscriptions
 *
 * @package WPGraphQL\WooCommerce_Subscriptions
 * @since 0.0.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooGraphQL_Subscriptions' ) ) :

	/**
	 * Class WooGraphQL_Subscriptions
	 */
	final class WooGraphQL_Subscriptions {

		/**
		 * Stores the instance of the WooGraphQL_Subscriptions class
		 *
		 * @var WooGraphQL_Subscriptions The one true WooGraphQL_Subscriptions
		 */
		private static $instance;

		/**
		 * Returns a WooGraphQL_Subscriptions Instance.
		 *
		 * @return WooGraphQL_Subscriptions
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( is_a( self::$instance, __CLASS__ ) ) ) {
				self::$instance = new self();
				self::$instance->includes();
				self::$instance->setup();
			}

			/**
			 * Fire off init action
			 *
			 * @param WooGraphQL_Subscriptions $instance The instance of the WooGraphQL_Subscriptions class
			 */
			do_action( 'woographql_subscriptions_init', self::$instance );

			// Return the WooGraphQL_Subscriptions Instance.
			return self::$instance;
        }
        
		/**
		 * Throw error on object clone.
		 * The whole idea of the singleton design pattern is that there is a single object
		 * therefore, we don't want the object to be cloned.
		 *
		 * @since  0.0.1
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'WooGraphQL_Subscriptions class should not be cloned.', 'woographql-subscriptions' ), '0.0.1' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since  0.0.1
		 */
		public function __wakeup() {
			// De-serializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, esc_html__( 'De-serializing instances of the WooGraphQL_Subscriptions class is not allowed', 'woographql-subscriptions' ), '0.0.1' );
		}

		/**
		 * Include required files.
		 * Uses composer's autoload
		 *
		 * @since  0.0.1
		 */
		private function includes() {
			// Autoload Required Classes.
			if ( defined( 'WOOGRAPHQL_SUBSCRIPTIONS_AUTOLOAD' ) && false !== WOOGRAPHQL_SUBSCRIPTIONS_AUTOLOAD ) {
				require_once WOOGRAPHQL_SUBSCRIPTIONS_PLUGIN_DIR . 'vendor/autoload.php';
			}
		}

		/**
		 * Add WooCommerce Subscription product types.
		 *
		 * @return array
		 */
		public function add_product_types( $product_types ) {
            $product_types['subscription']                  = 'SubscriptionProduct';
            $product_types['variable-subscription'] = 'SubscriptionVariableProduct';

            return $product_types;
        }
        
        public function add_product_enums( $values ) {
			$values = array_merge(
				array(
					'SUBSCRIPTION'         => array(
						'value'       => 'subscription',
						'description' => __( 'A subscription product', 'woographql-subscriptions' ),
					),
					'VARIABLE_SUBSCRIPTION' => array(
						'value'       => 'variable-subscription',
						'description' => __( 'A subscription variable product', 'woographql-subscriptions' ),
					),
					'SUBSCRIPTION_VARIATION' => array(
						'value'       => 'subscription_variation',
						'description' => __( 'A subscription variable product variation', 'woographql-subscriptions' ),
					),
				),
				$values
			);

            return $values;
        }


		/**
		 * Sets up WooGraphQL schema.
		 */
		private function setup() {
            // Add product types
            add_filter( 'graphql_woocommerce_product_types', array( $this, 'add_product_types' ), 10 );

            // Add product enumeration values.
            add_filter( 'graphql_product_types_enum_values', array( $this, 'add_product_enums' ), 10 );

			// Initialize WooGraphQL TypeRegistry.
			$registry = new \WPGraphQL\WooCommerce_Subscriptions\Type_Registry();
			add_action( 'graphql_register_types', array( $registry, 'init' ), 10, 1 );
		}
	}

endif;
