<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * AW_Report_List_Table
 *
 * @since 2.0
 */
class AW_Report_List_Table extends WP_List_Table {

	public $show_tablenav_at_top = false;

	public $nonce_action = 'automatewoo-report-action';


	/**
	 * @param array|string $args
	 */
	function __construct( $args ) {
		wp_enqueue_script('automatewoo-modal');

		parent::__construct( $args );
	}


	/**
	 * Output the report
	 */
	function output_report() {
		$this->prepare_items();
		echo '<div id="poststuff" class="woocommerce-reports-wide">';
		$this->display();
		echo '</div>';
	}

	/**
	 *
	 */
	function display_tablenav( $position ) {
		if ( $position != 'top' || $this->show_tablenav_at_top ) {
			parent::display_tablenav( $position );
		}
	}




	/**
	 * @param $user
	 */
	function format_user( $user ) {
		if ( $user )
			echo "$user->first_name $user->last_name <a href='mailto:$user->user_email'>$user->user_email</a> ";
		else
			$this->format_blank();

	}


	/**
	 * @param $email
	 */
	function format_guest( $email ) {
		if ( $email ) {
			$email = esc_attr( $email );
			echo esc_attr( __( '[Guest]', 'automatewoo' ) ) . ' <a href="mailto:'.$email.'">'.$email.'</a>';
		}
		else {
			$this->format_blank();
		}
	}


	/**
	 * @return array
	 */
	protected function get_table_classes() {
		return array( 'automatewoo-report-table', 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}


	/**
	 * @param $date_string
	 * @param bool $is_gmt
	 */
	function format_date( $date_string, $is_gmt = true ) {

		if ( $date_string && $date = aw_display_time( $date_string, false, $is_gmt ) ) {
			echo $date;
		}
		else {
			$this->format_blank();
		}
	}


	/**
	 * @param $workflow AW_Model_Workflow|false
	 */
	function format_workflow_title( $workflow ) {

		if ( ! $workflow || ! $workflow->exists ) {
			$this->format_blank();
		}
		else {
			echo '<a href="' . get_edit_post_link( $workflow->id ) . '"><strong>' . $workflow->title . '</strong></a>';

			if ( AW()->integrations()->is_wpml() ) {
				echo ' [' . $workflow->get_language() . ']';
			}
		}

	}


	function format_blank() {
		echo '-';
	}



	/**
	 * Display the table
	 */
	function display() {
		?>

		<form method="get">

			<?php AW()->admin->get_hidden_form_inputs_from_query([ 'page', 'section', 'tab' ] ); ?>

			<?php wp_nonce_field( $this->nonce_action, '_wpnonce', false ) ?>

			<?php $singular = $this->_args['singular']; ?>

			<div class="tablenav <?php echo esc_attr( 'top' ); ?>">

				<?php if ( $this->has_items() ): ?>
					<div class="alignleft actions bulkactions">
						<?php $this->bulk_actions( 'top' ); ?>

						<?php if ( method_exists( $this, 'filters' ) ): ?>
							<div style="display: inline-block">
								<?php $this->filters(); ?>
								<?php submit_button( __( 'Filter' ), 'button', 'submit', false ); ?>
							</div>
						<?php endif ?>

					</div>
				<?php endif;
				$this->extra_tablenav( 'top' );
				$this->pagination( 'top' );
				?>

				<br class="clear" />
			</div>

			<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
				<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
				</thead>

				<tbody id="the-list"<?php
				if ( $singular ) {
					echo " data-wp-lists='list:$singular'";
				} ?>>
				<?php $this->display_rows_or_placeholder(); ?>
				</tbody>

				<tfoot>
				<tr>
					<?php $this->print_column_headers( false ); ?>
				</tr>
				</tfoot>

			</table>

			<?php $this->display_tablenav( 'bottom' ); ?>

		</form>

		<?php
	}

}
