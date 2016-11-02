<?php

$sMetadataVersion = '1.1';

$aModule = array(
    'id'          => 'StylaFeed',
    'title'       => 'Styla Feed',
    'description' => 'Styla Feed Module provides a product api to add products to the stories.',
    'thumbnail'   => 'logo.png',
    'version'     => '1.4.0',
    'author'      => 'norisk GmbH',
    'url'         => 'http://www.noriskshop.de',
    'email'       => 'info@noriskshop.de',

    'extend'      => array(
        'oxconfig'      => 'StylaFeed/classes/StylaFeed_Config',
        'module_config' => 'StylaFeed/classes/StylaFeed_Module_Config',
    ),

    'files' => array(
        'StylaFeed_Util'          => 'StylaFeed/classes/StylaFeed_Util.php',
        'StylaFeed_Setup'         => 'StylaFeed/controllers/admin/StylaFeed_Setup.php',
        'StylaFeed_Output'        => 'StylaFeed/controllers/StylaFeed_Output.php',
        'StylaFeed_Search'        => 'StylaFeed/classes/StylaFeed_Search.php',
        'StylaFeed_Articlelist'   => 'StylaFeed/models/StylaFeed_Articlelist.php',
    ),

    'blocks'    => array(
    ),

    'templates' => array(
        'StylaFeed_JSON.tpl'      => 'StylaFeed/views/tpl/StylaFeed_JSON.tpl',
    ),

    'settings' => array(
        array('name' => 'styla_api_key', 'type' => 'str', 'value' => '', 'group' => 'styla_api', 'constraints' => '', 'position' => 1), // 6321424181
        array('name' => 'styla_feed_basedir', 'type' => 'str', 'value' => 'stylafeed', 'group' => 'styla_feed', 'constraints' => '', 'position' => 2),
        array('name' => 'styla_page_size', 'type' => 'str', 'value' => '10', 'group' => 'styla_feed', 'constraints' => '', 'position' => 3), //
        array('name' => 'styla_image_attribute', 'type' => 'str', 'value' => 'image', 'group' => 'styla_feed', 'constraints' => '', 'position' => 4), //
        array('name' => 'styla_image_width', 'type' => 'str', 'value' => '320', 'group' => 'styla_feed', 'constraints' => '', 'position' => 5), //
        array('name' => 'styla_image_height', 'type' => 'str', 'value' => '320', 'group' => 'styla_feed', 'constraints' => '', 'position' => 6), //
        array('name' => 'styla_extra_attributes', 'type' => 'str', 'value' => '', 'group' => 'styla_feed', 'constraints' => '', 'position' => 7), //
        array('name' => 'styla_feed_cache_ttl', 'type' => 'str', 'value' => '3600', 'group' => 'styla_feed', 'constraints' => '', 'position' => 8), //
    ),

    'events' => array(
        'onActivate' => array('StylaFeed_Setup', 'install'),
        'onDeactivate' => array('StylaFeed_Setup', 'uninstall'),
        'onSaveConfVar' => array('StylaFeed_Setup', 'updateSeoUrls')
    )
);
