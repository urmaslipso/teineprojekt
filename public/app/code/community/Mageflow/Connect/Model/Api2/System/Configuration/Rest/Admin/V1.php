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
 * Mageflow_Connect_Model_Api2_System_Configuration_Rest_Admin_V1
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Model
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Model_Api2_System_Configuration_Rest_Admin_V1
    extends Mageflow_Connect_Model_Api2_Abstract
{

    protected $_resourceType = 'system_configuration';

    /**
     * Class constructor
     *
     * @return V1
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
        $resourceModel = Mage::getModel('core/config_data');
        $itemCollection = $resourceModel->getCollection();
        if ($this->getRequest()->getParam('path', null)) {
            $path = str_replace(
                ':',
                '/',
                $this->getRequest()->getParam('path', null)
            );

            $itemCollection->addFieldToFilter('path', $path);
        }
        $scopeId = $this->getRequest()->getParam('scope_id', null);
        if (!is_null($scopeId)) {
            $itemCollection->addFieldToFilter('scope_id', $scopeId);
        }
        $configId = $this->getRequest()->getParam('id', null);
        if (!is_null($configId)) {
            Mage::helper('mageflow_connect/log')->log($configId);
            $itemCollection->addFieldToFilter('config_id', $configId);
        }
        $items = $itemCollection->load();

        foreach ($items->getItems() as $item) {
            $c = array();
            foreach ($item->getData() as $field => $entityField) {
                $c[$field] = $entityField;
            }
            $out[] = $c;
        }
        Mage::helper('mageflow_connect/log')->log($out);
        return $out;
    }

    public function _retrieveCollection()
    {
        return $this->_retrieve();
    }

    public function _update(array $filteredData)
    {
        Mage::helper('mageflow_connect/log')->log(sprintf('%s', $filteredData));
        return $this->_create($filteredData);
    }

    /**
     * Handles create (POST) request
     *
     * @param array $filteredData
     *
     * @return array|string
     */
    public function _create(array $filteredData)
    {
        Mage::helper('mageflow_connect/log')->log(
            sprintf('%s', print_r($filteredData, true))
        );
        $originalCacheSettings = Mage::helper('mageflow_connect/system')
            ->cacheSettings();
        Mage::helper('mageflow_connect/system')->cacheSettings(
            array('all' => 0)
        );
        //we shouldn't have any original data in case of creation
        $originalData = null;
        $handlerReturnArray = Mage::helper('mageflow_connect/data')
            ->systemConfigurationHandler($filteredData);

        if (is_null($handlerReturnArray)) {
            $this->_error("Could not save System Configuration.", 10);
        }

        $out = $handlerReturnArray['entity'];
        $originalData = $handlerReturnArray['original_data'];

        $rollbackFeed = array();
        // send overwritten data to mageflow
        if (!is_null($originalData)) {
            $rollbackFeedback = $this->sendRollback(
                str_replace('_', ':', $this->_resourceType),
                $filteredData,
                $originalData
            );
        }
        Mage::helper('mageflow_connect/system')->cacheSettings(
            $originalCacheSettings
        );
        $this->_successMessage(
            "Successfully saved System Configuration",
            0,
            $out
        );
        Mage::helper('mageflow_connect/log')->log(
            sprintf('%s', print_r($out, true))
        );
        return $out;
    }
}
