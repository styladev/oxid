<?php

class StylaSEO_Output extends oxUBase{

    protected $_sThisTemplate = 'StylaSEO_Body.tpl';
    protected $_iViewIndexState = VIEW_INDEXSTATE_INDEX;
    protected $_util;
    protected $_username        = null;
    protected $_snippet_url     = null;
    protected $_feed_params     = array();
    protected $_ret             = null;

    public function __construct(){
        $this->_username    = $this->getConfig()->getConfigParam('styla_username');

        $this->_snippet_url = $this->getConfig()->getConfigParam('styla_js_url');
        $this->_snippet_url = rtrim($this->_snippet_url, '/').'/'; // make sure there is always (exactly 1) trailing slash

        if(empty($this->_username)){
            oxRegistry::get("oxUtilsView")->addErrorToDisplay( "STYLA_SEO_ERROR_NOUSERNAME" );
        }

        $this->_util = oxNew('StylaSEO_Util');
    }

    public function getBreadCrumb(){
        $aPaths = array();
        $aPath = array();

        $aPath['title'] = oxRegistry::getLang()->translateString('Magazine', oxRegistry::getLang()->getBaseLanguage(), false);
        $aPath['link'] = $this->getLink();
        $aPaths[] = $aPath;

        return $aPaths;
    }

    public function getTitle(){
        return $this->_ret['page_title'];
    }

    /**
     * Returns the standard OXID page title if the corresponding setting is checked.
     * Returns only the Styla title otherwise
     *
     * @return string
     */
    public function getPageTitle(){
        if ($this->getConfig()->getConfigParam('styla_seo_magazin_title')) {
            return parent::getPageTitle();
        }

        return $this->getTitle();
    }

    public function getCanonicalUrl(){
        return $this->_ret['canonical_url'];
    }

    public function render(){

        parent::render();

        if(!empty($this->_username)) {
            $ret = $this->_util->getRemoteContent($this->_username);
            $this->_ret = $ret;
            if (isset($ret['status'])) {
                oxRegistry::getUtils()->setHeader("HTTP/1.0 ".$ret['status']);
            }

            $this->setMetaDescription($ret['meta']['description']);
            unset($ret['meta']['description']);

            $this->getConfig()->setConfigParam('sFbAppId', ''); // set this to empty because we will overwrite the FB/opengraph data in our template anyway, we dont want to display the ones coming from Azure templates

            if (isset($ret['meta']['keywords'])) {
                $this->setMetaKeywords($ret['meta']['keywords']);
            }

            $this->_aViewData['js_embed'] = $this->_util->getJsEmbedCode($this->_username, $this->_snippet_url);
            $this->_aViewData['css_embed'] = $this->_util->getCssEmbedCode($this->_username, $this->_snippet_url);
            $this->_aViewData['noscript_content'] = $ret['noscript_content'];
            $this->_aViewData['meta_author'] = $ret['meta']['author'];
            $this->_aViewData['meta'] = $this->createHeaderHtml($ret['meta']);
        }


        return $this->_sThisTemplate;
    }

    public function createHeaderHtml($meta) {
        $retArray = [];
        foreach($meta as $element) {
            if (isset($element->tag) && $element->tag != "") {
                $html = '<'.$element->tag;
                foreach ($element->attributes as $aKey => $aVal) {
                    $html .= ' '.$aKey.'="'.$aVal.'"';
                }
                if (isset($element->content) && $element->content != "") {
                    $html .= '>'.$element->content.'</'.$element->tag.'>';
                } else {
                    $html .= ' />';
                }
                array_push($retArray, $html);
            }
        }
        return $retArray;
    }

    /**
     * getPluginVersion
     * -----------------------------------------------------------------------------------------------------------------
     * entry point getVersion API
     *
     * @compatibleOxidVersion 5.2.x
     *
     */
    public function getPluginVersion()
    {
        // get version from metadata
        $oModule = oxNew('oxmodule');
        $oModule->load('StylaSEO');
        $sVersionSEO = $oModule->getInfo('version');

        $oModule = oxNew('oxmodule');
        $oModule->load('StylaFeed');
        $sVersionFEED = $oModule->getInfo('version');

        $aData = array('version_StylaSEO' => $sVersionSEO,
                        'version_StylaFeed' => $sVersionFEED,
        );

        die(json_encode($aData));
    }
}
