<?php

namespace App\Services;

use App\Helper\DefineFolderSaveFile;
use App\Helper\ResponseUtils;
use App\Models\MsgCode;
use GuzzleHttp\Client as GuzzleClient;

class UploadVideoService
{
    // const END_POINT = 'https://data3gohomy.ikitech.vn/api/video-upload';
    const END_POINT = 'https://data1.rencity.vn/api/video-upload';

    public static function uploadVideo($videoPath)
    {
        $type = request()->type ?? "ANOTHER_FILES_FOLDER";

        if ($videoPath == null) {
            return ResponseUtils::json([
                'code' => 400,
                'success' => false,
                'msg_code' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[0],
                'msg' => MsgCode::UNABLE_TO_FIND_THE_UPLOAD_IMAGE[1],
            ]);
        }

        $client = new GuzzleClient();

        if (request()->type == null || DefineFolderSaveFile::checkContainFolder(request()->type) == null) {
            $type = "ANOTHER_FILES_FOLDER";
        }


        try {
            $response = $client->request(
                'POST',
                UploadVideoService::END_POINT,
                [
                    'multipart' => [
                        [
                            'name'     => 'video',
                            'contents' => @file_get_contents($videoPath),
                            'Content-type' => 'multipart/form-data',
                            'filename' => 'dsadsad.png',
                        ],
                    ],
                    [
                        'name' => 'type',
                        'contents' => $type
                    ]
                ]

            );

            if ($response->getStatusCode() != 200) {
                return MsgCode::CANNOT_POST_VIDEOS;
            }


            $body = (string) $response->getBody();
            $jsonResponse = json_decode($body);


            return $jsonResponse->link;
        } catch (\GuzzleHttp\Exception\RequestException $e) {

            return MsgCode::CANNOT_POST_VIDEOS;
        }
    }
}
