<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>استمارة رقم /2/ لتوصيف أهداف المشروع وخطة تنفيذه</title>
    <style>
        body { font-family: 'cairo', sans-serif; direction: rtl; text-align: right; line-height: 1.8; font-size: 14px; }
        .center { text-align: center; font-weight: bold; }
        .section { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #444; }
        th, td { padding: 8px; text-align: center; }
    </style>
</head>
<body>

<div class="center">كلية الهندسة المعلوماتية</div>
<div class="center">قسم هندسة البرمجيات ونظم المعلومات</div>
<div class="center">استمارة رقم /2/ لتوصيف أهداف المشروع وخطة تنفيذه</div>
<div class="center">العام الدراسي: {{ now()->year - 1 }}/{{ now()->year }}</div>

<div class="section">
    <strong>عنوان المشروع (بالعربية):</strong> {{ $form->arabic_project_title }}
</div>

<div class="section">
    <strong>شريحة المستخدمين:</strong> {{ $form->user_segment }}
</div>

<div class="section">
    <strong>إجرائية تطوير المنتج البرمجي المتبعة مع التبرير:</strong> {{ $form->development_procedure }}
</div>

<div class="section">
    <strong>أهم المكتبات والمكونات البرمجية والأدوات ومجموعة البيانات المراد استخدامها:</strong>
    {{ $form->libraries_and_tools }}
</div>

<div class="section">
    <strong>خريطة طريق المشروع Project Roadmap:</strong>
    @if($form->roadmap_file)
        <br>
        <a href="{{ \App\Helpers\UrlHelper::imageUrl('storage/' . $form->roadmap_file) }}" target="_blank">تحميل PDF</a>
    @else
        <br>---
    @endif
</div>

<div class="section">
    <strong>خطة العمل بين الفريق:</strong>
    @if($form->work_plan_file)
        <br>
        <a href="{{ \App\Helpers\UrlHelper::imageUrl('storage/' . $form->work_plan_file) }}" target="_blank">تحميل PDF</a>
    @else
        <br>---
    @endif
</div>


<div class="section">
    <table>
        <thead>
        <tr>
            <th>اسم الطالب</th>
            <th>الاختصاص</th>
        </tr>
        </thead>
        <tbody>
        @foreach($form->group->members as $member)
            <tr>
                <td>{{ $member->user->name }}</td>
                <td>{{ $member->specialization ?? '---' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="section">
    <table>
        <tr>
            <th>تاريخ التسليم</th>
        </tr>
        <tr>
            <td>{{ $form->submission_date ? \Carbon\Carbon::parse($form->submission_date)->format('Y-m-d') : '---' }}</td>
        </tr>
    </table>
</div>

</body>
</html>
