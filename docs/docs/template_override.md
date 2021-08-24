Overriding the template means to change the layout of how the content is displayed in frontend.

### Overriding the Parent

* Here, parent is the `Video conferencing with zoom api` main plugin templates folder.
* All frontend templates are stored inside `public_html/wp-content/plugins/video-conferencing-with-zoom-api/templates/` folder.

To override, copy files from `public_html/wp-content/plugins/video-conferencing-with-zoom-api/templates/` folder to `public_html/wp-content/yourtheme/video-conferencing-zoom/` folder.

**Check out the <a href="https://youtu.be/5Z2Ii0PnHRQ?t=730" target="_blank">video documentation</a> if you want to see basic live demo on Template Overrides.**

### Content not showing?

If you are facing an issue with content not being shown on your single meetings page i.e **yourwesite/zoom-meetings/your-meetings** page then follow below steps:

1. Find out what your theme div structure looks like first.
2. Goto **wp-content/plugins/video-conferencing-with-zoom-api/templates/**
3. Copy **content-single-meeting.php**
4. Make folder **"video-conferencing-zoom"** in your theme if you dont have that folder.
5. Paste it into **yourchildtheme/video-conferencing-zoom/content-single-meeting.php**
6. Now edit the file **content-single-meeting.php**

Like said above, you'll need to find out the div structure from your theme. You can do so by going to your yoursite.com/zoom-meetings/yourmeetings-page > Right Click > Click on inspect. **Check what your theme is outputting in other pages of your site**

You Should see below image on your screen now.

![Zoom WooCommerce Appointments](img/plugin-template-override-guideline.png)

Match the classes accordingly in the file **content-single-meeting.php** now. Replacing the `"dpn-zvc-single-content-wrapper dpn-zvc-single-content-wrapper-<?php echo get_the_id(); ?>"` usually does the trick !!

![Zoom WooCommerce Appointments](img/plugin-overrride-guide.png)

Hope this helps !

You're Done !

You can change the layout of the pages anyway you want.




