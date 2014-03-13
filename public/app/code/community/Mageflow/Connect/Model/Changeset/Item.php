<?php

/**
 * Item
 *
 * PHP version 5
 *
 * @category   Deployment
 * @package    Application
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 */
//namespace Application;

/**
 * Item
 *
 * @category   Deployment
 * @package    Application
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 *
 *
 * @method int getId()
 * @method string getContent()
 * @method string getEncoding()
 * @method string getType()
 * @method string getStatus()
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 *
 * @method setContent($value)
 * @method setEncoding($value)
 * @method setType($value)
 * @method setStatus($value) set status to one if (new, sent, rejected, failed)
 */
class Mageflow_Connect_Model_Changeset_Item extends Mage_Core_Model_Abstract
{

    const TYPE_CMS_BLOCK = 'cms:block';
    const TYPE_CMS_PAGE = 'cms:page';
    const TYPE_SYSTEM_CONFIGURATION = 'system:configuration';
    const TYPE_SYSTEM_ADMIN_USER = 'system:admin:user';
    const TYPE_SYSTEM_ADMIN_GROUP = 'system:admin:group';
    const TYPE_CATALOG_CATEGORY = 'catalog:category';
    const TYPE_CATALOG_PRODUCT_ATTRIBUTESET = 'catalog:product:attributeset';
    const TYPE_CATALOG_PRODUCT_ATTRIBUTE = 'catalog:product:attribute';
    const TYPE_CATALOG_ATTRIBUTESET = 'eav:entity_attribute_set';
    const TYPE_CATALOG_ATTRIBUTE = 'catalog:attribute';
    const TYPE_CORE_WEBSITE = 'core:website';
    const TYPE_ADMIN_USER = 'admin:user';

    /**
     * @var string changeset statuses
     */
    const STATUS_NEW = 'new';
    const STATUS_SENT = 'sent';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FAILED = 'failed';

    /**
     * Class constructor
     *
     * @return Item
     */
    public function _construct()
    {
        $this->_init('mageflow_connect/changeset_item');
        return parent::_construct();
    }

}
