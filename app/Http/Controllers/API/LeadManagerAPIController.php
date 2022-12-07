<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Requests\Admin\UpdateStatusRequest;
use App\Http\Requests\LeadManager\DeleteMultipleLeadManagerRequest;
use App\Http\Requests\LeadManager\LeadManagerRequest;
use App\Http\Resources\CommonCollection;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\LeadManager\LeadManagerResource;
use App\Models\Admin;
use App\Models\LeadManager;
use App\Models\SubAdmin;
use App\Models\User;
use App\Traits\AuthRelationshipTrait;
use App\Traits\SspfTrait;
use App\Traits\UploadTrait;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\SalesAssociate\SalesAssociateResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;

use App\Models\SalesAssociate;

class LeadManagerAPIController extends Controller
{
    use AuthRelationshipTrait,
        SspfTrait,
        UploadTrait;

    /**
     * index
     *
     * @param  mixed $request
     * @return void
     */
    public function index(Request $request)
    {
        // $query = LeadManager::apply($request, false);
        $query = LeadManager::all();
        return $this->getMessage(LeadManagerResource::collection($query), config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
        // return ($query->count() > 0)
        //     ? new CommonCollection(LeadManagerResource::collection($query), LeadManagerResource::class)
        //     : $this->getMessage([], config('constants.messages.errors.content_not_found'), config('constants.validation_codes.content_not_found'), false);
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return void
     */
    public function store(LeadManagerRequest $request)
    {
        if (Auth::guard('api')->user()->user_type != config('constants.users.user_type.1')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }

        $request->admin_id = ($request->type == config('constants.lead_manager.type.0')) ? Auth::guard('api')->user()->admin->id : NULL;
        $user = User::create($request->except(['type', 'sub_admin_id', 'admin_id', 'base_location']));
        $lead_manager = $user->lead_manager()->create([
            'type' => $request->type,
            'sub_admin_id' => $request->sub_admin_id,
            'admin_id' => $request->admin_id,
            'base_location' => $request->base_location
        ]);
        return (new LeadManagerResource($lead_manager))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.created'),
            'message' => config('constants.messages.success.stored')
        ], config('constants.validation_codes.created'));
    }

    /**
     * show
     *
     * @param  mixed $lead_manager
     * @return void
     */
    public function show(LeadManager $lead_manager)
    {
        return (new LeadManagerResource($lead_manager->load([])))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.showed')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * update
     *
     * @param  mixed $lead_manager
     * @param  mixed $request
     * @return void
     */
    public function update(LeadManager $lead_manager, LeadManagerRequest $request)
    {
        $user = $lead_manager->user;
        if (!$this->canModifyModule('lead-manager', $user->id, 'update')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }

        if ($request->hasFile('photo')) {
            $realPath = 'lead-manager/' . $lead_manager->id . '/';
            Storage::deleteDirectory('/public/' . $realPath);
            $ResizeImages = $this->ResizeImages($request->file('photo'), $realPath);
            $request['profile_photo'] = $ResizeImages['image'];
            $request['profile_photo_thumb'] = $ResizeImages['thumbnail'];
        }
        $user->update($request->all());
        return (new LeadManagerResource($lead_manager))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.updated')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * destroy
     *
     * @param  mixed $lead_manager
     * @return void
     */
    public function destroy(LeadManager $lead_manager)
    {
        $user_id = is_null($lead_manager->admin_id)
            ? $lead_manager->sub_admin->admin->user_id : $lead_manager->admin->user_id;
        if (!$this->canModifyModule('lead-manager', $user_id, 'delete')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $lead_manager->delete();
        $lead_manager->user->delete();
        return $this->getMessage([], config('constants.messages.success.deleted'), config('constants.validation_codes.ok'), true);
    }

    /**
     * deleteMultiple
     *
     * @param  mixed $request
     * @return void
     */
    public function deleteMultiple(DeleteMultipleLeadManagerRequest $request)
    {
        if (Auth::guard('api')->user()->user_type != config('constants.users.user_type.1')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $counter = 0;
        foreach ($request->ids as $id) {
            $lead_manager = LeadManager::find($id);
            $this->destroy($lead_manager);
            $counter++;
        }
        return ($counter > 0)
            ? $this->getMessage([], config('constants.messages.success.deleted'), config('constants.validation_codes.ok'), true)
            : $this->getMessage([], config('constants.messages.errors.something_wrong'), config('constants.validation_codes.unprocessable_entity'), false);
    }

    /**
     * updateStatus
     *
     * @param  mixed $request
     * @param  mixed $lead_manager
     * @return void
     */
    public function updateStatus(UpdateStatusRequest $request, LeadManager $lead_manager)
    {
        $user_id = is_null($lead_manager->admin_id)
            ? $lead_manager->sub_admin->admin->user_id : $lead_manager->admin->user_id;
        if (!$this->canModifyModule('lead-manager', $user_id, 'update-status')) {
            return $this->getMessage([], config('constants.messages.errors.user_has_not_permission'), config('constants.validation_codes.unauthorized'), false);
        }
        $lead_manager->user()->update($request->all());
        return (new LeadManagerResource($lead_manager))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.ok'),
            'message' => config('constants.messages.success.updated')
        ], config('constants.validation_codes.ok'));
    }

    /**
     * myLeadManager
     *
     * @param  mixed $request
     * @return void
     */
    public function myLeadManager(Request $request)
    {
        $user = Auth::guard('api')->user();
        $query = ($user->user_type == config('constants.users.user_type.1'))
            ? $user->admin->lead_managers_of_sub_admin->merge($user->admin->lead_managers)
            : $user->sub_admin->lead_managers;
        return $this->getMessage(
            LeadManagerResource::collection($query),
            config('constants.messages.success.showed'),
            config('constants.validation_codes.ok'),
            true
        );
    }

    /**
     * adminWiseLM
     *
     * @param  mixed $admin
     * @param  mixed $request
     * @return void
     */
    public function adminWiseLM(Admin $admin, Request $request)
    {
        $query = $admin->lead_managers->merge($admin->lead_managers_of_sub_admin);
        return $this->getMessage(
            LeadManagerResource::collection($query),
            config('constants.messages.success.showed'),
            config('constants.validation_codes.ok'),
            true
        );
    }

    public function storesalesassociate(request $request)
    {
        $rules = array(
            'first_name' => 'required',
            'last_name' => 'required',
            'mobile_no' => ['required', 'unique:users,mobile_no,NULL,deleted_at', 'regex:/^(\\+\\d{1,3}( )?)?((\\(\\d{3}\\))|\\d{3})[- .]?\\d{3}[- .]?\\d{4}$/'],
            'password' => 'required', 

        );
        $validator = validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        }

        $id = Auth::guard('api')->user()->id  ;
        $leadmanager_id = LeadManager::where('user_id',$id)->first('id')->id;
        // dd($leadmanager_id);
        $user = User::create($request->except('type', 'admin_id', 'sub_admin_id', 'lead_manager_id'));
        $user->update(['user_type'=>'4']);
        $sale_associate = $user->sales_associate()->create([
            'type' => '2',
            'lead_manager_id' => $leadmanager_id
        ]);
        return (new SalesAssociateResource($sale_associate))->additional([
            'status' => true,
            'status_code' => config('constants.validation_codes.created'),
            'message' => config('constants.messages.success.stored')
        ], config('constants.validation_codes.created'));
    }

    /**
     * subadminWiseLM
     *
     * @param  mixed $sub_admin
     * @param  mixed $request
     * @return void
     */
    public function subadminWiseLM(SubAdmin $sub_admin, Request $request)
    {
        $query = $sub_admin->lead_managers;
        return $this->getMessage(
            LeadManagerResource::collection($query),
            config('constants.messages.success.showed'),
            config('constants.validation_codes.ok'),
            true
        );
        // $query = $this->customSSPF(LeadManager::class, $query, $request);
        // return new CommonCollection(LeadManagerResource::collection($query), LeadManagerResource::class);
    }
}
