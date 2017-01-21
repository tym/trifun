<?php
/*
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

/**
 *
 *
 * @class      YITH_Vendors_Admin
 * @package    Yithemes
 * @since      Version 2.0.0
 * @author     Your Inspiration Themes
 *
 */
if ( ! class_exists( 'YITH_Vendors_Admin_Premium' ) ) {
    

    class YITH_Vendors_Admin_Premium extends YITH_Vendors_Admin {

        /**
         * Constructor
         */
        public function __construct() {
            parent::__construct();

            $commissions_screen = YITH_Commissions()->get_screen();

            /* Add the admin settings page in the vendor dashboard */
            add_action( 'admin_menu', array( $this, 'vendor_settings' ) );
            add_filter( 'yith_vendors_admin_tabs', array( $this, 'admin_tabs' ) );

            /* Taxonomy table customization */
            add_filter( "manage_edit-{$this->_taxonomy_name}_columns", array( $this, 'get_columns' ) );
            add_filter( "manage_{$this->_taxonomy_name}_custom_column", array( $this, 'custom_columns' ), 10, 3 );
            add_filter( "manage_edit-{$this->_taxonomy_name}_sortable_columns", array( $this, 'sortable_columns' ) );
            add_filter( "{$this->_taxonomy_name}_row_actions", array( $this, 'tag_row_actions' ), 10, 2 );
            add_action( 'admin_action_switch-selling-capability', array( $this, 'switch_selling_capability' ) );
            add_action( 'admin_action_switch-pending-status', array( $this, 'switch_pending_status' ) );
            add_filter( "manage_toplevel_page_{$commissions_screen}_columns", array( $this, 'add_commissions_screen_options' ) );
            remove_filter( "bulk_actions-edit-{$this->_taxonomy_name}", '__return_empty_array' );
            add_action( "load-edit-tags.php", array( $this, 'vendor_bulk_action' ) );

            /* Vendor admin customizzation */
            remove_action( 'admin_menu', array( $this, 'menu_items' ) );
            add_action( 'admin_menu', array( $this, 'remove_posts_page' ) );

            $vendor_panel_actions = array(
                'yith_wpv_edit_vendor_settings',
                'yith_wpv_edit_payments',
                'yith_wpv_edit_frontpage',
                'yith_wpv_edit_vendor_vacation'
            );

            foreach( $vendor_panel_actions as $action ){
                add_action( $action, array( $this, 'admin_settings_page' ) );
            }

            /* Taxonomy management */
            add_filter( 'yith_edit_taxonomy_args', array( $this, 'edit_taxonomy_args' ) );
            add_filter( 'yith_wpv_save_checkboxes', array( $this, 'save_empty_checkboxes' ), 10, 2 );

            /* Check vendor caps */
            add_action( 'yith_wpv_after_save_taxonomy', array( $this, 'check_skip_review_cap' ), 10, 2 );

            /* YIT plugins options */
            add_filter( 'yith_wpv_panel_commissions_options', array( $this, 'add_panel_premium_options' ), 10, 2 );
            add_filter( 'yith_wpv_panel_vendors_options', array( $this, 'add_panel_premium_options' ), 10, 2 );
            add_action( 'woocommerce_admin_field_button', array( $this, 'admin_field_button' ), 10, 1 );
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action( 'wp_ajax_wpv_vendors_force_skip_review_option', array( YITH_Vendors(), 'force_skip_review_option' ) );
            add_filter( 'yith_wcmv_manage_role_caps', array( $this, 'manage_premium_caps' ), 10, 1 );
            add_action( 'woocommerce_update_option', array( $this, 'woocommerce_update_payment_option' ) );

            /* Commissions actions */
            add_action( 'admin_init', array( $this, 'process_bulk_action' ) );

            /* Json Search Vendors */
            add_action( 'wp_ajax_yith_json_search_vendors', array( $this, 'json_search_vendors' ) );

            /* Add commission rate on single product */
            add_filter( 'woocommerce_product_data_tabs', array( $this, 'single_product_commission_tab' ) );
            add_action( 'woocommerce_product_data_panels', array( $this, 'single_product_commission_content' ) );
            add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_commission_meta' ), 10, 2 );

            /* Register plugin to licence/update system */
            add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
            add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );

            /* Vendor Products Limit */
            add_action( 'admin_head-post-new.php', array( $this, 'allowed_wc_post_types' ) );
            add_action( 'admin_head-edit.php', array( $this, 'allowed_wc_post_types' ) );
            add_action( 'admin_init', array( $this, 'allowed_comments' ) );
            add_filter( 'manage_product_posts_columns', array( $this, 'render_product_columns' ), 15 );

            /* Vendor Reviews Management */
            add_filter( 'pre_get_comments', array( $this, 'filter_reviews_list' ), 10, 1 );
            add_filter( 'wp_count_comments', array( $this, 'count_comments' ), 5, 2 );
            add_action( 'load-comment.php', array( $this, 'disabled_manage_other_comments' ) );

            /* Vendor Dashboard Setup */
            add_action( 'admin_menu', array( $this, 'add_dashboard_widgets' ) );

            /* Coupon management */
            add_action( 'woocommerce_coupon_options_save', array( $this, 'add_vendor_to_coupon' ), 10, 1 );

            /* Order Details Customizzation */
            add_action( 'woocommerce_before_order_itemmeta', array( $this, 'add_sold_by_to_order' ), 10, 3 );

            /* Manage YIT Shortcodes Plugin */
            add_action( 'admin_init', array( $this, 'remove_shortcodes_button' ), 5 );

            /* Check for vendor's owner */
            add_action( 'admin_notices', array( $this, 'check_vendors_owner' ) );

            add_action( 'edit_tag_form_fields', array( $this, 'add_wp_editor_to_vendor_tax' ) );

            /* Hack WooCommerce Email Settings */
            add_filter( 'is_email', array( $this, 'is_email_hack' ), 10, 2 );

            /* Enabled duplicate product for vendor */
            add_filter( 'woocommerce_duplicate_product_capability', array( $this, 'enabled_duplicate_product_capability' ) );

            /* Media Management */
            remove_action( 'admin_menu', array( $this, 'remove_media_page' ) );

            /* Bulk and Quick Edit Products */
            add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_render' ), 10, 2 );
            add_action( 'bulk_edit_custom_box', array( $this, 'quick_edit_render' ), 10, 2 );
            add_action( 'manage_posts_custom_column', array( $this, 'manage_product_vendor_tax_column' ), 10, 2 );
            
            /* Save Post - Change the post status */
            add_action( 'yith_wcmv_save_post_product', array( $this, 'set_product_to_pending_review_after_edit' ), 10, 3 );
        }

        /**
         * Add input hidden with vendor id
         *
         * @param $col_name
         * @param $post_id
         */
        public function manage_product_vendor_tax_column( $col_name, $post_id ){
            if( $col_name == 'name' ){
                $vendor = yith_get_vendor( $post_id, 'product' );
                if( $vendor->is_valid() && current_user_can('manage_woocommerce') ){
                    $vendor_name = addslashes( $vendor->name );
                    echo "<input type='hidden' id='vendor-product-{$post_id}' value='{$vendor->id}' data-vendor_id='{$vendor->id}' />";
                }
            }
        }

        /**
         * Quick Edit output render
         *
         * @param $column_name
         * @param $post_type
         */
        public function quick_edit_render( $column_name, $post_type ) {
            $enabled = apply_filters( 'yith_wcmv_quick_bulk_edit_enabled', true );
            if( $enabled && $post_type == 'product' ){
                $vendor_col_name = 'taxonomy-' . YITH_Vendors()->get_taxonomy_name();
                if ( $column_name == $vendor_col_name ) {
                    $vendor = yith_get_vendor( 'current', 'user' );
                    if( $vendor->is_super_user() ){
                        yith_wcpv_get_template( 'bulk-set-vendor', array(), 'woocommerce/admin/products' );
                    }
                }
            }
        }

        /**
         * Add options tab
         *
         * @param $tabs
         */
        public function admin_tabs( $tabs ) {
            $tabs['payments']  = __( 'Payments', 'yith-woocommerce-product-vendors' );
            $tabs['frontpage'] = __( 'Frontpage', 'yith-woocommerce-product-vendors' );
            $tabs['reports']   = __( 'Reports', 'yith-woocommerce-product-vendors' );
            $tabs['add-ons']   = __( 'Add-ons', 'yith-woocommerce-product-vendors' );
            unset( $tabs['premium'] );
            return $tabs;
        }

        /**
         * Add items to dashboard menu
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0.0
         * @return void
         */
        public function vendor_settings() {
            $vendor = yith_get_vendor( 'current', 'user' );

            /* Add the pending vendor bubble on Products -> Vendors menu */
            if ( $vendor->is_super_user() ) {
                global $menu, $submenu, $pagenow;
                $pending_vendors = count( YITH_Vendors()->get_vendors( array( 'pending' => 'yes', 'fields' => 'ids' ) ) );

                if ( $pending_vendors > 0 ) {
                    $bubble              = " <span class='awaiting-mod count-{$pending_vendors}'><span class='pending-count'>{$pending_vendors}</span></span>";
                    $vendor_taxonomy_uri = htmlspecialchars( add_query_arg( array( 'taxonomy'  => YITH_Vendors()->get_taxonomy_name(), 'post_type' => 'product' ), 'edit-tags.php' ) );
                    $products_uri        = htmlspecialchars( add_query_arg( array( 'post_type' => 'product' ), 'edit.php' ) );

                    foreach ( $menu as $key => $value ) {
                        if ( $menu[$key][2] == $vendor_taxonomy_uri && $pending_vendors > 0 ) {
                            $menu[$key][0] .= $bubble;
                        }
                    }

                    foreach ( $submenu as $key => $value ) {
                        $submenu_items = $submenu[$key];
                        foreach ( $submenu_items as $position => $value ) {
                            if ( $submenu[$key][$position][2] == $vendor_taxonomy_uri ) {
                                $submenu[$key][$position][0] .= $bubble;
                                return;
                            }
                        }
                    }
                }
            }
            
            /**
             * @deprecated yith_wcmv_show_vendor_profile. 
             * Use the new hook yith_wcmv_hide_vendor_profile instead
             * 
             * The hold yith_wcmv_show_vendor_profile will remove in version 2.0.0
             */
            $hide_vendor_profile = apply_filters( 'yith_wcmv_hide_vendor_profile', false );
            if ( ! $vendor->is_valid() || ! $vendor->has_limited_access() || $hide_vendor_profile ) {
                return;
            }

            $general_tab = apply_filters( 'yith_wcmv_vendor_tabs', array(
                    'vendor-frontpage' => __( 'Front page', 'yith-woocommerce-product-vendors' ),
                )
            );

            $owner_tab = apply_filters( 'yith_wcmv_vendor_owner_tabs', array(
                    'vendor-settings' => __( 'Vendor settings', 'yith-woocommerce-product-vendors' ),
                    'vendor-payments' => __( 'Payments', 'yith-woocommerce-product-vendors' ),
                )
            );

            $admin_tabs = $vendor->is_owner() ? array_merge( $owner_tab, $general_tab ) : $general_tab;
            $page_title = $menu_title = __( 'Vendor Profile', 'yith-woocommerce-product-vendors' );

            $args = array(
                'create_menu_page' => false,
                'parent_slug'      => '',
                'page_title'       => $page_title,
                'menu_title'       => $menu_title,
                'capability'       => $this->_vendor_role,
                'parent'           => 'vendor_' . $vendor->id,
                'parent_page'      => '',
                'page'             => 'yith_vendor_settings',
                'admin-tabs'       => apply_filters( 'yith_wcmv_panel_admin_tabs', $admin_tabs ),
                'options-path'     => YITH_WPV_PATH . 'plugin-options/vendor',
                'icon_url'         => 'dashicons-id-alt',
                'position'         => 30
            );

            /* === Fixed: not updated theme/old plugin framework  === */
            if ( ! class_exists( 'YIT_Plugin_Panel' ) ) {
                require_once( YITH_WPV_PATH . 'plugin-fw/lib/yit-plugin-panel.php' );
            }

            $this->_vendor_panel = new YIT_Plugin_Panel( $args );
        }

        /**
         * Add TinyMCE text editor
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         *
         * @param string $value The text area value
         * @param array  $args  Text editor params
         *
         * @return void
         */
        public function add_wp_editor( $value = '', $args = array(), $add_remove_scripts = false ) {
            $default = array(
                'wpautop'       => false,
                'media_buttons' => 'yes' == get_option( 'yith_wpv_vendors_option_editor_media', 'no' ) ? true : false,
                'quicktags'     => true,
                'textarea_rows' => '15',
                'textarea_name' => 'yith_vendor_data[description]',
                'textarea_id'   => ''
            );

            $args          = wp_parse_args( $args, $default );
            $inline_script = "jQuery('#submit').mousedown( function() { tinyMCE.triggerSave(); });";
            $remove_script = "jQuery('{$args['textarea_id']}').closest('tr.form-field').remove();";

            if( $add_remove_scripts ){
                wc_enqueue_js( $remove_script );
            }

            wp_editor( wp_kses_post( $value, 'UTF-8' ), 'yithvendorstoredescription', $args );
            wc_enqueue_js( $inline_script );
        }

        /**
         * Add upload fields
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         *
         * @return void
         */
        public function add_upload_field( $wrapper = 'div', $image_id = '', $type = 'header_image' ) {
            $args = array(
                'placeholder'           => empty( $image_id ) ? wc_placeholder_img_src() : wp_get_attachment_url( $image_id ),
                'image_wrapper_id'      => "yith_vendor_{$type}",
                'hidden_field_id'       => "yith_vendor_{$type}_id",
                'hidden_field_name'     => "yith_vendor_data[{$type}]",
                'upload_image_button'   => "upload_image_button_{$type}",
                'remove_image_button'   => "remove_image_button_{$type}",
                'wrapper'               => $wrapper,
                'image_id'              => $image_id
            );

            yith_wcpv_get_template( 'taxonomy-upload-field', $args, 'admin' );

            $this->add_upload_field_script( $args );
        }

        /**
         * Add upload fields script
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         *
         * @return void
         * @use wc_enqueue_js
         */
        public function add_upload_field_script( $args ) {
            extract($args);
            // Only show the "remove image" button when needed
            $inline_script = "if ( ! jQuery('#{$hidden_field_id}').val() ) {
                jQuery('.{$remove_image_button}').hide();
            }

            // Uploading files
            var file_frame;

            jQuery( document ).on( 'click', '.{$upload_image_button}', function( event ) {

                event.preventDefault();

                // If the media frame already exists, reopen it.
                if ( file_frame ) {
                    file_frame.open();
                    return;
                }

                // Create the media frame.
                file_frame = wp.media.frames.downloadable_file = wp.media({
                    title: '" . __( 'Choose an image', 'yith-woocommerce-product-vendors' ) . "',
                    button: {
                        text: '" . __( 'Use image', 'yith-woocommerce-product-vendors' ) . "',
                    },
                    multiple: false
                });

                // When an image is selected, run a callback.
                file_frame.on( 'select', function() {
                    attachment = file_frame.state().get('selection').first().toJSON();

                    jQuery('#{$hidden_field_id}').val( attachment.id );
                    jQuery('#{$image_wrapper_id} img').attr('src', attachment.sizes.thumbnail.url );
                    jQuery('.{$remove_image_button}').show();
                });

                // Finally, open the modal.
                file_frame.open();
            });

            jQuery( document ).on( 'click', '.{$remove_image_button}', function( event ) {
                jQuery('#{$image_wrapper_id} img').attr('src', '" . wc_placeholder_img_src() . "');
                jQuery('#{$hidden_field_id}').val('');
                jQuery('.{$remove_image_button}').hide();
                return false;
            });";

            wc_enqueue_js( $inline_script );
        }

        /**
         * Add custom column
         *
         * @Author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0.0
         *
         * @param $columns The columns
         *
         * @return array The columns list
         * @use manage_{$this->screen->id}_columns filter
         */
        public function get_columns( $columns ) {
            $to_remove = array( 'description', 'posts', 'slug' );

            foreach ( $to_remove as $column ) {
                unset( $columns[$column] );
            }

            $to_add = array(
                'registration_date' => __( 'Registration date', 'yith-woocommerce-product-vendors' ),
                'owner'             => __( 'Owner', 'yith-woocommerce-product-vendors' ),
                'enable_sales'      => __( 'Enable', 'yith-woocommerce-product-vendors' ),
                'vat'               => __( 'VAT/SSN', 'yith-woocommerce-product-vendors' ),
                'commission_rate'   => __( 'Commission', 'yith-woocommerce-product-vendors' ),
                'products'          => __( 'Items', 'yith-woocommerce-product-vendors' ),
                'user_actions'      => __( 'Actions', 'yith-woocommerce-product-vendors' ),
            );

            if( ! YITH_Vendors()->is_vat_require() ){
                unset( $to_add['vat'] );
            }

            return array_merge( $columns, $to_add );
        }


        /**
         * Remove the description column from taxonomy table
         *
         * @Author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since    1.0.0
         *
         * @param $custom_column Filter value
         * @param $column_name   Column name
         * @param $term_id       The term id
         *
         * @internal param \The $columns columns
         *
         * @return array The columns list
         * @use manage_{$this->screen->taxonomy}_custom_column filter
         */
        public function custom_columns( $custom_column, $column_name, $term_id ) {
            $vendor = yith_get_vendor( $term_id, 'vendor' );

            switch ( $column_name ) {
                case 'registration_date':
                    return $vendor->get_registration_date( 'display' );
                    break;

                case 'owner':
                    $owner      = get_user_by( 'id', $vendor->get_owner() );
                    $edit_link  = esc_url( get_edit_term_link( $term_id, $this->_taxonomy_name, 'product' ) );
                    return  $owner instanceof WP_User ? sprintf( '<a href="%s" target="_blank">%s</a>', get_edit_user_link( $owner->ID ), $owner->display_name ) :sprintf( '<a href="%s" class="set-an-owner">%s</a>',  $edit_link, __( 'Set an owner', 'yith-woocommerce-product-vendors' ) );
                    break;

                case 'commission_rate':
                    return $vendor->get_commission() * 100 . ' %';
                    break;

                case 'enable_sales':
                    $shop_owner = $vendor->get_owner();
                    $sales      = 'yes' == $vendor->enable_selling ? 'enabled' : 'disabled';
                    $pending    = 'yes' == $vendor->pending ? 'pending' : '';
                    $owner      = ! empty( $shop_owner ) ? '' : 'no-owner';
                    $return     = '';

                    if( ! empty( $pending ) ){
                        $return = $pending;
                    }

                    else {
                        $return = ! empty( $shop_owner ) ? $sales : $owner;
                    }

                    return sprintf( '<mark class="%1$s">%1$s</mark>', $return );
                    break;

                case 'vat':
                    return sprintf( '<mark class="%1$s">%1$s</mark>', empty( $vendor->vat ) ? 'no-vat' : 'vat' );
                    break;

                case 'user_actions':
                    $edit_link = esc_url( get_edit_term_link( $term_id, $this->_taxonomy_name, 'product' ) );
                    return sprintf( '<a href="%s" class="button tips edit_extra_info">%s</a>', $edit_link, __( 'Edit extra info', 'yith-woocommerce-product-vendors' ) );
                    break;

                case 'products':
                    $args = array(
                        'post_type' => 'product',
                        'taxonomy'  => YITH_Vendors()->get_taxonomy_name(),
                        'term'      => $vendor->slug
                    );
                    $count = number_format_i18n( count( $vendor->get_products() ) );
                    return "<a href='" . esc_url ( add_query_arg( $args, 'edit.php' ) ) . "'>$count</a>";
                    break;
            }
        }

        /**
         * Add sortable columns
         *
         * @Author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.0.0
         *
         * @param $sortable_columns The columns
         *
         * @return array The sortable columns
         * @use manage_{$this->screen->id}_sortable_columns filter
         */
        public function sortable_columns( $sortable_columns ) {
            return array_merge( $sortable_columns, array( 'commission_rate' => 'commission_rate', 'owner' => 'owner' ) );
        }

        /**
         * Add sortable columns
         *
         * @Author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since    1.0.0
         *
         * @param $actions  The actions array
         * @param $tag      The tag object
         *
         * @return array The sortable columns
         * @use {$taxonomy}_row_actions filter
         */
        public function tag_row_actions( $actions, $tag ) {
            unset( $actions['inline hide-if-no-js'] );

            $vendor = yith_get_vendor( $tag->term_id );

            if ( 'yes' == $vendor->pending ) {
                unset( $actions['view'] );
                $actions['switch_pending_status'] = "<a class='disable-tag' href='" . wp_nonce_url( "edit-tags.php?post_type=product&action=switch-pending-status&amp;taxonomy=$this->_taxonomy_name&amp;vendor_id=$tag->term_id", 'switch-pending-status_' . $tag->term_id ) . "'>" . __( 'Approve', 'yith-woocommerce-product-vendors' ) . "</a>";
            }

            else {
                if ( 'no' == $vendor->enable_selling ) {
                    unset( $actions['view'] );
                    $actions['switch_selling_capabilities'] = "<a class='disable-tag' href='" . wp_nonce_url( "edit-tags.php?post_type=product&action=switch-selling-capability&amp;taxonomy=$this->_taxonomy_name&amp;vendor_id=$tag->term_id", 'switch-selling-capability_' . $tag->term_id ) . "'>" . __( 'Enable sales', 'yith-woocommerce-product-vendors' ) . "</a>";
                }
                else {
                    $actions['switch_selling_capabilities'] = "<a class='disable-tag' href='" . wp_nonce_url( "edit-tags.php?post_type=product&action=switch-selling-capability&amp;taxonomy=$this->_taxonomy_name&amp;vendor_id=$tag->term_id", 'switch-selling-capability_' . $tag->term_id ) . "'>" . __( 'Disable sales', 'yith-woocommerce-product-vendors' ) . "</a>";
                }
            }

            return $actions;
        }

        /**
         * Change selling capability -> Table row actions
         *
         * @Author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since    1.0.0
         *
         * @return void
         * @use admin_action_switch-selling-capability action
         */
        public function switch_selling_capability( $vendor_id = 0, $direct_call = false, $switch_to = false ) {
            if ( $direct_call || ! empty( $_GET['action'] ) && 'switch-selling-capability' == $_GET['action'] ) {
                $vendor_id = empty( $vendor_id ) ? $_GET['vendor_id'] : $vendor_id;
                $vendor    = yith_get_vendor( $vendor_id );

                if ( $switch_to ) {
                    $vendor->enable_selling = $switch_to;
                }

                else {
                    $enable_selling         = $vendor->enable_selling;
                    $vendor->enable_selling = 'yes' == $enable_selling ? 'no' : 'yes';
                }

                if ( ! $direct_call ) {
                    wp_redirect( esc_url_raw( remove_query_arg( array( 'action', 'vendor_id', '_wpnonce' ) ) ) );
                    exit;
                }
            }
        }

        /**
         * Change Pending Status -> Table row actions
         *
         * @Author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since    1.2
         *
         * @return void
         * @use admin_action_switch-selling-capability action
         */
        public function switch_pending_status( $vendor_id = 0, $direct_call = false ) {
            $check = $direct_call ? $direct_call : ! empty( $_GET['action'] ) && 'switch-pending-status' == $_GET['action'];
            if ( $check ) {
                $vendor_id = $direct_call ? $vendor_id : $_GET['vendor_id'];
                $vendor    = yith_get_vendor( $vendor_id );
                if ( 'yes' == $vendor->pending ) {
                    $vendor->enable_selling = 'yes';
                    YITH_Vendors()->delete_term_meta( $vendor->id, 'pending' );

                    /* Send Email notification to New vendor */
                    do_action( 'yith_vendors_account_approved', $vendor->get_owner() );
                }

                if ( ! $direct_call ) {
                    $redirect = remove_query_arg( array( 'action', 'vendor_id', '_wpnonce' ) );
                    $paged    = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
                    wp_redirect( esc_url_raw( add_query_arg( array( 'paged' => $paged ), $redirect ) ) );
                    exit;
                }
            }
        }

        /**
         * Get vendor admin template
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0.0
         * @return void
         */
        public function admin_settings_page() {

            $template       = '';
            $current_action = current_action();
            $vendor         = yith_get_vendor( 'current', 'user' );
            $args           = array();

            switch ( $current_action ) {
                case 'yith_wpv_edit_vendor_settings':
                    $template = 'vendor-admin-settings';
                    $owner    = get_userdata( $vendor->get_owner() );
                    $args = array(
                        'vendor_admins' => array(
                            'selected'  => $this->format_vendor_admins_for_select2( $vendor ),
                            'value'     => implode( ',', array_diff( $vendor->get_admins(), array( $owner->ID ) ) )
                        ),
                        'vendor_can_add_admins' => apply_filters( 'yith_wcmv_vendor_can_add_admins', 'yes' == get_option( 'yith_wpv_vendors_ahop_admins_cap', 'no' ) ? true : false )
                    );
                    break;

                case 'yith_wpv_edit_payments':
                    $template = 'vendor-admin-payments';
                    $args     = array(
                        'currency_symbol' => get_woocommerce_currency_symbol(),
                        'step'            => 'any',
                        'min'             => get_option( 'payment_minimum_withdrawals', 1 ),
                        'payments_type'   => array(
                            'instant'   => __( 'Instant Payment', 'yith-woocommerce-product-vendors' ),
                            'threshold' => __( 'Payment threshold', 'yith-woocommerce-product-vendors' )
                        )
                    );
                    break;

                case 'yith_wpv_edit_frontpage':
                    $template               = 'vendor-admin-frontpage';
                    $args                   = YITH_Vendors()->get_social_fields();
                    $args['show_gravatar']  = 'enabled' == get_option( 'yith_vendors_show_gravatar_image', 'enabled' ) ? true : false;
                    break;

                case 'yith_wpv_edit_vendor_vacation':
                    $template   = 'vendor-admin-vacation';
                    $args       = '';
                    break;
            }

            if( empty( $template ) ){
                return false;
            }

            // add vendor to args
            $args['vendor'] = $vendor;

            yith_wcpv_get_template( $template, $args, 'admin/vendor-panel' );
        }

        /**
         * Add the social fields to array args
         *
         * @param $args The edit taxonomy template args
         *
         * @since 1.0
         * @return array
         */
        public function edit_taxonomy_args( $args ) {
            return array_merge( $args, YITH_Vendors()->get_social_fields() );
        }

        /**
         * Check if vendor can publish product without admin approve.
         *
         * @param $vendor YITH_Vendor object
         *
         * @param $value  The post value to save
         *
         * @return   void
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @since    1.0
         */
        public function check_skip_review_cap( $vendor, $value ) {

            if ( $vendor->has_limited_access() || ! isset( $value['skip_review'] ) ) {
                return;
            }

            $method = 'yes' == $value['skip_review'] ? 'add_cap' : 'remove_cap';
            foreach ( $value['admins'] as $admin_id ) {
                $user = get_user_by( 'id', $admin_id );
                if( $user instanceof WP_User ){
                    $user->$method( 'publish_products' );
                }
            }
        }

        /**
         * Check if vendor can publish product without admin approve.
         *
         * @param $checkboxes Array The checkboxex array
         *
         * @return   Array
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @since    1.0
         */
        public function save_empty_checkboxes( $checkboxes, $has_limited_access ) {
            if( $has_limited_access ){
                /* Vendor checkbox options */
                $checkboxes[] = 'show_gravatar';
            }

            else {
                /* Website owner checkbox options */
                $checkboxes[] = 'skip_review';
                $checkboxes[] = 'featured_products';
            }
            return $checkboxes;
        }

        /**
         * Admin enqueue scripts
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         * @return void
         */
        public function enqueue_scripts() {
            parent::enqueue_scripts();
            global $pagenow;

            wp_register_script( 'yith-wpv-datepicker', YITH_WPV_ASSETS_URL . 'js/init.datepicker.js', array( 'jquery', 'jquery-ui-datepicker' ), YITH_Vendors()->version, true );
            $localize = array(
                'forceSkipMessage' => __( 'Are you sure? If you click "YES" you change skip review option for each vendor', 'yith-woocommerce-product-vendors' ),
                'warnPay'          => __( 'If you continue, the commission will be paid automatically to the vendor via PayPal. Do you want to continue?', 'yith-woocommerce-product-vendors' ),
                'approve'          => __( 'Approve', 'yith-woocommerce-product-vendors' ),
                'enable_sales'     => __( 'Enable sales', 'yith-woocommerce-product-vendors' ),
                'disable_sales'    => __( 'Disable sales', 'yith-woocommerce-product-vendors' ),
            );
            wp_localize_script( 'yith-wpv-admin', 'yith_vendors', $localize );

            /* Vendor Vacation Module */
            if( 'admin.php' == $pagenow && ! empty( $_GET['page'] ) && 'yith_vendor_settings' == $_GET['page'] && ! empty( $_GET['tab'] ) && 'vendor-vacation' == $_GET['tab'] ){
                wp_enqueue_script( 'yith-wpv-datepicker' );
            }

            /* Remove Customer in order details */
            elseif( YITH_Vendors()->orders->is_vendor_order_details_page() ){
                $style = $script = '';
                $enqueue = false;
                if( 'yes' == get_option( 'yith_wpv_vendors_option_order_hide_customer', 'no' ) ){
                    $style  .= '#order_data .wc-customer-user, .widefat .column-order_title small.meta.email {display:none;}';
                    $script .= "jQuery('#order_data').find('.wc-customer-user').remove();";
                    $enqueue = true;
                    add_action( 'woocommerce_admin_order_data_after_order_details', array( YITH_Vendors()->orders, 'hide_customer_info' ), 10, 1 );
                }

                if( 'yes' == get_option( 'yith_wpv_vendors_option_order_hide_payment', 'no' ) ){
                    $style  .= '#order_data .order_number{display:none;}';
                    $script .= "jQuery('#order_data').find('.order_number').remove();";
                    $enqueue = true;
                }

                if( $enqueue ){
                    wp_add_inline_style( 'yith-wc-product-vendors-admin', $style );
                    wc_enqueue_js( $script );
                }
            }

            elseif( YITH_Vendors()->orders->is_vendor_order_page() ){
                $style = $script = '';
                $enqueue = false;

                if( 'yes' == get_option( 'yith_wpv_vendors_option_order_hide_customer', 'no' ) ){
                    $style  .= '.wc-customer-search {display:none;}';
                    $script .= "jQuery('.wc-customer-search').remove();";
                    $enqueue = true;
                }

                if( $enqueue ){
                    wp_add_inline_style( 'yith-wc-product-vendors-admin', $style );
                    ! empty( $script ) && wc_enqueue_js( $script );
                }
            }

            if( $this->is_vendor_tax_page() && 'yes' == get_option( 'yith_wpv_vendors_option_editor_management', 'no' ) ){
                $style = '.wp-editor-container textarea.wp-editor-area{border-color: #e5e5e5;} tr.form-field.term-description-wrap, textarea#description{display: none;}';
                wp_add_inline_style( 'yith-wc-product-vendors-admin', $style );
            }

            //Check for featured products managemnet
            if( 'no' == get_option( 'yith_wpv_vendors_option_featured_management', 'no' ) && 'post-new.php' == $pagenow && ! empty( $_GET['post_type'] ) && 'product' == $_GET['post_type'] ){
                $vendor = yith_get_vendor( 'current', 'user' );
                if( $vendor->is_valid() && $vendor->has_limited_access() ){
                    $remove_featured_management = "var featured = jQuery('#_featured'); featured.add( featured.prev('p') ).add( featured.next('label') ).remove();";
                    wc_enqueue_js( $remove_featured_management );
                }
            }
        }

        /**
         * Delete wishlist on bulk action
         *
         * @since 1.0.0
         */
        public function process_bulk_action() {
            // Detect when a bulk action is being triggered...
            if ( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] != YITH_Commissions()->get_screen() || ! isset( $_REQUEST['action'] ) || ! isset( $_REQUEST['commissions'] ) ) {
                return;
            }

            $vendor = yith_get_vendor( 'current', 'user' );

            if ( ! $vendor->is_super_user() ) {
                return;
            }

            $action      = ( $_REQUEST['action'] != - 1 ) ? $_REQUEST['action'] : $_REQUEST['action2'];
            $commissions = $_REQUEST['commissions'];
            $message     = 'updated';

            // change status action
            if ( in_array( $action, array_keys( YITH_Commissions()->get_status() ) ) ) {
                foreach ( $commissions as $commission_id ) {
                    YITH_Commission( $commission_id )->update_status( $action );
                }
            }

            // pay paction
            else {
                if ( 'pay' == $action ) {
                    $data = array();
                    foreach ( $commissions as $commission_id ) {
                        $commission = YITH_Commission( $commission_id );
                        $vendor     = $commission->get_vendor();
                        $data[]     = array(
                            'paypal_email' => $vendor->paypal_email,
                            'amount'       => round( $commission->get_amount(), 2 ),
                            'request_id'   => $commission->id
                        );
                    }

                    // process payment
                    $result = YITH_Vendors_Credit()->pay( $data );

                    foreach ( $commissions as $commission_id ) {
                        /** @var YITH_Commission $commission */
                        $commission = YITH_Commission( $commission_id );

                        // set as processing, because paypal will set as paid as soon as the transaction is completed
                        if ( $result['status'] ) {
                            $message = 'pay-process';
                            $commission->update_status( 'processing' );
                        }

                        // save the error in the note
                        else {
                            $message = 'pay-failed';
                            $commission->add_note( sprintf( __( 'Payment failed: %s', 'yith-woocommerce-product-vendors' ), $result['messages'] ) );
                        }
                    }
                }

                else {
                    return;
                }
            }

            wp_redirect( esc_url_raw( add_query_arg( 'message', $message, wp_get_referer() ) ) );
            exit();
        }

        /**
         * Premium panel options
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         *
         * @param $options  array The original options array
         * @param $tab      string The tab
         *
         * @return array The new options array
         */
        public function add_panel_premium_options( $options, $tab ) {
            $premium_options = include( $this->_panel->settings['options-path'] . '/' . $tab . '-options-premium.php' );

            $premium_options[$tab][$tab . '_options_end'] = $options[$tab][$tab . '_options_end'];
            unset( $options[$tab][$tab . '_options_end'] );
            $new_section[$tab] = array();

            if ( 'yith_wpv_panel_vendors_options' == current_filter() ) {
                $to_unsets = array( 'vendors_color_name', 'vendors_order_start', 'vendors_order_title', 'vendors_order_management', 'vendors_order_synchronization', 'vendors_order_end' );

                foreach( $to_unsets as $to_unset ){
                    unset( $options[$tab][$to_unset] );
                }

                $new_section[$tab] = $premium_options[$tab]['new_section_options'];
                unset( $premium_options[$tab]['new_section_options'] );
            }

            $new_options[$tab] = array_merge( $options[$tab], $premium_options[$tab], $new_section[$tab] );
            return $new_options;
        }

        /**
         * Add the custom typoe option "button"
         *
         * @param $value The field value
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         * @return void
         */
        public function admin_field_button( $value ) {
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
                </th>
                <td class="forminp">
                    <input type="button" name="force_review" id="<?php echo $value['id'] ?>" value="<?php echo $value['name'] ?>" class="button-secondary" />
                    <span class="description with-spinner">
                        <?php echo $value['desc']; ?>
                    </span>
                    <span class="spinner"></span>
                </td>
            </tr>
        <?php
        }

        /**
         * Add columns filters to commissions page
         *
         * @param $columns The columns
         *
         * @author Andrea Grillo <andrea.grillo@yitheme.com>
         * @return array The columns array to print
         * @since  1.0
         */
        public function add_commissions_screen_options( $columns ) {
            return $columns = array(
                'commission_id'     => __( 'ID', 'yith-woocommerce-product-vendors' ),
                'commission_status' => __( 'Status', 'yith-woocommerce-product-vendors' ),
                'order_id'          => __( 'Order', 'yith-woocommerce-product-vendors' ),
                'line_item'         => __( 'Product', 'yith-woocommerce-product-vendors' ),
                'rate'              => __( 'Rate', 'yith-woocommerce-product-vendors' ),
                'user'              => __( 'User', 'yith-woocommerce-product-vendors' ),
                'vendor'            => YITH_Vendors()->get_vendors_taxonomy_label( 'singular_name' ),
                'amount'            => __( 'Amount', 'yith-woocommerce-product-vendors' ),
                'date'              => __( 'Date', 'yith-woocommerce-product-vendors' ),
                'date_edit'         => __( 'Last update', 'yith-woocommerce-product-vendors' ),
                'user_actions'      => __( 'Actions', 'yith-woocommerce-product-vendors' ),
            );
        }

        /**
         * Search for products and echo json
         *
         * @param string $x          (default: '')
         * @param string $post_types (default: array('product'))
         */
        public static function json_search_vendors( $x = '', $post_types = array( 'product' ) ) {
            ob_start();

            $term = (string) wc_clean( stripslashes( $_GET['term'] ) );

            if ( empty( $term ) ) {
                die();
            }

            check_ajax_referer( 'search-products', 'security' );

            $args = array(
                'orderby' => 'name',
                'order'   => 'ASC',
                'fields'  => 'all',
                'search'  => $term,
            );

            $vendors_obj = get_terms( YITH_Vendors()->get_taxonomy_name(), $args );
            $vendors     = array();

            foreach ( $vendors_obj as $vendor ) {
                $vendors[$vendor->term_id] = $vendor->name;
            }

            $vendors = apply_filters( 'yith_wpv_json_search_found_vendors', $vendors );

            wp_send_json( $vendors );
        }

        /**
         * Add commission tab in single product "Product Data" section
         */
        public function single_product_commission_tab( $product_data_tabs ) {
            if ( current_user_can( 'manage_woocommerce' ) ) {
                $product_data_tabs['commissions'] = array(
                    'label'  => __( 'Commission', 'woocommerce' ),
                    'target' => 'yith_wpv_single_commission',
                    'class'  => array(),
                );
            }

            return $product_data_tabs;
        }

        /**
         * Add commission tab in single product "Product Data" section
         */
        public function single_product_commission_content() {
            if ( current_user_can( 'manage_woocommerce' ) ) {
                global $post;
                $meta_value = get_post_meta( $post->ID, '_product_commission', true );

                $args = apply_filters( 'yith_wcmv_product_commission_field_args', array(
                        'field_args' => array(
                            'id'                => 'yith_wpv_product_commission',
                            'label'             => __( 'Product commission', 'yith-woocommerce-product-vendors' ),
                            'desc_tip'          => 'true',
                            'description'       => __( 'You can set a specific commission for a single product. Keep this field blank or zero to use the vendor commission', 'yith-woocommerce-product-vendors' ),
                            'value'             => $meta_value ? $meta_value : '',
                            'type'              => 'number',
                            'custom_attributes' => array(
                                'step' => 0.1,
                                'min'  => 0,
                                'max'  => 100
                            )
                        )
                    )
                );

                yith_wcpv_get_template( 'product-data-commission', $args, 'woocommerce/admin' );
            }
        }

        /**
         * Save product commission rate meta
         *
         * @param $post_id  The post id
         * @param $post     The post object
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0
         * @return void
         */
        public function save_product_commission_meta( $post_id, $post ) {
            // Save Product Commission Rate
            if ( ! empty( $_POST['yith_wpv_product_commission'] ) ) {
                update_post_meta( $post_id, '_product_commission', $_POST['yith_wpv_product_commission'] );
            }
            else {
                delete_post_meta( $post_id, '_product_commission' );
            }
        }

        /**
         * Register plugins for activation tab
         *
         * @return void
         * @since    2.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_activation() {
            if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
                require_once YITH_WPV_PATH . 'plugin-fw/licence/lib/yit-licence.php';
                require_once YITH_WPV_PATH . 'plugin-fw/licence/lib/yit-plugin-licence.php';
            }
            YIT_Plugin_Licence()->register( YITH_WPV_INIT, YITH_WPV_SECRET_KEY, YITH_WPV_SLUG );
        }

        /**
         * Register plugins for update tab
         *
         * @return void
         * @since    2.0.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function register_plugin_for_updates() {
            if ( ! class_exists( 'YIT_Upgrade' ) ) {
                require_once( YITH_WPV_PATH . 'plugin-fw/lib/yit-upgrade.php' );
            }
            YIT_Upgrade()->register( YITH_WPV_SLUG, YITH_WPV_INIT );
        }

        /**
         * Allowed WooCommerce Post Types
         *
         * @return void
         * @since    1.2.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function allowed_wc_post_types() {
            global $post_type;
            $_screen       = get_current_screen();

            if ( 'shop_coupon' == $post_type || 'edit-product' == $_screen->id || 'edit-shop_order' == $_screen->id ) {
                return;
            }

            $vendor = yith_get_vendor( 'current', 'user' );

            $allowed_post_types = apply_filters('yith_wpv_vendors_allowed_post_types', array(
                'product',
                'shop_coupon',
            ));

            if ( $vendor->is_valid() && $vendor->has_limited_access() ) {

                $post_types_check = in_array( $post_type, $allowed_post_types );

                if ( ( 'admin_head-edit.php' == current_action() && ! $post_types_check ) || ( $_screen->id == 'shop_order' && $_screen->action == 'add' ) ){
                    wp_die( sprintf( __( 'You do not have sufficient permissions to access this page. %1$sClick here to return in your dashboard%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( admin_url() ) . '">', '</a>' ) );
                }

                $enable_amount  = 'yes' == get_option( 'yith_wpv_enable_product_amount' ) ? true : false;
                if( $enable_amount && 'product' == $post_type ){
                    $products_limit = get_option( 'yith_wpv_vendors_product_limit', 25 );
                    $products_count = count( $vendor->get_products( array( 'post_status' => 'any' ) ) );
                    $enabled        = $post_type === 'product' && $vendor->is_valid() && $vendor->has_limited_access() && $products_limit > $products_count;

                    if ( ! $enabled ) {
                        wp_die( sprintf( __( 'You are not allowed to create more than %1$s products. %2$sClick here to return in your admin area%3$s.', 'yith-woocommerce-product-vendors' ), $products_limit, '<a href="' . esc_url( 'edit.php?post_type=product' ) . '">', '</a>' ) );
                    }
                }
            }
        }

        /**
         * Allow/Disable WooCommerce add new post type creation
         *
         * @return void
         * @since    1.2.0
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function add_new_link_check() {
            global $typenow;

            //Check if post types is product or shop order
            if( 'product' != $typenow && 'shop_order' != $typenow ){
                return;
            }

            //If product check for enable amount option
            $enable_amount  = '';
            if( 'product' == $typenow ){
                $enable_amount = 'yes' == get_option( 'yith_wpv_enable_product_amount' ) ? true : false;
                if ( ! $enable_amount ) {
                    return;
                }
            }

            $vendor = yith_get_vendor( 'current', 'user' );

            if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
                global $submenu;

                if( 'product' == $typenow ){
                    $products_limit = get_option( 'yith_wpv_vendors_product_limit', 25 );
                    $products_count = count( $vendor->get_products( array( 'post_status' => 'any' ) ) );

                    if ( $products_limit > $products_count ) {
                        return;
                    }
                }

                foreach ( $submenu as $section => $menu ) {
                    foreach ( $menu as $position => $args ) {
                        $submenu_url = "post-new.php?post_type={$typenow}";
                        if ( in_array( $submenu_url, $args ) ) {
                            $submenu[$section][$position][4] = 'yith-wcpv-hide-submenu-item';
                            break;
                        }
                    }
                }
                add_action( 'admin_enqueue_scripts', array( $this, 'remove_add_new_button' ), 20 );
            }
        }

        /**
         * If an user is a vendor admin remove the woocommerce prevent admin access
         *
         * @Author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.2
         * @return bool
         * @use woocommerce_prevent_admin_access hooks
         */
        public function prevent_admin_access( $prevent_access ) {
            global $current_user;
            $vendor = yith_get_vendor( 'current', 'user' );
            $is_valid = $vendor->is_valid();
            if( $is_valid && $vendor->has_limited_access() && $vendor->is_user_admin() ) {
                $prevent_access = $vendor->pending ? true : false;
            }

            elseif( ! $is_valid && in_array( YITH_Vendors()->get_role_name(), $current_user->roles ) ) {
                $prevent_access = true;
            }

            return $prevent_access;
        }

        /**
         * Manage vendor taxonomy bulk actions
         *
         * @Author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.2
         * @return void
         */
        public function vendor_bulk_action() {
            $wp_list_table   = _get_list_table( 'WP_Terms_List_Table' );
            $action          = $wp_list_table->current_action();
            $redirect        = false;
            $action_redirect = array( 'approve', 'enable_sales', 'disable_sales' );

            if ( ! empty( $action ) ) {
                // delete-tags: wp not allowed to add new bulk actions -> jquery hack
                $vendor_ids = ! empty( $_POST['delete_tags'] ) ? $_POST['delete_tags'] : false;
                if ( $vendor_ids ) {
                    foreach ( $vendor_ids as $k => $vendor_id ) {
                        if ( 'approve' == $action ) {
                            $this->switch_pending_status( $vendor_id, true );
                        }

                        if ( 'enable_sales' == $action ) {
                            $this->switch_selling_capability( $vendor_id, true, 'yes' );
                        }

                        if ( 'disable_sales' == $action ) {
                            $this->switch_selling_capability( $vendor_id, true, 'no' );
                        }
                    }
                }

                if ( in_array( $action, $action_redirect ) ) {
                    $redirect = remove_query_arg( array( 'action', 'vendor_id', '_wpnonce' ) );
                    $paged    = isset( $_GET['paged'] ) ? $_GET['paged'] : 1;
                    $redirect = esc_url_raw( add_query_arg( array( 'paged' => $paged ), $redirect ) );
                }
            }

            if ( $redirect ) {
                wp_redirect( $redirect );
                exit;
            }
        }

        /**
         * Restrict vendors from editing other vendors' posts
         *
         * @author      Andrea Grillo <andrea.grillo@yithemes.com>
         * @return      void
         * @since       1.3
         * @use         current_screen filter
         */
        public function disabled_manage_other_vendors_posts() {
            global $typenow;
            $vendor    = yith_get_vendor( 'current', 'user' );
            $is_seller = $vendor->is_valid() && $vendor->has_limited_access();

            if ( $is_seller && ! empty( $typenow ) && apply_filters( 'yith_wcmv_disable_post', 'post' == $typenow ) ) {
                wp_die( sprintf( __( 'You do not have permission to edit this post. %1$sClick here to view your dashboard%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( admin_url() ) . '">', '</a>' ) );
            }

            if ( isset( $_POST['post_ID'] ) || ! isset( $_GET['post'] ) ) {
                return;
            }

            /* WPML Support */
            $default_language = function_exists( 'wpml_get_default_language' ) ? wpml_get_default_language() : null;
            $post_id =  yit_wpml_object_id(  $_GET['post'], 'product', true, $default_language );
            $product_vendor = yith_get_vendor( $post_id, 'product' ); // If false, the product hasn't any vendor set
            $post           = get_post( $_GET['post'] );

            if ( $is_seller ) {

                if (
                    'product' == $post->post_type
                    &&
                    false !== $product_vendor
                    &&
                    $vendor->id != $product_vendor->id
                ) {
                    wp_die( sprintf( __( 'You do not have permission to edit this product. %1$sClick here to view and edit your products%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit.php?post_type=product' ) . '">', '</a>' ) );
                }

                else if (
                    'shop_coupon' == $post->post_type
                    &&
                    ! in_array( $post->post_author, $vendor->admins )
                ) {
                    wp_die( sprintf( __( 'You do not have permission to edit this coupon. %1$sClick here to view and edit your coupons%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit.php?post_type=shop_coupon' ) . '">', '</a>' ) );
                }

                else if( 'shop_order' == $post->post_type
                    &&
                    YITH_Vendors()->addons->has_plugin( 'request-quote' )
                    &&
                    'no' == get_option( 'yith_wpv_vendors_enable_request_quote', 'no' )
                    &&
                    in_array( $post->post_status, YITH_YWRAQ_Order_Request()->raq_order_status )
                ){
                    wp_die( sprintf( __( 'You do not have permission to edit this order. %1$sClick here to view your orders%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit.php?post_type=shop_order' ) . '">', '</a>' ) );
                }
            }
        }

        /**
         * Remove Posts From WP Menu Dashboard
         *
         * @author      Andrea Grillo <andrea.grillo@yithemes.com>
         * @return      void
         * @since       1.3
         * @use         admin_menu filter
         */
        public function remove_posts_page() {
            $vendor = yith_get_vendor( 'current', 'user' );

            if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
                global $menu;

                $to_remove = apply_filters( 'yith_wpv_vendor_to_remove_menu_items', array( 'edit.php', 'tools.php' ) );

                foreach ( $to_remove as $page ) {
                    remove_menu_page( $page );
                }

                $to_add = apply_filters( 'yith_wpv_vendor_menu_items', array(
                        'index.php',
                        'separator1',
                        'edit.php?post_type=product',
                        'edit.php?post_type=shop_coupon',
                        'edit.php?post_type=shop_order',
                        'profile.php',
                        'separator-last',
                        'yith_vendor_commissions',
                        'upload.php'
                    )
                );

                if( current_user_can( 'moderate_comments' ) ){
                    $to_add[] = 'edit-comments.php';
                }

                foreach ( $menu as $page ) {
                    if ( ! in_array( $page[2], $to_add ) ) {
                        remove_menu_page( $page[2] );
                    }
                }
            }
        }

        /**
         * filter product reviews
         *
         * @param $query object The WP_Comment_Query object
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.0.0
         * @fire product_vendors_details_fields_save action
         */
        public function filter_reviews_list( $query ) {

            $current_screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

            if( ! empty( $current_screen ) && 'edit-comments' != $current_screen->id ){
                return;
            }

            $vendor = yith_get_vendor( 'current', 'user' );

            if ( $vendor->is_valid() && $vendor->has_limited_access() )  {

                $vendor_products = $vendor->get_products();
                /**
                 * If vendor haven't products there isn't comment to show with array(0) the query will abort.
                 * Another way to do this is to use the_comments hook: add_filter( 'the_comments', '__return_empty_array' );
                 */
                $query->query_vars['post__in'] = ! empty( $vendor_products ) ? $vendor_products : array(0);
            }
        }

        /**
         * Disable to mange other vendor options
         *
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since 1.6
         * @return void
         */
        public function disabled_manage_other_comments(){
            $vendor = yith_get_vendor( 'current', 'user' );
            if( 'load-comment.php' == current_action() && $vendor->is_valid() && $vendor->has_limited_access() && ! empty( $_GET['action'] ) && 'editcomment' == $_GET['action'] ) {
                $comment = get_comment( $_GET['c'] );
                if( ! in_array( $comment->comment_post_ID, $vendor->get_products() ) ){
                    wp_die( sprintf( __( 'You do not have permission to edit this review. %1$sClick here to view and edit your product reviews%2$s.', 'yith-woocommerce-product-vendors' ), '<a href="' . esc_url( 'edit-comments.php' ) . '">', '</a>' ) );
                }
            }
        }

        /**
         * filter product reviews
         *
         * @param $stats    The comment stats
         * @param $post_id  The post di
         *
         * @return bool|mixed|object
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         * @since  1.3
         */
        public function count_comments( $stats, $post_id ) {
            $vendor = yith_get_vendor( 'current', 'user' );

            if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
                remove_filter( 'wp_count_comments', array( 'WC_Comments', 'wp_count_comments' ), 10, 2 );

                global $wpdb;

                if ( 0 === $post_id ) {

                    $count = wp_cache_get( 'comments-0', 'counts' );
                    if ( false !== $count ) {
                        return $count;
                    }

                    $sql = sprintf( "
                         SELECT comment_approved, COUNT( * ) AS num_comments
                         FROM {$wpdb->comments}
                         WHERE comment_type != '%s'
                         AND comment_post_ID in ( '%s' )
                         GROUP BY comment_approved", 'order_note', implode( "','", $vendor->get_products() ) );

                    $count = $wpdb->get_results( $sql, ARRAY_A );

                    $total    = 0;
                    $approved = array( '0' => 'moderated', '1' => 'approved', 'spam' => 'spam', 'trash' => 'trash', 'post-trashed' => 'post-trashed' );

                    foreach ( (array) $count as $row ) {
                        // Don't count post-trashed toward totals
                        if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] ) {
                            $total += $row['num_comments'];
                        }
                        if ( isset( $approved[$row['comment_approved']] ) ) {
                            $stats[$approved[$row['comment_approved']]] = $row['num_comments'];
                        }
                    }

                    $stats['total_comments'] = $total;
                    foreach ( $approved as $key ) {
                        if ( empty( $stats[$key] ) ) {
                            $stats[$key] = 0;
                        }
                    }

                    $stats = (object) $stats;
                    wp_cache_set( 'comments-0', $stats, 'counts' );
                }
            }
            return $stats;
        }

        /**
         * Check for reviews, coupons and order capabilities
         *
         * Add or remove vendor capabilities for coupon and review management
         *
         * @return array
         * @since  1.3
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function manage_premium_caps( $caps ) {
            $premium_caps = array(
                'coupon'     => array(
                    'edit_shop_coupons'             => true,
                    'read_shop_coupons'             => true,
                    'delete_shop_coupons'           => true,
                    'publish_shop_coupons'          => true,
                    'edit_published_shop_coupons'   => true,
                    'delete_published_shop_coupons' => true,
                    'edit_others_shop_coupons'      => true,
                    'delete_others_shop_coupons'    => true,
                ),

                'review'     => array(
                    'moderate_comments' => true,
                ),

                /* === Support to external plugins === */
                'live_chat'   => apply_filters( 'yith_wcmv_live_chat_caps', array() ),

                'surveys' => apply_filters( 'yith_wcmv_surveys_caps', array() ),
            );
            
            return apply_filters( 'yith_wcmv_premium_caps', array_merge( $caps, $premium_caps ) );
        }

        /**
         * Add vendor widget dashboard
         *
         * @return void
         * @since  1.3
         * @author Andrea Grillo <andrea.grillo@yithemes.com<
         */
        public function add_dashboard_widgets() {
            $vendor = yith_get_vendor( 'current', 'user' );

            if ( $vendor->is_valid() && $vendor->has_limited_access() ) {

                $review_management = 'yes' == get_option( 'yith_wpv_vendors_option_review_management' ) ? true : false;

                $to_adds = array(
                    array(
                        'id'       => 'woocommerce_dashboard_recent_reviews',
                        'name'     => __( 'Recent reviews', 'yith-woocommerce-product-vendors' ),
                        'callback' => array( $this, 'vendor_recent_reviews_widget' ),
                        'context'  => $review_management ? 'side' : 'normal'
                    )
                );

                if ( $review_management ) {
                    $to_adds[] = array(
                        'id'       => 'vendor_recent_reviews',
                        'name'     => __( 'Recent comments', 'yith-woocommerce-product-vendors' ),
                        'callback' => array( $this, 'vendor_recent_comments_widget' ),
                        'context'  => 'normal'
                    );
                }

                foreach ( $to_adds as $widget ) {
                    extract( $widget );
                    add_meta_box( $id, $name, $callback, 'dashboard', $context, 'high' );
                }
            }
        }

        /**
         * Vendor Recent Comments Widgets
         *
         * @since  1.3
         * @return void
         * @author andrea Grilo <andrea.grillo@yithemes.com>
         */
        public function vendor_recent_comments_widget() {
            echo '<div id="activity-widget">';

            // Select all comment types and filter out spam later for better query performance.
	        $comments        = array();
            $vendor          = yith_get_vendor( 'current', 'user' );
            $vendor_products = $vendor->is_valid() && $vendor->has_limited_access() ? $vendor->get_products() : array();
            $total_items     = apply_filters( 'vendor_recent_comments_widget_items', 5 );
            $comments_query  = array(
                'number' => $total_items * 5,
                'offset' => 0,
                'post__in' => ! empty( $vendor_products ) ? $vendor_products : array(0)
            );
            if ( ! current_user_can( 'edit_posts' ) ) {
                $comments_query['status'] = 'approve';
            }

            while ( count( $comments ) < $total_items && $possible = get_comments( $comments_query ) ) {
                if ( ! is_array( $possible ) ) {
                    break;
                }
                foreach ( $possible as $comment ) {
                    if ( ! current_user_can( 'read_post', $comment->comment_post_ID ) ) {
                        continue;
                    }
                    $comments[] = $comment;
                    if ( count( $comments ) == $total_items ) {
                        break 2;
                    }
                }
                $comments_query['offset'] += $comments_query['number'];
                $comments_query['number'] = $total_items * 10;
            }

            if ( $comments ) {
                echo '<div id="latest-comments" class="activity-block">';
                echo '<h4>' . __( 'Comments', 'yith-woocommerce-product-vendors' ) . '</h4>';

                echo '<div id="the-comment-list" data-wp-lists="list:comment">';
                foreach ( $comments as $comment ) {
                    _wp_dashboard_recent_comments_row( $comment );
                }
                echo '</div>';

                if ( current_user_can( 'edit_posts' ) ) {
                    _get_list_table( 'WP_Comments_List_Table' )->views();
                }

                wp_comment_reply( - 1, false, 'dashboard', false );
                wp_comment_trashnotice();

                echo '</div>';
            }

            else {
                echo '<div class="no-activity">';
                echo '<p class="smiley"></p>';
                echo '<p>' . __( 'No activity yet!', 'yith-woocommerce-product-vendors' ) . '</p>';
                echo '</div>';
            }

            echo '</div>';
        }

        /**
         * Vendor Recent Reviews Widgets
         *
         * @since  1.3
         * @return void
         * @author andrea Grilo <andrea.grillo@yithemes.com>
         */
        public function vendor_recent_reviews_widget() {
            global $wpdb;
            $vendor = yith_get_vendor( 'current', 'user' );

            $comments = $wpdb->get_results( "
                SELECT *, SUBSTRING(comment_content,1,100) AS comment_excerpt
                FROM $wpdb->comments
                LEFT JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID)
                WHERE comment_approved = '1'
                AND comment_type = ''
                AND post_password = ''
                AND post_type = 'product'
                AND comment_post_ID IN ( '" . implode( "','", $vendor->get_products( array( 'fields' => 'ids' ) ) ) . "' )
                ORDER BY comment_date_gmt DESC
                LIMIT 8" );

            if ( $comments ) {
                echo '<ul>';
                foreach ( $comments as $comment ) {

                    echo '<li>';

                    echo get_avatar( $comment->comment_author, '32' );

                    $rating = get_comment_meta( $comment->comment_ID, 'rating', true );

                    echo '<div class="star-rating" title="' . esc_attr( $rating ) . '">
					<span style="width:' . ( $rating * 20 ) . '%">' . $rating . ' ' . __( 'out of 5', 'woocommerce' ) . '</span></div>';

                    echo '<h4 class="meta"><a href="' . get_permalink( $comment->ID ) . '#comment-' . absint( $comment->comment_ID ) . '">' . esc_html__( apply_filters( 'woocommerce_admin_dashboard_recent_reviews', $comment->post_title, $comment ) ) . '</a> ' . __( 'reviewed by', 'woocommerce' ) . ' ' . esc_html( $comment->comment_author ) . '</h4>';
                    echo '<blockquote>' . wp_kses_data( $comment->comment_excerpt ) . ' [...]</blockquote></li>';

                }
                echo '</ul>';
            }
            else {
                echo '<p>' . __( 'There are no product reviews yet.', 'woocommerce' ) . '</p>';
            }
        }

        /**
         * Set the vendor id for current coupon
         *
         * @since  1.3
         * @return void
         * @author andrea Grilo <andrea.grillo@yithemes.com>
         */
        public function add_vendor_to_coupon( $post_id ) {
            $vendor = yith_get_vendor( 'current', 'user' );
            if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
                update_post_meta( $post_id, 'vendor_id', $vendor->id );
            }
        }

        /**
         * Check for featured management
         *
         * Allowed or Disabled for vendor
         *
         * @since  1.3
         *
         * @param $columns The product column name
         *
         * @return void
         * @author andrea Grilo <andrea.grillo@yithemes.com>
         */
        public function render_product_columns( $columns ) {
            $vendor = yith_get_vendor( 'current', 'user' );
            if (
                $vendor->is_valid()
                &&
                $vendor->has_limited_access()
                &&
                ! empty( $_GET['post_type'] )
                &&
                'product' == $_GET['post_type']
                &&
                'no' == $vendor->featured_products_management()
            ) {
                unset( $columns['featured'] );
            }

            return $columns;
        }

        /**
         * Update the minimum withdrawals for each vendor
         * @since    1.3
         *
         * @param $value the new withdrawals
         *
         * @return void
         * @author   andrea Grilo <andrea.grillo@yithemes.com>
         */
        public function woocommerce_update_payment_option( $value ) {
            if ( 'payment_minimum_withdrawals' == $value['id'] ) {
                $vendors   = YITH_Vendors()->get_vendors();
                $threshold = absint( $_REQUEST['payment_minimum_withdrawals'] );
                foreach ( $vendors as $vendor ) {
                    if ( absint( $vendor->threshold ) < $threshold ) {
                        $vendor->threshold = $threshold;
                    }
                }
            }
        }

        /**
         * Add sold by information to product in order details
         *
         * The follow args are documented in woocommerce\templates\emails\email-order-items.php:37
         *
         * @param $item_id
         * @param $item
         * @param $_product
         *
         * @since    1.6
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         * @return  void
         * @use    woocommerce_before_order_itemmeta hook
         */
        public function add_sold_by_to_order( $item_id, $item, $_product ) {
            /** @var $theorder WC_Order  */
            global $theorder;
            $current           = yith_get_vendor( 'current', 'user' );
            $vendor_by_product = isset( $item['product_id'] ) ? yith_get_vendor( $item['product_id'], 'product' ) : false;

            if ( $vendor_by_product && $vendor_by_product->is_valid() && $current->id != $vendor_by_product->id ) {
                $vendor_uri = $vendor_by_product->get_url( 'admin' );
                echo ' (<small>' . apply_filters( 'yith_wcmv_sold_by_string_admin', _x( 'Sold by', 'Order details: Product sold by', 'yith-woocommerce-product-vendors' ) ) . ': ' . '<a href="' . $vendor_uri . '" target="_blank">' . $vendor_by_product->name . '</a></small>)';
            }
        }

        /**
         * Remove YIT Shortcodes button in YITH Themes
         *
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         *
         * @return  void
         * @since    1.6
         */
        public function remove_shortcodes_button(){
            if( function_exists( 'YIT_Shortcodes' ) ){
                $vendor = yith_get_vendor( 'current', 'user' );
                $disabled_yit_shortcodes = 'no' == get_option( 'yith_wpv_yit_shortcodes', 'no' ) ? true : false;
                if( $vendor->is_valid() && $vendor->has_limited_access() && $disabled_yit_shortcodes ){
                    remove_action( 'admin_init', array( YIT_Shortcodes(), 'add_shortcodes_button' ) );
                }
            }
        }

        /**
         * Check for vendor without owner
         *
         * @author   Andrea Grillo <andrea.grillo@yithemes.com>
         *
         * @return  void
         * @since    1.6
         */
        public function check_vendors_owner(){
            $vendor = yith_get_vendor( 'current', 'user' );

            if( $vendor->is_super_user() ){
                global $pagenow;
                $is_vendor_taxonomy_page = 'edit-' . YITH_Vendors()->get_taxonomy_name() == get_current_screen()->id && 'edit-tags.php' == $pagenow;
                $vendors        = YITH_Vendors()->get_vendors( array( 'fields' => 'owner' ) );
                
                $no_owner_shop  = $no_owner_vat = 0;
                foreach( $vendors as $vendor ){
                    $vendor_owner_id = $vendor->owner;
                    empty( $vendor_owner_id ) && $no_owner_shop++;
                    YITH_Vendors()->is_vat_require() && empty( $vendor->vat )   && $no_owner_vat++;
                }

                if( ! empty( $no_owner_shop ) || ! empty( $no_owner_vat ) ) {
                    ?>
                    <div class="notice notice-warning">
                        <?php if( ! empty( $no_owner_shop ) ) : ?>
                            <p>
                                <?php
                                printf( '<strong>%s</strong>: %d %s.', __( 'Warning', 'yith-woocommerce-product-vendors' ), $no_owner_shop, __( 'vendor shops have no owner set. Please, set an owner for each vendor shop in order to enable them', 'yith-woocommerce-product-vendors' ) );
                                if( ! $is_vendor_taxonomy_page ) {
                                    printf( ' <a href="%s">%s</a>', esc_url( add_query_arg( array( 'post_type' => 'product', 'taxonomy' => YITH_Vendors()->get_taxonomy_name(), 'orderby' => 'owner' ), admin_url( 'edit-tags.php' ) ) ),  __( 'Go to Vendor page to fix it.', 'yith-woocommerce-product-vendors' ) );
                                }
                                ?>
                            </p>
                        <?php endif; ?>

                        <?php if( ! empty( $no_owner_vat ) ) : ?>
                            <p>
                                <?php
                                printf( '<strong>%s</strong>: %d %s.', __( 'Warning: ', 'yith-woocommerce-product-vendors' ), $no_owner_vat, __( 'vendor shops have no VAT/SSN set. Please, set VAT/SSN field for each vendor shop', 'yith-woocommerce-product-vendors' ) );
                                if( ! $is_vendor_taxonomy_page ) {
                                    printf( ' <a href="%s">%s</a>', esc_url( add_query_arg( array( 'post_type' => 'product', 'taxonomy' => YITH_Vendors()->get_taxonomy_name() ), admin_url( 'edit-tags.php' ) ) ),  __( 'Go to Vendor page to fix it.', 'yith-woocommerce-product-vendors' ) );
                                }
                                ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php
                }
            }

            elseif( YITH_Vendors()->is_vat_require() && $vendor->is_valid() && $vendor->has_limited_access() ) {
                if( empty( $vendor->vat ) ) {
                    global $pagenow;
                    $is_vendor_details_page = 'admin.php' == $pagenow && isset( $_GET['page'] ) && 'yith_vendor_settings' == $_GET['page'] && ( ( isset( $_GET['tab'] ) && 'vendor-settings' == $_GET['tab'] ) || ! isset( $_GET['tab'] ));
                    ?>
                    <div class="notice notice-warning">
                        <p>
                            <?php
                            printf( '<strong>%s</strong>: %s.', __( 'Warning: ', 'yith-woocommerce-product-vendors' ), __( 'Please, set the VAT/SSN field to complete your profile in "Vendor profile"', 'yith-woocommerce-product-vendors' ) );
                            if( ! $is_vendor_details_page ) {
                                printf( ' <a href="%s">%s</a>', esc_url( add_query_arg( array( 'page' => 'yith_vendor_settings', 'tab' => 'vendor-settings' ), admin_url( 'admin.php' ) ) ),  __( 'Go to Vendor details page to fix it.', 'yith-woocommerce-product-vendors' ) );
                            }
                            ?>
                        </p>
                    </div>
                    <?php
                }
            }
        }

        /*
         * Create "Become a Vendor" and "Terms and Conditions" pages.
         * Fire at register_activation_hook
         *
         * @return void
         * @since  1.7
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public static function create_plugins_page(){
            $become_a_vendor_page = get_option( 'yith_wpv_become_a_vendor_page_id' );

            if( $become_a_vendor_page === false ){
                /* wc_create_page( $slug, $option, $page_title, $page_content, $post_parent ) */
                $page_id = wc_create_page( 'become-a-vendor', 'yith_wpv_become_a_vendor_page_id', __( 'Become a vendor', 'yith-woocommerce-product-vendors' ), '[yith_wcmv_become_a_vendor]', 0 );
            }

            $terms_and_conditions_page = get_option( 'yith_wpv_terms_and_conditions_page_id' );

            if( $terms_and_conditions_page === false ){
                /* wc_create_page( $slug, $option, $page_title, $page_content, $post_parent ) */
                $page_id = wc_create_page( 'Vendors Terms and conditions', 'yith_wpv_terms_and_conditions_page_id', __( 'Terms and Conditions for Vendors', 'yith-woocommerce-product-vendors' ), '', 0 );
            }
        }

        /*
          * Add wp editor to vendor taxonomy page
          *
          * @return void
          * @since  1.8.3
          * @author Andrea Grillo <andrea.grillo@yithemes.com>
          */
        public function add_wp_editor_to_vendor_tax() {
            global $current_screen;

            if ( 'yes' == get_option( 'yith_wpv_vendors_option_editor_management', 'no' ) && $this->is_vendor_tax_page() && ! empty( $_REQUEST['tag_ID'] ) ) {
                $vendor = yith_get_vendor( $_REQUEST['tag_ID'], 'vendor' );
                ?>
                <tr class="form-field">
                    <th scope="row" valign="top"><label for="description"><?php _ex('Description', 'Taxonomy Description', 'yith-woocommerce-product-vendors'); ?></label></th>
                    <td>
                        <?php $this->add_wp_editor( $vendor->description, array( 'textarea_name' => 'description', 'textarea_id' => 'textarea#description'), true ); ?>
                        <br />
                        <span class="description"><?php _e( 'The description is not prominent by default; however, some themes may show it.', 'yith-woocommerce-product-vendors' ); ?></span>
                    </td>
                </tr>
                <?php
            }
        }

        /*
         * Enalbe duplicate product for vendor
         *
         * @return string The capability
         * @since  1.9.6
         * @author Andrea Grillo <andrea.grillo@yithemes.com>
         */
        public function enabled_duplicate_product_capability( $cap ){
            $vendor = yith_get_vendor( 'current', 'user' );
            if( $vendor->is_valid() && $vendor->has_limited_access() ){
                $cap = $this->get_special_cap();
            }
            return $cap;
        }

        /*
        * Is Email Hack
        *
        * @return void
        * @since  1.8.3
        * @author Andrea Grillo <andrea.grillo@yithemes.com>
        */
        public function is_email_hack( $check, $email ){
            if( $email == YITH_Vendors()->get_vendors_taxonomy_label( 'singular_name' ) ){
                $check = true;
            }

            return $check;
        }

        /**
         * Set product to pending status
         *
         * If the vendor haven't the skip admin cap, the product will be set to
         * pending review after any changed
         *
         * @author      Andrea Grillo <andrea.grillo@yithemes.com>
         * @return      void
         * @since       1.9.13
         * @use         yith_wcmv_save_post_product action
         */
        public function set_product_to_pending_review_after_edit( $post_id, $post, $current_vendor ){
            //If the vendor haven't the skip admin cap the post status go to pending review
            $set_to_pending_reviews = 'yes' == get_option( 'yith_wpv_vendors_option_pending_post_status', 'no' ) ? true : false;
            if( $current_vendor->is_valid() && $current_vendor->has_limited_access() && 'no' == $current_vendor->skip_review && 'pending' != $post->status && $set_to_pending_reviews ){
                global $wpdb;
                $old_status = $post->post_status;
                $post->post_status = $new_status = 'pending';
                $result = $wpdb->update( $wpdb->posts, array( 'post_status' => $new_status ), array( 'ID' => $post->ID) );
                clean_post_cache( $post->ID );
                wp_transition_post_status( $old_status, $new_status, $post );
            }
        }

        /**
         *
         */
        public function allowed_comments(){
            if( ! current_user_can( 'moderate_comments' ) ){
                $vendor = yith_get_vendor( 'current', 'user' );
                if( $vendor->is_valid() && $vendor->has_limited_access() ){
                    global $pagenow;
                    if( 'comment.php' == $pagenow || 'edit-comments.php' == $pagenow ){
                        wp_die(
                            '<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
                            '<p>' . __( 'Sorry, you are not allowed to edit comments.' ) . '</p>',
                            403
                        );
                    }
                }

            }
        }
    }
}