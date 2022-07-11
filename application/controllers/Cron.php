<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';

class Cron extends REST_Controller {

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
		$this->load->model('cron_model');
        $this->load->model('yt_model');
        $this->load->model('configuration_model');
        $settings = $this->configuration_model->getConfigurationInfo();
        $this->config->set_item('settings', $settings);
	}
	
    public function index(){
		$response = [
			'status'   => false,
			'messages' => 'Method does not exist'
		];
		$this->response($response, REST_Controller::HTTP_OK);
	}

    public function allocateProxyServer_get()
    {
        $call = 'view';
        $random_schedule = $this->cron_model->getRandomScheduledData($call);
        if ($random_schedule) {
            $scheduledData = $this->cron_model->getScheduledData($random_schedule, $call);
            if ($scheduledData) {
                $serverAvailable = $this->cron_model->checkServerAvailability();
                if ($serverAvailable) {
                    $thread = $serverAvailable[0]['maximum_thread'];
                    //multi CURL initiate
                    $multiCurl = array();
                    $result = array();
                    $mh = curl_multi_init();
                    for ($i=1; $i <= $thread; $i++) {
                        $proxyAvailable = $this->cron_model->checkProxyAvailability($scheduledData['video_url']);
                        if ($proxyAvailable) {
                            $insertStats = $this->cron_model->insertYTStatistics($proxyAvailable['proxy_url'],$proxyAvailable['proxy_port'],$scheduledData['video_url'],$random_schedule['schedule_data_id'],$serverAvailable[0]['server_master_id'],$serverAvailable[0]['server_ip']);

                            $rArray = array();
                            $rArray[] = array("ip" => $proxyAvailable['proxy_url'], "port" => $proxyAvailable['proxy_port'], "user" => $proxyAvailable['username'], "pass" => $proxyAvailable['password']);
                            $rArray[] = array("ip" => $proxyAvailable['proxy_url'], "port" => $proxyAvailable['proxy_port'], "user" => $proxyAvailable['username'], "pass" => $proxyAvailable['password']);

                            $data['search_text'] = $scheduledData['keyword']." ::::".$scheduledData['keyword']." :::: ".$scheduledData['video_url'];
                            $data['unique_reference_id'] = $insertStats;
                            $data['proxy'] = $rArray;
                            
                            echo json_encode($data);

                            $url = $serverAvailable[0]['end_point'];

                            $multiCurl[$i] = curl_init();

                            curl_setopt($multiCurl[$i], CURLOPT_URL,$url);
                            curl_setopt($multiCurl[$i], CURLOPT_POSTFIELDS, json_encode($data));
                            curl_setopt($multiCurl[$i], CURLOPT_HEADER,1);
                            curl_setopt($multiCurl[$i], CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                            curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER,1);
                            curl_setopt($multiCurl[$i], CURLOPT_CONNECTTIMEOUT, 0);
                            curl_setopt($multiCurl[$i], CURLOPT_TIMEOUT, 4000);
                            curl_multi_add_handle($mh, $multiCurl[$i]);

                            // $ch = curl_init($url);
                            // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                            // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                            // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            // $result = curl_exec($ch);
                            // curl_close($ch);
                        } else {
                            $response = [
                                'status'   => false,
                                'messages' => 'Proxy not available'
                            ];
                            $this->response($response, REST_Controller::HTTP_OK);
                        }
                    }
                    $index=null;
                    do {
                        curl_multi_exec($mh,$index);
                    } while($index > 0);
                    // get content and remove handles
                    // foreach($multiCurl as $k => $ch) {
                    //     $result[$k] = curl_multi_getcontent($ch);
                    //     curl_multi_remove_handle($mh, $ch);
                    // }
                    // close
                    curl_multi_close($mh);
                } else {
                    $response = [
                        'status'   => false,
                        'messages' => 'Server not available'
                    ];
                    $this->response($response, REST_Controller::HTTP_OK);
                }
            } else {
                $response = [
                    'status'   => false,
                    'messages' => 'Scheduled data not found'
                ];
                $this->response($response, REST_Controller::HTTP_OK);
            }
        } else {
            $response = [
                'status'   => false,
                'messages' => 'Schedule not found'
            ];
            $this->response($response, REST_Controller::HTTP_OK);
        }
    }

    public function getSeverDetailsbyAvailability() {
        // Get All Server Details
        $serverAvailablability = array();
        $serverData = $this->cron_model->getServerData();
        if($serverData) {
            foreach($serverData as $sData) {
                // Check Server is running or not
                $threadAvailable = array();
                $threadAvailable['server_master_id'] = $sData['server_master_id'];
                $threadAvailable['server_ip'] = $sData['server_ip'];
                $threadAvailable['maximum_thread'] = $sData['maximum_thread'];
                $threadAvailable['end_point'] = $sData['end_point'];
                // Get Specific Server Running Stats
                $sLog = $this->cron_model->checkServerStatsFromLog($sData['server_master_id']);
                // Calculate Specific server available Thread for Next execution..
                $aThread = $sData['maximum_thread'] - count($sLog);
                $threadAvailable['available_thread'] = $aThread;
                $serverAvailablability[] = $threadAvailable;
            }
        }
        shuffle($serverAvailablability);
        return $serverAvailablability;
    }

    public function ytView_get() {
        $serverAvailableData = $this->getSeverDetailsbyAvailability();
        if($serverAvailableData) {
            foreach($serverAvailableData as $sAvailable) {
                $call = 'view';
                $random_schedule = $this->cron_model->getRandomScheduledData($call);
                if ($random_schedule) {
                    $scheduledData = $this->cron_model->getScheduledData($random_schedule, $call);
                    if ($scheduledData) {
                        $thread = $sAvailable['available_thread'];
                        $response = $this->viewProcessCurl($random_schedule, $scheduledData, $sAvailable, $thread);
                        $this->response($response, REST_Controller::HTTP_OK);
                    } else {
                        $response = [
                            'status'   => false,
                            'messages' => 'Scheduled data not found'
                        ];
                        $this->response($response, REST_Controller::HTTP_OK);
                    }
                } else {
                    $response = [
                        'status'   => false,
                        'messages' => 'Schedule not found'
                    ];
                    $this->response($response, REST_Controller::HTTP_OK);
                }
                
            }
        } else {
            $response = [
                'status'   => false,
                'messages' => 'Server not Available'
            ];
            $this->response($response, REST_Controller::HTTP_OK);
        }
    }

    public function viewProcessCurl($random_schedule, $scheduledData, $sAvailable, $thread) {
        // Multi CURL initiate
        $multiCurl = array();
        $result = array();
        $mh = curl_multi_init();
        for ($i=1; $i <= $thread; $i++) {
            $proxyAvailable = $this->cron_model->checkProxyAvailability($scheduledData['video_url']);
            if ($proxyAvailable) {
                $insertStats = $this->cron_model->insertYTStatistics($proxyAvailable['proxy_url'],$proxyAvailable['proxy_port'],$scheduledData['video_url'],$random_schedule['schedule_data_id'],$sAvailable['server_master_id'], $sAvailable['server_ip']);
                
                $rArray = array();
                $rArray[] = array("ip" => $proxyAvailable['proxy_url'], "port" => $proxyAvailable['proxy_port'], "user" => $proxyAvailable['username'], "pass" => $proxyAvailable['password']);
                $rArray[] = array("ip" => $proxyAvailable['proxy_url'], "port" => $proxyAvailable['proxy_port'], "user" => $proxyAvailable['username'], "pass" => $proxyAvailable['password']);

                $data['search_text'] = $scheduledData['keyword']." ::::".$scheduledData['keyword']." :::: ".$scheduledData['video_url'];
                $data['unique_reference_id'] = $insertStats;
                $data['proxy'] = $rArray;
                
                echo json_encode($data);
                $url = $sAvailable['end_point'];
                $multiCurl[$i] = curl_init();

                curl_setopt($multiCurl[$i], CURLOPT_URL,$url);
                curl_setopt($multiCurl[$i], CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($multiCurl[$i], CURLOPT_HEADER,1);
                curl_setopt($multiCurl[$i], CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER,1);
                curl_setopt($multiCurl[$i], CURLOPT_CONNECTTIMEOUT, 0);
                curl_setopt($multiCurl[$i], CURLOPT_TIMEOUT, 4000);
                curl_multi_add_handle($mh, $multiCurl[$i]);
            } else {
                $response = [
                    'status'   => false,
                    'messages' => 'Proxy not available'
                ];
                return $response;
                // $this->response($response, REST_Controller::HTTP_OK);
            }
        }
        $index=null;
        do {
            curl_multi_exec($mh,$index);
        } while($index > 0);
        curl_multi_close($mh);
    }

    public function generateAccessToken($info) {
        $timeFirst  = strtotime($info['expire_time']);
        $timeSecond = strtotime(date('Y/m/d H:i:s'));
        $differenceInSeconds = $timeSecond - $timeFirst;

        // Check if already exist token is expire or not..
        if($differenceInSeconds > $info['expires_in'])
        {
            $postArr = array('client_id' => $info['client_id'], 'refresh_token' => $info['refresh_token'], 'client_secret' => $info['client_secret'], 'grant_type' => 'refresh_token');
            $token_url = $this->config->item('yt_token_url');
            $ch = curl_init($token_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postArr));
            $response = curl_exec($ch);
            curl_close($ch);

            $resObj = json_decode($response);
            $resArr = $this->object_to_array($resObj);
            if($resArr['access_token']) {
                $data = array('access_token' => $resArr['access_token'], 'expire_time' => date('Y/m/d H:i:s'));
                $updatedInfo = $this->cron_model->updateTokenInfo($data, $info);
                return json_encode($updatedInfo);
            }
            return $response;
        } else {
            $ret = array(
                    'access_token' => $info['access_token'],
                    'expires_in' => $info['expires_in'],
                    'scope' => $info['scope'],
                    'token_type' => $info['token_type']
                );
            return json_encode($ret);
        }
    }
    
    public function ytlike_get() {
        echo "YouTube Like Process ...";
        $call = 'like';
        $random_schedule = $this->cron_model->getRandomScheduledData($call);
        if ($random_schedule) {
            $scheduledData = $this->cron_model->getScheduledData($random_schedule, $call);
            if ($scheduledData) {
                $likeCount = $scheduledData['scheduled_like_count'] - $scheduledData['completed_like_count'];
                $ytVideoId = $scheduledData['video_url'];
                $scheduleDataId = $scheduledData['schedule_data_id'];
                $tokenInfoData = $this->cron_model->getRandomAuthTokenLike($ytVideoId, $likeCount);
                foreach($tokenInfoData as $tokenInfo) {
                    if (is_array($tokenInfo) && count($tokenInfo) > 0) {
                        $accessTokenData = $this->generateAccessToken($tokenInfo);
                        $accessTokenData = json_decode($accessTokenData, true);
                        if($accessTokenData) {
                            // Add sleep time between each comment.
                            sleep(rand(2,20));
                            $accessToken = $accessTokenData['access_token'];
                            $this->likeProcess($tokenInfo, $accessToken, $ytVideoId, $scheduleDataId);
                        } else {
                            $response = [
                                'status'   => false,
                                'messages' => 'Access token not generated!!'
                            ];
                            $this->response($response, REST_Controller::HTTP_OK);
                        }
                    } else {
                        $response = [
                            'status'   => false,
                            'messages' => 'Token info not found'
                        ];
                        $this->response($response, REST_Controller::HTTP_OK);
                    }
                }
            } else {
                $response = [
                    'status'   => false,
                    'messages' => 'Schedule data not found'
                ];
                $this->response($response, REST_Controller::HTTP_OK);
            }
        } else {
            $response = [
                'status'   => false,
                'messages' => 'Schedule data not found'
            ];
            $this->response($response, REST_Controller::HTTP_OK);
        }
    }

    public function likeProcess($info, $accessToken, $ytVideoId, $scheduleDataId) {
        if(isset($info) && isset($accessToken) && isset($ytVideoId) && isset($scheduleDataId)) {
            $ytLikeStats = array(
                'schedule_id' => $scheduleDataId,
                'video_id' => $ytVideoId,
                'app_id' => $info['app_id'],
                'user_email' => $info['user_email'],
                'client_id' => $info['client_id'],
                'client_secret' => $info['client_secret'],
                'accessToken' => $accessToken,
                'refresh_token' => $info['refresh_token'],
                'status' => 'InProgress',
                'created' => date('Y/m/d H:i:s')
            );
            
            $sId = $this->cron_model->addLikeStats($ytLikeStats);
            $yt_video_id = $ytVideoId;
            
            $curl_url = $this->config->item('yt_like_url');
            $curl_url = $curl_url."?id=".$yt_video_id."&rating=like";

            $httpHeader[] = "Authorization: Bearer ". $accessToken;
            $httpHeader[] = "Content-Length: 0";
            
            $ch = curl_init($curl_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
            $response = curl_exec($ch);
            if($response) {
                $responseArr = json_decode($response, true);
                if(isset($responseArr['error'])) {
                    $ytLikeStats = array(
                        'status' => 'error',
                        'error_msg' => $responseArr['error']['message']
                    );
                }
            } else {
                $ytLikeStats = array(
                    'status' => 'success',
                    'like_count' => 1
                );
                $this->cron_model->updateLikeCounter($yt_video_id);
            }
            if($sId) {
                $return = $this->cron_model->updateLikeStats($ytLikeStats, $sId);
            }
        }
    }

    public function ytsubscribe_get() {
        echo "YouTube Subscribe Process ...";
        $call = 'subscribe';
        $random_schedule = $this->cron_model->getRandomScheduledData($call);
        if ($random_schedule) {
            $scheduledData = $this->cron_model->getScheduledData($random_schedule, $call);
            if ($scheduledData) {
                $subscribeCount = $scheduledData['scheduled_subscribe_count'] - $scheduledData['completed_subscribe_count'];
                $ytChannelId = $scheduledData['channelId'];
                $scheduleDataId = $scheduledData['schedule_data_id'];
                $tokenInfoData = $this->cron_model->getRandomAuthTokenSubscribe($ytChannelId, $subscribeCount);
                foreach($tokenInfoData as $tokenInfo) {
                    if (is_array($tokenInfo) && count($tokenInfo) > 0) {
                        $accessTokenData = $this->generateAccessToken($tokenInfo);
                        $accessTokenData = json_decode($accessTokenData, true);
                        if($accessTokenData) {
                            // Add sleep time between each comment.
                            sleep(rand(2,20));
                            $accessToken = $accessTokenData['access_token'];
                            $this->subscribeProcess($tokenInfo, $accessToken, $ytChannelId, $scheduleDataId);
                        } else {
                            $response = [
                                'status'   => false,
                                'messages' => 'Access token not generated!!'
                            ];
                            $this->response($response, REST_Controller::HTTP_OK);
                        }
                    } else {
                        $response = [
                            'status'   => false,
                            'messages' => 'Token info not found'
                        ];
                        $this->response($response, REST_Controller::HTTP_OK);
                    }
                }
            } else {
                $response = [
                    'status'   => false,
                    'messages' => 'Schedule data not found'
                ];
                $this->response($response, REST_Controller::HTTP_OK);
            }
        } else {
            $response = [
                'status'   => false,
                'messages' => 'Schedule data not found'
            ];
            $this->response($response, REST_Controller::HTTP_OK);
        }
    }

    public function subscribeProcess($info, $accessToken, $ytChannelId, $scheduleDataId) {
        if(isset($info) && isset($accessToken) && isset($ytChannelId) && isset($scheduleDataId)) {
            $ytSubscribeStats = array(
                'schedule_id' => $scheduleDataId,
                'channelId' => $ytChannelId,
                'app_id' => $info['app_id'],
                'user_email' => $info['user_email'],
                'client_id' => $info['client_id'],
                'client_secret' => $info['client_secret'],
                'accessToken' => $accessToken,
                'refresh_token' => $info['refresh_token'],
                'status' => 'InProgress',
                'created' => date('Y/m/d H:i:s')
            );
            
            $sId = $this->cron_model->addSubscribeStats($ytSubscribeStats);
            $channelId = $ytChannelId;
            
            $curl_url = $this->config->item('yt_subscribe_url');
            $curl_url = $curl_url."?part=snippet";
            $postData = array("snippet" => array("resourceId" => array("channelId" => $channelId)));

            $httpHeader[] = "Authorization: Bearer ". $accessToken;
            $httpHeader[] = "Content-Type: application/json";
            
            $ch = curl_init($curl_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
            $response = curl_exec($ch);

            if($response) {
                $responseArr = json_decode($response, true);
                if(isset($responseArr['error'])) {
                    $ytSubscribeStats = array(
                        'status' => 'error',
                        'error_msg' => $responseArr['error']['message']
                    );
                } else {
                    $ytSubscribeStats = array(
                        'status' => 'success',
                        'subscribe_count' => 1
                    );
                    $this->cron_model->updateSubscribeCounter($channelId);
                }
            }
            if($sId) {
                $return = $this->cron_model->updateSubscribeStats($ytSubscribeStats, $sId);
            }
        }
    }

    public function ytcomment_get() {
        echo "YouTube Comment Process ...";
        $call = 'comment';
        $random_schedule = $this->cron_model->getRandomScheduledData($call);
        if ($random_schedule) {
            $scheduledData = $this->cron_model->getScheduledData($random_schedule, $call);
            if ($scheduledData) {
                $commentCount = $scheduledData['scheduled_comment_count'] - $scheduledData['completed_comment_count'];
                if($commentCount > 1) {
                    $commentCount = 1;
                }
                $ytVideoId = $scheduledData['video_url'];
                $scheduleDataId = $scheduledData['schedule_data_id'];
                $tokenInfoData = $this->cron_model->getRandomAuthTokenComment($ytVideoId, $commentCount);
                foreach($tokenInfoData as $tokenInfo) {
                    if (is_array($tokenInfo) && count($tokenInfo) > 0) {
                        $accessTokenData = $this->generateAccessToken($tokenInfo);
                        $accessTokenData = json_decode($accessTokenData, true);
                        if($accessTokenData) {
                            // Add sleep time between each comment.
                            sleep(rand(2,20));
                            $accessToken = $accessTokenData['access_token'];
                            $this->commentProcess($tokenInfo, $accessToken, $ytVideoId, $scheduleDataId);
                        } else {
                            $response = [
                                'status'   => false,
                                'messages' => 'Access token not generated!!'
                            ];
                            $this->response($response, REST_Controller::HTTP_OK);
                        }
                    } else {
                        $response = [
                            'status'   => false,
                            'messages' => 'Token info not found'
                        ];
                        $this->response($response, REST_Controller::HTTP_OK);
                    }
                }
            } else {
                $response = [
                    'status'   => false,
                    'messages' => 'Schedule data not found'
                ];
                $this->response($response, REST_Controller::HTTP_OK);
            }
        } else {
            $response = [
                'status'   => false,
                'messages' => 'Schedule data not found'
            ];
            $this->response($response, REST_Controller::HTTP_OK);
        }
    }

    public function commentProcess($info, $accessToken, $ytVideoId, $scheduleDataId) {
        if(isset($info) && isset($accessToken) && isset($ytVideoId) && isset($scheduleDataId)) {
            $comment = $this->cron_model->getUniqeComment($ytVideoId);
            if(empty($comment)) {
                $comment = $this->cron_model->getUniqeGeneralComment($ytVideoId);
            }
            if(empty($comment)) {
                $comment = "This is very nice video!!";
            }
            $ytCommentStats = array(
                'schedule_id' => $scheduleDataId,
                'video_id' => $ytVideoId,
                'comment' => $comment,
                'app_id' => $info['app_id'],
                'user_email' => $info['user_email'],
                'client_id' => $info['client_id'],
                'client_secret' => $info['client_secret'],
                'accessToken' => $accessToken,
                'refresh_token' => $info['refresh_token'],
                'status' => 'InProgress',
                'created' => date('Y/m/d H:i:s')
            );
            
            $cId = $this->cron_model->addCommentStats($ytCommentStats);
            $yt_video_id = $ytVideoId;
            
            $curl_url = $this->config->item('yt_comment_url');
            $curl_url = $curl_url."?part=snippet";
            $postData = array("snippet" => array("topLevelComment" => array("snippet" => array("videoId" => $yt_video_id, "textOriginal" => $comment))));

            $httpHeader[] = "Authorization: Bearer ". $accessToken;
            $httpHeader[] = "Content-Type: application/json";
            
            $ch = curl_init($curl_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
            $response = curl_exec($ch);

            if($response) {
                $responseArr = json_decode($response, true);
                if(isset($responseArr['error'])) {
                    $ytCommentStats = array(
                        'status' => 'error',
                        'error_msg' => $responseArr['error']['message']
                    );
                } else {
                    $ytCommentStats = array(
                        'status' => 'success',
                        'comment_count' => 1
                    );
                    $this->cron_model->updateCommentCounter($scheduleDataId);
                }
            }
            if($cId) {
                $return = $this->cron_model->updateCommentStats($ytCommentStats, $cId);
            }
        }
    }

    public function cleanupLogs_get() {
        echo $curr_hour = date('Y/m/d H:i:s');
        echo "<br/>";
        echo $last_hour = date('Y/m/d H:i:s', strtotime('-1 hour'));
        $this->cron_model->removeStatsEntries($last_hour);
    }

    function object_to_array($data) {
        if ((! is_array($data)) and (! is_object($data)))
            return 'xxx'; // $data;
    
        $result = array();
    
        $data = (array) $data;
        foreach ($data as $key => $value) {
            if (is_object($value))
                $value = (array) $value;
            if (is_array($value))
                $result[$key] = object_to_array($value);
            else
                $result[$key] = $value;
        }
        return $result;
    }

    public function generateVideoTranscript_get() {
        $scheduledData = $this->cron_model->getTranscriptVideoIds();
        $serverAvailable = $this->cron_model->checkServerAvailability();
        if($serverAvailable) {
            $url = $serverAvailable[0]['end_point'];
            $endPoint = str_replace('checkdata', 'transcript', $url);
            if($scheduledData) {
                foreach($scheduledData as $schedule) {
                    //divide sceduled comments ratio into 50, 25, 25 percent for comments, question & opinion
                    $scheduleComments = $schedule['scheduled_comments'];
                    $remainder = $scheduleComments % 2;
                    $twoHalf = floor($scheduleComments/2);
                    $commentRatio = $twoHalf + $remainder;

                    $remainder2 = $twoHalf % 2;
                    $twoHalfOfHalf = floor($twoHalf/2);
                    $questionOpinionRatio = $twoHalfOfHalf + $remainder2;
                    //start cURL
                    $data['video_id'] = $schedule['video_url'];
                    $ch = curl_init($endPoint);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 4000);

                    $response = curl_exec($ch);

                    if($response) {
                        $transcriptData = array();
                        $transcriptData = json_decode($response, true);
                        $transcriptData['video_id'] = $schedule['video_url'];
                        $transcriptData['created'] = date('Y/m/d H:i:s');
                        $transcriptData['total_comment'] = $schedule['total_comment'];
                        $transcriptData['comment'] = 0;
                        $transcriptData['comment_ratio'] = $commentRatio;
                        $transcriptData['question_ratio'] = $questionOpinionRatio;
                        $transcriptData['opinion_ratio'] = $questionOpinionRatio;
                        $sId = $this->cron_model->addtranscriptDesc($transcriptData);
                        echo $sId."\n";
                    }
                }
                $this->generatesubComments();
                $ret = [
                    'status'   => true,
                    'messages' => 'All Video Transcript Generated'
                ];
                $this->response($ret, REST_Controller::HTTP_OK);
            } else {
                $ret = [
                    'status'   => false,
                    'messages' => 'Data not found'
                ];
                $this->response($ret, REST_Controller::HTTP_OK);
            }
        } else {
            $ret = [
                'status'   => false,
                'messages' => 'Server not found'
            ];
            $this->response($ret, REST_Controller::HTTP_OK);
        }
    }

    public function generateVideoComments_get() {
        $proxyData = $this->cron_model->getRandomProxyforComments();
        $transcriptData = $this->cron_model->getVideoTranscriptIds();
        $serverAvailable = $this->cron_model->checkServerAvailability();
        if($transcriptData) {
            $url = $serverAvailable[0]['end_point'];
            
            $endPoint = str_replace('checkdata', 'copyai', $url);
            if($transcriptData) {
                foreach($transcriptData as $transcript) {
                    $tid = $transcript['_id']->{'$id'};
                    $data['video_id'] = $transcript['video_id'];
                    $data['transcript'] = $transcript['transcript'];
                    $data['proxy'] = $proxyData['proxy_url'];
                    $data['port'] = $proxyData['proxy_port'];

                    $ch = curl_init($endPoint);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 4000);

                    $response = curl_exec($ch);

                    if($response) {
                        $transcriptData = array();
                        $transcriptData = json_decode($response, true);
                        $comments = $transcriptData["comments"];
                        if(is_array($comments)) {
                            $videoCommentData = array();
                            foreach($comments as $comment) {
                                $videoCommentData['comment_id'] = $this->cron_model->getCommentLastId();
                                $videoCommentData['video_id'] = $transcript['video_id'];
                                $videoCommentData['comment'] = $comment;
                                $videoCommentData['created_date'] = date('Y/m/d H:i:s');
                                $videoCommentData['modified_date'] = date('Y/m/d H:i:s');
                                $sId = $this->cron_model->addvideoComment($videoCommentData);
                                $this->cron_model->updateTranscriptCommentCounter($tid);
                                echo $sId."\n";
                            }
                        }
                    }
                }
                $ret = [
                    'status'   => true,
                    'messages' => 'Video Comment Generated...'
                ];
                $this->response($ret, REST_Controller::HTTP_OK);
            } else {
                $ret = [
                    'status'   => false,
                    'messages' => 'Data not found'
                ];
                $this->response($ret, REST_Controller::HTTP_OK);
            }
        } else {
            $ret = [
                'status'   => false,
                'messages' => 'Transcript data not found!'
            ];
            $this->response($ret, REST_Controller::HTTP_OK);
        }
    }

    public function generatesubComments() {
        $transcriptData = $this->cron_model->getVideoTranscript();
        foreach($transcriptData as $transcript) {
            if(!isset($transcript['commentArr'])) {
                $this->convertParatoArray($transcript);
            }
        }
    }

    public function convertParatoArray($arr) {
        $length = 200;
        $tid = $arr['_id']->{'$id'};
        $transcriptContent = $arr['transcript'];
        $stringAry = explode("||",wordwrap($transcriptContent, $length, "||"));
        $this->cron_model->updateTranscriptwithjson(json_encode($stringAry), $tid);
    }

    public function generateCommentsWritesonic_get() {
        $writeSonicUserId = 'hello@thebaysocial.com';
        $transcriptData = $this->cron_model->getVideoTranscriptForComment();
        if($transcriptData) {
            $url = "https://api.writesonic.com/v1/business/content/analogies?end_user_id=".$writeSonicUserId."&engine=economy&language=en";
            // $url = "https://api.writesonic.com/v1/business/content/analogies?engine=economy&language=en";
            foreach($transcriptData as $transcript) {
                if(isset($transcript['commentArr']) && $transcript['status'] == 'success') {
                    $remainingComments = $transcript['total_comment'] - $transcript['comment'];
                    if($remainingComments > 0) {
                        $tid = $transcript['_id']->{'$id'};
                        $transcriptArr = json_decode($transcript['commentArr']);
                        $rand_keys = array_rand($transcriptArr);
                        $transcriptStr = $transcriptArr[$rand_keys];
                        
                        $data = array();
                        $data['content'] = $transcriptStr;
                        
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-API-KEY: d141e5c1-10d6-4037-a2e6-816e1e87a834', 'Content-Type:application/json'));
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 4000);

                        $response = curl_exec($ch);
                        $resArr = json_decode($response, true);
                        if(isset($resArr['detail'])) {
                            $errMsg = "";
                            foreach($resArr['detail'] as $res) {
                                $errMsg = $res['msg'];
                            }
                            $ret = [
                                'status'   => false,
                                'messages' => $errMsg
                            ];
                            $this->response($ret, REST_Controller::HTTP_OK);
                        } else {
                            foreach($resArr as $r) {
                                $videoCommentData = array();
                                $videoCommentData['comment_id'] = $this->cron_model->getCommentLastId();
                                $videoCommentData['video_id'] = $transcript['video_id'];
                                $videoCommentData['comment'] = $r['text'];
                                $videoCommentData['created_date'] = date('Y/m/d H:i:s');
                                $videoCommentData['modified_date'] = date('Y/m/d H:i:s');
                                $videoCommentData['source'] = 'writesonic';
                                $sId = $this->cron_model->addvideoComment($videoCommentData);
                                $this->cron_model->updateTranscriptCommentCounter($tid);
                                echo $sId."\n";
                            }
                        }
                    }
                } else {
                    $ret = [
                        'status'   => false,
                        'messages' => 'Video Transcript not found!'
                    ];
                    $this->response($ret, REST_Controller::HTTP_OK);
                }
                $ret = [
                    'status'   => true,
                    'messages' => 'Video Comment Generated...'
                ];
                $this->response($ret, REST_Controller::HTTP_OK);
            }
        } else {
            $ret = [
                'status'   => false,
                'messages' => 'Data not found'
            ];
            $this->response($ret, REST_Controller::HTTP_OK);
        }
    }

    public function createWorkFlowViaDrafter_get() {
        //get configuration settings
        $config = $this->config->item('settings');
        $con = json_decode($config[0]['configuration']);

        $transcriptData = $this->cron_model->getVideoTranscriptForComment();
        if($transcriptData) {
            $url = 'https://api.drafter.ai/workflow-executions';
            foreach($transcriptData as $transcript) {
                if($transcript['status'] === 'success' && isset($transcript['transcript']) && isset($transcript['video_id'])) {
                    $remainingComments = $transcript['total_comment'] - $transcript['comment'];
                    if($remainingComments > 0) {
                        $tid = $transcript['_id']->{'$id'};
                        $transcriptVal = $transcript['transcript'];
                        if (isset($transcript['comment_ratio'])) {
                            if ($transcript['comment_ratio'] > 0 || $transcript['question_ratio'] > 0 || $transcript['opinion_ratio'] > 0) {
                                $data = array();
                                if ($transcript['comment_ratio'] > 0) {
                                    $data['workflowId'] = $con->comment_wf_id;
                                } elseif ($transcript['question_ratio'] > 0) {
                                    $data['workflowId'] = $con->question_wf_id;
                                } elseif ($transcript['opinion_ratio'] > 0) {
                                    $data['workflowId'] = $con->opinion_wf_id;
                                }
                                //drafter may be allow only upto 400 words, so we can make transcript with 400 words
                                $ytTranscript = implode(' ', array_slice(explode(' ', $transcriptVal), 0, 395));
                                if ($data['workflowId'] === '110') {
                                    $data['context']['youtubeLink'] = 'https://www.youtube.com/watch?v='.$transcript['video_id'];
                                } else {
                                    $data['context']['transcript'] = $ytTranscript;
                                }
                                $post = json_encode($data);
    
                                $curl = curl_init();
                                $key = $con->drafter_key;
                                curl_setopt_array($curl, array(
                                    CURLOPT_URL => $url,
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'POST',
                                    CURLOPT_POSTFIELDS => $post,
                                    CURLOPT_HTTPHEADER => array(
                                        'X-Access-Key: '.$key,
                                        'Content-Type: application/json'
                                    ),
                                ));
    
                                $response = curl_exec($curl);
                                curl_close($curl);
                                $resArr = json_decode($response, true);
                                if($tid == '') {
                                    $errMsg = $resArr['error']['message'];
                                    $ret = [
                                        'status'   => false,
                                        'messages' => $errMsg
                                    ];
                                    $this->response($ret, REST_Controller::HTTP_OK);
                                } else {
                                    $silk = $this->cron_model->addWorkflowExecution($tid, $data['workflowId'], $resArr[0]['id'], $post, $resArr);
                                    if ($silk === true) {
                                        $ret = [
                                            'status'   => true,
                                            'messages' => 'Workflow execution added successfully'
                                        ];
                                        $this->response($ret, REST_Controller::HTTP_OK);
                                    } else {
                                        $ret = [
                                            'status'   => false,
                                            'messages' => 'Workflow execution add failed'
                                        ];
                                        $this->response($ret, REST_Controller::HTTP_OK);
                                    }
                                }
                            } else {
                                $ret = [
                                    'status'   => false,
                                    'messages' => 'Maximum comments are added!!'
                                ];
                                $this->response($ret, REST_Controller::HTTP_OK);
                            }
                        }
                    }
                } else {
                    $ret = [
                        'status'   => false,
                        'messages' => 'Video Transcript not found!'
                    ];
                    $this->response($ret, REST_Controller::HTTP_OK);
                }
            }
        } else {
            $ret = [
                'status'   => false,
                'messages' => 'Data not found'
            ];
            $this->response($ret, REST_Controller::HTTP_OK);
        }
    }
    
    public function generateCommentsViaDrafter_get() {
        //get configuration settings
        $config = $this->config->item('settings');
        $con = json_decode($config[0]['configuration']);

        $executedData = $this->cron_model->getWorkflowExecution();

        if ($executedData) {
            foreach($executedData as $execution) {
                if ($execution['status'] === "remaining" && isset($execution['execution_id']) && isset($execution['transcript_id']) && isset($execution['workflow_id'])) {
                    $transcriptData = $this->cron_model->getVideoTranscriptById($execution['transcript_id']);
                    if($transcriptData) {
                        if($transcriptData[0]['status'] === 'success' && isset($transcriptData[0]['video_id'])) {
                            $tid = $transcriptData[0]['_id']->{'$id'};
                            $wfobjid = $execution['_id']->{'$id'};
                            $workflowid = $execution['workflow_id'];
                            $request = $execution['request'];
                            $response = $execution['response'];
                            if ($transcriptData[0]['comment_ratio'] > 0 || $transcriptData[0]['question_ratio'] > 0 || $transcriptData[0]['opinion_ratio'] > 0) {
                                //cURL for get & insert executed comments from drafter
                                $wfexurl = 'https://api.drafter.ai/workflow-executions/?workflowId='.$workflowid.'&id='.$execution['execution_id'];

                                $key = $con->drafter_key;

                                $curlpenut = curl_init();
                                curl_setopt_array($curlpenut, array(
                                    CURLOPT_URL => $wfexurl,
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => '',
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 0,
                                    CURLOPT_FOLLOWLOCATION => true,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => 'GET',
                                    CURLOPT_HTTPHEADER => array(
                                        'X-Access-Key: '.$key
                                    ),
                                ));
                                $resmore = curl_exec($curlpenut);
                                curl_close($curlpenut);

                                $resGrapes = json_decode($resmore, true);

                                if(isset($resGrapes['error'])) {
                                    $errNews = $resGrapes['error']['message'];
                                    $ret = [
                                        'status'   => false,
                                        'messages' => $errNews
                                    ];
                                    $this->response($ret, REST_Controller::HTTP_OK);
                                } else if(!empty($resGrapes['data'])) {
                                    foreach($resGrapes['data'][0]['pipeline'] as $r) {
                                        $videoCommentData = array();
                                        $videoCommentData['comment_id'] = $this->cron_model->getCommentLastId();
                                        $videoCommentData['video_id'] = $transcriptData[0]['video_id'];
                                        $videoCommentData['comment'] = $workflowid === '110' ? $r['commentYoutube'] : ($workflowid === '82' ? $r['questionYoutube'] : $r['opinionYoutube']);
                                        $videoCommentData['created_date'] = date('Y/m/d H:i:s');
                                        $videoCommentData['modified_date'] = date('Y/m/d H:i:s');
                                        $videoCommentData['source'] = 'drafter';
                                        $videoCommentData['request'] = $request;
                                        $videoCommentData['response'] = $resGrapes['data'];
                                        if ($workflowid === '110') {
                                            $videoCommentData['comment_type'] = 'comment';
                                        } else if ($workflowid === '82') {
                                            $videoCommentData['comment_type'] = 'question';
                                        } else if ($workflowid === '142') {
                                            $videoCommentData['comment_type'] = 'opinion';
                                        }
                                        $sId = $this->cron_model->addvideoComment($videoCommentData);
                                        $this->cron_model->updateTranscriptCommentCounter($tid, $workflowid);
                                        echo $sId."\n";
                                    }
                                    $this->cron_model->updateWorkflowExStatus($wfobjid);
                                    $ret = [
                                        'status'   => true,
                                        'messages' => 'Comments added successfully'
                                    ];
                                    $this->response($ret, REST_Controller::HTTP_OK);
                                } else {
                                    $ret = [
                                        'status'   => false,
                                        'messages' => 'No comments found for add!!'
                                    ];
                                    $this->response($ret, REST_Controller::HTTP_OK);
                                }
                            } else {
                                $ret = [
                                    'status'   => false,
                                    'messages' => 'Maximum comments are added!!'
                                ];
                                $this->response($ret, REST_Controller::HTTP_OK);
                            }
                        } else {
                            $ret = [
                                'status'   => false,
                                'messages' => 'Transcripted video not found!'
                            ];
                            $this->response($ret, REST_Controller::HTTP_OK);
                        }
                    } else {
                        $ret = [
                            'status'   => false,
                            'messages' => 'Transcript data not found!'
                        ];
                        $this->response($ret, REST_Controller::HTTP_OK);
                    }
                } else {
                    $ret = [
                        'status'   => false,
                        'messages' => 'All execution completed!!'
                    ];
                    $this->response($ret, REST_Controller::HTTP_OK);
                }
            }
        } else {
            $ret = [
                'status'   => false,
                'messages' => 'Workflow execution data not found!'
            ];
            $this->response($ret, REST_Controller::HTTP_OK);
        }
    }
}