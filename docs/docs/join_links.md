Video Conferencing with Zoom plugin easily allows your users to join your meetings or webinars using [Zoom's Native WebSDK](https://marketplace.zoom.us/docs/sdk/native-sdks/web/) WebSDK without needing to download Zoom App on your computer. 

### Embed or Join via Browser Method

This section shows you on how you can embed your meeting using a simple shortcode which allows your users to directly join your meeting/webinar.

><strong style="color:red;">If you are facing issues with this or your plugin version is below 3.9.0: Check out this section first for [known issues](join_links.md#known-issues-when-embedding-join-via-browser).</strong>

#### Shortcode Usage

`[zoom_join_via_browser meeting_id="YOUR_MEETING_ID" login_required="no" iframe="no" title="Test" height="500px" disable_countdown="yes" passcode="1232132121" webinar="no" image="https://images.unsplash.com/photo-1459257831348-f0cdd359235f"]`

Where,

* `meeting_id` : Your MEETING ID.
* `login_required` : "yes or no", Requires login to view or join.
* `iframe` : "yes or no", Show meeting or webinar in iframe element. This is not recommeded as there are [known issues](join_links.md#known-issues-when-embedding-join-via-browser) with this approach. Set this to "no" for more robust approach.
* `title` : Title of your Embed Session
* `height` : Height of embedded video IFRAME.
* `disable_countdown` : "yes or no", enable or disable countdown.
* `passcode` : Set password of your meeting to automatically let users join without needing them to enter password.
* `webinar` : "yes" for embedding webinars.
* `image` : Image url for your post.

#### Overriding Template

You can edit elements from "join via browser" page by overriding template from **"plugins/video-conferencing-with-zoom-api/templates/shortcode/embed-session.php"**.

> Copy **"plugins/video-conferencing-with-zoom-api/templates/shortcode/embed-session.php"**. 

> to **"wp-content/themes/yourtheme/video-conferencing-zoom/shortcode/embed-session.php"**

After that you can edit the file and change the contents according to your preference.

#### Redirecting after completing the meeting filter.

To redirect user after a meeting fails, after completed or if meeting is not yet started; Add below code to your functions.php file in your theme and replace it with url you want to redirect:

**`add_filter('vczapi_api_redirect_join_browser', function() { 
    return 'https://yoursiteurl.com/page';
});`**

<img src="https://deepenbajracharya.com.np/wp-content/uploads/2020/03/Screen-Shot-2020-03-17-at-2.52.54-PM.png"  alt="Browser join image">

Check this post as well to know more on direct browser join feature <a href="https://deepenbajracharya.com.np/joining-meetings-in-zoom-directly-from-browser/">https://deepenbajracharya.com.np/joining-meetings-in-zoom-directly-from-browser/</a>

### Additional Informations

#### How join links work ?

Join links allow users to join meetings easily from frontend. Check below screenshot on how its used in this plugin.

<img src="https://deepenbajracharya.com.np/wp-content/uploads/2020/03/join-links-page.png" alt="Join Links Page">

#### You can disable join links as well

If you do not want to allow users to directly join via browser then you can disable that link from your posts page like shown in below screenshot.

<img src="https://deepenbajracharya.com.np/wp-content/uploads/2020/03/disable-join-links.png" alt="Disable Join Links">

### Browser Compatiblity

Mobile web browsers now supported
Meeting SDK for Web v2.4.0 and higher supports the major Android and iOS browsers.
Note that the Meeting SDK for Web on mobile browsers does not have complete feature parity with the Meeting SDK for Web on desktop browsers. Plugin currently uses webSDK higher than 2.4.0

<a href="https://marketplace.zoom.us/docs/sdk/native-sdks/web#browser-support" target="_blank">Official Doc here</a>

<table><thead><tr><th></th><th>Chrome 69+</th><th>Firefox 56+</th><th>Safari 11+</th><th>Edge 79+</th></tr></thead><tbody><tr><td>Gallery View</td><td>(1)</td><td>✘</td><td>✘</td><td>(1)</td></tr><tr><td>Pause Recording</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Waiting Room</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Share Video</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Share Screen</td><td>✓</td><td>✓</td><td>✘</td><td>✓</td></tr><tr><td>Join Computer Audio</td><td>✓</td><td>✓</td><td>(2)</td><td>✓</td></tr><tr><td>Join Audio by Phone</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Meeting Host Controls</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>View Shared Video</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>View Shared Screen</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Request Remote Control</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>In-meeting Chat</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Closed Captioning</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>Webinar Q&amp;A</td><td>✓</td><td>✓</td><td>✓</td><td>✓</td></tr><tr><td>End-to-end encryption (E2EE) (3)</td><td>✘</td><td>✘</td><td>✘</td><td>✘</td></tr><tr><td>Share computer audio</td><td>(4)</td><td>✘</td><td>✘</td><td>✘</td></tr></tbody></table>

### Performance Limits

Currently, the Zoom Meeting Web SDK encodes at a maximum resolution of 720p. See Web SDK 720p video for details. If a user is in a meeting with both native and browser clients, the browser client video displayed within the native client will be of lower quality due to encoding limitations.

### Loading CDN Resources

From plugin version 3.6.2 users can now decide to load the webSDK resources directly from Zoom CDN.

To do this add below code to your config.php file.

`define('VCZAPI_STATIC_CDN', true);`

If incase, there is problem loading your join via browser page when using CDN. You can change the "true" value to "false". 

### Known issues when embedding join via browser

Below are some known issues when you use **[zoom_join_via_browser iframe="yes"]** shortcode to embed your meeting into your site. Please note that these issues are not fixable at the moment so, if you are facing these issues - please avoid using **[zoom_join_via_browser iframe="yes"]**

- Cannot fullscreen the meeting window.
- Delay in voice.
- Delay in video.
- Delay in screen sharing.
- Echo in sound.
- Cannot go into gallery view mode.
