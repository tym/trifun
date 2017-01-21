<?php
/**
 * @var $log AW_Model_Log
 */

$notes = $log->get_meta('notes');

// todo move this to a separate admin view
$human_readable_data_layer = [];

$data_layer = $log->get_data_layer();

foreach ( $data_layer as $data_type => $data_item ) {

	if ( ! $data_item ) continue;

	switch ( $data_type ) {

		case 'order':
			$link = get_edit_post_link( $data_item->id );
			$human_readable_data_layer[] = array(
				'title' => __('Order', 'automatewoo'),
				'value' => "<a href='$link'>#$data_item->id</a>"
			);
			break;

		case 'user':
			$link = get_edit_user_link( $data_item->ID );
			$value = $data_item->first_name .' '. $data_item->last_name;

			if ( ! $value )
			{
				$value = $data_item->user_email;
			}

			$human_readable_data_layer[] = array(
				'title' => __('User', 'automatewoo'),
				'value' => "<a href='$link'>$value</a>"
			);
			break;

		case 'guest':
			$human_readable_data_layer[] = array(
				'title' => __('Guest', 'automatewoo'),
				'value' => "<a href='mailto:$data_item->email'>$data_item->email</a>"
			);
			break;


		case 'cart':
			$human_readable_data_layer[] = [
				'title' => __('Cart', 'automatewoo'),
				'value' => '#' . $data_item->id
			];
			break;

		case 'product':
			/** @var $data_item WC_Product */
			$link = get_edit_post_link($data_item->id);
			$human_readable_data_layer[] = array(
				'title' => __('Product', 'automatewoo'),
				'value' => "<a href='$link'>" . $data_item->get_title(). "</a>"
			);
			break;

		case 'subscription':
			/** @var $data_item WC_Subscription */
			$link = get_edit_post_link( $data_item->id );
			$human_readable_data_layer[] = array(
				'title' => __('Subscription', 'automatewoo'),
				'value' => "<a href='$link'>#$data_item->id</a>"
			);
			break;

		case 'wishlist':

			$human_readable_data_layer[] = array(
				'title' => __( 'Wishlist', 'automatewoo' ),
				'value' => '#' . $data_item->id
			);

			break;
	}
}

$human_readable_data_layer = apply_filters( 'automatewoo/log/human_readable_data_layer', $human_readable_data_layer, $log );


?>

	<div class="automatewoo-modal__header">
		<h1><?php printf(__( "Log #%s", 'automatewoo' ), $log->id ) ?></h1>
	</div>

	<div class="automatewoo-modal__body">
		<div class="automatewoo-modal__body-inner">

			<ul>
				<li><strong><?php _e('Workflow', 'automatewoo') ?>:</strong> <a href="<?php echo get_edit_post_link( $log->workflow_id ) ?>"><?php echo get_the_title( $log->workflow_id ) ?></a></li>
				<li><strong><?php _e('Time', 'automatewoo') ?>:</strong> <?php echo aw_display_time( $log->date ) ?></li>

				<?php foreach ($human_readable_data_layer as $item ): ?>
					<li><strong><?php echo $item['title'] ?>:</strong> <?php echo $item['value'] ?></li>
				<?php endforeach; ?>

				<li><strong><?php _e('Tracking enabled', 'automatewoo') ?>:</strong> <?php echo ( $log->tracking_enabled ? __('Yes','automatewoo') : __('No','automatewoo') ) ?></li>
				<li><strong><?php _e('Conversion tracking enabled', 'automatewoo') ?>:</strong> <?php echo ( $log->conversion_tracking_enabled ? __('Yes','automatewoo') : __('No','automatewoo') ) ?></li>

				<?php if ( $log->tracking_enabled ): ?>
					<li><strong><?php _e('Opened', 'automatewoo') ?>:</strong> <?php echo ( $log->has_open_recorded() ? aw_display_time($log->get_date_opened()) : __('No','automatewoo') ) ?></li>
					<li><strong><?php _e('Clicked', 'automatewoo') ?>:</strong> <?php echo ( $log->has_click_recorded() ? aw_display_time($log->get_date_clicked()) : __('No','automatewoo') ) ?></li>
				<?php endif; ?>

			</ul>

			<?php if ( $notes ): ?>
				<hr>

				<strong><?php _e( "Log notes:", 'automatewoo' ) ?></strong><br>
				<?php foreach ($notes as $note ): ?>
					<p><?php echo $note; ?></p>
				<?php endforeach; ?>

			<?php endif; ?>

		</div>
	</div>
