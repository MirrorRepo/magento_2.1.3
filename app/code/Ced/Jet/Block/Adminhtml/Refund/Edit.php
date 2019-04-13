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
namespace Ced\Jet\Block\Adminhtml\Refund;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize booking post edit block
     *
     * @return void
     */
    public function _construct()
    {

        $this->_objectId = 'id';
        $this->_blockGroup = 'Ced_Jet';
        $this->_controller = 'adminhtml_refund';
        /*  added code*/
        $this->setId('order_edit');
        $this->setUseContainer(true);
        /*end  */
        $id = $this->getRequest()->getParam('id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->get('Ced\Jet\Model\Refund')->getCollection()->addFieldToFilter('id',$id);
        if ($collection->getSize() < 1)
            $this->addButton(
                'save',
                [
                    'label' => __('Save'),
                    'class' => 'save primary',
                    'data_attribute' => [
                        'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                    ]
                ],
                1
            );

        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getBackUrl() . '\')',
                'class' => 'back'
            ],
            -1
        );

    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {

        if ($this->_coreRegistry->registry('ced_refund_data')->getId()) {
            return __("Edit Post '%1'", $this->escapeHtml($this->_coreRegistry->registry('ced_refund_data')->getTitle()));
        } else {
            return __('Submit Refund');
        }
    }


    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function _prepareLayout()
    {

        $this->_formScripts[] = "
         function toggleEditor() {
             if (tinyMCE.getInstanceById('page_content') == null) {
                 tinyMCE.execCommand('mceAddControl', false, 'content');
             } else {
                 tinyMCE.execCommand('mceRemoveControl', false, 'content');
             }
         };
     ";
        return parent::_prepareLayout();
    }
}
