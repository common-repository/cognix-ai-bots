<div class="wrap">
	<h2>Cognix AI Bots Settings</h2>
	<form method="post" action="options.php">
		<?php
		if ( ! defined( 'ABSPATH' ) ) exit;
		
		settings_fields( 'cognix_settings' );
		do_settings_sections( 'cognix_settings' );
		$base_url = get_option( 'cognix_base_url' );
		$user_consent = get_option( 'cognix_user_consent' );
		?>
        <input type="hidden" name="cognix_user_consent" value="<?php echo esc_attr( $user_consent ); ?>"/>
		<table class="form-table">
			<tr>
				<th scope="row">Base URL</th>
				<td>
					<input type="text" id="cognix_base_url" name="cognix_base_url" value="<?php echo esc_attr( $base_url ); ?>" class="regular-text"/>
				</td>
			</tr>
			<tr>
				<th scope="row">Default Base URL</th>
				<td>https://cognix.ai/llmToolsJavaApi</td>
			</tr>
		</table>
		<?php submit_button( 'Save Settings' ); ?>
	</form>
</div>
