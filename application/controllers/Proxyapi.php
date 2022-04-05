<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Proxyapi extends REST_Controller {

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
		$this->load->model('proxy_api_model');
	}
	
    public function index(){
		$response = [
			'status'   => false,
			'messages' => 'Method does not exist'
		];
		$this->response($response, REST_Controller::HTTP_OK);
	}
	public function getProxyData_get(){
		$data['proxy_master'] = $this->proxy_api_model->get_all();
		if ($data['proxy_master']) {
			$response = [
				'status'   => true,
				'messages' => 'Proxy Data',
				'data'     => $data
			];
		} else {
			$response = [
				'status'   => false,
				'messages' => 'Proxy Data Not Found'
			];
		}
		$this->response($response, REST_Controller::HTTP_OK);
	}
	public function getCheckProxy_get(){
		$proxy_ip = $this->get('proxy_ip');
		$proxy_port = $this->get('proxy_port');
		$video_id = $this->get('ytvideo_id');

		if ($proxy_ip && $proxy_port && $video_id) {
			$data = $this->proxy_api_model->check_proxy($proxy_ip,$proxy_port,$video_id);
			if (count($data) > 0) {
				$response = [
					'status'   => false,
					'messages' => 'Proxy Server Already Assigned'
				];
			} else {
				$response = [
					'status'   => true,
					'messages' => 'Proxy Server Available'
				];
			}
			$this->response($response, REST_Controller::HTTP_OK);
		} else {
			$this->requiredInput(['proxy_ip']);
			$this->requiredInput(['proxy_port']);
			$this->requiredInput(['ytvideo_id']);
		}
	}
	public function saveYTStatistics_post(){
		$data = json_decode(file_get_contents('php://input'), true);
		
		$res = $this->proxy_api_model->update_yt_stats($data);
		if ($res) {
			$this->response([
				'status' 	=> true,
				'message' 	=> 'Your data has been successfully updated into the database'
			], REST_Controller::HTTP_OK);

		} else {
			$this->response([
				'status' 	=> false,
				'message' 	=> 'Something Went Wrong!!'
			], REST_Controller::HTTP_NOT_ACCEPTABLE);
		}
	}
	public function checkYTGeneratedIP_get(){
		$ref_id = $this->get('reference_id');
		$generated_ip = $this->get('generated_ip');

		if ($ref_id && $generated_ip) {
			$res = $this->proxy_api_model->check_generated_ip($ref_id,$generated_ip);
			if (!$res) {
				$response = [
					'status'   => false,
					'messages' => 'Used already'
				];
			} else {
				$response = [
					'status'   => true,
					'messages' => 'Fresh'
				];
			}
			$this->response($response, REST_Controller::HTTP_OK);
		} else {
			$this->requiredInput(['reference_id']);
			$this->requiredInput(['generated_ip']);
		}
	}
}
