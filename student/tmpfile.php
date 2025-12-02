<?php
//error_reporting(E_ALL);   // デバッグ時
error_reporting(0);   // 運用時
session_start();

//linedatamouseへの書き込み
$FName2 = "linedatamouse";
$MemberID = $_SESSION["MemberID"];
$attempt = $_SESSION["attempt"];

// パラメータの受け取り（未定義の場合はNULLまたは空文字として処理）
$p9  = isset($_GET['param9'])  ? "'".$_GET['param9']."'"  : "NULL"; // register_stick
$p10 = isset($_GET['param10']) ? "'".$_GET['param10']."'" : "NULL"; // register_divide1
$p11 = isset($_GET['param11']) ? "'".$_GET['param11']."'" : "NULL"; // register_divide2
$p12 = isset($_GET['param12']) ? "'".$_GET['param12']."'" : "NULL"; // NOrder

// SQL文の構築
// 画像のDB構成に基づき、attemptの後ろに4つのカラムを追加します
$str = "INSERT INTO ".$FName2." VALUES(".$MemberID.",".$_GET['param1'].",".$_GET['param2'].",".$_GET['param3'].
	",".$_GET['param4'].",".$_GET['param5'].",".$_GET['param6'].",\"".$_GET['param7']."\",\"".
	$_GET['param8']."\",NULL,NULL,".$attempt.",".$p9.",".$p10.",".$p11.",".$p12.")";

//ファイル書き込みコード
$TempFileName = sys_get_temp_dir()."/tem".$MemberID.".tmp";
file_put_contents($TempFileName,$str."\n",FILE_APPEND);
echo file_get_contents($TempFileName);

?>