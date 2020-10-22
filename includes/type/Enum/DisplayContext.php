<?php
/**
 * Enum Type - DisplayContext
 *
 * @package WPGraphQL\WooCommerce_Subscriptions\Type\WPEnum
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce_Subscriptions\Type\WPEnum;

/**
 * Class DisplayContext
 */
class DisplayContext {
	/**
	 * Registers type
	 */
	public static function register() {
        register_graphql_enum_type(
			'DisplayContextEnum',
			array(
				'description' => __( 'WC Subscriptions query display context', 'wp-graphql-woocommerce' ),
				'values'      => array(
                    'RAW'     => array( 'value' => 'raw' ),
                    'HTML'    => array( 'value' => 'html' ),
                    'DEFAULT' => array( 'value' => 'default' )
                ),
			)
		);
	}
}
