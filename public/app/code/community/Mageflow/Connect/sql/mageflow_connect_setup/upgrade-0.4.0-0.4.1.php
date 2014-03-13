<?php
/**
 * This updatge script adds new attribute , mf_guid to catalog/category
 *
 *
 */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->startSetup();
$setup->addAttribute(
    'catalog_category',
    'mf_guid',
    array(
         'group'        => 'General Information',
         'input'        => 'text',
         'type'         => 'varchar',
         'label'        => 'MF guid',
         'backend'      => '',
         'visible'      => 0,
         'required'     => 0,
         'user_defined' => 1,
         'global'       =>
             Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    )
);
$setup->endSetup();