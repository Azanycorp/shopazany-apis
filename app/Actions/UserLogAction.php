<?php

namespace App\Actions;

use App\Models\UserLog;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class UserLogAction
{
    protected $user;
    protected $request;
    protected $action;
    protected $description;
    protected $response;
    protected $agent;

    public function __construct($request, $action, $description, $response, $user = null)
    {
        $this->user = $user;
        $this->request = $request;
        $this->action = $action;
        $this->description = $description;
        $this->response = $response;
        $this->agent = new Agent();
    }

    public function run()
    {
        try {
            UserLog::create([
                'user_id' => $this->user?->id,
                'email' => $this->request?->email,
                'user_type' => $this->user?->type,
                'action' => $this->action,
                'description' => $this->description,
                'ip' => $this->request->ip(),
                'url' => $this->request->fullUrl(),
                'device' => json_encode([
                    'browser' => $this->agent->browser(),
                    'platform' => $this->agent->platform(),
                    'device_name' => $this->agent->device(),
                    'is_robot' => $this->agent->robot()
                ]),
                'request' => $this->request->getContent(),
                'response' => $this->response->getContent(),
                'performed_at' => Carbon::now()
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

