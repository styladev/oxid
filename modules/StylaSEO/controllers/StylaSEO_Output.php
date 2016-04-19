<?php

class StylaSEO_Output extends oxUBase{

    protected $_sThisTemplate = 'StylaSEO_Body.tpl';
    protected $_iViewIndexState = VIEW_INDEXSTATE_INDEX;
    protected $_util;
    protected $_username        = null;
    protected $_source_url      = null;
    protected $_snippet_url     = null;
    protected $_feed_params     = array();
    protected $_ret             = null;

    public function __construct(){
        $this->_username    = $this->getConfig()->getConfigParam('styla_username');
        $this->_source_url  = $this->getConfig()->getConfigParam('styla_source_url');
        $this->_snippet_url = $this->getConfig()->getConfigParam('styla_js_url');

        $this->_source_url = rtrim($this->_source_url, '/').'/'; // make sure there is always (exactly 1) trailing slash
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
            $type = $this->_feed_params['type'];

            // TODO: metatags will here be fetched via filtering html input from curl, find better way about it
            $ret = $this->_util->getRemoteContent($this->_username, $this->_feed_params, $this->_source_url);
            $this->_ret = $ret;

            $this->setMetaDescription($ret['meta']['description']);

            if ($type == 'user' || $type == 'magazine' || $type == 'story') {
                $this->getConfig()->setConfigParam('sFbAppId', ''); // set this to empty because we will overwrite the FB/opengraph data in our template anyway, we dont want to display the ones coming from Azure templates
            }

            if ($type == 'story') {
                $this->setMetaKeywords($ret['meta']['keywords']);
            }

            $this->_aViewData['js_embed'] = $this->_util->getJsEmbedCode($this->_username, $this->_snippet_url);
            $this->_aViewData['noscript_content'] = $ret['noscript_content'];
            $this->_aViewData['meta_author'] = $ret['meta']['author'];
            $this->_aViewData['feed_type'] = $type;
            $this->_aViewData['meta'] = $ret['meta'];
        }


        return $this->_sThisTemplate;
    }

    public function showMagazine(){
        $this->_feed_params = array('type'=>'magazine');
    }

    public function showTag(){
        $tagname = $this->_util->getParamFromUrl('tag');
        $this->_feed_params = array('type'=>'tag', 'tagname' => $tagname);
    }

    public function showStory(){
        $storyname = $this->_util->getParamFromUrl('story');
        $this->_feed_params = array('type'=>'story', 'storyname' => $storyname);
    }

    /**
     * Gets username from url and writes it in _feed_params
     */
    public function showUser()
    {
        $username = $this->_util->getParamFromUrl('user');
        $this->_feed_params = array('type' => 'user', 'username' => $username);
    }

    /**
     * Gets category and user string from URL and writes it in _feed_params
     */
    public function showCategory()
    {
        $category = $this->_util->getParamFromUrl('category');

        $username = $this->_util->getParamFromUrl('user');
        if (!$username) {
            $username = $this->_username;
        } else {
            // getParamFromUrl gets everything after the first slash after the searched string. We only want the username
            $username = getStr()->substr($username, 0, getStr()->strpos($username, '/'));
        }

        $this->_feed_params = array('type' => 'category', 'username' => $username, 'category' => $category);
    }
}
