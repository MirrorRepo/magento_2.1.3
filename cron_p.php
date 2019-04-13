<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/app/bootstrap.php';
$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
 $objectManager = $bootstrap->getObjectManager();



     function execute($objectManager)
      {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/import-new.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        try {
          //$state = $objectManager->get('Magento\Framework\App\State');
            $objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // Intentionally left empty.
        }

        $import_path = "var/import/cron_products.csv";
        $import_file = pathinfo($import_path);

        $import = $objectManager->get('Magento\ImportExport\Model\ImportFactory')->create();
        $import->setData(
            array(
                'entity' => 'catalog_product',
                'behavior' => 'append',
                'validation_strategy' => 'validation-stop-on-errors',
            )
        );

        $read_file = $objectManager->get('Magento\Framework\Filesystem\Directory\ReadFactory')->create($import_file['dirname']);

        $csvSource = $objectManager->get('Magento\ImportExport\Model\Import\Source\CsvFactory')->create(
            array(
                'file' => $import_file['basename'],
                'directory' => $read_file,
            )
        );

        $validate = $import->validateSource($csvSource);

        if (!$validate) {
          $logger->info('Unable to validate the CSV');
        }

        $result = $import->importSource();
        if ($result) {
          try {
              $import->invalidateIndex();
              $logger->info('Finished importing products from'.$import_path);
          } catch (\Exception $e) {
            $logger->info($e->getMessage());
            $logger->info('Finished importing products from'.$e->getMessage());
            //continue;
          }

        }

    }

    execute($objectManager);

?>
