<?php

/**
 * @param $param
 *
 * @return bool
 */
function aw_request( $param ) {
	if ( isset( $_REQUEST[$param] ) )
		return $_REQUEST[$param];

	return false;
}


/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 * @param string|array $var
 * @return string|array
 */
function aw_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'aw_clean', $var );
	}
	else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}


/**
 * @param $email
 * @return string
 */
function aw_clean_email( $email ) {
	return strtolower( sanitize_email( $email ) );
}


/**
 * @param $var
 * @return string
 */
function aw_clean_textarea( $var ) {
	return wp_strip_all_tags( wp_check_invalid_utf8( stripslashes( $var ) ) );
}


/**
 * @param $type string
 * @param $item
 *
 * @return mixed item of false
 */
function aw_validate_data_item( $type, $item ) {

	if ( ! $type || ! $item )
		return false;

	$valid = false;

	// Validate with the data type classes
	if ( $data_type = AW()->get_data_type( $type ) ) {
		$valid = $data_type->validate( $item );
	}

	/**
	 * @since 2.1
	 */
	$valid = apply_filters( 'automatewoo_validate_data_item', $valid, $type, $item );

	if ( $valid ) return $item;

	return false;
}



/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * This is much like wc_get_template() but won't fail if the default template file is missing
 *
 * @access public
 * @param string $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function aw_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

	if ( ! $template_path ) $template_path = 'automatewoo/';
	if ( ! $default_path ) $default_path = AW()->path( '/templates/' );

	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	$located = wc_locate_template( $template_name, $template_path, $default_path );

	if ( file_exists( $located ) ) {
		include( $located );
	}

}


/**
 * Function that returns an array containing the IDs of the recent products.
 *
 * @since 2.1.0
 *
 * @param int $limit
 *
 * @return array
 */
function aw_get_recent_product_ids( $limit = -1 ) {
	$recent = get_posts( [
		'post_type' => 'product',
		'posts_per_page' => $limit,
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'desc',
		'meta_query' => [
			[
				'key' => '_visibility',
				'value' 	=> [ 'catalog', 'visible' ],
				'compare' => 'IN'
			]
		],
		'fields' => 'ids'
	]);

	return $recent;
}


/**
 * @param $content string
 * @return string
 */
function aw_shorten_urls_in_content( $content ) {

	if ( ! AW_Url_Shortener::check() )
		return $content;

	$replacer = new AW_Replace_Helper( $content, [ 'AW_Url_Shortener', 'process' ], 'text_urls' );
	return $replacer->process();
}




/**
 * @param string $phone
 * @param string $country
 *
 * @return string
 */
function aw_parse_phone_number( $phone, $country = '' ) {

	if ( ! $country )  {
		$country = WC()->countries->get_base_country();
	}

	$phone = preg_replace( "/\([^)]+\)/", '', $phone ); // remove within brackets
	$phone = str_replace( array( '-', ' ', '.' ), '', $phone );

	// already international
	if ( ! strstr( $phone, '+' ) ) {
		$phone = ltrim( $phone, '0' ); // remove leading zero
		$area_code = aw_get_phone_area_code( $country );

		if ( $area_code ) {
			$phone = '+' . $area_code . $phone;
		}
	}

	return apply_filters( 'automatewoo_parse_phone_number', $phone );
}


/**
 * @param int $timestamp
 * @param bool|int $max_diff
 * @param bool $convert_from_gmt
 * @return string
 */
function aw_display_date( $timestamp, $max_diff = false, $convert_from_gmt = true ) {

	if ( is_string( $timestamp ) )
		$timestamp = strtotime( $timestamp );

	if ( $timestamp < 0 ) {
		return false;
	}

	if ( $convert_from_gmt ) {
		$timestamp = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $timestamp ), 'Y-m-d H:i:s' ) );
	}

	$now = current_time( 'timestamp' );

	if ( ! $max_diff ) $max_diff = WEEK_IN_SECONDS; // set default

	$diff = $timestamp - $now;

	if ( abs( $diff ) > $max_diff ) {
		return $date_to_display = date_i18n( 'Y/m/d', $timestamp );
	}

	if ( $diff > 0 ) {
		return sprintf( __( 'In %s', 'automatewoo' ), human_time_diff( $now, $timestamp ) );
	}
	else {
		return sprintf( __( '%s ago', 'automatewoo' ), human_time_diff( $now, $timestamp ) );
	}
}


/**
 * @param int $timestamp
 * @param bool|int $max_diff
 * @param bool $convert_from_gmt If its gmt convert it to site time
 * @return string|false
 */
function aw_display_time( $timestamp, $max_diff = false, $convert_from_gmt = true ) {

	if ( is_string( $timestamp ) )
		$timestamp = strtotime( $timestamp );

	if ( $timestamp < 0 ) {
		return false;
	}

	if ( $convert_from_gmt ) {
		$timestamp = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $timestamp ), 'Y-m-d H:i:s' ) );
	}

	$now = current_time( 'timestamp' );

	if ( ! $max_diff ) $max_diff = DAY_IN_SECONDS; // set default

	$diff = $timestamp - $now;

	if ( abs( $diff ) > $max_diff ) {
		return $date_to_display = date_i18n( 'Y/m/d ' . wc_time_format(), $timestamp );
	}

	if ( $diff > 0 ) {
		return sprintf( __( 'In %s', 'automatewoo' ), human_time_diff( $now, $timestamp ) );
	}
	else {
		return sprintf( __( '%s ago', 'automatewoo' ), human_time_diff( $now, $timestamp ) );
	}
}


/**
 * @return int
 */
function aw_get_user_count() {

	if ( $cache = AW()->cache()->get( 'user_count' ) )
		return $cache;

	global $wpdb;

	$count = absint( $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->users" ) );

	AW()->cache()->set( 'user_count', $count );

	return $count;
}


/**
 * Use if accuracy is not important, count is cached for a week
 * @return int
 */
function aw_get_user_count_rough() {

	if ( $cache = AW()->cache()->get( 'user_count_rough' ) )
		return $cache;

	global $wpdb;

	$count = absint( $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->users" ) );

	AW()->cache()->set( 'user_count_rough', $count, 168 );

	return $count;
}


/**
 * @param $length int
 * @return string
 */
function aw_generate_key( $length = 25 ) {

	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$password = '';

	for ( $i = 0; $i < $length; $i++ ) {
		$password .= substr($chars, wp_rand( 0, strlen($chars) - 1), 1);
	}

	return $password;
}


/**
 * @param $price
 * @return float
 */
function aw_price_to_float( $price ) {

	$price = html_entity_decode( str_replace(',', '.', $price ) );

	$price = preg_replace( "/[^0-9\.]/", "", $price );

	return (float) $price;
}


/**
 * @since 2.7.1
 * @return array
 */
function aw_get_counted_order_statuses() {
	return apply_filters( 'automatewoo/counted_order_statuses', [ 'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-pending' ] );
}


/**
 * @since 2.7.1
 * @param int $user_id
 * @return int
 */
function aw_get_customer_order_count( $user_id ) {
	$count = get_user_meta( $user_id, '_aw_order_count', true );
	if ( '' === $count ) {
		global $wpdb;

		$count = $wpdb->get_var( "SELECT COUNT(*)
			FROM $wpdb->posts as posts

			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id

			WHERE   meta.meta_key       = '_customer_user'
			AND     posts.post_type     IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "')
			AND     posts.post_status   IN ('" . implode( "','", aw_get_counted_order_statuses() )  . "')
			AND     meta_value          = $user_id
		" );

		update_user_meta( $user_id, '_aw_order_count', absint( $count ) );
	}

	return absint( $count );
}


/**
 * @param string $email
 * @return int
 */
function aw_get_order_count_by_email( $email ) {

	$email = aw_clean_email( $email );

	global $wpdb;

	$count = $wpdb->get_var( "SELECT COUNT(*)
		FROM $wpdb->posts as posts

		LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id

		WHERE   meta.meta_key       = '_billing_email'
		AND     posts.post_type     IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "')
		AND     posts.post_status   IN ('" . implode( "','", aw_get_counted_order_statuses() )  . "')
		AND     meta_value          = '$email'
	" );

	return absint( $count );
}



/**
 * @param  string $email
 * @return int
 */
function aw_get_total_spent_by_email( $email ) {

	$email = aw_clean_email( $email );

	global $wpdb;

	$spent = $wpdb->get_var( "SELECT SUM(meta2.meta_value)
		FROM $wpdb->posts as posts

		LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
		LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id

		WHERE   meta.meta_key       = '_billing_email'
		AND     meta.meta_value     = '$email'
		AND     posts.post_type     IN ('" . implode( "','", wc_get_order_types( 'reports' ) ) . "')
		AND     posts.post_status   IN ( 'wc-completed', 'wc-processing' )
		AND     meta2.meta_key      = '_order_total'
	" );

	return absint( $spent );
}


/**
 * @param $order WC_Order
 * @return array
 */
function aw_get_order_cross_sells( $order ) {

	$cross_sells = [];
	$in_order = [];

	$items = $order->get_items();

	foreach ( $items as $item )
	{
		$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
		$in_order[] = $product->id;
		$cross_sells = array_merge( $product->get_cross_sells(), $cross_sells );
	}

	return array_diff( $cross_sells, $in_order );
}


/**
 * @param $array
 * @param $value
 */
function aw_array_remove_value( &$array, $value ) {
	if ( ( $key = array_search( $value, $array ) ) !== false ) {
		unset( $array[$key] );
	}
}


/**
 * @param $country
 * @return string
 */
function aw_get_phone_area_code( $country ) {
	$area_codes = [
		'AC' => '247',
		'AD' => '376',
		'AE' => '971',
		'AF' => '93',
		'AG' => '1268',
		'AI' => '1264',
		'AL' => '355',
		'AM' => '374',
		'AO' => '244',
		'AQ' => '672',
		'AR' => '54',
		'AS' => '1684',
		'AT' => '43',
		'AU' => '61',
		'AW' => '297',
		'AX' => '358',
		'AZ' => '994',
		'BA' => '387',
		'BB' => '1246',
		'BD' => '880',
		'BE' => '32',
		'BF' => '226',
		'BG' => '359',
		'BH' => '973',
		'BI' => '257',
		'BJ' => '229',
		'BL' => '590',
		'BM' => '1441',
		'BN' => '673',
		'BO' => '591',
		'BQ' => '599',
		'BR' => '55',
		'BS' => '1242',
		'BT' => '975',
		'BW' => '267',
		'BY' => '375',
		'BZ' => '501',
		'CA' => '1',
		'CC' => '61',
		'CD' => '243',
		'CF' => '236',
		'CG' => '242',
		'CH' => '41',
		'CI' => '225',
		'CK' => '682',
		'CL' => '56',
		'CM' => '237',
		'CN' => '86',
		'CO' => '57',
		'CR' => '506',
		'CU' => '53',
		'CV' => '238',
		'CW' => '599',
		'CX' => '61',
		'CY' => '357',
		'CZ' => '420',
		'DE' => '49',
		'DJ' => '253',
		'DK' => '45',
		'DM' => '1767',
		'DO' => '1809',
		'DO' => '1829',
		'DO' => '1849',
		'DZ' => '213',
		'EC' => '593',
		'EE' => '372',
		'EG' => '20',
		'EH' => '212',
		'ER' => '291',
		'ES' => '34',
		'ET' => '251',
		'EU' => '388',
		'FI' => '358',
		'FJ' => '679',
		'FK' => '500',
		'FM' => '691',
		'FO' => '298',
		'FR' => '33',
		'GA' => '241',
		'GB' => '44',
		'GD' => '1473',
		'GE' => '995',
		'GF' => '594',
		'GG' => '44',
		'GH' => '233',
		'GI' => '350',
		'GL' => '299',
		'GM' => '220',
		'GN' => '224',
		'GP' => '590',
		'GQ' => '240',
		'GR' => '30',
		'GT' => '502',
		'GU' => '1671',
		'GW' => '245',
		'GY' => '592',
		'HK' => '852',
		'HN' => '504',
		'HR' => '385',
		'HT' => '509',
		'HU' => '36',
		'ID' => '62',
		'IE' => '353',
		'IL' => '972',
		'IM' => '44',
		'IN' => '91',
		'IO' => '246',
		'IQ' => '964',
		'IR' => '98',
		'IS' => '354',
		'IT' => '39',
		'JE' => '44',
		'JM' => '1876',
		'JO' => '962',
		'JP' => '81',
		'KE' => '254',
		'KG' => '996',
		'KH' => '855',
		'KI' => '686',
		'KM' => '269',
		'KN' => '1869',
		'KP' => '850',
		'KR' => '82',
		'KW' => '965',
		'KY' => '1345',
		'KZ' => '7',
		'LA' => '856',
		'LB' => '961',
		'LC' => '1758',
		'LI' => '423',
		'LK' => '94',
		'LR' => '231',
		'LS' => '266',
		'LT' => '370',
		'LU' => '352',
		'LV' => '371',
		'LY' => '218',
		'MA' => '212',
		'MC' => '377',
		'MD' => '373',
		'ME' => '382',
		'MF' => '590',
		'MG' => '261',
		'MH' => '692',
		'MK' => '389',
		'ML' => '223',
		'MM' => '95',
		'MN' => '976',
		'MO' => '853',
		'MP' => '1670',
		'MQ' => '596',
		'MR' => '222',
		'MS' => '1664',
		'MT' => '356',
		'MU' => '230',
		'MV' => '960',
		'MW' => '265',
		'MX' => '52',
		'MY' => '60',
		'MZ' => '258',
		'NA' => '264',
		'NC' => '687',
		'NE' => '227',
		'NF' => '672',
		'NG' => '234',
		'NI' => '505',
		'NL' => '31',
		'NO' => '47',
		'NP' => '977',
		'NR' => '674',
		'NU' => '683',
		'NZ' => '64',
		'OM' => '968',
		'PA' => '507',
		'PE' => '51',
		'PF' => '689',
		'PG' => '675',
		'PH' => '63',
		'PK' => '92',
		'PL' => '48',
		'PM' => '508',
		'PR' => '1787',
		'PR' => '1939',
		'PS' => '970',
		'PT' => '351',
		'PW' => '680',
		'PY' => '595',
		'QA' => '974',
		'QN' => '374',
		'QS' => '252',
		'QY' => '90',
		'RE' => '262',
		'RO' => '40',
		'RS' => '381',
		'RU' => '7',
		'RW' => '250',
		'SA' => '966',
		'SB' => '677',
		'SC' => '248',
		'SD' => '249',
		'SE' => '46',
		'SG' => '65',
		'SH' => '290',
		'SI' => '386',
		'SJ' => '47',
		'SK' => '421',
		'SL' => '232',
		'SM' => '378',
		'SN' => '221',
		'SO' => '252',
		'SR' => '597',
		'SS' => '211',
		'ST' => '239',
		'SV' => '503',
		'SX' => '1721',
		'SY' => '963',
		'SZ' => '268',
		'TA' => '290',
		'TC' => '1649',
		'TD' => '235',
		'TG' => '228',
		'TH' => '66',
		'TJ' => '992',
		'TK' => '690',
		'TL' => '670',
		'TM' => '993',
		'TN' => '216',
		'TO' => '676',
		'TR' => '90',
		'TT' => '1868',
		'TV' => '688',
		'TW' => '886',
		'TZ' => '255',
		'UA' => '380',
		'UG' => '256',
		'UK' => '44',
		'US' => '1',
		'UY' => '598',
		'UZ' => '998',
		'VA' => '379',
		'VA' => '39',
		'VC' => '1784',
		'VE' => '58',
		'VG' => '1284',
		'VI' => '1340',
		'VN' => '84',
		'VU' => '678',
		'WF' => '681',
		'WS' => '685',
		'XC' => '991',
		'XD' => '888',
		'XG' => '881',
		'XL' => '883',
		'XN' => '857',
		'XN' => '858',
		'XN' => '870',
		'XP' => '878',
		'XR' => '979',
		'XS' => '808',
		'XT' => '800',
		'XV' => '882',
		'YE' => '967',
		'YT' => '262',
		'ZA' => '27',
		'ZM' => '260',
		'ZW' => '263'
	];

	return isset( $area_codes[$country] ) ? $area_codes[$country] : '';
}
