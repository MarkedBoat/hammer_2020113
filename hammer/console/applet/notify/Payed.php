<?php

    /**
     * Created by PhpStorm.
     * User: markedboat
     * Date: 2018/7/20
     * Time: 11:01
     */

    namespace console\applet\notify;

    use models\common\param\Params;
    use modules\sl\v1\dao\group\GroupMemberDao;
    use modules\sl\v1\model\User;
    use modules\sl\v1\model\wechat\applet\ApiRequest;

    class Payed {

        public function push($rst) {
            $param     = new Params(json_decode($rst, true));
            $ndate     = date('Y-m-d H:i:s', $param->getIntNotNull('time'));
            $title     = $param->getStringNotNull('title');
            $userPk    = $param->getIntNotNull('userPk');
            $userGroup = User::getByPk($userPk)->getGroup();
            $table     = $userGroup->getMemberRows();
            $this->out("{$userPk},{$title},{$ndate}");
            // if ($userGroup->isHolder === false)
            $table[] = ['member_type' => 1, 'is_member' => 1, 'member_pk' => $userGroup->groupId];
            foreach ($table as $row) {
                if (intval($row['member_type']) === GroupMemberDao::typeClient)
                    continue;
                if (!(intval($row['is_member']) === 1 || intval($row['is_apply']) === 1))
                    continue;
                $fromId = User::getFromId($row['member_pk']);
                if ($fromId !== false) {
                    try {
                        $result = ApiRequest::sendMessage(ApiRequest::getPayedSuccess(User::getByPk($row['member_pk'])->open_id, $fromId, $title, $ndate), $wechatResult);
                        $json   = json_encode($wechatResult);
                        $this->out("pk:{$row['member_pk']} {$json}", 3);
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