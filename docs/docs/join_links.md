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

Table is copied from Zoom official directly !

<a href="https://marketplace.zoom.us/docs/sdk/native-sdks/web#browser-support" target="_blank">Official Doc here</a>

<table>
    <tbody>
      <tr>
        <th>Browser / Feature</th>
        <th>Chrome 69+</th>
        <th>Firefox 76+</th>
        <th>Safari 11+</th>
        <th>Chromium Edge 80+</th>
      </tr>
      <tr>
        <td>Gallery View</td>
        <td>✓</td>
        <td>✘</td>
        <td>✘</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>Pause Recording</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>Waiting Room</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>Share Video</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>Share Screen</td>
        <td>✓</td>
        <td>✓</td>
        <td>✘</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>Join Computer Audio</td>
        <td>✓</td>
        <td>✓</td>
        <td>(1)</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>Join Audio by Phone</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>Meeting Host Controls</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>View Shared Video</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>View Shared Screen</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>Request Remote Control</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>In-meeting Chat</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>Closed Captioning</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>Webinar Q&amp;A</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
        <td>✓</td>
      </tr>
      <tr>
        <td>End-to-end encryption (E2EE) (2)</td>
        <td>✘</td>
        <td>✘</td>
        <td>✘</td>
        <td>✘</td>
      </tr>
    </tbody>
  </table>

### Not working ?

1. You'll need to have SSL enabled for this feature to work.
2. New version of Zoom WebSDK adds re-captcha. Please check if your browser is not blocking any popups when joining meeting via browser.

### Loading CDN Resources

From plugin version 3.6.2 users can now decide to load the webSDK resources directly from Zoom CDN.

To do this add below code to your config.php file.

`define('VCZAPI_STATIC_CDN', true);`

If incase, there is problem loading your join via browser page when using CDN. You can change the "true" value to "false". 