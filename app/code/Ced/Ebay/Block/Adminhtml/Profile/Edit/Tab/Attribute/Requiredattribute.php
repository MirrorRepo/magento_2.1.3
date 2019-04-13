<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Ebay
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\Ebay\Block\Adminhtml\Profile\Edit\Tab\Attribute;

/**
 * Rolesedit Tab Display Block.
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
//class Requiredattribute extends \Magento\Backend\Block\Template

class Requiredattribute extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{

    /**
     * @var string
     */
    protected $_template = 'Ced_Ebay::profile/attribute/required_attribute.phtml';


    protected  $_objectManager;

    protected  $_coreRegistry;

    protected  $_profile;

    protected  $_ebayAttribute;


    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\Registry $registry,
                                array $data = []

    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;

        $this->_profile = $this->_coreRegistry->registry('current_profile');

        parent::__construct($context, $data);
    }



    /**
     * Prepare global layout
     * Add "Add tier" button to layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Attribute'), 'onclick' => 'return requiredAttributeControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_required_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Retrieve 'add group price item' button HTML
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }



    /**
     * Retrieve walmart attributes
     *
     * @param int|null $groupId  return name by customer group id
     * @return array|string
     */
    public function getEbayAttributes()
    {
        $requiredAttribute = [
            'Product Name' => ['ebay_attribute_name' => 'name','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'name', 'required' => 1],
            'SKU' => ['ebay_attribute_name' => 'sku','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'sku', 'required' => 1],
            'Price' => ['ebay_attribute_name' => 'price','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'price', 'required' => 1],
            'Weight' => ['ebay_attribute_name' => 'weight','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'weight', 'required' => 1],
            'Description' => ['ebay_attribute_name' => 'description','ebay_attribute_type' => 'textarea', 'magento_attribute_code' => 'description', 'required' => 1],
            'Image' => ['ebay_attribute_name' => 'image','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'image', 'required' => 1],
            'Inventory And Stock' => ['ebay_attribute_name' => 'inventory','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'quantity_and_stock_status', 'required' => 1],
            'Barcode' => ['ebay_attribute_name' => 'barcode','ebay_attribute_type' => 'select', 'magento_attribute_code' => 'barcode', 'required' => 1],
            'Barcode Value' => ['ebay_attribute_name' => 'barcode_value','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'barcode_value', 'required' => 1],
            'Brand' => ['ebay_attribute_name' => 'brand','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'brand', 'required' => 1],
            'Manufacturer' => ['ebay_attribute_name' => 'manufacturer','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'manufacturer', 'required' => 1],
            'Manufacturer Part Number' => ['ebay_attribute_name' => 'manufacturer_part_number','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'mfr_part_number','required' => 1], 
            'Maximu Dispatch Time' => ['ebay_attribute_name' => 'max_dispatch_time','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'max_dispatch_time', 'required' => 1],
            'Bullets' => ['ebay_attribute_name' => 'bullets','ebay_attribute_type' => 'textarea', 'magento_attribute_code' => 'bullets', 'required' => 1],
            'Listing Type' => ['ebay_attribute_name' => 'listing_type','ebay_attribute_type' => 'select', 'magento_attribute_code' => 'listing_type', 'required' => 1],
            'Listing Duration' => ['ebay_attribute_name' => 'listing_duration','ebay_attribute_type' => 'select', 'magento_attribute_code' => 'listing_duration', 'required' => 1],
            'Packs or Sets' => ['ebay_attribute_name' => 'packs','ebay_attribute_type' => 'text', 'magento_attribute_code' => 'packs', 'required' => 1],
            'CPSIA Cautionary Statements' => ['ebay_attribute_name' => 'cpsia_cautionary_statements','ebay_attribute_type' => 'select', 'magento_attribute_code' => 'cpsia_cautionary_statements', 'required' => 1]
            ];

        $optionalAttribues = [ 
            'Auto Pay(boolean)' => ['ebay_attribute_name' => 'auto_pay','ebay_attribute_type' => "boolean"],
            'Best Price(boolean)' => ['ebay_attribute_name' => 'best_price','ebay_attribute_type' => "boolean"],
            'Manufacturer Suggested Retail Price(text)' => ['ebay_attribute_name' => 'msrp','ebay_attribute_type' => "text"],
            'Display Actual Price(text)' => ['ebay_attribute_name' => 'actual_price','ebay_attribute_type' => "text"],
            'Prop 65(boolean)' => ['ebay_attribute_name' => 'prop_65','ebay_attribute_type' => "boolean"],
            'Private Listing(boolean)' => ['ebay_attribute_name' => 'private_listing','ebay_attribute_type' => "boolean"],
            'Country Of Origine(select)' => ['ebay_attribute_name' => 'contry_of_origin','ebay_attribute_type' => "select"],
            'Safety Warning(text)' => ['ebay_attribute_name' => 'safety_warning','ebay_attribute_type' => "text"],
            'Over 18 Age Verification(boolean)' => ['ebay_attribute_name' => 'age_varify','ebay_attribute_type' => "boolean"],
            ];
        $this->_ebayAttribute[] = array(
            'label' => __('Required Attributes'),
            'value' => $requiredAttribute
        );


        $this->_ebayAttribute[] = array(
            'label' => __('Optional Attributes'),
            'value' => $optionalAttribues
        );

        return $this->_ebayAttribute;
    }


    /**
     * Retrieve magento attributes
     *
     * @param int|null $groupId  return name by customer group id
     * @return array|string
     */
    public function getMagentoAttributes()
    {

        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')
            ->getItems();

        $mattributecode = '--please select--';
        $magentoattributeCodeArray[''] = $mattributecode;
        foreach ($attributes as $attribute){
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getFrontendLabel();
        }

        return $magentoattributeCodeArray;
    }


    public function getMappedAttribute()
    {
        $data = $this->_ebayAttribute[0]['value'];
        if($this->_profile && $this->_profile->getId()>0){
            $data = json_decode($this->_profile->getProfileReqOptAttribute(), true);
            if(isset($data['required_attributes']) && isset($data['optional_attributes']))
                $data = array_merge($data['required_attributes'], $data['optional_attributes']);
        }
        return $data;
    }


    /**
     * Render form element as HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }
}
