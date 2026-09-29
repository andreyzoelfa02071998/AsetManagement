<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function login()
    {
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run()) {
            $user = $this->User_model->find_by_email($this->input->post('email', TRUE));
            if ($user && password_verify($this->input->post('password'), $user->password_hash)) {
                $this->session->set_userdata(array(
                    'user_id' => $user->id,
                    'user_name' => $user->name
                ));
                $redirect_to = $this->session->userdata('redirect_after_login') ?: site_url('dashboard');
                $this->session->unset_userdata('redirect_after_login');
                redirect($redirect_to);
            }
            $this->session->set_flashdata('error', 'Email atau password salah.');
        }

        $this->load->view('layouts/auth', array(
            'title' => 'Login',
            'content' => 'auth/login'
        ));
    }

    public function register()
    {
        $this->form_validation->set_rules('name', 'Nama', 'required|max_length[120]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');

        if ($this->form_validation->run()) {
            if ($this->User_model->find_by_email($this->input->post('email', TRUE))) {
                $this->session->set_flashdata('error', 'Email sudah terdaftar.');
                redirect('register');
            }

            $user_id = $this->User_model->create(array(
                'name' => $this->input->post('name', TRUE),
                'email' => $this->input->post('email', TRUE),
                'password' => $this->input->post('password')
            ));
            $this->session->set_userdata(array(
                'user_id' => $user_id,
                'user_name' => $this->input->post('name', TRUE)
            ));
            $redirect_to = site_url('onboarding');
            $this->session->unset_userdata('redirect_after_login');
            redirect($redirect_to);
        }

        $this->load->view('layouts/auth', array(
            'title' => 'Register',
            'content' => 'auth/register'
        ));
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }
}
