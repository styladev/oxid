Extending
=========


Implementing own search and article list logic for the product feed


Styla_Articlelist
---------------------

Used when the product feed is not searched for specific keywords or when filtering for SKUs or categories

### Extending:

The public method `\Styla_Articlelist::loadArticles` has to be extended with the OXID extension logic.
The method assigns (via `$this->selectString($selectString)`) all products to the Styla_Articlelist.
The extension has to build a full SELECT query and call `$this->selectString` with the query.

The currently used implementation can be found in `modules/Styla/models/Styla_Articlelist.php:5`

    public function loadArticles($currPage = 1, $pageSize = 10, $skuFilter = '', $categoryFilter = '')



Styla_Search
----------------

Used when the product feed is searched for a specific keyword 

### Extending:  

The protected method `\Styla_Search::_getSearchSelect` has to be extended with the OXID extension logic.
The method returns the full string for the search query.

The currently used implementation can be found in `application/models/oxsearch.php:121`

    protected function _getSearchSelect($sSearchParamForQuery = false, 
                                        $sInitialSearchCat = false, 
                                        $sInitialSearchVendor = false, 
                                        $sInitialSearchManufacturer = false, 
                                        $sSortBy = false)

