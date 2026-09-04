<?php

namespace Codemanas\VczApi\Helpers;

class FormHelper {

	public static function fields( $key, $args, $value = null ): void {
		$defaults = array(
			'type'              => 'text',
			'label'             => '',
			'description'       => '',
			'placeholder'       => '',
			'maxlength'         => false,
			'required'          => false,
			'autocomplete'      => false,
			'id'                => $key,
			'class'             => array(),
			'label_class'       => array(),
			'input_class'       => array(),
			'options'           => array(),
			'custom_attributes' => array(),
			'validate'          => array(),
			'default'           => '',
			'autofocus'         => false,
			'after_html'        => ''
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'vczapi_formFields', $args, $key, $value );

		// Base allowed HTML tags for wp_kses
		$allowed_html = array(
			'abbr'   => array( 'class' => array(), 'title' => array() ),
			'label'  => array( 'for' => array(), 'class' => array(), 'id' => array() ),
			'a'      => array( 'href' => array(), 'title' => array(), 'class' => array(), 'id' => array() ),
			'p'      => array( 'class' => array(), 'id' => array() ),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'span'   => array( 'class' => array(), 'id' => array(), 'style' => array() ),
			'i'      => array()
		);

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = ' <abbr class="required" title="' . esc_attr__( 'required', 'video-conferencing-with-zoom-api' ) . '">*</abbr>';
		} else {
			$required = '';
		}

		if ( is_null( $value ) ) {
			$value = $args['default'];
		}

		// Custom attribute handling
		$custom_attributes_array   = array();
		$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'] );

		if ( $args['maxlength'] ) {
			$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
		}

		if ( true === $args['autofocus'] ) {
			$args['custom_attributes']['autofocus'] = 'autofocus';
		}

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$sanitized_attr            = sanitize_key( $attribute );
				$custom_attributes_array[] = $sanitized_attr . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		if ( ! empty( $args['validate'] ) && is_array( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . sanitize_html_class( $validate );
			}
		}

		$field           = '';
		$label_id        = $args['id'];
		$input_class     = ! empty( $args['input_class'] ) ? implode( ' ', array_map( 'sanitize_html_class', (array) $args['input_class'] ) ) : '';
		$custom_attr_str = implode( ' ', $custom_attributes_array );

		switch ( $args['type'] ) {
			case 'textarea' :

				$allowed_html['textarea'] = array(
					'class'       => array(),
					'id'          => array(),
					'name'        => array(),
					'placeholder' => array(),
					'cols'        => array(),
					'rows'        => array()
				);

				if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
					foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
						$allowed_html['textarea'][ sanitize_key( $attribute ) ] = array();
					}
				}

				$field .= '<textarea name="' . esc_attr( $key ) . '" class="regular-text ' . esc_attr( $input_class ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . ' ' . $custom_attr_str . '>' . esc_textarea( $value ) . '</textarea>';

				break;

			case 'checkbox' :

				$allowed_html['input'] = array(
					'class'   => array(),
					'type'    => array(),
					'id'      => array(),
					'name'    => array(),
					'value'   => array(),
					'checked' => array()
				);

				$field = '<label class="checkbox" ' . $custom_attr_str . '> 
                    <input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( $input_class ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . ' /> '
				         . esc_html( $args['label'] ) . $required . '</label>';

				break;

			case 'password' :
			case 'text' :
			case 'email' :
			case 'tel' :
			case 'number' :

				$allowed_html['input'] = array(
					'class'       => array(),
					'type'        => array(),
					'id'          => array(),
					'name'        => array(),
					'placeholder' => array(),
					'value'       => array()
				);

				if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
					foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
						$allowed_html['input'][ sanitize_key( $attribute ) ] = array();
					}
				}

				$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="regular-text ' . esc_attr( $input_class ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" value="' . esc_attr( $value ) . '" ' . $custom_attr_str . ' />';

				break;

			case 'select' :

				$allowed_html['select'] = array(
					'class'            => array(),
					'id'               => array(),
					'name'             => array(),
					'value'            => array(),
					'data-placeholder' => array()
				);

				$allowed_html['option'] = array(
					'value'    => array(),
					'selected' => array()
				);

				if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
					foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
						$allowed_html['select'][ sanitize_key( $attribute ) ] = array();
					}
				}

				$options = '';

				if ( ! empty( $args['options'] ) && is_array( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
						if ( '' === $option_key ) {
							if ( empty( $args['placeholder'] ) ) {
								$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'video-conferencing-with-zoom-api' );
							}
							$custom_attributes_array[]                  = 'data-allow_clear="true"';
							$allowed_html['select']['data-allow_clear'] = array();
						}
						$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_html( $option_text ) . '</option>';
					}

					$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select ' . esc_attr( $input_class ) . '" ' . implode( ' ', $custom_attributes_array ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '"> 
                        ' . $options . ' 
                    </select>';
				}

				break;

			case 'radio' :

				$allowed_html['input'] = array(
					'class'   => array(),
					'type'    => array(),
					'id'      => array(),
					'name'    => array(),
					'value'   => array(),
					'checked' => array()
				);
				$allowed_html['div']   = array(
					'class' => array(),
					'id'    => array(),
					'style' => array()
				);

				if ( ! empty( $args['options'] ) && is_array( $args['options'] ) ) {
					$label_id = current( array_keys( $args['options'] ) );

					foreach ( $args['options'] as $option_key => $option_text ) {
						$radio_id = esc_attr( $args['id'] . '_' . $option_key );
						$field    .= '<div style="margin:10px 0;"><input type="radio" class="input-radio ' . esc_attr( $input_class ) . '" value="' . esc_attr( $option_key ) . '" name="' . esc_attr( $key ) . '" id="' . $radio_id . '" ' . checked( $value, $option_key, false ) . '/>';
						$field    .= '<label for="' . $radio_id . '" class="radio">' . esc_html( $option_text ) . '</label></div>';
					}
				}

				break;
		}

		if ( ! empty( $field ) ) {
			$field_html = '';

			if ( $args['label'] && 'checkbox' !== $args['type'] ) {
				$label_classes = ! empty( $args['label_class'] ) ? implode( ' ', array_map( 'sanitize_html_class', (array) $args['label_class'] ) ) : '';
				$field_html    .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( $label_classes ) . '">' . esc_html( $args['label'] ) . $required . '</label>';
			}

			$field_html .= $field;

			if ( $args['description'] ) {
				$field_html .= '<p class="description">' . esc_html( $args['description'] ) . '</p>';
			}

			if ( ! empty( $args['after_html'] ) ) {
				$field_html .= '<span style="color: #8a8a8a;"><i>' . wp_kses_post( $args['after_html'] ) . '</i></span>';
			}

			$field = $field_html;
		}

		$field = apply_filters( 'vcw_formField_' . $args['type'], $field, $key, $args, $value );

		echo wp_kses( $field, $allowed_html );
	}
}