<?php

namespace App\Helpers;

class Formatter{
    public static function replace(string|array $subject, array $replace)
    {

        if (is_array($subject)) {
            foreach ($subject as $key => $value) {
                $subject[$key] = self::replace($value, $replace);
            }
            return $subject;
        }

        foreach ($replace as $key => $value) {
            $subject = str_replace(":{$key}", $value, $subject);
        }

        return $subject ?? "";
    }


}