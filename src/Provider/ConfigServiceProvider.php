<?php

namespace OctoLab\Cilex\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
use OctoLab\Cilex\Config\Loader\YamlFileLoader;
use OctoLab\Cilex\Config\SimpleConfig;
use OctoLab\Cilex\Config\YamlConfig;
use Symfony\Component\Config\FileLocator;

/**
 * @author Kamil Samigullin <kamil@samigullin.info>
 *
 * @see \Cilex\Provider\ConfigServiceProvider
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    /** @var string */
    private $filename;
    /** @var array */
    private $placeholders;

    /**
     * @param string $filename
     * @param array $placeholders
     *
     * @api
     */
    public function __construct($filename, array $placeholders = [])
    {
        $this->filename = $filename;
        $this->placeholders = $placeholders;
    }

    /**
     * @param Application $app
     *
     * @api
     */
    public function register(Application $app)
    {
        $app['config'] = $app->share(function () {
            switch (strtolower(pathinfo($this->filename, PATHINFO_EXTENSION))) {
                case 'yml':
                    $config = (new YamlConfig(new YamlFileLoader(new FileLocator())))
                        ->load($this->filename)
                        ->replace($this->placeholders)
                        ->toArray()
                    ;
                    break;
                case 'php':
                    $config = (new SimpleConfig(include $this->filename))
                        ->replace($this->placeholders)
                        ->toArray()
                    ;
                    break;
                case 'json':
                    $config = json_decode(file_get_contents($this->filename), true);
                    break;
                default:
                    throw new \DomainException(sprintf('File "%s" is not supported.', $this->filename));
            }
            if (!is_array($config)) {
                throw new \RuntimeException('Configuration must be an array.');
            }
            return $config;
        });
    }
}
