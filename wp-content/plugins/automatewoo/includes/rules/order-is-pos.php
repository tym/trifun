<?php
/**
 * @class 		AW_Rule_Order_Is_POS
 * @package		AutomateWoo/Rules
 */

class AW_Rule_Order_Is_POS extends AW_Rule_Abstract_Bool
{
	public $data_item = 'order';

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( "Is POS Order?", 'automatewoo' );
		$this->group = __( 'POS', 'automatewoo' );
	}


	/**
	 * @param $order WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value )
	{
		$is_pos = (bool) get_post_meta( $order->id, '_pos', true );

		switch ( $value )
		{
			case 'yes':
				return $is_pos;
				break;

			case 'no':
				return ! $is_pos;
				break;
		}
	}

}

return new AW_Rule_Order_Is_POS();
