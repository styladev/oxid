<?php

class StylaFeed_Config extends StylaFeed_Config_parent{

    /**
     * Updates or adds new shop configuration parameters to DB.
     * Arrays must be passed not serialized, serialized values are supported just for backward compatibility.
     *
     * @param string $sVarType Variable Type
     * @param string $sVarName Variable name
     * @param mixed  $sVarVal  Variable value (can be string, integer or array)
     * @param string $sShopId  Shop ID, default is current shop
     * @param string $sModule  Module name (empty for base options)
     *
     * @return null
     */
    public function saveShopConfVar( $sVarType, $sVarName, $sVarVal, $sShopId = null, $sModule = '' )
    {
        parent::saveShopConfVar( $sVarType, $sVarName, $sVarVal, $sShopId, $sModule);

        // Mark @ BSolut - custom event listener for 'onSaveConfVar'
        if($sModule != ''){
            $moduleId = preg_replace('/^module:(.+)$/', '$1', $sModule);
            $oxModule = oxNew('oxmodule');
            $oxModule->load($moduleId);
            $aModuleEvents = $oxModule->getInfo("events");

            if ( isset( $aModuleEvents, $aModuleEvents['onSaveConfVar'] ) ) {
                $mEvent = $aModuleEvents['onSaveConfVar'];

                if ( is_callable( $mEvent ) ) {
                    call_user_func($mEvent);
                }
            }
        }
    }
}