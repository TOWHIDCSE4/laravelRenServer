<?php

namespace App\Helper;


class TypeOfStoreHelper
{
    static function handleFile()
    {
        $types = config('saha.type_store.type_store');
        return $types;
    }

    static function getNametype($id)
    {
        $types = TypeOfStoreHelper::handleFile();
        foreach($types as $type) {
            if($type['id'] == $id) {
                return $type['name'];
            }
        }
        return null;
    }

    static function getNameCareer($id)
    {
        
        $types = TypeOfStoreHelper::handleFile();
        foreach($types as $type) {
                foreach($type['childs'] as $career) {
                    if($career['id'] == $id) {
                        return $career['name'];
                    }
                }
               
        }
        return null;
    }

}
