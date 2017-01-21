<?php
/**
 * @class 		AW_Variable_Comment_ID
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Comment_ID extends AW_Variable
{
	protected $name = 'comment.id';

	function init()
	{
		$this->description = __( "Displays the ID of the comment.", 'automatewoo');
	}


	/**
	 * @param $comment WP_Comment
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $comment, $parameters )
	{
		return $comment->comment_ID;
	}
}

return new AW_Variable_Comment_ID();
