<?php
/**
 * @class 		AW_Data_Type_Loader
 * @package		AutomateWoo
 * @since		2.4.6
 */

class AW_Data_Type_Loader {

	/** @var array */
	private $includes;

	/** @var array  */
	private $loaded_data_types = [];


	/**
	 * @return array
	 */
	function get_includes() {
		if ( ! isset( $this->includes ) ) {
			$path = AW()->path( '/includes/data-types/' );

			$this->includes = apply_filters( 'automatewoo/data_types/includes', array(
				'user' => $path . 'user.php',
				'order' => $path . 'order.php',
				'product' => $path . 'product.php',
				'category' => $path . 'category.php',
				'tag' => $path . 'tag.php',
				'wishlist' => $path . 'wishlist.php',
				'guest' => $path . 'guest.php',
				'order_note' => $path . 'order-note.php',
				'order_item' => $path . 'order-item.php',
				'cart' => $path . 'cart.php',
				'workflow' => $path . 'workflow.php',
				'subscription' => $path . 'subscription.php',
				'post' => $path . 'post.php',
				'comment' => $path . 'comment.php'
			));
		}

		return $this->includes;
	}


	/**
	 * @param $data_type_id
	 * @return AW_Data_Type|false
	 */
	function get_data_type( $data_type_id ) {

		if ( ! $this->is_loaded( $data_type_id ) ) {
			$this->load( $data_type_id );
		}

		return $this->loaded_data_types[$data_type_id];
	}


	/**
	 * @param $data_type_id
	 * @return bool
	 */
	function is_loaded( $data_type_id ) {
		return isset( $this->loaded_data_types[ $data_type_id ] );
	}


	/**
	 * @param $data_type_id
	 * @return AW_Data_Type
	 */
	function load( $data_type_id ) {

		$data_type = false;
		$includes = $this->get_includes();

		if ( ! empty( $includes[ $data_type_id ] ) ) {
			if ( file_exists( $include = $includes[ $data_type_id ] ) ) {
				$data_type = include_once $includes[ $data_type_id ];
			}
		}

		$this->loaded_data_types[ $data_type_id ] = $data_type;
		return $data_type;
	}

}