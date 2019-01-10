<?php

    $method = strtolower($_SERVER['REQUEST_METHOD']);
    if ($method == 'get') {
        echo "Get没有实现";
        exit;
    }

    $body = file_get_contents("php://input");
    if (empty($body)) {
        exit(0);
    }
    if (strpos('crack', $body) !== false) {
        exit(0);
    }
    
    try {
        error_log($body);  //显示到日志，Windows IIS下可能有问题，可以自行去掉
    } catch (Exception $e) {
        //增加错误捕捉，避免Windows IIS下可能的问题
    }

    $body = json_decode($body, true);

    $action = $body['action'];

    if (!$action) {
        return_error_result(['error_code' => 1]);
    }
    $appid = $body['appid'];
    $wxid = $body['wxid'];
    $data = $body['data'];

    if (function_exists($action)) {
        try {
            call_user_func_array($action, [$appid, $wxid, $data]);
        } catch (Exception $e) {
            return_error_result(['error_reason' => $e->getMessage()]);
        }
    } else {
        return_error_result(['error_reason' => '接口未实现']);
    }

    function return_error_result($params=['error_reason' => '未知错误']) {
        $base_result = [
            'error_code' => 1,
            'error_reason' => $params['error_reason']
        ];
        echo json_encode(array_merge($base_result, $params));
        exit;
    }

    function return_success_result($params=[]) {
        $base_result = [
            'error_code' => 0,
            'error_reason' => ''
        ];
        echo json_encode(array_merge($base_result, $params));
        exit;
    }
    
    function login($app, $wxid, $data) {
        
        $params = [
            'ack_type' => 'login_ack',
            'data' => []
        ];
        if (isset($data['nonce'])) {
            $secretkey = '112233'; //需要修改成自己的secretkey，在登录网页获取
            $params['data']['signature'] = md5($wxid.'#'.$data['nonce'].'#'.$secretkey);
        }
        return_success_result($param);
    }

    function logout($app, $wxid, $data) {
        return_success_result();
    }

    function report_contact($app, $wxid, $data) {
        return_success_result();
    }

    /**
     * 聊天内容发送 hello，则会触发回复
     */
    function report_new_msg($app, $wxid, $data) {
        $message = $data['msg'];
        $room_wxid = isset($message['room_wxid']) ? $message['room_wxid'] : '';
        $sender_wxid = $message['wxid_from'];
        if ($wxid == $sender_wxid) {
            $reply = [];
        } else {
            if(empty($room_wxid)) {
                $reply = [
                    'task_type' => 1,
                    'task_dict' => [
                        'wxid_to'      => $sender_wxid,
                        'at_list'      => [],
                        'msg_list'     => [
                            [
                                'msg_type' => 1,
                                'msg' => "Hello, 这是来自Demo的回复",
                            ]
                        ]
                    ]
                ];
            } else {
                $reply = [
                    'task_type' => 1,
                    'task_dict' => [
                        'wxid_to'      => $room_wxid,
                        'at_list'      =>[$sender_wxid],
                        'msg_list'     => [
                            [
                                'msg_type' => 1,
                                'msg' => "Hello, 这是来自Demo的回复",
                            ]
                        ]
                    ]
                ];
            }
        }

        if (strtolower($message['msg']) !== 'hello' && strtolower($message['msg']) !== 'hi') {
            $reply = [];
        }
       
        return_success_result([
            'error_code'   => 0,
            'error_reason' => '',
            'ack_type'     => 'report_new_msg_ack',
            'data'         => [
                'reply_task_list' => [
                    $reply
                ]
            ]
        ]);
    }

    /**
     * "data" : {
     *    "fans_wxid": "wxid_ljsdlfjslfjl",        // 新好友的wxid
     *    "nickname": "Jerry",                    // 新好友的昵称
     *    "wx_alias": "jerry"                     // 新好友的微信号,可能为空
     *    "notice_word":  "xxxxxxx"                // 新好友加我时的打招呼的内容,可能为空
     *  }
     *
     */
    function report_new_friend($app, $wxid, $data) {
        exit; //测试的话，可以把这个删除
        return_success_result([
            'error_code'   => 0,
            'error_reason' => '',
            'ack_type'     => 'report_new_friend_ack',
            'data'         => [
                'reply_task_list' => [
                    [
                        'task_type' => 1,
                        'task_dict' => [
                            'wxid'      => $data['fans_wxid'],
                            'msg_list'       => [
                                [
                                    'msg_type' => 1,
                                    'msg' => "欢迎欢迎",
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ]);
    }

    function pull_task($app, $wxid, $data) {
        $resultTemplate = [
            'error_code'   => 1,
            'error_reason' => '',
            'ack_type'     => 'pull_task_ack',
            'data'         => []
        ];
        $dataList = [];
        $dataList[] = [
            'task_id' => '2',
            'task_data' => [
                'task_type' => 1,
                'task_dict' => [
                    'wxid_to' => 'fangqing_hust',
                    'msg_list' => [
                        [
                            'msg_type' => 1,
                            'msg' => "欢迎欢迎",
                        ],
                        [
                            'msg_type' => 1,
                            'msg' => "这是第二条信息",
                        ],
                    ]
                ]
            ]
        ];
        $dataList[] = [
            'task_id' => '3',
            'task_data' => [
                'task_type' => 2,
                'task_dict' => [
                    "room_wxid"=> "xxxxx@chatroom",
                    "wxid" => "xxxxxxx"
                ]
            ]
        ];
        $dataList[] = [
            'task_id' => '4',
            'task_data' => [
                "task_type" => 4,
                "task_dict" => [
                    "room_wxid_list" => ["xxxxx@chatroom","xxxxx2@chatroom"]  
                ]
            ]
        ];
        $dataList[] = [
            'task_id' => '5',
            'task_data' => [
                "task_type" => 5,
                "task_dict" => [
                    "room_wxid"=> "xxxxx@chatroom",
                    "wxid" => "xxxxxxx",
                    "msg" => "xxxxxx"
                ]
            ]
        ];
        $dataList[] = [
            'task_id' => '6',
            'task_data' => [
                "task_type" => 6,
                "task_dict" => [
                    "wxid" => "xxxxxxx",
                    "remark_name" => "xxxxxx"
                ]
            ]
        ];
        $dataList[] = [
            'task_id' => '7',
            'task_data' => [
                "task_type" => 7,
                "task_dict" => [
                    "room_wxid" => "xxxxxxx",
                    "room_nickname" => "xxxxxx"
                ]
            ]
        ];
        $dataList[] = [
            'task_id' => '8',
            'task_data' => [
                "task_type" => 8,
                "task_dict" => [
                    "room_wxid" => "xxxxxxx",
                ]
            ]
        ];
        
        
        $taskId = 5;//random_int(0, 6);
        
        $data = isset($dataList[$taskId]) ? $dataList[$taskId] : $dataList[0];

        //这里的demo数据都是非真实数据，只是展示结构
        $resultTemplate['error_code'] = 0;
        $resultTemplate['data']  = $data;
        return_success_result($resultTemplate);
    }

    function report_task_result($app, $wxid, $data) {
        $result = [
            'error_code'   => 0,
            'error_reason' => '',
            'ack_type'     => 'report_task_result_ack',
            'data'         => [
                'task_id'  => $data['task_id']
            ]
        ];
        return_success_result($result);
    }

    function rest_func($app, $wxid, $data) {
        
    }