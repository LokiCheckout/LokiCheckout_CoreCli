<?php declare(strict_types=1);

namespace LokiCheckout\CoreCli\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use JsonException;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LokiCheckout\Core\Config\LokiCheckoutModules;

class DumpModulesCommand extends Command
{
    private const FILE = 'etc/loki-checkout.json';

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $data = [];
        foreach ($this->lokiCheckoutModules->getActiveModules() as $module) {
            $data[] = [
                'name' => $module,
                'enabled' => (int)$this->moduleManager->isEnabled($module),
                'path' => $this->getRelativePath($module),
            ];
        }

        try {
            $json = json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $output->writeln('<error>Unable to encode module listing: '.$exception->getMessage().'</error>');
            return Command::FAILURE;
        }

        $directoryWrite = $this->filesystem->getDirectoryWrite(DirectoryList::APP);
        $tmpFile = self::FILE.'.'.bin2hex(random_bytes(6)).'.tmp';

        try {
            $directoryWrite->writeFile($tmpFile, $json);
            $directoryWrite->renameFile($tmpFile, self::FILE);
        } catch (FileSystemException|ValidatorException $exception) {
            $this->deleteFile($directoryWrite, $tmpFile);
            $output->writeln('<error>Unable to write app/'.self::FILE.': '.$exception->getMessage().'</error>');
            return Command::FAILURE;
        }

        $output->writeln(sprintf('Dumped %d modules to app/%s', count($data), self::FILE));

        return Command::SUCCESS;
    }

    private function deleteFile(WriteInterface $directoryWrite, string $file): void
    {
        try {
            $directoryWrite->delete($file);
        } catch (FileSystemException|ValidatorException) {
            return;
        }
    }

    private function getRelativePath(string $moduleName): string
    {
        $modulePath = (string)$this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        $root = rtrim($this->directoryList->getRoot(), '/').'/';
        if (str_starts_with($modulePath, $root)) {
            return substr($modulePath, strlen($root));
        }

        return $modulePath;
    }
}
