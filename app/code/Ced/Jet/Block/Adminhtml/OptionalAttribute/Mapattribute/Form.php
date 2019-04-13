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

namespace Ced\Jet\Block\Adminhtml\OptionalAttribute\Mapattribute;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\ObjectManagerInterface $objetManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface  $objetManager,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->objectManager = $objetManager;
    }

    /**
     * @return $this
     */
    public function _prepareForm()
    {
        $data = [];
        $form = $this->_formFactory->create(['data' =>
                [
                    'id'            => 'edit_form',
                    'action'        => $this->getUrl('jet/jetattribute/mapsubmit'),
                    'method'        => 'post',
                    'use_container' => true,
                ]
            ]
        );
        $collection = $this->objectManager->create('Ced\Jet\Model\OptionalAttribute')->getCollection();
        $this->setForm($form);
        $fieldset = $form->addFieldset('jeterror_form', []);

        $fieldset->addField('action', 'text', [
                'label'     => __('Mapp The Jet Attributes'),
                'class'     => 'action',
                'name'      => 'jet_code'
            ]
        );
        
        $locations = $form->getElement('action');

        $locations->setRenderer(
            $this->getLayout()->createBlock('Ced\Jet\Block\Adminhtml\OptionalAttribute\Renderer\Mapattr')
                ->setData('jet_attr_collection', $collection)
        );

        $form->setValues($data);
 
        return parent::_prepareForm();
    }
}
