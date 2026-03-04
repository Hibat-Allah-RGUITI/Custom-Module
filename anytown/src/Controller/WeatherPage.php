<?php

declare(strict_types=1);

namespace Drupal\anytown\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Logger\RfcLogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WeatherPage extends ControllerBase
{

    private $httpClient;

    private $logger;

    public function __construct(ClientInterface $http_client)
    {
        $this->httpClient = $http_client;
        $this->logger = $this->getLogger('anytown');
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('http_client')
        );
    }


    public function build(string $style): array
    {
        $style = (in_array($style, ['short', 'extended'])) ? $style : 'short';
        $ur = 'https://raw.githubusercontent.com/DrupalizeMe/module-developer-guide-demo-site/main/backups/weather_forecast.json';
        $data = NULL;

        try {
            $response = $this->httpClient->request('GET', $ur);
            $data = json_decode($response->getBody()->getContents());
        } catch (RequestException $e) {
            $this->logger->log(RfcLogLevel::ERROR, 'Failed to fetch weather data: @message', ['@message' => $e->getMessage()]);
        }

        if ($data) {
            $forrecast = '<ul>';
            foreach ($data->list as $day) {
                $weekday = ucfirst($day->day);
                $description = array_shift($day->weather)->description;
                $high = round(($day->main->temp_max - 273.15) * 9 / 5 + 32);
                $low = round(($day->main->temp_min - 273.15) * 9 / 5 + 32);
                $forecast .= "<li>$weekday will be <em>$description</em> with a high of $high and a low of $low.</li>";
            }
            $forrecast .= '</ul>';
        } else {
            $forrecast = '<p>Unable to fetch weather data at this time.</p>';
        }

        $output = "<p>Check out this weekend's weather forecast and come prepared. The market is mostly outside, and takes place rain or shine.</p>";
        $output .= $forecast;

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
