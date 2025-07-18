<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit29e970424d857ac5707b04aab1a5ae60
{
    public static $prefixLengthsPsr4 = array (
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
        'C' => 
        array (
            'Codemanas\\VczApi\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
        'Codemanas\\VczApi\\' => 
        array (
            0 => __DIR__ . '/../..' . '/legacy',
            1 => __DIR__ . '/../..' . '/includes',
        ),
    );

    public static $classMap = array (
        'CodeManas\\VczApi\\Elementor\\Elementor' => __DIR__ . '/../..' . '/includes/Elementor/Elementor.php',
        'CodeManas\\VczApi\\Elementor\\Widgets\\EmbedMeetings' => __DIR__ . '/../..' . '/includes/Elementor/Widgets/EmbedMeetings.php',
        'CodeManas\\VczApi\\Elementor\\Widgets\\MeetingByID' => __DIR__ . '/../..' . '/includes/Elementor/Widgets/MeetingByID.php',
        'CodeManas\\VczApi\\Elementor\\Widgets\\MeetingByPostID' => __DIR__ . '/../..' . '/includes/Elementor/Widgets/MeetingByPostID.php',
        'CodeManas\\VczApi\\Elementor\\Widgets\\MeetingHosts' => __DIR__ . '/../..' . '/includes/Elementor/Widgets/MeetingHosts.php',
        'CodeManas\\VczApi\\Elementor\\Widgets\\MeetingList' => __DIR__ . '/../..' . '/includes/Elementor/Widgets/MeetingList.php',
        'CodeManas\\VczApi\\Elementor\\Widgets\\RecordingByMeetingID' => __DIR__ . '/../..' . '/includes/Elementor/Widgets/RecordingByMeetingID.php',
        'CodeManas\\VczApi\\Elementor\\Widgets\\RecordingsByHost' => __DIR__ . '/../..' . '/includes/Elementor/Widgets/RecordingsByHost.php',
        'CodeManas\\VczApi\\Elementor\\Widgets\\WebinarList' => __DIR__ . '/../..' . '/includes/Elementor/Widgets/WebinarList.php',
        'Codemanas\\VczApi\\Blocks\\BlockTemplates' => __DIR__ . '/../..' . '/includes/Blocks/BlockTemplates.php',
        'Codemanas\\VczApi\\Blocks\\Blocks' => __DIR__ . '/../..' . '/includes/Blocks/Blocks.php',
        'Codemanas\\VczApi\\Bootstrap' => __DIR__ . '/../..' . '/includes/Bootstrap.php',
        'Codemanas\\VczApi\\Data\\Datastore' => __DIR__ . '/../..' . '/includes/Data/Datastore.php',
        'Codemanas\\VczApi\\Data\\Logger' => __DIR__ . '/../..' . '/includes/Data/Logger.php',
        'Codemanas\\VczApi\\Data\\Metastore' => __DIR__ . '/../..' . '/includes/Data/Metastore.php',
        'Codemanas\\VczApi\\Filters' => __DIR__ . '/../..' . '/includes/Filters.php',
        'Codemanas\\VczApi\\Helpers\\Date' => __DIR__ . '/../..' . '/includes/Helpers/Date.php',
        'Codemanas\\VczApi\\Helpers\\Encryption' => __DIR__ . '/../..' . '/includes/Helpers/Encryption.php',
        'Codemanas\\VczApi\\Helpers\\Links' => __DIR__ . '/../..' . '/includes/Helpers/Links.php',
        'Codemanas\\VczApi\\Helpers\\Locales' => __DIR__ . '/../..' . '/includes/Helpers/Locales.php',
        'Codemanas\\VczApi\\Helpers\\MeetingType' => __DIR__ . '/../..' . '/includes/Helpers/MeetingType.php',
        'Codemanas\\VczApi\\Helpers\\Templates' => __DIR__ . '/../..' . '/includes/Helpers/Templates.php',
        'Codemanas\\VczApi\\Marketplace' => __DIR__ . '/../..' . '/includes/Marketplace.php',
        'Codemanas\\VczApi\\Requests\\Zoom' => __DIR__ . '/../..' . '/includes/Requests/Zoom.php',
        'Codemanas\\VczApi\\Shortcodes' => __DIR__ . '/../..' . '/includes/Shortcodes.php',
        'Codemanas\\VczApi\\Shortcodes\\Embed' => __DIR__ . '/../..' . '/includes/Shortcodes/Embed.php',
        'Codemanas\\VczApi\\Shortcodes\\Helpers' => __DIR__ . '/../..' . '/includes/Shortcodes/Helpers.php',
        'Codemanas\\VczApi\\Shortcodes\\Meetings' => __DIR__ . '/../..' . '/includes/Shortcodes/Meetings.php',
        'Codemanas\\VczApi\\Shortcodes\\Recordings' => __DIR__ . '/../..' . '/includes/Shortcodes/Recordings.php',
        'Codemanas\\VczApi\\Shortcodes\\Webinars' => __DIR__ . '/../..' . '/includes/Shortcodes/Webinars.php',
        'Codemanas\\VczApi\\Timezone' => __DIR__ . '/../..' . '/includes/Timezone.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Firebase\\JWT\\BeforeValidException' => __DIR__ . '/..' . '/firebase/php-jwt/src/BeforeValidException.php',
        'Firebase\\JWT\\CachedKeySet' => __DIR__ . '/..' . '/firebase/php-jwt/src/CachedKeySet.php',
        'Firebase\\JWT\\ExpiredException' => __DIR__ . '/..' . '/firebase/php-jwt/src/ExpiredException.php',
        'Firebase\\JWT\\JWK' => __DIR__ . '/..' . '/firebase/php-jwt/src/JWK.php',
        'Firebase\\JWT\\JWT' => __DIR__ . '/..' . '/firebase/php-jwt/src/JWT.php',
        'Firebase\\JWT\\JWTExceptionWithPayloadInterface' => __DIR__ . '/..' . '/firebase/php-jwt/src/JWTExceptionWithPayloadInterface.php',
        'Firebase\\JWT\\Key' => __DIR__ . '/..' . '/firebase/php-jwt/src/Key.php',
        'Firebase\\JWT\\SignatureInvalidException' => __DIR__ . '/..' . '/firebase/php-jwt/src/SignatureInvalidException.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit29e970424d857ac5707b04aab1a5ae60::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit29e970424d857ac5707b04aab1a5ae60::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit29e970424d857ac5707b04aab1a5ae60::$classMap;

        }, null, ClassLoader::class);
    }
}
