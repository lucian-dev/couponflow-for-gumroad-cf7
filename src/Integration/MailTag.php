<?php

/**
 * Registers the [cf7g_coupon_code] tag for Contact Form 7.
 * 
 * @package CF7Gumroad\Integration
 */

declare( strict_types=1 );

namespace CF7Gumroad\Integration;

defined( 'ABSPATH' ) || exit;

class MailTag {
  
	// Initialize mail tag registration.
	public static function init(): void {
		add_action( 'wpcf7_init', array( __CLASS__, 'register_coupon_tag' ) );
	}

	// Register the [cf7g_coupon_code] form tag.
	public static function register_coupon_tag(): void {
		if ( function_exists( 'wpcf7_add_form_tag' ) ) {
			wpcf7_add_form_tag(
				'cf7g_coupon_code',
				array( __CLASS__, 'render_coupon_tag' ),
				array( 'name-attr' => false ),
			);
		}
	}

	// Render callback for [cf7g_coupon_code].
	public static function render_coupon_tag( $tag ): string {
		return '';
	}
}
