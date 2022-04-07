All these shortcodes are available as elementor and gutenberg modules.

### Types

1. `[zoom_api_link meeting_id="" link_only="no"]`
2. `[zoom_meeting_post post_id="ZOOM MEETING POST ID"]`
3. `[zoom_list_meetings per_page="5" category="test,test2,test3" filter="no" type="upcoming" cols="3"]` 
4. `[zoom_list_webinars per_page="5" category="test,test2,test3" filter="no" type="upcoming" cols="3"]` 
5. `[zoom_list_host_meetings host="YOUR_HOST_ID"]`
6. `[zoom_api_webinar meeting_id="YOUR_WEBINAR_ID" link_only="no"]`
7. `[zoom_list_host_webinars host="YOUR_HOST_ID"]`
8. `[zoom_join_via_browser meeting_id="YOUR_MEETING_ID" login_required="no" help="yes" title="Test" height="500px" disable_countdown="yes"]`
9. `[zoom_recordings host_id="YOUR_HOST_ID" downloadable="yes"]`
10. `[zoom_recordings_by_meeting meeting_id="YOUR_MEETING_ID" downloadable="no"]`

### 1. Show single Zoom meeting detail

Use: `[zoom_api_link meeting_id="" link_only="no"]`

Where,

* `meeting_id` = Your meeting ID.
* `link_only` = Show only link or not. Change to "yes" instead of "no" to show link only

Your frontend page should look like:

<img src="https://deepenbajracharya.com.np/wp-content/uploads/2019/11/Meetings-%E2%80%93-Plugin-Tester-1024x520.png">

### 2. Show a meeting post with Countdown

Use: `[zoom_meeting_post post_id=""]`

Where,

* `post_id` = Zoom Meeting post ID.

### 3. List Upcoming or Past Meetings

Use: `[zoom_list_meetings per_page="5" category="test,test2,test3" order="ASC" type="upcoming" cols="3"]`

Where,

* **author** = Author ID of the posts to display.
* **per_page** = Number of posts to show per page
* **category** = Which categories to show in the list
* **order** = ASC or DESC based on post created time.
* **type** = "upcoming" or "past" - To show only upcoming meeting based on start time. (Remove this option to show all meetings).
* **filter** = "yes" or "no" - Shows filter option for the list.
* **show_on_past** = "yes" or "no" - Default is "yes", this parameter will show meetings for 30 minutes after the past date, if upcoming type is defined.
* **cols** = Show how many columns in the grid. Default value is 3. Users can use 1 or 2 or 3 or 4 - Any value upper than 4 will take 3 column value.

**NOTE: This was added in version 3.3.4 so, old meetings which were created might need to be updated in order for this shortcode to work properly.**

### 4. List Upcoming or Past Webinars 

Use: `[zoom_list_webinars per_page="5" category="test,test2,test3" order="ASC" type="upcoming" cols="3"]`

Where,

* **author** = Author ID of the posts to display.
* **per_page** = Number of posts to show per page
* **category** = Which categories to show in the list
* **order** = ASC or DESC based on post created time.
* **type** = "upcoming" or "past" - To show only upcoming webinars based on start time. (Remove this option to show all webinars).
* **filter** = "yes" or "no" - Shows filter option for the list.
* **show_on_past** = "yes" or "no" - Default is "yes", this parameter will show webinars for 30 minutes after the past date, if upcoming type is defined.
* **cols** = Show how many columns in the grid. Default value is 3. Users can use 1 or 2 or 3 or 4 - Any value upper than 4 will take 3 column value.

### 5. List Meetings based on HOST ID

Use: `[zoom_list_host_meetings host="YOUR_HOST_ID"]`

Where,

* `host` = Your HOST ID where you can get from **wp-admin > Zoom Meeting > Users = User ID**

**NOTE: Added from version 3.3.10. This will list all past and upcoming 300 meetings related to the defined HOST ID.**

### 6. Show Specific Webinar Detail

Use: `[zoom_api_webinar webinar_id="YOUR_WEBINAR_ID" link_only="no"]`

Where,

* `meeting_id` = Your Webinar ID which you want to show 

**NOTE: Added in version 3.4.0**

### 7. Show List of Webinars

Use: `[zoom_list_host_webinars host="YOUR_HOST_ID"]`

Where,

* `host` = Your HOST ID where you can get from **wp-admin > Zoom Meeting > Users = User ID** 

**NOTE: Added from version 3.4.0**

### 8. Embed Zoom Meeting in your Browser

<strong style="color:red;">This section has been moved to [another page](join_links.md#embed-or-join-via-browser-method)</strong>

### 9. Show recordings based on HOST ID.

Show recordings list in frontend based on host ID.

Usage: `[zoom_recordings host_id="YOUR_HOST_ID" downloadable="yes"]`

Where,

* `host_id` : YOUR HOST ID.
* `downloadable` : Default is set to false. If you want your users to be able to download your recordings.

### 10. Show Recordings based on Meeting ID

Show recordings list based on your meeting ID

Usage: `[zoom_recordings_by_meeting meeting_id="YOUR_MEETING_ID" downloadable="no" cache="true"]`

Where,

* `meeting_id` : YOUR MEETING ID to pull.
* `downloadable` : Default is set to false. If you want your users to be able to download your recordings.
* `cache` : set to 'false' or 'true' - Default is true. Shows latest records without caching any previous data. **Note: this may result in slow page load speed or even fail to load recordings due to API call limit from zoom side, if there are alot of recordings/ alot of traffic in your site.**

### How to get Meeting ID

1. Goto your wp-admin
2. In the side menu look of for "Zoom Meeting"
3. Click or hover and then open up "Live meetings" page.
4. Select user from the dropdown on top right.
5. Grab the ID from "meeting ID" column

### Shortcode Template Override

With new version, its possible to override the display of output from default plugin layout. You can do so by following method.

1. Goto **`wp-admin/plugins/video-conferencing-with-zoom-api/templates/shortcode`** folder
2. Copy this "Shortcode" folder to **`yourtheme/video-conferencing-zoom/shortcode/zoom-shortcode.php`**
3. Your done ! Change the styling and divs according to your needs.

### Elementor and Gutenbert Supported

From version 3.4.0 - Plugin is fully compatible with Elementor modules as well Gutenberg Blocks.