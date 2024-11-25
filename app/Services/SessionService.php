<?php

namespace App\Services;

use Illuminate\Http\Request;

interface SessionService
{
    public function createSession(Request $request);
}
