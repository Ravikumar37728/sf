<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\Admin\UpdateStatusRequest;
use App\Http\Requests\SalesAssociate\DeleteMultipleSalesAssociateRequest;
use App\Http\Requests\SalesAssociate\SalesAssociateRequest;
use App\Http\Resources\CommonCollection;
use App\Http\Resources\SalesAssociate\SalesAssociateResource;
use App\Models\SalesAssociate;
use App\Models\User;
use App\Traits\AuthRelationshipTrait;
use App\Traits\MessagesTrait;
use App\Traits\SspfTrait;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\LeadManager;
use App\Models\SubAdmin;

class SalesAssociateAPIController extends Controller
{
    use MessagesTrait,
        AuthRelationshipTrait,
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
        // $query = SalesAssociate::apply($request, false);
        $query = SalesAssociate::all();
        return $this->getMessage(
            SalesAssociateResource::collection($query),
            config('constants.messages.errors.content_not_found'),
            config('constants.validation_codes.content_not_found'),
            false
        );
        // return ($query->count() > 0)
        //     ? new CommonCollection(SalesAssociateResource::collection($query), SalesAssociateResource::class)
        //     : $this->getMessage([], config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
    }

    /**
     * show
     *
     * @param  mixed $sales_associate
     * @return void
     */
    public function show(SalesAssociate $sales_associate)
    {
        return (new SalesAssociateResource($sales_associate->load([])))->additional([
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
    public function store(SalesAssociateRequest $request)
    {
        if (Auth::guard('api')->user()->user_type != config('constants.users.user_type.1')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        // dd(Auth::guard('api')->user()->user_type);
        $request->admin_id = ($request->type == config('constants.sales_associate.type.0'))
            ? Auth::guard('api')->user()->admin->id : NULL;
        $user = User::create($request->except('type', 'admin_id', 'sub_admin_id', 'lead_manager_id'));
        $sale_associate = $user->sales_associate()->create([
            'type' => $request->type,
            'admin_id' => $request->admin_id,
            'sub_admin_id' => $request->sub_admin_id,
            'lead_manager_id' => $request->lead_manager_id
        ]);
        return (new SalesAssociateResource($sale_associate))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.created'),
            'message' => config('constants.messages.success.stored')
        ], config('constants.validation_codes.created'));
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $sales_associate
     * @return void
     */
    public function update(SalesAssociateRequest $request, SalesAssociate $sales_associate)
    {
        $user = $sales_associate->user;
        if (!$this->canModifyModule('sales-associate', $user->id, 'update')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        if ($request->hasFile('photo')) {
            $realPath = 'sales-associate/' . $sales_associate->id . '/';
            Storage::deleteDirectory('/public/' . $realPath);
            $ResizeImages = $this->ResizeImages($request->file('photo'), $realPath);
            $request['profile_photo'] = $ResizeImages['image'];
            $request['profile_photo_thumb'] = $ResizeImages['thumbnail'];
        }
        $user->update($request->all());
        return (new SalesAssociateResource($sales_associate))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.updated')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * updateStatus
     *
     * @param  mixed $sales_associate
     * @param  mixed $request
     * @return void
     */
    public function updateStatus(SalesAssociate $sales_associate, UpdateStatusRequest $request)
    {
        if (Auth::guard('api')->user()->user_type != config('constants.users.user_type.1')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $sales_associate->user()->update($request->all());
        return (new SalesAssociateResource($sales_associate))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.updated')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * destroy
     *
     * @param  mixed $sales_associate
     * @return void
     */
    public function destroy(SalesAssociate $sales_associate)
    {
        if (Auth::guard('api')->user()->user_type != config('constants.users.user_type.1')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $sales_associate->delete();
        $sales_associate->user->delete();
        return $this->getMessage([], config('constants.messages.success.deleted'), config('constants.validation_codes.ok'), true);
    }

    /**
     * deleteMultiple
     *
     * @param  mixed $request
     * @return void
     */
    public function deleteMultiple(DeleteMultipleSalesAssociateRequest $request)
    {
        if (Auth::guard('api')->user()->user_type != config('constants.users.user_type.1')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }

        $counter = 0;
        foreach ($request->ids as $id) {
            $sales_associate = SalesAssociate::find($id);
            $this->destroy($sales_associate);
            $counter++;
        }
        return ($counter > 0)
            ? $this->getMessage([], config('constants.messages.success.deleted'), config('constants.validation_codes.ok'), true)
            : $this->getMessage([], config('constants.messages.errors.something_wrong'), config('constants.validation_codes.unprocessable_entity'), false);
    }

    /**
     * mySalesAssociates
     *
     * @param  mixed $request
     * @return void
     */
    public function mySalesAssociates(Request $request)
    {
        $user = Auth::guard('api')->user();
        $query = ($user->user_type == config('constants.users.user_type.1'))
            ? $user->admin->sales_associates
            : ($user->user_type == config('constants.users.user_type.2')
                ? $user->sub_admin->sales_associatess : $user->lead_manager->sales_associates);
        return $this->getMessage(
            SalesAssociateResource::collection($query),
            config('constants.messages.errors.content_not_found'),
            config('constants.validation_codes.content_not_found'),
            false
        );
        // $query = $this->customSSPF(SalesAssociate::class, $query, $request);
        // return new CommonCollection(SalesAssociateResource::collection($query), SalesAssociateResource::class);
    }

    /**
     * adminWiseSA
     *
     * @param  mixed $admin
     * @param  mixed $request
     * @return void
     */
    public function adminWiseSA(Admin $admin, Request $request)
    {
        $query = $admin->sales_associates->merge($admin->sales_associates_of_lead_manager)->merge($admin->sales_associates_of_sub_admin);
        $admin_sub_admin_lm = SalesAssociate::select('sales_associates.*')
            ->join('lead_managers', 'sales_associates.lead_manager_id', '=', 'lead_managers.id')
            ->join('sub_admins', 'lead_managers.sub_admin_id', '=', 'sub_admins.id')
            ->join('admins', 'sub_admins.admin_id', '=', 'admins.id')
            ->where('admins.id', '=', $admin->id)->get();
        $query = $query->merge($admin_sub_admin_lm);

        return $this->getMessage(
            SalesAssociateResource::collection($query),
            config('constants.messages.success.showed'),
            config('constants.validation_codes.ok'),
            true
        );
    }

    /**
     * subAdminWiseSA
     *
     * @param  mixed $sub_admin
     * @param  mixed $request
     * @return void
     */
    public function subAdminWiseSA(SubAdmin $sub_admin, Request $request)
    {
        $query = $sub_admin->sales_associates->merge($sub_admin->sales_associates_of_lead_manager);
        return $this->getMessage(
            SalesAssociateResource::collection($query),
            config('constants.messages.success.showed'),
            config('constants.validation_codes.ok'),
            true
        );
    }

    /**
     * leadManagerWiseSA
     * 
     * @param  mixed $lead_manager
     * @param  mixed $request
     * @return void
     */
    public function leadManagerWiseSA(LeadManager $lead_manager, Request $request)
    {
        $query = $lead_manager->sales_associates;
        return $this->getMessage(
            SalesAssociateResource::collection($query),
            config('constants.messages.errors.content_not_found'),
            config('constants.validation_codes.content_not_found'),
            false
        );
        // $query = $this->customSSPF(SalesAssociate::class, $query, $request);
        // return new CommonCollection(SalesAssociateResource::collection($query), SalesAssociateResource::class);
    }
}
