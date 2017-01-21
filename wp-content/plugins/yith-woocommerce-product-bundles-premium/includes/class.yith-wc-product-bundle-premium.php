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

if ( !class_exists( 'WC_Product_Yith_Bundle' ) ) {
    /**
     * Product Bundle Object
     *
     * @since  1.0.0
     * @author Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class WC_Product_Yith_Bundle extends WC_Product {

        public  $bundle_data;
        private $bundled_items;

        public $per_items_pricing;
        public $non_bundled_shipping;

        public $price_per_item_tot_max;
        public $price_per_item_tot;

        private $_advanced_options;

        /**
         * __construct
         *
         * @access public
         *
         * @param mixed $product
         */
        public function __construct( $product ) {
            $this->product_type = 'yith_bundle';
            parent::__construct( $product );

            $id = $this->get_wpml_parent_id();

            $this->bundle_data = get_post_meta( $id, '_yith_wcpb_bundle_data', true );

            $this->per_items_pricing    = ( get_post_meta( $id, '_yith_wcpb_per_item_pricing', true ) == 'yes' ) ? true : false;
            $this->non_bundled_shipping = ( get_post_meta( $id, '_yith_wcpb_non_bundled_shipping', true ) == 'yes' ) ? true : false;

            $default_advanced_options = array(
                'min' => 0,
                'max' => 0,
            );
            $this->_advanced_options  = get_post_meta( $id, '_yith_wcpb_bundle_advanced_options', true );
            $this->_advanced_options  = wp_parse_args( $this->_advanced_options, $default_advanced_options );

            if ( !empty( $this->bundle_data ) ) {
                $this->load_items();
            }

            if ( $this->per_items_pricing ) {
                $this->price = 0;
            }

            $this->price_per_item_tot_max = $this->get_per_item_price_tot_max();
            $this->price_per_item_tot     = $this->get_per_item_price_tot();

            //$ajax_add_to_cart_enabled = !$this->has_optional() && !$this->has_variables() && !$this->has_quantity_to_choose() && $this->is_purchasable() && $this->is_in_stock() && $this->all_items_in_stock();
            $ajax_add_to_cart_enabled = !$this->has_optional() && !$this->has_variables() && !$this->has_quantity_to_choose();
            if ( $ajax_add_to_cart_enabled ) {
                $this->supports[] = 'ajax_add_to_cart';
            }

        }

        public function get_advanced_options( $option = '' ) {
            if ( !$option ) {
                return $this->_advanced_options;
            }

            if ( isset( $this->_advanced_options[ $option ] ) ) {
                return $this->_advanced_options[ $option ];
            }

            return false;
        }

        public function get_wpml_parent_id() {
            global $sitepress;

            $id = $this->id;

            $bundle_data = get_post_meta( $id, '_yith_wcpb_bundle_data', true );
            if ( !!$bundle_data ) {
                return $id;
            }

            if ( isset( $sitepress ) ) {
                $finded = false;

                $details = $sitepress->get_element_language_details( $id );
                if ( $details->trid ) {
                    $current_id  = absint( $details->trid );
                    $bundle_data = get_post_meta( $current_id, '_yith_wcpb_bundle_data', true );
                    if ( !!$bundle_data ) {
                        $id     = $current_id;
                        $finded = true;
                    }
                }

                if ( !$finded ) {
                    $default_language = $sitepress->get_default_language();

                    $source_language = !empty( $details->source_language_code ) ? $details->source_language_code : $default_language;
                    $current_id      = $this->id;
                    if ( function_exists( 'icl_object_id' ) ) {
                        $current_id = icl_object_id( $this->id, 'product', true, $source_language );
                    } else if ( function_exists( 'wpml_object_id_filter' ) ) {
                        $current_id = wpml_object_id_filter( $this->id, 'product', true, $source_language );
                    }

                    $bundle_data = get_post_meta( $current_id, '_yith_wcpb_bundle_data', true );
                    if ( !!$bundle_data ) {
                        $id     = $current_id;
                        $finded = true;
                    }
                }
            }

            return $id;
        }

        /**
         * Load bundled items
         *
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        private function load_items() {
            $virtual = true;
            foreach ( $this->bundle_data as $b_item_id => $b_item_data ) {
                $b_item = new YITH_WC_Bundled_Item( $this, $b_item_id );
                if ( $b_item->exists() ) {
                    $this->bundled_items[ $b_item_id ] = $b_item;
                    //v( $b_item->product, $b_item->product->is_virtual(), '-------------' );
                    if ( !$b_item->product->is_virtual() ) {
                        $virtual = false;
                    }
                }
            }
            $this->virtual = $virtual;
        }


        /**
         * return bundled items array
         *
         * @return array
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function get_bundled_items() {
            return !empty( $this->bundled_items ) ? $this->bundled_items : array();
        }

        /**
         * Returns false if the product cannot be bought.
         *
         * @return bool
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function is_purchasable() {
            $purchasable = true;

            // Products must exist of course
            if ( !$this->exists() ) {
                $purchasable = false;
                // Other products types need a price to be set
            } elseif ( !$this->per_items_pricing && $this->get_price() === '' ) {
                $purchasable = false;

                // Check the product is published
            } elseif ( $this->post->post_status !== 'publish' && !current_user_can( 'edit_post', $this->id ) ) {
                $purchasable = false;

            }

            // Check the bundle items are purchasable

            $bundled_items = !empty( $this->bundled_items ) ? $this->bundled_items : false;
            if ( $bundled_items ) {
                foreach ( $bundled_items as $bundled_item ) {
                    if ( !$bundled_item->get_product()->is_purchasable() ) {
                        $purchasable = false;
                    }
                }
            }

            return apply_filters( 'woocommerce_is_purchasable', $purchasable, $this );
        }

        /**
         * Returns true if all items is in stock
         *
         * @return bool
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function all_items_in_stock() {
            $response = true;

            $bundled_items = !empty( $this->bundled_items ) ? $this->bundled_items : false;
            if ( $bundled_items ) {
                foreach ( $bundled_items as $bundled_item ) {
                    if ( !$bundled_item->get_product()->is_in_stock() ) {
                        $response = false;
                    }
                }
            }

            return $response;
        }

        /**
         * Return true if some item has quantity to choose
         *
         * @return  int
         */
        public function has_quantity_to_choose() {
            $bundled_items = !empty( $this->bundled_items ) ? $this->bundled_items : false;
            if ( $bundled_items ) {
                foreach ( $bundled_items as $bundled_item ) {
                    if ( $bundled_item->has_quantity_to_choose() ) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * Return true if some item is optional
         *
         * @return  int
         */
        public function has_optional() {
            $bundled_items = !empty( $this->bundled_items ) ? $this->bundled_items : false;
            if ( $bundled_items ) {
                foreach ( $bundled_items as $bundled_item ) {
                    if ( $bundled_item->is_optional() ) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * Returns true if one item at least is variable product.
         *
         * @return bool
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function has_variables() {
            $bundled_items = !empty( $this->bundled_items ) ? $this->bundled_items : false;
            if ( $bundled_items ) {
                foreach ( $bundled_items as $bundled_item ) {
                    if ( $bundled_item->has_variables() ) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * Get the add to cart url used in loops.
         *
         * @access public
         * @return string
         */
        public function add_to_cart_url() {
            $url = !$this->has_optional() && !$this->has_variables() && !$this->has_quantity_to_choose() && $this->is_purchasable() && $this->is_in_stock() && $this->all_items_in_stock() ? remove_query_arg( 'added-to-cart', add_query_arg( 'add-to-cart', $this->id ) ) : get_permalink( $this->id );

            return apply_filters( 'woocommerce_product_add_to_cart_url', $url, $this );
        }

        /**
         * Get the add to cart button text
         *
         * @access public
         * @return string
         */
        public function add_to_cart_text() {
            $text = !$this->has_optional() && !$this->has_variables() && !$this->has_quantity_to_choose() && $this->is_purchasable() && $this->is_in_stock() && $this->all_items_in_stock() ? __( 'Add to cart', 'woocommerce' ) : __( 'Read More', 'woocommerce' );

            return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
        }

        /**
         * Get the title of the post.
         *
         * @access public
         * @return string
         */
        public function get_title() {

            $title = $this->post->post_title;

            if ( $this->get_parent() > 0 ) {
                $title = get_the_title( $this->get_parent() ) . ' &rarr; ' . $title;
            }

            return apply_filters( 'woocommerce_product_title', $title, $this );
        }

        /**
         * Sync grouped products with the children lowest price (so they can be sorted by price accurately).
         *
         * @access public
         * @return void
         */
        public function grouped_product_sync() {
            if ( !$this->get_parent() )
                return;

            $children_by_price = get_posts( array(
                                                'post_parent'    => $this->get_parent(),
                                                'orderby'        => 'meta_value_num',
                                                'order'          => 'asc',
                                                'meta_key'       => '_price',
                                                'posts_per_page' => 1,
                                                'post_type'      => 'product',
                                                'fields'         => 'ids'
                                            ) );
            if ( $children_by_price ) {
                foreach ( $children_by_price as $child ) {
                    $child_price = get_post_meta( $child, '_price', true );
                    update_post_meta( $this->get_parent(), '_price', $child_price );
                }
            }

            delete_transient( 'wc_products_onsale' );

            do_action( 'woocommerce_grouped_product_sync', $this->id, $children_by_price );
        }


        public function get_bundled_item( $item_id ) {
            if ( !empty( $this->bundle_data ) && isset( $this->bundle_data[ $item_id ] ) ) {
                if ( isset( $this->bundled_items[ $item_id ] ) ) {
                    return $this->bundled_items[ $item_id ];
                } else {
                    return new YITH_WC_Bundled_Item( $item_id, $this );
                }
            }

            return false;
        }


        public function get_price() {
            if ( $this->per_items_pricing ) {
                return apply_filters( 'woocommerce_yith_bundle_get_price', $this->price, $this );
            } else {
                return parent::get_price();
            }
        }


        /**
         * get the MAX price of product bundle
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         *
         */
        public function get_per_item_price_tot_max( $apply_discount = false, $include_optionals = false, $with_max_quantity = false ) {
            if ( isset( $this->price_per_item_tot_max ) && !$apply_discount ) {
                return $this->price_per_item_tot_max;
            }
            $args = compact( 'apply_discount', 'include_optionals', 'with_max_quantity' );

            do_action('yith_wcpb_before_get_per_item_price_tot_max', $this, $args);

            $price = 0;
            if ( $this->per_items_pricing ) {
                $bundled_items_price = 0;

                $bundled_items = $this->bundled_items;

                foreach ( $bundled_items as $bundled_item ) {
                    if ( !$include_optionals && $bundled_item->is_optional() ) {
                        continue;
                    }
                    /**
                     * @var WC_Product $product
                     */
                    $product            = $bundled_item->product;
                    $bundled_item_price = 0;
                    if ( !$bundled_item->has_variables() ) {
                        // SIMPLE
                        $regular_price = $product->get_display_price( $product->get_regular_price() );
                        if ( !$with_max_quantity ) {
                            $bundled_item_price = $regular_price * max( 1, $bundled_item->min_quantity );
                        } else {
                            $bundled_item_price = $regular_price * max( 1, $bundled_item->max_quantity );
                        }
                    } else {
                        // VARIABLE
                        $max_price = $bundled_item->max_price;
                        if ( !$with_max_quantity ) {
                            $bundled_item_price = $max_price * max( 1, $bundled_item->min_quantity );
                        } else {
                            $bundled_item_price = $max_price * max( 1, $bundled_item->max_quantity );

                        }
                    }

                    if ( $apply_discount ) {
                        $discount = $bundled_item_price * $bundled_item->discount / 100;
                        $bundled_item_price -= $discount;
                        $bundled_items_price += $bundled_item_price;
                    } else {
                        $bundled_items_price += $bundled_item_price;
                    }
                }
                $price = $bundled_items_price;
            }

            do_action('yith_wcpb_after_get_per_item_price_tot_max', $this, $args);

            return apply_filters( 'yith_wcpb_get_per_item_price_tot_max', $price, $this, $args );
        }


        /**
         * get the MIN price of product bundle
         *
         * @access public
         * @since  1.0.0
         * @author Leanza Francesco <leanzafrancesco@gmail.com>
         */
        public function get_per_item_price_tot() {
            if ( isset( $this->price_per_item_tot ) ) {
                return $this->price_per_item_tot;
            }
            do_action('yith_wcpb_before_get_per_item_price_tot', $this);
            
            $price = 0;
            if ( $this->per_items_pricing ) {
                $bundled_items_price = 0;

                $bundled_items = $this->bundled_items;

                foreach ( $bundled_items as $bundled_item ) {
                    if ( $bundled_item->is_optional() ) {
                        continue;
                    }
                    /**
                     * @var WC_Product $product
                     */
                    $product            = $bundled_item->product;
                    $bundled_item_price = 0;
                    if ( !$bundled_item->has_variables() ) {
                        // SIMPLE
                        $regular_price      = $product->get_display_price( $product->get_regular_price() );
                        $bundled_item_price = $regular_price * max( 1, $bundled_item->min_quantity );
                    } else {
                        // VARIABLE
                        $min_price          = $bundled_item->min_price;
                        $bundled_item_price = $min_price * max( 1, $bundled_item->min_quantity );
                    }

                    $discount = $bundled_item_price * $bundled_item->discount / 100;
                    $bundled_item_price -= $discount;
                    $bundled_items_price += $bundled_item_price;
                }
                $price = $bundled_items_price;
            }

            do_action('yith_wcpb_after_get_per_item_price_tot', $this);

            return apply_filters( 'yith_wcpb_get_per_item_price_tot', $price, $this);
        }

        public function get_price_html_from_to( $from, $to ) {
            if ( !$this->per_items_pricing || ( !$this->has_variables() && !$this->has_optional() ) ) {
                return parent::get_price_html_from_to( $from, $to );
            } else {
                return wc_price( $to ) . '-' . wc_price( $from );
            }
        }


        public function get_per_item_price_tot_with_params( $array_quantity, $array_opt, $array_var, $html = true ) {
            if ( !is_array( $array_quantity ) ) {
                return $this->price_per_item_tot;
            }

            do_action( 'yith_wcpb_before_get_per_item_price_tot_with_params', $array_quantity, $array_opt, $array_var, $html );

            $price = $this->price;
            if ( $this->per_items_pricing ) {
                $bundled_items_price = 0;

                $bundled_items = $this->bundled_items;
                $loop          = 0;
                foreach ( $bundled_items as $bundled_item ) {
                    if ( $bundled_item->is_optional() && ( ( isset( $array_opt[ $loop ] ) && $array_opt[ $loop ] == '0' ) || !isset( $array_opt[ $loop ] ) ) ) {
                        $loop++;
                        continue;
                    }

                    /**
                     * @var WC_Product $product
                     */
                    $product = $bundled_item->product;
                    if ( 'variable' === $product->product_type ) {
                        if ( isset( $array_var[ $loop ] ) && $variation = $product->get_child( $array_var[ $loop ] ) ) {
                            $product       = $variation;
                            $regular_price = $product->get_display_price( $product->get_regular_price() );
                        } else {
                            /**
                             * @var WC_Product_Variable $product
                             */
                            $prices        = $product->get_variation_prices();
                            $regular_price = $product->get_display_price( current( $prices[ 'regular_price' ] ) );
                            unset( $prices );
                        }
                    } else {
                        $regular_price = $product->get_display_price( $product->get_regular_price() );
                    }

                    $qty = isset( $array_quantity[ $loop ] ) ? $array_quantity[ $loop ] : 1;

                    $bundled_item_price = $regular_price * $qty;

                    $discount = $bundled_item_price * $bundled_item->discount / 100;
                    $bundled_item_price -= $discount;

                    $bundled_items_price += $bundled_item_price;
                    $loop++;
                }

                //$bundled_items_price = $this->get_display_price( $bundled_items_price );
                $price = $bundled_items_price;
            }

            do_action( 'yith_wcpb_after_get_per_item_price_tot_with_params', $array_quantity, $array_opt, $array_var, $html );

            return $html ? wc_price( $price ) : $price;
        }


        /**
         * Gets product variation data which is passed to JS.
         *
         * @return array variation data array
         */
        public function get_available_bundle_variations() {

            if ( empty( $this->bundled_items ) ) {
                return array();
            }

            $bundle_variations = array();
            $price_zero        = !$this->per_items_pricing;
            foreach ( $this->bundled_items as $bundled_item )
                $bundle_variations[ $bundled_item->item_id ] = $bundled_item->get_product_variations( $price_zero );

            return $bundle_variations;
        }

        /**
         * Gets the attributes of all variable bundled items.
         *
         * @return array attributes array
         */
        public function get_bundle_variation_attributes() {

            if ( empty( $this->bundled_items ) ) {
                return array();
            }

            $bundle_attributes = array();

            foreach ( $this->bundled_items as $bundled_item ) {
                $bundle_attributes[ $bundled_item->item_id ] = $bundled_item->get_product_variation_attributes();
            }

            return $bundle_attributes;
        }

        public function get_selected_bundle_variation_attributes() {

            if ( empty( $this->bundled_items ) ) {
                return array();
            }

            $seleted_bundle_attributes = array();

            foreach ( $this->bundled_items as $bundled_item ) {
                $seleted_bundle_attributes[ $bundled_item->item_id ] = $bundled_item->get_selected_product_variation_attributes();
            }

            return $seleted_bundle_attributes;
        }


    }
}
?>