<?php

namespace App\Helper;


class ShiftUtils
{

    const START_GREATE_END = 0;
    const START_LESS_END = 1;
    const START_EQUAL_END = 2;

    static function compareTimeWork(
        $start_hour,
        $start_minute,
        $end_hour,
        $end_minute
    ) {
        if ($start_hour <  $end_hour) {
            return  ShiftUtils::START_LESS_END;
        }

        if ($start_hour >  $end_hour) {
            return  ShiftUtils::START_GREATE_END;
        }


        if ($start_minute <  $end_minute) {
            return  ShiftUtils::START_LESS_END;
        }

        if ($start_minute > $end_minute) {
            return  ShiftUtils::START_GREATE_END;
        }

        return ShiftUtils::START_EQUAL_END;
    }

    static function checkHour(
        $hour
    ) {
        if ($hour === null || $hour > 23 || $hour < 0) {
            return false;
        }
        return true;
    }

    static function checkMinute(
        $minute
    ) {
        if ($minute === null || $minute > 59 || $minute < 0) {
            return false;
        }
        return true;
    }


    static function check_duplicate($shifts1, $shifts2)
    {

        $has_day_same =  false;
        foreach ($shifts1->days_of_week_list as $day) {
            if (in_array($day, $shifts2->days_of_week_list)) {
                $has_day_same =  true;
                break;
            }
        }

        if ($has_day_same == false) {
            return false;
        }



        $start1 =
            ($shifts1->start_work_hour ?? 0) * 100 + ($shifts1->start_work_minute ?? 0);
        $end1 =
            ($shifts1->end_work_hour ?? 0) * 100 + ($shifts1->end_work_minute ?? 0);
        $start2 =
            ($shifts2->start_work_hour ?? 0) * 100 + ($shifts2->start_work_minute ?? 0);
        $end2 =
            ($shifts2->end_work_hour ?? 0) * 100 + ($shifts2->end_work_minute ?? 0);


        return !(ShiftUtils::checkSpace($start1, $end1, $start2, $end2));
    }

    static function checkSpace($start1, $end1, $start2, $end2)
    {
        if ($start1 == $start2) return false;

        if ($start1 > $start2) {
            if ($start1 > $end2) return true;
            return false;
        }

        if ($start1 < $start2) {
            if ($end1 < $start2) return true;
            return false;
        }

        return false;
    }
}
