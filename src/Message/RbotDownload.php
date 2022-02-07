<?php

namespace App\Plugins\RbotAssets\src\Message;

use App\RBot\Annotation\RBotOnMessage;
use App\RBot\RBotMsg;
use Hyperf\Utils\Str;

class RbotDownload
{
	// send: Rbot#version_id
	#[RBotOnMessage(post_type:"message",message_type:"group")]
	public function group($_,RBotMsg $data){
		if($this->check($data)){
			$id = explode('#',$data->message())[1];
			try {
				$this->download($id,$data);
			}catch (\Exception $e){
				sendMsg([
					"group_id" => $data->data()->group_id,
					"message" => "获取失败,可能是以下原因:\n\n1.服务器网络故障\n\n2.版本id不存在"
				],"send_group_msg");
			}
		}
	}
	
	private function check(RBotMsg $data)
	{
		if(!Str::contains($data->message(), '#')) {
			return false;
		}
		$msg = explode('#', $data->message());
		if(count($msg) !== 3) {
			return false;
		}
		if(Str::lower($msg[0]) !== "rbot") {
			return false;
		}
		if(!is_numeric($msg[1])) {
			return false;
		}
		if($msg[2] !== "下载") {
			return false;
		}
		return true;
	}
	
	private function download(string $id,RBotMsg $RbotMsg)
	{
		$data = http()->get("https://github-api.inkedus.workers.dev/repos/zhuchunshu/Rbot/releases/".$id);
		$url = $data['tarball_url'];
		$result = sendData([
			"url" => $url,
		],'download_file');
		sendMsg([
			"group_id" => $RbotMsg->data()->group_id,
			"file" => $result['data']['file'],
			"name" => "RBOT_".$data['name'].".tar.gz"
		],"upload_group_file");
	}
}