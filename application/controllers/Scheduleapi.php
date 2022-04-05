<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Scheduleapi extends REST_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
    public function __construct() {
        parent::__construct();
		$this->load->helper('url');
		$this->load->model('schedule_api_model');
	}
	
    public function index(){
		$response = [
			'status'   => false,
			'messages' => 'Method does not exist'
		];
		$this->response($response, REST_Controller::HTTP_OK);
	}
	public function checkScheduleStatus_get(){
        $security_token = $this->get('security_token');
		$schedulling_id = $this->get('schedulling_id');
        $type = $this->get('type');
        
        if ($security_token && $schedulling_id) {
            $data = $this->schedule_api_model->check_status($security_token,$schedulling_id,$type);
            if ($data['auth_data'] && $data['schedule_data']) {
                if ($data['auth_data'][0]['ytview_support'] !== 1) {
                    unset($data['schedule_data'][0]['scheduled_view_count']);
                    unset($data['schedule_data'][0]['completed_view_count']);
                }
                if ($data['auth_data'][0]['ytcomment_support'] !== 1) {
                    unset($data['schedule_data'][0]['scheduled_comment_count']);
                    unset($data['schedule_data'][0]['completed_comment_count']);
                }
                if ($data['auth_data'][0]['ytlike_support'] !== 1) {
                    unset($data['schedule_data'][0]['scheduled_like_count']);
                    unset($data['schedule_data'][0]['completed_like_count']);
                }
                if ($data['auth_data'][0]['ytsubscribe_support'] !== 1) {
                    unset($data['schedule_data'][0]['scheduled_subscribe_count']);
                    unset($data['schedule_data'][0]['completed_subscribe_count']);
                }
                unset($data['auth_data']);
                if ($data) {
                    $response = [
                        'status'   => true,
                        'messages' => 'Schedule Data',
                        'data'     => $data
                    ];
                } else {
                    $response = [
                        'status'   => false,
                        'messages' => 'Schedule Data Not Found'
                    ];
                }
                $this->response($response, REST_Controller::HTTP_OK);
            } else {
                $response = [
                    'status'   => false,
                    'messages' => 'Invalid Token Or Schedule Id'
                ];
                $this->response($response, REST_Controller::HTTP_OK);
            }
        }  else {
			$this->requiredInput(['security_token']);
			$this->requiredInput(['schedulling_id']);
		}
    }
    public function stopScheduleStatus_get(){
        $security_token = $this->get('security_token');
        $schedulling_id = $this->get('schedulling_id');
        if ($security_token && $schedulling_id) {
            $status = $this->schedule_api_model->stop_schedule($security_token,$schedulling_id);
            if ($status) {
                $response = [
                    'status'   => true,
                    'messages' => 'Stop schedule successfully'
                ];
                $this->response($response, REST_Controller::HTTP_OK);
            } else {
                $response = [
                    'status'   => false,
                    'messages' => 'Something went wrong'
                ];
                $this->response($response, REST_Controller::HTTP_NOT_ACCEPTABLE);
            }
        } else {
			$this->requiredInput(['security_token']);
			$this->requiredInput(['schedulling_id']);
		}
    }
    public function validateSecurityKey($key){
        $authData = $this->schedule_api_model->check_key($key);
        return $authData;
    }
    // public function validateUserPlan($view, $like, $comment, $subscribe){
    //     $planStatus = $this->schedule_api_model->check_plan($key, $view, $like, $comment, $subscribe);
    //     return $planStatus;
    // }
    public function saveUserRequest_post(){
        $yt_url = $_POST['video_url'];
        $keyword = $_POST['keyword'];
        $security_key = $_POST['security_key'];
        $view = $_POST['view'];
        $like = $_POST['like'];
        $comment = $_POST['comment'];
        $subscribe = $_POST['subscribe'];
        $duration = $_POST['duration'];
        $channel_id = $_POST['channel_id'];

        if ($yt_url && $keyword && $security_key && $keyword && $view && $like && $comment && $subscribe && $duration && $channel_id) {
            $validateAPIData = $this->validateSecurityKey($security_key);
            if ($validateAPIData) {
                $view_support = $validateAPIData[0]['ytview_support'];
                $comment_support = $validateAPIData[0]['ytcomment_support'];
                $like_support = $validateAPIData[0]['ytlike_support'];
                $subscribe_support = $validateAPIData[0]['ytsubscribe_support'];
                if (!$view_support) {
                    $view = 0;
                }
                if (!$comment_support) {
                    $comment = 0;
                }
                if (!$like_support) {
                    $like = 0;
                }
                if (!$subscribe_support) {
                    $subscribe = 0;
                }

                $lastId = $this->schedule_api_model->get_last_id();
                $auth_id = $validateAPIData[0]['authentication_id'];
                if (is_numeric($duration) && is_numeric($view) && is_numeric($like) && is_numeric($comment) && is_numeric($subscribe)) {
                    $res = $this->schedule_api_model->add_user_request($lastId, $auth_id, $yt_url, $keyword, $view, $like, $comment, $subscribe, $duration, $channel_id);
                    if ($res) {
                        $this->response([
                            'status' 	=> true,
                            'message' 	=> 'Your data has been successfully stored'
                        ], REST_Controller::HTTP_OK);

                    } else {
                        $this->response([
                            'status' 	=> false,
                            'message' 	=> 'Something went wrong!!'
                        ], REST_Controller::HTTP_NOT_ACCEPTABLE);
                    }
                } else {
                    $response = [
                        'status'   => false,
                        'messages' => 'Only numeric value allowed'
                    ];
                    $this->response($response, REST_Controller::HTTP_OK);
                }
                
            } else {
                $response = [
                    'status'   => false,
                    'messages' => 'Invalid security key'
                ];
                $this->response($response, REST_Controller::HTTP_OK);
            }
        } else {
            $response = [
                'status'   => false,
                'messages' => 'All fields are required'
            ];
            $this->response($response, REST_Controller::HTTP_OK);
        }
    }
    public function scheduleLogEntry_post(){
        $schedule_log_id = $_POST['schedule_log_id'];
        $schedule_id = $_POST['schedule_id'];
        $server_master_id = $_POST['server_master_id'];
        $video_url = $_POST['video_url'];
        $channel_id = $_POST['channel_id'];
        $data_process_server_ip = $_POST['data_process_server_ip'];
        $data_process_proxy_ip = $_POST['data_process_proxy_ip'];
        $port = $_POST['port'];
        $request_start_time = date("Y-m-d h:i:s");
        $request_end_time = date("Y-m-d h:i:s");
        $status = $_POST['status'];
        $request_type = $_POST['request_type'];
        $remarks = $_POST['remarks'];

        $lId = $this->schedule_api_model->get_last_schedulelog_id();
        $lastId = $lId + 1;

        $log_data = array("schedule_data_log_id" => (int)$lastId,"schedule_data_id" => (int)$schedule_id,"server_master_id" => (int)$server_master_id,"video_url" => $video_url,"data_process_server_ip" => $data_process_server_ip,"data_process_proxy_ip" => $data_process_proxy_ip,"port" => (int)$port,"request_start_time" => $request_start_time,"request_end_time" => $request_end_time,"status" => $status,"request_type" => $request_type,"remarks" => $remarks, "channel_id" => $channel_id,);

        if ($status) {
            if ($schedule_log_id === '') {
                $res = $this->schedule_api_model->add_schedule_log($log_data);
                $insertedId = $this->schedule_api_model->get_last_schedulelog_id();
                if ($res) {
                    $this->response([
                        'status' 	=> true,
                        'message' 	=> 'Data inserted successfully',
                        'log_id'    => $insertedId
                    ], REST_Controller::HTTP_OK);

                } else {
                    $this->response([
                        'status' 	=> false,
                        'message' 	=> 'Something went wrong!!'
                    ], REST_Controller::HTTP_NOT_ACCEPTABLE);
                }
            } else {
                $res = $this->schedule_api_model->update_schedule_log($schedule_log_id, $log_data);
                if ($res) {
                    $this->response([
                        'status' 	=> true,
                        'message' 	=> 'Data updated successfully'
                    ], REST_Controller::HTTP_OK);

                } else {
                    $this->response([
                        'status' 	=> false,
                        'message' 	=> 'Something went wrong!!'
                    ], REST_Controller::HTTP_NOT_ACCEPTABLE);
                }
            }
        } else {
            $response = [
                'status'   => false,
                'messages' => 'Status invalid'
            ];
            $this->response($response, REST_Controller::HTTP_OK);
        }
    }
}
