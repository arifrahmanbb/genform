<?php

/**
 * Freemius SDK Integration
 *
 * Initializes the Freemius SDK for GenForm.
 * This provides licensing, in-dashboard upgrades, analytics, and auto-updates.
 *
 * IMPORTANT: You must replace the placeholder values below with your actual
 * Freemius product credentials from https://dashboard.freemius.com
 *
 * @package GenForm
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create a helper function for easy SDK access.
 *
 * @return \Freemius
 */
function genform_fs(): \Freemius {
	global $genform_fs;

	if ( ! isset( $genform_fs ) ) {
		// Include Freemius SDK.
		require_once GENFORM_PATH . 'vendor/freemius/wordpress-sdk/start.php';

		$genform_fs = fs_dynamic_init(
			array(
				'id'                  => '00000', // @todo Replace with your Freemius Product ID.
				'slug'                => 'genform',
				'premium_slug'        => 'genform-pro',
				'type'                => 'plugin',
				'public_key'          => 'pk_YOUR_PUBLIC_KEY', // @todo Replace with your Freemius Public Key.
				'is_premium'          => false,
				'premium_suffix'      => 'Pro',
				'has_premium_version' => true,
				'has_addons'          => false,
				'has_paid_plans'      => true,
				'has_affiliation'     => 'selected',
				'menu'                => array(
					'slug'    => 'genform',
					'support' => false,
				),
				'is_live'             => true,
			)
		);
	}

	return $genform_fs;
}

// Init Freemius.
genform_fs();

// Signal that SDK was initiated.
do_action( 'genform_fs_loaded' );
