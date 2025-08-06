<?php

namespace App\Imports;

use App\Models\User;
use App\Rules\AllowedEmailDomain;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DoctorImport implements ToCollection , WithHeadingRow , WithDrawings
{

    public array $validRows = [];
    public array $errors = [];
    public array $drawingsByRow = [];
    public function drawings(): array
    {
        $indexed = [];
        foreach ($this->drawings as $drawing)
        {
            $cell = $drawing->getCoordinates();
            if(preg_match('/([A-Z]+)(\d+)/' , $cell , $matches))
            {
                $row = (int) $matches[2];
                $indexed[$row] = $drawing;
            }
        }
        $this->drawingsByRow = $indexed;

        return $this->drawingsByRow;
    }

    public function collection(Collection $collection): void
    {

        if($collection->isEmpty() || !isset($collection[0]['name']) || !isset($collection[0]['email']))
        {
            $this->errors[] = 'الملف غير صالح: تأكد من وجود الأعمدة name و email بالتسمية الصحيحة في الصف الأول.';
            return ;
        }

        $emailsInFile = [];

        foreach($collection as $index => $row)
        {
            $rowIndex = $index + 2 ;

            $name = trim($row['name'] ?? '');
            $email = trim($row['email'] ?? '');

            $validator = Validator::make(
                ['name' => $name , 'email' => $email],
                ['name' => ['required' , 'string' , 'max:80'] , 'email' => ['required' , 'email' , new AllowedEmailDomain()]]
            );

            if($validator->fails())
            {
                $messages = $validator->errors()->all();
                $reasons = implode(', ', $messages);
                $this->errors[] = "سطر $rowIndex :(خطأ تحقق) $reasons";
                continue;
            }

            if(in_array($email , $emailsInFile))
            {
                $this->errors[] = "سطر $rowIndex: البريد مكرر داخل الملف";
                continue;
            }

            $emailsInFile[] = $email;

            $profileImagePath = null;

            if(isset($this->drawingsByRow[$rowIndex]))
            {
                $drawing = $this->drawingsByRow[$rowIndex];

                $extension = pathinfo($drawing->getPath(), PATHINFO_EXTENSION);
                $safeName = 'Excel_Doctor_' . time() . '.' . $extension;
                $storePath = 'doctor_profile_image/' . $safeName ;

                Storage::disk('public')->put($storePath, file_get_contents($drawing->getPath()));

                $profileImagePath = $storePath;
            }


            $this->validRows[] = [
                'name' => $name,
                'email' => $email,
                'profile_image' => $profileImagePath
            ];
        }
    }
}
