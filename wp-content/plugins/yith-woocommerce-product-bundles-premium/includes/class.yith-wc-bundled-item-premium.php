<?php
/**
 * Product Bundle Class
 *
 * @author  Yithemes
 * @package YITH WooCommerce Product Bundles
 * @version 1.0.0
 */


if ( !defined( 'YITH_WCPB' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WC_Bundled_Item' ) ) {
    /**
     * Product Bundle Item Object
     *
     * @since  1.0.0
     * @author Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WC_Bundled_Item {

        public $item_id;

        /**
         * product id of a item
         *
         * @var int
         * @since 1.0.0
         */
        public $product_id;

        /**
         * product object of a item
         *
         * @var WC_Product
         * @since 1.0.0
         */
        public $product;

        private $quantity;

        public $hide_thumbnail;
        public $hidden;
        public $min_quantity;
        public $max_quantity;
        public $title;
        public $description;
        public $optional;
        public $discount;

        public $product_attributes;
        public $product_variations;
        public $selected_product_attributes;
        public $selection_overrides;

        public $min_price;
        public $max_price;

        public $filtered_variations;


        /**
         * __construct
         *
         * @access public
         *
         * @param mixed $product
         */
        public function __construct( $parent, $item_id ) {
            $this->item_id    = $item_id;
            $this->product_id = $parent->bundle_data[ $item_id ][ 'product_id' ];

            $this->quantity            = isset( $parent->bundle_data[ $item_id ][ 'bp_quantity' ] ) ? $parent->bundle_data[ $item_id ][ 'bp_quantity' ] : 1;
            $this->quantity            = isset( $parent->bundle_data[ $item_id ][ 'bp_min_qty' ] ) ? $parent->bundle_data[ $item_id ][ 'bp_min_qty' ] : $this->quantity;
            $this->hide_thumbnail      = ( isset( $parent->bundle_data[ $item_id ][ 'bp_hide_bundled_thumbs' ] ) && $parent->bundle_data[ $item_id ][ 'bp_hide_bundled_thumbs' ] == 'on' ) ? 1 : 0;
            $this->hidden              = ( isset( $parent->bundle_data[ $item_id ][ 'bp_hide_item' ] ) && $parent->bundle_data[ $item_id ][ 'bp_hide_item' ] == 'on' ) ? 1 : 0;
            $this->min_quantity        = isset( $parent->bundle_data[ $item_id ][ 'bp_min_qty' ] ) ? $parent->bundle_data[ $item_id ][ 'bp_min_qty' ] : $this->quantity;
            $this->max_quantity        = isset( $parent->bundle_data[ $item_id ][ 'bp_max_qty' ] ) ? $parent->bundle_data[ $item_id ][ 'bp_max_qty' ] : $this->quantity;
            $this->optional            = ( isset( $parent->bundle_data[ $item_id ][ 'bp_optional' ] ) && $parent->bundle_data[ $item_id ][ 'bp_optional' ] == 'on' ) ? 1 : 0;
            $this->discount            = isset( $parent->bundle_data[ $item_id ][ 'bp_discount' ] ) ? $parent->bundle_data[ $item_id ][ 'bp_discount' ] : 0;
            $this->filtered_variations = isset( $parent->bundle_data[ $item_id ][ 'bp_filtered_variations' ] ) ? $parent->bundle_data[ $item_id ][ 'bp_filtered_variations' ] : array();
            $this->selection_overrides = isset( $parent->bundle_data[ $item_id ][ 'bp_selection_overrides' ] ) ? $parent->bundle_data[ $item_id ][ 'bp_selection_overrides' ] : array();

            $this_terms        = get_the_terms( $this->product_id, 'product_type' );
            $this_product_type = !empty( $this_terms ) ? sanitize_title( current( $this_terms )->name ) : 'simple';
            if ( 'yith_bundle' === $this_product_type )
                return;

            $bundled_product = wc_get_product( $this->product_id );


            // if exist the product with $this->product_id
            if ( $bundled_product ) {
                $this->product = $bundled_product;

                $this->title       = isset( $parent->bundle_data[ $item_id ][ 'bp_title' ] ) ? $parent->bundle_data[ $item_id ][ 'bp_title' ] : $bundled_product->post->post_title;
                $this->description = isset( $parent->bundle_data[ $item_id ][ 'bp_description' ] ) ? $parent->bundle_data[ $item_id ][ 'bp_description' ] : $bundled_product->post->post_excerpt;

                /* ==== WPML start ==== */
                $parent_wpml_id = $this->get_wpml_product_id_current_language();
                if ( $this->product_id != $parent_wpml_id ) {
                    $wpml_parent_product = wc_get_product( $parent_wpml_id );
                    $this->title         = $wpml_parent_product->post->post_title;
                    $this->description   = $wpml_parent_product->post->post_excerpt;
                }
                /* ==== WPML end ==== */

                if ( $bundled_product->product_type != 'variable' ) {
                    // SIMPLE
                    $this->min_price = $bundled_product->get_sale_price();
                    $this->max_price = $bundled_product->get_regular_price();
                } else {
                    // VARIABLE
                    $this->min_price = $bundled_product->get_variation_regular_price( 'min', true );
                    $this->max_price = $bundled_product->get_variation_regular_price( 'max', true );
                }
            }
        }

        public function get_wpml_product_id_current_language() {
            global $sitepress;

            $id = $this->product_id;

            if ( isset( $sitepress ) ) {

                if ( function_exists( 'icl_object_id' ) ) {
                    $id = icl_object_id( $id, 'product', true );
                } else if ( function_exists( 'wpml_object_id_filter' ) ) {
                    $id = wpml_object_id_filter( $id, 'product', true );
                }
            }

            return $id;
        }

        /**
         * Return true if this->product is setted
         *
         * @return  boolean
         */
        public function exists() {

            return !empty( $this->product );
        }

        /**
         * Return this->product [or false if it not exist]
         *
         * @return  WC_Product
         */
        public function get_product() {
            return !empty( $this->product ) ? $this->product : false;
        }

        /**
         * Return this->quantity [or 0 if it's not setted]
         *
         * @return  int
         */
        public function get_quantity() {
            return !empty( $this->quantity ) ? $this->quantity : 0;
        }

        /**
         * Return true if min_quantity < max_quantity
         *
         * @return  bool
         */
        public function has_quantity_to_choose() {
            return ( $this->min_quantity < $this->max_quantity ) ? true : false;
        }

        /**
         * Return true if min_quantity < max_quantity
         *
         * @return  bool
         */
        public function is_optional() {
            return $this->optional;
        }

        /**
         * Return true if is variable
         *
         * @return  bool
         */
        function has_variables() {
            return $this->product->product_type === 'variable';
        }


        /**
         * Returns this product's available variations array.
         *
         * @return array
         */
        public function get_product_variations( $price_zero = false ) {

            if ( !empty( $this->product_variations ) ) {
                return $this->product_variations;
            }

            if ( $this->product->product_type === 'variable' ) {

                do_action( 'yith_wcpb_get_product_variations_before', $this );

                do_action( 'woocommerce_before_init_bundled_item', $this );

                add_filter( 'woocommerce_get_children', array( $this, 'bundled_item_children' ), 10, 2 );

                add_filter( 'woocommerce_show_variation_price', '__return_true', 98 );

                if ( $price_zero ) {
                    add_filter( 'woocommerce_get_variation_price_html', array( $this, 'price_zero' ), 99, 2 );
                } else {
                    add_filter( 'woocommerce_get_variation_price_html', array( $this, 'get_price_html' ), 99, 2 );
                }

                $parent_wpml_id = $this->get_wpml_product_id_current_language();
                if ( $this->product_id != $parent_wpml_id ) {
                    $wpml_parent_product     = wc_get_product( $parent_wpml_id );
                    $bundled_item_variations = $wpml_parent_product->get_available_variations();
                } else {
                    $bundled_item_variations = $this->product->get_available_variations();
                }
                if ( $price_zero ) {
                    remove_filter( 'woocommerce_get_variation_price_html', array( $this, 'price_zero' ), 99, 2 );
                } else {
                    remove_filter( 'woocommerce_get_variation_price_html', array( $this, 'get_price_html' ), 99, 2 );
                }

                remove_filter( 'woocommerce_show_variation_price', '__return_true', 98 );

                remove_filter( 'woocommerce_get_children', array( $this, 'bundled_item_children' ), 10, 2 );

                // add only active variations
                foreach ( $bundled_item_variations as $variation_data ) {
                    if ( !empty( $variation_data ) ) {
                        $variation_data[ 'display_regular_price_html' ] = $this->discount > 0 ? wc_price( $variation_data[ 'display_regular_price' ] ) : '';
                        $this->product_variations[]                     = $variation_data;

                    }
                }

                do_action( 'yith_wcpb_get_product_variations_after', $this );

                return $this->product_variations;
            }

            return false;
        }

        public function price_zero( $price_html, $product ) {
            return '';
        }

        public function bundled_item_children( $children, $bundled_product ) {

            if ( empty( $this->filtered_variations ) || !is_array( $this->filtered_variations ) ) {
                return $children;
            } else {
                $filtered_children = array();

                foreach ( $children as $variation_id ) {
                    // Remove if filtered
                    if ( in_array( $variation_id, $this->filtered_variations ) ) {
                        $filtered_children[] = $variation_id;
                    }
                }

                return $filtered_children;
            }
        }

        /**
         * @param string     $price_html
         * @param WC_Product $product
         *
         * @return string
         */
        public function get_price_html( $price_html, $product ) {
            $regular_price = $product->get_regular_price();
            $price         = $regular_price - $regular_price * $this->discount / 100;
            $price         = $product->get_display_price( $price );

            return wc_price( $price );
        }

        /**
         * Returns the variation attributes array if this product is variable.
         *
         * @return array
         */
        public function get_product_variation_attributes() {

            if ( !empty( $this->product_attributes ) ) {
                return $this->product_attributes;
            }

            if ( $this->product->product_type === 'variable' ) {
                $parent_wpml_id = $this->get_wpml_product_id_current_language();
                if ( $this->product_id != $parent_wpml_id ) {
                    $wpml_parent_product      = wc_get_product( $parent_wpml_id );
                    $this->product_attributes = $wpml_parent_product->get_variation_attributes();
                } else {
                    $this->product_attributes = $this->product->get_variation_attributes();
                }

                return $this->product_attributes;
            }

            return false;

        }

        function get_selected_product_variation_attributes() {
            if ( !empty( $this->selected_product_attributes ) ) {
                return $this->selected_product_attributes;
            }

            if ( $this->product->product_type === 'variable' ) {
                $selected_product_attributes = array();
                if ( !empty( $this->selection_overrides ) ) {
                    $selected_product_attributes = $this->selection_overrides;
                } else {
                    $selected_product_attributes = ( array ) maybe_unserialize( get_post_meta( $this->product_id, '_default_attributes', true ) );
                }

                $this->selected_product_attributes = apply_filters( 'woocommerce_product_default_attributes', $selected_product_attributes );

                return $this->selected_product_attributes;
            }

            return false;
        }

    }
}
?>