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

$values = $form->autovalue($name, $default);

$values = get_option('pixfields_list');

$is_settings_page = false;

if ( isset($_GET['page']) && $_GET['page'] == 'pixfields' ) {
	$post_types = $form->autovalue('display_on_post_types', array('post' => 'on', 'page' => 'on' ) );
	$is_settings_page = true;
} else {
	$post_types = array( get_post_type() => 'on' );
}

if ( ! empty( $post_types ) ) { ?>
	<div class="pixfields_wrapper">
		<?php if ( isset( $label ) && !empty( $label ) ) { ?>
			<h2 class="field_title">
				<?php
				echo $label;
				if ( ! $is_settings_page ) {
					echo '<a class="media-modal-close" href="#"><span class="media-modal-icon"><span class="screen-reader-text">Close media panel</span></span></a>';
				}?>
			</h2>
		<?php }

		if ( isset( $description ) && !empty( $description ) ) { ?>
			<span class="field_description"><?php echo $description; ?></span>
		<?php }

		foreach ( $post_types as $post_type => $val ) { ?>
			<div class="pixfields_box">
				<?php if ( $is_settings_page ) { ?>
					<h4><?php _e( 'Post type: ', 'pixfields_txtd' );  echo $post_type ?></h4>
				<?php } else {
					//$values = $values[$post_type];
				} ?>
				<ul class="table_head">
					<li class="pixfield">
						<span class="label">
							<?php _e('Name', 'pixfields_txtd') ?>
						</span>
						<span class="filterable"><?php _e('Filter', 'pixfields_txtd') ?></span>
					</li>
				</ul>
				<ul class="pix_fields_list ui-sortable ui-draggable">
					<?php
					if ( ! empty( $values[$post_type] )){

						foreach ( $values[$post_type] as $key => $value ) {

							$attrs = $key_attrs = $default_attrs = $filterable_atts = array(
								'name' => $name . '[' . $post_type . ']',
							);

							$label_atts['name'] = $attrs['name'] . '['.$key.'][label]';

							if ( isset($value['label']) ) {
								$label_atts['value'] = $value['label'];
							}

							$meta_key_attrs = array();
							if ( isset( $value['meta_key'] ) || !empty( $value['meta_key'] ) ) {
								$meta_key_attrs['name'] = $attrs['name'] . '['.$key.'][meta_key]';
								$meta_key_attrs['value'] = $value['meta_key'];
							}

							$filterable_atts['name'] = $attrs['name'] . '['.$key.'][filter]';
							if ( isset( $value['filter'] ) ) {
								$filterable_atts['checked'] = $value['filter'];
							} ?>
							<li class="pixfield">
								<span class="drag"><i class="fa fa-arrows"></i></span>
								<span class="label">
									<input type="text" <?php echo $field->htmlattributes( $label_atts ); ?> />
								</span>
								<?php
								if ( ! empty( $meta_key_attrs ) ) { ?>
									<span class="meta_key">
										<input type="hidden" <?php echo $field->htmlattributes( $meta_key_attrs ); ?> />
									</span>
								<?php } ?>
								<span class="filterable">
									<input type="checkbox" <?php echo $field->htmlattributes( $filterable_atts ); ?> />
								</span>
								<a href="#" class="delete_field"><?php _e('Delete', 'pixfields_txtd'); ?></a>
							</li>
						<?php }
					} ?>
				</ul>
			</div>

			<?php if ( ! $is_settings_page ) { ?>
				<ul class="add_new_pixfield">
					<li class="pixfield" data-post_type="<?php echo get_post_type();?>">
						<span class="drag"><i class="fa fa-plus"></i></span>
								<span class="label">
									<input type="text" name="add_pixfield[label]" placeholder="<?php _e('Enter field name ..', 'pixfields_txtd' ); ?>" />
								</span>
								<span class="filterable">
									<input type="checkbox" name="add_pixfield[filter]" />
								</span>
						<span class="button add_field"><?php _e('Add Field', 'pixfields_txtd'); ?></span>
					</li>
				</ul>
				<div class="control_bar">
					<div class="update_btn_wrapper">
						<span class="button button-primary update_pixfields"><?php _e('Update', 'pixfields_txtd'); ?></span>
					</div>
				</div>
		<?php }
		} ?>
	</div>
<?php
}