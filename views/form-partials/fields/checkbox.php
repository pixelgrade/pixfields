<?php defined('ABSPATH') or die;
/* @var PixFieldsFormField $field */
/* @var PixFieldsForm $form */
/* @var mixed $default */
/* @var string $name */
/* @var string $idname */
/* @var string $label */
/* @var string $desc */
/* @var string $rendering */

// [!!] the counter field needs to be able to work inside other fields; if
// the field is in another field it will have a null label

$checked = $form->autovalue($name, $default);
$options = $this->getmeta('options', array());

if ( ! empty( $options ) ) { ?>
	<div class="checkbox">
		<ul>
			<?php
			foreach ( $options as $key => $option ) {
				$attrs = array(
					'name' => $name . '[' . $key . ']',
					'id'   => $idname . '[' . $key . ']',
				); ?>
				<li>
					<input type="checkbox" <?php echo $field->htmlattributes( $attrs ); ?> <?php if ( isset( $checked[ $key ] ) && $checked[ $key ] == 'on' ) {
						echo 'checked="checked"';
					} ?>/>
					<?php echo $option; ?>
				</li>
			<?php } ?>
		</ul>
	</div>
<?php
}