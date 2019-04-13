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

namespace Ced\Jet\Block\Adminhtml\Product;

class Button extends \Magento\Backend\Block\Widget\Container
{

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Ced\Jet\Block\Adminhtml\Product
     */
    public function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'jet_product_button',
            'label' => __('Bulk Upload Actions'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     */
    public function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];

        $splitButtonOptions['massimport'] = [
                'label' => __('Bulk Product Upload'),
                'onclick' => "setLocation('" . $this->getUrl(
                  'jet/jetajax/massimport') . "')",
                'default' => true,
            ];

        $splitButtonOptions['sync_price_inv'] = [
                'label' => __('Sync Inventory And Price'),
                'onclick' => "setLocation('" . $this->getUrl(
                  'jet/jetajax/sync') . "')",
                'default' => false,
            ];    

         $splitButtonOptions['massarchived'] = [
                'label' => __('Bulk Product Archive'),
                'onclick' => "setLocation('" . $this->getUrl(
                  'jet/jetajax/massarchived') . "')",
                'default' => false,
            ];
            
          $splitButtonOptions['massunarchived'] = [
                'label' => __('Bulk Product Unarchive'),
                'onclick' => "setLocation('" . $this->getUrl(
                  'jet/jetajax/massunarchived') . "')",
                'default' => false,
            ];

        return $splitButtonOptions;
    }

}
