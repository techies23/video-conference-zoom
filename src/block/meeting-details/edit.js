import { __ } from '@wordpress/i18n';
import {
  useBlockProps,
  useInnerBlocksProps,
  InspectorControls,
} from '@wordpress/block-editor';

// Import shared meeting selector component
import MeetingSourceSelector from '../components/MeetingSourceSelector';

// Nested template starter with child blocks pre-populated inside core/group
const DEFAULT_TEMPLATE = [
  ['core/heading', { level: 3, content: __('Details', 'video-conferencing-with-zoom-api') }],
  [
    'core/group',
    { layout: { type: 'flex', orientation: 'vertical' } },
    [
      ['vczapi/meeting-details-topic', {}],
      ['vczapi/meeting-details-hosted-by', {}],
      ['vczapi/meeting-details-start-time', {}],
      ['vczapi/meeting-details-duration', {}],
      ['vczapi/meeting-details-timezone', {}],
    ],
  ],
];

export default function Edit({ attributes, setAttributes }) {
  const { sourceType, customMeetingId, selectedMeetingPostId } = attributes;

  const blockProps = useBlockProps({
    className: 'vczapi-meeting-details-container',
  });

  const innerBlocksProps = useInnerBlocksProps(blockProps, {
    template: DEFAULT_TEMPLATE,
    templateInsertUpdatesSelection: false,
  });

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
  );
}