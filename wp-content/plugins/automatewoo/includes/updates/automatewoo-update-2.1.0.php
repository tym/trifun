<?php
/**
 * Update AutomateWoo to 2.1.0
 *
 * Migrates from custom post types for Logs, Queue, Unsubscribes to custom tables.
 *
 * @version     2.1.0
 * @package     AutomateWoo/Updates
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


global $wpdb;

// get next abandoned cart id
$cart = new AW_Model_Abandoned_Cart();
$cart->visitor_email = 'test';
$cart->save();
$increment = absint($cart->id);

// changes to abandoned cart delete and re-add
$wpdb->query("DROP TABLE " . $wpdb->prefix . AW()->table_name_abandoned_cart );

AW_Install::install();

// update increment
$wpdb->query("ALTER TABLE " . $wpdb->prefix . AW()->table_name_abandoned_cart . " AUTO_INCREMENT = $increment" );



// migrate unsubscribes
$unsubscribes = get_posts(array(
	'post_type' => 'aw_unsubscribe',
	'posts_per_page' => -1,
));

if( $unsubscribes )
{
	foreach( $unsubscribes as $unsubscribe_post )
	{
		$new = new AW_Model_Unsubscribe();
		$new->user_id = get_post_meta( $unsubscribe_post->ID,'user_id', true );
		$new->workflow_id = get_post_meta( $unsubscribe_post->ID, 'workflow_id', true );
		$new->date = get_post_meta( $unsubscribe_post->post_date, 'date', true );
		$new->save();
		wp_delete_post($unsubscribe_post->ID);
	}
}



// migrate queue
$queue = get_posts(array(
	'post_type' => 'aw_queue',
	'posts_per_page' => -1,
));

if( $queue )
{
	foreach( $queue as $queued_event )
	{
		$new = new AW_Model_Queued_Event();
		$new->workflow_id = get_post_meta( $queued_event->ID, 'workflow_id', true );
		$new->data_items = get_post_meta( $queued_event->ID, 'data_items', true );
		$new->date = get_post_meta( $queued_event->ID, 'date', true );
		$new->failed = get_post_meta( $queued_event->ID, '_failed', true );
		$new->save();
		wp_delete_post($queued_event->ID);
	}
}



// migrate logs
$logs = get_posts(array(
	'post_type' => 'aw_log',
	'posts_per_page' => -1,
));


if( $logs )
{
	foreach( $logs as $log )
	{
		$new = new AW_Model_Log();
		$new->workflow_id = get_post_meta( $log->ID, 'workflow_id', true );
		$new->date = $log->post_date;
		$new->conversion_tracking_enabled = get_post_meta( $log->ID, 'conversion_tracking_enabled', true );
		$new->tracking_enabled = get_post_meta( $log->ID, 'tracking_enabled', true );

		$new->save();

		$fields = array( 'order_id', 'guest_email', 'user_id', 'category_id', 'tag_id',
			'wishlist_id', 'cart_id', 'order_item_id', 'tracking_data' );

		foreach ( $fields as $field )
		{
			if ( $value = get_post_meta( $log->ID, $field, true ) )
			{
				$new->add_meta( $field, $value );
			}
		}

		wp_delete_post($log->ID);
	}
}