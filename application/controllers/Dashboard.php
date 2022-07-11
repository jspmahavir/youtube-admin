<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';

/**
 * Class : Dashboard (DashboardController)
 * Dashboard Class to control all dashboard related operations.
 */
class Dashboard extends BaseController
{
    /**
     * This is default constructor of the class
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('dashboard_model');
        $this->isLoggedIn();   
    }
    
    /**
     * This function used to load the first screen of the dashboard
     */
    public function index()
    {
        $this->global['pageTitle'] = 'YouTube Viewer : Dashboard';

        $data['panelCounts'] = $this->dashboard_model->panelCount();
        
        $this->loadViews("dashboard", $this->global, $data, NULL);
    }

    /**
     * This function is used to show users profile
     */
    function profile($active = "details")
    {
        $data["userInfo"] = $this->dashboard_model->getUserInfoWithRole($this->vendorId);
        $data["active"] = $active;
        
        $this->global['pageTitle'] = $active == "details" ? 'YouTube Viewer : My Profile' : 'YouTube Viewer : Change Password';
        $this->loadViews("profile", $this->global, $data, NULL);
    }

    /**
     * This function is used to update the user details
     * @param text $active : This is flag to set the active tab
     */
    function profileUpdate($active = "details")
    {
        $this->load->library('form_validation');
            
        $this->form_validation->set_rules('username','Username','trim|required|max_length[128]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->profile($active);
        }
        else
        {
            $name = ucwords(strtolower($this->security->xss_clean($this->input->post('username'))));
            
            $userInfo = array('username'=>$name, 'modified_date'=>date('Y-m-d H:i:s'));
            
            $result = $this->dashboard_model->editUser($userInfo, $this->vendorId);
            
            if($result == true)
            {
                $this->session->set_userdata('name', $name);
                $this->session->set_flashdata('success', 'Profile updated successfully');
            }
            else
            {
                $this->session->set_flashdata('error', 'Profile updation failed');
            }

            redirect('profile/'.$active);
        }
    }

    /**
     * This function is used to change the password of the user
     * @param text $active : This is flag to set the active tab
     */
    function changePassword($active = "changepass")
    {
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('oldPassword','Old password','required|max_length[20]');
        $this->form_validation->set_rules('newPassword','New password','required|max_length[20]');
        $this->form_validation->set_rules('cNewPassword','Confirm new password','required|matches[newPassword]|max_length[20]');
        
        if($this->form_validation->run() == FALSE)
        {
            $this->profile($active);
        }
        else
        {
            $oldPassword = $this->input->post('oldPassword');
            $newPassword = $this->input->post('newPassword');
            
            $resultPas = $this->dashboard_model->matchOldPassword($this->vendorId, $oldPassword);
            
            if(empty($resultPas))
            {
                $this->session->set_flashdata('nomatch', 'Your old password is not correct');
                redirect('profile/'.$active);
            }
            else
            {
                $usersData = array('password'=>getHashedPassword($newPassword), 'modified_date'=>date('Y-m-d H:i:s'));
                
                $result = $this->dashboard_model->changePassword($this->vendorId, $usersData);
                
                if($result == true) { $this->session->set_flashdata('success', 'Password updation successful'); }
                else { $this->session->set_flashdata('error', 'Password updation failed'); }
                
                redirect('profile/'.$active);
            }
        }
    }

    /**
     * Page not found : error 404
     */
    function pageNotFound()
    {
        $this->global['pageTitle'] = 'CodeInsect : 404 - Page Not Found';
        
        $this->loadViews("404", $this->global, NULL, NULL);
    }
}

?>