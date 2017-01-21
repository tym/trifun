<?php
/**
 * @class       AW_Field_Order_Type
 * @package     AutomateWoo/Fields
 * @since       2.2
 */

class AW_Field_Order_Type extends AW_Field_Select {

	protected $default_title = 'Order Type';

	protected $default_name = 'order_type';

	protected $type = 'select';

	public $multiple = true;


	/**
	 * @param bool $allow_all
	 */
	function __construct( $allow_all = true ) {

		global $wc_order_types;

		if ( $allow_all )
			$this->set_placeholder('- Any -');
		else
			$this->set_placeholder('- Select -');

		$this->set_default('WC_Order');

		$this->set_description( __( 'Only trigger for selected order types.', 'automatewoo'  ) );

		$options = [];

		foreach ( $wc_order_types as $type_id => $type ) {
			$options[$type['class_name']] = ucwords(str_replace('_', ' ', $type_id ));
		}

		$this->set_options( $options );
	}
}
