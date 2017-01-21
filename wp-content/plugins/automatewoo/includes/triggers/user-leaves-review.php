<?php
/***
 * @class       AW_Trigger_User_Leaves_Review
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_User_Leaves_Review extends AW_Trigger
{
	public $name = 'user_leaves_review';

	public $supplied_data_items = [ 'comment', 'user', 'product', 'shop' ];


	function init()
	{
		$this->title = __( 'User Leaves a Product Review', 'automatewoo' );
		$this->group = __( 'User', 'automatewoo' );

		// Registers the trigger
		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields() {}



	/**
	 * When could this trigger run?
	 */
	function register_hooks()
	{
		add_action( 'transition_comment_status', [ $this, 'catch_comment_approval' ], 20, 3 );
		add_action( 'comment_post', [ $this, 'catch_new_comments' ], 10, 2 );
	}


	/**
	 * Catch any comments approved on creation
	 *
	 * @param $comment_ID
	 * @param $approved
	 */
	function catch_new_comments( $comment_ID, $approved )
	{
		if ( $approved != 1 )
			return;

		$comment = get_comment( $comment_ID );

		$this->catch_hooks( $comment );
	}


	/**
	 * Catch any comments that were approved after creation
	 *
	 * @param $new_status string
	 * @param $old_status string
	 * @param $comment object
	 */
	function catch_comment_approval( $new_status, $old_status, $comment )
	{
		if ( $new_status !== 'approved' )
			return;

		$this->catch_hooks( $comment );
	}


	/**
	 *
	 * @param $comment object
	 */
	function catch_hooks( $comment )
	{
		if ( ! $comment->user_id )
			return;

		// Make sure the comment is on a product
		if ( 'product' === get_post_type( $comment->comment_post_ID ) )
		{
			$user = get_user_by( 'id', $comment->user_id );
			$product = wc_get_product( $comment->comment_post_ID );

			$this->maybe_run(array(
				'user' => $user,
				'product' => $product,
				'comment' => $comment
			));
		}
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow )
	{
		$user = $workflow->get_data_item('user');
		$product = $workflow->get_data_item('product');
		$comment = $workflow->get_data_item('comment');

		if ( ! $user || ! $product || ! $comment )
			return false;

		// only run once for each comment and workflow
		// just in case the comment is approved more than once
		$log_query = new AW_Query_Logs();
		$log_query->set_limit(1);
		$log_query->where( 'workflow_id', $workflow->id );
		$log_query->where( 'comment_id', $comment->comment_ID );

		if ( $log_query->get_results() )
			return false;

		return true;
	}


}
