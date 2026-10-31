<?php declare(strict_types=1);

namespace LokiCheckout\CoreCli\Test\Integration\Console\Command;

use LokiCheckout\CoreCli\Console\Command\DumpModulesCommand;
use LokiCheckout\CoreCli\Console\Command\ListModulesCommand;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Console\CommandListInterface;
use Magento\Framework\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\GetObjectManager;

final class DumpModulesCommandTest extends TestCase
{
    use GetObjectManager;

    private string $appFolder = '';

    protected function setUp(): void
    {
        $this->appFolder = sys_get_temp_dir() . '/loki-checkout-core-cli-' . bin2hex(random_bytes(4));
        mkdir($this->appFolder . '/etc', 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->appFolder . '/etc/*') ?: [] as $file) {
            unlink($file);
        }

        rmdir($this->appFolder . '/etc');
        rmdir($this->appFolder);
    }

    public function testIfCommandsAreRegistered(): void
    {
        $commands = $this->om()->get(CommandListInterface::class)->getCommands();

        $this->assertInstanceOf(ListModulesCommand::class, $commands['loki-checkout-core-cli:modules:list']);
        $this->assertSame('loki-checkout:modules:list', $commands['loki-checkout-core-cli:modules:list']->getName());
        $this->assertInstanceOf(DumpModulesCommand::class, $commands['loki-checkout-core-cli:modules:dump']);
        $this->assertSame('loki-checkout:modules:dump', $commands['loki-checkout-core-cli:modules:dump']->getName());
    }

    public function testDumpWritesActiveModules(): void
    {
        $tester = new CommandTester($this->createCommand());

        $this->assertSame(Command::SUCCESS, $tester->execute([]));
        $this->assertSame(['loki-checkout.json'], $this->getEtcFiles());

        $data = json_decode((string)file_get_contents($this->getJsonFile()), true, 512, JSON_THROW_ON_ERROR);
        $modules = array_column($data, null, 'name');
        $this->assertArrayHasKey('LokiCheckout_Core', $modules);
        $this->assertArrayHasKey('LokiCheckout_CoreCli', $modules);
        $this->assertSame(1, $modules['LokiCheckout_CoreCli']['enabled']);

        $root = rtrim($this->om()->get(DirectoryList::class)->getRoot(), '/') . '/';
        foreach ($data as $module) {
            $this->assertNotSame('', $module['path']);
            $this->assertStringStartsNotWith($root, $module['path']);
        }

        $this->assertStringContainsString('Dumped ' . count($data) . ' modules', $tester->getDisplay());
    }

    public function testExistingFileSurvivesEncodingFailure(): void
    {
        file_put_contents($this->getJsonFile(), '[]');

        $componentRegistrar = $this->createStub(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturn("/opt/\xB1invalid");
        $tester = new CommandTester($this->createCommand(['componentRegistrar' => $componentRegistrar]));

        $this->assertSame(Command::FAILURE, $tester->execute([]));
        $this->assertSame('[]', file_get_contents($this->getJsonFile()));
        $this->assertSame(['loki-checkout.json'], $this->getEtcFiles());
    }

    private function createCommand(array $arguments = []): DumpModulesCommand
    {
        $directoryList = new DirectoryList(BP, [
            DirectoryList::APP => [DirectoryList::PATH => $this->appFolder],
        ]);

        $filesystem = $this->om()->create(Filesystem::class, ['directoryList' => $directoryList]);

        return $this->om()->create(DumpModulesCommand::class, $arguments + [
            'filesystem' => $filesystem,
            'directoryList' => $directoryList,
        ]);
    }

    private function getJsonFile(): string
    {
        return $this->appFolder . '/etc/loki-checkout.json';
    }

    private function getEtcFiles(): array
    {
        return array_values(array_diff((array)scandir($this->appFolder . '/etc'), ['.', '..']));
    }
}
