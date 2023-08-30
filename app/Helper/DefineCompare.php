<?php

namespace App\Helper;


class DefineCompare
{

    static function getOperator($operator)
    {
        if($operator == 'equal' || $operator == 'e' || $operator == 'eq') {
            return '==';
        } 
        if($operator == 'less_than' || $operator == 'lt' ) {
            return '<';
        } 
        if($operator == 'less_than_equal' || $operator == 'lte' ) {
            return '<=';
        } 
        if($operator == 'greater_than' || $operator == 'gt' ) {
            return '>';
        } 
        if($operator == 'greater_than_equal' || $operator == 'gte' ) {
            return '>=';
        } 
        if($operator == 'not_equal' || $operator == 'ne' ) {
            return '!=';
        } 
        return '==';
    }

}
