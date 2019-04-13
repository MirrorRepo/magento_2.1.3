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
namespace Ced\Jet\Block\Adminhtml;

class OptionalAttribute extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    public $_template = 'optionalattribute/list.phtml';

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
            \Magento\Backend\Block\Widget\Context $context,
            array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_getAddButtonOptions();
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    public function _prepareLayout()
    {   
        $this->setChild('grid' ,
            $this->getLayout()->createBlock('Ced\Jet\Block\Adminhtml\OptionalAttribute\Grid', 'rejected.view.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     * @return array
     */

    public function _getAddButtonOptions()
    {
        $splitButtonOptions = [
                'label' => __('Map Attributes'),
                'class' => 'primary',
                'onclick' => "setLocation('" . $this->getUrl('jet/*/mapattribute') . "')"
            ];
        $splitButtonOptions1 = [
                'label' => __('Add New Attribute'),
                'class' => 'primary',
                'onclick' => "setLocation('" . $this->getUrl('jet/*/addattribute') . "')"
            ];
        $this->buttonList->add('mappattr', $splitButtonOptions);
        $this->buttonList->add('addattr', $splitButtonOptions1);
    }

    /**
     * Render grid
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }
}
