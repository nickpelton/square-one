<?php

namespace Tribe\Project\Panels\Types;

use ModularContent\Fields;

class VideoText extends Panel_Type_Config {

	const NAME = 'videotext';

	const FIELD_LAYOUT                    = 'layout';
	const FIELD_LAYOUT_OPTION_VIDEO_RIGHT = 'video-right';
	const FIELD_LAYOUT_OPTION_VIDEO_LEFT  = 'video-left';
	const FIELD_TITLE                     = 'title';
	const FIELD_DESCRIPTION               = 'description';
	const FIELD_VIDEO                     = 'video';
	const FIELD_CTA                       = 'cta';

	protected function panel() {

		$panel = $this->handler->factory( self::NAME );
		$panel->set_template_dir( $this->ViewFinder );
		$panel->set_label( __( 'Video + Text', 'tribe' ) );
		$panel->set_description( __( 'A video and text with 2 layout options.', 'tribe' ) );
		$panel->set_thumbnail( $this->handler->thumbnail_url( 'video-text.svg' ) );

		// Panel Layout.
		$panel->add_settings_field(
			new Fields\ImageSelect(
				[
					'name'    => self::FIELD_LAYOUT,
					'label'   => __( 'Layout', 'tribe' ),
					'options' => [
						self::FIELD_LAYOUT_OPTION_VIDEO_LEFT  => $this->handler->layout_icon_url( 'imagetext-left.svg' ),
						self::FIELD_LAYOUT_OPTION_VIDEO_RIGHT => $this->handler->layout_icon_url( 'imagetext-right.svg' ),
					],
					'default' => self::FIELD_LAYOUT_OPTION_VIDEO_LEFT,
				]
			)
		);

		$panel->add_field(
			new Fields\TextArea(
				[
					'name'     => self::FIELD_DESCRIPTION,
					'label'    => __( 'Description', 'tribe' ),
					'richtext' => true,
				]
			)
		);

		$panel->add_field(
			new Fields\Text(
				[
					'name'        => self::FIELD_VIDEO,
					'label'       => __( 'Video', 'tribe' ),
					'description' => __( 'Add a video url.', 'tribe' ),
					'size'        => 'medium', // the size displayed in the admin.
				]
			)
		);

		$panel->add_field(
			new Fields\Link(
				[
					'name'  => self::FIELD_CTA,
					'label' => __( 'Call To Action Link', 'tribe' ),
				]
			)
		);

		return $panel;

	}
}
