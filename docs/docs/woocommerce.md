**This Addon is for WooCommerce Plugin** which acts as an automation process in creating zoom meetings which is available [here](https://www.codemanas.com/downloads/zoom-meetings-for-woocommerce/).

## Purpose

1. Make your meetings purchasable.
2. Allow users to join and receive meeting links via emails.
3. Automate the meeting creation process.

## Whats required ?

1. Free version of [Video Conferencing with Zoom API ](https://wordpress.org/plugins/video-conferencing-with-zoom-api/)
2. WooCommerce [Download Here](https://wordpress.org/plugins/woocommerce/)
3. [Zoom integration for WooCommerce](https://www.codemanas.com/downloads/zoom-meetings-for-woocommerce/)

## Demo

<iframe src="https://www.youtube.com/embed/V6SfMFatOH8" width="100%" height="400" allowfullscreen="allowfullscreen"></iframe>

## Instructions

There are 2 ways you can create a Zoom Product

1. Product Linked with Zoom Meeting
2. Zoom Meeting linked with Product	( Zoom Product )

### 1. Product Linked with Zoom Meeting

In this method - you can create a normal WooCommerce Product and simply connect it to a Zoom Meeting.

Let's take an example of a Simple Product Type

Create a product as you normally would then you will see the Zoom Connection tab on the product tabs

<img src="https://www.codemanas.com/wp-content/uploads/2020/03/Screen-Shot-2020-03-17-at-22.45.44.png">

After clicking the Zoom Meeting tab, you can see the Enable Zoom Connection Checkbox.
Checking this box will allow to link this product to a Zoom Meeting. Please note the meeting needs to be created first via `Zoom Meeting > All Meetings`.

After ticking Enable Zoom Connection - you will see further options:

<img src="https://www.codemanas.com/wp-content/uploads/2020/03/Screen-Shot-2020-03-17-at-22.50.20.png">

You can link to Zoom Meeting by searching - after a meeting has been selected it's meeting details will be shown.

<img src="https://www.codemanas.com/wp-content/uploads/2020/03/select-meeting.gif">

Now when the product is purchased - the customer will get the option to join the meeting. They will see this in:

1. Their Purchase Order Receipt E-mail
2. If registered or logged in when purchase made in my account page.

<video width="640" heigh="400" controls>
    <source src="https://www.codemanas.com/wp-content/uploads/2020/03/zoom-meeting-purchase.mov" />
</video><br>

### 2. Zoom Meeting linked with Product ( Zoom Product )

This method should be used to link Zoom Meeting with a Product. The product created using this method will not be shown on shop and search pages. The meeting created using this method is designed to be directly purchased via the zoom meeting single page. See image below.

#### How to create a Zoom Meeting

* Go to WordPress admin menu "Zoom Meetings"

<img src="https://www.codemanas.com/wp-content/uploads/2020/03/Screen-Shot-2020-03-18-at-11.14.50.png"><br/><br/>

* Add a New Zoom Meeting - fill out all the details like you would for the Zoom Meeting. After filling everything out there is a sidebar area that says WooCommerce Integration. Check the box that says enable purchase and enter the price that't it - the Zoom Product will then be purchasable via the Zoom Meeting Page. Please see video below.<br/><br/>
<video controls width="640" controls>
<source src="https://www.codemanas.com/wp-content/uploads/2020/03/zoom-product.mp4" />
</video>

### Shortcode

`[vczapi_wc_show_purchasable_meetings per_page="10" type="table" order="DESC" upcoming_only="no"]`

#### Usage:

This allows you to show your purchasable meeting lists. Show in DESC or ASC format as well as show upcoming only or not.

#### Parameters:

* `per_page:` Allows you to define how many list to show per page.
* `type`: Allows you to show the list in table format or boxed format. Change to `type="boxed"` to show in boxed format
* `order`: Show list in `ASC` or `DESC` format
* `upcoming_only`: Show only upcoming meetings based on start time or show all list. Change to "yes" for upcoming only

### Reminder E-mails ###

Meeting email reminders can be configured from Zoom > WooCommerce > Email tab. You can either choose to send emails 24 hours before the meeting or 3 hours before.<br />
#### Meeting Reminder Worklow: ####
* Meeting reminder email uses your WordPress configuration to send e-mails. Please make sure the mail sent out by your site is not going into spam.<br />
* Meeting reminder are dependent on cron job - the cron event name is "vczapi_meeting_events_cron" and it is run hourly.
So please make sure cron jobs are running properly on your system. It is recommended that you switch to a true cron job https://www.siteground.com/tutorials/wordpress/real-cron-job/ to make sure your e-mails are being sent properly.<br />
* User will not be sent an e-mail if they have purchased the meeting after the initial notification time has passed.
   So example if meeting time is less than 24 hours away - and new user has purchased the meeting the will not get 24 hour neeting alert. They will only get (if configured) - 3 hours away reminder email.
 
 
### Deep dive video for Reminder E-mails ###
 <iframe width="560" height="315" src="https://www.youtube.com/embed/Qb8OoT1eb2U" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> 
 
