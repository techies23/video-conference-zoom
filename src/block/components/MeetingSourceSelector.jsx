import { __ } from '@wordpress/i18n'
import { PanelBody, SelectControl, TextControl, ComboboxControl } from '@wordpress/components'
import { useSelect } from '@wordpress/data'
import { useState } from '@wordpress/element'

export default function MeetingSourceSelector ({
  sourceType,
  meetingId,
  selectedMeetingPostId,
  onChangeSourceType,
  onChangeMeetingId,
  onChangeSelectedMeetingPostId,
  title = __('Meeting Source', 'video-conferencing-with-zoom-api'),
}) {
  const [searchValue, setSearchValue] = useState('')

  // Fetch 'zoom-meetings' post type async
  const { meetingPosts, isLoading } = useSelect(
    (select) => {
      if (sourceType !== 'post_type') {
        return { meetingPosts: [], isLoading: false }
      }
      const { getEntityRecords, isResolving } = select('core')
      const query = {
        per_page: 20,
        search: searchValue || undefined,
        status: 'publish',
      }
      return {
        meetingPosts: getEntityRecords('postType', 'zoom-meetings', query) || [],
        isLoading: isResolving('getEntityRecords', ['postType', 'zoom-meetings', query]),
      }
    },
    [sourceType, searchValue],
  )

  const comboboxOptions = meetingPosts.map((post) => ({
    value: post.id,
    label: post.title?.rendered ? post.title.rendered : `(ID: ${post.id})`,
  }))

  return (
    <PanelBody title={title}>
      <SelectControl
        label={__('Source Type', 'video-conferencing-with-zoom-api')}
        value={sourceType}
        options={[
          { label: __('Current Post / Page', 'video-conferencing-with-zoom-api'), value: 'current' },
          { label: __('Select Zoom Meeting Post', 'video-conferencing-with-zoom-api'), value: 'post_type' },
          { label: __('Custom Meeting ID', 'video-conferencing-with-zoom-api'), value: 'custom' },
        ]}
        onChange={onChangeSourceType}
      />

      {sourceType === 'custom' && (
        <TextControl
          label={__('Zoom Meeting ID', 'video-conferencing-with-zoom-api')}
          value={meetingId}
          onChange={onChangeMeetingId}
          placeholder="1234567890"
        />
      )}

      {sourceType === 'post_type' && (
        <ComboboxControl
          label={__('Search Zoom Meeting', 'video-conferencing-with-zoom-api')}
          value={selectedMeetingPostId || null}
          onChange={(val) => onChangeSelectedMeetingPostId(Number(val))}
          options={comboboxOptions}
          onFilterValueChange={setSearchValue}
          isLoading={isLoading}
          help={__('Search by title to link to a specific zoom-meetings post.', 'video-conferencing-with-zoom-api')}
        />
      )}
    </PanelBody>
  )
}