<?php

/**
 * Cpu
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
 * Cpu
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Model_System_Info_Cpu extends Varien_Object
{

    /**
     * Class constructor
     *
     * @return Cpu
     */
    public function __construct()
    {
        return parent::__construct();
    }

    /**
     * Returns int number of CPU cores
     *
     * @return int
     */
    public function getCpuCores()
    {
        if (function_exists('exec')) {
            if (Mage::getModel('mageflow_connect/system_info_os')->getOsType()
                == Mageflow_Connect_Model_System_Info_Os::OS_OSX
            ) {
                $cmd = '/usr/sbin/sysctl -n hw.ncpu';
                $retval = exec($cmd, $out);
                $ret = (int)$out[0];
                Mage::helper('mageflow_connect/log')->log($out);
                return $ret;
            } elseif (Mage::getModel('mageflow_connect/system_info_os')
                    ->getOsType()
                == Mageflow_Connect_Model_System_Info_Os::OS_LINUX
            ) {
                $cmd = '/usr/bin/nproc';
                $retval = exec($cmd, $out);
                $ret = (int)$out[0];
                Mage::helper('mageflow_connect/log')->log($out);
                return $ret;
            }
        }
        return -1;
    }

    /**
     * Returns average CPU load
     * of last 5 minutes divided by
     * number of CPU cores to get "actual"
     * system load
     *
     * @return real
     */
    public function getSystemLoad()
    {
        $arr = sys_getloadavg();
        Mage::helper('mageflow_connect/log')->log($arr);
        return $arr[1];
    }

}
