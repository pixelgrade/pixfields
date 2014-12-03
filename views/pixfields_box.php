<?php
/**
 * Template used to display the pixfields box
 * Available vars:
 * array        $gallery_ids        An array with all attachments ids
 * object       $attachments        An object with all the attachments
 * string       $number_of_images   Count attachments
 * string       $columns            Number of columns
 */

if ( ! isset( $pixfields ) || empty( $pixfields ) ) return; ?>
<div class="pixfields_box" >
	<ul class="pixfields_list">
	<?php foreach ( $pixfields as $label => $pixfield) {
		if ( empty($pixfield) ) {
			continue;
		} ?>
		<li class="pixfield">
			<strong><?php echo $label; ?></strong> : <?php echo $pixfield; ?>
		</li>
	<?php } ?>
	</ul>
</div>
