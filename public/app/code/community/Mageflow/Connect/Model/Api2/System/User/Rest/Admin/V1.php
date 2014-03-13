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
 * Mageflow_Connect_Model_Api2_System_User_Rest_Admin_V1
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @author     Urmas Lipso <urmas@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */
class Mageflow_Connect_Model_Api2_System_User_Rest_Admin_V1
    extends Mageflow_Connect_Model_Api2_Abstract
{

    protected $_resourceType = 'system_user';

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

        $this->log($this->getRequest()->getParams());
        $model = $this->getWorkingModel();
        $itemCollection = $model->getCollection();
        if ($this->getRequest()->getParam('id', 0) > 0) {
            $id = $this->getRequest()->getParam('id');
            $itemCollection->addFieldToFilter('user_id', $id);
        }
        $items = $itemCollection->toArray();
        $this->log($items);
        for ($i = 0; $i < sizeof($items['items']); $i++) {
            unset($items['items'][$i]['password']);
        }
        return $items['items'];
    }

    public function _retrieveCollection()
    {
        return $this->_retrieve();
    }

    public function _update(array $filteredData)
    {
        $this->log(__METHOD__);
        $this->log($filteredData);
        $model = $this->getWorkingModel();
        $model->load($filteredData['id']);
        unset($filteredData['id']);
        $model->setData('is_active', $filteredData['is_active']);
        $model->save();
        $this->getResponse()->addMessage(
            'status',
            self::STATUS_SUCCESS,
            array(),
            Mage_Api2_Model_Response::MESSAGE_TYPE_SUCCESS
        );
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
            ->systemUserHandler($filteredData);

        if (is_null($handlerReturnArray)) {
            $this->_error("Could not save User.", 10);
        }

        $entity = $handlerReturnArray['entity'];
        $originalData = $handlerReturnArray['original_data'];

        $rollbackFeedback = array();
        // send overwritten data to mageflow
        if (!is_null($originalData)) {
            $rollbackFeedback = $this->sendRollback(
                str_replace('_', ':', $this->_resourceType),
                $filteredData,
                $originalData
            );
        }
        $out = $entity->getData();
        $this->_successMessage("Successfully saved User", 0, $out);
        Mage::helper('mageflow_connect/system')->cacheSettings(
            $originalCacheSettings
        );
        Mage::helper('mageflow_connect/log')->log(
            sprintf('%s', print_r($out, true))
        );
        return $out;
    }
}