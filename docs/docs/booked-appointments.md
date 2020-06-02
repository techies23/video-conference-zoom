This guideline will teach you on how to integrate Zoom Meetings with <a href="https://codecanyon.net/item/booked-appointments-appointment-booking-for-wordpress/9466968">**Booked Appointment Plugin by BoxyStudio**</a>

**Please note:** <a href="http://codemanas.com/">CodeManas</a> is not affiliated with **Booked Appointment Plugin by BoxyStudio**

We have this guideline on implementing our <a href="https://www.codemanas.com/downloads/zoom-meetings-for-booked-appointments/">Zoom Meetings for Booked Appointments</a> with Booked - Appointment Booking for WordPress.

### Requirements

1. [Booked - Appointment Booking for WordPress](https://codecanyon.net/item/booked-appointments-appointment-booking-for-wordpress/9466968)
2. Booked Add-On: WooCommerce Payments
3. [Video Conferencing with Zoom plugin](https://wordpress.org/plugins/video-conferencing-with-zoom-api/)
4. [WooCommerce Plugin](https://wordpress.org/plugins/woocommerce/)
5. <a href="https://www.codemanas.com/downloads/zoom-meetings-for-booked-appointments/">Zoom Meetings for Booked Appointments</a>

### Should i follow this guide ?

1. This guide will teach on how to create WooCommerce product that is linked with Booked plugin.
2. Will guide you on how to create meetings for products.
3. Link meetings to your Booked appointment on your calendar.

Setting up **Booked** plugin and more options for **Booked** plugin should be followed from <a href="https://getbooked.io/">Booked</a> official site directly.

### Creating and Linking a Meeting to Product First

After you install all the requirements goto your WooCommerce Product page in your `wp-admin > Products > Add New`

* Now, fill in the details for your product such as price, variations or whatever you need by selecting as simple or variable product. 
* For now i have given the name of "`New Product Test Appointment`" to the product.
* After that, goto `Zoom` tab in product edit page.
* Check `Booked Appointment Service`
* Check `Enable Zoom Meting`.
* Choose which User/Host you want the meeting to be booked from users perspective. Meaning: Any users booking into this product will be hosted by the selected host from here.
* Now hit **Publish**.

<img src="https://www.codemanas.com/wp-content/uploads/edd/2020/05/booked-appotinment-product.png" alt="Zoom Connection with WordPress">

### Linking Zoom Meetings with Booked

Guessing that you have set your appointment times and setup everything related to **Booked** plugin. 

To link your meetings with Booked Plugin, follow below instructions:

* Goto wp-admin > Appointments > Settings page and select **Custom Fields**

<img src="https://www.codemanas.com/wp-content/uploads/2020/03/booked-custom-fields-setup.png" alt="Booked WooCOmmerce">

* Shown in above screenshot. Where it says **PRODUCT SELECTOR**
* Enter the name for your Booking
* Select the Product your created, for this demo its "**New Product Test Appointment**"
* Click **Save Custom Fields**

For this demo i have added **New Product Test Appointment** as product name but for yours will be different probably. Something like **Appointment for 1:00pm to 2:00pm /w Dr. Josh** etc. For each timeslots you'll need to create different product and meetings.

Thats it ! After you linked these fields. Your Zoom Meetings is linked with your Product.

### How will users receive join links ?

At the moment - If you have kept your bookings to be **Auto Approved** they will receive join links in email. Othewise, you'll have to use [booked-appointments] shortcode to show.

It would be like like so:

<img src="https://www.codemanas.com/wp-content/uploads/edd/2020/05/booked-appointment-shortcode.png" alt="Booked WooCOmmerce">

### Guidelines on Booked Support

<a href="https://boxystudio.ticksy.com/article/3820/">https://boxystudio.ticksy.com/article/3820/</a> - Linking WooCommerce Product to your Appointment and How WooCommerce works.

