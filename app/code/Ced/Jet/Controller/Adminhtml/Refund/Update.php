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
namespace Ced\Jet\Controller\Adminhtml\Refund;

use Magento\Backend\App\Action;

class Update extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry = null;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public $resultPageFactory;

    /**
     * Update constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */

    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;

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
     * Check admin permissions for this controller
     *
     * @return boolean
     */

    public function _isAllowed()
    {
        return true;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */

    //Update refund action
    public function execute()
    {
        $res_arr= ['created','processing'];
        $result = $this->_objectManager->create('Ced\Jet\Model\Refund')->getCollection()
            ->addFieldToFilter('refund_status', [
                ['in' => $res_arr]
            ])->addFieldToSelect('refund_id')->getData();
        $count = count($result);
        $success_count = 0;
        $success_ids = "";
        if ($count > 0) {
            foreach ($result as $res) {
                $refundid = "";
                $refundid = $res ['refund_id'];
                $data = $this->_objectManager->get('Ced\Jet\Helper\Data')
                    ->CGetRequest('/refunds/state/' . $refundid . '');
                $responsedata = json_decode($data);
                print_r($responsedata);die;
                $success_count ++;
                if (isset($responsedata->refund_status))
                    if ($responsedata->refund_status != 'created') {
                        $modeldata =  $this->_objectManager->create('Ced\Jet\Model\Refund')
                            ->getCollection()->addFieldToFilter('refund_id', $refundid);
                        foreach ($modeldata as $models)
                        {
                            $this->SaveData($responsedata, $models);
                        }
                    }
            }
        }
        $this->_redirect('*/refund/index');
    }

    /**
     * @param $responsedata
     * @param $models
     */

    public function SaveData($responsedata, $models)
    {
        $id = $models ['id'];
        $update = ['refund_status' => $responsedata->refund_status];
        $model = "";
        $model = $this->_objectManager->create(
            'Ced\Jet\Model\Refund')->load($id);
        $model->addData($update);
        $model->save();
        $status = "";
        $status = $responsedata->refund_status;
        if (trim($status) == 'accepted') {
            $saved_data = "";
            $saved_data = $model->getData('saved_data');
            if ($saved_data != "") {
                $saved_data = unserialize($saved_data);
                $flag = false;
                $flag = $this->_objectManager->get('Ced\Jet\Helper\Data')->generateCreditMemoForRefund($saved_data);
            }
        }
        $this->_redirect('*/refund/index');
    }
}
