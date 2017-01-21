<?php
/**
 * @class       AW_Field_Product
 * @package     AutomateWoo/Fields
 */

class AW_Field_Product extends AW_Field {

	protected $default_title = 'Product';

	protected $default_name = 'product';

	protected $type = 'product';

	public $allow_variations = false;


	/**
	 * @param $value
	 *
	 * @return void
	 */
	function render( $value ) {

		$ajax_action = $this->allow_variations ? 'woocommerce_json_search_products_and_variations' : 'woocommerce_json_search_products';

		?>

		<input type="hidden" class="wc-product-search <?php echo $this->get_classes() ?>"
		       name="<?php echo $this->get_full_name(); ?>"
		       data-placeholder="<?php _e( 'Search for a product&hellip;', 'automatewoo' ); ?>"
		       data-action="<?php echo $ajax_action ?>"
		       data-selected="<?php

		       $product_id = absint( $value );
		       if ( $product_id && $product = wc_get_product( $product_id ) )
		       {
					 echo htmlspecialchars( $product->get_formatted_name() );
		       }

		       ?>"
		       value="<?php echo ( $product_id ? $product_id : '' ); ?>" />

		<script type="text/javascript">
			jQuery( 'body' ).trigger( 'wc-enhanced-select-init' );
		</script>

	<?php
	}
}
