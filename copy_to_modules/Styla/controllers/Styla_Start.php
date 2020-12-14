<?php

class Styla_Start extends Styla_Start_parent
{
    /**
     * @var object
     */
    protected $_StylaUtil;
    protected $_sStylaUsername;
    protected $_sStylaJsUrl;

    /**
     * Styla_Start constructor.
     * Sets some properties
     * @throws oxSystemComponentException
     */
    public function __construct()
    {
        parent::__construct();
        $this->_StylaUtil = oxNew('Styla_Util');
        $iLang = $this->getConfig()->getActiveShop()->getLanguage();
        $oHomePath = oxNew('Styla_Paths');
        $oHomePath->loadHomePath($iLang);
        if(isset($oHomePath->styla_paths__stylauser->value) && !empty($oHomePath->styla_paths__stylauser->value)){
            $this->_sStylaUsername = $oHomePath->styla_paths__stylauser->value;
        }
        $this->_sStylaJsUrl = $this->getConfig()->getConfigParam('styla_prophet_url');
        $this->_sStylaJsUrl = rtrim($this->_sStylaJsUrl, '/') . '/'; // make sure there is always (exactly 1) trailing slash
    }

    /**
     * render
     * -----------------------------------------------------------------------------------------------------------------
     * Renderfunction for the FE
     *
     * @return mixed
     */
    public function render()
    {
        $this->renderStylaContent();
        return parent::render();
    }


    /**
     * renderStylaContent
     * -----------------------------------------------------------------------------------------------------------------
     * Function to implement all things needed fpr a working styla plugin
     *
     */
    public function renderStylaContent()
    {
        $this->_aViewData['is_startpage'] = false;
        if (!empty($this->_sStylaUsername)) {
            $this->_sStylaUsername = str_replace('${language}', oxRegistry::getLang()->getLanguageAbbr(), $this->_sStylaUsername);
            $_GET['path'] = '/';
            $aContent = $this->_StylaUtil->getRemoteContent($this->_sStylaUsername);
            $this->_aViewData['js_embed'] = $this->_StylaUtil->getJsEmbedCode($this->_sStylaJsUrl);
            $this->_aViewData['css_embed'] = $this->_StylaUtil->getCssEmbedCode($this->_sStylaUsername, $this->_sStylaJsUrl);
            $this->_aViewData['styla_div'] = '<div id="stylaMagazine">' . $aContent['noscript_content'] . '</div>';
            $this->_aViewData['is_startpage'] = true;
        }
    }


}