This plugin allows you to easily join your meetings using join links from frontend page.

<img src="https://deepenbajracharya.com.np/wp-content/uploads/2020/03/Screen-Shot-2020-03-17-at-2.52.54-PM.png"  alt="Browser join image">

Check this post as well to know more on direct browser join feature <a href="https://deepenbajracharya.com.np/joining-meetings-in-zoom-directly-from-browser/">https://deepenbajracharya.com.np/joining-meetings-in-zoom-directly-from-browser/</a>

### How join links work ?

Join links allow users to join meetings easily from frontend. Check below screenshot on how its used in this plugin.

<img src="https://deepenbajracharya.com.np/wp-content/uploads/2020/03/join-links-page.png" alt="Join Links Page">

### You can disable join links as well

If you do not want to allow users to directly join via browser then you can disable that link from your posts page like shown in below screenshot.

<img src="https://deepenbajracharya.com.np/wp-content/uploads/2020/03/disable-join-links.png" alt="Disable Join Links">

### Browser Compatiblity

**The following are not supported:**

-  **Mobile web browsers.**
-  **Internet Explorer (IE).**
-  **iFrames. iFrames make Cross-Origin Isolation doubly complex. Use at your own risk.**

<a href="https://marketplace.zoom.us/docs/sdk/native-sdks/web#browser-support" target="_blank">Official Doc here</a>

<table><thead><tr><th></th><th>Chrome 69+</th><th>Firefox 56+</th><th>Safari 11+</th><th>Edge 79+</th></tr></thead><tbody><tr><td>Gallery View</td><td>(1)</td><td>✘</td><td>✘</td><td>(1)</td></tr><tr><td>Pause Recording</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Waiting Room</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Share Video</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Share Screen</td><td>✓</td><td>✓</td><td>✘</td><td>✓</td></tr><tr><td>Join Computer Audio</td><td>✓</td><td>✓</td><td>(2)</td><td>✓</td></tr><tr><td>Join Audio by Phone</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Meeting Host Controls</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>View Shared Video</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>View Shared Screen</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Request Remote Control</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>In-meeting Chat</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Closed Captioning</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Webinar Q&amp;A</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>End-to-end encryption (E2EE) (3)</td><td>✘</td><td>✘</td><td>✘</td><td>✘</td></tr><tr><td>Share computer audio</td><td>(4)</td><td>✘</td><td>✘</td><td>✘</td></tr></tbody></table>

### Performance Limits

Currently, the Zoom Meeting Web SDK encodes at a maximum resolution of 720p. See Web SDK 720p video for details. If a user is in a meeting with both native and browser clients, the browser client video displayed within the native client will be of lower quality due to encoding limitations.

### Not working ?

1. You'll need to have SSL enabled for this feature to work.
2. New version of Zoom WebSDK adds re-captcha. Please check if your browser is not blocking any popups when joining meeting via browser.

### Loading CDN Resources

From plugin version 3.6.2 users can now decide to load the webSDK resources directly from Zoom CDN.

To do this add below code to your config.php file.

`define('VCZAPI_STATIC_CDN', true);`

If incase, there is problem loading your join via browser page when using CDN. You can change the "true" value to "false". 

### Known issues when embedding join via browser

Below are some known issues when you use [zoom_join_via_browser] shortcode to embed your meeting into your site. Please note that these issues are not fixable at the moment so, if you are facing these issues - please avoid using [zoom_join_via_browser]

- Cannot fullscreen the meeting window.
- Delay in voice.
- Delay in video.
- Delay in screen sharing.
- Echo in sound.
- Cannot go into gallery view mode.

