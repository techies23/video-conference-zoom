export const escapeHtml = (value) => {
    return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
};

export const filesizeConverter = (bytes, i18n) => {
    bytes = parseInt(bytes, 10) || 0;
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' ' + i18n.formatGigaBytes;
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' ' + i18n.formatMegaBytes;
    if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' ' + i18n.formatKiloBytes;
    return (bytes || 0) + ' ' + i18n.formatBytes;
};

export const formatRecordingDate = (value) => {
    const date = new Date(value);
    if (isNaN(date.getTime())) return escapeHtml(value || '');

    const datePart = date.toLocaleDateString('en-US', {month: 'long', day: 'numeric', year: 'numeric'});
    const timePart = date.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit'});
    return escapeHtml(`${datePart}, ${timePart}`);
};