<?php

class Styla_Feed extends oxUBase
{
    protected $_sError = false;
    protected $_sThisTemplate = 'Styla_JSON.tpl';

    public $resize_imagepath;
    public $aData;

    /** @var oxModule */
    public $oModule;

    /** @var Styla_Util */
    public $oUtil;

    public function init()
    {
        parent::init();
        $this->resize_imagepath = rtrim($this->getConfig()->getPicturePath(null), '/') . '/stylafeed/';
        if (!file_exists($this->resize_imagepath)) {
            @mkdir($this->resize_imagepath);
        }
        $this->oModule = oxNew('oxModule');
        $this->oModule->load('Styla');
        $this->oUtil = oxNew('Styla_Util');
    }

    public function render()
    {
        parent::render();
        if ($this->_sError == 'API KEY INVALID') {
            oxRegistry::getUtils()->setHeader("HTTP/1.0 401 Unauthorized");
        }
        oxRegistry::getUtils()->setHeader("Content-Type: application/json; charset=" . oxRegistry::getLang()->translateString("charset"));

        $oSmarty = oxRegistry::get("oxUtilsView")->getSmarty();

        $this->_aViewData['errmsg'] = $this->_sError;
        $this->_aViewData['haserror'] = $this->_sError !== false;
        $this->_aViewData['data'] = $this->aData;

        foreach (array_keys($this->_aViewData) as $sViewName) {
            $oSmarty->assign_by_ref($sViewName, $this->_aViewData[$sViewName]);
        }

        oxRegistry::getUtils()->showMessageAndExit(
            $oSmarty->fetch($this->_sThisTemplate, $this->getViewId())
        );
    }

    public function showAll()
    {
        $this->_checkApiKey();

        if ($this->_sError)
            return;

        $currPage = (int) oxRegistry::getConfig()->getRequestParameter('page');
        if (!$currPage)
            $currPage = 1;
        $pageSize = oxRegistry::getConfig()->getRequestParameter('page_size');
        if (!$pageSize)
            $pageSize = $this->getConfig()->getConfigParam('styla_page_size');
        $nameFilter = oxRegistry::getConfig()->getRequestParameter('filter');
        $skuFilter = oxRegistry::getConfig()->getRequestParameter('sku');
        $categoryFilter = oxRegistry::getConfig()->getRequestParameter('category');
        $cacheKey = 'stylafeed_all';

        if ($nameFilter)
            $cacheKey .= '_' . $nameFilter;
        if ($skuFilter)
            $cacheKey .= '_' . $skuFilter;
        if ($categoryFilter)
            $cacheKey .= '_' . $categoryFilter;

        $cacheKey .= '_' . $currPage;

        if (!$items = $this->oUtil->loadFromCache($cacheKey, 'feed')) {
            if ($nameFilter) {
                $oArtList = $this->_getSearchArticleList($nameFilter, $currPage, $pageSize);
            } else {
                $oArtList = oxNew('Styla_Articlelist');
                $oArtList->loadArticles($currPage, $pageSize, $skuFilter, $categoryFilter);
            }
            $items = $this->_getArticleItems($oArtList);
            $this->oUtil->saveToCache($cacheKey, $items);
        }

        $this->aData['ver'] = $this->oModule->getInfo('version');
        $this->aData['page'] = $currPage;
        $this->aData['page_size'] = $pageSize;

        $this->aData['count'] = count($items);
        $this->aData['products'] = $items;

        $this->_aViewData['action'] = 'default';
    }

    public function showProduct()
    {
        // $this->_checkApiKey();

        if ($this->_sError)
            return;

        $sku = oxRegistry::getConfig()->getRequestParameter('sku');

        if (!$product_data = $this->oUtil->loadFromCache('stylafeed_article-' . $sku, 'feed')) {
            $oArticle = oxNew('oxArticle');

            // try to load the article by OXID first
            if (!$oArticle->load($sku)) {
                $sSelect = $oArticle->buildSelectString(array('oxartnum' => $sku));
                $sSelect .= ' ORDER BY OXPARENTID'; // Make sure the parent (if we got one) is selected first
                if (!$oArticle->assignRecord($sSelect)) {
                    $this->_sError = 'PRODUCT NOT FOUND';
                    return;
                }
            }

            $product_data = $this->_getProductDetails($oArticle);
            $this->oUtil->saveToCache('stylafeed_article-' . $sku, $product_data);
        }

        $this->aData = $product_data;
        $this->_aViewData['action'] = 'product';
    }

    public function showCategories()
    {
        $this->_checkApiKey();

        if ($this->_sError)
            return;

        if (!$items = $this->oUtil->loadFromCache('stylafeed_categories', 'feed')) {
            $oCatList = oxNew('oxcategorylist');
            $oCatList->buildTree(null);
            $items = $this->_getCategoryItems($oCatList);
            $this->oUtil->saveToCache('stylafeed_categories', $items);
        }
        $this->aData['name'] = 'Root';
        $this->aData['id'] = 0;
        $this->aData['childs'] = $items;

        $this->_aViewData['action'] = 'category';
    }

    public function showVersion()
    {
        // output as string
        die($this->oModule->getInfo('version'));
    }

    public function _checkApiKey()
    {
        $api_key = oxRegistry::getConfig()->getRequestParameter('key');
        if ($api_key == '' || $api_key != $this->getConfig()->getConfigParam('styla_api_key')) {
            $this->_sError = 'API KEY INVALID';
        }
    }

    protected function _getArticleItems(oxArticleList $oList)
    {
        $myUtilsUrl = oxRegistry::get("oxUtilsUrl");
        $aItems = array();
        $oLang = oxRegistry::getLang();
        $cfg = $this->getConfig();
        $oUtilsPic = oxRegistry::get("oxUtilsPic");

        foreach ($oList as $oArticle) {
            $oItem = array();
            $oActCur = $this->getConfig()->getActShopCurrencyObject();
            $oActCur->thousand = ''; // SMO-7 No thousand separator
            $oActCur->dec = '.'; // SMO-7 dec separator fixed to '.'

            $sPrice = '';
            $sFinalPrice = '';

            if ($oPrice = $oArticle->getPrice()) {
                $sPrice = $oLang->formatCurrency($oPrice->getBruttoPrice(), $oActCur);
                $oPrice->calculateDiscount();
                $sFinalPrice = $oLang->formatCurrency($oPrice->getBruttoPrice(), $oActCur);
            }

            foreach ($oArticle->getCategoryIds() as $cat_id) {
                $category = oxNew('oxcategory');
                $category->load($cat_id);
                $oItem['category'][] = $category->oxcategories__oxtitle->value;
            }
            $oItem["sku"] = $oArticle->getId();
            $oItem["name"] = $this->_filterText($oArticle->oxarticles__oxtitle->value);
            if ($sBrand = $this->_getArticleBrand($oArticle)) {
                $oItem["brand"] = $sBrand;
            }
            $oItem["description"] = $this->_filterText($oArticle->getLongDesc());
            $oItem["shortdescription"] = $this->_filterText($oArticle->oxarticles__oxshortdesc->value);
            $oItem["price"] = $sFinalPrice;
            $oItem["amount"] = $sPrice;
            $oItem["url"] = $myUtilsUrl->prepareUrlForNoSession($oArticle->getLink());
            $oItem["saleable"] = $oArticle->oxarticles__oxstock->value > 0; // Currently only active and in stock items are returned
            $oItem["type_id"] = $this->_hasVariants($oArticle) ? "configurable" : "simple";

            $oItem["image_org"] = $oArticle->getPictureUrl();
            $oItem["images"] = $this->_getArticleImages($oArticle);
            $imgname = $oArticle->oxarticles__oxpic1->value;
            $imgpath_source = '';
            if (!empty($imgname)) {
                $imgpath_source = $cfg->getMasterPictureDir() . 'product/1/' . $imgname;
            }
            $imgpath_target = $this->resize_imagepath . $oArticle->getId() . '_' . $imgname;
            $iCacheTtl = $cfg->getConfigParam('styla_feed_ttl');
            if (file_exists($imgpath_source) && (!file_exists($imgpath_target) || (time() - filemtime($imgpath_target) > $iCacheTtl))) { // regenerate resized images if older than cache ttl
                $resize_image = $oUtilsPic->resizeImage($imgpath_source, $imgpath_target, $cfg->getConfigParam('styla_image_width'), $cfg->getConfigParam('styla_image_height'));
                if (!$resize_image) {
                    throw new oxFileException('Unable to resize image ' . $imgpath_source);
                }
            }
            $resize_image_url = $cfg->getPictureUrl(null) . 'stylafeed/' . $oArticle->getId() . '_' . $imgname;
            $oItem["image"] = $resize_image_url;
            $aItems[] = $oItem;
        }

        return $aItems;
    }

    /**
     * Filter text from html tags and empty spaces
     *
     * @param $sText
     * @return mixed|string
     */
    protected function _filterText($sText)
    {
        $sText = html_entity_decode($sText, ENT_QUOTES, "UTF-8");
        $sText = preg_replace("/\s+/", ' ', $sText);
        $sText = strip_tags($sText);
        $sText = trim($sText);

        return $sText;
    }

    protected function _getSearchArticleList($searchTerm, $currPage = 1, $pageSize = 10)
    {
        $oSearchHandler = oxNew('Styla_Search');
        $oSearchList = $oSearchHandler->getStylaSearchArticles(
            $searchTerm,
            $currPage,
            $pageSize,
            $this->getSortingSql($this->getSortIdent())
        );
        return $oSearchList;
    }

    /**
     *
     * @param oxArticle $oArticle
     * @return array
     */
    protected function _getProductDetails($oArticle)
    {
        $myUtilsUrl = oxRegistry::get("oxUtilsUrl");
        $oLang = oxRegistry::getLang();
        $oActCur = $this->getConfig()->getActShopCurrencyObject();
        $oActCur->thousand = ''; // SMO-7 No thousand separator
        $oActCur->dec = '.'; // SMO-7 dec separator fixed to '.'

        $data = array();

        $sPrice = $finalPrice = '';
        if ($oPrice = $oArticle->getPrice()) {
            $sPrice = $oLang->formatCurrency($oPrice->getBruttoPrice(), $oActCur);
            $oPrice->calculateDiscount();
            $finalPrice = $oLang->formatCurrency($oPrice->getBruttoPrice(), $oActCur);
        }

        $data["id"] = $oArticle->getId();
        $data["price"] = $sPrice;
        $data["finalprice"] = $finalPrice;

        # Currently we only use saleable true / false as indicator not the qty, but maybe for the future
        #$data["minqty"] = 1;
        #$data["maxqty"] = $oArticle->oxarticles__oxstock->value;

        // Show old price if it is a single article with no variants
        if ($oArticle->oxarticles__oxvarcount->value == 0) {
            if ($oTPrice = $oArticle->getTPrice()) {
                $data["oldprice"] = $oLang->formatCurrency($oTPrice->getBruttoPrice(), $oActCur);
            }
        }

        $data["name"] = $this->_filterText($oArticle->oxarticles__oxtitle->value);
        if ($sBrand = $this->_getArticleBrand($oArticle)) {
            $data["brand"] = $sBrand;
        }
        $data["description"] = $this->_filterText($oArticle->oxarticles__oxshortdesc->value);
        $data["pageUrl"] = $myUtilsUrl->prepareUrlForNoSession($oArticle->getLink());
        $data["saleable"] = $oArticle->oxarticles__oxstock->value > 0; // Currently only active and in stock items are returned
        $hasVariants = $this->_hasVariants($oArticle);
        if ($hasVariants) {
            $data["attributes"] = $this->_getVariantsData($oArticle);
            # Update top-level saleable depeding on if any variant is saleable
            $data["saleable"] = $this->_isParentProductSaleable($data);
        }
        $data["type"] = $hasVariants ? "configurable" : "simple";

        $extra_attrs = $this->getConfig()->getConfigParam('styla_extra_attributes');
        if ($extra_attrs) {
            $arr_attrs = explode(',', $extra_attrs);

            foreach ($arr_attrs as $att) {
                $key = 'oxarticles__ox' . $att;
                $data[$att] = $oArticle->$key->value;
            }
        }
        return $data;
    }

    /**
     * SMO-55: Show brand in product feed
     *
     * @param oxArticle $oArticle
     * @return string
     */
    protected function _getArticleBrand($oArticle)
    {
        $sBrand = '';
        $sBrandSetting = $this->getConfig()->getConfigParam('styla_feed_brand');
        if ($sBrandSetting === 'oxmanufacturer' && ($oManufacturer = $oArticle->getManufacturer())) {
            $sBrand = $oManufacturer->getFieldData('oxtitle');
        } elseif ($sBrandSetting === 'oxvendor' && ($oVendor = $oArticle->getVendor())) {
            $sBrand = $oVendor->getFieldData('oxtitle');
        }

        return $sBrand;
    }

    /**
     * Check all variants if one is saleable.
     *
     * @param array $data
     * @return bool
     */
    protected function _isParentProductSaleable($data)
    {
        foreach ($data["attributes"] as $attrKey => $attrVal) {
            foreach ($attrVal["options"] as $optionsKey => $optionsVal) {
                foreach ($optionsVal["products"] as $productKey => $productVal) {
                    if ($productVal["saleable"] == true) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Returns true or false if product has variants or not (since $oArticle->hasVariants() always returns true).
     *
     * @param $oArticle
     * @return bool
     */
    protected function _hasVariants($oArticle)
    {
        $aVarNames = explode('|', $oArticle->oxarticles__oxvarname->value);
        foreach ($aVarNames as $sKey => $sVarName) {
            if (trim($sVarName) != '') return true;
        }
        return false;
    }

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

    /**
     * Sorts the given options array by the configured sorting setting
     *
     * @param array $aUnsortedOptions
     * @return array
     */
    protected function _getSortedOptions($aUnsortedOptions)
    {
        $aSorting = $this->_getConfiguredSorting();

        // Sorting is not configured, just return input
        if (!$aSorting) {
            return $aUnsortedOptions;
        }

        $aTmpArray = array();
        foreach ($aUnsortedOptions as $sNewKey => $aValue) {
            $aTmpArray[$sNewKey] = $aValue['label'];
        }

        $aOptions = array();

        // Sort by all values we got defined in the sorting array
        $aSorted = array_intersect($aSorting, $aTmpArray);
        $aTmpArray = array_flip($aTmpArray);
        foreach ($aSorted as $sVariantName) {
            $sNewKey = $aTmpArray[$sVariantName];
            $aOptions[$sNewKey] = $aUnsortedOptions[$sNewKey];
            unset($aTmpArray[$sVariantName]);
        }

        // Now re-add all other values at the end of the sorted array
        foreach ($aTmpArray as $sVarSelectId => $sNewKey) {
            $aOptions[$sNewKey] = $aUnsortedOptions[$sNewKey];
        }

        return $aOptions;
    }

    /**
     * Returns full sorting from config
     *
     * @return array
     */
    protected function _getConfiguredSorting()
    {
        $aSorting = $this->getConfig()->getConfigParam('styla_feed_sorting');

        $aFullSorting = array();
        foreach ($aSorting as $sSort) {
            $aFullSorting = array_merge($aFullSorting, explode(';', $sSort));
        }

        return $aFullSorting;
    }

    /**
     *
     * @param oxCategory[] $oCatList
     * @return array
     */
    protected function _getCategoryItems($oCatList)
    {
        $aReturn = array();
        foreach ($oCatList as $oCategory) {
            $aCategory = array();
            $aCategory['name'] = $this->_filterText($oCategory->oxcategories__oxtitle->value);
            $aCategory['id'] = $oCategory->getId();
            $aCategory['url'] = $oCategory->getLink();
            $aCategory['description'] = $this->_filterText($oCategory->oxcategories__oxdesc->value);
            $aSubCats = $oCategory->getSubCats();
            if (count($aSubCats)) {
                $aCategory['childs'] = $this->_getCategoryItems($aSubCats);
            }
            $aReturn[] = $aCategory;
        }

        return $aReturn;
    }

    /**
     * _getArticleImages
     * -----------------------------------------------------------------------------------------------------------------
     * Returns array of images of the given article and its variants (if possible)
     *
     * @compatibleOxidVersion 5.2.x
     * @param oxArticle $oArticle
     * @return array
     */
    public function _getArticleImages($oArticle)
    {
        $aImages = array();

        // Use parent article if given one is a variant
        if ($oArticle->isVariant()) {
            $oArticle = $oArticle->getParentArticle();
        }

        // Get images from oxpic fields from parent and variants
        for ($i = 1; $i <= 12; $i++) {
            $aImages[] = $oArticle->getMasterZoomPictureUrl($i);
        }

        // Needed to load full variants instead of oxSimpleVariant objects
        $oProperty = new ReflectionProperty($oArticle, '_blIsInList');
        $oProperty->setAccessible(true);
        $oProperty->setValue($oArticle, false);

        foreach ($oArticle->getVariants(false) as $oVariant) {
            for ($i = 0; $i <= 12; $i++) {
                $aImages[] = $oVariant->getMasterZoomPictureUrl($i);
            }
        }

        $aImages = array_filter($aImages); // Some fields will be empty, remove them
        $aImages = array_unique($aImages); // Only return each image once
        $aImages = array_values($aImages); // Reset keys

        return $aImages;
    }
}
