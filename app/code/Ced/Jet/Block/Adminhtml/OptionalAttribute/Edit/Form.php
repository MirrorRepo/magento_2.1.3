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

namespace Ced\Jet\Block\Adminhtml\OptionalAttribute\Edit;

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

        $data = $this->objectManager->create('Ced\Jet\Model\OptionalAttribute')->getCollection()
                        ->addFieldToFilter('used', ['eq' => 0]);
        $attr_array[] = ['value'=>'1', 'label'=>'Please Select A Value'];
        $complete_data[1] = ['id'=>'', 'label'=>'', 'frontend_input'=>'', 'note'=>''];
        foreach ($data as $value) {
            $complete_data[$value->getJetCode()] = $value->getData();
            $attr_array[] = ['value'=> $value->getJetCode(), 'label'=> $value->getJetCode()];
        }
    
        $form = $this->_formFactory->create(['data' =>
                [
                    'id'            => 'edit_form',
                    'action'        => $this->getUrl('jet/jetattribute/create'),
                    'method'        => 'post',
                    'use_container' => true,
                ]
            ]
        );
        $this->setForm($form);
        $fieldset = $form->addFieldset('jet_attribute_form', ['legend'=>'Create Jet Attribute In magento']);

        $fieldset->setHeaderBar('
            <script>
            require(["jquery"], function($) {
                var complete_data = '.json_encode($complete_data). ';
                $("#jet_code").change(function() {
                    $("#id").val(complete_data[this.value]["id"]);
                    $("#label").val(complete_data[this.value]["label"]);
                    $("#frontend_input").val(complete_data[this.value]["frontend_input"]);
                    $("#note").val(complete_data[this.value]["note"]);
                });
            });
            </script>
        ');
 
        $fieldset->addField('id', 'hidden', [
                'label'     => __('Id'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'id',
                'readonly'  => true,
            ]
        );
 
        $fieldset->addField('jet_code', 'select', [
                'label'     => __('Jet Attribute Code'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'jet_code',
                'values'    => $attr_array
            ]
        );

        $fieldset->addField('label', 'text', [
                'label'     => __('Code Label'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'label',
            ]
        );

        $fieldset->addField('frontend_input', 'text', [
                'label'     => __('Frontend Input'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'frontend_input',
                'readonly'  => true,
            ]
        );

        $fieldset->addField('note', 'textarea', [
                'label'     => __('Note'),
                'name'      => 'note',
            ]
        );
 
        return parent::_prepareForm();
    }
}
