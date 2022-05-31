<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\Services;
use CodeIgniter\HTTP\ResponseInterface;

class Token extends ResourceController
{
    protected $modelName = 'App\Models\UsersModel';
    protected $format = 'json';
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {

        $cookie = $this->request->getCookie();
        try {

            if (!$cookie) {
                return $this->failUnauthorized();
            }

            $checkToken = $this->model->where(['refresh_token' => $cookie['refreshToken']])->first();
            if ($checkToken === null) {
                return $this->failForbidden('Token Not Found');
            }
            $validateToken = JWT::decode($cookie['refreshToken'], new Key(getenv('REFRESH_TOKEN_SECRET'), 'HS256'));
            if (!$validateToken) {
                return $this->failForbidden('Token Not Valid');
            }
            $issuedate_claim = time();
            $notebefore_claim = $issuedate_claim + 10;
            $expire_claim = $issuedate_claim + 15;
            $newToken = [
                'iat'   => $issuedate_claim,
                'nbf'   => $notebefore_claim,
                'exp'   => $expire_claim,
                'data'  =>  [
                    'id' => $checkToken->id,
                    'firstname' => $checkToken->firstname,
                    'lastname'  => $checkToken->lastname,
                    'username'  => $checkToken->username,

                ]
            ];

            $accessToken = JWT::encode($newToken, getenv('TOKEN_SECRET'), 'HS256');
            $output = [
                'status'    => 200,
                'message'   => 'Login Successfully',
                'token'     => $accessToken,
                'expireAt'  => $expire_claim
            ];
            return $this->respond($output, 200);
        } catch (Exception $error) {
            return Services::response()->setJSON([
                'errors' => $error->getMessage(),

            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
}
