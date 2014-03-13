<?php

/**
 * AjaxController
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
 * AjaxController
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Controller
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_AjaxController
    extends Mageflow_Connect_Controller_AbstractController
{

    /**
     * index action
     *
     */
    public function indexAction()
    {
        $this->getResponse()->setHeader('Content-Type', 'application/json');
    }

    /**
     * Queries for enabled companies at MageFlow
     * and returns the list. It also returns URL
     * of next AJAX call
     *
     */
    public function getcompaniesAction()
    {

        Mage::helper('mageflow_connect/log')->log(
            $this->getRequest()->getParams(),
            __METHOD__,
            __LINE__
        );
        //save magento configuration fields that are set in the backend
        Mage::app()->getConfig()->saveConfig(
            Mageflow_Connect_Model_System_Config::API_ENABLED,
            1
        );
        Mage::app()->getConfig()->saveConfig(
            Mageflow_Connect_Model_System_Config::API_CONSUMER_KEY,
            $this->getRequest()->getParam('consumer_key')
        );
        Mage::app()->getConfig()->saveConfig(
            Mageflow_Connect_Model_System_Config::API_CONSUMER_SECRET,
            $this->getRequest()->getParam('consumer_secret')
        );
        Mage::app()->getConfig()
            ->saveConfig(
                Mageflow_Connect_Model_System_Config::API_TOKEN,
                $this->getRequest()->getParam('token')
            );
        Mage::app()->getConfig()->saveConfig(
            Mageflow_Connect_Model_System_Config::API_TOKEN_SECRET,
            $this->getRequest()->getParam('token_secret')
        );
        Mage::app()->getConfig()
            ->saveConfig(
                Mageflow_Connect_Model_System_Config::API_URL,
                $this->getRequest()->getParam('api_url')
            );

        $client = $this->getApiClient();
        $response = json_decode($client->get('company', array('')));

        $response->project_query_url = Mage::helper('adminhtml')
                ->getUrl('mageflow_connect/ajax/getprojects') . '?isAjax=true';
        Mage::helper('mageflow_connect/log')->log(
            $response->project_query_url,
            __METHOD__,
            __LINE__
        );
        $jsonData = Mage::helper('core')->jsonEncode($response);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    /**
     * Returns list of projects and
     * URL for instance registration
     */
    public function getprojectsAction()
    {
        Mage::helper('mageflow_connect/log')->log(
            $this->getRequest()->getParams(),
            __METHOD__,
            __LINE__
        );
        $company = (int)$this->getRequest()->getParam('company_id');
        $companyName = $this->getRequest()->getParam('company_name');
        if ($company > 0) {
            $arr = array('id' => $company, 'name' => $companyName);
            Mage::app()->getConfig()->saveConfig(
                Mageflow_Connect_Model_System_Config::API_COMPANY,
                $company
            );
            Mage::app()->getConfig()->saveConfig(
                Mageflow_Connect_Model_System_Config::API_COMPANY_NAME,
                serialize($arr)
            );
        }
        $client = $this->getApiClient();
        $response = json_decode($client->get('project'));
        Mage::helper('mageflow_connect/log')->log(
            $response,
            __METHOD__,
            __LINE__
        );

        $block = Mage::getBlockSingleton('adminhtml/template');
        $response->register_query_url = Mage::helper('adminhtml')->getUrl(
            'mageflow_connect/ajax/registerInstance',
            array('form_key' => $block->getFormKey())
        ) . '?isAjax=true';

        Mage::helper('mageflow_connect/log')->log(
            $response->register_query_url,
            __METHOD__,
            __LINE__
        );
        $jsonData = Mage::helper('core')->jsonEncode($response);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

    /**
     * Registers development instance at MageFlow
     */
    public function registerInstanceAction()
    {
        Mage::helper('mageflow_connect/log')->log(
            $this->getRequest()->getParams(),
            __METHOD__,
            __LINE__
        );

        $client = $this->getApiClient();
        $rawResponse
            = $client->post(
                'instance',
                array(
                 'company'      => $this->getRequest()->getParam(
                     'mageflow_connect_api_company'
                 ),
                 'project'      => $this->getRequest()->getParam(
                     'mageflow_connect_api_project'
                 ),
                 'type'         => 'development',
                 'instance_key' => $this->getRequest()->getParam(
                     'mageflow_connect_api_instance_key'
                 ),
                 'base_url'     => Mage::getBaseUrl(
                     Mage_Core_Model_Store::URL_TYPE_WEB,
                     true
                 ),
                 'api_url'      => Mage::getBaseUrl(
                     Mage_Core_Model_Store::URL_TYPE_WEB,
                     true
                 ) . 'api/rest/',
                )
            );

        Mage::helper('mageflow_connect/log')->log(
            $rawResponse,
            __METHOD__,
            __LINE__
        );

        $response = json_decode($rawResponse);

        Mage::helper('mageflow_connect/log')->log(
            $response,
            __METHOD__,
            __LINE__
        );

        //get instance key from client as  response
        Mage::app()->getConfig()->saveConfig(
            Mageflow_Connect_Model_System_Config::API_PROJECT,
            $this->getRequest()->getParam('mageflow_connect_api_project')
        );
        Mage::app()->getConfig()->saveConfig(
            Mageflow_Connect_Model_System_Config::API_PROJECT_NAME,
            $this->getRequest()->getParam('project_name')
        );
        Mage::app()->getConfig()->saveConfig(
            Mageflow_Connect_Model_System_Config::API_INSTANCE_KEY,
            $response->items[0]->instance_key
        );


        $jsonData = Mage::helper('core')->jsonEncode($response->items[0]);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody($jsonData);
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
        $configuration->_consumerKey = Mage::app()->getStore()
            ->getConfig(Mageflow_Connect_Model_System_Config::API_CONSUMER_KEY);
        $configuration->_consumerSecret = Mage::app()->getStore()
            ->getConfig(
                Mageflow_Connect_Model_System_Config::API_CONSUMER_SECRET
            );
        $configuration->_token = Mage::app()->getStore()->getConfig(
            Mageflow_Connect_Model_System_Config::API_TOKEN
        );
        $configuration->_tokenSecret = Mage::app()->getStore()
            ->getConfig(Mageflow_Connect_Model_System_Config::API_TOKEN_SECRET);
        $companyArr = unserialize(
            \Mage::app()->getStore()
                ->getConfig(
                    \Mageflow_Connect_Model_System_Config::API_COMPANY_NAME
                )
        );
        $configuration->_company = $companyArr['id'];
        $configuration->_project = \Mage::app()->getStore()
            ->getConfig(\Mageflow_Connect_Model_System_Config::API_PROJECT);
        $configuration->_instanceKey = \Mage::app()->getStore()
            ->getConfig(
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

    /**
     * Creates Oauth consumer on Magento's side
     * and passes that info to Mageflow so that
     * Mageflow can connect to that Magento
     */
    public function createoauthAction()
    {
        Mage::helper('mageflow_connect/log')->log(
            $this->getRequest()->getParams(),
            __METHOD__,
            __LINE__
        );
        $instanceKey = $this->getRequest()->getParam(
            'mageflow_connect_instance_key'
        );
        Mage::helper('mageflow_connect/log')->log(
            'instance key:' . $instanceKey,
            __METHOD__,
            __LINE__
        );


        $oauthHelper = Mage::helper('mageflow_connect/oauth');
        $response = $oauthHelper->createOAuthConsumer($instanceKey);

        $jsonData = Mage::helper('core')->jsonEncode($response);
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $this->getResponse()->setBody($jsonData);
    }

}
