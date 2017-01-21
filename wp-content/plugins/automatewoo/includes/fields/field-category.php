<?php
/**
 * @class       AW_Field_Category
 * @package     AutomateWoo/Fields
 */

class AW_Field_Category extends AW_Field {

	protected $default_title = 'Product Category';

	protected $default_name = 'category';

	protected $type = 'category';


	/**
	 * @param $value
	 *
	 * @return void
	 */
	function render( $value ) {
		?>

		<select name="<?php echo $this->get_full_name(); ?>"
		        class="wc-enhanced-select <?php echo $this->get_classes() ?>"
		        data-placeholder="<?php _e( '- Select - ', 'automatewoo' ); ?>">

			<option value=""><?php _e( '- Select -', 'automatewoo' ); ?></option>

			<?php

			$categories = get_terms( 'product_cat', 'orderby=name&hide_empty=0' );

			if ( $categories ) foreach ( $categories as $cat ) {
				echo '<option value="' . esc_attr( $cat->term_id ) . '" ' . selected( $cat->term_id, $value, false ) . '>' . esc_html( $cat->name ) . '</option>';
			}
			?>
		</select>

		<script type="text/javascript">
			jQuery( 'body' ).trigger( 'wc-enhanced-select-init' );
		</script>

	<?php
	}

}