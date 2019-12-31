<?php
session_start();
error_reporting(0);
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
$ServiceName = "KeJa Networks";
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $ServiceName;  ?></title>
<link rel="stylesheet" href="../bootstrap.min.css">
</head>
<body>
<div class="jumbotron">
<div style="text-align: center"><h1><?php echo $ServiceName; ?></h1></div>
<?php
include_once("../telemetry_settings.php");
require "../idObfuscation.php";
if($stats_password=="PASSWORD"){
	?>
		Please set $stats_password in telemetry_settings.php to enable access.
	<?php
}else if($_SESSION["logged"]===true){
	if($_GET["op"]=="logout"){
		$_SESSION["logged"]=false;
		?><script type="text/javascript">window.location=location.protocol+"//"+location.host+location.pathname;</script><?php
	}else{
		$conn=null;
		if($db_type=="mysql"){
			$conn = new mysqli($MySql_hostname, $MySql_username, $MySql_password, $MySql_databasename);
		}else if($db_type=="sqlite"){
			$conn = new PDO("sqlite:$Sqlite_db_file");
		} else if($db_type=="postgresql"){
			$conn_host = "host=$PostgreSql_hostname";
			$conn_db = "dbname=$PostgreSql_databasename";
			$conn_user = "user=$PostgreSql_username";
			$conn_password = "password=$PostgreSql_password";
			$conn = new PDO("pgsql:$conn_host;$conn_db;$conn_user;$conn_password");
		}else die();
?>
	<form action="" method="GET"><input type="hidden" name="op" value="logout" /><input class="btn btn-dark" style="position: absolute; top: 20px; right: 20px;" type="submit" value="Logout" /></form>
	<form action="" method="GET">
		<h3>Search test results</h6>
		<div class="btn-group">
		<input type="hidden" name="op" value="id" />
		<input class="input-group"  type="text" name="id" id="id" placeholder="Test ID or IP" value=""/>
		<input class="btn btn-dark" type="submit" value="Find" />
		<input class="btn btn-dark" type="submit" onclick="document.getElementById('id').value=''" value="Show last 100 tests" />
		</div>
	</form>
</div>
	<table class="table">
                <thead>
                        <tr>
                                <th scope="col">Test ID</th>
                                <th scope="col">Date</th>
                                <th scope="col">IP/ISP</th>
                                <th scope="col">User Agent</th>
                                <th scope="col">Down Speed</th>
                                <th scope="col">Up Speed</th>
                                <th scope="col">Ping</th>
                                <th scope="col">Jitter</th>
                                <th scope="col">Log</th>
                                <th scope="col">Extra</th>
                        </tr>
                </thead>
	<?php
		$q=null;
		if($_GET["op"]=="id"&&!empty($_GET["id"])){
			$id=$_GET["id"];
			$ip=strval($id);
			if (strpos($id, ".") !== false) {
				$id="a";
			}
			if($enable_id_obfuscation) $id=deobfuscateId($id);
			if($db_type=="mysql"){
				$q=$conn->prepare("select id,timestamp,ip,ispinfo,ua,lang,dl,ul,ping,jitter,log,extra from speedtest_users where id=? or ip =?");
				$q->bind_param("is",$id,$ip);
				$q->execute();
				$q->store_result();
				$q->bind_result($id,$timestamp,$ip,$ispinfo,$ua,$lang,$dl,$ul,$ping,$jitter,$log,$extra);
			} else if($db_type=="sqlite"||$db_type=="postgresql"){
				$q=$conn->prepare("select id,timestamp,ip,ispinfo,ua,lang,dl,ul,ping,jitter,log,extra from speedtest_users where id=?");
				$q->execute(array($id));
			} else die();
		}else{
			if($db_type=="mysql"){
				$q=$conn->prepare("select id,timestamp,ip,ispinfo,ua,lang,dl,ul,ping,jitter,log,extra from speedtest_users order by timestamp desc limit 0,100");
				$q->execute();
				$q->store_result();
				$q->bind_result($id,$timestamp,$ip,$ispinfo,$ua,$lang,$dl,$ul,$ping,$jitter,$log,$extra);
			} else if($db_type=="sqlite"||$db_type=="postgresql"){
				$q=$conn->prepare("select id,timestamp,ip,ispinfo,ua,lang,dl,ul,ping,jitter,log,extra from speedtest_users order by timestamp desc limit 0,100");
				$q->execute();
			}else die();
		}
		while(true){
			$id=null; $timestamp=null; $ip=null; $ispinfo=null; $ua=null; $lang=null; $dl=null; $ul=null; $ping=null; $jitter=null; $log=null; $extra=null;
			if($db_type=="mysql"){
				if(!$q->fetch()) break;
			} else if($db_type=="sqlite"||$db_type=="postgresql"){
				if(!($row=$q->fetch())) break;
				$id=$row["id"];
				$timestamp=$row["timestamp"];
				$ip=$row["ip"];
				$ispinfo=$row["ispinfo"];
				$ua=$row["ua"];
				$lang=$row["lang"];
				$dl=$row["dl"];
				$ul=$row["ul"];
				$ping=$row["ping"];
				$jitter=$row["jitter"];
				$log=$row["log"];
				$extra=$row["extra"];
			}else die();
	?>
			<tr>
			<td><?=htmlspecialchars(($enable_id_obfuscation?(obfuscateId($id)." (deobfuscated: ".$id.")"):$id), ENT_HTML5, 'UTF-8') ?></td>
			<td><?=htmlspecialchars($timestamp, ENT_HTML5, 'UTF-8') ?></td>
			<td><?=$ip ?><br/><?=htmlspecialchars($ispinfo, ENT_HTML5, 'UTF-8') ?></td>
			<td><?=$ua ?><br/><?=htmlspecialchars($lang, ENT_HTML5, 'UTF-8') ?></td>
			<td><?=htmlspecialchars($dl, ENT_HTML5, 'UTF-8') ?></td>
			<td><?=htmlspecialchars($ul, ENT_HTML5, 'UTF-8') ?></td>
			<td><?=htmlspecialchars($ping, ENT_HTML5, 'UTF-8') ?></td>
			<td><?=htmlspecialchars($jitter, ENT_HTML5, 'UTF-8') ?></td>
			<td><?=htmlspecialchars($log, ENT_HTML5, 'UTF-8') ?></td>
			<td><?=htmlspecialchars($extra, ENT_HTML5, 'UTF-8') ?></td>
			</tr>
	<?php
		}
	?>
			</table>
<?php
	}
}else{
	if($_GET["op"]=="login"&&$_POST["password"]===$stats_password){
		$_SESSION["logged"]=true;
		?><script type="text/javascript">window.location=location.protocol+"//"+location.host+location.pathname;</script><?php
	}else{
?>
	<form action="?op=login" method="POST">
		<h3>Login</h3>
		<input type="password" name="password" placeholder="Password" value=""/>
		<input type="submit" value="Login" />
	</form>
<?php
	}
}
?>
</body>
</html>
