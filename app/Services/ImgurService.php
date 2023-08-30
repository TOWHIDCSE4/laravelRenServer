<?php

namespace App\Services;

use App\Models\ImgurImage;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client as GuzzleClient;

class ImgurService
{
    const END_POINT = 'https://api.imgur.com/3/image';

    public static function uploadImage($imagePath)
    {
        $client = new GuzzleClient();
        $request = $client->request(
            'POST',
            ImgurService::END_POINT,
            [
                'headers' => [
                    'Authorization' => "Client-ID 0cc42cc3b24f928", // post as anonymous
                ],
                'form_params' => [
                    'image' => @file_get_contents($imagePath)
                ]
            ]
        );
        $response = (string) $request->getBody();
        $jsonResponse = json_decode($response);

        $imgurImageCreate = ImgurImage::create(
            [
                'link' => $jsonResponse->data->link,
            ]
        );

        return $jsonResponse->data->link; // return url of image
    }
}
