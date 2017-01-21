<?php
/**
 * @class       AW_Variables
 * @package     AutomateWoo
 * @since       2.4.6
 */

class AW_Variables {

	/** @var array */
	private $loaded_variables = [];

	/** @var array */
	private $variables_list;

	/** @var array */
	private $included_variables = [
		'order' => [
			'id',
			'number',
			'status',
			'date',
			'total',
			'itemscount',
			'items',
			'cross_sells',
			'related_products',
			'billing_phone',
			'billing_address',
			'shipping_address',
			'view_url',
			'payment_url',
			'meta'
		],
		'order_item' => [
			'attribute',
			'meta'
		],
		'user' => [
			'id',
			'email',
			'firstname',
			'lastname',
			'username',
			'billing_phone',
			'meta',
			'generate_coupon',
			'order_count',
			'total_spent'
		],
		'guest' => [
			'email',
			'generate_coupon'
		],
		'comment' => [
			'id',
			'author_name',
			'author_ip'
		],
		'product' => [
			'id',
			'title',
			'current_price',
			'regular_price',
			'featured_image',
			'permalink',
			'add_to_cart_url',
			'sku',
			'short_description',
			'meta'
		],
		'category' => [
			'id',
			'title',
			'permalink'
		],
		'wishlist' => [
			'items',
			'view_link',
			'itemscount'
		],
		'cart' => [
			'link',
			'items',
			'total'
		],
		'subscription' => [
			'id',
			'status',
			'payment_method',
			'view_order_url',
			'start_date',
			'next_payment_date',
			'trial_end_date',
			'end_date',
			'last_payment_date',
		],
		'shop' => [
			'title',
			'tagline',
			'url',
			'admin_email',
			'current_datetime',
			'products'
		]
	];



	/**
	 * @return array
	 */
	function get_list() {
		// cache the list after first generation
		if ( isset( $this->variables_list ) ) {
			return $this->variables_list;
		}

		$variables = [];
		$included_variables = $this->included_variables;

		if ( class_exists( 'WC_Shipment_Tracking' ) ) {
			$included_variables['order'][] = 'tracking_number';
			$included_variables['order'][] = 'tracking_url';
			$included_variables['order'][] = 'date_shipped';
			$included_variables['order'][] = 'shipping_provider';
		}

		// generate paths to included variables
		foreach ( $included_variables as $data_type => $fields ) {
			foreach ( $fields as $field ) {
				$filename = str_replace( '_', '-', $data_type ) . '-' . str_replace( '_', '-', $field ) . '.php';
				$variables[$data_type][$field] = AW()->path("/includes/variables/$filename");
			}
		}

		$this->variables_list = apply_filters( 'automatewoo/variables', $variables );

		return $this->variables_list;
	}


	/**
	 * @param $variable
	 * @return string
	 */
	function get_path_to_variable( $variable ) {

		list( $data_type, $data_field ) = explode( '.', $variable );

		$list = $this->get_list();

		if ( isset( $list[$data_type][$data_field] ) ) {
			return $list[$data_type][$data_field];
		}
	}



	/**
	 * @param $variable string
	 * @return AW_Variable|false
	 */
	function get_variable_object( $variable ) {

		if ( isset( $this->loaded_variables[$variable] ) ) {
			return $this->loaded_variables[$variable];
		}

		$path = $this->get_path_to_variable( $variable );

		if ( ! file_exists( $path ) )
			return false;

		$class = include $path;

		$this->loaded_variables[$variable] = $class;

		return $class;
	}


}
