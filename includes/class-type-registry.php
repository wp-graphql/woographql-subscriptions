<?php
/**
 * Registers WooGraphQL Subscriptions types to the schema.
 *
 * @package \WPGraphQL\WooCommerce_Subscriptions
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce_Subscriptions;

/**
 * Class Type_Registry
 */
class Type_Registry {

    /**
	 * Registers WooGraphQL Subscriptions types, connections, unions, and mutations to GraphQL schema.
	 *
	 * @param \WPGraphQL\Registry\TypeRegistry $type_registry  Instance of the WPGraphQL TypeRegistry.
	 */
	public function init( \WPGraphQL\Registry\TypeRegistry $type_registry ) {
        // Enumerations.
        \WPGraphQL\WooCommerce_Subscriptions\Type\WPEnum\PricingPropertiesEnum::register();
        \WPGraphQL\WooCommerce_Subscriptions\Type\WPEnum\DisplayContext::register();

        // Objects and connections.
        \WPGraphQL\WooCommerce_Subscriptions\Type\WPObject\SubscriptionProduct::register_types();
    }
}