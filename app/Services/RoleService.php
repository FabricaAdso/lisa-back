<?php

namespace App\Services;

interface RoleService
{
    public function getRoles();
    public function toggleRoles(int $userId, int $trainingCenterId, array $roles);
}