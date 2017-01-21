<?php 
/**
Plugin Name: Custom My Account for Woocommerce
Plugin URI: http://www.phoeniixx.com
Description: This plugin allows your customers to have all the important records like ‘My Downloads’ and ‘My Orders’ etc. details together at one place. 
Author: phoeniixx
Version: 1.2.3
Text Domain:custom-my-account
Domain Path: /languages
Author URI: http://www.phoeniixx.com
**/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
{

	function pdwooacnt_plugin_path() {
	 
	  // gets the absolute path to this plugin directory
	 
	  return untrailingslashit( plugin_dir_path( __FILE__ ) );
	 
	}

	 $row = get_option('myaccount_plugin_setting');
	 
	if($row=='enable')
	{

		add_filter( 'woocommerce_locate_template', 'pdwooacnt_locate_template', 10, 3 );
		
	}

	 
	function pdwooacnt_locate_template( $template, $template_name, $template_path ) {
	 
	  global $woocommerce;
	 
	  $_template = $template;
	 
	  if ( ! $template_path ) $template_path = $woocommerce->template_url;
	 
	  $plugin_path  = pdwooacnt_plugin_path() . '/woocommerce/';
	 
	  // Look within passed path within the theme - this is priority
	 
	  $template = locate_template(
	 
		array(
	 
		  $template_path . $template_name,
	 
		  $template_name
	 
		)
	 
	  );
	 
	  // Modification: Get the template from this plugin, if it exists
	 
	  if ( ! $template && file_exists( $plugin_path . $template_name ) )
	 
		$template = $plugin_path . $template_name;
	 
	  // Use default template
	 
	  if ( ! $template )
	 
	 
	   $template = $_template;
	   
	  // Return what we found
	 
	  return $template;
	 
	}

	/* add_action('admin_enqueue_scripts','phoen_my_account');
	
	function phoen_my_account()
	{
		
		 wp_register_style( 'phoen-wcmap-1',  $plugin_dir_url.'css/font-awesome.css' );
		 
		 wp_register_style( 'phoen-wcmap-2',  $plugin_dir_url.'css/font-awesome.min.css' );
		 
	} */

	function phoen_wcmap_add_scripts() 
	{
		
		wp_enqueue_style( 'phoen-wcmap',  plugins_url('css/phoen-wcmap.css', __FILE__) );

	}

	add_action( 'wp_enqueue_scripts', 'phoen_wcmap_add_scripts' );

	add_action('admin_menu', 'add_custom_myaccount_page');
		
	function add_custom_myaccount_page() 
	{

		$plugin_dir_url =  plugin_dir_url( __FILE__ );
		
		add_menu_page( 'phoeniixx', __( 'Phoeniixx', 'phe' ), 'nosuchcapability', 'phoeniixx', NULL, $plugin_dir_url.'assets/img/logo-wp.png', 57 );
		
		add_submenu_page( 'phoeniixx', 'Custom My Account', 'Custom My Account', 'manage_options', 'phoe_myaccount_setting', 'phoe_myaccount_setting' );	
	
	}
		
	function phoe_myaccount_setting()
	{
		
		include(dirname(__FILE__).'/setting.php');
		
	}
	
	register_activation_hook( __FILE__, 'phoen_wc_myaccount_registration' );

	function phoen_wc_myaccount_registration()
	{
		
		update_option('myaccount_plugin_setting','enable');
		
	}

}

?>