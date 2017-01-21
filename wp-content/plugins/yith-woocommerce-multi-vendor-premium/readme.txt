== YITH WooCommerce Multi Vendor Premium ===

Contributors: yithemes
Tags: product vendors, vendors, vendor, multi store, multi vendor, multi seller, woocommerce product vendors, woocommerce multi vendor, commission rate, seller, shops, vendor shop, vendor system, woo vendors, wc vendors, e-commerce, yit, yith, yithemes
Requires at least: 4.0
Tested up to: 4.6.1
Stable tag: 1.9.17
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Multi vendor e-commerce is a new idea of e-commerce platform that is becoming more and more popular in the web.

== Changelog ==

= 1.9.17 =

* Fixed: Vendor can see all trashed orders
* Fixed: Quick info widget dosn't show customer name and customer email

= 1.9.16 =

* Added: Body class yith_wcmv_user_is_vendor if current logged in user is a vendor
* Added: Body class yith_wcmv_user_is_vendor if current logged in user is not a vendor
* Added: yith_wcmv_product_commission_field_args hook
* Added: Option to remove the "Register as a vendor" and login form from "Become a vendor" page
* Added: Enable vendors to add admins
* Fixed: Unable to filter by attributes with pending vendor profile and YITH WooCommerce Ajax Product Filter plugin
* Fixed: Wrong processing order count in WordPress menu
* Fixed: Data range doesn't works in "Commissions by vendor" report
* Fixed: Can't translate vendor vacation module with multi lingual plugins
* Fixed: Double vacation message on variable products
* Fixed: Prevent error on activation for add_cap and WP_Role object
* Fixed: Website admin can't receive copy of quick info email
* Fixed: Order sync option doesn't work
* Fixed: Vendor order can't trigger correct action after order status changed
* Fixed: Shipping and Delivery event doesn't added in to calendar
* Fixed: Call undefined function get_current_screen() on plugin activation
* Fixed: Sales by Vendor reports doesn't exclude on-hold, cancelled and refunded orders

= 1.9.15 =

* Added: Support to YITH WooCommerce SMS Notifications
* Added: Support to YITH WooCommerce Bulk Product Editing
* Added: Resend order email for vendor in order detail page
* Fixed: Conflict between product limit and other custom post type
* Fixed: User can't add chat macro if user limit are enabled


= 1.9.14 =

* Added: New report "Commission by vendor" in WooCommerce -> Reports -> Vendors
* Fixed: Change text domain from 'yith_wc_product_vendors' to 'yith-woocommerce-product-vendors'
* Fixed: Vendor list shortcodes per_page=-1 arg doesn't works
* Fixed: No shipping address in order from RAQ
* Fixed: Vendor store header doesn't show with filtered url
* Fixed: Prevent to send SMS for vendr suborder with YITH WooCommerce SMS Notifications

= 1.9.13 =

* Added: Support to Adventure Tours theme
* Added: Set any product on which a vendor applies a change to “Pending review” status
* Fixed: Vendor store description with advanced editor doesn't save html tags
* Fixed: Vendor can change assign product to other vendors in bulk edit
* Fixed: Duplicated email to customer if an order are set to on-hold status
* Fixed: No shipping address in vendor email
* Fixed: Vacation icon doesn't appears on WooCommerce 2.6

= 1.9.12 =

* Added: Store description in vendor list shortcode
* Fixed: Duplicated download permissions with WooCommerce 2.6

= 1.9.11 =

* Added: Commission information to order line items
* Fixed: warning on vendor dashboard with WP User Avatar plugin
* Fixed: Unable to translate Address field placeholder
* Fixed: When you disabled/enabled the new order email, the vendor new order email will disabled too.
* Fixed: Wrong Google maps API Key in widget Vendor Store Location
* Fixed: Empty extra fields in vendor suborder with Bakery Theme
* Fixed: Missing product variations and taxes in vendor suborder after save main order

= 1.9.10 =

* Added: Support to extra order fields
* Added: Support to YITH WooCommerce Checkout Manager
* Fixed: Wrong sales report for website admin
* Fixed: Disable line item edit in vendor details
* Fixed: Stripe credit card refund issue when an order change status to complete

= 1.9.9 =

* Fixed: The message "X vendor shops have no owner set" is always shown in backend
* Fixed: Wrong action args in order's email
* Fixed: No vendor products in shop loop

= 1.9.8 =

* Added: Support to WooCommerce 2.6-beta-2
* Added: yith_wcmv_tax_label_frontend hook
* Added: yith_wcmv_tax_label_admin hook
* Added: Google maps api key support
* Added: Support to WooCommerce Customer/Order CSV Export
* Added: Support to WordPress User Frontend
* Added: Assign vendor to product with Bulk Edit
* Added: Assign vendor to product with Quick Edit
* Tweak: Support to WooCommerce 2.6 icon set
* Fixed: 404 not found error after change the slug of vendor store
* Fixed: On order complete the customer receive a duplicate email
* Fixed: Vendor with no owner abort ajax checkout in frontend
* Fixed: Wrong style in vendor list shortcodes with gravatar image
* Fixed: Wrong position of page content and "Vendor list" shortcode on frontend
* Fixed: Vendor store page VAT/SSN layout issue

= 1.9.7 =

* Updated: Language files

= 1.9.6 =

* Added: yith_wcmv_before_vendor_header e yith_wcmv_after_vendor_header hooks for vendor store page
* Added: Media gallery for vendors
* Added: Vendors to navigation menus
* Added: New option for order and orderby for vendors list shortcodes
* Added: Support to YITH WooCommerce Role Based Price Premium
* Added: Support to YITH WooCommerce Advanced Product Options Premium
* Added: Support to WP User Avatar plugin
* Added: yith_wcmv_hide_vendor_profile hook, use this to remove Vendor Details page in vendor dashboard
* Tweak: Vendor can't manage essential grid metabox in edit product
* Tweak: Widget quick info send the email to owner if no store email was set
* Fixed: Featured Products management doesn't work for vendor
* Fixed: Warning on order status not found in commissions report
* Fixed: Vacation module issue on frontend
* Fixed: Wrong product count in vendor screen
* Fixed: Wrong shop order counts
* Fixed: Class YITH_Addons doesn't exists in vendor dashboard
* Fixed: Blank "become a vendor" page for not logged in users
* Fixed: WooCommerce dashboard widget show duplicated sales in month
* Fixed: Unable to deactivated plugin in WordPress network website
* Fixed: Wrong order total for vendors (order total is without taxes)
* Removed: Essential grid metabox in add product page
* Removed: yith_wcmv_show_vendor_profile hook

= 1.9.5 =

* Added: Support to YITH WooCommerce PDF Invoce and Shipping list Premium
* Added: Support to YITH WooCommerce Request a quote Premium
* Added: Support to YITH WooCommerce Catalog Mode Premium
* Updated: All .po/.mo files
* Fixed: Translation issue in backend
* Fixed: Wrong tax calculation in vendor order
* Fixed: Admin can't enable vaction module
* Fixed: Vendor can edit reviews without capability in product details page
* Fixed: Widget quick info use owner email if no store email was set
* Fixed: Content issue in Become a Vendor page
* Fixed: Wrong total sales number with free orders
* Fixed: Wrong order total count in vendor admin
* Fixed: Featured products management doesn't work in edit product page for vendor
* Fixed: Privacy option for vendor orders doesn't hide email in order list

= 1.9.4 =

* Added: yith_vendor_not_allowed_reports hook for not allowed report for vendor
* Fixed: Vendor can't access to admin area if GeoDirectory plugin is activated
* Fixed: Report abuse link conflict with enfold theme
* Fixed: PrettyPhoto js library doesn't exists
* Fixed: Vendor Shop Owner removed after saving vendor data
* Removed: YITH WooCommerce Mailchimp and Jetpack Dashboard widgets

= 1.9.3 =

* Fixed: Vendor Shop Owner removed after saving of bank account (IBAN/BIC)
* Fixed: Spinner doesn't show in admin
* Fixed: Skip review capability doesn't work in frontend registration

= 1.9.2 =

* Fixed: Duplicate order in WooCommerce -> Reports -> Sales by date for admin
* Fixed: Unable to translate vendor registration form placeholder
* Fixed: Order actions doesn't work for vendor
* Fixed: Admin can't remove vendor owner

= 1.9.1 =

* Added: Featured products management can override for each vendor
* Fixed: Customer can't register if terms and conditions fields is set to required
* Fixed: Vendors can't save text editor style in store description field
* Fixed: Warning if not vendor owner was set 

= 1.9.0 =

* Added: New socials fields (Vimeo, Instagram, Pinterest, Flickr, Behance, Tripadvisor)
* Added: Admin can change the Vendor tab name
* Added: Legal notes fields for vendor
* Added: Support to WooCommerce 2.5
* Added: Support to YITH WooCommerce Customize My Account Page Premium
* Added: Select if you want to show header image or gravatar in vendor list shortcode
* Added: IBAN/BIC fields in vendor personal information
* Added: Admin can disable payment information in order details page for vendor
* Added: Terms and conditions fields for vendor in registration and become a vendor pages
* Updated: 3rd-party FontAwesome lib
* Updated: 3rd-party PayPal lib
* Updated: Language files
* Fixed: Missing text domain in some strings in text domain
* Fixed: Duplicate order if the customer pay with external gateway (like PayPal, Stripe, Simplify, ecc.)
* Fixed: Unable to show become a vendor form in My Account endpoint
* Fixed: Admin can't set vendor owner/admins if Yoast SEO plugin is activate on website
* Fixed: Warning in WooCommerce Email page (in admin)
* Fixed: Call to undefined function get_current_screen() in admin
* Fixed: Wrong data in Sales by date report for vendor
* Fixed: Missing WooCommerce font for vacation icon
* Fixed: VAT validation issue in become a vendor page
* Tweak: Replaced old chosen script to select2
* Tweak: Become a vendor form
* Tweak: New vendor registration form
* Tweak: Add "Vendor" label for recipient in WooCommerce -> Settings -> Emails 
* Moved: Vendor's VAT/SSN from "Vendor Settings" to "Front page" in Vendor dashboard
* Removed: add_select_customer_script() method from admin class
* Removed: enqueue_ajax_choosen() method from admin class
* Removed: vendor_admins_chosen() method from admin class

= 1.8.4 =

* Fixed: Product variations in order with latest WooCommerce

= 1.8.3 =

* Added: Advanced text editor for vendor description
* Fixed: Store header wrapper stylesheet error, no margin bottom
* Fixed: Vendor table column style
* Fixed: Vendor with no order can see all shop orders
* Fixed: yith_vendors not defined in vendor taxonomy page
* Fixed: User with vendor role but without store can edit products, order, coupons, ecc.
* Fixed: add to cart button disappears in Nielsen Theme with vacation module enabled

= 1.8.2 =

* Added: Hide customer section in order details page for vendor
* Added: Calculate commission include tax

= 1.8.1 =

* Fixed: Vendor lost translated product if edit by website admin
* Fixed: Support to WPML in vendor store page (frontend)
* Fixed: Can't create vendor sidebar in YITH Thmemes with WordPress 4.4
* Fixed: WooCommerce Report can't show correct information

= 1.8.0 =

* Added: Support to WordPress 4.4
* Added: Disabled vendor logo (gravatar) image in each vendor store page
* Added: Change vendor logo (gravatar) image size
* Added: Support to YITH WooCommerce Waiting List Premium (vendor can manage waiting list)
* Added: Support to YITH WooCommerce Order Tracking Premium (vendor can manage tracking code)
* Added: Support to YITH WooCommerce Membership Premium (vendor can manage membership plans)
* Added: Support to YITH WooCommerce Subscription Premium (vendor can manage subscription)
* Added: Support to YITH WooCommerce Badge Management Premium (vendor can manage product badges)
* Added: Support to YITH WooCommerce Survey Premium (vendor can manage survey)
* Added: Support to YITH WooCommerce Coupon Email System Premium (vendor can send coupon by mail)
* Added: yith_wcmv_vendor_taxonomy_args hook tyo change taxonomy rewrite rules
* Added: Change vendor store taxonomy rewrite slug option
* Added: Antispam filter for vendor registration form
* Added: Antispam filter for become a vendor form
* Tweak: Flush rewrite rules to prevent 404 not found page after plugin update in vendor store page
* Tweak: Vendor taxonomy menu management
* Fixed: Vendor can't see admin dashboard and vendor rules after plugin update
* Fixed: Undefined suborder_id when add inline item to parent order
* Fixed: Admin and Vendor can't view trashed orders
* Fixed: Issue with YITH WooCommerce Gift Card in checkout page
* Fixed: Lost products after edit vendor slug
* Fixed: New vendor without user role
* Fixed: Vendor information validation on become a vendor page
* Fixed: WPML issue vendor can edit her/his products in other languages

= 1.7.3 =

* Tweak: Performance increase use php construct instanceof instead of is_a function
* Tweak: Order management (added order version in DB)
* Fixed: Vendor can't add or upload a store image
* Fixed: Store Name and Gravatar issue
* Fixed: Can't see product variation in vendor order details
* Fixed: Website admin can't assigne products to a specific vendor

= 1.7.2 =

* Updated: Language files
* Fixed: Shop manager can't edit vendors profile
* Fixed: Customer can't register if VAT/SSN fields is set to required

= 1.7.1 =

* Added: Support to YITH Product Size Charts for WooCommerce Premium
* Added: Support to YITH WooCommerce Name Your Price Premium

= 1.7.0 =

* Added: Refund management
* Added: New user role "Vendor" (Dashboard->Users)
* Added: yit_wcmv_plugin_options_capability hook for admin panel capabilities
* Added: VAT/SSN field in vendor registration
* Added: yith_wcmv_vendor_capabilities hook
* Added: Store description in vendor page
* Updated: Languages file
* Tweak: User capabilities
* Tweak: Performance improved with new plugin core 2.0
* Fixed: Delete user capabilities after deactive or remove plugin
* Fixed: Fields "Commission id" in commission table doesn't display correctly
* Fixed: Unable to create new vendor account in front-end
* Fixed: Wrong user capabilities after delete vendor account
* Fixed: Add order link in dashboard menu
* Fixed: Issue with Date filter in Vendor sales report

= 1.6.5. =

* Updated: Italian translation 
* Fixed: Product amount limit doesn't calculate correct vendor products

= 1.6.4 =

* Fixed: Vendor disabled sales after save option
* Fixed: Become a vendor page doesn't show for not logged in users

= 1.6.3 =

* Added: Become a vendor registration form
* Added: Support to YITH Live Chat Premium
* Added: Disable user gravatar in vendor's store page
* Tweak: Support to YITH Nielsen theme
* Tweak: Custom post type capabilities
* Updated: Language pot file
* Fixed: Option deps doesn't work
* Fixed: Can't translate string localized by esc_attr__ and esc_attr_e function
* Fixed: Print wrong commission rate value after insert new vendor by admin

= 1.6.2 =

* Added: Auto enable vendor account after registration
* Added: Seller vacation module
* Updated: Language pot file
* Fixed: Order email issue
* Removed: Old Product -> Vendors admin menu link

= 1.6.1 =

* Updated: Italian translation
* Updated: pot language file
* Fixed: checkout abort if no store owner set

= 1.6.0 =

* Added: Order Management
* Added: Support to YITH Live Chat
* Added: Support to WordPress 4.3
* Added: "Sold by vendor" in order details page
* Added: "Sold by vendor" in cart details page
* Added: "Sold by vendor" in checkout details page
* Added: "Sold by vendor" in My Account -> View order page
* Added: yith_wcmv_register_as_vendor_text hook for "Register as a vendor" text on frontend
* Added: yith_wcmv_store_header_class hook for vendor store header wrapper classes
* Added: yith_wcmv_header_img_class hook for vendor store header image classes
* Added: New vendor status "no-owner" in vendor taxonomy page in admin
* Added: New "Vendors" main menu item
* Added: yith_wcmv_show_vendor_name_template filter to prevent load vendor name template
* Added: YITH Essential Kit for WooCommerce #1 support
* Added: Dashboard notification for products needs to approve
* Added: New option "Send a copy to website owner" in Quick Info widget
* Updated: Italian translation
* Updated: pot language file
* Tweak: Commission rate column in commission table
* Tweak: Support to WooCommerce 2.4
* Tweak: WooCommerce option panel with the latest WC Version
* Tweak: Javascript code optimization
* Tweak: Commissions list order by descending commission ids
* Fixed: Prevent to edit other vendor reviews
* Fixed: Add new post button doesn't display
* Fixed: Unable to add Shop coupon with product amount option enabled
* Fixed: Vendor don't see shop coupon page with product amount option enabled
* Fixed: Coupon and Reviews option issue after the first installation
* Fixed: Reviews list not filter comments if vendor have no products
* Fixed: Recent comment dashboard widget in vendor administrator
* Fixed: Wrong search in Add/Edit product for Grouped product
* Fixed: Remove "Add new" post types menu from wp-admin bar
* Fixed: No default value "per_page" in yith_wcmv_list shortcodes
* Fixed: Add vendor image issue in italian language
* Fixed: Unable to translate "Edit extra info" button in admin
* Fixed: Chart GroupBy parameter doesn't exist in Vendor Reports
* Fixed: Warning on vendor reviews list in admin
* Fixed: Warning "cart item key not found" on checkout page
* Fixed: Vendors don't receive the email order
* Fixed: Auto sync commission and order status
* Fixed: Undefined index: hide_from_guests in Quick Info widget
* Fixed: Vendor description tab translation issue with qTranslateX plugin

= 1.5.2 =

* Fixed: Unable to login in vendor dashboard using particular themes

= 1.5.1 =

* Added: Support to WooCommerce 2.4
* Added: "Sold by vendor" in commission page
* Tweak: Plugin Core Framework
* Fixed: Vendor don't see product page with product amount enabled

= 1.5.0 =

* Added: New order actions: "New order" and "Cancelled order" for vendor
* Added: New order email options in WooCommerce > Settings > Emails > New order (for vendor)
* Added: Cancelled order email options in WooCommerce > Settings > Emails > New order (for vendor)
* Added: Minimum value for commission withdrawals
* Added: Featured products management option
* Added: Shortcodes for list of vendors
* Added: Item sold information in single product page
* Added: Total sales information in vendor page
* Added: yith_wcmv_header_icons_class hook to change header icons in vendor page
* Added: YITH WooCommerce Ajax Product Filter Support
* Added: Italian language file
* Added: WPML Support
* Updated: pot language file
* Fixed: Wrong order date in "Vendors Sales" report
* Fixed: Can't locate email templates
* Fixed: Prevent double instance in singleton class
* Fixed: Hide store header if vendor account is disabled
* Fixed: Variations don't show commission detail page
* Fixed: New order email notification

= 1.4.4 =

* Updated: pot language file
* Fixed: Fatal error in the commision page for deleted orders

= 1.4.3 =

* Fixed: Plugin does not recognize the languages file

= 1.4.2 =

* Fixed: Vendor can see all custom post types

= 1.4.1 =

* Added: Enable/Disable seller capabilities Bulk action
* Added: Report abuse option
* Updated: Plugin default language file
* Fixed: Quick contact info widget text area style
* Fixed: Vendors bulk action string localizzation
* Removed: Old taxonomy bulk action hook

= 1.4.0 =

* Added: Vendors can manage customer reviews on their products
* Added: Vendor can manage coupons for their products
* Added: Recent Comments dashboard widget
* Added: Recent Reviews dashboard widget
* Fixed: Store header image on Firefox and Safari
* Fixed: Wrong commission link in order page

= 1.3.0 =

* Added: Bulk Action in Vendors table
* Added: Register a new vendor from front end
* Added: yith_frontend_vendor_name_prefix hook to change the "by" prefix in loop and single product page
* Added: yith_single_product_vendor_tab_name hook to change the title of "Vendor" tab in single product page
* Added: Customize submit label in quick info widget
* Added: Option to limit the vendor product amount 
* Added: Option to hide the quick info widget from guests
* Added: yith_wpv_quick_info_button_class hook for custom css classes to quick info button
* Added: Option to hide the vendor name in Shop page
* Added: Option to hide the vendor name in Single product page
* Added: Option to hide the vendor name in Product category page
* Updated: Plugin default language file
* Fixed: Store header on mobile
* Fixed: Unable to rewrite frontend css on child theme
* Fixed: Changed "Product Vendors" label  to "Vendor" in product list table
* Fixed: Wrong default title in store location and quick info widgets
* Fixed: Widget Vendor list: option "Hide this widget on vendor page" doesn't work
* Fixed: Spelling error in Quick Info widget. Change the label "Object" to "Subject"
* Removed: Old sidebar template
* Removed: Old default.po file

= 1.2.0 =

* Initial release
