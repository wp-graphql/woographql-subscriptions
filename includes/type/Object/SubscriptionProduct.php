<?php
/**
 * Object Type - Product types
 *
 * Registers product types
 *
 * @package WPGraphQL\WooCommerce_Subscriptions\Type\WPObject
 * @since   0.0.1
 */

namespace WPGraphQL\WooCommerce_Subscriptions\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Data\Connection\Product_Connection_Resolver;
use WPGraphQL\WooCommerce\Type\WPInterface\Product;
use WPGraphQL\WooCommerce\Type\WPObject\Product_Types;
use WPGraphQL\WooCommerce\Connection\Products;

/**
 * Class Product_Types
 */
class SubscriptionProduct {

	/**
	 * Registers product types to the WPGraphQL schema
	 */
	public static function register_types() {
		self::set_product_model_fields();
        self::register_subscription_product_type();
		self::register_subscription_variable_product_type();
		self::register_subscription_variation_type();
		self::register_variation_connection();
	}

	/**
	 * Adds filters for setting the necessary fields on the model.
	 *
	 * @return void
	 */
	public static function set_product_model_fields() {
		// SubscriptionProduct
		add_filter( 'graphql_subscription_product_model_use_pricing_and_tax_fields', '__return_true' );
		add_filter( 'graphql_subscription_product_model_use_inventory_fields', '__return_true' );
		add_filter( 'graphql_subscription_product_model_use_virtual_data_fields', '__return_true' );
		add_filter( 'graphql_subscription_product_model_use_variation_pricing_fields', '__return_false' );
		add_filter( 'graphql_subscription_product_model_use_external_fields', '__return_false' );
		add_filter( 'graphql_subscription_product_model_use_grouped_fields', '__return_false' );

		// SubscriptionVariableProduct
		add_filter( 'graphql_variable-subscription_product_model_use_pricing_and_tax_fields', '__return_true' );
		add_filter( 'graphql_variable-subscription_product_model_use_inventory_fields', '__return_true' );
		add_filter( 'graphql_variable-subscription_product_model_use_virtual_data_fields', '__return_true' );
		add_filter( 'graphql_variable-subscription_product_model_use_variation_pricing_fields', '__return_true' );
		add_filter( 'graphql_variable-subscription_product_model_use_external_fields', '__return_false' );
		add_filter( 'graphql_variable-subscription_product_model_use_grouped_fields', '__return_false' );
	}
	
	/**
	 * Returns shared fields related to subscriptions
	 *
	 * @param array $fields  Fields array used to overwrite any subscriptions fields.
	 * @return void
	 */
	public static function get_subscription_fields( $fields = array() ) {
		return array_merge(
			array(
				'signUpFee' => array(
					'type'        => 'String',
					'description' => __( 'Subscription pricing', 'woographql-subscriptions'),
					'resolve'     => function( $source ) {
						$sign_up_fee = $source->get_sign_up_fee();
				
						return ! empty( $sign_up_fee ) ? $sign_up_fee : null;
					},
				),
				'addToCartText'        => array(
					'type'        => 'String',
					'description' => __( 'Product\'s add to cart button text description', 'wp-graphql-woocommerce' ),
					'resolve'     => function( $source ) {
						$add_to_cart_text = $source->add_to_cart_text();

						return ! empty( $add_to_cart_text ) ? $add_to_cart_text : null;
					}
				),
			),
			$fields
		);
	}

    /**
	 * Registers "SubscriptionProduct" type.
	 */
	private static function register_subscription_product_type() {
        register_graphql_object_type(
            'SubscriptionProduct',
            array(
                'description' => __( 'A subscription product object', 'woographql-subscriptions' ),
                'interfaces'  => Product_Types::get_product_interfaces(),
                'fields'      => array_merge(
                    Product::get_fields(),
					Product_Types::get_pricing_and_tax_fields(
						array(
							'price'     => array(
								'type'        => 'String',
								'description' => __( 'Subscription pricing', 'woographql-subscriptions' ),
								'args'        => array(
									'exclude' => array(
										'type'        => array( 'list_of' => 'PricingPropertiesEnum' ),
										'description' => __( 'Properties to be excluding from the price statement', 'woographql-subscriptions' ),
									),
									'context'  => array(
										'type'        => 'DisplayContextEnum',
										'description' => __( 'How should price be format? Defaults to simple string', 'woographql-subscriptions' )
									),
								),
								'resolve'     => function( $source, array $args ) {
									if ( ! empty( $args['exclude'] ) ) {
										$include = array();
										foreach( $args['exclude'] as $property ) {
											$include[ $property ] = false;
										}
									}

									$format = null;
									if ( ! empty( $args['context'] ) ) {
										$format = $args['context'];
									}

									$price = $source->priceRaw;
									if ( 'raw' !== $format && ! empty( $price ) ) {
										$price = \WC_Subscriptions_Product::get_price_string(
											$source->as_WC_Data(),
											$include
										);
									}

									if ( 'html' !== $format && ! empty( $price ) ) {
										$price = preg_replace( '!\s+!', ' ', html_entity_decode( strip_tags( $price ) ) );
									}

									return ! empty( $price ) ? $price : null;
								},
							),
						)
					),
					Product_Types::get_inventory_fields(),
					Product_Types::get_shipping_fields(),
					Product_Types::get_virtual_data_fields(),
					self::get_subscription_fields()
                ),
            )
        );
    }

    /**
	 * Registers "SubscriptionVariableProduct" type.
	 */
	private static function register_subscription_variable_product_type() {
        register_graphql_object_type(
            'SubscriptionVariableProduct',
            array(
                'description' => __( 'A subscription variable product object', 'woographql-subscriptions' ),
                'interfaces'  => Product_Types::get_product_interfaces(),
                'fields'      => array_merge(
                    Product::get_fields(),
					Product_Types::get_pricing_and_tax_fields(
						array(
							'price' => array(
								'type'        => 'String',
								'description' => __( 'Subscription pricing', 'woographql-subscriptions' ),
								'args'        => array(
									'context'  => array(
										'type'        => 'DisplayContextEnum',
										'description' => __( 'How should price be format? Defaults to simple string', 'woographql-subscriptions' )
									),
								),
								'resolve'     => function( $source, array $args ) {
									$format = null;
									if ( ! empty( $args['context'] ) ) {
										$format = $args['context'];
									}

									$price = $source->priceRaw;
									if ( 'raw' !== $format && ! empty( $price ) ) {
										$price = $source->get_price_html();
									}

									if ( 'html' !== $format && ! empty( $price ) ) {
										$price = preg_replace( '!\s+!', ' ', html_entity_decode( strip_tags( $price ) ) );
									}

									return ! empty( $price ) ? $price : null;
								},
							)
						)
					),
					Product_Types::get_inventory_fields(),
					Product_Types::get_shipping_fields(),
					self::get_subscription_fields()
                )
            )
        );
	}
	
	/**
	 * Registers "SubscriptionProductVariation" type.
	 *
	 * @return void
	 */
	private static function register_subscription_variation_type() {
		register_graphql_object_type(
			'SubscriptionProductVariation',
			array(
				'description' => __( 'A subscription variable product variation object', 'woographql-subscriptions' ),
                'interfaces'  => array(
					'Node',
					'NodeWithFeaturedImage',
					'ContentNode',
					'UniformResourceIdentifiable',
				),
                'fields'      => array_merge(
					Product::get_fields(),
					Product_Types::get_pricing_and_tax_fields(
						array(
							'price'     => array(
								'type'        => 'String',
								'description' => __( 'Subscription pricing', 'woographql-subscriptions' ),
								'args'        => array(
									'exclude' => array(
										'type'        => array( 'list_of' => 'PricingPropertiesEnum' ),
										'description' => __( 'Properties to be excluding from the price statement', 'woographql-subscriptions' ),
									),
									'context'  => array(
										'type'        => 'DisplayContextEnum',
										'description' => __( 'How should price be format? Defaults to simple string', 'woographql-subscriptions' )
									),
								),
								'resolve'     => function( $source, array $args ) {
									if ( ! empty( $args['exclude'] ) ) {
										$include = array();
										foreach( $args['exclude'] as $property ) {
											$include[ $property ] = false;
										}
									}

									$format = null;
									if ( ! empty( $args['context'] ) ) {
										$format = $args['context'];
									}

									$price = $source->priceRaw;
									if ( 'raw' !== $format && ! empty( $price ) ) {
										$price = \WC_Subscriptions_Product::get_price_string(
											$source->as_WC_Data(),
											$include
										);
									}

									if ( 'html' !== $format && ! empty( $price ) ) {
										$price = preg_replace( '!\s+!', ' ', html_entity_decode( strip_tags( $price ) ) );
									}

									return ! empty( $price ) ? $price : null;
								},
							),
						)
					),
					Product_Types::get_inventory_fields(),
					Product_Types::get_shipping_fields(),
					Product_Types::get_virtual_data_fields(),
					self::get_subscription_fields()
				)
			)
		);
	}

	/**
	 * Register connection from SubscriptionVariableProduct to SubscriptionProductVariation
	 */
	private static function register_variation_connection() {
		// From VariableProduct to ProductVariation.
		register_graphql_connection(
			Products::get_connection_config(
				array(
					'fromType'      => 'SubscriptionVariableProduct',
					'toType'        => 'SubscriptionProductVariation',
					'fromFieldName' => 'variations',
					'resolve'       => function( $source, array $args, AppContext $context, ResolveInfo $info ) {
						$resolver = new Product_Connection_Resolver( $source, $args, $context, $info );

						$resolver->set_query_arg( 'post_parent', $source->ID );
						$resolver->set_query_arg( 'post_type', 'product_variation' );
						$resolver->set_query_arg( 'post__in', $source->variation_ids );

						// Change default ordering.
						if ( ! in_array( 'orderby', array_keys( $resolver->get_query_args() ) ) ) {
							$resolver->set_query_arg( 'orderby', 'post__in' );
						}

						return $resolver->get_connection();
					},
				)
			)
		);
	}

}