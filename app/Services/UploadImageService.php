<?php

namespace App\Services;

use App\Helper\DefineFolderSaveFile;
use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use GuzzleHttp\Client as GuzzleClient;

class UploadImageService
{
    // const END_POINT = 'https://data3gohomy.ikitech.vn/api/image-upload-new';
    const END_POINT = 'https://data1.rencity.vn/api/image-upload-new';

    public static function uploadImage($imagePath)
    {
        $type = request()->type ?? "ANOTHER_FILES_FOLDER";
        if ($imagePath == null) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[0],
                'msg' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[1],
            ]);
        }

        if (request()->type == null || DefineFolderSaveFile::checkContainFolder(request()->type) == null) {
            $type = "ANOTHER_FILES_FOLDER";
        }

        $client = new GuzzleClient();


        try {
            $response = $client->request(
                'POST',
                UploadImageService::END_POINT,
                [
                    'multipart' => [
                        [
                            'name'     => 'image',
                            'contents' => @file_get_contents($imagePath),
                            'Content-type' => 'multipart/form-data',
                            'filename' => 'dsadsad.png',
                        ],
                        [
                            'name' => 'type',
                            'contents' => $type
                        ]
                    ],
                ]
            );

            if ($response->getStatusCode() != 200) {
                return MsgCode::CANNOT_POST_PICTURES;
            }


            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);


            return $jsonResponse->link;
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            return MsgCode::CANNOT_POST_PICTURES;
        }
    }
}
