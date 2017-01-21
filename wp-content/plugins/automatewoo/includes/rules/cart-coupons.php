<?php
/**
 * @class 		AW_Rule_Cart_Coupons
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Cart_Coupons extends AW_Rule_Abstract_Select
{
	/** @var array  */
	public $data_item = 'cart';

	public $is_multi = true;

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'Cart Coupons', 'automatewoo' );
		$this->group = __( 'Cart', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_select_choices()
	{
		if ( ! isset( $this->select_choices ) )
		{
			$this->select_choices = [];

			$coupons = get_posts([
				'post_type' => 'shop_coupon',
				'posts_per_page' => -1
			]);

			foreach ( $coupons as $coupon )
			{
				$this->select_choices[$coupon->post_title] = $coupon->post_title;
			}
		}

		return $this->select_choices;
	}



	/**
	 * @param $cart AW_Model_Abandoned_Cart
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $cart, $compare, $value )
	{
		return $this->validate_select( array_keys( $cart->get_coupons() ), $compare, $value );
	}


}

return new AW_Rule_Cart_Coupons();
