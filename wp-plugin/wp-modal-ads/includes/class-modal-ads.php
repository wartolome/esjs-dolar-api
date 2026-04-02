<?php
/**
 * Frontend class for WP Modal Ads.
 *
 * Enqueues scripts/styles and injects the modal markup + configuration
 * as an inline JSON object so the JavaScript can read it.
 *
 * @package WP_Modal_Ads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Modal_Ads_Frontend {

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Register hooks.
	 */
	public function init() {
		$this->settings = get_option( WP_MODAL_ADS_OPTION_KEY, array() );

		// Only run on the frontend when ads are enabled and a URL is provided.
		if ( is_admin() ) {
			return;
		}

		if ( empty( $this->settings['enabled'] ) || '0' === $this->settings['enabled'] ) {
			return;
		}

		if ( empty( $this->settings['ad_url'] ) ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'render_modal_html' ) );
	}

	/**
	 * Enqueue frontend CSS and JS.
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'wp-modal-ads',
			WP_MODAL_ADS_PLUGIN_URL . 'assets/css/modal-ads.css',
			array(),
			WP_MODAL_ADS_VERSION
		);

		wp_enqueue_script(
			'wp-modal-ads',
			WP_MODAL_ADS_PLUGIN_URL . 'assets/js/modal-ads.js',
			array(),
			WP_MODAL_ADS_VERSION,
			true
		);

		// Pass settings to JS.
		wp_localize_script(
			'wp-modal-ads',
			'wpModalAdsConfig',
			array(
				'adUrl'         => esc_url( $this->settings['ad_url'] ),
				'modalSize'     => isset( $this->settings['modal_size'] ) ? intval( $this->settings['modal_size'] ) : 100,
				'skipDelay'     => isset( $this->settings['skip_delay'] ) ? intval( $this->settings['skip_delay'] ) : 5,
				'trigger'       => isset( $this->settings['trigger'] ) ? $this->settings['trigger'] : 'click',
				'scrollPercent' => isset( $this->settings['scroll_percent'] ) ? intval( $this->settings['scroll_percent'] ) : 30,
				'timeDelay'     => isset( $this->settings['time_delay'] ) ? intval( $this->settings['time_delay'] ) : 3,
				'maxPerHour'    => isset( $this->settings['max_per_hour'] ) ? intval( $this->settings['max_per_hour'] ) : 3,
			)
		);
	}

	/**
	 * Output the modal HTML structure at the end of <body>.
	 */
	public function render_modal_html() {
		?>
		<div id="wp-modal-ads-overlay" class="wp-modal-ads-overlay" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Anuncio', 'wp-modal-ads' ); ?>" hidden>
			<div id="wp-modal-ads-container" class="wp-modal-ads-container">
				<button
					id="wp-modal-ads-skip"
					class="wp-modal-ads-skip"
					type="button"
					aria-label="<?php esc_attr_e( 'Saltar anuncio', 'wp-modal-ads' ); ?>"
					hidden
				>
					<span id="wp-modal-ads-skip-countdown" class="wp-modal-ads-skip-countdown" aria-hidden="true"></span>
					<span class="wp-modal-ads-skip-label"><?php esc_html_e( 'Saltar anuncio', 'wp-modal-ads' ); ?> &#10006;</span>
				</button>
				<iframe
					id="wp-modal-ads-iframe"
					class="wp-modal-ads-iframe"
					src=""
					allowfullscreen
					title="<?php esc_attr_e( 'Anuncio', 'wp-modal-ads' ); ?>"
					sandbox="allow-scripts allow-popups allow-forms"
				></iframe>
			</div>
		</div>
		<?php
	}
}
