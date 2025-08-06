<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToCollection , WithHeadingRow
{

    public array $validRows = [];
    public array $errors = [];

    public function collection(Collection $collection)
    {
        if($collection->isEmpty() || !isset($collection[0]['university_number']) || !isset($collection[0]['name']))
        {
            $this->errors[] = 'الملف غير صالح: تأكد من وجود الأعمدة name & university_number بالتسمية الصحيحة في الصف الأول.';
            return ;
        }

        $universityInFile = [];

        foreach ($collection as $index => $row)
        {
            $rowIndex = $index + 2 ;
            $universityNumber = trim($row['university_number'] ?? '');
            $name = trim($row['name'] ?? '');

            $validator = Validator::make(
                ['university_number' => $universityNumber , 'name' => $name],
                ['university_number' => ['required' , 'integer' , 'min:1' , 'unique:users,university_number'] , 'name' => ['required', 'string' , 'max:255']]
            );

            if($validator->fails())
            {
                $messages = $validator->errors()->all();
                $reasons = implode(', ', $messages);
                $this->errors[] = "سطر $rowIndex :(خطأ تحقق) $reasons";
                continue;
            }

            if(in_array($universityNumber, $universityInFile))
            {
                $this->errors[] = "سطر $rowIndex: الرقم الجامعي مكرر داخل الملف";
                continue ;
            }

            $universityInFile[] = $universityNumber;

            $this->validRows[] = [
                'university_number' => $universityNumber,
                'name' => $name,
            ];
        }
    }

}
