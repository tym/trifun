<?php

$settings = array(

	'settings'  => array(

		'general-options' => array(
			'title' => __( 'General Options', 'yith-woocommerce-product-bundles' ),
			'type' => 'title',
			'desc' => '',
			'id' => 'yith-wcpb-general-options'
		),

        'show-bundled-items-in-report' => array(
            'id'        => 'yith-wcpb-show-bundled-items-in-report',
            'name'      => __( 'Show bundled items in Reports', 'yith-woocommerce-product-bundles' ),
            'type'      => 'checkbox',
            'desc'      => __( 'Flag this option to show also the bundled items in WooCommerce Reports.', 'yith-woocommerce-product-bundles' ),
            'default'   => 'no'
        ),

		'hide-bundled-items-in-cart' => array(
				'id'        => 'yith-wcpb-hide-bundled-items-in-cart',
				'name'      => __( 'Hide bundled items in Cart and Checkout', 'yith-woocommerce-product-bundles' ),
				'type'      => 'checkbox',
				'desc'      => __( 'Flag this option to hide the bundled items in WooCommerce Cart and Checkout.', 'yith-woocommerce-product-bundles' ),
				'default'   => 'no'
		),

		'general-options-end' => array(
			'type'      => 'sectionend',
			'id'        => 'yith-wcqv-general-options'
		)

	)
);

return apply_filters( 'yith_wcpb_panel_settings_options', $settings );