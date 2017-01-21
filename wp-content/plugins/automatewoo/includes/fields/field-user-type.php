<?php
/**
 * For all users return val will be false
 *
 * @class       AW_Field_User_Type
 * @package     AutomateWoo/Fields
 */

class AW_Field_User_Type extends AW_Field_Select {

	protected $default_title = 'User Type';

	protected $default_name = 'user_type';

	protected $type = 'select';

	/**
	 * @param bool $allow_all
	 * @param bool $allow_guest
	 */
	function __construct( $allow_all = true, $allow_guest = true ) {

		if ( $allow_all )
			$this->set_placeholder('All');
		else
			$this->set_placeholder('- Select -');


		global $wp_roles;

		$options = [];

		foreach( $wp_roles->roles as $key => $role ) {
			$options[$key] = $role['name'];
		}

		if ( $allow_guest )
			$options['guest'] = 'Guest';

		$this->set_options( $options );
	}

}
