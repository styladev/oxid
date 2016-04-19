<?php

/**
 * Extends Module_Config
 */
class StylaFeed_Module_Config extends StylaFeed_Module_Config_parent
{
    /**
     * Generates and saves a unique API key when loading the config and the API key is empty
     *
     * @return string
     */
    public function render()
    {
        $sTemplate = parent::render();

        if ($this->_sModuleId === 'StylaFeed' && $this->_aViewData['confstrs']['styla_api_key'] === '') {
            $sAPIKey = oxRegistry::get('oxUtilsObject')->generateUId();
            $sModuleId = $this->_getModuleForConfigVars();
            oxRegistry::getConfig()->saveShopConfVar('str', 'styla_api_key', $sAPIKey, null, $sModuleId);

            $this->_aViewData['confstrs']['styla_api_key'] = $sAPIKey;
        }

        return $sTemplate;
    }
}