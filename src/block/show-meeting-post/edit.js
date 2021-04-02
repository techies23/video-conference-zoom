/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';
import ServerSideRender from "@wordpress/server-side-render";
import {BlockControls, useBlockProps} from '@wordpress/block-editor';
import {Disabled, Placeholder, ToolbarGroup, ToolbarButton} from "@wordpress/components";
import AsyncSelect from "react-select/async";
import {useEffect, useState, useRef} from "@wordpress/element";
import apiFetch from '@wordpress/api-fetch';

export default function Edit(props) {
    const {attributes, setAttributes} = props;
    const {postID} = attributes;

    const [availableMeetings, setAvailableMeetings] = useState([]);
    const [isEditing, setIsEditing] = useState(false);

    const isStillMounted = useRef();

    const getMeetings = (inputValue) => {
        return apiFetch({path: '/wp/v2/zoom_meetings?per_page=5&search=' + inputValue}).then(
            meetings => {
                if (isStillMounted.current === true) {
                    return meetings.length > 0 ? meetings.map((meeting, i) => {
                        return {label: meeting.title.rendered, value: meeting.id}
                    }) : [];
                }
            }
        ).catch(() => {
            return [];
        })
    }

    const editControls = [{
        icon: (!isEditing) ? 'edit' : 'no',
        title: (!isEditing) ? "Edit" : "Close",
        subscript: "Edit",
        onClick: () => {
            setIsEditing(prevIsEditing => !prevIsEditing);
        }
    }];

    useEffect(() => {
        isStillMounted.current = true;

        let queryParams = '/wp/v2/zoom_meetings?per_page=5';
        queryParams = (postID !== 0) ? queryParams + '&include=' + postID : queryParams;

        apiFetch({
            path: queryParams,
        }).then(
            meetings => {
                if (isStillMounted.current === true) {
                    const returnedMeetings = meetings.length > 0 ? meetings.map((meeting, i) => {
                        return {label: meeting.title.rendered, value: meeting.id}
                    }) : [];
                    setAvailableMeetings(returnedMeetings);
                }
            }
        )
        
        return () => {
            isMounted.current = false;
        }
        
    }, []);


    return (
        <div {...useBlockProps()}>
            <BlockControls>
                <ToolbarGroup controls={editControls}/>
            </BlockControls>
            {(postID === 0 || isEditing) &&
            <Placeholder>
                <h2>{__('Zoom -  Show Meeting Post', 'video-conferencing-with-zoom')}</h2>
                <div className="vczapi-blocks-form">
                    
                    <AsyncSelect
                        cacheOptions
                        className="vczapi-blocks-form--select"
                        placeholder={__("Select Meeting to Show", "video-conferencing-with-zoom")}
                        defaultOptions={availableMeetings}
                        loadOptions={getMeetings}
                        onChange={(selectedOption, {action}) => {
                            setAttributes({postID: selectedOption.value});
                            setIsEditing(false);
                        }}
                        defaultValue={availableMeetings.filter((meeting) => {
                            return meeting.value === postID;
                        })}
                    />

                </div>
            </Placeholder>
            }
            {(postID !== 0 && !isEditing) &&
            <Disabled>
                <ServerSideRender
                    block="vczapi/show-meeting-post"
                    attributes={
                        {postID: postID}
                    }
                />
            </Disabled>
            }
        </div>
    );

}