<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
trait ApiBugAtlas
{

    
    private $baseURL = 'https://api.bugatlas.com/v1';


    public function processApiResponse($endPoint, $body)
    {
        $response = Http::withHeaders($this->getApiHeaders())->post($this->baseURL . $endPoint, $body);
        return $response;
        
    }



    private function getApiHeaders()
    {
        return [
            'api_key' => config('bugatlas.api_key'),
            'secret_key' => config('bugatlas.secret_key'),
            'Content-Type' => 'application/json'
        ];
    }
}