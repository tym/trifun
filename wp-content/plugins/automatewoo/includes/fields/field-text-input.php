<?php
/**
 * @class       AW_Field_Text_Input
 * @package     AutomateWoo/Fields
 * @since       1.0.0
 */

class AW_Field_Text_Input extends AW_Field {

	protected $default_title = 'Text Input';

	protected $default_name = 'text_input';

	protected $type = 'text';


	/**
	 * @param $value
	 *
	 * @return void
	 */
	function render( $value ) {

	?>
		<input type="<?php echo $this->get_type() ?>"
		       name="<?php echo $this->get_full_name() ?>"
		       value="<?php echo $value ?>"
		       class="aw-field <?php echo $this->get_classes() ?>"
		       placeholder="<?php echo $this->get_placeholder() ?>"
			   <?php echo $this->get_extra_attrs(); ?>
			   <?php echo ( $this->get_required() ? 'required' : '' ) ?>
			>

	<?php
	}

}