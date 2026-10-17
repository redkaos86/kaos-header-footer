<?php
defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * "Kaos Sticky" panel in the Advanced tab of Elementor sections and containers.
 * The controls are generated from the KAOSHF_Fx schema: no field is written twice.
 */
class KAOSHF_Elementor {

	const PREFIX = 'kaoshf_';

	public static function init() {
		// Id of the "Advanced" section per element type: in containers it is called section_layout.
		$advanced = array(
			'section'   => 'section_advanced',
			'container' => 'section_layout',
		);
		foreach ( $advanced as $type => $section_id ) {
			add_action( "elementor/element/{$type}/{$section_id}/after_section_end", array( __CLASS__, 'controls' ) );
			add_action( "elementor/frontend/{$type}/before_render", array( __CLASS__, 'before_render' ) );
		}
	}

	/** Converts a schema field definition into the arguments of an Elementor control. */
	private static function control_args( $def, $when ) {
		$cond = array( self::PREFIX . 'on' => 'yes' );
		$all  = KAOSHF_Fx::fields();
		foreach ( $when as $dep => $values ) {
			$is_check = isset( $all[ $dep ] ) && 'check' === $all[ $dep ]['type'];
			$cond[ self::PREFIX . $dep ] = $is_check ? 'yes' : $values;
		}

		$args = array(
			'label'       => $def['label'],
			'condition'   => $cond,
			'description' => $def['help'] ?? '',
		);

		switch ( $def['type'] ) {
			case 'select':
				return $args + array( 'type' => Controls_Manager::SELECT, 'options' => $def['options'], 'default' => $def['default'] );
			case 'check':
				return $args + array( 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => $def['default'] ? 'yes' : '', 'label_on' => __( 'Yes', 'kaos-header-footer' ), 'label_off' => __( 'No', 'kaos-header-footer' ) );
			case 'number':
				return $args + array( 'type' => Controls_Manager::NUMBER, 'min' => $def['min'], 'max' => $def['max'], 'default' => $def['default'] );
			case 'color':
				return $args + array( 'type' => Controls_Manager::COLOR, 'default' => $def['default'] );
			case 'image':
				return $args + array( 'type' => Controls_Manager::MEDIA, 'default' => array( 'url' => '' ) );
			case 'multi':
				return $args + array( 'type' => Controls_Manager::SELECT2, 'multiple' => true, 'label_block' => true, 'options' => $def['options'], 'default' => $def['default'] );
			default:
				return $args + array( 'type' => Controls_Manager::TEXT, 'default' => $def['default'], 'label_block' => true );
		}
	}

	public static function controls( $element ) {
		$element->start_controls_section(
			'kaoshf_fx_section',
			array(
				'label' => __( 'Kaos Sticky', 'kaos-header-footer' ),
				'tab'   => Controls_Manager::TAB_ADVANCED,
			)
		);

		$element->add_control(
			self::PREFIX . 'on',
			array(
				'label'        => __( 'Enable scroll effects', 'kaos-header-footer' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => '',
			)
		);

		foreach ( KAOSHF_Fx::schema() as $key => $group ) {
			$element->add_control(
				self::PREFIX . 'h_' . $key,
				array(
					'label'     => $group['label'],
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array( self::PREFIX . 'on' => 'yes' ),
				)
			);
			foreach ( $group['fields'] as $field => $def ) {
				$element->add_control( self::PREFIX . $field, self::control_args( $def, $def['when'] ?? array() ) );
			}
		}

		$element->end_controls_section();
	}

	public static function before_render( $element ) {
		if ( ! KAOSHF_Fx::enabled() || class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return;
		}
		$settings = $element->get_settings_for_display();
		if ( 'yes' !== ( $settings[ self::PREFIX . 'on' ] ?? '' ) ) {
			return;
		}

		$raw = array();
		foreach ( KAOSHF_Fx::fields() as $key => $def ) {
			$v = $settings[ self::PREFIX . $key ] ?? null;
			if ( null === $v ) {
				continue;
			}
			if ( 'check' === $def['type'] ) {
				$v = 'yes' === $v ? 1 : 0;
			} elseif ( 'image' === $def['type'] ) {
				$v = is_array( $v ) ? ( $v['url'] ?? '' ) : '';
			}
			$raw[ $key ] = $v;
		}

		$cfg = KAOSHF_Fx::config( KAOSHF_Fx::sanitize( $raw ) );
		if ( ! KAOSHF_Fx::has_effects( $cfg ) ) {
			return;
		}

		$element->add_render_attribute( '_wrapper', 'data-khf-fx', wp_json_encode( $cfg ) );
		KAOSHF_Fx::request();
	}
}
