<?php
/***
 * @class       AW_Trigger_Order_Note_Added
 * @package     AutomateWoo/Triggers
 * @since       2.2
 */

class AW_Trigger_Order_Note_Added extends AW_Trigger
{
	public $name = 'order_note_added';

	public $group = 'Order';

	public $supplied_data_items = [ 'order', 'order_note', 'user', 'shop' ];

	function init()
	{
		$this->title = __('Order Note Added', 'automatewoo');

		// Registers the trigger
		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$contains = new AW_Field_Text_Input();
		$contains->set_name('note_contains');
		$contains->set_title( __( 'Note Contains Text', 'automatewoo'  ) );
		$contains->set_description( __( 'Only trigger if the order note contains the following text.', 'automatewoo'  ) );

		$this->add_field( $contains );
	}



	/**
	 * When could this trigger run?
	 */
	function register_hooks()
	{
		add_action( 'wp_insert_comment', [ $this, 'catch_hooks' ], 20, 2 );
	}


	/**
	 * Catch any comments approved on creation
	 *
	 * @param $comment_id
	 * @param $comment WP_Comment
	 */
	function catch_hooks( $comment_id, $comment )
	{
		if ( $comment->comment_type !== 'order_note' )
			return;

		$order = wc_get_order( $comment->comment_post_ID );
		$user = AW()->order_helper->prepare_user_data_item( $order );

		$order_note = new AW_Model_Order_Note( $comment->comment_ID, $comment->comment_content, $order->id );

		$this->maybe_run([
			'user' => $user,
			'order' => $order,
			'order_note' => $order_note
		]);
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow )
	{
		$user = $workflow->get_data_item('user');
		$order = $workflow->get_data_item('order');
		$order_note = $workflow->get_data_item('order_note');

		if ( ! $user || ! $order || ! $order_note )
			return false;

		$note_contains = $workflow->get_trigger_option('note_contains');

		if ( $note_contains )
		{
			if ( ! stristr( $order_note->note, $note_contains ) )
				return false;
		}

		return true;
	}


}
