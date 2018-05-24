<?php

$sMetadataVersion = '1.1';

$aModule = array(
    'id'          => 'Styla',
    'title'       => 'Styla',
    'description' => 'This module provides Styla magazine functionality to your OXID shop. It accepts all requests on the configured base directory and generates a dynamic response that includes the shop template containing the magazine JavaScript snippet (that can’t usually be crawled by search engines) and the crawlable HTML content including meta information. The module also provides an API with product data from OXID for you to use in Styla editor (backoffice) and callbacks for the users to add the products from the magazine to OXID cart.',
    'thumbnail'   => 'logo.png',
    'version'     => '1.7.0',
    'author'      => 'norisk GmbH',
    'url'         => 'http://www.noriskshop.de',
    'email'       => 'info@noriskshop.de',
    'extend'      => array(
        'oxconfig'      => 'Styla/core/Styla_Config',
        'module_config' => 'Styla/controllers/admin/Styla_Module_Config',
        'oxseodecoder'  => 'Styla/core/Styla_Router',
    ),
    'files'       => array(
        'Styla_Util'        => 'Styla/core/Styla_Util.php',
        'Styla_Setup'       => 'Styla/core/Styla_Setup.php',
        'Styla_Curl'        => 'Styla/core/Styla_Curl.php',
        'Styla_Search'      => 'Styla/models/Styla_Search.php',
        'Styla_Articlelist' => 'Styla/models/Styla_Articlelist.php',
        'Styla_Feed'        => 'Styla/controllers/Styla_Feed.php',
        'Styla_Magazine'    => 'Styla/controllers/Styla_Magazine.php',
    ),
    'templates'   => array(
        'Styla_JSON.tpl'     => 'Styla/views/tpl/Styla_JSON.tpl',
        'Styla_Magazine.tpl' => 'Styla/views/tpl/Styla_Magazine.tpl',
    ),
    'settings'    => array(
        array('name' => 'styla_username', 'type' => 'str', 'value' => '', 'group' => 'styla_general', 'constraints' => '', 'position' => 1), //
        array('name' => 'styla_api_url', 'type' => 'str', 'value' => 'http://live.styla.com', 'group' => 'styla_general', 'constraints' => '', 'position' => 3), //
        array('name' => 'styla_js_url', 'type' => 'str', 'value' => 'https://client-scripts.styla.com', 'group' => 'styla_general', 'constraints' => '', 'position' => 3), //
        array('name' => 'styla_seo_server', 'type' => 'str', 'value' => 'http://seoapi.styla.com', 'group' => 'styla_general', 'constraints' => '', 'position' => 3), //
        array('name' => 'styla_seo_basedir', 'type' => 'str', 'value' => 'magazin', 'group' => 'styla_general', 'constraints' => '', 'position' => 4), //
        array('name' => 'styla_seo_cache_ttl', 'type' => 'str', 'value' => '3600', 'group' => 'styla_general', 'constraints' => '', 'position' => 5), //
        array('name' => 'styla_seo_magazin_title', 'type' => 'bool', 'value' => '0', 'group' => 'styla_general', 'constraints' => '', 'position' => 6), //

        array('name' => 'styla_api_key', 'type' => 'str', 'value' => '', 'group' => 'styla_feed', 'constraints' => '', 'position' => 1), // 6321424181
        array('name' => 'styla_feed_basedir', 'type' => 'str', 'value' => 'stylafeed', 'group' => 'styla_feed', 'constraints' => '', 'position' => 2),
        array('name' => 'styla_page_size', 'type' => 'str', 'value' => '10', 'group' => 'styla_feed', 'constraints' => '', 'position' => 3),
        array('name' => 'styla_image_attribute', 'type' => 'str', 'value' => 'image', 'group' => 'styla_feed', 'constraints' => '', 'position' => 4),
        array('name' => 'styla_image_width', 'type' => 'str', 'value' => '320', 'group' => 'styla_feed', 'constraints' => '', 'position' => 5),
        array('name' => 'styla_image_height', 'type' => 'str', 'value' => '320', 'group' => 'styla_feed', 'constraints' => '', 'position' => 6),
        array('name' => 'styla_extra_attributes', 'type' => 'str', 'value' => '', 'group' => 'styla_feed', 'constraints' => '', 'position' => 7),
        array('name' => 'styla_feed_cache_ttl', 'type' => 'str', 'value' => '3600', 'group' => 'styla_feed', 'constraints' => '', 'position' => 8),
        array('name' => 'styla_feed_search_cols', 'type' => 'arr', 'value' => array('oxtitle', 'oxshortdesc', 'oxid', 'oxartnum', 'oxean'), 'group' => 'styla_feed', 'constraints' => '', 'position' => 9),
        array('name' => 'styla_feed_search_subcategories', 'type' => 'bool', 'value' => '0', 'group' => 'styla_feed', 'constraints' => '', 'position' => 10), //
        array('name' => 'styla_feed_sorting', 'type' => 'arr', 'value' => '', 'group' => 'styla_feed', 'constraints' => '', 'position' => 11),
        array('name' => 'styla_feed_brand', 'type' => 'select', 'value' => 'none', 'group' => 'styla_feed', 'constraints' => 'none|oxmanufacturer|oxvendor', 'position' => 12),
        array('name' => 'styla_feed_vat_showlabel', 'type' => 'bool', 'value' => '1', 'group' => 'styla_feed', 'constraints' => '', 'position' => 13),
        array('name' => 'styla_feed_show_variant_urls', 'type' => 'bool', 'value' => '0', 'group' => 'styla_feed', 'constraints' => '', 'position' => 14),

    ),
    'events'      => array(
        'onActivate'    => array('Styla_Setup', 'install'),
        'onDeactivate'  => array('Styla_Setup', 'uninstall'),
        'onSaveConfVar' => array('Styla_Setup', 'updateSeoUrls')
    ),
    'blocks'      => array(),
);
