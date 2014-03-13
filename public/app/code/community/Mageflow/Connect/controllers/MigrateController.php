<?php

/**
 * IndexController
 *
 * PHP version 5
 *
 * @category Mageflow
 * @package  Mageflow_Connect
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/ MageFlow Commercial Software License
 * @link     http://mageflow.com/
 */

/**
 * IndexController
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Controller
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_MigrateController
    extends Mageflow_Connect_Controller_AbstractController
{

    public function indexAction()
    {

        $this->loadLayout();
        $this->_setActiveMenu('mageflow/connect');
        $this->_addContent(
            $this->getLayout()->createBlock(
                'mageflow_connect/adminhtml_migrate',
                'connect.extensions'
            )
        );
        $this->renderLayout();
    }

    /**
     * Pushes changesets to MageFlow
     */
    public function pushAction()
    {
        $params = $this->getRequest()->getParams();
        $this->getLogger()->log($params, __METHOD__, __LINE__);


        $idList = $this->getRequest()->getParam('id', array());
        $idArr = array();
        if (is_scalar($idList)) {
            $idArr[] = $idList;
        } else {
            $idArr = $idList;
        }
        $changesetItemList = Mage::getModel('mageflow_connect/changeset_item')
            ->getCollection()
            ->addFieldToFilter(
                'id',
                array('in' => $idArr
                )
            );

        /**
         * TODO
         *
         * add changeset items to changeset
         * get client
         * client-> send changeset
         */
        $itemData = array();
        foreach ($changesetItemList as $changesetItem) {
//            $this->getLogger()->log($changesetItem->getId());

            $dataItem = array(
                'type'     => str_replace(
                    array('::', ':'),
                    '/',
                    $changesetItem->getType()
                ),
                'content'  => $changesetItem->getContent(),
                'encoding' => $changesetItem->getEncoding(),
            );
            if ($changesetItem->getMetainfo()) {
                $dataItem['metainfo'] = $changesetItem->getMetainfo();
            } else {
                $dataItem['metainfo'] = array();
            }
            $itemData[] = $dataItem;
        }
        $company = Mage::app()->getStore()->getConfig(
            \Mageflow_Connect_Model_System_Config::API_COMPANY
        );
        $data = array(
            'company'     => $company,
            'instance'    => Mage::app()->getStore()->getConfig(
                \Mageflow_Connect_Model_System_Config::API_INSTANCE_KEY
            ),
            'description' => $this->getRequest()->getParam('comment'),
            'items'       => json_encode($itemData),
        );

        $client = $this->getApiClient();
        $response = $client->post('changeset', $data);


        foreach ($changesetItemList as $changesetItem) {
            $changesetItem->setStatus(
                Mageflow_Connect_Model_Changeset_Item::STATUS_SENT
            );
            $changesetItem->setUpdatedAt(now());
            $changesetItem->save();
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Apply changeset
     */
    public function applyAction()
    {
        $params = $this->getRequest()->getParams();
        $this->getLogger()->log($params, __METHOD__, __LINE__);

        $idList = $this->getRequest()->getParam('id', array());
        $idArr = array();
        if (is_scalar($idList)) {
            $idArr[] = $idList;
        } else {
            $idArr = $idList;
        }
        $changesetItemList = Mage::getModel('mageflow_connect/changeset_item')
            ->getCollection()
            ->addFieldToFilter(
                'id',
                array('in' => $idArr
                )
            );
        $this->getLogger()->log(
            count($changesetItemList),
            __METHOD__,
            __LINE__
        );
        /**
         * TODO
         *
         * add changeset items to changeset
         * get client
         * client-> send changeset
         */
        $itemData = array();
        foreach ($changesetItemList as $changesetItem) {
//            $this->getLogger()->log($changesetItem->getId());
            $filteredData = json_decode($changesetItem->getContent(), true);
            switch ($changesetItem->getType()) {
                case "cms:block" :
                    Mage::helper('mageflow_connect/handler_cmsblock')->handle(
                        $filteredData
                    );
                    break;
                case "cms:page" :
                    Mage::helper('mageflow_connect/handler_cmspage')->handle(
                        $filteredData
                    );
                    break;
                case "catalog:category" :
                    Mage::helper('mageflow_connect/data')
                        ->catalogCategoryHandler($filteredData);
                    break;
                case "catalog:resource_eav_attribute" :
                    Mage::helper('mageflow_connect/data')
                        ->catalogAttributeHandler($filteredData);
                    break;
                case "system:configuration" :
                    Mage::helper('mageflow_connect/data')
                        ->systemConfigurationHandler($filteredData);
                    break;
                case "eav:entity_attribute_set" :
                    Mage::helper('mageflow_connect/data')->attributeSetHandler(
                        $filteredData
                    );
                    break;
            }
        }

        $this->_redirect('*/*/index');
    }

    public function gridAction()
    {
        $this->getLogger()->log($this->getRequest()->getParams());
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock(
                'mageflow_connect/adminhtml_migrate_grid'
            )->toHtml()
        );
    }

    /**
     * Discards changesets
     */
    public function discardAction()
    {
        $idList = $this->getRequest()->getParam('id', array());
        $idArr = array();
        if (is_scalar($idList)) {
            $idArr[] = $idList;
        } else {
            $idArr = $idList;
        }
        foreach ($idArr as $id) {
            $changesetItem = Mage::getModel('mageflow_connect/changeset_item')
                ->load($id);
            $changesetItem->delete();
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Returns MageFlow API client instance with
     * authentication fields filled in
     *
     * @return \Mageflow\Connect\Model\Api\Mageflow\Client
     */
    public function getApiClient()
    {
        $configuration = new stdClass();
        $configuration->_consumerKey = Mage::app()->getStore()->getConfig(
            Mageflow_Connect_Model_System_Config::API_CONSUMER_KEY
        );
        $configuration->_consumerSecret = Mage::app()->getStore()->getConfig(
            Mageflow_Connect_Model_System_Config::API_CONSUMER_SECRET
        );
        $configuration->_token = Mage::app()->getStore()->getConfig(
            Mageflow_Connect_Model_System_Config::API_TOKEN
        );
        $configuration->_tokenSecret = Mage::app()->getStore()->getConfig(
            Mageflow_Connect_Model_System_Config::API_TOKEN_SECRET
        );
        $configuration->_company = \Mage::app()->getStore()->getConfig(
            \Mageflow_Connect_Model_System_Config::API_COMPANY
        );
        $configuration->_project = \Mage::app()->getStore()->getConfig(
            \Mageflow_Connect_Model_System_Config::API_PROJECT
        );
        $configuration->_instanceKey = \Mage::app()->getStore()->getConfig(
            \Mageflow_Connect_Model_System_Config::API_INSTANCE_KEY
        );
        $client
            = new \Mageflow\Connect\Model\Api\Mageflow\Client($configuration);

        $this->getLogger()->log(
            $configuration,
            __METHOD__,
            __LINE__
        );

        return $client;
    }

}
