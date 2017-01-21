<?php
/**
 * Frontend class
 *
 * @author  Yithemes
 * @package YITH WooCommerce Membership
 * @version 1.1.1
 */

if ( !defined( 'YITH_WCMBS' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WCMBS_Frontend_Premium' ) ) {
    /**
     * Frontend class.
     * The class manage all the Frontend behaviors.
     *
     * @since    1.0.0
     * @author   Leanza Francesco <leanzafrancesco@gmail.com>
     */
    class YITH_WCMBS_Frontend_Premium extends YITH_WCMBS_Frontend {

        /**
         * Constructor
         *
         * @access public
         * @since  1.0.0
         */
        public function __construct() {
            // add frontend css
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );


            $hide_contents_option = get_option( 'yith-wcmbs-hide-contents', 'all' );

            if ( $hide_contents_option == 'alternative_content' ) {
                add_filter( 'the_content', array( $this, 'filter_content_for_membership' ) );
                add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'control_product_access_in_shop' ) );
                add_action( 'woocommerce_before_main_content', array( $this, 'control_product_access_in_product_page' ) );
            }

            if ( $hide_contents_option == 'redirect' ) {
                add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'control_product_access_in_shop' ) );
                add_action( 'get_header', array( $this, 'redirect_if_not_have_access' ) );
            }

            if ( $hide_contents_option != 'redirect' ) {
                // Filter Post, Pages and product
                add_action( 'pre_get_posts', array( $this, 'hide_not_allowed_posts' ) );
                add_filter( 'the_posts', array( $this, 'filter_posts' ) );
                add_filter( 'get_pages', array( $this, 'filter_posts' ) );

                // Filter nav menu
                add_filter( 'wp_nav_menu_objects', array( $this, 'filter_nav_menu_pages' ), 10, 2 );
                // Filter next and previous post link
                add_filter( 'get_next_post_where', array( $this, 'filter_adiacent_post_where' ), 10, 3 );
                add_filter( 'get_previous_post_where', array( $this, 'filter_adiacent_post_where' ), 10, 3 );
            }

            /* Validate product before add to cart */
            add_action( 'woocommerce_add_to_cart', array( $this, 'validate_product_add_to_cart' ), 10, 6 );

            /* Print Membership History in My Account */
            add_action( 'woocommerce_after_my_account', array( $this, 'print_membership_history' ) );

            /* Checkout Validation*/
            add_action( 'woocommerce_checkout_init', array( $this, 'checkout_validation' ) );

            /* Messages in Frontend */
            YITH_WCMBS_Messages_Manager_Frontend();
        }

        /**
         * checkout validation for membership products
         *
         * @param WC_Checkout $checkout
         */
        public function checkout_validation( $checkout ) {
            if ( get_option( 'yith-wcmbs-enable-guest-checkout', 'no' ) == 'yes' || $checkout->must_create_account == true )
                return;

            foreach ( WC()->cart->cart_contents as $key => $item ) {
                $prod_id = isset( $item[ 'product_id' ] ) ? $item[ 'product_id' ] : 0;
                if ( YITH_WCMBS_Manager()->get_plan_by_membership_product( $prod_id ) ) {
                    $checkout->must_create_account = is_user_logged_in() ? false : true;
                    if ( $checkout->must_create_account ) {
                        wp_enqueue_script( 'yith_wcmbs_force_create_account', YITH_WCMBS_ASSETS_URL . '/js/force_create_account.js', array( 'jquery' ), YITH_WCMBS_VERSION, true );
                    }
                    break;
                }
            }

        }


        /**
         * Unset posts with alternative content, if the option "hide-content" = 'alternative_content'
         *
         * @param array $post_ids
         *
         * @access public
         * @since  1.0.0
         *
         * @return array
         */
        public function unset_posts_with_alternative_content( $post_ids ) {
            $hide_contents_option = get_option( 'yith-wcmbs-hide-contents', 'all' );

            $new_post_ids = array();

            if ( !empty( $post_ids ) && $hide_contents_option == 'alternative_content' ) {
                foreach ( $post_ids as $id ) {
                    $alternative_content = get_post_meta( $id, '_alternative-content', true );
                    if ( empty( $alternative_content ) ) {
                        $new_post_ids[] = $id;
                    }
                }
            } else {
                return $post_ids;
            }

            return $new_post_ids;
        }


        /**
         * Filter Adiacent Posts (next and previous)
         *
         * @param string $where
         * @param bool   $in_same_term
         * @param array  $excluded_terms
         *
         * @access public
         * @since  1.0.0
         *
         * @return string
         */
        public function filter_adiacent_post_where( $where, $in_same_term, $excluded_terms ) {
            $current_user_id      = get_current_user_id();
            $non_allowed_post_ids = YITH_WCMBS_Manager()->get_non_allowed_post_ids_for_user( $current_user_id );

            $non_allowed_post_ids = $this->unset_posts_with_alternative_content( $non_allowed_post_ids );

            if ( !empty( $non_allowed_post_ids ) )
                $where .= " AND p.ID NOT IN (" . implode( $non_allowed_post_ids, ',' ) . ')';

            return $where;
        }

        /**
         * Filter Nav Menu Pages
         *
         * @param $items array
         * @param $args  array
         *
         * @access public
         * @since  1.0.0
         *
         * @return array
         */
        public function filter_nav_menu_pages( $items, $args ) {
            $current_user_id      = get_current_user_id();
            $non_allowed_post_ids = YITH_WCMBS_Manager()->get_non_allowed_post_ids_for_user( $current_user_id );

            $non_allowed_post_ids = $this->unset_posts_with_alternative_content( $non_allowed_post_ids );

            foreach ( $items as $key => $post ) {
                if ( in_array( absint( $post->object_id ), $non_allowed_post_ids ) ) {
                    unset( $items[ $key ] );
                }
            }

            return $items;
        }

        /**
         * Filter pre get posts Query
         *
         * @param $query WP_Query
         *
         * @access public
         * @since  1.0.0
         */
        public function hide_not_allowed_posts( $query ) {
            $suppress_filter = isset( $query->query[ 'yith_wcmbs_suppress_filter' ] ) ? $query->query[ 'yith_wcmbs_suppress_filter' ] : false;

            $restricted_post_types   = apply_filters( 'yith_wcmbs_restricted_post_types', YITH_WCMBS_Manager()->post_types );
            $is_restricted_post_type = isset( $query->query[ 'post_type' ] ) ? in_array( $query->query[ 'post_type' ], $restricted_post_types ) : true;

            if ( $is_restricted_post_type && !$suppress_filter ) {

                $current_user_id      = get_current_user_id();
                $non_allowed_post_ids = YITH_WCMBS_Manager()->get_non_allowed_post_ids_for_user( $current_user_id );

                $non_allowed_post_ids = $this->unset_posts_with_alternative_content( $non_allowed_post_ids );

                $query->set( 'post__not_in', (array) $non_allowed_post_ids );
            }
        }

        /**
         * Filter posts
         *
         * @param array $posts
         *
         * @return array
         *
         * @access public
         * @since  1.0.0
         */
        public function filter_posts( $posts ) {
            $current_user_id = get_current_user_id();

            $hide_contents_option = get_option( 'yith-wcmbs-hide-contents', 'all' );


            foreach ( $posts as $post_key => $post ) {
                if ( !YITH_WCMBS_Manager()->user_has_access_to_post( $current_user_id, $post->ID ) ) {
                    if ( $hide_contents_option == 'alternative_content' ) {
                        $alternative_content = get_post_meta( $post->ID, '_alternative-content', true );
                        if ( empty( $alternative_content ) ) {
                            unset( $posts[ $post_key ] );
                        }
                    } else {
                        unset( $posts[ $post_key ] );
                    }
                }
            }

            return $posts;
        }


        /**
         * If user doesn't have access to content, redirect to the link setted by admin
         *
         *
         * @access public
         * @since  1.0.0
         */
        public function redirect_if_not_have_access() {
            global $post;
            $current_user_id = get_current_user_id();

            if ( ( is_single() || is_page() ) && !YITH_WCMBS_Manager()->user_has_access_to_post( $current_user_id, $post->ID ) ) {
                $redirect_link = get_option( 'yith-wcmbs-redirect-link', '' );
                if ( !empty( $redirect_link ) ) {
                    if ( strpos( $redirect_link, 'http' ) != 0 )
                        $redirect_link = 'http://' . str_replace( 'http://', '', $redirect_link );
                }
                wp_redirect( $redirect_link );
            }
        }

        /**
         * Before add to cart a product check if user can buy it
         * If user cannot buy the product, show a Error message
         *
         *
         * @access public
         * @since  1.0.0
         */
        public function validate_product_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
            $current_user_id = get_current_user_id();
            $product         = wc_get_product( $product_id );
            $product_title   = $product->get_title();

            if ( !YITH_WCMBS_Manager()->user_has_access_to_post( $current_user_id, $product_id ) ) {
                throw new Exception( sprintf( __( 'You cannot purchase "%s". To do it, you need a membership plan', 'yith-woocommerce-membership' ), $product_title ) );
            }
        }


        /**
         * Control the allowed access for products in shop
         * If the user don't have access remove all WooCommerce actions that show contents in shop
         *
         *
         *
         * @access public
         * @since  1.0.0
         */
        public function control_product_access_in_shop() {
            global $post;
            $current_user_id = get_current_user_id();

            if ( YITH_WCMBS_Manager()->user_has_access_to_post( $current_user_id, $post->ID ) ) {
                $this->restore_woocommerce_product_shop_actions();
            } else {
                $this->remove_woocommerce_product_shop_actions();
            }
        }

        /**
         * Control the allowed access for products in single product page
         * If the user don't have access remove all WooCommerce actions that show contents in single product page
         *
         *
         *
         * @access public
         * @since  1.0.0
         */
        public function control_product_access_in_product_page() {
            global $post;
            $current_user_id = get_current_user_id();

            if ( is_single() ) {
                if ( YITH_WCMBS_Manager()->user_has_access_to_post( $current_user_id, $post->ID ) ) {
                    $this->restore_woocommerce_product_actions();
                } else {
                    $this->remove_woocommerce_product_actions();
                }
            }
        }


        /**
         * Remove WooCommerce actions in Shop loop
         *
         *
         * @access public
         * @since  1.0.0
         */
        public function remove_woocommerce_product_shop_actions() {
            remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
            remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );

            remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
            remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
            remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
        }

        /**
         * Restore WooCommerce actions in Shop loop
         *
         *
         * @access public
         * @since  1.0.0
         */
        public function restore_woocommerce_product_shop_actions() {
            add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
            add_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );

            add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
            add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
            add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
        }

        /**
         * Remove WooCommerce actions in Single Product Page
         * and add alternative content
         *
         *
         * @access public
         * @since  1.0.0
         */
        public function remove_woocommerce_product_actions() {
            remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

            remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
            remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
            remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
            remove_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
            remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
            remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
            remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );

            add_action( 'woocommerce_single_product_summary', array( $this, 'get_the_alternative_content' ) );
        }

        /**
         * Restore WooCommerce actions in Single Product Page
         *
         *
         * @access public
         * @since  1.0.0
         */
        public function restore_woocommerce_product_actions() {
            add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

            add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
            add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
            add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
            add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
            add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );


            add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
            add_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
            add_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
            add_action( 'woocommerce_variable_add_to_cart', 'woocommerce_variable_add_to_cart', 30 );
            add_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
            add_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
            add_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );

            remove_action( 'woocommerce_single_product_summary', array( $this, 'get_the_alternative_content' ) );
        }


        /**
         * Print the alternative content for products
         *
         *
         * @access public
         * @since  1.0.0
         */
        public function get_the_alternative_content() {
            global $post;
            $alternative_content = get_post_meta( $post->ID, '_alternative-content', true );

            echo nl2br( $alternative_content );
        }

        /**
         * Filter the content in base of membership
         * if the user don't have access, show the alternative content
         *
         * @param string $content the content of post, page
         *
         * @return string
         *
         * @access public
         * @since  1.0.0
         */
        public function filter_content_for_membership( $content ) {
            global $post;
            $current_user_id = get_current_user_id();

            if ( YITH_WCMBS_Manager()->user_has_access_to_post( $current_user_id, $post->ID ) ) {
                return $content;
            } else {
                $alternative_content = get_post_meta( $post->ID, '_alternative-content', true );

                return $alternative_content;
            }

        }

        /**
         * Print Membership History in MyAccount
         *
         * @access public
         * @since  1.0.0
         */
        public function print_membership_history() {
            $show = get_option( 'yith-wcmbs-show-history-in-my-account', 'yes' ) == 'yes';
            if ( $show ) {
                $title     = __( 'Membership Plans:', 'yith-woocommerce-membership' );
                $shortcode = '[membership_history title="' . $title . '"]';
                $shortcode = apply_filters( 'yith_wcmbs_membership_history_shortcode_in_my_account', $shortcode, $title );

                do_shortcode( $shortcode );
            }
        }

        public function enqueue_scripts() {
            parent::enqueue_scripts();
            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

            wp_enqueue_style( 'yith_wcmbs_frontend_opensans', "//fonts.googleapis.com/css?family=Open+Sans:100,200,300,400,600,700,800" );

            wp_enqueue_style( 'yith_wcmbs_membership_icons', YITH_WCMBS_ASSETS_URL . '/fonts/membership-icons/style.css' );

            wp_enqueue_style( 'dashicons' );

            wp_enqueue_script( 'yith_wcmbs_frontend_js', YITH_WCMBS_ASSETS_URL . '/js/frontend_premium' . $suffix . '.js', array(
                'jquery',
                'jquery-ui-accordion',
                'jquery-ui-tabs',
                'jquery-ui-tooltip'
            ), YITH_WCMBS_VERSION, true );
            wp_localize_script( 'yith_wcmbs_frontend_js', 'my_ajax_obj', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'user_id'  => get_current_user_id()
            ) );

            if ( apply_filters( 'yith_wcmbs_inline_style', true ) ) {
                wp_add_inline_style( 'yith-wcmbs-frontent-styles', $this->get_inline_css_for_plans() );
            }
        }


        /**
         * get the custom css for plans
         *
         * @access public
         * @since  1.0.0
         */
        public function get_inline_css_for_plans() {
            $plans = YITH_WCMBS_Manager()->plans;

            $css = '';
            if ( !empty ( $plans ) ) {
                foreach ( $plans as $plan ) {
                    $plan_list_styles = get_post_meta( $plan->ID, '_yith_wcmbs_plan_list_styles', true );
                    $plan_id          = $plan->ID;

                    /**
                     * @var string $list_style
                     * @var string $title_color
                     * @var string $title_background
                     * @var string $title_font_size
                     * @var string $title_margin_top
                     * @var string $title_margin_right
                     * @var string $title_margin_bottom
                     * @var string $title_margin_left
                     * @var string $title_padding_top
                     * @var string $title_padding_right
                     * @var string $title_padding_bottom
                     * @var string $title_padding_left
                     * @var string $item_background
                     * @var string $item_color
                     * @var string $item_font_size
                     * @var string $item_margin_top
                     * @var string $item_margin_right
                     * @var string $item_margin_bottom
                     * @var string $item_margin_left
                     * @var string $item_padding_top
                     * @var string $item_padding_right
                     * @var string $item_padding_bottom
                     * @var string $item_padding_left
                     * @var string $show_icons
                     */

                    $default_plan_list_styles = array(
                        'list_style'           => 'none',
                        'title_color'          => '#333333',
                        'title_background'     => 'transparent',
                        'title_font_size'      => '15',
                        'title_margin_top'     => '0',
                        'title_margin_right'   => '0',
                        'title_margin_bottom'  => '0',
                        'title_margin_left'    => '0',
                        'title_padding_top'    => '0',
                        'title_padding_right'  => '0',
                        'title_padding_bottom' => '0',
                        'title_padding_left'   => '0',
                        'item_background'      => 'transparent',
                        'item_color'           => '#333333',
                        'item_font_size'       => '15',
                        'item_margin_top'      => '0',
                        'item_margin_right'    => '0',
                        'item_margin_bottom'   => '0',
                        'item_margin_left'     => '20',
                        'item_padding_top'     => '0',
                        'item_padding_right'   => '0',
                        'item_padding_bottom'  => '0',
                        'item_padding_left'    => '0',
                        'show_icons'           => 'yes'
                    );

                    $plan_list_styles = wp_parse_args( $plan_list_styles, $default_plan_list_styles );

                    extract( $plan_list_styles );

                    $dark_item_color = wc_hex_lighter( $item_color );

                    $css
                        .= ".yith-wcmbs-plan-list-container-{$plan_id} ul.child{
                        list-style: $list_style;
                        margin-left: 0px;
                    }

                    .yith-wcmbs-plan-list-container-{$plan_id} ul.child li{
                        margin-top:     {$item_margin_top}px;
                        margin-right:   {$item_margin_right}px;
                        margin-bottom:  {$item_margin_bottom}px;
                        margin-left:    {$item_margin_left}px;
                        padding-top:    {$item_padding_top}px;
                        padding-right:  {$item_padding_right}px;
                        padding-bottom: {$item_padding_bottom}px;
                        padding-left:   {$item_padding_left}px;
                        background:     {$item_background};
                    }

                    .yith-wcmbs-plan-list-container-{$plan_id} p{
                        color:          $title_color;
                        font-size:      {$title_font_size}px ;
                        margin-top:     {$title_margin_top}px;
                        margin-right:   {$title_margin_right}px;
                        margin-bottom:  {$title_margin_bottom}px;
                        margin-left:    {$title_margin_left}px;
                        padding-top:    {$title_padding_top}px;
                        padding-right:  {$title_padding_right}px;
                        padding-bottom: {$title_padding_bottom}px;
                        padding-left:   {$title_padding_left}px;
                        background:     {$title_background};
                    }
                    .yith-wcmbs-plan-list-container-{$plan_id} a, .yith-wcmbs-plan-list-container-{$plan_id} li{
                        color: $item_color;
                        font-size: {$item_font_size}px;
                    }
                    .yith-wcmbs-plan-list-container-{$plan_id} a:hover{
                        color: $dark_item_color;
                    }
                    ";
                }
            }

            return $css;
        }


    }
}

?>
