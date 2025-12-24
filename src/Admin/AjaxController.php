<?php
/**
 * AJAX endpoints for the CF7 Gumroad admin (authenticated, wp-admin only).
 * 
 * @package CF7Gumroad\Admin
 */

declare( strict_types=1 );

namespace CF7Gumroad\Admin;

use CF7Gumroad\Support\Helpers;

defined( 'ABSPATH' ) || exit;

class AjaxController {
  
	// Init
	public static function init(): void {
		add_action( 'wp_ajax_cf7g_check_connection', array( __CLASS__, 'check_connection' ) );
		add_action( 'wp_ajax_cf7g_reset_connection', array( __CLASS__, 'reset_connection' ) );
	}

	// AJAX: validate token and fetch products.
	public static function check_connection(): void {
		check_ajax_referer( 'cf7g_check_connection', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized.', 'couponflow-for-gumroad-cf7' ), 403 );
		}

		$form_id  = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		$settings = get_post_meta( $form_id, '_cf7g_gumroad_settings', true );
		$settings = is_array( $settings ) ? $settings : array();

		$token  = $settings['token'] ?? '';
		$result = Helpers::validate_token( $token );

		$user_key = 'cf7g_notice_' . get_current_user_id();

		if ( $result['success'] ) {
			$settings = array_merge(
				$settings,
				array(
					'connected' => true,
					'products'  => $result['data'] ?? array(),
				)
			);

			update_post_meta( $form_id, '_cf7g_gumroad_settings', $settings );

			set_transient( $user_key, __( '✅ Gumroad connection successful!', 'couponflow-for-gumroad-cf7' ), 15 );

			wp_send_json_success(
				array(
					'message'  => $result['message'],
					'products' => $result['data'] ?? array(),
				)
			);
		}

		// If failed, clear connection flag.
		$settings['connected'] = false;
		update_post_meta( $form_id, '_cf7g_gumroad_settings', $settings );

		set_transient( $user_key, __( '❌ Connection failed. Please check your token.', 'couponflow-for-gumroad-cf7' ), 15 );

		wp_send_json_error( array( 'message' => $result['message'] ) );
	}

	// AJAX: reset Gumroad connection (clear token).
	public static function reset_connection(): void {
		check_ajax_referer( 'cf7g_reset_connection', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized.', 'couponflow-for-gumroad-cf7' ), 403 );
		}

    $form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
    if ( ! $form_id ) {
      wp_send_json_error( array( 'message' => __( 'Invalid form.', 'couponflow-for-gumroad-cf7' ) ), 400 ); 
    }

    delete_post_meta( $form_id, '_cf7g_gumroad_settings' );
    delete_post_meta( $form_id, '_cf7g_coupon_log' );

		$user_key = 'cf7g_notice_' . get_current_user_id();
		set_transient( $user_key, __( '🔄 Gumroad connection has been reset.', 'couponflow-for-gumroad-cf7' ), 15 );

		wp_send_json_success( array( 'message' => __( 'Connection reset successful', 'couponflow-for-gumroad-cf7' ) ) );
	}
}
