<?php
//error_reporting(E_ALL);   // デバッグ時
error_reporting(0);   // 運用時
session_start();

//linedatamouseへの書き込み
$FName2 = "linedatamouse";
$MemberID = $_SESSION["MemberID"];
$attempt = $_SESSION["attempt"];

// ▼▼▼ 修正: 追加カラムの受け取り (param9 ～ param15) ▼▼▼
// 値が空文字の場合は "NULL" をSQLに入れる処理
function getParam($key)
{
	if (isset($_GET[$key]) && $_GET[$key] !== "") {
		return "'" . $_GET[$key] . "'";
	} else {
		return "NULL";
	}
}

$p9  = getParam('param9');  // register_stick
$p10 = getParam('param10'); // register_stick_count
$p11 = getParam('param11'); // repel
$p12 = getParam('param12'); // repel_count
$p13 = getParam('param13'); // back
$p14 = getParam('param14'); // back_count
$p15 = getParam('param15'); // NOrder

// SQL文の構築
// param1~8は既存通り、attemptの後ろに新しい7カラムを追加
$str = "INSERT INTO " . $FName2 . " VALUES("
	. $MemberID . ","
	. $_GET['param1'] . ","
	. $_GET['param2'] . ","
	. $_GET['param3'] . ","
	. $_GET['param4'] . ","
	. $_GET['param5'] . ","
	. $_GET['param6'] . ",\""
	. $_GET['param7'] . "\",\""
	. $_GET['param8'] . "\",NULL,NULL,"
	. $attempt . ","
	. $p9 . ","
	. $p10 . ","
	. $p11 . ","
	. $p12 . ","
	. $p13 . ","
	. $p14 . ","
	. $p15
	. ")";

//ファイル書き込みコード
$TempFileName = sys_get_temp_dir() . "/tem" . $MemberID . ".tmp";
file_put_contents($TempFileName, $str . "\n", FILE_APPEND | LOCK_EX);
echo file_get_contents($TempFileName);
