<?php

namespace App\Services;


interface JustificationService
{
    public function editJustification($request);
    public function checkAndUpdateExpiredJustifications();
}
