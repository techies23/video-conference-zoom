import { __ } from '@wordpress/i18n'
import { useEffect, useState, useMemo } from '@wordpress/element'
import { useBlockProps, BlockControls } from '@wordpress/block-editor'
import ServerSideRender from '@wordpress/server-side-render'
import {
  Placeholder,
  ToolbarGroup,
  ToolbarButton,
  Button,
  SelectControl,
  ComboboxControl,
  Spinner,
} from '@wordpress/components'
import { debounce } from 'lodash'

export default function EditListHostMeeting ({ attributes, setAttributes }) {
  const { host, shouldShow, preview } = attributes
  const [isEditing, setIsEditing] = useState(false)
  const [hostOptions, setHostOptions] = useState([])
  const [isLoading, setIsLoading] = useState(false)

  // Fetch host options via WordPress AJAX endpoint
  const fetchHosts = (searchInput = '') => {
    setIsLoading(true)
    const ajaxUrl = window.ajaxurl || 'admin-ajax.php'
    const nonce = window?.vczapi_blocks?.nonce || ''

    const queryUrl = `${ajaxUrl}?action=vczapi_get_zoom_hosts&host=${encodeURIComponent(searchInput)}&nonce=${nonce}`

    fetch(queryUrl).then((response) => response.json()).then((result) => {
      // Format response to match ComboboxOption structure: { label, value }
      const formattedOptions = (result || []).map((item) => ({
        label: item.label || item.name || item.value,
        value: String(item.value || item.id),
      }))

      setHostOptions(formattedOptions)
    }).catch(() => {
      setHostOptions([])
    }).finally(() => {
      setIsLoading(false)
    })
  }

  // Debounce remote search queries to limit redundant network calls
  const debouncedFetchHosts = useMemo(
    () => debounce(fetchHosts, 300),
    [],
  )

  // Load initial host options on load & clean up pending debounced calls on unmount
  useEffect(() => {
    fetchHosts()
    return () => {
      debouncedFetchHosts.cancel()
    }
  }, [])

  if (preview) {
    return (
      <img
        src={vczapi_blocks?.list_host_meetings_preview_image}
        alt={__('List Host meetings', 'video-conferencing-with-zoom-api')}
      />
    )
  }

  const blockProps = useBlockProps()

  return (
    <div {...blockProps}>
      <BlockControls>
        <ToolbarGroup>
          <ToolbarButton
            icon={!isEditing ? 'edit' : 'no'}
            title={
              !isEditing
                ? __('Edit', 'video-conferencing-with-zoom-api')
                : __('Close', 'video-conferencing-with-zoom-api')
            }
            onClick={() => setIsEditing((prev) => !prev)}
          />
        </ToolbarGroup>
      </BlockControls>

      {(!host || isEditing) && (
        <Placeholder
          label={__(
            'Zoom - List Meetings/Webinars based on HOST',
            'video-conferencing-with-zoom-api',
          )}
        >
          <div className="vczapi-blocks-form">
            <div className="vczapi-blocks-form--group">
              <SelectControl
                label={__('Type', 'video-conferencing-with-zoom-api')}
                value={shouldShow?.value || shouldShow || 'meeting'}
                options={[
                  {
                    label: __('Meeting', 'video-conferencing-with-zoom-api'),
                    value: 'meeting',
                  },
                  {
                    label: __('Webinar', 'video-conferencing-with-zoom-api'),
                    value: 'webinar',
                  },
                ]}
                onChange={(newValue) =>
                  setAttributes({
                    shouldShow: {
                      label:
                        newValue === 'meeting' ? 'Meeting' : 'Webinar',
                      value: newValue,
                    },
                  })
                }
              />
            </div>

            <div className="vczapi-blocks-form--group">
              <ComboboxControl
                label={__('Select Host', 'video-conferencing-with-zoom-api')}
                value={host?.value || host || ''}
                options={hostOptions}
                onFilterValueChange={(searchValue) =>
                  debouncedFetchHosts(searchValue)
                }
                onChange={(selectedValue) => {
                  const selectedOption = hostOptions.find(
                    (opt) => opt.value === selectedValue,
                  )
                  setAttributes({
                    host: selectedOption || selectedValue,
                  })
                }}
              />
              {isLoading && <Spinner/>}
            </div>

            <div className="vczapi-blocks-form--group">
              <Button
                variant="primary"
                onClick={() => setIsEditing(false)}
              >
                {__('Save', 'video-conferencing-with-zoom-api')}
              </Button>
            </div>
          </div>
        </Placeholder>
      )}

      {host?.value && !isEditing && (
        <ServerSideRender
          block="vczapi/list-host-meetings"
          attributes={{
            host,
            shouldShow,
          }}
        />
      )}
    </div>
  )
}