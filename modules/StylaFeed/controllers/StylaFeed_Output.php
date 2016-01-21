<?php

class StylaFeed_Output extends oxUBase{

    protected $err = false;
    public $oModule, $resize_imagepath, $data, $util;

    public function __construct(){
        $this->_sThisTemplate = 'StylaFeed_JSON.tpl';
        $this->util = oxNew('StylaFeed_Util');
    }

    public function init(){
        parent::init();
        $this->resize_imagepath = rtrim($this->getConfig()->getPicturePath(null),'/').'/stylafeed/';
        if(!file_exists($this->resize_imagepath)){
            @mkdir($this->resize_imagepath);
        }
        $this->oModule = oxNew('oxModule');
        $this->oModule->load('StylaFeed');
    }

    public function render(){
        parent::render();
        oxRegistry::getUtils()->setHeader("Content-Type: application/json; charset=" . oxRegistry::getLang()->translateString("charset"));
        $oSmarty = oxRegistry::get("oxUtilsView")->getSmarty();

        $this->_aViewData['errmsg'] = $this->err;
        $this->_aViewData['haserror'] = $this->err!==false;
        $this->_aViewData['data'] = $this->data;

        foreach (array_keys($this->_aViewData) as $sViewName) {
            $oSmarty->assign_by_ref($sViewName, $this->_aViewData[$sViewName]);
        }

        oxRegistry::getUtils()->showMessageAndExit(
            $oSmarty->fetch($this->_sThisTemplate, $this->getViewId())
        );

    }

    public function showAll(){
        $this->_checkApiKey();

        if($this->err)
            return;

        $currPage = (int)oxRegistry::getConfig()->getRequestParameter('page');
        if(!$currPage)
            $currPage = 1;
        $pageSize = oxRegistry::getConfig()->getRequestParameter('page_size');
        if(!$pageSize)
            $pageSize = $this->getConfig()->getConfigParam('styla_page_size');
        $nameFilter = oxRegistry::getConfig()->getRequestParameter('filter');
        $skuFilter = oxRegistry::getConfig()->getRequestParameter('sku');
        $categoryFilter = oxRegistry::getConfig()->getRequestParameter('category');
        $cacheKey = 'stylafeed_all';

        if($nameFilter)
            $cacheKey .= '_'.$nameFilter;
        if($skuFilter)
            $cacheKey .= '_'.$skuFilter;
        if($categoryFilter)
            $cacheKey .= '_'.$categoryFilter;

        $cacheKey .= '_'.$currPage;

        if(!$items = $this->util->loadFromCache($cacheKey)){
            if($nameFilter){
                $oArtList = $this->_getSearchArticleList($nameFilter, $currPage, $pageSize);
            }else{
                $oArtList = oxNew('StylaFeed_Articlelist');
                $oArtList->loadArticles($currPage, $pageSize, $skuFilter, $categoryFilter);
            }
            $items = $this->_getArticleItems($oArtList);
            $this->util->saveToCache($cacheKey, $items);
        }

        $this->data['ver'] = $this->oModule->getInfo('version');
        $this->data['page'] = $currPage;
        $this->data['page_size'] = $pageSize;

        $this->data['count'] = count($items);
        $this->data['products'] = $items;

        $this->_aViewData['action'] = 'default';
    }

    public function showProduct(){
        if($this->err)
            return;

        $sku = oxRegistry::getConfig()->getRequestParameter('sku');

        if(!$product_data = $this->util->loadFromCache('stylafeed_article-'.$sku)){
            $oArticle = oxNew('oxArticle');

            // try to load the article by OXID first
            if (!$oArticle->load($sku)) {
                $sSelect = $oArticle->buildSelectString(array('oxartnum' => $sku));
                if(!$oArticle->assignRecord($sSelect)){
                    $this->err = 'PRODUCT NOT FOUND';
                    return;
                }
            }

            $product_data = $this->_getProductDetails($oArticle);
            $this->util->saveToCache('stylafeed_article-'.$sku, $product_data);
        }

        $this->data = $product_data;
        $this->_aViewData['action'] = 'product';
    }


    public function showCategories(){
        $this->_checkApiKey();

        if($this->err)
            return;

        if(!$items = $this->util->loadFromCache('stylafeed_categories')){
            $oCatList = oxNew('oxcategorylist');
            $oCatList->buildTree(null);
            $items = $this->_getCategoryItems($oCatList);
            $this->util->saveToCache('stylafeed_categories', $items);

        }
        $this->data['name'] = 'Root';
        $this->data['id'] = 0;
        $this->data['childs'] = $items;

        $this->_aViewData['action'] = 'category';
    }

    public function _checkApiKey(){
        $api_key = oxRegistry::getConfig()->getRequestParameter('key');
        if($api_key=='' || $api_key != $this->getConfig()->getConfigParam('styla_api_key')){
            $this->err = 'API KEY INVALID';
        }
    }

    protected function _getArticleItems(oxArticleList $oList){
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

            foreach($oArticle->getCategoryIds() as $cat_id){
                $category = oxNew('oxcategory');
                $category->load($cat_id);
                $oItem['category'][] = $category->oxcategories__oxtitle->value;
            }
            $oItem["sku"] = $oArticle->getId();
            $oItem["name"] = $this->_filterText($oArticle->oxarticles__oxtitle->value);
            $oItem["description"] = $this->_filterText($oArticle->getLongDesc());
            $oItem["shortdescription"] =  $this->_filterText($oArticle->oxarticles__oxshortdesc->value);
            $oItem["price"] = $sFinalPrice;
            $oItem["amount"] = $sPrice;
            $oItem["url"] = $myUtilsUrl->prepareUrlForNoSession($oArticle->getLink());
            $oItem["saleable"] = !$oArticle->isNotBuyable(); // Currently only active and in stock items are returned

            $oItem["image_org"] = $oArticle->getPictureUrl();
            $imgname = $oArticle->oxarticles__oxpic1->value;
            if(!empty($imgname)){
                $imgpath_source = $cfg->getMasterPictureDir().'product/1/'.$imgname;
            }
            $imgpath_target = $this->resize_imagepath. $oArticle->getId().'_'.$imgname;
            $iCacheTtl = $cfg->getConfigParam('styla_feed_cache_ttl');
            if(file_exists($imgpath_source) && (!file_exists($imgpath_target) || (time()-filemtime($imgpath_target)>$iCacheTtl))){ // regenerate resized images if older than cache ttl
                $resize_image = $oUtilsPic->resizeImage($imgpath_source, $imgpath_target, $cfg->getConfigParam('styla_image_width'), $cfg->getConfigParam('styla_image_height'));
                if(!$resize_image){
                    throw new oxFileException('Unable to resize image '.$imgpath_source);
                }
            }
            $resize_image_url = $cfg->getPictureUrl(null) . 'stylafeed/' . $oArticle->getId().'_'.$imgname;
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
    protected function _filterText($sText){

        $sText = html_entity_decode($sText, ENT_QUOTES, "UTF-8");
        $sText = preg_replace("/\s+/", ' ', $sText);
        $sText = strip_tags($sText);
        $sText = trim($sText);

        return $sText;
    }

    protected function _getSearchArticleList($searchTerm, $currPage=1, $pageSize=10){
        $oSearchHandler = oxNew('StylaFeed_Search');
        $oSearchList = $oSearchHandler->getStylaSearchArticles(
            $searchTerm,
            $currPage,
            $pageSize,
            $this->getSortingSql($this->getSortIdent())
        );
        return $oSearchList;
    }

    protected function _getProductDetails($oArticle){
        $myUtilsUrl = oxRegistry::get("oxUtilsUrl");
        $oLang = oxRegistry::getLang();
        $oActCur = $this->getConfig()->getActShopCurrencyObject();
        $oActCur->thousand = ''; // SMO-7 No thousand separator
        $oActCur->dec = '.'; // SMO-7 dec separator fixed to '.'

        $data = array();

        if ($oPrice = $oArticle->getPrice()) {
            $sPrice = $oLang->formatCurrency($oPrice->getBruttoPrice(), $oActCur);
            $oPrice->calculateDiscount();
            $finalPrice = $oLang->formatCurrency($oPrice->getBruttoPrice(), $oActCur);
        }

        $data["id"] = $oArticle->getId();
        $data["minqty"] = 1;
        $data["maxqty"] = $oArticle->oxarticles__oxstock->value;
        $data["price"] = $sPrice;
        $data["finalprice"] = $finalPrice;

        // Show old price if it is a single article with no variants
        if($oArticle->oxarticles__oxvarcount->value == 0){
            if ($oTPrice = $oArticle->getTPrice()) {
                $data["oldprice"] = $oLang->formatCurrency($oTPrice->getBruttoPrice(), $oActCur);
            }
        }

        $data["name"] = $this->_filterText($oArticle->oxarticles__oxtitle->value);
        $data["description"] = $this->_filterText($oArticle->oxarticles__oxshortdesc->value);
        $data["pageUrl"] = $myUtilsUrl->prepareUrlForNoSession($oArticle->getLink());
        $data["saleable"] = !$oArticle->isNotBuyable(); // Currently only active and in stock items are returned
        if (!$oArticle->isVariant()) {
            $data["attributes"] = $this->_getVariantsData($oArticle);
        }

        $extra_attrs = $this->getConfig()->getConfigParam('styla_extra_attributes');
        if($extra_attrs){
            $arr_attrs = explode(',', $extra_attrs);

            foreach($arr_attrs as $att){
                $key = 'oxarticles__ox'.$att;
                $data[$att] = $oArticle->$key->value;
            }
        }
        return $data;
    }

    /**
     * _getVariantsData
     * -----------------------------------------------------------------------------------------------------------------
     * Get information for all variants of this article
     *
     * @compatibleOxidVersion 5.2.x
     *
     * @param oxArticle $oArticle
     * @return array
     */
    protected function _getVariantsData($oArticle)
    {
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
                        $aAttributes[$sKey]['options'][$sVarSelectId]['products'][$sProductId]['saleable'] = $oVariant->isBuyable();
                    }
                }
            }
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

    protected function _getCategoryItems($oCatList){
        $ret = array();
        foreach($oCatList as $cat){
            $item = array();
            $item['name'] = $this->_filterText($cat->oxcategories__oxtitle->value);
            $item['id'] = $cat->getId();
            $item['url'] = $cat->getLink();
            $item['description'] = $this->_filterText($cat->oxcategories__oxdesc->value);
            $subcats = $cat->getSubCats();
            if(count($subcats))
                $item['childs'] = $this->_getCategoryItems($subcats);
            $ret[] = $item;
        }
        return $ret;
    }

}