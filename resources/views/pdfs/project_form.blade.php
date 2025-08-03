<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>استمارة تعريف بالمشروع</title>
    <style>
        body {
            font-family: 'cairo', sans-serif;
            direction: rtl;
            text-align: right;
            line-height: 1.8;
            font-size: 14px;
        }

        .center {
            text-align: center;
            font-weight: bold;
        }

        .section {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #444;
        }

        th, td {
            padding: 8px;
            text-align: center;
        }

        .note {
            margin-top: 30px;
            font-style: italic;
            font-size: 13px;
        }

        .line {
            border-bottom: 1px dotted #aaa;
            height: 20px;
        }

        .label {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="center">كلية الهندسة المعلوماتية</div>
<div class="center">قسم هندسة البرمجيات ونظم المعلومات</div>
<div class="center">استمارة رقم /1/ للتعريف بالمشروع</div>
<div class="center">العام الدراسي: {{ now()->year - 1}}/{{ now()->year }}</div>

<div class="section">
    <span class="label">عنوان المشروع (بالعربية):</span><br>
    {{ $form->arabic_title }}
</div>

<div class="section">
    <span class="label">عنوان المشروع (بالإنكليزية):</span><br>
    {{ $form->english_title }}
</div>

<div class="section">
    <table>
        <thead>
        <tr>
            <th>السنة</th>
            <th>عدد الطلاب</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>{{ now()->year  }}</td>
            <td>{{ $form->group->number_of_members ?? '---' }}</td>
        </tr>
        </tbody>
    </table>
</div>

<div class="section">
    <table>
        <thead>
        <tr>
            <th>اسم الطالب</th>
            <th>الاختصاص</th>
            <th>رقم الهاتف</th>
        </tr>
        </thead>
        <tbody>
        @foreach($form->group->members as $member)
            <tr>
                <td>{{ $member->user->name }}</td>
                <td>{{ $member->specialization ?? '---' }}</td>
                <td>{{ $member->user->phone ?? '---' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="section">
    <table>
        <tr>
            <th>اسم عضو الهيئة التدريسية الداعم للمشروع</th>
            <th>التوقيع</th>
        </tr>
        <tr>
            <td>{{ $form->users->name ?? '---' }}</td>
            <td></td>
        </tr>
    </table>
</div>

<div class="section">
    <span class="label">نطاق المشروع يتضمن:</span><br>
    <ul>
        <li><strong>شرح للفكرة:</strong> {{ $form->description }}</li>
        <li><strong>قطاع الأعمال المستهدف:</strong> {{ $form->targeted_sector }}</li>
        <li><strong>تصنيف قطاع الأعمال:</strong> {{ $form->sector_classification }}</li>
        <li><strong>أصحاب المصلحة:</strong> {{ $form->stakeholders }}</li>
    </ul>
</div>

<div class="section">
    <table>
        <tr>
            <th>تاريخ التسليم</th>
            <th>نتيجة تقييم الاستمارة</th>
            <th>توقيع رئيس القسم المختص</th>
        </tr>
        <tr>
            <td>{{ $form->submission_date ? \Carbon\Carbon::parse($form->submission_date)->format('Y-m-d') : '---' }}
            </td>
            <td></td>
            <td></td>
        </tr>
    </table>
</div>

<div>
    ملاحظة: كل تأخير عن موعد التسليم المحدد من رئاسة القسم يستوجب الخصم من علامات التقييم الخاصة بالمشروع.
</div>

</body>
</html>
