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
import Select from 'react-select';
import {Placeholder, ToolbarGroup, Button} from "@wordpress/components";

export default function EditListHostMeeting(props) {
    const {className, attributes, setAttributes} = props;
    const {host, selectedMeeting} = attributes;
    const isMounted = useRef;
    const [isEditing, setIsEditing] = useState(false);
    const [availableMeetings, setAvailableMeetings] = useState([]);
    const [tempHost, setTempHost] = useState([]);
    const [numberOfPages, setNumberOfPages] = useState(1);
    const editControls = [{
        icon: (!isEditing) ? 'edit' : 'no',
        title: (!isEditing) ? "Edit" : "Close",
        subscript: "Edit",
        onClick: () => {
            setIsEditing(prevIsEditing => !prevIsEditing);
        }
    }];

    const get_hosts = (input, callback) => {
        fetch(ajaxurl + '?action=vczapi_get_zoom_hosts&host=' + input).then(
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
    const get_live_meetings = (input, additional_args = {}) => {
        if (input === "undefined" || input === '')
            return [];
        let queryUrl = ajaxurl + '?action=vczapi_get_live_meetings&host_id=' + input.value

        if (additional_args.hasOwnProperty("page_number") && additional_args.page_number !== "undefined") {
            queryUrl += "&page_number=" + additional_args.page_number;
        }

        fetch(queryUrl).then(
            response => response.json()
        ).then(
            result => {
                let returnedPages = parseFloat(result.total_records) / parseFloat(result.page_size);
                if (returnedPages > 1) {
                    let pagination_count = Math.round(returnedPages);
                    setNumberOfPages(pagination_count);
                } else {
                    setNumberOfPages(1);
                }
                setAvailableMeetings(result.formatted_meetings);
            }
        )
    }

    const PaginateLinks = ({numberOfPages}) => {
        let pages = [];
        if (numberOfPages > 1) {
            for (let i = 1; i <= numberOfPages; i++) {
                pages.push(<Button key={i} onClick={() => {
                    get_live_meetings(host, {
                        page_number: i
                    })
                }}>{i}</Button>)
            }
            return (
                <div className={"vczapi-blocks-pagination"}>
                    {pages}
                </div>
            );
        }
        return '';
    }

    const [tempSelectedMeeting, setTempSelectedMeeting] = useState({});

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

            {(typeof selectedMeeting === "undefined" || isEditing) &&
            <Placeholder>
                <div className="vczapi-label-header">
                    <h2>{__("Zoom - Show Meeting directly from Zoom", "video-conferencing-with-zoom-api")}</h2>
                    <div><p>{__("Get a meeting directly from Zoom", "video-conferencing-with-zoom-api")}</p></div>
                </div>
                <div className="vczapi-blocks-form">
                    <div className="vczapi-blocks-form--group">
                        {
                            (typeof selectedMeeting !== "undefined" && selectedMeeting.hasOwnProperty('value')) 
                            && <div className={"vczapi-blocks-form--selected-meeting"}>Currently Selected Meeting:{selectedMeeting.label}</div>
                        }
                        <AsyncSelect
                            className={'vczapi-blocks-form--select'}
                            defaultOptions
                            defaultValue={tempHost}
                            placeholder={__("Select host to see meetings", "video-conferencing-with-zoom-api")}
                            noOptionsMessage={() => __("No options found", "video-conferencing-with-zoom-api")}
                            loadOptions={debounce(get_hosts, 800)}
                            onChange={(input, {action}) => {
                                if (action === 'select-option') {
                                    setAttributes({host: input})
                                    setTempHost(input);
                                    get_live_meetings(input);
                                }
                            }}
                        />
                    </div>

                    {(availableMeetings.length > 0) &&
                    <div className="vczapi-blocks-form--group">
                        <Select
                            className={'vczapi-blocks-form--select'}
                            defaultValue={selectedMeeting}
                            options={availableMeetings}
                            onChange={(input) => {
                                setTempSelectedMeeting(input);
                            }}
                            placeholder={__("Select a meeting", "video-conferencing-with-zoom-api")}
                        />
                        <PaginateLinks numberOfPages={numberOfPages}/>
                    </div>
                    }

                    <div className="vczapi-blocks-form--group">
                        <Button isPrimary onClick={() => {
                            setAttributes({selectedMeeting: tempSelectedMeeting});
                            setIsEditing(false);
                        }}>{__("Save", "video-conferencing-with-zoom-api")}</Button>
                    </div>

                </div>
            </Placeholder>
            }

            {((typeof selectedMeeting !== 'undefined' && selectedMeeting.hasOwnProperty('value')) && !isEditing)
            &&
            <ServerSideRender
                block="vczapi/show-live-meeting"
                attributes={
                    {
                        host: host,
                        selectedMeeting: selectedMeeting
                    }
                }
            />
            }

        </div>
    )
}