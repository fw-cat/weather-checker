<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class OpenWeatherException extends Exception
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function context(): array
    {
        return [
            'message' => "OpenWeatherの取得に失敗しました",
            'status' => Response::HTTP_BAD_REQUEST
        ];
    }
}
