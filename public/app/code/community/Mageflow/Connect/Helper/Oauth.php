<?php

/**
 * @package    Mageflow
 * @subpackage Connect
 */

/**
 * MageFlow OAuth helper that deals with setting up Magento OAuth consumer
 * as well as returning MageFlow API client instance
 *
 * PLEASE READ THIS SOFTWARE LICENSE AGREEMENT ("LICENSE") CAREFULLY
 * BEFORE USING THE SOFTWARE. BY USING THE SOFTWARE, YOU ARE AGREEING
 * TO BE BOUND BY THE TERMS OF THIS LICENSE.
 * IF YOU DO NOT AGREE TO THE TERMS OF THIS LICENSE, DO NOT USE THE SOFTWARE.
 *
 * Full text of this license is available @license
 *
 * @license    http://mageflow.com/licenses/mfx/eula.txt MageFlow EULA
 * @version    1.0
 * @author     MageFlow
 * @copyright  2013 MageFlow http://mageflow.com/
 *
 * @package    Mageflow
 * @subpackage Connect
 * @category   Mageflow
 */

class Mageflow_Connect_Helper_Oauth extends Mage_Core_Helper_Abstract
{

    /**
     * Helper method to create OAuth consumer
     */
    public function createOAuthConsumer($instanceKey)
    {
        $response = new stdClass();
        $response->success = false;
        try {
            /**
             * @var Mage_Oauth_Model_Consumer
             */
            $adminUserName = $instanceKey . '_oauth';
            Mage::helper('mageflow_connect/log')->log($adminUserName);

            $adminUserModel = Mage::getModel('admin/user');
            $adminUserModel->loadByUsername($adminUserName);
            if ($adminUserModel->getId() <= 0) {
                $adminUserModel->setEmail(
                    $adminUserName . '@oauth.mageflow.com'
                );
                $adminUserModel->setUsername($adminUserName);
                $adminUserModel->setFirstname('Mageflow');
                $adminUserModel->setLastname('Consumer');
                $password = Mage::helper('mageflow_connect')->randomHash();
                $adminUserModel->setPassword($password);
                $adminUserModel->save();

                $rootRoleModel = Mage::getModel('admin/role')->getCollection()
                    ->addFilter('role_type', 'G')->addFilter('tree_level', 1)
                    ->getFirstItem();


                $adminRoleModel = Mage::getModel('admin/role');
                $adminRoleModel->setUserId($adminUserModel->getId());
                $adminRoleModel->setParentId($rootRoleModel->getId());
                $adminRoleModel->setRoleType('U');
                $adminRoleModel->setTreeLevel(2);
                $adminRoleModel->setRoleName($adminUserModel->getUsername());
                $adminRoleModel->save();

            }
            //set API2 user role
            //add creation of admin role of it does not exist
            $apiAclRole = Mage::getModel('api2/acl_global_role')->getCollection(
            )->addFilter('role_name', 'Admin')
                ->getFirstItem();

            if (!($apiAclRole instanceof Mage_Api2_Model_Acl_Global_Role)
                || !$apiAclRole->getId()
            ) {
                $apiAclRole->setRoleName('Admin');
                $apiAclRole->save();
                /**
                 * @var Mage_Api2_Model_Acl_Global_Rule
                 */
                $rule = Mage::getModel('api2/acl_global_rule');
                $collection = $rule->getCollection();
                $ruleItem = $collection->addFilterByRoleId($apiAclRole->getId())
                    ->getFirstItem();
                $ruleItem->setRoleId($apiAclRole->getId());
                $ruleItem->setResourceId(
                    Mage_Api2_Model_Acl_Global_Rule::RESOURCE_ALL
                );
                $ruleItem->save();
            }

            //save admin user to role relation
            Mage::getModel('api2/acl_global_role')
                ->getResource()->saveAdminToRoleRelation(
                    $adminUserModel->getId(),
                    $apiAclRole->getId()
                );


            $apiAclAttribute = Mage::getModel('api2/acl_filter_attribute')
                ->getCollection()
                ->addFilter('user_type', 'admin')->getFirstItem();
            if (!($apiAclAttribute instanceof
                    Mage_Api2_Model_Acl_Filter_Attribute)
                || !$apiAclAttribute->getId()
            ) {
                $apiAclAttribute->setUserType('admin');
                $apiAclAttribute->setResourceId(
                    Mage_Api2_Model_Acl_Global_Rule::RESOURCE_ALL
                );
                $apiAclAttribute->save();
            }
            $oauthConsumerModel = Mage::getModel('oauth/consumer');
            //create admin user with the same username
            $oauthConsumerModel->load($adminUserName, 'name');
            if ($adminUserModel->getId() > 0
                && $oauthConsumerModel->getId() <= 0
            ) {
                $oauthConsumerModel->setName($adminUserName);
                $oauthConsumerModel->setKey(
                    md5(Mage::helper('mageflow_connect')->randomHash())
                );
                $oauthConsumerModel->setSecret(
                    md5(Mage::helper('mageflow_connect')->randomHash())
                );
                $oauthConsumerModel->save();
                $oauthConsumerId = $oauthConsumerModel->getId();
                Mage::helper('mageflow_connect/log')->log(
                    'Created OAuth consumer with ID ' . $oauthConsumerId
                );
            }

            $token = Mage::getModel('oauth/token');
            $token->createRequestToken(
                $oauthConsumerModel->getId(),
                'http://escape.to.the.void/' . Mage::helper('mageflow_connect')
                    ->randomHash() . '/'
            );
            $token->authorize(
                $adminUserModel->getId(),
                Mage_Oauth_Model_Token::USER_TYPE_ADMIN
            );
            $token->convertToAccess();

            Mage::helper('mageflow_connect/log')->log(
                'Converted token to access token'
            );

            if ($oauthConsumerModel->getId() > 0) {
                //send registraton info and keys to MageFlow HERE
                $findClient = $this->getApiClient();
                $findRequest = 'find/Instance/instance_key/' . $instanceKey;
                Mage::helper('mageflow_connect/log')->log(
                    'Searching for existing entity: ' . $findRequest
                );

                $findResponse = $findClient->get($findRequest);
                $instanceData = json_decode($findResponse);
                Mage::helper('mageflow_connect/log')->log(
                    print_r($instanceData, true)
                );
                $instanceId = $instanceData->items[0]->id;

                if ($instanceId < 1) {
                    Mage::helper('mageflow_connect/log')->log(
                        'ERROR: Could not fetch
                        instance ID and cannot continue without it.'
                    );
                    $response->success = false;
                    $response->errrorMessage = "Could not retrieve instance ID";
                    return $response;
                }
                $key = $oauthConsumerModel->getKey();
                $data = array(
                    'consumer_key'    => $oauthConsumerModel->getKey(),
                    'consumer_secret' => $oauthConsumerModel->getSecret(),
                    'token'           => $token->getToken(),
                    'token_secret'    => $token->getSecret(),
                    'api_url'         =>
                        Mage::getBaseUrl(
                            Mage_Core_Model_Store::URL_TYPE_WEB,
                            true
                        )
                        . 'api/rest/',
                    'base_url'        => Mage::getBaseUrl(
                        Mage_Core_Model_Store::URL_TYPE_WEB,
                        true
                    ),
                );

                $client = $this->getApiClient();

                Mage::helper('mageflow_connect/log')->log(
                    'Registering OAuth consumer at MageFlow'
                );

                $encodedResponse = $client->put(
                    'instance/' . $instanceId,
                    $data
                );

                $response = json_decode($encodedResponse);

                Mage::helper('mageflow_connect/log')->log(
                    'Response: ' . print_r($response, true)
                );

                if (!empty($response)) {
                    $response->success = true;
                }
            }
        } catch (Exception $e) {
            Mage::helper('mageflow_connect/log')->log($e->getMessage());
            $response->success
                = false;
            $response->errormessage
                = $e->getMessage();
        }

        return $response;
    }

    /**
     * Returns MageFlow API client instance
     *
     * @return \Mageflow\Connect\Model\Api\Mageflow\Client
     */
    public function getApiClient()
    {
        Mage::helper('mageflow_connect/log')->log(
            'Creating and configuring MageFlow API client',
            __METHOD__,
            __LINE__
        );
        $configuration = new stdClass();

        Mage::app()->getConfig()->cleanCache();

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

        $companyArr = unserialize(
            \Mage::app()->getStore()->getConfig(
                \Mageflow_Connect_Model_System_Config::API_COMPANY_NAME
            )
        );

        $configuration->_company = $companyArr['id'];

        $configuration->_project = \Mage::app()->getStore()->getConfig(
            \Mageflow_Connect_Model_System_Config::API_PROJECT
        );

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
}