<?php
/**
 * @package		AutomateWoo/Admin/Views
 *
 * @var $tools array
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="wrap automatewoo-page automatewoo-page--tools">

	<h1><?php _e( 'Tools', 'automatewoo' ) ?></h1>

	<?php AW_Admin_Controller_Tools::output_messages(); ?>

	<div id="poststuff">
		<table class="aw_tools_table wc_status_table widefat" cellspacing="0"><tbody>

			<?php foreach ( $tools as $tool ): ?>
				<tr>
					<td class="">
						<a href="<?php echo AW_Admin_Controller_Tools::get_route_url( 'view', $tool ) ?>"><?php echo $tool->title; ?></a>
					</td>

					<td class="">
						<span class="description"><?php echo $tool->description; ?></span>
					</td>
				</tr>

			<?php endforeach; ?>

		</tbody></table>
	</div>


</div>


