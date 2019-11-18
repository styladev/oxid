<?php

class Styla_Util
{
    const STYLA_URL = 'https://client-scripts.styla.com';
    const API_STYLA_URL = 'http://live.styla.com';
    const SEO_URL = 'http://seoapi.styla.com';

    /**
     * Returns cache ID
     *
     * @param string $name
     * @return string
     */
    protected function _getCacheId($name)
    {
        $oConfig = oxRegistry::getConfig();

        return $name . '_' . $oConfig->getShopId() . '_' . oxRegistry::getLang()->getBaseLanguage() . '_' . (int) $oConfig->getShopCurrency();
    }

    /**
     * Loads data from cache
     *
     * @param string $name
     * @param string $mode
     * @return mixed
     */
    public function loadFromCache($name, $mode = 'seo')
    {
        if ($aRes = oxRegistry::getUtils()->fromFileCache($this->_getCacheId($name))) {
            $iCacheTtl = oxRegistry::getConfig()->getConfigParam("styla_{$mode}_cache_ttl");
            if ($aRes['timestamp'] > time() - $iCacheTtl) {
                return $aRes['content'];
            }
        }

        return false;
    }

    /**
     * Saves data to cache
     *
     * @param string $name
     * @param mixed  $aContent
     * @return bool
     */
    public function saveToCache($name, $aContent)
    {
        $aData = array('timestamp' => time(), 'content' => $aContent);

        return oxRegistry::getUtils()->toFileCache($this->_getCacheId($name), $aData);
    }

    /**
     * Returns remote content based on currently called URL
     *
     * @param string $username
     * @return array|bool
     */
    public function getRemoteContent($username)
    {
        $seoServerUrl = oxRegistry::getConfig()->getConfigParam('styla_seo_server');
        if (!$seoServerUrl) $seoServerUrl = self::SEO_URL;

        // Get the correct url for the server's url parameter
        $path = strtok(oxRegistry::get('oxUtilsServer')->getServerVar('REQUEST_URI'), '?');
        $url = rtrim($seoServerUrl, '/') . '/clients/' . $username . '?url=' . urlencode($path);

        $cache_key = preg_replace('/[\/:]/i', '-', 'stylaseo_' . $url);

        if (!$arr = $this->loadFromCache($cache_key, 'seo')) {
            try {
                $arr = $this->_fetchSeoData($url);
                if ($arr) {
                    $this->saveToCache($cache_key, $arr);
                }
            } catch (Exception $e) {
                echo 'ERROR: ' . $e->getMessage();

                return false;
            }
        }

        return $arr;
    }

    /**
     * Adds meta properties to the given array and returns it
     *
     * @param string $url
     * @return array
     */
    protected function _fetchSeoData($url)
    {
        $ret = array();

        if (!($_res = $this->_getCurlResult($url))) {
            return $ret;
        }
        $result = json_decode($_res);

        // Try to extract relevant meta tags
        if (isset($result->tags) && count($result->tags)) {
            $ret['meta'] = array();
            foreach ($result->tags as $tag) {
                if (in_array($tag->tag, array('link', 'meta', 'noscript'), true)) {
                    if (isset($tag->attributes->name)
                        && in_array($tag->attributes->name, array('description', 'keywords'), true)
                    ) {
                        $ret[$tag->attributes->name] = $tag->attributes->content;
                    } else {
                        $ret['meta'][] = $tag;
                        if (isset($tag->attributes->name)
                            && in_array($tag->attributes->name, array('canonical', 'author'), true)
                        ) {
                            $ret['meta'][$tag->attributes->name] = $tag->attributes->content;
                        }
                    }
                } elseif ($tag->tag === 'title') {
                    $ret['page_title'] = $tag->content;
                }
            }
        }

        if (isset($result->html->body)) {
            $ret['noscript_content'] = $result->html->body;
        }

        if (isset($result->status)) {
            $ret['status'] = $result->status;
        }

        return $ret;
    }

    /**
     * Helper method: returns StylaSEO_Curl result for given URL
     *
     * @param string $url
     * @return string
     */
    protected static function _getCurlResult($url)
    {
        $curl = oxNew('Styla_Curl');
        $curl->setUrl($url);
        $curl->setOption('CURLOPT_POST', 0);
        $curl->setOption('CURLOPT_HEADER', 0);
        $curl->setOption('CURLOPT_HTTPHEADER', array('OXID Styla SEO Module'));
        $curl->setOption('CURLOPT_FRESH_CONNECT', 1);
        $curl->setOption('CURLOPT_RETURNTRANSFER', 1);
        $curl->setOption('CURLOPT_FOLLOWLOCATION', 1);
        $curl->setOption('CURLOPT_FORBID_REUSE', 1);
        $curl->setOption('CURLOPT_TIMEOUT', 60);
        $curl->setOption('CURLOPT_SSL_VERIFYPEER', 0);
        $curl->setOption('CURLOPT_SSL_VERIFYHOST', 0);
        $curl->setOption('CURLOPT_USERPWD', null);

        return $curl->execute();
    }
}
