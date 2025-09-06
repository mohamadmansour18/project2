<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use App\Models\GroupMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Faker\Generator as FakerGenerator;

use App\Enums\GroupType;
use App\Enums\GroupMemberRole;
use App\Enums\GroupSpecialityNeeded;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    /**
     * استخدام Faker بالعربية
     */
    protected function withFaker(): FakerGenerator
    {
        return \Faker\Factory::create('ar_SA');
    }

    public function definition(): array
    {
        // قيم التخصصات من الـ Enum أو بدائل نصية
        $specialityCases = enum_exists(GroupSpecialityNeeded::class)
            ? array_map(fn($c) => $c->value, GroupSpecialityNeeded::cases())
            : ['backend', 'frontWeb', 'frontMobile', 'uiux'];

        // أطر العمل المحتملة (JSON/Array)
        $frameworks = ['laravel', 'flutter', 'react', 'node.js'];

        // اختيار عشوائي 1–2 تخصصات (كمصفوفة)
        $specialities = Arr::wrap(Arr::random($specialityCases, random_int(1, 2)));

        // اختيار عشوائي 1–3 أطر عمل (كمصفوفة)
        $frameworkNeeded = Arr::wrap(Arr::random($frameworks, random_int(1, 3)));

        // نوع الجروب من الـ Enum أو نص بديل
        $type = enum_exists(GroupType::class)
            ? $this->faker->randomElement(GroupType::cases())
            : $this->faker->randomElement(['public', 'private']);

        return [
            'name'               => $this->faker->unique()->company(),
            'description'        => $this->faker->realText(120),
            'speciality_needed'  => $specialities,        // cast: array
            'framework_needed'   => $frameworkNeeded,     // cast: array
            'type'               => $type,                // cast: enum GroupType
            'qr_code'            => 'GRP-'.Str::upper(Str::random(10)),
            'number_of_members'  => 0,                    // سيتم ضبطه بعد إنشاء الأعضاء
            'image'              => null,
        ];
    }

    /**
     * بعد إنشاء الجروب: نضيف أعضاء من مستخدمين (IDs 2..79) بدون تكرار عبر الجروبات
     * مع قائد واحد فقط، ثم نحدّث number_of_members حسب العدد الفعلي.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Group $group) {

            // مخزون المستخدمين المتاحين على مستوى العملية كلها
            static $availableUserIds = null;

            if ($availableUserIds === null) {
                // جميع المستخدمين المسموح بهم
                $availableUserIds = User::query()
                    ->whereBetween('id', [2, 79])
                    ->pluck('id')
                    ->all();

                // استبعاد أي مستخدمين سبق إضافتهم في group_members
                $alreadyUsed = GroupMember::query()->pluck('user_id')->all();
                $availableUserIds = array_values(array_diff($availableUserIds, $alreadyUsed));
            }

            // لا يوجد مستخدمون متاحون
            if (count($availableUserIds) === 0) {
                return;
            }

            // هدف عدد الأعضاء 1–6 لكن لا نتجاوز المتاح
            $targetCount = min(random_int(1, 6), count($availableUserIds));

            // سحب IDs بدون إرجاع (وإزالتها من المخزون)
            $assigned = array_splice($availableUserIds, 0, $targetCount);

            // تجهيز أدوار (Enum أو نص)
            $leaderRole = enum_exists(GroupMemberRole::class) ? GroupMemberRole::from('leader') : 'leader';
            $memberRole = enum_exists(GroupMemberRole::class) ? GroupMemberRole::from('member') : 'member';

            // القائد أولاً
            $leaderId = array_shift($assigned);

            GroupMember::query()->create([
                'group_id' => $group->id,
                'user_id'  => $leaderId,
                'role'     => $leaderRole,
            ]);

            // بقية الأعضاء كـ member
            foreach ($assigned as $userId) {
                GroupMember::query()->create([
                    'group_id' => $group->id,
                    'user_id'  => $userId,
                    'role'     => $memberRole,
                ]);
            }

            // تحديث العدد الفعلي
            $group->update([
                'number_of_members' => 1 + count($assigned),
            ]);
        });
    }
}
