{{-- resources/views/pdfs/announcementFormDate.blade.php --}}
@php
    use Illuminate\Support\Carbon;

    $fmt = function ($d) {
        if (!$d) {
            return '-';
        }

        if ($d instanceof \DateTimeInterface) {
            return $d->format('Y-m-d');
        }

        if (is_string($d)) {
            try {
                return Carbon::parse($d)->toDateString();
            } catch (\Throwable $e) {
                return $d;
            }
        }

        return '-';
    };
@endphp

    <!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>إعلان مواعيد - {{ $year }}</title>
    <style>
        /* خطوط وأساسيات */
        body { font-family: "DejaVu Sans", sans-serif; direction: rtl; margin: 24px; }
        .header { display: flex; align-items: center; gap: 16px; border-bottom: 1px solid #ccc; padding-bottom: 12px; margin-bottom: 16px; }
        .logo { width: 54px; height: 54px; object-fit: contain; }
        .app-name { font-size: 20px; font-weight: bold; }
        .subtitle { color: #666; margin-top: 4px; }
        .badge { display: inline-block; background: #f4f4f4; padding: 4px 10px; border-radius: 8px; font-size: 12px; margin-top: 6px; }

        .card { border: 1px solid #e5e5e5; border-radius: 10px; padding: 12px; margin-bottom: 12px; }
        .card-title { font-weight: 700; margin-bottom: 6px; }
        .row { margin: 4px 0; font-size: 12px; line-height: 1.9; }
        .label { font-weight: 600; display: inline-block; min-width: 100px; }
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
        <div class="subtitle">إعلان من رئاسة قسم هندسة البرمجيات متعلق بمواعيد المشروع 2</div>
        <div class="badge">سنة: {{ $year }}</div>
    </div>
</div>

@if($form1)
    <div class="card">
        <div class="card-title">مواعيد تقديم الاستمارة (1)</div>
        <div class="row"><span class="label">تاريخ البداية:</span> {{ $fmt($form1->start_date) }}</div>
        <div class="row"><span class="label">تاريخ النهاية:</span> {{ $fmt($form1->end_date) }}</div>
    </div>
@endif

@if($form2)
    <div class="card">
        <div class="card-title">مواعيد تقديم الاستمارة (2)</div>
        <div class="row"><span class="label">تاريخ البداية:</span> {{ $fmt($form2->start_date) }}</div>
        <div class="row"><span class="label">تاريخ النهاية:</span> {{ $fmt($form2->end_date) }}</div>
    </div>
@endif

@if($interview)
    <div class="card">
        <div class="card-title">مواعيد المقابلات النهائية</div>
        <div class="row"><span class="label">تاريخ البداية:</span> {{ $fmt($interview->start_date) }}</div>
        <div class="row"><span class="label">تاريخ النهاية:</span> {{ $fmt($interview->end_date) }}</div>
        <div class="row"><span class="label">الأيام:</span> {{ empty($daysAr) ? '-' : implode('، ', $daysAr) }}</div>
    </div>
@endif

<div class="footer">
    تم إنشاء هذا المستند آليًا بواسطة نظام جامعتي.
</div>

</body>
</html>
