<?php
/*
 Plugin Name: CouponFlow for Gumroad with Contact Form 7
 Plugin URI: https://lucian-web.dev/cf7-gumroad-coupons
 Description: Generate Gumroad coupon codes automatically from Contact Form 7 submissions.
 Version: 1.0.0
 Author: Lucian-DEV
 Author URI: https://lucian-web.dev
 Text Domain: couponflow-for-gumroad-cf7
 Requires at least: 6.0
 Requires PHP: 8.0
 Requires Plugins: contact-form-7
 License: GPL v2 or later
 License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

 CouponFlow for Gumroad with Contact Form 7 is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 2 of the License, or
 any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.
*/
 
defined( 'ABSPATH' ) || exit;

// Define plugin constants.
define( 'CGC_VERSION', '1.0.0' );
define( 'CGC_PATH', plugin_dir_path( __FILE__ ) );
define( 'CGC_URL', plugin_dir_url( __FILE__ ) );

// Check for Composer autoload.
if ( ! file_exists( CGC_PATH . 'vendor/autoload.php' ) ) {
  return;
}
// Load Composer autoload.
require_once CGC_PATH . 'vendor/autoload.php';

// Environment Checks
add_action(
	'plugins_loaded',
	static function () {
		$min_wp  = '6.0';
		$min_php = '8.0';

		if ( version_compare( get_bloginfo( 'version' ), $min_wp, '<' ) || version_compare( PHP_VERSION, $min_php, '<' ) ) {
			add_action(
				'admin_notices',
				static function () use ( $min_wp, $min_php ) {
					printf(
						'<div class="notice notice-error"><p>%s</p></div>',
						esc_html( sprintf( __( 'CouponFlow for Gumroad with Contact Form 7 requires WordPress %1$s+ and PHP %2$s+.', 'couponflow-for-gumroad-cf7' ), $min_wp, $min_php ) )
					);
				}
			);
			return;
		}

		// Contact-Form-7 required.
		if ( ! class_exists( 'WPCF7' ) && is_admin() ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p>'
					. esc_html__( 'CouponFlow for Gumroad with Contact Form 7 requires Contact Form 7 to be installed and active.', 'couponflow-for-gumroad-cf7' )
					. '</p></div>';
				}
			);
			return;
		}

		// Load admin-side logic only in dashboard.
		if ( is_admin() ) {
			\CF7Gumroad\Admin\SettingsPage::init();
			\CF7Gumroad\Admin\AjaxController::init();
		}

		// Always load CF7 integration layer.
		\CF7Gumroad\Integration\MailTag::init();
		\CF7Gumroad\Integration\CouponHandler::init();
	}
);

// Helpers
require_once CGC_PATH . 'src/Support/Helpers.php';