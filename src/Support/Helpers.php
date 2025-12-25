<?php

namespace CouponFlow\CF7Gumroad\Support;

defined( 'ABSPATH' ) || exit;

class Helpers {

	// Validate Gumroad access token via API.
	public static function validate_token( string $token ): array {
		if ( empty( $token ) ) {
			return array(
				'success' => false,
				'message' => __( 'No token provided.', 'couponflow-for-gumroad-cf7' ),
			);
		}

		// Make request to Gumroad API.
		$response = wp_remote_get(
			'https://api.gumroad.com/v2/products',
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . trim( $token ),
					'Accept'        => 'application/json',
				),
				'timeout' => 15,
			)
		);

		// Network error.
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
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		// API response check.
		if ( 200 === $code && isset( $body['success'] ) && true === $body['success'] ) {
			$raw_products   = $body['products'] ?? array();
			$clean_products = array();

			foreach ( $raw_products as $product ) {
				if ( isset( $product['id'], $product['name'] ) ) {
					$clean_products[] = array(
						'id'   => sanitize_text_field( $product['id'] ),
						'name' => sanitize_text_field( $product['name'] ),
					);
				}
			}

			return array(
				'success' => true,
				'message' => __( '✅ Gumroad connection successful.', 'couponflow-for-gumroad-cf7' ),
				'data'    => $clean_products,
			);
		}

		// Invalid or unauthorized token.
		return array(
			'success' => false,
			'message' => __( 'Invalid Gumroad token or unauthorized access.', 'couponflow-for-gumroad-cf7' ),
		);
	}
}
