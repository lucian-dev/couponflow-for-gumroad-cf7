=== CouponFlow for Gumroad with Contact Form 7 ===
Contributors: luciandev13
Tags: cf7, contact form 7, gumroad, gumroad coupons, discount codes
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically generate and send personalized Gumroad coupon codes from Contact Form 7 submissions.

== Description ==

CouponFlow for Gumroad with Contact Form 7 allows to automatically generate and send personalized Gumroad coupon codes from Contact Form 7 submissions.

== Features ==

- 🎟️ Automatically **create Gumroad coupon codes** from any CF7 form submission  
- 🔗 Securely **connect your Gumroad account** using an access token  
- 🧩 Choose **which Gumroad product** the coupon applies to  
- 💬 Insert the generated code in your autoresponder using `[cf7g_coupon_code]`  
- 🧾 View a full **log of generated coupons + recipient emails**  
- 🪄 Reset connection or clean up all data in one click  
- 💾 Full uninstall cleanup — no leftover options or logs

== Installation ==

= Automatic Install =

1. Log into your WordPress dashboard and go to Plugins &rarr; Add New
2. Search for "CouponFlow"
3. Click "Install Now" under the "CouponFlow for Gumroad with Contact Form 7" plugin
4. Click "Activate Now"

= Manual Install =

1. Download the plugin from the download button on this page
2. Unzip the file, and upload the resulting `couponflow-for-gumroad-cf7` folder to your `/wp-content/plugins` directory
3. Log into your WordPress dashboard and go to Plugins
4. Click "Activate" under the "CouponFlow for Gumroad with Contact Form 7" plugin

== Frequently Asked Questions ==

= Will it affect my Contact Form 7 forms? =
No. The plugin works independently - it only adds an extra "Gumroad Coupons" tab to each form. Your existing forms and mail templates remain untouched.

= Is my Gumroad token secure? =
Yes. Your access token is stored only in WordPress meta, sanitized, and never exposed on the front end or in emails. You can also see here how you generate one (https://gumroad.com/help/article/280-create-application-api).

= Does it work with multiple forms? =
Yes. You can connect multiple Contact Form 7 forms to different Gumroad products. Each form has its own connection, coupon settings and logs.

== Screenshots ==
1. Connect to your Gumroad account via Access Token.
2. Configure your coupon settings. Choose your Gumroad product and customize how your discount codes are generated.
3. Add the coupon to your autoresponder. Send each user their unique Gumroad discount code automatically after submission.

== Changelog ==

= 1.0.0 =
Initial release — integrates Gumroad with Contact Form 7 for automatic coupon generation.