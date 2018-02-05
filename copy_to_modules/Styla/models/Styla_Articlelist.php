<?php

class Styla_Articlelist extends oxArticleList
{
    /**
     * Loads all relevant articles into the current list object
     *
     * @param int    $currPage       The current page
     * @param int    $pageSize       The page size
     * @param string $skuFilter      A single SKU / OXARTNUM
     * @param string $categoryFilter A single oxCategory ID
     */
    public function loadArticles($currPage = 1, $pageSize = 10, $skuFilter = '', $categoryFilter = '')
    {
        $config = $this->getConfig();
        $db = oxDb::getDb();

        $this->_aArray = array();
        $currPage = $currPage - 1;

        $join = '';
        $articleTable = getViewName('oxarticles');
        $type = 'oxtimestamp';
        if ($config->getConfigParam('blNewArtByInsert')) {
            $type = 'oxinsert';
        }

        $select = "SELECT $articleTable.* from $articleTable";
        // SMO-75: Explicitly NO active check on the articles
        $where = " WHERE $articleTable.oxparentid = '' AND $articleTable.oxissearch = 1 ";

        if ($skuFilter) {
            $where .= " AND $articleTable.oxartnum=" . $db->quote($skuFilter);
        }

        if ($categoryFilter) {
            $join = " JOIN oxobject2category o2c ON " . $articleTable . ".oxid = o2c.oxobjectid JOIN oxcategories cat on cat.oxid = o2c.oxcatnid";

            // SMO-75: Search all subcategories of the given category
            $searchSubCategories = $this->getConfig()->getConfigParam('styla_feed_search_subcategories');
            if ($searchSubCategories) {
                $allCategories = $this->_getSubCategories($categoryFilter);
                $allCategories = $db->quoteArray($allCategories);

                $where .= ' AND cat.oxid IN(' . implode(',', $allCategories) . ')';
            } else {
                $quotedCategory = $db->quote($categoryFilter);
                // Used this query instead of the oxid tree function cause they didnt return a proper tree ...
                $where .= " AND cat.oxid IN(SELECT OXID from oxcategories WHERE OXID = $quotedCategory OR OXPARENTID = $quotedCategory OR OXROOTID = $quotedCategory)";
            }
        }

        $orderBy = " ORDER by $articleTable." . $type . " DESC ";
        if (!($limit = (int) $pageSize)) {
            $limit = $config->getConfigParam('iNrofNewcomerArticles');
        }
        $start = $currPage;
        if ($start > 0) {
            $start = $start * $limit;
        }

        $selectString = $select . $join . $where . $orderBy;
        $this->_aSqlLimit[0] = $start;
        $this->_aSqlLimit[1] = $limit;
        $this->selectString($selectString);
    }

    /**
     * Returns array of all subcategories of the given category
     *
     * @param string $categoryId
     * @return array
     */
    protected function _getSubCategories($categoryId)
    {
        $db = oxDb::getDb();
        $subCategoriesQuery = 'SELECT OXID FROM oxcategories WHERE OXPARENTID in (%s)';

        $allCategories = array($categoryId);
        $categories = array($categoryId);

        do {
            $categories = $db->quoteArray($categories);
            $categories = $db->getCol(sprintf($subCategoriesQuery, implode(', ', $categories)));

            $allCategories = array_merge($allCategories, $categories);
        } while (count($categories));

        return $allCategories;
    }
}
