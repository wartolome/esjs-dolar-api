<?php
/**
 * Admin panel template for WP Modal Ads.
 *
 * @package WP_Modal_Ads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wp-modal-ads-admin">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php settings_errors( 'wp_modal_ads_group' ); ?>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'wp_modal_ads_group' );
		do_settings_sections( 'wp-modal-ads' );
		submit_button( __( 'Guardar cambios', 'wp-modal-ads' ) );
		?>
	</form>

	<hr>

	<h2><?php esc_html_e( 'Vista previa del modal', 'wp-modal-ads' ); ?></h2>
	<p>
		<button type="button" id="wp-modal-ads-preview-btn" class="button button-secondary">
			<?php esc_html_e( 'Previsualizar modal con la URL configurada', 'wp-modal-ads' ); ?>
		</button>
	</p>
	<p class="description">
		<?php esc_html_e( 'Guarda los cambios antes de previsualizar para ver los últimos ajustes.', 'wp-modal-ads' ); ?>
	</p>

	<script>
	(function () {
		var btn = document.getElementById('wp-modal-ads-preview-btn');
		if (!btn) return;

		btn.addEventListener('click', function () {
			var urlField = document.getElementById('wp_modal_ads_ad_url');
			var sizeField = document.getElementById('wp_modal_ads_modal_size');
			var skipField = document.getElementById('wp_modal_ads_skip_delay');

			var adUrl = urlField ? urlField.value.trim() : '';
			var size  = sizeField ? parseInt(sizeField.value, 10) || 100 : 100;
			var skip  = skipField ? parseInt(skipField.value, 10) || 5 : 5;

			if (!adUrl) {
				alert('<?php echo esc_js( __( 'Ingresa una URL de anuncio válida para previsualizar.', 'wp-modal-ads' ) ); ?>');
				return;
			}

			openPreviewModal(adUrl, size, skip);
		});

		function openPreviewModal(adUrl, size, skip) {
			var overlay = document.createElement('div');
			overlay.id = 'wp-modal-ads-preview-overlay';
			overlay.style.cssText = [
				'position:fixed;inset:0;background:rgba(0,0,0,0.7);',
				'display:flex;align-items:center;justify-content:center;z-index:999999;'
			].join('');

			var pct = (size >= 10 && size <= 100) ? size : 100;

			var container = document.createElement('div');
			container.style.cssText = [
				'position:relative;width:' + pct + 'vw;height:' + pct + 'vh;',
				'background:#000;overflow:hidden;'
			].join('');

			var skipBtn = document.createElement('button');
			skipBtn.textContent = skip > 0
				? '<?php echo esc_js( __( 'Saltar en', 'wp-modal-ads' ) ); ?> ' + skip + 's ✕'
				: '<?php echo esc_js( __( 'Saltar anuncio', 'wp-modal-ads' ) ); ?> ✕';
			skipBtn.style.cssText = [
				'position:absolute;top:12px;right:12px;z-index:10;',
				'padding:8px 14px;background:rgba(0,0,0,0.75);color:#fff;',
				'border:1px solid rgba(255,255,255,0.5);border-radius:4px;',
				'cursor:pointer;font-size:13px;'
			].join('');
			skipBtn.disabled = skip > 0;

			if (skip > 0) {
				var remaining = skip;
				var timer = setInterval(function () {
					remaining--;
					if (remaining <= 0) {
						clearInterval(timer);
						skipBtn.disabled = false;
						skipBtn.textContent = '<?php echo esc_js( __( 'Saltar anuncio', 'wp-modal-ads' ) ); ?> ✕';
					} else {
						skipBtn.textContent = '<?php echo esc_js( __( 'Saltar en', 'wp-modal-ads' ) ); ?> ' + remaining + 's ✕';
					}
				}, 1000);
			}

			skipBtn.addEventListener('click', function () {
				document.body.removeChild(overlay);
			});

			var iframe = document.createElement('iframe');
			iframe.src = adUrl;
			iframe.style.cssText = 'width:100%;height:100%;border:none;';
			iframe.setAttribute('allowfullscreen', '');
			iframe.setAttribute('sandbox', 'allow-scripts allow-popups allow-forms');

			container.appendChild(skipBtn);
			container.appendChild(iframe);
			overlay.appendChild(container);
			document.body.appendChild(overlay);
		}
	})();
	</script>
</div>
