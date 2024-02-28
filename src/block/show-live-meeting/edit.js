import './editor.scss'
import ServerSideRender from "@wordpress/server-side-render";
import {BlockControls, useBlockProps} from "@wordpress/block-editor";
import {debounce} from "lodash";
import {__} from '@wordpress/i18n';
import {useEffect, useState, useRef} from "@wordpress/element";
import AsyncSelect from 'react-select/async';
import Select from 'react-select';
import {Placeholder, ToolbarGroup, ToolbarButton, Button, RadioControl, Disabled, Spinner} from "@wordpress/components";
import previewImage from './direct-meeting.jpg'

export default function EditLiveMeeting(props) {
    const {className, attributes, setAttributes} = props;
    const {host, selectedMeeting, link_only, preview, shouldShow} = attributes;
    const isMounted = useRef;
    const [isEditing, setIsEditing] = useState(false);
    const [availableMeetings, setAvailableMeetings] = useState([]);

    const [tempHost, setTempHost] = useState([host]);
    const [tempShouldShow, setTempShouldShow] = useState(shouldShow);
    const [tempSelectedMeeting, setTempSelectedMeeting] = useState({});

    const [numberOfPages, setNumberOfPages] = useState(1);
    const [currentPage, setCurrentPage] = useState(1);

    const [isLoadingMeetings, setIsLoadingMeetings] = useState(false);

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
    const get_live_meetings = (host_id, shouldShow, additional_args = {}) => {
        if (host_id === "undefined" || host_id === '')
            return [];
        let queryUrl = ajaxurl + '?action=vczapi_get_live_meetings&host_id=' + host_id + '&show=' + shouldShow.value

        if (additional_args.hasOwnProperty("page_number") && additional_args.page_number !== "undefined") {
            queryUrl += "&page_number=" + additional_args.page_number;
        }
        setIsLoadingMeetings(true);
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
                setIsLoadingMeetings(false);
            }
        )
    }
    const PaginateLinks = ({numberOfPages}) => {
        let pages = [];
        if (numberOfPages > 1) {
            for (let i = 1; i <= numberOfPages; i++) {
                let className = (i === currentPage) ? 'selected' : '';
                pages.push(
                    <Button
                        key={i}
                        className={className}
                        onClick={() => {
                            get_live_meetings(host.value, shouldShow, {
                                page_number: i
                            })
                            setCurrentPage(i);
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


    useEffect(() => {
        isMounted.current = true;
        return () => {
            isMounted.current = false;
        }
    }, []);

    if (preview) {
        return <img src={previewImage}
                    alt={ __("Direct Meeting from Zoom","video-conferencing-with-zoom-api")}
                    className="vczapi-blocks__preview-image"
        />
    }

    return (
        <div {...useBlockProps()}>
            <BlockControls>
                <ToolbarGroup>
                    <ToolbarButton
                        icon={(!isEditing) ? 'edit' : 'no'}
                        title={(!isEditing) ? "Edit" : "Close"}
                        subscript={"Edit"}
                        onClick={() => {
                            setIsEditing(prevIsEditing => !prevIsEditing);
                        }}
                    />
                </ToolbarGroup>
            </BlockControls>

            {(typeof selectedMeeting === "undefined" || isEditing) &&
                <Placeholder>
                    <div className="vczapi-label-header">
                        <h2>{__("Zoom - Show Meeting directly from Zoom", "video-conferencing-with-zoom-api")}</h2>
                        <div><p>{__("Get a meeting directly from Zoom", "video-conferencing-with-zoom-api")}</p></div>
                    </div>
                    <div className="vczapi-blocks-form">
                        {
                            (typeof selectedMeeting !== "undefined" && selectedMeeting.hasOwnProperty('value'))
                            && <div className={"vczapi-blocks-form--selected-meeting"}>
                                <h4>Currently Selected Meeting: <strong>{selectedMeeting.label}</strong></h4>
                            </div>
                        }

                        <div className={"vczapi-blocks-form--group"}>
                            <RadioControl
                                className={"radio-inline"}
                                label="Link Only"
                                selected={link_only}
                                options={[
                                    {label: 'Yes', value: 'yes'},
                                    {label: 'No', value: 'no'},
                                ]}
                                onChange={(option) => {
                                    setAttributes({link_only: option})
                                }}
                            />
                        </div>
                        <div className="vczapi-blocks-form--group">
                            <div className={"vczapi-blocks-form--input-label"}>
                                {__("Would you like to show a Meeting or Webinar", "video-conferencing-with-zoom-api")}
                            </div>
                            <Select
                                label="Show"
                                className={'vczapi-blocks-form--select'}
                                defaultValue={tempShouldShow}
                                options={[
                                    {label: 'Meeting', value: 'meeting'},
                                    {label: 'Webinar', value: 'webinar'},
                                ]}
                                onChange={(option) => {
                                    setTempShouldShow(option);
                                    if (typeof host === "object" && host.hasOwnProperty('value')) {
                                        setAvailableMeetings([]);
                                        get_live_meetings(host.value, option);
                                        setTempHost(host);
                                    }
                                }}
                            />
                        </div>
                        <div className="vczapi-blocks-form--group">
                            <div className={"vczapi-blocks-form--input-label"}>
                                {__("Select A Host", "video-conferencing-with-zoom-api")}
                            </div>
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
                                        get_live_meetings(input.value, tempShouldShow);
                                    }
                                }}
                            />
                        </div>
                        {(isLoadingMeetings && (typeof availableMeetings === "undefined" || availableMeetings.length === 0)) &&
                            <div className="vczapi-blocks-form--group"><Spinner/></div>
                        }

                        {(typeof availableMeetings != "undefined" && availableMeetings.length > 0) &&
                            <div className="vczapi-blocks-form--group">
                                <div className={"vczapi-blocks-form--input-label"}>
                            <span>
                                {__("Select A Meeting : ", "video-conferencing-with-zoom-api")}
                                {numberOfPages > 1 && <span>use pagination to load more meeting if necessary</span>}
                            </span>

                                </div>
                                <Select
                                    className={'vczapi-blocks-form--select'}
                                    defaultValue={selectedMeeting}
                                    options={availableMeetings}
                                    isLoading={isLoadingMeetings}
                                    isDisabled={isLoadingMeetings}
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
                                if (!tempSelectedMeeting.hasOwnProperty('value')) {
                                    alert('Meeting Needs to be selected');
                                    return false;
                                }
                                setAttributes({selectedMeeting: tempSelectedMeeting});
                                setAttributes({shouldShow: tempShouldShow});
                                setIsEditing(false);
                            }}>{__("Save", "video-conferencing-with-zoom-api")}</Button>
                        </div>

                    </div>
                </Placeholder>
            }
            {((typeof selectedMeeting !== 'undefined' && selectedMeeting.hasOwnProperty('value')) && !isEditing)
                &&
                <Disabled>
                    <ServerSideRender
                        block="vczapi/show-live-meeting"
                        attributes={
                            {
                                link_only: link_only,
                                selectedMeeting: selectedMeeting,
                                shouldShow: shouldShow
                            }
                        }
                    />
                </Disabled>
            }

        </div>
    )
}