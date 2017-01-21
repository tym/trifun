<?php
/*
 * Template for Message
 */

$sended_by_user = get_comment_meta( $message->comment_ID, 'sended_by_user', true );

$sended_by_user_class = !!$sended_by_user ? 'yith-wcmbs-message-sended-by-user' : '';

?>

<li>
    <div class="yith-wcmbs-message-container <?php echo $sended_by_user_class; ?>">
        <div class="yith-wcmbs-message-content">
            <?php echo wpautop( wptexturize( wp_kses_post( $message->comment_content ) ) ); ?>
        </div>
        <div class="yith-wcmbs-message-date">
            <abbr class="exact-date"
                  title="<?php echo $message->comment_date; ?>"><?php printf( __( 'sended on %1$s at %2$s', 'yith-woocommerce-membership' ), date_i18n( wc_date_format(), strtotime( $message->comment_date ) ), date_i18n( wc_time_format(), strtotime( $message->comment_date ) ) ); ?></abbr>
        </div>
    </div>
</li>
