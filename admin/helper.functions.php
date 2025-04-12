<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function yikes_custom_tabs_maybe_unserialize( $data ) {

	if ( is_serialized( $data ) ) { // Don't attempt to unserialize data that wasn't serialized going in.
		return @unserialize( trim( $data ), array( 'allowed_classes' => false ) ); //phpcs:ignore -- allowed classes is false.
	}

	return $data;
}
