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
 * @package     Ced_Jet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Jet\Setup;

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

        $groupName = 'jetcom';
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $eavSetup->addAttributeGroup($entityTypeId, $attributeSetId, $groupName, 1000);
        $eavSetup->getAttributeGroupId($entityTypeId, $attributeSetId, $groupName);

        if (!$this->eavAttribute->loadByCode('catalog_product','barcode')->getId()) {
            $eavSetup->addAttribute('catalog_product', 'barcode', [
                'group' => 'jetcom',
                'note' => 'Please Select type of barcode',
                'input' => 'select',
                'type' => 'varchar',
                'label' => 'Barcode',
                'backend' => '',
                'visible' => 1,
                'required' => 0,
                'sort_order' => 1,
                'user_defined' => 1,
                'source' => 'Ced\Jet\Model\Source\Barcode',
                'comparable' => 0,
                'visible_on_front' => 0,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            ]
            ); 
        }
        
        if (!$this->eavAttribute->loadByCode('catalog_product','barcode_value')->getId()) {
            $eavSetup->addAttribute('catalog_product', 'barcode_value', [
                    'group' => 'jetcom',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Barcode Value',
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

        if (!$this->eavAttribute->loadByCode('catalog_product','jet_product_status')->getId()) {
            $eavSetup->addAttribute('catalog_product', 'jet_product_status', [
                    'group' => 'jetcom',
                    'input' => 'select',
                    'type' => 'varchar',
                    'label' => 'Jet Product Status',
                    'backend' => '',
                    'visible' => 1,
                    'required' => 0,
                    'sort_order' => 3,
                    'user_defined' => 1,
                    'source' => 'Ced\Jet\Model\Source\Productstatus',
                    'comparable' => 0,
                    'visible_on_front' => 0,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                ]
            );
        }

        if (!$this->eavAttribute->loadByCode('catalog_product','jet_brand')->getId()) {
            $eavSetup->addAttribute('catalog_product', 'jet_brand', [
                    'group' => 'jetcom',
                    'note' => '1 to 50 characters',
                    'frontend_class' => 'validate-length maximum-length-50',
                    'input' => 'text',
                    'type' => 'text',
                    'label' => 'Brand',
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

        if (!$this->eavAttribute->loadByCode('catalog_product','mfr_part_number')->getId()) {
            $eavSetup->addAttribute('catalog_product', 'mfr_part_number', [
                    'group' => 'jetcom',
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

        if (!$this->eavAttribute->loadByCode('catalog_product','bullets')->getId()) {
            $eavSetup->addAttribute('catalog_product', 'bullets', [
                    'group' => 'jetcom',
                    'note' => 'Please enter product feature description.Add each feature inside \'{}\'.Example :- {This is first one.}{This is second one.} and so on.Each \'{}\' contains maximum of 500 characters.Maximum 5 \'{}\' is allowed.',
                    'input' => 'textarea',
                    'type' => 'text',
                    'label' => 'Bullets',
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
        /*$csvhandler = $this->objectManager->create('Ced\Jet\Model\CsvHandler');
        $mediaDirectory = $this->objectManager->get('\Magento\Framework\Filesystem')
            ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $path = $mediaDirectory->getAbsolutePath('code/Ced/Jet/Setup/jetcsv/');
        $files['tmp_name'] = $path . 'Jet_Taxonomy.csv';
        $filesMapdata['tmp_name'] = $path . 'Jet_Taxonomy_mapping.csv';
        $filesAttrdata['tmp_name'] = $path . 'Jet_Taxonomy_attribute.csv';
        $taxonomy = $csvhandler->readFromCsvFile($files);
        $taxonomyMapping = $csvhandler->readFromCsvFile($filesMapdata);
        $taxonomyAttribute = $csvhandler->readFromCsvFile($filesAttrdata);
        unset($taxonomy[0]);
        unset($taxonomyMapping[0]);
        unset($taxonomyAttribute[0]);


        foreach ($taxonomy as $txt) {
            if (number_format($txt[3], 0, '', '') != 0) {
                $catId = number_format($txt[0], 0, '', '');
                $commaSeperatedId = '';
                foreach ($taxonomyMapping as $txtMap) {
                    if ($catId == number_format($txtMap[1], 0, '', '')) {
                        $commaSeperatedId = $commaSeperatedId . ',' . number_format($txtMap[0], 0, '', '');
                    }
                }
                $commaSeperatedAttIds = ltrim($commaSeperatedId, ',');
                if ($commaSeperatedAttIds != "") {
                    $attributeIds = explode(',', $commaSeperatedAttIds);
                    $commaSepratedNames = [];
                    foreach ($attributeIds as $val) {
                        foreach ($taxonomyAttribute as $txtAttr) {
                            if ($val == number_format($txtAttr[0], 0, '', '')) {
                                $commaSepratedNames[] = $txtAttr[2];
                            }
                        }
                    }
                    $attributesNames = implode(',', $commaSepratedNames);
                } else {
                    $attributesNames = '';
                }

                $parentId = number_format($txt[2], 0, '', '');
                $prepareArray = [$catId, $parentId, $txt[1], $txt[5], $txt[4], $commaSeperatedAttIds,
                    $attributesNames];
                $csvRawdata[] = $prepareArray;
                unset($attributeIds);
            }
        }
        $this->objectManager->create('Ced\Jet\Model\Categories')->getCollection()->getConnection()->truncateTable($setup->getTable('jet_category_list'));
        $setup->getConnection()->insertArray($setup->getTable('jet_category_list'),
            ['cat_id', 'parent_cat_id', 'name', 'is_taxable_product', 'level', 'attribute_ids', 'jetattr_name'],
            $csvRawdata
        );*/
    }
}
