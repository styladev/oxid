<?php

class StylaFeed_Articlelist extends oxArticleList{

    public function loadArticles($currPage=1, $pageSize=10, $skuFilter='', $categoryFilter=''){
        //has module?
        $myConfig = $this->getConfig();

        $this->_aArray = array();
        $currPage = $currPage - 1;
        $values = array();

            $sArticleTable = getViewName('oxarticles');
        if ($myConfig->getConfigParam('blNewArtByInsert')) {
            $sType = 'oxinsert';
        } else {
            $sType = 'oxtimestamp';
        }
        $sSelect = "SELECT $sArticleTable.* from $sArticleTable";
        $sWhere = " WHERE $sArticleTable.oxparentid = '' AND " . $this->getBaseObject()->getSqlActiveSnippet() . " AND $sArticleTable.oxissearch = 1 AND $sArticleTable.oxpic1 != '' ";
        if($skuFilter){
            $sWhere .= ' AND $sArticleTable.oxartnum=?';
            $values[] = $skuFilter;
        }
        if($categoryFilter){
            /**
            $oCatList = oxNew('oxcategorylist');
            $oCatList->buildTree($categoryFilter);
            $cat_arr = array();
            foreach($oCatList as $cat){
                $cat_arr[] = $cat->getId();
            }
            $catids = '"'.(implode('","',$cat_arr)).'"';
            **/

            $sJoin = " JOIN oxobject2category o2c ON ".$sArticleTable.".oxid = o2c.oxobjectid JOIN oxcategories cat on cat.oxid = o2c.oxcatnid";
            $sWhere .= ' AND cat.oxid IN(';
            /**
            foreach($cat_arr as $cat){
                $sWhere .= '?,';
                $values[] = $cat;
            }
            $sWhere = rtrim($sWhere,',').')';
            **/

            // Used this query instead of the oxid tree function cause they didnt return a proper tree ...
            $sWhere .= "SELECT OXID from oxcategories WHERE OXID = '" . $categoryFilter . "' OR OXPARENTID = '" . $categoryFilter . "' OR OXROOTID = '" . $categoryFilter . "')";
        }
        $sOrder = " ORDER by $sArticleTable.".$sType." DESC ";
        if (!($iLimit = (int) $pageSize)) {
            $iLimit = $myConfig->getConfigParam('iNrofNewcomerArticles');
        }
        $iStart = $currPage;
        if ($iStart > 0) {
            $iStart = $iStart * $iLimit;
        }
        $selectString = $sSelect.$sJoin.$sWhere.$sOrder;
        $this->_aSqlLimit[0] = $iStart;
        $this->_aSqlLimit[1] = $iLimit;
        $this->selectString($selectString);
    }

    public function selectString($sSql, $values=array()){
        startProfile("loadinglists");
        $this->clear();

        $oDb = oxDb::getDb(oxDb::FETCH_MODE_ASSOC);
        if ($this->_aSqlLimit[0] || $this->_aSqlLimit[1]) {
            $rs = $oDb->selectLimit($sSql, $this->_aSqlLimit[1], $this->_aSqlLimit[0], $values);
        } else {
            $rs = $oDb->select($sSql, $values);
        }

        if ($rs != false && $rs->recordCount() > 0) {
            $oSaved = clone $this->getBaseObject();
            while (!$rs->EOF) {
                $oListObject = clone $oSaved;
                $this->_assignElement($oListObject, $rs->fields);
                $this->add($oListObject);
                $rs->moveNext();
            }
        }
        stopProfile("loadinglists");
    }

    public function add($oObject){
        if ($oObject->getId()) {
            $this->_aArray[$oObject->getId()] = $oObject;
        } else {
            $this->_aArray[] = $oObject;
        }
    }

}