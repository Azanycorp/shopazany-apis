<?php

namespace App\Services;

use App\Models\Admin;
use App\Trait\SignUp;
use App\Enum\PlanStatus;
use App\Enum\AdminStatus;
use App\Enum\MailingEnum;
use App\Enum\OrderStatus;
use App\Models\Shippment;
use App\Trait\FindOrders;
use App\Enum\CentreStatus;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\PickupStation;
use App\Models\ShippingAgent;
use App\Mail\B2BNewAdminEmail;
use App\Models\CollationCenter;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\HubResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Trait\SuperAdminNotification;
use App\Mail\AccountVerificationEmail;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\ShippmentResource;
use App\Http\Resources\ShippingAgentResource;
use App\Http\Resources\CollationCentreResource;
use App\Http\Resources\AdminNotificationResource;

class SuperAdminService
{
    use HttpResponse, FindOrders, SuperAdminNotification, SignUp;

    public function getDashboardDetails()
    {
        $centers = CollationCenter::with(['country', 'hubs.country'])->latest('id')->get();

        $collation_counts = CollationCenter::selectRaw('
        COUNT(*) as total_centers,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_centers,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as inactive_centers
    ', [PlanStatus::ACTIVE, PlanStatus::INACTIVE])
            ->first();

        $collation_details = [
            'total_centers' => $collation_counts->total_centers ?? 0,
            'active_centers' => $collation_counts->active_centers ?? 0,
            'inactive_centers' => $collation_counts->inactive_centers ?? 0,
            'centers' => CollationCentreResource::collection($centers),
        ];

        return $this->success($collation_details, 'All available collation centres');
    }

    // Collation centers
    public function deliveryOverview()
    {
        $order_counts = Shippment::selectRaw('
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as out_for_delivery,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered
        ', [OrderStatus::SHIPPED, OrderStatus::DELIVERED])
            ->first();

        $collation_centers = CollationCenter::where('status', PlanStatus::ACTIVE)->count();
        $hubs = PickupStation::where('status', PlanStatus::ACTIVE)->count();

        $details = [
            'total_shippments' => $order_counts->total_orders ?? 0,
            'out_for_delivery' => $order_counts->out_for_delivery ?? 0,
            'delivered' => $order_counts->delivered ?? 0,
            'hubs' => $hubs,
            'collation_centers' => $collation_centers,
        ];

        return $this->success($details, 'delivery overview');
    }

    public function allCollationCentres()
    {
        $query = CollationCenter::with(['country', 'hubs.country'])
            ->when(request()->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when(request()->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('city', 'like', '%' . $search . '%')
                        ->orWhere('location', 'like', '%' . $search . '%');
                });
            });

        $center_counts = CollationCenter::selectRaw('
        COUNT(*) as total_centers,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_active,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as maintenance,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as processing
    ', [
            CentreStatus::ACTIVE,
            CentreStatus::INACTIVE,
            CentreStatus::MAINTENANCE,
            CentreStatus::PROCESSING,
        ])->first();


        $data = [
            'total_centers' => $center_counts->total_centers ?? 0,
            'active_centers' =>  $center_counts->active ?? 0,
            'inactive_centers' => $center_counts->in_active ?? 0,
            'under_maintenance' => $center_counts->maintenance ?? 0,
            'daily_processing' => $center_counts->processing ?? 0,
            'centers' => CollationCentreResource::collection($query->latest()->get()),
        ];

        return $this->success($data, 'Filtered collation centres');
    }

    public function addCollationCentre($request)
    {
        $centre = CollationCenter::create([
            'name' => $request->name,
            'location' => $request->location,
            'note' => $request->note,
            'city' => $request->city,
            'country_id' => $request->country_id ?? 160,
            'status' => PlanStatus::ACTIVE,
        ]);
        $this->createNotification('New Collation Centre Added', 'New collation centre created ' . $centre->name);
        return $this->success($centre, 'Centre added successfully', 201);
    }

    public function viewCollationCentre($id)
    {
        $centre = CollationCenter::with(['country', 'hubs.country'])->find($id);

        if (! $centre) {
            return $this->error(null, 'Centre not found', 404);
        }

        $order_counts = Shippment::selectRaw('
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as out_for_delivery,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as ready_for_pickup,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_transit
    ', [
            OrderStatus::SHIPPED,
            OrderStatus::DELIVERED,
            OrderStatus::READY_FOR_PICKUP,
            OrderStatus::IN_TRANSIT
        ])
            ->where('hub_id', $centre->id)
            ->first();

        $shippments = Shippment::where('collation_id', $centre->id)->latest()->get();

        $data = [
            'current_batches'      => $order_counts->total_orders ?? 0,
            'total_processed'    => $order_counts->delivered ?? 0,
            'daily_throughout'   => $order_counts->ready_for_pickup ?? 0,
            'awaiting_dispatch'  => $order_counts->in_transit ?? 0,
            'center'                => new CollationCentreResource($centre),
            'shippments'   => ShippmentResource::collection($shippments)
        ];

        return $this->success($data, 'Centre details');
    }


    public function editCollationCentre($request, $id)
    {
        $centre = CollationCenter::find($id);

        if (! $centre) {
            return $this->error(null, 'Centre not found', 404);
        }

        $centre->update([
            'name' => $request->name ?? $centre->name,
            'location' => $request->location ?? $centre->location,
            'note' => $request->note ?? $centre->note,
            'city' => $request->city ?? $centre->city,
            'country_id' => $request->country_id ?? $centre->country_id,
            'status' => $request->status ?? $centre->status,
        ]);

        return $this->success(null, 'Details updated successfully');
    }

    public function deleteCollationCentre($id)
    {
        $centre = CollationCenter::with('hubs')->find($id);

        if (! $centre) {
            return $this->error(null, 'Centre not found', 404);
        }

        if (count($centre->hubs) > 0) {
            return $this->error(null, 'Centre can not be deleted because it has content', 422);
        }

        $centre->delete();

        return $this->success(null, 'Centre deleted successfully.');
    }

    // Hubs under Collation centers
    public function allHubs()
    {
        $hubs = PickupStation::with(['country', 'collationCenter'])->latest()->get();

        $total_hubs = PickupStation::count();

        $statusCounts = PickupStation::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $data = [
            'total_hubs' => $total_hubs,
            'active_hubs' => $statusCounts[CentreStatus::ACTIVE] ?? 0,
            'inactive_hubs' => $statusCounts[CentreStatus::INACTIVE] ?? 0,
            'pending' => $statusCounts[CentreStatus::PENDING] ?? 0,
            'hubs' => HubResource::collection($hubs),
        ];

        return $this->success($data, 'All available collation centre hubs');
    }

    public function addHub($request)
    {
        $hub = PickupStation::create([
            'collation_center_id' => $request->collation_center_id,
            'name' => $request->name,
            'location' => $request->location,
            'note' => $request->note,
            'city' => $request->city,
            'country_id' => $request->country_id,
            'status' => PlanStatus::ACTIVE,
        ]);

        $this->createNotification('New Hub Added', 'New hub created ' . $hub->name);

        return $this->success($hub, 'Hub added successfully', 201);
    }

    public function viewHub($id)
    {
        $hub = PickupStation::with(['country', 'collationCenter'])->find($id);

        if (! $hub) {
            return $this->error(null, 'Hub not found', 404);
        }

        $order_counts = Shippment::selectRaw('
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as out_for_delivery,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as ready_for_pickup,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_transit
    ', [
            OrderStatus::SHIPPED,
            OrderStatus::DELIVERED,
            OrderStatus::READY_FOR_PICKUP,
            OrderStatus::IN_TRANSIT
        ])
            ->where('hub_id', $hub->id)
            ->first();

        $shippments = Shippment::where('hub_id', $hub->id)->latest()->get();

        $data = [
            'current_items'      => $order_counts->total_orders ?? 0,
            'total_processed'    => $order_counts->delivered ?? 0,
            'ready_for_pickup'   => $order_counts->ready_for_pickup ?? 0,
            'awaiting_dispatch'  => $order_counts->in_transit ?? 0,
            'hub'                => new HubResource($hub),
            'shippments'   => ShippmentResource::collection($shippments)
        ];

        return $this->success($data, 'Hub details');
    }

    public function editHub($request, $id)
    {
        $hub = PickupStation::find($id);

        if (! $hub) {
            return $this->error(null, 'Hub not found', 404);
        }

        $hub->update([
            'collation_center_id' => $request->collation_center_id ?? $hub->collation_center_id,
            'name' => $request->name ?? $hub->name,
            'location' => $request->location ?? $hub->location,
            'note' => $request->note ?? $hub->note,
            'city' => $request->city ?? $hub->city,
            'country_id' => $request->country_id ?? $hub->country_id,
            'status' => $request->status ?? $hub->status,
        ]);

        return $this->success(null, 'Details updated successfully');
    }

    public function deleteHub($id)
    {
        $hub = PickupStation::find($id);

        if (! $hub) {
            return $this->error(null, 'Hub not found', 404);
        }

        $hub->delete();

        return $this->success(null, 'Hub deleted successfully.');
    }

    public function findOrder($request)
    {
        return $this->searchOrder($request);
    }

    public function findPickupLocationOrder($request)
    {
        return $this->findHubOrder($request);
    }

    public function findCollationCentreOrder($request)
    {
        return $this->findCollationOrder($request);
    }

    // Admin User Management
    public function adminUsers()
    {
        $user = Auth::user();
        $searchQuery = request()->input('search');

        $admins = Admin::with('permissions:id,name')
            ->select('id', 'first_name', 'last_name', 'email', 'created_at')
            ->when($searchQuery, function ($queryBuilder) use ($searchQuery): void {
                $queryBuilder->where(function ($subQuery) use ($searchQuery): void {
                    $subQuery->where('first_name', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('email', 'LIKE', "%{$searchQuery}%");
                });
            })
            ->orderByDesc('created_at')
            ->get();

        return $this->success($admins, 'All Admin Users');
    }

    public function addAdmin($request)
    {
        DB::beginTransaction();
        try {
            $password = Str::random(5);

            $admin = Admin::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'type' => $request->type,
                'status' => AdminStatus::ACTIVE,
                'phone_number' => $request->phone_number,
                'password' => bcrypt($password),
            ]);

            $admin->permissions()->sync($request->permissions);

            $loginDetails = [
                'name' => $request->first_name,
                'email' => $request->email,
                'password' => $password,
            ];

            DB::commit();

            $type = MailingEnum::ADMIN_ACCOUNT;
            $subject = 'Admin Account Creation email';
            $mail_class = B2BNewAdminEmail::class;

            mailSend($type, $admin, $subject, $mail_class, $loginDetails);

            $this->createNotification('New Admin Added', 'New admin account created for ' . $admin->fullName);

            return $this->success($admin, 'Admin user added successfully', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function viewAdmin($id)
    {
        $admin = Admin::findOrFail($id);

        return $this->success($admin, 'Admin details');
    }

    public function editAdmin($request, $id)
    {
        $admin = Admin::findOrFail($id);

        $admin->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'type' => $request->type,
            'phone_number' => $request->phone_number,
        ]);

        $admin->roles()->sync($request->role_id);

        if ($request->permissions) {
            $admin->permissions()->sync($request->permissions);
        }

        return $this->success($admin, 'Details updated successfully');
    }

    public function verifyPassword($request)
    {
        $currentUserId = userAuthId();

        $admin = Admin::findOrFail($currentUserId);

        if (Hash::check($request->password, $admin->password)) {
            return $this->success(null, 'Password matched');
        }

        return $this->error(null, 'Password do not match');
    }

    public function revokeAccess($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->permissions()->detach();

        return $this->success(null, 'Access Revoked');
    }

    public function removeAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->permissions()->detach();
        $admin->delete();

        return $this->success(null, 'Deleted successfully');
    }

    // Shipping Agents
    public function shippingAgents()
    {
        $agents = ShippingAgent::latest()->get();
        $data = ShippingAgentResource::collection($agents);

        return $this->success($data, 'All Agents');
    }

    public function addShippingAgent($request)
    {
        $agent = ShippingAgent::create([
            'name' => $request->name,
            'type' => $request->type,
            'country_ids' => $request->country_ids,
            'account_email' => $request->account_email,
            'account_password' => $request->account_password,
            'api_live_key' => $request->api_live_key,
            'api_test_key' => $request->api_test_key,
            'status' => $request->status,
        ]);

        $this->createNotification('New Shipping Agent Added', 'New shipping agent created ' . $agent->name);

        return $this->success($agent, 'Agent added successfully', 201);
    }

    public function viewShippingAgent($id)
    {
        $agent = ShippingAgent::findOrFail($id);
        $data = new ShippingAgentResource($agent);

        return $this->success($data, 'Agent details');
    }

    public function editShippingAgent($request, $id)
    {
        $agent = ShippingAgent::findOrFail($id);
        $agent->update([
            'name' => $request->name ?? $agent->name,
            'type' => $request->type ?? $agent->type,
            'logo' => $request->logo ?? $agent->logo,
            'country_ids' => $request->country_ids ?? $agent->country_ids,
            'account_email' => $request->account_email ?? $agent->account_email,
            'account_password' => $request->account_password ?? $agent->account_password,
            'api_live_key' => $request->api_live_key ?? $agent->api_live_key,
            'api_test_key' => $request->api_test_key ?? $agent->api_test_key,
            'status' => $request->status ?? $agent->status,
        ]);

        return $this->success(null, 'Details updated successfully');
    }

    public function deleteShippingAgent($id)
    {
        $agent = ShippingAgent::findOrFail($id);
        $agent->delete();

        return $this->success(null, 'Details deleted successfully');
    }

    // CMS / Promo and banners
    public function adminProfile()
    {
        $authUser = userAuth();
        $user = Admin::where('id', $authUser->id)->firstOrFail();

        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile details');
    }

    public function updateAdminProfile($request)
    {
        $authUser = userAuth();
        $user = Admin::where('id', $authUser->id)
            ->firstOrFail();

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ]);

        $this->createNotification('Admin Profile Updated', 'Admin profile updated for ' . $user->fullName);

        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile detail');
    }

    public function enableTwoFactor($request)
    {
        $authUser = userAuth();
        $user = Admin::where('id', $authUser->id)
            ->firstOrFail();

        $user->update([
            'two_factor_enabled' => $request->two_factor_enabled,
        ]);

        $this->createNotification('Two Factor Authentication Updated', 'Two factor authentication updated for ' . $user->fullName);

        return $this->success('Settings updated');
    }

    public function updateAdminPassword($request)
    {
        $authUser = userAuth();
        $user = Admin::where('id', $authUser->id)
            ->firstOrFail();

        if (!Hash::check($request->old_password, $user->password)) {
            return $this->error('Old password is incorrect.', 400);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return $this->success(null, 'Password updated');
    }

    public function sendCode()
    {
        $admin = userAuth();

        if (!$admin->email) {
            return $this->error('Oops! No email found to send code.', 404);
        }

        $verificationCode = mt_rand(1000, 9999);
        $expiry = now()->addMinutes(30);

        $admin->update([
            'verification_code' => $verificationCode,
            'verification_code_expire_at' => $expiry,
        ]);

        $type = MailingEnum::ACCOUNT_VERIFICATION;
        $subject = 'Account Verification';
        $mail_class = AccountVerificationEmail::class;
        $data = [
            'user' => $admin,
        ];
        mailSend($type, $admin, $subject, $mail_class, $data);

        return $this->success('A verification code has been sent to you');
    }

    public function verifyCode($request)
    {
        $user = Admin::where('verification_code', $request->verification_code)->first();
        if (!$user) {
            return $this->error('Invalide code entered, please try it again.', 422);
        }

        if ($user->verification_code_expire_at < now()) {
            return $this->error('Verification Code has Expired!', 404);
        }

        $user->update([
            'verification_code' => null,
            'verification_code_expire_at' => null,
        ]);
        return $this->success("Code Verified");
    }

    //AdminNotification
    public function getNotifications()
    {
        $notifications = AdminNotification::latest()->get();

        $data = AdminNotificationResource::collection($notifications);

        return $this->success($data, 'All notifications');
    }

    public function getNotification($id)
    {
        $notification = AdminNotification::find($id);

        if (!$notification) {
            return $this->error(null, 'Notification not found', 404);
        }

        $data = new AdminNotificationResource($notification);

        return $this->success($data, 'Notification details');
    }

    public function markRead($id)
    {
        $notification = AdminNotification::findOrFail($id);

        if ($notification->is_read) {
            return $this->error(null, 'Notification already marked read');
        }

        $notification->update(['is_read' => true]);

        return $this->success(null, 'Notification marked as read');
    }

    //Shippments
    public function allShippments()
    {
        $order_counts = Shippment::selectRaw('
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_transit
    ', [
            OrderStatus::CANCELLED,
            OrderStatus::DELIVERED,
            OrderStatus::IN_TRANSIT
        ])->first();

        $shippments = Shippment::latest()->get();

        $data = [
            'total_shippments'  => $order_counts->total_orders ?? 0,
            'in_transit'  => $order_counts->in_transit ?? 0,
            'completed'    => $order_counts->delivered ?? 0,
            'failed'   => $order_counts->cancelled ?? 0,
            'shippments'   => ShippmentResource::collection($shippments)
        ];

        return $this->success($data, 'Shippment Data');
    }

    public function shippmentDetails($id)
    {
        $shippment = Shippment::findOrFail($id);

        return $this->success(new ShippmentResource($shippment), 'shippment details');
    }

    public function updateShippmentDetails($request, $id)
    {
        $shippment = Shippment::findOrFail($id);

        $shippment->update([
            'current_location' => $request->current_location,
            'note' => $request->note,
            'status' => $request->status,
            'destination_name' => $request->destination_name,
        ]);

        $shippment->activities()->create([
            'action' => $request->activity
        ]);

        return $this->success(null, 'shippment details Updated');
    }
}
