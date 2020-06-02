Previously, shortcode had to be written in complex way that some people would not feel reliable for this specific plugin. With new update its pretty easy to remember.

**Check out the <a href="https://youtu.be/5Z2Ii0PnHRQ?t=453" target="_blank">video documentation</a> if you want to see live demo.**

With new version you can grab your shortcode with just a click.

### Types

1. `[zoom_api_link meeting_id="" link_only="no"]`
2. `[zoom_list_meetings per_page="5" category="test,test2,test3"]` 
3. `[zoom_list_host_meetings host="YOUR_HOST_ID"]`
4. `[zoom_api_webinar meeting_id="YOUR_WEBINAR_ID" link_only="no"]`
5. `[zoom_list_host_webinars host="YOUR_HOST_ID"]`
6. `[zoom_join_via_browser meeting_id="YOUR_MEETING_ID" login_required="no" help="yes" title="Test" height="500px" disable_countdown="yes"]`

### 1. Show Single Zoom Meeting Detail

Use: `[zoom_api_link meeting_id="" link_only="no"]`

Where,

* `meeting_id` = Your meeting ID.
* `link_only` = Show only link or not. Change to "yes" instead of "no" to show link only

Your frontend page should look like:

<img src="https://deepenbajracharya.com.np/wp-content/uploads/2019/11/Meetings-%E2%80%93-Plugin-Tester-1024x520.png">

### 2. List Meeting Shortcode

Use: `[zoom_list_meetings per_page="5" category="test,test2,test3" order="ASC" type="upcoming"]`

Where,

* **per_page** = Number of posts to show per page
* **category** = Which categories to show in the list
* **order** = ASC or DESC based on post created time.
* **type** = "upcoming" or "past" - To show only upcoming meeting based on start time (Update to meeting is required for old post type meetings). Do not add to show all meetings.

**NOTE: This was added in version 3.3.4 so, old meetings which were created might need to be updated in order for this shortcode to work properly.**

### 3. List Meetings based on HOST ID

Use: `[zoom_list_host_meetings host="YOUR_HOST_ID"]`

Where,

* `host` = Your HOST ID where you can get from **wp-admin > Zoom Meeting > Users = User ID**

**NOTE: Added from version 3.3.10. This will list all past and upcoming 300 meetings related to the defined HOST ID.**

### 4. Show Specific Webinar Detail

Use: `[zoom_api_webinar webinar_id="YOUR_WEBINAR_ID" link_only="no"]`

Where,

* `meeting_id` = Your Webinar ID which you want to show 

**NOTE: Added in version 3.4.0**

### 5. Show Specific Webinar Detail

Use: `[zoom_list_host_webinars host="YOUR_HOST_ID"]`

Where,

* `host` = Your HOST ID where you can get from **wp-admin > Zoom Meeting > Users = User ID** 

**NOTE: Added from version 3.4.0**

### 6. Embed Zoom Meeting in your Browser

Embeds your meeting in an IFRAME for any page or post you insert this shortcode into.

<strong style="color:red;">Although this embed feature is here. I do no garauntee this would work properly as this is not natively supported by Zoom itself. This is here only because of user requests. USE THIS AT OWN RISK !!</strong>

Use: `[zoom_join_via_browser meeting_id="YOUR_MEETING_ID" login_required="no" help="yes" title="Test" height="500px" disable_countdown="yes"]`

Where,

* `meeting_id` : Your MEETING ID.
* `login_required` : "yes or no", Requires login to view or join.
* `help` : "yes or no", Help text.
* `title` : Title of your Embed Session
* `height` : Height of embedded video IFRAME.
* `disable_countdown` : "yes or no", enable or disable countdown.

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

### Elementor Support

From version 3.4.0 - Plugin now has some inbuilt modules for Elementor as well. We'll keep adding in the future as well.