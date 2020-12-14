<?php

class Styla_Router extends Styla_Router_parent
{
    /**
     * Goal: If passed url is not found,
     * try to find any other that consist of it,
     * by removing each time the string after the last slash.
     *
     * Ex.
     * if "http://[yourwebsite.com]/magazin/tag/[tagname]" was not found
     * then render "http://[yourwebsite.com]/magazin/"
     *
     * @param string $sSeoUrl
     * @return array
     */
    public function decodeUrl($sSeoUrl)
    {
        $aReturn = parent::decodeUrl($sSeoUrl);

        $sStylaBase = Styla_Setup::STYLA_MAGAZINE_BASEDIR;
        if ($this->getConfig()->getConfigParam('styla_seo_basedir')) {
            $sStylaBase = $this->getConfig()->getConfigParam('styla_seo_basedir');
        }
        // Parent found no result and styla base (i.e. magazine) was found in URL:
        // Get result for configured base dir and let Styla_SEO handle which content to show
        if ($aReturn === false && getStr()->strpos($sSeoUrl, $sStylaBase) !== false) {
            // Fallback to magazine route
            $aReturn = parent::decodeUrl((rtrim($sStylaBase, '/') . '/'));
        }
        if($aReturn === false){
            $oDb = oxDb::getDb();
            $sBasePath = $oDb->getOne('SELECT oxseo.OXSEOURL
                FROM oxseo
                JOIN styla_paths on styla_paths.OXID = oxseo.OXOBJECTID
                WHERE ? LIKE CONCAT(oxseo.OXSEOURL,"%")',
                [$sSeoUrl]
            );
            if($sBasePath){
                $aReturn = parent::decodeUrl((rtrim($sBasePath, '/') . '/'));
                $aReturn['path'] = substr($sSeoUrl,(strlen($sBasePath)-1));
            }
        }
        
        return $aReturn;
    }
}

