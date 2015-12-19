<?php

namespace Test\OctoLab\Cilex\Command;

use Cilex\Application;
use OctoLab\Cilex\Command\Command;
use OctoLab\Cilex\Provider\ConfigServiceProvider;
use OctoLab\Cilex\Provider\DoctrineServiceProvider;
use OctoLab\Cilex\Provider\MonologServiceProvider;
use Test\OctoLab\Cilex\TestCase;
use Symfony\Component\Console\Output\NullOutput;

/**
 * phpunit tests/Command/CommandTest.php
 *
 * @author Kamil Samigullin <kamil@samigullin.info>
 */
class CommandTest extends TestCase
{
    /**
     * @test
     */
    public function commandNamespace()
    {
        $command = $this->getCommandMock('test');
        self::assertEquals('test', $command->getName());
        $command = $this->getCommandMock('test', 'mock');
        self::assertEquals('mock:test', $command->getName());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function getDbConnectionFail()
    {
        $app = new Application('Test');
        $command = $this->getCommandMock();
        $app->command($command);
        self::assertInstanceOf('\Doctrine\DBAL\Connection', $command->getDbConnection());
    }

    /**
     * @test
     * @dataProvider doctrineConfigProvider
     *
     * @param ConfigServiceProvider $config
     */
    public function getDbConnectionSuccess(ConfigServiceProvider $config)
    {
        $app = new Application('Test');
        $app->register($config);
        $app->register(new DoctrineServiceProvider());
        $command = $this->getCommandMock();
        $app->command($command);
        self::assertInstanceOf('\Doctrine\DBAL\Connection', $command->getDbConnection());
    }

    /**
     * @test
     * @expectedException \RuntimeException
     */
    public function getLoggerFail()
    {
        $app = new Application('Test');
        $command = $this->getCommandMock();
        $app->command($command);
        self::assertInstanceOf('\Psr\Log\LoggerInterface', $command->getLogger());
    }

    /**
     * @test
     * @dataProvider monologConfigProvider
     *
     * @param ConfigServiceProvider $config
     */
    public function getLoggerSuccess(ConfigServiceProvider $config)
    {
        $app = new Application('Test');
        $app->register($config);
        $app->register(new MonologServiceProvider());
        $command = $this->getCommandMock();
        $app->command($command);
        self::assertInstanceOf('\Psr\Log\LoggerInterface', $command->getLogger());
    }

    /**
     * @test
     * @dataProvider monologConfigProvider
     *
     * @param ConfigServiceProvider $config
     */
    public function setOutputInterface(ConfigServiceProvider $config)
    {
        $output = new NullOutput();
        $app = new Application('Test');
        $app->register($config);
        $app->register(new MonologServiceProvider(true));
        $command = $this->getCommandMock();
        $app->command($command);
        $command->initConsoleHandler($output);
    }

    /**
     * @param string $name
     * @param string $namespace
     *
     * @return Command
     */
    private function getCommandMock($name = 'test', $namespace = null)
    {
        /** @var Command $instance */
        $instance = (new \ReflectionClass(Command::class))->newInstanceWithoutConstructor();
        $reflection = (new \ReflectionObject($instance));
        if (null !== $namespace) {
            $property = $reflection->getProperty('namespace');
            $property->setAccessible(true);
            $property->setValue($instance, $namespace);
        }
        $instance->setName($name);
        $reflection->getConstructor()->invoke($instance);
        return $instance;
    }
}