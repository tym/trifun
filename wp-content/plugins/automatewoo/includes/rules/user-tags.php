<?php
/**
 * @class 		AW_Rule_User_Tags
 * @package		AutomateWoo/Rules
 */

class AW_Rule_User_Tags extends AW_Rule_Abstract_Select
{
	public $data_item = 'user';

	public $is_multi = true;

	/**
	 * Init
	 */
	function init()
	{
		$this->title = __( 'User Tags', 'automatewoo' );
		$this->group = __( 'User', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_select_choices()
	{
		if ( ! isset( $this->select_choices ) )
		{
			$this->select_choices = [];

			$tags = get_terms([
				'taxonomy' => 'user_tag',
				'hide_empty' => false
			]);

			foreach ( $tags as $tag )
			{
				$this->select_choices[$tag->term_id] = $tag->name;
			}
		}

		return $this->select_choices;
	}


	/**
	 * @param $user WP_User|AW_Model_Order_Guest
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $user, $compare, $value )
	{
		if ( $user instanceof AW_Model_Order_Guest )
			return false;

		$tags = wp_get_object_terms( $user->ID, 'user_tag', [
			'fields' => 'ids'
		]);

		return $this->validate_select( $tags, $compare, $value );
	}

}

return new AW_Rule_User_Tags();
