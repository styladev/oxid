<?php

class StylaSEO_Setup{

    const STYLA_BASEDIR = 'magazin';

    private static $_urls = array(
        array('orig_url' => 'index.php?cl=StylaSEO_Output', 'seo_action' => ''),
        array('orig_url' => 'index.php?cl=StylaSEO_Output', 'seo_action' => 'tag/'),
        array('orig_url' => 'index.php?cl=StylaSEO_Output', 'seo_action' => 'story/'),
        array('orig_url' => 'index.php?cl=StylaSEO_Output', 'seo_action' => 'user/'),
        array('orig_url' => 'index.php?cl=StylaSEO_Output', 'seo_action' => 'category/'),
        array('orig_url' => 'index.php?cl=StylaSEO_Output&fnc=getPluginVersion', 'seo_action' => 'version/')
    );


    public static function updateSeoUrls(){
        $shop_id = oxRegistry::getConfig()->getShopId();
        self::cleanup(); // prevent duplicate entries

        $aLanguages = oxRegistry::getLang()->getLanguageArray();
        $defaultLang = oxRegistry::getConfig()->getConfigParam('sDefaultLang');
        $basedir = oxRegistry::getConfig()->getConfigParam('styla_seo_basedir');
        if($basedir == '')
            $basedir = self::STYLA_BASEDIR;

        $basedir = rtrim($basedir, '/').'/';

        foreach(self::$_urls as $item){
            foreach($aLanguages as $lang){
                $oxId = md5(uniqid());
                if($lang->id == $defaultLang)
                    $lang_prefix = '';
                else
                    $lang_prefix = $lang->abbr . '/';

                $url = $lang_prefix.$basedir.$item['seo_action'];
                $sQuery = "INSERT INTO `oxseo` (`OXOBJECTID`, `OXIDENT`, `OXSHOPID`, `OXLANG`, `OXSTDURL`, `OXSEOURL`, `OXTYPE`, `OXFIXED`, `OXEXPIRED`, `OXPARAMS`) VALUES
                  ('" . $oxId . "', '" . md5(strtolower($url)) . "', '" . $shop_id . "', $lang->id, '" . $item['orig_url'] . "', '" . $url. "', 'static', 0, 0, '');";
                oxDb::getDb()->Execute($sQuery);
            }
        }

    }

    public static function cleanup(){
        $oDb = oxDb::getDb();
        $sShopId = oxRegistry::getConfig()->getShopId();
        $sQuery   = "DELETE FROM `oxseo` WHERE `OXSTDURL` LIKE '%StylaSEO_Output%' and oxshopid = ".$oDb->quote($sShopId)." ;";
        $oDb->Execute($sQuery);
    }

    static function install(){
        self::updateSeoUrls();
    }

    static function uninstall(){
        self::cleanup();
    }
}

