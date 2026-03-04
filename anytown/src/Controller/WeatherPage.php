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
        if ($forecast_data) {
            $forecast = '<ul>';
            foreach ($forecast_data as $item) {
                [
                    'weekday' => $weekday,
                    'description' => $description,
                    'high' => $high,
                    'low' => $low,
                ] = $item;
                $forecast .= "<li>$weekday will be <em>$description</em> with a high of $high and a low of $low.</li>";
            }
            $forecast .= '</ul>';
        } else {
            $forecast = '<p>Could not get the weather forecast. Dress for anything.</p>';
        }

        $output = "<p>Check out this weekend's weather forecast and come prepared. The market is mostly outside, and takes place rain or shine.</p>";
        $output .= $forecast;
        $output .= '<h3>Weather related closures</h3></h3><ul><li>Ice rink closed until winter - please stay off while we prepare it.</li><li>Parking behind Apple Lane is still closed from all the rain last week.</li></ul>';


        return [
            '#markup' => $output,
        ];

        /*
        $build['content'] = [
            '#type' => 'markup',
            '#markup' => '<p>The weather forecast for this week is sunny with a chance of meatballs.</p>',
        ];

        if ($style === 'extended') {
            $build['content_extended'] = [
                '#type' => 'markup',
                '#markup' => '<p><strong>Extended forecast:</strong> Looking ahead to next week we expect some snow.</p>',
            ];
        }
        return $build;
        */
    }
}
