<?php

class Styla_Search extends oxSearch
{
    protected $iActPage;

    public function getStylaSearchArticles($sSearchParamForQuery = false, $pageNr = 1, $pageSize = 10, $sSortBy = false)
    {
        $this->iActPage = $pageNr - 1;
        $iNrOfCatArticles = $pageSize;

        $oArtList = oxNew('Styla_Articlelist');
        $oArtList->setSqlLimit($iNrOfCatArticles * $this->iActPage, $iNrOfCatArticles);

        $sSelect = $this->_getSearchSelect($sSearchParamForQuery, false, false, false, $sSortBy);
        if ($sSelect) {
            $oArtList->selectString($sSelect);
            // Replace the select fields until the first "from" with a count
            $countQuery = preg_replace('/select (.*?) from/', 'select count(1) from', $sSelect);
            $oArtList->setTotalCount((int) oxDb::getDb()->getOne($countQuery));
        }

        return $oArtList;
    }

    /**
     * Returns the appropriate SQL select for a search according to search parameters
     * Totally taken from oxSearch replacing aSearchCols with styla_feed_search_cols
     *
     * @param bool|string $sSearchParamForQuery       query parameter
     * @param bool|string $sInitialSearchCat          initial category to search in
     * @param bool|string $sInitialSearchVendor       initial vendor to search for
     * @param bool|string $sInitialSearchManufacturer initial Manufacturer to search for
     * @param bool|string $sSortBy                    sort by
     * @return string
     */
    protected function _getSearchSelect($sSearchParamForQuery = false, $sInitialSearchCat = false, $sInitialSearchVendor = false, $sInitialSearchManufacturer = false, $sSortBy = false)
    {
        $oDb = oxDb::getDb();

        // performance
        if ($sInitialSearchCat) {
            // lets search this category - is no such category - skip all other code
            $oCategory = oxNew('oxcategory');
            $sCatTable = $oCategory->getViewName();

            $sQ = "select 1 from $sCatTable where $sCatTable.oxid = " . $oDb->quote($sInitialSearchCat) . " ";
            $sQ .= "and " . $oCategory->getSqlActiveSnippet();
            if (!$oDb->getOne($sQ)) {
                return null;
            }
        }

        // performance
        if ($sInitialSearchVendor) {
            // lets search this vendor - if no such vendor - skip all other code
            $oVendor = oxNew('oxvendor');
            $sVndTable = $oVendor->getViewName();

            $sQ = "select 1 from $sVndTable where $sVndTable.oxid = " . $oDb->quote($sInitialSearchVendor) . " ";
            $sQ .= "and " . $oVendor->getSqlActiveSnippet();
            if (!$oDb->getOne($sQ)) {
                return null;
            }
        }

        // performance
        if ($sInitialSearchManufacturer) {
            // lets search this Manufacturer - if no such Manufacturer - skip all other code
            $oManufacturer = oxNew('oxmanufacturer');
            $sManTable = $oManufacturer->getViewName();

            $sQ = "select 1 from $sManTable where $sManTable.oxid = " . $oDb->quote($sInitialSearchManufacturer) . " ";
            $sQ .= "and " . $oManufacturer->getSqlActiveSnippet();
            if (!$oDb->getOne($sQ)) {
                return null;
            }
        }

        $sWhere = null;

        if ($sSearchParamForQuery) {
            $sWhere = $this->_getWhere($sSearchParamForQuery);
        } elseif (!$sInitialSearchCat && !$sInitialSearchVendor && !$sInitialSearchManufacturer) {
            //no search string
            return null;
        }

        $oArticle = oxNew('oxarticle');
        $sArticleTable = $oArticle->getViewName();
        $sO2CView = getViewName('oxobject2category');

        $sSelectFields = $oArticle->getSelectFields();

        // longdesc field now is kept on different table
        $sDescJoin = '';
        if (is_array($aSearchCols = $this->getConfig()->getConfigParam('styla_feed_search_cols'))) {
            if (in_array('oxlongdesc', $aSearchCols) || in_array('oxtags', $aSearchCols)) {
                $sDescView = getViewName('oxartextends', $this->_iLanguage);
                $sDescJoin = " LEFT JOIN {$sDescView} ON {$sArticleTable}.oxid={$sDescView}.oxid ";
            }
        }

        //select articles
        $sSelect = "select {$sSelectFields}, {$sArticleTable}.oxtimestamp from {$sArticleTable} {$sDescJoin} where ";

        // must be additional conditions in select if searching in category
        if ($sInitialSearchCat) {
            $sCatView = getViewName('oxcategories', $this->_iLanguage);
            $sInitialSearchCatQuoted = $oDb->quote($sInitialSearchCat);
            $sSelectCat = "select oxid from {$sCatView} where oxid = $sInitialSearchCatQuoted and (oxpricefrom != '0' or oxpriceto != 0)";
            if ($oDb->getOne($sSelectCat)) {
                $sSelect = "select {$sSelectFields}, {$sArticleTable}.oxtimestamp from {$sArticleTable} $sDescJoin " .
                    "where {$sArticleTable}.oxid in ( select {$sArticleTable}.oxid as id from {$sArticleTable}, {$sO2CView} as oxobject2category, {$sCatView} as oxcategories " .
                    "where (oxobject2category.oxcatnid=$sInitialSearchCatQuoted and oxobject2category.oxobjectid={$sArticleTable}.oxid) or (oxcategories.oxid=$sInitialSearchCatQuoted and {$sArticleTable}.oxprice >= oxcategories.oxpricefrom and
                            {$sArticleTable}.oxprice <= oxcategories.oxpriceto )) and ";
            } else {
                $sSelect = "select {$sSelectFields} from {$sO2CView} as
                            oxobject2category, {$sArticleTable} {$sDescJoin} where oxobject2category.oxcatnid=$sInitialSearchCatQuoted and
                            oxobject2category.oxobjectid={$sArticleTable}.oxid and ";
            }
        }

        $sSelect .= $oArticle->getSqlActiveSnippet();
        $sSelect .= " and {$sArticleTable}.oxparentid = '' and {$sArticleTable}.oxissearch = 1 ";

        if ($sInitialSearchVendor) {
            $sSelect .= " and {$sArticleTable}.oxvendorid = " . $oDb->quote($sInitialSearchVendor) . " ";
        }

        if ($sInitialSearchManufacturer) {
            $sSelect .= " and {$sArticleTable}.oxmanufacturerid = " . $oDb->quote($sInitialSearchManufacturer) . " ";
        }

        $sSelect .= $sWhere;

        if ($sSortBy) {
            $sSelect .= " order by {$sSortBy} ";
        }

        return $sSelect;
    }

    /**
     * Forms and returns SQL query string for search in DB.
     * Totally taken from oxSearch replacing aSearchCols with styla_feed_search_cols
     *
     * @param string $sSearchString searching string
     * @return string
     */
    protected function _getWhere($sSearchString)
    {
        $oDb = oxDb::getDb();
        $oConfig = $this->getConfig();
        $blSep = false;
        $sArticleTable = getViewName('oxarticles', $this->_iLanguage);

        $aSearchCols = $oConfig->getConfigParam('styla_feed_search_cols');
        if (!(is_array($aSearchCols) && count($aSearchCols))) {
            return '';
        }

        $sSearchSep = $oConfig->getConfigParam('blSearchUseAND') ? 'and ' : 'or ';
        $aSearch = explode(' ', $sSearchString);
        $sSearch = ' and ( ';
        $myUtilsString = oxRegistry::get("oxUtilsString");

        foreach ($aSearch as $sSearchString) {

            if (!strlen($sSearchString)) {
                continue;
            }

            if ($blSep) {
                $sSearch .= $sSearchSep;
            }

            $blSep2 = false;
            $sSearch .= '( ';

            foreach ($aSearchCols as $sField) {

                if ($blSep2) {
                    $sSearch .= ' or ';
                }

                // as long description now is on different table table must differ
                if ($sField == 'oxlongdesc' || $sField == 'oxtags') {
                    $sSearchField = getViewName('oxartextends', $this->_iLanguage) . ".{$sField}";
                } else {
                    $sSearchField = "{$sArticleTable}.{$sField}";
                }

                $sSearch .= " {$sSearchField} like " . $oDb->quote("%$sSearchString%");

                // special chars ?
                if (($sUml = $myUtilsString->prepareStrForSearch($sSearchString))) {
                    $sSearch .= " or {$sSearchField} like " . $oDb->quote("%$sUml%");
                }

                $blSep2 = true;
            }
            $sSearch .= ' ) ';

            $blSep = true;
        }

        $sSearch .= ' ) ';

        return $sSearch;
    }
}
