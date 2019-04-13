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
namespace Ced\Jet\Cron;

class UpdateRefundStatus
{
    public $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ){
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $res_arr= ['created','processing'];
        $result = $objectManager->create('Ced\Jet\Model\Refund')->getCollection()->addFieldToFilter('refund_status', [['in' => $res_arr]])->addFieldToSelect('refund_id')->getData();
        $count = count($result);
        $success_count = 0;
        $success_ids = "";
        if ($count > 0) {
            foreach ($result as $res) {
                $refundid = "";
                $refundid = $res ['refund_id'];
                $data = $objectManager->get('Ced\Jet\Helper\Data')
                    ->CGetRequest('/refunds/state/' . $refundid . '');
                $responsedata = json_decode($data);
                $success_count ++;
                if (isset($responsedata->refund_status)) {
                    if ($responsedata->refund_status != 'created') {
                        $modeldata =  $objectManager->create('Ced\Jet\Model\Refund')
                            ->getCollection()->addFieldToFilter('refund_id', $refundid);
                        foreach ($modeldata as $models) {
                            $this->SaveData($responsedata, $models);
                        }
                    }
                }
            }
        }
        $this->logger->info('update refund status Cron run successfully');
         return true;
    }

    /**
     * @param $responsedata
     * @param $models
     */

    public function SaveData($responsedata, $models)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $id = $models ['id'];
        $update = ['refund_status' => $responsedata->refund_status];
        $model = "";
        $model = $objectManager->create(
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
                $flag = $objectManager->get('Ced\Jet\Helper\Data')->generateCreditMemoForRefund($saved_data);
            }
        }
    }
}
