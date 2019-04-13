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

namespace Ced\Jet\Controller\Adminhtml\Jetattribute;

class Create extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::upload_products';
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    public $productHelper;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    public $attributeFactory;
    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    public $validatorFactory;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    public $groupCollectionFactory;

    /**
     * Create constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
    ) {
        parent::__construct($context);
        $this->productHelper = $productHelper;
        $this->attributeFactory = $attributeFactory;
        $this->validatorFactory = $validatorFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    public function execute()
    {
        $attributeCode = $this->getRequest()->getParam('jet_code');
        $id = $this->getRequest()->getParam('id');
        if (isset($attributeCode) && isset($id)) {

            $model = $this->_objectManager->create('Ced\Jet\Model\OptionalAttribute')->load($id);
            if (isset($model) && $model->getId() && $model->getJetCode() != $attributeCode) {
                $this->messageManager->addError(__('Jet Attribute Did not Find In Database'));
                $this->_redirect('jet/*/index');
            }

            if ($attributeCode !== "") {
                $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,30}$/']);
                if (!$validatorAttrCode->isValid($attributeCode)) {
                    $this->messageManager->addError(
                        __(
                            'Jet Attribute code "%1" is invalid. Please use only letters(a-z), ' .
                            'numbers(0-9) or underscore(_) in this field, first character should be a letter.',
                            $attributeCode
                        )
                    );
                    $this->_redirect('jet/*/index');
                }
            }
            $data['attribute_code'] = $attributeCode;
            $data['frontend_input'] = $model->getFrontendInput();
            $data['frontend_label'] = $this->getRequest()->getParam('label');
            $data['note'] = $this->getRequest()->getParam('note');
            if (isset($data['frontend_input'])) {
                $inputType = $this->validatorFactory->create();
                if (!$inputType->isValid($model->getFrontendInput())) {
                    foreach ($inputType->getMessages() as $message) {
                        $this->messageManager->addError($message);
                    }
                    $this->_redirect('jet/*/index');
                }
            }

            try {
                $attr_model = $this->attributeFactory->create();
                $data['source_model'] = $model->getSourceModel() ? $model->getSourceModel() : $this->productHelper->
                getAttributeSourceModelByInputType(  $data['frontend_input']);
                $data['backend_model'] = $model->getBackendModel() ? $model->getBackendModel() : $this->productHelper->
                getAttributeBackendModelByInputType($data['frontend_input']);
                $data += ['is_filterable' => 0, 'is_filterable_in_search' => 0, 'apply_to' => []];

                $data['backend_type'] = $model->getBackendType() ? $model->getBackendType() : $attr_model->getBackendTypeByInput($data['frontend_input']);

                $attr_model->addData($data);

                $entityType = $this->_objectManager->create('Magento\Eav\Model\Entity\Type')
                    ->loadByCode(\Magento\Catalog\Model\Product::ENTITY);

                $entityTypeId = $entityType->getEntityTypeId();
                $attributeSetId = $entityType->getDefaultAttributeSetId();

                $groupCollection = $this->groupCollectionFactory->create()
                    ->setAttributeSetFilter($attributeSetId)
                    ->addFieldToFilter('attribute_group_code', 'jetcom')
                    ->setPageSize(1)
                    ->load();
                $group = $groupCollection->getFirstItem();

                $attr_model->setEntityTypeId($entityTypeId);
                $attr_model->setIsUserDefined(1);
                $attr_model->setAttributeSetId($attributeSetId);
                $attr_model->setAttributeGroupId($group->getId());
                $attr_model->save();

                $model->setData('used', 1);
                $model->setData('map_attribute_code', $attributeCode);
                $model->save();
                $this->messageManager->addSuccess(__('Attributes Created Successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('No data Posted.'));
        }
        $this->_redirect('jet/*/index');
    }
}
