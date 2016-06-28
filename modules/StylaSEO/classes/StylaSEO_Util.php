<?php

class StylaSEO_Util{

    const STYLA_URL = 'http://cdn.styla.com';
    const API_STYLA_URL = 'http://live.styla.com';
    const SEO_URL = 'http://seo.styla.com';
    protected static $_username = '';
    protected static $_res = '';

    public static function getJsEmbedCode($username, $js_url = null){

        if(!$js_url)
            $js_url = self::STYLA_URL;
        $url = preg_filter('/https?:(.+)/i', '$1', (rtrim($js_url, '/').'/')).'scripts/clients/'.$username.'.js?version=' . self::_getVersion($username);

        return '<script  type="text/javascript" src="'.$url.'" defer="defer"></script>';
    }

    public static function getCssEmbedCode($username, $css_url = null){

        if(!$css_url)
            $css_url = self::STYLA_URL;

        $sCssUrl = preg_filter('/https?:(.+)/i', '$1', (rtrim($css_url, '/').'/')).'styles/clients/'.$username.'.css?version=' . self::_getVersion($username);

        return '<link rel="stylesheet" type="text/css" href="' .  $sCssUrl . '">';
    }


    public static function getActionFromUrl($basedir = 'magazin'){
        $url = $_SERVER['REQUEST_URI'];
        $action = preg_filter('(/en)?/'.$basedir.'/([^\/]+).*/i', '$2', $url);
        return $action;
    }

    public function getParamFromUrl($search){
        $url = $_SERVER['REQUEST_URI'];
        if(($start = strpos($url,$search))===false)
            return false;

        $ret =substr($url, $start+strlen($search)+1);
        return rtrim($ret,'/');
    }

    protected function _getCacheId($name){
        $oConfig = oxRegistry::getConfig();
        return $name . '_' . $oConfig->getShopId() . '_' . oxRegistry::getLang()->getBaseLanguage() . '_' . (int) $oConfig->getShopCurrency();
    }

    public function loadFromCache($name){
        if ($aRes = oxRegistry::getUtils()->fromFileCache($this->_getCacheId($name))) {
            $iCacheTtl = oxRegistry::getConfig()->getConfigParam('styla_seo_cache_ttl');
            if ($aRes['timestamp'] > time() - $iCacheTtl) {
                return $aRes['content'];
            }
        }
        return false;
    }

    public function saveToCache($name, $aContent){
        $aData = array('timestamp' => time(), 'content' => $aContent);
        return oxRegistry::getUtils()->toFileCache($this->_getCacheId($name), $aData);
    }

    public function getRemoteContent($username, $params){

        $type = $params['type'];
        self::$_username = $username;

        if ($type == 'tag') {
            $url = 'user/' . $username . '/tag/' . $params['tagname'];
        } elseif ($type == 'story') {
            $url = 'story/' . $params['storyname'];
        } elseif ($type == 'category') {
            $url = 'user/' . $params['username'] . '/category/' . $params['category'];
        } elseif ($type == 'user') {
            $url = 'user/' . $params['username'];
        } else {
            $url = 'user/' . $username; // magazine default
        }

        $cache_key = preg_replace('/[\/:]/i','-','stylaseo_'.$url);

        if(!$arr = $this->loadFromCache($cache_key)){
            $arr = $this->_loadRemoteContent();
            if(!$arr)
                return;

            $this->saveToCache($cache_key, $arr);
        }

        return $arr;
    }

    protected function _loadRemoteContent()
    {
        try{
            return $this->_getMetadata();

        } catch (Exception $e) {
            echo 'ERROR: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * Adds meta properties to the given array and returns it
     *
     * @return array
     */
    protected function _getMetadata()
    {
        $ret = array();

        $seoServerUrl = oxRegistry::getConfig()->getConfigParam('styla_seo_server');
        if (!$seoServerUrl) $seoServerUrl = self::SEO_URL;

        $basedir = oxRegistry::getConfig()->getConfigParam('styla_seo_basedir');
        if (!$basedir) $basedir = StylaSEO_Setup::STYLA_BASEDIR;

        // Should be filled, StylaSEO_Output checks this already
        $username = oxRegistry::getConfig()->getConfigParam('styla_username');

        // Get the correct url for the server's url parameter
        $request = oxRegistry::get('oxUtilsServer')->getServerVar('REQUEST_URI');
        $request = substr($request, strpos($request, $basedir) + strlen($basedir) + 1);
        $url = rtrim($seoServerUrl, '/') . '/clients/' . $username . '?url=' . $request;

        if (!$_res = $this->_getCurlResult($url)) {
            return $ret;
        }

        $result = json_decode($_res);
        if (isset($result->tags) && count($result->tags)) {
            $ret['meta'] = array();
            foreach ($result->tags as $tag) {
                if (in_array($tag->tag, array('link', 'meta'), true)) {
                    $ret['meta'][] = $tag;

                    if (isset($tag->attributes->name)
                        && in_array($tag->attributes->name, array('description', 'keywords', 'canonical', 'author'), true)
                    ) {
                        $ret['meta'][$tag->attributes->name] = $tag->attributes->content;
                    }
                } elseif ($tag->tag === 'title') {
                    $ret['page_title'] = $tag->content;
                }
            }
        }

        if (isset($result->html->body)) {
            $ret['noscript_content'] = $result->html->body;
        }

        return $ret;
    }

    /**
     * Helper method: returns StylaSEO_Curl result for given URL
     *
     * @param string $url
     * @return string
     */
    protected function _getCurlResult($url)
    {
        $curl = oxNew('StylaSEO_Curl');
        $curl->setUrl($url);
        $curl->setOption('CURLOPT_POST', 0);
        $curl->setOption('CURLOPT_HEADER', 0);
        $curl->setOption('CURLOPT_HTTPHEADER', array('OXID Styla SEO Module for ' . self::$_username));
        $curl->setOption('CURLOPT_FRESH_CONNECT', 1);
        $curl->setOption('CURLOPT_RETURNTRANSFER', 1);
        $curl->setOption('CURLOPT_FORBID_REUSE', 1);
        $curl->setOption('CURLOPT_TIMEOUT', 60);
        $curl->setOption('CURLOPT_SSL_VERIFYPEER', 0);
        $curl->setOption('CURLOPT_SSL_VERIFYHOST', 0);
        $curl->setOption('CURLOPT_USERPWD', null);

        return $curl->execute();
    }

    /**
     * _getVersion
     * -----------------------------------------------------------------------------------------------------------------
     * requests and caches the current version from styla
     *
     * @param $username
     *
     * @compatibleOxidVersion 5.2.x
     *
     */
    protected function _getVersion($username)
    {
        $sVersion = '';

        // try to load from cache
        $sCacheName = 'StylaVersionCache';

        if ($aRes = oxRegistry::getUtils()->fromFileCache($sCacheName)) {
            $iCacheTtl = 3600; // 1 hour expiration
            if ($aRes['timestamp'] > time() - $iCacheTtl) {
                $sVersion = $aRes['content'];
            }
        }
        else {
            // get version from styla
            $api_url = oxRegistry::getConfig()->getConfigParam('styla_api_url');
            if (!$api_url) {
                $api_url = self::API_STYLA_URL;
            }

            $url = $api_url . '/api/version/' . $username;

            $sVersion = self::_getCurlResult($url);

            // save to cache
            $aData = array('timestamp' => time(), 'content' => $sVersion);
            oxRegistry::getUtils()->toFileCache($sCacheName, $aData);
        }

        return $sVersion;
    }
}