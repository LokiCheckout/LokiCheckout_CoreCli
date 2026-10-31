<?php declare(strict_types=1);

namespace LokiCheckout\CoreCli\Test\Unit\Console\Command;

use LokiCheckout\Core\Config\LokiCheckoutModules;
use LokiCheckout\CoreCli\Console\Command\ListModulesCommand;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Module\Manager as ModuleManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Tester\CommandTester;

final class ListModulesCommandTest extends TestCase
{
    public function testTableListsModules(): void
    {
        $tester = $this->createTester(['LokiCheckout_Foo' => '/var/www/vendor/foo', 'LokiCheckout_Bar' => '/opt/bar']);

        $this->assertSame(Command::SUCCESS, $tester->execute([]));
        $display = $tester->getDisplay();
        $this->assertMatchesRegularExpression('/LokiCheckout_Foo\s*\|\s*Enabled\s*\|\s*\/var\/www\/vendor\/foo/', $display);
        $this->assertMatchesRegularExpression('/LokiCheckout_Bar\s*\|\s*Disabled\s*\|\s*\/opt\/bar/', $display);
    }

    public function testJsonHasUnescapedSlashes(): void
    {
        $tester = $this->createTester(['LokiCheckout_Foo' => '/var/www/vendor/foo']);

        $this->assertSame(Command::SUCCESS, $tester->execute(['--format' => 'json']));
        $display = $tester->getDisplay();
        $this->assertStringContainsString('"/var/www/vendor/foo"', $display);
        $this->assertSame(
            [['name' => 'LokiCheckout_Foo', 'enabled' => 1, 'path' => '/var/www/vendor/foo']],
            json_decode($display, true)
        );
    }

    public function testUnknownFormatReturnsInvalid(): void
    {
        $tester = $this->createTester(['LokiCheckout_Foo' => '/var/www/vendor/foo']);

        $this->assertSame(Command::INVALID, $tester->execute(['--format' => 'xml']));
        $this->assertStringContainsString('Invalid format "xml"', $tester->getDisplay());
    }

    public function testFormatWithoutValueIsRejected(): void
    {
        $tester = $this->createTester(['LokiCheckout_Foo' => '/var/www/vendor/foo']);

        $this->expectException(ExceptionInterface::class);
        $this->expectExceptionMessage('"--format" option requires a value');
        $tester->execute(['--format' => null]);
    }

    public function testInvalidUtf8JsonReturnsFailure(): void
    {
        $tester = $this->createTester(['LokiCheckout_Foo' => "/opt/\xB1foo"]);

        $this->assertSame(Command::FAILURE, $tester->execute(['--format' => 'json']));
        $this->assertStringContainsString('Unable to encode', $tester->getDisplay());
    }

    private function createTester(array $modulePaths): CommandTester
    {
        $lokiCheckoutModules = $this->createStub(LokiCheckoutModules::class);
        $lokiCheckoutModules->method('getModules')->willReturn(array_keys($modulePaths));

        $componentRegistrar = $this->createStub(ComponentRegistrar::class);
        $componentRegistrar->method('getPath')->willReturnCallback(
            fn (string $type, string $name) => $modulePaths[$name] ?? null
        );

        $moduleManager = $this->createStub(ModuleManager::class);
        $moduleManager->method('isEnabled')->willReturnCallback(
            fn (string $name) => $name !== 'LokiCheckout_Bar'
        );

        return new CommandTester(new ListModulesCommand($lokiCheckoutModules, $componentRegistrar, $moduleManager));
    }
}
