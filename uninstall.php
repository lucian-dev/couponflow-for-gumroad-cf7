<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clean up all plugin-related meta.
$cgc_forms = get_posts(
	array(
		'post_type'      => 'wpcf7_contact_form',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);

if ( ! empty( $cgc_forms ) && is_array( $cgc_forms ) ) {
	foreach ( $cgc_forms as $form_id ) {
		delete_post_meta( $form_id, '_cf7g_gumroad_settings' );
		delete_post_meta( $form_id, '_cf7g_coupon_log' );
	}
}