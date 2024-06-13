<?php

namespace App\Services;

use App\Exceptions\OpenWeatherException;
use Illuminate\Support\Facades\Http;

class OpenWeatherService
{
    private $apiKey = "";
    private $baseUrl = "https://api.openweathermap.org/data/2.5/";

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->apiKey = config("api.openweather.apiKey");
    }

    public function getNowWeather(float $lat, float $lng): array
    {
        $url = "{$this->baseUrl}weather?lat={$lat}&lon={$lng}&exclude=current&appid={$this->apiKey}";
        $response = Http::get($url);
        if ($response->failed()) {
            throw new OpenWeatherException();
        }
        $body = $response->json();
        $timezone = $body['timezone'];
        return [
            // 日の出関連
            'suns' => [
                'sunrise_at'    => $body['sys']['sunrise'] + $timezone,
                'sunrise'       => date("Y-m-d H:i:s", $body['sys']['sunrise'] + $timezone),
                'sunset_at'     => $body['sys']['sunset'] + $timezone,
                'sunset'        => date("Y-m-d H:i:s", $body['sys']['sunset'] + $timezone),
            ],

            // 気温関連
            'temps' => [
                'now'           => $this->k2c($body['main']['temp']),
                'min'           => $this->k2c($body['main']['temp_min']),
                'max'           => $this->k2c($body['main']['temp_max']),
                'feels_like'    => $this->k2c($body['main']['feels_like']),
                'humidity'      => $body['main']['humidity'],
                'discomfort'    => $this->discomfort($this->k2c($body['main']['temp']), $body['main']['humidity']),
            ],
        ];
    }

    /**
     * ケルビン（kelvin）から（Celsius温度）に変換
     */
    private function k2c(float $kelvin): float {
        return bcsub($kelvin, 273.0, 2);
    }
    /**
     * 不快度指数の計算
     */
    private function discomfort(float $temp, float $humidity): float {
        $num1 = bcmul(0.81, $temp, 2);
        $num2 = bcmul(bcmul(0.01, $humidity, 2), bcsub(bcmul(0.99, $temp, 2), 14.3, 2), 2);
        $num3 = 46.3;
        return bcadd(bcadd($num1, $num2, 2), $num3, 2);
    }
}
