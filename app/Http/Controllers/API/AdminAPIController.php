<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\AdminRequest;
use App\Http\Requests\Admin\UpdateStatusRequest;
use App\Http\Requests\Admin\DeleteMultipleAdminRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Http\Resources\CommonCollection;
use App\Models\Admin;
use App\Models\User;
use App\Traits\AuthRelationshipTrait;
use App\Traits\MessagesTrait;
use App\Traits\UploadTrait;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

class AdminAPIController extends Controller
{
    use MessagesTrait,
        UploadTrait,
        AuthRelationshipTrait;

    /**
     * index
     *
     * @param  mixed $request
     * @return void
     */
    public function index(Request $request)
    {
        $query = Admin::all();
        return $this->getMessage(AdminResource::collection($query), config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
        // return ($query->count() > 0)
        //     ? new CommonCollection(AdminResource::collection($query), AdminResource::class)
        //     : $this->getMessage([], config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
    }

    /**
     * show
     *
     * @param  mixed $admin
     * @return void
     */
    public function show(Admin $admin)
    {
        return (new AdminResource($admin->load([])))->additional([
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
    public function store(AdminRequest $request)
    {
        if (!$this->canModifyModule('admin', NULL, 'store')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $request['status'] = config('constants.status.1');
        $request['user_type'] = config('constants.users.user_type.1');
        $user = User::create($request->except('remark'));
        $admin = $user->admin()->create(["remark" => $request['remark']]);
        return (new AdminResource($admin))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.created'),
            'message' => config('constants.messages.success.stored')
        ], config('constants.validation_codes.created'));
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $admin
     * @return void
     */
    public function update(AdminRequest $request, Admin $admin)
    {
      
        $user = $admin->user;
        if (!$this->canModifyModule('admin', $user->id, 'update')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        if ($request->hasFile('photo')) {
            $realPath = 'admin/' . $admin->id . '/';
            Storage::deleteDirectory('/public/' . $realPath);
            $ResizeImages = $this->ResizeImages($request->file('photo'), $realPath);
            $request['profile_photo'] = $ResizeImages['image'];
            $request['profile_photo_thumb'] = $ResizeImages['thumbnail'];
        }
        $user->update($request->all());
        return (new AdminResource($admin))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.updated')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * updateStatus
     *
     * @param  mixed $admin
     * @param  mixed $request
     * @return void
     */
    public function updateStatus(Admin $admin, UpdateStatusRequest $request)
    {
        if (!$this->canModifyModule('admin', NULL, 'update-status')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $admin->user()->update($request->all());
        return (new AdminResource($admin))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.updated')
        ], config('constants.validation_codes.ok'));
    }
    
    /**
     * updateRemark
     *
     * @param  mixed $admin
     * @param  mixed $request
     * @return void
     */
    public function updateRemark(Admin $admin, Request $request)
    {
        if (!$this->canModifyModule('admin', NULL, 'update-remark')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $admin->update($request->all());
        return (new AdminResource($admin))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.updated')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * destroy
     *
     * @param  mixed $admin
     * @return void
     */
    public function destroy(Admin $admin)
    {
        if (!$this->canModifyModule('admin', NULL, 'delete')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $admin->delete();
        $admin->user->delete();
        return $this->getMessage([], config('constants.messages.success.deleted'), config('constants.validation_codes.ok'), true);
    }

    /**
     * deleteMultiple
     *
     * @param  mixed $request
     * @return void
     */
    public function deleteMultiple(DeleteMultipleAdminRequest $request)
    {
        if (!$this->canModifyModule('admin', NULL, 'delete')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $counter = 0;
        foreach ($request->ids as $id) {
            $admin = Admin::find($id);
            $this->destroy($admin);
            $counter++;
        }
        return ($counter > 0)
            ? $this->getMessage([], config('constants.messages.success.deleted'), config('constants.validation_codes.ok'), true)
            : $this->getMessage([], config('constants.messages.errors.something_wrong'), config('constants.validation_codes.unprocessable_entity'), false);
    }
}
