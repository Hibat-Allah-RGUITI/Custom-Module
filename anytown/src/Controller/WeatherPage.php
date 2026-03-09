<?php

declare(strict_types=1);

namespace Drupal\anytown\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\anytown\ForecastClientInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\path_alias\AliasManagerInterface;

class WeatherPage extends ControllerBase
{
    /**
     * Forecast API client.
     *
     * @var \Drupal\anytown\ForecastClientInterface
     */
    private $forecastClient;

    private $alias_manager;


    /**
     * WeatherPage controller constructor.
     */
    public function __construct(ForecastClientInterface $forecast_client, AliasManagerInterface $alias_manager)
    {
        $this->forecastClient = $forecast_client;
        $this->alias_manager = $alias_manager;
    }

    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('anytown.forecast_client'),
            $container->get('path_alias.manager'),
        );
    }

    public function build(string $style): array
    {
        $style = (in_array($style, ['short', 'extended'])) ? $style : 'short';
        $settings = $this->config('anytown.settings');

        $url = 'https://raw.githubusercontent.com/DrupalizeMe/module-developer-guide-demo-site/main/backups/weather_forecast.json';

        if ($location=$settings->get('location')){
            $url .='?location=' . $location;
        }

        $forecast_data = $this->forecastClient->getForecastData($url);

        $rows = [];
        $highest = 0;
        $lowest = 0;

        $short_forecast = [];

        if ($forecast_data) {
            foreach ($forecast_data as $item) {
                [
                    'weekday' => $weekday,
                    'description' => $description,
                    'high' => $high,
                    'low' => $low,
                    'icon' => $icon,
                ] = $item;

                $rows[] = [
                    $weekday,
                    [
                        'data' => [
                            '#markup' => '<img alt="' . $description . '" src="' . $icon . '" width="50" height="50" />',
                        ],
                    ],
                    [
                        'data' => [
                            '#markup' => "<em>{$description}</em> with a high of {$high} and a low of {$low}",
                        ],
                    ],
                ];
                $highest = max($highest, $high);
                $lowest = min($lowest, $low);
            }

            $weather_forecast = [
                '#type' => 'table',
                '#header' => ['Day', '', 'Forecast'],
                '#rows' => $rows,
                '#attributes' => ['class' => ['weather_page--forecast-table']],
            ];

            $short_forecast = [
                '#markup' => '<p>' . $this->t(
                    'The high for the weekend is @highest and the low is @lowest.',
                    ['@highest' => $highest, '@lowest' => $lowest]
                ) . '</p>',
            ];
        } else {
            $weather_forecast = [
                '#markup' => "<p>" . $this->t('Could not get the weather forecast.') . "</p>"
            ];
        }

        $alias = $this->alias_manager->getAliasByPath('/node/1');

        $url_object = Url::fromRoute('entity.node.canonical', ['node' => 1]);
        $link = Link::fromTextAndUrl($this->t('View node 1'), $url_object)->toString();

        $build = [
            '#theme' => 'weather_page',
            '#attached' => [
                'library' => ['anytown/forecast'],
            ],
            '#weather_intro' => [
                '#markup' => "<p>" . $this->t("Check out this weekend's weather forecast.") . "</p>",
            ],
            '#weather_forecast' => $weather_forecast,
            '#short_forecast' => $short_forecast,
            '#weather_closures' => [
                '#theme' => 'item_list',
                '#title' => $this->t('Weather related closures'),
                '#items' => explode(PHP_EOL, $settings->get('weather_closures')),

            ],
            '#node_alias' => ['#markup' => '<p>Alias: ' . $alias . '</p>'],
            '#node_link' => ['#markup' => '<p>Link: ' . $link . '</p>'],
        ];

        return $build;
    }
}
