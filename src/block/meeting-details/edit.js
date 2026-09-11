import { __ } from '@wordpress/i18n'
import {
  useBlockProps,
  useInnerBlocksProps,
  InspectorControls,
} from '@wordpress/block-editor'

// Import our shared meeting selector component
import MeetingSourceSelector from '../components/MeetingSourceSelector'

// Default template starter (optional baseline structure)
const DEFAULT_TEMPLATE = [
  ['core/heading', { level: 3, content: __('Details', 'video-conferencing-with-zoom-api') }],
  ['core/group', { layout: { type: 'flex', orientation: 'vertical' } }],
]

export default function Edit ({ attributes, setAttributes }) {
  const { sourceType, customMeetingId, selectedMeetingPostId } = attributes

  const blockProps = useBlockProps({
    className: 'vczapi-meeting-details-container',
  })

  // Notice NO allowedBlocks restriction here
  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    template: DEFAULT_TEMPLATE,
    templateInsertUpdatesSelection: false,
  })

  return (
    <>
      <InspectorControls>
        <MeetingSourceSelector
          sourceType={sourceType}
          meetingId={customMeetingId}
          selectedMeetingPostId={selectedMeetingPostId}
          onChangeSourceType={(val) => setAttributes({ sourceType: val })}
          onChangeMeetingId={(val) => setAttributes({ customMeetingId: val })}
          onChangeSelectedMeetingPostId={(val) => setAttributes({ selectedMeetingPostId: val })}
        />
      </InspectorControls>

      <div {...innerBlocksProps} />
    </>
  )
}