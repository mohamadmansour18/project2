{{-- resources/views/pdfs/interview_schedule.blade.php --}}
    <!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>مواعيد مقابلات المشروع 2 - {{ $year }}</title>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; direction: rtl; margin: 24px; }
        .header {
            display: flex; align-items: center; gap: 16px; margin-bottom: 20px;
            border-bottom: 1px solid #ccc; padding-bottom: 12px;
        }
        .logo { width: 54px; height: 54px; object-fit: contain; }
        .app-name { font-size: 20px; font-weight: bold; }
        .subtitle { color: #666; margin-top: 4px; }
        .badge { background: #f5f5f5; padding: 4px 8px; border-radius: 8px; display: inline-block; font-size: 12px; margin-top: 6px; }

        .title { font-size: 18px; font-weight: bold; margin: 16px 0 8px; }

        .day-card { border: 1px solid #e5e5e5; border-radius: 10px; padding: 12px; margin-bottom: 16px; }
        .day-title { font-weight: 700; margin-bottom: 8px; }

        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #e5e5e5; padding: 6px 8px; font-size: 12px; vertical-align: top; text-align: right; }
        th { background: #fafafa; }

        .muted { color: #666; font-size: 11px; line-height: 1.5; }

        /* فاصل اللجنة */
        .committee-sep { background: #fff9c4; } /* أصفر فاهي */
        .committee-chip {
            display: inline-block;
            background: #fff3a0;
            border: 1px solid #e6d87a;
            border-radius: 10px;
            padding: 4px 10px;
            font-weight: 700;
            margin-bottom: 6px; /* مسافة أسفل "لجنة" */
        }
        .committee-meta { margin-top: 6px; } /* مسافة بين العنوان وبيانات المشرفين */

        .footer { margin-top: 16px; font-size: 11px; color: #666; }
    </style>
</head>
<body>

<div class="header">
    @if(!empty($logoImg))
        <img class="logo" src="{{ $logoImg }}" alt="logo">
    @endif
    <div>
        <div class="app-name">جامعتي</div>
        <div class="subtitle">إعلان من رئاسة قسم هندسة البرمجيات يضم مواعيد مقابلات المشروع 2</div>
        <div class="badge">سنة: {{ $year }}</div>
    </div>
</div>

@php
    // خريطة أيام الأسبوع الإنجليزية -> العربية
    $dayMap = [
        'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء',
        'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة',
        'Saturday' => 'السبت',
    ];

    // "الأيام المختارة" بالعربية مع حراسة على $period
    $periodDays = [];
    if (isset($period) && !empty($period->days)) {
        $rawDays = is_array($period->days) ? $period->days
                  : (is_string($period->days) ? (json_decode($period->days, true) ?: []) : []);
        foreach ($rawDays as $d) { $periodDays[] = $dayMap[$d] ?? $d; }
    }

    // قائمة المدرّجات الثابتة
    $HALLS = [
        'المدرج الاول', 'المدرج الثاني', 'المدرج الثالث', 'المدرج الرابع',
        'المدرج الخامس', 'المدرج الثاني عشر', 'المدرج الرابع عشر', 'المدرج السادس عشر',
    ];
@endphp

<div class="title">الفترة</div>
<div>تاريخ البداية: {{ \Illuminate\Support\Carbon::parse($period->start_date ?? 'now')->format('d-m-Y') }}</div>
<div>تاريخ النهاية: {{ \Illuminate\Support\Carbon::parse($period->end_date ?? 'now')->format('d-m-Y') }}</div>
<div>الأيام المختارة:
    @if(!empty($periodDays))
        {{ implode('، ', $periodDays) }}
    @else
        -
    @endif
</div>

<div class="title" style="margin-top:14px;">جدول المقابلات</div>

@foreach($dateList as $date)
    @php
        $d = \Illuminate\Support\Carbon::parse($date)->locale('ar');
        $dayNameAr = $d->translatedFormat('l');   // اسم اليوم بالعربية
        $dateAr    = $d->format('d-m-Y');         // تاريخ فقط

        // جلب سطور هذا اليوم بطريقة آمنة دون افتراض وجود $byDay
        $rows = collect();
        if (isset($byDay)) {
            if ($byDay instanceof \Illuminate\Support\Collection) {
                $rows = $byDay->get($date, collect());
            } elseif (is_array($byDay)) {
                $rows = collect($byDay[$date] ?? []);
            }
        }
    @endphp

    <div class="day-card">
        <div class="day-title">اليوم: {{ $dayNameAr }} - {{ $dateAr }}</div>

        <table>
            <thead>
            <tr>
                <th style="width: 14%;">رقم الغروب</th>
                <th style="width: 34%;">اسم الغروب</th>
                <th style="width: 26%;">بداية المقابلة</th>
                <th style="width: 26%;">نهاية المقابلة</th>
            </tr>
            </thead>
            <tbody>
            @if($rows->isEmpty())
                <tr>
                    <td colspan="4">لا توجد مقابلات مسجلة لهذا اليوم.</td>
                </tr>
            @else
                @php
                    // نجمع حسب اللجنة داخل هذا اليوم
                    $byCommittee = $rows->groupBy(fn($r) => optional($r->committee)->id);

                    // ترقيم اللجان تصاعديًا بدءًا من 1 (لكل يوم)
                    $committeeNumber = 0;

                    // توزيع المدرجات دون تكرار في نفس اليوم
                    $hallsForThisDay = $HALLS; // نسخة
                    $hallOverflowIndex = 1;     // في حال زاد عدد اللجان عن عدد القاعات
                @endphp

                @foreach($byCommittee as $committeeId => $list)
                    @php
                        $committeeNumber++; // لجنة #1, #2, ...

                        // أسماء المشرفين
                        $supervisor = $list->first()->committee?->adminSupervisor?->name ?? '-';
                        $member     = $list->first()->committee?->adminMember?->name ?? '-';

                        // اسناد المدرّج المتاح
                        if (count($hallsForThisDay) > 0) {
                            $hallName = array_shift($hallsForThisDay);
                        } else {
                            $hallName = 'قاعة إضافية ' . $hallOverflowIndex++;
                        }
                    @endphp

                        <!-- فاصل اللجنة -->
                    <tr class="committee-sep">
                        <td colspan="4">
                            <span class="committee-chip">لجنة&nbsp;&nbsp;{{ $committeeNumber }}#</span>
                            <div class="committee-meta muted">المشرف الأول: {{ $supervisor }}</div>
                            <div class="muted">المشرف الثاني: {{ $member }}</div>
                            <div class="muted">المدرج: {{ $hallName }}</div>
                        </td>
                    </tr>

                    <!-- مواعيد هذه اللجنة -->
                    @foreach($list as $slotIndex => $r)
                        <tr>
                            <td>#{{ $loop->iteration }}</td> {{-- يبدأ من 1 لكل لجنة --}}
                            <td>{{ $r->group?->name ?? '-' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($r->interview_time)->setTimezone('Asia/Damascus')->format('H:i') }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($r->interview_end_time)->setTimezone('Asia/Damascus')->format('H:i') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endif
            </tbody>
        </table>
    </div>
@endforeach

<div class="footer">
    تم إنشاء هذا المستند آليًا بواسطة نظام جامعتي.
</div>

</body>
</html>
