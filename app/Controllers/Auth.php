<?php

namespace App\Controllers;

use App\Models\UsersModel;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;


class Auth extends ResourceController
{
    protected $modelName = 'App\Models\UsersModel';
    protected $format = 'json';
    protected $usersModel;

    public function __construct()
    {
        $this->validation = \Config\Services::validation();
        $this->usersModel = new UsersModel();
    }

    public function index()
    {

        $data = $this->request->getJSON(true);
        $validate = $this->validation->run($data, 'login');
        $error = $this->validation->getErrors();
        if ($error) {
            return $this->fail($error);
        }
        $dataUser =  $this->model->where(['username' => $data['username']])->first();
        // return $this->respond($dataUser['']);
        $password = $data['password'];
        // die;
        if ($dataUser === null) {
            return $this->failNotFound("User Not Found");
        }
        if (password_verify($password, $dataUser->password)) {

            $secret_key = getenv('TOKEN_SECRET');
            $issuedate_claim = time();
            $notebefore_claim = $issuedate_claim + 10;
            $expire_claim = $issuedate_claim + 15;

            $token = [
                'iat'   => $issuedate_claim,
                'nbf'   => $notebefore_claim,
                'exp'   => $expire_claim,
                'data'  =>  [
                    'id' => $dataUser->id,
                    'firstname' => $dataUser->firstname,
                    'lastname'  => $dataUser->lastname,
                    'username'  => $dataUser->username,

                ]
            ];
            $refreshToken = [
                'iat'   => $issuedate_claim,
                'nbf'   => $notebefore_claim,
                'exp'   => $issuedate_claim + 86400,
                'data'  =>  [
                    'id' => $dataUser->id,
                    'firstname' => $dataUser->firstname,
                    'lastname'  => $dataUser->lastname,
                    'username'  => $dataUser->username,

                ]
            ];

            $token = JWT::encode($token, $secret_key, 'HS256');
            $refreshToken = JWT::encode($refreshToken, getenv('REFRESH_TOKEN_SECRET'), 'HS256');
            $this->usersModel->save([
                'id' => $dataUser->id,
                'refresh_token' => $refreshToken
            ]);
            $output = [
                'status'    => 200,
                'message'   => 'Login Successfully',
                'token'     => $token,
                'expireAt'  => $expire_claim
            ];
            setcookie('refreshToken', $refreshToken, time() + 86400, '/', '', false, true);
            return $this->respond($output, 200);
        } else {
            $output = [
                'status'    => 401,
                'message'   => 'Login Failed',
            ];
            return $this->fail("Username/Password Wrong!");
        }
    }
    public function register()
    {
        $data = $this->request->getJSON(true);
        $validate = $this->validation->run($data, 'register');
        $errors = $this->validation->getErrors();

        if ($errors) {
            return $this->fail($errors);
        }

        $user = new \App\Entities\Users();
        $user->fill($data);
        $user->created_by = 0;
        $user->created_date = date("Y-m-d H:i:s");

        if ($this->model->save($user)) {
            return $this->respondCreated($user, 'User Success Created');
        }
    }
    public function logout()
    {
        $refreshToken = $this->request->getCookie();
        if (!$refreshToken) {
            return $this->fail('refresh token not found', 204);
        }
        $dataUser = $this->model->where(['refresh_token' => $refreshToken])->first();
        if (!$dataUser) {
            return $this->fail('User Not Found', 204);
        }
        $this->usersModel->save([
            'id' => $dataUser->id,
            'refresh_token' => ''
        ]);
        if (isset($_COOKIE['refreshToken'])) {
            setcookie('refreshToken', "", time() - 86400, '/', '', false, true);
        }
        return $this->respond("Berhasil LogOut", 200);
    }
}
