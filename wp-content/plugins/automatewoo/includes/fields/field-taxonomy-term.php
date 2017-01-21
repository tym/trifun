<?php
/**
 * @class       AW_Field_Taxonomy_Term
 * @package     AutomateWoo/Fields
 */

class AW_Field_Taxonomy_Term extends AW_Field {

	protected $default_title = 'Term';

	protected $default_name = 'term';

	protected $type = 'term';


	/**
	 * @param $value
	 *
	 * @return void
	 */
	function render( $value ) {
		?>

		<input type="hidden" class="automatewoo-json-search <?php echo $this->get_classes() ?>"
			 name="<?php echo $this->get_full_name(); ?>"
			 data-placeholder="<?php _e( 'Search for a term&hellip;', 'automatewoo' ); ?>"
			 data-action="aw_json_search_taxonomy_terms"
			 data-pass-sibling="aw_workflow_data[trigger_options][taxonomy]"
			 data-selected="<?php

			 if ( strstr( $value, '|' ) )
			 {
				 list( $term_id, $taxonomy ) = explode( '|', $value );

				 if ( $term = get_term_by( 'id', $term_id, $taxonomy ) )
					 echo wp_kses_post( $term->name );
			 }

			 ?>"
			 value="<?php echo ( $value ? $value : '' ); ?>" />

	<?php
	}

}
