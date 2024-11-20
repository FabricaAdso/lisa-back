<?php

namespace App\Services\Implementations;

use App\Services\TokenService;
use Illuminate\Support\Facades\Crypt;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenServiceImpl implements TokenService {

    public function getTrainingCenterIdFromToken()
    {
        $token = JWTAuth::getToken();
        $payload = JWTAuth::getPayload($token);

        $trainingCenterId = $payload->get('training_center_id');

        if (!$trainingCenterId) {
            return response()->json(['error' => 'Training center ID not found in token payload'], 400);
        }

        $trainingCenterId = Crypt::decrypt($trainingCenterId);

        return $trainingCenterId;
    }

}
