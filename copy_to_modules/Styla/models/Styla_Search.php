<?php

class Styla_Search extends oxSearch
{

    public function getStylaSearchArticles($sSearchParamForQuery = false, $pageNr = 1, $pageSize = 10, $sSortBy)
    {
        $this->iActPage = $pageNr - 1;
        $iNrofCatArticles = $pageSize;

        $oArtList = oxNew('Styla_Articlelist');
        $oArtList->setSqlLimit($iNrofCatArticles * $this->iActPage, $iNrofCatArticles);

        $sSelect = $this->_getSearchSelect($sSearchParamForQuery, false, false, false, $sSortBy);
        if ($sSelect) {
            $oArtList->selectString($sSelect);
        }

        return $oArtList;
    }
}
