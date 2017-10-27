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

Styla_Feed
----------

Used for the Styla API.
Can be used to list articles of a category, search for articles or just show a single article and its variants

### Extending:

- `\Styla_Feed::_getProductDetails`
This method is given an oxArticle object
It builds the general article information which are later shown in the API (OXID, name, brand, description)
If the given article has variants `\Styla_Feed::_getVariantsData` will be called

- `\Styla_Feed::_getVariantsData`
This method is also given an oxArticle object
The method loads all variants of the given article and returns an array made up of the different OXVARSELECT options
Example: OXVARNAME = "size | color"
Resulting array will be `array(array('name'=> 'size', 'options'=>array()), array('name'=> 'color', 'options'=>array()))`

To add additional information for each variant the method has to be completely overwritten


Adding the OXSTOCK field of each variant to the data as an example:


    foreach ($aVarSelect as $iVarSelKey => $sName) {

        if ($sKey == $iVarSelKey) {
            $sName = trim($sName);
            $sVarSelectId = md5($sName);
            $aAttributes[$sKey]['options'][$sVarSelectId]['id'] = $sVarSelectId;
            $aAttributes[$sKey]['options'][$sVarSelectId]['label'] = $sName;
            $aAttributes[$sKey]['options'][$sVarSelectId]['products'][$sProductId]['id'] = $sProductId;
            $aAttributes[$sKey]['options'][$sVarSelectId]['products'][$sProductId]['price'] = $sPrice;
            $aAttributes[$sKey]['options'][$sVarSelectId]['products'][$sProductId]['oldPrice'] = $sTPrice;
            $aAttributes[$sKey]['options'][$sVarSelectId]['products'][$sProductId]['saleable'] = $oVariant->oxarticles__oxstock->value > 0;
            /** ADDED */
            $aAttributes[$sKey]['options'][$sVarSelectId]['products'][$sProductId]['stock'] = $oVariant->oxarticles__oxstock->value;
        }
    }


The full code of the extended function would then be the following:

    /**
     * Get information for all variants of this article
     *
     * @param oxArticle $oArticle
     * @return array
     */
    protected function _getVariantsData($oArticle)
    {
        // Temporarily (for the current request) set to true to enable variant loading
        // setConfigParam does not save to DB so the next page load uses the normal setting
        $this->getConfig()->setConfigParam('blLoadVariants', true);

        $aAttributes = array();

        $oParent = $oArticle;
        $aVarNames = explode('|', $oParent->oxarticles__oxvarname->value);

        $aVariants = $oParent->getFullVariants(false);
        foreach ($aVarNames as $sKey => $sVarName) {

            $sVarName = trim($sVarName);
            $aAttributes[$sKey]['id'] = md5($sVarName);
            $aAttributes[$sKey]['label'] = $sVarName;

            foreach ($aVariants as $oVariant) {
                $aVarSelect = explode('|', $oVariant->oxarticles__oxvarselect->rawValue);
                $sProductId = $oVariant->getId();
                $oLang = oxRegistry::getLang();
                $oActCur = oxRegistry::getConfig()->getActShopCurrencyObject();
                $oActCur->thousand = ''; // SMO-7 No thousand separator
                $oActCur->dec = '.'; // SMO-7 dec separator fixed to '.'

                $sPrice = '';
                $sTPrice = '';
                if ($oPrice = $oVariant->getPrice()) {
                    $sPrice = $oLang->formatCurrency($oPrice->getBruttoPrice(), $oActCur);
                }
                if ($oTPrice = $oVariant->getTPrice()) {
                    $sTPrice = $oLang->formatCurrency($oTPrice->getBruttoPrice(), $oActCur);
                }

                foreach ($aVarSelect as $iVarSelKey => $sName) {

                    if ($sKey == $iVarSelKey) {
                        $sName = trim($sName);
                        $sVarSelectId = md5($sName);
                        $aAttributes[$sKey]['options'][$sVarSelectId]['id'] = $sVarSelectId;
                        $aAttributes[$sKey]['options'][$sVarSelectId]['label'] = $sName;
                        $aAttributes[$sKey]['options'][$sVarSelectId]['products'][$sProductId]['id'] = $sProductId;
                        $aAttributes[$sKey]['options'][$sVarSelectId]['products'][$sProductId]['price'] = $sPrice;
                        $aAttributes[$sKey]['options'][$sVarSelectId]['products'][$sProductId]['oldPrice'] = $sTPrice;
                        $aAttributes[$sKey]['options'][$sVarSelectId]['products'][$sProductId]['saleable'] = $oVariant->oxarticles__oxstock->value > 0;
                        /** ADDED */
                        $aAttributes[$sKey]['options'][$sVarSelectId]['products'][$sProductId]['stock'] = $oVariant->oxarticles__oxstock->value;
                    }
                }
            }

            $aAttributes[$sKey]['options'] = $this->_getSortedOptions($aAttributes[$sKey]['options']);
        }

        // Convert elements 'options' & 'products' to not associative arrays
        // so that later on json_encode will generate arrays instead of objects
        foreach ($aAttributes as $sKey => $aAttribute) {
            $aAttributes[$sKey]['options'] = array_values($aAttribute['options']);

            if (is_array($aAttributes[$sKey]['options'])) {
                foreach ($aAttributes[$sKey]['options'] as $sVarSelId => $aOption) {
                    $aAttributes[$sKey]['options'][$sVarSelId]['products'] = array_values($aOption['products']);
                }
            }
        }

        return $aAttributes;
    }
