<?php
/**
 * @package AutomateWoo/Admin/Views
 * @since 2.7.8
 *
 * @var string $page
 * @var string $heading
 * @var string $sidebar_content
 * @var string $messages
 * @var AW_Admin_Controller_Abstract $controller
 * @var AW_Report_List_Table $table
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wrap automatewoo-page automatewoo-page--<?php echo $page ?>">

	<h1><?php echo esc_attr( $heading ) ?></h1>

	<?php echo $messages ?>

	<div class="automatewoo-content automatewoo-content--has-sidebar">

		<div class="automatewoo-sidebar">
			<?php echo $sidebar_content ?>
		</div>

		<div class="automatewoo-main">
			<?php $table->display() ?>
		</div>

	</div>

</div>
