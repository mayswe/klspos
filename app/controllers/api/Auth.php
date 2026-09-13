<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('ion_auth');
        $this->load->model('auth_model');
        $this->load->model('site');
        $this->Settings = $this->site->getSettings();
        header('Content-Type: application/json');
    }

    /*
    |--------------------------------------------------------------------------
    | GOOGLE LOGIN
    |--------------------------------------------------------------------------
    */
    public function google()
    {
        $input = json_decode(file_get_contents("php://input"), true);
        $id_token = $input['id_token'] ?? null;

        if (!$id_token) {
            return $this->response(false, "No token provided");
        }

        try {
            // 1. Ask Google directly to verify this token
            $url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . $id_token;
            $response = @file_get_contents($url);
            
            if (!$response) {
                return $this->response(false, "Google could not verify this token. It may have expired.");
            }

            $payload = json_decode($response, true);

            // 2. Security Check: Allow Client IDs from BOTH of your projects
            $allowed_client_ids = [
                '692177977430-5p449io8f844dlcfd312pjtosr012u3d.apps.googleusercontent.com', // klspos Web Client (Master)
                '950977985374-cl4iev25iqrmss61g0d7us4g2um3t0cb.apps.googleusercontent.com'  // zaygabar Web Client
            ];
            
            if (!isset($payload['aud']) || !in_array($payload['aud'], $allowed_client_ids)) {
                $received_aud = $payload['aud'] ?? 'NO_AUDIENCE_FOUND';
                return $this->response(false, "Security Mismatch! Received: $received_aud");
            }

            // 3. Get User Info from the payload
            $email = $payload['email'];
            $name  = $payload['name'] ?? $payload['given_name'] ?? 'Google User';

            // 4. Database Logic
            $user = $this->db
                ->where('email', $email)
                ->get('users')
                ->row();

            if (!$user) {
                $this->db->insert('users', [
                    'email'      => $email,
                    'first_name' => $name,
                    'active'     => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $user_id = $this->db->insert_id();
                $user = $this->db->where('id', $user_id)->get('users')->row();
            }

            if (!$user->active) {
                return $this->response(false, "Account disabled");
            }

            $token = $this->generate_token($user->id);

            return $this->response(true, "Login successful", [
                "token" => $token,
                "user"  => $this->user_data($user)
            ]);

        } catch (Throwable $e) {
            return $this->response(false, "Server Error: " . $e->getMessage());
        }
    }
    
    /*
    |--------------------------------------------------------------------------
    | EMAIL LOGIN
    |--------------------------------------------------------------------------
    */
    public function login()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        $identity = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        if (!$identity || !$password) {
            return $this->response(false, "Email and password required");
        }

        if ($this->ion_auth->login($identity, $password, false)) {
            $user = $this->ion_auth->user()->row();
            $token = $this->generate_token($user->id);

            return $this->response(true, "Login successful", [
                "token" => $token,
                "user"  => $this->user_data($user)
            ]);
        } else {
            return $this->response(false, $this->ion_auth->errors());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    public function logout()
    {
        $token = $this->get_bearer_token();
    
        if ($token) {
            // FIX: Changed from 'user_tokens' to 'api_tokens' to match your DB schema
            $this->db->where('token', $token)->delete('api_tokens');
        }
    
        return $this->response(true, 'Logged out');
    }

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */
    public function profile()
    {
        $user = $this->validate_token();

        if (!$user) {
            return $this->response(false, "Unauthorized");
        }

        return $this->response(true, "Success", [
            "user" => $this->user_data($user)
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER METHODS
    |--------------------------------------------------------------------------
    */
    private function generate_token($user_id)
    {
        $token = bin2hex(random_bytes(40));

        $this->db->insert('api_tokens', [
            'user_id'   => $user_id,
            'token'     => $token,
            'created_at'=> date('Y-m-d H:i:s'),
            'expires_at'=> date('Y-m-d H:i:s', strtotime('+30 days'))
        ]);

        return $token;
    }

    private function validate_token()
    {
        $token = $this->get_bearer_token();
        if (!$token) return false;

        $record = $this->db
            ->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->get('api_tokens')
            ->row();

        if (!$record) return false;

        return $this->db
            ->where('id', $record->user_id)
            ->get('users')
            ->row();
    }

    private function get_bearer_token()
    {
        $headers = getallheaders();
        // Fallback to CodeIgniter framework header check if getallheaders fails
        $auth = $headers['Authorization'] ?? $this->input->get_request_header('Authorization', TRUE) ?? '';

        if (!$auth) return null;

        return str_replace('Bearer ', '', $auth);
    }

    private function user_data($user)
    {
        return [
            "id"        => $user->id,
            "name"      => $user->first_name ?? '',
            "email"     => $user->email,
            "role"      => $user->role ?? 'user',
            "tenant_id" => $user->tenant_id ?? null,
            "store_id"  => $user->store_id ?? null
        ];
    }

    private function response($status, $message, $data = [])
    {
        echo json_encode([
            "status"  => $status,
            "message" => $message,
            "data"    => $data
        ]);
        exit; // Ensure nothing else prints after this JSON string
    }
}