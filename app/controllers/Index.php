<?php

use Params\EC;
use Lib\TaobaoSms;
use Easemobile\Service;

class IndexController extends Base\Controller\Api
{
    static protected function loadPublicApi() 
    {
        return ['redisTest', 'index', 'smsTest', 'uploadTest', 'verifiyCode'];
    }

    public function indexAction()
	{
        $uid = 61;
		$userInfo = UserModel::getInstance()->userInfo($uid);
        $result = [];
        if (!empty($userInfo)) {
            $result = $userInfo;
        }
		return $this->output($result);
	}

	public function addOrderAction()
	{
		$result = array(
				'a' => '1',
				'c' => '2'
			);

		return $this->output($result);
	}

    public function redisTestAction()
    {
        $value = $this->jsonParam['v'];
        $redis = Factory::redis();
        $redis->setex('test', 3600, $value);
        $a = $redis->get('test');
        return $this->output(['a' => $a]);
    }

    public function smsTestAction()
    {
        $code = $this->jsonParam['code'];
        if (empty($code)) {
            return $this->err(EC::PARARM);
        }
        $res = Lib\LeanCloud::getInstance()->sms('17717563803');
        var_dump($res);
    }

    public function verifiyCodeAction()
    {
        $code = $this->jsonParam['code'];
        if (empty($code)) {
            return $this->err(EC::PARARM);
        }
        $res = Lib\LeanCloud::getInstance()->verifySmsCode('17717563803', $code);
        var_dump($res);
    }

    public function uploadTestAction()
    {
        if ($this->_request->isGet()) {
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>上传图片</title>
            </head>
            <body>
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="file" name="test">
                    <input type="submit" value="提交">
                </form>
            </body>
            </html>';
            return;
        }
        $res = \Lib\File::getInstance()->upload('test');
        var_dump($res);
    }

    //环信测试
    public function easemobAction()
    {
        $easemobile = Service::getInstance();
        $i = 1;
        $username = 'zhangsan1111';
        $password = md5($username);
        $username1 = 'lisi1111';
        $password1 = md5($username1);
        switch ($i) {
            case 1:
                //获取token；
                $res = $easemobile->easemobServ->getToken();
                break;
            
            case 2:
                //创建用户
                $res = $easemobile->easemobServ->createUser($username, $password);
                break;
            
            case 3:
                //重置密码
                $res = $easemobile->easemobServ->resetPassword($username, $password);
                break;
            
            case 4:
                //获取用户信息
                $res = $easemobile->easemobServ->getUser($username);
                break;
            
            case 5:
                //获取所有用户，不分页
                $res = $easemobile->easemobServ->getUsers();
                break;
            
            case 6:
                //获取用户，分页
                $res = $easemobile->easemobServ->getUsersForPage();
                break;
            
            case 7:
                //删除用户
                $res = $easemobile->easemobServ->deleteUser($username);
                break;
            
            case 8:
                //修改用户昵称
                $res = $easemobile->easemobServ->editNickname($username, $nickname);
                break;
            
            case 9:
                //添加好友
                $res = $easemobile->easemobServ->addFriend($username,$username1);
                break;
            
            case 10:
                //查看好友
                $res = $easemobile->easemobServ->showFriends($username);
                break;

            case 11:
                //删除好友
                $res = $easemobile->easemobServ->deleteFriend($username,$username1);
                break;
            
            case 12:
                //查看黑名单
                $res = $easemobile->easemobServ->getBlacklist($username);
                break;
            
            case 13:
                //添加黑名单
                $res = $easemobile->easemobServ->addUserForBlacklist($username, [$username1]);
                break;
            
            case 14:
                //去掉黑名单
                $res = $easemobile->easemobServ->deleteUserFromBlacklist($username, [$username1]);
                break;
            
            case 15:
                //查看用户是否在线
                $res = $easemobile->easemobServ->isOnline($username);
                break;
            
            case 16:
                //查看用户离线消息数
                $res = $easemobile->easemobServ->getOfflineMessages($username);
                break;
            
            case 17:
                //查看某条消息的离线状态？？？？
                $res = $easemobile->easemobServ->getOfflineMessageStatus();
                break;
            
            case 18:
                //冻结用户
                $res = $easemobile->easemobServ->deactiveUser($username);
                break;
            
            case 19:
                //激活用户
                $res = $easemobile->easemobServ->activeUser($username);
                break;
            
            case 20:
                //强制用户下线
                $res = $easemobile->easemobServ->disconnectUser($username);
                break;
            
            case 21:
                //上传图片或文件
                $res = $easemobile->easemobServ->uploadFile($filePath);
                break;
            
            case 22:
                //下载文件或图片
                $res = $easemobile->easemobServ->downloadFile($uuid,$shareSecret);
                break;
            
            case 23:
                //下载缩略图
                $res = $easemobile->easemobServ->downloadThumbnail($uuid,$shareSecret);
                break;
            
            case 24:
                //发送文本消息
                $res = $easemobile->easemobServ->sendText($from="admin",$target_type,$target,$content,$ext);
                break;
            
            case 25:
                //发送透传消息
                $res = $easemobile->easemobServ->sendCmd($from="admin",$target_type,$target,$action,$ext);
                break;
            
            case 26:
                //发送图片消息
                $res = $easemobile->easemobServ->sendImage($filePath,$from="admin",$target_type,$target,$filename,$ext);
                break;
            
            case 27:
                //强制用户下线
                $res = $easemobile->easemobServ->sendAudio($filePath,$from="admin",$target_type,$target,$filename,$length,$ext);
                break;
            
            case 28:
                //强制用户下线
                $res = $easemobile->easemobServ->sendVedio($filePath,$from="admin",$target_type,$target,$filename,$length,$thumb,$thumb_secret,$ext);
                break;
            
            case 29:
                //强制用户下线
                $res = $easemobile->easemobServ->sendFile($filePath,$from="admin",$target_type,$target,$filename,$length,$ext);
                break;
            
            // -------------------------群组操作
            case 30:
                //获取群组
                $res = $easemobile->easemobServ->getGroups();
                break;
            
            case 31:
                //获取群组带分页
                $res = $easemobile->easemobServ->getGroupsForPage();
                break;
            
            case 32:
                //获取一个或多个群组的详情
                $res = $easemobile->easemobServ->getGroupDetail($group_ids);
                break;
            
            case 33:
                //创建群组
                $res = $easemobile->easemobServ->createGroup($options);
                break;
            
            case 34:
                //修改群组
                $res = $easemobile->easemobServ->modifyGroupInfo($group_id,$options);
                break;
            
            case 35:
                //删除群组
                $res = $easemobile->easemobServ->deleteGroup($group_id);
                break;
            
            case 36:
                //获取群组中的成员
                $res = $easemobile->easemobServ->getGroupUsers($group_id);
                break;
            
            case 37:
                //群组中单个加人
                $res = $easemobile->easemobServ->addGroupMember($group_id,$username);
                break;
            
            case 38:
                //群组中批量加人
                $res = $easemobile->easemobServ->addGroupMembers($group_id,$usernames);
                break;
            
            case 39:
                //群组中单个减人
                $res = $easemobile->easemobServ->deleteGroupMember($group_id,$username);
                break;
            
            case 40:
                //群组中批量减人
                $res = $easemobile->easemobServ->deleteGroupMembers($group_id,$usernames);
                break;
            
            case 41:
                //获取一个用户的所有群组
                $res = $easemobile->easemobServ->getGroupsForUser($username);
                break;
            
            case 42:
                //转让群组
                $res = $easemobile->easemobServ->changeGroupOwner($group_id,$options);
                break;
            
            case 43:
                //查询一个群组黑名单用户列表
                $res = $easemobile->easemobServ->getGroupBlackList($group_id);
                break;
            
            case 44:
                //群组黑名单单个加人
                $res = $easemobile->easemobServ->addGroupBlackMember($group_id,$username);
                break;
            
            case 45:
                //群组批量添加黑名单
                $res = $easemobile->easemobServ->addGroupBlackMembers($group_id,$usernames);
                break;
            
            case 46:
                //群组黑名单单个减人
                $res = $easemobile->easemobServ->deleteGroupBlackMember($group_id,$username);
                break;
            
            case 47:
                //群组黑名单批量减人
                $res = $easemobile->easemobServ->deleteGroupBlackMembers($group_id,$usernames);
                break;
            
            case 48:
                //创建聊天室
                $res = $easemobile->easemobServ->createChatRoom($options);
                break;
            
            case 49:
                //修改聊天室
                $res = $easemobile->easemobServ->modifyChatRoom($chatroom_id,$options);
                break;
            
            case 50:
                //删除聊天室
                $res = $easemobile->easemobServ->deleteChatRoom($chatroom_id);
                break;
            
            case 51:
                //获取所有聊天室
                $res = $easemobile->easemobServ->getChatRooms();
                break;
            
            case 52:
                //获取聊天室的信息
                $res = $easemobile->easemobServ->getChatRoomDetail($chatroom_id);
                break;
            
            case 53:
                //获取一个用户加入的所有聊天室
                $res = $easemobile->easemobServ->getChatRoomJoined($username);
                break;
            
            case 54:
                //聊天室单个加人
                $res = $easemobile->easemobServ->addChatRoomMember($chatroom_id,$username);
                break;
            
            case 55:
                //聊天室批量成员添加
                $res = $easemobile->easemobServ->addChatRoomMembers($chatroom_id,$usernames);
                break;
            
            case 56:
                //聊天室单个减人
                $res = $easemobile->easemobServ->deleteChatRoomMember($chatroom_id,$username);
                break;
            
            case 57:
                //聊天室批量减人
                $res = $easemobile->easemobServ->deleteChatRoomMembers($chatroom_id,$usernames);
                break;
            
            default:
                # code...
                break;
        }
        var_dump($res);
    }
}