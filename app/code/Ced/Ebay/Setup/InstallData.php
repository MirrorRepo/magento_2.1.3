<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Ebay
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Ebay\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    /**
     * @var EavSetupFactory
     */
    public $eavSetupFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    public $eavAttribute;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute
     */
    public function __construct(EavSetupFactory $eavSetupFactory, \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->objectManager = $objectManager;
        $this->eavAttribute = $eavAttribute;
    }


    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */

        $groupName = 'ebay';
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 1000);
        $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);
        
        if (!$this->eavAttribute->getIdByCode('catalog_product','isbn')) {
            $eavSetup->addAttribute('catalog_product', 'isbn', [
                    'group' => 'ebay',
                    'note' => '10 or 13 characters',
                    'frontend_class' => 'validate-length maximum-length-13',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'ISBN',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 2,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product','ean')) {
            $eavSetup->addAttribute('catalog_product', 'ean', [
                    'group' => 'ebay',
                    'note' => '8 or 13 characters',
                    'frontend_class' => 'validate-length maximum-length-13',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'EAN',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 2,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product','upc')) {
            $eavSetup->addAttribute('catalog_product', 'upc', [
                    'group' => 'ebay',
                    'note' => '12 characters',
                    'frontend_class' => 'validate-length maximum-length-12',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'UPC',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 2,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product','brand')) {
            $eavSetup->addAttribute('catalog_product', 'brand', [
                    'group' => 'ebay',
                    'note' => '1 to 50 characters',
                    'frontend_class' => 'validate-length maximum-length-50',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Brand',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 3,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product','manufacturer')) {
            $eavSetup->addAttribute('catalog_product', 'manufacturer', [
                    'group' => 'ebay',
                    'note' => '1 to 50 characters',
                    'frontend_class' => 'validate-length maximum-length-50',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Manufacturer',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 4,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product','mfr_part_number')) {
            $eavSetup->addAttribute('catalog_product', 'mfr_part_number', [
                    'group' => 'ebay',
                    'note' => 'Part number provided by the original manufacturer of the merchant SKU',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Manufacturer part number',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 5,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product','bullets')) {
            $eavSetup->addAttribute('catalog_product', 'bullets', [
                    'group' => 'ebay',
                    'note' => "Please enter product feature description.Add each feature inside '{}'.Example :- {This is first one.}{This is second one.} and so on. Each '{}' contains maximum of 500 characters.Maximum 5 {} is allowed.",
                    'input' => 'textarea',
                    'type' => 'text',
                    'label' => 'Bullets',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 10,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product','max_dispatch_time')) {
            $eavSetup->addAttribute('catalog_product', 'max_dispatch_time', [
                    'group' => 'ebay',
                    'note'  => 'Specifies the maximum number of business days the seller commits to for preparing an item to be shipped after receiving a cleared payment.',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Maximum Dispatch Time',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 6,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product','listing_type')) {
            $eavSetup->addAttribute('catalog_product', 'listing_type', [
                    'group' => 'ebay',
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'Listing Type',
                    'note'  => '',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 7,
                    'user_defined' => 1,
                    'source' => 'Ced\Ebay\Model\Source\ListingType',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product','listing_duration')) {
            $eavSetup->addAttribute('catalog_product', 'listing_duration', [
                    'group' => 'ebay',
                    'input' => 'select',
                    'type' => 'varchar',
                    'note'      => '',
                    'label' => 'Listing Duration',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 8,
                    'user_defined' => 1,
                    'source' => 'Ced\Ebay\Model\Source\ListingDuration',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product','cpsia_cautionary_statements')) {
            $eavSetup->addAttribute('catalog_product', 'cpsia_cautionary_statements', [
                    'group' => 'ebay',
                    'input' => 'select',
                    'type'  => 'varchar',
                    'label' => 'CPSIA Cautionary Statements',
                    'note'  => "Use this field to indicate if a cautionary statement relating to the choking hazards of children's toys and games applies to your product. These cautionary statements are defined in Section 24 of the Federal Hazardous Substances Act and Section 105 of the Consumer Product Safety Improvement Act of 2008.",
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 11,
                    'user_defined' => 1,
                    'source' => 'Ced\Ebay\Model\Source\Caution',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->getIdByCode('catalog_product','packs')) {
            $eavSetup->addAttribute('catalog_product', 'packs', [
                    'group' => 'ebay',
                    'note'      => 'Identify the package count of this product.',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Packs Or Sets',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 9,
                    'user_defined' => 1,
                    'searchable' => 1,
                    'filterable' => 0,
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }
    }
}

