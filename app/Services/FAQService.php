<?php

namespace App\Services;

use App\Repositories\FAQRepository;
use Illuminate\Support\Collection;

class FAQService
{
    public function __construct(
        protected FAQRepository $FAQRepository
    )
    {}

    public function getFaqs(): array|Collection
    {
        $faqs = $this->FAQRepository->getAll();
        if($faqs->isEmpty())
        {
            return [];
        }
        return $faqs;
    }

    public function createFaq(array $data)
    {
        return $this->FAQRepository->create($data);
    }

    public function deleteFaq(int $questionId)
    {
        return $this->FAQRepository->deleteById($questionId);
    }
}
