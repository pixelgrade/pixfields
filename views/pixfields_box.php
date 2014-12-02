<?php
/**
 * Template used to display the pixfields box
 * Available vars:
 * array        $gallery_ids        An array with all attachments ids
 * object       $attachments        An object with all the attachments
 * string       $number_of_images   Count attachments
 * string       $columns            Number of columns
 */
var_dump($pixfields);
if ( !isset( $pixfields ) ) return; ?>
<div class="pixfields_box" >
	<ul>
	<?php foreach ( $pixfields as $label => $pixfield) { ?>
		<li>
			<strong><?php echo $label; ?></strong> : <?php echo $pixfield; ?>
		</li>
	<?php }?>
	</ul>
</div>
