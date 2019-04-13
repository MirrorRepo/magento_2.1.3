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
namespace Ced\Jet\Controller\Adminhtml\Order;

class Acknowledge extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Ced_Jet::jet_orders';
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public $resultRedirectFactory;
    /**
     * @var \Ced\Jet\Helper\Order
     */
    public $jetHelper;

    /**
     * Acknowledge constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Ced\Jet\Helper\Order $jetHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Ced\Jet\Helper\Order $jetHelper
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->jetHelper = $jetHelper;
        parent::__construct($context);
    }

    /**
     * Jet Order Acknowledge action
     * @return String
     */

    public function execute()
    {
        $ids = $this->getRequest()->getParam('id');
        foreach ($ids as $key => $id) {
            $this->jetHelper->autoOrderacknowledge($id);
        }
        $result = $this->resultRedirectFactory->create();
        $result->setPath('*/*/listorder');
        return $result;
    }
}
