/**
 * Registers a new block provided a unique name and an object defining its behavior.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

/**
 * Internal dependencies
 */
import { SVG, Path } from '@wordpress/components';
import Edit from './edit';
import metadata from './block.json';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( metadata.name, {
	icon: {
		src: (
			<SVG width="20" height="17" viewBox="0 0 20 17" xmlns="http://www.w3.org/2000/svg">
				<Path fill-rule="evenodd" clip-rule="evenodd" d="M3.5 5C4.32843 5 5 4.32843 5 3.5C5 2.67157 4.32843 2 3.5 2C2.67157 2 2 2.67157 2 3.5C2 4.32843 2.67157 5 3.5 5ZM7.5 2.5C7.22386 2.5 7 2.72386 7 3V4C7 4.27614 7.22386 4.5 7.5 4.5H17.5C17.7761 4.5 18 4.27614 18 4V3C18 2.72386 17.7761 2.5 17.5 2.5H7.5ZM5 8.5C5 9.32843 4.32843 10 3.5 10C2.67157 10 2 9.32843 2 8.5C2 7.67157 2.67157 7 3.5 7C4.32843 7 5 7.67157 5 8.5ZM3.5 15C4.32843 15 5 14.3284 5 13.5C5 12.6716 4.32843 12 3.5 12C2.67157 12 2 12.6716 2 13.5C2 14.3284 2.67157 15 3.5 15ZM14 8.5C14 9.32843 13.3284 10 12.5 10C11.6716 10 11 9.32843 11 8.5C11 7.67157 11.6716 7 12.5 7C13.3284 7 14 7.67157 14 8.5ZM12.5 15H17.5C17.5 12.2386 15.2614 10.5 12.5 10.5C9.73858 10.5 7.5 12.2386 7.5 15H12.5Z"  />
			</SVG>
		),
	},
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
} );
