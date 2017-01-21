<?php
/**
 * @class 		AW_Rule_User_Purchased_Products
 * @package		AutomateWoo/Rules
 */

class AW_Rule_User_Purchased_Products extends AW_Rule_Abstract_Object
{
	public $data_item = 'user';

	public $is_multi = false;

	public $ajax_action = 'woocommerce_json_search_products_and_variations';

	public $class = 'wc-product-search';


	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'User Purchased Products', 'automatewoo' );
		$this->group = __( 'User', 'automatewoo' );
		$this->placeholder = __( 'Search products...', 'automatewoo' );

		$this->compare_types = [
			'includes' => __( 'includes', 'automatewoo' ),
			'not_includes' => __( 'does not include', 'automatewoo' )
		];
	}


	/**
	 * @param $value
	 * @return string
	 */
	function get_object_display_value( $value )
	{
		if ( $product = wc_get_product( absint( $value ) ) )
			return $product->get_formatted_name();
	}


	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $compare
	 * @param $expected_value
	 * @return bool
	 */
	function validate( $user, $compare, $expected_value )
	{
		if ( ! $product = wc_get_product( absint( $expected_value ) ) )
			return false;

		// support product variations
		$product_id = $product->get_type() == 'variation' ? $product->variation_id : $product->id;

		$includes = wc_customer_bought_product( $user->user_email, $user->ID, $product_id );

		switch ( $compare )
		{
			case 'includes':
				return $includes;
				break;

			case 'not_includes':
				return ! $includes;
				break;
		}

	}
}

return new AW_Rule_User_Purchased_Products();