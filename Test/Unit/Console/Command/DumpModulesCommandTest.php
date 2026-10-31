<?php declare(strict_types=1);

namespace LokiCheckout\CoreCli\Test\Unit\Console\Command;

use LokiCheckout\Core\Config\LokiCheckoutModules;
use LokiCheckout\CoreCli\Console\Command\DumpModulesCommand;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Phrase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class DumpModulesCommandTest extends TestCase
{
    private WriteInterface&MockObject $directoryWrite;
    private array $writes = [];
    private array $renames = [];

    protected function setUp(): void
    {
        $this->writes = [];
        $this->renames = [];
        $this->directoryWrite = $this->createMock(WriteInterface::class);
    }

    public function testRelativePathOnlyStripsRootPrefix(): void
    {
        $this->expectSuccessfulWrite();
        $tester = $this->createTester('/var/www', [
            'LokiCheckout_Foo' => '/var/www/vendor/loki-checkout/foo',
            'LokiCheckout_Bar' => '/data/var/www/x/LokiCheckout_Bar',
        ]);

        $this->assertSame(Command::SUCCESS, $tester->execute([]));
        $paths = array_column($this->getWrittenData(), 'path', 'name');
        $this->assertSame('vendor/loki-checkout/foo', $paths['LokiCheckout_Foo']);
        $this->assertSame('/data/var/www/x/LokiCheckout_Bar', $paths['LokiCheckout_Bar']);
    }

    public function testRootWithTrailingSlash(): void
    {
        $this->expectSuccessfulWrite();
        $tester = $this->createTester('/var/www/', ['LokiCheckout_Foo' => '/var/www/app/code/LokiCheckout/Foo']);

        $tester->execute([]);
        $this->assertSame('app/code/LokiCheckout/Foo', $this->getWrittenData()[0]['path']);
    }

    public function testWriteGoesThroughTempFileAndRename(): void
    {
        $this->expectSuccessfulWrite();
        $tester = $this->createTester('/var/www', ['LokiCheckout_Foo' => '/var/www/vendor/foo']);

        $this->assertSame(Command::SUCCESS, $tester->execute([]));
        $this->assertCount(1, $this->writes);
        $tmpFile = array_key_first($this->writes);
        $this->assertMatchesRegularExpression('#^etc/loki-checkout\.json\.[a-f0-9]+\.tmp$#', $tmpFile);
        $this->assertSame([[$tmpFile, 'etc/loki-checkout.json']], $this->renames);
        $this->assertSame(
            [['name' => 'LokiCheckout_Foo', 'enabled' => 1, 'path' => 'vendor/foo']],
            json_decode($this->writes[$tmpFile], true)
        );
        $this->assertStringNotContainsString('\/', $this->writes[$tmpFile]);
    }

    public function testInvalidUtf8PathFailsWithoutWriting(): void
    {
        $this->directoryWrite->expects($this->never())->method('writeFile');
        $this->directoryWrite->expects($this->never())->method('renameFile');
        $tester = $this->createTester('/var/www', ['LokiCheckout_Foo' => "/opt/\xB1foo"]);

        $this->assertSame(Command::FAILURE, $tester->execute([]));
        $this->assertStringContainsString('Unable to encode', $tester->getDisplay());
    }

    public function testFilesystemExceptionReturnsFailureAndCleansTempFile(): void
    {
        $this->directoryWrite->method('writeFile')->willReturn(10);
        $this->directoryWrite->method('renameFile')
            ->willThrowException(new FileSystemException(new Phrase('Rename failed')));
        $this->directoryWrite->expects($this->once())->method('delete')
            ->with($this->matchesRegularExpression('#^etc/loki-checkout\.json\.[a-f0-9]+\.tmp$#'));
        $tester = $this->createTester('/var/www', ['LokiCheckout_Foo' => '/var/www/vendor/foo']);

        $this->assertSame(Command::FAILURE, $tester->execute([]));
        $this->assertStringContainsString('Rename failed', $tester->getDisplay());
    }

    public function testOutputsSummary(): void
    {
        $this->expectSuccessfulWrite();
        $tester = $this->createTester('/var/www', [
            'LokiCheckout_Foo' => '/var/www/vendor/foo',
            'LokiCheckout_Bar' => '/var/www/vendor/bar',
        ]);

        $tester->execute([]);
        $this->assertStringContainsString('Dumped 2 modules to app/etc/loki-checkout.json', $tester->getDisplay());
    }

    private function expectSuccessfulWrite(): void
    {
        $this->directoryWrite->expects($this->once())->method('writeFile')->willReturnCallback(function (string $path, string $content) {
            $this->writes[$path] = $content;
            return strlen($content);
        });
        $this->directoryWrite->expects($this->once())->method('renameFile')->willReturnCallback(function (string $path, string $newPath) {
            $this->renames[] = [$path, $newPath];
            return true;
        });
    }

    private function getWrittenData(): array
    {
        $this->assertCount(1, $this->writes);
        return json_decode((string)reset($this->writes), true, 512, JSON_THROW_ON_ERROR);
    }

    private function createTester(string $root, array $modulePaths): CommandTester
    {
        $lokiCheckoutModules = $this->createStub(LokiCheckoutModules::class);
        $lokiCheckoutModules->method('getActiveModules')->willReturn(array_keys($modulePaths));

        $componentRegistrar = $this->createStub(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturnCallback(
            fn (string $type, string $name) => $modulePaths[$name] ?? null
        );

        $moduleManager = $this->createStub(ModuleManager::class);
        $moduleManager->method('isEnabled')->willReturn(true);

        $filesystem = $this->createStub(Filesystem::class);
        $filesystem->method('getDirectoryWrite')->willReturn($this->directoryWrite);

        $directoryList = $this->createStub(DirectoryList::class);
        $directoryList->method('getRoot')->willReturn($root);

        return new CommandTester(new DumpModulesCommand(
            $lokiCheckoutModules,
            $componentRegistrar,
            $moduleManager,
            $filesystem,
            $directoryList
        ));
    }
}
