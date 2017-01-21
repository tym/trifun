<?php
return array(
	'order-tracking' => array(
		'name'              => 'YITH WooCommerce Order Tracking',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-order-tracking/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to manage order tracking', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YITH_YWOT_PREMIUM',
		'installed_version' => 'YITH_YWOT_VERSION',
		'min_version'       => '1.1.9',
		'compare'           => '>='
	),

	'subscription' => array(
		'name'              => 'YITH WooCommerce Subscription',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-subscription/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage Subscription products', 'yith-woocommerce-product-vendors' ),
		'post_types'        => array( 'ywsbs_subscription' ),
		'capabilities'      => apply_filters( 'yith_wcmv_subscription_caps', array( 'edit_subscription' => true ) ),
		'premium'           => 'YITH_YWSBS_PREMIUM',
		'installed_version' => 'YITH_YWSBS_VERSION',
		'min_version'       => '1.0.0',
		'compare'           => '>='
	),

	'name-your-price' => array(
		'name'              => 'YITH WooCommerce Name Your Price',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-name-your-price/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage name your price products', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YWCNP_PREMIUM',
		'installed_version' => 'YWCNP_VERSION',
		'min_version'       => '1.0.0',
		'compare'           => '>='
	),

	'size-charts' => array(
		'name'              => 'YITH Product Size Charts for WooCommerce',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-product-size-charts-for-woocommerce/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to add product size charts for their own products', 'yith-woocommerce-product-vendors' ),
		'post_types'        => apply_filters( 'yith_wcpsc_vendor_allowed_post_types', array( 'yith-wcpsc-wc-chart' ) ),
		'capabilities'      => apply_filters( 'yith_wcpsc_vendor_allowed_caps', yith_wcmv_create_capabilities( array(
			'size_chart',
			'size_charts'
		) ) ),
		'premium'           => 'YITH_WCPSC_PREMIUM',
		'installed_version' => 'YITH_WCPSC_VERSION',
		'min_version'       => '1.0.6',
		'compare'           => '>='
	),

	'membership' => array(
		'name'              => 'YITH WooCommerce Membership',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-membership/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage memberships for their own customers', 'yith-woocommerce-product-vendors' ),
		'post_types'        => apply_filters( 'yith_wcmbs_vendor_allowed_post_types', array( 'yith-wcmbs-plan' ) ),
		'capabilities'      => apply_filters( 'yith_wcmbs_vendor_allowed_caps', yith_wcmv_create_capabilities( array(
			'plan',
			'plans'
		) ) ),
		'premium'           => 'YITH_WCMBS_PREMIUM',
		'installed_version' => 'YITH_WCMBS_VERSION',
		'min_version'       => '1.0.4',
		'compare'           => '>='
	),

	'live-chat' => array(
		'name'              => 'YITH Live Chat',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-live-chat/',
		'option_desc'       => __( 'If you enable this option, each vendor will be able to chat with their customers directly', 'yith-woocommerce-product-vendors' ),
		'post_types'        => array( 'ylc-macro' ),
		'capabilities'      => apply_filters( 'yith_ylc_vendor_caps', yith_wcmv_create_capabilities( array(
			'ylc-macro',
			'ylc-macros'
		) ) ),
		'premium'           => 'YLC_PREMIUM',
		'installed_version' => 'YLC_VERSION',
		'min_version'       => '1.0.5',
		'compare'           => '>='
	),

	'waiting-list' => array(
		'name'              => 'YITH WooCommerce Waiting List',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-waiting-list/',
		'option_desc'       => __( 'If you enable this option, each vendor will be able to manage their waiting lists and send mail to their customers.', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YITH_WCWTL_PREMIUM',
		'installed_version' => 'YITH_WCWTL_VERSION',
		'min_version'       => '1.0.6',
		'compare'           => '>='
	),

	'surveys' => array(
		'name'              => 'YITH WooCommerce Surveys',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-surveys/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage surveys for their own customers', 'yith-woocommerce-product-vendors' ),
		'post_types'        => apply_filters( 'yith_wc_surveys_vendor_allowed_post_types', array( 'yith_wc_surveys' ) ),
		'capabilities'      => apply_filters( 'yith_wc_surveys_vendor_allowed_caps', yith_wcmv_create_capabilities( array(
			'survey',
			'surveys'
		) ) ),
		'premium'           => 'YITH_WC_SURVEYS_PREMIUM',
		'installed_version' => 'YITH_WC_SURVEYS_VERSION',
		'min_version'       => '1.0.1',
		'compare'           => '>='
	),

	'badge-management' => array(
		'name'              => 'YITH WooCommerce Badge Management',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-badge-management/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage badges for their own products', 'yith-woocommerce-product-vendors' ),
		'post_types'        => apply_filters( 'yith_wcbm_vendor_allowed_post_types', array( 'yith-wcbm-badge' ) ),
		'capabilities'      => apply_filters( 'yith_wcbm_vendor_allowed_caps', yith_wcmv_create_capabilities( array(
			'badge',
			'badges'
		) ) ),
		'premium'           => 'YITH_WCBM_PREMIUM',
		'installed_version' => 'YITH_WCBM_VERSION',
		'min_version'       => '1.2.3',
		'compare'           => '>='
	),

	'review-discounts' => array(
		'name'              => 'YITH WooCommerce Review For Discounts',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-review-for-discounts/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to create and manage discounts for their own customers', 'yith-woocommerce-product-vendors' ),
		'post_types'        => array( 'ywrfd-discount' ),
		'capabilities'      => apply_filters( 'yith_wrfd_vendor_caps', yith_wcmv_create_capabilities( array(
			'ywrfd-discount',
			'ywrfd-discounts'
		) ) ),
		'premium'           => 'YWRFD_PREMIUM',
		'installed_version' => 'YWRFD_VERSION',
		'min_version'       => '1.0.0',
		'compare'           => '>=',
	),

	'coupon-email-system' => array(
		'name'              => 'YITH WooCommerce Coupon Email System',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-coupon-email-system/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to create custom coupon and send it by email for their own customers', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YWCES_PREMIUM',
		'installed_version' => 'YWCES_VERSION',
		'min_version'       => '1.0.5',
		'compare'           => '>='
	),

	'pdf-invoice' => array(
		'name'              => 'YITH WooCommerce PDF Invoice',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-pdf-invoice/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to create invoice for their order. This feature require that vendor are able to manage their order individually', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YITH_YWPI_PREMIUM',
		'installed_version' => 'YITH_YWPI_VERSION',
		'min_version'       => '1.3.0',
		'compare'           => '>=',
	),

	'request-quote' => array(
		'name'              => 'YITH WooCommerce Request a quote',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-request-a-quote/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to receive and manage their order quote. This feature require that vendor are able to manage their order individually', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YITH_YWRAQ_PREMIUM',
		'installed_version' => 'YITH_YWRAQ_VERSION',
		'min_version'       => '1.4.0',
		'compare'           => '>=',
	),

	'catalog-mode' => array(
		'name'              => 'YITH WooCommerce Catalog Mode',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-catalog-mode/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to manage the selling of their products.', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YWCTM_PREMIUM',
		'installed_version' => 'YWCTM_VERSION',
		'min_version'       => '1.3.0',
		'compare'           => '>=',
	),

	'role-based-prices' => array(
		'name'              => 'YITH WooCommerce Role Based Prices',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-role-based-prices/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to create custom price rules for their own products.', 'yith-woocommerce-product-vendors' ),
		'post_types'        => array( 'yith_price_rule' ),
		'capabilities'      => apply_filters( 'yith_wrbp_vendor_caps', yith_wcmv_create_capabilities( array(
			'price_rule',
			'price_rules'
		) ) ),
		'premium'           => 'YWCRBP_PREMIUM',
		'installed_version' => 'YWCRBP_VERSION',
		'min_version'       => '1.0.0',
		'compare'           => '>=',
	),

	'advanced-product-options' => array(
		'name'              => 'YITH WooCommerce Product Add-ons',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-advanced-product-options/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to create advanced product options for their products.', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YITH_WAPO_PREMIUM',
		'installed_version' => 'YITH_WAPO_VERSION',
		'min_version'       => '1.0.0',
		'compare'           => '>=',
	),
	'sms-notifications'        => array(
		'name'              => 'YITH WooCommerce SMS Notifications',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-sms-notifications/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to SMS notifications for their orders.', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YWSN_PREMIUM',
		'installed_version' => 'YWSN_VERSION',
		'min_version'       => '1.0.3',
		'compare'           => '>=',
	),

	'bulk-product-editing' => array(
		'name'              => 'YITH WooCommerce Bulk Product Editing',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-bulk-product-editing/',
		'option_desc'       => __( 'If you enable this option, vendors will be able to access to bulk product editing for their products.', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YITH_WCBEP_PREMIUM',
		'installed_version' => 'YITH_WCBEP_VERSION',
		'min_version'       => '1.1.23',
		'compare'           => '>=',
	),

//	'best-price' => array(
//		'name'              => 'YITH Best Price Guaranteed for WooCommerce',
//		'landing_uri'       => '//yithemes.com/themes/plugins/yith-best-price-guaranteed-for-woocommerce/',
//		'option_desc'       => __( 'If you enable this option, vendors will be able to manage best price notifications for products', 'yith-woocommerce-product-vendors' ),
//		'post_types'        => array( 'yith_best_price' ),
//		'capabilities'      => yith_wcmv_create_capabilities( 'yith_best_price' ),
//		'premium'           => 'YITH_WCBPG_PREMIUM',
//		'installed_version' => 'YITH_WCBPG_VERSION',
//		'min_version'       => '1.0.2',
//		'compare'           => '>=',
//		'coming_soon'       => true,
//	),

	'product-bundles' => array(
		'name'              => 'YITH WooCommerce Product Bundles',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-product-bundles/',
		'option_desc'       => __( 'Vendors can create bundle products', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YITH_WCPB_PREMIUM',
		'installed_version' => 'YITH_WCPB_VERSION',
		'min_version'       => '1.1.3',
		'compare'           => '>=',
		'is_new'            => true,
	),

	'eu-energy-label' => array(
		'name'              => 'YITH WooCommerce EU Energy Label',
		'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-eu-energy-label/',
		'option_desc'       => __( 'Vendors can add a label on their products with their energy class', 'yith-woocommerce-product-vendors' ),
		'premium'           => 'YITH_WCEUE_PREMIUM',
		'installed_version' => 'YITH_WCEUE_VERSION',
		'min_version'       => '1.0.5',
		'compare'           => '>=',
		'is_new'            => true,
	),
    'paypal-adaptive' => array(
        'name'              => 'YITH PayPal Adaptive Payments for WooCommerce',
        'landing_uri'       => '//yithemes.com/themes/plugins/yith-woocommerce-paypal-adaptive-payments/',
        'option_desc'       => __( 'The vendor commissions are paid automatically by Adaptive Payments system', 'yith-woocommerce-product-vendors' ),
        'premium'           => 'YITH_PAYPAL_ADAPTIVE_PREMIUM',
        'installed_version' => 'YITH_PAYPAL_ADAPTIVE_VERSION',
        'min_version'       => '1.0.0',
        'compare'           => '>=',
        'is_new'            => true,
    ),
);