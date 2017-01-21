<?php
/**
 * Custom Function Action
 *
 * @class       AW_Action_Custom_Function
 * @package     AutomateWoo/Actions
 */

class AW_Action_Custom_Function extends AW_Action {

	public $name = 'custom_function';


	function init() {
		$this->title = __('Custom Function', 'automatewoo');

		// Registers the actions
		parent::init();
	}


	function load_fields() {
		$function_name = new AW_Field_Text_Input();
		$function_name->set_title( __( 'Function Name', 'automatewoo'  ) );
		$function_name->set_name('function_name');
		$function_name->set_description( __( 'More about custom functions here.', 'automatewoo'  ) );

		$this->add_field($function_name);
	}


	/**
	 * @return mixed|void
	 */
	function run() {
		if ( function_exists( $this->get_option('function_name') ) ) {
			call_user_func( $this->get_option('function_name'), $this->workflow );
		}
	}

}