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
            'displayedAttributes'  => ['id', 'question', 'answer', 'is_active' , 'arr_question_normalized'] ,
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
            'استمارة' => ['وثيقة' , 'عقد' , 'الاستمارة' , 'العقد' , 'الوثيقة' , 'فورم'],
            'عضو' => ['فرد' , 'شخص' , 'اعضاء' , 'العضو' , 'الشخص'],
            'علامة' => ['العلامة' , 'درجة' , 'درجات' , 'نتيجة' , 'نتائج' , 'علامات' , 'الدرجة' , 'النتيجة' ],
            'مجموعتي' => ['غروبي' , 'فريقي' , 'طاقمي'],
            'علامتي' => ['درجتي' , 'نتيجتي'],
            ' مجبورين'=> ['ملزمين',' مضطرين','مقيدين','محكومين'],
            ' محددة'=> ['جاهزة','معينة'],
            ' تقديم'=> ['تسليم',' إرسال','رفع' , 'التقديم' , 'التسليم'],
            'طلب'=> ['رغبة','نموذج'],
            ' انضمام'=> ['إضافة','إدخال','إشراك','إلحاق'],
            'الموعد'=> ['مهلة','وقت','الوقت','موعد', 'المهلة'],
            'مسموحة'=> ['متاحة','مقبولة','ممكنة','صالحة','المسموحة','المتاحة' ,'المقبولة' ,'الممكنة' , 'الصالحة' ],
            'يمكننا' => ['فينا','نستطيع','مسموح ','بيجوز'],
            'طرد'=> ['إزالة','إخراج','منع','فصل'],
            'أقل'=> ['ناقص','انقص'],
            'المصرح'=> ['المسموح','المحددة','المعتمدة','المقررة', 'مصرح' , 'مسموح' , 'محددة' , 'معتمدة' , 'مقررة' ]
        ]);

        $this->info('Meilisearch index "faqs" configured');

        return self::SUCCESS;
    }
}
