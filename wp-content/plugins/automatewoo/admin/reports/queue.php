<?php
/**
 * @class		AW_Report_Queue
 * @package		AutomateWoo/Admin/Reports
 */

class AW_Report_Queue extends AW_Report_List_Table {

	public $_column_headers;

	public $max_items;

	/**
	 * __construct function.
	 */
	function __construct() {
		parent::__construct([
			'singular' => __( 'Event', 'automatewoo' ),
			'plural' => __( 'Events', 'automatewoo' ),
			'ajax' => false
		]);
	}


	function filters() {
		$workflow_id = empty( $_GET['_workflow'] ) ? '' : absint( $_GET['_workflow'] );
		$workflow_name = $workflow_id ? get_the_title( $workflow_id ) : '';

		?>

		<input type="hidden" class="wc-product-search" style="width:203px;" name="_workflow"
				 data-placeholder="<?php esc_attr_e( 'Search for a workflow&hellip;', 'automatewoo' ); ?>"
				 data-selected="<?php echo htmlspecialchars( $workflow_name ); ?>" value="<?php echo $workflow_id; ?>"
				 data-action="aw_json_search_workflows" data-allow_clear="true">

		<?php
	}



	/**
	 * No items found text
	 */
	function no_items() {
		_e( 'No results found.', 'automatewoo' );
	}


	/**
	 * @param $queued_event AW_Model_Queued_Event
	 * @return string
	 */
	function column_cb( $queued_event ) {
		return '<input type="checkbox" name="queued_event_ids[]" value="' . absint( $queued_event->id ) . '" />';
	}


	/**
	 * column_default function.
	 *
	 * @param $item AW_Model_Queued_Event
	 * @param mixed $column_name
	 */
	function column_default( $item, $column_name ) {

		$workflow = $item->get_workflow();

		switch( $column_name ) {
			case 'queued_event_id':
				echo '#' . $item->id . '';
				echo ( $item->failed ? '<span class="error-message"> - ' . esc_attr__( 'Failed', 'automatewoo' ) . '</span>' : '' );
				break;

			case 'workflow':
				$this->format_workflow_title( $workflow );
				break;


			case 'user':

				if ( $workflow ) {
					if ( $user = $workflow->get_data_item('user') ) {
						$this->format_user( $user );
					}
					elseif( $guest = $workflow->get_data_item('guest') ) {
						$this->format_guest( $guest->email );
					}
					else {
						$this->format_blank();
					}
				}

				break;

			case 'date':

				if ( strtotime( $item->date ) > time() ) {
					$this->format_date( $item->date );
				}
				else {
					_e( 'now', 'automatewoo' );
				}

				break;

			case 'actions':

				$run_url = wp_nonce_url(
					add_query_arg([
						'action' => 'run_now',
						'queued_event_id' => $item->id
					]),
					$this->nonce_action
				);

				?>
					<a class="button" href="<?php echo $run_url; ?>"><?php esc_attr_e( 'Run Now', 'automatewoo' ) ?></a>
				<?php

				break;

		}
	}

	/**
	 * get_columns function.
	 */
	function get_columns() {
		$columns = [
			'cb' => '<input type="checkbox" />',
			'queued_event_id' => __( 'Queued Event', 'automatewoo' ),
			'workflow' => __( 'Workflow', 'automatewoo' ),
			'user' => __( 'User', 'automatewoo' ),
			'date' => __( 'Run Date', 'automatewoo' ),
			'actions' => __( 'Actions', 'automatewoo' ),
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

		$this->set_pagination_args([
			'total_items' => $this->max_items,
			'per_page' => $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		]);
	}



	/**
	 * Get Products matching stock criteria
	 */
	function get_items( $current_page, $per_page ) {

		$query = new AW_Query_Queue();
		$query->set_limit( $per_page );
		$query->set_offset( ($current_page - 1 ) * $per_page );
		$query->set_ordering('date', 'ASC');

		if ( ! empty( $_GET[ '_workflow' ] ) ) {
			$query->where( 'workflow_id', absint( $_GET['_workflow'] ) );
		}

		$res = $query->get_results();

		$this->items = $res;

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
