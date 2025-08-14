<?php

namespace App\Services;

use App\Helpers\ArabicText;
use App\Models\FAQ;

class FaqMatcher
{
    public function match(string $studentText): array
    {
        //نقوم بتجريد سؤال الطالب من اي اضافات
        $normalized = ArabicText::normalize($studentText);

        $studentTokens = $this->tokenize($normalized);

        $minTokens = (int) config('faq_bot.min_tokens' , 2);

        if(count($studentTokens) < $minTokens)
        {
            return [
                'faq' => null ,
                'coverage' => 0.0 ,
                'tokens' => $studentTokens
            ];
        }

        $result = FAQ::search($normalized)
            ->take(1)
            ->get()
            ->first();

        if(!$result)
        {
            return [
                'faq' => null ,
                'coverage' => 0.0 ,
                'tokens' => $studentTokens
            ];
        }

        $faqTokens = $this->tokenize(ArabicText::normalize($result->question));
        $coverage = $this->coverage($studentTokens , $faqTokens);

        $threshold = (float) config('faq_bot.threshold' , 0.70);
        if($coverage >= $threshold)
        {
            return [
                'faq' => $result ,
                'coverage' => $coverage ,
                'tokens' => $studentTokens
            ];
        }

        return [
            'faq' => null ,
            'coverage' => $coverage ,
            'tokens' => $studentTokens
        ];
    }

    /**
     * تقطيع بسيط للكلمات + إزالة الفراغات، وإرجاع مجموعة (unique حسب الإعداد).
     */
    private function tokenize(string $text): array
    {
        //تقسيم الجملة الى مصفوفة من الكلمات حيث يتم قسم الكلمة عند رؤية فراغ او tab او new line
        $parts = preg_split('/\s+/u', trim($text)) ? : [];

        //وظيفة ال array_filter هي فلترة المصفوفة حسب شرط معين وهو : يتم حساب طول الكلمة عبر التابع mb_strlen اذا كانت اكبر من الصفر تبقى في المصفوفة والا يتم حذفها
        //بعد تطبيق array filter قد ينتج مفاتيح غير متسلسلة تصاعديا فيتم اعادة ترتيبها باستخدام array values ابتداءا من الواحد
        $parts = array_values(array_filter($parts , fn($w) => mb_strlen($w) > 0));

        if(config('faq_bot.unique_tokens' , true))
        {
            // يتم ازالة الكلمات المكررة من المصفوفة باستخدام array unique
            $parts = array_values(array_unique($parts));
        }
        return $parts;
    }

    private function coverage(array $studentTokens, array $faqTokens)
    {
        if(count($studentTokens) === 0)
        {
            return 0.0;
        }

        //يقوم تابع ال array flip بقلب ال key الى value وال value الى key
        $feqSet = array_flip($faqTokens);

        $common = 0 ;
        foreach ($studentTokens as $token)
        {
            if(isset($feqSet[$token]))
            {
                $common++;
            }
        }
        return $common/count($studentTokens);
    }
}
