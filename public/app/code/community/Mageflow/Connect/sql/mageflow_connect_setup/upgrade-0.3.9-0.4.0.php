<?php

/* @var $installer Mageflow_Connect_Model_Resource_Setup */
/**
 * This update script adds special "unique id" column to each entity
 * that is manageable by MageFlow. Currently these entities are:
 * - cms block
 * - cms page
 * - configuration item
 * - catalog category
 * - backend user
 * - oauth consumers
 * - product attribute
 * - product attribute set
 * - product attribute group
 */
$installer = $this;

$installer->startSetup();

//just in case to ensure class loading ...
$dummy = Mage::getModel('mageflow_connect/types_supported');
$tablesToBeChecked = Mageflow_Connect_Model_Types_Supported::getSupportedTypes(
);

$guidColumn = 'mf_guid';
$updatedAtColumn = 'updated_at';
$createdAtColumn = 'created_at';

foreach ($tablesToBeChecked as $tableName) {
    //check for table because we have some non-table types, too
    if ($installer->tableExists($tableName)) {
        //add GUID column
        if (!$installer->getConnection()
            ->tableColumnExists($tableName, $guidColumn)
        ) {
            $installer->getConnection()->addColumn(
                $installer->getConnection()->getTableName($tableName),
                $guidColumn,
                'VARCHAR(64) NULL'
            );
            $installer->getConnection()->addIndex(
                $tableName,
                'ix_' . $guidColumn,
                array($guidColumn),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
            );
        }
        //add created_at column
        if (!$installer->getConnection()
            ->tableColumnExists($tableName, $createdAtColumn)
        ) {
            $installer->getConnection()->addColumn(
                $installer->getConnection()->getTableName($tableName),
                $createdAtColumn,
                'DATETIME NULL'
            );
        }
        //add updated_at column
        if (!$installer->getConnection()->tableColumnExists(
            $tableName,
            $updatedAtColumn
        )
        ) {
            $installer->getConnection()->addColumn(
                $installer->getConnection()->getTableName($tableName),
                $updatedAtColumn,
                'DATETIME NULL'
            );
        }
    }
}

$installer->endSetup();