<?php
/**
 * @class 		AW_Reports_Tab_Conversions
 */

class AW_Reports_Tab_Conversions extends AW_Admin_Reports_Tab_Abstract
{
	function __construct()
	{
		$this->id = 'conversions';
		$this->name = __( 'Conversions', 'automatewoo' );
	}


	/**
	 * @return object
	 */
	function get_report_class()
	{
		include_once AW()->admin_path( '/reports/abstract-graph.php' );
		include_once AW()->admin_path( '/reports/conversions.php' );

		return new AW_Report_Conversions();
	}
}

return new AW_Reports_Tab_Conversions();