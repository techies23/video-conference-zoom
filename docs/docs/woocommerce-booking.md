**This Addon is for WooCommerce Booking Plugin** which acts as an automation process in creating zoom meetings which is available [here](https://www.codemanas.com/downloads/zoom-integration-for-woocommerce-booking/).

## Prerequisite

1. Free version of [Video Conferencing with Zoom API ](https://wordpress.org/plugins/video-conferencing-with-zoom-api/)
2. WooCommerce [Download Here](https://wordpress.org/plugins/woocommerce/)
3. WooCommerce Bookings [Download Here](https://woocommerce.com/products/woocommerce-bookings/)

## Introduction

Zoom Integration for WooCommerce Booking allows you to automate your zoom meetings directly from your WordPress dashboard by linking zoom meetings to your WooCommerce Booking products automatically when a Booking Product is created. Users will receive join links in their booking confirmation emails. Admin can directly start meetings from wp-admin > Bookings list page.

### Demo Video of Implementation

<iframe src="https://www.youtube.com/embed/lozhBjjIZTw" width="100%" height="300" allowfullscreen="allowfullscreen"></iframe>

Implementaion of the plugin video above.

## How to link meetings/webinar to booking product?

* Goto wp-admin > Products > Add New
* Add your Booking title and Description.
* Select product data as "Bookable Product" and under general section > Booking Duration should be either **hour(s) or minute(s).** like in the below screenshot.

**Note: You must either select hour(s) or minute(s) only otherwise the meeting/webinar won't be created.**

![Minutes and hours only](img/wc-bookings/minutes-hours.png)

* Select **Zoom** tab now - you should see a screen like shown in below screenshot. Check the "**Enable Zoom Meeting**" and select the type of your meeting you want it to be - for example: normal zoom meetings or webinars.

![Enable Zoom](img/wc-bookings/enable-zoom.png)

* Publish the post 

Now, your users should be able to book from the frontend.

## Features

Below are few features listed.

### Create Automated zoom meetings without configurations

With this plugin, your zoom meetings are automated when a booking product is booked. A zoom meeting is created once a booking is booked from a customer side. It happens automatically without any configurations.

<img src="https://www.codemanas.com/wp-content/uploads/edd/2020/02/zoom-wordpress.jpg" alt="Zoom and WooCommerce">

### Manually Select which host to assign for the Booking

When you create a booking product, you'll be able to select which host to assign that booking product so that the host can manually start the meeting when a booking is booked.

<img src="https://www.codemanas.com/wp-content/uploads/edd/2020/02/selection-of-host.jpg" alt="Zoom and WooCommerce">

### Join links and start links in email as well as your frontend my-account section.

After a booking is done. Users will be able to join their bookings from email link. Zoom meeting will be included in the booking email as well as inside the users my-account > bookings frontend table section.

<img src="https://www.codemanas.com/wp-content/uploads/edd/2020/02/My-account-WooCommerce.jpg" alt="Zoom and WooCommerce">

### Product Vendors Support 
Zoom for WooCommerce Bookings supports [Product Vendors](https://woocommerce.com/products/product-vendors/)  since version 2.1.7 .
Each vendor can be assigned a Zoom User / Host ID. When the vendor creates a Booking Product the host is automatically selected based on which host they have been assigned.

<b>Note: The host(s) need to be under the Zoom account that has been configured - this is a paid Zoom feature.</b> 
<b> Assign Zoom Host </b>
To assign Zoom Host to a Vendor. Goto Products > Zoom Vendors here you will be shown a screen of available vendors and ability to assign a host to them.
---
 
![Assign Vendor Zoom Host](img/product-vendors-bookings.png)

---
Once Zoom host is assigned to vendor. When the vendor in question when creates a Bookable Product - the selected Host will be used to create the Zoom Meeting. 
    

