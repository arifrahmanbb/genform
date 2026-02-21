<?php

/**
 * Pro Feature Gate
 *
 * Lightweight checker that determines whether a Pro feature is available.
 * This class lives in the free plugin and renders upsell badges when Pro is not active.
 *
 * It integrates with Freemius SDK for license validation. When Freemius reports
 * a valid license with a paid plan, all Pro features become available.
 *
 * @package GenForm
 */

namespace GenForm\Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FeatureGate {

	/**
	 * Pro feature registry with titles, descriptions, icons, and preview images.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function getFeatures(): array {
		return array(
			'conditional_logic' => array(
				'title'       => __( 'Conditional Logic', 'genform' ),
				'description' => __( 'Show or hide fields based on user input. Create smart, dynamic forms that adapt to your visitors.', 'genform' ),
				'icon'        => 'randomize',
				'preview'     => 'conditional-logic.png',
			),
			'multi_step'        => array(
				'title'       => __( 'Multi-Step Forms', 'genform' ),
				'description' => __( 'Split long forms into beautiful step-by-step pages with animated progress bars.', 'genform' ),
				'icon'        => 'editor-insertmore',
				'preview'     => 'multi-step.png',
			),
			'file_upload'       => array(
				'title'       => __( 'File Uploads', 'genform' ),
				'description' => __( 'Let users upload documents, images, and files with drag & drop, file type validation, and size limits.', 'genform' ),
				'icon'        => 'upload',
				'preview'     => 'file-upload.png',
			),
			'payments'          => array(
				'title'       => __( 'Payment Collection', 'genform' ),
				'description' => __( 'Accept payments via Stripe and PayPal directly in your forms. One-time or recurring.', 'genform' ),
				'icon'        => 'money-alt',
				'preview'     => 'payments.png',
			),
			'visual_reports'    => array(
				'title'       => __( 'Visual Reports & Analytics', 'genform' ),
				'description' => __( 'Beautiful charts, graphs, and stats dashboard. Track submissions, conversion rates, and revenue.', 'genform' ),
				'icon'        => 'chart-bar',
				'preview'     => 'visual-reports.png',
			),
			'signature'         => array(
				'title'       => __( 'Digital Signature', 'genform' ),
				'description' => __( 'Capture legally binding signatures with a touch-friendly canvas. Perfect for contracts and agreements.', 'genform' ),
				'icon'        => 'art',
				'preview'     => 'signature.png',
			),
			'star_rating'       => array(
				'title'       => __( 'Star Rating', 'genform' ),
				'description' => __( 'Interactive star rating field for reviews, feedback forms, and NPS surveys.', 'genform' ),
				'icon'        => 'star-filled',
				'preview'     => '',
			),
			'integrations'      => array(
				'title'       => __( 'Integrations Hub', 'genform' ),
				'description' => __( 'Connect to Zapier, Mailchimp, Slack, Google Sheets, and 50+ services.', 'genform' ),
				'icon'        => 'admin-plugins',
				'preview'     => 'integrations.png',
			),
			'save_resume'       => array(
				'title'       => __( 'Save & Resume', 'genform' ),
				'description' => __( 'Let users save their progress and comeback later. Perfect for long application forms.', 'genform' ),
				'icon'        => 'backup',
				'preview'     => '',
			),
			'calculations'      => array(
				'title'       => __( 'Calculations', 'genform' ),
				'description' => __( 'Build quote calculators, order forms, and pricing estimators with real-time math.', 'genform' ),
				'icon'        => 'calculator',
				'preview'     => '',
			),
			'form_abandonment'  => array(
				'title'       => __( 'Form Abandonment', 'genform' ),
				'description' => __( 'Capture partial entries from visitors who leave. Recover lost leads automatically.', 'genform' ),
				'icon'        => 'warning',
				'preview'     => '',
			),
			'repeater'          => array(
				'title'       => __( 'Repeater Field', 'genform' ),
				'description' => __( 'Dynamic "add more" rows for work experience, order items, and any repeating data.', 'genform' ),
				'icon'        => 'plus-alt',
				'preview'     => '',
			),
			'address'           => array(
				'title'       => __( 'Address Field', 'genform' ),
				'description' => __( 'Structured address input with street, city, state, zip, and country fields.', 'genform' ),
				'icon'        => 'location',
				'preview'     => '',
			),
			'rich_text'         => array(
				'title'       => __( 'Rich Text Editor', 'genform' ),
				'description' => __( 'WYSIWYG editor field for formatted content input in forms.', 'genform' ),
				'icon'        => 'editor-paragraph',
				'preview'     => '',
			),
			'survey'            => array(
				'title'       => __( 'Surveys & Polls', 'genform' ),
				'description' => __( 'Likert scales, NPS scores, opinion scales for professional survey forms.', 'genform' ),
				'icon'        => 'editor-alignleft',
				'preview'     => '',
			),
			'user_registration' => array(
				'title'       => __( 'User Registration', 'genform' ),
				'description' => __( 'Create WordPress user accounts directly from form submissions.', 'genform' ),
				'icon'        => 'admin-users',
				'preview'     => '',
			),
			'post_submission'   => array(
				'title'       => __( 'Post Submission', 'genform' ),
				'description' => __( 'Let users create blog posts and articles from the frontend via forms.', 'genform' ),
				'icon'        => 'edit-page',
				'preview'     => '',
			),
			'pdf_generator'     => array(
				'title'       => __( 'PDF Generation', 'genform' ),
				'description' => __( 'Generate PDF documents from entries. Perfect for invoices, receipts, and certificates.', 'genform' ),
				'icon'        => 'media-document',
				'preview'     => '',
			),
			'geolocation'       => array(
				'title'       => __( 'Geolocation', 'genform' ),
				'description' => __( 'Auto-detect user country, city, and coordinates with every submission.', 'genform' ),
				'icon'        => 'location-alt',
				'preview'     => '',
			),
			'coupons'           => array(
				'title'       => __( 'Coupons & Discounts', 'genform' ),
				'description' => __( 'Create promo codes with percentage or fixed discounts for payment forms.', 'genform' ),
				'icon'        => 'tag',
				'preview'     => '',
			),
			'landing_page'      => array(
				'title'       => __( 'Form Landing Pages', 'genform' ),
				'description' => __( 'Create distraction-free, conversion-optimized pages for individual forms.', 'genform' ),
				'icon'        => 'welcome-widgets-menus',
				'preview'     => '',
			),
			'paypal'            => array(
				'title'       => __( 'PayPal Payments', 'genform' ),
				'description' => __( 'Accept PayPal payments directly in your forms with one-click checkout.', 'genform' ),
				'icon'        => 'cart',
				'preview'     => '',
			),
		);
	}

	/**
	 * Check whether a specific Pro feature is available.
	 *
	 * Priority:
	 * 1. Check Freemius license (if SDK available)
	 * 2. Fall back to genform_pro_features filter (for add-on plugin approach)
	 *
	 * @param string $feature Feature slug (e.g. 'conditional_logic').
	 */
	public static function has( string $feature ): bool {
		if ( self::isFreemiusPaying() ) {
			return true;
		}

		$features = apply_filters( 'genform_pro_features', array() );
		return in_array( $feature, $features, true );
	}

	/**
	 * Check if any Pro license is active.
	 */
	public static function isProActive(): bool {
		if ( self::isFreemiusPaying() ) {
			return true;
		}
		return (bool) apply_filters( 'genform_is_pro_active', false );
	}

	/**
	 * Check if Freemius reports an active paid plan.
	 */
	private static function isFreemiusPaying(): bool {
		if ( ! function_exists( 'genform_fs' ) ) {
			return false;
		}

		$fs = genform_fs();

		return method_exists( $fs, 'is_paying' ) && $fs->is_paying();
	}

	/**
	 * Check if user is on a specific Freemius plan (by slug).
	 *
	 * @param string $plan_slug Plan slug (e.g. 'personal', 'agency', 'unlimited').
	 */
	public static function isOnPlan( string $plan_slug ): bool {
		if ( ! function_exists( 'genform_fs' ) ) {
			return false;
		}

		$fs = genform_fs();

		return method_exists( $fs, 'is_plan' ) && $fs->is_plan( $plan_slug );
	}

	/**
	 * Check if user is on a trial.
	 */
	public static function isTrial(): bool {
		if ( ! function_exists( 'genform_fs' ) ) {
			return false;
		}

		$fs = genform_fs();

		return method_exists( $fs, 'is_trial' ) && $fs->is_trial();
	}

	/**
	 * Render a "PRO" badge for the builder UI.
	 *
	 * @param string $feature Feature slug.
	 * @param string $label   Optional custom label text.
	 */
	public static function proBadge( string $feature, string $label = '' ): string {
		if ( self::has( $feature ) ) {
			return '';
		}
		$text = $label ?: esc_html__( 'PRO', 'genform' );
		return sprintf(
			'<span class="gfm-pro-badge" data-feature="%s" title="%s">%s</span>',
			esc_attr( $feature ),
			esc_attr__( 'Upgrade to GenForm Pro', 'genform' ),
			esc_html( $text )
		);
	}

	/**
	 * Render a visual preview card with blurred screenshot for Pro upsell.
	 *
	 * Shows a real screenshot of the feature with a CSS blur overlay,
	 * feature title, description, and upgrade CTA. On hover, the blur
	 * slightly reduces to tease the user.
	 *
	 * @param string $feature Feature slug from the registry.
	 */
	public static function previewCard( string $feature ): void {
		if ( self::has( $feature ) ) {
			return;
		}

		$features = self::getFeatures();
		$info = $features[ $feature ] ?? null;

		if ( ! $info ) {
			return;
		}

		$preview_url = '';
		if ( ! empty( $info['preview'] ) ) {
			$preview_url = GENFORM_URL . 'assets/images/pro-previews/' . $info['preview'];
		}
		?>
		<div class="gfm-pro-preview-card" data-feature="<?php echo esc_attr( $feature ); ?>">
			<?php if ( $preview_url ) : ?>
				<div class="gfm-pro-preview-image">
					<img
						src="<?php echo esc_url( $preview_url ); ?>"
						alt="<?php echo esc_attr( $info['title'] ); ?>"
						loading="lazy"
					>
					<div class="gfm-pro-preview-blur-overlay"></div>
					<div class="gfm-pro-preview-lock">
						<span class="dashicons dashicons-lock"></span>
					</div>
				</div>
			<?php endif; ?>
			<div class="gfm-pro-preview-content">
				<div class="gfm-pro-preview-header">
					<span class="dashicons dashicons-<?php echo esc_attr( $info['icon'] ); ?>"></span>
					<h4><?php echo esc_html( $info['title'] ); ?></h4>
					<span class="gfm-pro-badge"><?php esc_html_e( 'PRO', 'genform' ); ?></span>
				</div>
				<p><?php echo esc_html( $info['description'] ); ?></p>
				<a href="<?php echo esc_url( self::upgradeUrl() ); ?>" class="gfm-btn gfm-btn-pro-upgrade gfm-pro-field-locked" data-pro="<?php echo esc_attr( $feature ); ?>">
					<span class="dashicons dashicons-superhero-alt"></span>
					<?php esc_html_e( 'Unlock Feature', 'genform' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render all Pro feature preview cards as a grid.
	 * Used on the settings page and potentially a dedicated "Pro Features" page.
	 *
	 * @param array $feature_slugs Optional. Specific features to show. Default: all.
	 */
	public static function previewGrid( array $feature_slugs = array() ): void {
		if ( self::isProActive() ) {
			return;
		}

		$features = self::getFeatures();
		$show = ! empty( $feature_slugs ) ? $feature_slugs : array_keys( $features );
		?>
		<div class="gfm-pro-preview-grid">
			<?php foreach ( $show as $slug ) : ?>
				<?php self::previewCard( $slug ); ?>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render a full locked card UI section for Pro upsell (simple version without image).
	 *
	 * @param string $feature     Feature slug.
	 * @param string $title       Title of the locked feature.
	 * @param string $description Description of what the feature does.
	 * @param string $icon        Dashicon class name.
	 */
	public static function lockedCard( string $feature, string $title, string $description, string $icon = 'lock' ): void {
		if ( self::has( $feature ) ) {
			return;
		}
		?>
		<div class="gfm-pro-locked-card" data-feature="<?php echo esc_attr( $feature ); ?>">
			<div class="gfm-pro-locked-inner">
				<div class="gfm-pro-locked-icon">
					<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
				</div>
				<h3><?php echo esc_html( $title ); ?></h3>
				<p><?php echo esc_html( $description ); ?></p>
				<span class="gfm-pro-badge gfm-pro-badge-lg"><?php esc_html_e( 'PRO', 'genform' ); ?></span>
				<a href="<?php echo esc_url( self::upgradeUrl() ); ?>" class="gfm-btn gfm-btn-pro-upgrade gfm-pro-field-locked" data-pro="<?php echo esc_attr( $feature ); ?>">
					<span class="dashicons dashicons-lock"></span>
					<?php esc_html_e( 'Upgrade to GenForm Pro', 'genform' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Get the Pro upgrade URL.
	 *
	 * Uses Freemius in-dashboard upgrade URL when available,
	 * otherwise falls back to external pricing page.
	 */
	public static function upgradeUrl(): string {
		if ( function_exists( 'genform_fs' ) ) {
			$fs = genform_fs();
			if ( method_exists( $fs, 'get_upgrade_url' ) ) {
				return $fs->get_upgrade_url();
			}
		}

		return 'https://genform.developer-jewell.dev/pro';
	}

	/**
	 * Get the Freemius pricing page URL for in-dashboard checkout.
	 */
	public static function pricingUrl(): string {
		if ( function_exists( 'genform_fs' ) ) {
			$fs = genform_fs();
			if ( method_exists( $fs, 'pricing_url' ) ) {
				return $fs->pricing_url();
			}
		}

		return self::upgradeUrl();
	}

	/**
	 * Get the Freemius account page URL.
	 */
	public static function accountUrl(): string {
		if ( function_exists( 'genform_fs' ) ) {
			$fs = genform_fs();
			if ( method_exists( $fs, 'get_account_url' ) ) {
				return $fs->get_account_url();
			}
		}

		return admin_url( 'admin.php?page=genform-account' );
	}
}
