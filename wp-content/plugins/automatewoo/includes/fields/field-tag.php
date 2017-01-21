<?php
/**
 * @class       AW_Field_Tag
 * @package     AutomateWoo/Fields
 */

class AW_Field_Tag extends AW_Field {

	protected $default_title = 'Product Tag';

	protected $default_name = 'tag';

	protected $type = 'tag';


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

			$tags = get_terms( 'product_tag', 'orderby=name&hide_empty=0' );

			if ( $tags ) foreach ( $tags as $tag ) {
				echo '<option value="' . esc_attr( $tag->term_id ) . '" ' . selected( $tag->term_id, $value, false ) . '>' . esc_html( $tag->name ) . '</option>';
			}
			?>
		</select>

		<script type="text/javascript">
			jQuery( 'body' ).trigger( 'wc-enhanced-select-init' );
		</script>


	<?php
	}
}
