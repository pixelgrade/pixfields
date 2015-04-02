<?php
/**
 * Template used to display the pixfields box
 * Available vars:
 * array        $pixfields        An array with all pixfields keys and their labels
 */

if ( ! isset( $pixfields ) || empty( $pixfields ) ) return; ?>
<div class="pixfields_box" >
	<ul class="pixfields_list">
	<?php foreach ( $pixfields as $id => $pixfield) {

		if ( empty($pixfield) ) {
			continue;
		}

		$label = $id;
		$value = $pixfield;
		if ( is_array( $pixfield  ) ) {
			if ( isset( $pixfield['label'] ) ) {
				$label = $pixfield['label'];
			}

			if ( isset( $pixfield['value'] ) ) {
				$value = $pixfield['value'];
			}
		} ?>
		<li class="pixfield">
			<strong><?php echo $label; ?></strong> : <?php echo $value; ?>
		</li>
	<?php } ?>
	</ul>
</div>
