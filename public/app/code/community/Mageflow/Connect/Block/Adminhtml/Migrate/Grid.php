<?php

/**
 * Grid
 *
 * PHP version 5
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Block
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */

/**
 * Grid
 *
 * @category   Mageflow
 * @package    Mageflow_Connect
 * @subpackage Block
 * @author     Sven Varkel <sven@mageflow.com>
 * @license    http://mageflow.com/license/connector/eula.txt MageFlow EULA
 * @link       http://mageflow.com/
 */
class Mageflow_Connect_Block_Adminhtml_Migrate_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{

    private $_items;

    /**
     *
     * @var Mageflow_Connect_Helper_Log
     */
    private $_logger;

    /**
     *
     * @return Mageflow_Connect_Helper_Log
     */
    private function getLogger()
    {
        if (is_null($this->_logger)) {
            $this->_logger = Mage::helper('mageflow_connect/log');
        }
        return $this->_logger;
    }

    public function __construct()
    {
        parent::__construct();
//        $this->_items = Mage::getModel('mageflow_connect/data_collection');
        $this->setId('migrationGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Returns item collection
     *
     * @return Varien_Data_Collection
     */
    public function getItems()
    {
        return $this->_items;
    }

    private function createMockData()
    {
        $collection = new Varien_Data_Collection();
        $websites = Mage::app()->getWebsites();
        $stores = Mage::app()->getStores();

        for ($i = 0; $i < 10; $i++) {
            $c = new Varien_Object();
            $c->setName('Some name');
            $c->setType('cms/block');
            $randomWebsite = mt_rand(1, count($websites));
            $randomStore = mt_rand(1, count($stores));
            $c->setWebsite($websites[$randomWebsite]->getCode());
            $c->setStore($stores[$randomStore]->getCode());
            $collection->addItem($c);
        }
        return $collection;
    }

    protected function _prepareCollection()
    {
        $itemCollection = Mage::getModel('mageflow_connect/changeset_item')
            ->getCollection();
        $this->setCollection($itemCollection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('ID'),
                 'width'  => '50px',
                 'index'  => 'id',
                 'type'   => 'text',
            )
        );

        $this->addColumn(
            'type',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('Type'),
                 'index'  => 'type'
            )
        );
        $this->addColumn(
            'preview',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('Preview'),
                 'index'  => 'preview',
                 'renderer'
                          => 'Mageflow_Connect_Block_Adminhtml_Migrate_Grid_Column_Renderer',
                 'filter' => false
            )
        );
        $this->addColumn(
            'website',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('Website'),
                 'index'  => 'website'
            )
        );
        $this->addColumn(
            'created_at',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('Created at'),
                 'index'  => 'created_at'
            )
        );
        $this->addColumn(
            'store',
            array(
                 'header' => Mage::helper('mageflow_connect')->__('Store View'),
                 'index'  => 'store'
            )
        );
        $this->addColumn(
            'status',
            array(
                 'header'   => Mage::helper('mageflow_connect')->__('Status'),
                 'index'    => 'status',
                 'sortable' => true,
                 'type'     => 'options',
                 'options'  => array(
                     Mageflow_Connect_Model_Changeset_Item::STATUS_NEW
                     => Mageflow_Connect_Model_Changeset_Item::STATUS_NEW,
                     Mageflow_Connect_Model_Changeset_Item::STATUS_SENT
                     => Mageflow_Connect_Model_Changeset_Item::STATUS_SENT,
                     Mageflow_Connect_Model_Changeset_Item::STATUS_FAILED
                     => Mageflow_Connect_Model_Changeset_Item::STATUS_FAILED,
                     Mageflow_Connect_Model_Changeset_Item::STATUS_REJECTED
                     => Mageflow_Connect_Model_Changeset_Item::STATUS_REJECTED
                 ),
            ),
            'frontend_label'
        );

        $this->addColumn(
            'action',
            array(
                 'header'    => Mage::helper('mageflow_connect')->__('Action'),
                 'type'      => 'action',
                 'getter'    => 'getId',
                 'actions'   => array(
                     array(
                         'caption' => Mage::helper('mageflow_connect')->__(
                             'Push'
                         ),
                         'url'     => array('base' => '*/*/push'),
                         'field'   => 'id'
                     ),
                     array(
                         'caption' => Mage::helper('mageflow_connect')->__(
                             'Apply'
                         ),
                         'url'     => array('base' => '*/*/apply'),
                         'field'   => 'id'
                     ),
                     array(
                         'caption' => Mage::helper('mageflow_connect')->__(
                             'Discard'
                         ),
                         'url'     => array('base' => '*/*/discard'),
                         'field'   => 'id'
                     )
                 ),
                 'filter'    => false,
                 'sortable'  => false,
                 'index'     => 'stores',
                 'is_system' => true,
            )
        );

        $this->addExportType(
            '*/*/exportCsv',
            Mage::helper('customer')->__('CSV')
        );
        $this->addExportType(
            '*/*/exportXml',
            Mage::helper('customer')->__('Excel XML')
        );
        return parent::_prepareColumns();
    }

    /**
     * Returns row url
     *
     * @param Varien_Object $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        $url = $this->getUrl(
            '*/*/*'
        );
        return $url;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    public function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem(
            'push',
            array(
                 'label'   => Mage::helper('mageflow_connect')->__(
                     'Push to MageFlow'
                 ),
                 'url'     => $this->getUrl('*/*/push'),
                 'confirm' => Mage::helper('mageflow_connect')->__(
                     'Are you sure you want to
                          push these objects to MageFlow?'
                 )
            )
        );
        $this->getMassactionBlock()->addItem(
            'discard',
            array(
                 'label'   => Mage::helper('mageflow_connect')->__(
                     'Discard selected'
                 ),
                 'url'     => $this->getUrl('*/*/discard'),
                 'confirm' => Mage::helper('mageflow_connect')->__(
                     'Are you sure you want to discard these changesets?'
                 )
            )
        );

        return $this;
    }

}
