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

class UpdateReturnStatus
{
    public $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Ced\Jet\Helper\Data');
        $status = rawurlencode("completed by merchant");
        $data = $helper->CGetRequest('/returns/'.$status);
        $response = json_decode($data, true);
        $responseDecode = isset($response['return_urls']) ? $response['return_urls'] : [];
        if (empty($responseDecode)) {
            return true;
        }
        foreach ($responseDecode as $res) {
            $arr = explode("/", $res);
            $returnids[] = $arr [3];
        }
        $resultdata = $objectManager->get('Ced\Jet\Model\OrderReturn')->getCollection()->addFieldToFilter('status', 'inprogress');
        $ids = [];
        if (isset($resultdata)) {
            foreach ($resultdata as $return) {
                $ids[] = $return->getReturnid();
            }
        }
        $commonIds = array_intersect($returnids, $ids);
        $this->insertUpdatedStatus($commonIds); // to check/set inprogress status
        $this->logger->info('update return status Cron run successfully');
         return true;
    }

    /**
     * @param $commonIds
     * @return bool
     */
    public function insertUpdatedStatus($commonIds)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $i = 0;
        foreach ($commonIds as $id) {
            $model = $objectManager->create('Ced\Jet\Model\OrderReturn')->load($id, 'returnid')->setData('status', 'completed')->save();
            $mageIncOrderId = $objectManager->get('Ced\Jet\Helper\Data')->getMagentoIncrementOrderId($model->getMerchantOrderId());
            $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($mageIncOrderId);
            if ($order->canCreditmemo()) {
                $saveData = $model->getDetailsSavedAfter();
                $saveData = unserialize($saveData);
                $objectManager->get('Ced\Jet\Helper\Data')->generateCreditMemoForRefund($saveData);
            }
                $i ++;
        }
        if ($i > 0) {
            return true;
        } else {
            return false;
        }
    }
}
