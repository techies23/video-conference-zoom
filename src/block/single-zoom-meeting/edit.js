import {useBlockProps} from '@wordpress/block-editor';
import {__} from '@wordpress/i18n';
import BlockPreviewPlaceholder from '../components/BlockPreviewPlaceholder';

export default function Edit(props) {
    return <div {...useBlockProps()}>
        <div class="vczapi-singleMeetingSkeleton">
            <h5> Please do not remove this block, this block is used to display single meeting page for FSE Themes</h5>
            <BlockPreviewPlaceholder label={__('Zoom Meeting Single Page Outline', 'video-conferencing-with-zoom-api')} />
        </div>
    </div>
}