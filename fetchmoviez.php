<?php
//第一次使用请修改第90行参数
ini_set("max_execution_time", "999");
ini_set("memory_limit", "200M");

require_once("include/bittorrent.php");
dbconn();

$content = get_url("http://xtmhd.com/archiver/fid-150-page-1.html");//fid-150-page-1.html可修改为fid-150-page-2.html、fid-150-page-3.html等...每天采集则无需考虑
$matches = array();
preg_match_all("/<li><a href=\"(.*?)\">(.*?)<\\/a>/i", $content, $matches);
$arr_url = $matches[1];
$arr_title = $matches[2];

foreach ($arr_url as $key => $url)
{
	$title = $arr_title[$key];
	$url = "http://xtmhd.com/" . $url;
	$matches = array();
	$tid = preg_match("/tid-(\\d+)\\.html/i", $url, $matches);
	$tid = $matches[1];
	if (check_fetched($tid))
	{
		continue;
	}
	$content = get_url($url);
	$content = str_replace("\n", "", $content);
	$matches = array();
	$match_times = preg_match("/<\\/h3>(.*?)<p class=\"author\">/i", $content, $matches);
	if ($match_times == 1)
	{
		$content = $matches[1];
	}
	else
	{
		$match_times = preg_match("/<\\/h3>(.*?)<div class=\"page\">/i", $content, $matches);
		if ($match_times == 1)
		{
			$content = $matches[1];
		}
		else
		{
			$match_times = preg_match("/<\\/h3>(.*?)<div class=\"page\">/i", $content, $matches);
			if ($match_times == 1)
			{
				$content = $matches[1];
			}
			else
			{
				$content = '';
			}
		}
	}
	if ($content != '')		
	{
		$content = str_replace("<br />","\n",$content);
		$content = str_replace("<br>","\n",$content);
		$content = str_replace("<br/>","\n",$content);
		$content = str_replace("&nbsp;"," ",$content);
		$content = str_replace("&gt;",">",$content);
		$content = str_replace("&lt;","<",$content);
		insert($title, $tid, $content);
	}
	sleep(1);
}
sendto_forum();
function check_fetched($tid)
{

	$sql = "select 1 from moviez where tid='$tid' LIMIT 1";
	$res = sql_query($sql);
	$row = mysql_fetch_array($res);
	$count = $row[0];
	if ($count)
	{
		return true;
	}
	else
	{
		return false;
	}
}

function sendto_forum()
{
	$forumid=22;

	$sql  = "select * from moviez where sent=0";
	$res = sql_query($sql);
	while($row = mysql_fetch_array($res)) {
		$rows[] = $row;
	}
	
	foreach ($rows as $row) {
		$row[title] = preg_replace("/'/","\'",$row[title]);
		$row[content] = preg_replace("/'/","\'",$row[content]);

		sql_query("set names utf8") or die("character error!");
		$sql = "INSERT INTO topics (userid, forumid, subject) VALUES('43571', '22','$row[title]')";
		sql_query($sql) or die("Insert error!");
		
		$topicid = mysql_insert_id();
		
		$sql = "UPDATE forums SET topiccount=topiccount+1, postcount=postcount+1 WHERE id= ".$forumid;
		sql_query($sql);
		
		$sql = "INSERT INTO posts (topicid, userid,added, editdate, body, ori_body ) VALUES ('$topicid', '43571',now(),now(), '$row[content]', '$row[content]')";
		sql_query($sql) or die("TOPICID: ****".$topicid."*****");
		
		$postid = mysql_insert_id();
		
		$sql = "UPDATE topics SET firstpost=$postid, lastpost=$postid WHERE id=".sqlesc($topicid);
		sql_query($sql);
		
		$sql = "update moviez set sent='1',newtid='$tid' where tid='$row[tid]'";//成功后改回原状，设置每日定时任务即可
		sql_query($sql);
	}
}

function insert($title, $tid, $content)
{
	$title = mysql_escape_string($title);
	$content = mysql_escape_string($content);	
	$sql = "insert into moviez (tid,title,content,sent)values('$tid','$title','$content','0')";
	sql_query($sql);
}

/**
 * 获取url内容，curl方法 
 *
 * @param string  $url     请求url地址
 * @param integer $timeout 超时时间
 * @return string          请求结果
 */
function get_url($url, $timeout = 3)
{
	return file_get_contents($url);
/*
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$ret = curl_exec($ch);
	curl_close($ch);
	return $ret;
*/
}




?>
