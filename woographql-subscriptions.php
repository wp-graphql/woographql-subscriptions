<?php
/**
 * Plugin Name: WooGraphQL Subscriptions
 * Plugin URI: https://github.com/wp-graphql/woographql-subscriptions
 * Description: Adds Woocommerce subscriptions functionality to WPGraphQL schema.
 * Version: 0.0.1
 * Author: kidunot89
 * Author URI: https://axistaylor.com
 * Text Domain: woographql-subscriptions
 * Domain Path: /languages
 * License: GPL-3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * WC requires at least: 3.0.0
 * WC tested up to: 4.0.0
 * WPGraphQL requires at least: 0.13.4+
 * WooGraphQL requires at least: 0.6.1+
 *
 * @package WPGraphQL\WooCommerce
 * @author  Geoff Taylor <geoff@axistaylor.com>
 * @license GPL-3 <https://www.gnu.org/licenses/gpl-3.0.html>
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Setups WooGraphQL Subscriptions constants
 */
function woographql_subscriptions_constants() {
    // Plugin version.
    if ( ! defined( 'WOOGRAPHQL_SUBSCRIPTIONS_VERSION' ) ) {
        define( 'WOOGRAPHQL_SUBSCRIPTIONS_VERSION', '0.0.1' );
    }
    // Plugin Folder Path.
    if ( ! defined( 'WOOGRAPHQL_SUBSCRIPTIONS_PLUGIN_DIR' ) ) {
        define( 'WOOGRAPHQL_SUBSCRIPTIONS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }
    // Plugin Folder URL.
    if ( ! defined( 'WOOGRAPHQL_SUBSCRIPTIONS_PLUGIN_URL' ) ) {
        define( 'WOOGRAPHQL_SUBSCRIPTIONS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }
    // Plugin Root File.
    if ( ! defined( 'WOOGRAPHQL_SUBSCRIPTIONS_PLUGIN_FILE' ) ) {
        define( 'WOOGRAPHQL_SUBSCRIPTIONS_PLUGIN_FILE', __FILE__ );
    }
    // Whether to autoload the files or not.
    if ( ! defined( 'WOOGRAPHQL_SUBSCRIPTIONS_AUTOLOAD' ) ) {
        define( 'WOOGRAPHQL_SUBSCRIPTIONS_AUTOLOAD', true );
    }
}

/**
 * Checks if WooGraphQL Subscriptions required plugins are installed and activated
 */
function woographql_subscriptions_dependencies_not_ready() {
    $deps = array();
    if ( ! class_exists( '\WPGraphQL' ) ) {
        $deps[] = 'WPGraphQL';
    }
    if ( ! class_exists( '\WooCommerce' ) ) {
        $deps[] = 'WooCommerce';
    }

    if ( ! class_exists( '\WC_Subscriptions' ) ) {
        $deps[] = 'WooCommerce Subscriptions';
    }

    if ( ! class_exists( '\WPGraphQL\WooCommerce\WP_GraphQL_WooCommerce' ) ) {
        $deps[] = 'WooGraphQL';
    }

    return $deps;
}

/**
 * Initializes WooGraphQL Subscriptions
 */
function woographql_subscriptions_init() {
    woographql_subscriptions_constants();

    $not_ready = woographql_subscriptions_dependencies_not_ready();
    if ( empty( $not_ready ) ) {
        require_once WOOGRAPHQL_SUBSCRIPTIONS_PLUGIN_DIR . 'includes/class-woographql-subscriptions.php';
        return WooGraphQL_Subscriptions::instance();
    }

    foreach ( $not_ready as $dep ) {
        add_action(
            'admin_notices',
            function() use ( $dep ) {
                ?>
                <div class="error notice">
                    <p>
                        <?php
                            printf(
                                /* translators: dependency not ready error message */
                                esc_html__( '%1$s must be active for "WooGraphQL Subscriptions" to work', 'wp-graphql-woocommerce' ),
                                esc_html( $dep )
                            );
                        ?>
                    </p>
                </div>
                <?php
            }
        );
    }

    return false;
}
add_action( 'graphql_woocommerce_init', 'woographql_subscriptions_init' );
