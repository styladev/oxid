<?php

class StylaSEO_Util{

    const STYLA_URL = 'http://live.styla.com/';
    protected static $_username = '';
    protected static $_res = '';

    public static function getJsEmbedCode($username, $js_url = null){
        if(!$js_url)
            $js_url = self::STYLA_URL;
        $url = preg_filter('/https?:(.+)/i', '$1', (rtrim($js_url, '/').'/')).'scripts/preloader/'.$username.'.js';
        return '<script id="stylaMagazine" type="text/javascript" src="'.$url.'" defer="defer"></script>';
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

    public function getRemoteContent($username, $params, $src_url = null){

        if(!$src_url)
            $src_url =  self::STYLA_URL;

        $type = $params['type'];
        self::$_username = $username;

        if (!self::endsWith($src_url, "/")) {
            $src_url = $src_url . "/";
        }

        if($type=='tag')
            $url = $src_url . 'user/'.$username.'/tag/'.$params['tagname'];
        elseif($type=='story')
            $url = $src_url . 'story/'.$params['storyname'];
        elseif($type=='category')
            $url = $src_url . 'user/' . $params['username'] . '/category/' . $params['category'];
        elseif($type=='user')
            $url = $src_url . 'user/'.$params['username'];
        else
            $url = $src_url.'user/'.$username; // magazine default

        $cache_key = preg_replace('/[\/:]/i','-','stylaseo_'.$url);

        if(!$arr = $this->loadFromCache($cache_key)){
            $arr = self::_loadRemoteContent($url, $type);
            if(!$arr)
                return;

            $this->saveToCache($cache_key, $arr);
        }

        return $arr;
    }



    private static function _loadRemoteContent($url, $type = null){

        try{
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

            if(!self::$_res = $curl->execute())
                return false;

            $ret = array();
            $ret['meta'] = array();

            /** DEFAULT SET OF METADATA  */
            // Noscript content
            if(preg_match('/<noscript>(.*)<\/noscript>/is', self::$_res, $matches)){
                $ret['noscript_content'] = $matches[1];
            }

            // Meta description
            $ret['meta']['description'] = self::_getMetadataValueByName('description');

            // Page title
            if(preg_match('/<title>(.*)<\/title>/is', self::$_res, $matches)){
                $ret['page_title'] = $matches[1];
            }

            // Canonical link
            if(preg_match('/(<link rel="canonical"[^>]* href="([^"]+)"[^>]*>)/is', self::$_res, $matches)){
                $ret['canonical_url'] = $matches[2];
            }

            if($type == 'user' || $type == 'magazine' || $type == 'story'){

                // Facebook & opengraph tags
                $ret['meta']['fb_app_id'] = self::_getMetadataTagsByProperty('fb:app_id');
                $ret['meta']['og'] = self::_getMetadataTagsByProperty('og:(.*?)'); // Regex: Everything starting with og:

                // Author link
                if(preg_match('/(<link rel="author"[^>]+>)/is', self::$_res, $matches)){
                    $ret['author'] = $matches[1];
                }
            }

            if($type == 'story'){
                // Meta keywords
                $ret['meta']['keywords'] = self::_getMetadataValueByProperty('keywords');
            }
            return $ret;

        }catch (Exception $e){
            echo 'ERROR: '.$e->getMessage().' url:'.$url;
            return false;
        }



    }

    private static function _getMetadataValueByName($name){
        return self::_getMetadataValue('name', $name);
    }

    private static function _getMetadataValueByProperty($property){
        return self::_getMetadataValue('property', $property);
    }

    private static function _getMetadataTagsByProperty($property){
        return self::_getMetadataTags('property', $property);
    }

    private static function _getMetadataValue($type, $key){
        if(preg_match('/<meta [^>]*'.$type.'="'.$key.'" (.*?)content="([^"]+)"\W?\/>/is', self::$_res, $matches)){
            return $matches[2];
        }
    }

    private static function _getMetadataTags($type, $key){
        if(preg_match_all('/(<meta [^>]*'.$type.'="'.$key.'" (.*?)content="([^"]+)"\W?\/>)+/is', self::$_res, $matches)){
            $ret = $matches[0];
            if(!is_array($ret))
                return false;

            return implode("\r\n", $ret);
        }
    }

    function endsWith($haystack, $needle){
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }
}