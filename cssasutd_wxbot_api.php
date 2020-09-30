<?php
require_once 'config.php'; //加载配置
require_once 'admin_tool.php'; //加载管理员工具
require_once 'tool.php'; //加载普通工具
require_once "LovelyCat.php"; //加载预制机器人库
//普通实例化
$cssasutd_wxbot = new lovelyCat( $config );
$cssasutd_wxbot->config = $config; //写入配置
$cssasutd_wxbot->ckey = $config[ 'ckey' ]; //设置密钥
$pass = false;
if ( $cssasutd_wxbot->config[ 'allowed_ip' ] == $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) { //检查ip合法性，如果不合法将拒绝连接
	$pass = true;
	$use_key = false;
} elseif ( $cssasutd_wxbot->config[ 'custom_key' ] == $_POST[ 'key' ] ) {
	$pass = true;
	$use_key = true;
}
if ( $pass ) {
	switch ( $cssasutd_wxbot->type ) { //检测事件
		case 100: //私聊消息
			switch ( true ) {
				case in_array( $cssasutd_wxbot->from_wxid, $cssasutd_wxbot->config[ 'admin' ] ): //如果来自管理员
					switch ( $cssasutd_wxbot->msg_type ) { //匹配已知消息类型
						case 1: //普通文字消息
							command( $cssasutd_wxbot );
							break;
						case 3: //图片消息
							$file_local = urlencode( $cssasutd_wxbot->msg );
							$cssasutd_wxbot->sendImageMsg( $file_local, $cssasutd_wxbot->robot_wxid, $cssasutd_wxbot->from_wxid );
							break;
						case 34: //语音消息
							$file = $cssasutd_wxbot->file_url;
							$file_link = str_replace( ':8073', 'wxbot.mxyr.tech:8073', $file );
							$cssasutd_wxbot->sendTextMsg( '这是一条语音消息，暂不支持转发。\n地址：\n' . $file_link );
							break;
						case 42: //名片消息
							$cssasutd_wxbot->sendTextMsg( '这是一个名片，暂不支持转发。' );
							break;
						case 43: //视频消息
							$file_local = urlencode( $cssasutd_wxbot->msg );
							$cssasutd_wxbot->sendVideoMsg( $file_local, $cssasutd_wxbot->robot_wxid, $cssasutd_wxbot->from_wxid );
							break;
						case 47: //动态表情消息
							$file = $cssasutd_wxbot->msg;
							$cssasutd_wxbot->sendTextMsg( '这是一条动态表情消息，暂不支持转发。\n地址：\n' . $file );
							break;
						case 49: //链接消息，无法转发，提供地址供查询
							$msg = $cssasutd_wxbot->msg;
							$pattern = $pattern = '/<url>(.*?)<\/\url>/';
							preg_match( $pattern, $msg, $match );
							$link = $match[ 1 ];
							$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . '发送了一条链接卡片，暂不支持转发卡片。\n链接地址：\n' . $link );
							$cssasutd_wxbot->sendTextMsg( '完整消息:\n' . $cssasutd_wxbot->msg );
							break;
						case 2001: //红包消息
							$cssasutd_wxbot->sendTextMsg( '这是一个红包，无法转发。' );
							break;
						case 2004: //文件消息
							$file_local = urlencode( $cssasutd_wxbot->msg );
							$cssasutd_wxbot->sendFileMsg( $file_local, $cssasutd_wxbot->robot_wxid, $cssasutd_wxbot->from_wxid );
							break;
						default: //如果不能匹配任何已知消息类型，报告错误
							$cssasutd_wxbot->sendTextMsg( '未定义的消息类型：msg_type=' . $cssasutd_wxbot->msg_type );
							$cssasutd_wxbot->sendTextMsg( '完整消息:\n' . 'type=' . $cssasutd_wxbot->type . '\nmsg_type=' . $cssasutd_wxbot->msg_type . '\nfrom_name=' . $cssasutd_wxbot->from_name . '\nfrom_wxid=' . $cssasutd_wxbot->from_wxid . '\nfinal_from_name=' . $cssasutd_wxbot->final_from_name . '\nfinal_from_wxid=' . $cssasutd_wxbot->final_from_wxid . '\nmsg=' . $cssasutd_wxbot->msg . '\nfile_url=' . $cssasutd_wxbot->file_url . '\n结束。' );
							break;
					}
					break;
				default: //如果私聊消息并非来自顶级管理员，忽略
					break;
			}
			break;
		case 200: //群聊消息
			switch ( true ) {
				case in_array( $cssasutd_wxbot->from_wxid, $cssasutd_wxbot->config[ 'group_main' ] ): //如果来自大群
					//通过检测消息来源确定转发目标
					$to_wxid_list = find_target( $cssasutd_wxbot->from_wxid, $cssasutd_wxbot->config[ 'group_main' ] );
					switch ( $cssasutd_wxbot->msg_type ) { //匹配已知消息类型
						case 1: //普通文字，直接转发
							$at_pattern = '/\[\@at,nickname=(.*?),/';
							$msg_pattern_qian = '/^(.*?)\[/';
							$msg_pattern_hou = '/\](.*?)$/';
							preg_match( $at_pattern, $cssasutd_wxbot->msg, $at_match );
							if ( $at_match == [] ) {
								foreach ( $to_wxid_list as $group_name => $to_wxid ) {
									$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . "：\n" . $cssasutd_wxbot->msg, $cssasutd_wxbot->robot_wxid, $to_wxid );
								}
							} else {
								preg_match( $msg_pattern_qian, $cssasutd_wxbot->msg, $msg_match_qian );
								preg_match( $msg_pattern_hou, $cssasutd_wxbot->msg, $msg_match_hou );
								foreach ( $to_wxid_list as $group_name => $to_wxid ) {
									$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . "：\n" . '@' . $at_match[ 1 ] . ' ' . $msg_match_qian[ 1 ] . $msg_match_hou[ 1 ], $cssasutd_wxbot->robot_wxid, $to_wxid );
								}
							}
							break;
						case 3: //图片，直接转发
							foreach ( $to_wxid_list as $group_name => $to_wxid ) {
								$file_local = urlencode( $cssasutd_wxbot->msg );
								$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . "发送了一张图片：", $cssasutd_wxbot->robot_wxid, $to_wxid );
								sleep( 1 );
								$cssasutd_wxbot->sendImageMsg( $file_local, $cssasutd_wxbot->robot_wxid, $to_wxid );
							}
							break;
						case 34: //语音消息，无法转发，提供.silk文件地址供查询
							foreach ( $to_wxid_list as $group_name => $to_wxid ) {
								$file = $cssasutd_wxbot->file_url;
								$file_link = str_replace( ':8073', 'wxbot.mxyr.tech:8073', $file );
								$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . '发送了一条语音消息，暂不支持转发。\n地址：\n' . $file_link, $cssasutd_wxbot->robot_wxid, $to_wxid );
							}
							break;
						case 42: //名片消息
							foreach ( $to_wxid_list as $group_name => $to_wxid ) {
								$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . '发送了一个名片，暂时无法转发。', $cssasutd_wxbot->robot_wxid, $to_wxid );
							}
							break;
						case 43: //视频消息，直接转发
							foreach ( $to_wxid_list as $group_name => $to_wxid ) {
								$file_local = urlencode( $cssasutd_wxbot->msg );
								$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . '发送了一个视频：', $cssasutd_wxbot->robot_wxid, $to_wxid );
							}
							sleep( 1 );
							$cssasutd_wxbot->sendVideoMsg( $file_local, $cssasutd_wxbot->robot_wxid, $to_wxid );
							break;
						case 47: //动态表情消息，无法转发，提供地址供查询
							foreach ( $to_wxid_list as $group_name => $to_wxid ) {
								$file_link = $cssasutd_wxbot->msg;
								$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . '发送了一条动态表情消息，暂不支持转发。\n地址：\n' . $file_link, $cssasutd_wxbot->robot_wxid, $to_wxid );
							}
							break;
						case 49: //链接消息，无法转发，提供地址供查询
							foreach ( $to_wxid_list as $group_name => $to_wxid ) {
								$msg = $cssasutd_wxbot->msg;
								$pattern = '/<url>(.*?)<\/\url>/';
								preg_match( $pattern, $msg, $match );
								$link = $match[ 1 ];
								$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . '发送了一条链接卡片，暂不支持转发卡片。\n链接地址：\n' . $link, $cssasutd_wxbot->robot_wxid, $to_wxid );
							}
							break;
						case 2001: //红包消息
							foreach ( $to_wxid_list as $group_name => $to_wxid ) {
								$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . '有人（暂时无法解析）发了一个红包，无法转发。', $cssasutd_wxbot->robot_wxid, $to_wxid );
							}
							break;
						case 2004: //文件消息
							foreach ( $to_wxid_list as $group_name => $to_wxid ) {
								$file_local = urlencode( $cssasutd_wxbot->msg );
								$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . '发送了一个文件：', $cssasutd_wxbot->robot_wxid, $to_wxid );
								sleep( 1 );
								$cssasutd_wxbot->sendFileMsg( $file_local, $cssasutd_wxbot->robot_wxid, $to_wxid );
							}
							break;
						case 10000: //系统消息，如果未定义则通知管理员
							notify_error( $cssasutd_wxbot, '未定义的系统消息，详情如下：\n' );
							break;
						case 10002: //群管理消息，机器人不做出任何反应
							break;
						default: //如果不能匹配任何已知消息类型，报告错误
							foreach ( $to_wxid_list as $group_name => $to_wxid ) {
								$cssasutd_wxbot->sendTextMsg( $cssasutd_wxbot->final_from_name . '发送了一条消息，但是该消息类型无法解析，非常抱歉！', $cssasutd_wxbot->robot_wxid, $to_wxid );
							} //提示遇到错误
							notify_error( $cssasutd_wxbot, $cssasutd_wxbot->final_from_name . '在' . $cssasutd_wxbot->from_name . '里发送了一条消息，但是该消息类型无法解析，详细信息如下：\n' ); //通知管理员
							break;
					}
					break;
				case in_array( $cssasutd_wxbot->from_wxid, $cssasutd_wxbot->config[ 'group_admin' ] ): //如果消息来自管理群
					command( $cssasutd_wxbot );
					break;
				default: //如果不来自任何已知群，忽视
					break;
			}
			break;
		case 400: //新人入群
			//{
			//	"group_wxid": "xxx@chatroom",
			//	"group_name": "xxx",
			//	"guest": [{
			//		"wxid": "xxx",
			//		"nickname": "xxx"
			//	}],
			//	"inviter": {
			//		"wxid": "xxx",
			//		"nickname": "xxx"
			//	}
			//}
			switch ( true ) {
				case in_array( $cssasutd_wxbot->from_wxid, $cssasutd_wxbot->config[ 'group_main' ] ): //如果来自大群
					$group_wxid_list = find_target( $cssasutd_wxbot->from_wxid, $cssasutd_wxbot->config[ 'group_main' ] ); //通过检测消息来源确定转发目标
					$origin = json_decode( $cssasutd_wxbot->msg, true );
					//$msg = sprintf( "%s拉了新人入群，撒花欢迎：%s！！", $origin[ 'inviter' ][ 'nickname' ], $origin[ 'guest' ][ 0 ][ 'nickname' ] );
					$welcome = sprintf( "欢迎%s加入SUTD中国学生学者之家，\n请阅读群公告并及时修改昵称，感谢配合！", $origin[ 'guest' ][ 0 ][ 'nickname' ] );
					$cssasutd_wxbot->sendTextMsg( $welcome, $cssasutd_wxbot->robot_wxid, $cssasutd_wxbot->from_wxid );
					$welcome_overall = sprintf( "欢迎%s加入SUTD中国学生学者之家", $origin[ 'guest' ][ 0 ][ 'nickname' ] );
					foreach ( $to_wxid_list as $group_name => $to_wxid ) {
						sleep( 1 );
						$cssasutd_wxbot->sendTextMsg( $welcome_overall, $cssasutd_wxbot->robot_wxid, $to_wxid );
					}
					break;
				default: //如果不来自任何已知群，忽视
					break;
			}
			break;
		case 410: //有人退群
			//msg 消息体：
			//{
			//	"member_wxid": "xxx",
			//	"member_nickname": "xxx",
			//	"group_wxid": "11111@chatroom",
			//	"group_name": "xxx",
			//	"timestamp": 1575890752
			//}
			$origin = json_decode( $cssasutd_wxbot->msg, true );
			break;
		case 500: //收到好友请求
			//$cssasutd_wxbot->agreeFriendVerify();
			break;
		case 600: //收到二维码转账
			//{
			//	"to_wxid": "wxid_9c6d4r3taosh22",
			//	"msgid": 1705897420,
			//	"received_money_index": "1",
			//	"money": "0.01",
			//	"total_money": "0.01",
			//	"remark": "",
			//	"scene_desc": "个人收款完成",
			//	"scene": 3,
			//	"timestamp": 1575891497
			//}
			$origin = json_decode( $cssasutd_wxbot->msg, true );
			//此处可以执行日志记录知道的操作
			break;
		case 700: //收到转账
			//{
			//	"paysubtype": "3",
			//	"is_arrived": 1,
			//	"is_received": 1,
			//	"receiver_pay_id": "1000050101201912090003409856290",
			//	"payer_pay_id": "100005010119120900084341523899983197",
			//	"money": "0.01",
			//	"remark": "",
			//	"robot_pay_id": "1000050101201912090003409856290",
			//	"pay_id": "100005010119120900084341523899983197",
			//	"update_msg": "receiver_pay_id、payer_pay_id属性为robot_pay_id、pay_id的新名字，内容是一样的，建议更换"
			//}
			$origin = json_decode( $cssasutd_wxbot->msg, true );
			break;
		case 900: //登陆成功
			break;
		default: //未定义事件类型
			notify_error( $cssasutd_wxbot, '未知事件类型，详细信息如下：\n' );
			break;
	}
} else {
	echo '别想了，你搞不进来的。。。';
}