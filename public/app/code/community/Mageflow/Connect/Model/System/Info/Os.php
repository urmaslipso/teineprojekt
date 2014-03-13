<?php

/**
 * Os
 *
 * PHP version 5
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */

/**
 * Os
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Model_System_Info_Os extends Varien_Object
{

    const OS_OSX = 'osx';
    const OS_LINUX = 'linux';

    private $_osType;

    /**
     * Class constructor
     *
     * @return Os
     */
    public function __construct()
    {
        return parent::__construct();
    }

    /**
     * Detects and returns OS type
     *
     * @return string OS Type
     */
    public function getOsType()
    {
        if (is_null($this->_osType)) {
            switch (php_uname('s')) {
                case 'Darwin':
                    $this->_osType = self::OS_OSX;
                    break;
                case 'Linux':
                    $this->_osType = self::OS_LINUX;
                default:
                    $this->_osType = 'N/A';
                    break;
            }
        }
        return $this->_osType;
    }

}
