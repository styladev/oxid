<?php

class Styla_Setup
{
    // Default values
    const STYLA_FEED_BASEDIR = 'stylafeed';
    const STYLA_MAGAZINE_BASEDIR = 'magazin';

    // URLs to write to oxseo
    private static $_aFeedUrls = array(
        array('orig_url' => 'index.php?cl=Styla_Feed&fnc=showAll', 'seo_action' => 'index/'),
        array('orig_url' => 'index.php?cl=Styla_Feed&fnc=showCategories', 'seo_action' => 'index/category/'),
        array('orig_url' => 'index.php?cl=Styla_Feed&fnc=showProduct', 'seo_action' => 'index/product/'),
    );

    /**
     * Updates SEO URLs for Styla feed and magazine
     */
    public static function updateSeoUrls()
    {
        self::cleanup(); // prevent duplicate entries
        $oUtil = oxNew('Styla_Util');
        //get languages
        $oLang = oxRegistry::getLang();
        $aLanguages = $oLang->getLanguageArray();
        foreach (array_keys($aLanguages) as $iLangId){
            $oList = OxNew('Styla_Pathslist')->getListInLang($iLangId);
            foreach ($oList->getArray() as $oPath){
                if(!$oPath->styla_paths__styla_home->value){
                    $sBaseDir = $oPath->styla_paths__stylapath->value;
                    if ($sBaseDir == '') {
                        $sBaseDir = self::STYLA_MAGAZINE_BASEDIR;
                    }
                    // Writing magazine URLs to SEO
                    $oUtil->addStylaSeo(
                        $oPath->getId(),$sBaseDir,$iLangId,$oPath->styla_paths__stylauser->value
                    );
                }
            }
        }

        // Writing feed URLs to SEO
        $sBaseDir = oxRegistry::getConfig()->getConfigParam('styla_feed_basedir');
        if ($sBaseDir == '') {
            $sBaseDir = self::STYLA_FEED_BASEDIR;
        }
        self::_writeSeoUrls($sBaseDir, self::$_aFeedUrls);

        // Write version getter
        self::_writeSeoUrls("", array(array('orig_url' => 'index.php?cl=Styla_Feed&fnc=showVersion', 'seo_action' => 'styla-plugin-version/')));
    }

    /**
     * _writeSeoUrls
     * -----------------------------------------------------------------------------------------------------------------
     * Writes SEO URLs with given base directory to oxseo
     *
     * @param string $sBaseDir
     * @param array  $aUrls
     */
    protected static function _writeSeoUrls($sBaseDir, $aUrls)
    {
        if (!empty($sBaseDir)) {
            $sBaseDir = rtrim($sBaseDir, '/') . '/';
        }

        $iShopID = oxRegistry::getConfig()->getShopId();
        $aLanguages = oxRegistry::getLang()->getLanguageArray();
        $defaultLang = oxRegistry::getConfig()->getConfigParam('sDefaultLang');
        //check if we have language defined from db ( only for StylaPaths )

        foreach ($aUrls as $aURL) {
            $sOxID = oxRegistry::get('oxSeoEncoder')->getDynamicObjectId($iShopID, $aURL['orig_url']);
            foreach ($aLanguages as $oLang) {
                $sLangPrefix = '';
                if ($oLang->id != $defaultLang) {
                    $sLangPrefix = $oLang->abbr . '/';
                }
                $sURL = $sLangPrefix . $sBaseDir . $aURL['seo_action'];

                oxRegistry::get('oxSeoEncoder')->addSeoEntry($sOxID, $iShopID, $oLang->id, $aURL['orig_url'], $sURL, 'static', 0);
            }
        }
    }

    /**
     * Removes old oxseo entries for Styla modules
     */
    public static function cleanup()
    {
        $sShopId = oxRegistry::getConfig()->getShopId();
        $aQuerys = array(
            // Delete legacy settings, they are otherwise conflicting with the new module's settings
            "DELETE FROM oxconfig WHERE (OXMODULE = 'module:StylaSEO' or OXMODULE = 'module:StylaFeed') AND OXSHOPID = ?",
            "DELETE FROM `oxseo` WHERE `OXSTDURL` LIKE '%Styla_%' and oxshopid = ?",
            // Legacy: Old StylaFeed module
            "DELETE FROM `oxseo` WHERE `OXSTDURL` LIKE '%StylaFeed_Output%' and oxshopid = ?",
            // Legacy: Old StylaSEO module
            "DELETE FROM `oxseo` WHERE `OXSTDURL` LIKE '%StylaSEO_Output%' and oxshopid = ?",
            // Legacy
            "DELETE FROM `oxseo` WHERE `OXSTDURL` LIKE '%Amazinefeed_Output%' and oxshopid = ?"
        );
        //run all delete querys
        $oDb = oxDb::getDb();
        foreach ($aQuerys as $sQuery) {
            $oDb->execute($sQuery, array($sShopId));
        }
    }

    /**
     * Called when activating the module
     */
    public static function install()
    {
        $sSQL = 'CREATE TABLE  if not exists `styla_paths` (
             `OXID`        char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
             `OXTIMESTAMP` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
             `styla_home`  tinyint(4) NOT NULL DEFAULT "0",
             `STYLAPATH`   varchar(255) NOT NULL,
             `STYLAPATH_1` varchar(255) NOT NULL,
             `STYLAPATH_2` varchar(255) NOT NULL,
             `STYLAPATH_3` varchar(255) NOT NULL,
             `STYLAUSER`   varchar(255) NOT NULL,
             `STYLAUSER_1` varchar(255) NOT NULL,
             `STYLAUSER_2` varchar(255) NOT NULL,
             `STYLAUSER_3` varchar(255) NOT NULL,
             PRIMARY KEY (`OXID`)
             ) ENGINE=InnoDB DEFAULT CHARSET=latin1';
        $oDb = oxDb::getDb();
        $oDb->execute($sSQL);
        
        //create views
        $oShop = oxRegistry::getConfig()->getActiveShop();
        $oLang = oxRegistry::getLang();
        $aLanguages = $oLang->getLanguageIds($oShop->getId());
        $oShop->createViewQuery('styla_paths',$aLanguages);
        foreach ($oShop->getQueries() as $sQuery){
            $oDb->execute($sQuery);
        }
        
        self::updateSeoUrls();
    }

    /**
     * Called when deactivating the module
     */
    public static function uninstall()
    {
        self::cleanup();
    }

}
