<?php
/*
Plugin Name: CMB2 Field Type: Google Maps
Plugin URI: https://github.com/dbrock89/cmb_field_map
GitHub Plugin URI: https://github.com/dbrock89/cmb_field_map
Description: Google Maps field type for CMB2.
Version: 2.2.0
Author: D. Brock
Author URI: https://github.com/dbrock89
License: GPLv2+
*/

/**
 * Class PW_CMB2_Field_Google_Maps.
 */
class PW_CMB2_Field_Google_Maps {

	/**
	 * Current version number.
	 */
	const VERSION = '2.2.0';

	/**
	 * Initialize the plugin by hooking into CMB2.
	 */
	public function __construct() {
		add_filter( 'cmb2_render_pw_map', array( $this, 'render_pw_map' ), 10, 5 );
		add_filter( 'cmb2_sanitize_pw_map', array( $this, 'sanitize_pw_map' ), 10, 4 );
		add_filter( 'pw_google_api_key', array( $this, 'google_api_key_constant' ));
	}

	/**
	 * Render field.
	 */
	public function render_pw_map( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {

		// Get the Google API key from the field's parameters.
		$api_key = $field->args( 'api_key' );

		// Allow a custom hook to specify the key.
		$api_key = apply_filters( 'pw_google_api_key', $api_key );

		$this->setup_admin_scripts( $api_key );

		echo '<input type="text" class="large-text pw-map-search" id="' . $field->args( 'id' ) . '" placeholder="Type Name of Facility or Location or Address"/>';

		$field_type_object->_desc( false, false );

		if ( $field->args('split_values') === true ) {
			echo '<br /><br />';
			echo $field_type_object->input(array(
				'type'       => 'text',
				'id' 				 => $field->args( 'id' ) . '_place_name',
				'name'       => $field->args('_name') . '[place_name]',
				'value'      => isset( $field_escaped_value['place_name'] ) ? $field_escaped_value['place_name'] : '',
				'class'      => 'pw-map-place_name',
				'placeholder'       => 'Facility Name',
			));
			echo '<br />';
			echo $field_type_object->input(array(
				'type'       => 'text',
				'id' 				 => $field->args( 'id' ) . '_street_number',
				'name'       => $field->args('_name') . '[street_number]',
				'value'      => isset( $field_escaped_value['street_number'] ) ? $field_escaped_value['street_number'] : '',
				'class'      => 'pw-map-street_number',
				'placeholder'       => 'Street Number',
			));
			echo $field_type_object->input(array(
				'type'       => 'text',
				'id' 				 => $field->args( 'id' ) . '_route',
				'name'       => $field->args('_name') . '[route]',
				'value'      => isset( $field_escaped_value['route'] ) ? $field_escaped_value['route'] : '',
				'class'      => 'pw-map-route',
				'placeholder'       => 'Route',
			));
			echo $field_type_object->input(array(
				'type'       => 'text',
				'id' 				 => $field->args( 'id' ) . '_unit',
				'name'       => $field->args('_name') . '[unit]',
				'value'      => isset( $field_escaped_value['unit'] ) ? $field_escaped_value['unit'] : '',
				'class'      => 'pw-map-unit',
				'placeholder'       => 'Bldg. / Room # / Sub-Location',
			));
			echo '<br />';
			echo $field_type_object->input(array(
				'type'       => 'text',
				'id' 				 => $field->args( 'id' ) . '_locality',
				'name'       => $field->args('_name') . '[locality]',
				'value'      => isset( $field_escaped_value['locality'] ) ? $field_escaped_value['locality'] : '',
				'class'      => 'pw-map-locality',
				'placeholder'       => 'Locality (City)',
			));
			echo $field_type_object->input(array(
				'type'       => 'text',
				'id' 				 => $field->args( 'id' ) . '_administrative_area_level_1',
				'name'       => $field->args('_name') . '[administrative_area_level_1]',
				'value'      => isset( $field_escaped_value['administrative_area_level_1'] ) ? $field_escaped_value['administrative_area_level_1'] : '',
				'class'      => 'pw-map-administrative_area_level_1',
				'placeholder'       => 'Administrative Area Level 1 (State)',
			));
			echo $field_type_object->input(array(
				'type'       => 'text',
				'id' 				 => $field->args( 'id' ) . '_postal_code',
				'name'       => $field->args('_name') . '[postal_code]',
				'value'      => isset( $field_escaped_value['postal_code'] ) ? $field_escaped_value['postal_code'] : '',
				'class'      => 'pw-map-postal_code',
				'placeholder'       => 'Postal Code',
			));
			echo '<br />';
			echo $field_type_object->input(array(
				'type'       => 'text',
				'id' 				 => $field->args( 'id' ) . '_country',
				'name'       => $field->args('_name') . '[country]',
				'value'      => isset( $field_escaped_value['country'] ) ? $field_escaped_value['country'] : '',
				'class'      => 'pw-map-country',
				'placeholder'       => 'Country',
			));
			echo $field_type_object->input(array(
				'type'       => current_user_can( 'manage_options' ) ? 'text' : 'hidden',
				'id' 				 => $field->args( 'id' ) . '_utc_offset',
				'name'       => $field->args('_name') . '[utc_offset]',
				'value'      => isset( $field_escaped_value['utc_offset'] ) ? $field_escaped_value['utc_offset'] : '',
				'class'      => 'pw-map-utc_offset',
				'placeholder'       => 'UTC Offset',
			));
			echo '<br /><br />';
		}
		echo '<div class="pw-map"></div>';
		echo '<br />';
		echo $field_type_object->input(array(
			'type'       => current_user_can( 'manage_options' ) ? 'text' : 'hidden',
			'id' 				 => $field->args( 'id' ) . '_lat',
			'name'       => $field->args('_name') . '[lat]',
			'value'      => isset( $field_escaped_value['lat'] ) ? $field_escaped_value['lat'] : '',
			'class'      => 'pw-map-lat',
			'placeholder'       => 'Latitude',
		));
		echo $field_type_object->input(array(
			'type'       => current_user_can( 'manage_options' ) ? 'text' : 'hidden',
			'id' 				 => $field->args( 'id' ) . '_lng',
			'name'       => $field->args('_name') . '[lng]',
			'value'      => isset( $field_escaped_value['lng'] ) ? $field_escaped_value['lng'] : '',
			'class'      => 'pw-map-lng',
			'placeholder'       => 'Longitude',
		));
		echo $field_type_object->input(array(
			'type'       => current_user_can( 'manage_options' ) ? 'text' : 'hidden',
			'id' 				 => $field->args( 'id' ) . '_formatted_address',
			'name'       => $field->args('_name') . '[formatted_address]',
			'value'      => isset( $field_escaped_value['formatted_address'] ) ? $field_escaped_value['formatted_address'] : '',
			'class'      => 'pw-map-formatted_address',
			'placeholder'       => 'Formated Address',
		));

		if ( $field->args('map_drawings') === true ) {
			echo $field_type_object->input(array(
				'type'       => 'hidden',
				'id' 				 => $field->args( 'id' ) . '_drawing_manager',
				'name'       => $field->args('_name') . '[drawing_manager]',
				'class'      => 'pw-map-drawing_manager',
			));
		}

	}

	/**
	 * Optionally save the latitude/longitude values into two custom fields.
	 */
	public function sanitize_pw_map( $override_value, $value, $object_id, $field_args ) {
		if ( isset( $field_args['split_values'] ) && $field_args['split_values'] ) {
			if ( ! empty( $value['lat'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_lat', $value['lat'] );
			}

			if ( ! empty( $value['lng'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_lng', $value['lng'] );
			}

			if ( ! empty( $value['formatted_address'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_formatted_address', $value['formatted_address'] );
			}

			if ( ! empty( $value['utc_offset'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_utc_offset', $value['utc_offset'] );
			}

			if ( ! empty( $value['place_name'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_place_name', $value['place_name'] );
			}

			if ( ! empty( $value['street_number'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_street_number', $value['street_number'] );
			}

			if ( ! empty( $value['route'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_route', $value['route'] );
			}

			if ( ! empty( $value['unit'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_unit', $value['unit'] );
			}

			if ( ! empty( $value['locality'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_locality', $value['locality'] );
			}

			if ( ! empty( $value['administrative_area_level_1'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_administrative_area_level_1', $value['administrative_area_level_1'] );
			}

			if ( ! empty( $value['postal_code'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_postal_code', $value['postal_code'] );
			}

			if ( ! empty( $value['country'] ) ) {
				update_post_meta( $object_id, $field_args['id'] . '_country', $value['country'] );
			}
		}

		return $value;
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function setup_admin_scripts($api_key) {
		wp_register_script( 'pw-google-maps-api', "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=drawing,places,geometry", null, null );
		wp_enqueue_script( 'pw-google-maps', plugins_url( 'js/script.js', __FILE__ ), array( 'pw-google-maps-api', 'jquery' ), self::VERSION );
		wp_enqueue_style( 'pw-google-maps', plugins_url( 'css/style.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Default filter to return a Google API key constant if defined.
	 */
	public function google_api_key_constant( $google_api_key = null ) {

		// Allow the field's 'api_key' parameter or a custom hook to take precedence.
		if ( ! empty( $google_api_key ) ) {
			return $google_api_key;
		}

		if ( defined( 'PW_GOOGLE_API_KEY' ) ) {
			$google_api_key = PW_GOOGLE_API_KEY;
		}

		return $google_api_key;
	}
}
$pw_cmb2_field_google_maps = new PW_CMB2_Field_Google_Maps();
