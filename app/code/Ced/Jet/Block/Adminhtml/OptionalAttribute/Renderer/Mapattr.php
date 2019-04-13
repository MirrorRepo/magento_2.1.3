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

class Mapattr extends \Magento\Backend\Block\Widget implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{

    /**
     * @var string
     */
    public $_template = 'Ced_Jet::optionalattribute/mappattr.phtml';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface  $objetManager,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->objectManager = $objetManager;
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

    public function getProductAttribute() {

        $attributes = $this->objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection')->getItems();
        $attributesArrays = [];
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisible()) {
                $attributesArrays[] = [
                    'label' => $attribute->getFrontendLabel(),
                    'code' => $attribute->getAttributecode(),
                    'frontend_input' => $attribute->getFrontendInput()
               ];
            }
        }
        return $attributesArrays;
    }
}
