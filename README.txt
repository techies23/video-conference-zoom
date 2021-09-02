=== Video Conferencing with Zoom ===
Contributors: j__3rk, codemanas, digamberpradhan
Tags: zoom video conference, video conference, zoom, zoom video conferencing, web conferencing, online meetings
Donate link: https://www.paypal.com/donate?hosted_button_id=2UCQKR868M9WE
Requires at least: 4.9
Tested up to: 5.8
Stable tag: 3.8.13
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gives you the power to manage Zoom Meetings, Webinars, Recordings, Reports and create users directly from your WordPress dashboard.

== Description ==

Simple plugin which gives you the extensive functionality to manage Zoom Meetings, Webinars, Recordings, Users, Reports from your WordPress Dashboard. Now, with capability to add your own post as a meeting. Create posts as meetings directly from your WordPress dashboard to show in the frontend as a meeting page. Allow users to directly join via that page with click of a button.

[View the plugin live demo from here.](https://demo.codemanas.com/code-manas-pro/zoom-meetings/demo-zoom-event/ "Checkout our live demo here.")

**FEATURES:**

* Manage WordPress posts and link them to Live Zoom meetings and Zoom Webinars.
* Create Recurring meetings and Webinars (PRO)
* Enable registrations (PRO)
* Webhooks (PRO)
* Override single and archive page templates via your theme.
* JOIN DIRECTLY VIA WEB BROWSER FROM FRONTEND !
* Start Links for post authors.
* CountDown timer to Meeting start shows in individual meeting page.
* Start time and join links are shown according to local time compared with zoom timezone.
* Show user recordings based on Zoom Account.
* Display Webinars via Shortcode
* Use PMI (PRO)
* WCFM Integration( See EXTENDING AND MAKING MEETINGS PURCHASABLE section )
* WooCommerce Integration( See EXTENDING AND MAKING MEETINGS PURCHASABLE section )
* WooCommerce Appointments Integration( See EXTENDING AND MAKING MEETINGS PURCHASABLE section )
* WooCommerce Bookings Integration( See EXTENDING AND MAKING MEETINGS PURCHASABLE section )
* Developer Friendly
* Daily and Account Reports
* Shortcode
* Shortcode Template Customize
* Import your Zoom Meetings into your WordPress Dashboard in one click.
* Gutenberg Blocks Support
* Elementor Support

**DOCUMENTATION LINKS:**

* [Installation](https://zoom.codemanas.com/integration/ "Installation")
* [Full Shortcode Documentation](https://zoom.codemanas.com/shortcode/ "Shortcodes")
* [Full Documentation](https://zoom.codemanas.com/ "Usage Documentation")
* [Usage Documentation /w WP](https://deepenbajracharya.com.np/zoom-api-integration-with-wordpress/ "Usage Documentation")
* [Webhooks](https://zoom.codemanas.com/webhooks/ "Webhooks")

**EXTENDING AND MAKING MEETINGS PURCHASABLE:**

Addon: **[WooCommerce Integration](https://www.codemanas.com/downloads/zoom-meetings-for-woocommerce/ "WooCommerce Integration")**:
Addon: **[WCFM Integration](https://www.codemanas.com/downloads/wcfm-integration-for-zoom/ "WCFM Integration")**:
Addon: **[WooCommerce Booking Integration](https://www.codemanas.com/downloads/zoom-integration-for-woocommerce-booking/ "WooCommerce Booking Integration")**:
Addon: **[Booked Appointments Integration](https://www.codemanas.com/downloads/zoom-meetings-for-booked-appointments/ "Booked Appointments Integration")**:
Addon: **[WooCommerce Appointments Integration](https://www.codemanas.com/downloads/zoom-for-woocommerce-appointments/ "WooCommerce Appointments Integration")**:

* Integration with WooCommerce and Zoom Meetings Countdown page.
* Purchasable Single Meetings !
* WP-Cron emails before 24 hours of the meeting.
* Separate meeting list page in my-account section.
* Integration with WooCommerce Bookings
* Automated WooCommerce Booking meeting process.
* Individual Booking Product Meetings
* Individual Booking Product Hosts
* Individual Booking product meeting links for each bookings.
* Integration with WCFM
* Integration with WooCommerce Product Vendors
* Integration with Booked Appointments.
* Integration with WooCommerce Appointments.

& more functionalities and integrations are on its way!

You can find more information on the Pro version on website: **[codemanas.com](https://www.codemanas.com/ "codemanas.com")**

**OVERRIDDING TEMPLATES:**

If you use Zoom Meetings > Add new section i.e Post Type meetings then you might need to override the template. Currently this plugin supports default templates.

REFER FAQ to override page templates!

**COMPATIBILITY:**

* Enables direct integration of Zoom into WordPress.
* Compatible with LearnPress, LearnDash 3.
* Enables most of the settings from zoom via admin panel.
* Provides Shortcode to conduct the meeting via any WordPress page/post or custom post type pages
* Separate Admin area to manage all meetings.
* Can add meeting links via shortcode to your WooCommerce product pages as well.

**Zoom Web SDK Notice from Zoom Itself**

The Web SDK enables the development of video applications powered by Zoom’s core framework inside an HTML5 web client through a highly optimized WebAssembly module.

As an extension of the Zoom browser client, this SDK is intended for implementations where the end user has a low-bandwidth environment, is behind a network firewall, or has restrictions on their machine which would prevent them from installing the Zoom Desktop or Mobile Clients.

**SHORTCODE:**

[Full Shortcode Documentation](https://zoom.codemanas.com/shortcode/ "Shortcodes")

**CONTRIBUTING**

There’s a [GIT repository](https://github.com/techies23/video-conference-zoom "GIT repository") if you want to contribute a patch. Please check issues. Pull requests are welcomed and your contributions will be appreciated.

Please consider giving a 5 star thumbs up if you found this useful.

Lastly, Thank you all to those contributors who have contributed for this plugin in one or the other way. Taking from language translations to minor or major suggestions. We appreciate your input in every way !!

**QUICK DEMO:**

[youtube https://www.youtube.com/watch?v=5Z2Ii0PnHRQ]

== Installation ==
Search for the plugin -> add new dialog and click install, or download and extract the plugin, and copy the the Zoom plugin folder into your wp-content/plugins directory and activate.

== Frequently Asked Questions ==

= Add users not working for me =

The plugin settings allow you to add and manage users. But, you should remember that you can add users in accordance with the Zoom Plans, so they will be active for the chosen plan. More information about Zoom pricing plans you can find here: https://zoom.us/pricing

= Join via Browser not working, Camera and Audio not detected =

This issue is because of HTTPS protocol. You need to use HTTPS to be able to allow browser to send audio and video.

= Blank page for Single Meetings page =

If you face blank page in this situation you should refer to [Template Overriding](https://zoom.codemanas.com/template_override/#content-not-showing "Template Overriding") and see Template override section.

This happens because of the single meeting page template from the plugin not being supported by your theme and i cannot make my plugin support for every theme page template because of which you'll need to override the plugin template from my plugin to your theme's standard. ( Basically, like how WooCommerce does!! )

= Countdown not showing/ guess is undefined error in my console log =

If countdown is not working for you then the first thing you'll nweed to verify is whether your meeting got created successfully or not. You can do so by going to wp-admin > Zoom Meetings > Select your created meeting and on top right check if there are "Start Meeting", "join Meeting links". If there are those links then, you are good on meeting.

However, even though meeting is created and you are not seeing countdown timer then, you might want to check your browser console and see if there is any "guess is undefined" error. If so, there might be a plugin conflict using the same moment.js library. **Report to me in this case**

= Forminator plugin conflict fix =

Please check this thread: https://wordpress.org/support/topic/conflict-with-forminator-2/

= How to show Zoom Meetings on Front =

* By using shortcode like [zoom_api_link meeting_id="123456789"] you can show the link of your meeting in front.

= How to override plugin template to your theme =

1. Goto **wp-content/plugins/video-conferencing-with-zoom-api/templates**
2. Goto your active theme folder to create new folder. Create a folder such as **yourtheme/video-conferencing-zoom/{template-file.php}**
3. Replace **template-file.php** with the file you need to override.
4. Overriding shortcode template is also the same process inside folder **templates/shortcode**

= Do i need a Zoom Account ? =

Yes, you should be registered in Zoom. Also, depending on the zoom account plan you are using - Number of hosts/users will vary.

== Screenshots ==
1. Join via browser
2. Meetings Listings. Select a User in order to list meetings for that user.
3. Add a Meeting.
4. Frontend Display Page.
5. Users List Screen. Flush cache to clear the cache of users.
6. Reports Section.
7. Settings Page.
8. Backend Meeting Create via CPT
9. Shortcode Output

== Changelog ==

= 3.8.13 September 2nd, 2021 =
* Changed: WebSDK version updated to 1.9.8.

= 3.8.12 August 20th, 2021 =
* Fixed: Join via browser not working when used PMI for meetings/webinars.
* Fixed: Meeting/Webinar description unescaped string being passed.
* Fixed: Browser header issue fixed "Cross-Origin-Embedder-Policy", "Cross-Origin-Opener-Policy" related to webSDK resulting in not showing gallery view and video for user joining through join via web.

= 3.8.11 August 11th, 2021 =
* Fixed: Ajax pagination for webinars not working correctly.

= 3.8.10 August 10th, 2021 =
* Added: Made Datatable translatable.
* Minor bug fixes.

= 3.8.9 August 1st, 2021 =
* Minor Bug Fixes

= 3.8.8 July 23rd, 2021 =
* Added: Show recording password, can be enabled with filter "vczapi_recordings_show_password"
* Updated: WebSDK to version 1.9.7
* Added: "cache" paramter to [zoom_recordings_by_meeting] shortcode.

= 3.8.7 July 19th, 2021 =
* Changed: Ajax pagination filter changed for zoom-meetings list page.
* Fixed: Registrations not working when [zoom_meeting_post] shortcode is used.

= 3.8.6 July 16th, 2021 =
* Fixed: Join via browser block fix for webinar.

= 3.8.5 July 8th, 2021 =
* Added: Ajax pagination for shortcode meeting/webinar list page (https://zoom.codemanas.com/shortcode/#3-list-upcoming-or-past-meetings).

= 3.8.4 July 1st, 2021 =
* Changed: Inceased default duration from 40 to 45 as per zoom change.

= 3.8.3 June 10th, 2021 =
* Fixed: PRO version register now link not working due to priority check incorrectly.
* Fixed: Importing meetings not working sometimes due to incorrect meeting ID check.
* Added: Option to stop meeting deletion on zoom side when you delete it from your website.
* Updated: WebSDK to version 1.9.6

= 3.8.2 May 18th, 2021 =
* Updated: WebSDK version to 1.9.5
* Fixed: Gutenberg plugin compatibility.
* Fixed: https://wordpress.org/support/topic/gutenberg-blocks-support/ issue

= 3.8.1 April 27th, 2021 =
* Fixed: If meeting does not have password, password field will be hidden when join via browser.
* Fixed: atob error when meeeting password embed is disabled.

= 3.8.0 April 8th, 2021 =
* Added: Gutenberg Blocks support.
* Added: Option to choose host ID manually when creating meeting incase there are more than 300 users in a zoom account.

= 3.7.4 March 30th, 2021 =
* Added: Setting to disable auto password generation when creating a meeting from wp-admin.

= 3.7.3 March 25th, 2021 =
* Added: Capability to show multiple recording shortcode in single page. Same for Webinar list shortcode.

= 3.7.2 March 18th, 2021 =
* Fixed: Archive page not showing webinar dates correctly.

= 3.7.1 March 12th, 2021 =
* Added: Option to enable disable waiting room.
* Added: Encoded password and meeting ID when join via browser.
* Updated: Allowed template functions to be overridden from theme.

= 3.7.0 March 10th, 2021 =
* Fixed: Validation for Agenda.
* Added: Shortcode for displaying post type counter post. [zoom_meeting_post post_id="YOUR_POST_ID"]
* Updated: Zoom WebSDK to version 1.9.1 (https://github.com/zoom/websdk/blob/master/CHANGELOG.md)

= 3.6.33 March 4th, 2021 =
* Changed: CSS class re-added for backwards compatibility.

= 3.6.32 March 3rd, 2021 =
* Added: Plugin settings menu
* Added: 'vczapi_admin_meeting_fields' filter hook to filter results when create/update post type.
* Added: Filter upcoming or past meetings in wp-admin

= 3.6.31 March 1st, 2021 =
* Added: 'vczapi_join_via_browser_after_script_load' action hook for additional scripts to be added in join via browser page.
* Added: Column parameter for [zoom_list_meetings] and [zoom_list_webinars] shortcodes.
* Changed: CSS Grid layout for Meeting list page and shortcode listing pages.

= 3.6.30 February 24th, 2021 =
* Added: Dutch translation file added - Thanks to "Gijsbert van Luinen" for providing the translation files.
* Fixed: Width for join via browser fields when browser info is removed.
* Added: Show meetings after the event date has passed for about 30 minutes more. This can be done by adding "show_on_past" in [zoom_list_meetings] or [zoom_list_webinars] - By default this is set to true.
* Added: Theme style enqueued in join via browser pages for additional css changes.

= 3.6.29 February 15th, 2021 =
* Fixed: Recordings list shortcode pulled via UUID for recurring meeting fix.

= 3.6.28 February 12th, 2021 =
* Fixed: View recordings button not working when recordings exceed 10 entries.
* Added: Recordings list view sorting removed for action and duration

= 3.6.27 February 9th, 2021 =
* Fixed: Showing hour and minutes in frontend meeting details page.
* Updated: Translations - Thanks to the WordPress translation team !!
* Fixed: Show shortcode on backend based on type - Meeting or Webinar

= 3.6.26 February 2nd, 2021 =
* Updated: WebSDK library to 1.9.0 which supports gallery view
* Updated: Join via browser ( If logged-in user, automatically fills out email and name )

= 3.6.25 January 29th, 2021 =
* Fixed: Post Author Name not showing in single meeting page.

= 3.6.24 January 29th, 2021 =
* Added filter hook to show or enable different views in join via browser window. See https://zoom.codemanas.com/filters_hooks/#join-via-browser-show-fields

= 3.6.23 January 28th, 2021 =
* Updated: Duration selector when creating meeting.
* Added: Invite button remove from join via browser through settings page.

= 3.6.22 January 13th, 2021 =
* Fixed: Helper function to get time according to DST.
* Fixed: Single meeting page timezone showing in backend timezone instead of local timezone.

= 3.6.21 January 12th, 2021 =
* Added: Default language selector option for join via browser page.
* Updated: WebSDK to version 1.8.6

= 3.6.20 December 29th, 2020 =
* Added: Viewing recordings in modal view.
* Added: Recordings search via date.

= 3.6.19 December 23rd, 2020 =
* Updated: WebSDK to verion 1.8.5
* Changed: Join via browser output format changed.
* Fixed: Date Format display issue.

= 3.6.18 =
* Fixed: jQuery on load event not triggered in mozilla firefox resulting in join display links errors.

= 3.6.17 =
* Updated: Translations from WordPress language directly. Thanks to WordPress language community.
* Updated: Show default host if selected user has explictly assigned host id in user meta.

= 3.6.16 =
* Added: Enable/Disable gutenberg support when editing a meeting post type.

= 3.6.15 =
* Addded: Action hook before single page render.

= 3.6.14 December 1st, 2020 =
* Fixed: Issue with join via browser showing signature invalid.

= 3.6.13 November 26th, 2020 =
* Fixed: Elementor Widget passcode escaping resulted in incorrect password output.
* Added: Hooks, for PRO version to add - ical functionality in PRO version in meeting individual pages.
* Fixed: FATAL error on include_template function being called when filename was not parsed in for join-links template.

= 3.6.12 November 25th, 2020 =
* Modified: Changed API calls for custom implementation methods.
* Added: Recurring meeting sync support for PRO version.
* Updated: WebSDK version 1.8.3

= 3.6.11 November 10th, 2020 =
* Added: For Devs, Filter for meetings for Join via Shortcode

= 3.6.9/3.6.10 November 9th, 2020 =
* Added: User cache delete on activation and deactivation
* Fixed: [zoom_recordings_by_meeting] shortcode did not fetch all past recordings previously. This version fixes that.
* Updated: Zoom WebSDk to version 1.8.1

= 3.6.8 October 30th, 2020 =
* Fixed: Join via web browser not working when Elementor Page Builder or most of the page builder is used to override Zoom Meetings archive page.

= 3.6.7 October 28th, 2020 =
* Added: Custom date type added in settings.

= 3.6.6 October 23rd, 2020 =
* Added: Global option to disable join via browser.

= 3.6.5 October 13th, 2020 =
* Changed: Assign Zoom Users to WordPress users to be more flexible with PRO version.
* Fixed: Zoom WebSDK when joining in IFRAME redirection in the same iframe window. Fixed it to redirect back to main screen without users noticing the iframe.
* Added: Pending Users view page.
* Changed: Cache Helper functions updated.
* Fixed: Rank Math SEO tab not working in Elementor because of script loading error from the plugin.
* Minor bug fixes.

= 3.6.4 October 7th, 2020 =
* Removed: Enforce login field from "Live Meetings" because Zoom API has removed this field.
* Added: Support for Personal Meeting ID in shortcode [zoom_api_link]

= 3.6.3 October 2nd, 2020 =
* Added: Arguement "passcode" to [zoom_join_via_browser] browser shortcode. If passed "passcode" then join via browser will not require password to join the meeting.
* Added: Webinar capability for join via browser shortcode.
* Bug Fixes

= 3.6.2 September 17th, 2020 =
* Updated: Zoom WebSDK to version 1.8.0
* Added: CDN loading support for zoom webSDK static resources. Add "VCZAPI_STATIC_CDN" to true in config file for this.
* Added: Zoom API notice error if connection is not established with API in wp-admin.
* Added: Ability to display same "zoom_list_meetings" shortcode twice in same page.

= 3.6.1 August 17th, 2020 =
* Fixed: Deprecated warning issue on PHP 7.4 using {} syntax reported by <a href="https://wordpress.org/support/users/antonyjosephsmith/">@antonyjosephsmith</a>
* Fixed: Sync Date not syncing properly when editing synced meeting.
* Fixed: Deprecated ternary operator multiple usage in same check function.
* Fixed: Join via browser - Error joining if email field is disabled.
* Updated: Join via browser page - CSS identifiers changed.

= 3.6.0 August 11th, 2020 =
* Added: Webinar post type module.
* Added: Meetings importer.
* Updated: Translations
* Added: Support for recurring meetings based on PRO version.
* Added: Email field when joining via browser.
* Fixed: Webinar bulk delete.
* Fix: Major bug fixes and code refactor.
* Updated: Transitioning to PSR-4 Standard

= 3.5.2 July 27th, 2020 =
* Added: [zoom_recordings_by_meeting meeting_id="MEETING_ID" downloadable="yes"] which shows recordings based on meeting ID.
* Added: Elementor Widgets for new shortcodes.
* Fixed: Time shown in fronted with shortcode and singe meetings pages changed.
* Updated: Old global variable pull removed. New variable assigned for more performance loading and accurate meeting details and timings.

= 3.5.1 July 23rd, 2020 =
* Fixed: Time Locale Fixed.
* Added: Support for Recurring meetings via Pro Version.
* Fixed: Shortcode [zoom_list_meetings] for upcoming meetings. Added new meta field for showing exact meetings based on local timezones. Users will need to re-update the old meetings.
* Added: Hook vczapi_join_via_browser_footer in join via browser page to enable users to enqueue personal scripts or code in the join via browser footer page.
* Added: Shortcode parameter "downloadable" which disables users from downloading the recordings. Set to "false" by default (https://wordpress.org/support/topic/recordings-api-ignores-download-settings/).
* Added: Datatable Responsive added (https://wordpress.org/support/topic/responsive-datatables-js/)
* Changed: Table design fixes
* Fixed: Minor bug fixes.

= 3.5.0 July 10th, 2020 =
* Added: Recordings API
* Added: Recordings shortcode to show recordings by host.
* Fixed: Embed Join via Web Browser re-captcha popup fail issue.

= 3.4.2 July 9th, 2020 =
* Updated: WebSDK to version 1.7.10 ( webSDK changes https://timeline.noticeable.io/8XMdMkIr8cTlKfj8DTtx/posts/web-sdk-version-1-7-9-updates?cache=false & https://timeline.noticeable.io/8XMdMkIr8cTlKfj8DTtx/posts/web-sdk-updates-version-1-7-10 )
* Added: Filter Hook to remove language field at the time of joining meeting via web "vczapi_api_bypass_lang"
* Added: Filter Hook to remove browser info field at the time of joining meeting via web "vczapi_api_bypass_lang"
* Changed: Template file "join-web-browser.php" minor changes i.e added filter hooks.
* Updated: Zoom WebSDK css libraries.
* Changed: Shortcode "zoom_list_host_webinars" and "zoom_list_host_meetings" meeting cache value to 5 minutes.

= 3.4.0/3.4.1 June 4th, 2020 =
* Added: Webinar Support Added with Shortcode for showing webinars.
* Added: Elementor Widgets for Listing Meetings and Meeting Display Output.
* Added: Webinar Shortcodes
* Added : Join Via Browser supported by WebSDK
* Updated: WebSDK to version 1.7.8
* Minor Bug Fixes
* Updated Translations
* Added: Chinese(Taiwan) Translation. Thanks to the WordPress translation community !

= 3.3.13 May 23rd, 2020 =
* Bug Fixed: All join links were being hidden when setting was not checked in Zoom Meeting > Seetings page "Hide Join Links for Non-Loggedin ?".

= 3.3.12 May 22nd, 2020 =
* Updated: [zoom_list_meetings] - Upcoming meetings are shown based on WordPress timezone settings.
* Added: Hide join links for non-loggedin users for shortcode.
* Updated: Checking "Requires Login?" from Zoom Meetings > Add New page will not hide join links to non-logged in users.
* Added: Meeting Password field for Join via Browser

= 3.3.11 May 11th, 2020 =
* Fixed: Shortcode category listing
* Updated: Meeting Password Links fixed according to new Zoom Meeting password change policy.

= 3.3.10 May 6th, 2020 =
* Added: Shortcode [zoom_list_host_meetings host="your_host_id"] for showing list of meetings based on HOST ID.
* Added: Date Localization based on WordPress Locale.
* Updated: Zoom WebSDK to version 1.7.7

= 3.3.9 May 1st, 2020 =
* Added: Spanish Translation. Thanks to <a href="https://wordpress.org/support/users/clickening/">@clickening</a>
* Added: Russian Translation. Thanks to the <a href="https://translate.wordpress.org/locale/ru/default/wp-plugins/video-conferencing-with-zoom-api/">Translation team</a>.

= 3.3.8 April 23rd, 2020 =
* Fixed: Normal shortcode meeting start time not showing due to recurring check script.

= 3.3.7 April 22nd, 2020 =
* Fixed: Shortcode Join Links
* Updated: Zoom WEBSDK to version 1.7.6

= 3.3.6 April 20th, 2020 =
* Fixed: Archive page not loading when no meetings existed.

= 3.3.5 April 20th, 2020 =
* Fixed: add_query_args when joining via browser occured a blank page or 404 page in some cases.
* Removed: Host selection when editing the meeting after created
* Fixed: Minor bug Fixes

= 3.3.4 April 15th, 2020 =
* Fixed: Category for Shortcode
* Slovak Translation Updated: Thanks to <a href="https://profiles.wordpress.org/branike/">Branislav Ďorď</a>
* Added: Meeting Type for [zoom_list_meetings type="upcoming"] shortcode.

= 3.3.3 April 10th, 2020 =
* Fix: Static resources JS and CSS file version number changes according to update. Reported by <a href="https://wordpress.org/support/users/bencoates/">bencoates</a>

= 3.3.2 April 10th, 2020 =
* Updated: WEBSDK to version 1.7.5
* Bug Fix: Error Messages Check
* Added: WebSDK (Join via browser) link in Shortcode as well [zoom_list_meetings].

= 3.3.1 April 7th, 2020 =
* Updated: WebSDK updated 1.7.3
* Fixed: Shortcode bug not outputting multiple shortcodes when called.

= 3.3.0 April 6th, 2020 =
* German Translation Added: Thanks to Peter Ginser <a href="https://wordpress.org/support/users/ginspet/">@ginspet</a>
* Slovak Translation Added: Thanks to Branislav Ďorď
* Fixed: New shortcode to embed that allows you to directly or start join via page or post. See shortcode section in details page for details.
* Added: Start or End meeting manually which allows users to end meeting ahead of time and disallowing anyone to join it.
* Added: New hooks for recurring meetings support
* Added: Filters for WC Product Vendors Support
* Fixed: Countdown timer adjusted.
* Added: Meeting start, end text can be now customized from settings page.
* Added: Allow original zoom author name to be shown in frontend single pages.
* Added: Filter added which allows you to modify the post DATA you sent at time of creating meeting as well as updating !
* Fixed: Responsive issue when join via browser ( link somewhere in the support which i lost it ).
* Added: Meeting states to be manually changed from users perspective (https://wordpress.org/support/topic/feature-request-more-details-on-meeting-states/)
* Added: Password field in post type pages.
* Added: Debug Mode button on posts page for Zoom Meetings
* Alot of Bug fixes

= 3.2.31 - March 29th, 2020 =
* Added: Filter hook: vczapi_timezone_list => for timezone list.
* Added: Meeting link encryption changed.
* Added: Disable review nag notices
* Added: French Translation thanks to Julien Laumond
* Added: Meeting start/ended text filters => vczapi_meeting_event_text

= 3.2.2 - Mar 27th, 2020 =
* Added: New shortcode for displaying list of meetings in frontend via category.
* Added: Join link button classes

= 3.2.1 - Mar 23, 2020 =
* Fixed: vczapi_get_template_part trailingslashhit fix reported by @https://wordpress.org/support/users/amba_13/
* Added: Users table pagination for WP-Admin section
* Fixed: Re-Added users section with bug fixed
* Added: Time format display changed to 'LLLL' /w Day also on single meetings page.
* Added: Category for Zoom Meetings added

= 3.2.0 - Mar 17, 2020 =
Added: Join directly via browser without needing to goto Zoom Website.
Added: Join links show/hide option in backend.
Fixed: Minor bugs and fixes

= 3.1.7 - Mar 11, 2020 =
Added: Shortcode copy button in each meeting page in wp-admin.

= 3.1.5 - Feb 27, 2020 =
Added: Adjustments on settings pages.

= 3.1.3 - 3.1.4 - Feb 25, 2020 =
Added: Start time to show according to local time.
Fixed: Minor bug fixes ( No effect elsewhere ).

= 3.1.2 - Feb 22, 2020 =
Fixed: Frontend coutdown timer fixed according to client local timezone.
Fixed: Join Links show on frontend according to time.
Fixed: Some minor bug fixes.
Added: Ajax link fetch in regards to client local time and show join links accordingly.
Added: Join Link timezone with Local Time ( For shortcode and individual meeting pages )
Added: Meetings links will now only show in Local Timezone ( For shortcode and individual meeting pages )
Added: Meetings links will be valid till 1 hour - Before and after the meeting time. ( For shortcode and individual meeting pages )
Added: Localized string values.
Added: Shortcode join link template override.
Bug Fix: Meeting links dissapearing. ( For shortcode and individual meeting pages )

= 3.1.1 =
Fixes: Minor fixes in Reports and enqueue script section.
Added: Addons page.

= 3.1.0 =
Added: Show past join link meetings on frontend links.

= 3.0.6 =
Fixed: Multiple link only shortcode in single page output fixed.

= 3.0.5 =
Fixed: Countdown timer. Countdown fixed on more than a month of countdown.

= 3.0.4 =
* Added: Single link output shortcode parameter added

= 3.0.3 =
Fixed: Timer countdown now supports safari
Updated: Timer Countdown library
Fixed: Timer will now show "meeting starting" text after countdown is completed.
Updated: Corrected Localization strings

= 3.0.0 - 3.0.2 =
Support: Divi template support for frontend
Fixed: Auto rewrite url flush

= 3.0.0 - 3.0.1 =
Added: Custom post type meetings for seperate post meetings.
Added: Page template overrides.
Added: Frontend meeting join links, start links for authors.
Fixed: Timezone Values
Changed: Optimized overall codebase.
Removed: Seperate vanity shortcode removed.
Fixed: Bug Fixes on creating meetings, Warnings and Notice errors.

= 2.2.3 =
Fixed: API access token time increased by 1 hour

= 2.2.3 =
Added: Validation issue fixed
Fixed: Added vanity URL functionality in settings
Fixed: Minor users API bug fixes

= 2.2.2 =
Added: UI changes
Fixed: Validation Issues fixed
Fixed: Minor bug fixes

= 2.2.1 =
Fixed: CURL Request fail fixed

= 2.2.0 =
* Removed: API version 1 support. Added to deprecated library.
* Added: New options when adding meetings
* Added: Classic editor meeting link add icon
* Fix: Changed API call implementation to fit WordPress standards
* Fix: Major bug fixes

= 2.1.3 =
* Minor Changes

= 2.1.2 =
* Minor Changes
* Timezone Settings Changes

= 2.1.1 =
* Minor Changes

= 2.1.0 =
* API version 2 added.
* Major fixes
* Major breaking changes in this version.
* Added: Assign Host ID manually section for Developers

= 2.0.5 =
* Minor Changes

= 2.0.4 =
* Minor Change

= 2.0.3 =
* WordPress 4.8 Compatible

= 2.0.1 =
* Added: Translation Error Fixed
* Added: French Translation
* Added: 3 new hooks see under "Using Action Hook" in description page.

= 2.0.0 =
* Added: Datatables in order to view all listings
* Added: New shortcode button in tinymce section
* Added: Bulk delete
* Added: Redesigned Zoom Meetings section where meetings can be viewed based on users.
* Added: Redesigned add meetings section with alot of bug fixes and attractive UI.
* Changed: Easy datepicker
* Changed: Removed editing of users capability. Maybe in future again ?
* Removed: Single link shortcode ( [zoom_api_video_uri] )
* Bug Fix: Reports section causing to define error when viewing available reports
* Bug Fix: Error on reload after creating a meeting
* Bug Fix: Unknown error when trying to connect with api keys ( Rare Case )
* Changed: Total codebase of the plugin.
* Fixed: Few security issues such as no nonce validations.
* Alot of Major Bug Fixes but no breaking change except for a removed shortcode

= 1.3.1 =
* Minor Bug Fixes

= 1.3.0 =
* Added Pagination to meetings list
* Hidden API token fields
* Fixed various bugs and flaws

= 1.2.4 =
* WordPress 4.6 Compatible

= 1.2.3 =
* Validation Errors Added
* Minor Bug Fixes

= 1.2.2 =
* Minor Functions Change

= 1.2.1 =
* Bug Fixes
* Major Bug fix on problem when adding users
* Removed only system users on users adding section
* Added a shortcode which will print out zoom video link. [zoom_api_video_uri]

= 1.2.0 =
* Various Bug Fixes
* Validation Errors Fixed
* Translation Ready

= 1.1.1 =
* Increased Add Meeting Refresh time interval to 5 seconds.

= 1.1 =
* Added Reports
* Minor Bug fixes and Changes

= 1.0.2 =
* Minor Changes

= 1.0.1 =
* Minor UI Changes
* Removed the unecessary dropdown in Meeting Type since only Scheduled Meetings are allowed to be created.
* Added CSS Editor in Settings Page
* Alot of Minor Bug Fixes

= 1.0.0 - May 9th, 2016 =
* Initial Release