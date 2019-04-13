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
//class Configattribute extends \Magento\Backend\Block\Template
class Ebayattribute extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface

{
    /**
     * @var string
     */
    public $_template = 'Ced_Ebay::profile/attribute/ebayattribute.phtml';


    public  $_objectManager;

    public  $_coreRegistry;

    public  $_profile;

    public  $_ebayAttribute;

    public $_ebayAttributeFeature;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->_helper = $this->_objectManager->create('Ced\Ebay\Helper\Data');
        $this->_coreRegistry = $registry;
        $this->_profile = $this->_coreRegistry->registry('current_profile');
        parent::__construct($context, $data);
    }

    /**
     * Prepare global layout | Add button to layout
     * @return $this
     */

    protected function _prepareLayout()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            ['label' => __('Add Attribute'), 'onclick' => 'return ebayAttributeControl.addItem()', 'class' => 'add']
        );
        $button->setName('add_required_item_button');

        $this->setChild('add_button', $button);
        return parent::_prepareLayout();
    }

    /**
     * Add button HTML
     * @return string
     */

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * Retrieve Ebay Category Attributes
     * @return array
     */

    public function getEbayAttributes()
    {
        $catId =  $this->getCatId();
        $ebayAttribute = [];
        if ($catId) {
            $getAttribute = $this->_helper->getCatSpecificAttribute($catId);
            $attributeCollections = $getAttribute->Recommendations->NameRecommendation;
            $catId = $getAttribute->Recommendations->CategoryID;
            $temp = [];
            foreach ($attributeCollections as $item) {
                $temp['ebay_attribute_type'] = $item->ValidationRules->SelectionMode;
                $ebayAttribute[$item->Name] = $temp;
            }
        } else {
            if($this->_profile && $this->_profile->getId()>0){
                $ebayAttribute = json_decode($this->_profile->getProfileCatAttribute(), true);
            }
        }
        $this->_ebayAttribute = $ebayAttribute;
        return $this->_ebayAttribute;
    }

    /**
     * Retrieve magento attributes
     * @return array
     */

    public function getMagentoAttributes()
    {
        $attributes = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')->getItems();
        $mattributecode = '--please select--';
        $magentoattributeCodeArray[''] = $mattributecode;
        foreach ($attributes as $attribute) {
            $magentoattributeCodeArray[$attribute->getAttributecode()] = $attribute->getFrontendLabel();
        }
        return $magentoattributeCodeArray;
    }

    /**
     * Retrieve Category Features
     * @return array
     */

    public function getCategoryFeature()
    {
        $catId = $this->getCatId();
        $_ebayAttributeFeature = [];
        if($this->_profile && $this->_profile->getId()>0) {
            $catArray = json_decode($this->_profile->getProfileCategory(), true);
            $data = array_reverse($catArray);
            if ($data) {
                foreach ($data as $value) {
                    if ($value != "") {
                        $catId = $value;
                        break;
                    }
                }
            }
        }
        if ($catId) {
            $limit = ['ConditionEnabled','ConditionValues'];
            $getCatFeatures = $this->_helper->getCategoryFeatures($catId, $limit);
            $getCatFeatures = isset($getCatFeatures->Category) ? $getCatFeatures->Category : false;
            if (isset($getCatFeatures->ConditionValues)) {
                $valueForDropdown = $getCatFeatures->ConditionValues->Condition;
                $_ebayAttributeFeature = [];
                if (count($valueForDropdown) > 1) {
                    foreach ($valueForDropdown as $key => $value) {
                        $_ebayAttributeFeature[$value->ID] = $value->DisplayName;
                    }
                } else {
                      $_ebayAttributeFeature[$valueForDropdown->ID] = $valueForDropdown->DisplayName;
                }
            }
        }
        $this->_ebayAttributeFeature = $_ebayAttributeFeature;
        return $this->_ebayAttributeFeature;
    }

    /**
     * Get Saved attributes
     * @return array
     */

    public function getEbayAttributeValuesMapping()
    {
        $data = [];
        if($this->_profile && $this->_profile->getId()>0){
            $data = json_decode($this->_profile->getProfileCatAttribute(), true);
        }
        return $data;
    }

    /**
     * Get Saved Category Feature
     * @return array
     */

    public function getSavedCatFeatures()
    {
        $data = "";
        if ( $this->_profile && $this->_profile->getId() > 0 ) 
            $data = $this->_profile->getProfileCatFeature();
        return $data;
    }


    /**
     * Render form element as HTML
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

}
