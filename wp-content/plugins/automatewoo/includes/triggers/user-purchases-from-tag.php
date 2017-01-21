<?php
/**
 * Only allows a single tag choice as the text variable system only supports single data items
 *
 * @class       AW_Trigger_User_Purchases_From_Tag
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_User_Purchases_From_Tag extends AW_Trigger
{
	public $name = 'user_purchases_from_tag';

	public $group = 'Order';

	public $supplied_data_items = array( 'user', 'order', 'tag', 'shop' );


	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __('Order Includes Product from a Specific Tag', 'automatewoo');

		// Registers the trigger
		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$category = new AW_Field_Tag();
		$category->set_description( __( 'Only trigger when the a product is purchased from a certain tag.', 'automatewoo'  ) );
		$category->set_required(true);

		$order_status = new AW_Field_Order_Status( false );
		$order_status->set_required(true);
		$order_status->set_default('wc-completed');

		$this->add_field( $category );
		$this->add_field( $order_status );
	}



	/**
	 * When could this trigger run?
	 */
	function register_hooks()
	{
		add_action( 'woocommerce_order_status_changed', array( $this, 'catch_hooks' ), 100, 1 );
	}


	/**
	 * Route hooks through here
	 */
	function catch_hooks( $order_id )
	{
		$order = wc_get_order( $order_id );
		$user = AW()->order_helper->prepare_user_data_item( $order );

		$this->maybe_run(array(
			'order' => $order,
			'user' => $user
		));
	}


	/**
	 * @param $workflow AW_Model_Workflow
	 *
	 * @return bool
	 */
	function validate_workflow( $workflow )
	{
		$trigger = $workflow->get_trigger();
		$user = $workflow->get_data_item('user');
		$order = $workflow->get_data_item('order');

		if ( ! $user || ! $order )
			return false;

		if ( ! $this->validate_order_status_field( $trigger, $order ) )
			return false;


		// Validate category
		if ( $valid_tag = absint( $workflow->get_trigger_option('tag') ) )
		{
			foreach ( $order->get_items() as $item )
			{
				if ( $item['product_id'] > 0 )
				{
					$product_tags = get_the_terms( $item['product_id'], 'product_tag' );

					if ( ! $product_tags )
						continue;

					foreach( $product_tags as $tag )
					{
						if ( $tag->term_id == $valid_tag )
						{
							// user has bought something from the valid categories
							$workflow->add_data_item('tag', $tag );
							return true;
						}
					}
				}
			}
		}

		return false;
	}


}
