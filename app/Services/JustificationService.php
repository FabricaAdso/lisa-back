<?php

namespace App\Services;


interface JustificationService
{
    public function createJustification($request);
    public function checkAndUpdateExpiredJustifications();
}
