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
$post_types = get_post_types();

if ( ! empty( $post_types ) ) { ?>
	<div class="post_types_checkbox">
		<?php if ( isset( $label ) && !empty( $label ) ) { ?>
			<h3 class="field_title"><?php echo $label; ?></h3>
		<?php } ?>
		<?php if ( isset( $description ) && !empty( $description ) ) { ?>
			<span class="field_description"><?php echo $description; ?></span>
		<?php } ?>
		<ul>
			<?php
			foreach ( $post_types as $post_type ) {
				$attrs = array(
					'name' => $name . '[' . $post_type . ']',
					'id'   => $idname . '[' . $post_type . ']',
				); ?>
				<li>
					<input type="checkbox" <?php echo $field->htmlattributes( $attrs ); ?> <?php if ( isset( $checked[ $post_type ] ) && $checked[ $post_type ] == 'on' ) {
						echo 'checked="checked"';
					} ?>/>
					<?php echo $post_type; ?>
				</li>
			<?php } ?>
		</ul>
	</div>
<?php
}