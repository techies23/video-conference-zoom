This page shows you the few filters you can use in this plugin. I won't go in full detail here but yes there are few filters which you can extend this plugin into yours.

**How to use ?** 

`add_filter('vczapi_hook', function($data) {
    return $data;
});`

#### Rename "zoom-meetings" Slug

Add below to your functions.php file in your theme. Change "your-slug-name" to the slug you want. Then flush your permalink from **wp-adming > settings > permalink** and save.

`add_filter('vczapi_cpt_slug', function() {
    return 'your-slug-name';
});`

#### Before Create a Zoom User

* `apply_filters( 'vczapi_createAUser', $data );` 

**Usage:** Used when doing API call for creating a user on Zoom.

#### Before Listing a Zoom User
 
* `apply_filters( 'vczapi_listUsers', $data );` 

**Usage:** Used when doing API call for listing users from zoom.

#### Before getting a Zoom User

* `apply_filters( 'vczapi_getUserInfo', $data );` 

**Usage:** Used when doing API call for getting a specific HOST ID info.

#### Before listing a meeting

* `apply_filters( 'vczapi_listMeetings', $data );` 

**Usage:** Used when doing API call for getting list of meetings for a Zoom User.

#### Before Creating a meeting

* `apply_filters( 'vczapi_createAmeeting', $data );` 

**Usage:** Used when doing API call for posting your own data when creating a Meeting.

#### Before Updating a meeting

* `apply_filters( 'vczapi_updateMeetingInfo', $data );` 

**Usage:** Used when doing API call for posting your own data when updating a Meeting.

#### Before Getting a meeting

* `apply_filters( 'vczapi_getMeetingInfo', $data );` 

**Usage:** Used when doing API call for getting a meeting info.

#### Before getting daily reports data

* `apply_filters( 'vczapi_getDailyReport', $data );` 

**Usage:** Used when doing API call for when pulling in reports data.






