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
 * @package   Ced_Ebay
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\Ebay\Model\Config;

class PaymentMethods implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Objet Manager
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Constructor
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        $location = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ebay_config/ebay_setting/location');
        $mediaDirectory = $this->objectManager->get('\Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $folderPath = $mediaDirectory->getAbsolutePath('code/Ced/Ebay/Setup/json/');
        $locationList = $this->objectManager->get('Ced\Ebay\Model\Config\Location')->toOptionArray();
        foreach ($locationList as $value) {
            if ($value['value'] == $location) {
                $locationName = $value['label'];
            }
        }
        $path = $folderPath .$locationName. '/payment-methods.json';
        if (file_exists($folderPath .$locationName)) {
            $payMethods = $this->objectManager->get('Ced\Ebay\Helper\Data')->loadFile($path, '', '');
            if (isset($payMethods)) {
                
                $allMethods = [ 'American Express'=>'AmEx','Cash-in-person option'=>'CashInPerson','Payment on delivery'=>'CashOnPickup','Credit card'=>'CCAccepted','Cash on delivery'=>'COD','Credit Card'=>'CreditCard','Diners'=>'Diners','Direct Debit'=>'DirectDebit','Discover card'=>'Discover','Elektronisches Lastschriftverfahren (direct debit)'=>'ELV','Integrated Merchant CreditCard'=>'IntegratedMerchantCreditCard','Loan Check'=>'LoanCheck','Money order/cashiers check'=>'MOCC','Direct transfer of money'=>'MoneyXferAccepted','MoneyXferAcceptedInCheckout'=>'MoneyXferAcceptedInCheckout','None'=>'No payment method specified','Other'=>'Other forms of payment','All other online payments'=>'OtherOnlinePayments','PaisaPay (for India site only)'=>'PaisaPayAccepted','PaisaPayEscrow payment option'=>'PaisaPayEscrow','PaisaPayEscrowEMI (Equal Monthly Installments) Payment option'=>'PaisaPayEscrowEMI','Payment See Description'=>'PaymentSeeDescription','PayOnPickup payment method'=>'PayOnPickup','PayPal'=>'PayPal','PayPal Credit'=>'PayPalCredit','PayUpon Invoice'=>'PayUponInvoice','Personal Check'=>'PersonalCheck','Visa/Mastercard'=>'VisaMC'
                ];

                $paymentMethods = array_intersect($allMethods, $payMethods);
                foreach ($paymentMethods as $key => $value) {
                    $result[] = ['label' => $key, 'value' => $value];
                }
            }
        }
        return $result;
    }
}
