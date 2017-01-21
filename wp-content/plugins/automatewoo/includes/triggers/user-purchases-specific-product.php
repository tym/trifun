<?php
/**
 * Only allows a single product choice as the text variable system only supports single data items
 *
 * @class       AW_Trigger_User_Purchases_Specific_Product
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_User_Purchases_Specific_Product extends AW_Trigger
{
	/** @var string */
	public $name = 'user_purchases_specific_product';

	/** @var array */
	public $supplied_data_items = [ 'user', 'order', 'product', 'order_item', 'shop' ];


	/**
	 * Construct
	 */
	function init()
	{
		$this->title = __( 'Order Includes a Specific Product', 'automatewoo');
		$this->group = __( 'Order', 'automatewoo' );

		// Registers the trigger
		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$product = new AW_Field_Product();
		$product->allow_variations = true;
		$product->set_description( __( 'Only trigger when a certain product is purchased.', 'automatewoo'  ) );
		$product->set_required(true);

		$order_status = new AW_Field_Order_Status( false );
		$order_status->set_required(true);
		$order_status->set_default('wc-completed');

		$this->add_field( $product );
		$this->add_field( $order_status );
		$this->add_field_validate_queued_order_status();
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
	 *
	 * @param $order_id
	 */
	function catch_hooks( $order_id )
	{
		$order = wc_get_order( $order_id );
		$user = AW()->order_helper->prepare_user_data_item( $order );

		$this->maybe_run(array(
			'order' => $order,
			'user' => $user,
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


		$valid_product = wc_get_product( absint( $workflow->get_trigger_option('product') ) );


		// Validate product
		if ( $valid_product )
		{
			$order_item_id_key = $valid_product->get_type() == 'variation' ? 'variation_id' : 'product_id';

			// can't use get_id() method only added in WC 2.5
			$product_id_property = $valid_product->get_type() == 'variation' ? 'variation_id' : 'id';

			foreach ( $order->get_items() as $order_item_id => $order_item )
			{
				if ( $order_item[$order_item_id_key] > 0 )
				{
					if ( $order_item[$order_item_id_key] == $valid_product->{$product_id_property} )
					{
						// Pass data item through to workflow
						$workflow->set_data_item( 'product', $valid_product );
						$workflow->set_data_item( 'order_item', AW()->order_helper->prepare_order_item( $order_item_id, $order_item ) );

						return true;
					}
				}
			}
		}

		return false;
	}



	/**
	 * Ensures 'to' status has not changed while sitting in queue
	 *
	 * @param $workflow
	 *
	 * @return bool
	 */
	function validate_before_queued_event( $workflow )
	{
		// check parent
		if ( ! parent::validate_before_queued_event( $workflow ) )
			return false;

		$order = $workflow->get_data_item('order');

		if ( ! $order )
			return false;

		// Option to validate order status
		if ( $workflow->get_trigger_option('validate_order_status_before_queued_run') )
		{
			if ( ! $this->validate_status_field( $workflow->get_trigger_option('order_status'), $order->get_status() ) )
				return false;
		}

		return true;
	}


}
