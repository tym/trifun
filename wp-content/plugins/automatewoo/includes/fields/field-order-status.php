<?php
/**
 * @class       AW_Field_Order_Status
 * @package     AutomateWoo/Fields
 * @since       1.9.0
 */

class AW_Field_Order_Status extends AW_Field_Select {

	protected $default_title = 'Order Status';

	protected $default_name = 'order_status';

	protected $type = 'select';


	/**
	 * @param bool $allow_all
	 */
	function __construct( $allow_all = true ) {

		if ( $allow_all )
			$this->set_placeholder('- Any -');
		else
			$this->set_placeholder('- Select -');

		$this->set_description( __( 'Select which order status to preform trigger.', 'automatewoo'  ) );

		$statuses = wc_get_order_statuses();

		$this->set_options( $statuses );
	}

}
