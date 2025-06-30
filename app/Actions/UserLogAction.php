<?php

namespace App\Actions;

use App\Models\UserLog;
use Jenssegers\Agent\Agent;

class UserLogAction
{
    protected $user;

    protected $request;

    protected $action;

    protected $description;

    protected $response;

    protected \Jenssegers\Agent\Agent $agent;

    public function __construct($request, $action, $description, $response, $user = null)
    {
        $this->user = $user;
        $this->request = $request;
        $this->action = $action;
        $this->description = $description;
        $this->response = $response;
        $this->agent = new Agent;
    }

    public function run(): void
    {
        UserLog::create([
            'user_id' => $this->user?->id,
            'email' => $this->user?->email,
            'user_type' => $this->user?->type,
            'action' => $this->action,
            'description' => $this->description,
            'ip' => $this->request->ip(),
            'url' => $this->request->fullUrl(),
            'device' => json_encode([
                'browser' => $this->agent->browser(),
                'platform' => $this->agent->platform(),
                'device_name' => $this->agent->device(),
                'is_robot' => $this->agent->robot(),
            ]),
            'request' => $this->request->getContent(),
            'response' => is_object($this->response) && method_exists($this->response, 'getContent')
            ? $this->response->getContent()
            : $this->response,
            'performed_at' => now(),
        ]);
    }
}
