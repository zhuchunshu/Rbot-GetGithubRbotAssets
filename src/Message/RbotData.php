<?php

namespace App\Plugins\RbotAssets\src\Message;

use App\RBot\Annotation\RBotOnMessage;
use App\RBot\RBotMsg;
use Hyperf\Utils\Str;

class RbotData
{
    // send: Rbot#version_id
    #[RBotOnMessage(post_type:"message",message_type:"group")]
    public function group($_,RBotMsg $data){
        if($this->check($data)){
            $id = explode('#',$data->message())[1];
            try {
                sendMsg([
                    "group_id" => $data->data()->group_id,
                    "message" => $this->getContent($id)
                ],"send_group_msg");
            }catch (\Exception $e){
                sendMsg([
                "group_id" => $data->data()->group_id,
                    "message" => "获取失败,可能是以下原因:\n\n1.服务器网络故障\n\n2.版本id不存在"
                ],"send_group_msg");
            }
        }
    }

    private function check(RBotMsg $data){
        if(!Str::contains($data->message(), '#')){
            return false;
        }
        $msg = explode('#',$data->message());
        if(count($msg)!==2){
            return false;
        }
        if(Str::lower($msg[0])!=="rbot"){
            return false;
        }
        if(!is_numeric($msg[1])){
            return false;
        }
        return true;
    }

    private function getContent(int $id):string
    {
        $data = http()->get("https://github-api.inkedus.workers.dev/repos/zhuchunshu/Rbot/releases/".$id);
        $content = '';
        $prerelease = $data['prerelease'];
        if($prerelease){
            $content .= "[预发布]:".$data['name'];
        }
        $content.="\n\n\n——资源链接:".$data['html_url'];
        $content.="\n\n——tar.gz包下载地址:".$data['tarball_url'];
        $content.="\n\n——zip包下载地址:".$data['zipball_url'];
		if($data['body']){
			$content.="\n\n\n------------------------------------\n\n";
			$content.=$data['body'];
			$content.="\n\n------------------------------------";
		}
        $content.="\n\n\n如需获取此源文件,请发送: \n\nRbot#".$id."#下载\n\n发送后会发送给你此源码的.tar.gz文件";
        return $content;
    }
}