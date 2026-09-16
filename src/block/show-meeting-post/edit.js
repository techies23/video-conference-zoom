/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n'
import { debounce } from 'lodash'
import ServerSideRender from '@wordpress/server-side-render'
import { BlockControls, useBlockProps } from '@wordpress/block-editor'
import { Disabled, Placeholder, ToolbarGroup, Spinner, CheckboxControl, SelectControl, ComboboxControl } from '@wordpress/components'
import { useEffect, useState, useRef } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

export default function Edit (props) {
  const { attributes, setAttributes } = props
  const { postID, preview, template, countdown, description, details } = attributes

  const [availableMeetings, setAvailableMeetings] = useState([])
  const [searchResults, setSearchResults] = useState([])
  const [isLoading, setIsLoading] = useState(false)
  const [isEditing, setIsEditing] = useState(false)

  const isStillMounted = useRef()

  const handleFilter = debounce((searchValue) => {
    if (!searchValue) {
      setSearchResults([])
      return
    }

    setIsLoading(true)
    apiFetch({ path: '/wp/v2/zoom_meetings?per_page=5&search=' + searchValue })
    .then(meetings => {
      if (isStillMounted.current === true) {
        const formatted = meetings.length > 0 ? meetings.map(meeting => ({
          label: meeting.title.rendered,
          value: meeting.id
        })) : []
        setSearchResults(formatted)
        setIsLoading(false)
      }
    })
    .catch(() => {
      if (isStillMounted.current === true) {
        setSearchResults([])
        setIsLoading(false)
      }
    })
  }, 400)

  const editControls = [{
    icon: (!isEditing) ? 'edit' : 'no',
    title: (!isEditing) ? 'Edit' : 'Close',
    subscript: 'Edit',
    onClick: () => {
      setIsEditing(prevIsEditing => !prevIsEditing)
    }
  }]

  useEffect(() => {
    isStillMounted.current = true

    let queryParams = '/wp/v2/zoom_meetings?per_page=5'
    queryParams = (postID !== 0) ? queryParams + '&include=' + postID : queryParams

    apiFetch({
      path: queryParams,
    }).then(
      meetings => {
        if (isStillMounted.current === true) {
          const returnedMeetings = meetings.length > 0 ? meetings.map((meeting) => {
            return { label: meeting.title.rendered, value: meeting.id }
          }) : []
          setAvailableMeetings(returnedMeetings)
        }
      }
    )

    return () => {
      isStillMounted.current = false
    }

  }, [])

  if (preview) {
    return (
      <img src={vczapi_blocks.embed_post_preview} alt="Embed Zoom post"/>
    )
  }

  // Combine initial meetings with search results to avoid losing selected options
  const comboboxOptions = searchResults.length > 0 ? searchResults : availableMeetings

  return (
    <div {...useBlockProps()}>
      <BlockControls>
        <ToolbarGroup controls={editControls}/>
      </BlockControls>
      {
        !isStillMounted.current && <Spinner/>
      }

      {(postID === 0 || isEditing) &&
        <Placeholder>
          <h2>{__('Zoom -  Show Meeting Post', 'video-conferencing-with-zoom-api')}</h2>
          <div className="vczapi-blocks-form">
            <div className="vczapi-blocks-form--group">
              <ComboboxControl
                label={__('Select Meeting to Show', 'video-conferencing-with-zoom-api')}
                value={postID || null}
                options={comboboxOptions}
                onFilterValueChange={handleFilter}
                isLoading={isLoading}
                onChange={(selectedID) => {
                  setAttributes({ postID: Number(selectedID) })
                  setIsEditing(false)
                }}
              />
            </div>
            <div className="vczapi-blocks-form--group">
              <SelectControl
                label={__('Template', 'video-conferencing-with-zoom-api')}
                value={template}
                options={[
                  { label: 'Default', value: 'none' },
                  { label: 'Boxed', value: 'boxed' }
                ]}
                onChange={(selectedOption) => {
                  setAttributes({ template: selectedOption })
                }}
              />
            </div>
            {(template !== 'boxed') &&
              <>
                <div className="vczapi-blocks-form--group">
                  <CheckboxControl
                    label={__('Display Countdown?', 'video-conferencing-with-zoom-api')}
                    checked={countdown}
                    onChange={(selectedOption) => {
                      setAttributes({ countdown: selectedOption })
                    }}
                  />
                </div>
                <div className="vczapi-blocks-form--group">
                  <CheckboxControl
                    label={__('Display Description?', 'video-conferencing-with-zoom-api')}
                    checked={description}
                    onChange={(selectedOption) => {
                      setAttributes({ description: selectedOption })
                    }}
                  />
                </div>
                <div className="vczapi-blocks-form--group">
                  <CheckboxControl
                    label={__('Display Details Section?', 'video-conferencing-with-zoom-api')}
                    checked={details}
                    onChange={(selectedOption) => {
                      setAttributes({ details: selectedOption })
                    }}
                  />
                </div>
              </>
            }
          </div>
        </Placeholder>
      }
      {(postID !== 0 && !isEditing) &&
        <Disabled>
          <ServerSideRender
            block="vczapi/show-meeting-post"
            attributes={
              {
                postID: postID,
                template: template,
                description: description,
                countdown: countdown,
                details: details
              }
            }
          />
        </Disabled>
      }
    </div>
  )
}