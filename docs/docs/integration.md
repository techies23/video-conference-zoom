Here below are the steps to integrate zoom plugin into WordPress. There are no complicated processes. Just the basic steps.

**Check out the <a href="https://youtu.be/5Z2Ii0PnHRQ?t=41" target="_blank">video documentation</a> if you want to see live demo.**

<p style="color:red;"><strong>NOTE: if you recently getting an error called "API_CREDENTIALS_NOT_FOUDN" this is an error from zoom side. Please contact zoom support in this case.</strong></p>

## Generating API keys and Secret Keys

For this plugin you’ll be using JWT token method to make the API connection. Please note, this method is only account level connection.

## First Step ( Choost App )

1. First goto [Create Page](https://marketplace.zoom.us/develop/create)

2. Click on Develop on top of the page and build app page if you directly did not go into [Create Page](https://marketplace.zoom.us/develop/create)

3. Click JWT and "Create"

<img src="https://deepenbajracharya.com.np/wp-content/uploads/2019/05/App-Marketplace-1024x520.png">

### Second Step ( Get keys )

1. On the next screen you should see Information, App Credentials, Feature and Activation menus

2. Fill in your basic information about the App.

3. On the Credentials Page. Copy both `API key and API Secret` after you have filled all the details in information page.

<img src="https://deepenbajracharya.com.np/wp-content/uploads/2017/01/Credentials-1024x698.png">

### Copying into the plugin

Now, copy these credentials and go into WordPress Zoom Meetings settings page.

<img src="https://deepenbajracharya.com.np/wp-content/uploads/2019/05/Settings-%E2%80%B9-Plugin-Tester-%E2%80%94-WordPress-1024x520.png">

**Click on `Check API connection` button to check if your API connection is good !**

