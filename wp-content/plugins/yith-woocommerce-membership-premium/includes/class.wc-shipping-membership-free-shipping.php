<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Membership Shipping Method.
 *
 * A simple shipping method for membership free shipping.
 *
 * @class   WC_Shipping_Membership_Free_Shipping
 * @version 1.0.0
 *
 * @since 1.2.5
 *
 * @author  Leanza Francesco <leanzafrancesco@gmail.com>
 */
class WC_Shipping_Membership_Free_Shipping extends WC_Shipping_Method {

    /** @var string Requires option */
    public $requires = '';

    /**
     * Constructor.
     */
    public function __construct( $instance_id = 0 ) {
        $this->id                 = 'membership_free_shipping';
        $this->instance_id        = absint( $instance_id );
        $this->method_title       = __( 'Membership Free Shipping', 'yith-woocommerce-membership' );
        $this->method_description = __( 'Membership Free Shipping is a special method enabled only for members in the plan specified below.', 'yith-woocommerce-membership' );
        $this->supports           = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
        );
        $this->title              = $this->get_option( 'title' );
        $this->requires           = $this->get_option( 'requires' );

        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    /**
     * Get setting form fields for instances of this shipping method within zones.
     *
     * @return array
     */
    public function get_instance_form_fields() {
        $plans         = YITH_WCMBS_Manager()->get_plans( array( 'fields' => 'ids' ) );
        $plans_options = array(
            '' => __( 'an active membership plan', 'yith-woocommerce-membership' ),
        );

        if ( !!$plans && is_array( $plans ) ) {
            foreach ( $plans as $plan_id ) {
                $plans_options[ $plan_id ] = get_the_title( $plan_id );
            }
        }

        return array(
            'title'    => array(
                'title'       => __( 'Title', 'yith-woocommerce-membership' ),
                'type'        => 'text',
                'description' => __( 'This is the shipping method title shown to users in checkout page.', 'yith-woocommerce-membership' ),
                'default'     => $this->method_title,
                'desc_tip'    => true,
            ),
            'requires' => array(
                'title'   => __( 'Membership Free Shipping requires...', 'yith-woocommerce-membership' ),
                'type'    => 'select',
                'class'   => 'wc-enhanced-select',
                'default' => '',
                'options' => $plans_options,
            ),
        );
    }

    /**
     * See if free shipping is available based on the package and cart.
     *
     * @param array $package
     *
     * @return bool
     */
    public function is_available( $package ) {
        $is_available = false;

        $member = YITH_WCMBS_Members()->get_member( get_current_user_id() );
        if ( $member->is_valid() ) {
            if ( !$this->requires ) {
                $is_available = $member->is_member();
            } else {
                $is_available = $member->has_active_plan( $this->requires, false );
            }
        }

        return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package );
    }

    /**
     * Called to calculate shipping rates for this method. Rates can be added using the add_rate() method.
     *
     * @uses WC_Shipping_Method::add_rate()
     */
    public function calculate_shipping( $package = array() ) {
        $this->add_rate( array(
                             'label'   => $this->title,
                             'cost'    => 0,
                             'taxes'   => false,
                             'package' => $package,
                         ) );
    }
}
