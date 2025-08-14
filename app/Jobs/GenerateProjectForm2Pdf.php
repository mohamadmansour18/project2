<?php

namespace App\Jobs;

use App\Models\ProjectForm2;
use App\Services\ProjectForm2Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateProjectForm2Pdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $formId) {}

    public function handle(): void
    {
        $form = ProjectForm2::with(['group.members.user'])->findOrFail($this->formId);

        app(ProjectForm2Service::class)->regeneratePdf($form);
    }
}
