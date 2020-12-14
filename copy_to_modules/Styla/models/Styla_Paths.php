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

class Styla_Paths extends oxi18n
{

    /** @var string Name of current class */
    protected $_sClassName = 'styla_paths';

    /** @var string Core database table name */
    protected $_sCoreTable = 'styla_paths';

    /**
     * __construct
     * -----------------------------------------------------------------------------------------------------------------
     * Constructor, initialises fields
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->init($this->_sCoreTable);
    }
    
    /**
     * loadHomePath
     * -----------------------------------------------------------------------------------------------------------------
     * loads the special home path
     *
     * @param $iLang
     *
     * @return bool
     */
    public function loadHomePath($iLang){
        return $this->loadInLang($iLang,'styla_home');
    }

}