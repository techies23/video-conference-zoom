/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';
import {isEqual, unionBy, intersectionWith, debounce} from 'lodash';
import {addQueryArgs} from "@wordpress/url";

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import ServerSideRender from "@wordpress/server-side-render";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import {Disabled, PanelBody, RangeControl, SelectControl, CheckboxControl} from "@wordpress/components";
import {Fragment, useEffect, useRef, useState} from "@wordpress/element";
import apiFetch from '@wordpress/api-fetch';
import AsyncSelect from 'react-select/async';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit(props) {
    const {className, attributes, setAttributes, isSelected} = props;
    const {
        shortcodeType,
        orderBy,
        postsToShow,
        displayType,
        showPastMeeting,
        showFilter,
        selectedCategory,
        selectedAuthor,
        columns,
        preview
    } = attributes;

    const isStillMounted = useRef();

    const [availableCategories, setAvailableCategories] = useState([]);
    const [availableUsers, setAvailableUsers] = useState([]);

    const getUsers = (inputValue, callback) => {
        //?per_page=5&who=authors'
        let userQueryParams = {
            per_page: 5,
            who: 'authors'
        }
        if (inputValue !== '') {
            //userQuery = addQueryArgs(userQuery, {search: inputValue});
            userQueryParams.search = inputValue;
        }
        let userQuery = addQueryArgs('/wp/v2/users', userQueryParams);
        return apiFetch({path: userQuery}).then(
            users => {
                if (isStillMounted.current === true) {
                    callback(users.length > 0 ? users.map((user, i) => {
                        return {label: user.name, value: user.id}
                    }) : []);
                }
            }
        ).catch(() => {
            callback([]);
        })
    }

    useEffect(() => {
        isStillMounted.current = true;

        apiFetch({path: '/wp/v2/zoom_meeting_cats'}).then(
            zoomCats => {
                if (isStillMounted.current === true) {

                    const formattedCategories = zoomCats.length > 0 ? zoomCats.map((cats, i) => {
                        return {label: cats.name + ' (' + cats.count + ')', value: cats.slug}
                    }) : [];

                    setAvailableCategories((prevAvailableCategories) => {
                        return [...prevAvailableCategories, ...formattedCategories]
                    });
                }
            }
        ).catch(() => {
            if (isStillMounted.current === true)
                setAvailableCategories([]);
        });


        //?per_page=10&who=authors
        let userQueryArgs = {
            per_page: 10,
            who: "authors"
        }
        if (selectedAuthor !== 0) {
            // userUrl = userUrl + '&include=' + selectedAuthor;
            userQueryArgs.include = selectedAuthor;
        }

        let userUrl = addQueryArgs('/wp/v2/users', userQueryArgs);

        apiFetch({path: userUrl}).then(
            users => {
                if (isStillMounted.current === true) {
                    const returnedUsers = users.length > 0 ? users.map((user, i) => {
                        return {label: user.name, value: user.id}
                    }) : [];
                    setAvailableUsers((prevAvailableUsers) => [...prevAvailableUsers, ...returnedUsers]);
                }
            }
        ).catch(() => {
        });

        return () => {
            isStillMounted.current = false;
        }

    }, []);

    if (preview) {
        return (
            <Fragment>
                <ServerSideRender
                    block="vczapi/list-meetings"
                    attributes={
                        {
                            columns: columns,
                            displayType: displayType,
                            postsToShow: postsToShow,
                            orderBy: orderBy,
                            shortcodeType: shortcodeType,
                            showFilter: showFilter,
                            selectedCategory: selectedCategory,
                            selectedAuthor: selectedAuthor
                        }
                    }
                />
            </Fragment>
        );
    }

    return (
        <>
            <InspectorControls>
                <PanelBody title="Settings" initialOpen={true}>
                    {isStillMounted.current &&
                    <SelectControl
                        label={__('Show Meeting or Webinar', 'video-conferencing-with-zoom-api')}
                        value={shortcodeType}
                        options={[
                            {label: "Meeting", value: "meeting"},
                            {label: "Webinar", value: "webinar"},
                        ]}
                        help={"Determines whether to show Meeting or Webinar"}
                        onChange={(value) => setAttributes({shortcodeType: value})}
                    />
                    }

                    {isStillMounted.current &&
                    <>
                        <SelectControl
                            label={__('Type of Meeting/Webinar to Show', 'video-conferencing-with-zoom-api')}
                            value={displayType}
                            onChange={(value) => setAttributes({displayType: value})}
                            options={[
                                {label: "Show All", value: ""},
                                {label: "Upcoming", value: "upcoming"},
                                {label: "Past", value: "past"},
                            ]}
                            help={"Show All,Upcoming or Past Meeting - default All"}
                        />
                        {displayType === 'upcoming' && <CheckboxControl
                            label={__('Show Meeting that have started', 'video-conferencing-with-zoom-api')}
                            checked={showPastMeeting}
                            onChange={(value) => setAttributes({showPastMeeting: value})}
                            help={"will show meetings that have passed meeting start time - upto 30 minutes after the meeting was scheduled to start."}
                        />}
                    </>
                    }


                    {isStillMounted.current &&
                    <SelectControl
                        label={__("Show Filter", "video-conferencing-with-zoom-api")}
                        value={showFilter}
                        options={[
                            {label: 'Yes', value: 'yes'},
                            {label: 'No', value: 'no'},
                        ]}
                        onChange={(value) => {
                            setAttributes({showFilter: value})
                        }}
                    />
                    }
                    {isStillMounted.current &&
                    <RangeControl
                        label={__("Columns", 'video-conferencing-with-zoom-api')}
                        value={columns}
                        help={"how many columns in the grid. Default value is 3. Users can use 1 or 2 or 3 or 4 - Any value upper than 4 will take 3 column value."}
                        onChange={(value) => {
                            setAttributes({columns: value})
                        }}
                        min={1}
                        max={4}
                    />
                    }

                    {isStillMounted.current &&
                    <SelectControl
                        label={__("Order By", "video-conferencing-with-zoom-api")}
                        value={orderBy}
                        options={[
                            {label: 'Default Sorting', value: ''},
                            {label: 'Ascending', value: 'ASC'},
                            {label: 'Descending', value: 'DESC'},
                        ]}
                        onChange={(value) => {
                            setAttributes({orderBy: value})
                        }}
                    />
                    }


                    {isStillMounted.current &&
                    <>
                        <span>{__('Category', 'video-conferencing-with-zoom-api')}</span>
                        <AsyncSelect
                            cacheOptions
                            defaultOptions={availableCategories}
                            isMulti
                            isClearable
                            placeholder={__('Select categories - default All categories are shown', 'video-conferencing-with-zoom-api')}
                            onChange={(selectedInput, {action}) => {
                                if (action === "clear") {
                                    setAttributes({selectedCategory: []});
                                    return [];
                                } else if (action === "remove-value") {
                                    setAttributes({selectedCategory: selectedInput});
                                    return selectedInput;
                                } else if (action === "select-option") {
                                    const newSelectedCategories = unionBy(selectedCategory, selectedInput, 'value');
                                    setAttributes({selectedCategory: newSelectedCategories});
                                    setAvailableUsers(prevAvailableCategory => {
                                        return unionBy(prevAvailableCategory, [selectedInput], 'value');
                                    });
                                }
                            }}
                            defaultValue={intersectionWith(availableCategories, selectedCategory, isEqual)}
                            className="components-base-control"
                        />
                    </>
                    }


                    {isStillMounted.current &&
                    <>
                        <span>{__('Author', 'video-conferencing-with-zoom-api')}</span>
                        <AsyncSelect
                            cacheOptions
                            defaultOptions={availableUsers}
                            noResultsText
                            loadOptions={debounce(getUsers, 800)}
                            isClearable
                            placeholder={__('Select Author - Default All Authors are shown', 'video-conferencing-with-zoom-api')}
                            onChange={(selectedUser, {action}) => {
                                if (action === 'clear') {
                                    setAttributes({selectedAuthor: 0});
                                } else if (action === 'remove-value') {
                                    setAttributes({selectedAuthor: selectedUser});
                                } else {
                                    setAttributes({selectedAuthor: selectedUser.value});
                                    setAvailableUsers(prevAvailableUser => {
                                        return unionBy(prevAvailableUser, [selectedUser], 'value');
                                    });
                                }

                            }}
                            defaultValue={availableUsers.filter(option => option.value === selectedAuthor)}
                            className="components-base-control"
                        />
                    </>

                    }

                    {isStillMounted.current &&
                    <RangeControl
                        label={__('Number of Meetings/Webinars to show', 'video-conferencing-with-zoom-api')}
                        value={postsToShow}
                        onChange={(value) => setAttributes({postsToShow: value})}
                        min={1}
                        max={100}
                    />
                    }
                </PanelBody>
            </InspectorControls>
            <div {...useBlockProps()}>
                <Disabled>
                    <ServerSideRender
                        block="vczapi/list-meetings"
                        attributes={
                            {
                                columns: columns,
                                displayType: displayType,
                                postsToShow: postsToShow,
                                orderBy: orderBy,
                                shortcodeType: shortcodeType,
                                showFilter: showFilter,
                                selectedCategory: selectedCategory,
                                selectedAuthor: selectedAuthor
                            }
                        }
                    />
                </Disabled>
            </div>
        </>

    );
}
