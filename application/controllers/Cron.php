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
                    $this->cron_model->updateCommentCounter($yt_video_id);
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
                        $sId = $this->cron_model->addtranscriptDesc($transcriptData);
                        echo $sId."\n";
                    }
                }
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
                                $sId = $this->cron_model->addvideoComment($videoCommentData);
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
                'messages' => 'Server not found'
            ];
            $this->response($ret, REST_Controller::HTTP_OK);
        }
    }
}