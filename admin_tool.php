<?php

function command( $bot ) { //管理员命令解析器
	$deco_msg = json_decode( $bot->msg ); //尝试解读json
	if ( $deco_msg == null ) { //非json格式：短命令或者普通消息
		switch ( $bot->msg ) { //检测消息内容，与已定义短命令相匹配
			case '群列表': //查询群列表指令
				$reply = $bot->getGroupList( $bot->robot_wxid, 1 );
				$gp_list_array = json_decode( $reply );
				$gp_list_msg = '';
				foreach ( $gp_list_array as $k => $gp_info ) {
					$gp_index = $k + 1;
					$gp_list_msg .= $gp_index . '.' . $gp_info->nickname . ':\n' . $gp_info->wxid . '\n';
				}
				$bot->sendTextMsg( $gp_list_msg );
				break;
			case '微信id':
				$bot->sendTextMsg( '我的微信id为：\n' . $bot->robot_wxid );
				break;
			case '通信测试':
				$bot->sendTextMsg( '通信测试成功！' );
				break;
			default: //如果没有匹配，不进行任何操作
				break;
		}
	} else { //json格式命令
		switch ( $deco_msg->命令 ) {
			case '成员列表':
				//首先验证命令合法性
				$gp_list = $bot->getGroupList( $bot->robot_wxid, 1 );
				$gp_list_array = json_decode( $gp_list );
				$gp_array = [];
				foreach ( $gp_list_array as $k => $gp_info ) {
					$gp_array[] = $gp_info->wxid;
				}
				if ( in_array( $deco_msg->参数, $gp_array ) ) { //如果命令合法
					$reply = $bot->getGroupMemberList( $bot->robot_wxid, $deco_msg->参数, 1 );
					$gpmem_list_msg = '';
					foreach ( $reply as $mem_index => $mem_info ) {
						$mem_index += 1;
						$gpmem_list_msg .= $mem_index . '. ' . $mem_info[ 'nickname' ] . '：' . $mem_info[ 'wxid' ] . '\n';
						//						$mem_id = $mem_info[ 'wxid' ];
						//						$personal_info = $bot->getGroupMember( $bot->robot_wxid, $deco_msg->参数, $mem_id );
						//						var_dump( $personal_info );
					}
					$bot->sendTextMsg( $gpmem_list_msg );
				} else {
					$bot->sendTextMsg( '参数格式不合法！' );
				}
				break;
			case '公告':
				//首先验证命令合法性
				$gp_list = $bot->getGroupList( $bot->robot_wxid, 1 );
				$gp_list_array = json_decode( $gp_list );
				$gp_array = [];
				foreach ( $gp_list_array as $k => $gp_info ) {
					$gp_array[] = $gp_info->wxid;
				}
				if ( $deco_msg->参数 != null and is_string( $deco_msg->参数 ) ) {
					foreach ( $bot->config[ 'group_main' ] as $name => $qun_id ) {
						sleep( 1 );
						if ( in_array( $qun_id, $gp_array ) ) {
							$bot->setGroupNotice( $bot->robot_wxid, $qun_id, $deco_msg->参数 );
						} else {
							$bot->sendTextMsg( $qun_id . '不在群聊列表，设置公告失败！' );
						}
					}
					sleep( 1 );
					$bot->sendTextMsg( '设置公告成功！' );
				} else {
					$bot->sendTextMsg( '参数格式不合法！' );
				}
				break;
			default:
				$bot->sendTextMsg( '无法识别命令！' );
				break;
		}
	}

}

function notify_error( $bot, $msg ) {
	foreach ( $bot->config[ 'admin' ] as $name => $id ) {
		sleep( 1 );
		$bot->sendTextMsg( $msg . 'type=' . $bot->type . '\nmsg_type=' . $bot->msg_type . '\nfrom_name=' . $bot->from_name . '\nfrom_wxid=' . $bot->from_wxid . '\nfinal_from_name=' . $bot->final_from_name . '\nfinal_from_wxid=' . $bot->final_from_wxid . '\nmsg=' . $bot->msg . '\nfile_url=' . $bot->file_url . '\n结束。', $bot->robot_wxid, $id );
	}

}