<?php

declare(strict_types=1);

namespace Drupal\anytown\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides a hello world block.
 */
#[Block(
    id: 'anytown_hello_world',
    admin_label: new TranslatableMarkup('Hello World'),
    category: new TranslatableMarkup('Custom')
)]
class HelloWorldBlock extends BlockBase implements ContainerFactoryPluginInterface
{

    private $currentUser;


    public function __construct(array $configuration, $plugin_id, $plugin_definition,  AccountProxyInterface $current_user)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->currentUser = $current_user;
    }

    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('current_user')
        );
    }


    /**
     * {@inheritdoc}
     */
    public function build(): array
    {
        if ($this->currentUser->isAuthenticated()) {
            $build['content'] = [
                '#markup' => $this->t('Hello, @name! Welcome back.', ['@name' => $this->currentUser->getDisplayName()]),
            ];
        } else {
            $build['content'] = [
                '#markup' => $this->t('Hello world!'),
            ];
        }
        $build['content']['#cache'] = [
            'max-age' => 0,
        ];
        return $build;
    }
}
