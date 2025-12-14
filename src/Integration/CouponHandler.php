<?php

/**
 * Handles coupon creation when a CF7 form is submitted.
 * 
 * @package CF7Gumroad\Integration
 */

declare( strict_types=1 );

namespace CF7Gumroad\Integration;

use CF7Gumroad\Api\GumroadAPI;

defined( 'ABSPATH' ) || exit;

class CouponHandler {
  
	// Initialize integration hooks.
	public static function init(): void {
		// Hook before email is sent (coupon generation point).
		add_action( 'wpcf7_before_send_mail', array( __CLASS__, 'handle_submission' ) );
	}

	// Handle Contact Form 7 form submission.
	public static function handle_submission( $form ): void {
		if ( ! $form instanceof \WPCF7_ContactForm ) {
			return;
		}

		$form_id  = $form->id();
		$settings = get_post_meta( $form_id, '_cf7g_gumroad_settings', true );

		if ( empty( $settings['token'] ) || empty( $settings['product'] ) ) {
			return;
		}

		// Access submission data.
		$submission  = \WPCF7_Submission::get_instance();
		$posted_data = $submission ? $submission->get_posted_data() : array();
		$email       = '';
		foreach ( $posted_data as $key => $value ) {
			if ( is_string( $value ) && is_email( $value ) ) {
				$email = sanitize_email( $value );
				break;
			}
		}

		// Generate coupon.
		$result = GumroadAPI::create_coupon(
			$settings['token'],
			$settings['product'],
			$settings['prefix'] ?? 'CF7',
			(int) $settings['discount'] ?? 10,
			(int) $settings['usages'] ?? 1
		);

		if ( ! $result['success'] ) {
			return;
		}

		$coupon_code = $result['data']['coupon_code'] ?? '';

		if ( ! empty( $coupon_code ) ) {
			// Attach coupon code to submission data for mail tag usage.
			add_filter(
				'wpcf7_special_mail_tags',
				function ( $output, $name ) use ( $coupon_code ) {
					if ( 'cf7g_coupon_code' === $name ) {
						return sanitize_text_field( $coupon_code );
					}
					return $output;
				},
				10,
				2
			);
		}

		// Save last generated coupon + email (for logs/debug).
		$log   = get_post_meta( $form_id, '_cf7g_coupon_log', true );
		$log   = is_array( $log ) ? $log : array();
		$log[] = array(
			'coupon_code' => sanitize_text_field( $coupon_code ),
			'email'       => $email,
		);

		update_post_meta( $form_id, '_cf7g_coupon_log', $log );
	}
}
