<?php
/**
 * @class		AW_Report_Abstract_Graph
 * @package		AutomateWoo/Admin/Reports
 * @since		2.4.7
 */

if ( ! class_exists( 'WC_Admin_Report' ) )
{
	include_once( WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php' );
}


class AW_Report_Abstract_Graph extends WC_Admin_Report
{
	public $chart_colours = [];


	/**
	 * Output the report
	 */
	function output_report()
	{
		$ranges = array(
			'year'         => __( 'Year', 'automatewoo' ),
			'last_month'   => __( 'Last Month', 'automatewoo' ),
			'month'        => __( 'This Month', 'automatewoo' ),
			'7day'         => __( 'Last 7 Days', 'automatewoo' )
		);

		$current_range = ! empty( $_GET['range'] ) ? aw_clean( $_GET['range'] ) : '7day';

		if ( ! in_array( $current_range, array( 'custom', 'year', 'last_month', 'month', '7day' ) ) )
			$current_range = '7day';

		$this->calculate_current_range( $current_range );

		include( WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php');
	}



	/**
	 * Output an export link
	 */
	function get_export_button()
	{
		$current_range = ! empty( $_GET['range'] ) ? aw_clean( $_GET['range'] ) : '7day';
		?>
		<a
			href="#"
			download="automatewoo-report-<?php echo esc_attr( $current_range ); ?>-<?php echo date_i18n( 'Y-m-d', current_time('timestamp') ); ?>.csv"
			class="export_csv"
			data-export="chart"
			data-xaxes="<?php _e( 'Date', 'automatewoo' ); ?>"
			data-groupby="<?php echo $this->chart_groupby; ?>"
		>
			<?php _e( 'Export CSV', 'automatewoo' ); ?>
		</a>
		<?php
	}



	/**
	 * @return array
	 */
	function get_filtered_workflows()
	{
		if ( is_array( aw_request('workflow_ids') ) )
		{
			return array_filter( array_map( 'absint', aw_request('workflow_ids') ) );
		}
		elseif ( aw_request('workflow_ids') )
		{
			return array_filter( array( absint( aw_request('workflow_ids') ) ) );
		}
	}


	/**
	 * Workflows selection widget
	 */
	function output_workflows_widget()
	{
		?>
		<h4 class="section_title"><span><?php _e( 'Workflow Search', 'automatewoo' ); ?></span></h4>
		<div class="section">
			<form method="GET">
				<div>
					<input type="hidden" class="wc-product-search" style="width:203px;" name="workflow_ids[]" data-placeholder="<?php _e( 'Search for a workflow&hellip;', 'automatewoo' ); ?>" data-action="aw_json_search_workflows" />
					<input type="submit" class="submit button" value="<?php _e( 'Show', 'automatewoo' ); ?>" />
					<?php AW()->admin->get_hidden_form_inputs_from_query( ['range', 'start_date', 'end_date', 'page', 'tab' ] ) ?>
				</div>
			</form>
		</div>


		<script type="text/javascript">
			jQuery('.section_title').click(function(){
				var next_section = jQuery(this).next('.section');

				if ( jQuery(next_section).is(':visible') )
					return false;

				jQuery('.section:visible').slideUp();
				jQuery('.section_title').removeClass('open');
				jQuery(this).addClass('open').next('.section').slideDown();

				return false;
			});
			jQuery('.section').slideUp( 100, function() {
				jQuery('.section_title:first').click();
			});
		</script>
		<?php
	}

}
