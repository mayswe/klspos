<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Api_Controller extends CI_Controller {

    protected $user;

    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');
        $this->load->database();

        // Optional: validate token here
        $this->user = $this->validate_token();
    }

    protected function response($status, $message, $data = [])
    {
        echo json_encode([
            "status" => $status,
            "message" => $message,
            "data" => $data
        ]);
        exit;
    }

    protected function validate_token()
    {
        $headers = getallheaders();
        $token = str_replace('Bearer ', '', $headers['Authorization'] ?? '');
        if (!$token) return null;

        $record = $this->db
            ->where('token', $token)
            ->where('expires_at >=', date('Y-m-d H:i:s'))
            ->get('api_tokens')
            ->row();

        if (!$record) return null;

        return $this->db
            ->where('id', $record->user_id)
            ->get('users')
            ->row();
    }
}