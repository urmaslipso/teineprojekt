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
class Mageflow_Connect_PullgridController
    extends Mageflow_Connect_Controller_AbstractController
{

    public function indexAction()
    {
        $company = Mage::app()->getStore()->getConfig(
            \Mageflow_Connect_Model_System_Config::API_COMPANY
        );
        $project = Mage::app()->getStore()->getConfig(
            \Mageflow_Connect_Model_System_Config::API_PROJECT
        );
        $data = array(
            'company' => $company,
            'project' => $project
        );

        $client = $this->getApiClient();
        $response = $client->get('changeset', $data);
        $changesetData = json_decode($response, true)['items'];

        $itemCollection = new Varien_Data_Collection();

        foreach ($changesetData as $changeset) {
            $data['id'] = $changeset['id'];
            $response = $client->get('changeset', $data);
            $changesetItemData = json_decode($response, true);
            foreach (
                $changesetItemData['items'][0]['items'] as $changesetItem
            ) {
                $itemCollection->addItem(
                    new Varien_Object(
                        [
                        'id'        => $changesetItem['id'],
                        'changeset' => $changeset['name'],
                        'type'      => $changesetItem['type']
                        ]
                    )
                );
            }
        }

        $this->loadLayout();
        $this->_setActiveMenu('mageflow/connect');
        $this->_addContent(
            $this->getLayout()->createBlock(
                'mageflow_connect/adminhtml_pullgrid',
                'connect.pullgrid'
            )
        );

        $this->getLayout()->getBlock('connect.pullgrid')->setData(
            'itemdata',
            $itemCollection
        );
        $this->renderLayout();
    }

    /**
     * Apply changeset
     */
    public function pullAction()
    {
        $params = $this->getRequest()->getParams();
        $this->getLogger()->log($params, __METHOD__, __LINE__);

        $company = Mage::app()->getStore()->getConfig(
            \Mageflow_Connect_Model_System_Config::API_COMPANY
        );
        $data = array(
            'company' => $company,
        );

        $client = $this->getApiClient();

        $idList = $this->getRequest()->getParam('id', array());
        $idArr = array();
        if (is_scalar($idList)) {
            $idArr[] = $idList;
        } else {
            $idArr = $idList;
        }
        $this->getLogger()->log($idArr, __METHOD__, __LINE__);
        foreach ($idArr as $id) {
            $data['id'] = $id;
            $response = $client->get('changesetitem', $data);

            $item = json_decode($response, true)['items'][0];
            $this->getLogger()->log($item, __METHOD__, __LINE__);
            $filteredData = json_decode($item['content'], true);
            $this->getLogger()->log($filteredData, __METHOD__, __LINE__);

            switch ($item['type']) {
                case "cms/block" :
                    Mage::helper('mageflow_connect/handler_cmsblock')->handle(
                        $filteredData
                    );
                    break;
                case "cms/page" :
                    Mage::helper('mageflow_connect/handler_cmspage')->handle(
                        $filteredData
                    );
                    break;
                case "catalog/category" :
                    Mage::helper('mageflow_connect/data')
                        ->catalogCategoryHandler($filteredData);
                    break;
                case "catalog/resource_eav_attribute" :
                    Mage::helper('mageflow_connect/data')
                        ->catalogAttributeHandler($filteredData);
                    break;
                case "system/configuration" :
                    Mage::helper('mageflow_connect/data')
                        ->systemConfigurationHandler($filteredData);
                    break;
                case "eav/entity_attribute_set" :
                    Mage::helper('mageflow_connect/data')->attributeSetHandler(
                        $filteredData
                    );
                    break;
            }
        }
        die;
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

        Mage::helper('mageflow_connect/log')->log(
            $configuration,
            __METHOD__,
            __LINE__
        );

        return $client;
    }

}
