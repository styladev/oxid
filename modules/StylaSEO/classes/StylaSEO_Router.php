<?php

class StylaSEO_Router extends StylaSEO_Router_parent{


    /**
     * Goal: If passed url is not found,
     * try to find any other that consist of it,
     * by removing each time the string after the last slash.
     *
     * Ex.
     * if "http://[yourwebsite.com]/magazin/tag/[tagname]" was not found
     * then render "http://[yourwebsite.com]/magazin/"
     *
     * @param $sSeoUrl
     * @return mixed
     */
    public function decodeUrl($sSeoUrl)
    {
        $aRet = parent::decodeUrl($sSeoUrl);

        $sStylaBase = StylaSEO_Setup::STYLA_BASEDIR;
        if ($this->getConfig()->getConfigParam('styla_seo_basedir')) {
            $sStylaBase = $this->getConfig()->getConfigParam('styla_seo_basedir');
        }

        $oStr = getStr();

        // Only use our way if parent did not find something and styla base dir is used
        if($aRet === false && $oStr->strpos($sSeoUrl, $sStylaBase) !== false){
            // Remove shop base url from url (should not even be there)
            $sBaseUrl = $this->getConfig()->getShopURL();
            if ($oStr->strpos($sSeoUrl, $sBaseUrl) === 0) {
                $sSeoUrl = $oStr->substr($sSeoUrl, $oStr->strlen($sBaseUrl));
            }
            
            if ($this->_getStylaFncFromUrl($sSeoUrl)) {
                return $this->_getStylaFncFromUrl($sSeoUrl);
            }

            $sSeoUrl = rtrim($sSeoUrl,'/');
            $sSeoUrl = substr($sSeoUrl,0,strrpos($sSeoUrl,'/')+1);

            $aRet = parent::decodeUrl($sSeoUrl);
        }

        return $aRet;
    }

    /**
     * Returns array with styla controller and function extracted 
     *
     * @param string $sSeoUrl
     *
     * @return string[]|bool
     */
    protected function _getStylaFncFromUrl($sSeoUrl)
    {
        if (getStr()->strpos($sSeoUrl, 'user') !== false) {
            return array(
                'cl' => 'StylaSEO_Output',
                'fnc' => 'showUser',
            );
        }

        // showCategory can override showUser
        if (getStr()->strpos($sSeoUrl, 'category') !== false) {
            return array(
                'cl' => 'StylaSEO_Output',
                'fnc' => 'showCategory',
            );
        }

        // showCategory can override showUser
        if (getStr()->strpos($sSeoUrl, 'version') !== false) {
            return array(
                'cl' => 'StylaSEO_Output',
                'fnc' => 'getPluginVersion',
            );
        }

        return false;
    }
}

