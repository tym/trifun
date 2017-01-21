<?php
/**
 * @class 		AW_Admin_Workflow_List
 * @package		AutomateWoo/Admin
 * @since		2.6.1
 */

class AW_Admin_Workflow_List {

	/**
	 * Constructor
	 */
	function __construct() {

		add_action( 'pre_get_posts', [ $this, 'set_default_order'] );

		add_filter( 'manage_posts_columns' , [ $this, 'columns'] );
		add_filter( 'manage_posts_custom_column' , [ $this, 'column_data'], 10 , 2 );
		add_filter( 'bulk_actions-edit-aw_workflow' , [ $this, 'bulk_actions' ], 10 , 2 );
		add_filter( 'post_row_actions' , [ $this, 'row_actions' ], 10 , 2 );

		$this->statuses();
	}


	/**
	 * @param $columns
	 * @return array
	 */
	function columns( $columns ) {

		unset( $columns['date'] );

		$columns['times_run'] = __( 'Times Run', 'automatewoo' );
		$columns['queued'] = __( 'Current Queue', 'automatewoo' );
		$columns['aw_status_toggle'] = '';

		return $columns;
	}


	/**
	 * @param $column
	 * @param $post_id
	 */
	function column_data( $column, $post_id ) {

		$workflow = AW()->get_workflow( $post_id );

		if ( ! $workflow )
			return;

		switch ( $column ) {
			case 'times_run':
				echo '<a href="' . add_query_arg( '_workflow', $workflow->id, AW()->admin->page_url('logs') ) . '">' . $workflow->get_times_run() . '</a>';
				break;

			case 'queued':
				echo '<a href="' . add_query_arg( '_workflow', $workflow->id, AW()->admin->page_url('queue') ) . '">' . $workflow->get_current_queue_count() . '</a>';
				break;

			case 'aw_status_toggle':
				echo '<button type="button" class="aw-switch js-toggle-workflow-status" '
					. 'data-workflow-id="'. $workflow->id .'" '
					. 'data-aw-switch="'. ( $workflow->is_active() ? 'on' : 'off' ) . '">'
					. __( 'Toggle Status', 'automatewoo' ) . '</button>';
				break;

		}
	}


	/**
	 * @param $query WP_Query
	 */
	function set_default_order( $query ) {

		if ( ! $query->is_main_query() ) return;

		if ( get_query_var('orderby') ) return;

		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC' );
	}


	/**
	 * Tweak workflow statuses
	 */
	function statuses() {

		global $wp_post_statuses;

		// rename published
		$wp_post_statuses['publish']->label_count = _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'automatewoo' );

		$trash = $wp_post_statuses['trash'];
		unset( $wp_post_statuses['trash'] );
		$wp_post_statuses['trash'] = $trash;
	}


	/**
	 * @param $actions
	 * @return mixed
	 */
	function bulk_actions( $actions ) {
		unset($actions['edit']);
		return $actions;
	}


	/**
	 * @param $actions
	 * @return mixed
	 */
	function row_actions( $actions ) {
		unset($actions['inline hide-if-no-js']);
		return $actions;
	}

}

new AW_Admin_Workflow_List();
