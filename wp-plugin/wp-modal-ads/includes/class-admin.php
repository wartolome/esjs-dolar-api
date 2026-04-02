<?php
/**
 * Admin panel class for WP Modal Ads.
 *
 * @package WP_Modal_Ads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Modal_Ads_Admin {

	/**
	 * Register hooks.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add admin menu page.
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'WP Modal Ads', 'wp-modal-ads' ),
			__( 'Modal Ads', 'wp-modal-ads' ),
			'manage_options',
			'wp-modal-ads',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting(
			'wp_modal_ads_group',
			WP_MODAL_ADS_OPTION_KEY,
			array( $this, 'sanitize_settings' )
		);

		// Section: General
		add_settings_section(
			'wp_modal_ads_general',
			__( 'Configuración general', 'wp-modal-ads' ),
			null,
			'wp-modal-ads'
		);

		add_settings_field(
			'enabled',
			__( 'Activar anuncios', 'wp-modal-ads' ),
			array( $this, 'field_enabled' ),
			'wp-modal-ads',
			'wp_modal_ads_general'
		);

		add_settings_field(
			'ad_url',
			__( 'URL del anuncio', 'wp-modal-ads' ),
			array( $this, 'field_ad_url' ),
			'wp-modal-ads',
			'wp_modal_ads_general'
		);

		add_settings_field(
			'modal_size',
			__( 'Tamaño del modal (%)', 'wp-modal-ads' ),
			array( $this, 'field_modal_size' ),
			'wp-modal-ads',
			'wp_modal_ads_general'
		);

		// Section: Skip button
		add_settings_section(
			'wp_modal_ads_skip',
			__( 'Botón Saltar anuncio', 'wp-modal-ads' ),
			null,
			'wp-modal-ads'
		);

		add_settings_field(
			'skip_delay',
			__( 'Tiempo antes de mostrar el botón (segundos)', 'wp-modal-ads' ),
			array( $this, 'field_skip_delay' ),
			'wp-modal-ads',
			'wp_modal_ads_skip'
		);

		// Section: Trigger
		add_settings_section(
			'wp_modal_ads_trigger',
			__( 'Disparador del anuncio', 'wp-modal-ads' ),
			null,
			'wp-modal-ads'
		);

		add_settings_field(
			'trigger',
			__( 'Tipo de disparador', 'wp-modal-ads' ),
			array( $this, 'field_trigger' ),
			'wp-modal-ads',
			'wp_modal_ads_trigger'
		);

		add_settings_field(
			'scroll_percent',
			__( 'Porcentaje de scroll para disparar (%)', 'wp-modal-ads' ),
			array( $this, 'field_scroll_percent' ),
			'wp-modal-ads',
			'wp_modal_ads_trigger'
		);

		add_settings_field(
			'time_delay',
			__( 'Tiempo de espera para disparar (segundos)', 'wp-modal-ads' ),
			array( $this, 'field_time_delay' ),
			'wp-modal-ads',
			'wp_modal_ads_trigger'
		);

		// Section: Limits
		add_settings_section(
			'wp_modal_ads_limits',
			__( 'Límites de visualización', 'wp-modal-ads' ),
			null,
			'wp-modal-ads'
		);

		add_settings_field(
			'max_per_hour',
			__( 'Máximo de anuncios por visitante por hora', 'wp-modal-ads' ),
			array( $this, 'field_max_per_hour' ),
			'wp-modal-ads',
			'wp_modal_ads_limits'
		);
	}

	/**
	 * Sanitize settings input.
	 *
	 * @param array $input Raw input.
	 * @return array Sanitized input.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		$sanitized['enabled']        = isset( $input['enabled'] ) ? '1' : '0';
		$sanitized['ad_url']         = isset( $input['ad_url'] ) ? esc_url_raw( trim( $input['ad_url'] ) ) : '';
		$sanitized['modal_size']     = isset( $input['modal_size'] ) ? absint( $input['modal_size'] ) : 100;
		$sanitized['skip_delay']     = isset( $input['skip_delay'] ) ? absint( $input['skip_delay'] ) : 5;
		$sanitized['max_per_hour']   = isset( $input['max_per_hour'] ) ? absint( $input['max_per_hour'] ) : 3;
		$sanitized['scroll_percent'] = isset( $input['scroll_percent'] ) ? absint( $input['scroll_percent'] ) : 30;
		$sanitized['time_delay']     = isset( $input['time_delay'] ) ? absint( $input['time_delay'] ) : 3;

		$allowed_triggers            = array( 'click', 'scroll', 'time', 'exit' );
		$sanitized['trigger']        = ( isset( $input['trigger'] ) && in_array( $input['trigger'], $allowed_triggers, true ) )
			? $input['trigger']
			: 'click';

		// Clamp modal_size between 10 and 100.
		$sanitized['modal_size'] = max( 10, min( 100, $sanitized['modal_size'] ) );

		return $sanitized;
	}

	/**
	 * Enqueue admin stylesheets.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_wp-modal-ads' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'wp-modal-ads-admin',
			WP_MODAL_ADS_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			WP_MODAL_ADS_VERSION
		);
	}

	/**
	 * Render the admin settings page.
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		include WP_MODAL_ADS_PLUGIN_DIR . 'admin/partials/admin-panel.php';
	}

	// -----------------------------------------------------------------------
	// Field renderers
	// -----------------------------------------------------------------------

	private function get( $key ) {
		$options = get_option( WP_MODAL_ADS_OPTION_KEY, array() );
		return isset( $options[ $key ] ) ? $options[ $key ] : '';
	}

	public function field_enabled() {
		$value = $this->get( 'enabled' );
		?>
		<label>
			<input type="checkbox" name="<?php echo esc_attr( WP_MODAL_ADS_OPTION_KEY ); ?>[enabled]" value="1" <?php checked( '1', $value ); ?> />
			<?php esc_html_e( 'Mostrar anuncios en el sitio', 'wp-modal-ads' ); ?>
		</label>
		<?php
	}

	public function field_ad_url() {
		$value = $this->get( 'ad_url' );
		?>
		<input
			type="url"
			id="wp_modal_ads_ad_url"
			name="<?php echo esc_attr( WP_MODAL_ADS_OPTION_KEY ); ?>[ad_url]"
			value="<?php echo esc_attr( $value ); ?>"
			class="regular-text"
			placeholder="https://ejemplo.com/anuncio"
		/>
		<p class="description"><?php esc_html_e( 'URL que se mostrará dentro del modal (iframe).', 'wp-modal-ads' ); ?></p>
		<?php
	}

	public function field_modal_size() {
		$value = $this->get( 'modal_size' );
		if ( '' === $value ) {
			$value = 100;
		}
		?>
		<input
			type="number"
			id="wp_modal_ads_modal_size"
			name="<?php echo esc_attr( WP_MODAL_ADS_OPTION_KEY ); ?>[modal_size]"
			value="<?php echo esc_attr( $value ); ?>"
			min="10"
			max="100"
			step="5"
			class="small-text"
		/> %
		<p class="description"><?php esc_html_e( 'Porcentaje del tamaño de pantalla que ocupará el modal (10–100). Use 100 para pantalla completa.', 'wp-modal-ads' ); ?></p>
		<?php
	}

	public function field_skip_delay() {
		$value = $this->get( 'skip_delay' );
		if ( '' === $value ) {
			$value = 5;
		}
		?>
		<input
			type="number"
			id="wp_modal_ads_skip_delay"
			name="<?php echo esc_attr( WP_MODAL_ADS_OPTION_KEY ); ?>[skip_delay]"
			value="<?php echo esc_attr( $value ); ?>"
			min="0"
			max="60"
			step="1"
			class="small-text"
		/> <?php esc_html_e( 'segundos', 'wp-modal-ads' ); ?>
		<p class="description"><?php esc_html_e( 'Segundos que deben transcurrir antes de que aparezca el botón "Saltar anuncio". Use 0 para mostrarlo inmediatamente.', 'wp-modal-ads' ); ?></p>
		<?php
	}

	public function field_trigger() {
		$value   = $this->get( 'trigger' );
		$options = array(
			'click'  => __( 'Clic en cualquier lugar', 'wp-modal-ads' ),
			'scroll' => __( 'Scroll (porcentaje de página)', 'wp-modal-ads' ),
			'time'   => __( 'Tiempo de espera', 'wp-modal-ads' ),
			'exit'   => __( 'Intención de salida (mover cursor hacia arriba)', 'wp-modal-ads' ),
		);
		?>
		<select
			id="wp_modal_ads_trigger"
			name="<?php echo esc_attr( WP_MODAL_ADS_OPTION_KEY ); ?>[trigger]"
		>
			<?php foreach ( $options as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Evento que dispara la aparición del modal de anuncio.', 'wp-modal-ads' ); ?></p>
		<?php
	}

	public function field_scroll_percent() {
		$value = $this->get( 'scroll_percent' );
		if ( '' === $value ) {
			$value = 30;
		}
		?>
		<input
			type="number"
			id="wp_modal_ads_scroll_percent"
			name="<?php echo esc_attr( WP_MODAL_ADS_OPTION_KEY ); ?>[scroll_percent]"
			value="<?php echo esc_attr( $value ); ?>"
			min="1"
			max="100"
			step="1"
			class="small-text"
		/> %
		<p class="description"><?php esc_html_e( 'Porcentaje de scroll de la página que activa el modal (solo cuando el disparador es "Scroll").', 'wp-modal-ads' ); ?></p>
		<?php
	}

	public function field_time_delay() {
		$value = $this->get( 'time_delay' );
		if ( '' === $value ) {
			$value = 3;
		}
		?>
		<input
			type="number"
			id="wp_modal_ads_time_delay"
			name="<?php echo esc_attr( WP_MODAL_ADS_OPTION_KEY ); ?>[time_delay]"
			value="<?php echo esc_attr( $value ); ?>"
			min="1"
			max="300"
			step="1"
			class="small-text"
		/> <?php esc_html_e( 'segundos', 'wp-modal-ads' ); ?>
		<p class="description"><?php esc_html_e( 'Segundos de espera antes de abrir el modal automáticamente (solo cuando el disparador es "Tiempo de espera").', 'wp-modal-ads' ); ?></p>
		<?php
	}

	public function field_max_per_hour() {
		$value = $this->get( 'max_per_hour' );
		if ( '' === $value ) {
			$value = 3;
		}
		?>
		<input
			type="number"
			id="wp_modal_ads_max_per_hour"
			name="<?php echo esc_attr( WP_MODAL_ADS_OPTION_KEY ); ?>[max_per_hour]"
			value="<?php echo esc_attr( $value ); ?>"
			min="1"
			max="100"
			step="1"
			class="small-text"
		/> <?php esc_html_e( 'por hora', 'wp-modal-ads' ); ?>
		<p class="description"><?php esc_html_e( 'Cantidad máxima de veces que se mostrará el anuncio al mismo visitante dentro de una hora.', 'wp-modal-ads' ); ?></p>
		<?php
	}
}
