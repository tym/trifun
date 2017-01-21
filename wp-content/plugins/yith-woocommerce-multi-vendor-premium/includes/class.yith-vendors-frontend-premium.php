<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'YITH_WPV_VERSION' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Vendors_Frontend
 * @package    Yithemes
 * @since      Version 2.0.0
 * @author     Your Inspiration Themes
 *
 */

if ( ! class_exists( 'YITH_Vendors_Frontend_Premium' ) ) {
    

    /**
     * Class YITH_Vendors_Frontend
     *
     * @author Andrea Grillo <andrea.grillo@yithemes.com>
     */
    class YITH_Vendors_Frontend_Premium extends YITH_Vendors_Frontend {
        

        /**
         * Constructor
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function __construct() {

            /* Enqueue  Scripts */
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            /* Shop Page */
            add_action( 'woocommerce_product_query', array( $this, 'hide_vendors_product' ), 15, 1 );
            add_filter( 'woocommerce_show_page_title', array( $this, 'remove_store_page_title' ) );
            add_action( 'woocommerce_archive_description', array( $this, 'add_store_page_header' ) );
            add_filter( 'wp_head', array( $this, 'vendor_name_style' ) );
            add_filter( 'yith_woocommerce_product_vendor_tab', array( $this, 'vendor_tab_priority' ) );

            /* Vendor Registration */
            add_action( 'woocommerce_register_form', array( $this, 'register_form' ) );
            add_filter( 'woocommerce_process_registration_errors', array( $this, 'process_registration' ), 10 );
            add_action( 'woocommerce_created_customer', array( $this, 'create_vendor' ), 10, 1 );
            add_action( 'wp_loaded', array( $this, 'switch_customer_to_vendor' ) );

            /* WooCommerce My Account */
            add_action( 'woocommerce_before_my_account', array( $this, 'vendor_dashboard_endpoint' ) );

            /* Coupon Management */
            add_filter( 'woocommerce_coupon_is_valid_for_product', array( $this, 'check_vendor_coupon_product' ), 10, 4 );
            add_filter( 'woocommerce_coupon_is_valid', array( $this, 'vendor_coupon_is_valid' ), 10, 2 );

            /* Report Abuse */
            add_action( 'woocommerce_product_thumbnails', array( $this, 'add_report_abuse_link' ), 30 );
            add_action( 'wp_ajax_send_report_abuse', array( $this, 'send_report_abuse' ) );
            add_action( 'wp_ajax_nopriv_send_report_abuse', array( $this, 'send_report_abuse' ) );

            /* Single Product Page */
            add_action( 'woocommerce_product_meta_end', array( $this, 'woocommerce_product_meta' ) );

            /* Cart and Checkout */
            add_filter( 'woocommerce_cart_item_name', array( $this, 'add_sold_by_vendor' ), 10, 3 );
            add_filter( 'woocommerce_order_item_name', array( $this, 'add_sold_by_vendor' ), 10, 3 );

            /* Check for enabled vendor */
            add_filter( 'show_admin_bar', array( $this, 'show_admin_bar' ) );

            /* Load Shortcodes */
            class_exists( 'YITH_Multi_Vendor_Shortcodes' ) && add_action( 'init', 'YITH_Multi_Vendor_Shortcodes::load', 20 );

            /* Body Class */
            add_filter( 'body_class', array( $this, 'body_class' ) );

            parent::__construct();
        }

        /**
         * Add style for vendor label name
         *
         * @return void
         * @since  1.0.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function vendor_name_style() {
            global $post;
            $link_style = get_option( 'yith_wpv_vendor_name_style', 'theme' );

            //like this is_shop() || is_product() || is_product_category() || is_product_tag() || is_product_taxonomy()
            if ( ! empty( $post ) && 'product' == $post->post_type && 'custom' == $link_style ) :
                ?>
                <style type="text/css">
                    .by-vendor-name a.by-vendor-name-link {
                        color: <?php echo get_option( 'yith_vendors_color_name' ) ?>
                    }

                    .by-vendor-name a.by-vendor-name-link:hover {
                        color: <?php echo get_option( 'yith_vendors_color_name_hover' ) ?>
                    }
                </style>
            <?php
            endif;
        }

        /**
         * Add style for vendor label name
         *
         * @param $args The vendor tab param
         *
         * @return array The args array
         * @since  1.0.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function vendor_tab_priority( $args ) {
            $args['priority'] = absint( get_option( 'yith_vendors_tab_position' ) );
            return $args;
        }

        /**
         * Exclude the not enable vendors to Related products
         *
         * @param $args The related products query args
         *
         * @return mixed|array the query args
         * @since  1.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function related_products_args( $args ) {
            global $product;
            $vendor = yith_get_vendor( $product, 'product' );

            if ( ! $vendor->is_valid() ) {
                return parent::related_products_args( $args );
            }

            $related = get_option( 'yith_vendors_related_products' );

            if ( 'disable' == $related ) {
                return false;
            }
            elseif ( 'default' == $related ) {
                return parent::related_products_args( $args );
            }
            elseif ( 'vendor' == $related ) {
                $args['post__in'] = $vendor->get_products();
                return $args;
            }
        }

        /**
         * Add vendor name after product title
         *
         * @return   string The title
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use     the_title filter
         */
        public function woocommerce_template_vendor_name() {
            // Set the get option default value to 'yes' for YWPV free version support
            $show_in_store      = 'yes' == get_option( 'yith_wpv_vendor_name_in_store', 'yes' ) ? true : false;
            $show_in_loop       = 'yes' == get_option( 'yith_wpv_vendor_name_in_loop', 'yes' ) ? true : false;
            $show_in_single     = 'yes' == get_option( 'yith_wpv_vendor_name_in_single', 'yes' ) ? true : false;
            $show_in_categories = 'yes' == get_option( 'yith_wpv_vendor_name_in_categories', 'yes' ) ? true : false;

            if ( ! $show_in_store && is_product_taxonomy() && ! is_product_category() ) {
                return false;
            }

            else {
                if ( ! $show_in_loop && is_shop() ) {
                    return false;
                }

                else {
                    if ( ! $show_in_single && is_singular( 'product' ) ) {
                        return false;
                    }

                    else {
                        if ( ! $show_in_categories && is_product_category() ) {
                            return false;
                        }

                        else {
                            apply_filters( 'yith_wcmv_show_vendor_name_template', true ) && parent::woocommerce_template_vendor_name();
                        }
                    }
                }
            }
        }

        /**
         * Check if the product listing options is enabled and filter the product list
         *
         * @param $query The WP_Query object
         *
         * @since  1.0
         * @return void
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @use woocommerce_product_query action
         */
        public function hide_vendors_product( $query ) {

            if ( 'yes' == get_option( 'yith_wpv_hide_vendor_products' ) && ! is_product_taxonomy() ) {

                $tax_query = array(
                    array(
                        'taxonomy' => YITH_Vendors()->get_taxonomy_name(),
                        'field'    => 'id',
                        'terms'    => YITH_Vendors()->get_vendors( array( 'fields' => 'ids' ) ),
                        'operator' => 'NOT IN'
                    )
                );

                $query->set( 'tax_query', $tax_query );
            }
        }

        /**
         * Check if the user see a store vendor page
         *
         * @return bool
         * @since  1.0
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function is_vendor_page() {
            return is_tax( YITH_Vendors()->get_taxonomy_name() );
        }

        /**
         * Remove the page title in Vendor store page
         *
         * @param $title bool If true print the page title
         *
         * @since    1.0
         * @return void
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use woocommerce_show_page_title filter
         */
        public function remove_store_page_title( $title ) {
            return $this->is_vendor_page() ? false : $title;
        }

        /**
         * Print vendor store header in archive-product template
         *
         * @since    1.0
         * @return void
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @use woocommerce_show_page_title filter
         */
        public function add_store_page_header() {
            if ( $this->is_vendor_page() ) {

                global $sitepress;
                $has_wpml = ! empty( $sitepress );
                $default_term = null;
                $term = get_queried_object();
                if( $has_wpml ){
                    $term_slug      = yit_wpml_object_id( $term->term_id, YITH_Vendors()->get_taxonomy_name(), true, wpml_get_default_language() );
                    $default_term   = get_term( $term_slug, YITH_Vendors()->get_taxonomy_name() );
                }

                else {
                    $term_slug = $term->slug;
                }

                $vendor = yith_get_vendor( $term_slug, 'vendor' );

                if ( 'no' == $vendor->enable_selling ) {
                    return false;
                }

                $header_img_class = apply_filters( 'yith_wcmv_header_img_class', array( 'class' => 'store-image' ) );

                $args = array(
                    'vendor'             => $vendor,
                    'name'               => $has_wpml ? $default_term->name : $vendor->name,
                    'header_skin'        => get_option( 'yith_vendors_skin_header' ),
                    'show_total_sales'   => 'yes' == get_option( 'yith_wpv_vendor_total_sales' ) ? true : false,
                    'icons'              => apply_filters( 'yith_wcmv_header_icons_class', array(
                            'rating'        => 'fa fa-star-half-o',
                            'sales'         => 'fa fa-credit-card',
                            'vat'           => 'fa fa-file-text-o',
                            'legal_notes'   => 'fa fa-gavel',
                        )
                    ),
                    'owner'              => get_user_by( 'id', $vendor->owner ),
                    'header_img_class'   => $header_img_class,
                    'header_image'       => wp_get_attachment_image( $vendor->header_image, 'big', false, $header_img_class ),
                    'owner_avatar'       => get_avatar( $vendor->owner, get_option( 'yith_vendors_gravatar_image_size', '62' ) ),
                    'vendor_reviews'     => $vendor->get_reviews_average_and_product(),
                    'total_sales'        => count( $vendor->get_orders( 'suborder' ) ),
                    'store_header_class' => apply_filters( 'yith_wcmv_store_header_class', 'store-header-wrapper' ),
                    'show_vendor_vat'    => 'yes' === get_option( 'yith_wpv_vendor_show_vendor_vat', 'yes' ) ? true : false,
                    'show_gravatar'      => 'enabled' == get_option( 'yith_vendors_show_gravatar_image', 'enabled' ) && 'yes' == $vendor->show_gravatar,
                    'socials_list'       => YITH_Vendors()->get_social_fields(),
                );

                do_action( 'yith_wcmv_before_vendor_header', $args, $vendor );
                yith_wcpv_get_template( 'store-header', $args, 'woocommerce/loop' );

                /* Vacation Module */
                if( $vendor->is_on_vacation() ){
                    /* Support for MultiLingua plugins */
                    $vendor_vacation_message = call_user_func( '__', $vendor->vacation_message, 'yith-woocommerce-product-vendors' );
                    $args = array( 'vacation_message' => $vendor_vacation_message );
                    yith_wcpv_get_template( 'store-vacation', $args, 'woocommerce/loop' );
                }

                /* Vendor Description */
                $vendor_description = $has_wpml ? $default_term->description : $vendor->description;
                if( 'yes' == get_option( 'yith_wpv_vendor_store_description', 'no' ) && ! empty( $vendor_description ) ){
                    $args = array(
                        'store_description_class' => apply_filters( 'yith_wcmv_store_descritpion_class', 'store-description-wrapper' ),
                        'vendor_description'      => $vendor_description
                    );
                    yith_wcpv_get_template( 'store-description', $args, 'woocommerce/loop' );
                }
                do_action( 'yith_wcmv_after_vendor_header', $args, $vendor );
            }
        }

        /**
         * Enqueue Style and Scripts
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @fire yith_wpv_stylesheet_paths The stylesheet paths
         */
        public function enqueue_scripts() {
            /* === Main Stylesheet === */
            wp_enqueue_style( 'yith-wc-product-vendors', YITH_WPV_ASSETS_URL . 'css/product-vendors.css' );

            /* === Theme Stylesheet === */
            $paths = apply_filters( 'yith_wpv_stylesheet_paths', array(
                    WC()->template_path() . 'product-vendors.css',
                    'product-vendors.css',
                )
            );

            $located = locate_template( $paths, false, false );
            $search  = array( get_stylesheet_directory(), get_template_directory() );
            $replace = array( get_stylesheet_directory_uri(), get_template_directory_uri() );

            if ( ! empty( $located ) ) {
                $theme_stylesheet = str_replace( $search, $replace, $located );
                wp_enqueue_style( 'yith-wc-product-vendors-theme', $theme_stylesheet );
            }

            /* === Font Awesome Sylesheet === */
            wp_enqueue_style( 'font-awesome', YITH_WPV_ASSETS_URL . 'third-party/font-awesome/css/font-awesome.min.css' );

            /* === Scripts === */
            $gmaps_api_key = get_option( 'yith_wpv_frontpage_gmaps_key', '' );
            $gmaps_api_uri = '//maps.google.com/maps/api/js?language=en';

            if( ! empty( $gmaps_api_key ) ){
                $gmaps_api_uri .= "&key={$gmaps_api_key}";
            }
            
            wp_register_script( 'imagesloaded', YITH_WPV_ASSETS_URL . 'third-party/imagesloaded/imagesloaded.pkgd.min.js', array( 'jquery' ), '3.1.8', true );
            wp_register_script( 'gmaps-api', $gmaps_api_uri, array( 'jquery' ) );
            wp_register_script( 'gmap3', YITH_WPV_ASSETS_URL . 'third-party/gmap3/gmap3.min.js', array( 'jquery', 'gmaps-api' ), '6.0.0', false );

            if ( $this->is_vendor_page() ) {
                wp_enqueue_script( 'gmap3' );
            }

            /* Add PrettyPhoto if WooCommerce Lightbox is disabled and Report Abuse link activated */
            $show_report_abuse  = 'none' != get_option( 'yith_wpv_report_abuse_link_text' ) ? true : false;
            $lightbox           = get_option( 'woocommerce_enable_lightbox', 'no' );
            $lightbox_disabled  = 'no' === $lightbox || empty( $lightbox ) || ! $lightbox;
            if ( is_singular('product') ) {
                wp_register_script( 'yith-wpv-prettyPhoto-init', YITH_WPV_ASSETS_URL . 'js/init.prettyPhoto.js', array( 'jquery', 'prettyPhoto' ), YITH_WPV_VERSION, true );
                if( $show_report_abuse && $lightbox_disabled ){
                    $wc_assets_path = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';
                    $suffix         = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
                    wp_register_script( 'prettyPhoto', $wc_assets_path . 'js/prettyPhoto/jquery.prettyPhoto' . $suffix . '.js', array( 'jquery' ), '3.1.5', true );
                    wp_enqueue_style( 'woocommerce_prettyPhoto_css', $wc_assets_path . 'css/prettyPhoto.css' );
                }
            }

            if ( $show_report_abuse ) {
                $args = array(
                    'ajaxurl'  => admin_url( 'admin-ajax.php' ),

                    'messages' => array(
                        'success' => __( 'Abuse reported correctly.', 'yith-woocommerce-product-vendors' ),
                        'empty'   => __( 'All fields are mandatory.', 'yith-woocommerce-product-vendors' ),
                        'failed'  => __( 'Your request could not be processed. Please try again', 'yith-woocommerce-product-vendors' )
                    ),

                    'classes'  => array(
                        'success' => 'woocommerce-message',
                        'failed'  => 'woocommerce-error',
                        'empty'   => 'woocommerce-info',
                    ),
                );

                wp_enqueue_script( 'yith-wpv-prettyPhoto-init' );
                wp_localize_script( 'yith-wpv-prettyPhoto-init', 'report_abuse', $args );
            }

            $js_file = function_exists( 'yit_load_js_file' ) ? yit_load_js_file( 'multi-vendor.js' ) : 'multi-vendor.min.js';
            wp_enqueue_script( 'product-vendors', YITH_WPV_ASSETS_URL . 'js/' . $js_file, array( 'jquery', 'imagesloaded' ), '1.0', true );
            wp_localize_script( 'product-vendors', 'field_check', array( 'is_vat_require' => YITH_Vendors()->is_vat_require() ) );
        }

        /**
         * Add Vendor registration form
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_form() {
            $enabled = 'yes' == get_option( 'yith_wpv_vendors_my_account_registration', 'no' ) ? true : false;
            if ( $enabled ) {
                $args = array(
                    'is_vat_require'                    => YITH_Vendors()->is_vat_require(),
                    'is_terms_and_conditions_require'   => YITH_Vendors()->is_terms_and_conditions_require(),
                    'is_become_a_vendor_page'           => $this->is_become_a_vendor_page(),
                    'become_a_vendor_style'             => get_option( 'yith_wpv_become_a_vendor_style', 'myaccount' ),
                );

                yith_wcpv_get_template( 'vendor-registration', $args, 'woocommerce/myaccount' );
            }
        }

        /**
         * Process Vendor registration form
         *
         * @param $validation_error
         * @param $username
         * @param $password
         * @param $email
         *
         * @return    array The error array
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function process_registration( $validation_error, $check_type = 'new_vendor' ) {
            /* @var $validation_error WP_Error */
            if ( isset( $_POST['vendor-register'] ) ) {

                if ( empty( $_POST['vendor-owner-firstname'] ) && 'new_vendor' == $check_type ) {
                    $validation_error->add( 'empty_firstname', $this->get_registration_error_string( 'firstname' ) ) ;
                    wc_add_notice( '<strong>' . __( 'Error', 'woocommerce' ) . ':</strong> ' . $this->get_registration_error_string( 'firstname' ), 'error' );
                }

                if ( empty( $_POST['vendor-owner-lastname'] ) && 'new_vendor' == $check_type ) {
                    $validation_error->add( 'empty_lastname', $this->get_registration_error_string( 'lastname' ) );
                }

                if ( empty( $_POST['vendor-location'] ) ) {
                    $validation_error->add( 'empty_location', $this->get_registration_error_string( 'location' ) );
                    'switch_customer' == $check_type && wc_add_notice( '<strong>' . __( 'Error', 'woocommerce' ) . ':</strong> ' . $this->get_registration_error_string( 'location' ), 'error' );
                }

                if ( empty( $_POST['vendor-email'] ) ) {
                    $validation_error->add( 'empty_email', $this->get_registration_error_string( 'email' ) );
                    'switch_customer' == $check_type && wc_add_notice( '<strong>' . __( 'Error', 'woocommerce' ) . ':</strong> ' . $this->get_registration_error_string( 'email' ), 'error' );
                }

                else {
                    false === is_email( $_POST['vendor-email'] ) && $validation_error->add( 'is_not_email', $this->get_registration_error_string( 'not_email' ) );
                    'switch_customer' == $check_type && false === is_email( $_POST['vendor-email'] ) && wc_add_notice( '<strong>' . __( 'Error', 'woocommerce' ) . ':</strong> ' . $this->get_registration_error_string( 'not_email' ), 'error' );
                }

                if ( empty( $_POST['vendor-telephone'] ) ) {
                    $validation_error->add( 'empty_telephone', $this->get_registration_error_string( 'telephone' ) );
                    'switch_customer' == $check_type && wc_add_notice( '<strong>' . __( 'Error', 'woocommerce' ) . ':</strong> ' . $this->get_registration_error_string( 'empty_telephone' ), 'error' );
                }

                if ( empty( $_POST['vendor-name'] ) ) {
                    $validation_error->add( 'empty_term', $this->get_registration_error_string( 'store_name' ) );
                    'switch_customer' == $check_type && wc_add_notice( '<strong>' . __( 'Error', 'woocommerce' ) . ':</strong> ' . $this->get_registration_error_string( 'store_name' ), 'error' );
                }

                if( empty( $_POST['vendor-vat'] ) && YITH_Vendors()->is_vat_require() ){
                    $validation_error->add( 'vat_ssn', $this->get_registration_error_string( 'vat_ssn' ) );
                    'switch_customer' == $check_type && wc_add_notice( '<strong>' . __( 'Error', 'woocommerce' ) . ':</strong> ' . $this->get_registration_error_string( 'vat_ssn' ), 'error' );
                }

                if( empty( $_POST['vendor-terms'] ) && YITH_Vendors()->is_terms_and_conditions_require() ){
                    $validation_error->add( 'terms_and_conditions', $this->get_registration_error_string( 'terms_and_conditions' ) );
                    'switch_customer' == $check_type && wc_add_notice( '<strong>' . __( 'Error', 'woocommerce' ) . ':</strong> ' . $this->get_registration_error_string( 'terms_and_conditions' ), 'error' );
                }

                else {
                    $term       = sanitize_text_field( $_POST['vendor-name'] );
                    $duplicated = yith_wcpv_check_duplicate_term_name( $term, YITH_Vendors()->get_taxonomy_name() );
                    if ( $duplicated ) {
                        $validation_error->add( 'term_exists', $this->get_registration_error_string( 'duplicated' ), $duplicated );
                        'switch_customer' == $check_type && wc_add_notice( '<strong>' . __( 'Error', 'woocommerce' ) . ':</strong> ' . $this->get_registration_error_string( 'duplicated' ), 'error' );
                    }
                }

                if ( ! empty( $_POST['vendor-antispam'] ) ) {
                    $validation_error->add( 'antispam', $this->get_registration_error_string( 'antispam' ) );
                    'switch_customer' == $check_type && wc_add_notice( '<strong>' . __( 'Error', 'woocommerce' ) . ':</strong> ' . $this->get_registration_error_string( 'antispam' ), 'error' );
                }
            }
            return $validation_error;
        }

        /**
         * Get error message in registration form
         *
         * @param $field string the field that require string error message
         *
         * @return   string Error message
         * @since    1.7
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function get_registration_error_string( $field = 'all' ){
            $errors = array(
                'firstname'            => __( 'The First name field is mandatory', 'yith-woocommerce-product-vendors' ),
                'lastname'             => __( 'The Last name field is mandatory', 'yith-woocommerce-product-vendors' ),
                'location'             => __( 'The Store address field is mandatory', 'yith-woocommerce-product-vendors' ),
                'email'                => __( 'The Email field is mandatory', 'yith-woocommerce-product-vendors' ),
                'not_email'            => __( 'The Email address entered is not valid', 'yith-woocommerce-product-vendors' ),
                'telephone'            => __( 'The Telephone field is mandatory', 'yith-woocommerce-product-vendors' ),
                'vat_ssn'              => __( 'The VAT/SSN field is mandatory', 'yith-woocommerce-product-vendors' ),
                'store_name'           => __( 'Insert the vendor name', 'yith-woocommerce-product-vendors' ),
                'duplicated'           => __( 'A vendor with this name already exists', 'yith-woocommerce-product-vendors' ),
                'terms_and_conditions' => __( 'Please, read and accept the terms & conditions', 'yith-woocommerce-product-vendors' ),
                'antispam'             => __( 'Please, no spam here', 'yith-woocommerce-product-vendors' ),
            );

            if( 'all' != $field ){
                return isset( $errors[ $field ] ) ? $errors[ $field ] : false;
            }

            else {
                return $errors;
            }
        }

        /**
         * Create the vendor profile after Vendor registration
         *
         * @param      $customer_id       The new customer_id
         * @param bool $new_customer_data
         *
         * @return   string Vendor id
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function create_vendor( $customer_id, $new_customer_data = false ) {
            $term      = sanitize_text_field( $_POST['vendor-name'] );
            $firstname = isset( $_POST['vendor-owner-firstname'] ) ? sanitize_text_field( $_POST['vendor-owner-firstname'] ) : false;
            $lastname  = isset( $_POST['vendor-owner-lastname'] )  ? sanitize_text_field( $_POST['vendor-owner-lastname'] ) : false;

            /* Create Vendor */
            $term_info = wp_insert_term( $term, YITH_Vendors()->get_taxonomy_name() );

            if ( is_wp_error( $term_info ) ) {
                return;
            }

            /* Auto Enable Account */
            $auto_enable = 'yes' == get_option( 'yith_wpv_vendors_my_account_registration_auto_approve', 'no' ) ? true : false;

            /* Get the new vendor */
            $vendor      = yith_get_vendor( $term_info['term_id'] );
            $customer_id = intval( $customer_id );

            /* Set the vendor information by magic method */
            $vendor->owner                 = $customer_id;
            $vendor->location              = sanitize_text_field( $_POST['vendor-location'] );
            $vendor->telephone             = sanitize_text_field( $_POST['vendor-telephone'] );
            $vendor->store_email           = sanitize_email( $_POST['vendor-email'] );
            $vendor->registration_date     = current_time( 'mysql' );
            $vendor->registration_date_gmt = current_time( 'mysql', 1 );
            $vendor->enable_selling        = $auto_enable ? 'yes' : 'no';
            $vendor->vat                   = isset( $_POST['vendor-vat'] ) ? sanitize_text_field( $_POST['vendor-vat'] ) : '';

            /* Add First name and Last name to new user */
            if( $firstname && $lastname ) {
                wp_update_user( array( 'ID' => $customer_id, 'first_name' => $firstname, 'last_name' => $lastname ) );
            }

            /* New vendor field */
            if( ! $auto_enable ){
                $vendor->pending = 'yes';
            }

            /* Set Owner */
            update_user_meta( $customer_id, YITH_Vendors()->get_user_meta_owner(), $vendor->id );
            update_user_meta( $customer_id, YITH_Vendors()->get_user_meta_key(), $vendor->id );

            /* Add Vendor Owner Capabilities */
            $owner = get_user_by( 'id', $vendor->owner );
            $owner->add_role( YITH_Vendors()->get_role_name() );
            $owner->remove_role( 'customer' );

            //Check for review option
            YITH_Vendors()->force_skip_review_option( array( $vendor ) );

            /* Allow plugins/theme to add new vendor fields */
            $vendor = apply_filters( 'yith_new_vendor_registration_fields', $vendor );

            /* Email Sender */
            do_action( 'yith_new_vendor_registration', $vendor );

            return ! empty( $vendor->id ) ? $vendor->id : false;
        }

        /**
         * Add vendor dashboard endpoint in my Account
         *
         * @param bool|YITH_Vendor $vendor vendor object
         *
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function vendor_dashboard_endpoint( $vendor = false ) {

            if( ! $vendor ){
                $vendor = yith_get_vendor( 'current', 'user' );
            }

            if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
                $args = array(
                    'is_pending'    => 'yes' == $vendor->pending ? true : false,
                    'vendor_name'   => $vendor->name
                );
                yith_wcpv_get_template( 'my-vendor-dashboard', $args, 'woocommerce/myaccount' );
            }
        }

        /**
         * Check product vendor coupon
         *
         * @return   void
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return   bool true if is possibile to apply the coupon for current product, false otherwise
         */
        public function check_vendor_coupon_product( $valid, $product, $coupon, $values ) {
            if ( $valid ) {
                $vendor_id = get_post_meta( $coupon->id, 'vendor_id' );
                if ( ! empty( $vendor_id ) ) {
                    $vendor_id      = array_shift( $vendor_id );
                    $discount_types = array( 'fixed_product', 'percent_product' );
                    if ( in_array( $coupon->discount_type, $discount_types ) && empty( $coupon->product_ids ) ) {
                        $vendor = yith_get_vendor( $product, 'product' );
                        return $vendor->is_valid() && $vendor->id === absint( $vendor_id );
                    }
                }
            }
            return $valid;
        }

        /**
         * Check if in the cart there is a product from a vendor
         *
         * Use to apply a cart coupon
         *
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return   bool true if is possibile to apply the coupon for current product, false otherwise
         */
        public function vendor_coupon_is_valid( $valid, $coupon ) {
            $vendor_id = get_post_meta( $coupon->id, 'vendor_id' );

            if ( ! $vendor_id || ! empty( $coupon->product_ids ) ) {
                return $valid;
            }

            $vendor = yith_get_vendor( absint( array_shift( $vendor_id ) ), 'vendor' );

            $cart = WC()->cart->get_cart();
            foreach ( $cart as $k => $item ) {
                if ( in_array( $item['product_id'], $vendor->get_products() ) ) {
                    return true;
                }
            }
            return false;
        }

        /**
         * Add a report abuse link in single product page
         *
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return   void
         */
        public function add_report_abuse_link() {
            $abuse  = get_option( 'yith_wpv_report_abuse_link', 'none' );
            $vendor = yith_get_vendor( 'current', 'product' );

            $args = array(
                'abuse_text'   => get_option( 'yith_wpv_report_abuse_link_text' ),
                'button_class' => apply_filters( 'yith_wpv_report_abuse_button_class', 'submit' ),
                'submit_label' => apply_filters( 'yith_wpv_report_submit_button_label', __( 'Report', 'yith-woocommerce-product-vendors' ) ),
                'vendor'       => $vendor,
                'product'      => wc_get_product(),
            );

            if ( 'all' == $abuse ) {
                yith_wcpv_get_template( 'abuse', $args, 'woocommerce/single-product' );
            }

            if ( 'vendor' == $abuse ) {
                if ( $vendor->is_valid() ) {
                    yith_wcpv_get_template( 'abuse', $args, 'woocommerce/single-product' );
                }
            }
        }

        /**
         * Send a report to abuse
         *
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return   void
         */
        public function send_report_abuse() {
            $check        = false;
            $report_abuse = $_REQUEST['report_abuse'];

            if ( ! empty( $report_abuse['spam'] ) ) {
                wp_send_json( 'spam' );
            }

            $name         = sanitize_text_field( $report_abuse['name'] );
            $from_email   = sanitize_email( $report_abuse['email'] );
            $user_message = sanitize_text_field( $report_abuse['message'] );

            $check = ! empty( $name ) && ! empty( $from_email ) && ! empty( $user_message );

            if ( $check ) {
                $product = get_post( absint( sanitize_text_field( $report_abuse['product_id'] ) ) );
                $vendor  = yith_get_vendor( absint( sanitize_text_field( $report_abuse['vendor_id'] ) ) );

                $subject    = sanitize_text_field( $report_abuse['subject'] );
                $to         = sanitize_email( get_option( 'woocommerce_email_from_address' ) );
                $from_email = sanitize_email( $report_abuse['email'] );
                $headers    = "From: {$name} <{$from_email}>" . "\r\n";

                $message = sprintf( __( "User %s (%s) is reporting an abuse on the following product: \n", 'yith-woocommerce-product-vendors' ), $name, $from_email );
                $message .= sprintf( __( "Product details: %s (ID: #%s) \n", 'yith-woocommerce-product-vendors' ), $product->post_title, $product->ID );

                if ( $vendor->is_valid() ) {
                    $message .= sprintf( __( "Vendor shop: %s (ID: #%s) \n", 'yith-woocommerce-product-vendors' ), $vendor->name, $vendor->id );
                }

                $message .= sprintf( __( "Message: %s\n", 'yith-woocommerce-product-vendors' ), $user_message );
                $message .= "\n\n\n";

                $message .= sprintf( __( "Product page: %s\n", 'yith-woocommerce-product-vendors' ), get_the_permalink( $product->ID ) );

                if ( $vendor->is_valid() ) {
                    $message .= sprintf( __( "Vendor Account details: %s \n", 'yith-woocommerce-product-vendors' ), $vendor->get_url( 'admin' ) );
                }


                /* === Send Mail === */
                $response = wp_mail( $to, $subject, $message, $headers );
                wp_send_json( $response );
            }

            wp_send_json( 'empty_value' );
        }

        /**
         * Add total sales product meta
         *
         * @since    1.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return   void
         */
        public function woocommerce_product_meta() {
            if ( 'no' == get_option( 'yith_wpv_vendor_show_item_sold', 'no' ) ) {
                return false;
            }

            global $product;
            $vendor = yith_get_vendor( $product, 'product' );

            if ( ! $vendor->is_valid() ) {
                return false;
            }

            global $product; ?>
            <span class="item-sold">
                <?php _e( 'Item sold', 'yith-woocommerce-product-vendors' ); ?>:
                <strong><?php echo get_post_meta( $product->id, 'total_sales', true ); ?></strong>
            </span>
        <?php
        }

        /**
         * Add sold by information to product in cart and in checkout order review details
         *
         * The follow args are documented in woocommerce\templates\cart\cart.php:74
         *
         * @param           $product_title    The product title HTML
         * @param           $cart_item        The cart item array
         * @param bool|\The $cart_item_key    The cart item key
         *
         * @since    1.6
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string  The product title HTML
         * @use     woocommerce_cart_item_name hook
         */
        public function add_sold_by_vendor( $product_title, $cart_item, $cart_item_key = false ) {
            $vendor = yith_get_vendor( $cart_item['product_id'], 'product' );
            if ( $vendor->is_valid() ) {
                $product_title .= ' (<small>' . apply_filters('yith_wcmv_sold_by_string_frontend', _x( 'Sold by', 'Cart details: Product sold by', 'yith-woocommerce-product-vendors' ) ) . ': ' . '<a href="' . $vendor->get_url( 'frontend' ) . '">' . $vendor->name . '</a></small>)';
            }
            return $product_title;
        }

        /**
         * Check if vendor is in pending to enable or disable admin access
         *
         * @param $show The show parameter
         *
         * @since    1.6
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  string  The product title HTML
         */
        public function show_admin_bar( $show ) {
            $vendor = yith_get_vendor( 'current', 'user' );

            if ( $vendor->is_valid() && $vendor->has_limited_access() && $vendor->pending ) {
                $show = false;
            }

            return $show;
        }

        /**
         * 
         */
        public function switch_customer_to_vendor(){
            $current_user_id = get_current_user_id();
            do_action( 'yith_wcmv_before_become_a_vendor', $current_user_id );
            if( isset( $_POST['vendor-register'] ) && ! empty( $current_user_id ) && is_user_logged_in() ){
                $validation = new WP_Error();
                $validation = $this->process_registration( $validation, 'switch_customer' );
                if( empty( $validation->errors ) ) {
                    $vendor_id = $this->create_vendor( $current_user_id );

                    if( $vendor_id ){
                        //Check for review option
                        if( 'yes' == get_option( 'yith_wpv_vendors_option_skip_review', 'no' ) ){
                            $vendor = yith_get_vendor( $vendor_id );
                            if( $vendor->is_valid() ){
                                YITH_Vendors()->force_skip_review_option( array( $vendor ) );
                            }
                        }
                        do_action( 'yith_wcmv_after_become_a_vendor', $vendor_id, $current_user_id );
                        wp_redirect( apply_filters( 'yith_wcmv_after_become_a_vendor_redirect_uri', get_permalink( wc_get_page_id( 'myaccount' ) ) ) );
                    }
                }
            }
        }

        /**
         * Check if current page is the become a vendor page
         *
         * @since    1.9.16
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  bool  true if the current page is the become a vendor page, false otherwise
         */
        public function is_become_a_vendor_page(){
            return is_page( get_option( 'yith_wpv_become_a_vendor_page_id' ) );
        }

        /**
         * Add a body class(es)
         *
         * @param $classes The classes array
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.9.16
         * @return array
         */
        public function body_class( $classes ) {
            if ( $this->is_become_a_vendor_page() ) {
                $become_a_vendor_style = get_option( 'yith_wpv_become_a_vendor_style', 'myaccount' );
                $classes[] = 'become-a-vendor';
                if( 'multivendor' == $become_a_vendor_style ){
                    $classes[] = 'multi-vendor-style';    
                }
            }
            return $classes;
        }
    }
}