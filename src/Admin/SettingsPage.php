<?php

/**
 * Settings page for the Gumroad Coupons tab in CF7.
 * 
 * @package CF7Gumroad\Admin
 */

declare( strict_types=1 );

namespace CF7Gumroad\Admin;

defined( 'ABSPATH' ) || exit;

class SettingsPage {

	// Init
	public static function init() {
		// Add "Gumroad Coupons" tab in CF7 editor.
		add_filter( 'wpcf7_editor_panels', array( __CLASS__, 'add_gumroad_panel' ) );
		// Save form meta.
		add_action( 'wpcf7_save_contact_form', array( __CLASS__, 'save_form_meta' ) );
		// Enqueue admin JS only on CF7 settings.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	// Add custom panel
	public static function add_gumroad_panel( $panels ): array {
		$panels['gumroad-coupons-panel'] = array(
			'title'    => __( 'Gumroad Coupons', 'couponflow-for-gumroad-cf7' ),
			'callback' => array( __CLASS__, 'render_panel_content' ),
		);
		return $panels;
	}

	// Panel content
	public static function render_panel_content( $post ) {
		$form_id  = $post->id();
		$settings = get_post_meta( $form_id, '_cf7g_gumroad_settings', true );
		$settings = is_array( $settings ) ? $settings : array();

		$token     = isset( $settings['token'] ) ? $settings['token'] : '';
		$product   = isset( $settings['product'] ) ? esc_attr( $settings['product'] ) : '';
		$products  = isset( $settings['products'] ) && is_array( $settings['products'] ) ? $settings['products'] : array();
		$prefix     = isset( $settings['prefix'] ) ? $settings['prefix'] : '';
		$discount  = isset( $settings['discount'] ) ? (int) $settings['discount'] : 10;
		$usages    = isset( $settings['usages'] ) ? (int) $settings['usages'] : 1;
		$connected = ! empty( $settings['connected'] );

		$log_entries = get_post_meta( $form_id, '_cf7g_coupon_log', true );
		$log_entries = is_array( $log_entries ) ? $log_entries : array();

		// Nonces for AJAX security.
		$check_nonce = wp_create_nonce( 'cf7g_check_connection' );
		$reset_nonce = wp_create_nonce( 'cf7g_reset_connection' );

		// Retrieve transient notice.
		$notice_key = 'cf7g_notice_' . get_current_user_id();
		$notice     = get_transient( $notice_key );
		if ( $notice ) {
			echo '<div class="notice notice-success" style="margin:10px 0;padding:8px;"><p>' . esc_html( $notice ) . '</p></div>';
			delete_transient( $notice_key );
		}

		?>
		<div class="cf7g-gumroad-panel wrap">
			<h2><?php esc_html_e( 'Gumroad Coupons Settings', 'couponflow-for-gumroad-cf7' ); ?></h2>
			<p><?php esc_html_e( 'Connect your Gumroad account and configure coupon generation for this form.', 'couponflow-for-gumroad-cf7' ); ?></p>
      <ol style="margin:10px 0 20px 20px; color:#555;">
        <li><?php esc_html_e( 'Enter your Gumroad Access Token and click Save.', 'couponflow-for-gumroad-cf7' ); ?></li>
        <li><?php esc_html_e( 'Click "Check Connection" to validate your token and load your Gumroad products.', 'couponflow-for-gumroad-cf7' ); ?></li>
        <li><?php esc_html_e( 'Once connected, configure your coupon details below.', 'couponflow-for-gumroad-cf7' ); ?></li>
        <li><?php esc_html_e( 'Use "Reset Connection" to disconnect or refresh your Gumroad setup.', 'couponflow-for-gumroad-cf7' ); ?></li>
      </ol>
		  <div class="cf7g-mailtag-tip" style="background:#fefbea; border:1px solid #f7e1a3; border-left:4px solid #f5b300; border-radius:4px; padding:12px 14px; margin:15px 0;">
        <p style="margin:0; font-weight:500; color:#333;">
          💡 <?php esc_html_e( 'Tip:', 'couponflow-for-gumroad-cf7' ); ?>
          <span style="font-weight:400; color:#444;">
          <?php esc_html_e( 'To include the generated coupon in your autoresponder email, add the following tag to your mail template:', 'couponflow-for-gumroad-cf7' ); ?>
          </span>
        </p>
		    <code style="display:inline-block; margin-top:6px; background:#fff; padding:4px 8px; border:1px solid #ddd; border-radius:3px;">[cf7g_coupon_code]</code>
		  </div>
		<!-- Section 1: Access Token -->
      <table class="form-table">
        <tr>
          <th scope="row">
            <label for="cf7g_gumroad_token"><?php esc_html_e( 'Gumroad Access Token', 'couponflow-for-gumroad-cf7' ); ?></label>
          </th>
          <td>
            <input type="password" id="cf7g_gumroad_token" name="cf7g_gumroad_token" value="<?php echo esc_attr( $token ); ?>" class="regular-text" />
            <button type="button" class="button" id="cf7g_check_connection" data-form="<?php echo esc_attr( $form_id ); ?>" data-nonce="<?php echo esc_attr( $check_nonce ); ?>" <?php disabled( empty( $token ) ); ?>>
              <?php esc_html_e( 'Check Connection', 'couponflow-for-gumroad-cf7' ); ?>
            </button>
            <button type="button" class="button" id="cf7g_reset_connection" data-form="<?php echo esc_attr( $form_id ); ?>" data-nonce="<?php echo esc_attr( $reset_nonce ); ?>" <?php disabled( empty( $token ) ); ?> style="margin-left:6px;">
              <?php esc_html_e( 'Reset Connection', 'couponflow-for-gumroad-cf7' ); ?>
            </button>
          </td>
        </tr>
      </table>
		<!-- Section 2: Coupon Fields -->
		<?php if ( $connected ) : ?>
		  <h3><?php esc_html_e( 'Coupon Configuration', 'couponflow-for-gumroad-cf7' ); ?></h3>
      <table class="form-table">
        <tr>
          <th><label for="cf7g_product"><?php esc_html_e( 'Select Product', 'couponflow-for-gumroad-cf7' ); ?></label></th>
          <td>
            <select id="cf7g_product" name="cf7g_product">
              <option value=""><?php esc_html_e( '-- Select Product --', 'couponflow-for-gumroad-cf7' ); ?></option>
              <?php
              foreach ( $products as $p ) :
                $id   = $p['id'] ?? '';
                $name = $p['name'] ?? '';
                if ( ! $id || ! $name ) {
                  continue;
                }
                printf(
                  '<option value="%1$s" %2$s>%3$s</option>',
                  esc_attr( $id),
                  selected( $product, $id, false ),
                  esc_attr( $name )
                );
              endforeach;
              ?>
            </select>
          </td>
        </tr>
        <tr>
          <th><label for="cf7g_coupon_prefix"><?php esc_html_e( 'Coupon Prefix', 'couponflow-for-gumroad-cf7' ); ?></label></th>
          <td><input type="text" id="cf7g_coupon_prefix" name="cf7g_coupon_prefix" value="<?php echo esc_attr( $prefix ); ?>" class="regular-text" /></td>
        </tr>
        <tr>
          <th><label for="cf7g_discount"><?php esc_html_e( 'Discount Percentage', 'couponflow-for-gumroad-cf7' ); ?></label></th>
          <td><input type="number" id="cf7g_discount" name="cf7g_discount" value="<?php echo esc_attr( $discount ); ?>" min="1" max="100" /></td>
        </tr>
        <tr>
          <th><label for="cf7g_usages"><?php esc_html_e( 'Usage Limit', 'couponflow-for-gumroad-cf7' ); ?></label></th>
          <td><input type="number" id="cf7g_usages" name="cf7g_usages" value="<?php echo esc_attr( $usages ); ?>" min="1" /></td>
        </tr>
      </table>
		<?php endif; ?>
		<?php if ( ! empty( $log_entries ) && is_array( $log_entries ) ) : ?>
      <div class="cf7g-coupon-log" style="margin-top:30px;">
        <h3 style="margin-bottom:10px;"><?php esc_html_e( 'Generated Coupons Log', 'couponflow-for-gumroad-cf7' ); ?></h3>
        <ul style="list-style:disc; padding-left:20px; background:#fff; border:1px solid #e2e4e7; border-radius:6px; padding:12px 16px; max-width:700px;">
        <?php
        foreach ( array_reverse( $log_entries ) as $entry ) :
          $coupon = $entry['coupon_code'] ?? '';
          $email  = $entry['email'] ?? '';
          ?>
          <li style="margin-bottom:6px;">
          <strong><?php echo esc_html( $coupon ); ?></strong>
          <?php if ( $email ) : ?>
            <span style="color:#666;">— <?php echo esc_html( $email ); ?></span>
          <?php endif; ?>
          </li>
        <?php endforeach; ?>
        </ul>
      </div>
		<?php else : ?>
      <div class="cf7g-coupon-log-empty" style="margin-top:30px; color:#666;">
        <p><?php esc_html_e( 'No coupons generated yet for this form.', 'couponflow-for-gumroad-cf7' ); ?></p>
      </div>
		<?php endif; ?>
		  </div>
		<?php
	}

	// Save form meta when CF7 form is saved.
	public static function save_form_meta( $form ): void {

		if ( ! $form instanceof \WPCF7_ContactForm ) {
			return;
		}

    $form_id = $form->id();

    // Verify CF7 nonce.
    if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wpcf7-save-contact-form_' . $form_id ) ) {
      return;
    }

		$existing = get_post_meta( $form_id, '_cf7g_gumroad_settings', true );
		$existing = is_array( $existing ) ? $existing : array();

		// Sanitize incoming fields.
		$token    = isset( $_POST['cf7g_gumroad_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cf7g_gumroad_token'] ) ) : '';
		$product  = isset( $_POST['cf7g_product'] ) ? sanitize_text_field( wp_unslash( $_POST['cf7g_product'] ) ) : '';
		$prefix    = isset( $_POST['cf7g_coupon_prefix'] ) ? sanitize_text_field( wp_unslash( $_POST['cf7g_coupon_prefix'] ) ) : '';
		$discount = isset( $_POST['cf7g_discount'] ) ? absint( $_POST['cf7g_discount'] ) : 0;
		$usages   = isset( $_POST['cf7g_usages'] ) ? absint( $_POST['cf7g_usages'] ) : 1;

		// Build the settings array.
		$settings = array(
			'token'     => $token,
			'products'  => $existing['products'] ?? array(),
			'product'   => $product,
			'prefix'     => $prefix,
			'discount'  => $discount,
			'usages'    => $usages,
			'connected' => $existing['connected'] ?? false,
		);

		update_post_meta( $form_id, '_cf7g_gumroad_settings', $settings );
	}

	// Enqueue admin JS on the CF7 settings screen.
	public static function enqueue_assets( string $hook ): void {
		if ( 'toplevel_page_wpcf7' !== $hook && strpos( $hook, 'wpcf7' ) === false ) {
			return;
		}

		wp_enqueue_script( 'cf7g-admin', CGC_URL . 'assets/js/admin.js', array( 'jquery' ), CGC_VERSION, true );
		wp_localize_script(
			'cf7g-admin',
			'CGC',
			array(
				'ajax' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
}