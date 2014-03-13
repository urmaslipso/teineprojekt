<?php

/* @var $installer Mageflow_Connect_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('core/config_data');
if (!$installer->getConnection()->tableColumnExists($tableName, 'created_at')) {
    $installer->getConnection()->addColumn(
        $tableName,
        'created_at',
        'DATETIME NOT NULL'
    );
}
if (!$installer->getConnection()->tableColumnExists($tableName, 'updated_at')) {
    $installer->getConnection()->addColumn(
        $tableName,
        'updated_at',
        'DATETIME NOT NULL'
    );
}

$indexFields = array('created_at', 'updated_at');
$indexName = $installer->getConnection()->getIndexName(
    $tableName,
    $indexFields
);
if (!in_array(
    $idxName,
    $installer->getConnection()->getIndexList($tableName)
)
) {
    $installer->getConnection()->addIndex($tableName, $indexName, $indexFields);
}
$installer->endSetup();