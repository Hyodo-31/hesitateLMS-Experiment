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

$p9 = getParam('param9');  // register_stick
$p10 = getParam('param10'); // register_stick_count

// ★新規追加 (param11, 12, 13)
$p11 = getParam('param11'); // stick_now
$p12 = getParam('param12'); // stick_number1
$p13 = getParam('param13'); // stick_number2

// ★シフトした既存項目 (param14 ～ 18)
$p14 = getParam('param14'); // repel (旧param11)
$p15 = getParam('param15'); // repel_count (旧param12)
$p16 = getParam('param16'); // back (旧param13)
$p17 = getParam('param17'); // back_count (旧param14)
$p18 = getParam('param18'); // NOrder (旧param15)

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
	// ▼▼▼ ここに新規追加分を挿入 ▼▼▼
	. $p11 . "," // stick_now
	. $p12 . "," // stick_number1
	. $p13 . "," // stick_number2
    // ▲▲▲ 追加ここまで ▲▲▲
	. $p14 . "," // repel
	. $p15 . "," // repel_count
	. $p16 . "," // back
	. $p17 . "," // back_count
	. $p18       // NOrder
	. ")";

//ファイル書き込みコード
$TempFileName = sys_get_temp_dir() . "/tem" . $MemberID . ".tmp";
file_put_contents($TempFileName, $str . "\n", FILE_APPEND | LOCK_EX);
echo file_get_contents($TempFileName);

?>