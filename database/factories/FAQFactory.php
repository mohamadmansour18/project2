<?php

namespace Database\Factories;

use App\Models\FAQ;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FAQ>
 */
class FAQFactory extends Factory
{
    protected $model = FAQ::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $pool = [
            [
                'question' => 'ما هو موعد التسجيل للمساقات؟',
                'answer'   => 'يبدأ التسجيل يوم الأحد وينتهي يوم الخميس الساعة 11:59 مساءً.',
                'is_active' => true,
            ],
            [
                'question' => 'كيف أطلع على علاماتي النهائية؟',
                'answer'   => 'من خلال البوابة الطلابية > الخدمات الأكاديمية > كشف الدرجات.',
                'is_active' => true,
            ],
            [
                'question' => 'متى تُعقد محاضرات دكتور أحمد؟',
                'answer'   => 'الأحد والثلاثاء 10:00–11:30، والخميس 12:00–13:30.',
                'is_active' => true,
            ],
            [
                'question' => 'ما هو رقم الطالب/الرقم الجامعي وأين أجده؟',
                'answer'   => 'هو معرفك في الجامعة وتجدُه في صفحة الحساب وفي بطاقتك الجامعية.',
                'is_active' => true,
            ],
            [
                'question' => 'ما آخر موعد للانسحاب من مادة بدون رسوب؟',
                'answer'   => 'الأسبوع الثامن من الفصل الدراسي كحدّ أقصى.',
                'is_active' => true,
            ],
            [
                'question' => 'كيف أقدّم استمارة تأجيل فصل؟',
                'answer'   => 'من صفحة النماذج > تأجيل فصل، ثم ارفع المستندات المطلوبة.',
                'is_active' => true,
            ],
            [
                'question' => 'هل يُسمح بإعادة الامتحان؟',
                'answer'   => 'يعتمد على عذر رسمي وموافقة الكلية خلال 48 ساعة من الامتحان.',
                'is_active' => true,
            ],
            [
                'question' => 'كيف أتواصل مع الدعم الفني؟',
                'answer'   => 'عبر البريد support@univ.edu أو مركز الاتصال 1234 من الداخل.',
                'is_active' => true,
            ],
            [
                'question' => 'ما متطلب سابق لمساق رياضيات 101؟',
                'answer'   => 'اجتياز مساق أساسيات الرياضيات أو ما يعادله.',
                'is_active' => true,
            ],
            [
                'question' => 'أين أجد جدول الامتحانات النهائية؟',
                'answer'   => 'على لوحة الإعلانات الأكاديمية وصفحة كل مساق في النظام.',
                'is_active' => true,
            ],
        ];

        $item = $this->faker->randomElement($pool);

        return [
            'question' => $item['question'],
            'answer' => $item['answer'],
            'is_active' => $item['is_active']
        ];
    }
}
