<?php
/**
 * Update to 2.6.1
 *
 * migrate all 'disabled like' post statuses to the new 'disabled' status
 */

$workflows_query = new AW_Query_Workflows();
$workflows_query->args['post_status'] = [ 'draft', 'pending', 'private' ];

$workflows = $workflows_query->get_results();

if ( $workflows ) foreach ( $workflows as $workflow )
{
	/** @var $workflow AW_Model_Workflow */

	wp_update_post([
		'ID' => $workflow->id,
		'post_status' => 'aw-disabled'
	]);
}
