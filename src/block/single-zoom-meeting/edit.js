import {useBlockProps} from '@wordpress/block-editor';

export default function Edit(props) {
    return <div {...useBlockProps()}>Please do not remove this block, this block is used to display single meeting option</div>
}