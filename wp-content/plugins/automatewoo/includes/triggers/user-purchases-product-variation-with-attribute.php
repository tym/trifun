<?php
/**
 * @class       AW_Trigger_User_Purchases_Product_With_Attribute
 * @package     AutomateWoo/Triggers
 */

class AW_Trigger_User_Purchases_Product_Variation_With_Attribute extends AW_Trigger {

	public $name = 'user_purchases_product_variation_with_attribute';

	public $supplied_data_items = [ 'user', 'order', 'order_item', 'product', 'shop' ];

	function init() {
		$this->title = __('Order Includes Product Variation with Specific Attribute', 'automatewoo');
		$this->group = __( 'Order', 'automatewoo' );
		$this->description = __(
			"This trigger will look at the selected variations for each order item for the selected attribute terms. " .
			"For example if you have a attribute for 'size' and you select 'SML' and 'MED' in the Terms field then this trigger " .
			"will fire if an order is placed that contains a product in 'SML' or a product in 'MED'.",
			'automatewoo'  );

		// Registers the trigger
		parent::init();
	}


	/**
	 * Add options to the trigger
	 */
	function load_fields() {
		$attribute = new AW_Field_Attribute();
		$attribute->set_required(true);

		$terms = new AW_Field_Attribute_Term();
		$terms->set_required(true);

		$order_status = new AW_Field_Order_Status( false );
		$order_status->set_required(true);
		$order_status->set_default('wc-completed');

//		$each_item = ( new AW_Field_Checkbox() )
//			->set_name( 'fire_for_each_item' )
//			->set_title( __( '', 'automatewoo' ) )
//			->set_default_to_checked();


		$this->add_field( $attribute );
		$this->add_field( $terms );
		$this->add_field( $order_status );
	}



	/**
	 * When could this trigger run?
	 */
	function register_hooks() {
		add_action( 'woocommerce_order_status_changed', [ $this, 'catch_hooks' ], 100, 1 );
	}


	/**
	 * Route hooks through here
	 *
	 * @param $order_id
	 */
	function catch_hooks( $order_id ) {

		if ( ! $this->has_workflows() )
			return;

		$order = wc_get_order( $order_id );
		$user = AW()->order_helper->prepare_user_data_item( $order );

		// need to loop through every item as an order might have more than 1 product with a matching variation

		foreach ( $order->get_items() as $order_item_id => $order_item ) {

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
	function validate_workflow( $workflow ) {

		$trigger = $workflow->get_trigger();
		$user = $workflow->get_data_item('user');
		$order = $workflow->get_data_item('order');
		$order_item = $workflow->get_data_item('order_item');

		if ( ! $user || ! $order || ! $order_item )
			return false;


		// check status
		if ( ! $this->validate_order_status_field( $trigger, $order ) )
			return false;

		// Validate attribute terms
		$valid_attribute_terms = explode( ',', $workflow->get_trigger_option('term') );

		// no selected terms
		if ( empty( $valid_attribute_terms ) )
			return false;

		// look for at least 1 valid term
		foreach ( $valid_attribute_terms as $valid_attribute_term ) {

			if ( ! strstr( $valid_attribute_term, '|' ) )
				continue;

			list( $attribute_term_id, $taxonomy ) = explode( '|', $valid_attribute_term );

			$attribute_term = get_term( $attribute_term_id, $taxonomy );

			// does the order item have the matching attribute?
			if ( ! empty( $order_item[$taxonomy] ) ) {
				// match with slug
				if ( $order_item[$taxonomy] === $attribute_term->slug )
					return true;
			}
		}

		return false;
	}


}
