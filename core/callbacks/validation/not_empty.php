<?php defined( 'ABSPATH' ) or die;

function pixfields_validate_not_empty( $fieldvalue, $processor ) {
	return ! empty( $fieldvalue );
}
