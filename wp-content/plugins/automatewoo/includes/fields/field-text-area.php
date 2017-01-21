<?php
/**
 * @class       AW_Field_Text_Area
 * @package     AutomateWoo/Fields
 */

class AW_Field_Text_Area extends AW_Field {

	protected $default_title = 'Text Area';

	protected $default_name = 'text_area';

	protected $type = 'text_area';


	/**
	 * @param $rows int
	 * @return $this
	 */
	function set_rows( $rows ) {
		$this->add_extra_attr('rows', $rows );
		return $this;
	}


	/**
	 * @param $value
	 *
	 * @return void
	 */
	function render( $value ) {

	?>
		<textarea
		       name="<?php echo $this->get_full_name() ?>"
		       class="<?php echo $this->get_classes() ?>"
		       placeholder="<?php echo $this->get_placeholder() ?>"
			   <?php echo $this->get_extra_attrs(); ?>
			   <?php echo ( $this->get_required() ? 'required' : '' ) ?>
			><?php echo $value ?></textarea>

	<?php
	}


	/**
	 * @param $value
	 * @return mixed|string|void
	 */
	function esc_value( $value ) {
		return esc_textarea( $value );
	}

}
