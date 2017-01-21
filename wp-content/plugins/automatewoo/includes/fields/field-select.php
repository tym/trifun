<?php
/**
 * @class       AW_Field_Select
 * @package     AutomateWoo/Fields
 */

class AW_Field_Select extends AW_Field {

	protected $default_title = 'Select';

	protected $default_name = 'select';

	protected $type = 'select';

	protected $default_option;

	public $multiple = false;

	protected $options = [];


	/**
	 * @param bool $show_placeholder
	 */
	function __construct( $show_placeholder = true ) {
		if ( $show_placeholder )
			$this->set_placeholder('- Select -');
	}


	/**
	 * @param $options
	 * @return $this
	 */
	function set_options( $options ) {
		$this->options = $options;
		return $this;
	}


	/**
	 * @return array
	 */
	function get_options() {
		return $this->options;
	}


	/**
	 * @param $option
	 * @return $this
	 */
	function set_default( $option ) {
		$this->default_option = $option;
		return $this;
	}


	/**
	 * @param bool $multi
	 * @return $this
	 */
	function set_multiple( $multi = true ) {
		$this->multiple = $multi;
		return $this;
	}



	/**
	 * @param $value
	 *
	 * @return void
	 */
	function render( $value = false ) {

		if ( $this->multiple ) {
			if ( ! $value ) {
				$value = $this->default_option ? $this->default_option : [];
			}

			$this->render_multiple( (array) $value );
		}
		else {
			if ( empty( $value ) && $this->default_option ) {
				$value = $this->default_option;
			}

			$this->render_single( $value );
		}

	}


	/**
	 * @param $value
	 */
	function render_single( $value ) {
		?>

		<select name="<?php echo $this->get_full_name() ?>"
		        class="aw-field <?php echo $this->get_classes() ?>"
			<?php echo $this->get_extra_attrs() ?>>

			<?php if ( $this->get_placeholder() ): ?>
				<option value=""><?php echo $this->get_placeholder() ?></option>
			<?php endif; ?>

			<?php foreach( $this->get_options() as $opt_name => $opt_value ): ?>
				<?php if ( is_array($opt_value) ): ?>
					<optgroup label="<?php echo $opt_name ?>">
						<?php foreach( $opt_value as $opt_sub_name => $opt_sub_value ): ?>
							<option value="<?php echo $opt_sub_name; ?>" <?php selected( $value, $opt_sub_name ) ?>><?php echo $opt_sub_value; ?></option>
						<?php endforeach?>
					</optgroup>
				<?php else: ?>
					<option value="<?php echo $opt_name; ?>" <?php selected( $value, $opt_name ) ?>><?php echo $opt_value; ?></option>
				<?php endif; ?>
			<?php endforeach; ?>

		</select>

	<?php
	}


	/**
	 *
	 * @param $values
	 */
	function render_multiple( $values ) {
?>
		<select name="<?php echo $this->get_full_name() ?>[]"
		        class="aw-field <?php echo $this->get_classes() ?> wc-enhanced-select" multiple="multiple" placeholder="<?php echo $this->get_placeholder() ?>"
			<?php echo $this->get_extra_attrs() ?>>

			<?php foreach( $this->get_options() as $opt_name => $opt_value ): ?>
				<option value="<?php echo $opt_name; ?>"
					<?php echo ( in_array( $opt_name, $values ) ? 'selected="selected"' : '' ) ?>
					><?php echo $opt_value; ?></option>
			<?php endforeach; ?>

		</select>

		<script type="text/javascript">
			jQuery( 'body' ).trigger( 'wc-enhanced-select-init' );
		</script>

<?php
	}

}