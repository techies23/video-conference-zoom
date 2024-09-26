import ZoomMtgEmbedded from '@zoom/meetingsdk/embedded'

const EmbedZoomMtg = (() => {
    const init = () => {
        const client = ZoomMtgEmbedded.createClient()

        let meetingSDKElement = document.getElementById('vczapi-embed-source')
        console.log(meetingSDKElement);

        client.init({zoomAppRoot: meetingSDKElement, language: 'en-US'})
    }

    return {
        init
    }
})();

document.addEventListener('DOMContentLoaded', function () {
    EmbedZoomMtg.init();
});
