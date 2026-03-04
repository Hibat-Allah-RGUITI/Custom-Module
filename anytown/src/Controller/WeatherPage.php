<?php

declare(strict_types=1);

namespace Drupal\anytown\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\anytown\ForecastClientInterface;

class WeatherPage extends ControllerBase
{

    /**
     * Forecast API client.
     *
     * @var \Drupal\anytown\ForecastClientInterface
     */
    private $forecastClient;

    /**
     * WeatherPage controller constructor.
     *
     * @param \Drupal\anytown\ForecastClientInterface $forecast_client
     *   Forecast API client service.
     */

    //private $httpClient;

    //private $logger;

    public function __construct(ForecastClientInterface $forecast_client)
    {
        $this->forecastClient = $forecast_client;
    }


    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('anytown.forecast_client')
        );
    }


    public function build(string $style): array
    {
        $style = (in_array($style, ['short', 'extended'])) ? $style : 'short';
        $url = 'https://raw.githubusercontent.com/DrupalizeMe/module-developer-guide-demo-site/main/backups/weather_forecast.json';
        /*$data = NULL;

        try {
            $response = $this->httpClient->request('GET', $ur);
            $data = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            $this->logger->log(RfcLogLevel::ERROR, 'Failed to fetch weather data: @message', ['@message' => $e->getMessage()]);
        }*/

        $forecast_data = $this->forecastClient->getForecastData($url);

        $rows = [];
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
                            '#markup' => '<img alt="' . $description . '" src="' . $icon . '" width="200" height="200" />',
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
                '#header' => [
                    'Day',
                    '',
                    'Forecast',
                ],
                '#rows' => $rows,
                '#attributes' => [
                    'class' => ['weather_page--forecast-table'],
                ],
            ];

            $short_forecast = [
                '#type' => 'markup',
                '#markup' => "The high for the weekend is {$highest} and the low is {$lowest}.",
            ];
        } else {
            $weather_forecast = ['#markup' => "<p>Could not get the weather forecast. Dress for anything.</p>"];
        }

        $build = [
            '#theme' => 'weather_page',
            '#weather_intro' => [
                '#markup' => "<p>Check out this weekend's weather forecast and come prepared. The market is mostly outside, and takes place rain or shine.</p>",
            ],
            '#weather_forecast' => $weather_forecast,
            '#short_forecast' => $short_forecast,
            '#weather_closures' => [
                '#theme' => 'item_list',
                '#title' => 'Weather related closures',
                '#items' => [
                    'Ice rink closed until winter - please stay off while we prepare it.',
                    'Parking behind Apple Lane is still closed from all the rain last weekend.',
                ],
            ],
        ];

        return $build;
    }
}
