<?php

namespace App\Pipelines\Auth;

use App\Trait\Login;

class LogUserIn
{
    use Login;

    public function handle($request)
    {
        return $this->logUserIn($request->user, $request);
    }
}
