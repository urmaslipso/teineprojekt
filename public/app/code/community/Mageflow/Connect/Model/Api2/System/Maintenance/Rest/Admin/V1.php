<?php

/**
 * V1
 *
 * PHP version 5
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @author     Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */

/**
 * Mageflow_Connect_Model_Api2_System_Maintenance_Rest_Admin_V1
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @author     Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */
class Mageflow_Connect_Model_Api2_System_Maintenance_Rest_Admin_V1
    extends Mageflow_Connect_Model_Api2_Abstract
{

    protected $_resourceType = 'system_website';

    /**
     * Class constructor
     *
     * @return \Mageflow_Connect_Model_Api2_System_Maintenance_Rest_Admin_V1
     */
    public function __construct()
    {
        return parent::__construct();
    }

    /**
     * Returns array with system info
     *
     * @return array
     */
    public function _retrieve()
    {
        $out = array();
        $out['resource_type'] = $this->_resourceType;
        $this->log($this->getRequest()->getParams());
        $mode = $this->getRequest()->getParam('mode');
        $out['items']['enabled'] = Mage::app()->getStore()->getConfig(
            'mageflow_connect/system/maintenance_mode'
        );
        $out['items']['ip_list'] = array();
        $allowIps = Mage::app()->getStore()->getConfig(
            'dev/restrict/allow_ips'
        );
        if (!is_null($allowIps)) {
            $out['items']['ip_list'] = array_map(
                'trim',
                explode(',', $allowIps)
            );
        }
        $this->log($out);

        return $this->prepareResponse($out);
    }

    public function _retrieveCollection()
    {
        return $this->_retrieve();
    }

    public function _update(array $filteredData)
    {
        $this->log(__METHOD__);
        $this->log($filteredData);
        if (isset($filteredData['items']['enabled'])) {
            $this->log(
                'setting maintenance mode to '
                . $filteredData['items']['enabled']
            );
            Mage::app()->getConfig()->saveConfig(
                'mageflow/system/maintenance_mode',
                (int)$filteredData['items']['enabled']
            );
        }
        if (isset($filteredData['items']['ip_list'])) {
            Mage::app()->getConfig()->saveConfig(
                'dev/restrict/allow_ips',
                implode(',', $filteredData['items']['ip_list'])
            );
        }

        $this->getResponse()->addMessage(
            'status',
            self::STATUS_SUCCESS,
            array(),
            Mage_Api2_Model_Response::MESSAGE_TYPE_SUCCESS
        );

        if (isset($filteredData['items']['clean_cache'])
            && $filteredData['items']['clean_cache']
        ) {
            $this->log('cleaning cache');
            Mage::app()->cleanCache();
        }
        return $filteredData;
    }

    public function _multiUpdate(array $filteredData)
    {
        $this->log(__METHOD__);
        foreach ($filteredData as $data) {
            $this->_update($data);
        }
    }

    public function _create(array $filteredData)
    {
        $this->log(__METHOD__);
        $this->log($filteredData);
        $this->getResponse()->addMessage(
            'status',
            self::STATUS_SUCCESS,
            array(),
            Mage_Api2_Model_Response::MESSAGE_TYPE_SUCCESS
        );
    }

    public function _multiCreate(array $filteredData)
    {
        $this->log(__METHOD__);
        foreach ($filteredData as $data) {
            $this->_create($data);
        }
    }

}