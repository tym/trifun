<?php
/**
 * @class       AW_Install
 * @package     AutomateWoo
 */

class AW_Install {

	/** @var array */
	private static $db_updates = [
		'2.1.0' => 'automatewoo-update-2.1.0.php',
		'2.3' => 'automatewoo-update-2.3.php',
		'2.4' => 'automatewoo-update-2.4.php',
		'2.6' => 'automatewoo-update-2.6.php',
		'2.6.1' => 'automatewoo-update-2.6.1.php',
		'2.7' => 'automatewoo-update-2.7.php'
	];


	/**
	 * Init
	 */
	public static function init() {
		add_action( 'admin_init', [ __CLASS__, 'admin_init' ], 5 );
		add_filter( 'plugin_action_links_' . AW()->plugin_basename, [ __CLASS__, 'plugin_action_links' ] );
	}



	/**
	 * Admin init
	 */
	public static function admin_init() {

		if ( defined( 'IFRAME_REQUEST' ) || is_ajax() )
			return;

		if ( AW()->options()->version != AW()->version ) {

			self::install();

			// check for required database update
			if ( self::database_upgrade_available() ) {
				add_action( 'admin_notices', [ __CLASS__, 'data_upgrade_prompt' ] );
			}
			else {
				self::update_version();
			}
		}

		foreach( AW()->addons()->get_all() as $addon ) {
			/** @var AW_Abstract_Addon $addon */
			$addon->check_version();
		}

		if ( did_action( 'automatewoo_updated' ) || did_action( 'automatewoo_addon_updated' ) ) {
			// do API check in after an update
			AW()->licenses->check_statuses();
		}

	}


	/**
	 * Install
	 */
	public static function install() {

		self::create_tables();

		do_action( 'automatewoo_installed' );
	}


	/**
	 * @return bool
	 */
	static function database_upgrade_available() {

		if ( AW()->options()->version == AW()->version )
			return false;

		return AW()->options()->version && version_compare( AW()->options()->version, max( array_keys( self::$db_updates ) ), '<' );
	}


	/**
	 * Handle updates
	 */
	public static function update() {

		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );

		foreach ( self::$db_updates as $version => $updater ) {
			if ( version_compare( AW()->options()->version, $version, '<' ) ) {
				include( AW()->path( '/includes/updates/' . $updater ) );
			}
		}

		self::update_version();
	}


	/**
	 * Update version to current
	 */
	private static function update_version() {
		update_option( 'automatewoo_version', AW()->version, true );
		do_action( 'automatewoo_updated' );
	}


	/**
	 * Set up the database tables which the plugin needs to function.
	 */
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( self::get_schema() );
	}


	/**
	 * Renders prompt notice for user to update
	 */
	static function data_upgrade_prompt() {

		AW()->admin->get_view( 'data-upgrade-prompt', [
			'plugin_name' => __( 'AutomateWoo', 'automatewoo' ),
			'plugin_slug' => AW()->plugin_slug
		]);
	}



	/**
	 * @return bool
	 */
	static function is_data_update_screen() {

		$screen = get_current_screen();
		return $screen->id === 'automatewoo_page_automatewoo-data-upgrade';
	}


	/**
	 * Show action links on the plugin screen.
	 *
	 * @param	mixed $links Plugin Action links
	 * @return	array
	 */
	public static function plugin_action_links( $links ) {

		$action_links = [
			'settings' => '<a href="' . AW()->admin->page_url( 'settings' ) . '" title="' . esc_attr( __( 'View AutomateWoo Settings', 'woocommerce' ) ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>',
		];

		return array_merge( $action_links, $links );
	}


	/**
	 * Get Table schema
	 * @return string
	 */
	private static function get_schema() {

		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		/*
		 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
		 * As of WordPress 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
		 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
		 *
		 * This may cause duplicate index notices in logs due to https://core.trac.wordpress.org/ticket/34870 but dropping
		 * indexes first causes too much load on some servers/larger DB.
		 */
		$max_index_length = 191;

		return "
CREATE TABLE {$wpdb->prefix}automatewoo_guests (
  id bigint(20) NOT NULL auto_increment,
  email varchar(255) NOT NULL default '',
  tracking_key varchar(32) NOT NULL default '',
  created datetime NULL,
  last_active datetime NULL,
  language varchar(10) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY tracking_key (tracking_key),
  KEY email (email($max_index_length))
) $collate;
CREATE TABLE {$wpdb->prefix}automatewoo_abandoned_carts (
  id bigint(20) NOT NULL auto_increment,
  user_id bigint(20) NOT NULL default 0,
  guest_id bigint(20) NOT NULL default 0,
  last_modified datetime NULL,
  created datetime NULL,
  items longtext NOT NULL default '',
  coupons longtext NOT NULL default '',
  total varchar(32) NOT NULL default '0',
  token varchar(32) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY user_id (user_id),
  KEY guest_id (guest_id)
) $collate;
CREATE TABLE {$wpdb->prefix}automatewoo_queue (
  id bigint(20) NOT NULL auto_increment,
  workflow_id bigint(20) NULL,
  date datetime NULL,
  created datetime NULL,
  data_items longtext NOT NULL default '',
  failed int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  KEY workflow_id (workflow_id),
  KEY date (date)
) $collate;
CREATE TABLE {$wpdb->prefix}automatewoo_logs (
  id bigint(20) NOT NULL auto_increment,
  workflow_id bigint(20) NULL,
  date datetime NULL,
  tracking_enabled int(1) NOT NULL DEFAULT 0,
  conversion_tracking_enabled int(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  KEY workflow_id (workflow_id),
  KEY date (date),
  KEY workflow_id_date (workflow_id, date)
) $collate;
CREATE TABLE {$wpdb->prefix}automatewoo_log_meta (
  meta_id bigint(20) NOT NULL auto_increment,
  log_id bigint(20) NULL,
  meta_key varchar(255) NULL,
  meta_value longtext NOT NULL default '',
  PRIMARY KEY  (meta_id),
  KEY log_id (log_id),
  KEY meta_key (meta_key($max_index_length))
) $collate;
CREATE TABLE {$wpdb->prefix}automatewoo_unsubscribes (
  id bigint(20) NOT NULL auto_increment,
  workflow_id bigint(20) NULL,
  user_id bigint(20) NOT NULL default 0,
  email varchar(255) NOT NULL default '',
  date datetime NULL,
  PRIMARY KEY  (id),
  KEY workflow_id (workflow_id),
  KEY used_id (user_id),
  KEY email (email($max_index_length))
) $collate;
		";
	}

}
