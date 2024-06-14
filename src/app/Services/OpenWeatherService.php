<?php

namespace App\Services;

use App\Enums\Weather;
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
        $weatherInfo = Weather::from($body['weather'][0]['main']);
        $temp = $this->k2c($body['main']['temp']);
        $wetTemp = $this->wetTemp($temp, $body['main']['humidity']);

        return [
            // 位置情報
            'coord' => [
                'lat' => $lat,
                'lon' => $lng,
            ],
                
            // 日の出関連
            'suns' => [
                'sunrise_at'    => $body['sys']['sunrise'] + $timezone,
                'sunrise'       => date("Y-m-d H:i:s", $body['sys']['sunrise'] + $timezone),
                'sunset_at'     => $body['sys']['sunset'] + $timezone,
                'sunset'        => date("Y-m-d H:i:s", $body['sys']['sunset'] + $timezone),
            ],

            // 気温関連
            'temps' => [
                'now'           => $temp,
                'min'           => $this->k2c($body['main']['temp_min']),
                'max'           => $this->k2c($body['main']['temp_max']),
                'feels_like'    => $this->k2c($body['main']['feels_like']),
                'humidity'      => $body['main']['humidity'],
                'discomfort'    => $this->discomfort($temp, $body['main']['humidity']),
                'wet_temp'      => $wetTemp,
                'wbgt'          => $this->wbgt($temp, $wetTemp),
            ],

            // 天候
            'weather' => [
                'info' => $weatherInfo,
            ],
        ];
    }

    /**
     * 湿球温度の計算
     */
    private function wetTemp(float $temp, float $humidity): float
    {
        $num1 = bcmul(0.151977, pow(bcadd($humidity, 8.313659), 0.5));
        $num2 = bcadd($temp, $humidity);
        $num3 = bcsub($humidity, 1.676331);
        $num4 = bcmul(0.023101, $humidity);

        $wetTemp = $temp * atan($num1) + atan($num2) - atan($num3) + 0.00391838 * pow($humidity, 1.5) * atan($num4) - 4.686035;
        return round($wetTemp);
    }

    /**
     * WBGTの計算
     */
    private function wbgt(float $temp, float $wet_temp): float
    {
        $wbgt = bcadd(bcmul(0.7, $wet_temp), bcmul(0.3, $temp));
        return round($wbgt, 2);
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
