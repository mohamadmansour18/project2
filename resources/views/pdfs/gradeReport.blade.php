{{-- resources/views/pdfs/grade_report.blade.php --}}
    <!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>علامات المشروع 2 - {{ $year }}</title>
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

        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e5e5; padding: 8px 10px; font-size: 12px; vertical-align: top; }
        th { background: #fafafa; font-weight: 700; text-align: right; }
        tr:nth-child(even) { background: #fcfcfc; }

        .excepted { background: #fff9c4; } /* أصفر فاهي */
        .muted { color: #666; font-size: 11px; }

        .footer { margin-top: 16px; font-size: 11px; color: #666; }
        .stats {
            margin-top: 14px; padding: 10px; border: 1px dashed #ccc; border-radius: 8px;
            font-size: 12px; line-height: 1.7;
        }
    </style>
</head>
<body>

<div class="header">
    @if(!empty($logoImg))
        <img class="logo" src="{{ $logoImg }}" alt="logo">
    @endif
    <div>
        <div class="app-name">جامعتي</div>
        <div class="subtitle">إعلان من رئاسة قسم هندسة البرمجيات يضم علامات المشروع 2</div>
        <div class="badge">سنة: {{ $year }}</div>
    </div>
</div>

<div class="title">علامات المجموعات</div>

<table>
    <thead>
    <tr>
        <th style="width: 7%;">#</th>
        <th style="width: 23%;">اسم الغروب</th>
        <th style="width: 30%;">اسم الطالب</th>
        <th style="width: 20%;">الرقم الجامعي</th>
        <th style="width: 20%;">العلامة</th>
    </tr>
    </thead>
    <tbody>
    @foreach($groups as $g)
        @php $rows = count($g['members']); @endphp
        @foreach($g['members'] as $i => $m)
            <tr>
                @if($i === 0)
                    <td rowspan="{{ $rows }}">{{ $g['index'] }}</td>
                    <td rowspan="{{ $rows }}">{{ $g['name'] }}</td>
                @endif

                <td class="{{ $m['is_excepted'] ? 'excepted' : '' }}">
                    {{ $m['name'] }}
                    @if($m['is_excepted'])
                        <div class="muted">(مستثنى من العلامة)</div>
                    @endif
                </td>
                <td class="{{ $m['is_excepted'] ? 'excepted' : '' }}">
                    {{ $m['exam_number'] }}
                </td>
                <td class="{{ $m['is_excepted'] ? 'excepted' : '' }}">
                    {{ $m['grade'] }} / 100
                </td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>

<div class="stats">
    <div><strong>إحصاءات:</strong></div>
    <div>عدد الناجحين (≥ 60): {{ $passed }}</div>
    <div>عدد الراسبين (&lt; 60): {{ $failed }}</div>
    <div>نسبة النجاح: {{ number_format($successRate, 2) }}%</div>
</div>

<div class="footer">
    تم إنشاء هذا المستند آليًا بواسطة نظام جامعتي.
</div>

</body>
</html>

