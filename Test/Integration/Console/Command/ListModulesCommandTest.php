<?php declare(strict_types=1);

namespace LokiCheckout\CoreCli\Test\Integration\Console\Command;

use LokiCheckout\CoreCli\Console\Command\ListModulesCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Yireo\IntegrationTestHelper\Test\Integration\Traits\GetObjectManager;

final class ListModulesCommandTest extends TestCase
{
    use GetObjectManager;

    public function testJsonFormatListsModules(): void
    {
        $tester = new CommandTester($this->om()->create(ListModulesCommand::class));

        $this->assertSame(Command::SUCCESS, $tester->execute(['--format' => 'json']));
        $modules = array_column(json_decode($tester->getDisplay(), true, 512, JSON_THROW_ON_ERROR), null, 'name');
        $this->assertArrayHasKey('LokiCheckout_CoreCli', $modules);
        $this->assertSame(1, $modules['LokiCheckout_CoreCli']['enabled']);
    }

    public function testUnknownFormatIsRejected(): void
    {
        $tester = new CommandTester($this->om()->create(ListModulesCommand::class));

        $this->assertSame(Command::INVALID, $tester->execute(['--format' => 'xml']));
    }
}
