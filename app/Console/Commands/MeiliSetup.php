<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MeiliSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meili:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup Meilisearch index for FAQs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        /** @var \Meilisearch\Client $client */
        $client = app(\Meilisearch\Client::class);
        $index = $client->index('faqs');

        $index->updateSettings([
            'searchableAttributes' => ['arr_question_normalized' , 'question'],
            'displayedAttributes'  => ['id', 'question', 'answer', 'is_active'] ,
            'filterableAttributes' => ['is_active'],
            'typoTolerance' => [
                'enabled' => true,
                'minWordSizeForTypos' => ['oneTypo' => 4, 'twoTypos' => 8],
                'disableOnWords' => [],
                'disableOnAttributes' => ['arr_question_normalized'],
            ],
            'rankingRules' => [
                'words',
                'typo',
                'proximity',
                'attribute',
                'exactness',
            ],
        ]);

        $index->updateStopWords([
            'ما','هو','هي','من','الى','إلى','في','على','عن','هل','كم','كيف','متى','اين','أين','او','أو','و','ال','هذا','هذه','ذلك','تلك'
        ]);

        $index->updateSynonyms([
            'مجموعة' => ['غروب' , 'غروبات' , 'فريق' , 'طواقم' , 'طاقم' , 'مجموعات'],
            'استمارة' => ['وثيقة' , 'عقد' , 'الاستمارة' , 'العقد' , 'الوثيقة'],
            'عضو' => ['فرد' , 'شخص' , 'اعضاء' , 'العضو'],
            'علامة' => ['العلامة' , 'درجة' , 'درجات' , 'نتيجة' , 'نتائج' , 'علامات' , 'الدرجة' , 'النتيجة' ],
            'مجموعتي' => ['غروبي' , 'فريقي' , 'طاقمي'],
            'علامتي' => ['درجتي' , 'نتيجتي'],
        ]);

        $this->info('Meilisearch index "faqs" configured');

        return self::SUCCESS;
    }
}
