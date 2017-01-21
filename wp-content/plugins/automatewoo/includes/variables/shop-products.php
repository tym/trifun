<?php
/**
 * @class 		AW_Variable_Shop_Products
 * @package		AutomateWoo/Variables
 */

class AW_Variable_Shop_Products extends AW_Variable_Abstract_Product_Display {

	protected $name = 'shop.products';

	public $support_limit_field = true;

	/**
	 * Init
	 */
	function init() {

		$this->add_parameter_select_field( 'type', __( "Determines which products will be displayed.", 'automatewoo'), array(
			'featured' => 'Featured',
			'sale' => 'Sale',
			'recent' => 'Recent',
			'top_selling' => 'Top Selling',
			'category' => 'By Product Category',
			'tag' => 'By Product Tag',
			'ids' => 'By Product IDs',
			'custom' => 'By Custom Filter',
		), true );

		$this->add_parameter_text_field( 'ids', __( "Display products by ID, use '+' as a delimiter. E.g. 34+12+5", 'automatewoo'), true, '', array(
			'show' => 'type=ids'
		));

		$this->add_parameter_text_field( 'category', __( "Display products by product category slug. E.g. clothing or clothing+shoes", 'automatewoo'), true, '', array(
			'show' => 'type=category'
		));

		$this->add_parameter_text_field( 'tag', __( "Display products by product tag slug. E.g. winter or winter+summer", 'automatewoo'), true, '', array(
			'show' => 'type=tag'
		));

		$this->add_parameter_text_field( 'filter', __( "Display products by using a WP filter.", 'automatewoo'), true, '', array(
			'show' => 'type=custom'
		));

		parent::init();

		$this->description = __( "Display your shop's products by various criteria.", 'automatewoo');
	}


	/**
	 * @param $parameters
	 * @param $workflow AW_Model_Workflow
	 * @return string
	 */
	function get_value( $parameters, $workflow ) {

		$type = isset( $parameters['type'] ) ? $parameters['type'] : false;
		$template = isset( $parameters['template'] ) ? $parameters['template'] : false;
		$limit = isset( $parameters['limit'] ) ? absint( $parameters['limit'] ) : 8;

		switch ( $type ) {

			case 'ids':

				if ( empty( $parameters['ids'] ) )
					return false;

				$ids = explode('+', $parameters['ids'] );
				$ids = array_map( 'absint', $ids );

				$products = $this->prepare_products( $ids, $limit );

				break;

			case 'category':

				$categories = $this->get_term_ids_from_slugs( $parameters['category'], 'product_cat' );

				if ( empty( $categories ) )
					return false;

				$products = $this->prepare_products( get_objects_in_term( $categories, 'product_cat' ), $limit );

				break;

			case 'tag':

				$tags = $this->get_term_ids_from_slugs( $parameters['tag'], 'product_tag' );

				if ( empty( $tags ) )
					return false;

				$products = $this->prepare_products( get_objects_in_term( $tags, 'product_tag' ), $limit );

				break;

			case 'featured':
				$products = $this->prepare_products( wc_get_featured_product_ids(), $limit );
				break;

			case 'sale':
				$products = $this->prepare_products( wc_get_product_ids_on_sale(), $limit );
				break;

			case 'recent':
				$products = $this->prepare_products( aw_get_recent_product_ids( $limit ), $limit );
				break;

			case 'top_selling':

				$query_args = [
					'post_type' => 'product',
					'post_status' => 'publish',
					'ignore_sticky_posts' => 1,
					'posts_per_page' => $limit,
					'meta_key' => 'total_sales',
					'orderby' => 'meta_value_num',
					'meta_query' => WC()->query->get_meta_query(),
					'fields'	=> 'ids'
				];

				$products = array_map( 'wc_get_product', get_posts( $query_args ) );

				break;

			case 'custom':

				if ( empty( $parameters['filter'] ) )
					return false;

				$product_ids = apply_filters( $parameters['filter'], [], $workflow, $parameters );
				$products = $this->prepare_products( $product_ids, $limit );

				break;

			default:
				return false;
				break;
		}


		$args = array_merge( $this->get_default_product_template_args( $workflow ), [
			'products' => $products
		]);

		return $this->get_product_display_html( $template, $args );
	}


	/**
	 * Slugs should be separated by '+'
	 *
	 * @param string $slugs
	 * @param string $taxonomy
	 * @return array
	 */
	private function get_term_ids_from_slugs( $slugs, $taxonomy ) {

		if ( empty( $slugs ) )
			return [];

		$ids = [];

		foreach ( explode( '+', $slugs ) as $slug ) {
			if ( $term = get_term_by( 'slug', trim( $slug ), $taxonomy ) ) {
				$ids[] = $term->term_id;
			}
		}

		return $ids;
	}

}

return new AW_Variable_Shop_Products();
