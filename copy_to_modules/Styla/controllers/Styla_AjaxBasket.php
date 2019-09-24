<?php

class Styla_AjaxBasket extends oxUBase
{
    /**
     * Return default error destination
     * If nothing is returned destination is popup which means the error isn't shown
     *
     * @return string
     */
    public function getErrorDestination()
    {
        return 'default';
    }

    /**
     * toBasket
     * -----------------------------------------------------------------------------------------------------------------
     * adds item to basket
     * outputs a json true | error string
     *
     * @throws \OxidEsales\Eshop\Core\Exception\ArticleInputException
     * @throws \OxidEsales\Eshop\Core\Exception\NoArticleException
     * @throws \OxidEsales\Eshop\Core\Exception\OutOfStockException
     * @throws oxOutOfStockException
     */
    public function toBasket(){
        $articleID = oxRegistry::getConfig()->getRequestParameter('aid');
        $amount = oxRegistry::getConfig()->getRequestParameter('am');

        try {
            oxRegistry::getConfig()->getSession()->getBasket()->addToBasket($articleID, $amount);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $json = new StdClass;
        if ($error) {
            $json->error = oxRegistry::getLang()->translateString(html_entity_decode($error));
            $json->success = false;
        } else {
            $json->success = true;
        }

        $this->_setOutputHeaders();
        oxRegistry::getUtils()->showMessageAndExit(json_encode($json));
    }

    /**
     * _setOutputHeaders
     * -----------------------------------------------------------------------------------------------------------------
     * Set headers before outputting json
     *
     * @compatibleOxidVersion 6.0
     *
     */
    protected function _setOutputHeaders()
    {
        oxRegistry::get('oxHeader')->setHeader('Content-Type: application/json');
        oxRegistry::get('oxHeader')->setHeader('Access-Control-Allow-Origin: *');
        oxRegistry::get('oxHeader')->sendHeader();
    }
}
