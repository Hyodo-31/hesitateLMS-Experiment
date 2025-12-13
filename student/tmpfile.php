<?php
//ログイン関連
//error_reporting(E_ALL);   // デバッグ時
error_reporting(0);   // 運用時
session_start();

//linedatamouseへの書き込み
$FName2 = "linedatamouse";
$MemberID = $_SESSION["MemberID"];
$attempt = $_SESSION["attempt"];

// ▼▼▼ ヘルパー関数 ▼▼▼
function getParam($key)
{
	if (isset($_GET[$key]) && $_GET[$key] !== "") {
		return "'" . $_GET[$key] . "'";
	} else {
		return "NULL";
	}
}

// ▼▼▼ パラメータ取得 ▼▼▼
// 既存の stick 関連 (param9 - 13)
$p9 = getParam('param9');   // register_stick
$p10 = getParam('param10'); // register_stick_count
$p11 = getParam('param11'); // stick_now
$p12 = getParam('param12'); // stick_number1
$p13 = getParam('param13'); // stick_number2

// ★新規追加 (param14 - 16)
$p14 = getParam('param14'); // stick_number_same (構成が変わらず戻された場合)
$p15 = getParam('param15'); // stick_composition_count (構成単語数)
$p16 = getParam('param16'); // word_now (単語単体の個数)

// ★シフトした既存項目 (param17 - 21)
$p17 = getParam('param17'); // repel (旧param14)
$p18 = getParam('param18'); // repel_count (旧param15)
$p19 = getParam('param19'); // back (旧param16)
$p20 = getParam('param20'); // back_count (旧param17)
$p21 = getParam('param21'); // NOrder (旧param18)

$p22 = getParam('param22'); // left_groupword_X
$p23 = getParam('param23'); // right_groupword_X
$p24 = getParam('param24'); // groupword_Y

$p25 = getParam('param25'); // incorrect_stick
$p26 = getParam('param26');

// SQL文の構築
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
	. $p11 . "," // stick_now
	. $p12 . "," // stick_number1
	. $p13 . "," // stick_number2
	// ▼▼▼ 新規追加 ▼▼▼
	. $p14 . "," // stick_number_same
	. $p15 . "," // stick_composition_count
	. $p16 . "," // word_now
	// ▼▼▼ シフト分 ▼▼▼
	. $p17 . "," // repel
	. $p18 . "," // repel_count
	. $p19 . "," // back
	. $p20 . "," // back_count
	. $p21 . "," // NOrder
	. $p22 . "," // left_groupword_X
	. $p23 . "," // right_groupword_X
	. $p24 . "," // groupword_Y
	. $p25 . "," // incorrect_stick
	. $p26       // incorrect_stick_now
	. ")";

//ファイル書き込みコード
$TempFileName = sys_get_temp_dir() . "/tem" . $MemberID . ".tmp";
file_put_contents($TempFileName, $str . "\n", FILE_APPEND | LOCK_EX);
echo file_get_contents($TempFileName);
?>