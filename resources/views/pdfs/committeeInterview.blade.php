{{-- resources/views/pdfs/committee_announcement.blade.php --}}
    <!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>إعلان لجان المقابلات - {{ $year }}</title>
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
        th, td { border: 1px solid #e5e5e5; padding: 8px 10px; font-size: 12px; }
        th { background: #fafafa; font-weight: 700; text-align: right; }
        tr:nth-child(even) { background: #fcfcfc; }

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
        <div class="subtitle">إعلان من رئاسة قسم هندسة البرمجيات متعلق بتحديد لجان المقابلات للمشروع 2</div>
        <div class="badge">سنة: {{ $year }}</div>
    </div>
</div>

<div class="title">لجان المقابلات</div>

<table>
    <thead>
    <tr>
        <th style="width: 10%;"># اللجنة</th>
        <th style="width: 35%;">الدكتور الأول</th>
        <th style="width: 35%;">الدكتور الثاني</th>
        <th style="width: 20%;">المشرف</th>
    </tr>
    </thead>
    <tbody>
    @foreach($committees as $c)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $c->adminSupervisor?->name ?? '-' }}</td>
            <td>{{ $c->adminMember?->name ?? '-' }}</td>
            <td>{{ $c->adminSupervisor?->name ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    تم إنشاء هذا المستند آليًا بواسطة نظام جامعتي.
</div>

</body>
</html>

