<?php

namespace App\Models\Base;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;

class BaseModel extends Model
{


    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        $array = [
            'created_at',
            'updated_at',
        ];

        if (in_array($key, $array)) {

            $timezone = new \DateTime("now", new \DateTimeZone('Asia/Ho_Chi_Minh'));
            if (!($value instanceof Carbon)) {
                $value = Carbon::parse($value);
            }

            return $this->attributes[$key] =
                Carbon::createFromFormat('Y-m-d H:i:s', $value, 'Asia/Ho_Chi_Minh')
                ->setTimezone('Asia/Ho_Chi_Minh');
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {

        $array = [
            'created_at',
            'updated_at',
        ];

        $castBoolean = [
            "has_park",
            "has_wifi",
            "has_wc",
            "has_window",
            "has_security",
            "has_free_move",
            "has_own_owner",
            "has_air_conditioner",
            "has_water_heater",
            "has_kitchen",
            "has_fridge",
            "has_washing_machine",
            "has_mezzanine",
            "has_bed",
            "has_wardrobe",
            "has_tivi",
            "has_pet",
            "has_balcony",
            "admin_verified",
            "has_post",
            "has_finger_print",
            "has_kitchen_stuff",
            "has_table",
            "has_picture",
            "has_decorative_lights",
            "has_tree",
            "has_pillow",
            "has_mattress",
            "has_shoes_rasks",
            "has_curtain",
            "has_mirror",
            "has_sofa",
            "has_ceiling_fans",
            "has_contract",
        ];


        if (in_array($key, $array)) {
            $timezone = new \DateTime('now', new \DateTimeZone('Asia/Ho_Chi_Minh'));
            $value = $this->getAttributeValue($key);

            if (!($value instanceof Carbon)) {
                $value = Carbon::parse($value);
            }

            return Carbon::createFromFormat('Y-m-d H:i:s', $value, 'Asia/Ho_Chi_Minh')
                ->setTimezone('Asia/Ho_Chi_Minh');
        }

        // if (in_array($key, $castBoolean)) {
        //     $value = $this->getAttributeValue($key);
        //     if ($value == null) {
        //         return $value = false;
        //     }

        //     return $value;
        // }

        return parent::getAttribute($key);
    }
}
