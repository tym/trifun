<?php
/*
 * Template for Reports Page
 */
?>

<div class="postbox yith-wcmbs-reports-metabox">
    <h2><span><?php _e( 'Downloads', 'yith-woocommerce-membership' ) ?></span></h2>

    <div class="yith-wcmbs-reports-content">
        <?php wc_get_template( '/reports/download-reports-graphics.php', array(), YITH_WCMBS_TEMPLATE_PATH, YITH_WCMBS_TEMPLATE_PATH ); ?>
    </div>
</div>

<div class="postbox yith-wcmbs-reports-metabox">
    <h2><span><?php _e( 'Membership download reports', 'yith-woocommerce-membership' ) ?></span></h2>

    <div class="yith-wcmbs-reports-content">
        <div class="yith-wcmbs-reports-filters">
            <select id="yith-wcmbs-reports-filter-user-id" name="user_id" class="ajax_chosen_select_customer" style="width:250px;"
                    data-placeholder="<?php _e( 'Filters by user', 'yith-woocommerce-membership' ); ?>">
                <option></option>
            </select>
            <input id="yith-wcmbs-reports-filter-button" type="button" class="button primary-button" value="<?php _e( 'Filter', 'yith-woocommerce-membership' ) ?>">
            <input id="yith-wcmbs-reports-filter-reset" type="button" class="button primary-button" value="<?php _e( 'Reset Filters', 'yith-woocommerce-membership' ) ?>">
        </div>

        <div id="yith-wcmbs-reports-download-reports-table">
            <?php wc_get_template( '/reports/download-reports-table.php', array(), YITH_WCMBS_TEMPLATE_PATH, YITH_WCMBS_TEMPLATE_PATH ); ?>
        </div>
    </div>
</div>