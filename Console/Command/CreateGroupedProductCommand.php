<?php
namespace Magento2\CreateGroupedProduct\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Catalog\Model\Product;

class CreateGroupedProductCommand extends Command
{
    public function __construct(
        State $appState
    ) {
        parent::__construct();
        $this->appState = $appState;
    }

    protected function configure()
    {
        $this->setName('magento2:create_group')
            ->setDescription('Create grouped product for performance test.')
            ->addArgument('start', InputArgument::REQUIRED, 'the start of performance data')
            ->addArgument('amount', InputArgument::REQUIRED, 'the amount of performance data');
    }

    protected function addSimpleToGroup(array $simpleSkus, $groupSku)
    {
        $objectManager = ObjectManager::getInstance();
        $product = $objectManager->create(Product::class);
        $productId = $product->getResource()->getIdBySku("product_dynamic_" . $groupSku);
        $product->load($productId)
                ->setSku("group_dynamic_" . $groupSku)
                ->setName("Grouped Product " . $groupSku)
                ->setUrlKey("grouped-product-" . $groupSku)
                ->setVisibility(4)
                ->setTypeId("grouped");
        $links = array();
        foreach ($simpleSkus as $index => $simpleSku) {
            $link = $objectManager->create(Link::class)
                ->setSku("group_dynamic_" . $groupSku)
                ->setLinkedProductSku("product_dynamic_" . $simpleSku)
                ->setPosition($index + 1)
                ->setLinkType('associated');
            $links[] = $link;
        }
        $product->setProductLinks($links);
        $product->save();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = microtime(true);
        $output->writeln('<info>Begin to change simple product to grouped product.</info>');
        $start = $input->getArgument('start');
        if(!is_numeric($start)) return;
        $amount = $input->getArgument('amount');
        if ($amount == 'one_million') {
            $amount = 600000;
        } elseif ($amount == 'ten_million') {
            $amount = 10000000;
        } else {
            return;
        }
        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        $end = $start + $amount;
        $counter = range($end, $end+$amount/10-1, 1);
        foreach($counter as $index => $groupSku) {
            $simpleSkus = range($start+$index*10, $start+($index+1)*10-1, 1);
            $this->addSimpleToGroup($simpleSkus, $groupSku);
            $output->writeln($groupSku);
            $output->writeln(implode(",",$simpleSkus));
        }
        $endTime = microtime(true);
        $resultTime = $endTime - $startTime;
        $output->writeln('<info> done in ' . gmdate('H:i:s', $resultTime) . '</info>');
    }
}
