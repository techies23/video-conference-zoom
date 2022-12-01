### Join via Browser Signature in Invalid or Connection Timeout.

In this case please check your **SDK app type credential are valid and activated.** Please note that the app type must be **activated**. You can also check if all the api credential values are correct. 

If everything is ok on your side, reach out to us with your site details as this issue can not be debugged without looking at the code.

### Embedded Join via Web Browser Issues
Few of the known issues when using embed join via web browser i.e only when used with <a href="https://zoom.codemanas.com/shortcode/#8-embed-zoom-meeting-in-your-browser">embed join via browser shortcode</a> or Join via Browser gutenberg Block.

1. Video not showing to participants but only shows to host.
2. Not able to fullscreen the view.

Above issues are caused due to the use of IFRAME html component. Currently, these issues are unsolvable so, please use this feature at your own risk i.e <a href="https://zoom.codemanas.com/shortcode/#8-embed-zoom-meeting-in-your-browser">embed join via browser shortcode</a> or Join via Browser gutenberg Block.

**_Also, note: These above issues does not affect for normal join via web browser pages._**

### Zoom Pro Plans

If you are subscribed to Zoom PRO plans you have more benefits for your meetings as well as webinars you host. So, its worth checking out Zoom plans here <a href="https://zoom.us/pricing">https://zoom.us/pricing</a>

Subscribing to PRO plans will only benefit your Zoom Account however, this plugin can be used for **FREE ACCOUNT** users as well.

### Add users not working for me

The plugin settings allow you to add and manage users. But, you should remember that you can add users in accordance with the Zoom Plans, so they will be active for the chosen plan. More information about Zoom pricing plans you can find here: https://zoom.us/pricing

### Join via Browser not working, Camera and Audio not detected

This issue is because of HTTPS protocol. You need to use HTTPS to be able to allow browser to send audio and video.

### Blank page for Single Meetings page

If you face blank page in this situation you should refer to [Template Overriding](https://zoom.codemanas.com/template_override/#content-not-showing "Template Overriding") and see Template override section.

This happens because of the single meeting page template from the plugin not being supported by your theme and i cannot make my plugin support for every theme page template because of which you'll need to override the plugin template from my plugin to your theme's standard. ( Basically, like how WooCommerce does!! )

### Countdown not showing/ guess is undefined error in my console log

If countdown is not working for you then the first thing you'll nweed to verify is whether your meeting got created successfully or not. You can do so by going to wp-admin > Zoom Meetings > Select your created meeting and on top right check if there are "Start Meeting", "join Meeting links". If there are those links then, you are good on meeting.

However, even though meeting is created and you are not seeing countdown timer then, you might want to check your browser console and see if there is any "guess is undefined" error. If so, there might be a plugin conflict using the same moment.js library.

### How to show Zoom Meetings on Front

* By using shortcode like [zoom_api_link meeting_id="123456789"] you can show the link of your meeting in front.

### Embed Zoom Meeting to Your Browser

See https://zoom.codemanas.com/shortcode/#6-embed-zoom-meeting-in-your-browser section.