<?php
/**
 * WPObject Type - Subscription
 *
 * Registers Subscription WPObject type and queries
 *
 * @package WPGraphQL\WooCommerce_Subscriptions\Type\WPObject
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce_Subscriptions\Type\WPObject;

use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQLRelay\Relay;
use WPGraphQL\AppContext;
use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce\Type\WPObject\Meta_Data_Type;
use WPGraphQL\WooCommerce_Subscriptions\Model\Subscription;

/**
 * Class Subscription
 */
class Subscription_Type {

    /**
     * Register Subscription type and queries to the WPGraphQL schema
     */
    public static function register_types() {
        register_graphql_object_type(
            'Subscription',
            array(
                'description' => __( 'A subscription object', 'woographql-subscriptions' ),
                'interfaces'  => array(
                    'Node',
                    'NodeWithComments',
                ),
                'fields'      => array(
                    'id'                    => array(
                        'type'        => array( 'non_null' => 'ID' ),
                        'description' => __( 'The globally unique identifier for the subscription', 'woographql-subscriptions' ),
                    ),
                    'databaseId'            => array(
                        'type'        => 'Int',
                        'description' => __( 'The ID of the subscription in the database', 'woographql-subscriptions' ),
                    ),
                    'date'                  => array(
                        'type'        => 'String',
                        'description' => __( 'Date subscription was created', 'woographql-subscriptions' ),
                    ),
                    'dateCompleted'         => array(
                    	'type'        => 'String',
                    	'description' => __( 'Date subscription was completed', 'woographql-subscriptions' ),
                    ),
                    'datePaid'              => array(
                        'type'        => 'String',
                        'description' => __( 'Date subscription was paid', 'woographql-subscriptions' ),
                    ),
                    'modified'              => array(
                        'type'        => 'String',
                        'description' => __( 'Date subscription was last updated', 'woographql-subscriptions' ),
                    ),
                    'customerNote'          => array(
                        'type'        => 'String',
                        'description' => __( 'Customer note', 'woographql-subscriptions' ),
                    ),
                    'status'                => array(
                        'type'        => 'OrderStatusEnum',
                        'description' => __( 'Order status', 'woographql-subscriptions' ),
                    ),
                    'order'                 => array(
                        'type'        => 'Order',
                        'description' => __( 'Parent order', 'woographql-subscriptions' ),
                        'resolve'     => function( $order, array $args, AppContext $context ) {
                            return Factory::resolve_crud_object( $order->parent_id, $context );
                        },
                    ),
                    'customer'              => array(
                        'type'        => 'Customer',
                        'description' => __( 'Subscription customer', 'woographql-subscriptions' ),
                        'resolve'     => function( $subscription, array $args, AppContext $context ) {
                            if ( empty( $subscription->customer_id ) ) {
                                // Guest subscription don't have an attached customer.
                                return null;
                            }

                            return Factory::resolve_customer( $subscription->customer_id, $context );
                        },
                    ),
                    'requiresManualRenewal' => array(
                        'type'        => 'Boolean',
                        'description' => __( 'Whether subscription requires manual renewal', 'woographql-subscriptions' ),
                    ),
                    'scheduleCancelled'     => array(
                        'type'        => 'String',
                        'description' => __( 'Date subscription trial was cancelled', 'woographql-subscriptions' ),
                    ),
                    'scheduleEnd'           => array(
                        'type'        => 'String',
                        'description' => __( 'Date subscription ends', 'woographql-subscriptions' ),
                    ),
                    'scheduleNextPayment'           => array(
                        'type'        => 'String',
                        'description' => __( 'Date of subscription\'s next payment', 'woographql-subscriptions' ),
                    ),
                    'scheduleStart'         => array(
                        'type'        => 'String',
                        'description' => __( 'Date subscription starts', 'woographql-subscriptions' ),
                    ),
                    'metaData'              => Meta_Data_Type::get_metadata_field_definition(),
                ),
            )
        );
        register_graphql_field(
            'RootQuery',
            'subscription',
            array(
                'type'        => 'Subscription',
                'description' => __( 'A subscription object', 'woographql-subscriptions' ),
                'args'        => array(
                    'id'     => array(
                        'type'        => 'ID',
                        'description' => __( 'The ID for identifying the subscription', 'woographql-subscriptions' ),
                    ),
                    'idType' => array(
                        'type'        => 'OrderIdTypeEnum',
                        'description' => __( 'Type of ID being used identify subscription', 'woographql-subscriptions' ),
                    ),
                ),
                'resolve'     => function ( $source, array $args, AppContext $context ) {
                    $id      = isset( $args['id'] ) ? $args['id'] : null;
                    $id_type = isset( $args['idType'] ) ? $args['idType'] : 'global_id';

                    $subscription_id = null;
                    switch ( $id_type ) {
                        case 'order_number':
                            $subscription_id = \wc_get_order_id_by_order_key( $id );
                            break;
                        case 'database_id':
                            $subscription_id = absint( $id );
                            break;
                        case 'global_id':
                        default:
                            $id_components = Relay::fromGlobalId( $id );
                            if ( empty( $id_components['id'] ) || empty( $id_components['type'] ) ) {
                                throw new UserError( __( 'The "id" is invalid', 'woographql-subscriptions' ) );
                            }
                            $subscription_id = absint( $id_components['id'] );
                            break;
                    }

                    if ( empty( $subscription_id ) ) {
                        /* translators: %1$s: ID type, %2$s: ID value */
                        throw new UserError( sprintf( __( 'No subscription ID was found corresponding to the %1$s: %2$s', 'woographql-subscriptions' ), $id_type, $id ) );
                    } elseif ( get_post( $subscription_id )->post_type !== 'shop_subscription' ) {
                        /* translators: %1$s: ID type, %2$s: ID value */
                        throw new UserError( sprintf( __( 'No subscription exists with the %1$s: %2$s', 'woographql-subscriptions' ), $id_type, $id ) );
                    }

                    // Check if user authorized to view subscription.
                    $current_user_id = get_current_user_id();
                    $is_authorized = current_user_can( $post_type->cap->edit_others_posts );
                    $post_type = get_post_type_object( 'shop_subscription' );
                    if ( $current_user_id ) {
                        // Taken from get_users_subscription_orders
                        $subscriptions = get_posts( array(
                            'posts_per_page' => 1,
                            'post__in'       => array( $subscription_id ),
                            'post_type'      => 'shop_subscription',
                            'post_status'    => 'any',
                            'fields'         => 'ids',
                            'orderby'        => 'date',
                            'order'          => 'DESC',
                            'meta_query'     => array(
                                array(
                                    'key'      => '_customer_user',
                                    'compare'  => '=',
                                    'value'    => $current_user_id,
                                    'type'     => 'numeric',
                                ),
                            ),
                        ) );

                        if ( in_array( $subscription_id, $subscriptions, true ) ) {
                            $is_authorized = true;
                        }
                    }

                    $subscription = $is_authorized ? new Subscription( $subscription_id ) : null;

                    return $subscription;
                },
            )
        );
    }

}
