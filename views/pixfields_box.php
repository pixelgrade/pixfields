<?php
/**
 * Template used to display the pixfields box
 * Available vars:
 * array        $pixfields        An array with all pixfields keys and their labels
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
