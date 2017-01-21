<?php
/**
 * @class       AW_Field_Checkbox
 * @package     AutomateWoo/Fields
 */

class AW_Field_Checkbox extends AW_Field {

	protected $default_title = 'Checkbox';

	protected $default_name = 'checkbox';

	protected $type = 'checkbox';

	public $default_to_checked = false;

	/**
	 * @param bool $checked
	 * @return $this
	 */
	function set_default_to_checked( $checked = true ) {
		$this->default_to_checked = $checked;
		return $this;
	}


	/**
	 * @param $value
	 *
	 * @return void
	 */
	function render( $value ) {
		if ( $value === null ) $value = $this->default_to_checked;

		?>
		<input type="checkbox"
		       name="<?php echo $this->get_full_name() ?>"
		       value="1"
			   <?php echo ( $value ? 'checked' : '' ) ?>
		       class="aw-field <?php echo $this->get_classes() ?>"
			   <?php echo $this->get_extra_attrs(); ?>
			>
	<?php
	}


}