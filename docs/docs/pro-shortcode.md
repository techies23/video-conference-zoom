### 1. List meeting registrants

This shortcode can be used to list all author meeting list in frontend and to view registrants for those meeting.

Use: `[vczapi_pro_author_registrants]`

### 2. Show calendar view

You can find more information for this in [PRO version widget documentation.](/vczapi-pro/#calendar-widget).

Use: `[vczapi_zoom_calendar author="" show="" calendar_default_view="dayGridMonth" show_calendar_views="yes"]`

Where,

* **class** = Ability to add custom css classes to modify design of calendar
* **author** = The user id of an Author, will show only the meetings/webinars of that particular author
* **show** = use "meeting" or "webinar" to either show only meetings or only webinars leave empty or do not use if you want to show all.
* **calendar_default_view** = options => dayGridMonth,timeGridWeek,timeGridDay,listWeek
* **show_calendar_views** = give ability to user to see calendar in different views - default value is "no", use "yes" to show other views

### 3. List Meetings with Register Now button

Use: `[vczapi_list_meetings per_page="5" category="test,test2,test3" order="ASC" type="upcoming" show="meeting" cols="3" filter="yes"]`

**NOTE: This shortcode will show register now button if a meeting is enabled registration.**

Where,

* **author** = Author ID of the posts to display.
* **per_page** = Number of posts per page
* **category** = Show assigned category lists
* **order** = Show order of posts "DESC" or "ASC"
* **type** = options => upcoming or past
* **show** = options => meeting or webinar
* **show_on_past** = "yes" or "no" - Default is "yes", this parameter will show meetings for 30 minutes after the past date, if upcoming type is defined.
* **cols** = Show how many columns in the grid. Default value is 3. Users can use 1 or 2 or 3 or 4 - Any value upper than 4 will take 3 column value.
* **filter** = "yes" (default) or "no" - Show filter options or not.

### 4. List Registered Events

This shortcode will display all registered events via user ID or via logged in user session ID.

Use: `[vczapi_registered_meetings user_id="" show="upcoming"]`

Where,

* **user_id** = (optional) - Use this to show events for only the defined user ID
* **show** = (optional ) - Use "upcoming" or "past" to show upcoming or past registered events.

### 5. Show Registration Form Only

Display registration form in any page you want.

Use: `[vczapi_pro_registration_form post_id='']`

Where,

* **post_id** = (required) = POST ID of the registration zoom event you want to display.