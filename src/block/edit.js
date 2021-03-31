/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import {__} from '@wordpress/i18n';
import {unionBy} from 'lodash';

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
import './editor.scss';
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
    const {className, attributes, setAttributes} = props;
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
    } = attributes;

    const isStillMounted = useRef();

    const [availableCategories, setAvailableCategories] = useState([]);
    const [availableUsers, setAvailableUsers] = useState([{label: 'None', value: 0}]);

    const getUsers = (inputValue) => {
        return apiFetch({path: '/wp/v2/users?per_page=5&search=' + inputValue}).then(
            users => {
                if (isStillMounted.current === true) {
                    return users.length > 0 ? users.map((user, i) => {
                        return {label: user.name, value: user.id}
                    }) : [];
                }
            }
        ).catch(() => {
            return [];
        })
    }

    useEffect(() => {
        isStillMounted.current = true;

        apiFetch({path: '/wp/v2/zoom_meeting_cats'}).then(
            zoomCats => {
                if (isStillMounted.current === true) {
                    const defaultCategories = [{label: 'None', value: ''}];
                    const formattedCategories = zoomCats.length > 0 ? zoomCats.map((cats, i) => {
                        return {label: cats.name + ' (' + cats.count + ')', value: cats.slug}
                    }) : [];
                    // console.log(zoomCats);
                    setAvailableCategories([...defaultCategories, ...formattedCategories]);
                }
            }
        ).catch(() => {
            if (isStillMounted.current === true)
                setAvailableCategories([]);
        });


        let userUrl = '/wp/v2/users?per_page=10&who=authors';
        if (selectedAuthor !== 0) {
            userUrl = userUrl + '&include=' + selectedAuthor;
        }

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

    return (
        <Fragment>
            <InspectorControls>
                <PanelBody title="Settings" initialOpen={true}>

                    <SelectControl
                        label={__('Show Meeting or Webinar', 'vczapi-pro')}
                        value={shortcodeType}
                        options={[
                            {label: "Meeting", value: "meeting"},
                            {label: "Webinar", value: "webinar"},
                        ]}
                        help={"Determines whether to show Meeting or Webinar"}
                        onChange={(value) => setAttributes({shortcodeType: value})}
                    />

                    <SelectControl
                        label={__('Type of Meeting/Webinar to Show', 'vczapi-pro')}
                        value={displayType}
                        options={[
                            {label: "Show All", value: ""},
                            {label: "Upcoming", value: "upcoming"},
                            {label: "Past", value: "past"},
                        ]}
                        help={"Show All,Upcoming or Past Meeting - default All"}
                        onChange={(value) => setAttributes({displayType: value})}
                    />

                    {displayType === 'upcoming' && <CheckboxControl
                        label={__('Show Meeting that have started', 'vczapi-pro')}
                        checked={showPastMeeting}
                        onChange={(value) => setAttributes({showPastMeeting: value})}
                        help={"will show meetings that have passed meeting start time - upto 30 minutes after the meeting was scheduled to start."}
                    />}

                    <SelectControl
                        label={__("Show Filter", "vczapi-pro")}
                        value={showFilter}
                        options={[
                            {label: 'Yes', value: 'yes'},
                            {label: 'No', value: 'no'},
                        ]}
                        onChange={(value) => {
                            setAttributes({showFilter: value})
                        }}
                    />

                    <RangeControl
                        label={__("Columns", 'vczapi-pro')}
                        value={columns}
                        help={"how many columns in the grid. Default value is 3. Users can use 1 or 2 or 3 or 4 - Any value upper than 4 will take 3 column value."}
                        onChange={(value) => {
                            setAttributes({columns: value})
                        }}
                        min={1}
                        max={4}
                    />

                    <SelectControl
                        label={__("Order By", "vczapi-pro")}
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

                    <SelectControl
                        label={__("Categories", "vczapi-pro")}
                        value={selectedCategory}
                        options={availableCategories}
                        onChange={(value) => {
                            setAttributes({selectedCategory: value})
                        }}
                    />

                    {isStillMounted.current === true &&
                    <>
                        <span>{__('Author','vczapi-pro')}</span>
                        <AsyncSelect
                            cacheOptions
                            textFieldProps={{
                                label: 'Label',
                                InputLabelProps: {
                                    shrink: true,
                                },
                            }}
                            defaultOptions={availableUsers}
                            loadOptions={getUsers}
                            onChange={(selectedUser) => {
                                setAttributes({selectedAuthor: selectedUser.value});
                                setAvailableUsers(prevAvailableUser => {
                                    return unionBy(prevAvailableUser, [selectedUser], 'value');
                                });
                            }}
                            value={availableUsers.filter(option => option.value === selectedAuthor)}
                            className="components-base-control"
                        />
                    </>

                    }


                    <RangeControl
                        label={__('Number of Meetings/Webinars to show', 'vczapi-pro')}
                        value={postsToShow}
                        onChange={(value) => setAttributes({postsToShow: value})}
                        min={1}
                        max={100}
                    />
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
        </Fragment>
    );
}
