#Setup Video Conferencing with Zoom API

Here below are the steps to setup Zoom into WordPress.
## Setup Server-to-Server OAuth
###Generating API Credentials:

For this plugin you will be using Server-to-Server OAuth app type.

1. First go to [Create Page](https://marketplace.zoom.us/develop/create)
2. Find Server-to-Server OAuth and click create ![Create Server to Server Oauth](img/s2s-oauth/create-s2s-oauth.png)
3. Add your app name ![Give your app a name](img/s2s-oauth/app-name.png)
4. Once App is created - you will be taken to an App Overview page where you can see App Credentials ![App Credentials](img/s2s-oauth/app-credentials.png)
5. You will need to add Contact name, Contact email and Company name on the information page ![App Informaiton](img/s2s-oauth/s2s-info.png)
6. For the plugin to work - you will need to define the correct scopes for the App. Go to scopes and click "Add Scopes" and add Meeting, Webinar, Report, User, Recording, Report and select all options on each selected scopes ![Add Scopes](img/s2s-oauth/add-scopes.png)

###Adding Keys to the plugin
1. Go to Zoom Events > Settings > Connect tab ![Plugin Settings S2S-Oauth](img/s2s-oauth/plugin-settings-s2s-oauth.png)
2. Add the account credentials that can be viewed in the app ![App Credentials](img/s2s-oauth/app-credentials.png)

##Setup App SDK Credentials
App SDK are required for Join via Browser/Web SDK to work properly.

###Generating App SDK Credentials
1. Go to [Create Page](https://marketplace.zoom.us/develop/create)
2. Find 