<?php

namespace App\Services;

use App\Models\Admin;
use App\Trait\SignUp;
use App\Enum\PlanStatus;
use App\Enum\AdminStatus;
use App\Enum\MailingEnum;
use App\Enum\OrderStatus;
use App\Models\Shippment;
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
use App\Http\Resources\OrderResource;
use App\Models\B2bOrder;
use App\Models\Order;
use App\Http\Resources\SearchB2BOrderResource;
use App\Enum\ShippmentCategory;

class SuperAdminService
{
    use HttpResponse, SuperAdminNotification, SignUp;

    public function getDashboardDetails()
    {
        $centers = CollationCenter::with('country')->latest()->get();

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
        $query = CollationCenter::with('country')
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
        $centre = CollationCenter::with('country')->find($id);

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
            ->where('collation_id', $centre->id)
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
        $centre = CollationCenter::findOrFail($id);

        $centre->delete();

        return $this->success(null, 'Centre deleted successfully.');
    }

    // Hubs under Collation centers
    public function allHubs()
    {
        $hubs = PickupStation::with('country')->latest()->get();

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
        $hub = PickupStation::with('country')->findOrFail($id);

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
        $hub = PickupStation::findOrFail($id);

        $hub->delete();

        return $this->success(null, 'Hub deleted successfully.');
    }

    public function findOrder($request)
    {
        $orderNumber = $request->order_number;

        if ($order = Order::firstWhere('order_no', $orderNumber)) {
            return $this->success(new OrderResource($order), 'Order found successfully.');
        }

        if ($b2bOrder = B2bOrder::firstWhere('order_no', $orderNumber)) {
            return $this->success(new SearchB2BOrderResource($b2bOrder), 'B2B order found successfully.');
        }

        return $this->error(null, 'Order not found.', 404);
    }

    public function findPickupLocationOrder($request)
    {
        $hub = PickupStation::find($request->pickup_id);
        $orderNumber = $request->order_number;

        if (! $hub) {
            return $this->error(null, 'Hub not found', 404);
        }

        if ($order = Order::firstWhere('order_no', $orderNumber)) {
            return $this->success(new OrderResource($order), 'Order found successfully.');
        }

        if (! $b2bOrder = B2bOrder::firstWhere('order_no', $orderNumber)) {
            return $this->error(null, 'Order not found.', 404);
        }

        $resource = new SearchB2BOrderResource($b2bOrder);
        $array = $resource->toArray(request());

        $items = $array['product_quantity'];
        $vendor = $array['vendor'];
        $package = $array['product'];
        $customer = $array['customer'];

        $shippment = Shippment::create([
            'hub_id' => $hub->id,
            'type' => ShippmentCategory::INCOMING,
            'package' => $package,
            'customer' => $customer,
            'vendor' => $vendor,
            'status' => $request->status,
            'priority' => $request->priority,
            'expected_delivery_date' => $request->expected_delivery_date,
            'start_origin' => $hub->name,
            'items' => $items,
        ]);

        $shippment->activities()->create([
            'comment' => $request->activity,
            'note' => $request->note
        ]);

        $this->createNotification('New Shippment created', 'New Shippment created at ' . $hub->name . 'Pickup station/hub ' . 'by ' . Auth::user()->fullName);

        return $this->success(new ShippmentResource($shippment), 'Item Logged successfully.');
    }

    public function findCollationCentreOrder($request)
    {
        $centre = CollationCenter::find($request->collation_id);
        $orderNumber = $request->order_number;

        if (! $centre) {
            return $this->error(null, 'Center not found', 404);
        }

        if ($order = Order::firstWhere('order_no', $orderNumber)) {
            return $this->success(new OrderResource($order), 'Order found successfully.');
        }

        if (! $b2bOrder = B2bOrder::firstWhere('order_no', $orderNumber)) {
            return $this->error(null, 'Order not found.', 404);
        }

        $resource = new SearchB2BOrderResource($b2bOrder);
        $array = $resource->toArray(request());

        $items = $array['product_quantity'];
        $vendor = $array['vendor'];
        $package = $array['product'];
        $customer = $array['customer'];

        $shippment = Shippment::create([
            'collation_id' => $centre->id,
            'type' => ShippmentCategory::INCOMING,
            'package' => $package,
            'customer' => $customer,
            'vendor' => $vendor,
            'status' => $request->status,
            'priority' => $request->priority,
            'expected_delivery_date' => $request->expected_delivery_date,
            'start_origin' => $centre->name,
            'items' => $items,
        ]);

        $shippment->activities()->create([
            'comment' => $request->activity,
            'note' => $request->note
        ]);

        $this->createNotification('New Shippment created', 'New Shippment created at ' . $centre->name . 'Collation centre ' . 'by ' . Auth::user()->fullName);

        return $this->success(new ShippmentResource($shippment), 'Item Logged successfully.');
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
        $user = Admin::findOrFail($authUser->id);

        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile details');
    }

    public function updateAdminProfile($request)
    {
        $authUser = userAuth();
        $user = Admin::findOrFail($authUser->id);

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
        $user = Admin::findOrFail($authUser->id);

        $user->update([
            'two_factor_enabled' => $request->two_factor_enabled,
        ]);

        $this->createNotification('Two Factor Authentication Updated', 'Two factor authentication updated for ' . $user->fullName);

        return $this->success(null, 'Settings updated');
    }

    public function updateAdminPassword($request)
    {
        $authUser = userAuth();
        $user = Admin::findOrFail($authUser->id);

        if (! Hash::check($request->old_password, $user->password)) {
            return $this->error(null, 'Old password is incorrect.', 400);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return $this->success(null, 'Password updated');
    }

    public function sendCode()
    {
        $admin = userAuth();

        if (! $admin->email) {
            return $this->error(null, 'Oops! No email found to send code.', 404);
        }

        $verificationCode = generateVerificationCode(4);
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

        return $this->success(null, 'A verification code has been sent to you');
    }

    public function verifyCode($request)
    {
        $user = Admin::where('verification_code', $request->verification_code)->first();

        if (! $user) {
            return $this->error(null, 'Invalide code entered, please try it again.', 422);
        }

        if ($user->verification_code_expire_at < now()) {
            return $this->error(null, 'Verification Code has Expired!', 404);
        }

        $user->update([
            'verification_code' => null,
            'verification_code_expire_at' => null,
        ]);
        return $this->success(null, "Code Verified");
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

        DB::transaction(function () use ($shippment, $request) {

            $shippment->update([
                'current_location' => $request->current_location,
                'note' => $request->note,
                'status' => $request->status,
                'destination_name' => $request->destination_name,
            ]);

            $shippment->activities()->create([
                'comment' => $request->activity,
                'note' => $request->note
            ]);
        });

        return $this->success(new ShippmentResource($shippment), 'shippment details Updated');
    }

    public function readyForDelivery($request, $id)
    {
        $shippment = Shippment::findOrFail($id);

        DB::transaction(function () use ($shippment, $request) {

            $shippment->update([
                'status' => $request->status,
                'dispatch_name' => $request->dispatch_name,
                'dispatch_phone' => $request->dispatch_phone,
                'vehicle_number' => $request->vehicle_number,
                'delivery_address' => $request->delivery_address,
            ]);

            $shippment->activities()->create([
                'comment' => $request->activity,
                'note' => $request->activity
            ]);
        });

        return $this->success(new ShippmentResource($shippment), 'shippment details Updated');
    }

    public function returnToSender($request, $id)
    {
        $shippment = Shippment::findOrFail($id);

        DB::transaction(function () use ($shippment, $request) {

            $shippment->update([
                'status' => $request->status,
                'note' => $request->note,
            ]);

            $shippment->activities()->create([
                'comment' => $request->activity,
                'note' => $request->note
            ]);
        });

        return $this->success(new ShippmentResource($shippment), 'shippment details Updated');
    }

    public function readyForPickup($request, $id)
    {
        $shippment = Shippment::findOrFail($id);

        DB::transaction(function () use ($shippment, $request) {

            $shippment->update([
                'status' => $request->status,
                'reciever_name' => $request->reciever_name,
                'reciever_phone' => $request->reciever_phone,
            ]);

            $shippment->activities()->create([
                'comment' => $request->activity,
                'note' => $request->activity
            ]);
        });

        return $this->success(new ShippmentResource($shippment), 'shippment details Updated');
    }

    public function readyForDispatched($request, $id)
    {
        $shippment = Shippment::findOrFail($id);

        DB::transaction(function () use ($shippment, $request) {

            $shippment->update([
                'status' => $request->status,
                'destination_name' => $request->destination_name,
                'dispatch_name' => $request->dispatch_name,
                'dispatch_phone' => $request->dispatch_phone,
                'expected_delivery_time' => $request->expected_delivery_time,
                'vehicle_number' => $request->vehicle_number,
                'delivery_address' => $request->delivery_address,
                'note' => $request->note,
            ]);

            $shippment->activities()->create([
                'comment' => $request->activity,
                'note' => $request->note
            ]);
        });

        return $this->success(new ShippmentResource($shippment), 'shippment dispatched');
    }


    public function transferShippment($request, $id)
    {
        $shippment = Shippment::findOrFail($id);

        if ($shippment->collation_id) {
            return $this->error(null, 'shippment belongs to collation centre');
        }

        DB::transaction(function () use ($shippment, $request) {

            $shippment->update([
                'status' => $request->status,
                'transfer_reason' => $request->transfer_reason,
                'hub_id' => $request->hub_id,
                'note' => $request->note,
            ]);

            $shippment->activities()->create([
                'comment' => $request->activity,
                'note' => $request->note
            ]);
        });

        return $this->success(new ShippmentResource($shippment), 'shippment transfered');
    }
}
