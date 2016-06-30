<?php

class StylaFeed_Setup{

    const STYLA_BASEDIR = 'stylafeed/';

    private static $_urls = array(
        array('orig_url' => 'index.php?cl=StylaFeed_Output&fnc=showAll', 'seo_action' => 'index/'),
        array('orig_url' => 'index.php?cl=StylaFeed_Output&fnc=showCategories', 'seo_action' => 'index/category/'),
        array('orig_url' => 'index.php?cl=StylaFeed_Output&fnc=showProduct', 'seo_action' => 'index/product/'),
    );

    public static function updateSeoUrls(){

        $shop_id = oxRegistry::getConfig()->getShopId();
        self::cleanup(); // prevent duplicate entries

        $aLanguages = oxRegistry::getLang()->getLanguageArray();
        $defaultLang = oxRegistry::getConfig()->getConfigParam('sDefaultLang');
        $basedir = oxRegistry::getConfig()->getConfigParam('styla_feed_basedir');
        if($basedir == '')
            $basedir = self::STYLA_BASEDIR;

        $basedir = rtrim($basedir, '/').'/';

        foreach(self::$_urls as $item){
            foreach($aLanguages as $lang){
                $oxId = md5(uniqid());
                if($lang->id == $defaultLang)
                    $lang_prefix = '';
                else
                    $lang_prefix = $lang->abbr.'/';

                $url = $lang_prefix.$basedir.$item['seo_action'];
                $sQuery   = "INSERT INTO `oxseo` (`OXOBJECTID`, `OXIDENT`, `OXSHOPID`, `OXLANG`, `OXSTDURL`, `OXSEOURL`, `OXTYPE`, `OXFIXED`, `OXEXPIRED`, `OXPARAMS`) VALUES
                  ('".$oxId."', '".md5(strtolower($url))."', '".$shop_id."', $lang->id, '".$item['orig_url']."', '".$url."', 'static', 0, 0, '');";
                oxDb::getDb()->Execute($sQuery);

            }
        }
    }

    public static function cleanup(){

        $oDb = oxDb::getDb();
        $sShopId = oxRegistry::getConfig()->getShopId();
        $sQuery   = "DELETE FROM `oxseo` WHERE `OXSTDURL` LIKE '%StylaFeed_Output%' and oxshopid = ".$oDb->quote($sShopId)." ;";
        $oDb->Execute($sQuery);

        // Legacy
        $sQuery   = "DELETE FROM `oxseo` WHERE `OXSTDURL` LIKE '%Amazinefeed_Output%' and oxshopid = ".$oDb->quote($sShopId)." ;";
        $oDb->Execute($sQuery);
    }

    static function install(){
        self::updateSeoUrls();
    }

    static function uninstall(){
        self::cleanup();
    }
}



