<?php
/**
 * Enum Type - PricingPropertiesEnum
 *
 * @package WPGraphQL\WooCommerce_Subscriptions\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce_Subscriptions\Type\WPEnum;

/**
 * Class PricingPropertiesEnum
 */
class PricingPropertiesEnum {
	/**
	 * Registers type
	 */
	public static function register() {
        register_graphql_enum_type(
			'PricingPropertiesEnum',
			array(
				'description' => __( 'Properties that make up the subscription price', 'wp-graphql-woocommerce' ),
				'values'      => array(
                    'SUBSCRIPTION_PRICE'  => array( 'value' => 'subscription_price' ),
                    'SUBSCRIPTION_PERIOD' => array( 'value' => 'subscription_period' ),
                    'SUBSCRIPTION_LENGTH' => array( 'value' => 'subscription_length' ),
                    'SIGN_UP_FEE'         => array( 'value' => 'sign_up_fee' ),
                    'TRAIL_LENGTH'        => array( 'value' => 'trial_length' ),
                ),
			)
		);
	}
}
