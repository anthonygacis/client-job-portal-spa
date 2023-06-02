<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\User\SaveDetailsRequest;
use App\Http\Requests\Api\User\UpdateRoleRequest;
use App\Models\LeaveCredit;
use App\Models\TemporaryFileUpload;
use App\Models\User;
use App\Models\UserEducBackground;
use App\Models\UserLicense;
use App\Models\UserOrganization;
use App\Models\UserTraining;
use App\Models\UserWorkExperience;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function list()
    {
        $users = User::get(['id', 'name', 'email']);

        return response()->json([
            'data' => $users
        ]);
    }

    public function show(User $user, Request $request)
    {
        if ($request->has('only')) {
            $data = [];
            $choices = explode(',', $request->get('only'));
            if (in_array('credit', $choices)) {
                $data['credit'] = $user->leaveCredit;
            }

            return response()->json($data);
        } else {
            $user->load(['userPrimary', 'userEducBackground', 'userLicense', 'userWorkExperience', 'userOrganization', 'userTraining', 'userOtherInfo']);
        }

        return response()->json($user);
    }

    public function updateRole(User $user, UpdateRoleRequest $request)
    {
        $validated = $request->validated();

        if ($validated['role_type'] == 'existing') {
            $user->syncRoles($validated['existing_roles']);
        } else {
            $role = Role::updateOrCreate([
                'name' => $validated['new_role'],
                'guard_name' => 'web'
            ]);

            $role->givePermissionTo($validated['permissions']);

            $user->syncRoles($role);
        }

        $user->update([
            'relogin_required' => true
        ]);

        return response()->json([
            'message' => "User's role has been updated."
        ]);
    }

    public function update(SaveDetailsRequest $request)
    {
        $validated = $request->validated();
        $fullName = ($validated['personal_info']['last_name'] ?? '') . ', ' . ($validated['personal_info']['first_name'] ?? '') . ' ' . ($validated['personal_info']['middle_name'] ?? '');
        $properFullName = ($validated['personal_info']['first_name'] ?? '') . ' ' . substr($validated['personal_info']['middle_name'] ?? '', 0, 1) . '. ' . ($validated['personal_info']['last_name'] ?? '');

        $user = null;
        $message = 'Data has been updated.';
        if (isset($validated['user_id'])) {
            $user = User::find($validated['user_id']);
            if ($user) {
                $user->userPrimary()->updateOrCreate([
                    'user_id' => $user->id
                ], Arr::except($validated['personal_info'], ['email']));

                $user->update([
                    'email' => $validated['personal_info']['email'] ?? '',
                    'name' => $fullName,
                    'proper_full_name' => $properFullName
                ]);

            }
        } else {
            $check = User::where('name', $fullName)->first();
            if ($check) {
                return response()->json('This record already exist', 422);
            }

            $user = User::updateOrCreate([
                'name' => $fullName,
                'proper_full_name' => $properFullName,
                'username' => $validated['personal_info']['agency_emp_no'],
                'email' => $validated['personal_info']['email'] ?? $validated['personal_info']['agency_emp_no'] . '@sample.com',
                'password' => Hash::make($validated['personal_info']['agency_emp_no'])
            ]);

            $user->userPrimary()->updateOrCreate([
                'user_id' => $user->id
            ], Arr::except($validated['personal_info'], ['email']));

            $credit = $user->leaveCredit()->create([
                'vacation_leave' => 0,
                'sick_leave' => 0,
            ]);
            $credit->leaveCreditTransaction()->createMany([
                [
                    'transaction_type' => 'init',
                    'leave_type' => 'sick',
                    'amount' => 0,
                ],
                [
                    'transaction_type' => 'init',
                    'leave_type' => 'vacation',
                    'amount' => 0,
                ],
            ]);

            $message = 'New employee has been added.';
        }

        if ($user) {
            if (isset($validated['educ_background'])) {
                $itemIds = collect($validated['educ_background'])->pluck('id')->filter()->toArray();
                UserEducBackground::whereNotIn('id', $itemIds)->where('user_id', $user->id)->delete();
                foreach ($validated['educ_background'] as $key => $item) {
                    $user?->userEducBackground()->updateOrCreate([
                        'user_id' => $user->id,
                        'educ_level' => $item['educ_level']
                    ], Arr::except($item, ['educ_level', 'created_at', 'updated_at']) + [
                            'item_no' => $key + 1
                        ]);
                }
            }
            if (isset($validated['license'])) {
                $itemIds = collect($validated['license'])->pluck('id')->filter()->toArray();
                UserLicense::whereNotIn('id', $itemIds)->where('user_id', $user->id)->delete();
                foreach ($validated['license'] as $key => $item) {
                    if (isset($item['id'])) {
                        $user?->userLicense()->updateOrCreate([
                            'id' => $item['id'],
                            'user_id' => $user->id
                        ], Arr::except($item, ['id', 'user_id', 'created_at', 'updated_at', 'item_no']) + [
                                'item_no' => $key + 1
                            ]);
                    } else {
                        $user?->userLicense()->create($item + [
                                'item_no' => $key + 1
                            ]);
                    }
                }
            }
            if (isset($validated['work_experience'])) {
                $itemIds = collect($validated['work_experience'])->pluck('id')->filter()->toArray();
                UserWorkExperience::whereNotIn('id', $itemIds)->where('user_id', $user->id)->delete();
                foreach ($validated['work_experience'] as $key => $item) {
                    if (isset($item['id'])) {
                        $user?->userWorkExperience()->updateOrCreate([
                            'id' => $item['id'],
                            'user_id' => $user->id
                        ], Arr::except($item, ['id', 'user_id', 'created_at', 'updated_at', 'item_no', 'is_gov_service']) + [
                                'item_no' => $key + 1,
                                'is_gov_service' => $item['is_gov_service']
                            ]);
                    } else {
                        $user?->userWorkExperience()->create(Arr::except($item, ['is_gov_service']) + [
                                'item_no' => $key + 1,
                                'is_gov_service' => $item['is_gov_service']
                            ]);
                    }
                }
            }
            if (isset($validated['organization'])) {
                $itemIds = collect($validated['organization'])->pluck('id')->filter()->toArray();
                UserOrganization::whereNotIn('id', $itemIds)->where('user_id', $user->id)->delete();
                foreach ($validated['organization'] as $key => $item) {
                    if (isset($item['id'])) {
                        $user?->userOrganization()->updateOrCreate([
                            'id' => $item['id'],
                            'user_id' => $user->id
                        ], Arr::except($item, ['id', 'user_id', 'created_at', 'updated_at', 'item_no']) + [
                                'item_no' => $key + 1,
                            ]);
                    } else {
                        $user?->userOrganization()->create($item + [
                                'item_no' => $key + 1,
                            ]);
                    }
                }
            }
            if (isset($validated['training'])) {
                $itemIds = collect($validated['training'])->pluck('id')->filter()->toArray();
                UserTraining::whereNotIn('id', $itemIds)->where('user_id', $user->id)->delete();
                foreach ($validated['training'] as $key => $item) {
                    if (isset($item['id'])) {
                        $user?->userTraining()->updateOrCreate([
                            'id' => $item['id'],
                            'user_id' => $user->id
                        ], Arr::except($item, ['id', 'user_id', 'created_at', 'updated_at', 'item_no']) + [
                                'item_no' => $key + 1,
                            ]);
                    } else {
                        $user?->userTraining()->create($item + [
                                'item_no' => $key + 1,
                            ]);
                    }
                }
            }
            if (isset($validated['other_info'])) {
                $user?->userOtherInfo()->updateOrCreate([
                    'user_id' => $user->id
                ], [
                    'civil_service' => $validated['other_info']
                ]);
            }
        }

        if (!$user->hasRole('Super Admin')) {
            $user->assignRole('User');
        } else {
            $user->syncRoles('Super Admin');
        }

        return response()->json([
            'message' => $message
        ]);
    }

    public function delete(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'Your data has been deleted.'
        ]);
    }

    public function uploadFile(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $folder = uniqid('file-', true);
            $file->storeAs('files/tmp/' . $folder, $fileName);

            TemporaryFileUpload::create([
                'user_id' => auth()->id(),
                'folder_path' => 'files/tmp/' . $folder,
                'file_name' => $fileName,
            ]);

            $file = fopen($file, 'r');
            $currData = [];
            while (($column = fgetcsv($file, 0, ",")) !== FALSE) {
                $currData[] = implode(",", $column);
            }

            return response()->json([
                'folder' => $folder,
                'total' => count($currData)
            ]);
        }

        return response()->json('');
    }

    public function storeFromCsv($folder, Request $request)
    {
        if ($folder) {
            $tmp = TemporaryFileUpload::where('folder_path', 'files/tmp/' . $folder)
                ->first();

            $file = Storage::path($tmp->folder_path . '/' . $tmp->file_name);
            $file = fopen($file, 'r');
            $count = 0;
            while (($row = fgetcsv($file, 0)) !== FALSE) {
                if ($count >= 1) {
                    foreach ($row as &$field) {
                        $field = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $field);
                    }

                    $fullName = ($row[0] ?? '') . ', ' . ($row['1'] ?? '') . ' ' . ($row[2] ?? '');
                    $properFullName = ($row[1] ?? '') . ' ' . (substr($row[2] ?? '', 0, 1) ?? '') . '. ' . ($row[0] ?? '');

                    if (isset($row[4])) {
                        $user = User::updateOrCreate([
                            'username' => $row[4]
                        ], [
                            'name' => $fullName,
                            'proper_full_name' => $properFullName,
                            'email' => $row[1] . '@sample.com',
                            'password' => Hash::make($row[4])
                        ]);

                        $user->userPrimary()->updateOrCreate([
                            'agency_emp_no' => $row[4]
                        ], [
                            'last_name' => $row[0],
                            'first_name' => $row[1],
                            'middle_name' => $row[2],
                            'name_ext' => $row[3],
                            'gender' => strtolower($row[5]),
                            'birth_date' => $row[6] ? Carbon::parse($row[6])->format('Y-m-d') : null
                        ]);

                        $isCreditExist = LeaveCredit::where('user_id', $user->id)->first();
                        if (!$isCreditExist) {
                            $credit = $user->leaveCredit()->create([
                                'vacation_leave' => 0,
                                'sick_leave' => 0,
                            ]);

                            $credit->leaveCreditTransaction()->createMany([
                                [
                                    'transaction_type' => 'init',
                                    'leave_type' => 'sick',
                                    'amount' => 0,
                                ],
                                [
                                    'transaction_type' => 'init',
                                    'leave_type' => 'vacation',
                                    'amount' => 0,
                                ],
                            ]);
                        }
                    }
                }
                $count++;
            }
            fclose($file);

            if ($tmp) {
                // delete after importing
                Storage::deleteDirectory($tmp->folder_path . '/');
                $tmp->delete();
            }
        }

        return response()->json([
            'message' => 'Your file content has been created.'
        ]);
    }

    public function deleteFile(Request $request)
    {
        $folder = str_replace('"', '', $request->getContent());
        if ($folder) {
            $tmp = TemporaryFileUpload::where('folder_path', 'files/tmp/' . $folder)
                ->first();
            if ($tmp) {
                Storage::deleteDirectory($tmp->folder_path);
                $tmp->delete();
            }
        }
        return response()->json($folder);
    }
}
