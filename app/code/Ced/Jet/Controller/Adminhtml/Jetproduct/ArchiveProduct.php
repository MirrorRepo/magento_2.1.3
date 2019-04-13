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
 * @category    Ced
 * @package     Ced_Jet
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Jet\Controller\Adminhtml\Jetproduct;

use Magento\Backend\App\Action;

class ArchiveProduct extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    public $_objectManager;

    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    public $resultRedirectFactory;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\View\Result\Redirect $resultRedirectFactory ,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->resultForwardFactory= $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @return void
     */
    public function _construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->messageManager = $messageManager;
    }

    /**
     * @return void
     */

    public function execute()
    {
       $sku = $this->getRequest()->getParam('merchant_sku');
       $requestSent =  $this->_objectManager->get('Ced\Jet\Helper\Data')->CPutRequest(
                '/merchant-skus/'.$sku.'/status/archive', json_encode(['is_archived'=>true]));
        if (empty($requestSent)) {
            $this->messageManager->addSuccess('Archive Request for'.$sku.'has been sent to jet');
        } else {
            $requestSent = json_decode($requestSent, true);
            $requestSent = empty($requestSent) ? [] : $requestSent;
            foreach ($requestSent as $value) {
                $this->messageManager->addError($value);
            }
        }
    }
}
