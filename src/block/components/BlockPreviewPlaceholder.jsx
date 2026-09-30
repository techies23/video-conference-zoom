/**
 * Editor placeholder for blocks that have nothing meaningful to render yet.
 *
 * These blocks previously showed a screenshot shipped from
 * `dist/images/block-previews/`, but no such images were ever committed to the
 * repository, so every placeholder in the editor rendered as a broken image.
 * Drawing the placeholder in CSS keeps the editor working without shipping
 * binaries and keeps the string translations intact.
 */

export default function BlockPreviewPlaceholder({label, children}) {
    return <div className="vczapi-block-preview-placeholder">
        <span className="vczapi-block-preview-placeholder__label">{label}</span>
        {children}
    </div>;
}
