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

        if($aRet === false && $oStr->strpos($sSeoUrl, $sStylaBase) !== false){
            # Fallback to magazine route
            $aRet = parent::decodeUrl((rtrim($sStylaBase, '/').'/'));
        }

        return $aRet;
    }
}

