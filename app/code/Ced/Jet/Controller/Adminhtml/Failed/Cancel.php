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
namespace Ced\Jet\Controller\Adminhtml\Failed;

class Cancel extends \Magento\Backend\App\Action
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
    public $orderHelper;

    /**
     * Fetch constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Ced\Jet\Helper\Order $orderHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Ced\Jet\Helper\Order $orderHelper
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->orderHelper = $orderHelper;
        parent::__construct($context);
    }

    /**
     * Update status of failed jet orders 
     * @return Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $getFailedOrder = $this->_objectManager->get('Ced\Jet\Model\OrderImportError')->load($id);
        $merchantOrderId = $getFailedOrder->getMerchantOrderId();
        $orderItemId = $getFailedOrder->getOrderItemId();
        $this->orderHelper->orderCancelRequest($merchantOrderId, $orderItemId);
        $getFailedOrder->setStatus('cancelled');
        $getFailedOrder->save();
    	$result = $this->resultRedirectFactory->create();
        $result->setPath('jet/failed/orders');
        return $result;
    }
}
