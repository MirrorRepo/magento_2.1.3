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

class Rejected extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    public $_template = 'refund/view.phtml';

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
     * Prepare button and gridCreate Grid , edit/add grid row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    public function _prepareLayout()
    {   
        $this->setChild('grid' ,
            $this->getLayout()->createBlock('Ced\Jet\Block\Adminhtml\Rejected\Grid', 'rejected.view.grid')
        );
        return parent::_prepareLayout();
    }

    /**
     * @return array
     */

    public function _getAddButtonOptions()
    {
        $splitButtonOptions = [
                'label' => __('Clear All Log'),
                'class' => 'primary',
                'onclick' => "setLocation('" . $this->getUrl('jet/jetproduct/clearall') . "')"
            ];
        $splitButtonOptions1 = [
                'label' => __('Fetch Rejected Files'),
                'class' => 'primary',
                'onclick' => "setLocation('" . $this->getUrl('jet/jetproduct/fetcherror') . "')"
            ];
        $this->buttonList->add('clearall', $splitButtonOptions);
        $this->buttonList->add('fetcherror', $splitButtonOptions1);
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
