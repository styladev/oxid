<?php

class Styla_PathsTabController extends oxAdminDetails
{
    
    /**
     * @var object|Styla_Util
     */
    protected $_oStylaUtil;
    
    /**
     * Current class template name.
     * @var string
     */
    protected $_sThisTemplate = 'Styla_pathsAdminTab.tpl';
    
    function __construct()
    {
        parent::__construct();
        $this->_oStylaUtil = oxNew('Styla_Util');
    }

    /**
     * save
     * -----------------------------------------------------------------------------------------------------------------
     * Saves configuration parameters.
     *
     * @throws oxSystemComponentException
     */
    public function save()
    {
        $oConfig = oxRegistry::getConfig();
        $sOxid = $this->getEditObjectId();
        $iLang = $this->_iEditLang;
        $aParams = $oConfig->getRequestParameter('editval');
        //checkboxhandling
        if(isset($aParams['styla_home'])){
            $aParams['styla_home'] = 1;
            $aParams['oxid'] = 'styla_home';
        }
        else{
            $aParams['styla_home'] = 0;
        }
        $oModel = oxNew('Styla_Paths');
        if ($sOxid != "-1") {
            $oModel = oxNew('Styla_Paths');
            $oModel->loadInLang($iLang,$sOxid);
        }
        $oModel->assign($aParams);
        $oModel->save();
        parent::save();
        $this->setEditObjectId($oModel->getId());
        //no seo for landingpage
        if(!$aParams['styla_home']){
            $this->_oStylaUtil->updateStylaSeo(
                $oModel->getId(),$aParams['stylapath'],$iLang,$aParams['stylauser']
            );
        }
    }

    /**
     * render
     * -----------------------------------------------------------------------------------------------------------------
     * render tab content
     *
     * @return string
     * @throws oxSystemComponentException
     */
    public function render()
    {
        parent::render();
        $oModel = oxNew('Styla_Paths');
        $this->_aViewData['edit'] = $oModel;
        $sOxId = $this->getEditObjectId();
        //check if load of dataset -> load data in model
        if ($sOxId && $sOxId != "-1") {
            $oModel->loadInLang($this->_iEditLang,$sOxId);
    
            // load object in other languages
            $oOtherLang = $oModel->getAvailableInLangs();
            if (!isset($oOtherLang[$this->_iEditLang])) {
                // echo "language entry doesn't exist! using: ".key($oOtherLang);
                $oModel->loadInLang(key($oOtherLang), $sOxId);
            }
            
            $aLang = array_diff(oxRegistry::getLang()->getLanguageNames(), $oOtherLang);
            if (count($aLang)) {
                $this->_aViewData["posslang"] = $aLang;
            }
    
            foreach ($oOtherLang as $id => $language) {
                $oLang = new stdClass();
                $oLang->sLangDesc = $language;
                $oLang->selected = ($id == $this->_iEditLang);
                $this->_aViewData["otherlang"][$id] = clone $oLang;
            }
        }
        return $this->_sThisTemplate;
    }

    /**
     * saveinnlang
     * -----------------------------------------------------------------------------------------------------------------
     * copy lang - save method
     *
     * @throws oxSystemComponentException
     */
    public function saveinnlang(){
        $this->_iEditLang = oxRegistry::getConfig()->getRequestParameter('new_lang');
        $this->save();
    }
    
}