<?php
/**
 * Model - Subscription
 *
 * Resolves subscription crud object model
 *
 * @package WPGraphQL\WooCommerce_Subscriptions\Model
 * @since   0.0.2
 */

namespace WPGraphQL\WooCommerce_Subscriptions\Model;

use GraphQLRelay\Relay;
use WPGraphQL\WooCommerce\Model\Order;

/**
 * Class Subscription
 */
class Subscription extends Order {

    /**
     * Hold subscription post type slug
     *
     * @var string $post_type
     */
    protected $post_type = 'shop_subscription';

    /**
     * Subscription constructor.
     *
     * @param int|\WC_Data $id - shop_subscription post-type ID.
     */
    public function __construct( $id ) {
        $data = \wcs_get_subscription( $id );

        parent::__construct( $data );
    }

    /**
     * Return the fields that visible to owners of the order without management caps.
     *
     * @param array $allowed_restricted_fields  The fields to allow when the data is designated as restricted to the current user.
     *
     * @return array
     */
    protected static function get_allowed_restricted_fields( $allowed_restricted_fields = array() ) {
        return array_merge(
            parent::get_allowed_restricted_fields(),
            array(
                'requiresManualRenewal',
                'scheduleCanceled',
                'scheduleEnd',
                'scheduleNextPayment',
                'scheduleStart',
            ),
        );
    }

    /**
     * Initializes the Subscription field resolvers.
     */
    protected function init() {
        if ( empty( $this->fields ) ) {
            parent::init();

            $fields = array(
                'id'                    => function() {
                    return ! empty( $this->wc_data->get_id() ) ? Relay::toGlobalId( 'shop_subscription', $this->wc_data->get_id() ) : null;
                },
                'requiresManualRenewal' => function() {
                    $value = $this->wc_data->requires_manual_renewal;
                    return ! empty( $value ) ? $value : null;
                },
                'scheduleCancelled'     => function() {
                    $value = $this->wc_data->schedule_canceled;
                    return ! empty( $value ) ? $value : null;
                },
                'scheduleEnd'           => function() {
                    $value = $this->wc_data->schedule_end;
                    return ! empty( $value ) ? $value : null;
                },
                'scheduleNextPayment'   => function() {
                    $value = $this->wc_data->schedule_next_payment;
                    return ! empty( $value ) ? $value : null;
                },
                'scheduleStart'         => function() {
                    $value = $this->wc_data->schedule_start;
                    return ! empty( $value ) ? $value : null;
                },
            );

            $this->fields = array_merge( $this->fields, $fields );
        }
    }

    /**
     * Get the related Order model.
     *
     * @return WC_Subscription|false
     */
    public function get_order() {
        return new Order($this->wc_data->get_parent_id());
    }

    /**
     * Get the underlying WooCommerce Subscription class.
     *
     * @return WC_Subscription|false
     */
    public function get_wc_subscription() {
        return $this->wc_data;
    }

}
