<?php
/*
 * Template for Membership Plans in frontend
 */

/**
 * @var YITH_WCMBS_Membership $membership
 */
?>

<?php
if ( !empty( $title ) ) {
    echo "<h2>{$title}</h2>";
}
if ( !empty( $user_plans ) ) {
    ?>
    <div class="yith-wcmbs-my-account-accordion">
        <?php

        foreach ( $user_plans as $plan_id => $membership ) {
            $display_content_in_plan = get_post_meta( $membership->plan_id, '_show-contents-in-my-account', true );
            $display_content_in_plan = $display_content_in_plan == true;

            $key   = 'yith_wcmbs_membership_plans[' . $membership->plan_id . ']';
            $label = $membership->get_plan_title();
            ?>
            <h3><?php echo esc_html( $label ); ?></h3>
            <div class="yith-wcmbs-my-account-membership-container">

                <div class="yith-wcmbs-my-account-membership-status-container">
                    <table class="yith-wcmbs-membership-table">
                        <thead>
                        <tr>
                            <th><?php _e( 'Starting Date', 'yith-woocommerce-membership' ); ?></th>
                            <th><?php _e( 'Expiration Date', 'yith-woocommerce-membership' ); ?></th>
                            <th><?php _e( 'Status', 'yith-woocommerce-membership' ); ?></th>
                            <?php if ( $membership->has_credit_management() ) : ?>
                                <th><?php _e( ' Remaining Credits', 'yith-woocommerce-membership' ); ?></th>
                                <th><?php _e( 'Next credits update', 'yith-woocommerce-membership' ); ?></th>
                            <?php endif ?>
                        </tr>
                        </thead>
                        <tr>
                            <td><?php echo $membership->get_formatted_date( 'start_date' ) ?></td>
                            <td><?php echo ( $membership->end_date == 'unlimited' ) ? __( 'Unlimited', 'yith-woocommerce-membership' ) : $membership->get_formatted_date( 'end_date' ) ?></td>
                            <td><span class="yith-wcmbs-membership-status-text <?php echo $membership->status ?>"><?php echo $membership->get_status_text() ?></span></td>
                            <?php if ( $membership->has_credit_management() ) : ?>
                                <td><?php echo $membership->get_remaining_credits() ?></td>
                                <td><?php echo date( wc_date_format(), $membership->next_credits_update ) ?></td>
                            <?php endif ?>
                        </tr>
                    </table>
                </div>

                <div class="yith-wcmbs-tabs">
                    <ul>
                        <li>
                            <a href="#yith-wcmbs-tab-history-<?php echo $membership->id; ?>"><?php _e( 'History', 'yith-woocommerce-membership' ) ?></a>
                        </li>
                        <?php if ( $display_content_in_plan && $membership->is_active() ) : ?>
                            <li>
                                <a href="#yith-wcmbs-tab-contents-<?php echo $membership->id; ?>"><?php _e( 'Contents', 'yith-woocommerce-membership' ) ?></a>
                            </li>
                        <?php endif; ?>
                    </ul>

                    <div id="yith-wcmbs-tab-history-<?php echo $membership->id; ?>">

                        <?php
                        $activities = $membership->activities;

                        if ( !empty( $activities ) ) : ?>
                            <table class="yith-wcmbs-membership-table">
                                <thead>
                                <tr>
                                    <th><?php _e( 'Status', 'yith-woocommerce-membership' ); ?></th>
                                    <th><?php _e( 'Update', 'yith-woocommerce-membership' ); ?></th>
                                    <th><?php _e( 'Note', 'yith-woocommerce-membership' ); ?></th>
                                </tr>
                                </thead>
                                <?php foreach ( $activities as $a ) : ?>
                                    <tr>
                                        <td><?php echo strtr( $a->status, yith_wcmbs_get_membership_statuses() ) ?></td>
                                        <td><?php echo date_i18n( wc_date_format(), $a->timestamp ); ?></td>
                                        <td><?php echo $a->note ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                    </div>

                    <?php if ( $display_content_in_plan && $membership->is_active() ) : ?>
                        <div id="yith-wcmbs-tab-contents-<?php echo $membership->id; ?>" class="yith-wcmbs-my-account-list-plan-items-container">
                            <?php

                            $allowed_in_plan = YITH_WCMBS_Manager()->get_allowed_posts_in_plan( $membership->plan_id, true );

                            $ordered_items = get_post_meta( $membership->plan_id, '_yith_wcmbs_plan_items', true );
                            $ordered_items = !empty( $ordered_items ) ? $ordered_items : array();

                            foreach ( $ordered_items as $key => $item ) {
                                if ( is_numeric( $item ) ) {
                                    if ( !in_array( $item, $allowed_in_plan ) ) {
                                        unset( $ordered_items[ $key ] );
                                    }
                                }
                            }

                            if ( !empty( $allowed_in_plan ) ) {
                                foreach ( $allowed_in_plan as $item_id ) {
                                    if ( !in_array( $item_id, $ordered_items ) )
                                        $ordered_items[] = $item_id;
                                }
                            }

                            $t_args = array(
                                'posts' => $ordered_items,
                                'plan'  => $membership,
                            );

                            wc_get_template( '/frontend/my_account_plan_list_items.php', $t_args, YITH_WCMBS_TEMPLATE_PATH, YITH_WCMBS_TEMPLATE_PATH );

                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }

        ?>
    </div>
    <?php
} else {
    echo $no_membership_message;
}
?>