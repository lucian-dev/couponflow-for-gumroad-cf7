<?php

/**
 * Handles direct communication with the Gumroad API.
 * 
 * @package CF7Gumroad\Api
 */

declare( strict_types=1 );

namespace CF7Gumroad\Api;

defined( 'ABSPATH' ) || exit;

class GumroadAPI {

	// Create a coupon for a specific Gumroad product.
	public static function create_coupon( string $token, string $product_id, string $prefix, int $discount = 10, int $usages = 1 ): array {
		if ( empty( $token ) || empty( $product_id ) ) {
			return array(
				'success' => false,
				'message' => __( 'Missing Gumroad token or product ID.', 'couponflow-for-gumroad-cf7' ),
			);
		}

		$code = strtoupper( sanitize_title( $prefix ) ) . '-' . wp_generate_password( 6, false, false );
		$body = array(
			'name'               => $code,
			'offer_type'         => 'percent',
			'amount_off'         => $discount,
			'max_purchase_count' => $usages,
		);

		$response = wp_remote_post(
			"https://api.gumroad.com/v2/products/$product_id/offer_codes",
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . trim( $token ),
					'Accept'        => 'application/json',
				),
				'body'    => $body,
				'timeout' => 15,
			)
		);

		// Handle connection errors.
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					__( 'Connection error: %s', 'couponflow-for-gumroad-cf7' ),
					$response->get_error_message()
				),
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		// Success: Gumroad returns 200 + success true.
		if ( 200 === $code && isset( $data['success'] ) && true === $data['success'] ) {
			return array(
				'success' => true,
				'message' => __( 'Coupon created successfully.', 'couponflow-for-gumroad-cf7' ),
				'data'    => array(
					'coupon_code' => $data['coupon']['name'] ?? $body['name'],
					'discount'    => $discount,
					'usages'      => $usages,
					'product_id'  => $product_id,
				),
			);
		}

		// Fallback: failed response.
		return array(
			'success' => false,
			'message' => $data['message'] ?? __( 'Failed to create coupon.', 'couponflow-for-gumroad-cf7' ),
			'data'    => $data,
		);
	}
}
