<?php

/**
 * V1
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
 * V1
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Model_Api2_Ping_Rest_Admin_V1
    extends Mageflow_Connect_Model_Api2_Abstract
{

    /**
     * Class constructor
     *
     * @return \Mageflow_Connect_Model_Api2_Ping_Rest_Admin_V1
     */
    public function __construct()
    {
        return $this;
    }

    /**
     * retrieve
     *
     * @return array
     */
    public function _retrieve()
    {
        $out = array();
        $out['timestamp'] = time();
        $load = Mage::getModel('mageflow_connect/system_info_cpu')->getSystemLoad();
        $coreCount = Mage::getModel('mageflow_connect/system_info_cpu')->getCpuCores();
        $coreCount = ($coreCount > 0) ? $coreCount : 1;
        $balancedLoad = $load / $coreCount;
        $out['system_load'] = round(
            $balancedLoad,
            2
        );
        $freeDisk = disk_free_space(dirname(__FILE__));
        $totalDisk = disk_total_space(dirname(__FILE__));
        $out['free_disk'] = round(($freeDisk / $totalDisk) * 100, 2);
        $out['active_sessions'] = Mage::getModel('mageflow_connect/system_info_session')
            ->getNumberOfActiveSessions();
        $out['mfx_version'] = Mage::app()->getConfig()
            ->getNode('modules/Mageflow_Connect/version')
            ->asArray();
        return $out;
    }

}
