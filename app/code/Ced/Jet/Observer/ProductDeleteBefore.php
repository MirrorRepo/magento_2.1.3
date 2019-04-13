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

class ProductDeleteBefore implements ObserverInterface
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
     * Catalog product delete before event handler
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // code for Archive Request on Delete product Sku
        $skus = array();
        $data =  $this->request->getParams();
        if (isset($data['excluded'])) {
            if ($data['excluded'] == 'false') {
                if (isset($data['filters']['entity_id'])) {
                    $to = intval($data['filters']['entity_id']['to']);
                    for ($from = intval($data['filters']['entity_id']['from']) ; $from <= $to ; $from++) {
                        $data['selected'][] = $from;
                    }
                } else {
                    $productArray = $this->_objectManager->get(
                        'Magento\Catalog\Model\Product')->getCollection()->getData();
                    foreach ($productArray as $value) {
                        $skus[] = trim($value['sku']); 
                    }
                }
            }
        }
        if (isset($data['selected'])) {
            foreach ($data['selected'] as  $value) {
                $sku = $this->_objectManager->get('Magento\Catalog\Model\Product')->load(intval($value))->getSku(); 
                $skus[] = trim($sku);
            }
        }
       
        $this->_objectManager->get('Magento\Framework\Registry')->register('skus', implode(',', $skus));
        return $observer;
    }
}
