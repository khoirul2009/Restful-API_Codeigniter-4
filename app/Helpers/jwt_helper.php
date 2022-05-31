<?php

use App\Controllers\Auth;
use App\Models\UsersModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getJWT($authHeader)
{
    // var_dump($authHeader);
    if (is_null($authHeader)) {
        throw new Exception("Otentikasi JWT gagal");
    }
    return explode(" ", $authHeader)[1];
}
function validateJWT($encodedToken)
{
    $key = getenv('TOKEN_SECRET');
    $decodedToken = JWT::decode($encodedToken, new Key($key, 'HS256'));
    $modelUser = new UsersModel();
    $modelUser->getUsername($decodedToken->data->username);
}
