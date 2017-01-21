<?php
/**
 * @class 		AW_Variable_Comment_Author_IP
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Comment_Author_IP extends AW_Variable
{
	protected $name = 'comment.author_ip';

	function init()
	{
		$this->description = __( "Displays the IP address of the comment author.", 'automatewoo');
	}

	/**
	 * @param $comment WP_Comment
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $comment, $parameters )
	{
		return $comment->comment_author_IP;
	}
}

return new AW_Variable_Comment_Author_IP();