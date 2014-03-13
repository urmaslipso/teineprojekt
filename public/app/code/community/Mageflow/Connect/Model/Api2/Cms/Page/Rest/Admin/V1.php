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
class Mageflow_Connect_Model_Api2_Cms_Page_Rest_Admin_V1
    extends Mageflow_Connect_Model_Api2_Cms
{

    protected $_resourceType = 'cms_page';

    /**
     * Class constructor
     *
     * @return \Mageflow_Connect_Model_Api2_Cms_Page_Rest_Admin_V1
     */
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /**
     * GET request to retrieve a single CMS page
     *
     * @return array|mixed
     */
    public function _retrieve()
    {
        Mage::log(
            sprintf(
                '%s(%s): %s',
                __METHOD__,
                __LINE__,
                print_r($this->getRequest()->getParams(), true)
            )
        );
        $pageId = (int)Mage::getModel('cms/page')->checkIdentifier(
            $this->getRequest()->getParam('key'),
            $this->getRequest()->getParam('store')
        );

        $out = array();
        if ($pageId) {
            $page = Mage::getModel('cms/page')->load($pageId);
            $out = $page->getData();
        }
        Mage::log(
            sprintf(
                '%s(%s): %s',
                __METHOD__,
                __LINE__,
                print_r($out, true)
            )
        );
        return $out;
    }

    /**
     * PUT request to update a single CMS page
     *
     * @param array $filteredData
     *
     * @return array|string|void
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
     * @return Mage_Core_Model_Abstract
     */
    public function _create(array $filteredData)
    {
        Mage::helper('mageflow_connect/log')->log(
            sprintf('%s', print_r($filteredData, true))
        );

        //we shouldn't have any original data in case of creation
        $originalData = null;
        $handlerReturnArray = Mage::helper('mageflow_connect/handler_cmspage')
            ->handle($filteredData);

        if (is_null($handlerReturnArray)) {
            $this->_error("Could not save CMS page.", 10);
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
        $this->_successMessage("Successfully created new CMS page", 0, $out);
        Mage::helper('mageflow_connect/log')->log(
            sprintf('%s', print_r($out, true))
        );
        return $out;
    }

    /**
     * DELETE to delete a collection of pages
     *
     * @param array $filteredData
     */
    public function _multiDelete(array $filteredData)
    {
        Mage::helper('mageflow_connect/log')->log($filteredData);

        $pageEntity = Mage::getModel('cms/page')
            ->load($filteredData['mf_guid'], 'mf_guid');

        $originalData = $pageEntity->getData();
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
            $pageEntity->delete();
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
