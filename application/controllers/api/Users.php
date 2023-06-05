<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use SebastianBergmann\Environment\Console;

class Users extends RestController {

    protected $user_detail;
    function __construct()
    {
        parent::__construct();
        $this->load->library('Authorization_Token');
        $this->load->helper(array('user_helper'));
        $this->load->model('User_model','user');
        $this->load->model('Validator_model','validator');
    }
    public function register_post()
	{   
        try {
            $nama = $this->input->post('nama');
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            $this->form_validation->set_rules('nama', 'Nama', 'required');
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[tbl_user.email]',array('is_unique' => 'Email sudah pernah terdaftar!'));
            $this->form_validation->set_rules('password', 'Password', 'required');
            $this->form_validation->set_rules('passconf', 'Konfirmsi Password', 'required|matches[password]',
                                                        array('matches' => 'Password Konfirmasi harus sama!')
            );
            $this->form_validation->set_message('required', '{field} tidak boleh kosong!');
            $this->form_validation->set_error_delimiters('', '');
            if(!$this->form_validation->run()) throw new Exception(validation_errors());

            // $data = array(
            //     'nama'      => $nama,
            //     'username'  => generate_username($nama),
            //     'password'  => bCrypt($password,12),
            //     'email'     => $email,
            //     'foto'      => '',
            // );
            $data['nama'] = $nama;
            $data['username'] = generate_username($nama);
            $data['password'] = bCrypt($password,12);
            $data['email'] = $email;
            $data['foto'] = '';
            if($this->input->post('role') != null){
                $data['role'] = $this->input->post('role');
                // $data['validator_brand_id'] = $this->input->post('validator_brand_id');
                $data['validator_kategori_id'] = $this->input->post('validator_kategori_id');
            }
            $data['user_code'] = intCodeRandom(4);

            $register = $this->user->createUser($data);
            if($register){
                $this->response([
                    'status' => true,
                    'message'   => 'Register Berhasil',
                    'data'  => []
                ],200);
            }else{
                throw new Exception('Register Fail!');
            }

        } catch (\Throwable $th) {
            $this->response([
                'status' => false,
                'message'   => $th->getMessage(),
                'error_data'    => [
                    'nama'  => form_error('nama'),
                    'email' => form_error('email'),
                    'password' => form_error('password'),
                    'passconf' => form_error('passconf'),
                ]
            ],400);
        }
	}

    public function googleauth_post(){
        $post = $this->input->post();
        $email = $this->input->post('email_address');
        if(isset($email)){
            $cek_email = $this->user->get_by(array('email' => $email),1,NULL,TRUE,array('id','nama','username','password','email','role','register_tipe','validator_brand_id','validator_kategori_id','user_code'));
            if(!empty($cek_email)){
                $token_data = array(
                    'user_id'   => $cek_email->id,
                    'nama'  => $cek_email->nama,
                    'username' => $cek_email->username,
                    'email'  => $cek_email->email,
                    'role'  => $cek_email->role,
                    'validator_brand_id'    => $cek_email->validator_brand_id,
                    'validator_kategori_id'    => $cek_email->validator_kategori_id,
                    'user_code' => $cek_email->user_code
                );
                $token = $this->authorization_token->generateToken($token_data);
                //update user info to google account
                $update_akun_user_to_google = array(
                    'foto'  => $post['profile_picture'],
                    'register_tipe' => 'google',
                    'gid' => $post['gid'],
                    'updated_at' => date('Y-m-d H:i:s')
                );
                $this->user->update($update_akun_user_to_google,array('email'=>$email));
                $this->response([
                    'status' => true,
                    'uid'   => $cek_email->id,
                    'message'   => 'Login Berhasil!',
                    'token'  => $token
                ],200);
                // if($cek_email == 'google'){
                // }else{
                //     $this->response([
                //         'status' => false,
                //         'message'   => 'Maaf, akun email anda terdaftar manual ',
                //     ],200);
                // }
            }else{
                //register new email
                $data['nama'] = $post['full_name'];
                $data['username'] = generate_username($post['full_name']);
                $data['password'] = '';
                $data['email'] = $email;
                $data['foto'] = $post['profile_picture'];
                $data['role'] = 'user';
                $data['register_tipe'] = 'google';
                $data['validator_kategori_id'] = 0;
                $data['user_code'] = intCodeRandom(4);
                $data['gid'] = $post['gid'];
                $data['updated_at'] = date('Y-m-d H:i:s');

                $register = $this->user->insert($data);
                if($register){
                    $cek_email = $this->user->get_by(array('id' => $register),1,NULL,TRUE,array('id','nama','username','password','email','role','register_tipe','validator_brand_id','validator_kategori_id','user_code'));
                    $token_data = array(
                        'user_id'   => $cek_email->id,
                        'nama'  => $cek_email->nama,
                        'username' => $cek_email->username,
                        'email'  => $cek_email->email,
                        'role'  => $cek_email->role,
                        'validator_brand_id'    => $cek_email->validator_brand_id,
                        'validator_kategori_id'    => $cek_email->validator_kategori_id,
                        'user_code' => $cek_email->user_code
                    );
                    $token = $this->authorization_token->generateToken($token_data);
                    $this->response([
                        'status' => true,
                        'uid'   => $cek_email->id,
                        'message'   => 'Login Berhasil!',
                        'token'  => $token
                    ],200);
                }else{
                    $this->response([
                        'status' => false,
                        'message'   => 'Gagal, silahkan ulangi kembali',
                        'data'  => []
                    ],500);
                }
            }
            // $this->response([
            //     'status' => false,
            //     'email'   => '$cek_email',
            // ]);
        }
    }

    public function login_post(){

        $email = $this->input->post('email');
        $password = $this->input->post('password');
        if(isset($email)){
            $cek_email = $this->user->get_by(array('email' => $email),1,NULL,TRUE,array('id','nama','username','password','email','role','register_tipe','validator_brand_id','validator_kategori_id','user_code'));
            if($cek_email != null){
                $this->user_detail = $cek_email;
            }else{
                $this->response([
                    'status' => false,
                    'message'   => 'Email tidak terdaftar!',
                ],400);
            }
        }
        try {
            if($this->user_detail->register_tipe == 'google'){
                $this->response([
                    'status' => false,
                    'message'   => 'Gagal, email anda terhubung dengan metode login akun Google',
                ],200);
            }
            $this->form_validation->set_rules('email', 'Email', 'required');
            $this->form_validation->set_rules('password', 'Password', 'required|callback_password_check');

            $this->form_validation->set_message('required', '{field} tidak boleh kosong!');
            $this->form_validation->set_error_delimiters('', '');
            if(!$this->form_validation->run()) throw new Exception(validation_errors());

            $token_data = array(
                'user_id'   => $this->user_detail->id,
                'nama'  => $this->user_detail->nama,
                'username' => $this->user_detail->username,
                'email'  => $this->user_detail->email,
                'role'  => $this->user_detail->role,
                'validator_brand_id'    => $this->user_detail->validator_brand_id,
                'validator_kategori_id'    => $this->user_detail->validator_kategori_id,
                'user_code' => $this->user_detail->user_code
            );
            $token = $this->authorization_token->generateToken($token_data);
            $this->response([
                'status' => true,
                'uid'   => $this->user_detail->id,
                'message'   => 'Login Berhasil!',
                'token'  => $token
            ],200);
            
        } catch (\Throwable $th) {
            $this->response([
                'status' => false,
                'message'   => $th->getMessage(),
            ],400);
        }
    }

    public function password_check($str){
		$user_detail = $this->user_detail;
		if($user_detail){
			if($user_detail->password == crypt($str,$user_detail->password)){
				return TRUE;
			}else{
				$this->form_validation->set_message('password_check','Password salah!');
				return FALSE;
			}
		}else{
			$this->form_validation->set_message('password_check','Password salah!');
			return FALSE;
		}
	}

    public function validatetoken_post(){
        $headers = $this->input->request_headers();
        if(isset($headers['Authorization'])){
			$decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
			$this->response($decodedToken);  
		}else{
			$this->response([
                'status' => false,
                'message' => 'Unauthorized'
            ],401);
		}
    }

    public function datasummary_get(){
        $this->authorization_token->authtoken();
        $headers = $this->input->request_headers();
        $decodedToken = $this->authorization_token->validateToken($headers['Authorization']);

        $tipe = $this->get('tipe');
        $user_id = $decodedToken['data']->user_id;
        $dataSumary = $this->validator->validator_sumary($user_id,$tipe);
        if($dataSumary){
            $this->response([
                'status' => true,
                'data'  => $dataSumary,
            ],200);
        }else{
            $this->response([
                'status' => false,
                'message' => 'Case Code Not Found!'
            ],404);
        }
    }

    public function userlist_get(){
        $this->authorization_token->authtoken();
        $headers = $this->input->request_headers();
        $decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
        if($decodedToken['status'] == true && $decodedToken['data']->role == 'admin'){
            $param = $this->get('datatable');
            $query = $param['sSearch'];
            $start = $param['iDisplayStart'];
            $length = $param['iDisplayLength'];
            $keySearch = 'nama';

            $role = $this->get('role');
            $result['sEcho'] = intval($param['sEcho']);
            $result['iTotalRecords'] = $this->user->count('role = "'.$role.'"');
            $result['iTotalDisplayRecords'] = $this->user->count_filter($query,$role);
            if ($length == -1) $length = $result['iTotalDisplayRecords'];
            $data = $this->user->list($start, $length, $query, $keySearch,$role);
            foreach ($data as $key) {
                $key->data_nama = '<label for="" class="font-16">'.$key->nama.'</label><p class="font-400 font-12 p-0 m-0">'.$key->email.'</p>';
            }
            $result['aaData'] = $data;
            $this->response($result);
        }else{
            $this->response([
                'status'    => false,
            ]);
        }
    }


}