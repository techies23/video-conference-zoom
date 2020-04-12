**Check out the <a href="https://youtu.be/5Z2Ii0PnHRQ?t=126" target="_blank">video documentation</a> if you want to see live demo.**

### Basic Information

Technically, every meeting created from the Video conferencing with zoom api plugin is live meetings but, i have divided the term into two different categories:

## Post Type Meetings

This is created when you create a new posts from `Zoom Meetings > Add New` page. These posts are related to your site and meetings created from here are binded to your site. 

***So, deleting any linked meeting without deleting the POST FIRST WOULD RESULT IN FAILURE TO JOIN MEETING FOR END USER !*** 

This allows you to view your meeting by styling your frontend pages by overriding templates. See more on `template override` section. 

However, it does create real meetings on zoom side as well.

## Actual Live Meetings

This is created from page `Zoom Meetings > Live meetings` section.

Meetings created from here are not binded to your site and have no relation to your site. If you want to show these meetings from your site then you'll have to rely on using shortcode using the meeting ID value here.
 