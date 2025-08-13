<?php

namespace App\Services;

use App\Enums\ProjectForm2Status;
use App\Exceptions\PermissionDeniedException;
use App\Jobs\GenerateProjectForm2Pdf;
use App\Models\ProjectForm2;
use App\Repositories\GroupMemberRepository;
use App\Repositories\ProjectForm2Repository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Mpdf\Mpdf;

class ProjectForm2Service
{
    public function __construct(
        protected ProjectForm2Repository $repository,
        protected GroupMemberRepository $groupRepo,
    ) {}

    public function store(array $data): void
    {
        $user = Auth::user();

        $user = Auth::user();

        if ($this->repository->existsForGroup($data['group_id'], $user->id)) {
            throw new PermissionDeniedException(
                'عملية مكررة',
                'لا يمكنك تعبئة الاستمارة أكثر من مرة لنفس المجموعة.'
            );
        }

        $this->validatePdfFile($data['roadmap_file'] ?? null);
        $this->validatePdfFile($data['work_plan_file'] ?? null);

        // save files
        $timestamp = time();

        $roadmapPath = isset($data['roadmap_file'])
            ? $data['roadmap_file']->storeAs('forms2', "project_form2_roadmap_{$timestamp}.pdf", 'public')
            : null;

        $workPlanPath = isset($data['work_plan_file'])
            ? $data['work_plan_file']->storeAs('forms2', "project_form2_workplan_{$timestamp}.pdf", 'public')
            : null;

        $form = $this->repository->create([
            'group_id'             => $data['group_id'],
            'arabic_project_title' => $data['arabic_title'],
            'user_segment'         => $data['user_segment'],
            'development_procedure'=> $data['development_procedure'],
            'libraries_and_tools'  => $data['libraries_and_tools'],
            'roadmap_file'         => $roadmapPath,
            'work_plan_file'       => $workPlanPath,
            'status'               => ProjectForm2Status::Pending,
            'submission_date'      => now(),
        ]);

        GenerateProjectForm2Pdf::dispatch($form->id);
    }

    public function downloadFilledForm(ProjectForm2 $form)
    {
        $user = auth()->user();

        $isLeaderOrMember = $this->groupRepo->isMember($form->group_id, $user->id) ||
            $form->group->leader_id === $user->id;

        if (!$isLeaderOrMember) {
            throw new PermissionDeniedException('غير مصرح','لا يمكنك تحميل هذا الملف.');
        }

        if (!$form->filled_form_file_path || !Storage::disk('public')->exists($form->filled_form_file_path)) {
            throw new PermissionDeniedException('غير موجود','الملف غير متوفر.');
        }

        return Response::download(
            storage_path('app/public/' . $form->filled_form_file_path),
            'project_form2_' . $form->id . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function getPreviewPdfBase64(ProjectForm2 $form): array
    {
        $user = auth()->user();

        $isLeaderOrMember = $this->groupRepo->isMember($form->group_id, $user->id) ||
            $form->group->leader_id === $user->id;

        if (!$isLeaderOrMember) {
            throw new PermissionDeniedException('غير مصرح', 'لا يمكنك معاينة هذا الملف.');
        }

        if (!$form->filled_form_file_path || !Storage::disk('public')->exists($form->filled_form_file_path)) {
            throw new PermissionDeniedException('غير موجود', 'الملف غير متوفر.');
        }

        $filePath = storage_path('app/public/' . $form->filled_form_file_path);
        $pdfContent = file_get_contents($filePath);
        $base64Pdf = base64_encode($pdfContent);

        return [
            'file_name' => 'project_form2_' . $form->id . '.pdf',
            'file_type' => 'application/pdf',
            'file_base64' => $base64Pdf,
        ];
    }


    private function validatePdfFile($file): void
    {
        if ($file) {
            $mime = $file->getMimeType();
            $handle = fopen($file->getRealPath(), 'r');
            $firstBytes = fread($handle, 4);
            fclose($handle);

            if ($mime !== 'application/pdf' || $firstBytes !== '%PDF') {
                throw new PermissionDeniedException(
                    'صيغة غير مقبولة',
                    'الملفات يجب أن تكون PDF حقيقية حصراً.'
                );
            }
        }
    }

    public function regeneratePdf(ProjectForm2 $form): void
    {
        $form->loadMissing('group.members.user');

        if ($form->filled_form_file_path) {
            Storage::disk('public')->delete($form->filled_form_file_path);
        }

        $html = view('pdfs.project_form2', compact('form'))->render();

        $filePath = storage_path('app/public/forms2/project_form2_' . $form->id . '_' . time() . '.pdf');

        $mpdf = new Mpdf([
            'mode'         => 'utf-8',
            'format'       => 'A4',
            'default_font' => 'cairo',
            'direction'    => 'rtl',
            'tempDir'      => storage_path('app/mpdf-temp'),
        ]);

        $mpdf->WriteHTML($html);
        $mpdf->Output($filePath, 'F');

        $form->updateQuietly([
            'filled_form_file_path' => 'forms2/' . basename($filePath)
        ]);
    }



}
