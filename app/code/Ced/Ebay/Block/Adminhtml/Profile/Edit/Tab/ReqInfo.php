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
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Ebay\Block\Adminhtml\Profile\Edit\Tab;



class ReqInfo extends \Magento\Backend\Block\Widget\Form\Generic
{
	
	public function __construct(
    		\Magento\Backend\Block\Widget\Context $context,
    		\Magento\Framework\Registry $registry,
    		\Magento\Framework\Data\FormFactory $formFactory,
            \Magento\Framework\ObjectManagerInterface $objectInterface,
    		array $data = []
    ) {
    	$this->_coreRegistry = $registry;
    	$this->_objectManager = $objectInterface;
    	parent::__construct($context,$registry, $formFactory);
    }
	protected function _prepareForm(){
		
		$form=$this->_formFactory->create();
		$profile = $this->_coreRegistry->registry('current_profile');
	  
		$fieldset = $form->addFieldset('required_info', array('legend'=>__('Required Information')));
	
		$fieldset->addField('auto_pay', 'select',
				array(
						'name'      => "auto_pay",
						'label'     => __('Auto Pay'),
						'note'  	=> __('If checked, the seller requests immediate payment for the item. If false or not specified, immediate payment is not requested . This feature is also dependent on category.'),
						'values'    => ["" => "Select Field" ,"No" => "No", "Yes" => "Yes"],
						'value'     => $profile->getData('auto_pay'),
				)
		);


		$fieldset->addField('best_price', 'select',
				array(
						'name'      => "best_price",
						'label'     => __('Best Offer Feature'),
						'note'  	=> __('if Yes, the seller request immediate payment for the item'),
						'values'    => ["" => "Select Field" , "Yes" => "Yes", "No" => "No"],
						'value'     => $profile->getData('best_price'),
				)
		);

		$fieldset->addField('private_listing', 'select',
				array(
						'name'      => "private_listing",
						'label'     => __('Private Listing'),
						'note'  	=> __('Enable, If you want product to be in private listing'),
						'values'    => ["" => "Select Field" , "Yes" => "Yes", "No" => "No"],
						'value'     => $profile->getData('private_listing'),
				)
		);

		$fieldset->addField('prop_65', 'select',
				array(
						'name'      => "prop_65",
						'label'     => __('Proposition 65'),
						'note'  	=> __('Enable if products under proposition 65'),
						'values'    => ["" => "Select Field" , "Yes" => "Yes", "No" => "No"],
						'value'     => $profile->getData('prop_65'),
				)
		);

		$fieldset->addField('age_varify', 'select',
				array(
						'name'      => "age_varify",
						'label'     => __('Over 18 Age Varification'),
						'note'  	=> __('Enable if products contained inappropriate content for under 18 years old'),
						'values'    => ["" => "Select Field" , "Yes" => "Yes", "No" => "No"],
						'value'     => $profile->getData('age_varify'),
				)
		);

		$this->setForm($form);
		return parent::_prepareForm();
	}
	
}