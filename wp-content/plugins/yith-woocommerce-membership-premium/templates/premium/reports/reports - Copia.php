<?php
/*
 * Template for Reports Page
 */
?>
<div class="wrap">
    <h1><?php _e( 'Membership Reports', 'yith-woocommerce-membership' ) ?></h1>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div id="postbox-container-1" class="postbox-container">
                    <div class="postbox yith-wcmbs-reports-metabox">
                        <h2><span><?php _e( 'Active memberships', 'yith-woocommerce-membership' ) ?></span></h2>

                        <div class="yith-wcmbs-reports-content">
                            <div class="yith-wcmbs-reports-big-number"><?php echo YITH_WCMBS_Membership_Helper()->get_count_active_membership() ?></div>
                        </div>
                    </div>
                    <div class="postbox yith-wcmbs-reports-metabox">
                        <h2><span><?php _e( 'Last actived memberships', 'yith-woocommerce-membership' ) ?></span></h2>

                        <div class="yith-wcmbs-reports-content">
                            <table class="yith-wcmbs-reports-table-membership">
                                <tr>
                                    <th><?php _e( 'Today', 'yith-woocommerce-membership' ) ?></th>
                                    <td><?php echo YITH_WCMBS_Membership_Helper()->get_count_actived_membership( 'today' ) ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( '7days', 'yith-woocommerce-membership' ) ?></th>
                                    <td><?php echo YITH_WCMBS_Membership_Helper()->get_count_actived_membership( '7day' ) ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'This month', 'yith-woocommerce-membership' ) ?></th>
                                    <td><?php echo YITH_WCMBS_Membership_Helper()->get_count_actived_membership( 'month' ) ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'Last month', 'yith-woocommerce-membership' ) ?></th>
                                    <td><?php echo YITH_WCMBS_Membership_Helper()->get_count_actived_membership( 'last_month' ) ?></td>
                                </tr>
                                <tr>
                                    <th><?php _e( 'This year', 'yith-woocommerce-membership' ) ?></th>
                                    <td><?php echo YITH_WCMBS_Membership_Helper()->get_count_actived_membership( 'year' ) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="postbox yith-wcmbs-reports-metabox">
                        <h2><span><?php _e( 'Total membership (ever)', 'yith-woocommerce-membership' ) ?></span></h2>

                        <div class="yith-wcmbs-reports-content">
                            <div class="yith-wcmbs-reports-big-number"><?php echo YITH_WCMBS_Membership_Helper()->get_count_actived_membership( 'ever' ) ?></div>
                        </div>
                    </div>

                    <div class="postbox yith-wcmbs-reports-metabox">
                        <h2><span><?php _e( 'Downloads', 'yith-woocommerce-membership' ) ?></span></h2>

                        <div class="yith-wcmbs-reports-content">
                            <?php
                            $ten_days_start_timestamp = strtotime( '-9days midnight' );
                            $ten_days_end_timestamp   = strtotime( 'tomorrow midnight' );
                            $ten_days_start_date      = date( 'Y-m-d H:i:s', $ten_days_start_timestamp );
                            $ten_days_end_date        = date( 'Y-m-d H:i:s', $ten_days_end_timestamp );
                            $ten_days_args            = array(
                                'select'   => 'timestamp_date as date, COUNT(*) as count',
                                'group_by' => 'day(timestamp_date)',
                                'order_by' => 'timestamp_date',
                                'order'    => 'ASC',
                                'where'    => array(
                                    array(
                                        'key'     => 'timestamp_date',
                                        'value'   => $ten_days_start_date,
                                        'compare' => '>='
                                    ),
                                    array(
                                        'key'     => 'timestamp_date',
                                        'value'   => $ten_days_end_date,
                                        'compare' => '<'
                                    )
                                )
                            );
                            $ten_days_results         = YITH_WCMBS_Downloads_Report()->get_download_reports( $ten_days_args );

                            $ten_days_date_result_array = array();
                            foreach ( $ten_days_results as $ten_days_result ) {
                                $ten_days_current_date                                = date( 'j M', strtotime( $ten_days_result->date ) );
                                $ten_days_date_result_array[ $ten_days_current_date ] = $ten_days_result->count;
                            }

                            $ten_days_counter_row = '';
                            $ten_days_label_row   = '';
                            $ten_days_max         = 0;
                            if ( !empty( $ten_days_date_result_array ) ) {
                                $ten_days_max = absint( max( $ten_days_date_result_array ) );
                            }

                            for ( $i = 0; $i < 10; $i++ ) {
                                $ten_days_current_date = date( 'j M', strtotime( '+' . $i . 'days', $ten_days_start_timestamp ) );
                                $ten_days_counter      = 0;
                                if ( isset( $ten_days_date_result_array[ $ten_days_current_date ] ) ) {
                                    $ten_days_counter = absint( $ten_days_date_result_array[ $ten_days_current_date ] );
                                }

                                $height = $ten_days_max > 0 ? ( $ten_days_counter / $ten_days_max * 100 ) : 0;
                                $grafic = "<div class='yith-wcmbs-reports-downloads-table-date-grafic tips-top' data-tip='$ten_days_counter' style='height:$height%;'></div>";
                                $ten_days_counter_row .= "<td>$grafic</td>";
                                $ten_days_label_row .= '<th>' . $ten_days_current_date . '</th>';
                            }


                            $first_day_current_month       = strtotime( date( 'Y-m-01', current_time( 'timestamp' ) ) );
                            $twelve_months_start_timestamp = strtotime( date( 'Y-m-01', strtotime( '-11 months', $first_day_current_month ) ) );
                            $twelve_months_end_timestamp   = strtotime( 'tomorrow midnight' );
                            $twelve_months_start_date      = date( 'Y-m-d H:i:s', $twelve_months_start_timestamp );
                            $twelve_months_end_date        = date( 'Y-m-d H:i:s', $twelve_months_end_timestamp );
                            $twelve_months_args            = array(
                                'select'   => 'timestamp_date as date, COUNT(*) as count',
                                'group_by' => 'month(timestamp_date)',
                                'order_by' => 'timestamp_date',
                                'order'    => 'ASC',
                                'where'    => array(
                                    array(
                                        'key'     => 'timestamp_date',
                                        'value'   => $twelve_months_start_date,
                                        'compare' => '>='
                                    ),
                                    array(
                                        'key'     => 'timestamp_date',
                                        'value'   => $twelve_months_end_date,
                                        'compare' => '<'
                                    )
                                )
                            );
                            $twelve_months_results         = YITH_WCMBS_Downloads_Report()->get_download_reports( $twelve_months_args );

                            $twelve_months_date_result_array = array();
                            foreach ( $twelve_months_results as $twelve_months_result ) {
                                $twelve_months_current_date                                     = date( 'M', strtotime( $twelve_months_result->date ) );
                                $twelve_months_date_result_array[ $twelve_months_current_date ] = $twelve_months_result->count;
                            }

                            $twelve_months_counter_row = '';
                            $twelve_months_label_row   = '';
                            $twelve_months_max         = 0;
                            if ( !empty( $twelve_months_date_result_array ) ) {
                                $twelve_months_max = absint( max( $twelve_months_date_result_array ) );
                            }

                            for ( $i = 0; $i < 12; $i++ ) {
                                $twelve_months_current_date = date( 'M', strtotime( '+' . $i . 'months', $twelve_months_start_timestamp ) );
                                $twelve_months_counter      = 0;
                                if ( isset( $twelve_months_date_result_array[ $twelve_months_current_date ] ) ) {
                                    $twelve_months_counter = absint( $twelve_months_date_result_array[ $twelve_months_current_date ] );
                                }

                                $height = $twelve_months_max > 0 ? ( $twelve_months_counter / $twelve_months_max * 100 ) : 0;
                                $grafic = "<div class='yith-wcmbs-reports-downloads-table-date-grafic tips-top' data-tip='$twelve_months_counter' style='height:$height%;'></div>";
                                $twelve_months_counter_row .= "<td>$grafic</td>";
                                $twelve_months_label_row .= '<th>' . $twelve_months_current_date . '</th>';
                            }
                            ?>
                            <div class="yith-wcmbs-tabs">
                                <ul>
                                    <li><a href="#yith-wcmbs-reports-downloads-ten-days"><?php _e( '10 days', 'yith-woocommerce-membership' ); ?></a></li>
                                    <li><a href="#yith-wcmbs-reports-downloads-twelve-months"><?php _e( '12 months', 'yith-woocommerce-membership' ); ?></a></li>
                                </ul>

                                <div id="yith-wcmbs-reports-downloads-ten-days">
                                    <p class="yith-wcmbs-reports-downloads-max"> <?php echo sprintf( __( 'Max downloads: %s', 'yith-woocommerce-membership' ), '<strong>' . $ten_days_max . '</strong>' ) ?></p>
                                    <table class="yith-wcmbs-reports-downloads-table-date">
                                        <tr>
                                            <?php echo $ten_days_counter_row; ?>
                                        </tr>
                                        <tr>
                                            <?php echo $ten_days_label_row; ?>
                                        </tr>
                                    </table>
                                </div>
                                <div id="yith-wcmbs-reports-downloads-twelve-months">
                                    <p class="yith-wcmbs-reports-downloads-max"> <?php echo sprintf( __( 'Max downloads: %s', 'yith-woocommerce-membership' ), '<strong>' . $twelve_months_max . '</strong>' ) ?></p>
                                    <table class="yith-wcmbs-reports-downloads-table-date">
                                        <tr>
                                            <?php echo $twelve_months_counter_row; ?>
                                        </tr>
                                        <tr>
                                            <?php echo $twelve_months_label_row; ?>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <div class="postbox yith-wcmbs-reports-metabox">
                        <h2><span><?php _e( 'Membership download reports', 'yith-woocommerce-membership' ) ?></span></h2>

                        <div class="yith-wcmbs-reports-content">
                            <form action="edit.php?post_type=yith-wcmbs-plan&page=yith-wcmbs-reports" method="post">
                                <select name="user_id" class="ajax_chosen_select_customer" style="width:250px;"
                                        data-placeholder="<?php _e( 'Filters by user', 'yith-woocommerce-membership' ); ?>">
                                    <option></option>
                                </select>
                                <input type="submit" class="button primary-button" value="<?php _e( 'Filter', 'yith-woocommerce-membership' ) ?>">
                                <a href="<?php echo admin_url( 'edit.php?post_type=yith-wcmbs-plan&page=yith-wcmbs-reports' ) ?>" class="button primary-button">
                                    <?php _e( 'Reset Filters', 'yith-woocommerce-membership' ) ?>
                                </a>
                            </form>
                        </div>

                        <?php if ( isset( $_REQUEST[ 'user_id' ] ) ) : ?>
                            <div class="yith-wcmbs-reports-content">
                                <?php
                                $user_id        = absint( $_REQUEST[ 'user_id' ] );
                                $username       = get_user_meta( $user_id, 'nickname', true );
                                $user_edit_link = get_edit_user_link( $user_id );
                                $username       = "<a href='$user_edit_link'>$username</a>";

                                echo sprintf( __( 'Download reports for user %s', 'yith-woocommerce-membership' ), $username );
                                ?>
                            </div>
                        <?php endif ?>

                        <div class="yith-wcmbs-reports-content">
                            <?php
                            $query_args = array(
                                'group_by' => 'product_id',
                                'select'   => 'product_id, COUNT(*) as count',
                                'order_by' => 'count',
                                'order'    => 'DESC'
                            );

                            if ( isset( $_REQUEST[ 'user_id' ] ) ) {
                                $query_args[ 'where' ] = array(
                                    array(
                                        'key'   => 'user_id',
                                        'value' => $_REQUEST[ 'user_id' ]
                                    )
                                );
                            }

                            if ( isset( $_REQUEST[ 'order_by' ] ) ) {
                                $query_args[ 'order_by' ] = $_REQUEST[ 'order_by' ];
                            }
                            if ( isset( $_REQUEST[ 'order' ] ) ) {
                                $query_args[ 'order' ] = $_REQUEST[ 'order' ];
                            }

                            $results = YITH_WCMBS_Downloads_Report()->get_download_reports( $query_args );

                            $order                  = isset( $_REQUEST[ 'order' ] ) && $_REQUEST[ 'order' ] == 'ASC' ? 'DESC' : 'ASC';
                            $order_by_download_link = add_query_arg( array( 'order_by' => 'count', 'order' => $order ) );

                            $arrow_type = $order == 'DESC' ? 'up' : 'down';

                            if ( !empty( $results ) ) { ?>
                                <table class="yith-wcmbs-reports-table-downloads">
                                    <tr>
                                        <th><?php _e( 'Product', 'yith-woocommerce-membership' ) ?></th>
                                        <th><?php
                                            $arrow = "<span class='dashicons dashicons-arrow-{$arrow_type}'></span>";
                                            echo '<a href="' . $order_by_download_link . '">' . __( 'Downloads', 'yith-woocommerce-membership' ) . $arrow . '</a>';
                                            ?></th>
                                    </tr>
                                    <?php foreach ( $results as $result ) : ?>
                                        <tr>
                                            <td class="title-col"><?php
                                                $title     = get_the_title( $result->product_id );
                                                $edit_link = get_edit_post_link( $result->product_id );
                                                $view_link = get_permalink( $result->product_id );
                                                echo "<a href='$view_link'><span class='dashicons dashicons-visibility'></span></a>";
                                                echo "<a href='$edit_link'><span class='dashicons dashicons-admin-generic'></span></a>";
                                                if ( $title ) {
                                                    echo $title;
                                                } else {
                                                    echo __( 'Product', 'yith-woocommerce-membership' ) . ' #' . $result->product_id;
                                                }

                                                ?></td>
                                            <td class="downloads-col"><?php echo $result->count; ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                </table>
                            <?php } else {
                                if ( isset( $_REQUEST[ 'user_id' ] ) ) {
                                    $user_id        = absint( $_REQUEST[ 'user_id' ] );
                                    $username       = get_user_meta( $user_id, 'nickname', true );
                                    $user_edit_link = get_edit_user_link( $user_id );
                                    $username       = "<a href='$user_edit_link'>$username</a>";

                                    echo sprintf( __( 'No downloads for user %s', 'yith-woocommerce-membership' ), $username );
                                } else {
                                    _e( 'No downloads', 'yith-woocommerce-membership' );
                                }
                            }

                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>