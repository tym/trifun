<?php
/**
 * @class       AW_Trigger_Guest_Leaves_Review
 * @package     AutomateWoo/Triggers
 * @since       2.1.12
 */

class AW_Trigger_Guest_Leaves_Review extends AW_Trigger {

	public $name = 'guest_leaves_review';

	public $supplied_data_items = [ 'guest', 'product', 'shop', 'comment' ];


	function init() {

		$this->title = __( 'Guest Leaves a Product Review', 'automatewoo');
		$this->title = __( 'Guest', 'automatewoo' );

		parent::init();
	}


	function load_fields(){}


	/**
	 * When could this trigger run?
	 */
	function register_hooks() {
		add_action( 'transition_comment_status', array( $this, 'catch_comment_approval' ), 20, 3 );
		add_action( 'comment_post', array( $this, 'catch_new_comments' ), 20, 2 ); // happens after the guest has been stored
	}


	/**
	 * Catch any comments approved on creation
	 *
	 * @param $comment_ID
	 * @param $approved
	 */
	function catch_new_comments( $comment_ID, $approved ) {

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
	 * @param $comment WP_Comment
	 */
	function catch_comment_approval( $new_status, $old_status, $comment ) {
		if ( $new_status !== 'approved' ) return;
		$this->catch_hooks( $comment );
	}


	/**
	 * @param WP_Comment $comment
	 */
	function catch_hooks( $comment ) {

		if ( $comment->user_id )
			return;

		// Make sure the comment is on a product
		if ( 'product' === get_post_type( $comment->comment_post_ID ) ) {

			$guest = new AW_Model_Guest();
			$guest->get_by( 'email', strtolower( $comment->comment_author_email ) );

			$product = wc_get_product( $comment->comment_post_ID );

			$this->maybe_run([
				'guest' => $guest,
				'product' => $product,
				'comment' => $comment
			]);
		}
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$guest = $workflow->get_data_item('guest');
		$product = $workflow->get_data_item('product');
		$comment = $workflow->get_data_item('comment');

		if ( ! $guest || ! $product || ! $comment )
			return false;

		// only run once for each comment and workflow
		// just in case the comment is approved more than once
		$log_query = ( new AW_Query_Logs() )
			->set_limit(1)
			->where( 'workflow_id', $workflow->id )
			->where( 'comment_id', $comment->comment_ID );

		if ( $log_query->get_results() )
			return false;

		return true;
	}

}
