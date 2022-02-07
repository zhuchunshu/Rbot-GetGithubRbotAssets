<?php

namespace App\Plugins\RbotAssets\src\Message;

use App\RBot\Annotation\RBotOnMessage;
use App\RBot\RBotMsg;
use Hyperf\Utils\Str;

class RbotAssets
{
    // group send: rbot assets
    #[RBotOnMessage(post_type:"message",message_type:"group")]
    public function group($_,RBotMsg $data){

        if($this->check($data)){
            $content = $this->getContent();
            sendMsg([
                "group_id" => $data->data()->group_id,
                "message" => $content
            ],"send_group_msg");
        }

    }

    public function check($data): bool
    {
        $message = Str::lower($data->message());

        return $message === "rbot assets";
    }

    private function getContent(): string
    {
        $content = "Rbot Assets \n\n";
        $data = http()->get("https://github-api.inkedus.workers.dev/repos/zhuchunshu/Rbot/releases");
        $version = '';
        foreach ($data as $value){
            $prerelease = $value['prerelease'];
            $text = '';
            if($prerelease){
                $text.=$value['id'].":[预发布]";
            }
            $text.=$value['name']."\n";
            $text.="     发布时间:".date("Y年m月d日H时i分",strtotime($value['published_at']));
            $version.=$text."\n\n";
        }
        $content.=$version;
        $content.="\n";
        $content.="发送:Rbot#版本id 获取详细信息\n\n例如:Rbot#58801193";

        return $content;
    }


}