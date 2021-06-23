<?php

class Styla_ShopControl extends Styla_ShopControl_parent
{
    /**
     * Check if the start page is configured for Styla
     * If so: Just use the styla_magazine controller directly instead of start
     *
     * @throws oxSystemComponentException
     * @return string
     */
    protected function _getStartController()
    {
        $sStart = parent::_getStartController();
        $sClass = oxRegistry::getConfig()->getRequestParameter('cl');

        $oHomePath = oxNew('Styla_Paths');
        if (!isAdmin() && !$sClass
            && $oHomePath->loadHomePath(oxRegistry::getLang()->getBaseLanguage())
            && isset($oHomePath->styla_paths__stylauser->value)
            && !empty($oHomePath->styla_paths__stylauser->value)
        ) {
            $_POST['user'] = $oHomePath->styla_paths__stylauser->value;
            oxRegistry::getConfig()->setGlobalParameter('StylaStart', true);
            $sStart = 'Styla_Magazine';
        }

        return $sStart;
    }
}
