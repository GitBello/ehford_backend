<?php

namespace App\Controllers;

use App\Models\User;
use Carbon\Carbon;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Services;
use Firebase\JWT\JWT;

class UserController extends BaseController
{
    use ResponseTrait;
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        $user = new User();
        $userList = $user->findAll();
        if(is_null($userList) || empty($userList)){
            return $this->respond(['message' => 'ANY OBJECT FOUND'], 401);
        }
        return $this->respond($userList, 200, 'LIST OF USERS');
    }
    public function login()
    {

        $userModel = new User();

        $email = $this->request->getVar('email');
        $password = $this->request->getVar('password');
        $user = $userModel->where('email', $email)->select($select = '*', $escape = null)
            ->first();
        //var_dump($user['email']);die;
        if (is_null($user)) {
            return $this->respond(['error' => 'Invalid email '], 401);
        }
        $pwd_verify = password_verify($password, $user['password']);
        if(!$pwd_verify) {
            return $this->respond(['error' => 'Invalid password.'], 401);
        }


        $key = env('JWT_SECRET');
        //$iat = Carbon::now()->getTimestamp();
        $iat = time();
        $expiryTime=36000;
        $exp = $iat + $expiryTime;

        $payload = array(
            "iss" => "EHFORD PLATEFORME",
            "aud" => $user['name'],
            "sub" => $user['id'],
            "iat" => $iat,
            "exp" => $exp,
            "email" =>$user['email'],
        );
        $token = JWT::encode($payload, $key, 'HS256');

        $this->result->token = $token;
        $this->result->expiresIn = $expiryTime;
        $response = [
            'message' => 'Login Succesful',
            'token' => $token,
            'expiresIn' => $expiryTime
        ];
        $sessionData = [
            'token' => $token,
            'userId' => $user['id'],
        ];
        $session = services::session();
        $session->set($sessionData);
        $set = $session->get('userId');
        //var_dump($set);die;
        if ($set !== null) {
            return $this->respond($response, 200);
        }
    }
    public function logout()
    {
        $this->result = new \stdclass();
        header('access-control-allow-origin: *');
        header("access-control-allow-headers: x-paymode, content-type, accept, authorization");
        header('access-control-allow-methods: post, get, options');
        $session = Services::session();
        $session->remove('role');
        $session->remove('userId');
        $session->remove('status');
        $session->remove('token');
        $this->setResponse(200,  'LOGOUT SUCCESSFULLY.')
            ->send();
    }
    public function saveUser()
    {

        $rules = $this->validate ([
            'name' => [
                'rules' => 'trim|required',
                'errors' => [
                    'required' => 'Le champ nom est obligatoire',
                ],
            ],
            'email' => [
                'rules' => 'trim|required|valid_email|is_unique[users.email]',
                'errors' => [
                    'required' => 'Le champ e-mail est obligatoire',
                    'is_unique' => 'Cette adresse mail est prise par un autre compte.',
                    'valid_email' => 'Cette adresse mail n\'est pas correcte.',
                ],
            ],
            'password' => [
                'rules' => 'trim|required|min_length[8]',
                'errors' => [
                    'required' => 'Le champ mot de passe est obligatoire',
                    'min_length' => 'Le mot de passe doit au moins avoir 8 caracteres'
                ]
            ],
            'confirm_password' => [
                'rules' => 'trim|required|matches[password]',
                'errors' => [
                    'required' => 'Le champ mot de passe est obligatoire',
                    'matches' => 'Les mots de passe ne correspondent pas '
                ]
            ],
        ]);
        if (!$rules) {
            return $this->respond([
                'statusCode' => 400,
                'message' => $this->validator->getErrors()
            ]);
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
        ];
        $user = new User();
        $usersaved = $user->insert($data);
        if(is_null($usersaved) || empty($usersaved)){
            return $this->respond([
                'Status' => 400,
                'message' => 'Echec de création du compte utilisateur',
            ]);
        }
        return $this->respond([
            'statusCode' => 200,
            'message' => 'user account created succefully !',
            'data' => $data
        ]);
    }

}
