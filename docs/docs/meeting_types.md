**Check out the <a href="https://youtu.be/5Z2Ii0PnHRQ?t=126" target="_blank">video documentation</a> if you want to see live demo.**

### Basic Information

Technically, every meeting created from the Video conferencing with zoom api plugin is live meetings meaning that it will be created on Zoom but, i have divided the term into two different categories:

## Post Type Meetings

This is created when you create a new post from **`Zoom Meetings > Add New`** page. These posts are related to your site and meetings created from here are binded to your site. 

***So, deleting any linked meeting without deleting the POST FIRST WOULD RESULT IN FAILURE TO JOIN MEETING FOR END USER !*** 

This allows you to view your meeting by styling your frontend pages by overriding templates. See more on `template override` section. 

Basically, this will create a Meeting on your Zoom Account and a Post in your WordPress site which is linked. So, if you create a Meeting from here you'll be able to show a countdown timer in the frontend.

<img src="https://www.codemanas.com/wp-content/uploads/2020/04/post-type-meetings.png" alt="Post Type Meeting">

## Actual Live Meetings

This is created from page `Zoom Meetings > Live meetings` section.

Meetings created from here are not binded to your site. This will create a meeting in your Zoom Account and will not create or add any additional data on your WordPress website. **Everything will be fetched directly using the Zoom API.**

If you want to show these meetings from your site then you'll have to rely on using shortcode using the meeting ID value here.

See shortcode section on how to use Shortcode.

<img src="https://www.codemanas.com/wp-content/uploads/2020/04/live-meetings.png" alt="Post Type Meeting">
 