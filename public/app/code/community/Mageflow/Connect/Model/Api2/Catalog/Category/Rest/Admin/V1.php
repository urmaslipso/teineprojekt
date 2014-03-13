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
class Mageflow_Connect_Model_Api2_Catalog_Category_Rest_Admin_V1
    extends Mageflow_Connect_Model_Api2_Abstract
{

    protected $_resourceType = 'catalog_category';

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
     * retrieve collection
     *
     * @return array
     */
    public function _retrieveCollection()
    {
        $out = array();
        Mage::helper('mageflow_connect/log')->log($this->getWorkingModel());
        $items = $this->getWorkingModel()->getCollection()
            ->addAttributeToSelect('*')
            ->addIsActiveFilter()->load();

        foreach ($items->getItems() as $item) {
            $c = new stdClass();
            foreach ($this->getEntityFields() as $field => $entityField) {
                $c->$field = $item->getData($entityField);
            }
            $out[] = $c;
        }
        Mage::helper('mageflow_connect/log')->log($out);
        return $out;
    }

    /**
     * update
     *
     * @param array $filteredData
     *
     * @return array|string
     */
    public function _update(array $filteredData)
    {
        Mage::helper('mageflow_connect/log')->log(sprintf('%s', $filteredData));
        return $this->_create($filteredData);
    }

    /**
     * Handles create (POST) request for cms/page
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
            ->catalogCategoryHandler($filteredData);

        if (is_null($handlerReturnArray)) {
            $this->_error("Could not save Catalog Category block.", 10);
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
        Mage::helper('mageflow_connect/system')->cacheSettings(
            $originalCacheSettings
        );
        $this->_successMessage("Successfully saved Catalog Caterogry", 0, $out);
        Mage::helper('mageflow_connect/log')->log(
            sprintf('%s', print_r($out, true))
        );
        return $out;
    }

    /**
     * multidelete
     *
     * @param array $filteredData
     */
    public function _multiDelete(array $filteredData)
    {
        Mage::helper('mageflow_connect/log')->log(sprintf('%s', $filteredData));

        $categoryCollection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('mf_guid', $filteredData['mf_guid']);
        $categoryEntity = $categoryCollection->getFirstItem();

        $groupCollection = Mage::getModel('core/store_group')
            ->getCollection()
            ->addFieldToFilter(
                'root_category_id',
                $categoryEntity->getEntityId()
            );

        Mage::helper('mageflow_connect/log')->log($groupCollection->getSize());
        if ($groupCollection->getSize() > 0) {
            $blockingStoreNames = array();
            foreach ($groupCollection as $blockingStore) {
                $blockingStoreNames[] = $blockingStore->getName();
            }
            $this->sendJsonResponse(
                [
                'delete error'    => 'Can not delete store root category',
                'blocking stores' => $blockingStoreNames
                ]
            );
            return;
        }
        Mage::helper('mageflow_connect/log')->log(sprintf('%s', 'deletable'));
        $originalData = $categoryEntity->getData();
        $rollbackFeedback = array();
        // send overwritten data to mageflow
        if ($originalData) {
            $rollbackFeedback = $this->sendRollback(
                str_replace('_', ':', $this->_resourceType),
                $filteredData,
                $originalData
            );
        } else {
            $this->sendJsonResponse(
                ['notice' => 'target not found or empty, mf_guid='
                    . $filteredData['mf_guid']]
            );
        }
        try {
            $categoryEntity->delete();
            $this->sendJsonResponse(
                array_merge(
                    ['message' =>
                        'target deleted, mf_guid=' . $filteredData['mf_guid']],
                    $rollbackFeedback
                )
            );
        } catch (Exception $e) {
            $this->sendJsonResponse(
                array_merge(
                    ['delete error' => $e->getMessage()],
                    $rollbackFeedback
                )
            );
        }
    }

}
