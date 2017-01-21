<?php
/**
 * @class AW_Dashboard_Widget_Queue
 */

class AW_Dashboard_Widget_Queue extends AW_Dashboard_Widget_Abstract {

	public $id = 'queue';

	/**
	 * @return AW_Model_Queued_Event[]
	 */
	function get_logs() {

		$query = new AW_Query_Queue();
		$query->set_limit( 7 );
		$query->set_ordering( 'date', 'ASC' );

		return $query->get_results();
	}


	function output_content() {

		$queue = $this->get_logs();

		?>

		<div class="automatewoo-dashboard-list">

			<div class="automatewoo-dashboard-list__header">
				<div class="automatewoo-dashboard-list__heading">
					<?php _e( 'Upcoming queued events', 'automatewoo' ) ?>
				</div>
				<a href="<?php echo AW()->admin->page_url( 'queue' ) ?>" class="automatewoo-arrow-link"></a>
			</div>

			<?php if ( $queue ): ?>

				<div class="automatewoo-dashboard-list__items">

					<?php foreach ( $queue as $event ):

						$workflow = $event->get_workflow();

						?>

						<div class="automatewoo-dashboard-list__item">

							<a href="<?php echo get_edit_post_link( $workflow->id ) ?>" class="automatewoo-dashboard-list__item-title"><?php echo $workflow->title; ?></a>
							<div class="automatewoo-dashboard-list__item-text"><?php echo aw_display_time( $event->date ) ?></div>
						</div>

					<?php endforeach; ?>

				</div>

			<?php else: ?>

				<div class="automatewoo-dashboard-list__empty">
					<?php _e( 'There are no events currently queued&hellip;', 'automatewoo' ) ?>
				</div>

			<?php endif; ?>

		</div>

		<?php
	}

}

return new AW_Dashboard_Widget_Queue();
