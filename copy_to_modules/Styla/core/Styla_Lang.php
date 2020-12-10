<?php
/**
 *
 * ---------------------------------------------------------------------------------------------------------------------
 *
 * @package
 * @copyright       ©2020 norisk GmbH
 *
 * @author          Sven Beutel <sbeutel@noriskshop.de>
 */

class Styla_Lang extends Styla_Lang_parent
{
    
    /**
     * @compatibleOxidVersion 5.2.x
     *
     * Getter for all multi language tables.
     * @return array
     */
    public function getMultiLangTables(){
        $aMultilangTables = parent::getMultiLangTables();
        
        $aMultilangTables[] = 'styla_paths';
        
        return $aMultilangTables;
    }
    
}