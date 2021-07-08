<?php

return [

    /*
    |--------------------------------------------------------------------------
    | STUDY_COUNT_ARRAY
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'STUDY_COUNT_ARRAY' => array(1,2,5,10,25,50),
    'DEFAULT_ORTHANC_HOST' => 1,
    'DB_TYPE' => 'mysql',
    'DB_HOST' => 'mysql_db',
    'DB_NAME' => 'laravel',
    'DB_USER' => 'root',
    'DB_PASS' => 'root',
    'DB_PORT' => '3306',
    'DB_CHARSET' => 'utf8',
    'COOKIE_RUNTIME' => 604800,
    'COOKIE_PATH' => '/',
    'COOKIE_DOMAIN' => ".orthanc.test",
    'COOKIE_SECURE' => true,
    'COOKIE_HTTP' => true,
    'SESSION_RUNTIME' => 86400,
    'COOKIE_SAMESITE' => 'Lax',
    'PATH_PLUPLOAD_TEMP' => '/tmp',
    'DEFAULT_OLD_STUDIES' => 50,
    'API_Authorization' => 'Bearer AUTHORIZATION',
    'API_Token' => 'TOKEN',
    'PATH_DCMTK' => '/usr/bin/', // the path in the Docker php-fpm container, which has that installed.
    'PATH_DICOM_TMP_PARENT' => realpath(dirname(__FILE__).'/../') . '/TMPUPLOADS',
    'SEND_SMS_NOTIFICATIONS' => false,
    'REPORT_PDF' => true,
    'DEFAULT_FACILITY_ID'=> 1,
    'REPORTS_SITE_LOGO' => '/images/sitelogo.png'

];
