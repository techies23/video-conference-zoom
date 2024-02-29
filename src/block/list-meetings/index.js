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
			<SVG width="22" height="19" viewBox="0 0 22 19" xmlns="http://www.w3.org/2000/svg">
				<Path fill-rule="evenodd" clip-rule="evenodd" d="M4.5 6C5.32843 6 6 5.32843 6 4.5C6 3.67157 5.32843 3 4.5 3C3.67157 3 3 3.67157 3 4.5C3 5.32843 3.67157 6 4.5 6ZM8.5 3.5C8.22386 3.5 8 3.72386 8 4V5C8 5.27614 8.22386 5.5 8.5 5.5H18.5C18.7761 5.5 19 5.27614 19 5V4C19 3.72386 18.7761 3.5 18.5 3.5H8.5ZM6 9.5C6 10.3284 5.32843 11 4.5 11C3.67157 11 3 10.3284 3 9.5C3 8.67157 3.67157 8 4.5 8C5.32843 8 6 8.67157 6 9.5ZM8.5 8.5C8.22386 8.5 8 8.72386 8 9V10C8 10.2761 8.22386 10.5 8.5 10.5H18.5C18.7761 10.5 19 10.2761 19 10V9C19 8.72386 18.7761 8.5 18.5 8.5H8.5ZM6 14.5C6 15.3284 5.32843 16 4.5 16C3.67157 16 3 15.3284 3 14.5C3 13.6716 3.67157 13 4.5 13C5.32843 13 6 13.6716 6 14.5ZM8.5 13.5C8.22386 13.5 8 13.7239 8 14V15C8 15.2761 8.22386 15.5 8.5 15.5H18.5C18.7761 15.5 19 15.2761 19 15V14C19 13.7239 18.7761 13.5 18.5 13.5H8.5Z" />
			</SVG>
		),
	},
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
} );
