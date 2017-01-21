<?php
/**
 * Updater
 *
 * @class       AW_Updater
 * @package     AutomateWoo
 */

class AW_Updater {

	/**
	 * Add actions
	 */
	static function init() {

		add_filter( 'plugins_api', array( __CLASS__, 'check_info' ), 10, 3 );
		add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'inject_update' ) );
		add_filter( 'pre_set_site_transient_update_plugins', array( __CLASS__, 'inject_addon_updates' ) );
		add_action( 'in_plugin_update_message-' . AW()->plugin_basename, array( __CLASS__, 'in_plugin_update_message' ) );

		foreach ( AW()->addons()->get_all() as $addon ) {
			add_action( 'in_plugin_update_message-' . $addon->plugin_basename, array( __CLASS__, 'in_plugin_update_message' ) );
		}

	}


	/**
	 * Check for updates
	 *
	 * @param $transient
	 *
	 * @return
	 */
	static function inject_update( $transient ) {

		if ( empty( $transient->checked ) )
			return $transient;

		if ( ! $version = self::update_available( AW()->plugin_slug ) )
			return $transient;

		$obj = new stdClass();
		$obj->slug = AW()->plugin_slug;
		$obj->plugin = AW()->plugin_basename;
		$obj->new_version = $version;
		$obj->url = AW()->website_url;

		if ( AW()->licenses->is_active( AW()->plugin_slug ) && ! AW()->licenses->is_expired( AW()->plugin_slug ) ) {

			$license = AW()->licenses->get_primary_license();

			$package_url = add_query_arg([
				'wc-api' => 'licences',
				'request' => 'app_download',
				'licence_key' => $license['key'],
				'instance' => urlencode( AW()->licenses->get_instance() ),
				'app_id' => AW()->plugin_slug
			], AW()->website_url );

			$obj->package = $package_url;
		}
		else {
			$obj->upgrade_notice = '<div class="woothemes-updater-plugin-upgrade-notice">' . __( "To enable updates please enter your license key on the WooCommerce &gt; Settings &gt; AutomateWoo page.", 'automatewoo' ) . '</div>';
		}

		if ( $transient ) {
			$transient->response[ AW()->plugin_basename ] = $obj;
			return $transient;
		}
	}


	/**
	 * @param $transient
	 */
	static function inject_addon_updates( $transient ) {

		if ( empty( $transient->checked ) )
			return $transient;

		$addons = AW()->addons()->get_all();

		foreach ( $addons as $addon ) {

			if ( ! $version = self::update_available( $addon->id ) )
				continue;

			$obj = new stdClass();
			$obj->slug = $addon->plugin_slug;
			$obj->plugin = $addon->plugin_basename;
			$obj->new_version = $version;
			$obj->url = AW()->website_url;

			if ( AW()->licenses->is_active( $addon->id ) && ! AW()->licenses->is_expired( $addon->id ) ) {

				$license = AW()->licenses->get_license( $addon->id );

				$package_url = add_query_arg([
					'wc-api' => 'licences',
					'request' => 'app_download',
					'licence_key' => $license['key'],
					'instance' => urlencode( AW()->licenses->get_instance() ),
					'app_id' => $addon->id
				], AW()->website_url );

				$obj->package = $package_url;
			}
			else {
				$obj->upgrade_notice = '<div class="woothemes-updater-plugin-upgrade-notice">' . __( "To enable updates please enter your license key on the WooCommerce &gt; Settings &gt; AutomateWoo page.", 'automatewoo' ) . '</div>';
			}

			if ( $transient ) {
				$transient->response[ $addon->plugin_basename ] = $obj;
			}
		}

		return $transient;
	}


	/**
	 * Check if there is a new version regardless of licence type.
	 *
	 * @param $product_id
	 * @return bool
	 */
	static function update_available( $product_id ) {

		$args = array(
			'app_id' => $product_id,
			'instance' => AW()->licenses->get_instance()
		);

		if ( AW()->licenses->is_primary( $product_id ) ) {
			$version = AW()->version;
		}
		else {
			$addon = AW()->addons()->get( $product_id );
			$version = $addon->version;
		}

		if ( AW()->licenses->is_active( $product_id ) && ! AW()->licenses->is_valid_dev_domain() ) {

			if ( AW()->licenses->is_primary( $product_id ) ) {
				$license = AW()->licenses->get_primary_license();
				$key = $license['key'];
			}
			else {
				$license = AW()->licenses->get_license( $product_id );
				$key = $license['key'];
			}

			$args['licence_key'] = $key;
			$args['version'] = $version;
		}

		$response = AW()->licenses->remote_get( 'app_version', $args );

		if ( $response );{
			if ( $response->success ) {

				if ( version_compare( $response->version, $version, '<=' ) ) {
					return false;
				}

				return $response->version;
			}
		}
	}


	/**
	 * @param $plugin_data array
	 */
	static function in_plugin_update_message( $plugin_data ) {

		$product_id = $plugin_data['slug'];

		$m = false;

		if ( AW()->licenses->is_expired( $product_id ) ) {
			$m = sprintf(
				__('<strong>Your license has expired.</strong> <a href="%s" target="_blank">Renew your license</a> to receive updates and support.', 'automatewoo'),
				AW()->licenses->get_renewal_url()
			);
		}
		elseif ( ! AW()->licenses->is_active( $product_id ) ) {
			$m = sprintf(
				__('To enable updates, please enter your license key on the <a href="%s"><strong>AutomateWoo Settings</strong></a> page.', 'automatewoo'),
				AW()->admin->page_url( 'licenses' ),
				AW()->website_url
			);
		}

		if ( ! $m ) return;

		echo '<br /><div class="woothemes-updater-plugin-upgrade-notice">' . $m . '</div>';
	}


	/**
	 * @param $false
	 * @param $action
	 * @param $arg
	 *
	 * @return mixed
	 */
	static function check_info( $false, $action, $arg ) {

		if ( isset( $arg->slug ) && $arg->slug === AW()->plugin_slug ) {
			if ( $response = AW()->licenses->remote_get( 'app_wp_plugin_info' ) ) {
				if ( $response->success ) {
					$info = $response->info;

					$info->sections = get_object_vars( $info->sections );
					return $info;
				}
			}
		}
		return false;
	}

}
