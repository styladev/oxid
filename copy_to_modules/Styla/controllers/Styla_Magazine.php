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
        $this->_oUtil = oxNew('Styla_Util');
        $this->_sUsername = $this->getConfig()->getConfigParam('styla_username');
        $this->_sSnippetURL = $this->getConfig()->getConfigParam('styla_js_url');
        $this->_sSnippetURL = rtrim($this->_sSnippetURL, '/') . '/'; // make sure there is always (exactly 1) trailing slash

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

    public function render()
    {
        parent::render();

        if (!empty($this->_sUsername)) {
            $this->_sUsername = str_replace('${language}', oxRegistry::getLang()->getLanguageAbbr(), $this->_sUsername);

            $aContent = $this->_oUtil->getRemoteContent($this->_sUsername);
            $this->_aContent = $aContent;

            if (isset($aContent['status'])) {
                oxRegistry::getUtils()->setHeader("HTTP/1.0 " . $aContent['status']);
            }

            $this->setMetaDescription($aContent['meta']['description']);
            unset($aContent['meta']['description']);

            $this->getConfig()->setConfigParam('sFbAppId', ''); // set this to empty because we will overwrite the FB/opengraph data in our template anyway, we dont want to display the ones coming from Azure templates

            if (isset($aContent['meta']['keywords'])) {
                $this->setMetaKeywords($aContent['meta']['keywords']);
            }

            $this->_aViewData['js_embed'] = $this->_oUtil->getJsEmbedCode($this->_sSnippetURL);
            $this->_aViewData['styla_div'] = '<div id="stylaMagazine" data-styla-client="' . $this->_sUsername . '">'.$aContent['noscript_content'].'</div>';
            $this->_aViewData['meta_author'] = $aContent['meta']['author'];
            $this->_aViewData['meta'] = $this->_createHeaderHtml($aContent['meta']);
        }

        return $this->_sThisTemplate;
    }

    /**
     * Returns meta tags HTML from given elements
     *
     * @param array $aMetaElements
     * @return array
     */
    protected function _createHeaderHtml($aMetaElements)
    {
        $aReturn = array();
        foreach ($aMetaElements as $oElement) {
            if (isset($oElement->tag) && $oElement->tag != "") {
                $sHTML = '<' . $oElement->tag;
                foreach ($oElement->attributes as $aKey => $aVal) {
                    $sHTML .= ' ' . $aKey . '="' . $aVal . '"';
                }
                if (isset($oElement->content) && $oElement->content != "") {
                    $sHTML .= '>' . $oElement->content . '</' . $oElement->tag . '>';
                } else {
                    $sHTML .= ' />';
                }
                array_push($aReturn, $sHTML);
            }
        }

        return $aReturn;
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
