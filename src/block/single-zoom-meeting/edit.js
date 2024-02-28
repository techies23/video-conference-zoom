import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';
export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<div className="vczapi-singleMeetingSkeleton">
				<h5> {__("Please do not remove this block, this block is used to display single meeting page for FSE Themes", "video-conferencing-with-zoom-api")}</h5>
				<img alt="Zoom Meeting Single Page Outline" src={vczapi_blocks.single_zoom_meeting_page}/>
			</div>
		</div>
	);
}
