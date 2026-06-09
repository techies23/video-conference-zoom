=== Video Conferencing with Zoom ===
Contributors: j__3rk, digamberpradhan, codemanas
Tags: zoom video conference, video conference, web conferencing, online meetings, webinars
Donate link: https://www.paypal.com/donate?hosted_button_id=2UCQKR868M9WE
Requires at least: 5.0
Tested up to: 7.0
Stable tag: 4.6.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gives you the power to manage Zoom Meetings, Zoom Webinars, Recordings, Reports and create users directly from your WordPress dashboard.

== Description ==

Video conferencing with Zoom plugin gives you the extensive functionality to manage your Zoom Meetings, Webinars, Recordings, Users, Reports from your WordPress Dashboard directly. The plugin is a great tool for managing your Zoom sessions on the fly without needing to go back and forth on multiple platforms. This plugin is developed in order to make smooth transitions in managing your online meetings or webinars without any hassle and time loss.

[View the plugin live demo from here.](https://demo.codemanas.com/code-manas-pro/zoom-meetings/demo-zoom-event/ "Checkout our live demo here.")

**FEATURES:**

* Manage your Zoom Meetings and Zoom Webinars.
* Manage Zoom users and Reports.
* Change frontend layouts as per your needs using template override.
* Join via browser directly without Zoom App.
* Show User recordings based on Zoom Account.
* Extensive Developer Friendly
* Shortcodes
* Import your Zoom Meetings into your WordPress Dashboard in one click.
* Gutenberg Blocks Support
* Elementor Support

**ADDON FEATURES**

* Recurring meetings and Webinars (PRO)
* Enable registrations (PRO)
* Webhooks (PRO)
* Use PMI (PRO)
* WCFM Integration ( See EXTENDING AND MAKING MEETINGS PURCHASABLE section )
* WooCommerce Integration ( See EXTENDING AND MAKING MEETINGS PURCHASABLE section )
* WooCommerce Appointments Integration ( See EXTENDING AND MAKING MEETINGS PURCHASABLE section )
* WooCommerce Bookings Integration ( See EXTENDING AND MAKING MEETINGS PURCHASABLE section )
 and more...

* [Zoom Theme](https://cmblocks.com/themes/cm-zoom/ "Zoom Theme")

**DOCUMENTATION LINKS:**

* [Installation](https://zoomdocs.codemanas.com/setup/ "Installation")
* [Shortcodes](https://zoomdocs.codemanas.com/shortcode/ "Shortcodes")
* [Documentation](https://zoomdocs.codemanas.com/ "Documentation")
* [Usage Documentation /w WP](https://deepenbajracharya.com.np/zoom-api-integration-with-wordpress/ "Usage Documentation")
* [Webhooks](https://zoomdocs.codemanas.com/webhooks/ "Webhooks")

**EXTENDING AND MAKING MEETINGS PURCHASABLE:**

Addon: **[Video Conferencing with Zoom Pro](https://www.codemanas.com/downloads/video-conferencing-with-zoom-pro/ "Video Conferencing with Zoom Pro")**:
Addon: **[WooCommerce Integration](https://www.codemanas.com/downloads/zoom-meetings-for-woocommerce/ "WooCommerce Integration")**:
Addon: **[WCFM Integration](https://www.codemanas.com/downloads/wcfm-integration-for-zoom/ "WCFM Integration")**:
Addon: **[WooCommerce Booking Integration](https://www.codemanas.com/downloads/zoom-integration-for-woocommerce-booking/ "WooCommerce Booking Integration")**:
Addon: **[Booked Appointments Integration](https://www.codemanas.com/downloads/zoom-meetings-for-booked-appointments/ "Booked Appointments Integration")**:
Addon: **[WooCommerce Appointments Integration](https://www.codemanas.com/downloads/zoom-for-woocommerce-appointments/ "WooCommerce Appointments Integration")**:

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
* Gutenberg
* Elementor
* Beaver Builder

**CONTRIBUTING**

There’s a [GIT repository](https://github.com/techies23/video-conference-zoom "GIT repository") if you want to contribute a patch. Please check issues. Pull requests are welcomed and your contributions will be appreciated.

Please consider giving a 5 star thumbs up if you found this useful.

Lastly, Thank you all to those contributors who have contributed for this plugin in one or the other way. Taking from language translations to minor or major suggestions. We appreciate your input in every way !!

**QUICK DEMO:**

[youtube https://www.youtube.com/watch?v=5Z2Ii0PnHRQ]

== Installation ==
Search for the plugin -> add new dialog and click install, or download and extract the plugin, and copy the the Zoom plugin folder into your wp-content/plugins directory and activate.

== Frequently Asked Questions ==

= Migrating from JWT to Server to Server method =

As of June 2023, Zoom will deprecate JWT App type - the plugin has moved to Server-to-Server OAuth App and SDK App type for Join via Browser / Web SDK support. If you face any Zoom connection issues then this might be the issue. Refer to this [Documentation](https://zoomdocs.codemanas.com/migration/ "Documentation") on how to migrate your old JWT method.

= Join via Browser showing Signature Invalid or Timeout =

Please check if you SDK app type is activated and re-check all the app credentials are valid.

= Updating to version 4.0.0 =

Please check how you can do the [Migration from JWT](https://zoomdocs.codemanas.com/migration/ "Migration from JWT")

= Add users not working for me =

The plugin settings allow you to add and manage users. But, you should remember that you can add users in accordance with the Zoom Plans, so they will be active for the chosen plan. More information about Zoom pricing plans you can find here: https://zoom.us/pricing

= Join via Browser not working, Camera and Audio not detected =

This issue is because of HTTPS protocol. You need to use HTTPS to be able to allow browser to send audio and video.

= Blank page for Single Meetings page =

If you face blank page in this situation you should refer to [Template Overriding](https://zoomdocs.codemanas.com/template_override/#content-not-showing "Template Overriding") and see Template override section.

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
= 4.6.8 - June 4th, 2026 =
* Security Fix: Patched get_auth ajax handler.
* Added: Periodically Re-Sync Zoom Meeting via API to avoid ZAK timeouts

= 4.6.7 - April 20th, 2026 =
* Security Fix: Patched delete action ajax.

= 4.6.6 - Jan 25th, 2026 =
* Security Fix: Patched get_auth ajax handler.

= 4.6.5 - July 7th 2025 =
* Fix: Issue with block themes - templates reverting to zoom template.

= 4.6.4 - April 21st 2025 =
* Recordings UUID needs to check for / and needs double encoding

= 4.6.3 - November 27th 2024 =
* Updated tested to version for WordPress to 6.7

= 4.6.1 - 4.6.2 October 3rd, 2024 =
* Changed name of class from I18N => Locales.

= 4.6.0 September 26th, 2024 =
* Updated: WebSDK to version 3.8.10
* Optimized: Join via browser code.
* Fixed: Join via browser language change.
* Added: Join before host time.
* Optimized: Scripts and Stylings
* Bug Fixes related to Meeting.

= 4.5.3 August 27th, 2024 =
* Fix: No Fixed Time meeting not working with `[zoom_meeting_post post_id="1938" template="boxed"]`

= 4.5.2 August 12th, 2024 =
* Fix: Auto recording was not being set for webinars
* Updated: Zoom WebSDK to version 2.18.3

= 4.5.1 July 1st, 2024 =
* Fixed: Undefined error $type issue.

= 4.5.0 June 10, 2024 =
* Added: Helper function for meeting types
* Fixed bugs related to meeting types

= 4.4.6 March 20, 2024 =
* Security Update: Fixed a issue related to ajax.

= 4.4.5 March 11th, 2024 =
* Security Update: Escaping for https://zoomdocs.codemanas.com/shortcode/#10-show-recordings-based-on-meeting-id (Cross-Site Scripting via Shortcode)
* Security Fix: Open Redirection when joining meeting with Join via Browser.

= 4.4.4 February 6th, 2024 =
* Re-Added back download button for recordings shortcode.

= 4.4.3 February 5th, 2024 =
* Fixed: Recordings fetching method changed based recurring meeting or Normal meeting. Should past meetings now be visible.

= 4.4.2 January 26th, 2024 =
*  Minor Warning issue fix.

= 4.4.1 January 24th, 2024 =
* Fixed: Gallery View should now be supported for IFrame join via browser shortcode.

= 4.4.0 January 16th, 2024 =
* Recordings hidable and bug fixes.
* Updated: Fetch Meeting ID recordings asynchronously.
* Updated websdk to version 2.18.2
* Bump WP scripts version
* Bug Fixes

= 4.3.3 October 31st, 2023 =
* Fixed: Conflict with Meow Gallery
* Updated: Vendor Library

= 4.3.2 September 25th 22nd, 2023 =
* Fixed: Debugger log not working.
* Added: WebSDK validator.
* Updated: Websdk to version 2.16.0

= 4.3.1 August 18th, 2023 =
* Fixed: Timezone issue not showing correctly in backend.

= 4.3.0 July 18th, 2023 =
* Deprecated: vczapi_encrypt_decrypt() to generate dynamic key when generating value.
* Added: New Encrypt Decrypt methods
* Added: Helper functions
* Updated: WebSDK to version 2.13.0
* Few updates to Codebase into PSR-4

= 4.2.1 June 19th, 2023 =
* Updated: Admin SDK text changed to Client ID and Client Secret.
* Fixed: Timezone Fix
* Fixed: Spectra plugin blocks template compatibility issue.
* Added: Join from browser directly without name, email field.

