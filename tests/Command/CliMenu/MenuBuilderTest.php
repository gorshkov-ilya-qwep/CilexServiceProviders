<?php

namespace OctoLab\Cilex\Command\CliMenu;

/**
 * @author Kamil Samigullin <kamil@samigullin.info>
 */
class MenuBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getItemCallback()
    {
        if ($this->isValidEnvironment()) {
            $builder = new MenuBuilder();
            $builder->addItem('test', function () {
                return 'success';
            });
            self::assertEquals('success', call_user_func($builder->getItemCallback('test')));
            try {
                self::assertEmpty(call_user_func($builder->getItemCallback('unknown')));
                self::fail(sprintf('%s exception expected.', \InvalidArgumentException::class));
            } catch (\InvalidArgumentException $e) {
                self::assertTrue(true);
            }
        } else {
            self::assertFalse(false);
        }
    }

    /**
     * @test
     */
    public function getItemCallbacks()
    {
        if ($this->isValidEnvironment()) {
            $builder = new MenuBuilder();
            self::assertCount(0, $builder->getItemCallbacks());
            $builder->addItem('test', function () {
                return 'success';
            });
            self::assertCount(1, $builder->getItemCallbacks());
        } else {
            self::assertFalse(false);
        }
    }

    /**
     * @return bool
     */
    private function isValidEnvironment()
    {
        return defined('HHVM_VERSION') || (defined('PHP_VERSION') && version_compare(PHP_VERSION, '5.6', '>='));
    }
}