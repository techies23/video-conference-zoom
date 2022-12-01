#Setup Video Conferencing with Zoom API

Here below are the steps to setup Zoom into WordPress.

From version 4.0.0 of Video Conferencing with Zoom API - the plugin has moved to Server-to-Server OAuth from JWT, this is because Zoom is deprecating JWT App type from June 2023,
For more info see [JWT App Type Depecation FAQ](https://marketplace.zoom.us/docs/guides/build/jwt-app/jwt-faq/), please see [migration guide](migration.md) to go to new Server-to-Server OAuth / App SDK

## Setup Server-to-Server OAuth
###Generating API Credentials:

For this plugin you will be using Server-to-Server OAuth app type.

1. First go to [Create Page](https://marketplace.zoom.us/develop/create)
2. Find Server-to-Server OAuth and click create ![Create Server to Server Oauth](img/s2s-oauth/create-s2s-oauth.png)
3. Add your app name ![Give your app a name](img/s2s-oauth/app-name.png)
4. Once App is created - you will be taken to an App Overview page where you can see App Credentials ![App Credentials](img/s2s-oauth/app-credentials.png)
5. You will need to add Contact name, Contact email and Company name on the information page ![App Informaiton](img/s2s-oauth/s2s-info.png)
6. For the plugin to work - you will need to define the correct scopes for the App. Go to scopes and click "Add Scopes" and add Meeting, Webinar, Report, User, Recording, Report and select all options on each selected scopes ![Add Scopes](img/s2s-oauth/add-scopes.png)

###Below are permissions that are needed/required:

**Note: Not adding any of the below permissions may result in permission issue error notices when trying to create your meeting/webinars.**

####Meetings
* View and manage sub account’s user meetings/meeting:master
* View all user meetings/meeting:read:admin
* View and manage all user meetings/meeting:write:admin

####Recordings
* View and manage sub account’s user recordings/recording:master
* View all user recordings/recording:read:admin
* View and manage all user recordings/recording:write:admin

####Reports
* View sub account’s report data/report:master
* View report data/report:read:admin
* View your chat history report/report_chat:read:admin

####Users
* View and manage sub account’s user information/user:master
* View all user information/user:read:admin
* View users information and manage users/user:write:admin

####Webinars
* View and manage sub account’s user webinars/webinar:master
* View all user Webinars/webinar:read:admin
* View and manage all user Webinars/webinar:write:admin

###Adding Server-to-Server OAuth Credentials to the plugin
1. Go to Zoom Events > Settings > Connect tab ![Plugin Settings S2S-Oauth](img/s2s-oauth/plugin-settings-s2s-oauth.png)
2. Add the Server-to-Server credentials ( Account ID, Client ID, Client Secret ) that can be viewed in the app under credentials (see step 4 above) ![App Credentials](img/s2s-oauth/app-credentials.png)

##Setup App SDK Credentials
App SDK are required for Join via Browser/Web SDK to work properly.

###Generating App SDK Credentials
1. Go to [Create Page](https://marketplace.zoom.us/develop/create)
2. Find SDK and click create ![Create SDK App](img/s2s-oauth/create-sdk-app.png)
3. The next screen will prompt you for an App Name & ask you if you want to publish this app on Zoom App Marketplace. Add a name and toggle publish off ![Name SDK App](img/s2s-oauth/sdk-app-name.png)
4. Once App is created - you will need to add Company name, Name and Contact email ![SDK Information](img/s2s-oauth/sdk-info.png)
5. Once the information has been added go to the App Credentials and you can view the SDK Credentials there ![SDK App Credentials](img/s2s-oauth/sdk-app-credentials.png)
6. Ensure that your SDK app is activated in order to use the Join via browser features otherwise, you might get a **signature invalid error message.**

###Adding SDK Credentials to the plugin
1. Go to Zoom Events > Settings > Connect tab ![Plugin Settings S2S-Oauth](img/s2s-oauth/sdk-credentials-plugin-creds.png)
2. Add SDK credentials ( SDK Key and SDK Secret ) ![SDK Plugin Setup](img/s2s-oauth/sdk-app-credentials.png)
3. To test out join via browser - you will need to create a meeting and test if join via browser is working properly
