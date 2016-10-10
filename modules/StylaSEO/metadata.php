<?php
$sMetadataVersion = '1.3.1';

$aModule = array(
    'id'          => 'StylaSEO',
    'title'       => 'Styla SEO Enhancements',
    'description' => 'Generating HTML & metadata for better SEO throughout magazine, tag, story pages from Amazine/Styla',
    'thumbnail'   => 'logo.png',
    'version'     => '1.3.1',
    'author'      => 'norisk GmbH',
    'url'         => 'http://www.noriskshop.de',
    'email'       => 'info@noriskshop.de',

    'extend'      => array(
        'oxconfig'      => 'StylaSEO/classes/StylaSEO_Config',
        'oxseodecoder'  => 'StylaSEO/classes/StylaSEO_Router'
    ),

    'files' => array(
        'StylaSEO_Util'   => 'StylaSEO/classes/StylaSEO_Util.php',
        'StylaSEO_Curl'   => 'StylaSEO/classes/StylaSEO_Curl.php',
        'StylaSEO_Config' => 'StylaSEO/classes/StylaSEO_Config.php',
        'StylaSEO_Setup'  => 'StylaSEO/controllers/admin/StylaSEO_Setup.php',
        'StylaSEO_Output' => 'StylaSEO/controllers/StylaSEO_Output.php',
    ),

    'blocks'    => array(),

    'templates' => array(
        'StylaSEO_Body.tpl'   => 'StylaSEO/views/tpl/StylaSEO_Body.tpl',
    ),

    'settings' => array(
        array('name' => 'styla_username', 'type' => 'str', 'value' => '', 'group' => 'styla_general', 'constraints' => '', 'position' => 1), //
        array('name' => 'styla_api_url', 'type' => 'str', 'value' => 'http://live.styla.com', 'group' => 'styla_general', 'constraints' => '', 'position' => 3), //
        array('name' => 'styla_js_url', 'type' => 'str', 'value' => 'http://cdn.styla.com', 'group' => 'styla_general', 'constraints' => '', 'position' => 3), //
        array('name' => 'styla_seo_server', 'type' => 'str', 'value' => 'http://seo.styla.com', 'group' => 'styla_general', 'constraints' => '', 'position' => 3), //
        array('name' => 'styla_seo_basedir', 'type' => 'str', 'value' => 'magazin', 'group' => 'styla_general', 'constraints' => '', 'position' => 4), //
        array('name' => 'styla_seo_cache_ttl', 'type' => 'str', 'value' => '3600', 'group' => 'styla_general', 'constraints' => '', 'position' => 5), //
        array('name' => 'styla_seo_magazin_title', 'type' => 'bool', 'value' => '0', 'group' => 'styla_general', 'constraints' => '', 'position' => 6), //
    ),

    'events' => array(
        'onActivate' => array('StylaSEO_Setup', 'install'),
        'onDeactivate' => array('StylaSEO_Setup', 'uninstall'),
        'onSaveConfVar' => array('StylaSEO_Setup', 'updateSeoUrls')
    )
);
