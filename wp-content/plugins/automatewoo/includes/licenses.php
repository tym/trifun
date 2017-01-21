<?php
/**
 * Performs licence functions for AutomateWoo and any add-ons.
 * Must be loaded before main admin class.
 *
 * @class       AW_Licenses
 * @package     AutomateWoo
 */

class AW_Licenses {

	/**
	 * Add hooks
	 */
	function __construct() {
		add_action( 'admin_init', [ $this, 'maybe_check_statuses' ] );
	}


	/**
	 * Manage status check with transients instead of cron to ensure the check does not happen via frontend request
	 */
	function maybe_check_statuses() {

		if ( AW()->is_request('ajax') )
			return;

		$transient_name = 'automatewoo_license_status_checked';

		if ( get_transient( $transient_name ) )
			return;

		$this->check_statuses();

		set_transient( $transient_name, true, DAY_IN_SECONDS * 4 );
	}



	/**
	 * @return array|false
	 */
	function get_primary_license() {

		if ( ! $license = get_option( 'automatewoo_license' ) )
			return false;

		$license = maybe_unserialize( base64_decode( $license ) );
		return $license;
	}


	/**
	 * @return array
	 */
	function get_addon_licenses() {

		if ( ! $licenses = get_option( 'automatewoo_addon_licenses' ) )
			return [];

		return $licenses;
	}


	/**
	 * @param $product_id
	 * @return array|false
	 */
	function get_license( $product_id ) {

		if ( $this->is_primary( $product_id ) ) {
			return $this->get_primary_license();
		}
		else {
			$licenses = $this->get_addon_licenses();

			if ( ! isset( $licenses[ $product_id ] ) )
				return false;

			return $licenses[ $product_id ];
		}
	}


	/**
	 * @param $product_id
	 * @return bool
	 */
	function is_primary( $product_id ) {
		return $product_id == AW()->plugin_slug;
	}


	/**
	 * Returns true if product license is active or expired
	 *
	 * @param $product_id
	 * @return bool
	 */
	function is_active( $product_id = false ) {

		if ( ! $product_id )
			$product_id = AW()->plugin_slug;

		// If license exists then its active
		if ( $this->get_license( $product_id ) )
			return true;
	}


	/**
	 * @param $product_id
	 * @return bool
	 */
	function is_expired( $product_id ) {

		$license = $this->get_license( $product_id );

		// no license at all
		if ( ! $license )
			return false;

		$date = new DateTime( $license['expiry'] );
		return current_time( 'timestamp' ) > $date->format( 'U' );
	}


	/**
	 * @param string $product_id
	 * @param string $key
	 * @param bool|string $expiry
	 */
	function update( $product_id, $key, $expiry = false ) {

		if ( $this->is_primary( $product_id ) ) {
			$license = [
				'key' => $key,
				'url' => $this->get_instance()
			];

			if ( $expiry ) $license['expiry'] = $expiry;

			$license = base64_encode( maybe_serialize($license) );
			update_option( 'automatewoo_license', $license );
		}
		else {
			$licenses = $this->get_addon_licenses();

			$licenses[$product_id] = [
				'key' => $key,
				'expiry' => $expiry
			];

			update_option( 'automatewoo_addon_licenses', $licenses, true );
		}
	}


	/**
	 * Remove the licence
	 * @param $product_id
	 */
	function remove( $product_id ) {

		if ( $this->is_primary( $product_id ) ) {
			delete_option('automatewoo_license');
		}
		else {
			$licenses = $this->get_addon_licenses();
			unset( $licenses[$product_id] );
			update_option( 'automatewoo_addon_licenses', $licenses, true );
		}
	}


	/**
	 *
	 */
	function remove_all() {
		delete_option( 'automatewoo_license' );
		delete_option( 'automatewoo_addon_licenses' );
	}


	/**
	 *
	 */
	function check_for_domain_mismatch() {

		$license = $this->get_primary_license();

		if ( ! $license )
			return;

		$instance_url = $this->get_instance();
		$license_url = $license['url'];

		if ( ! $instance_url || ! $license_url )
			return;

		if ( $license_url != $instance_url ) {
			// remove and but don't deactivate, install may have been cloned
			$this->remove_all();
		}
	}


	/**
	 * Checks the status of licence and performs any required actions based on the status
	 *
	 * @todo check dev status
	 */
	function check_statuses() {

		$apps = [];

		if ( $license = $this->get_primary_license() ) {
			$apps[] = [
				'id' => AW()->plugin_slug,
				'key' => $license['key'],
				'version' => AW()->version
			];
		}

		foreach ( AW()->addons()->get_all() as $addon ) {
			if ( $license = $this->get_license( $addon->id ) ) {
				$apps[] = [
					'id' => $addon->id,
					'key' => $license['key'],
					'version' => $addon->version
				];
			}
		}

		// no apps need checking
		if ( empty( $apps ) )
			return false;

		global $wp_version;

		$response = $this->remote_get( 'multi_app_status_check', [
			'domain' => $this->get_instance(),
			'wp_version' => $wp_version,
			'wc_version' => WC()->version,
			'locale' => get_locale(),
			'apps' => $apps
		]);

		if ( ! $response || ! $response->success )
			return false;

		if ( $response->apps ) foreach ( $response->apps as $app_id => $app ) {

			if ( ! $license = $this->get_license( $app_id ) )
				continue;

			switch ( $app->licence_status ) {
				case 'valid':
				case 'valid-dev':
				case 'expired':
					$this->update( $app_id, $license['key'], $app->licence_expires );
					break;

				case 'deactivated':
				case 'invalid':
					$this->remove( $app_id );
					break;
			}
		}
	}


	/**
	 * Remotely activate a license
	 * Sets updates the license option if license is activate.
	 *
	 * @param $product_id string
	 * @param $license_key string
	 *
	 * @return false|WP_Error|string
	 */
	function remote_activate( $product_id, $license_key ) {

		$response = $this->remote_get( 'activation', [
			'app_id' => $product_id,
			'licence_key' => $license_key,
			'instance' => $this->get_instance()
		]);

		if ( ! $response )
			return false;

		if ( isset( $response->error ) ) {
			return new WP_Error( '1', $response->error );
		}

		if ( isset( $response->activated ) ) {
			$this->update( $product_id, $license_key, $response->licence_expires );
			return $response->message;
		}

		return false;
	}


	/**
	 * @param $product_id
	 * @return bool|WP_Error
	 */
	function remote_deactivate( $product_id ) {

		if ( $this->is_primary( $product_id ) ) {

			$license = $this->get_primary_license();

			if ( $license ) {
				// Attempt deactivation
				$this->remote_get( 'deactivation', [
					'licence_key' => $license['key'],
					'instance' => $license['url']
				]);
			}
		}
		else {
			$license_info = $this->get_license( $product_id );

			if ( $license_info ) {
				// Attempt deactivation
				$this->remote_get( 'deactivation', [
					'app_id' => $product_id,
					'licence_key' => $license_info['key'],
					'instance' => $this->get_instance()
				]);
			}
		}

		$this->remove( $product_id ); // remove anyway
	}



	/**
	 * @return bool|WP_Error
	 */
	function is_valid_dev_domain() {

		if ( $cache = AW()->cache()->get( 'is_dev_domain' ) ) {
			return $cache === 'yes';
		}

		$check = $this->remote_check_dev_domain();

		if ( is_wp_error( $check ) )
			return $check;

		AW()->cache()->set( 'is_dev_domain', $check ? 'yes' : 'no' );

		return $check;
	}



	/**
	 * @return bool|WP_Error
	 */
	function remote_check_dev_domain() {

		$response = $this->remote_get( 'is_dev_domain', [
			'domain' => $this->get_instance(),
		]);

		if ( $response && $response->success ) {
			return $response->is_dev;
		}
		else {
			return new WP_Error( 1, __( 'Could not connect to remote server to check domain status.', 'automatewoo' ) );
		}
	}


	/**
	 *
	 */
	function get_instance() {
		$url = site_url();
		$url = str_replace( [ 'http://', 'https://' ], '', $url );
		$url = untrailingslashit($url);
		return $url;
	}


	/**
	 * @return string
	 */
	function get_renewal_url() {
		return AW()->website_url . 'account/';
	}


	/**
	 * @return bool
	 */
	function has_expired_products() {

		if ( $this->is_expired( AW()->plugin_slug ) )
			return true;

		$addons = $this->get_addon_licenses();

		foreach ( $addons as $id => $addon ) {
			if ( $this->is_expired( $id ) )
				return true;
		}

		return false;
	}


	/**
	 * @return bool
	 */
	function has_unactivated_products() {

		if ( ! $this->is_active( AW()->plugin_slug ) )
			return true;

		$addons = AW()->addons()->get_all();

		foreach ( $addons as $id => $addon ) {
			if ( ! $this->is_active( $id ) )
				return true;
		}

		return false;
	}



	/**
	 * @param $request
	 * @param $args
	 *
	 * @return object|false
	 */
	function remote_get( $request, $args = [] ) {

		$base = [
			'wc-api' => 'licences',
			'app_id' => AW()->plugin_slug
		];

		$base['request'] = $request;
		$args = array_merge( $base, $args );

		$request_url = add_query_arg( $args, AW()->website_url );

		$response = wp_safe_remote_get( $request_url, [ 'timeout' => 30 ] );

		if ( $response instanceof WP_Error || ! isset( $response['body'] ) ) {
			return false;
		}

		return json_decode( $response['body'] );
	}

}
