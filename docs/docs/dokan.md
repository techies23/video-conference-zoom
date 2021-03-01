**This Addon is for Dokan** you can get it [here](https://wordpress.org/plugins/dokan-lite/).

## Prerequisite

1. Free version of [Video Conferencing with Zoom API ](https://wordpress.org/plugins/video-conferencing-with-zoom-api/)
2. WooCommerce [Download Here](https://wordpress.org/plugins/woocommerce/)
3. [Dokan](https://wordpress.org/plugins/dokan-lite/)
3. [Zoom integration for WooCommerce](https://www.codemanas.com/downloads/zoom-meetings-for-woocommerce/) or [Zoom Integration for WooCommerce Bookings](https://www.codemanas.com/downloads/zoom-integration-for-woocommerce-booking/)
4. [Dokan Integration for Zoom](https://www.codemanas.com/downloads/dokan-integration-for-zoom/)

## Introduction

Dokan Integration for Zoom acts as a glue plugin for Zoom integration for WooCommerce or Zoom Integration for WooCommerce Bookings and Dokan. This addon allows vendors to create and manage Zoom Meeting Products from the front end.

## Instructions

After successfully installing all the required plugins. If you log in as a vendor - you will be first shown a Zoom Meeting section on the Dokan dashboard.

![Zoom_Dokan_dashboard](img/dokan/frontend-dashboard.png "Zoom Dokan Dashboard")

1. Zoom Menu dashboard link
2. From this menu item vendors can create and mange Zoom Meetings. Vendors will only be able to see their own meetings. The vendors will only be able to see and attach meetings they create via their own dashboard. i.e they will not be able to see other users Meetings.
3. After meeting is created, the created meeting can be then attached to a product. Below screenshot show how the Zoom option will be shown on the frontend product edit screen.

![Dokan_Product](img/dokan/connect-meeting.png "Dokan connect Product to meeting")

---

## Assign Host to Vendor

At the moment it is not possible for vendors to add their own Zoom Account - they have to be added under the admins Zoom Account. The admin of the site or the account used to connect to Zoom on the site should be a paid account. Only then will the site admin be able to add users under their Zoom account. This becomes useful when you want your vendors to be able to use different zoom accounts.

By default, Vendors will be able to select all the hosts under a Zoom Account. If you want the vendors to be only be able to create meetings with a particular Host then you will need to go to Zoom Meetings > Dokan Vendors
![Assign_Vendors](img/dokan/assign-vendors.png"Assing Vendors")

---

## WooCommerce Booking Integration

### Prerequisites
These plugins are required before Zoom can be enable with Dokan + WooCommerce Bookings.

- [WooCommerce Bookings](https://woocommerce.com/products/woocommerce-bookings/)
- Either Dokan Pro Module with WC Booking Integration enabled [Dokan Business](https://wedevs.com/dokan/pricing]) or [WooCommerce Booking Integration Module](https://wedevs.com/dokan/extensions/woocommerce-booking-integration/)
- [Zoom Integration for WooCommerce Bookings](https://www.codemanas.com/downloads/zoom-integration-for-woocommerce-booking/) 

#### Basic Settings
After all of the required plugins are installed. Vendors will now see a new field when creating their Bookable Products.
![Enable Zoom Meeting for Bookable Product](img/dokan/enable-zoom-meetings.png)
Enabling Zoom Meeting here for a product will create a Zoom Meeting when the bookable product is booked. For more information of how it works please check out  [Zoom Integration for WooCommerce Bookings Documentation](woocoommerce-bookings.md).