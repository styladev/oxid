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

class Styla_PathsListController extends oxAdminList
{

    /**
     * Current class template name.
     * @var string
     */
    protected $_sThisTemplate = 'Styla_pathsAdminList.tpl';

    /**
     * Name of chosen object class (default null).
     *
     * @var string
     */
    protected $_sListClass = 'Styla_Paths';

    /**
     * Styla_Utils for SEO URL Handling
     *
     * @var object
     */
    protected $_oStylaUtil;

    function __construct()
    {
        parent::__construct();
        $this->_oStylaUtil = oxNew('Styla_Util');
    }

    /**
     * Deletes entry from the database
     */
    public function deleteEntry()
    {
        $sOxId = $this->getEditObjectId();
        $oModel = oxNew("Styla_Paths");
        if ($sOxId && $oModel->load($sOxId)) {
            $this->_oStylaUtil->deleteStylaSeo($sOxId);
            parent::deleteEntry();
        }
    }

}