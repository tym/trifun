<?php
/**
 * @class       AW_Model_Abandoned_Cart
 * @package     AutomateWoo/Models
 * @since       2.0.0
 *
 * @property string $user_id
 * @property string $guest_id
 * @property string $last_modified
 * @property string $created
 * @property array $items
 * @property array $coupons
 * @property string $total
 * @property string $token
 */

class AW_Model_Abandoned_Cart extends AW_Model {

	/** @var string */
	public $model_id = 'abandoned-cart';

	/** @var null|AW_Model_Guest|false */
	private $guest;

	/** @var float */
	public $calculated_total = 0;

	/** @var float */
	public $calculated_tax_total = 0;

	/** @var float */
	public $calculated_subtotal = 0;


	/**
	 * @param bool|int $id
	 */
	function __construct( $id = false ) {
		$this->table_name = AW()->table_name_abandoned_cart;

		if ( $id ) $this->get_by( 'id', $id );
	}


	/**
	 * @return bool
	 */
	function has_coupons() {
		return sizeof( $this->get_coupons() ) > 0;
	}


	/**
	 * @return array
	 */
	function get_coupons() {
		if ( $this->coupons ) {
			return $this->coupons;
		}
		return [];
	}


	/**
	 * @return bool
	 */
	function has_items() {
		return sizeof( $this->get_items() ) > 0;
	}


	/**
	 * @return array
	 */
	function get_items() {
		if ( $this->items ) {
			return $this->items;
		}
		return [];
	}


	/**
	 * Updates the stored cart with the current time and cart items
	 */
	function sync() {

		$this->last_modified = current_time( 'mysql', true );
		$this->items = WC()->cart->get_cart_for_session();

		$coupon_data = [];

		foreach( WC()->cart->get_applied_coupons() as $coupon_code ) {
			$coupon_data[$coupon_code] = [
				'discount_incl_tax' => WC()->cart->get_coupon_discount_amount( $coupon_code, false ),
				'discount_excl_tax' => WC()->cart->get_coupon_discount_amount( $coupon_code ),
				'discount_tax' => WC()->cart->get_coupon_discount_tax_amount( $coupon_code )
			];
		}

		$this->coupons = $coupon_data;

		if ( WC()->cart->tax_display_cart === 'excl' ) {
			$this->total = WC()->cart->cart_contents_total;
		}
		else {
			$this->total = WC()->cart->cart_contents_total + WC()->cart->tax_total;
		}

		$this->save();
	}


	/**
	 * @return AW_Model_Guest|false
	 */
	function get_guest() {

		if ( $this->guest === null ) {
			$this->guest = AW()->get_guest( $this->guest_id );
		}

		return $this->guest;
	}


	/**
	 * @param bool $token (optional)
	 */
	function set_token( $token = false ) {

		if ( ! $token ) {
			$token = aw_generate_key( 32 );
		}

		$this->token = $token;
	}


	/**
	 *
	 */
	function calculate_totals() {

		$this->calculated_subtotal = 0;
		$this->calculated_tax_total = 0;
		$this->calculated_total = 0;

		$tax_display = get_option( 'woocommerce_tax_display_cart' );

		foreach( $this->get_items() as $item ) {
			$line_total = $tax_display === 'excl' ? $item[ 'line_subtotal' ] : $item['line_subtotal'] + $item['line_subtotal_tax'];
			$this->calculated_tax_total += $item['line_subtotal_tax'];
			$this->calculated_total += $line_total;
			$this->calculated_subtotal += $line_total;
		}

		foreach ( $this->get_coupons() as $coupon_code => $coupon ) {
			$coupon_discount = $tax_display === 'excl' ? $coupon[ 'discount_excl_tax' ] : $coupon[ 'discount_incl_tax' ];
			$this->calculated_total -= $coupon_discount;
			$this->calculated_tax_total -= $coupon['discount_tax'];
		}
	}


	/**
	 * Save
	 */
	function save() {

		if ( ! $this->exists ) {
			$this->created = current_time( 'mysql', true );
		}

		parent::save();
	}

}

