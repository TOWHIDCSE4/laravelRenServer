<?php

namespace App\Helper;

use App\Helper\PlaceJsonSpeed;

class Place
{

    static function handleFile()  //Hiện tại ko lưu trong Storage
    {
        // $contents = Storage::get('location/vn/vn.json');


        //$jsonFile = json_decode($contents, true);


        $jsonFile = PlaceJsonSpeed::jsonSpeed();

        return $jsonFile;
    }

    static function getNameProvince($id)
    {
        $data = null;
        $id = (int)$id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            if ((int)$value["level1_id"] == $id) {
                return $value["name"];
            }
        }

        return $data;
    }

    static function getNameDistrict($id)
    {
        $data = null;
        $id = (int)$id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            foreach ($value["level2s"] as $key => $value2) {
                if ((int)$value2["level2_id"] == $id) {
                    return $value2["name"];
                }
            }
        }

        return $data;
    }

    static function getNameWards($id)
    {
        $data = null;
        $id = (int)$id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            foreach ($value["level2s"] as $key => $value2) {
                foreach ($value2["level3s"] as $key => $value3) {
                    if ((int)$value3["level3_id"] == $id) {
                        return $value3["name"];
                    }
                }
            }
        }

        return $data;
    }

    static function getListProvince($parent_id)
    {
        $data = array();
        $parent_id = (int)$parent_id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            array_push($data, [
                "id" => (int)$value["level1_id"],
                "name" => $value["name"],
                "type" => $value["type"],
            ]);
        }

        return $data;
    }

    static function getListDistrict($parent_id)
    {
        $data = array();
        $parent_id = (int)$parent_id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            if ((int)$value["level1_id"] == $parent_id) {
                foreach ($value["level2s"] as $key => $value2) {
                    array_push($data, [
                        "id" => (int)$value2["level2_id"],
                        "name" => $value2["name"],
                        "type" => $value2["type"],
                    ]);
                }
            }
        }

        return $data;
    }

    static function getListWards($parent_id)
    {
        $data = array();
        $parent_id = (int)$parent_id;
        $jsonFile = Place::handleFile();

        foreach ($jsonFile["data"] as $key => $value) {
            foreach ($value["level2s"] as $key => $value2) {
                if ((int)$value2["level2_id"] == $parent_id) {
                    foreach ($value2["level3s"] as $key => $value3) {
                        array_push($data, [
                            "id" => (int)$value3["level3_id"],
                            "name" => $value3["name"],
                            "type" => $value3["type"],
                        ]);
                    }
                }
            }
        }

        return $data;
    }
}
