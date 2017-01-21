<?php
/**
 * @package		AutomateWoo/Admin/Views
 *
 * @var $tool AW_Tool
 */
?>

<h1><a href="<?php echo AW()->admin->page_url('tools') ?>"><?php _e( 'Tools', 'automatewoo' ) ?></a> &gt; <?php echo $tool->title ?></h1>

<?php AW_Admin_Controller_Tools::output_messages(); ?>

<?php echo wpautop( $tool->description ) ?>

<?php if ( $tool->additional_description ): ?>
	<?php echo wpautop( $tool->additional_description ) ?>
<?php endif ?>

