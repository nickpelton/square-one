<?php

namespace Tribe\Project\Templates\Content\Panels;

use Tribe\Project\Panels\Types\Interstitial as InterstitialPanel;
use Tribe\Project\Templates\Components\Button;
use Tribe\Project\Templates\Components\Image;
use Tribe\Project\Templates\Components\Content_Block;
use Tribe\Project\Templates\Components\Text;
use Tribe\Project\Templates\Components\Title;
use Tribe\Project\Theme\Image_Sizes;
use Tribe\Project\Theme\Util;

class Interstitial extends Panel {

	public function get_data(): array {
		$data       = parent::get_data();
		$panel_data = $this->get_mapped_panel_data();
		$data       = array_merge( $data, $panel_data );

		return $data;
	}

	public function get_mapped_panel_data(): array {
		$data = [
			'row_classes'     => $this->get_row_classes(),
			'content_classes' => $this->get_content_classes(),
			'image'           => $this->get_image(),
			'content_block'   => $this->get_content_block(),
		];

		return $data;
	}

	protected function is_split_layout(): bool {
		if ( $this->panel_vars[ InterstitialPanel::FIELD_LAYOUT ] === InterstitialPanel::FIELD_LAYOUT_OPTION_CONTENT_SPLIT_LEFT || $this->panel_vars[ InterstitialPanel::FIELD_LAYOUT ] === InterstitialPanel::FIELD_LAYOUT_OPTION_CONTENT_SPLIT_RIGHT ) {
			return true;
		}

		return false;
	}

	protected function get_classes(): string {
		$classes = [
			'panel',
			'site-panel',
			sprintf( 'site-panel--%s', $this->panel->get_type_object()->get_id() ),
			sprintf( 'hero--layout-%s', $this->panel_vars[ InterstitialPanel::FIELD_LAYOUT ] ),
			$this->panel_vars[ InterstitialPanel::FIELD_BG_COLOR ],
		];

		return Util::class_attribute( $classes );
	}

	protected function get_row_classes(): string {
		$classes = [
			! $this->is_split_layout() ? 'g-row' : '',
			! $this->is_split_layout() ? 'g-row--vertical-center' : '',
			$this->panel_vars[ InterstitialPanel::FIELD_LAYOUT ] === InterstitialPanel::FIELD_LAYOUT_OPTION_CONTENT_CENTER ? 'g-row--center u-text-align-center' : '',
		];

		return Util::class_attribute( $classes, false );
	}

	protected function get_content_classes(): string {
		$classes = [
			't-content',
			! $this->is_split_layout() ? 'g-col' : '',
			! $this->is_split_layout() ? 'g-col--two-thirds' : '',
			$this->text_color(),
		];

		return Util::class_attribute( $classes, false );
	}

	protected function get_image() {

		if ( empty( $this->panel_vars[ InterstitialPanel::FIELD_IMAGE ] ) ) {
			return false;
		}

		$placeholder_size = Image_Sizes::CORE_16X9_PLACEHOLDER;
		$src_size         = Image_Sizes::CORE_16X9_LARGE;

		$srcset_sizes = [
			Image_Sizes::CORE_16X9_LARGE,
			Image_Sizes::CORE_16X9_MEDIUM,
			Image_Sizes::CORE_16X9_SMALL,
		];

		// Override image sizes if left/right layout selected
		if ( $this->is_split_layout() ) {
			$src_size         = Image_Sizes::SQUARE_LARGE;
			$placeholder_size = Image_Sizes::SQUARE_PLACEHOLDER;

			$srcset_sizes = [
				Image_Sizes::SQUARE_LARGE,
				Image_Sizes::SQUARE_MEDIUM,
				Image_Sizes::SQUARE_SMALL,
			];
		}

		$image_placeholder = wp_get_attachment_image_src( $this->panel_vars[ InterstitialPanel::FIELD_IMAGE ], $placeholder_size )[ 0 ];

		$options = [
			Image::IMG_ID          => $this->panel_vars[ InterstitialPanel::FIELD_IMAGE ],
			Image::IMG_ALT_TEXT    => esc_attr( $this->panel_vars[ InterstitialPanel::FIELD_TITLE ] ),
			Image::SHIM            => $image_placeholder,
			Image::COMPONENT_CLASS => 'c-image interstitial__image',
			Image::WRAPPER_CLASS   => 'c-image__bg',
			Image::AS_BG           => true,
			Image::USE_LAZYLOAD    => true,
			Image::ECHO            => false,
			Image::SRC_SIZE        => $src_size,
			Image::SRCSET_SIZES    => $srcset_sizes,
		];

		$image_obj = Image::factory( $options );

		return $image_obj->render();
	}

	protected function get_content_block() {

		$title_attrs       = [];
		$description_attrs = [];

		if ( is_panel_preview() ) {

			$title_attrs = [
				'data-depth'    => $this->panel->get_depth(),
				'data-name'     => esc_attr( InterstitialPanel::FIELD_TITLE ),
				'data-livetext' => true,
			];

			$description_attrs = [
				'data-depth'    => $this->panel->get_depth(),
				'data-name'     => esc_attr( InterstitialPanel::FIELD_DESCRIPTION ),
				'data-autop'    => 'true',
				'data-livetext' => true,
			];
		}

		$options = [
			Content_Block::TITLE           => $this->get_interstitial_title( $title_attrs ),
			Content_Block::TEXT            => $this->get_interstitial_text( $description_attrs ),
			Content_Block::BUTTON          => $this->get_interstitial_button(),
			Content_Block::CLASSES         => [],
			Content_Block::CONTENT_CLASSES => [],
		];

		$content_block_obj = Content_Block::factory( $options );

		return $content_block_obj->render();
	}

	protected function text_color() {

		$classes = [];

		if ( InterstitialPanel::FIELD_TEXT_LIGHT === $this->panel_vars[ InterstitialPanel::FIELD_TEXT_COLOR ] ) {
			$classes[] = 't-content--light';
		}

		if ( InterstitialPanel::FIELD_TEXT_DARK === $this->panel_vars[ InterstitialPanel::FIELD_TEXT_COLOR ] ) {
			$classes[] = 't-content--dark';
		}

		return implode( ' ', $classes );
	}

	protected function get_interstitial_title( $title_attrs ) {
		$options = [
			Title::CLASSES => [ 'h1' ],
			Title::TAG     => 'h2',
			Title::ATTRS   => $title_attrs,
			Title::TITLE   => $this->panel_vars[ InterstitialPanel::FIELD_TITLE ],
		];

		$title_object = Title::factory( $options );

		return $title_object->render();
	}

	protected function get_interstitial_text( $description_attrs ) {
		$options = [
			Text::ATTRS   => $description_attrs,
			Text::CLASSES => [ 'site-panel--interstitial__desc' ],
			Text::TEXT    => $this->panel_vars[ InterstitialPanel::FIELD_DESCRIPTION ],
		];

		$text_object = Text::factory( $options );

		return $text_object->render();
	}

	protected function get_interstitial_button() {
		$classes = [ 'c-btn' ];

		// Set button class based on text color
		if ( InterstitialPanel::FIELD_TEXT_LIGHT === $this->panel_vars[ InterstitialPanel::FIELD_TEXT_COLOR ] ) {
			$classes = [ 'c-btn-inverse' ];
		}

		$options = [
			Button::CLASSES     => $classes,
			Button::ATTRS       => '',
			Button::TAG         => '',
			Button::TARGET      => $this->panel_vars[ InterstitialPanel::FIELD_CTA ][ Button::TARGET ],
			Button::BTN_AS_LINK => true,
			Button::URL         => $this->panel_vars[ InterstitialPanel::FIELD_CTA ][ Button::URL ],
			Button::LABEL       => ! empty( $this->panel_vars[ InterstitialPanel::FIELD_CTA ][ Button::LABEL ] ) ? $this->panel_vars[ InterstitialPanel::FIELD_CTA ][ Button::LABEL ] : __( 'Learn more', 'tribe' ),
		];

		$button_object = Button::factory( $options );

		return $button_object->render();
	}

	public static function instance() {
		return tribe_project()->container()[ 'twig.templates.content/panels/interstitial' ];
	}
}