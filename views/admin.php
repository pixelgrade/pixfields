<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should
 * provide the user interface to the end user.
 *
 * @package   PixTypes
 * @author    Pixelgrade <contact@pixelgrade.com>
 * @license   GPL-2.0+
 * @link      http://pixelgrade.com
 * @copyright 2013 Pixel Grade Media
 */

$config = include pixfields::pluginpath() . 'plugin-config' . EXT;

// invoke processor
$processor = pixfields::processor( $config );
$status    = $processor->status();
$errors    = $processor->errors(); ?>

<div class="wrap" id="pixfields_form">

	<div id="icon-options-general" class="icon32"><br></div>

	<h2><?php _e( 'PixFields', 'pixfields_txtd' ); ?></h2>

	<?php if ( $processor->ok() ): ?>

		<?php if ( ! empty( $errors ) ): ?>
			<br/>
			<p class="update-nag">
				<strong><?php _e( 'Unable to save settings.', 'pixfields_txtd' ); ?></strong>
				<?php _e( 'Please check the fields for errors and typos.', 'pixfields_txtd' ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $processor->performed_update() ): ?>
			<br/>
			<p class="update-nag">
				<?php _e( 'Settings have been updated.', 'pixfields_txtd' ); ?>
			</p>
		<?php endif;

		echo $f = pixfields::form( $config, $processor );
		echo $f->field( 'hiddens' )->render();
		echo $f->field( 'general' )->render();
		//echo $f->field( 'fields_manager' )->render(); ?>

		<?php wp_nonce_field( 'pixfields-save-settings' ); ?>

		<button type="submit" class="button button-primary">
			<?php _e( 'Save Changes', 'pixfields_txtd' ); ?>
		</button>

		<?php echo $f->endform() ?>

	<?php elseif ( $status['state'] == 'error' ): ?>

		<h3><?php _e( 'Critical Error', 'pixfields_txtd' ); ?></h3>

		<p><?php echo $status['message'] ?></p>

	<?php endif; ?>
</div>
