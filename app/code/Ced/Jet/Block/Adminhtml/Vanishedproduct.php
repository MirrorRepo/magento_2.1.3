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

class Vanishedproduct extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    public $_template = 'vanishedproduct/view.phtml';

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
     * Prepare button and gridCreate Grid , edit/add grid row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    public function _prepareLayout()
    {
        $this->unsetChild('reset_filter_button');
        $this->unsetChild('search_button');
        $this->unset('Pager');
        $this->setChild('grid' ,
            $this->getLayout()->createBlock('Ced\Jet\Block\Adminhtml\Vanishedproduct\Grid', 'vanished.view.grid')
        );
        return parent::_prepareLayout();
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
