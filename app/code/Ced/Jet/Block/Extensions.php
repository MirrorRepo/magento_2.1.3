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
 * @category  Ced
 * @package   Ced_Jet
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Config backend model for version display.
 */
namespace Ced\Jet\Block;

class Extensions extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\DataObject
     */
    public $_dummyElement;
    /**
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    public $_fieldRenderer;
    /**
     * @var array
     */
    public $_values;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_moduleList = $moduleList;
    }
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $header = $html = $footer = '';
        $header = $this->_getHeaderHtml($element);
        $modules= $this->_moduleList
        ->getOne(self::Ced_Jet)['setup_version'];
        $field = $element->addField(
            'extensions_heading', 'note', [ 
            'name'  => 'extensions_heading',
                'label' => '<a href="javascript:;"><b>Installed Version</b></a>',
                'text' => '<a href="javascript:;"><b>Available Version</b></a>']
                )->setRenderer($this->_getFieldRenderer());
        $html.= $field->toHtml();
        
        $modules = $this->_moduleList->getNames();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $dispatchResult = $objectManager->get('\Magento\Framework\DataObject\Factory')->create(
            $modules);
        $modules = $dispatchResult->toArray();
        sort($modules);
        foreach ($modules as $moduleName) {
            if ($moduleName === 'Magento_Backend') {
                continue;
            }
            $html .= $this->_getFieldHtml($element, $moduleName);
        }
        $html .= $this->_getFooterHtml($element);
        return $html;
    }
    /**
     * @return \Magento\Framework\DataObject
     */
    public function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->_dummyElement = $objectManager->get('\Magento\Framework\DataObject\Factory')->create(
            ['showInDefault' => 1, 'showInWebsite' => 1]);
        }
        return $this->_dummyElement;
    }
    /**
     * @return \Magento\Config\Block\System\Config\Form\Field
     */
    public function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->_layout->getBlockSingleton(
                'Magento\Config\Block\System\Config\Form\Field'
            );
        }
        return $this->_fieldRenderer;
    }
    /**
     * @return array
     */
    public function _getValues()
    {
        if (empty($this->_values)) {
            $this->_values = [
                ['label' => __('Enable'), 'value' => 0],
                ['label' => __('Disable'), 'value' => 1],
            ];
        }
        return $this->_values;
    }
    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $setfield
     * @param string $moduleName
     * @return mixed
     */
    public function _getFieldHtml($setfield, $moduleName)
    {
        $configData = $this->getConfigData();
        $path = 'advanced/modules_disable_output/' . $moduleName;
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data =(int)(string)$this->getForm()->getConfigValue($path);
            $inherit = true;
        }
        $element = $this->_getDummyElement();
        $field = $setfield->addField(
            $moduleName,
            'select',
            [
                'name' => 'groups[modules_disable_output][fields][' . $moduleName . '][value]',
                'label' => $moduleName,
                'value' => $data,
                'values' => $this->_getValues(),
                'inherit' => $inherit,
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($element),
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($element)
            ]
        )->setRenderer(
            $this->_getFieldRenderer()
        );
        return $field->toHtml();
    }
}
