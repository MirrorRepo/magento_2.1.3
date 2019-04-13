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

namespace Ced\Jet\Block\Adminhtml\Product\Edit;
//use Magento\Framework\App\RequestInterface;
/**
 * Class AddAttribute
 */
class SyncWithJet extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $id= $this->getProduct()->getId();
        $sku = $this->getProduct()->getSku();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $data = $objectManager->get('Ced\Jet\Helper\Data')->CGetRequest('/merchant-skus/' . $sku);
        $data = json_decode($data, true);
        if (isset($data['merchant_sku'])) {
            return [
                'label' => __('Sync With Jet'),
                'class' => 'action-primary',
                'on_click' => sprintf("location.href = '%s';", $this->getUrl('jet/syncwithjet/jetproductedit/id/'.$id)),
                'sort_order' => 10
            ];
        }

    }
}
