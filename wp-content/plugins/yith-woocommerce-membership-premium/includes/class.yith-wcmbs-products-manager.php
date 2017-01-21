<?php
if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Members Class
 *
 * @class   YITH_WCMBS_Products_Manager
 * @package Yithemes
 * @since   1.0.0
 * @author  Yithemes
 *
 */
class YITH_WCMBS_Products_Manager {

    /**
     * Single instance of the class
     *
     * @var \YITH_WCMBS_Products_Manager
     * @since 1.0.0
     */
    protected static $instance;

    private $_manage_products;

    /**
     * Returns single instance of the class
     *
     * @return \YITH_WCMBS_Products_Manager
     * @since 1.0.0
     */
    public static function get_instance() {
        $self = __CLASS__;

        if ( is_null( $self::$instance ) ) {
            $self::$instance = new $self;
        }

        return $self::$instance;
    }

    /**
     * Constructor
     *
     * @access public
     * @since  1.0.0
     */
    public function __construct() {
        $this->_manage_products = get_option( 'yith-wcmbs-products-in-membership-management', 'hide_products' );
        if ( $this->is_allowed_download() ) {
            add_filter( 'yith_wcmbs_restricted_post_types', array( $this, 'remove_product_post_type_in_manager' ) );

            // add description to add products in plan
            add_filter( 'yith_wcmbs_add_products_in_plan_description', array( $this, 'add_products_in_plan_description' ) );

            // set the default value for only downloadable for YITH_WCMBS_Membership->get_products()
            add_filter( 'yith_wcmbs_membership_default_only_downloadable', '__return_true' );

            $download_link_position = get_option( 'yith-wcmbs-download-link-position', 'tab' );
            switch ( $download_link_position ) {
                case 'tab':
                    add_filter( 'woocommerce_product_tabs', array( $this, 'product_tabs' ) );
                    break;
                case 'before_summary':
                    add_action( 'woocommerce_before_single_product_summary', array( $this, 'print_download_link_html' ), 25 );
                    break;
                case 'before_description':
                    add_action( 'woocommerce_single_product_summary', array( $this, 'print_download_link_html' ), 15 );
                    break;
                case 'after_description':
                    add_action( 'woocommerce_single_product_summary', array( $this, 'print_download_link_html' ), 25 );
                    break;
                case 'after_add_to_cart':
                    add_action( 'woocommerce_single_product_summary', array( $this, 'print_download_link_html' ), 35 );
                    break;
                case 'after_summary':
                    add_action( 'woocommerce_after_single_product_summary', array( $this, 'print_download_link_html' ), 9 );
                    break;
            }

            add_filter( 'yith_wcmbs_allowed_in_plan', array( $this, 'add_products_in_plan' ), 10, 3 );

            if ( isset( $_GET[ 'protected_file' ] ) && isset( $_GET[ 'product_id' ] ) ) {
                add_action( 'init', array( $this, 'download_protected_file' ), 999 );
            }

            add_action( 'yith_wcmbs_before_product_download', array( $this, 'check_if_has_credits_to_download' ), 10, 2 );

            if ( !is_admin() ) {
                add_action( 'woocommerce_before_single_product_summary', array( $this, 'hide_price_and_add_to_cart' ) );
            }

            /* Set Custom Credits in products */
            add_action( 'woocommerce_product_options_pricing', array( $this, 'add_credits_field_in_products' ) );
            add_action( 'save_post', array( $this, 'save_credits_field_for_products' ) );

        }
    }

    /**
     * Add Credits field in product options metabox
     */
    public function add_credits_field_in_products() {
        global $post;

        $credits           = yith_wcmbs_get_product_credits( $post->ID );
        $credit_field_args = array(
            'id'                => '_yith_wcmbs_credits',
            'label'             => __( 'Credits', 'yith-woocommerce-membership' ),
            'type'              => 'number',
            'wrapper_class'     => 'show_if_downloadable',
            'value'             => $credits,
            'custom_attributes' => array(
                'min'     => 0,
                'step'    => 1,
                'pattern' => '\d*'
            ),
        );

        woocommerce_wp_text_input( $credit_field_args );
    }

    /**
     * Save custom Credits for products
     *
     * @param int $post_id the id of the product
     */
    public function save_credits_field_for_products( $post_id ) {
        if ( 'product' == get_post_type( $post_id ) ) {
            if ( isset( $_POST[ '_yith_wcmbs_credits' ] ) ) {
                update_post_meta( $post_id, '_yith_wcmbs_credits', absint( $_POST[ '_yith_wcmbs_credits' ] ) );
            }
        }
    }

    public function hide_price_and_add_to_cart() {
        $hide_price_and_add_to_cart = get_option( 'yith-wcmbs-hide-price-and-add-to-cart', 'no' ) == 'yes';
        if ( $hide_price_and_add_to_cart ) {
            global $product;

            if ( $product ) {
                $downloadable = false;
                if ( $product->product_type != 'variable' ) {
                    if ( $product->is_downloadable() ) {
                        $downloadable = true;
                    }
                } else {
                    $variations = $product->get_children();
                    if ( !empty( $variations ) ) {
                        foreach ( $variations as $variation ) {
                            $p = wc_get_product( $variation );
                            if ( $p->is_downloadable() ) {
                                $downloadable = true;
                                break;
                            }
                        }
                    }
                }

                if ( $downloadable && $this->user_has_access_to_product( get_current_user_id(), $product->id ) && !$this->product_needs_credits_to_download( get_current_user_id(), $product->id ) ) {
                    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
                    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );

                    do_action( 'yith_wcbk_hide_price_and_add_to_cart' );
                }
            }
        }
    }


    /**
     * check if user has credits to download product
     *
     * @param int        $user_id
     * @param WC_Product $product
     */
    public function check_if_has_credits_to_download( $user_id, $product ) {
        /* Admin doesn't need credits to download products */
        if ( user_can( $user_id, 'edit_users' ) )
            return;

        $member = YITH_WCMBS_Members()->get_member( $user_id );
        /* If member has already downloaded the product he doesn't need credits to download the product */
        if ( $member->has_just_downloaded_product( $product->id ) )
            return;

        $credits_for_product = yith_wcmbs_get_product_credits( $product->id );
        /* If product has credits set to 0, the product doesn't need credits to be downloaded */
        if ( !$credits_for_product )
            return;

        $user_plans    = $member->get_membership_plans( array( 'return' => 'complete' ) );
        $product_plans = $this->product_is_in_plans( $product->id );

        $need_credits  = true;
        $credits_array = array();
        foreach ( $user_plans as $membership ) {
            $plan_ids = array_intersect( array_merge( array( $membership->plan_id ), $membership->get_linked_plans() ), $product_plans );
            if ( !empty( $plan_ids ) ) {
                if ( !$membership->has_credit_management() ) {
                    $need_credits = false;
                } else {
                    $credits_array[] = array(
                        'membership' => $membership,
                        'credits'    => $membership->get_remaining_credits()
                    );
                }
            }
        }

        if ( $need_credits ) {
            $has_credits = false;
            $max_credits = array();
            foreach ( $credits_array as $credit_array ) {
                $max_credits[] = absint( $credit_array[ 'credits' ] );
                if ( $credit_array[ 'credits' ] >= $credits_for_product ) {
                    $has_credits = true;
                    /** @var YITH_WCMBS_Membership $current_membership */
                    $current_membership = $credit_array[ 'membership' ];
                    $current_membership->remove_credit( $credits_for_product );
                    break;
                }
            }
            if ( !$has_credits ) {
                $max_credits = max( $max_credits );
                $alert       = __( 'You can\'t access to this content. You don\'t have enough credits.', 'yith-woocommerce-membership' );
                $alert .= '<br />';
                $alert .= sprintf( _n( 'This product needs one credit,', 'This product needs %s credits,', $credits_for_product, 'yith-woocommerce-membership' ), $credits_for_product );
                $alert .= sprintf( _n( 'but you have only one credit!', 'but you have only %s credits!', $max_credits, 'yith-woocommerce-membership' ), $max_credits );
                wp_die( $alert, __( 'Restricted Access.', 'yith-woocommerce-membership' ) );
            }
        }
    }

    /**
     * check if product needs credits to be downloaded by user
     *
     * @param $user_id
     * @param $product_id
     *
     * @return bool
     */
    public function product_needs_credits_to_download( $user_id, $product_id ) {
        if ( user_can( $user_id, 'edit_users' ) ) {
            return false;
        }
        $member = YITH_WCMBS_Members()->get_member( $user_id );

        if ( $member->has_just_downloaded_product( $product_id ) )
            return false;

        $credits_for_product = yith_wcmbs_get_product_credits( $product_id );
        /* If product has credits set to 0, the product doesn't need credits to be downloaded */
        if ( !$credits_for_product )
            return false;

        $product_plans = $this->product_is_in_plans( $product_id );
        $user_plans    = $member->get_membership_plans( array( 'return' => 'complete' ) );

        $need_credits = true;
        foreach ( $user_plans as $membership ) {
            $plan_ids = array_intersect( array_merge( array( $membership->plan_id ), $membership->get_linked_plans() ), $product_plans );
            if ( !empty( $plan_ids ) ) {
                if ( !$membership->has_credit_management() ) {
                    $need_credits = false;

                    return false;
                }
            }
        }

        return $need_credits;
    }

    public function add_products_in_plan_description( $description ) {
        $description .= ' ' . __( 'Remember that only downloadable products will be visible to members.', 'yith-woocommerce-membership' );

        return $description;
    }

    /**
     * Check if user has access to product. If user have access forces the file download
     *
     * @since 1.0.0
     */
    public function download_protected_file() {
        $product_id         = $_GET[ 'product_id' ];
        $protected_file_key = $_GET[ 'protected_file' ];

        $user_id = get_current_user_id();

        $product = wc_get_product( $product_id );

        if ( $product && $product->product_type == 'variable' ) {
            $children = $product->get_children();
            if ( !empty( $children ) ) {
                foreach ( $children as $child_id ) {
                    $child = wc_get_product( $child_id );
                    if ( $child && $child->has_file( $protected_file_key ) ) {
                        $product = $child;
                        break;
                    }
                }
            }
        }

        if ( $product && $product->has_file( $protected_file_key ) && $this->user_has_access_to_product( $user_id, $product_id ) ) {
            do_action( 'yith_wcmbs_before_product_download', $user_id, $product );

            $file      = $product->get_file( $protected_file_key );
            $file_path = $file[ 'file' ];

            YITH_WCMBS_Downloads_Report()->add_report( $product->id, $user_id );
            WC_Download_Handler::download( $file_path, $product->id );
        } else {
            wp_die( __( 'You can\'t access to this content', 'yith-woocommerce-membership' ), __( 'Restricted Access.', 'yith-woocommerce-membership' ) );
        }
    }

    /**
     * return true if the option for Manage Products is allow_download
     *
     * @return string|void
     */
    public function is_allowed_download() {
        return $this->_manage_products == 'allow_download';
    }

    public function add_products_in_plan( $allowed_in_plan, $plan_id, $user_id = 0 ) {
        // get products in plan
        $args     = array(
            'post_type'                  => 'product',
            'posts_per_page'             => -1,
            'post_status'                => 'publish',
            'yith_wcmbs_suppress_filter' => true,
            'meta_query'                 => array(
                array(
                    'key'     => '_yith_wcmbs_restrict_access_plan',
                    'value'   => $plan_id,
                    'compare' => 'LIKE',
                )
            ),
        );
        $products = get_posts( $args );

        $plan_cats      = get_post_meta( $plan_id, '_product-cats', true );
        $plan_prod_tags = get_post_meta( $plan_id, '_product-tags', true );

        $cat_tag_args = array(
            'post_type'                  => 'product',
            'posts_per_page'             => -1,
            'post_status'                => 'publish',
            'yith_wcmbs_suppress_filter' => true,
            'tax_query'                  => array(
                'relation' => 'OR',
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $plan_cats,
                    'operator' => 'IN'
                ),
                array(
                    'taxonomy' => 'product_tag',
                    'field'    => 'term_id',
                    'terms'    => $plan_prod_tags,
                    'operator' => 'IN'
                )
            ),
        );
        $products     = array_merge( $products, get_posts( $cat_tag_args ) );

        $product_ids = array();
        if ( !empty( $products ) ) {
            foreach ( $products as $product ) {
                $p = wc_get_product( $product->ID );

                if ( $user_id > 0 ) {

                    $delay     = get_post_meta( $product->ID, '_yith_wcmbs_plan_delay', true );
                    $member    = YITH_WCMBS_Members()->get_member( $user_id );
                    $last_plan = $member->get_oldest_active_plan( $plan_id );
                    if ( isset( $delay[ $plan_id ] ) && $last_plan instanceof YITH_WCMBS_Membership ) {
                        $delay_days = $delay[ $plan_id ];

                        $date = $last_plan->start_date + ( $last_plan->paused_days * 60 * 60 * 24 );

                        $passed_days = intval( ( time() - $date ) / ( 24 * 60 * 60 ) );
                        if ( $passed_days <= $delay_days )
                            continue;
                    }
                }
                if ( $p ) {
                    $downloadable = false;
                    if ( $p->product_type != 'variable' ) {
                        if ( $p->is_downloadable() ) {
                            $downloadable = true;
                        }
                    } else {
                        $variations = $p->get_children();
                        if ( !empty( $variations ) ) {
                            foreach ( $variations as $variation ) {
                                $p_tmp = wc_get_product( $variation );
                                if ( $p_tmp->is_downloadable() ) {
                                    $downloadable = true;
                                    break;
                                }
                            }
                        }
                    }

                    // add ONLY Downloadable Products
                    if ( $downloadable ) {
                        $product_ids[] = $product->ID;
                    }
                }
            }
        }


        return array_unique( array_merge( $allowed_in_plan, $product_ids ) );
    }

    /**
     * add tabs to product
     *
     * @access   public
     * @since    1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    public function product_tabs( $tabs ) {
        global $post;

        $product_plans = $this->product_is_in_plans( $post->ID );
        $product       = wc_get_product( $post->ID );

        if ( $product && !empty( $product_plans ) ) {
            $downloadable = false;
            if ( $product->product_type != 'variable' ) {
                if ( $product->is_downloadable() ) {
                    $downloadable = true;
                }
            } else {
                $variations = $product->get_children();
                if ( !empty( $variations ) ) {
                    foreach ( $variations as $variation ) {
                        $p = wc_get_product( $variation );
                        if ( $p->is_downloadable() ) {
                            $downloadable = true;
                            break;
                        }
                    }
                }
            }

            if ( $downloadable && $this->user_has_access_to_product( get_current_user_id(), $post->ID ) ) {
                $tabs[ 'yith-wcmbs-download' ] = array(
                    'title'      => __( 'Downloads', 'yith-woocommerce-membership' ),
                    'priority'   => 99,
                    'callback'   => array( $this, 'create_tab_content' ),
                    'product_id' => $post->ID
                );
            }
        }

        return $tabs;
    }


    /**
     * return true if user has access to product
     *
     *
     *
     * @access   public
     * @since    1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    public function user_has_access_to_product( $user_id, $product_id ) {

        if ( $user_id == get_current_user_id() && current_user_can( 'edit_users' ) )
            return true;

        $user_has_access = apply_filters( 'yith_wcmbs_user_has_access_to_product', false, $user_id, $product_id );
        if ( $user_has_access )
            return true;

        $product_plans = $this->product_is_in_plans( $product_id );

        $delay  = get_post_meta( $product_id, '_yith_wcmbs_plan_delay', true );
        $member = YITH_WCMBS_Members()->get_member( $user_id );

        $user_plans = $member->get_membership_plans( array( 'return' => 'complete' ) );

        foreach ( $user_plans as $membership ) {
            $plan_ids = array_intersect( array_merge( array( $membership->plan_id ), $membership->get_linked_plans() ), $product_plans );
            if ( !empty( $plan_ids ) ) {

                $start_date = $membership->start_date + ( $membership->paused_days * 60 * 60 * 24 );
                if ( !empty( $delay ) ) {
                    if ( !isset( $delay[ $membership->plan_id ] ) ) {
                        $linked         = $membership->get_linked_plans();
                        $min_delay_time = 0;
                        $first          = true;
                        foreach ( $linked as $plan_id ) {
                            if ( isset( $delay[ $plan_id ] ) ) {
                                if ( $first ) {
                                    $min_delay_time = $delay[ $plan_id ];
                                    $first          = false;
                                } else {
                                    if ( $delay[ $plan_id ] < $min_delay_time ) {
                                        $min_delay_time = $delay[ $plan_id ];
                                    }
                                }
                            }
                        }

                        if ( $min_delay_time > 0 ) {
                            if ( strtotime( '+' . $min_delay_time . ' days midnight', $start_date ) <= strtotime( 'midnight' ) ) {
                                return true;
                            }
                        } else {
                            return true;
                        }
                    } else {
                        if ( $delay[ $membership->plan_id ] < 1 || strtotime( '+' . $delay[ $membership->plan_id ] . ' days midnight', $start_date ) <= strtotime( 'midnight' ) ) {
                            return true;
                        }
                    }
                } else {
                    return true;
                }
            }
        }

        return false;
        // VECCHIO codice -------------------------------------------------
        $user_plans = $member->get_membership_plans( array( 'return' => 'id_date', 'include_linked' => true ) );

        if ( !empty( $user_plans ) ) {
            foreach ( $user_plans as $plan ) {
                if ( in_array( $plan[ 'id' ], $product_plans ) ) {
                    if ( isset( $delay[ $plan[ 'id' ] ] ) ) {
                        $delay_days = $delay[ $plan[ 'id' ] ];

                        if ( strtotime( '+' . $delay_days . ' days midnight', $plan[ 'date' ] ) <= strtotime( 'midnight' ) )
                            return true;
                    } else {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * create the content of download tab
     *
     * @access   public
     * @since    1.0.0
     *
     * @param string $key the key of the tab
     * @param array  $tab array that contains info of tab (title, priority, callback, product_id)
     *
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    public function create_tab_content( $key, $tab ) {
        $this->print_download_link_html();
    }

    /**
     * print the download link list for product in membership
     *
     * @access   public
     * @since    1.0.0
     **
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    public function print_download_link_html() {
        global $post;
        if ( !$this->user_has_access_to_product( get_current_user_id(), $post->ID ) )
            return;

        echo do_shortcode( '[membership_download_product_links id="' . $post->ID . '"]' );

        return;

        // OLD CODE

        $default_args = array(
            'type'       => '',
            'link_class' => 'yith-wcmbs-button',
            'link_title' => __( 'Download', 'yith-woocommerce-membership' )
        );

        $args       = wp_parse_args( $args, $default_args );
        $type       = $args[ 'type' ];
        $link_class = $args[ 'link_class' ];
        $link_title = $args[ 'link_title' ];

        $unlocked = $this->is_allowed_download() ? !$this->product_needs_credits_to_download( get_current_user_id(), $post->ID ) : true;
        $link_class .= $unlocked ? ' unlocked' : ' locked';

        $product_id = $post->ID;
        $product    = wc_get_product( $product_id );
        $files      = array();

        if ( $product ) {
            if ( $product->product_type != 'variable' ) {
                if ( $product->is_downloadable() ) {
                    $files = $product->get_files();
                }
            } else {
                $variations = $product->get_children();
                if ( !empty( $variations ) ) {
                    foreach ( $variations as $variation ) {
                        $p = wc_get_product( $variation );
                        if ( $p->is_downloadable() ) {
                            $files = array_merge( $files, $p->get_files() );
                        }
                    }
                }
            }
        }

        if ( !empty( $files ) ) {
            $stamp = '';
            foreach ( $files as $key => $file ) {
                $link  = add_query_arg( array( 'protected_file' => $key, 'product_id' => $product_id ), home_url( '/' ) );
                $name  = !empty( $link_title ) ? $link_title : $file[ 'name' ];
                $title = $file[ 'name' ];

                $product_link = "<a class='{$link_class}' href='{$link}' title='$title'>$name</a>";
                if ( $type == 'list' )
                    $product_link = "<li>{$product_link}</li>";

                $stamp .= $product_link;
            }

            if ( $type == 'list' )
                $stamp = '<ul class="yith-wcmbs-download-file-list">' . $stamp . '</ul>';

            echo $stamp;
        }
    }

    public function get_download_links( $args = array() ) {

        if ( !$this->is_allowed_download() )
            return array();

        global $post;

        $default_args = array(
            'return' => 'links', //link_name
            'id'     => false
        );
        $args         = wp_parse_args( $args, $default_args );
        $return       = $args[ 'return' ];

        $product_id = $args[ 'id' ] ? $args[ 'id' ] : $post->ID;

        $user_id = get_current_user_id();

        if ( !$this->user_has_access_to_product( $user_id, $product_id ) )
            return array();

        $product = wc_get_product( $product_id );
        $files   = array();

        if ( $product ) {
            if ( $product->product_type != 'variable' ) {
                if ( $product->is_downloadable() ) {
                    $files = $product->get_files();
                }
            } else {
                $variations = $product->get_children();
                if ( !empty( $variations ) ) {
                    foreach ( $variations as $variation ) {
                        $p = wc_get_product( $variation );
                        if ( $p->is_downloadable() ) {
                            $files = array_merge( $files, $p->get_files() );
                        }
                    }
                }
            }
        }

        $unlocked = !$this->product_needs_credits_to_download( $user_id, $product_id );

        $r = array();

        if ( !empty( $files ) ) {
            foreach ( $files as $key => $file ) {
                $link = add_query_arg( array( 'protected_file' => $key, 'product_id' => $product_id ), home_url( '/' ) );
                $name = !empty( $link_title ) ? $link_title : $file[ 'name' ];
                switch ( $return ) {
                    case 'links':
                        $r[] = $link;
                        break;
                    case 'links_names':
                        $r[] = array(
                            'link'     => $link,
                            'name'     => $name,
                            'unlocked' => $unlocked
                        );
                        break;
                }
            }
        }

        return $r;
    }

    /**
     * get a list of plan ids that have a product
     *
     * @param int $product_id the id of the product
     *
     * @return array
     *
     * @access   public
     * @since    1.0.0
     *
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    public function product_is_in_plans( $product_id ) {
        $product = wc_get_product( $product_id );
        if ( !$product )
            return array();

        $plan_ids = array();

        $restrict_access_plan = get_post_meta( $product_id, '_yith_wcmbs_restrict_access_plan', true );
        if ( !empty( $restrict_access_plan ) ) {
            $plan_ids = $restrict_access_plan;
        }

        $prod_cats_plans_array = array();
        $prod_tags_plans_array = array();
        $plans_info            = YITH_WCMBS_Manager()->get_plans_info_array();;
        extract( $plans_info );

        // FILTER PRODUCT CATS AND TAGS IN PLANS
        if ( !empty( $prod_cats_plans_array ) ) {
            //$this_product_cats = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
            $this_product_cats = yith_wcmbs_get_post_term_ids( $product_id, 'product_cat', array(), true );
            foreach ( $prod_cats_plans_array as $cat_id => $c_plan_ids ) {
                if ( !empty( $c_plan_ids ) && in_array( $cat_id, (array) $this_product_cats ) ) {
                    $plan_ids = array_merge( $plan_ids, $c_plan_ids );
                }
            }
        }
        if ( !empty( $prod_tags_plans_array ) ) {
            $this_product_tags = wp_get_post_terms( $product_id, 'product_tag', array( 'fields' => 'ids' ) );
            foreach ( $prod_tags_plans_array as $tag_id => $t_plan_ids ) {
                if ( !empty( $t_plan_ids ) && in_array( $tag_id, (array) $this_product_tags ) ) {
                    $plan_ids = array_merge( $plan_ids, $t_plan_ids );
                }
            }
        }

        foreach ( $plan_ids as $key => $plan_id ) {
            $allowed           = YITH_WCMBS_Manager()->exclude_hidden_items( array( $product_id ), $plan_id );
            $is_hidden_in_plan = empty( $allowed );
            if ( $is_hidden_in_plan )
                unset( $plan_ids[ $key ] );
        }

        return array_unique( $plan_ids );
    }

    /**
     * Remove products from post type in manager
     *
     * @param array $post_types the post types in manager. default values are 'post', 'product', 'page', 'attachment'
     *
     * @return array
     *
     * @access public
     * @since  1.0.0
     */
    public function remove_product_post_type_in_manager( $post_types ) {
        $post_types = array_diff( $post_types, array( 'product' ) );

        return $post_types;
    }

}

/**
 * Unique access to instance of YITH_WCMBS_Products_Manager class
 *
 * @return YITH_WCMBS_Products_Manager
 * @since 1.0.0
 */
function YITH_WCMBS_Products_Manager() {
    return YITH_WCMBS_Products_Manager::get_instance();
}