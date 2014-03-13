<?php
/**
 * This updatge script adds new attribute , mf_guid to catalog/category
 */
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->startSetup();
$collection = Mage::getModel('catalog/category')->getCollection();
$collection->addFieldToFilter('parent_id', 0);
$collection->load();
$absoluteRoot = $collection->getFirstItem();

Mage::log(
    sprintf(
        '%s(%s): Found absolute root ID: %s',
        __METHOD__,
        __LINE__,
        $absoluteRoot->getId()
    )
);

$collection = Mage::getModel('catalog/category')->getCollection();
$collection->addFieldToFilter('parent_id', $absoluteRoot->getId());
$collection->load();

foreach ($collection as $categoryEntity) {
    if ($categoryEntity->getParentId() == $absoluteRoot->getId()) {
        $mfguid = md5($categoryEntity->getPath());
        $categoryEntity->setMfGuid($mfguid);
        $categoryEntity->save();
    }
}

$setup->endSetup();