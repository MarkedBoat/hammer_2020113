<?php

    /**
     * Created by PhpStorm.
     * User: markedboat
     * Date: 2018/7/20
     * Time: 11:01
     */

    namespace console\applet\notify;

    use modules\sl\v1\dao\group\GroupMemberDao;
    use modules\sl\v1\model\Client;
    use modules\sl\v1\model\User;
    use modules\sl\v1\model\wechat\applet\ApiRequest;

    class ClientOnline {
        ///sl_hammer system/queue call --env=prod queue=applet_notify_client_online  class=\\console\\applet\\notify\\ClientOnline --method=push --timeLimit=200

        public function push($rst) {
            //    $param    = new Params(json_decode($rst, true));
            $clientPk = $rst;

            $this->out("{$clientPk}");
            $client = Client::getByPk($clientPk);
            $userPk = intval($client->oper);

            $this->out("{$client->str_id},{$client->device_name}");
            $user      = User::getByPk($userPk);
            $userGroup = $user->getGroup();
            if ($userGroup->hasGroup) {
                $table   = $userGroup->getMemberRows();
                $table[] = ['member_type' => 1, 'is_member' => 1, 'member_pk' => $userGroup->groupId];
            } else {
                $table[] = ['member_type' => 1, 'is_member' => 1, 'member_pk' => $user->id];
            }

            foreach ($table as $row) {
                if (!(intval($row['member_type']) === GroupMemberDao::typeOper && intval($row['is_member']) === 1))
                    continue;
                $fromId = User::getFromId($row['member_pk']);
                if ($fromId !== false) {
                    try {
                        $user = User::getByPk($row['member_pk']);
                        ApiRequest::sendMessage(ApiRequest::getTvOnlineMsgParamByClient($client, $user->open_id, $fromId), $wechatResult);
                        $json = json_encode($wechatResult);
                        $this->out("{$fromId} {$json}", 3);
                    } catch (\Exception $e) {
                    }
                } else {
                    $this->out("pk:{$row['member_pk']} fromId:empty", 3);
                }
            }
            return 'ok';
        }

        public function out($str, $level = 2) {
            echo str_repeat(' ', $level * 10) . $str . "\n";
        }


    }