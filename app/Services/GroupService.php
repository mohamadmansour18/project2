<?php

namespace App\Services;

use App\Http\Requests\CreateGroupRequest;
use App\Models\Group;
use App\Models\User;
use App\Repositories\GroupRepository;
use App\Enums\GroupMemberRole;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GroupService
{
    protected GroupRepository $groupRepo;

    public function __construct(GroupRepository $groupRepo)
    {
        $this->groupRepo = $groupRepo;
    }

    public function createGroup(CreateGroupRequest $request, User $creator): Group
    {
        // image save in storage/app/public/group_images
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('group_images', 'public');
            $imagePath = 'storage/' . $imagePath;
        }

        // Generate UUID for image QR
        $qrCodeString = (string) Str::uuid();
        $groupNameSlug = Str::slug($request->name);
        $dateString = date('Ymd_His');
        $qrCodeFileName = $groupNameSlug . '_' . $dateString . '.png';

        // save in storage/app/public/qrcodes
        $qrImageStoragePath = 'qrcodes/' . $qrCodeFileName;
        Storage::disk('public')->put($qrImageStoragePath, QrCode::format('png')->size(300)->generate($qrCodeString));

        $qrImagePathForDb = 'storage/' . $qrImageStoragePath;

       //Create group
        $group = $this->groupRepo->create([
            'name' => $request->name,
            'description' => $request->description,
            'speciality_needed' => $request->speciality_needed,
            'framework_needed' => $request->framework_needed,
            'type' => $request->type,
            'qr_code' => $qrImagePathForDb,
            'number_of_members' => 1,
            'image' => $imagePath,
        ]);

        //add Leader
        $this->groupRepo->addMember($group, $creator->id, GroupMemberRole::Leader);

        // send invitations
        if ($request->has('invitations')) {
            foreach ($request->invitations as $inviteeId) {
                $this->groupRepo->invite($group, $inviteeId, $creator->id);
            }
        }

        return $group;
    }
}
