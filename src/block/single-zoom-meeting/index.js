import {registerBlockType} from "@wordpress/blocks";
import Edit from './edit'

registerBlockType('vczapi/single-zoom-meeting',{
    apiVersion: 3,
    edit: Edit
})