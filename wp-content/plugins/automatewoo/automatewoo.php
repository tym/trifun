<?php
/**
 * Plugin Name: AutomateWoo
 * Plugin URI: http://automatewoo.com
 * Description: Powerful marketing automation for your WooCommerce store.
 * Version: 2.8.0
 * Author: Daniel Bitzer
 * Author URI: http://danielbitzer.com
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Text Domain: automatewoo
 * Domain Path: /languages
 */

// Copyright (c) 2015 Daniel Bitzer. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

if ( ! defined( 'ABSPATH' ) ) exit;

final class AutomateWoo {

	/** @var string */
	public $version = '2.8.0';

	/** @var string  */
	public $plugin_slug;

	/** @var string  */
	public $plugin_main_file;

	/** @var string */
	public $plugin_basename;

	/** @var string  */
	public $website_url = 'http://automatewoo.com/';

	/** @var string  */
	public $url_license_docs = 'http://automatewoo.com/docs/licenses/';

	/** @var string  */
	public $post_type = 'aw_workflow';

	/** @var array */
	public $registered_triggers = array();

	/** @var array */
	public $registered_actions = array();

	/** @var AW_Admin */
	public $admin;

	/** @var AW_Email */
	public $email;

	/** @var AW_Licenses */
	public $licenses;

	/** @var AW_Abandoned_Cart_Manager */
	public $abandoned_cart;

	/** @var AW_Session_Tracker */
	public $session_tracker;

	/** @var AW_Queue_Manager */
	public $queue_manager;

	/** @var AW_Conversion_Manager */
	public $conversion_manager;


	/** @var AW_Language_Helper */
	public $language_helper;

	/** @var AW_Order_Helper */
	public $order_helper;

	/** @var AW_System_Checker */
	public $system_checker;

	/** @var AW_Tools_Manager */
	public $tools;

	/** @var AW_Cache_Helper */
	private $cache;

	/** @var AW_Data_Type_Loader */
	private $data_type_loader;

	/** @var AW_Unsubscribe_Manager */
	private $unsubscribes;

	/** @var AW_Rules_Loader */
	private $rules_loader;

	/** @var AW_Wishlist */
	private $wishlist;

	/** @var AW_Variables */
	private $variables;

	/** @var AW_Addon_Manager */
	private $addons;

	/** @var AW_Options */
	private $options;

	/** @var AW_Integrations */
	private $integrations;

	/** @var bool */
	public $debug = false;

	/** @var AutomateWoo */
	private static $_instance = null;

	/** Database table names */
	public $table_name_abandoned_cart = 'automatewoo_abandoned_carts';
	public $table_name_guests = 'automatewoo_guests';
	public $table_name_queue = 'automatewoo_queue';
	public $table_name_logs = 'automatewoo_logs';
	public $table_name_log_meta = 'automatewoo_log_meta';
	public $table_name_unsubscribes = 'automatewoo_unsubscribes';


	/**
	 * Constructor
	 */
	private function __construct() {
		$this->plugin_basename = plugin_basename( __FILE__ );
		list ( $this->plugin_slug, $this->plugin_main_file ) = explode( '/', $this->plugin_basename );

		$this->load_plugin_textdomain();

		if ( $this->check_env() ) {
			spl_autoload_register( array( $this, 'autoload' ) );
			add_action( 'woocommerce_init', array( $this, 'init' ), 20 );
		}
	}


	/**
	 * Init
	 */
	function init() {

		$this->includes();

		AW_Post_Types::init();
		AW_Cron::init();
		AW_Ajax::init();

		$this->email = new AW_Email();
		$this->session_tracker = new AW_Session_Tracker();
		$this->abandoned_cart = new AW_Abandoned_Cart_Manager();
		$this->queue_manager = new AW_Queue_Manager();
		$this->conversion_manager = new AW_Conversion_Manager();
		$this->licenses = new AW_Licenses();
		$this->language_helper = new AW_Language_Helper();
		$this->order_helper = new AW_Order_Helper();
		$this->tools = new AW_Tools_Manager();

		do_action( 'automatewoo_init_addons' );

		// Load and init the triggers
		// Actions don't load until required by admin interface or when a workflow runs
		$this->load_triggers();
		$this->init_triggers();

		if ( is_admin() ) {
			$this->admin = new AW_Admin();
			$this->system_checker = new AW_System_Checker();
			AW_Updater::init();
			AW_Install::init();
		}

		// TODO improve this debug system
		if ( WP_DEBUG ) {
			add_action('wp_loaded', array( $this, 'enable_debug' ) );
		}

		do_action( 'automatewoo_init' );
		do_action( 'automatewoo_loaded' );

		new AW_Hooks();
	}


	/**
	 * Autoload any class that isn't a trigger or an action
	 *
	 * @since 2.0
	 *
	 * @param $class
	 *
	 * @return mixed|void
	 */
	function autoload( $class ) {
		$path = $this->get_autoload_path( $class );

		if ( $path && file_exists( $path ) )
			include $path;
	}


	/**
	 * @param $class
	 * @return string
	 */
	function get_autoload_path( $class ) {

		if ( substr( $class, 0, 3 ) != 'AW_')
			return false;

		$file = str_replace( 'AW_', '/', $class );
		$file = str_replace( '_', '-', $file );
		$file = strtolower( $file );

		$abstracts = array(
			'/action',
			'/trigger',
			'/field',
			'/query',
			'/model',
			'/query-custom-table',
			'/system-check',
			'/integration',
			'/variable',
			'/options-api',
			'/tool',
			'/data-type',
		);

		if ( in_array( $file, $abstracts ) ) {
			return dirname(__FILE__) . '/includes/abstracts' . $file . '.php';
		}
		elseif ( strstr( $file, '/admin-' ) ) {
			$file = str_replace( '/admin-', '/admin/', $file );
			$file = str_replace( '/controller-', '/controllers/', $file );

			return dirname(__FILE__) . $file . '.php';
		}
		else {
			$file = str_replace( '/trigger-', '/triggers/', $file );
			$file = str_replace( '/action-', '/actions/', $file );
			$file = str_replace( '/field-', '/fields/field-', $file );
			$file = str_replace( '/query-', '/queries/query-', $file );
			$file = str_replace( '/model-', '/models/model-', $file );
			$file = str_replace( '/variable-', '/variables/', $file );
			$file = str_replace( '/system-check-', '/system-checks/', $file );
			$file = str_replace( '/integration-', '/integrations/', $file );
			$file = str_replace( '/rule-', '/rules/', $file );

			return dirname(__FILE__) . '/includes' . $file . '.php';
		}
	}


	/**
	 * Includes
	 */
	function includes() {
		include_once( 'includes/compatibility.php' );
		include_once( 'includes/helpers.php' );
		include_once( 'includes/hooks.php' );

		include_once( $this->lib_path( '/easy-user-tags/easy-user-tags.php' ) );

		if ( is_admin() ) {
			include_once( 'admin/admin.php' );
		}
	}


	/**
	 * Load triggers
	 *
	 * @since 2.0.0
	 */
	function load_triggers() {

		if ( did_action('automatewoo_triggers_loaded') ) return;

		do_action('automatewoo_before_triggers_loaded');

		new AW_Trigger_Order_Placed();
		new AW_Trigger_Order_Payment_Received();
		new AW_Trigger_Order_Status_Changes();
		new AW_Trigger_Order_Processing();
		new AW_Trigger_Order_Completed();
		new AW_Trigger_Order_Cancelled();
		new AW_Trigger_Order_On_Hold();
		new AW_Trigger_Order_Refunded();
		new AW_Trigger_Order_Pending();
		new AW_Trigger_Order_Note_Added();

		new AW_Trigger_User_Absent();
		new AW_Trigger_User_New_Account();
		new AW_Trigger_User_Total_Spend_Reaches();
		new AW_Trigger_User_Order_Count_Reaches();
		new AW_Trigger_User_Leaves_Review();
		new AW_Trigger_Guest_Leaves_Review();
		new AW_Trigger_User_Purchases_From_Category();
		new AW_Trigger_User_Purchases_From_Tag();
		new AW_Trigger_User_Purchases_From_Taxonomy_Term();
		new AW_Trigger_User_Purchases_Specific_Product();
		new AW_Trigger_User_Purchases_Product_Variation_With_Attribute();

		new AW_Trigger_Guest_Created();

		if ( AW()->options()->abandoned_cart_enabled ) {
			new AW_Trigger_Abandoned_Cart_User();
			new AW_Trigger_Abandoned_Cart_Guest();
		}

		if ( AW()->integrations()->subscriptions_enabled() ) {
			new AW_Trigger_Subscription_Status_Changed();
			new AW_Trigger_Subscription_Payment_Complete();
			new AW_Trigger_Subscription_Payment_Failed();
			new AW_Trigger_Subscription_Trial_End();
			new AW_Trigger_Subscription_Before_Renewal();
		}

		new AW_Trigger_Wishlist_Item_Goes_On_Sale();
		new AW_Trigger_Wishlist_Reminder();
		new AW_Trigger_Wishlist_Item_Added();

		new AW_Trigger_Workflow_Times_Run_Reaches();

		do_action('automatewoo_triggers_loaded');
	}


	/**
	 * Loads triggers.
	 *
	 * Triggers must always be loaded on init so that their hooks can be added.
	 */
	function init_triggers() {
		if ( did_action('automatewoo_init_triggers') ) return;
		do_action('automatewoo_init_triggers');
	}


	/**
	 * Load actions
	 *
	 * @since 2.0
	 */
	function load_actions() {

		if ( did_action('automatewoo_actions_loaded') ) return;

		do_action('automatewoo_before_actions_loaded');

		new AW_Action_Send_Email();

		if ( AW()->options()->twilio_integration_enabled ) {
			new AW_Action_Send_SMS_Twilio();
		}

		new AW_Action_Change_User_Type();
		new AW_Action_Update_User_Meta();
		new AW_Action_User_Add_Tags();
		new AW_Action_User_Remove_Tags();

		new AW_Action_Change_Order_Status();
		new AW_Action_Update_Order_Meta();
		new AW_Action_Resend_Order_Email();
		new AW_Action_Trigger_Order_Action();

		if ( AW()->options()->active_campaign_integration_enabled ) {
			new AW_Action_Active_Campaign_Create_Contact();
			new AW_Action_Active_Campaign_Add_Tag();
			new AW_Action_Active_Campaign_Remove_Tag();
		}

		if ( AW()->options()->mailchimp_integration_enabled ) {
			new AW_Action_MailChimp_Subscribe();
			new AW_Action_MailChimp_Unsubscribe();
		}

		new AW_Action_Add_To_Campaign_Monitor();
		new AW_Action_Add_To_Mad_Mimi_List();

		if ( AW()->integrations()->subscriptions_enabled() ) {
			new AW_Action_Change_Subscription_Status();
		}

		if ( AW()->integrations()->is_memberships_enabled() ) {
			new AW_Action_Memberships_Change_Plan();
		}

		new AW_Action_Clear_Queued_Events();
		new AW_Action_Custom_Function();
		new AW_Action_Update_Product_Meta();
		new AW_Action_Change_Post_Status();
		new AW_Action_Change_Workflow_Status();

		do_action('automatewoo_actions_loaded');
	}


	/**
	 * Init actions. Also calls load just in case.
	 *
	 * Actions need only be loaded when they are needed.
	 * This method can be called multiple times.
	 */
	function init_actions() {
		if ( did_action('automatewoo_init_actions') ) return;
		$this->load_actions();
		do_action('automatewoo_init_actions');
	}


	/**
	 * @param $trigger_name string
	 *
	 * @return AW_Trigger/false
	 */
	function get_registered_trigger( $trigger_name ) {
		if ( ! isset( $this->registered_triggers[$trigger_name] ) )
			return false;

		return $this->registered_triggers[$trigger_name];
	}


	/**
	 * @param $action_name string
	 *
	 * @return AW_Action/false
	 */
	function get_registered_action( $action_name ) {
		$this->init_actions();

		if ( ! isset( $this->registered_actions[$action_name] ) )
			return false;

		return $this->registered_actions[$action_name];
	}


	/**
	 * @return array
	 */
	function get_actions() {
		$this->init_actions();
		return $this->registered_actions;
	}


	/**
	 *
	 */
	function load_plugin_textdomain() {
		load_plugin_textdomain( 'automatewoo', false, "automatewoo/languages" );
	}


	/**
	 * @return AW_Data_Type_Loader
	 */
	function data_type_loader() {
		if ( ! isset( $this->data_type_loader ) ) {
			$this->data_type_loader = new AW_Data_Type_Loader();
		}
		return $this->data_type_loader;
	}


	/**
	 * @return AW_Rules_Loader
	 */
	function rules() {
		if ( ! isset( $this->rules_loader ) ) {
			$this->rules_loader = new AW_Rules_Loader();
		}
		return $this->rules_loader;
	}


	/**
	 * @return AW_Cache_Helper
	 */
	function cache() {
		if ( ! isset( $this->cache ) ) {
			$this->cache = new AW_Cache_Helper();
		}
		return $this->cache;
	}


	/**
	 * @return AW_Wishlist
	 */
	function wishlist() {
		if ( ! isset( $this->wishlist ) ) {
			$this->wishlist = new AW_Wishlist();
		}
		return $this->wishlist;
	}


	/**
	 * @return AW_Addon_Manager
	 */
	function addons() {
		if ( ! isset( $this->addons ) ) {
			$this->addons = new AW_Addon_Manager();
		}
		return $this->addons;
	}


	/**
	 * @return AW_Variables
	 */
	function variables() {
		if ( ! isset( $this->variables ) ) {
			$this->variables = new AW_Variables();
		}
		return $this->variables;
	}


	/**
	 * @return AW_Options
	 */
	function options() {
		if ( ! isset( $this->options ) ) {
			$this->options = new AW_Options();
		}
		return $this->options;
	}


	/**
	 * @return AW_Integrations
	 */
	function integrations() {
		if ( ! isset( $this->integrations ) ) {
			$this->integrations = new AW_Integrations();
		}
		return $this->integrations;
	}


	/**
	 * @return AW_Unsubscribe_Manager
	 */
	function unsubscribes() {
		if ( ! isset( $this->unsubscribes ) ) {
			$this->unsubscribes = new AW_Unsubscribe_Manager();
		}
		return $this->unsubscribes;
	}


	/**
	 * @param $data_type_id
	 * @return AW_Data_Type|false
	 */
	function get_data_type( $data_type_id ) {
		return $this->data_type_loader()->get_data_type( $data_type_id );
	}


	/**
	 *
	 */
	function enable_debug() {
		$this->debug = true;

		if ( aw_request('aw-debug-cron-15') ) {
			echo "AutomateWoo Fifteen Minute Worker";
			do_action('automatewoo_fifteen_minute_worker');
			exit();
		}

		if ( aw_request('aw-debug-cron-30') ) {
			echo "AutomateWoo Thirty Minute Worker";
			do_action('automatewoo_thirty_minute_worker');
			exit();
		}

		if ( aw_request('aw-debug-cron-hourly') ) {
			echo "AutomateWoo Hourly Worker";
			do_action('automatewoo_hourly_worker');
			exit();
		}

		if ( aw_request('aw-debug-cron-daily') ) {
			echo "AutomateWoo Daily Worker";
			do_action('automatewoo_daily_worker');
			exit();
		}

		if ( aw_request('aw-debug-cron-four-hourly') ) {
			echo "AutomateWoo Four Hourly Worker";
			do_action('automatewoo_four_hourly_worker');
			exit();
		}

		if ( aw_request('aw-debug-cron-weekly') ) {
			echo "AutomateWoo Weekly Worker";
			do_action('automatewoo_weekly_worker');
			exit();
		}
	}


	/**
	 * What type of request is this?
	 * string $type ajax, frontend or admin.
	 *
	 * @param $type string
	 * @return bool
	 */
	function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}


	/**
	 * @param string $end
	 * @return string
	 */
	function url( $end = '' ) {
		return untrailingslashit( plugin_dir_url( $this->plugin_basename ) ) . $end;
	}


	/**
	 * @param string $end
	 * @return string
	 */
	function admin_assets_url( $end = '' ) {
		return AW()->url( '/admin/assets' . $end );
	}


	/**
	 * @param string $end
	 * @return string
	 */
	function path( $end = '' ) {
		return untrailingslashit( dirname( __FILE__ ) ) . $end;
	}


	/**
	 * @param string $end
	 * @return string
	 */
	function admin_path( $end = '' ) {
		return $this->path( '/admin' . $end );
	}


	/**
	 * @param string $end
	 * @return string
	 */
	function lib_path( $end = '' ) {
		return $this->path( '/includes/libraries' . $end );
	}


	/**
	 *
	 */
	function check_env() {
		$ok = true;

		if ( is_network_admin() ) {
			add_action( 'network_admin_notices', array( $this, 'network_admin_notice' ) );
			$ok = false;
		}

		if ( version_compare( phpversion(), '5.4', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'php_version_admin_notice' ) );
			$ok = false;
		}

		return $ok;
	}


	/**
	 *
	 */
	function php_version_admin_notice() {
		echo '<div class="notice notice-error"><p><strong>'
			. __( 'AutomateWoo requires PHP version 5.4+.' , 'automatewoo' ) . ' </strong>'
			. __( 'Please contact your hosting provider to resolve the issue.', 'automatewoo' )
			. '</p></div>';
	}



	function network_admin_notice() {
		echo '<div class="notice notice-error"><p><strong>'
			. __( 'AutomateWoo can not be activated network-wide.' , 'automatewoo' ) . ' </strong>'
			. '</p></div>';
	}


	/**
	 * @return string
	 * @since 2.4.4
	 */
	function get_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			$ip = $_SERVER['REMOTE_ADDR'];
		return $ip;
	}


	/**
	 * @param $id
	 * @return AW_Model_Log|bool
	 * @since 2.6.7
	 */
	function get_log( $id ) {
		if ( ! $id ) return false;
		$log = new AW_Model_Log( $id );
		return $log->exists ? $log : false;
	}


	/**
	 * @param $id
	 * @return AW_Model_Workflow|bool
	 */
	function get_workflow( $id ) {
		if ( ! $id ) return false;
		$workflow = new AW_Model_Workflow( $id );
		return $workflow->exists ? $workflow : false;
	}


	/**
	 * @param $id
	 * @return AW_Model_Queued_Event|bool
	 */
	function get_queued_event( $id ) {
		if ( ! $id ) return false;
		$event = new AW_Model_Queued_Event( $id );
		return $event->exists ? $event : false;
	}


	/**
	 * @param $id
	 * @return AW_Model_Unsubscribe|bool
	 */
	function get_unsubscribe( $id ) {
		if ( ! $id ) return false;
		$unsubscribe = new AW_Model_Unsubscribe( $id );
		return $unsubscribe->exists ? $unsubscribe : false;
	}


	/**
	 * @param $id
	 * @return AW_Model_Guest|bool
	 */
	function get_guest( $id ) {
		if ( ! $id ) return false;
		$guest = new AW_Model_Guest( $id );
		return $guest->exists ? $guest : false;
	}


	/**
	 * @param $id
	 * @return AW_Model_Abandoned_Cart|bool
	 */
	function get_cart( $id ) {
		if ( ! $id ) return false;
		$cart = new AW_Model_Abandoned_Cart( $id );
		return $cart->exists ? $cart : false;
	}



	/**
	 * @return AutomateWoo - Main instance
	 */
	static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

}

/**
 * Backwards compatible
 * @return AutomateWoo
 */
function AutomateWoo() {
	return AW();
}

/**
 * @return AutomateWoo
 */
function AW() {
	return AutomateWoo::instance();
}

AW();
