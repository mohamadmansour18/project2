<?php

namespace App\Helpers;

class ArabicText
{
    public static function normalize(?string $text): string
    {
        if(!$text)
        {
            return '';
        }

        //تحويل الاحرف الكبيرة الى احرف صغيرة
        $t = mb_strtolower($text , 'UTF-8');

        //الغاء تشكيل الحركات مثل فتحة او ضمة او كسرة ... الخ من النص
        $t = preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $t);

        //حل مشكلة حرف الالف
        $t = $t = preg_replace('/[أإآٱ]/u', 'ا', $t);

        //يحذف الحروف المستخدمة في التمطيط مثل : ســلام -> سلام
        $t = $t = preg_replace('/\x{0640}/u', '', $t);

        //يستبدل حرف الالف المقصورة بياء
        $t = preg_replace('/[ى]/u', 'ي', $t);

        //استبدال اي محرف لاينتمي الى احرف اللغة العربية او الارقام او الاحرف الانكليزية الصغيرة او (مسافة - تاب - سطر جديد) فقم باستبداله بمسافة
        $t = preg_replace('/[^\p{Arabic}0-9a-z\s]/u', ' ', $t);

        //ضبط الفارغات المتكررة الى فراغ واحد
        $t = preg_replace('/\s+/u', ' ', $t);

        return trim($t);
    }

    //Example
    //Before :
    //$text = "أَهْلًا وَسَهْلًا ـ سَلَامٌ! مصطفىً، ١٢٣ ABC, Hello 😀";

    //After :
    //$text = "اهلا ومرحبا سلام مصطفى 123 abc hello";

    public static function tokens (string $normalized, int $minLen = 2): array
    {
        $parts = preg_split('/\s+/u' , trim($normalized)) ? : [];
        return array_values(array_filter($parts , fn($w) => mb_strlen($w) >= $minLen));
    }

    public static function shingles(array $tokens , int $n): array|string
    {
        $out = [];
        $count = count($tokens);
        for($i = 0 ; $i <= $count - $n ; $i++)
        {
            $out = implode(' ' , array_slice($tokens , $i , $n));
        }
        return $out ;
    }
}
