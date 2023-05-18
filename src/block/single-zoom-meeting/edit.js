import {useBlockProps} from '@wordpress/block-editor';

export default function Edit(props) {
    return <div {...useBlockProps()}>
        <div class="vczapi-singleMeetingSkeleton">
            <h5> Please do not remove this block, this block is used to display single meeting page for FSE Themes</h5>
            <img alt="Zoom Meeting Single Page Outline" src={vczapi_blocks.single_zoom_meeting_page}/>
        </div>
    </div>
}