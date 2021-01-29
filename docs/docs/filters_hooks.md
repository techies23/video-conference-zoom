This page shows you the few filters you can use in this plugin. I won't go in full detail here but yes there are few filters which you can extend this plugin into yours.

**How to use ?** 

`add_filter('vczapi_hook', function($data) {
    return $data;
});`

### Redirecting to custom page url after Join via Web Browser completed or Unfinished

`add_filter('vczapi_api_redirect_join_browser', function() { 
    return 'https://yoursiteurl.com/page';
});`

### Rename "zoom-meetings" Slug

Add below to your functions.php file in your theme. Change "your-slug-name" to the slug you want. Then flush your permalink from **wp-adming > settings > permalink** and save.

<a href="https://gist.github.com/techies23/377f019396c3eaf0903f193a9c0b5251">View Implementation</a>

### Before Create a Zoom User

* `apply_filters( 'vczapi_createAUser', $data );` 

**Usage:** Used when doing API call for creating a user on Zoom.

### Before Listing a Zoom User
 
* `apply_filters( 'vczapi_listUsers', $data );` 

**Usage:** Used when doing API call for listing users from zoom.

### Before getting a Zoom User

* `apply_filters( 'vczapi_getUserInfo', $data );` 

**Usage:** Used when doing API call for getting a specific HOST ID info.

### Before listing a meeting

* `apply_filters( 'vczapi_listMeetings', $data );` 

**Usage:** Used when doing API call for getting list of meetings for a Zoom User.

### Before Creating a meeting

* `apply_filters( 'vczapi_createAmeeting', $data );` 

**Usage:** Used when doing API call for posting your own data when creating a Meeting.

### Before Updating a meeting

* `apply_filters( 'vczapi_updateMeetingInfo', $data );` 

**Usage:** Used when doing API call for posting your own data when updating a Meeting.

### Before Getting a meeting

* `apply_filters( 'vczapi_getMeetingInfo', $data );` 

**Usage:** Used when doing API call for getting a meeting info.

### Before getting daily reports data

* `apply_filters( 'vczapi_getDailyReport', $data );` 

**Usage:** Used when doing API call for when pulling in reports data.

### Enable Gutenberg Support for Zoom Meeting edit page on backend.

Add below code to your functions.php file

* `add_filter( 'vczapi_cpt_show_in_rest', '__return_true' );` 

### Join via browser show fields

If you want to show extra fields or hide them, add below code to your functions.php in your theme.

<pre>add_filter('vczapi_api_join_via_browser_params', 'vczapi_jvb_fields');
function vczapi_jvb_fields( $fields ) {
    $fields = [
        'meetingInfo'       => [
            'topic',
            'host',
            #'mn',
            #'pwd',
            #'telPwd',
            #'invite',
            #'participant',
            #'dc',
            #'enctype',
            #'report'
        ],
        'disableRecord'     => false,
        'disableJoinAudio'  => false,
        'isSupportChat'     => true, //Enable or disable chat
        'isSupportQA'       => true, //Enable or disable QA
        'isSupportBreakout' => true, //Enable or disable breakout rooms
        'isSupportCC'       => true, //Enable or disable CC
        'screenShare'       => true //Enable or disable Screenshare
    ];
    return $fields;
}</pre>
