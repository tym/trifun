<?php

function trifi_save_member_is_used_meta($post_id, $_post) {
  $is_used_meta_key = '_trifi_is_used';
  $post_meta = get_post_meta($post_id);
  $is_used_meta = $_POST[$is_used_meta_key];

  // throw new Exception(print_r($_POST['_trifi_is_used'], true));

  foreach ($post_meta as $key => $value) {
    if (strpos($key, '_trifi_is_used:') !== false) {
      if (empty($is_used_meta)) {
        update_post_meta($post_id, $key, 'no');
      } else {
        list($k, $v) = explode(':', $key);

        if ($is_used_meta[$v]) {
          update_post_meta($post_id, $key, $is_used_meta[$v]);
        } else {
          update_post_meta($post_id, $key, 'no');
        }
      }
    }
  }
}
add_action('woocommerce_process_shop_order_meta', 'trifi_save_member_is_used_meta', 10, 2);

// function trifi_add_member_is_used_meta($post_id, $post) {
//   update_post_meta($post_id, 'test_checkout_update_order_meta', 'Test!!!');
// }
// add_action('woocommerce_checkout_update_order_meta', 'trifi_add_member_is_used_meta', 10, 2);

function admin_style() {
  wp_enqueue_style('admin-styles', get_stylesheet_directory_uri().'/admin.css');
}
add_action('admin_enqueue_scripts', 'admin_style');

add_action( 'admin_bar_menu', 'remove_wp_logo', 999 );
function remove_wp_logo( $wp_admin_bar ) {
	$wp_admin_bar->remove_node( 'wp-logo' );
}

add_action( 'admin_menu', 'my_remove_menu_pages' );
function my_remove_menu_pages() {
	remove_menu_page('link-manager.php');
}

add_filter( 'auth_cookie_expiration', 'keep_me_logged_in_for_1_year' );

function keep_me_logged_in_for_1_year( $expirein ) {
    return 31556926; // 1 year in seconds
}

/**
 * Redirect users to custom URL based on their role after login
 *
 * @param string $redirect
 * @param object $user
 * @return string
 */
function wc_custom_user_redirect( $redirect, $user ) {
	// Get the first of all the roles assigned to the user
	$role = $user->roles[0];

	$dashboard = admin_url('edit.php?post_type=shop_order');
	$myaccount = get_permalink( wc_get_page_id( 'myaccount' ) );
  $clientaccount = home_url('my-account/orders');

	if( $role == 'administrator' ) {
		//Redirect administrators to the dashboard
		$redirect = $dashboard;
	} elseif ( $role == 'shop-manager' ) {
		//Redirect shop managers to the dashboard
		$redirect = $dashboard;
	} elseif ( $role == 'editor' ) {
		//Redirect editors to the dashboard
		$redirect = $dashboard;
	} elseif ( $role == 'author' ) {
		//Redirect authors to the dashboard
		$redirect = $dashboard;
  } elseif ( $role == 'yith_vendor' ) {
    //Redirect authors to the dashboard
    $redirect = $dashboard;
	} elseif ( $role == 'customer' || $role == 'subscriber' ) {
		//Redirect customers and subscribers to the "My Account" page
		$redirect = $clientaccount;
	} else {
		//Redirect any other role to the previous visited page or, if not available, to the home
		$redirect = wp_get_referer() ? wp_get_referer() : home_url();
	}

	return $redirect;
}
add_filter( 'woocommerce_login_redirect', 'wc_custom_user_redirect', 10, 2 );

// Removed Existing Order Page collumns
remove_filter('manage_edit-shop_order_columns', 'woocommerce_edit_order_columns');

// Added My own filter to Show the PRN - Personal Registration field
add_filter('manage_edit-shop_order_columns', 'omak_edit_order_columns', 'pippin_show_user_id_content');

// The omak_edit_order_columns definition
/*** Taken from admin/post_types/shop_order.php ***/
function omak_edit_order_columns($columns){
  if( current_user_can('yith_vendor')) {
    global $woocommerce;

      $columns = array();
      // $columns["_customer_number"]       = __( 'Member Number','WooCommerce_Customer_Manager' );    // This is the line which added the column after the Title Column
      $columns["order_status"]    = __( 'Status', 'woocommerce' );
      $columns["order_title"]     = __( 'Member', 'woocommerce' );
       $columns["order_date"]             = __( 'Date', 'woocommerce' );
      $columns["order_actions"]       = __( 'Actions', 'woocommerce' );

      return $columns;
    }
  else{
    global $woocommerce;

    $columns = array();

     $columns["cb"]          = "<input type=\"checkbox\" />";
     $columns["order_status"]    = __( 'Status', 'woocommerce' );
     $columns["order_title"]     = __( 'Order', 'woocommerce' );
     $columns["billing_address"]     = __( 'Billing', 'woocommerce' );
     $columns["shipping_address"]    = __( 'Shipping', 'woocommerce' );
     $columns["order_date"]             = __( 'Date', 'woocommerce' );
     $columns["order_actions"]       = __( 'Actions', 'woocommerce' );

    return $columns;
  }
}

/* Custom Email Templates Function */

add_filter( 'automatewoo_email_templates', 'my_automatewoo_email_templates' );

function my_automatewoo_email_templates( $templates )
{
	$templates['custom'] = 'Custom Template #1';

	// as of v2.6.7 you can also create a template with a unique from name and
 	// from email by passing using the following array format
	$templates['custom'] = array(
		'template_name' => 'Custom Template #1',
		'from_name' => 'AutomateWoo Custom',
		'from_email' => 'info@tri-fun.com',
		'slug' => 'custom'
	);

	return $templates;
}

if ( is_user_logged_in() ) {
    add_filter('body_class','add_role_to_body');
    add_filter('admin_body_class','add_role_to_body');
}
function add_role_to_body($classes) {
    $current_user = new WP_User(get_current_user_id());
    $user_role = array_shift($current_user->roles);
    if (is_admin()) {
        $classes .= 'role-'. $user_role;
    } else {
        $classes[] = 'role-'. $user_role;
    }
    return $classes;
}

add_filter( 'wpmem_notify_addr', 'my_admin_email' );
 

// Filter the Admin email so it sends to Preston 

function my_admin_email( $email ) {
 
    // single email example
    $email = 'info@tri-fun.com';
     
    // multiple emails example
    // $email = 'notify1@mydomain.com, notify2@mydomain.com';
     
    // take the default and append a second address to it example:
    // $email = $email . ', notify2@mydomain.com';
     
    // return the result
    return $email;
}


/**
 *Reduce the strength requirement on the woocommerce password.
 *
 * Strength Settings
 * 3 = Strong (default)
 * 2 = Medium
 * 1 = Weak
 * 0 = Very Weak / Anything
 */
function reduce_woocommerce_min_strength_requirement( $strength ) {
    return 1;
}
add_filter( 'woocommerce_min_password_strength', 'reduce_woocommerce_min_strength_requirement' );


/* Remove strength meter */
/*
function wc_ninja_remove_password_strength() {
	if ( wp_script_is( 'wc-password-strength-meter', 'enqueued' ) ) {
		wp_dequeue_script( 'wc-password-strength-meter' );
	}
}
add_action( 'wp_print_scripts', 'wc_ninja_remove_password_strength', 100 );
*/





