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
                'question' => '?كم عدد الاعضاء المسموح بها بالغروب',
                'answer'   => '5 أعضاء ويمكن انضمام عضو سادس بشرط ان تكون فكرة المشروع كبيرة نوعا ما',
                'is_active' => true,
            ],
            [
                'question' => 'متى اخر مدة مسموحة لتسليم استمارة 1؟',
                'answer'   => 'نهاية الشهر الخامس',
                'is_active' => true,
            ],
            [
                'question' => 'هل يمكننا طرد عضو من الغروب ؟',
                'answer'   => 'لا يمكن لاي طالب طرد زميله من الغروب بعد تقديم اي نوع من الاستمارات',
                'is_active' => true,
            ],
            [
                'question' => 'هل يسمح باضافة عضو سادس للغروب ؟',
                'answer'   => 'يجب تقديم تبرير مقنع متعلق بحجم المشروع لاضافة عضو سادس',
                'is_active' => true,
            ],
            [
                'question' => 'هل يمكن تقديم استمارة 2 قبل استمارة 1 ؟',
                'answer'   => 'لا يمكن تقديم استمارة 2 قبل استمارة1',
                'is_active' => true,
            ],
            [
                'question' => 'هل يمكن التقدم للمقابلة النهائية بعدد أقل من الأعضاء المصرح بها؟',
                'answer'   => 'لا يمكن الا في حال كان السبب ان الاعضاء انسحبوا من الغروب انسحاب ',
                'is_active' => true,
            ],
            [
                'question' => 'هل يمكن لعضو الانسحاب بعد التقديم للاستمارتين ؟',
                'answer'   => 'نعم يمكن للعضو الانسحاب باي وقت ',
                'is_active' => true,
            ],
            [
                'question' => 'هل يمكن تغيير الفكرة بعد التقديم للاستمارة 1؟',
                'answer'   => 'لا بمكن تغير الفكرة بعد التقديم للاستمارة 1',
                'is_active' => true,
            ],
            [
                'question' => ' كيف يمكن تقديم طلب انضمام عضو سادس ؟',
                'answer'   => 'اما عن طريق التطبيق الابكتروني او عن طريق تطبيق طلب ورقي لسكرتاريا القسم ',
                'is_active' => true,
            ],
            [
                'question' => 'هل نحن مجبورين بأفكار محددة للمشروع ؟',
                'answer'   => 'لا يمكن  للغروب اختياز الفكرة التي يريدها لكن يجب اخذ الموافقة عليها من قبل مشرف واحد على الاقل',
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
