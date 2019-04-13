<?php
     
namespace IWD\B2B\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class CompanyShipping extends AbstractDb
{
    const TBL_NAME = 'iwd_b2b_company_shippings';
    
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('iwd_b2b_company_shippings', 'entity_id');
    }
}