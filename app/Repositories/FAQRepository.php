<?php

namespace App\Repositories;

use App\Models\FAQ;
use Illuminate\Support\Collection;

class FAQRepository
{
    public function getAll(): Collection|array
    {
        return FAQ::query()->select(['id', 'question', 'answer'])->get();
    }

    public function create(array $data)
    {
        return FAQ::create([
            'question'  => $data['question'],
            'answer'    => $data['answer'],
            'is_active' => true,
        ]);
    }

    public function deleteById(int $questionId)
    {
        $faq = FAQ::query()->findOrFail($questionId);
        return $faq->delete();
    }
}
