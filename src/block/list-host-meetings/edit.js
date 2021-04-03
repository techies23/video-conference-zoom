import ServerSideRender from "@wordpress/server-side-render";
import {BlockControls, useBlockProps} from "@wordpress/block-editor";
import {debounce} from "lodash";
/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';
import {useEffect, useState, useRef} from "@wordpress/element";

import AsyncSelect from 'react-select/async';
import {Placeholder, ToolbarGroup} from "@wordpress/components";

export default function EditListHostMeeting(props) {
    const {className, attributes, setAttributes} = props;
    const {host} = attributes;
    const isMounted = useRef;
    const [isEditing, setIsEditing] = useState(false);

    const editControls = [{
        icon: (!isEditing) ? 'edit' : 'no',
        title: (!isEditing) ? "Edit" : "Close",
        subscript: "Edit",
        onClick: () => {
            setIsEditing(prevIsEditing => !prevIsEditing);
        }
    }];

    const get_hosts = (input, callback) => {
        fetch('admin-ajax.php?action=vczapi_get_zoom_hosts&host=' + input).then(
            response => response.json()
        ).then(
            result => {
                callback(result);
            }
        ).catch(
            () => {
                callback([]);
            }
        )
    }

    useEffect(() => {
        isMounted.current = true;
        return () => {
            isMounted.current = false;
        }
    }, []);

    return (
        <div {...useBlockProps()}>
            <BlockControls>
                <ToolbarGroup controls={editControls}/>
            </BlockControls>
            {(typeof host === "undefined" || isEditing) &&
            <Placeholder>
                <h2>{__('Zoom -  List Meetings based on HOST', 'video-conferencing-with-zoom')}</h2>
                <div className="vczapi-blocks-form">
                    <AsyncSelect
                        className={'vczapi-blocks-form--select'}
                        defaultOptions
                        noResultsText={__("No options found", "video-conferencing-with-zoom-api")}
                        loadOptions={debounce(get_hosts, 800)}
                        defaultValue={host}
                        onChange={(input, {action}) => {
                            if (action === 'select-option') {
                                setAttributes({host: input})
                            }
                        }}
                    />
                </div>
            </Placeholder>
            }

            {((typeof host !== 'undefined' && host.hasOwnProperty('value')) && !isEditing)
            &&
            <ServerSideRender
                block="vczapi/list-host-meetings"
                attributes={
                    {
                        host: host
                    }
                }
            />
            }

        </div>
    )
}