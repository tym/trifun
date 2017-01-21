<?php
/**
 * @class		AW_Report_Logs
 * @package		AutomateWoo/Admin/Reports
 */

class AW_Report_Logs extends AW_Report_List_Table {

	public $_column_headers;
	public $max_items;
	public $show_tablenav_at_top = true;

	public $nonce_action = 'logs-action';


	/**
	 * __construct function.
	 */
	function __construct() {
		parent::__construct([
			'singular' => __( 'Log', 'automatewoo' ),
			'plural' => __( 'Logs', 'automatewoo' ),
			'ajax' => false
		]);
	}


	function filters() {

		$user_string = '';
		$user_id = '';
		$workflow_id = '';
		$workflow_name = '';

		if ( ! empty( $_GET['_workflow'] ) ) {
			$workflow_id = absint( $_GET['_workflow'] );
			$workflow_name = get_the_title( $workflow_id );
		}

		if ( ! empty( $_GET['_customer_user'] ) ) {
			$user_id = absint( $_GET['_customer_user'] );
			$user = get_user_by( 'id', $user_id );
			$user_string = esc_html( $user->display_name ) . ' (#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email );
		}

		?>

		<input type="hidden" class="wc-product-search" style="width:203px;" name="_workflow"
				 data-placeholder="<?php _e( 'Search for a workflow&hellip;', 'automatewoo' ); ?>"
				 data-selected="<?php echo htmlspecialchars( $workflow_name ); ?>" value="<?php echo $workflow_id; ?>"
				 data-action="aw_json_search_workflows" data-allow_clear="true">

		<input type="hidden" class="wc-customer-search" name="_customer_user"
				 data-placeholder="<?php esc_attr_e( 'Search for a customer&hellip;', 'automatewoo' ); ?>"
				 data-selected="<?php echo htmlspecialchars( $user_string ); ?>" value="<?php echo $user_id; ?>"
				 data-allow_clear="true">
		<?php
	}


	function no_items() {
		_e( 'No logs found.', 'automatewoo' );
	}


	/**
	 * @param $log AW_Model_Log
	 * @return string
	 */
	function column_cb( $log ) {
		return '<input type="checkbox" name="log_ids[]" value="' . absint( $log->id ) . '" />';
	}


	/**
	 * column_default function.
	 *
	 * @param AW_Model_Log $log
	 * @param mixed $column_name
	 */
	function column_default( $log, $column_name ) {

		switch( $column_name ) {
			case 'id':
				echo '#' . $log->id;
				break;

			case 'workflow':

				$this->format_workflow_title( $log->get_workflow() );

				break;

			case 'user':

				if ( $email = $log->get_meta('guest_email') ) {
					$this->format_guest($email);
				}
				elseif ( $user_id = $log->get_meta('user_id') ) {
					$user = get_user_by( 'id', $user_id );
					$this->format_user($user);
				}
				else {
					$this->format_blank();
				}
				break;

			case 'time':
				$this->format_date( $log->date );
				break;

			case 'actions':

				$url = add_query_arg([
					'action' => 'aw_modal_log_info',
					'log_id' => $log->id
					], admin_url('admin-ajax.php') );

				echo '<a class="button view aw-button-icon js-open-automatewoo-modal" data-modal-type="ajax" href="' . $url . '">View</a>';

				break;
		}
	}

	/**
	 * get_columns function.
	 */
	function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'id'  => __( 'Log', 'automatewoo' ),
			'workflow'  => __( 'Workflow', 'automatewoo' ),
			'user' => __( 'User', 'automatewoo' ),
			'time' => __( 'Time', 'automatewoo' ),
			'actions' => '',
		];

		return $columns;
	}

	/**
	 * prepare_items function.
	 */
	function prepare_items() {

		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];
		$current_page = absint( $this->get_pagenum() );
		$per_page = apply_filters( 'automatewoo_report_items_per_page', 20 );

		$this->get_items( $current_page, $per_page );

		/**
		 * Pagination
		 */
		$this->set_pagination_args([
			'total_items' => $this->max_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		]);
	}



	/**
	 * Get Products matching stock criteria
	 */
	function get_items( $current_page, $per_page ) {

		$query = new AW_Query_Logs();
		$query->set_limit( $per_page );
		$query->set_offset( ($current_page - 1 ) * $per_page );
		$query->set_ordering('date', 'DESC');

		if ( ! empty($_GET['_workflow']) ) {
			$query->where('workflow_id', absint( $_GET['_workflow'] ) );
		}

		if ( ! empty($_GET['_customer_user']) ) {
			$query->where('user_id', absint($_GET['_customer_user']) );
		}

		$this->items = $query->get_results();
		$this->max_items = $query->found_rows;

	}


	/**
	 * Retrieve the bulk actions
	 */
	function get_bulk_actions() {
		$actions = [
			'bulk_delete' => __( 'Delete', 'automatewoo' ),
		];

		return $actions;
	}


}
