<?php
/**
 * @class 		AW_Variable_Abstract_Datetime
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Abstract_Datetime extends AW_Variable
{
	public $_desc_format_tip;

	function init()
	{
		$this->_desc_format_tip = sprintf(
			__( "To modify how dates appear refer to the %sPHP date format documentation%s.", 'automatewoo'),
			'<a href="http://php.net/manual/en/function.date.php#refsect1-function.date-parameters" target="_blank">', '</a>'
		);

		$this->add_parameter_text_field( 'format',
			__("Optional parameter to modify the display of the datetime. Default is MySQL format (Y-m-d H:i:s)", 'automatewoo' ),
			false, 'Y-m-d H:i:s' );

		$this->add_parameter_text_field( 'modify',
			__( "Optional parameter to modify the value of the datetime. Uses the PHP strtotime() function.", 'automatewoo' ), false,
			__( "e.g. +2 months, -1 day, +6 hours", 'automatewoo' )
		);
	}


	/**
	 * @param DateTime|string $date
	 * @param array $parameters (modify, format)
	 *
	 * @param string $format
	 * @return string|void
	 */
	function format_datetime( $date, $parameters, $format = 'Y-m-d H:i:s' )
	{
		if ( ! $date )
			return false;

		if ( is_string( $date ) )
		{
			$date = new DateTime( $date );
		}

		if ( ! empty( $parameters['format'] ) )
		{
			$format = $parameters['format'];
		}

		if ( ! empty( $parameters['modify'] ) )
		{
			$date->modify( $parameters['modify'] );
		}

		return $date->format( $format );
	}
}
