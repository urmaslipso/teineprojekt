<?php

/**
 * Data
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
 * Data
 *
 * @category Mageflow
 * @package  Mageflow_Connect
 * @author   Sven Varkel <sven@mageflow.com>
 * @license  http://mageflow.com/ MageFlow Commercial Software License
 * @link     http://mageflow.com/
 */
class Mageflow_Connect_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Returns hash of pretty random bytes
     *
     * @return string
     */
    public function randomHash()
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            return sha1(openssl_random_pseudo_bytes(64));
        }

        return md5(uniqid(uniqid(mt_rand(0, PHP_INT_MAX), true), true));
    }


    /**
     * update or create catalog/category from data array
     *
     * @param $filteredData
     *
     * @return array|null
     */
    public function catalogCategoryHandler($filteredData)
    {
        $itemModel = null;

        $catalogCollection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('mf_guid', $filteredData['mf_guid']);
        $itemModel = $catalogCollection->getFirstItem();

        $originalData = null;
        if (!is_null($itemModel)) {
            $itemModel = Mage::getModel('catalog/category');
        } else {
            $originalData = $itemModel->getData();
        }

        if ($itemModel->getData('entity_id')) {
            $filteredData['entity_id'] = $itemModel->getData('entity_id');
        }

        $originalPath = $filteredData['path'];
        unset($filteredData['path']);

        $rootCategory = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('parent_id', 0)
            ->load()
            ->getFirstItem();

        $parentCategory = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('mf_guid', $filteredData['parent_id'])
            ->load()
            ->getFirstItem();

        Mage::helper('mageflow_connect/log')->log(
            $rootCategory->getEntityId()
        );
        Mage::helper('mageflow_connect/log')->log(
            $parentCategory->getEntityId()
        );
        if ($parentCategory->getEntityId() == 0) {
            Mage::helper('mageflow_connect/log')->log('parent was not found');
            Mage::helper('mageflow_connect/log')->log(
                $filteredData['parent_id']
            );
            $parentId = $rootCategory->getEntityId();
            Mage::helper('mageflow_connect/log')->log('replacing parent');
            Mage::helper('mageflow_connect/log')->log(
                $filteredData['parent_id']
            );
        } else {
            $parentId = $parentCategory->getEntityId();
        }
        $mfGuid = $filteredData['mf_guid'];

        Mage::helper('mageflow_connect/log')->log($filteredData);
        $savedEntity = $this->saveItem($itemModel, $filteredData);
        $filteredData = $savedEntity->getData();
        Mage::helper('mageflow_connect/log')->log($filteredData);
        $savedEntity->setMfGuid($mfGuid);
        $savedEntity->move($parentId, $parentId);
        $filteredData = $savedEntity->getData();
        Mage::helper('mageflow_connect/log')->log($filteredData);
        $savedEntity->save();

        if ($savedEntity instanceof Mage_Catalog_Model_Category) {
            return array(
                'entity'        => $savedEntity,
                'original_data' => $originalData
            );
        }
        Mage::helper('mageflow_connect/log')->log(
            "Error occurred while tried to save Catalog Category.
            Data follows:\n"
            . print_r($filteredData, true)
        );
        return null;
    }

    /**
     * update or create catalog/resource_eav_attribute from data array
     *
     * @param $filteredData
     *
     * @return array|null
     */
    public function catalogAttributeHandler($filteredData)
    {
        $itemFoundByIdentifier = false;
        $itemFoundByMfGuid = false;
        $foundItemsMatch = false;
        $itemModel = false;


        $itemModelByIdentifier = Mage::getModel('eav/entity_attribute')
            ->load($filteredData['attribute_code'], 'attribute_code');
        $itemModelByMfGuid = Mage::getModel('eav/entity_attribute')
            ->load($filteredData['mf_guid'], 'mf_guid');

        if ($itemModelByIdentifier->getAttributeId()) {
            $itemFoundByIdentifier = true;
        }
        if ($itemModelByMfGuid->getAttributeId()) {
            $itemFoundByMfGuid = true;
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid) {
            $idByIdent = $itemModelByIdentifier->getAttributeId();
            $idByGuid = $itemModelByMfGuid->getAttributeId();

            Mage::helper('mageflow_connect/log')->log(
                'by mf_guid ' . $idByGuid
            );
            Mage::helper('mageflow_connect/log')->log('by ident ' . $idByIdent);

            if ($idByGuid == $idByIdent) {
                $foundItemsMatch = true;
            }
        }

        if ($itemFoundByIdentifier && !$itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 01');
            $itemModel = $itemModelByIdentifier;
            $filteredData['attribute_id'] = $itemModel->getAttributeId();
        }
        if (!$itemFoundByIdentifier && $itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 10 - error');
            //$itemModel = $itemModelByMfGuid;
            $filteredData['attribute_id'] = $itemModel->getAttributeId();
        }
        if (!$itemFoundByIdentifier && !$itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 00');
            $itemModel = Mage::getModel('catalog/resource_eav_attribute');
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid && $foundItemsMatch) {
            Mage::helper('mageflow_connect/log')->log('case 11-1');
            $itemModel = $itemModelByMfGuid;
            $filteredData['attribute_id'] = $itemModel->getAttributeId();
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid && !$foundItemsMatch) {
            Mage::helper('mageflow_connect/log')->log('case 11-0 error');
            //$itemModel = $itemModelByMfGuid;
            $filteredData['attribute_id'] = $itemModel->getAttributeId();
        }

        $originalData = null;
        $originalOptionValues = array();
        $originalOptionOrder = array();
        $originalDefaults = null;

        if (!is_null($itemModel)) {
            $originalData = $itemModel->getData();
            Mage::helper('mageflow_connect/log')->log($originalData);

            $originalDefaults = array
            (
                'default'                => array(
                    $originalData['default_value']
                ),
                'default_value'          => $originalData['default_value'],
                'default_value_text'     => $originalData['default_value'],
                'default_value_yesno'    => $originalData['default_value'],
                'default_value_textarea' => $originalData['default_value'],
            );

            Mage::helper('mageflow_connect/log')->log($originalDefaults);

            $storeCollection = Mage::getModel('core/store')
                ->getCollection()
                ->load();

            $originalOptionCollection = Mage::getModel(
                'eav/entity_attribute_option'
            )
                ->getCollection()
                ->addFieldToFilter('attribute_id', $itemModel->getAttributeId())
                ->load();

            foreach ($originalOptionCollection as $optionEntity) {
                foreach ($storeCollection as $storeEntity) {
                    $valueCollection = Mage::getModel(
                        'eav/entity_attribute_option'
                    )
                        ->getCollection()
                        ->setStoreFilter($storeEntity->getStoreId())
                        ->join(
                            'attribute',
                            'attribute.attribute_id=main_table.attribute_id',
                            'attribute_code'
                        )
                        ->addFieldToFilter(
                            'main_table.option_id',
                            array('eq' => $optionEntity->getOptionId())
                        )
                        ->load();

                    foreach ($valueCollection as $value) {
                        Mage::helper('mageflow_connect/log')->log(
                            print_r($value->getData(), true)
                        );
                        $originalOptionValues[$optionEntity->getOptionId()][0]
                            = $value->getDefaultValue();
                        $originalOptionValues[$optionEntity->getOptionId(
                        )][$storeEntity->getStoreId()]
                            = $value->getValue();
                        $originalOptionOrder[$optionEntity->getOptionId()]
                            = $value->getSortOrder();
                    }
                }
            }
        }

        Mage::helper('mageflow_connect/log')->log(
            print_r($originalOptionValues, true)
        );
        Mage::helper('mageflow_connect/log')->log(
            print_r($originalOptionOrder, true)
        );

        if (isset($filteredData['store_labels'])) {
            foreach ($filteredData['store_labels'] as $key => $label) {
                if ($key != "0") {
                    $storeEntity = Mage::getModel('core/store')
                        ->load($key, 'code');
                    $filteredData['store_labels'][$storeEntity->getId()]
                        = $label;
                    unset($filteredData['store_labels'][$key]);
                }
            }
        }

        $optionArray = null;

        if (isset($filteredData['option'])) {
            foreach (
                $filteredData['option']['value'] as $valueSetKey => $valueSet
            ) {
                foreach ($valueSet as $key => $value) {
                    if ($key != "0") {
                        $storeEntity = Mage::getModel('core/store')
                            ->load($key, 'code');
                        $filteredData['option']['value'][$valueSetKey]
                        [$storeEntity->getId()]
                            = $value;
                        unset(
                        $filteredData['option']['value'][$valueSetKey][$key]
                        );
                    }
                }
            }
            $optionArray = $filteredData['option'];
            unset($filteredData['option']);
        }


        if (count($originalOptionValues)) {
            $filteredData['option']['value'] = $originalOptionValues;
            $filteredData['option']['order'] = $originalOptionOrder;
        }

        Mage::helper('mageflow_connect/log')->log('first iteration');
        Mage::helper('mageflow_connect/log')->log(
            sprintf('%s', print_r($filteredData, true))
        );
        $savedEntity = $this->saveItem($itemModel, $filteredData);

        $attributeId = $savedEntity->getAttributeId();
        $filteredData = $savedEntity->getData();

// start rebuilding new option values

        if (!is_null($originalDefaults)) {
            $filteredData = array_merge($filteredData, $originalDefaults);
        }

        if (!is_null($optionArray)) {
            foreach ($optionArray['value'] as $key => $optionArrayValue) {

                $duplicateOption = false;
                foreach ($filteredData['option']['value'] as $existingOption) {
                    if ($existingOption == $optionArray['value'][$key]) {
                        $duplicateOption = true;
                    }
                }

                if ($duplicateOption) {
                    continue;
                }

                $optionEntity = Mage::getModel('eav/entity_attribute_option');
                $optionEntity->setData(
                    array
                    (
                    'attribute_id' => $attributeId,
                    'sort_order'   => $optionArray['order'][$key]
                    )
                );
                $optionEntity->save();

                $filteredData['option']['value'][$optionEntity->getOptionId()]
                    = $optionArray['value'][$key];

                $filteredData['option']['order'][$optionEntity->getOptionId()]
                    = $optionArray['order'][$key];

                if ($filteredData['default'][0] == $key) {

                    $filteredData['default']
                        = array($optionEntity->getOptionId());
                    $filteredData['default_value_text']
                        = $optionEntity->getOptionId();
                    $filteredData['default_value_yesno']
                        = $optionEntity->getOptionId();
                    $filteredData['default_value_textarea']
                        = $optionEntity->getOptionId();

                }
            }
        }

        Mage::helper('mageflow_connect/log')->log(
            sprintf('%s', print_r($filteredData, true))
        );
        Mage::helper('mageflow_connect/log')->log(get_class($itemModel));
        $savedEntity = $this->saveItem($itemModel, $filteredData);
        Mage::helper('mageflow_connect/log')->log(get_class($savedEntity));

        if ($savedEntity instanceof Mage_Eav_Model_Entity_Attribute) {
            return array(
                'entity'        => $savedEntity,
                'original_data' => $originalData
            );
        }
        Mage::helper('mageflow_connect/log')->log(
            "Error occurred while tried to save
            Catalog Attribute. Data follows:\n"
            . print_r($filteredData, true)
        );
        return null;
    }

    /**
     * create or update core/config_data from data array
     *
     * @param $filteredData
     *
     * @return array|null
     */
    public function systemConfigurationHandler($filteredData)
    {
        $itemModel = null;

        switch ($filteredData['scope']) {
            case 'default':
                $oldValue = Mage::app()->getStore()
                    ->getConfig($filteredData['path']);
                Mage::helper('mageflow_connect/log')->log($oldValue);
                $scopeId = 0;
                break;
            case 'websites':
                $website = Mage::getModel('core/website')
                    ->load($filteredData['website_code'], 'code');
                $oldValue = $website->getConfig($filteredData['path']);
                Mage::helper('mageflow_connect/log')->log($oldValue);
                $scopeId = $website->getWebsiteId();
                break;
            case 'stores':
                $store = Mage::getModel('core/store')
                    ->load($filteredData['store_code'], 'code');
                $oldValue = $store->getConfig($filteredData['path']);
                Mage::helper('mageflow_connect/log')->log($oldValue);
                $scopeId = $store->getStoreId();
                break;
        }

        $originalData = null;
        if (!is_null($oldValue)) {
            $originalData = $filteredData;
            $originalData['value'] = $oldValue;
        }

        Mage::helper('mageflow_connect/log')->log($scopeId);
        try {
            Mage::getModel('core/config')->saveConfig(
                $filteredData['path'],
                $filteredData['value'],
                $filteredData['scope'],
                $scopeId
            );
            Mage::helper('mageflow_connect/log')
                ->log('Config saved');
            return array(
                'entity'        => $filteredData,
                'original_data' => $originalData
            );
        } catch (Exception $e) {
            Mage::helper('mageflow_connect/log')->log(
                sprintf(
                    'Error occurred while saving item: %s',
                    $e->getMessage()
                )
            );
        }
        return null;
    }

    /**
     * create or update eav/entity_attribute_set from data array
     * all attributes used by attribute set must exist already
     * on update, pre-existing attribute groups shall
     * be deleted & new groups created
     *
     * @param $filteredData
     *
     * @return array|null
     */
    public function attributeSetHandler($filteredData)
    {
        $itemFoundByIdentifier = false;
        $itemFoundByMfGuid = false;
        $foundItemsMatch = false;
        $itemModel = false;


        $itemModelByIdentifier = Mage::getModel('eav/entity_attribute_set')
            ->load($filteredData['attribute_set_name'], 'attribute_set_name');
        $itemModelByMfGuid = Mage::getModel('eav/entity_attribute_set')
            ->load($filteredData['mf_guid'], 'mf_guid');

        if ($itemModelByIdentifier->getAttributeSetId()) {
            $itemFoundByIdentifier = true;
        }
        if ($itemModelByMfGuid->getAttributeIdSet()) {
            $itemFoundByMfGuid = true;
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid) {
            $idByIdent = $itemModelByIdentifier->getAttributeId();
            $idByGuid = $itemModelByMfGuid->getAttributeId();

            if ($idByGuid == $idByIdent) {
                $foundItemsMatch = true;
            }
        }

        if ($itemFoundByIdentifier && !$itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 01');
            $itemModel = $itemModelByIdentifier;
        }
        if (!$itemFoundByIdentifier && $itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 10');
            $itemModel = $itemModelByMfGuid;
        }
        if (!$itemFoundByIdentifier && !$itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 00');
            $itemModel = Mage::getModel('eav/entity_attribute_set');
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid && $foundItemsMatch) {
            Mage::helper('mageflow_connect/log')->log('case 11-1');
            $itemModel = $itemModelByMfGuid;
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid && !$foundItemsMatch) {
            Mage::helper('mageflow_connect/log')->log('case 11-0 error');
            //$itemModel = $itemModelByMfGuid;
        }

        $originalData = null;

        if ($itemModel->getData()) {
            $itemModel->save();
            $dummyChangeset = $this->createChangesetFromItem(
                'Mage_Eav_Model_Entity_Attribute_Set',
                $itemModel->getData()
            );
            $originalData = $dummyChangeset->getData();
        }

        if ($itemModel) {
            $allAttributesAreOk = true;

            // go over all Attributes in all Groups
            //and verify, if they are present
            // we can create Groups, but not Attributes

            foreach ($filteredData['groups'] as $group) {
                foreach ($group['attributes'] as $attribute) {

                    $attributeFoundByIdentifier = false;
                    $attributeFoundByMfGuid = false;
                    $foundAttributesMatch = false;

                    $attributeModelByMfGuid = Mage::getModel(
                        'eav/entity_attribute'
                    )
                        ->load($attribute['mf_guid'], 'mf_guid');
                    Mage::helper('mageflow_connect/log')->log(
                        $attributeModelByMfGuid
                    );

                    $attributeCollection = Mage::getModel(
                        'eav/entity_attribute'
                    )
                        ->getCollection()
                        ->addFieldToFilter(
                            'attribute_code',
                            $attribute['attribute_code']
                        )
                        ->addFieldToFilter('entity_type_id', 4);
                    $attributeModelByIdentifier
                        = $attributeCollection->getFirstItem();

                    if ($attributeModelByIdentifier->getAttributeId()) {
                        $attributeFoundByIdentifier = true;
                    }
                    if ($attributeModelByMfGuid->getAttributeId()) {
                        $attributeFoundByMfGuid = true;
                    }
                    if ($attributeFoundByIdentifier
                        && $attributeFoundByMfGuid
                    ) {
                        $idByIdent
                            = $attributeModelByIdentifier->getAttributeId();
                        $idByGuid = $attributeModelByMfGuid->getAttributeId();

                        Mage::helper('mageflow_connect/log')->log(
                            'by mf_guid ' . $idByGuid
                        );
                        Mage::helper('mageflow_connect/log')->log(
                            'by ident ' . $idByIdent
                        );

                        if ($idByGuid == $idByIdent) {
                            $foundAttributesMatch = true;
                        }
                    }

                    if ((!$attributeFoundByIdentifier
                            && $attributeFoundByMfGuid)
                        || (!$attributeFoundByIdentifier
                            && !$attributeFoundByMfGuid)
                        || ($attributeFoundByIdentifier
                            && $attributeFoundByMfGuid
                            && !$foundAttributesMatch)
                    ) {
                        $allAttributesAreOk = false;
                        Mage::helper('mageflow_connect/log')->log(
                            'attributes are not ok'
                        );
                        /*
                        Mage::helper('mageflow_connect/log')->log(
                            $attributeModelByMfGuid
                        );
                        Mage::helper('mageflow_connect/log')->log(
                            $attributeModelByIdentifier
                        );
                        */
                    }
                }
            }
            // we have verified all attributes

            if ($allAttributesAreOk) {
                // attributes are ok

                // we need id for the attribute set

                if (!$itemModel->getAttributeSetId()) {
                    $attributeSetData = $filteredData;
                    $attributeSetData['groups'] = array();

                    $itemModel->setData($attributeSetData);
                    $itemModel->save();
                } else {
                    $attributeGroupCollection = Mage::getModel(
                        'eav/entity_attribute_group'
                    )
                        ->getCollection()
                        ->addFieldToFilter(
                            'attribute_set_id',
                            $itemModel->getAttributeSetId()
                        );
                    foreach ($attributeGroupCollection as $attributeGroup) {
                        $attributeGroup->delete();
                    }
                }


                $attributeSetData = array(
                    'groups' => array()
                );

                foreach ($filteredData['groups'] as $group) {
                    $attributeGroupCollection = Mage::getModel(
                        'eav/entity_attribute_group'
                    )
                        ->getCollection()
                        ->addFieldToFilter(
                            'attribute_group_name',
                            $group['attribute_group_name']
                        )
                        ->addFieldToFilter(
                            'attribute_set_id',
                            $itemModel->getAttributeSetId()
                        );
                    $attributeGroup = $attributeGroupCollection->getFirstItem();

                    if (!$attributeGroup->getAttributeGroupId()) {
                        $attributeGroup = Mage::getModel(
                            'eav/entity_attribute_group'
                        );
                    }

                    $groupData = $group;
                    $groupData['attribute_set_id']
                        = $itemModel->getAttributeSetId();
                    unset($groupData['attributes']);
                    $attributeGroup->setData($groupData);
                    $attributeGroup->save();
                    $groupData['attributes'] = array();

                    foreach ($group['attributes'] as $attribute) {
                        Mage::helper('mageflow_connect/log')->log(
                            'attribute code ' . $attribute['attribute_code']
                        );

                        $attributeModelByMfGuid = Mage::getModel(
                            'eav/entity_attribute'
                        )
                            ->load($attribute['mf_guid'], 'mf_guid');

                        $attributeCollection = Mage::getModel(
                            'eav/entity_attribute'
                        )
                            ->getCollection()
                            ->addFieldToFilter(
                                'attribute_code',
                                $attribute['attribute_code']
                            )
                            ->addFieldToFilter('entity_type_id', 4);
                        $attributeModelByIdentifier
                            = $attributeCollection->getFirstItem();

                        if ($attributeModelByMfGuid->getAttributeId()) {
                            Mage::helper('mageflow_connect/log')->log(
                                'attribute by mf_guid'
                            );
                            $groupData['attributes'][]
                                = $attributeModelByMfGuid;
                            $attributeModelByMfGuid->setAttributeSetId(
                                $itemModel->getAttributeSetId()
                            );
                            $attributeModelByMfGuid->setAttributeGroupId(
                                $attributeGroup->getAttributeGroupId()
                            );
                            $attributeModelByMfGuid->save();
                        } else {
                            Mage::helper('mageflow_connect/log')->log(
                                'attribute by identifier'
                            );
                            $groupData['attributes'][]
                                = $attributeModelByIdentifier;
                            $attributeModelByIdentifier->setAttributeSetId(
                                $itemModel->getAttributeSetId()
                            );
                            $attributeModelByIdentifier->setAttributeGroupId(
                                $attributeGroup->getAttributeGroupId()
                            );
                            $attributeModelByIdentifier->save();
                        }
                    }
                    $attributeGroup->setAttributes($groupData['attributes']);
                    $attributeGroup->save();
                    $attributeSetData['groups'][] = $attributeGroup;
                }

                $itemModel->setGroups($attributeSetData['groups']);
                $itemModel->save();
                Mage::helper('mageflow_connect/log')->log($itemModel);

                return array(
                    'entity'        => $itemModel,
                    'original_data' => $originalData
                );
            }
        }
        Mage::helper('mageflow_connect/log')->log(
            "Error occurred while tried to save CMS page. Data follows:\n"
            . print_r($filteredData, true)
        );
        return null;
    }

    /**
     * create or update website from changeset
     * all used categories must already exist with correct mf_guid's
     *
     * @param $filteredData
     *
     * @throws Exception
     * @return array|null
     */
    public function systemWebsiteHandler($filteredData)
    {
        $categoryIdList = array();
        foreach ($filteredData['groups'] as $group) {
            $categoryIdList[] = $group['root_category_id'];
        }
        $catalogCollection = Mage::getModel('catalog/category')
            ->getCollection()
            ->addFieldToFilter('mf_guid', $categoryIdList);
        $foundCategories = $catalogCollection->getSize();

        //FIXME it may not be necessary to have a category
//        if ($foundCategories != count($categoryIdList)) {
//            throw new Exception('Specified root category not found');
//            return null;
//        }

        $websiteEntity = Mage::getModel('core/website')
            ->load($filteredData['code'], 'code');

        $originalData = null;
        if (!is_null($websiteEntity)) {
            $originalData = $websiteEntity->getData();
        }

        $websiteEntity->setCode($filteredData['code']);
        $websiteEntity->setName($filteredData['name']);
        $websiteEntity->setSortOrder($filteredData['sort_order']);
        $websiteEntity->setIsDefault($filteredData['is_default']);
        $websiteEntity->save();

        Mage::helper('mageflow_connect/log')->log(
            sprintf(
                'Saved website with ID %s',
                print_r($websiteEntity->getId(), true)
            )
        );

        foreach ($filteredData['groups'] as $group) {
            $groupCollection = Mage::getModel('core/store_group')
                ->getCollection()
                ->addFieldToFilter('name', $group['name'])
                ->addFieldToFilter(
                    'website_id',
                    $websiteEntity->getWebsiteId()
                );

            $groupEntity = Mage::getModel('core/store_group')
                ->load($groupCollection->getFirstItem()->getGroupId());

            $groupEntity->setName($group['name']);

            $catalogCollection = Mage::getModel('catalog/category')
                ->getCollection()
                ->addFieldToFilter('mf_guid', $group['root_category_id']);
            $rootCategory = $catalogCollection->getFirstItem();
            $groupEntity->setRootCategoryId($rootCategory->getEntityId());
            $groupEntity->setWebsiteId($websiteEntity->getWebsiteId());
            $groupEntity->save();

            if ($groupEntity->getName() == $filteredData['default_group_id']) {
                $websiteEntity->setDefaultGroupId($groupEntity->getGroupId());
                $websiteEntity->save();
            }

            foreach ($group['stores'] as $store) {
                $storeEntity = Mage::getModel('core/store')
                    ->load($store['code'], 'code');

                $storeEntity->setCode($store['code']);
                $storeEntity->setName($store['name']);
                $storeEntity->setSortOrder($store['sort_order']);
                $storeEntity->setIsActive($store['is_active']);
                $storeEntity->setWebsiteId($websiteEntity->getWebsiteId());
                $storeEntity->setGroupId($groupEntity->getGroupId());
                $storeEntity->save();

                if ($storeEntity->getCode() == $group['default_store_id']) {
                    $groupEntity->setDefaultStoreId($storeEntity->getStoreId());
                }

            }
        }
        Mage::helper('mageflow_connect/log')->log(get_class($websiteEntity));

        if ($websiteEntity instanceof Mage_Core_Model_Website) {
            return array(
                'entity'        => $websiteEntity,
                'original_data' => $originalData
            );
        }
        Mage::helper('mageflow_connect/log')->log(
            "Error occurred while tried to save Website. Data follows:\n"
            . print_r($filteredData, true)
        );
        return null;

    }

    /**
     * update or create  from data array
     *
     * @param $filteredData
     *
     * @return array|null
     */
    public function systemUserHandler($filteredData)
    {
        $itemFoundByIdentifier = false;
        $itemFoundByMfGuid = false;
        $foundItemsMatch = false;
        $itemModel = null;

        $itemModelByIdentifier = Mage::getModel('admin/user')
            ->load($filteredData['username'], 'username');
        $itemModelByMfGuid = Mage::getModel('admin/user')
            ->load($filteredData['mf_guid'], 'mf_guid');

        if ($itemModelByIdentifier->getUserId()) {
            $itemFoundByIdentifier = true;
        }
        if ($itemModelByMfGuid->getUserId()) {
            $itemFoundByMfGuid = true;
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid) {
            $idByIdent = $itemModelByIdentifier->getUserId();
            $idByGuid = $itemModelByMfGuid->getUserId();

            Mage::helper('mageflow_connect/log')->log(
                'by mf_guid ' . $idByGuid
            );
            Mage::helper('mageflow_connect/log')->log('by ident ' . $idByIdent);

            if ($idByGuid == $idByIdent) {
                $foundItemsMatch = true;
            }
        }

        if ($itemFoundByIdentifier && !$itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 01');
            $itemModel = $itemModelByIdentifier;
            $filteredData['user_id'] = $itemModel->getUserId();
        }
        if (!$itemFoundByIdentifier && $itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 10');
            $itemModel = $itemModelByMfGuid;
            $filteredData['user_id'] = $itemModel->getUserId();
        }
        if (!$itemFoundByIdentifier && !$itemFoundByMfGuid) {
            Mage::helper('mageflow_connect/log')->log('case 00');
            $itemModel = Mage::getModel('admin/user');
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid && $foundItemsMatch) {
            Mage::helper('mageflow_connect/log')->log('case 11-1');
            $itemModel = $itemModelByMfGuid;
            $filteredData['user_id'] = $itemModel->getUserId();
        }
        if ($itemFoundByIdentifier && $itemFoundByMfGuid && !$foundItemsMatch) {
            Mage::helper('mageflow_connect/log')->log('case 11-0');
            $itemModel = $itemModelByMfGuid;
            $filteredData['user_id'] = $itemModel->getUserId();
        }

        $originalData = null;
        if (!is_null($itemModel)) {
            $originalData = $itemModel->getData();
        }

        Mage::helper('mageflow_connect/log')->log($originalData);

        foreach ($filteredData['roles'] as $key => $roleName) {
            $roleEntity = Mage::getModel('admin/role')
                ->load($roleName, 'role_name');
            Mage::helper('mageflow_connect/log')->log($roleEntity);
            if ($roleEntity->getRoleName() != '') {
                $filteredData['roles'][$key] = $roleEntity->getRoleId();
            } else {
                unset($filteredData['roles'][$key]);
            }
        }

        $savedEntity = $this->saveItem($itemModel, $filteredData);
        if ($savedEntity instanceof Mage_Admin_Model_User) {
            return array(
                'entity'        => $savedEntity,
                'original_data' => $originalData
            );
        }
        Mage::helper('mageflow_connect/log')->log(
            "Error occurred while tried to save User. Data follows:\n"
            . print_r($filteredData, true)
        );
        return null;
    }

    /**
     * sets data from array and saves object
     *
     * @param $itemModel
     * @param $filteredData
     *
     * @return array
     */
    public function saveItem($itemModel, $filteredData)
    {
        if (is_null($itemModel)) {
            return null;
        }

        try {
            $itemModel->setData($filteredData);
            $itemModel->save();
            Mage::helper('mageflow_connect/log')
                ->log(sprintf('Saved item with ID %s', $itemModel->getId()));
        } catch (Exception $e) {
            Mage::helper('mageflow_connect/log')->log(
                sprintf(
                    'Error occurred while saving item: %s',
                    $e->getMessage()
                )
            );
            Mage::helper('mageflow_connect/log')->log($e->getTraceAsString());
            return null;
        }
        return $itemModel;
    }

    /**
     * create changesetitem object of type from content
     * type must be with ":", like "cms:block"
     * content must be array from getData()
     *
     * @param $type
     * @param $content
     *
     * @return mixed
     */
    public function createChangesetFromItem($type, $content)
    {
        $changesetItem = Mage::getModel('mageflow_connect/changeset_item');
        $now = new Zend_Date();

        if (isset($content['block_id'])) {
            unset($content['block_id']);
        }
        if (isset($content['page_id'])) {
            unset($content['page_id']);
        }
        if (isset($content['attribute_id'])) {
            unset($content['attribute_id']);
        }
        if (isset($content['entity_id'])) {
            unset($content['entity_id']);
        }
        if (isset($content['config_id'])) {
            unset($content['config_id']);
        }

        if (isset($content['user_id'])) {
            unset($content['user_id']);
        }

        Mage::helper('mageflow_connect/log')->log($type);
        Mage::helper('mageflow_connect/log')->log($content);

        if ($type == 'base_url') {
            Mage::helper('mageflow_connect/log')->log('base_url change');

            $company = Mage::app()->getStore()->getConfig(
                \Mageflow_Connect_Model_System_Config::API_COMPANY
            );
            $data = array(
                'command'      => 'change base url',
                'company'      => $company,
                'value'        => $content['value'],
                'path'         => $content['path'],
                'value'        => $content['value'],
                'scope'        => $content['scope'],
                'scope_id'     => $content['scope_id'],
                'website_code' => $content['website_code'],
                'store_code'   => $content['store_code']
            );

            $instanceKey = \Mage::app()->getStore()
                ->getConfig(
                    \Mageflow_Connect_Model_System_Config::API_INSTANCE_KEY
                );
            $client = $this->getApiClient();
            $response = $client->put('instance/' . $instanceKey, $data);

            Mage::helper('mageflow_connect/log')->log('client added');
            return false;
        }

        if ($type == 'Mage_Eav_Model_Entity_Attribute_Set'
            || $type == 'eav:entity_attribute_set'
            || $type == 'catalog:attribute_set'
        ) {
            $originalContent = $content;
            Mage::helper('mageflow_connect/log')->log($content);

            $attributeGroupCollection = Mage::getModel(
                'eav/entity_attribute_group'
            )
                ->getCollection()
                ->addFieldToFilter(
                    'attribute_set_id',
                    $content['attribute_set_id']
                );

            if (isset($content['groups'])) {
                unset($content['groups']);
            }
            $groups = array();
            foreach ($attributeGroupCollection as $group) {
                Mage::helper('mageflow_connect/log')->log($group);
                $attributes = array();
                $actualAttributes = Mage::getModel('eav/entity_attribute')
                    ->getCollection()
                    ->setAttributeGroupFilter(
                        $group->getAttributeGroupId()
                    );
                foreach ($actualAttributes as $actualAttribute) {
                    $attributes[] = array(
                        'attribute_code' => $actualAttribute->getData(
                        )['attribute_code'],
                        'mf_guid'        => $actualAttribute->getData(
                        )['mf_guid']
                    );
                }

                $attributeGroup = Mage::getModel('eav/entity_attribute_group')
                    ->load(
                        $group->getData()['attribute_group_id'],
                        'attribute_group_id'
                    );
                Mage::helper('mageflow_connect/log')->log(
                    $attributeGroup->getData()
                );
                $data = $attributeGroup->getData();

                if (isset($data['attribute_group_id'])) {
                    unset($data['attribute_group_id']);
                }
                if (isset($data['attribute_set_id'])) {
                    unset($data['attribute_set_id']);
                }
                if (isset($data['attributes'])) {
                    unset($data['attributes']);
                }

                $data['attributes'] = $attributes;
                $groups[] = $data;
            }
            if (isset($content['attribute_set_id'])) {
                unset($content['attribute_set_id']);
            }

            $content['groups'] = $groups;
            foreach ($originalContent['remove_attributes'] as $attribute) {
                $content['remove_attributes'][] = $attribute->getData();
            }
            Mage::helper('mageflow_connect/log')->log($content);
        }

        if ($type == 'Mage_Core_Model_Store' || $type == 'core:store'
            || $type == 'Mage_Core_Model_Website'
            || $type == 'core:website'
            || $type == 'Mage_Core_Model_Store_Group'
            || $type == 'core:store_group'
        ) {

            $type = 'core:website';

            $website = Mage::getModel('core/website')
                ->load($content['website_id']);

            $content = $website->getData();
            $groups = array();
            $groupCollection = Mage::getModel('core/store_group')
                ->getCollection()
                ->addFieldToFilter('website_id', $website->getWebsiteId());

            foreach ($groupCollection as $group) {
                $stores = array();
                $storeCollection = Mage::getModel('core/store')
                    ->getCollection()
                    ->addFieldToFilter('group_id', $group->getGroupId());

                foreach ($storeCollection as $store) {
                    $storeData = $store->getData();
                    unset($storeData['store_id']);
                    unset($storeData['website_id']);
                    unset($storeData['group_id']);

                    $stores[] = $storeData;
                }

                $groupData = $group->getData();
                unset($groupData['website_id']);
                unset($groupData['group_id']);
                $groupData['stores'] = $stores;
                $rootCategory = Mage::getModel('catalog/category')
                    ->load($groupData['root_category_id']);
                $defaultStore = Mage::getModel('core/store')
                    ->load($groupData['default_store_id']);

                $groupData['root_category_id'] = $rootCategory->getMfGuid();
                $groupData['default_store_id'] = $defaultStore->getCode();
                $groups[] = $groupData;
            }

            $content = $website->getData();
            $content['groups'] = $groups;
            unset($content['website_id']);

            $defaultGroup = Mage::getModel('core/store_group')
                ->load($content['default_group_id']);
            $content['default_group_id'] = $defaultGroup->getName();
        }

        if ($type == 'Mage_Core_Model_Config_Data'
            || $type == 'system:configuration'
        ) {
            Mage::helper('mageflow_connect/log')->log(print_r($content, true));
            $cleanedContent = array
            (
                'group_id'     => $content['group_id'],
                'store_code'   => $content['store_code'],
                'website_code' => $content['website_code'],
                'scope'        => $content['scope'],
                'scope_id'     => $content['scope_id'],
                'path'         => $content['path'],
                'value'        => $content['value'],
                'updated_at'   => $content['updated_at'],
                'created_at'   => $content['created_at'],
                'mf_guid'      => $content['mf_guid'],
            );
            $content = $cleanedContent;
        }

        if ($type == 'Mage_Admin_Model_User' || $type == 'admin:user') {
            if (isset($content['password_confirmation'])) {
                unset($content['password_confirmation']);
            }
            foreach ($content['roles'] as $key => $roleId) {
                $roleEntity = Mage::getModel('admin/role')
                    ->load($roleId, 'role_id');
                Mage::helper('mageflow_connect/log')->log($roleEntity);
                if ($roleEntity->getRoleName() != '') {
                    $content['roles'][$key] = $roleEntity->getRoleName();
                } else {
                    unset($content['roles'][$key]);
                }
            }
        }

        if ($type == 'Mage_Cms_Model_Page' || $type == 'cms:page'
            || $type == 'Mage_Cms_Model_Block'
            || $type == 'cms:block'
        ) {
            if (isset($content['stores']) && is_array($content['stores'])) {
                foreach ($content['stores'] as $key => $storeId) {
                    if ($storeId != 0) {
                        $storeEntity = Mage::getModel('core/store')
                            ->load($storeId, 'store_id');
                        $content['stores'][$key] = $storeEntity->getCode();
                    }
                }
            }
        }

        if ($type == 'Mage_Catalog_Model_Resource_Eav_Attribute'
            || $type == 'catalog:resource_eav_attribute'
            || $type == 'catalog:attribute'
        ) {
            Mage::helper('mageflow_connect/log')->log('processing attribute');
            foreach ($content['option']['value'] as $valueSetKey => $valueSet) {
                foreach ($valueSet as $key => $value) {
                    if ($key != 0) {
                        $storeEntity = Mage::getModel('core/store')
                            ->load($key, 'store_id');
                        $content['option']['value'][$valueSetKey]
                        [$storeEntity->getCode()]
                            = $value;
                        unset($content['option']['value'][$valueSetKey][$key]);
                    }
                }
            }
            foreach ($content['store_labels'] as $key => $label) {
                Mage::helper('mageflow_connect/log')->log(
                    print_r($content['store_labels'], true)
                );
                if ($key != 0) {
                    $storeEntity = Mage::getModel('core/store')
                        ->load($key, 'store_id');
                    $content['store_labels'][$storeEntity->getCode()] = $label;
                    unset($content['store_labels'][$key]);
                }
                Mage::helper('mageflow_connect/log')->log(
                    print_r($content['store_labels'], true)
                );
            }
            Mage::helper('mageflow_connect/log')->log(print_r($content, true));
        }

        if ($type == 'Mage_Catalog_Model_Category'
            || $type == 'catalog:category'
        ) {

            if (isset($content['parent_id'])) {
                $parentCategory = Mage::getModel('catalog/category')
                    ->load($content['parent_id']);
                $content['parent_id'] = $parentCategory->getMfGuid();
            }

            if (isset($content['path'])) {
                $pathIdList = explode('/', $content['path']);
                $fixedPath = array();
                foreach ($pathIdList as $pathId) {
                    $categoryInPath = Mage::getModel('catalog/category')
                        ->load($pathId);
                    $fixedPath[] = $categoryInPath->getMfGuid();
                }
                $content['path'] = implode('/', $fixedPath);
            }

        }
        Mage::helper('mageflow_connect/log')->log($type);
        $encodedContent = json_encode(
            $content,
            JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE
        );
        Mage::helper('mageflow_connect/log')->log($encodedContent);
        $changesetItem->setContent($encodedContent);
        $changesetItem->setType($type);
        $changesetItem->setEncoding('json');
        $changesetItem->setCreatedAt($now->toString('c'));
        $changesetItem->setUpdatedAt($now->toString('c'));

        return $changesetItem;
    }

    /**
     * Returns MageFlow API client instance with
     * authentication fields filled in
     *
     * @return \Mageflow\Connect\Model\Api\Mageflow\Client
     */
    public function getApiClient()
    {

        @include_once 'Mageflow/Connect/Module.php';
        $m = new \Mageflow\Connect\Module();

        $configuration = new stdClass();
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
        $configuration->_company = \Mage::app()->getStore()->getConfig(
            \Mageflow_Connect_Model_System_Config::API_COMPANY
        );
        $configuration->_project = \Mage::app()->getStore()->getConfig(
            \Mageflow_Connect_Model_System_Config::API_PROJECT
        );
        $configuration->_instanceKey = \Mage::app()->getStore()->getConfig(
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
