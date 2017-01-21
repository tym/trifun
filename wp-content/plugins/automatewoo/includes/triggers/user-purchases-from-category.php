<?php
/**
 * Only allows a single category choice as the text variable system only supports single data items
 *
 * @class        AW_Trigger_User_Purchases_From_Category
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_User_Purchases_From_Category extends AW_Trigger
{
	public $name = 'user_purchases_from_category';

	public $supplied_data_items = [ 'user', 'order', 'category', 'order_item', 'product', 'shop' ];


	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __('Order Includes Product From Category', 'automatewoo');
		$this->group = __( 'Order', 'automatewoo' );

		// Registers the trigger
		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$category = new AW_Field_Category();
		$category->set_description( __( 'Only trigger when the a product is purchased from a certain category.', 'automatewoo'  ) );
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
		add_action( 'woocommerce_order_status_changed', [ $this, 'catch_hooks' ], 100, 1 );
	}


	/**
	 * Route hooks through here
	 */
	function catch_hooks( $order_id )
	{
		$order = wc_get_order( $order_id );
		$user = AW()->order_helper->prepare_user_data_item( $order );

		foreach ( $order->get_items() as $order_item_id => $order_item )
		{
			$this->maybe_run([
				'order' => $order,
				'order_item' => AW()->order_helper->prepare_order_item( $order_item_id, $order_item ),
				'user' => $user,
				'product' => $order->get_product_from_item( $order_item )
			]);
		}
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
		$product = $workflow->get_data_item('product');

		if ( ! $user || ! $order || ! $product )
			return false;

		if ( ! $this->validate_order_status_field( $trigger, $order ) )
			return false;

		if ( ! $expected_category_id = absint( $workflow->get_trigger_option('category') ) )
			return false;

		$categories = get_the_terms( $product->id, 'product_cat' );

		if ( ! $categories )
			return false;

		foreach ( $categories as $category )
		{
			if ( $category->term_id == $expected_category_id )
			{
				$workflow->set_data_item( 'category', $category );
				return true;
			}
		}

		return false;
	}

}
