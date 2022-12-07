<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\UpdateStatusRequest;
use App\Http\Requests\SubAdmin\DeleteMultipleSubAdminRequest;
use App\Http\Requests\SubAdmin\SubAdminRequest;
use App\Http\Resources\CommonCollection;
use App\Http\Resources\SubAdmin\SubAdminResource;
use App\Models\SubAdmin;
use App\Models\User;
use App\Traits\AuthRelationshipTrait;
use App\Traits\MessagesTrait;
use App\Traits\UploadTrait;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Traits\SspfTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class SubAdminAPIController extends Controller
{
    use AuthRelationshipTrait,
        MessagesTrait,
        UploadTrait,
        SspfTrait;

    /**
     * index
     *
     * @param  mixed $request
     * @return void
     */
    public function index(Request $request)
    {
        // $query = SubAdmin::apply($request, false);
        $query = SubAdmin::all();
        return $this->getMessage(SubAdminResource::collection($query), config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
        // return ($query->count() > 0)
        //     ? new CommonCollection(SubAdminResource::collection($query), SubAdminResource::class)
        //     : $this->getMessage([], config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
    }

    /**
     * show
     *
     * @param  mixed $sub_admin
     * @return void
     */
    public function show(SubAdmin $sub_admin)
    {
        return (new SubAdminResource($sub_admin->load([])))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.showed')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(SubAdminRequest $request)
    {
        if (!$this->canModifyModule('admin', NULL, 'store')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $request['status'] = config('constants.status.1');
        $user = User::create($request->except(['city_assigned', 'admin_id']));
        $sub_admin = $user->sub_admin()->create(['city_assigned' => $request->city_assigned, 'admin_id' => $request->admin_id]);
        return (new SubAdminResource($sub_admin))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.created'),
            'message' => config('constants.messages.success.stored')
        ], config('constants.validation_codes.created'));
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $sub_admin
     * @return void
     */
    public function update(SubAdminRequest $request, SubAdmin $sub_admin)
    {
        $user = $sub_admin->user;
        if (!$this->canModifyModule('sub-admin', $user->id, 'update')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }

        if ($request->hasFile('photo')) {
            $realPath = 'sub-admin/' . $sub_admin->id . '/';
            Storage::deleteDirectory('/public/' . $realPath);
            $ResizeImages = $this->ResizeImages($request->file('photo'), $realPath);
            $request['profile_photo'] = $ResizeImages['image'];
            $request['profile_photo_thumb'] = $ResizeImages['thumbnail'];
        }
        $user->update($request->all());
        return (new SubAdminResource($sub_admin))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.updated')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * updateStatus
     *
     * @param  mixed $request
     * @param  mixed $sub_admin
     * @return void
     */
    public function updateStatus(UpdateStatusRequest $request, SubAdmin $sub_admin)
    {
        if (!$this->canModifyModule('sub-admin', NULL, 'update-status')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $sub_admin->user()->update($request->all());
        return (new SubAdminResource($sub_admin))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.updated')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * destroy
     *
     * @param  mixed $sub_admin
     * @return void
     */
    public function destroy(SubAdmin $sub_admin)
    {
        if (!$this->canModifyModule('sub-admin', NULL, 'delete')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $sub_admin->delete();
        $sub_admin->user->delete();
        return $this->getMessage([], config('constants.messages.success.deleted'), config('constants.validation_codes.ok'), true);
    }

    /**
     * deleteMultiple
     *
     * @param  mixed $request
     * @return void
     */
    public function deleteMultiple(DeleteMultipleSubAdminRequest $request)
    {
        if (!$this->canModifyModule('admin', NULL, 'delete')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $counter = 0;
        foreach ($request->ids as $id) {
            $admin = SubAdmin::find($id);
            $this->destroy($admin);
            $counter++;
        }
        return ($counter > 0)
            ? $this->getMessage([], config('constants.messages.success.deleted'), config('constants.validation_codes.ok'), true)
            : $this->getMessage([], config('constants.messages.errors.something_wrong'), config('constants.validation_codes.unprocessable_entity'), false);
    }

    /**
     * mySubAdmin
     *
     * @param  mixed $request
     * @return void
     */
    public function mySubAdmin(Request $request)
    {
        $query = Auth::guard('api')->user()->admin->sub_admins;
        // $query = $this->customSSPF(SubAdmin::class, $query, $request);
        return $this->getMessage(SubAdminResource::collection($query), config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
        // return new CommonCollection(SubAdminResource::collection($query), SubAdminResource::class);
    }

    /**
     * adminWiseSubadmin
     *
     * @param  mixed $admin
     * @param  mixed $request
     * @return void
     */
    public function adminWiseSubadmin(Admin $admin, Request $request)
    {
        $query = Admin::find($admin->id)->sub_admins;
        // $query = $this->customSSPF(SubAdmin::class, $query, $request);
        return $this->getMessage(SubAdminResource::collection($query), config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
        // return new CommonCollection(SubAdminResource::collection($query), SubAdminResource::class);
    }
}
