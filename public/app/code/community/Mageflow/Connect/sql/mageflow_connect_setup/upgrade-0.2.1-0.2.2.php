<?php

/* @var $installer Mageflow_Connect_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableName = 'mageflow_changeset_item';

if (!$installer->getConnection()->isTableExists(
    $installer->getTable('mageflow_connect/changeset_item')
)
) {
    $table = $installer->getConnection()->newTable($tableName)
        ->addColumn(
            'id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                 'identity' => true,
                 'unsigned' => true,
                 'nullable' => false,
                 'primary'  => true,
            ),
            'Record ID'
        )
        ->addColumn(
            'content',
            Varien_Db_Ddl_Table::TYPE_VARBINARY,
            null,
            array(
                 'nullable' => false,
                 'length'   => Varien_Db_Ddl_Table::MAX_VARBINARY_SIZE,
            ),
            'Changeset content'
        )
        ->addColumn(
            'encoding',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            24,
            array(
                 'nullable' => true,
                 'default'  => 'json'
            ),
            'Encoder/decoder of content'
        )
        ->addColumn(
            'type',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            32,
            array(
                 'nullable' => false,
            ),
            'Entity type of content'
        )
        ->addColumn(
            'status',
            Varien_Db_Ddl_Table::TYPE_VARCHAR,
            16,
            array(
                 'nullable' => false,
                 'default'  => 'new'
            ),
            'Status of changeset: new, sent, rejected, failed'
        )
        ->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_DATETIME,
            null,
            array(
                 'nullable' => false
            ),
            'Creation time'
        )
        ->addColumn(
            'updated_at',
            Varien_Db_Ddl_Table::TYPE_DATETIME,
            null,
            array(
                 'nullable' => false
            ),
            'Update time'
        )
        ->addIndex('ix_updated_at', array('updated_at'))
        ->addIndex('ix_created_at', array('created_at'))
        ->addIndex('ix_type', array('type'));
    $installer->getConnection()->createTable($table);
}
$installer->endSetup();