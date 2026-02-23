<?php declare(strict_types=1);

namespace LokiCheckout\CoreCli\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Manager as ModuleManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LokiCheckout\Core\Config\LokiCheckoutModules;

class DumpModulesCommand extends Command
{
    public function __construct(
        private readonly LokiCheckoutModules $lokiCheckoutModules,
        private readonly ComponentRegistrar $componentRegistrar,
        private readonly ModuleManager $moduleManager,
        private readonly Filesystem $filesystem,
        private readonly DirectoryList $directoryList,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('loki-checkout:modules:dump');
        $this->setDescription('Dump all registered Loki Checkout modules to app/etc/loki-checkout.json.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = [];
        foreach ($this->lokiCheckoutModules->getActiveModules() as $module) {
            $data[] = [
                'name' => $module,
                'enabled' => (int)$this->moduleManager->isEnabled($module),
                'path' => $this->getRelativePath($module),
            ];
        }

        $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::APP);
        $directoryWrite->writeFile('etc/loki-checkout.json', json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

        return Command::SUCCESS;
    }

    private function getRelativePath(string $moduleName): string
    {
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        return str_replace($this->directoryList->getRoot().'/', '', $modulePath);
    }
}
