<?php
/**
 * @class       AW_Field_Attribute_Term
 * @package     AutomateWoo/Fields
 */

class AW_Field_Attribute_Term extends AW_Field {

	protected $default_name = 'term';

	protected $type = 'term';

	/**
	 * AW_Field_Attribute_Term constructor.
	 */
	function __construct() {
		$this->default_title = __( 'Terms', 'automatewoo' );
	}


	/**
	 * @param $value
	 *
	 * @return void
	 */
	function render( $value ) {

		$values = explode( ',', $value );

		$display_values = [];

		foreach ( $values as $value ) {
			if ( strstr( $value, '|' ) ) {
				list( $term_id, $taxonomy ) = explode( '|', $value );

				if ( $term = get_term_by( 'id', $term_id, $taxonomy ) ) {
					$display_values[ $value ] = wp_kses_post( $term->name );
				}
			}
		}

		?>

		<input type="hidden" class="automatewoo-json-search <?php echo $this->get_classes() ?>"
			 name="<?php echo $this->get_full_name(); ?>"
			 data-placeholder="<?php _e( 'Search for a term&hellip;', 'automatewoo' ); ?>"
			 data-action="aw_json_search_attribute_terms"
			 data-multiple="true"
			 data-pass-sibling="aw_workflow_data[trigger_options][attribute]"
			 data-selected="<?php echo esc_attr( json_encode( $display_values ) ); ?>"
			 value="<?php echo implode( ',', $values ); ?>" >

	<?php
	}

}