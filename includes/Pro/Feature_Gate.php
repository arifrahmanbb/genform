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
			// ── Form Logic ──
			'conditional_logic' => array(
				'title'       => __( 'Conditional Logic', 'genform' ),
				'description' => __( 'Show or hide fields based on user input. Create smart, dynamic forms.', 'genform' ),
				'icon'        => 'randomize',
				'preview'     => 'conditional-logic.png',
			),
			'multi_step'        => array(
				'title'       => __( 'Multi-Step Forms', 'genform' ),
				'description' => __( 'Split long forms into step-by-step pages with animated progress bars.', 'genform' ),
				'icon'        => 'editor-insertmore',
				'preview'     => 'multi-step.png',
			),
			'calculations'      => array(
				'title'       => __( 'Calculations', 'genform' ),
				'description' => __( 'Quote calculators, order forms, and pricing estimators with real-time math.', 'genform' ),
				'icon'        => 'calculator',
				'preview'     => '',
			),
			'save_resume'       => array(
				'title'       => __( 'Save & Resume', 'genform' ),
				'description' => __( 'Let users save progress and return later with a unique link.', 'genform' ),
				'icon'        => 'backup',
				'preview'     => '',
			),
			'form_abandonment'  => array(
				'title'       => __( 'Form Abandonment', 'genform' ),
				'description' => __( 'Capture partial entries from visitors who leave. Recover lost leads.', 'genform' ),
				'icon'        => 'warning',
				'preview'     => '',
			),
			// ── Pro Fields ──
			'file_upload'       => array(
				'title'       => __( 'File Uploads', 'genform' ),
				'description' => __( 'Drag & drop file uploads with type validation and size limits.', 'genform' ),
				'icon'        => 'upload',
				'preview'     => 'file-upload.png',
			),
			'signature'         => array(
				'title'       => __( 'Digital Signature', 'genform' ),
				'description' => __( 'Touch-friendly signature canvas for contracts and agreements.', 'genform' ),
				'icon'        => 'art',
				'preview'     => 'signature.png',
			),
			'star_rating'       => array(
				'title'       => __( 'Star Rating', 'genform' ),
				'description' => __( 'Interactive star rating for reviews and feedback forms.', 'genform' ),
				'icon'        => 'star-filled',
				'preview'     => '',
			),
			'repeater'          => array(
				'title'       => __( 'Repeater Field', 'genform' ),
				'description' => __( 'Dynamic "add more" rows for work experience, order items, etc.', 'genform' ),
				'icon'        => 'plus-alt',
				'preview'     => '',
			),
			'address'           => array(
				'title'       => __( 'Address Field', 'genform' ),
				'description' => __( 'Structured address with street, city, state, zip, and country.', 'genform' ),
				'icon'        => 'location',
				'preview'     => '',
			),
			'rich_text'         => array(
				'title'       => __( 'Rich Text Editor', 'genform' ),
				'description' => __( 'WYSIWYG editor for formatted content input in forms.', 'genform' ),
				'icon'        => 'editor-paragraph',
				'preview'     => '',
			),
			'survey'            => array(
				'title'       => __( 'Surveys & Polls', 'genform' ),
				'description' => __( 'Likert scales, NPS scores, and opinion scales for surveys.', 'genform' ),
				'icon'        => 'editor-alignleft',
				'preview'     => '',
			),
			// ── Payment Gateways (each separate) ──
			'stripe'            => array(
				'title'       => __( 'Stripe Payments', 'genform' ),
				'description' => __( 'Accept credit card payments securely via Stripe. One-time or recurring.', 'genform' ),
				'icon'        => 'money-alt',
				'preview'     => 'payments.png',
			),
			'paypal'            => array(
				'title'       => __( 'PayPal Payments', 'genform' ),
				'description' => __( 'Accept PayPal payments directly in your forms with one-click checkout.', 'genform' ),
				'icon'        => 'cart',
				'preview'     => '',
			),
			'coupons'           => array(
				'title'       => __( 'Coupons & Discounts', 'genform' ),
				'description' => __( 'Create promo codes with percentage or fixed discounts for payment forms.', 'genform' ),
				'icon'        => 'tag',
				'preview'     => '',
			),
			// ── Integrations (each separate) ──
			'zapier'            => array(
				'title'       => __( 'Zapier', 'genform' ),
				'description' => __( 'Connect your forms to 5,000+ apps via Zapier webhooks. Automate everything.', 'genform' ),
				'icon'        => 'admin-plugins',
				'preview'     => '',
			),
			'mailchimp'         => array(
				'title'       => __( 'Mailchimp', 'genform' ),
				'description' => __( 'Auto-subscribe form submitters to your Mailchimp audience and lists.', 'genform' ),
				'icon'        => 'email-alt',
				'preview'     => '',
			),
			'slack'             => array(
				'title'       => __( 'Slack Notifications', 'genform' ),
				'description' => __( 'Get instant Slack messages in your channel when a form is submitted.', 'genform' ),
				'icon'        => 'format-chat',
				'preview'     => '',
			),
			'google_sheets'     => array(
				'title'       => __( 'Google Sheets', 'genform' ),
				'description' => __( 'Send form submissions as rows directly to your Google Spreadsheet.', 'genform' ),
				'icon'        => 'media-spreadsheet',
				'preview'     => '',
			),
			// ── Reporting ──
			'visual_reports'    => array(
				'title'       => __( 'Visual Reports', 'genform' ),
				'description' => __( 'Charts, graphs, and stats dashboard. Track submissions and conversion.', 'genform' ),
				'icon'        => 'chart-bar',
				'preview'     => 'visual-reports.png',
			),
			// ── Features ──
			'user_registration' => array(
				'title'       => __( 'User Registration', 'genform' ),
				'description' => __( 'Create WordPress accounts directly from form submissions.', 'genform' ),
				'icon'        => 'admin-users',
				'preview'     => '',
			),
			'post_submission'   => array(
				'title'       => __( 'Post Submission', 'genform' ),
				'description' => __( 'Let users create blog posts from the frontend via forms.', 'genform' ),
				'icon'        => 'edit-page',
				'preview'     => '',
			),
			'entry_editor'      => array(
				'title'       => __( 'Entry Editor', 'genform' ),
				'description' => __( 'Edit submitted entry data directly from the admin dashboard.', 'genform' ),
				'icon'        => 'edit',
				'preview'     => '',
			),
			'pdf_generator'     => array(
				'title'       => __( 'PDF Generation', 'genform' ),
				'description' => __( 'Generate PDF documents from entries — invoices, receipts, certificates.', 'genform' ),
				'icon'        => 'media-document',
				'preview'     => '',
			),
			'geolocation'       => array(
				'title'       => __( 'Geolocation', 'genform' ),
				'description' => __( 'Auto-detect user country, city, and coordinates for each submission.', 'genform' ),
				'icon'        => 'location-alt',
				'preview'     => '',
			),
			'landing_page'      => array(
				'title'       => __( 'Form Landing Pages', 'genform' ),
				'description' => __( 'Distraction-free, conversion-optimized standalone pages for forms.', 'genform' ),
				'icon'        => 'welcome-widgets-menus',
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
	 * Render a visual preview card for a single Pro feature.
	 *
	 * Shows a clear screenshot with subtle opacity overlay (no blur).
	 * Cards without images show a gradient icon area instead.
	 * Each card highlights ONE single feature for maximum impact.
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

		$card_class = $preview_url ? 'gfm-pro-preview-card' : 'gfm-pro-preview-card gfm-pro-preview-card--no-image';
		?>
		<div class="<?php echo esc_attr( $card_class ); ?>" data-feature="<?php echo esc_attr( $feature ); ?>">
			<?php if ( $preview_url ) : ?>
				<div class="gfm-pro-preview-image">
					<img
						src="<?php echo esc_url( $preview_url ); ?>"
						alt="<?php echo esc_attr( $info['title'] ); ?>"
						loading="lazy"
					>
					<div class="gfm-pro-preview-overlay"></div>
					<div class="gfm-pro-preview-lock">
						<span class="dashicons dashicons-lock"></span>
					</div>
				</div>
			<?php else : ?>
				<div class="gfm-pro-preview-icon-area">
					<span class="dashicons dashicons-<?php echo esc_attr( $info['icon'] ); ?>"></span>
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
		<div class="gfm-pro-settings-header">
			<h2>
				<span class="dashicons dashicons-superhero-alt"></span>
				<?php esc_html_e( 'Unlock All Pro Features', 'genform' ); ?>
			</h2>
			<p>
				<?php
				printf(
					/* translators: %d: number of Pro features */
					esc_html__( '%d powerful features to supercharge your forms. Upgrade to unlock everything.', 'genform' ),
					count( $show )
				);
				?>
			</p>
		</div>
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

	/**
	 * Render the Pro upgrade modal with pricing tiers.
	 *
	 * Inspired by WPForms/Fluent Forms/Gravity Forms patterns:
	 * - 3 pricing plans with "Most Popular" badge
	 * - Feature bullet list with green checkmarks
	 * - Money-back guarantee + trust indicators
	 * - Context-aware: shows which feature triggered the modal
	 *
	 * Called via admin_footer hook to put the modal markup in the page.
	 */
	public static function renderUpgradeModal(): void {
		if ( self::isProActive() ) {
			return;
		}

		$upgrade_url = self::upgradeUrl();
		$pricing_url = self::pricingUrl();
		$features    = self::getFeatures();
		$total       = count( $features );
		?>
		<!-- GenForm Pro Upgrade Modal -->
		<div id="gfm-pro-upgrade-modal" class="gfm-modal gfm-hidden">
			<div class="gfm-modal-content gfm-pro-upgrade-modal-content">

				<!-- Header -->
				<div class="gfm-pro-upgrade-header">
					<div class="gfm-pro-upgrade-header-left">
						<span class="dashicons dashicons-superhero-alt"></span>
						<h3><?php esc_html_e( 'Upgrade to GenForm Pro', 'genform' ); ?></h3>
					</div>
					<span class="gfm-close-modal dashicons dashicons-no" title="<?php esc_attr_e( 'Close', 'genform' ); ?>"></span>
				</div>

				<div class="gfm-pro-upgrade-body">
					<!-- Feature context banner (dynamically updated by JS) -->
					<div class="gfm-pro-upgrade-context" id="gfm-pro-context-banner">
						<span class="dashicons dashicons-lock"></span>
						<span id="gfm-pro-context-text">
							<?php esc_html_e( 'This feature requires GenForm Pro.', 'genform' ); ?>
						</span>
					</div>

					<!-- Pricing Cards -->
					<div class="gfm-pro-pricing-grid">
						<!-- Personal Plan -->
						<div class="gfm-pro-plan-card">
							<div class="gfm-pro-plan-header">
								<h4><?php esc_html_e( 'Personal', 'genform' ); ?></h4>
								<div class="gfm-pro-plan-price">
									<span class="gfm-pro-price-amount">$49</span>
									<span class="gfm-pro-price-period">/<?php esc_html_e( 'year', 'genform' ); ?></span>
								</div>
								<span class="gfm-pro-plan-sites"><?php esc_html_e( '1 Site License', 'genform' ); ?></span>
							</div>
							<ul class="gfm-pro-plan-features">
								<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'All Pro Features', 'genform' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( '1 Year Updates', 'genform' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Priority Support', 'genform' ); ?></li>
								<li class="gfm-plan-feature-muted"><span class="dashicons dashicons-minus"></span><?php esc_html_e( 'Multisite Support', 'genform' ); ?></li>
							</ul>
							<a href="<?php echo esc_url( $upgrade_url ); ?>" class="gfm-btn gfm-pro-plan-btn">
								<?php esc_html_e( 'Get Personal', 'genform' ); ?>
							</a>
						</div>

						<!-- Agency Plan (Popular) -->
						<div class="gfm-pro-plan-card gfm-pro-plan-popular">
							<div class="gfm-pro-plan-badge"><?php esc_html_e( 'Most Popular', 'genform' ); ?></div>
							<div class="gfm-pro-plan-header">
								<h4><?php esc_html_e( 'Agency', 'genform' ); ?></h4>
								<div class="gfm-pro-plan-price">
									<span class="gfm-pro-price-amount">$99</span>
									<span class="gfm-pro-price-period">/<?php esc_html_e( 'year', 'genform' ); ?></span>
								</div>
								<span class="gfm-pro-plan-sites"><?php esc_html_e( '5 Site License', 'genform' ); ?></span>
							</div>
							<ul class="gfm-pro-plan-features">
								<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'All Pro Features', 'genform' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( '1 Year Updates', 'genform' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Priority Support', 'genform' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Multisite Support', 'genform' ); ?></li>
							</ul>
							<a href="<?php echo esc_url( $upgrade_url ); ?>" class="gfm-btn gfm-btn-pro-upgrade gfm-pro-plan-btn">
								<?php esc_html_e( 'Get Agency', 'genform' ); ?>
							</a>
						</div>

						<!-- Unlimited Plan -->
						<div class="gfm-pro-plan-card">
							<div class="gfm-pro-plan-header">
								<h4><?php esc_html_e( 'Unlimited', 'genform' ); ?></h4>
								<div class="gfm-pro-plan-price">
									<span class="gfm-pro-price-amount">$199</span>
									<span class="gfm-pro-price-period">/<?php esc_html_e( 'year', 'genform' ); ?></span>
								</div>
								<span class="gfm-pro-plan-sites"><?php esc_html_e( 'Unlimited Sites', 'genform' ); ?></span>
							</div>
							<ul class="gfm-pro-plan-features">
								<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'All Pro Features', 'genform' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Lifetime Updates', 'genform' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Priority Support', 'genform' ); ?></li>
								<li><span class="dashicons dashicons-yes-alt"></span><?php esc_html_e( 'Multisite Support', 'genform' ); ?></li>
							</ul>
							<a href="<?php echo esc_url( $upgrade_url ); ?>" class="gfm-btn gfm-pro-plan-btn">
								<?php esc_html_e( 'Get Unlimited', 'genform' ); ?>
							</a>
						</div>
					</div>

					<!-- Trust indicators -->
					<div class="gfm-pro-upgrade-trust">
						<div class="gfm-pro-trust-item">
							<span class="dashicons dashicons-shield-alt"></span>
							<?php esc_html_e( '14-Day Money-Back Guarantee', 'genform' ); ?>
						</div>
						<div class="gfm-pro-trust-item">
							<span class="dashicons dashicons-lock"></span>
							<?php esc_html_e( 'Secure Checkout', 'genform' ); ?>
						</div>
						<div class="gfm-pro-trust-item">
							<span class="dashicons dashicons-update"></span>
							<?php
							printf(
								/* translators: %d: total number of Pro features */
								esc_html__( '%d+ Pro Features', 'genform' ),
								$total
							);
							?>
						</div>
					</div>

					<!-- View All Features link -->
					<div class="gfm-pro-upgrade-footer">
						<a href="<?php echo esc_url( $pricing_url ); ?>" class="gfm-pro-view-all-link">
							<?php esc_html_e( 'View full feature comparison →', 'genform' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

