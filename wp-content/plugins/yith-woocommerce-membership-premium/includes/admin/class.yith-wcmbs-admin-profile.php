<?php
/**
 * Add extra profile fields for users in admin.
 *
 *
 * @author  Yithemes
 * @package YITH WooCommerce Membership
 * @version 1.0.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !class_exists( 'YITH_WCMBS_Admin_Profile' ) ) {

    /**
     * YITH_WCMBS_Admin_Profile Class
     */
    class YITH_WCMBS_Admin_Profile {

        /**
         * Single instance of the class
         *
         * @var \YITH_WCMBS_Admin_Profile
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WCMBS_Admin_Profile
         * @since 1.0.0
         */
        public static function get_instance() {
            $self = __CLASS__;

            if ( is_null( $self::$instance ) ) {
                $self::$instance = new $self;
            }

            return $self::$instance;
        }

        /**
         * Hook in tabs.
         */
        public function __construct() {

            // Show membership history in user profiles
            add_action( 'show_user_profile', array( $this, 'add_customer_meta_fields' ) );
            add_action( 'edit_user_profile', array( $this, 'add_customer_meta_fields' ) );

            // add membership info in User List Columns
            add_filter( 'manage_users_columns', array( $this, 'add_membership_columns' ) );
            add_filter( 'manage_users_custom_column', array( $this, 'render_membership_columns' ), 10, 3 );

            // Filter Users in base of Membership plans
            add_action( 'restrict_manage_users', array( $this, 'add_filters_by_plan' ) );
            add_filter( 'pre_get_users', array( $this, 'admin_users_filter' ) );

            // Bulk Membership Actions
            add_action( 'restrict_manage_users', array( $this, 'add_membership_editing_actions' ) );
            add_action( 'load-users.php', array( $this, 'bulk_edit_membership' ) );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        }

        /**
         * Add filters by plan in User list page
         *
         *
         */
        public function add_filters_by_plan() {
            $plans = YITH_WCMBS_Manager()->plans;
            if ( empty( $plans ) )
                return;
            ?>
            <input type="hidden" name="francesco" value="1"/>
            <select class="filter_by_membership_plan" name="filter_by_membership_plan" style="float: none;">
                <option value=""><?php _e( 'Filter by membership plan', 'yith-woocommerce-membership' ); ?></option>
                <?php foreach ( $plans as $plan ) : ?>
                    <option
                        value="<?php echo $plan->ID ?>"><?php echo $plan->post_title . ' (' . YITH_WCMBS_Manager()->count_users_in_plan( $plan->ID ) . ')' ?></option>
                <?php endforeach; ?>
            </select>
            <?php
            submit_button( __( 'Filter' ), 'button', 'm_filterit', false );
        }


        /**
         * Add Membership editing actions for bulk editing
         *
         *
         */
        public function add_membership_editing_actions() {
            if ( !current_user_can( 'edit_users' ) )
                return;

            $plans = YITH_WCMBS_Manager()->plans;
            if ( empty( $plans ) )
                return;
            ?>
            <select class="membership_editing_bulk_action" name="membership_editing_bulk_action" style="float: none;">
                <option value=""><?php _e( 'Membership bulk editing', 'yith-woocommerce-membership' ); ?></option>
                <option value="cancel"><?php _e( 'Cancel membership', 'yith-woocommerce-membership' ); ?></option>
                <option value="new"><?php _e( 'New membership', 'yith-woocommerce-membership' ); ?></option>
                <option value="delete_history"><?php _e( 'Delete history', 'yith-woocommerce-membership' ); ?></option>
            </select>

            <select class="membership_editing_action_plan" name="membership_editing_action_plan" style="float: none;">
                <option value=""><?php _e( 'Select membership plan', 'yith-woocommerce-membership' ); ?></option>
                <?php foreach ( $plans as $plan ) : ?>
                    <option
                        value="<?php echo $plan->ID ?>"><?php echo $plan->post_title . ' (' . YITH_WCMBS_Manager()->count_users_in_plan( $plan->ID ) . ')' ?></option>
                <?php endforeach; ?>
            </select>
            <?php
            submit_button( __( 'Apply' ), 'button', 'm_applyit', false );
        }

        /**
         * Bulk Editing Membership
         *
         *
         */
        public function bulk_edit_membership() {
            if ( empty( $_REQUEST[ 'users' ] ) || empty( $_REQUEST[ 'membership_editing_bulk_action' ] ) ) {
                return;
            }

            if ( !empty( $_REQUEST[ 'membership_editing_action_plan' ] ) || $_REQUEST[ 'membership_editing_bulk_action' ] == 'delete_history' ) {
                $users   = $_REQUEST[ 'users' ];
                $action  = $_REQUEST[ 'membership_editing_bulk_action' ];
                $plan_id = $_REQUEST[ 'membership_editing_action_plan' ];

                unset( $_REQUEST[ 'users' ] );
                unset( $_REQUEST[ 'membership_editing_bulk_action' ] );
                unset( $_REQUEST[ 'membership_editing_action_plan' ] );

                $report_action = 'membership_bulk_' . $action;
                $changed       = 0;

                switch ( $action ) {
                    case 'cancel':
                        foreach ( $users as $user_id ) {
                            $member     = YITH_WCMBS_Members()->get_member( $user_id );
                            $user_plans = $member->get_membership_plans( array( 'return' => 'complete', 'status' => 'any' ) );

                            if ( !empty( $user_plans ) ) {
                                foreach ( $user_plans as $user_plan ) {
                                    if ( $user_plan instanceof YITH_WCMBS_Membership ) {
                                        if ( $user_plan->plan_id == $plan_id && $user_plan->can_be_cancelled() ) {
                                            $user_plan->update_status( 'cancelled' );
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    case 'new':
                        foreach ( $users as $user_id ) {
                            $member = YITH_WCMBS_Members()->get_member( $user_id );

                            // set a transient to prevent multiple creation of membership for multiple call of page
                            $transient_name = 'ywcmbs_newmemb_' + $user_id;
                            $transient      = get_transient( $transient_name );

                            if ( !$transient ) {
                                $member->create_membership( $plan_id );
                                set_transient( $transient_name, true, 10 );
                            }
                        }
                        break;
                    case 'delete_history':
                        foreach ( $users as $user_id ) {
                            $member_plans = YITH_WCMBS_Membership_Helper()->get_memberships_by_user( $user_id );

                            if ( !empty( $member_plans ) ) {
                                foreach ( $member_plans as $plan ) {
                                    wp_delete_post( $plan->id );
                                }
                            }

                        }
                        break;
                }

                wp_redirect( admin_url( 'users.php' ) );
            }
        }

        /**
         * filter WP User Query in base of users plan [in User list page]
         *
         * @param WP_User_Query $query
         */
        public function admin_users_filter( $query ) {
            if ( isset( $query->query_vars[ 'yith_wcmbs_user_suppress_filter' ] ) && $query->query_vars[ 'yith_wcmbs_user_suppress_filter' ] )
                return;

            remove_action( current_action(), array( $this, __FUNCTION__ ) );
            global $pagenow;
            if ( is_admin() && $pagenow == 'users.php' && isset( $_REQUEST[ 'filter_by_membership_plan' ] ) && $_REQUEST[ 'filter_by_membership_plan' ] != '' ) {
                $membership_plan_id = $_REQUEST[ 'filter_by_membership_plan' ];
                $users_ids_in_plan  = YITH_WCMBS_Manager()->get_user_ids_by_plan_id( $membership_plan_id );
                array_push( $users_ids_in_plan, 0 );
                $query->set( 'include', $users_ids_in_plan );
            }
            add_action( current_action(), array( $this, __FUNCTION__ ) );
        }

        /**
         * Show Membership Plans on edit user pages.
         *
         * @param WP_User $user
         */
        public function add_customer_meta_fields( $user ) {
            if ( !current_user_can( 'edit_users' ) )
                return;

            $plans = YITH_WCMBS_Manager()->plans;

            $member     = YITH_WCMBS_Members()->get_member( $user->ID );
            $user_plans = $member->get_membership_plans( array(
                                                             'return'       => 'array_complete',
                                                             'status'       => 'any',
                                                             'sort_by_date' => true,
                                                             'history'      => true
                                                         ) );
            ?>
            <h3><?php _e( 'Membership plan history:', 'yith-woocommerce-membership' ) ?></h3>
            <table class="form-table">
                <?php
                if ( empty( $user_plans ) ) {
                    _e( 'This user doesn\'t have any membership plan.', 'yith-woocommerce-membership' );
                } else {
                    foreach ( $user_plans as $plan_id => $history_plans ) {
                        if ( empty( $history_plans ) )
                            continue;

                        $label = get_the_title( $plan_id );
                        if ( $history_plans[ 0 ] instanceof YITH_WCMBS_Membership ) {
                            $label = !empty( $label ) ? $label : $history_plans[ 0 ]->title;
                        }

                        $key = 'yith_wcmbs_user_plans[' . $plan_id . ']';
                        ?>
                        <tr>
                            <th><label><?php echo esc_html( $label ); ?></label></th>
                            <td>
                                <table class="yith-wcmbs-admin-profile-membership-table">
                                    <tr>
                                        <th class="status-indicator"></th>
                                        <th class="yith-wcmbs-edit"><span class="dashicons dashicons-visibility"></span></th>
                                        <th><?php _e( 'Starting Date', 'yith-woocommerce-membership' ); ?></th>
                                        <th><?php _e( 'Expiration Date', 'yith-woocommerce-membership' ); ?></th>
                                        <th class="mini-width"><?php _e( 'Order ID', 'yith-woocommerce-membership' ); ?></th>
                                        <th><?php _e( 'Status', 'yith-woocommerce-membership' ); ?></th>
                                        <th><?php _e( 'Last Update', 'yith-woocommerce-membership' ); ?></th>
                                    </tr>
                                    <?php $loop = 0; ?>
                                    <?php foreach ( $history_plans as $p ) : ?>
                                        <tr>
                                            <td class="status-indicator <?php echo $p->status ?>"></td>
                                            <td class="yith-wcmbs-edit">
                                                <a href="<?php echo get_edit_post_link( $p->id ) ?>">
                                                    <span class="dashicons dashicons-visibility"></span>
                                                </a>
                                            </td>
                                            <td><?php echo $p->get_formatted_date( 'start_date' ) ?></td>
                                            <td><?php echo $p->get_formatted_date( 'end_date' ) ?></td>
                                            <td><?php echo $p->order_id ?></td>
                                            <td><?php echo $p->get_status_text(); ?></td>
                                            <td><?php echo $p->get_formatted_date( 'last_update', true ); ?></td>
                                        </tr>
                                        <?php $loop++; ?>
                                    <?php endforeach; ?>
                                </table>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>

            </table>
            <?php
        }

        /**
         * Add column in admin table list
         *
         * @param array $columns
         *
         * @return array
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function add_membership_columns( $columns ) {
            $columns[ 'yith_wcmbs_user_membership_plans' ] = __( 'Membership Plans', 'yith-woocommerce-membership' );

            return $columns;
        }

        /**
         * Add column in admin table list
         *
         * @param string $output
         * @param string $column_name
         * @param int    $user_id
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         * @return string
         */
        public function render_membership_columns( $output, $column_name, $user_id ) {
            if ( $column_name == 'yith_wcmbs_user_membership_plans' ) {
                $member       = new YITH_WCMBS_Member_Premium( $user_id );
                $member_plans = $member->get_membership_plans( array( 'return' => 'complete', 'status' => 'any' ) );
                if ( !empty( $member_plans ) ) {

                    $ret = '';
                    foreach ( $member_plans as $membership ) {
                        if ( $membership instanceof YITH_WCMBS_Membership ) {
                            $p_name = $membership->get_plan_title();
                            $p_info = $membership->get_plan_info_html();
                            $ret .= "<span class='yith-wcmbs-users-membership-info tips {$membership->status}' data-tip='{$p_info}'>{$p_name}</span>  ";
                        }
                    }

                    return $ret;
                }
            }

            return $output;
        }

        public function admin_enqueue_scripts() {
            $screen = get_current_screen();
            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
            if ( 'users' == $screen->id ) {
                wp_enqueue_script( 'yith_wcmbs_admin_user_bulk', YITH_WCMBS_ASSETS_URL . '/js/admin_user_bulk' . $suffix . '.js', array( 'jquery' ), YITH_WCMBS_VERSION, true );
            }
        }

    }

}

return YITH_WCMBS_Admin_Profile::get_instance();
