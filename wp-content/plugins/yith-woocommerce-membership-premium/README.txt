=== YITH WooCommerce Membership ===

== Changelog ==

= 1.2.8 =

* Added: user option in membership_protected_content shortcode to display content to non-members or guests or logged users only
* Fixed: shortcode issue
* Fixed: membership activation issue in combination with polylang


= 1.2.7 =

* Fixed: download link style
* Fixed: protected link saving

= 1.2.6 =

* Added: protected links in posts, pages and product descriptions
* Added: protected contents through shortcode
* Added: "copy to clipboard" for shortcodes in Membership Plan list
* Added: improved CSS and JS inclusion
* Fixed: YITH WooCommerce Multi Vendor compatibility (hide alternative content if user is not enabled to see it)

= 1.2.5 =

* Added: Membership Free Shipping method since WooCommerce 2.6
* Added: parameter to sort membership items in [membership_items] shortcode
* Added: italian language

= 1.2.4 =

* Added: possibility to associate automatically membership plans to newly registered users

= 1.2.3 =

* Added: compatibility with YITH WooCommerce Dynamic Pricing and Discounts 1.1.0 to allow discounts for members
* Added: shortcode to show downloaded product links
* Fixed: bug in memberships with user_id = 0
* Fixed: issue with creation of download report table on multisite installation
* Fixed: memory issue in Membership Plan settings
* Fixed: membership access issue
* Tweak: fixed strings

= 1.2.2 =

* Tweak: display membership access in Media Editor
* Tweak: fixed Multi Vendor suborder bug ( duplicate membership )
* Tweak: fixed minor bugs

= 1.2.1 =

* Added: possibility to change the subscription id for every membership with membership advanced management enabled
* Added: order info in membership list
* Added: sorting for starting and expiring date in All Memberships WP List
* Fixed: datepicker css style
* Fixed: date bug in advanced membership management
* Fixed: redirect bug for pages in membership

= 1.2.0 =

* Added: membership advanced management
* Added: possibility to hide product download links and use shortcode
* Added: credit advanced management, admin can set different credits for every product (default is 1)
* Added: possibility to set credits for the first term
* Added: compatibility with Premium YITH WooCommerce Email Templates 1.2.0
* Added: possibility to override membership email templates
* Added: subscription status in All Memberships list
* Added: possibility to hide price and add-to-cart button in Single Product Page, if members are allowed to download the product
* Added: reports for memberships purchased with subscription
* Fixed: duplicate membership plan
* Fixed: CSS tooltip in frontend
* Fixed: Membership can now be activated even when cancelled Subscription is payed
* Fixed: subscription cancel-now bug
* Fixed: issue concering product download by admin (check credit error); now Admin doesn't need credits to download products
* Tweak: added hierarchical structure in "chosen for product" and post categories
* Tweak: added buttons "Select All" and "Deselect All" for chosen field of post and product categories in Membership Plan Options
* Tweak: added action to manage PayPal and Stripe disputes
* Tweak: improved frontend style for membership history, download buttons and list of planned items
* Tweak: added admin tab shortcodes to explain shortcode usage
* Tweak: email classes and templates updated for WC 2.5
* Tweak: improved reports
* Tweak: changed status label from "Not Active" to "Suspended"
* Tweak: changed labels in admin membership plan
* Tweak: included child categories for products if parent category is selected in Membership Plan Options
* Tweak: fixed css for metabox chosen, select and descriptions
* Tweak: added style for download buttons in Single Product Page

= 1.1.1 =

* Tweak: fixed bug for current memberships without credits management

= 1.1.0 =

* Added: download credits management for membership
* Added: possibility to choose limit for membership downloads
* Added: membership and download reports
* Added: user download reports table and graphics in orders
* Added: status filters in Memberships WP List
* Added: compatibility with WooCommerce 2.5 RC2
* Tweak: fixed minor bug with bbPress
* Tweak: fixed membership bulk actions for users
* Tweak: fixed pot language file
* Tweak: changed menu name Memberships in All Memberships
* Tweak: fixed minor bugs

= 1.0.4 =

* Added: possibility to hide items directly in membership plan settings page
* Tweak: better styling management for membership item list (shortcode)
* Tweak: improved compatibility with YITH WooCommerce Multi Vendor
* Tweak: improved cron performance
* Tweak: fixed end date calculation after pause
* Tweak: fixed minor bugs

= 1.0.3 =

* Added: support for membership bought by guest users
* Added: shortcode for showing membership history
* Tweak: improved compatibility with YITH WooCommerce Multi Vendor
* Tweak: added possibility to hide membership history in My Account page
* Tweak: improved download list

= 1.0.2 =

* Initial release