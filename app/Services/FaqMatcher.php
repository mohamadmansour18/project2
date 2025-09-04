<?php

namespace App\Services;

use App\Helpers\ArabicText;
use App\Models\FAQ;
use Illuminate\Support\Facades\Log;

class FaqMatcher
{
    public function match(string $studentText): array
    {
        //نقوم بتجريد سؤال الطالب من اي اضافات
        $normalized = ArabicText::normalize($studentText);

        $studentTokens = ArabicText::tokens($normalized);
        $studentB2 = ArabicText::shingles($studentTokens , 2);
        $studentSet = array_values(array_unique(array_merge($studentTokens , $studentB2)));

        $minTokens = (int) config('faq_bot.min_tokens' , 2);

        if(count($studentTokens) < $minTokens)
        {
            return [
                'faq' => null ,
                'coverage' => 0.0 ,
                'tokens' => $studentTokens
            ];
        }

        $results = FAQ::search($normalized , function ($ms , $query , $options){
            $options['limit'] = 5;
            $options['filter'] = 'is_active = 1';
            return $ms->search($query , $options);
        })->get();

        if($results->isEmpty())
        {
            return [
                'faq' => null ,
                'coverage' => 0.0 ,
                'tokens' => $studentTokens
            ];
        }

        $best = null ;
        $bestScore = 0.0;

        foreach ($results as $result)
        {
            $faqNorm = ArabicText::normalize($result->question);
            $faqTokens = ArabicText::tokens($faqNorm);
            $faqB2     = ArabicText::shingles($faqTokens, 2);
            $faqSet    = array_values(array_unique(array_merge($faqTokens, $faqB2)));

            $score = $this->dice($studentSet , $faqSet);
            if($score > $bestScore)
            {
                $bestScore = $score ;
                $best = $result ;
            }
        }

        $threshold = (float) config('faq_bot.threshold', 0.70);
        if($best && $bestScore >= $threshold)
        {
            Log::info("the bestScore matching is : $bestScore");
            return [
                'faq' => $best ,
                'coverage' => $bestScore,
                'tokens' => $studentTokens
            ];
        }
        Log::info("the bestScore dose not matching is : $bestScore");

        return ['faq' => null, 'coverage' => $bestScore, 'tokens' => $studentTokens];
    }

    private function dice(array $a , array $b): float|int
    {
        if(!$a || !$b)
        {
            return 0.0;
        }

        $a = array_values(array_unique($a));
        $b = array_values(array_unique($b));
        $setB = array_flip($b);
        $inter = 0 ;
        foreach ($a as $token)
        {
            if(isset($setB[$token]))
            {
                $inter++;
            }
        }
        return (2.0 * $inter)/(count($a) + count($b));
    }

    private function jaccard(array $a , array $b): float|int
    {
        if(!$a || !$b)
        {
            return 0.0;
        }

        $a = array_values(array_unique($a));
        $b = array_values(array_unique($b));
        $setB = array_flip($b);
        $inter = 0 ;
        foreach ($a as $token)
        {
            if(isset($setB[$token]))
            {
                $inter++;
            }
        }

        $union = count($a) + count($b) - $inter ;

        return $union ? ($inter / $union) : 0.0;

    }

}
