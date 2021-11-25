<?php
/**
 * Adds filters that modify core schema.
 *
 * @package \WPGraphQL\WooCommerce_Subscriptions
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce_Subscriptions;

use WPGraphQL\WooCommerce\Data\Factory;
use WPGraphQL\WooCommerce_Subscriptions\Model\Subscription;

/**
 * Class Core_Schema_Filters
 */
class Core_Schema_Filters {

    /**
     * Register filters
     */
    public static function add_filters() {
        // Registers WooCommerce Subscriptions CPTs.
        add_filter( 
            'register_post_type_args',
            array( __CLASS__, 'register_post_types' ),
            10,
            2
        );

        // Add node resolvers.
        add_filter(
            'graphql_resolve_node',
            array( __CLASS__ , 'graphql_resolve_node' ),
            10,
            4
        );
        add_filter(
            'graphql_resolve_node_type',
            array( __CLASS__ , 'graphql_resolve_node_type' ),
            10,
            2
        );

        // Filter Unions.
        add_filter(
            'graphql_union_resolve_type',
            array( __CLASS__, 'inject_type_resolver' ),
            10,
            3
        );
        add_filter(
            'graphql_interface_resolve_type',
            array( __CLASS__, 'inject_type_resolver' ),
            10,
            3
        );

        add_filter(
            'graphql_woocommerce_cpt_loader_model',
            array( __CLASS__, 'graphql_woocommerce_cpt_loaders' ),
            10,
            2
        );

		add_filter(
			'graphql_post_object_connection_query_args',
			array( '\WPGraphQL\WooCommerce_Subscriptions\Connection\Subscriptions', 'post_object_connection_query_args' ),
			10,
			5
		);

        add_filter(
            'graphql_map_input_fields_to_wp_query',
            array( '\WPGraphQL\WooCommerce_Subscriptions\Connection\Subscriptions', 'map_input_fields_to_wp_query' ),
            10,
            7
        );

    }

    /**
     * Registers WooCommerce post-types to be used in GraphQL schema
     *
     * @param array  $args      - allowed post-types.
     * @param string $post_type - name of taxonomy being checked.
     *
     * @return array
     */
    public static function register_post_types( $args, $post_type ) {
        if ( 'shop_subscription' === $post_type ) {
            $args['show_in_graphql']            = true;
            $args['graphql_single_name']        = 'Subscription';
            $args['graphql_plural_name']        = 'Subscriptions';
            $args['skip_graphql_type_registry'] = true;
        }

        return $args;
    }

    /**
     * Resolves Relay node for some WooGraphQL types.
     *
     * @param mixed      $node     Node object.
     * @param string     $id       Object unique ID.
     * @param string     $type     Node type.
     * @param AppContext $context  AppContext instance.
     *
     * @return mixed
     */
    public static function resolve_node( $node, $id, $type, $context ) {
        switch ( $type ) {
            case 'shop_subscription':
                $node = new Subscription( $id );
                break;
        }

        return $node;
    }

    /**
     * Resolves Relay node type for some WooGraphQL types.
     *
     * @param string|null $type  Node type.
     * @param mixed       $node  Node object.
     *
     * @return string|null
     */
    public static function graphql_resolve_node_type( $type, $node ) {
        switch ( true ) {
            case is_a( $node, Subscription::class ):
                $type = 'Subscription';
                break;
        }

        return $type;
    }

    /**
     * Registers model-loaders to be used when resolving WooCommerce-related GraphQL types
     *
     * @param array  $args      - null
     * @param string $post_type - post type to model
     *
     * @return mixed
     */
    public static function graphql_woocommerce_cpt_loaders( $args, $post_type ) {
        switch ( $post_type ) {
            case 'shop_subscription':
                return Subscription::class;
            default:
                return null;
        }
    }

    /**
     * Inject Union type resolver that resolve to Product with Product types
     *
     * @param \WPGraphQL\Type\WPObjectType $type           Type be resolve to.
     * @param mixed                        $value          Object for which the type is being resolve config.
     * @param WPUnionType|WPInterfaceType  $abstract_type  WPGraphQL abstract class object.
     */
    public static function inject_type_resolver( $type, $value, $abstract_type ) {
        switch ( $type ) {
            case 'Subscription':
                $new_type = self::graphql_resolve_node_type( $type, $value );
                if ( $new_type ) {
                    $type = $abstract_type->type_registry->get_type( $new_type );
                }
                break;
        }

        return $type;
    }

}
