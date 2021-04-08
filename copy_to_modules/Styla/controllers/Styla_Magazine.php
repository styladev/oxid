<?php

class Styla_Magazine extends oxUBase
{
    protected $_sThisTemplate = 'Styla_Magazine.tpl';
    protected $_iViewIndexState = VIEW_INDEXSTATE_INDEX;
    protected $_oUtil;
    protected $_sUsername;
    protected $_sSnippetURL;
    protected $_aContent;

    /**
     * Sets some properties
     */
    public function __construct()
    {
        parent::__construct();
        $this->_oUtil = oxNew('Styla_Util');
        $this->_sUsername = oxRegistry::getConfig()->getRequestParameter('user');
        $this->_sSnippetURL = $this->getConfig()->getConfigParam('styla_prophet_url');

        if (empty($this->_sUsername)) {
            oxRegistry::get("oxUtilsView")->addErrorToDisplay("STYLA_SEO_ERROR_NOUSERNAME");
        }
    }

    /**
     * Returns magazine breadcrumb
     *
     * @return array
     */
    public function getBreadCrumb()
    {
        $aPaths = array(
            array(
                'title' => oxRegistry::getLang()->translateString('Magazine', oxRegistry::getLang()->getBaseLanguage(), false),
                'link'  => $this->getLink(),
            ),
        );

        return $aPaths;
    }

    /**
     * render
     * -----------------------------------------------------------------------------------------------------------------
     *
     *
     * @return string
     */
    public function render()
    {
        parent::render();
        $this->_aViewData['is_startpage'] = false;
        
        if (!empty($this->_sUsername)) {
            $this->_sUsername = str_replace('${language}', oxRegistry::getLang()->getLanguageAbbr(), $this->_sUsername);
            $aContent = $this->_oUtil->getRemoteContent($this->_sUsername);
            $this->_aContent = $aContent;

            if (isset($aContent['status'])) {
                oxRegistry::getUtils()->setHeader("HTTP/1.0 " . $aContent['status']);
            }

            $this->setMetaDescription($aContent['meta']['description']);
            unset($aContent['meta']['description']);
            //set this to empty because we will overwrite the FB/opengraph data in our template anyway, we dont want to display the ones coming from Azure templates
            $this->getConfig()->setConfigParam('sFbAppId', '');

            if (isset($aContent['meta']['keywords'])) {
                $this->setMetaKeywords($aContent['meta']['keywords']);
            }

            $this->_aViewData['js_embed'] = $this->_oUtil->getJsEmbedCode($this->_sSnippetURL);
            $this->_aViewData['styla_div'] = '<div id="stylaMagazine" data-styla-client="' . $this->_sUsername . '">'.$aContent['noscript_content'].'</div>';
            $this->_aViewData['meta_author'] = $aContent['meta']['author'];
            $this->_aViewData['meta'] = $this->_oUtil->createMetaHeaderHtml($aContent['meta']);
        }

        return $this->_sThisTemplate;
    }

    /**
     * Returns the standard OXID page title if the corresponding setting is checked.
     * Returns only the Styla title otherwise
     *
     * @return string
     */
    public function getPageTitle()
    {
        if ($this->getConfig()->getConfigParam('styla_seo_magazin_title')) {
            return parent::getPageTitle();
        }

        return $this->getTitle();
    }

    public function getTitle()
    {
        return $this->_aContent['page_title'];
    }

    public function getCanonicalUrl()
    {
        return $this->_aContent['canonical_url'];
    }

    public function getMetaDescription()
    {
        return $this->_aContent['description'];
    }

    public function getMetaKeywords()
    {
        return $this->_aContent['keywords'];
    }
}
