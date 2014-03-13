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
class Mageflow_Connect_Model_Api2_Catalog_Attribute_Rest_Admin_V1
    extends Mageflow_Connect_Model_Api2_Abstract
{

    protected $_resourceType = 'catalog_attribute';

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
     * retrieve
     *
     * @return array
     */
    public function _retrieve()
    {
        $out = array();

        try {

            $storeCollection = Mage::getModel('core/store')
                ->getCollection()
                ->load();

            $storeIdArray = array(0);
            $storeCodes = array(0 => 0);

            foreach ($storeCollection as $storeEntity) {
                $storeIdArray[] = $storeEntity->getStoreId();
                $storeCodes[$storeEntity->getStoreId()] = $storeEntity->getCode(
                );
            }

            foreach (
                Mage::getModel('eav/entity_type')
                    ->getCollection()
                    ->addFieldToFilter(
                        'entity_type_code',
                        array('catalog_product')
                    )
                    ->load()
                as $allowedEntityType
            ) {

                $collection = $this->getWorkingModel()
                    ->getCollection()
                    ->setEntityTypeFilter($allowedEntityType);

                if (($key = trim($this->getRequest()->getParam('key'))) !== ''
                ) {
                    $collection->addFieldToFilter('attribute_code', $key);
                }

                $items = $collection->load();

                //map outgoing items to array
                foreach ($items->getItems() as $item) {

                    $data = $item->getData();
                    $data['option'] = array();
                    $data['store_labels'] = array();

                    if ($item->usesSource()) {
                        foreach ($storeIdArray as $storeId) {
                            $collection = Mage::getResourceModel(
                                'eav/entity_attribute_option_collection'
                            )
                                ->setPositionOrder('asc')
                                ->setAttributeFilter($item->getId())
                                ->setStoreFilter($storeId)
                                ->load();

                            $option = $collection->toOptionArray();
                            foreach ($option as $value) {
                                $data['option']['value'][$value['value']]
                                [$storeCodes[$storeId]]
                                    = $value['label'];
                            }
                        }
                    }
                    $out[] = $data;
                }
            }

            Mage::helper('mageflow_connect/log')->log($out);
        } catch (Exception $e) {
            Mage::helper('mageflow_connect/log')->log($out);
            $this->_error('Cannot retrieve catalog/attribute', 500);
        }

        return $out;
    }

    /**
     * retrieve collection
     *
     * @return array
     */
    public function _retrieveCollection()
    {
        return $this->_retrieve();
    }

    /**
     * update
     * actually not in use
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

        //we shouldn't have any original data in case of creation
        $originalData = null;
        $handlerReturnArray = Mage::helper('mageflow_connect/data')
            ->catalogAttributeHandler($filteredData);

        if (is_null($handlerReturnArray)) {
            $this->_error("Could not save Catalog Attribute.", 10);
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
        $this->_successMessage("Successfully saved Catalog Attribute", 0, $out);
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

        $attributeCollection = Mage::getModel('eav/entity_attribute')
            ->getCollection()
            ->addFieldToFilter(
                'attribute_code',
                $filteredData['attribute_code']
            )
            ->addFieldToFilter('entity_type_id', 4);
        $attributeEntity = $attributeCollection->getFirstItem();

        $originalData = $attributeEntity->getData();
        // send overwritten data to mageflow
        $rollbackFeedback = array();
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
            $attributeEntity->delete();
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
