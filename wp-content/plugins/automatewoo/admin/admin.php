<?php
/**
 * @class 		AW_Admin
 * @package		AutomateWoo/Admin
 */

class AW_Admin {

	/**
	 * Constructor
	 */
	function __construct() {

		AW_Admin_Ajax::init();

		add_action( 'current_screen', [ $this, 'includes' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ], 20 );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_footer', [ $this, 'replace_top_level_menu' ] );
		add_action( 'admin_notices', [ $this, 'license_notices'] );

		add_filter( 'admin_body_class', [ $this, 'body_class' ] );
		add_filter( 'woocommerce_reports_screen_ids', [ $this, 'inject_woocommerce_reports_screen_ids' ] );
		add_filter( 'editor_stylesheets', [ $this, 'add_editor_styles' ] );

		if ( aw_request('action') === 'automatewoo-settings' ) {
			add_action( 'wp_loaded', [ 'AW_Admin_Controller_Settings', 'save' ] );
		}

		if ( aw_request('automatewoo-email-preview-loader') ) {
			add_action( 'wp_loaded', [ $this, 'email_preview_loader' ] );
		}
	}


	/**
	 *
	 */
	function includes() {

		if ( ! $screen = get_current_screen() )
			return;

		switch ( $screen->id ) {
			case 'aw_workflow' :
				include( 'workflow-edit.php' );
				break;

			case 'edit-aw_workflow' :
				include( 'workflow-list.php' );
				break;
		}
	}


	/**
	 *
	 */
	function admin_menu() {

		$sub_menu = [];
		$position = '55.6324'; // fix for rare position clash bug

		add_menu_page( __( 'AutomateWoo', 'automatewoo' ), __( 'AutomateWoo', 'automatewoo' ), 'manage_woocommerce', 'automatewoo', false, 'none', $position );

		if ( AW()->licenses->is_active() ) {

			$sub_menu[ 'dashboard' ] = [
				'title' => __( 'Dashboard', 'automatewoo' ),
				'function' => [ 'AW_Admin_Controller_Dashboard', 'output' ]
			];

			$sub_menu[ 'workflows' ] = [
				'title' => __( 'Workflows', 'automatewoo' ),
				'slug' => 'edit.php?post_type=aw_workflow'
			];

			$sub_menu[ 'logs' ] = [
				'title' => __( 'Logs', 'automatewoo' ),
				'function' => [ 'AW_Admin_Controller_Logs', 'output' ]
			];

			$sub_menu[ 'queue' ] = [
				'title' => __( 'Queue', 'automatewoo' ),
				'function' => [ 'AW_Admin_Controller_Queue', 'output' ]
			];

			if ( AW()->options()->abandoned_cart_enabled ) {
				$sub_menu['carts'] = [
					'title' => __( 'Carts', 'automatewoo'),
					'function' => [ 'AW_Admin_Controller_Carts', 'output' ]
				];

				$sub_menu['guests'] = [
					'title' => __( 'Guests', 'automatewoo'),
					'function' => [ 'AW_Admin_Controller_Guests', 'output' ]
				];
			}

			$sub_menu[ 'unsubscribes' ] = [
				'title' => __( 'Unsubscribes', 'automatewoo' ),
				'function' => [ 'AW_Admin_Controller_Unsubscribes', 'output' ]
			];

			$sub_menu[ 'reports' ] = [
				'title' => __( 'Reports', 'automatewoo' ),
				'function' => [ 'AW_Admin_Controller_Reports', 'output' ]
			];

			$sub_menu[ 'tools' ] = [
				'title' => __( 'Tools', 'automatewoo' ),
				'function' => [ 'AW_Admin_Controller_Tools', 'output' ]
			];
		}

		$sub_menu[ 'settings' ] = [
			'title' => __( 'Settings', 'automatewoo' ),
			'function' => [ 'AW_Admin_Controller_Settings', 'output' ]
		];

		$sub_menu[ 'data-upgrade' ] = [
			'title' => __( 'AutomateWoo Data Update', 'automatewoo' ),
			'function' => [ $this, 'page_data_upgrade' ]
		];

		foreach ( $sub_menu as $key => $item ) {

			if ( empty( $item['function'] ) ) $item['function'] = '';
			if ( empty( $item['capability'] ) ) $item['capability'] = 'manage_woocommerce';
			if ( empty( $item['slug'] ) ) $item['slug'] = 'automatewoo-' . $key;
			if ( empty( $item['page_title'] ) ) $item['page_title'] = $item['title'];

			add_submenu_page( 'automatewoo', $item['page_title'], $item['title'], $item['capability'], $item['slug'], $item['function'] );

			if ( $key === 'workflows' ) {
				do_action( 'automatewoo/admin/submenu_pages', 'automatewoo' );
			}
		}
	}


	/**
	 * Dynamic replace top level menu
	 */
	function replace_top_level_menu() {
		$top_menu_link = AW()->licenses->is_active() ? $this->page_url('dashboard') : $this->page_url('licenses');

		?>
		<script type="text/javascript">
			jQuery('#adminmenu a.toplevel_page_automatewoo').attr( 'href', '<?php echo $top_menu_link ?>' );
		</script>
		<?php
	}



	/**
	 *
	 */
	function register_scripts() {

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$url = AW()->admin_assets_url( '/js' );
			$suffix = '';
		}
		else {
			$url = AW()->admin_assets_url( '/js/min' );
			$suffix = '.min';
		}

		$vendor_url = AW()->admin_assets_url( '/js/vendor' );

		wp_register_script( 'automatewoo-clipboard', $vendor_url . "/clipboard$suffix.js", [], AW()->version );
		wp_register_script( 'jquery-cookie', WC()->plugin_url() . '/assets/js/jquery-cookie/jquery.cookie.js', [ 'jquery' ], '1.4.1' );

		wp_register_script( 'automatewoo', $url . "/automatewoo$suffix.js", ['jquery', 'jquery-ui-datepicker', 'jquery-tiptip', 'backbone', 'underscore' ], AW()->version );
		wp_register_script( 'automatewoo-validate', $url . "/validate$suffix.js", [ 'automatewoo' ], AW()->version );
		wp_register_script( 'automatewoo-workflows', $url . "/automatewoo-workflows$suffix.js", [ 'automatewoo', 'automatewoo-validate', 'automatewoo-modal', 'wp-util' ], AW()->version );
		wp_register_script( 'automatewoo-variables', $url . "/automatewoo-variables$suffix.js", [ 'automatewoo-modal', 'automatewoo-clipboard' ], AW()->version );
		wp_register_script( 'automatewoo-tools', $url . "/automatewoo-tools$suffix.js", ['automatewoo'], AW()->version );
		wp_register_script( 'automatewoo-sms-test', $url . "/automatewoo-sms-test$suffix.js", ['automatewoo'], AW()->version );
		wp_register_script( 'automatewoo-modal', $url . "/automatewoo-modal$suffix.js", [ 'automatewoo' ], AW()->version );
		wp_register_script( 'automatewoo-rules', $url . "/automatewoo-rules$suffix.js", [ 'automatewoo', 'automatewoo-workflows' ], AW()->version );
		wp_register_script( 'automatewoo-dashboard', $url . "/dashboard$suffix.js", [ 'automatewoo', 'automatewoo-modal', 'jquery-masonry', 'flot', 'flot-resize', 'flot-time', 'flot-pie', 'flot-stack' ], AW()->version );


		global $wp_locale;

		wp_localize_script( 'automatewoo-dashboard', 'automatewooDashboardLocalizeScript', []);

		wp_localize_script( 'automatewoo-validate', 'automatewooValidateLocalizedErrorMessages', [
			'noVariablesSupport' => __( 'This field does not support variables.', 'automatewoo' ),
			'invalidDataType' => __( "Variable '%s' is not available with the selected trigger. Please only use variables listed in the the variables box.", 'automatewoo' )
		]);

		wp_localize_script( 'automatewoo', 'automatewooLocalizeScript', [
			'url' => [
				'admin' => admin_url(),
				'ajax' => admin_url( 'admin-ajax.php' )
			],
			'locale' => [
				'month_abbrev' => array_values( $wp_locale->month_abbrev ),
				'currency_symbol' => get_woocommerce_currency_symbol(),
				'currency_position' => get_option( 'woocommerce_currency_pos' )
			]
		] );

	}


	/**
	 * Enqueue scripts based on screen id
	 */
	function enqueue_scripts() {

		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		wp_enqueue_script( 'automatewoo' );

		if ( in_array( $screen_id, $this->screen_ids() ) ) {
			wp_enqueue_script( 'woocommerce_admin' );
			wp_enqueue_script( 'wc-enhanced-select' );
			wp_enqueue_script( 'jquery-tiptip' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-autocomplete' );
			wp_enqueue_script( 'jquery-cookie' );
		}
	}



	/**
	 * Load styles earlier than scripts to avoid flash of un-styled workflows UI
	 */
	function enqueue_styles() {

		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		wp_register_style( 'automatewoo-main', AW()->admin_assets_url( '/css/aw-main.css' ), [], AW()->version );
		wp_enqueue_style( 'automatewoo-main' );

		if ( in_array( $screen_id, $this->screen_ids() ) ) {
			wp_enqueue_style( 'woocommerce_admin_styles' );
			wp_enqueue_style( 'jquery-ui-style' );
		}
	}


	function page_data_upgrade() {
		AW()->admin->get_view( 'page-data-upgrade' );
	}


	function license_notices() {

		if ( ! current_user_can( 'manage_woocommerce' ) )
			return;

		if ( AW()->licenses->has_expired_products() ) {

			if ( get_transient('aw_dismiss_licence_expiry_notice') )
				return; // notice has been dismissed

			$strong = __( 'Your AutomateWoo license has expired.', 'automatewoo' );
			$more = sprintf(
				__( '<a href="%s" target="_blank">Renew your license</a> to receive updates and support.', 'automatewoo' ),
				AW()->licenses->get_renewal_url(),
				$this->page_url( 'licenses' )
			);

			AW()->admin->notice('warning is-dismissible', $strong, $more, 'aw-notice-licence-renew' );
		}

		if ( AW()->licenses->has_unactivated_products() ) {

			if ( AW()->addons()->has_addons() ) {
				$strong = __( 'AutomateWoo - You have unactivated products.', 'automatewoo' );
			}
			else {
				$strong = __( 'AutomateWoo is not activated.', 'automatewoo' );
			}

			$more = sprintf(
				__( 'Please enter your <a href="%s">license here</a>.', 'automatewoo' ),
				$this->page_url( 'licenses' )
			);

			AW()->admin->notice( 'warning', $strong, $more );
		}
	}


	function screen_ids() {

		$ids = [];
		$prefix = 'automatewoo_page_automatewoo';

		$ids[] = "$prefix-logs";
		$ids[] = "$prefix-reports";
		$ids[] = "$prefix-settings";
		$ids[] = "$prefix-tools";
		$ids[] = "$prefix-carts";
		$ids[] = "$prefix-queue";
		$ids[] = "$prefix-guests";
		$ids[] = "$prefix-unsubscribes";
		$ids[] = 'aw_workflow';

		return apply_filters( 'automatewoo/admin/screen_ids', $ids );
	}


	/**
	 * @param $ids
	 * @return array
	 */
	function inject_woocommerce_reports_screen_ids( $ids ) {
		$ids[] = 'automatewoo_page_automatewoo-reports';
		return $ids;
	}


	/**
	 * @param $classes string
	 * @return string
	 */
	function body_class( $classes ) {
		if ( ! AW()->licenses->is_active() )
			$classes .= ' automatewoo-not-active ';

		return $classes;
	}


	/**
	 * @param $stylesheets
	 * @return array
	 */
	function add_editor_styles( $stylesheets ) {
		$stylesheets[] = AW()->admin_assets_url( '/css/editor.css' );
		return $stylesheets;
	}


	/**
	 * @param $view
	 * @param array $args
	 * @param mixed $path
	 */
	function get_view( $view, $args = [], $path = false ) {

		if ( $args && is_array( $args ) )
			extract( $args );

		if ( ! $path )
			$path = AW()->admin_path( '/views/' );

		include( $path . $view . '.php' );
	}


	/**
	 * @param $id
	 * @param $title
	 * @param $callback
	 * @param null $screen
	 * @param string $context
	 * @param string $priority
	 * @param null $callback_args
	 */
	function add_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null ) {
		$id = 'aw_' . $id;

		add_meta_box( $id, $title, $callback, $screen, $context, $priority, $callback_args );

		add_filter("postbox_classes_{$screen}_{$id}", array( $this, 'inject_postbox_class' ) );
	}


	/**
	 * @param $classes
	 *
	 * @return array
	 */
	function inject_postbox_class( $classes ) {
		$classes[] = 'automatewoo-metabox';
		$classes[] = 'no-drag';
		return $classes;
	}


	/**
	 * @param $type (warning,error,success)
	 * @param $strong
	 * @param string $more
	 * @param string $class
	 * @param string $button_text
	 * @param string $button_link
	 * @param string $button_class
	 */
	function notice( $type, $strong, $more = '', $class = '', $button_text = '', $button_link = '', $button_class = '' ) {
		?>
		<div class="notice notice-<?php echo $type ?> automatewoo-notice <?php echo $class ?>">
			<p>
				<strong><?php echo $strong; ?></strong> <?php echo $more; ?>
			</p>
			<?php if ($button_text): ?>
				<p><a href="<?php echo $button_link; ?>" class="button-primary <?php echo $button_class; ?>"><?php echo $button_text; ?></a></p>
			<?php endif; ?>
		</div>
		<?php
	}


	/**
	 * @param $page
	 * @return string
	 */
	function page_url( $page ) {

		switch ( $page ) {

			case 'dashboard':
				return admin_url( 'admin.php?page=automatewoo-dashboard' );
				break;

			case 'workflows':
				return admin_url( 'edit.php?post_type=aw_workflow' );
				break;

			case 'settings':
				return admin_url( 'admin.php?page=automatewoo-settings' );
				break;

			case 'licenses':
				return admin_url( 'admin.php?page=automatewoo-settings&tab=license' );
				break;

			case 'logs':
				return admin_url( 'admin.php?page=automatewoo-logs' );
				break;

			case 'queue':
				return admin_url( 'admin.php?page=automatewoo-queue' );
				break;

			case 'guests':
				return admin_url( 'admin.php?page=automatewoo-guests' );
				break;

			case 'email-tracking':
				return admin_url( 'admin.php?page=automatewoo-reports&tab=email-tracking' );
				break;

			case 'carts':
				return admin_url( 'admin.php?page=automatewoo-carts' );
				break;

			case 'unsubscribes':
				return admin_url( 'admin.php?page=automatewoo-unsubscribes' );
				break;

			case 'conversions':
				return admin_url( 'admin.php?page=automatewoo-reports&tab=conversions' );
				break;

			case 'conversions-list':
				return admin_url( 'admin.php?page=automatewoo-reports&tab=conversions-list' );
				break;

			case 'workflows-report':
				return admin_url( 'admin.php?page=automatewoo-reports&tab=runs-by-date' );
				break;

			case 'tools':
				return admin_url( 'admin.php?page=automatewoo-tools' );
				break;

			case 'system-check':
				return admin_url( 'admin.php?page=automatewoo-settings&tab=system-check' );
				break;
		}
	}


	/**
	 * @param $page
	 * @return bool
	 */
	function is_page( $page ) {

		$current_page = aw_clean( aw_request( 'page' ) );
		$current_tab = aw_clean( aw_request( 'tab' ) );

		switch ( $page ) {
			case 'dashboard':
				return $current_page == 'automatewoo-dashboard';
			break;
			case 'settings':
				return $current_page == 'automatewoo-settings';
				break;
			case 'reports':
				return $current_page == 'automatewoo-reports';
				break;
			case 'licenses':
				return $current_page == 'automatewoo-settings' && $current_tab == 'license';
				break;
		}
	}


	/**
	 * @param $vars array
	 */
	function get_hidden_form_inputs_from_query( $vars ) {
		foreach ( $vars as $var ) {
			if ( empty( $_GET[$var] ) )
				continue;

			echo '<input type="hidden" name="' . esc_attr( $var ) . '" value="' . esc_attr( $_GET[$var] ) . '">';
		}
	}



	/**
	 * @param $tip
	 * @param bool $allow_html
	 * @return string
	 */
	function help_tip( $tip, $allow_html = false ) {
		if ( $allow_html ) {
			$tip = wc_sanitize_tooltip($tip);
		}
		else {
			$tip = esc_attr($tip);
		}

		return '<span class="woocommerce-help-tip" data-tip="' . $tip . '"></span>';
	}



	function email_preview_loader() {
		header( "Expires: " . gmdate( "D, d M Y H:i:s", time() + DAY_IN_SECONDS ) . " GMT" );
		AW()->admin->get_view( 'email-preview-loader' );
		exit;
	}


}