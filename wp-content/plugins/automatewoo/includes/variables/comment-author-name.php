<?php
/**
 * @class 		AW_Variable_Comment_Author_Name
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Comment_Author_Name extends AW_Variable
{
	protected $name = 'comment.author_name';

	function init()
	{
		$this->description = __( "Displays the name of the comment author.", 'automatewoo');
	}

	/**
	 * @param $comment WP_Comment
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $comment, $parameters )
	{
		return $comment->comment_author;
	}
}

return new AW_Variable_Comment_Author_Name();