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
namespace Ced\Jet\Controller\Adminhtml\OrderReturn;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class UpdateStatus extends \Magento\Backend\App\Action

{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */

    public $resultPageFactory;

    /**
     * UpdateStatus constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function _construct(\Magento\Framework\Message\ManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * Update Return Status
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $helper = $this->_objectManager->get('Ced\Jet\Helper\Data');
        $status = rawurlencode("completed by merchant");
        $data = $helper->CGetRequest('/returns/'.$status);
        $response = json_decode($data, true);
        if (isset($response['return_urls'])) {
            $responseDecode = $response['return_urls'];
            foreach ($responseDecode as $res) {
                $arr = explode("/", $res);
                $returnids[] = $arr [3];
            }
        }
        $resultdata = $this->_objectManager->get('Ced\Jet\Model\OrderReturn')->getCollection()->addFieldToFilter('status', 'inprogress');
        $ids = [];
        if (isset($resultdata)) {
            foreach ($resultdata as $return) {
                $ids[] = $return->getReturnid();
            }
        }
        if (isset($returnids) && isset($ids)) {
            $commonIds = array_intersect($returnids, $ids);
            if ($this->insertUpdatedStatus($commonIds)) {
                $this->messageManager->addSuccess('Return Status updated');
             }
        }
        $this->_redirect('*/*/index');
        return;
    }

    /**
     * @param $commonIds
     * @return bool
     */
    public function insertUpdatedStatus($commonIds)
    {
        $i = 0;
        foreach ($commonIds as $id) {
            $model = $this->_objectManager->create('Ced\Jet\Model\OrderReturn')->load($id, 'returnid')->setData('status', 'completed')->save();
            $magento_incr_id = $this->_objectManager->get('Ced\Jet\Helper\Data')->getMagentoIncrementOrderId($model->getMerchantOrderId());
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($magento_incr_id);
            if ($order->canCreditmemo()) {
                $saved_data = $model->getDetailsSavedAfter();
                $saved_data = unserialize($saved_data);
                $this->_objectManager->get(
                    'Ced\Jet\Helper\Data')->generateCreditMemoForRefund(
                    $saved_data);
            }
                $i ++;
        }
        if ($i > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */

    public function _isAllowed() {
        return true;
    }
}
