<?php
/**
 * @class       AW_Trigger_User_Purchases_From_Taxonomy_Term
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_User_Purchases_From_Taxonomy_Term extends AW_Trigger
{
	public $title;

	public $name = 'user_purchases_from_taxonomy_term';

	public $group = 'Order';

	public $supplied_data_items = array( 'user', 'order', 'shop' );


	function init()
	{
		$this->title = __('Order Includes Product from Taxonomy Term', 'automatewoo');

		// Registers the trigger
		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields()
	{
		$taxonomy = new AW_Field_Taxonomy();
		$taxonomy->set_required(true);

		$term = new AW_Field_Taxonomy_Term();
		$term->set_required(true);

		$order_status = new AW_Field_Order_Status( false );
		$order_status->set_required(true);
		$order_status->set_default('wc-completed');

		$this->add_field( $taxonomy );
		$this->add_field( $term );
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


		$stored_term_data = $workflow->get_trigger_option('term');

		if ( ! strstr( $stored_term_data, '|' ) )
			return false;

		list( $term_id, $taxonomy ) = explode( '|', $stored_term_data );


		// Validate taxonomy term
		if ( ! $term_id || ! $taxonomy )
			return false;


		foreach ( $order->get_items() as $item )
		{
			if ( $item['product_id'] > 0 )
			{
				$product_terms = get_the_terms( $item['product_id'], $taxonomy );

				if ( ! $product_terms )
					continue;

				foreach( $product_terms as $product_term )
				{
					if ( $product_term->term_id == $term_id )
					{
						// user has bought something from the valid categories
						return true;
					}
				}
			}
		}

		return false;
	}


}