<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Dynamic Pricing Compatibility Class
 *
 * @class   YITH_WCMBS_Dynamic_Pricing_Compatibility
 * @package Yithemes
 * @since   1.0.0
 * @author  Yithemes
 *
 */
class YITH_WCMBS_Dynamic_Pricing_Compatibility {

    /**
     * Single instance of the class
     *
     * @var \YITH_WCMBS_Dynamic_Pricing_Compatibility
     * @since 1.0.0
     */
    protected static $instance;

    /**
     * Returns single instance of the class
     *
     * @return \YITH_WCMBS_Dynamic_Pricing_Compatibility
     * @since 1.0.0
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Constructor
     *
     * @access public
     * @since  1.0.0
     */
    public function __construct() {
        add_action( 'yit_ywdpd_pricing_rules_panel_after_user_status', array( $this, 'add_membership_fields_in_pricing_rules_panel' ), 10, 2 );
        add_action( 'yit_ywdpd_pricing_rules_after_user_status', array( $this, 'add_membership_fields_in_pricing_rules' ), 10, 4 );
        add_filter( 'yit_ywdpd_sub_rules_valid', array( $this, 'validate_user_rule' ), 10, 3 );
        add_filter( 'yit_ywdpd_validate_user', array( $this, 'validate_user' ), 10, 3 );
    }


    /**
     * Add Membership in pricing rules options
     *
     * @param $options
     *
     * @return mixed
     * @see called in init.php
     */
    public static function add_membership_in_pricing_rules_options( $options ) {
        if ( defined( 'YITH_YWDPD_PREMIUM' ) && YITH_YWDPD_PREMIUM && defined( 'YITH_YWDPD_VERSION' ) && version_compare( YITH_YWDPD_VERSION, '1.1.0', '>=' ) ) {
            $options[ 'user_rules' ][ 'memberships_list' ] = __( 'Include customer with membership plans', 'yith-woocommerce-membership' );
        }

        return $options;
    }

    /**
     * Add Membership fields in pricing rules panel (New rule)
     *
     * @param $suffix_name
     * @param $suffix_id
     */
    public function add_membership_fields_in_pricing_rules_panel( $suffix_name, $suffix_id ) {
        $plan_posts = YITH_WCMBS_Manager()->get_plans();

        if ( !!$plan_posts && is_array( $plan_posts ) ) {
            $plans = array();
            foreach ( $plan_posts as $plan_post ) {
                $plans[ $plan_post->ID ] = $plan_post->post_title;
            }

            ?>
            <tr class="deps-user_rules" data-type="memberships_list">
                <th>
                    <?php _e( 'Select plans to include', 'yith-woocommerce-membership' ); ?>
                </th>
                <td>
                    <select name="<?php echo $suffix_name ?>[user_rules_memberships_list][]" multiple="multiple"
                            id="<?php echo $suffix_id . '[user_rules_memberships_list]' ?>"
                            data-placeholder="<?php _e( 'Select plans', 'yith-woocommerce-membership' ) ?>">
                        <?php foreach ( $plans as $plan_id => $plan_name ): ?>
                            <option value="<?php echo $plan_id ?>"><?php echo $plan_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <?php
        }
    }

    /**
     * Add Membership fields in pricing rules
     *
     * @param $suffix_name
     * @param $suffix_id
     */
    public function add_membership_fields_in_pricing_rules( $suffix_name, $suffix_id, $db_value, $key ) {
        $plan_posts = YITH_WCMBS_Manager()->get_plans();

        if ( !!$plan_posts && is_array( $plan_posts ) ) {
            $plans = array();
            foreach ( $plan_posts as $plan_post ) {
                $plans[ $plan_post->ID ] = $plan_post->post_title;
            }

            ?>
            <tr class="deps-user_rules" data-type="memberships_list">
                <th>
                    <?php _e( 'Select plans to include', 'yith-woocommerce-membership' ); ?>
                </th>
                <td>
                    <select name="<?php echo $suffix_name ?>[user_rules_memberships_list][]" multiple="multiple"
                            id="<?php echo $suffix_id . '[user_rules_memberships_list]' ?>"
                            data-placeholder="<?php _e( 'Select plans', 'yith-woocommerce-membership' ) ?>">
                        <?php foreach ( $plans as $plan_id => $plan_name ): ?>
                            <option
                                value="<?php echo $plan_id ?>" <?php ( isset( $db_value[ $key ][ 'user_rules_memberships_list' ] ) ) ? selected( in_array( $plan_id, $db_value[ $key ][ 'user_rules_memberships_list' ] ) ) : '' ?>><?php echo $plan_name ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <?php
        }
    }

    /**
     * Validate user rule
     *
     * @param $sub_rules_valid
     * @param $discount_type
     * @param $r
     *
     * @return bool
     */
    public function validate_user_rule( $sub_rules_valid, $discount_type, $r ) {
        if ( is_user_logged_in() && 'memberships_list' === $discount_type ) {

            if ( !isset( $r[ 'rules_type_' . $discount_type ] ) ) {
                return false;
            }

            $member = YITH_WCMBS_Members()->get_member( get_current_user_id() );

            $member_has_one_plan_at_least = false;
            if ( is_array( $r[ 'rules_type_' . $discount_type ] ) ) {
                foreach ( $r[ 'rules_type_' . $discount_type ] as $plan_id ) {
                    if ( $member->has_active_plan( $plan_id, false ) ) {
                        $member_has_one_plan_at_least = true;
                        break;
                    }
                }
                $sub_rules_valid = !$member_has_one_plan_at_least;
            }
        }

        return $sub_rules_valid;
    }

    /**
     * Validate User
     *
     * @param $to_return
     * @param $type
     * @param $users_list
     *
     * @return bool
     */
    public function validate_user( $to_return, $type, $users_list ) {
        if ( is_user_logged_in() && 'memberships_list' === $type ) {
            $member = YITH_WCMBS_Members()->get_member( get_current_user_id() );

            if ( is_array( $users_list ) ) {
                foreach ( $users_list as $plan_id ) {
                    if ( $member->has_active_plan( $plan_id, false ) ) {
                        return true;
                    }
                }
            }
        }

        return $to_return;
    }
}

/**
 * Unique access to instance of YITH_WCMBS_Dynamic_Pricing_Compatibility class
 *
 * @return YITH_WCMBS_Dynamic_Pricing_Compatibility
 * @since 1.0.0
 */
function YITH_WCMBS_Dynamic_Pricing_Compatibility() {
    return YITH_WCMBS_Dynamic_Pricing_Compatibility::get_instance();
}