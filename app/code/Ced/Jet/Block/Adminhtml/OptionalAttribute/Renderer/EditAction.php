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
namespace Ced\Jet\Block\Adminhtml\OptionalAttribute\Renderer;

class EditAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer

{
    /**
     * @var
     */
    public $_storeManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    public $_eavAttribute;

    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    public $context;

    /**
     * EditAction constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objetManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface  $objetManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        array $data = [])
    {
        $this->context = $context;
        $this->objectManager = $objetManager;
        $this->_eavAttribute = $eavAttribute;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $attributeId = $this->_eavAttribute->getIdByCode('catalog_product', $row->getJetCode());
        $path = $this->context->getUrlBuilder()->getUrl(
            'catalog/product_attribute/edit/', ['attribute_id'=> $attributeId]);
        $html = '<a id="' . $this->getColumn()->getId() . '" href="'.$path .'"';
        $html .= '>Edit</a>';
        return $html;
    }
}
