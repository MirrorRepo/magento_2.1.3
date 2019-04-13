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
namespace Ced\Jet\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Magento\Framework;
use \Magento\Framework\ObjectManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductDeleteAfter implements ObserverInterface
{
    /**
     *
     * @var ObjectManagerInterface
     */
    public $_objectManager;
    /**
     * @var ManagerInterface
     */
    public $messageManager;

    /**
     * ProductDelete constructor.
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param RequestInterface $request
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->_objectManager = $objectManager;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->registry  = $registry;
    }

    /**
     * Catalog product delete after event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // code for Archive Request on Delete product Sku
        $skus = $this->registry->registry('skus');
        $allSkus = explode(',', $skus);
        if (isset($allSkus)) {
            foreach ($allSkus as  $sku) {
                $this->sendArchiveRequest($sku);
            }
        }
        $this->registry->unregister('skus');
    }

    /**
     * Send Archive request
     * @param $sku
     * @return void
     */
    public function sendArchiveRequest($sku)
    {
        $response = $this->_objectManager->get('Ced\Jet\Helper\Data')->CGetRequest('/merchant-skus/' . $sku);
        $checkSku = json_decode($response, true);
        if (isset($checkSku['merchant_sku'])) {
            $requestSent =  $this->_objectManager->get(
                'Ced\Jet\Helper\Data')->CPutRequest(
                    '/merchant-skus/'.$sku.'/status/archive',
                json_encode(['is_archived'=>true]));
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
}
