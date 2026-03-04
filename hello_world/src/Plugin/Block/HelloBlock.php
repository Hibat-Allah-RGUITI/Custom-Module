<?php

namespace Drupal\hello_world\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;


class HelloBlock extends BlockBase implements ContainerFactoryPluginInterface {

    /**
     * The config factory service.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected ConfigFactoryInterface $configFactory;

    /**
     * Constructor.
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->configFactory = $config_factory;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new self(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('config.factory')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function build(): array {
        // Récupérer la valeur par défaut depuis la configuration.
        $default_config = $this->configFactory->get('hello_world.settings');
        $hello_name = $default_config->get('hello.name') ?? 'Default Name';

        return [
            '#theme' => 'hello_block',
            '#custom_data' => ['name' => $hello_name, 'age' => 23],
            '#custom_string' => 'Hello Block',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration(): array {
        $default_config = $this->configFactory->get('hello_world.settings');
        return [
            'hello_block_name' => $default_config->get('hello.name') ?? 'Default Name',
        ];
    }
}