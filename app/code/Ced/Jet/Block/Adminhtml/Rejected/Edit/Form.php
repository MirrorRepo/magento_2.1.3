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

namespace Ced\Jet\Block\Adminhtml\Rejected\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\ObjectManagerInterface $objetManager
     * @param array $data
     */

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface  $objetManager,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->objectManager = $objetManager;
    }

    /**
     * Prepare form
     *
     * @return $this
     */

    public function _prepareForm()
    {
        if ($this->_coreRegistry->registry('errorfile_collection')) {
            $data = $this->_coreRegistry->registry('errorfile_collection')->getData();
        } else {
            $data = [];
        }

        $form = $this->_formFactory->create(['data' =>
                [
                    'id'            => 'edit_form',
                    'action'        => $this->getUrl('jet/jetproduct/resubmit'),
                    'method'        => 'post',
                    'use_container' => true,
                ]
            ]
        );
        $this->setForm($form);
        $fieldset = $form->addFieldset('jeterror_form', []);

        $fieldset->addField('id', 'hidden', [
                'label'     => __('error_id'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'id',
                'readonly'  => true,
            ]
        );

        $fieldset->addField('jetinfofile_id', 'hidden', [
                'label'     => __('jetinfofile_id'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'jetinfofile_id',
                'readonly'  => true,
            ]
        );

        $fieldset->addField('jet_file_id', 'text', [
                'label'     => __('Jet File Id'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'jet_file_id',
                'readonly'  => true,
            ]
        );

        $fieldset->addField('file_name', 'text', [
                'label'     => __('File Name'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'file_name',
                'readonly'  => true,
            ]
        );

        $fieldset->addField('file_type', 'text', [
                'label'     => __('File Type'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'file_type',
                'readonly'  => true,
            ]
        );

        $fieldset->addField('error', 'textarea', [
                'label'     => __('Error Description'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'error',
                'readonly'  => true,
            ]
        );

        $form->setValues($data);
        $this->_readFile($data);
        $fdata = $this->_coreRegistry->registry('errorfilecontent');
        $errorfilecontent = isset($fdata) ? $fdata : 'File not found';

        $fieldset->addField('errorfile', 'textarea', [
                'label' => __('File Content'),
                'class' => 'required-entry',
                'required' => true,
                'readonly' => true,
                'name'  => 'errorfile',
                'value' => $errorfilecontent,
            ]
        );

        return parent::_prepareForm();
    }

    /**
     * Read data from file. If file can't be opened, throw to exception.
     *
     * @param string $source
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _readFile($data)
    {
        $file_name = explode(' ', $data['file_name']);
        $data['file_name'] = implode('', $file_name);
        $var_dir = $this->objectManager->get('Magento\Framework\Filesystem\DirectoryList')->getPath('var');
        $errorfile = $var_dir.'/jetupload'.'/'.$data['file_name'];
        if (file_exists($errorfile)) {
            $errorfilecontent = file_get_contents($errorfile);
        } else {
            $errorfilecontent = null;
        }
        $this->_coreRegistry->register('errorfilecontent', $errorfilecontent);
    }
}
