<?php

class Styla_Pathslist extends oxList
{
    
    /**
     * @compatibleOxidVersion all
     *
     * Call parent class constructor
     *
     * @param string $sObjectsInListName Associated list item object type
     */
    public function __construct( $sObjectsInListName = 'Styla_Paths' )
    {
        parent::__construct( 'Styla_Paths' );
    }
    
    /**
     * getListInLang
     * -----------------------------------------------------------------------------------------------------------------
     * build list select query in specific language
     *
     * @param $iLang
     *
     * @return $this
     */
    public function getListInLang($iLang){
        $sQ = "select oxid, oxtimestamp, styla_home,stylapath,stylauser
            from " . $this->_getLangViewName($iLang,oxRegistry::getConfig()->getShopId());
        $this->selectString($sQ);
    
        return $this;
    }
    
    /**
     * _getLangViewName
     * -----------------------------------------------------------------------------------------------------------------
     * method to get the right view -> styla_path is when this list is used not in the array of Multilang Tables so we
     * use this methode to get the view names for the languages
     *
     * @param null $iLangId
     * @param null $sShopId
     *
     * @return string
     */
    protected function _getLangViewName($iLangId = null, $sShopId = null)
    {
        $myConfig = oxRegistry::getConfig();
    
        //This config option should only be used in emergency case.
        //Originally it was planned for the case when admin area is not reached due to the broken views.
        if (!$myConfig->getConfigParam('blSkipViewUsage')) {
            $sViewSfx = '';
        
            //if user does not want to use views he could specify that in config.inc.php
            //this could be used for example for performance reasons when there exists only one shop
            if ($sShopId != -1 && $myConfig->isMall() && in_array('styla_paths', $myConfig->getConfigParam('aMultiShopTables'))) {
                $sShopId = $sShopId ? $sShopId : $myConfig->getShopId();
                $sViewSfx .= "_{$sShopId}";
            }
        
            if ($iLangId != -1) {
                $oLang = oxRegistry::getLang();
                $iLangId = $iLangId !== null ? $iLangId : oxRegistry::getLang()->getBaseLanguage();
                $sAbbr = $oLang->getLanguageAbbr($iLangId);
                $sViewSfx .= "_{$sAbbr}";
            }
        
            if ($sViewSfx || (($iLangId == -1 || $sShopId == -1))) {
                return "oxv_styla_paths{$sViewSfx}";
            }
        
        }
    
        return 'styla_paths';
    }
    
}