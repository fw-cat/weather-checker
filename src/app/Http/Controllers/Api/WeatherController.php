<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OpenWeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WeatherController extends Controller
{
    public function index(Request $request, float $lat, float $lng, OpenWeatherService $service): JsonResponse
    {
        $weather = $service->getNowWeather($lat, $lng);
        return new JsonResponse($weather, Response::HTTP_OK);
    }
}
