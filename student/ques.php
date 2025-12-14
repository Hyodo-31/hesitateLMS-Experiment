<?php
//ログイン関連
error_reporting(E_ALL);
//session_start();
include '../lang.php';
if (!isset($_SESSION["MemberName"])) {
    require "notlogin";
    //session_destroy();
    exit;
}

//if ($_SESSION["examflag"] == 1) {
//require "overlap.php";
//exit;
//} else {
$_SESSION["examflag"] = 2;
$_SESSION["page"] = "ques";
//}
?>
<!DOCTYPE html PUBLIC "-//W3c//DTD HTML 4.01 Transitional//EN">
<html lang="<?= $lang ?>">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?= translate('ques.php_22行目_並べ替え問題プログラム') ?></title>
    <link rel="stylesheet" href="../style/StyleSheet.css" type="text/css" />

    <script type="text/javascript" src="jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="yui/build/yahoo/yahoo-min.js"></script>
    <script type="text/javascript" src="yui/build/event/event-min.js"></script>
    <script type="text/javascript" src="yui/build/dom/dom-min.js"></script>
    <script type="text/javascript" src="prototype.js"></script>
    <script type="text/javascript" src="dateformat.js"></script>
    <script type="text/javascript" src="wz_jsgraphics.js"></script>
    <script type="text/javascript" src="yui/build/dragdrop/dragdrop-min.js"></script>
    <script type="text/javascript" src="yui/build/animation/animation-min.js"></script>
</head>

<script type="text/javascript">
    // ======================= ▼▼▼ ここから追加 ▼▼▼ =======================
    var currentLang = "<?php echo $lang; ?>";
    // ======================= ▲▲▲ ここまで追加 ▲▲▲ =======================
    //ストップウォッチ関数
    myButton = 0; // [Start]/[Stop]のフラグ
    var myStart;
    var myStop;

    function myCheck(myFlg) {
        if (myButton == 0) { // Startボタンを押した
            myStart = new Date(); // スタート時間を退避
            myButton = 1;
            myInterval = setInterval("myCheck(1)", 1);
        } else { // スタート実行中
            if (myFlg == 0) { // Stopボタンを押した
                myButton = 0;
                clearInterval(myInterval);
            }
            myStop = new Date(); // 経過時間を退避
            myTime = myStop.getTime() - myStart.getTime(); // 通算ミリ秒計算
            myS = Math.floor(myTime / 1000); // '秒'取得
            myMS = myTime % 1000; // 'ミリ秒'取得
            document.getElementById("TextBox1").innerHTML = myS + "." + myMS + <?= json_encode(translate('ques.php_82行目_秒')) ?>;
        }
    }
    //ストップウォッチ関数2(全体用)
    myButton2 = 0; // [Start]/[Stop]のフラグ
    var myStart2;
    var myStop2;

    function myCheck2(myFlg2) {
        if (myButton2 == 0) { // Startボタンを押した
            myStart2 = new Date(); // スタート時間を退避
            myButton2 = 1;
            myInterval2 = setInterval("myCheck2(1)", 1);
        } else { // スタート実行中
            if (myFlg2 == 0) { // Stopボタンを押した
                myButton2 = 0;
                clearInterval(myInterval2);
            }
            myStop2 = new Date(); // 経過時間を退避
            myTime2 = myStop2.getTime() - myStart2.getTime(); // 通算ミリ秒計算
            myS2 = Math.floor(myTime2 / 1000); // '秒'取得
            myMS2 = myTime2 % 1000; // 'ミリ秒'取得
        }
    }
    //＃構造体の宣言
    var Mouse = new Object();
    Mouse["Time"] = 0;
    Mouse["X"] = 0;
    Mouse["Y"] = 0;
    Mouse["DragDrop"] = 0; //ドラッグ中か（0:MouseMove,1:MouseDown,2:MouseUp)
    Mouse["DropPos"] = 0; //どこドロップされたか(0:元,1:レジスタ1,2:レジスタ2,3:レジスタ3)
    Mouse["hlabel"] = ""; //ドラッグしているラベル（マウスが当たっているラベル）
    Mouse["Label"] = ""; //どのラベルが対象か（複数ラベル)
    Mouse["WID"] = 0;
    //-------------------------
    var AnswerData = new Object();
    AnswerData["WID"] = 0; //問題番号
    AnswerData["Date"] = new Date; //解答日時
    AnswerData["TF"] = 0; //正誤
    AnswerData["Time"] = 0; //解答時間
    AnswerData["Understand"] = 0;
    AnswerData["EndSentence"] = "";
    AnswerData["hesitate"] = "";
    AnswerData["hesitate1"] = "";
    AnswerData["hesitate2"] = "";
    AnswerData["comments"] = "";
    AnswerData["check"] = 0;
    $countHearing = [];
    $s = 0;

    //--------------------------
    p = new Array();
    Mouse_Flag = new Boolean(false); //マウスの軌跡を保存するかどうか
    IsDragging = new Boolean(false); //ドラッグ中の場合true
    function Point(_x, _y) {
        this.x = _x;
        this.y = _y;
    }
    //使う例 print(DiffPoint.x);
    var DiffPoint = new Point(0, 0); //ドラッグ開始地点とドラッグ開始時のボタンの位置とのずれ
    var DLabel = "";
    var x = 0; //挿入線を描画する位置
    var y1 = 0;
    var y2 = 0;
    //frag = 0;
    var Mylabels = new Array(); //並び替えラベルの元
    var MylabelsD = new Array(); //divideyou
    var Mylabels_r1 = new Array(); //レジスタ用
    var Mylabels_r2 = new Array();
    var Mylabels_r3 = new Array();
    var Mylabels_ea = new Array(); //最終解答欄用
    var MyLabels_h = new Array(); //ヒアリング機能用
    var MyNums = new Array(); //番号リスト
    var DefaultX = 30; //ラベルの初期値
    var DefaultY = 100;
    var DefaultX_r1 = 30; //ラベルの初期値
    var DefaultY_r1 = 310;
    var DefaultX_r2 = 30; //ラベルの初期値
    var DefaultY_r2 = 390;
    var DefaultX_r3 = 30; //ラベルの初期値
    var DefaultY_r3 = 470;
    var DefaultX_ea = 30; //ラベルの初期値
    var DefaultY_ea = 170;
    var sPos = new Point(0, 0);
    var ePos = new Point(0, 0);
    var PorQ; //文末の.または?を格納するよう
    var Answer; //正解
    var Question; //問題文(先頭小文字、文末ぬき）
    var str1; //Answerの補助
    var str2;
    var LabelNum; //ラベルの数
    var Answer; //正解
    var Answer1; //別解1
    var Answer2; //別解2
    var linedataFlg = false; //linedataに書き込み中
    var Answertime = new Date; //解答日時(datatime?)
    var $Mouse_Data = Mouse; //マウスの軌跡情報を保持
    var Mouse_Num; //マウスの軌跡情報の数
    var StartQues = ""; //始めの問題の状態
    var MyAnswer = ""; //自分の答え
    var WriteAnswer = ""; //自分の答え保存用
    var $QAData = AnswerData; //問題データ保存用
    var MyControls = new Array(); //グループ化ラベルをまとめた配列
    var AllCorrectAns = 0; //全体の正解数
    var ResAns = 0;
    var AllResAns = 0; //全体の解答数
    var OID = 0; //解答番号、linedataとlinedatamouseを関連付けるキー
    var WID = 0;
    var checkl = 0; //phpオリジナル、重さをなくすため
    var cx = 0; //キャンバスのギャップの修正用
    var cy = 0;
    var MV = new Boolean(false); //グループ化のためのドラッグ中か
    var loc = -1; //グループ化の線の位置　0:左上 1:左下 2:右上 3:右下
    var PreMouseTime = -1; //前回のマウス取得時間（※新しい問題が出るたびに初期化させている）
    var dd = new Array(); //ドラッグドロップ変数
    var $AccessDate; //ログイン日時
    Mld = new Boolean(false); //mylabeldownイベント中か
    var FixLabels = new Array(); //固定ラベル
    var FixNum = new Array(); //固定ラベルの番号
    var FixText = new Array(); //固定ラベルのタグを含むテキスト
    MytoFo = new Boolean(false); //IEのバグ対応。MyLabels_MouseMove→Form1_onMouseMoveのため
    var DragL; //ドラッグ中のラベルの引渡し。
    var QuesNum = 0; //問題番号のインターフェース用.　何問目？とかにつかう。1-30
    var array_flag = -1; //どこでイベントが起こったか判定する。(マウスダウン用)　0=問題提示欄 1=レジスタ1 2=レジスタ2 3=レジスタ3 4=最終解答欄
    var array_flag2 = -1; //どこでイベントが起こったか判定する。(マウスアップ用)　0=問題提示欄 1=レジスタ1 2=レジスタ2 3=レジスタ3 4=最終解答欄
    var d_flag = -1; //どこでイベントが起こったか判定する。(マウスアップ)　0=問題提示欄 1=レジスタ1 2=レジスタ2 3=3 4=最終解答欄
    //再表示用だよ
    var Mylabels2 = new Array();
    var Mylabels_left = new Array();
    var region = 0;
    var URL = './' //サーバー用
    var attempt = 0; //20250514追加

    // ======================= ▼▼▼ 新規追加: 単語群状態管理用 ▼▼▼ =======================
    var GlobalGroupCounter = 0; // 単語群IDの採番用カウンタ
    var LastGroupState = []; // 直前の単語群の状態 [{id: 1, ver: 1, members: "1#2"}]
    // ▼▼▼ 修正: ドラッグ開始時の状態保持用変数 ▼▼▼
    var DragStartGroupState = null; // { id: 1, members: "1#3#4#8" }
    var GlobalGroupMoveCounts = {}; // { groupID: count } 移動回数保持
    var GlobalGroupFormationHistory = {}; // { membersStr: count } 構成履歴保持

    // ▼▼▼ 修正: IDではなく「単語テキスト」の正解順序を保持 ▼▼▼
    var CorrectWordSequence = [];

    // 正解文(Answer)から、正しい「単語テキスト」の並び順を生成する関数
    function generateCorrectWordSequence() {
        if (!Answer) return;

        // 1. 正解文を単語に分解（句読点を除去して小文字化）
        // 文末のピリオドなども除去して、純粋な単語の並びにする
        var cleanAnswer = Answer.replace(/[.,?!:;)'’]+/g, "").trim();

        // 空白で分割して配列化
        var ansWords = cleanAnswer.split(/\s+/);

        var seq = [];
        for (var i = 0; i < ansWords.length; i++) {
            // 比較用に小文字化して保存
            seq.push(ansWords[i].toLowerCase());
        }

        CorrectWordSequence = seq;
        console.log("Correct Word Sequence:", CorrectWordSequence);
    }
    // ======================= ▼▼▼ 修正版: stick_same テキスト順序判定版 calculateStickParams ▼▼▼ =======================
    /**
     * @param {boolean} isMouseDown 
     * @param {Array} draggedIDs - ドラッグ中の単語IDの配列
     */
    function calculateStickParams(isMouseDown, draggedIDs) {

        if (!draggedIDs) draggedIDs = [];

        // --- ドラッグ開始時の状態保存 (MouseDown時) ---
        if (isMouseDown && draggedIDs.length > 0) {
            DragStartGroupState = null;
            for (var k = 0; k < LastGroupState.length; k++) {
                var oldMembers = LastGroupState[k].members.split("#");
                var isHit = false;
                for (var d = 0; d < draggedIDs.length; d++) {
                    if (oldMembers.indexOf(draggedIDs[d]) !== -1) {
                        isHit = true;
                        break;
                    }
                }
                if (isHit) {
                    DragStartGroupState = {
                        id: LastGroupState[k].id,
                        members: LastGroupState[k].members,
                        visualMembers: LastGroupState[k].visualMembers
                    };

                    // ▼▼▼ 分裂時の残存グループカウント処理 (テキスト順序ベース) ▼▼▼
                    // 以前の状態(LastGroupState)の visualMembers は視覚順でIDが並んでいる
                    var oldVisualMembers = LastGroupState[k].visualMembers.split("#");
                    var remainingVisualIDs = [];

                    for (var m = 0; m < oldVisualMembers.length; m++) {
                        // ドラッグされていないIDを、元の順序を保ったまま抽出
                        if (draggedIDs.indexOf(oldVisualMembers[m]) === -1) {
                            remainingVisualIDs.push(oldVisualMembers[m]);
                        }
                    }

                    // 残ったメンバーが2語以上の場合
                    if (remainingVisualIDs.length >= 2) {
                        // IDからテキスト(正規化済み)を取得してキーを作成
                        var remainingTexts = [];
                        for (var r = 0; r < remainingVisualIDs.length; r++) {
                            var el = document.getElementById(remainingVisualIDs[r]);
                            if (el) {
                                var txt = el.innerHTML.replace(/[.,?!:;)'’]+/g, "").trim().toLowerCase();
                                remainingTexts.push(txt);
                            }
                        }
                        var remainingKey = remainingTexts.join("|"); // 例: "this|is|a"

                        // 履歴を更新
                        if (!GlobalGroupFormationHistory[remainingKey]) {
                            GlobalGroupFormationHistory[remainingKey] = 1;
                        } else {
                            GlobalGroupFormationHistory[remainingKey]++;
                        }
                    }
                    // ▲▲▲ 追加ここまで ▲▲▲

                    break;
                }
            }
        }

        // 1. 盤面の有効な群を取得
        var allGroups = getAnswerGroups(25, isMouseDown);
        var validClusters = [];
        for (var i = 0; i < allGroups.length; i++) {
            if (allGroups[i].members.length >= 2) {
                validClusters.push(allGroups[i]);
            }
        }

        var stick_now = validClusters.length;
        var incorrect_stick_now = 0;

        // 変数定義
        var targetID = "",
            targetVer = "",
            targetSame = "",
            targetCount = "";
        var targetLeftX = "",
            targetRightX = "",
            targetY = "",
            targetIncorrect = "";

        // 返却用変数
        var val_stick_move = "";
        var val_stick_same = "";

        var newGroupState = [];
        var usedOldIds = {};

        var sortedDraggedStr = "";
        if (draggedIDs.length > 0) {
            sortedDraggedStr = draggedIDs.slice().sort(function(a, b) {
                return a - b
            }).join("#");
        }

        // --- A. 現在盤面にある群の処理 ---
        for (var i = 0; i < validClusters.length; i++) {
            var domElements = validClusters[i].members; // 既に視覚順(左→右)に並んでいる
            var membersArr = []; // ID管理用（ソートして使用）
            var visualIds = []; // バージョン管理用（視覚順ID）
            var visualWords = []; // 不正解判定・stick_same用（視覚順テキスト）

            for (var m = 0; m < domElements.length; m++) {
                var id = domElements[m].id;
                membersArr.push(id);
                visualIds.push(id);

                var text = domElements[m].innerHTML.replace(/[.,?!:;)'’]+/g, "").trim().toLowerCase();
                visualWords.push(text);
            }

            // バージョン管理・ID引継ぎ用は IDベース(ソート済) で行う
            var visualMembersStr = visualIds.join("#"); // 視覚順ID文字列
            membersArr.sort(function(a, b) {
                return a - b
            });
            var membersStr = membersArr.join("#"); // ソート済ID文字列

            // ★ stick_same 用のキーは「視覚順のテキスト」で作る
            var groupKey = visualWords.join("|");

            // incorrect_stick_now カウント
            if (CorrectWordSequence.length > 0) {
                var currentSeqStr = "," + visualWords.join(",") + ",";
                var correctSeqStr = "," + CorrectWordSequence.join(",") + ",";
                if (correctSeqStr.indexOf(currentSeqStr) === -1) {
                    incorrect_stick_now++;
                }
            }

            var assignedID = -1;
            var assignedVer = 1;
            var isUpdated = false;

            var isTarget = false;
            if (draggedIDs.length > 0) {
                for (var x = 0; x < membersArr.length; x++) {
                    if (draggedIDs.indexOf(membersArr[x]) !== -1) {
                        isTarget = true;
                        break;
                    }
                }
            }

            // ID引継ぎ (Exact Match)
            for (var k = 0; k < LastGroupState.length; k++) {
                if (usedOldIds[LastGroupState[k].id]) continue;
                if (LastGroupState[k].members === membersStr) {
                    assignedID = LastGroupState[k].id;
                    usedOldIds[assignedID] = true;
                    if (isTarget) {
                        if (typeof array_flag !== 'undefined' && array_flag == 4 &&
                            sortedDraggedStr === membersStr &&
                            DragStartGroupState && DragStartGroupState.members === sortedDraggedStr) {
                            assignedVer = LastGroupState[k].ver;
                        } else {
                            assignedVer = LastGroupState[k].ver + 1;
                        }
                    } else {
                        assignedVer = LastGroupState[k].ver;
                    }
                    break;
                }
            }

            // ID引継ぎ (Intersection)
            if (assignedID === -1) {
                for (var k = 0; k < LastGroupState.length; k++) {
                    if (usedOldIds[LastGroupState[k].id]) continue;
                    var oldMembers = LastGroupState[k].members.split("#");
                    if (oldMembers.length === 2) {
                        var containsAllOldMembers = true;
                        for (var o = 0; o < oldMembers.length; o++) {
                            if (membersArr.indexOf(oldMembers[o]) === -1) {
                                containsAllOldMembers = false;
                                break;
                            }
                        }
                        if (!containsAllOldMembers) continue;
                    }
                    var hasIntersection = false;
                    for (var x = 0; x < membersArr.length; x++) {
                        if (oldMembers.indexOf(membersArr[x]) !== -1) {
                            hasIntersection = true;
                            break;
                        }
                    }
                    if (hasIntersection) {
                        assignedID = LastGroupState[k].id;
                        assignedVer = LastGroupState[k].ver + 1;
                        usedOldIds[assignedID] = true;
                        isUpdated = true;
                        isTarget = true;
                        break;
                    }
                }
            }

            // 新規ID発行
            if (assignedID === -1) {
                GlobalGroupCounter++;
                assignedID = GlobalGroupCounter;
                assignedVer = 1;
                isUpdated = true;
                if (draggedIDs.length > 0) isTarget = true;
            }

            newGroupState.push({
                id: assignedID,
                ver: assignedVer,
                members: membersStr, // ソート済ID (ID引継ぎ判定用)
                visualMembers: visualMembersStr // 視覚順ID (分裂判定用)
            });

            // パラメータ算出 (操作対象のみ)
            if (isTarget || isUpdated) {
                if (!isMouseDown || (isMouseDown && draggedIDs.length > 0)) {
                    targetID = assignedID;
                    targetVer = assignedVer;
                    targetCount = membersArr.length;

                    if (!isMouseDown && DragStartGroupState) {
                        if (DragStartGroupState.id === assignedID &&
                            DragStartGroupState.members === membersStr &&
                            DragStartGroupState.visualMembers === visualMembersStr) {
                            targetSame = "1";
                        }
                    }

                    // ▼▼▼ stick_move / stick_same 判定ロジック ▼▼▼
                    var isMoveOperation = false;

                    // 1. 移動判定
                    if (sortedDraggedStr === membersStr) {
                        if (DragStartGroupState && DragStartGroupState.members === sortedDraggedStr) {
                            isMoveOperation = true;
                        }
                    }

                    if (isMoveOperation) {
                        // --- stick_move ---
                        if (!GlobalGroupMoveCounts[assignedID]) {
                            val_stick_move = 0;
                        } else {
                            val_stick_move = GlobalGroupMoveCounts[assignedID];
                        }

                        if (!isMouseDown) {
                            if (!GlobalGroupMoveCounts[assignedID]) GlobalGroupMoveCounts[assignedID] = 0;
                            GlobalGroupMoveCounts[assignedID]++;
                            val_stick_move = GlobalGroupMoveCounts[assignedID];
                        }
                    } else {
                        // --- stick_same ---
                        // ★修正: groupKey (テキスト順序) を使用して履歴を管理
                        var currentCount = 0;
                        if (GlobalGroupFormationHistory[groupKey]) {
                            currentCount = GlobalGroupFormationHistory[groupKey];
                        }

                        if (isMouseDown) {
                            if (currentCount > 0) {
                                val_stick_same = currentCount - 1;
                            } else {
                                val_stick_same = 0;
                            }
                        } else {
                            // MouseUp時: 更新
                            if (currentCount === 0) {
                                GlobalGroupFormationHistory[groupKey] = 1; // 1回目
                                val_stick_same = 0;
                            } else {
                                GlobalGroupFormationHistory[groupKey]++; // 加算
                                val_stick_same = GlobalGroupFormationHistory[groupKey] - 1;
                            }
                        }
                    }
                    // ▲▲▲ 判定ここまで ▲▲▲

                    // 移動時は既存カラム(num1, num2等)を記録しない
                    if (isMoveOperation) {
                        if (typeof array_flag !== 'undefined' && array_flag == 4) {
                            targetID = "";
                            targetVer = "";
                            targetSame = "";
                        }
                    }

                    // 座標計算
                    var minX = 99999;
                    var maxX = -99999;
                    var sumY = 0;
                    for (var d = 0; d < domElements.length; d++) {
                        var region = YAHOO.util.Dom.getRegion(domElements[d]);
                        if (region.left < minX) minX = region.left;
                        if (region.right > maxX) maxX = region.right;
                        sumY += region.top;
                    }
                    targetLeftX = minX;
                    targetRightX = maxX;
                    targetY = Math.round(sumY / domElements.length);

                    if (CorrectWordSequence.length > 0) {
                        var currentSeqStr = "," + visualWords.join(",") + ",";
                        var correctSeqStr = "," + CorrectWordSequence.join(",") + ",";
                        if (correctSeqStr.indexOf(currentSeqStr) === -1) {
                            targetIncorrect = "1";
                        }
                    }
                }
            }
        }

        // --- B. 維持処理 ---
        if (isMouseDown && draggedIDs.length > 0) {
            for (var k = 0; k < LastGroupState.length; k++) {
                if (usedOldIds[LastGroupState[k].id]) continue;
                var oldMembers = LastGroupState[k].members.split("#");
                var isDragging = false;
                for (var m = 0; m < oldMembers.length; m++) {
                    if (draggedIDs.indexOf(oldMembers[m]) !== -1) {
                        isDragging = true;
                        break;
                    }
                }
                if (isDragging) {
                    newGroupState.push(LastGroupState[k]);
                    usedOldIds[LastGroupState[k].id] = true;
                }
            }
        }

        LastGroupState = newGroupState;

        return {
            now: stick_now,
            num1: (targetID !== "") ? targetID : "",
            num2: (targetID !== "") ? targetVer : "",
            same: (targetSame !== "") ? targetSame : "",
            count: (targetCount !== "") ? targetCount : "",
            leftX: (targetLeftX !== "") ? targetLeftX : "",
            rightX: (targetRightX !== "") ? targetRightX : "",
            topY: (targetY !== "") ? targetY : "",
            incorrect: (targetIncorrect !== "") ? targetIncorrect : "",
            incorrectNow: incorrect_stick_now,
            stickMove: val_stick_move,
            stickSame: val_stick_same
        };
    }

    // ★新規追加: 単一単語の個数をカウントする関数 (word_now用)
    function getSingleWordCount() {
        // 現在の解答欄にある全グループ(単体含む)を取得
        // false = ドラッグ中を無視しない（MouseUp後なので配置済みとして扱う）
        var allGroups = getAnswerGroups(25, false);
        var singleCount = 0;

        for (var i = 0; i < allGroups.length; i++) {
            // メンバーが1つだけ＝単一単語
            if (allGroups[i].members.length === 1) {
                singleCount++;
            }
        }
        return singleCount;
    }

    var JapaneseAnswer = "";
    var sorted_labels = new Array();
    var Qid = <?php echo $Qid = $_GET['Qid']; ?> //LineQuesFormのボタンのURL引数
    /*
    var nEnd;
    if (Qid === 0) {
        nEnd = 30;
    }
    else if (Qid === 1) {
        nEnd = 55;
    }
    else if (Qid === 2) {
        nEnd = 80;
    }
    else if (Qid === 3) {
        nEnd = 104;
    }
        */


    // ======================= ▼▼▼ 修正点 1/3 ▼▼▼ =======================
    // 日本語の正解文を保存するためのグローバル変数を追加
    var JapaneseAnswer = "";
    // ======================= ▲▲▲ 修正点 1/3 ▲▲▲ =======================

    var Qid = <?php echo $Qid = $_GET['Qid']; ?> //LineQuesFormのボタンのURL引数
</script>
<?php
//Qidの値を取得して、それによってnEndを取得
//nEndはOIDの最終番号
require "../dbc.php";
$_SESSION['Qid'] = $_GET['Qid'];
$Qid = $_GET['Qid'];

// ▼▼▼【ここから追加】▼▼▼
// testsテーブルから言語タイプを取得
$lang_type = 'en'; // デフォルトは英語
$sql_lang = "SELECT lang_type FROM tests WHERE id = ?";
$stmt_lang = $conn->prepare($sql_lang);
$stmt_lang->bind_param('i', $Qid);
$stmt_lang->execute();
$result_lang = $stmt_lang->get_result();
if ($row_lang = $result_lang->fetch_assoc()) {
    if (!empty($row_lang['lang_type'])) {
        $lang_type = $row_lang['lang_type'];
    }
}
$stmt_lang->close();
// ▲▲▲【ここまで追加】▲▲▲

$sql = "select t_q.OID,t_q.WID 
            from test_questions t_q
            WHERE t_q.test_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $Qid);
$stmt->execute();
$result = $stmt->get_result();
$all_rows = $result->fetch_all(MYSQLI_ASSOC);
$last_rows = end($all_rows);        //最後の行の要素を取得
$nEnd = $last_rows['OID'];
//echo $Qid;
//echo $nEnd;
$stmt->close();
?>

<script type="text/javascript">
    // ▼▼▼【ここに追加】▼▼▼
    // PHPから受け取った言語タイプをJavaScript変数に格納
    var testLangType = "<?= $lang_type ?>";
    // ▲▲▲【ここに追加】▲▲▲
    nEnd = <?php echo $nEnd; ?>


    //ランダムに配列を並び替えるソース
    Array.prototype.random = function() {
        this.sort(function(a, b) {
            var i = Math.ceil(Math.random() * 100) % 2;
            if (i == 0) {
                return -1;
            } else {
                return 1;
            }
        });
    }
    //-------------------------------------------------------------
    //配列に指定した値があるかチェック
    if (!Array.prototype.contains) {
        Array.prototype.contains = function(value) {
            for (var i in this) {
                if (this.hasOwnProperty(i) && this[i] === value) {
                    return true;
                }
            }
            return false;
        }
    }
    //-------------------------------------------------------------
    //ロードイベント
    //body がloadされた時点で実行される。
    function ques_Load() {
        window.resizeTo(885, 860);
        new Ajax.Request(URL + 'swrite.php', //こんにちはOOさん出力
            {
                method: 'get',
                onSuccess: getA,
                onFailure: getE
            });
        //▲マウスデータの取得
        //ドラッグ開始地点の保存
        function getA(req) {
            alert(req.responseText);
        }

        function getE(req) {
            alert(<?= json_encode(translate('ques.php_325行目_書き込みに失敗しました')) ?>);
        }
        AnswerT = new DateFormat("yyyy-MM-dd HH:mm:ss");
        $AccessDate = AnswerT.format(new Date());
        BPen = new jsGraphics("myCanvas"); //ペン(グループ化用)
        BPen.setColor("black");
        //破線のスタイルを設定
        BPen.setStroke(-1);
        BPen2 = new jsGraphics("myCanvas"); //ペン(挿入線用)
        BPen2.setColor("black");
        BPen2.setStroke(4);
        //スラッシュ入れる用
        BPen3 = new jsGraphics("myCanvas2");

        BPenGroup = new jsGraphics("myCanvas");
        BPenGroup.setColor("blue"); // 下線の色（青）
        BPenGroup.setStroke(2); // 下線の太さ
        BPenTarget = new jsGraphics("myCanvas");
        BPenTarget.setColor("red"); // 枠の色
        BPenTarget.setStroke(3); // 枠の太さ（少し太めに強調）
        document.onmousemove = Form1_MouseMove;
        document.onselectstart = "return false";
        //-------------------------------------------------------------
        //DBから引用
        function getError(req, requestSource) {
            alert(<?= json_encode(translate('ques.php_335行目_リクエストエラー')) ?> + "(" + requestSource + "):" + req.status + "--" + req.statusText);
            console.log("エラー発生元：" + requestSource);
            console.log(req.responseText);
            window.close();
        }
        //=============linedatamouseがなかったら作成============
        new Ajax.Request(URL + 'linemouse.php', {
            method: 'get',
            onSuccess: getm,
            onFailure: function(req) {
                getError(req, "linemouse.php")
            }
        });

        function getm(res) {}
        //======================================================

        //解答データのうち最大のOIDを計算。要は次に出題する問題を算出する。
        var $a = "a"; //モード制御用
        $params = 'param1=' + encodeURIComponent($a) + '&lang=' + encodeURIComponent(testLangType);
        new Ajax.Request(URL + 'load.php', {
            method: 'get',
            onSuccess: getOID,
            onFailure: function(req) {
                getError(req, "load.php")
            },
            parameters: $params
        });

        function getOID(res) {
            OID = res.responseText; //load.phpから最大のOIDが入っているはずのresが帰ってくるのでそれを代入
            console.log("res" + res.responseText);
            console.log("OID = " + OID);
            if (OID == <?= json_encode(translate('ques.php_351行目_OID抽出エラー（マウス）')) ?> || OID == "" || OID == <?= json_encode(translate('ques.php_351行目_OIDエラー')) ?> || OID == <?= json_encode(translate('ques.php_351行目_これは最初の問題です')) ?>) {
                OID = 1;
            } else {
                OID = parseInt(OID) + 1; //取って来たのか履歴データなので次の問題を出すためにインクリメント
            }
            QuesNum = parseInt(OID);
        }
        myCheck2(0);
        //===================

        window.addEventListener('unload', function() {
            // ewrite.phpを呼び出して、サーバー上の一時ファイルをデータベースに書き込む
            // この通信はページのクローズを妨げず、バックグラウンドで実行される
            navigator.sendBeacon('ewrite.php');
        });
    }
    //ロードイベント終了========================================

    //問題の出題関数
    function setques() {
        //OID=出題順
        Fixmsg.innerHTML = <?= json_encode(translate('ques.php_364行目_情報')) ?>;
        myCheck(0);
        //問題固定var------------
        var $Load = "load";
        var $w = "w";
        var $params = 'param1=' + encodeURIComponent(OID) +
            '&param2=' + encodeURIComponent($Load) +
            '&lang=' + encodeURIComponent(testLangType);
        console.log("$wの時:" + $params);
        new Ajax.Request(URL + 'dbsyori.php', //本番用
            {
                method: 'get',
                onSuccess: getOIDtoWID,
                onFailure: Error,
                parameters: $params
            });

        function Error(res) {
            alert(<?= json_encode(translate('ques.php_379行目_問題取得失敗')) ?>);
            window.close;
        }

        function getOIDtoWID(res) {
            if (res.responseText == "エラー" && OID != nEnd + 1) {
                alert(<?= json_encode(translate('ques.php_384行目_固定問題番号取得エラー')) ?>);
            } else {
                console.log("デバッグ" + res.responseText);
                WID = res.responseText - 0;
                console.log("WID = " + WID);
                $q = "q";
                var $params_for_attempt = 'param1=' + encodeURIComponent(WID) + '&lang=' + encodeURIComponent(testLangType); //注意点
                new Ajax.Request(URL + 'getattempt.php', //本番用
                    {
                        method: 'get',
                        onSuccess: getAttempt,
                        onFailure: function(req) {
                            getError(req, "getattempt.php")
                        },
                        parameters: $params_for_attempt
                    });

                function getAttempt(res) {
                    attempt = res.responseText;
                    console.log("attempt = " + attempt);
                }
                var $params = 'param1=' + encodeURIComponent(WID) +
                    '&param2=' + encodeURIComponent($q) +
                    '&lang=' + encodeURIComponent(testLangType);
                console.log("$qの時:" + $params);
                new Ajax.Request(URL + 'dbsyori.php', //本番用
                    {
                        method: 'get',
                        onSuccess: getResponse,
                        onFailure: function(req) {
                            getError(req, "dbsyori.php")
                        },
                        parameters: $params
                    });

                //関数開始-----------------------------------
                function getResponse(req) {
                    //console.log("attempt = " + attempt);
                    PorQ = req.responseText.charAt(req.responseText.length - 1); //ピリオド、または？を抜き取る
                    str1 = req.responseText.substr(0, 1);
                    str2 = req.responseText.substr(1);
                    Answer = str1.toUpperCase() + str2; //完全な答え
                    $q = "q1";
                    $params = 'param1=' + encodeURIComponent(WID) +
                        '&param2=' + encodeURIComponent($q) +
                        '&lang=' + encodeURIComponent(testLangType);
                    new Ajax.Request(URL + 'dbsyori.php', //本番用
                        {
                            method: 'get',
                            onSuccess: getStart,
                            onFailure: function(req) {
                                getError(req, "dbsyori.php")
                            },
                            parameters: $params
                        });

                    function getStart(req1) {
                        Mylabels = req1.responseText.split("|");
                        $d = "d";
                        $params = 'param1=' + encodeURIComponent(WID) +
                            '&param2=' + encodeURIComponent($d) +
                            '&lang=' + encodeURIComponent(testLangType);
                        new Ajax.Request(URL + 'dbsyori.php', //本番用
                            {
                                method: 'get',
                                onSuccess: getDivide,
                                onFailure: function(req) {
                                    getError(req, "dbsyori.php")
                                },
                                parameters: $params
                            });

                        function getDivide(req2) {
                            MylabelsD = req2.responseText.split("|");
                            $f = "f";
                            $params = 'param1=' + encodeURIComponent(WID) +
                                '&param2=' + encodeURIComponent($f) +
                                '&lang=' + encodeURIComponent(testLangType);
                            new Ajax.Request(URL + 'dbsyori.php', //本番用
                                {
                                    method: 'get',
                                    onSuccess: getFix,
                                    onFailure: function(req) {
                                        getError(req, "dbsyori.php")
                                    },
                                    parameters: $params
                                });

                            function getFix(Fix) //固定情報の表示
                            {
                                msg.innerHTML = Fix.responseText;
                                if (Fix.responseText != "-1") {


                                    FixNum = Fix.responseText.split("#"); //♯区切り
                                    for (i = 0; i <= FixNum.length - 1; i++) {
                                        FixNum[i] -= 0; //数値化
                                        FixLabels[i] = MylabelsD[FixNum[i]];
                                        FixNum[i] += 1;
                                        Fixmsg.innerHTML += "</br><font size='5' color='green'>" + FixLabels[i] + "</font>" + <?= json_encode(translate('ques.php_442行目_は')) ?> + "<font size='5' color='red'>" + FixNum[i] + "</font>" + <?= json_encode(translate('ques.php_442行目_番目にきます')) ?>;
                                        FixNum[i] -= 1;
                                    }
                                } else {
                                    FixNum = 0
                                }
                                LabelNum = Mylabels.length;
                                //--------------------------------
                                //body要素を取得
                                var body = document.getElementsByTagName("body")[0];
                                var el;
                                //------------------------------
                                for (i = 0; i <= LabelNum - 1; i++) {
                                    //p要素を作成
                                    var p = document.createElement("div");
                                    var n = document.createElement("div"); //そのラベルが何番目にくるのかを表示するためのdiv要素
                                    //テキストノードを作成
                                    p.setAttribute("id", i);
                                    n.setAttribute("id", -i); //一応何かのために(削除用)
                                    YAHOO.util.Dom.setStyle(p, "position", "absolute");
                                    YAHOO.util.Dom.setStyle(n, "position", "absolute");
                                    if (i < 1) {
                                        YAHOO.util.Dom.setStyle(p, "left", DefaultX);
                                        YAHOO.util.Dom.setStyle(p, "top", DefaultY);
                                        var LL = YAHOO.util.Dom.getRegion(p);
                                        YAHOO.util.Dom.setStyle(n, "top", DefaultY - 15);
                                    } else {
                                        YAHOO.util.Dom.setStyle(p, "left", el.right + 17);
                                        YAHOO.util.Dom.setStyle(p, "top", DefaultY);
                                        var LL = YAHOO.util.Dom.getRegion(p);
                                        YAHOO.util.Dom.setStyle(n, "top", DefaultY - 15);
                                    }
                                    YAHOO.util.Dom.setStyle(p, "width", "auto");
                                    YAHOO.util.Dom.setStyle(n, "width", "auto");
                                    YAHOO.util.Dom.setStyle(p, "font-family", "Arial");
                                    YAHOO.util.Dom.setStyle(n, "font-size", "20px");
                                    if (i == LabelNum - 1) {
                                        StartQues += Mylabels[i];
                                    } else {
                                        StartQues += Mylabels[i] + "|";
                                    }
                                    dd[i] = new YAHOO.util.DD(p);
                                    var str = document.createTextNode(Mylabels[i]);
                                    //テキストノードをp要素に追加
                                    p.appendChild(str);
                                    MyNums[i] = i + 1;
                                    var str2 = document.createTextNode(MyNums[i]);

                                    //p要素をbody要素に追加
                                    Mylabels[i] = p;
                                    body.appendChild(Mylabels[i]);
                                    //p要素をbody要素に追加
                                    MyNums[i] = n;
                                    body.appendChild(MyNums[i]);

                                    var LL = YAHOO.util.Dom.getRegion(p);
                                    YAHOO.util.Dom.setStyle(n, "left", LL.left + (LL.right - LL.left) / 2 - 2);

                                    el = YAHOO.util.Dom.getRegion(p);
                                    //イベントハンドラの追加
                                    dd[i].onMouseDown = function(e) {
                                        MyLabels_MouseDown(this.getDragEl())
                                    }
                                    dd[i].onMouseUp = function(e) {
                                        MyLabels_MouseUp(this.getDragEl())
                                    }
                                    dd[i].onDrag = function(e) {
                                        MyLabels_MouseMove(this.getDragEl(), e)
                                    }
                                    YAHOO.util.Event.addListener(Mylabels[i], 'mouseover', MyLabels_MouseEnter);
                                    YAHOO.util.Event.addListener(Mylabels[i], 'mouseout', MyLabels_MouseLeave);

                                    region = YAHOO.util.Dom.getRegion(Mylabels[i]);
                                    Mylabels_left[i] = region.left;
                                    if (i != Mylabels.length - 1) {
                                        BPen3.setFont("arial", "15px", Font.ITALIC_BOLD);
                                        BPen3.drawString("/", region.right + 7, 100);
                                        BPen3.paint();
                                    }
                                }
                                //Mylabels配列のコピー。Mylabelは今後動かさないので。
                                Mylabels2 = Mylabels.concat();
                                //-------------------------------------
                                //日本文の取得
                                var $j = "j";
                                $params = 'param1=' + encodeURIComponent(WID) +
                                    '&param2=' + encodeURIComponent($j) +
                                    '&lang=' + encodeURIComponent(testLangType);
                                new Ajax.Request(URL + 'dbsyori.php', {
                                    method: 'get',
                                    onSuccess: getJapanese,
                                    onFailure: function(req) {
                                        getError(req, "dbsyori.php")
                                    },
                                    parameters: $params
                                });
                                // ======================= ▼▼▼ 修正点 2/3 ▼▼▼ =======================
                                // getJapanese関数を修正
                                function getJapanese(res) {
                                    if (testLangType === 'ja') {
                                        // 【日本語テストの場合】
                                        JapaneseAnswer = res.responseText;
                                        document.getElementById("RichTextBox1").innerHTML = Answer;
                                        // ラベルのテキストを「英文」に変更
                                        document.getElementById("reference_text_label").innerHTML = "<?= translate('英文') ?>";
                                    } else {
                                        // 【英語テストの場合】
                                        document.getElementById("RichTextBox1").innerHTML = res.responseText;
                                        // ラベルのテキストを「日本文」に戻す
                                        document.getElementById("reference_text_label").innerHTML = "<?= translate('ques.php_1554行目_日本文') ?>";
                                    }

                                    //-------------------------------------
                                    //別解の取得(得点は10点の物）
                                    var $s1 = "s1";
                                    $params = 'param1=' + encodeURIComponent(WID) +
                                        '&param2=' + encodeURIComponent($s1) +
                                        '&lang=' + encodeURIComponent(testLangType);
                                    new Ajax.Request(URL + 'dbsyori.php', {
                                        method: 'get',
                                        onSuccess: getSentence1,
                                        onFailure: function(req) {
                                            getError(req, "dbsyori.php")
                                        },
                                        parameters: $params
                                    });

                                    function getSentence1(res) {
                                        if (res.responseText != "") {
                                            str1 = res.responseText.substr(0, 1);
                                            str2 = res.responseText.substr(1);
                                            Answer1 = str1.toUpperCase() + str2; //先頭を大文字に変更
                                            //英文を取得
                                            var $s2 = "s2";
                                            $params = 'param1=' + encodeURIComponent(WID) +
                                                '&param2=' + encodeURIComponent($s2) +
                                                '&lang=' + encodeURIComponent(testLangType);
                                            new Ajax.Request(URL + 'dbsyori.php', {
                                                method: 'get',
                                                onSuccess: getSentence2,
                                                onFailure: function(req) {
                                                    getError(req, "dbsyori.php")
                                                },
                                                parameters: $params
                                            });

                                            function getSentence2(res) {
                                                if (res.responseText != "") { //NULL以外だったら
                                                    str1 = res.responseText.substr(0, 1);
                                                    str2 = res.responseText.substr(1);
                                                    Answer2 = str1.toUpperCase() + str2;
                                                }
                                            }
                                        }
                                    }
                                }
                                // ======================= ▲▲▲ 修正点 2/3 ▲▲▲ =======================
                                generateCorrectWordSequence();
                                Mouse_Flag = true;
                            } //Fix関数ここまで--------------------------------------------------------
                        }
                    }
                } /*getStart関数ここまで*/

                //--関数getresponseここまで---------------------------------------
            }
        }
        /*
        function getError(req) {
            alert("失敗b");
            window.close;
        }
            */
        //マウス取得スタート
        PreMouseTime = -1;

        //時刻を取得
        AnswerT = new DateFormat("yyyy-MM-dd HH:mm:ss");
        Answertime = AnswerT.format(new Date());
    }
    //問題の出題関数ここまで-------------------------------------------------------
    //範囲指定をするときのドラッグ開始処理------------------------------
    function Form1_MouseDown() {
        // 座標判定をシンプルに修正
        if (event.y <= 160) {
            d_flag = 0; // 問題提示欄
        } else if (event.y <= 550 && event.y > 160) { // 解答欄
            d_flag = 4;
        } else {
            d_flag = -1; // それ以外（ボタンエリアなど）は無効
        }

        if (Mouse_Flag == false) {
            return;
        }
        document.body.style.cursor = "crosshair";

        for (i = 0; i <= MyControls.length - 1; i++) {
            YAHOO.util.Dom.setStyle(MyControls[i], "color", "black");
        }
        MyControls = new Array();
        sPos.x = event.x + cx;
        sPos.y = event.y + cy;
        ePos.x = event.x + cx;
        ePos.y = event.y + cy;
        MV = true;

        // ▼▼▼ 追加: ドラッグ開始時に下線をリフレッシュ（一時的に消すなど）
        if (typeof BPenGroup !== 'undefined') BPenGroup.clear();
    }
    //------------------------------------------------------------------
    //マウスアップ関数ここから(範囲選択を確定（ラベルをグループ化))---------------------------------------------------
    function Form1_MouseUp() {
        MV = false;
        if (Mouse_Flag == false || IsDragging == true) {
            return;
        }
        BPen.clear();
        //マウスカーソルを戻す
        document.body.style.cursor = "default";
        var g_array = new Array();
        if (d_flag == 0) {
            g_array = Mylabels.slice(0);
        } else if (d_flag == 1) {
            g_array = Mylabels_r1.slice(0);
        } else if (d_flag == 2) {
            g_array = Mylabels_r2.slice(0);
        } else if (d_flag == 3) {
            g_array = Mylabels_r3.slice(0);
        } else if (d_flag == 4) {
            g_array = Mylabels_ea.slice(0);
        }
        //選択範囲の中にラベルがあればグループ化する
        //青色への色変えも
        //左上,右上,左下,右下の４方向からのドラッグに対応------------------------------------------
        for (i = 0; i <= g_array.length; i++) {
            //一時退避・・・なくて良い
            MLi = YAHOO.util.Dom.getRegion(g_array[i]);
            if (sPos.x <= ePos.x && sPos.y <= ePos.y) { //左上
                if ((sPos.x < MLi.right && sPos.y < MLi.bottom) && (ePos.x > MLi.left && ePos.y > MLi.top)) {
                    MyControls.push(g_array[i]);
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "blue");
                }
            } else if (sPos.x <= ePos.x && sPos.y >= ePos.y) { //左下
                if ((sPos.x < MLi.right && sPos.y > MLi.top) && (ePos.x > MLi.left && ePos.y < MLi.bottom)) {
                    MyControls.push(g_array[i]);
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "blue");
                }
            } else if (sPos.x > ePos.x && sPos.y < ePos.y) { //右上
                if ((sPos.x > MLi.left && sPos.y < MLi.bottom) && (ePos.x < MLi.right && ePos.y > MLi.top)) {
                    MyControls.push(g_array[i]);
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "blue");
                }
            } else if (sPos.x > ePos.x && sPos.y > ePos.y) { //右下
                if ((sPos.x > MLi.left && sPos.y > MLi.top) && (ePos.x < MLi.right && ePos.y < MLi.bottom)) {
                    MyControls.push(g_array[i]);
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "blue");
                }
            }
        } //----------------------------------------------------------------------------------------
    }
    //-----------------------------------------------------------
    //ドラッグ中に範囲指定の線を描画など
    function Form1_MouseMove(sender) {
        //ドラッグ中
        if (MV == true) {
            draw();
            ePos.x = event.x + cx;
            ePos.y = event.y + cy;
        }
        //--------------------別のマウスムーブの取り込み--------------------------------------
        var P = new Point(0, 0);
        if (Mouse_Flag == true) {
            //マウスの位置座標を取得
            P.x = event.x;
            P.y = event.y;
            var a;
            if (PreMouseTime != -1) { //データを間引く
                //経過時間取得-----
                myStop = new Date();
                mTime = myStop.getTime() - myStart.getTime();
                a = mTime - PreMouseTime;
                if (a < 100) {
                    return;
                }
            }

            //マウスデータの取得
            myStop = new Date();
            mTime = myStop.getTime() - myStart.getTime();
            $Mouse_Data["WID"] = WID;
            $Mouse_Data["Time"] = mTime;
            if (IsDragging == true) {
                var hLabel = sender;
                var hl = YAHOO.util.Dom.getRegion(DragL);
                $Mouse_Data["X"] = hl.left;
                $Mouse_Data["Y"] = hl.top;
            } else {
                $Mouse_Data["X"] = P.x;
                $Mouse_Data["Y"] = P.y;
            }
            $Mouse_Data["DragDrop"] = 0;
            $Mouse_Data["DropPos"] = -1;
            $Mouse_Data["hlabel"] = "";
            $Mouse_Data["Label"] = "";
            Mouse_Num += 1;
            PreMouseTime = $Mouse_Data["Time"];

            //encodeURI = 変換してるだけだぴょん
            //paramっていうのに各変数を入れてる！(tmpfileで&で区切って送ってる)
            var $params = 'param1=' + encodeURIComponent($Mouse_Data["WID"]) +
                '&param2=' + encodeURIComponent($Mouse_Data["Time"]) +
                '&param3=' + encodeURIComponent($Mouse_Data["X"]) +
                '&param4=' + encodeURIComponent($Mouse_Data["Y"]) +
                '&param5=' + encodeURIComponent($Mouse_Data["DragDrop"]) +
                '&param6=' + encodeURIComponent($Mouse_Data["DropPos"]) +
                '&param7=' + encodeURIComponent($Mouse_Data["hlabel"]) +
                '&param8=' + encodeURIComponent($Mouse_Data["Label"]) +
                '&lang=' + encodeURIComponent(testLangType);
            new Ajax.Request(URL + 'tmpfile.php', {
                method: 'get',
                onSuccess: getA,
                onFailure: getE,
                parameters: $params
            });
            //▲マウスデータの取得
            //ドラッグ開始地点の保存
            function getA(req) {
                document.getElementById("msg").innerHTML = req.responseText;
                MytoFo = false;
            }

            function getE(req) {
                alert("失敗c");
            }
        }
        //--------------------別のマウスムーブここまで----------------------------------------------------------------
    }

    // ■■■ 修正: 範囲選択の描画（解答欄の描画範囲を拡張） ■■■
    function draw() {
        BPen.clear();

        if (d_flag == 0) { // 問題提示欄
            if (ePos.y >= 160) {
                ePos.y = 160;
            }
        } else if (d_flag == 4) { // 解答欄（拡張）
            if (ePos.y <= 160) {
                ePos.y = 160;
            } else if (ePos.y >= 550) { // HTMLに合わせて拡張 (580px付近のプレビュー欄手前まで)
                ePos.y = 550;
            }
        }

        // 四角形の描画処理
        if (sPos.x <= ePos.x && sPos.y <= ePos.y) { //左上
            BPen.drawRect(sPos.x, sPos.y, ePos.x - sPos.x, ePos.y - sPos.y)
            loc = 0;
        } else if (sPos.x <= ePos.x && sPos.y >= ePos.y) { //左下
            BPen.drawRect(sPos.x, ePos.y, ePos.x - sPos.x, sPos.y - ePos.y)
            loc = 1;
        } else if (sPos.x > ePos.x && sPos.y < ePos.y) { //右上
            BPen.drawRect(ePos.x, sPos.y, sPos.x - ePos.x, ePos.y - sPos.y)
            loc = 2;
        } else if (sPos.x > ePos.x && sPos.y > ePos.y) { //右下
            BPen.drawRect(ePos.x, ePos.y, sPos.x - ePos.x, sPos.y - ePos.y)
            loc = 3;
        }
        BPen.paint();

        // 色変え処理（レジスタ判定を削除）
        var g_array = new Array();
        if (d_flag == 0) {
            g_array = Mylabels.slice(0);
        } else if (d_flag == 4) {
            g_array = Mylabels_ea.slice(0);
        }

        for (i = 0; i <= g_array.length - 1; i++) {
            MLi = YAHOO.util.Dom.getRegion(g_array[i]);
            // 包含判定ロジック（変更なし）
            var isSelected = false;
            if (sPos.x <= ePos.x && sPos.y <= ePos.y) { //左上
                if ((sPos.x < MLi.right && sPos.y < MLi.bottom) && (ePos.x > MLi.left && ePos.y > MLi.top)) isSelected = true;
            } else if (sPos.x <= ePos.x && sPos.y >= ePos.y) { //左下
                if ((sPos.x < MLi.right && sPos.y > MLi.top) && (ePos.x > MLi.left && ePos.y < MLi.bottom)) isSelected = true;
            } else if (sPos.x > ePos.x && sPos.y < ePos.y) { //右上
                if ((sPos.x > MLi.left && sPos.y < MLi.bottom) && (ePos.x < MLi.right && ePos.y > MLi.top)) isSelected = true;
            } else if (sPos.x > ePos.x && sPos.y > ePos.y) { //右下
                if ((sPos.x > MLi.left && sPos.y > MLi.top) && (ePos.x < MLi.right && ePos.y < MLi.bottom)) isSelected = true;
            }

            if (isSelected) {
                YAHOO.util.Dom.setStyle(g_array[i], "color", "red");
            } else {
                YAHOO.util.Dom.setStyle(g_array[i], "color", "black");
            }
        }
    }

    // ▼▼▼ 新規追加: ドラッグ開始時に穴を埋める関数 ▼▼▼
    function closeGapOnDrag(sender, controls) {
        // 1. ドラッグ対象（単体 or グループ）の範囲を計算
        var targets = (controls.length > 0) ? controls : [sender];

        var minX = 99999,
            maxX = -99999;
        var avgY = 0;

        for (var i = 0; i < targets.length; i++) {
            var r = YAHOO.util.Dom.getRegion(targets[i]);
            if (r.left < minX) minX = r.left;
            if (r.right > maxX) maxX = r.right;
            avgY += r.top;
        }
        avgY /= targets.length;

        var removedWidth = maxX - minX;
        var gap = 15; // 単語間の隙間
        var shiftAmount = removedWidth + gap; // 左にずらす量

        // 2. 解答欄にある「ドラッグしていない単語」を抽出
        var candidates = [];
        var draggedIds = {};
        for (var i = 0; i < targets.length; i++) draggedIds[targets[i].id] = true;

        for (var i = 0; i < Mylabels_ea.length; i++) {
            var lbl = Mylabels_ea[i];
            if (lbl && !draggedIds[lbl.id]) {
                candidates.push(lbl);
            }
        }

        // X座標順にソート
        candidates.sort(function(a, b) {
            return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
        });

        // 3. 右隣にある「つながっている単語群」を特定
        var chainToShift = [];
        var gapLimit = 25; // クラスターとみなす距離（赤枠・下線と同じ）

        for (var i = 0; i < candidates.length; i++) {
            var lbl = candidates[i];
            var r = YAHOO.util.Dom.getRegion(lbl);

            // 行が違う（高さが違う）ものは無視
            if (Math.abs(r.top - avgY) > 20) continue;

            // ドラッグ対象より右にあるか？
            // ※少し余裕を見て minX より右にあるものをチェック対象とする
            if (r.left > minX) {
                if (chainToShift.length === 0) {
                    // 最初の1個目：ドラッグ対象の「すぐ右」にあるか？
                    // (右端 - 左端) が 隙間許容値以内なら「つながっている」とみなす
                    if (r.left >= maxX && (r.left - maxX) < gapLimit) {
                        chainToShift.push(lbl);
                    }
                } else {
                    // 2個目以降：前の単語とつながっているか？
                    var prevR = YAHOO.util.Dom.getRegion(chainToShift[chainToShift.length - 1]);
                    if (r.left - prevR.right < gapLimit) {
                        chainToShift.push(lbl);
                    } else {
                        // 途切れたら終了
                        break;
                    }
                }
            }
        }

        // 4. 特定した単語群を左にずらす（穴埋め）
        for (var i = 0; i < chainToShift.length; i++) {
            var el = chainToShift[i];
            var currentX = YAHOO.util.Dom.getRegion(el).left;
            YAHOO.util.Dom.setX(el, currentX - shiftAmount);
        }
    }
    // ▲▲▲ 追加ここまで ▲▲▲

    // ソート関数（境界厳守・空きスペース探索版）
    function MyLabelSort(sender, ex, ey) {
        var mylabelarray3 = new Array();

        // --- A. 問題提示欄へ戻す処理（変更なし） ---
        if (array_flag2 == 0) {
            var targetLabels = (MyControls.length > 0) ? MyControls : [sender];
            mylabelarray3 = Mylabels.slice(0);
            for (var k = 0; k < targetLabels.length; k++) {
                var tLabel = targetLabels[k];
                var originalIndex = -1;
                for (var i = 0; i < Mylabels2.length; i++) {
                    if (Mylabels2[i].id == tLabel.id) {
                        originalIndex = i;
                        break;
                    }
                }
                if (originalIndex != -1) {
                    if (mylabelarray3.indexOf(tLabel) === -1) mylabelarray3.push(tLabel);
                    YAHOO.util.Dom.setX(tLabel, Mylabels_left[originalIndex]);
                    YAHOO.util.Dom.setY(tLabel, DefaultY);
                    YAHOO.util.Dom.setStyle(tLabel, "color", "black");
                }
            }
            Mylabels = mylabelarray3.slice(0);
            return mylabelarray3;
        }

        // --- B. 解答欄での処理 ---
        else if (array_flag2 == 4) {
            var dragTargets = (MyControls.length > 0) ? MyControls : [sender];
            var info = getTargetInfo(ex, ey, dragTargets);

            if (info.mode === "insert") {
                // --- 挿入モード（変更なし） ---
                // ※縦線が出ているときはそのガイドに従うため、ここでは衝突計算等は不要
                // （前回のコードと同じロジックを維持）
                var targetCluster = info.targets;
                var insertIdx = info.index;
                var mergedGroup = [];

                for (var i = 0; i < insertIdx; i++) mergedGroup.push(targetCluster[i]);
                for (var i = 0; i < dragTargets.length; i++) mergedGroup.push(dragTargets[i]);
                for (var i = insertIdx; i < targetCluster.length; i++) mergedGroup.push(targetCluster[i]);

                // 衝突チェックと結合
                var processedIds = {};
                for (var i = 0; i < mergedGroup.length; i++) processedIds[mergedGroup[i].id] = true;

                var otherCandidates = [];
                for (var i = 0; i < Mylabels_ea.length; i++) {
                    var lbl = Mylabels_ea[i];
                    if (lbl && !processedIds[lbl.id]) {
                        var isDrag = false;
                        for (var d = 0; d < dragTargets.length; d++) {
                            if (dragTargets[d].id == lbl.id) isDrag = true;
                        }
                        if (!isDrag) otherCandidates.push(lbl);
                    }
                }
                otherCandidates.sort(function(a, b) {
                    return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
                });

                var gap = 15;
                var mergeDist = 25;
                var startX = 0;
                if (insertIdx === 0 && info.lineX !== -1) startX = info.lineX;
                else startX = YAHOO.util.Dom.getRegion(mergedGroup[0]).left;

                var currentRight = startX;
                for (var i = 0; i < mergedGroup.length; i++) {
                    var r = YAHOO.util.Dom.getRegion(mergedGroup[i]);
                    currentRight += (r.right - r.left) + gap;
                }
                currentRight -= gap;

                for (var i = 0; i < otherCandidates.length; i++) {
                    var candidate = otherCandidates[i];
                    var rCand = YAHOO.util.Dom.getRegion(candidate);
                    if (Math.abs(rCand.top - info.refY) < 20) {
                        // 修正: 候補が「現在のグループの開始位置より右側」にあり、かつ「距離が近い」場合のみ結合する
                        if (rCand.left > startX && (rCand.left - currentRight < mergeDist)) {
                            mergedGroup.push(candidate);
                            processedIds[candidate.id] = true;
                            currentRight += gap + (rCand.right - rCand.left);
                        }
                    }
                }

                var currentX = startX;
                for (var i = 0; i < mergedGroup.length; i++) {
                    var el = mergedGroup[i];
                    var r = YAHOO.util.Dom.getRegion(el);
                    YAHOO.util.Dom.setX(el, currentX);
                    YAHOO.util.Dom.setY(el, info.refY);
                    currentX += (r.right - r.left) + gap;
                }

                var finalArray = [];
                for (var i = 0; i < Mylabels_ea.length; i++) {
                    var lbl = Mylabels_ea[i];
                    if (lbl && !processedIds[lbl.id]) {
                        var isDrag = false;
                        for (var d = 0; d < dragTargets.length; d++) {
                            if (dragTargets[d].id == lbl.id) isDrag = true;
                        }
                        if (!isDrag) finalArray.push(lbl);
                    }
                }
                for (var i = 0; i < mergedGroup.length; i++) finalArray.push(mergedGroup[i]);
                Mylabels_ea = finalArray;
                return finalArray;

            } else {
                // --- 自由配置モード：▼▼▼ 修正箇所：総当たりスペース探索 ▼▼▼ ---
                GlobalRepelFlag = false;

                // 1. 静的単語（障害物）リストを作成
                var staticRegions = [];
                var draggedIds = {};
                for (var i = 0; i < dragTargets.length; i++) draggedIds[dragTargets[i].id] = true;

                for (var i = 0; i < Mylabels_ea.length; i++) {
                    if (Mylabels_ea[i] && !draggedIds[Mylabels_ea[i].id]) {
                        // 障害物の領域を保存
                        staticRegions.push(YAHOO.util.Dom.getRegion(Mylabels_ea[i]));
                    }
                }

                // 2. ドラッグ群全体のバウンディングボックス（サイズ）を計算
                var minX = 99999,
                    maxX = -99999,
                    minY = 99999,
                    maxY = -99999;
                var groupOffsets = []; // 左上を(0,0)とした時の各単語の相対位置

                // まず全体枠を取得
                for (var i = 0; i < dragTargets.length; i++) {
                    var r = YAHOO.util.Dom.getRegion(dragTargets[i]);
                    if (r.left < minX) minX = r.left;
                    if (r.right > maxX) maxX = r.right;
                    if (r.top < minY) minY = r.top;
                    if (r.bottom > maxY) maxY = r.bottom;
                }
                var groupW = maxX - minX;
                var groupH = maxY - minY;

                // 各単語の相対位置を保存（後で移動させるため）
                for (var i = 0; i < dragTargets.length; i++) {
                    var r = YAHOO.util.Dom.getRegion(dragTargets[i]);
                    groupOffsets.push({
                        el: dragTargets[i],
                        offsetX: r.left - minX,
                        offsetY: r.top - minY
                    });
                }

                // 3. 探索設定
                // 解答欄の境界
                var areaMinX = 40;
                var areaMaxX = 812;
                var areaMinY = 160;
                var areaMaxY = 550;

                // 探索開始位置（ドロップ位置）
                // ただし、ドロップ位置が既にエリア外ならエリア内にクランプ（引き戻し）してからスタート
                var startX = minX;
                var startY = minY;

                if (startX < areaMinX) startX = areaMinX;
                if (startX + groupW > areaMaxX) startX = areaMaxX - groupW;
                if (startY < areaMinY) startY = areaMinY;
                if (startY + groupH > areaMaxY) startY = areaMaxY - groupH;

                // 4. スパイラル探索（総当たり）
                // (x,y) が有効かチェックする関数
                var isValidPosition = function(x, y) {
                    // 解答欄の枠内チェック（ここは厳密に）
                    if (x < areaMinX || x + groupW > areaMaxX) return false;
                    if (y < areaMinY || y + groupH > areaMaxY) return false;

                    // 衝突チェック用のマージン（px）
                    // この値の分だけ、他の単語から離します
                    var marginY = 20;
                    var marginX = 20;

                    var myBottom = y + groupH;
                    var myRight = x + groupW;

                    for (var k = 0; k < staticRegions.length; k++) {
                        var obs = staticRegions[k];

                        // Y軸（高さ）が重なっているか
                        var isYOverlap = (y < obs.bottom + marginY && myBottom > obs.top - marginY);

                        // X軸（横幅）が重なっているか
                        var isXOverlap = (x < obs.right + marginX && myRight > obs.left - marginX);

                        // 両方重なっている場合のみ「配置不可」とする
                        if (isYOverlap && isXOverlap) {
                            return false; // 近すぎる（衝突）
                        }
                    }
                    return true;
                };
                var foundX = startX;
                var foundY = startY;
                var isFound = false;

                // まず現在地（補正後）をチェック
                if (isValidPosition(startX, startY)) {
                    foundX = startX;
                    foundY = startY;
                    isFound = true;
                } else {
                    GlobalRepelFlag = true;
                    // スパイラル探索開始
                    // 半径rを広げながら、角度thetaを回して探す
                    var step = 10; // 探索の粗さ（px）
                    var maxRadius = 800; // 探索最大半径

                    searchLoop:
                        for (var r = 1; r * step < maxRadius; r++) {
                            var dist = r * step;
                            // 円周上をチェック（半径に応じて分割数を変えるとなお良いが固定でも可）
                            var angleStep = 0.5; // ラジアン単位
                            if (dist > 100) angleStep = 0.2; // 遠くへ行くほど細かく

                            for (var theta = 0; theta < 2 * Math.PI; theta += angleStep) {
                                var checkX = startX + dist * Math.cos(theta);
                                var checkY = startY + dist * Math.sin(theta);

                                if (isValidPosition(checkX, checkY)) {
                                    foundX = checkX;
                                    foundY = checkY;
                                    isFound = true;
                                    break searchLoop;
                                }
                            }
                        }
                }

                // 見つからなかった場合（ほぼあり得ないが）、エリア左上に強制配置などの安全策
                if (!isFound) {
                    // 最終手段として空きスペース検索を左上からグリッドで行う
                    gridLoop: for (var y = areaMinY; y < areaMaxY - groupH; y += 20) {
                        for (var x = areaMinX; x < areaMaxX - groupW; x += 20) {
                            if (isValidPosition(x, y)) {
                                foundX = x;
                                foundY = y;
                                break gridLoop;
                            }
                        }
                    }
                }

                // 5. 決定した位置に移動
                for (var i = 0; i < groupOffsets.length; i++) {
                    var item = groupOffsets[i];
                    YAHOO.util.Dom.setX(item.el, foundX + item.offsetX);
                    YAHOO.util.Dom.setY(item.el, foundY + item.offsetY);
                }

                // 6. 配列保存
                var newArray = [];
                for (var i = 0; i < Mylabels_ea.length; i++) {
                    if (Mylabels_ea[i] && !draggedIds[Mylabels_ea[i].id]) newArray.push(Mylabels_ea[i]);
                }
                for (var i = 0; i < dragTargets.length; i++) newArray.push(dragTargets[i]);

                Mylabels_ea = newArray;
                return newArray;
            }
        }
        return mylabelarray3;
    }
    //マウスが上に来たらラベルの見た目を変えたり、グループ化やレジスタラベルの対応---------------
    function MyLabels_MouseEnter(e) {

        // ======================= ▼▼▼ 修正箇所 ▼▼▼ =======================
        var tooltip = document.getElementById('wordOrderTooltip');
        var hoveredLabel = this; // マウスが乗っているラベル

        // ラベルが解答欄にあるかチェック
        var isInAnswerArea = Mylabels_ea.some(function(label) {
            return label.id === hoveredLabel.id;
        });

        if (isInAnswerArea) {
            // 【変更】共通関数を使って正しい順序を取得
            var sortedCopy = getSortedAnswerLabels();

            // ソート済み配列内でのインデックスを探す
            var orderIndex = -1;
            for (var i = 0; i < sortedCopy.length; i++) {
                if (sortedCopy[i].id === hoveredLabel.id) {
                    orderIndex = i;
                    break;
                }
            }

            if (orderIndex !== -1) {
                tooltip.innerHTML = orderIndex + 1; // 1から始まる番号
                var hoveredRegion = YAHOO.util.Dom.getRegion(hoveredLabel);

                tooltip.style.left = (hoveredRegion.left) + 'px';
                tooltip.style.top = (hoveredRegion.top - 20) + 'px'; // 20px上に表示
                tooltip.style.display = 'block';
            }
        }
        // ======================= ▲▲▲ 修正ここまで ▲▲▲ =======================

        if (MV == true || IsDragging == true) {
            return;
        }
        //レジスタ内のグループ化・・・なくてよし
        var index = MyControls.indexOf(this);
        //グループ化されたラベルの初期化
        if (index == -1) {
            for (i = 0; i <= MyControls.length - 1; i++) {
                //YAHOO.util.Dom.setStyle(MyControls[i], "text-decoration", "none");
                YAHOO.util.Dom.setStyle(MyControls[i], "background-color", "transparent");
            }
            MyControls = new Array();
        } else {
            for (i = 0; i <= MyControls.length - 1; i++) {
                //YAHOO.util.Dom.setStyle(MyControls[i], "text-decoration", "underline overline");
                YAHOO.util.Dom.setStyle(MyControls[i], "background-color", "yellow");
            }
        }
        //YAHOO.util.Dom.setStyle(this, "text-decoration", "underline overline");
        YAHOO.util.Dom.setStyle(this, "background-color", "yellow");

    }

    function MyLabels_MouseLeave() {
        // ======================= ▼▼▼ ここに追加 ▼▼▼ =======================
        // マウスが離れたらポップアップを隠す
        document.getElementById('wordOrderTooltip').style.display = 'none';
        // ======================= ▲▲▲ 追加ここまで ▲▲▲ =======================
        if (MV == true || IsDragging == true) {
            return;
        }
        for (i = 0; i <= MyControls.length - 1; i++) {
            //YAHOO.util.Dom.setStyle(MyControls[i], "text-decoration", "none");
            YAHOO.util.Dom.setStyle(MyControls[i], "background-color", "transparent");
        }
        //YAHOO.util.Dom.setStyle(this, "text-decoration", "none");
        YAHOO.util.Dom.setStyle(this, "background-color", "transparent");
    }

    // ■■■ 修正: 解答プレビュー更新用関数 ■■■
    function updateAnswerPreview() {
        var previewBox = document.getElementById("AnswerPreview");
        if (!previewBox) return;

        // 【変更】単純なXソートから、共通関数を使った「塊ソート」へ変更
        // これにより、プレビュー欄も「塊ごとの順序」で表示されます
        // ※ getSortedAnswerLabels() は先ほど追加した共通関数です
        var sortedPreviewLabels = getSortedAnswerLabels();

        var previewText = "";

        // 直前にスペースを入れない記号の定義（ピリオド, カンマ, クエスチョン, エクスクラメーション, コロン, セミコロン, 閉じ括弧, アポストロフィ系）
        var noSpacePattern = /^[.,?!:;)'’]+$/;

        for (var i = 0; i < sortedPreviewLabels.length; i++) {
            var word = sortedPreviewLabels[i].innerHTML;

            // 区切り文字を除外
            if (word === "/") continue;

            // 【機能1】最初の単語の頭文字を大文字化
            // previewTextがまだ空（＝これが最初の単語）の場合のみ実行
            if (previewText === "") {
                // 英語のテストの場合のみ大文字化などの処理を入れるのが理想ですが、
                // 既存ロジックに合わせて単純に適用します
                word = word.charAt(0).toUpperCase() + word.slice(1);
            }

            // 【機能2】単語間のスペース調整
            if (previewText.length > 0) {
                // 現在の単語が「直前にスペースを入れない記号」でなければ、スペースを追加
                if (!noSpacePattern.test(word)) {
                    previewText += " ";
                }
            }

            previewText += word;
        }

        // プレビュー欄に表示
        previewBox.innerHTML = previewText;
    }

    //★★ラベルクリック時。引っこ抜くときの作業とかしてるよ
    function MyLabels_MouseDown(sender) {
        myStop = new Date();
        var mylabelarray = new Array();
        var index_sender = 0;
        var index_sender_g = 0;

        // 所属配列の判定（レジスタ1-3の判定を削除）
        array_flag = -1;

        // 問題提示欄
        for (i = 0; i < Mylabels.length; i++) {
            if (Mylabels[i] == undefined) continue;
            if (Mylabels[i].id == sender.id) {
                array_flag = 0;
                index_sender = i;
            }
        }
        // 最終解答欄
        for (i = 0; i < Mylabels_ea.length; i++) {
            if (Mylabels_ea[i].id == sender.id) {
                array_flag = 4;
                index_sender = i;
            }
        }

        // グループ化の場合のインデックス特定
        if (MyControls.length > 0) {
            var g_array = new Array();
            if (array_flag == 4) {
                g_array = Mylabels_ea.slice(0);
            }
            for (i = 0; i < g_array.length; i++) {
                if (g_array[i] == undefined) continue;
                if (g_array[i].id == MyControls[0].id) {
                    index_sender_g = i;
                }
            }
        }

        // 配列のコピー
        if (array_flag == 0) {
            mylabelarray = Mylabels.slice(0);
        } else if (array_flag == 4) {
            mylabelarray = Mylabels_ea.slice(0);
        }

        Mld = true;
        var hLabel = sender;
        DragL = sender;
        IsDragging = true;
        var DPos = 0;
        DLabel = "";

        // グループラベルIDの生成
        if (MyControls.length != 0) {
            for (i = 0; i <= MyControls.length - 1; i++) {
                if (i == MyControls.length - 1) {
                    DLabel = DLabel + MyControls[i].id;
                } else {
                    DLabel = DLabel + MyControls[i].id + "#";
                }
            }
        } else {
            DLabel = DLabel + hLabel.id;
        }

        // 解答欄からドラッグする場合、配列から削除する前に穴埋め処理を実行
        if (array_flag == 4) {
            closeGapOnDrag(sender, MyControls);
        }

        // 配列からの削除処理
        if (array_flag == 0) {
            delete mylabelarray[index_sender];
        } else {
            // 解答欄からのドラッグ
            if (MyControls.length > 0) {
                mylabelarray.splice(index_sender_g, MyControls.length);
            } else {
                mylabelarray.splice(index_sender, 1);
            }
            // ★レジスタからの復帰処理(整列)は削除
        }

        // ログ保存処理（省略なし）
        mTime = myStop.getTime() - myStart.getTime();
        var X = YAHOO.util.Dom.getRegion(hLabel);
        $Mouse_Data["WID"] = WID;
        $Mouse_Data["Time"] = mTime;
        $Mouse_Data["X"] = X.left;
        $Mouse_Data["Y"] = X.top;
        $Mouse_Data["DragDrop"] = 2; // MouseDown
        $Mouse_Data["DropPos"] = DPos;
        $Mouse_Data["hlabel"] = hLabel.id;
        $Mouse_Data["Label"] = DLabel;
        Mouse_Num += 1;

        // ======================= ▼▼▼ 修正: 分裂した単語群のみ記録する処理 ▼▼▼ =======================
        var val_stick = "";
        var val_stick_count = "";
        var val_repel = "";
        var val_repel_count = "";
        var val_back = "";
        var val_back_count = "";
        var val_norder = "";

        // 解答欄からドラッグされた場合のみ判定
        if (array_flag == 4) {
            // 1. ドラッグ対象（単体 or グループ）を特定
            var dragTargets = (MyControls.length > 0) ? MyControls : [sender];

            // 2. 「ドラッグ対象以外」で構成されるグループを取得
            var remainingGroups = getAnswerGroups(25, true);
            var stickIds = [];
            var gapLimit = 25; // 接続判定距離（px）

            // 3. 各グループについて「ドラッグ対象と隣接していたか」をチェック
            for (var g = 0; g < remainingGroups.length; g++) {
                var group = remainingGroups[g];

                // 条件: 残されたものが「2単語以上のグループ」であること
                if (group.members.length < 2) continue;

                var isConnected = false;

                // グループ内の全単語と、ドラッグ対象の全単語の距離を総当たりチェック
                // (MouseDown時点ではまだ座標移動していないため、この判定が可能)
                for (var m = 0; m < group.members.length; m++) {
                    var rStatic = YAHOO.util.Dom.getRegion(group.members[m]);

                    for (var d = 0; d < dragTargets.length; d++) {
                        var rDrag = YAHOO.util.Dom.getRegion(dragTargets[d]);

                        // 同じ行か？ (Y座標のズレが小さい)
                        if (Math.abs(rStatic.top - rDrag.top) > 20) continue;

                        // 左右どちらかに隣接しているか？
                        var distLeft = Math.abs(rStatic.right - rDrag.left); // Staticの右にDrag
                        var distRight = Math.abs(rStatic.left - rDrag.right); // Staticの左にDrag

                        if (distLeft < gapLimit || distRight < gapLimit) {
                            isConnected = true;
                            break;
                        }
                    }
                    if (isConnected) break;
                }

                // 元々くっついていたグループなら記録対象に追加
                if (isConnected) {
                    for (var k = 0; k < group.members.length; k++) {
                        stickIds.push(group.members[k].id);
                    }
                    // 基本的に1回の操作で分裂するのは1グループだけなのでループを抜ける
                    break;
                }
            }

            if (stickIds.length > 0) {
                val_stick = stickIds.join("#");
                val_stick_count = "2";
            }
        }
        // ======================= ▲▲▲ 修正ここまで ▲▲▲ =======================
        // ドラッグ対象のIDリストを作成
        var draggingIDs = [];
        if (MyControls.length > 0) {
            for (var i = 0; i < MyControls.length; i++) draggingIDs.push(MyControls[i].id);
        } else {
            draggingIDs.push(sender.id);
        }

        // 第2引数にドラッグ中のIDを渡す
        var stickInfo = calculateStickParams(true, draggingIDs);

        var $params = 'param1=' + encodeURIComponent($Mouse_Data["WID"]) +
            '&param2=' + encodeURIComponent($Mouse_Data["Time"]) +
            '&param3=' + encodeURIComponent($Mouse_Data["X"]) +
            '&param4=' + encodeURIComponent($Mouse_Data["Y"]) +
            '&param5=' + encodeURIComponent($Mouse_Data["DragDrop"]) +
            '&param6=' + encodeURIComponent($Mouse_Data["DropPos"]) +
            '&param7=' + encodeURIComponent($Mouse_Data["hlabel"]) +
            '&param8=' + encodeURIComponent($Mouse_Data["Label"]) +
            // --- 既存 stick (9-13) ---
            '&param9=' + encodeURIComponent(val_stick) + // register_stick
            '&param10=' + encodeURIComponent(val_stick_count) + // register_stick_count
            '&param11=' + encodeURIComponent(stickInfo.now) + // stick_now
            '&param12=' + encodeURIComponent(stickInfo.num1) + // stick_number1
            '&param13=' + encodeURIComponent(stickInfo.num2) + // stick_number2
            // --- ▼▼▼ 新規追加 (14-16) ▼▼▼ ---
            '&param14=' + encodeURIComponent("") + // stick_number_same (MouseDown時は空)
            '&param15=' + encodeURIComponent(stickInfo.count) + // stick_composition_count (ドラッグ中の群構成数)
            '&param16=' + encodeURIComponent("") + // word_now (MouseDown時は空)
            // --- ▼▼▼ シフト分 (17-21) ▼▼▼ ---
            '&param17=' + encodeURIComponent(val_repel) + // 旧 param14 (repel)
            '&param18=' + encodeURIComponent(val_repel_count) + // 旧 param15 (repel_count)
            '&param19=' + encodeURIComponent(val_back) + // 旧 param16 (back)
            '&param20=' + encodeURIComponent(val_back_count) + // 旧 param17 (back_count)
            '&param21=' + encodeURIComponent(val_norder) + // 旧 param18 (NOrder)
            '&param22=' + encodeURIComponent(stickInfo.leftX) +
            '&param23=' + encodeURIComponent(stickInfo.rightX) +
            '&param24=' + encodeURIComponent(stickInfo.topY) +
            '&param25=' + encodeURIComponent(stickInfo.incorrect) +
            '&param26=' + encodeURIComponent(stickInfo.incorrectNow) + // incorrect_stick_now
            '&param27=' + encodeURIComponent(stickInfo.stickMove) + // stick_move
            '&param28=' + encodeURIComponent(stickInfo.stickSame) + // stick_same
            '&lang=' + encodeURIComponent(testLangType);
        // 注意: param9, param10はtmpfile.php側で固定でNULLを入れる仕様のようなので、
        // ここではJSから値を送ってもtmpfile.php側で無視されるか、既存コードに合わせて空を送ります。
        // 今回の要件では「param11,12,13」を追加したいとのことなので、
        // tmpfile.phpの受け取り側と整合性を合わせます。

        new Ajax.Request(URL + 'tmpfile.php', {
            method: 'get',
            onSuccess: function(req) {
                document.getElementById("msg").innerHTML = req.responseText;
                Mld = false;
            },
            onFailure: function() {
                alert("失敗d");
            },
            parameters: $params
        });

        DiffPoint = new Point(event.x, event.y);

        // グローバル配列への反映
        if (array_flag == 0) {
            Mylabels = mylabelarray.slice(0);
        } else if (array_flag == 4) {
            Mylabels_ea = mylabelarray.slice(0);
            drawGroupUnderlines();
            // ★ドラッグ開始で単語が抜けたのでプレビューを更新
            updateAnswerPreview();
        }
    }

    // ======================= ▼▼▼ 新規追加関数 ▼▼▼ =======================

    // 機能A: 近くの単語に吸着させる関数
    function snapToNearestWord(droppedLabel) {
        var labels = Mylabels_ea; // 解答欄にある単語リスト
        var droppedRegion = YAHOO.util.Dom.getRegion(droppedLabel);
        var snapDistanceY = 30; // Y座標の吸着しきい値(px)
        var snapDistanceX = 20; // X座標の吸着しきい値(px)

        var nearestLabel = null;
        var minDiff = 99999;

        for (var i = 0; i < labels.length; i++) {
            var target = labels[i];
            if (target.id === droppedLabel.id) continue; // 自分自身は無視

            var targetRegion = YAHOO.util.Dom.getRegion(target);

            // Y座標の差（絶対値）を確認
            var diffY = Math.abs(droppedRegion.top - targetRegion.top);

            if (diffY < snapDistanceY) {
                // Yが近い場合、X方向の距離を確認
                // 左側にくっつくか、右側にくっつくか
                var distToRight = Math.abs(droppedRegion.left - targetRegion.right);
                var distToLeft = Math.abs(droppedRegion.right - targetRegion.left);

                // 最も近い距離を採用
                var closestXDist = Math.min(distToRight, distToLeft);

                if (closestXDist < 50 && closestXDist < minDiff) { // 50px以内なら吸着候補
                    minDiff = closestXDist;
                    nearestLabel = target;
                }
            }
        }

        // 吸着対象が見つかった場合、座標を更新
        if (nearestLabel) {
            var targetRegion = YAHOO.util.Dom.getRegion(nearestLabel);
            var gap = 15; // ★修正: 間隔を5pxから15pxに広げました

            // Y座標をターゲットに合わせる（これで横並びになる）
            YAHOO.util.Dom.setY(droppedLabel, targetRegion.top);

            // X座標の調整（重ならないように配置）
            if (droppedRegion.left < targetRegion.left) {
                // ターゲットの左側に配置
                YAHOO.util.Dom.setX(droppedLabel, targetRegion.left - (droppedRegion.right - droppedRegion.left) - gap);
            } else {
                // ターゲットの右側に配置
                YAHOO.util.Dom.setX(droppedLabel, targetRegion.right + gap);
            }
        }
    }

    // 機能B: グループ化された単語に下線を引く関数（隙間判定強化版）
    function drawGroupUnderlines() {
        if (typeof BPenGroup === 'undefined') return;
        BPenGroup.clear();

        if (Mylabels_ea.length === 0) return;

        // 1. 座標順にソート
        var sorted = Mylabels_ea.slice(0).sort(function(a, b) {
            var rA = YAHOO.util.Dom.getRegion(a);
            var rB = YAHOO.util.Dom.getRegion(b);
            if (Math.abs(rA.top - rB.top) > 15) return rA.top - rB.top;
            return rA.left - rB.left;
        });

        // 2. グループごとに線を引く
        var currentGroup = [];
        var groupGapLimit = 25; // ★この距離以上離れていたら線を切る

        for (var i = 0; i < sorted.length; i++) {
            var label = sorted[i];
            var region = YAHOO.util.Dom.getRegion(label);

            if (currentGroup.length === 0) {
                currentGroup.push(region);
            } else {
                var prevRegion = currentGroup[currentGroup.length - 1];

                var isSameLine = Math.abs(region.top - prevRegion.top) < 15;
                // ★重要: 隙間が広い場合はグループを切る
                var isCloseEnough = (region.left - prevRegion.right) < groupGapLimit;

                if (isSameLine && isCloseEnough) {
                    currentGroup.push(region);
                } else {
                    if (currentGroup.length >= 2) drawLineForGroup(currentGroup);
                    currentGroup = [region];
                }
            }
        }
        if (currentGroup.length >= 2) drawLineForGroup(currentGroup);
    }

    // 実際に線を引く補助関数
    function drawLineForGroup(regions) {
        var startX = regions[0].left;
        var endX = regions[regions.length - 1].right;
        var y = regions[0].bottom - 2; // 文字の下端から少し上に線を引く

        BPenGroup.drawLine(startX, y, endX, y);
        BPenGroup.paint();
    }
    // ======================= ▲▲▲ 新規追加ここまで ▲▲▲ =======================

    // ======================= ▼▼▼ 新規追加関数 ▼▼▼ =======================

    // 機能C: スナップ対象のグループに赤枠を表示する関数
    function highlightSnapTarget(draggedLabel) {
        if (typeof BPenTarget === 'undefined') return;
        BPenTarget.clear(); // 常に一度クリア（これで「同時に1個」を実現）

        // 解答欄外やドラッグ中でない場合は何もしない
        if (array_flag2 !== 4 && array_flag !== 4) return;

        var labels = Mylabels_ea;
        var droppedRegion = YAHOO.util.Dom.getRegion(draggedLabel);
        var snapDistanceY = 30;

        var nearestLabel = null;
        var minDiff = 99999;

        // 1. 最も近い単語を探す（snapToNearestWordと同じロジック）
        for (var i = 0; i < labels.length; i++) {
            var target = labels[i];
            if (target.id === draggedLabel.id) continue; // 自分自身は無視

            // ドラッグ中のグループに含まれる単語も無視
            if (MyControls.length > 0) {
                var isSelfGroup = false;
                for (var k = 0; k < MyControls.length; k++) {
                    if (MyControls[k].id === target.id) isSelfGroup = true;
                }
                if (isSelfGroup) continue;
            }

            var targetRegion = YAHOO.util.Dom.getRegion(target);
            var diffY = Math.abs(droppedRegion.top - targetRegion.top);

            if (diffY < snapDistanceY) {
                var distToRight = Math.abs(droppedRegion.left - targetRegion.right);
                var distToLeft = Math.abs(droppedRegion.right - targetRegion.left);
                var closestXDist = Math.min(distToRight, distToLeft);

                // 吸着範囲（50px）に入っているか
                if (closestXDist < 50 && closestXDist < minDiff) {
                    minDiff = closestXDist;
                    nearestLabel = target;
                }
            }
        }

        // 2. 近い単語が見つかったら、その「グループ全体」を囲む
        if (nearestLabel) {
            var bounds = getGroupBounds(nearestLabel);

            // 赤枠の描画（少し余白を持たせる）
            var padding = 5;
            BPenTarget.drawRect(
                bounds.left - padding,
                bounds.top - padding,
                (bounds.right - bounds.left) + (padding * 2),
                (bounds.bottom - bounds.top) + (padding * 2)
            );
            BPenTarget.paint();
        }
    }

    // グループ範囲特定関数（隙間対応版）
    function getGroupBounds(targetLabel) {
        var staticLabels = [];
        var draggedIds = {};
        for (var i = 0; i < MyControls.length; i++) draggedIds[MyControls[i].id] = true;

        for (var i = 0; i < Mylabels_ea.length; i++) {
            if (Mylabels_ea[i] && !draggedIds[Mylabels_ea[i].id]) {
                staticLabels.push(Mylabels_ea[i]);
            }
        }

        staticLabels.sort(function(a, b) {
            var rA = YAHOO.util.Dom.getRegion(a);
            var rB = YAHOO.util.Dom.getRegion(b);
            if (Math.abs(rA.top - rB.top) > 15) return rA.top - rB.top;
            return rA.left - rB.left;
        });

        var groupGapLimit = 25;
        var currentGroup = [];
        var targetGroup = null;

        // グループ分け処理
        for (var i = 0; i < staticLabels.length; i++) {
            var label = staticLabels[i];
            var region = YAHOO.util.Dom.getRegion(label);

            if (currentGroup.length === 0) {
                currentGroup.push({
                    label: label,
                    region: region
                });
            } else {
                var prev = currentGroup[currentGroup.length - 1];
                var isSameLine = Math.abs(region.top - prev.region.top) < 15;
                var isCloseEnough = (region.left - prev.region.right) < groupGapLimit;

                if (isSameLine && isCloseEnough) {
                    currentGroup.push({
                        label: label,
                        region: region
                    });
                } else {
                    // グループ確定時にターゲットが含まれているかチェック
                    for (var k = 0; k < currentGroup.length; k++) {
                        if (currentGroup[k].label.id === targetLabel.id) {
                            targetGroup = currentGroup;
                            break;
                        }
                    }
                    if (targetGroup) break;
                    currentGroup = [{
                        label: label,
                        region: region
                    }];
                }
            }
        }

        if (!targetGroup) {
            for (var k = 0; k < currentGroup.length; k++) {
                if (currentGroup[k].label.id === targetLabel.id) {
                    targetGroup = currentGroup;
                    break;
                }
            }
        }

        if (!targetGroup) {
            var r = YAHOO.util.Dom.getRegion(targetLabel);
            return {
                left: r.left,
                top: r.top,
                right: r.right,
                bottom: r.bottom
            };
        }

        var minX = 99999,
            minY = 99999,
            maxX = -99999,
            maxY = -99999;
        for (var i = 0; i < targetGroup.length; i++) {
            var r = targetGroup[i].region;
            if (r.left < minX) minX = r.left;
            if (r.top < minY) minY = r.top;
            if (r.right > maxX) maxX = r.right;
            if (r.bottom > maxY) maxY = r.bottom;
        }

        return {
            left: minX,
            top: minY,
            right: maxX,
            bottom: maxY
        };
    }

    // ======================= ▼▼▼ 新規追加: オーバーフロー予測関数 ▼▼▼ =======================
    function predictOverflow(sender) {
        var limitRight = 812; // 解答欄の右端座標
        var gap = 15; // 単語間の隙間
        var snapDist = 50; // 吸着判定距離

        // 1. ドラッグしている対象（単体またはグループ）の総幅を計算
        var targets = (MyControls.length > 0) ? MyControls : [sender];
        var minX = 99999,
            maxX = -99999;
        for (var i = 0; i < targets.length; i++) {
            var r = YAHOO.util.Dom.getRegion(targets[i]);
            if (r.left < minX) minX = r.left;
            if (r.right > maxX) maxX = r.right;
        }
        var dragWidth = maxX - minX;

        // 現在のマウス位置（ドロップ位置）の基準
        var senderRegion = YAHOO.util.Dom.getRegion(sender);

        // 2. 【判定A】既存の単語の「右側」に吸着する場合のチェック
        // snapToNearestWord と同様の判定を行い、はみ出しを予測
        var labels = Mylabels_ea;
        var draggedIds = {};
        for (var i = 0; i < targets.length; i++) draggedIds[targets[i].id] = true;

        for (var i = 0; i < labels.length; i++) {
            var target = labels[i];
            if (draggedIds[target.id]) continue; // 自分自身は除外

            var tR = YAHOO.util.Dom.getRegion(target);

            // Y軸（行）が合っているか
            if (Math.abs(senderRegion.top - tR.top) > 30) continue;

            // X軸: ターゲットの「右側」に吸着する距離にあるか？
            // (ドロップ位置の左端 - ターゲットの右端) の距離をチェック
            var distToRight = Math.abs(senderRegion.left - tR.right);

            // 吸着範囲内、かつ ドロップ位置がターゲットより右側（あるいはほぼ真上）の場合
            if (distToRight < snapDist) {
                // 吸着後の「予想される右端」を計算
                // ターゲットの右端 + 隙間 + ドラッグしている幅
                var futureRightEdge = tR.right + gap + dragWidth;

                if (futureRightEdge > limitRight) {
                    return true; // はみ出し確定
                }
            }
        }

        // 3. 【判定B】既存のグループの間に「挿入」する場合のチェック
        // getTargetInfo（挿入モード）が発動する場合、グループ全体が右に伸びるためチェックが必要
        var ex = (typeof event !== 'undefined') ? event.x : 0;
        var ey = (typeof event !== 'undefined') ? event.y : 0;

        // ドラッグ対象を除外して挿入判定
        var info = getTargetInfo(ex, ey, targets);

        if (info.mode === "insert") {
            // 挿入対象となるグループの、現在の「最も右にある端」を取得
            var clusterMaxX = -9999;
            for (var k = 0; k < info.targets.length; k++) {
                var r = YAHOO.util.Dom.getRegion(info.targets[k]);
                if (r.right > clusterMaxX) clusterMaxX = r.right;
            }

            // 挿入によってグループの幅が「ドラッグ幅 + 隙間」ぶん増えるため、それを加算して判定
            if (clusterMaxX + gap + dragWidth > limitRight) {
                return true; // はみ出し確定
            }
        }

        return false; // はみ出しなし
    }
    // ======================= ▲▲▲ 追加ここまで ▲▲▲ =======================

    //★★ラベルを離した時の作業。問題文の形を変えたりいろいろ
    function MyLabels_MouseUp(sender) {
        // ▼▼▼ 追加: ドロップしたら赤枠は消す ▼▼▼
        if (typeof BPenTarget !== 'undefined') {
            BPenTarget.clear();
        }
        // ▲▲▲ 追加ここまで ▲▲▲
        //枠の色リセット
        document.getElementById("question").style.borderColor = "black";
        document.getElementById("answer").style.borderColor = "black";
        var mylabelarray2 = new Array();
        var isOverflow = false; // ★追加: オーバーフロー（はみ出し）判定用フラグ
        // ======================= ▼▼▼ 修正箇所 ▼▼▼ =======================
        // イベントが起こった座標判定
        // 解答欄の範囲 (12 <= x <= 812 かつ 160 <= y <= 550) の場合のみ配置許可
        // それ以外（問題提示欄含む、解答欄外すべて）は、初期位置に戻す判定(0)にする
        if (event.x >= 40 && event.x <= 812 && event.y >= 160 && event.y <= 550) {
            if (predictOverflow(sender)) {
                array_flag2 = 0; // はみ出すので問題提示欄へ強制送還
                isOverflow = true; // ★追加: はみ出し発生としてマーク
            } else {
                array_flag2 = 4; // 解答欄へ配置OK
            }
        } else {
            array_flag2 = 0; // 問題提示欄（リセット処理）へ強制送還
            isOverflow = true;
        }
        // ======================= ▲▲▲ 修正箇所 ▲▲▲ =======================

        if (array_flag2 == 0) {
            mylabelarray2 = Mylabels.slice(0);
        } else if (array_flag2 == 4) {
            mylabelarray2 = Mylabels_ea.slice(0);
            // ▼▼▼ 追加: 吸着と下線描画処理 ▼▼▼

            // 1. 単語が解答欄にドロップされた場合、近くの単語に吸着させる
            snapToNearestWord(sender);

            // 2. 吸着後の位置情報を反映させるために配列情報を更新などは不要（DOM操作済みのため）
            // ただし、もしグループドラッグ(MyControls)があった場合は、それら全てに対して吸着判定を行う必要があります。
            // 今回はシンプルに sender (ドラッグした要素) に対して行います。

            // 3. グループ下線を再描画
            drawGroupUnderlines();

            // ▲▲▲ 追加ここまで ▲▲▲

            // ★ここでプレビューを更新！
            updateAnswerPreview();
        } else {
            IsDragging = false;
        }
        if (IsDragging != true) {
            return;
        }
        var hLabel = sender;

        //グループ化関係の処理
        for (i = 0; i <= MyControls.length - 1; i++) {
            //YAHOO.util.Dom.setStyle(MyControls[i], "text-decoration", "none");
            YAHOO.util.Dom.setStyle(MyControls[i], "background-color", "transparent");
        }
        //YAHOO.util.Dom.setStyle(hLabel, "text-decoration", "none");
        YAHOO.util.Dom.setStyle(hLabel, "background-color", "transparent");
        draw3();

        var Dpos = 0;
        var P = new Point(0, 0);
        var hl = YAHOO.util.Dom.getRegion(hLabel);
        P.x = hl.left;
        P.y = hl.top;
        mylabelarray2 = MyLabelSort(sender, event.x, event.y);

        DPos = 0;
        IsDragging = false;

        //▼マウスデータの取得
        myStop = new Date();
        mTime = myStop.getTime() - myStart.getTime();
        $Mouse_Data["WID"] = WID;
        $Mouse_Data["Time"] = mTime;
        $Mouse_Data["X"] = P.x;
        $Mouse_Data["Y"] = P.y;
        $Mouse_Data["DragDrop"] = 1;
        $Mouse_Data["DropPos"] = DPos;
        $Mouse_Data["hlabel"] = "";
        $Mouse_Data["Label"] = "";
        Mouse_Num += 1;

        // --- ▼▼▼ 追加: 7つの新カラム用データ生成 ▼▼▼ ---
        var val_stick = "";
        var val_stick_count = "";
        var val_repel = "";
        var val_repel_count = "";
        var val_back = "";
        var val_back_count = "";
        var val_norder = "";

        // 対象となる単語ID群を取得（単体またはグループ）
        var targetIDs = [];
        if (MyControls.length > 0) {
            for (var i = 0; i < MyControls.length; i++) targetIDs.push(MyControls[i].id);
        } else {
            targetIDs.push(sender.id);
        }
        var targetIDStr = targetIDs.join("#");

        // 1. Back判定 (解答欄(4)から問題提示欄(0)へ戻された場合)
        if ((array_flag == 4 && array_flag2 == 0) || isOverflow) {
            val_back = targetIDStr;
            val_back_count = "1";
        }

        // 2. 解答欄内(4)での処理 (Stick, NOrder)
        if (array_flag2 == 4) {
            // A. NOrder (現在の順番)
            var sortedList = getSortedAnswerLabels();
            var orderList = [];
            for (var i = 0; i < targetIDs.length; i++) {
                for (var k = 0; k < sortedList.length; k++) {
                    if (sortedList[k].id == targetIDs[i]) {
                        orderList.push(k + 1); // 1から始まる番号
                        break;
                    }
                }
            }
            val_norder = orderList.join("#");

            // B. Stick (グループ化判定)
            // 現在のグループ構成を取得し、自分が含まれるグループが「複数単語」か確認
            var groups = getAnswerGroups(25, false);
            for (var g = 0; g < groups.length; g++) {
                var members = groups[g].members;
                var memberIds = [];
                var isHit = false;
                for (var m = 0; m < members.length; m++) {
                    memberIds.push(members[m].id);
                    // 自分のIDが含まれているか
                    if (targetIDs.indexOf(members[m].id) !== -1) {
                        isHit = true;
                    }
                }
                // 自分を含み、かつ2単語以上のグループであれば「くっついている」とみなす
                if (isHit && memberIds.length > 1) {
                    val_stick = memberIds.join("#");
                    val_stick_count = "1";
                    break;
                }
            }

            // C. Repel (はじかれ判定)
            if (GlobalRepelFlag === true) {
                val_repel = targetIDStr; // はじかれた単語ID（グループなら連結済み）
                val_repel_count = "1";
                GlobalRepelFlag = false; // フラグを戻す
            }
        }
        // --- ▲▲▲ 追加ここまで ▲▲▲ ---

        // ★calculateStickParamsに操作対象のIDを渡すことで、そのグループの情報を正確に取得
        var stickInfo = calculateStickParams(false, targetIDs);

        // ▼▼▼ 新規追加: word_now の計算 ▼▼▼
        var val_word_now = "";
        // 解答欄(4)での操作であれば常に現在の単一単語数をカウントして記録する
        if (array_flag2 == 4) {
            val_word_now = getSingleWordCount();
        }

        var $params = 'param1=' + encodeURIComponent($Mouse_Data["WID"]) +
            '&param2=' + encodeURIComponent($Mouse_Data["Time"]) +
            '&param3=' + encodeURIComponent($Mouse_Data["X"]) +
            '&param4=' + encodeURIComponent($Mouse_Data["Y"]) +
            '&param5=' + encodeURIComponent($Mouse_Data["DragDrop"]) +
            '&param6=' + encodeURIComponent($Mouse_Data["DropPos"]) +
            '&param7=' + encodeURIComponent($Mouse_Data["hlabel"]) +
            '&param8=' + encodeURIComponent($Mouse_Data["Label"]) +
            // 既存 stick (9-13)
            '&param9=' + encodeURIComponent(val_stick) +
            '&param10=' + encodeURIComponent(val_stick_count) +
            '&param11=' + encodeURIComponent(stickInfo.now) +
            '&param12=' + encodeURIComponent(stickInfo.num1) +
            '&param13=' + encodeURIComponent(stickInfo.num2) +
            // ▼▼▼ 新規追加 (14-16) ▼▼▼
            '&param14=' + encodeURIComponent(stickInfo.same) + // stick_number_same
            '&param15=' + encodeURIComponent(stickInfo.count) + // stick_composition_count
            '&param16=' + encodeURIComponent(val_word_now) + // word_now
            // ▼▼▼ シフト分 (17-21) ▼▼▼
            '&param17=' + encodeURIComponent(val_repel) + // 旧param14
            '&param18=' + encodeURIComponent(val_repel_count) + // 旧param15
            '&param19=' + encodeURIComponent(val_back) + // 旧param16
            '&param20=' + encodeURIComponent(val_back_count) + // 旧param17
            '&param21=' + encodeURIComponent(val_norder) + // 旧param18
            '&param22=' + encodeURIComponent(stickInfo.leftX) +
            '&param23=' + encodeURIComponent(stickInfo.rightX) +
            '&param24=' + encodeURIComponent(stickInfo.topY) +
            '&param25=' + encodeURIComponent(stickInfo.incorrect) +
            '&param26=' + encodeURIComponent(stickInfo.incorrectNow) + // incorrect_stick_now
            '&param27=' + encodeURIComponent(stickInfo.stickMove) + // stick_move
            '&param28=' + encodeURIComponent(stickInfo.stickSame) + // stick_same
            '&lang=' + encodeURIComponent(testLangType);

        new Ajax.Request(URL + 'tmpfile.php', {
            method: 'get',
            onSuccess: getA,
            onFailure: getE,
            parameters: $params
        });
        //▲マウスデータの取得
        //ドラッグ開始地点の保存
        function getA(req) {
            document.getElementById("msg").innerHTML = req.responseText;
        }

        function getE(req) {
            alert("失敗f");
        }

        if (array_flag2 == 0) {
            Mylabels = mylabelarray2.slice(0);
        } else if (array_flag2 == 4) {
            Mylabels_ea = mylabelarray2.slice(0);
            // 1. 吸着処理（ドロップした瞬間に位置を補正）
            snapToNearestWord(sender);

            // 2. 下線描画（位置補正の直後に必ず実行）
            drawGroupUnderlines();

            // 3. プレビュー更新
            updateAnswerPreview();
        }

    }
    //★★マウスでラベルをドラッグ中。動かしてるときだからここで挿入線をアレしたりコレしたり
    function MyLabels_MouseMove(sender, e) {
        if (IsDragging != true) {
            return;
        }
        // ▼▼▼ 座標取得処理を追加 ▼▼▼
        var ey = 0;
        var ex = 0;
        if (e) {
            // YUIイベント、または標準イベントからページY座標を取得
            ey = (typeof e.pageY !== 'undefined') ? e.pageY : e.clientY;
            ex = (typeof event !== 'undefined') ? event.x : e.clientX;
        } else if (typeof event !== 'undefined') {
            // IEなどの古い仕様へのフォールバック
            ey = event.y;
            ex = event.x;
        }

        var hLabel = sender;

        //グループ化ラベルを動かすときの処理。何故動いているか不明。
        var GroupMem = 0;
        hl1 = YAHOO.util.Dom.getRegion(hLabel);
        for (i = 0; i <= MyControls.length - 1; i++) {
            var mcl = YAHOO.util.Dom.getRegion(MyControls[i]);
            if (hl1.left == mcl.left && hl1.top == mcl.top) {
                GroupMem = i //今どのラベルを動かしてるかを記憶（グループ化ラベル）
                break;
            }
        }
        //グループ化ラベルの位置を決定
        for (j = 0; j <= MyControls.length - 1; j++) {
            //ドラッグラベルの左側の位置を決定(hLabelの左側をはじめに決定、それ以降は減算により位置を決定していく)
            if (j < GroupMem) {
                var mcl1 = YAHOO.util.Dom.getRegion(MyControls[GroupMem - 1]);
                YAHOO.util.Dom.setX(MyControls[GroupMem - 1], hl1.left - (mcl1.right - mcl1.left) - 10); //解像度
                YAHOO.util.Dom.setY(MyControls[GroupMem - 1], hl1.top);
                for (k = GroupMem - 1; k >= 0; k--) {
                    var mcl2 = YAHOO.util.Dom.getRegion(MyControls[k + 1]);
                    var mcl3 = YAHOO.util.Dom.getRegion(MyControls[k]);
                    YAHOO.util.Dom.setX(MyControls[k], mcl2.left - (mcl3.right - mcl3.left) - 10); //解像度の影響本来は10
                    YAHOO.util.Dom.setY(MyControls[k], mcl2.top);
                }
                j = GroupMem;
            } else if (j == GroupMem) {
                YAHOO.util.Dom.setX(MyControls[j], hl1.left);
                YAHOO.util.Dom.setY(MyControls[j], hl1.top);
            } else if (j > GroupMem) {
                //ドラッグラベルの右側の位置を決定
                var mclj = YAHOO.util.Dom.getRegion(MyControls[j - 1]);
                YAHOO.util.Dom.setX(MyControls[j], mclj.right + 10); //
                YAHOO.util.Dom.setY(MyControls[j], mclj.top);
            }
        }

        // ▼▼▼ 追加: スナップ候補の赤枠表示処理 ▼▼▼
        if (IsDragging && typeof highlightSnapTarget === 'function') {
            // ドラッグしている要素(sender)をもとに判定
            highlightSnapTarget(sender);
        }
        // ▲▲▲ 追加ここまで ▲▲▲

        var line_flag = -1;
        var line_array = new Array();
        var line_x = 0;
        var line_y = 0;
        var line_y2 = 0;
        var lstart_x = 0;
        var lstart_y = 0;
        //枠の色リセット
        document.getElementById("question").style.borderColor = "black";
        document.getElementById("answer").style.borderColor = "black";
        //挿入線関係。まずy座標でどこに挿入線を引くか判定
        // ▼▼▼ event.y を ey に書き換え ▼▼▼
        if (ey < 160) {
            line_flag = 0;
        } else if (ey <= 550 && ey >= 160) {
            line_flag = 4;
        }
        if (line_flag == 4) {
            document.getElementById("answer").style.borderColor = "red";

            // ドラッグ中の対象（単体またはグループ）を特定
            var dragTargets = (MyControls.length > 0) ? MyControls : [sender];

            // 判定関数を呼び出し
            var info = getTargetInfo(ex, ey, dragTargets);

            if (info.mode === "insert") {
                // 挿入モードなら線を引く
                draw3();
                draw2(info.lineX, info.refY, info.refY + 25); // 縦線を少し長く

                // ★                // 補足: 視覚的に分かりやすくするため、線の位置を赤枠表示と連動させても良いですが
                // 今回は縦線のみを表示します。
            } else {
                // 自由配置モードなら線は消す
                draw3();
            }

            // スナップ候補の赤枠表示（既存機能）
            if (typeof highlightSnapTarget === 'function') {
                highlightSnapTarget(sender);
            }

        } else if (line_flag == 0) {
            document.getElementById("question").style.borderColor = "red";
            draw3();
        } else {
            document.getElementById("answer").style.borderColor = "black";
            document.getElementById("question").style.borderColor = "black";
            draw3();
        }

        if (MV == true) {
            draw();
            ePos.x = event.x + cx;
            ePos.y = event.y + cy;
        }
    }

    // 挿入位置計算用関数（クラスター判定・距離制限版）
    // 挿入位置計算用関数（修正版：ドラッグ領域考慮）
    // 挿入位置計算用関数（クラスター判定・距離制限版）
    function getTargetInfo(mouseX, mouseY, draggedLabels) {
        // 1. 静的単語の抽出
        var staticLabels = [];
        var draggedIds = {};
        for (var i = 0; i < draggedLabels.length; i++) draggedIds[draggedLabels[i].id] = true;

        for (var i = 0; i < Mylabels_ea.length; i++) {
            if (Mylabels_ea[i] && !draggedIds[Mylabels_ea[i].id]) {
                staticLabels.push(Mylabels_ea[i]);
            }
        }

        // 2. 同じ高さ（行）にある単語だけを抽出
        var rangeY = 30;
        var rowLabels = [];
        for (var i = 0; i < staticLabels.length; i++) {
            var r = YAHOO.util.Dom.getRegion(staticLabels[i]);
            var centerY = (r.top + r.bottom) / 2;
            if (Math.abs(mouseY - centerY) < rangeY) {
                rowLabels.push(staticLabels[i]);
            }
        }

        if (rowLabels.length === 0) return {
            mode: "free"
        };

        // 3. X座標でソート
        rowLabels.sort(function(a, b) {
            return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
        });

        // 4. クラスター（塊）ごとに分解する
        var clusters = [];
        var currentCluster = [];
        var gapLimit = 25; // ★ここが赤枠や下線の判定と同じ距離

        for (var i = 0; i < rowLabels.length; i++) {
            var r = YAHOO.util.Dom.getRegion(rowLabels[i]);
            if (currentCluster.length === 0) {
                currentCluster.push(rowLabels[i]);
            } else {
                var prevR = YAHOO.util.Dom.getRegion(currentCluster[currentCluster.length - 1]);
                if (r.left - prevR.right < gapLimit) {
                    currentCluster.push(rowLabels[i]);
                } else {
                    clusters.push(currentCluster);
                    currentCluster = [rowLabels[i]];
                }
            }
        }
        if (currentCluster.length > 0) clusters.push(currentCluster);

        // 5. マウスに最も近いクラスターを探す
        var bestCluster = null;
        var minDistance = 99999;
        var snapRange = 50; // ★縦線を表示する限界距離

        for (var i = 0; i < clusters.length; i++) {
            // クラスターの左端・右端を取得
            var firstR = YAHOO.util.Dom.getRegion(clusters[i][0]);
            var lastR = YAHOO.util.Dom.getRegion(clusters[i][clusters[i].length - 1]);

            // マウスがこのクラスターの有効範囲（左右+50px）に入っているか？
            if (mouseX >= firstR.left - snapRange && mouseX <= lastR.right + snapRange) {
                // 中心からの距離などで優先度を決める（今回は範囲内なら採用）
                var dist = Math.min(Math.abs(mouseX - firstR.left), Math.abs(mouseX - lastR.right));
                // 中に入っている場合は距離0
                if (mouseX > firstR.left && mouseX < lastR.right) dist = 0;

                if (dist < minDistance) {
                    minDistance = dist;
                    bestCluster = clusters[i];
                }
            }
        }

        // 近くのクラスターがなければ自由配置
        if (!bestCluster) return {
            mode: "free"
        };

        // 6. そのクラスター内での挿入位置を決定
        var targetLabels = bestCluster;
        var insertIndex = targetLabels.length;
        var lineX = -1;
        var gap = 15;

        var firstR = YAHOO.util.Dom.getRegion(targetLabels[0]);
        var firstCenter = (firstR.left + firstR.right) / 2;
        if (mouseX < firstCenter) {
            insertIndex = 0;
            lineX = firstR.left - (gap / 2);
        } else {
            for (var i = 0; i < targetLabels.length; i++) {
                var currentR = YAHOO.util.Dom.getRegion(targetLabels[i]);
                if (i === targetLabels.length - 1) {
                    if (mouseX >= currentR.left) {
                        insertIndex = targetLabels.length;
                        lineX = currentR.right + (gap / 2);
                    }
                } else {
                    var nextR = YAHOO.util.Dom.getRegion(targetLabels[i + 1]);
                    var currentCenter = (currentR.left + currentR.right) / 2;
                    var nextCenter = (nextR.left + nextR.right) / 2;
                    if (mouseX >= currentCenter && mouseX < nextCenter) {
                        insertIndex = i + 1;
                        lineX = (currentR.right + nextR.left) / 2;
                        break;
                    }
                }
            }
        }

        var refY = YAHOO.util.Dom.getRegion(targetLabels[0]).top;

        return {
            mode: "insert",
            index: insertIndex,
            lineX: lineX,
            refY: refY,
            targets: targetLabels // ★ここには「近くの塊」だけが入る
        };
    }
    //挿入線の描画
    function draw2(x, y1, y2) {
        BPen2.drawLine(x + cx, y1 + cy, x + cx, y2 + cy);
        BPen2.paint();
        checkl = x;
    }

    function draw3() {
        BPen2.clear();
    }

    //○○終了ボタン主に一時ファイルの書き込み処理
    function LineQuestioneForm_Closing() {
        //▲マウスデータの取得
        alert(<?= json_encode(translate('ques.php_919行目_お疲れ様です')) ?>);
        new Ajax.Request(URL + 'ewrite.php', {
            method: 'get',
            onSuccess: getA,
            onFailure: getE
        });
        //▲マウスデータの取得
        //ドラッグ開始地点の保存
        function getA(req) {
            mybutton = 0;
            console.log(req.responseText);
            alert(req.responseText);
            // ▼▼▼ 修正 ▼▼▼
            document.location = "result.php?Qid=" + Qid + "&lang=" + currentLang;
            window.close();
        }

        function getE(req) {
            alert(<?= json_encode(translate('ques.php_929行目_書き込み失敗')) ?>);
        }
    }

    // ▼▼▼ 【新規追加】塊（グループ）認識・ソート用共通関数 ▼▼▼

    // 解答欄の単語を「塊」ごとにグループ化する関数
    function getAnswerGroups(gapLimit, ignoreDragged) {
        // 1. 対象ラベルの抽出
        var targets = [];
        var draggedIds = {};

        // ドラッグ中を除外する場合の処理
        if (ignoreDragged && typeof MyControls !== 'undefined') {
            for (var i = 0; i < MyControls.length; i++) draggedIds[MyControls[i].id] = true;
            if (typeof DragL !== 'undefined' && DragL) draggedIds[DragL.id] = true;
        }

        for (var i = 0; i < Mylabels_ea.length; i++) {
            var lbl = Mylabels_ea[i];
            if (lbl && !draggedIds[lbl.id]) {
                targets.push(lbl);
            }
        }

        // 2. まずは視覚的な読み順（上から下、左から右）でソートして整列させる
        targets.sort(function(a, b) {
            var rA = YAHOO.util.Dom.getRegion(a);
            var rB = YAHOO.util.Dom.getRegion(b);
            // 高さのズレが20px未満なら同じ行とみなす
            if (Math.abs(rA.top - rB.top) > 20) {
                return rA.top - rB.top;
            }
            return rA.left - rB.left;
        });

        // 3. 距離が近いものをグループ化
        var groups = [];
        if (targets.length > 0) {
            var currentMembers = [targets[0]];
            // グループの左端座標（最初の単語の左端）
            var rFirst = YAHOO.util.Dom.getRegion(targets[0]);
            var currentLeft = rFirst.left;

            for (var i = 1; i < targets.length; i++) {
                var prev = targets[i - 1];
                var curr = targets[i];
                var rPrev = YAHOO.util.Dom.getRegion(prev);
                var rCurr = YAHOO.util.Dom.getRegion(curr);

                var isSameLine = Math.abs(rCurr.top - rPrev.top) < 20;
                var isClose = (rCurr.left - rPrev.right) < gapLimit;

                if (isSameLine && isClose) {
                    currentMembers.push(curr);
                } else {
                    groups.push({
                        members: currentMembers,
                        left: currentLeft
                    });
                    currentMembers = [curr];
                    currentLeft = rCurr.left;
                }
            }
            groups.push({
                members: currentMembers,
                left: currentLeft
            });
        }
        return groups;
    }

    // グループの左端座標に基づいて並び替えた単語リストを取得する関数
    function getSortedAnswerLabels() {
        // 1. グループ化（しきい値25px）
        var groups = getAnswerGroups(25, false);

        // 2. グループの「左端（left）」を比較して、小さい順（左→右）に並べ替え
        // これにより、行が違っても「左にある塊」から順に並びます
        groups.sort(function(a, b) {
            return a.left - b.left;
        });

        // 3. フラットな配列に戻す
        var sortedList = [];
        for (var i = 0; i < groups.length; i++) {
            var members = groups[i].members;
            for (var j = 0; j < members.length; j++) {
                sortedList.push(members[j]);
            }
        }
        return sortedList;
    }
    // ▲▲▲ 追加ここまで ▲▲▲

    //○○決定ボタン
    function Button1_Click() {
        //終了条件チェック
        if (Mylabels_ea.length != Mylabels2.length) {
            alert(<?= json_encode(translate('ques.php_778行目_まだ並べ替えが終了していません')) ?>);
            return;
        }

        //固定ラベルチェック
        for (i = 0; i < FixNum.length; i++) {
            var fixcheck = 0;
            fixcheck = Mylabels_ea[FixNum[i]].innerHTML;
            if (FixLabels[i] != fixcheck) {
                var fixnum2 = FixNum[i] + 1
                var confirm_msg = <?= json_encode(translate('ques.php_783行目_位置固定単語である')) ?> + FixLabels[i] + <?= json_encode(translate('ques.php_783行目_が')) ?> + fixnum2 + <?= json_encode(translate('ques.php_783行目_番目に来ていませんがよろしいですか')) ?>;
                var fix_a = confirm(confirm_msg);
                if (fix_a == true) {
                    continue;
                } else {
                    return;
                }
            }
        }

        YAHOO.util.Dom.setStyle("AnswerPreview", "display", "none");

        BPen3.clear();
        YAHOO.util.Dom.setStyle("Button1", "display", "none");
        document.getElementById("Button1").disabled = true;

        var P = new Point(0, 0);
        P.x = event.x;
        P.y = event.y;
        if (Mouse_Flag == false) {
            return;
        }
        Mouse_Flag = false;
        myStop = new Date();
        mTime = myStop.getTime() - myStart.getTime();

        myCheck(0); //ストップウォッチを止める

        //グループ化されたコントロールの初期化
        for (i = 0; i <= MyControls.length - 1; i++) {
            YAHOO.util.Dom.setStyle(Mylabels_ea[i], "color", "black");
        }
        //削除
        MyControls.splice(0, MyControls.length - 1);

        var $Mouse_Data = Mouse;
        $Mouse_Data["WID"] = WID;
        $Mouse_Data["Time"] = mTime;
        $Mouse_Data["X"] = P.x;
        $Mouse_Data["Y"] = P.y;
        $Mouse_Data["DragDrop"] = -1;
        $Mouse_Data["DropPos"] = -1;
        $Mouse_Data["hlabel"] = "";
        $Mouse_Data["Label"] = "";
        Mouse_Num += 1;

        var $params = 'param1=' + encodeURIComponent($Mouse_Data["WID"]) +
            '&param2=' + encodeURIComponent($Mouse_Data["Time"]) +
            '&param3=' + encodeURIComponent($Mouse_Data["X"]) +
            '&param4=' + encodeURIComponent($Mouse_Data["Y"]) +
            '&param5=' + encodeURIComponent($Mouse_Data["DragDrop"]) +
            '&param6=' + encodeURIComponent($Mouse_Data["DropPos"]) +
            '&param7=' + encodeURIComponent($Mouse_Data["hlabel"]) +
            '&param8=' + encodeURIComponent($Mouse_Data["Label"]) +
            '&lang=' + encodeURIComponent(testLangType);
        new Ajax.Request(URL + 'tmpfile.php', {
            method: 'get',
            onSuccess: getA,
            onFailure: getE,
            parameters: $params
        });
        //▲マウスデータの取得
        function getA(req) {}

        function getE(req) {
            alert("失敗g");
        }

        // ▼▼▼ 修正版: 共通関数を使用したソート処理 ▼▼▼

        // 共通関数 getSortedAnswerLabels を呼び出して、塊ごとの順序で並んだ配列を取得
        sorted_labels = getSortedAnswerLabels();

        // ソートされた順に単語を連結して解答文を作成
        MyAnswer = "";
        for (var i = 0; i < sorted_labels.length; i++) {
            // 区切りラベルは解答に入れない
            if (sorted_labels[i].innerHTML == "/") {
                continue;
            }
            MyAnswer += sorted_labels[i].innerHTML + " ";
        }
        MyAnswer = MyAnswer.trim(); // 前後の空白を削除

        // 英語テストの場合のみ、文頭の大文字化と文末の句読点追加を行う
        if (testLangType !== 'ja' && MyAnswer) {
            // 文頭を大文字に
            MyAnswer = MyAnswer.charAt(0).toUpperCase() + MyAnswer.slice(1);
            // 文末に句読点を追加
            MyAnswer += PorQ;
        }


        WriteAnswer = MyAnswer;
        $QAData["EndSentence"] = MyAnswer;
        ResAns += 1;
        AllResAns += 1;


        //単語単位迷い度取得の配列初期化・単語迷い度変数初期化
        $countHearing = [];
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            $countHearing[i] = 0;
        }
        $countH0 = 999999999;
        $countH1 = 999999999;
        $countH2 = 999999999;
        $countH3 = 999999999;
        $countH4 = 999999999;
        $countH5 = 999999999;
        $countH6 = 999999999;
        $countH7 = 999999999;
        $countH8 = 999999999;
        $countH9 = 999999999;
        $countH10 = 999999999;
        $countH11 = 999999999;
        $countH12 = 999999999;
        $countH13 = 999999999;
        $countH14 = 999999999;
        $countH15 = 999999999;
        $countH16 = 999999999;
        $countH17 = 999999999;
        $countH18 = 999999999;
        $countH19 = 999999999;
        $countH20 = 999999999;


        //例題の場合
        if (OID == -1) {
            print_answer();
            YAHOO.util.Dom.setStyle("TextBox1", "display", "none");
            YAHOO.util.Dom.setStyle("Label2", "display", "none");
            YAHOO.util.Dom.setStyle("Fixmsg", "display", "none");
            YAHOO.util.Dom.setStyle("ButtonE2", "display", "block");
            YAHOO.util.Dom.setStyle("register", "display", "none");
            YAHOO.util.Dom.setStyle("register1", "display", "none");
            YAHOO.util.Dom.setStyle("register2", "display", "none");
            YAHOO.util.Dom.setStyle("register3", "display", "none");
        } else {

            $QAData["comments"] = -1;
            $QAData["hesitate"] = -1;
            $QAData["hesitate1"] = -1;
            $QAData["hesitate2"] = -1;
            $QAData["check"] = 0;

            YAHOO.util.Dom.setStyle("hearing", "display", "block");
            YAHOO.util.Dom.setStyle("hearing2", "display", "block");
            YAHOO.util.Dom.setStyle("hearingT1", "display", "block");
            YAHOO.util.Dom.setStyle("hearingT2", "display", "block");
            YAHOO.util.Dom.setStyle("checkbox", "display", "block");
            YAHOO.util.Dom.setStyle("checkbox2", "display", "block");

            var HearingHtml = "";
            HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
            for (i = 0; i <= Mylabels2.length - 1; i++) {
                HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                    sorted_labels[i].innerHTML + "</label>";

            }
            HearingHtml += "</div><textarea id='comment' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
            document.getElementById("hearing").innerHTML = HearingHtml;

            YAHOO.util.Dom.setStyle("comments", "display", "block");
            YAHOO.util.Dom.setStyle("QuesLevel", "display", "none");
            YAHOO.util.Dom.setStyle("choose2", "display", "none");
            YAHOO.util.Dom.setStyle("TextBox1", "display", "none");
            YAHOO.util.Dom.setStyle("Label2", "display", "none");
            YAHOO.util.Dom.setStyle("Fixmsg", "display", "none");
            YAHOO.util.Dom.setStyle("ButtonM", "display", "none");
            YAHOO.util.Dom.setStyle("Button5", "display", "block");
            YAHOO.util.Dom.setStyle("register", "display", "none");
            YAHOO.util.Dom.setStyle("register1", "display", "none");
            YAHOO.util.Dom.setStyle("register2", "display", "none");
            YAHOO.util.Dom.setStyle("register3", "display", "none");

            document.getElementById("Button2").disabled = true;
            document.getElementById("Buttonl").disabled = true;

        }

        //決定を押した後にクリックできないように要素を見えなくする→解答欄のdivタグに追加して表示
        var answerBox = document.getElementById('answer');
        for (var i = 0; Mylabels_ea.length; i++) {
            Mylabels_ea[i].setAttribute('style', 'display:none;');
            var span = document.createElement('span');
            span.setAttribute('style', 'font-size:1.6em;');
            span.appendChild(document.createTextNode(Mylabels_ea[i].firstChild.nodeValue + ' '));
            answerBox.appendChild(span);
        }
    }

    //○○次の問題ボタン
    function Button2_Click() {
        var answerBox = document.getElementById('answer');
        jQuery(answerBox).empty();
        var previewBox = document.getElementById("AnswerPreview");
        if (previewBox) {
            previewBox.innerHTML = "";
            YAHOO.util.Dom.setStyle("AnswerPreview", "display", "block");
        }

        if (OID == nEnd) {
            alert(<?= json_encode(translate('ques.php_996行目_終了です')) ?>);
            return;
        } else if (OID % 5 == 0 && OID != 0) {
            var alert_msg = <?= json_encode(translate('ques.php_998行目_書き込みを行ってください')) ?> + QuesNum + <?= json_encode(translate('ques.php_998行目_問終了')) ?>;
            alert(alert_msg);
            return;
        }
        if (Mouse_Flag == true) {
            return;
        }

        for (i = 0; i <= LabelNum - 1; i++) {
            _delete_dom_obj(i);
        }
        for (i = 0; i >= -MyNums.length + 1; i--) {
            _delete_dom_obj(i);
        }
        MyNums.splice(0, MyNums.length - 1);
        StartQues = "";
        FixLabels = new Array(); //固定ラベル
        FixNum = new Array(); //固定ラベルの番号
        FixText = new Array(); //固定ラベルのタグを含むテキスト
        //ここで初期化類やっちゃおう
        Mylabels_r1.length = 0;
        Mylabels_r2.length = 0;
        Mylabels_r3.length = 0;
        Mylabels_ea.length = 0;
        Mylabels2.length = 0;
        Mylabels_left.length = 0;
        OID = (OID * 1) + 1;
        QuesNum = (OID * 1);
        MyAnswer = "";
        linedataFlg = false;
        document.getElementById("TermText").value = "";
        //document.getElementById("QuesLevel").options[0].selected = true;

        if (OID == 1) {
            YAHOO.util.Dom.setStyle("exercise", "display", "none");
            YAHOO.util.Dom.setStyle("ButtonE2", "display", "none");
            document.getElementById("Button1").disabled = false;
        } else {
            YAHOO.util.Dom.setStyle("Button2", "display", "none");
            YAHOO.util.Dom.setStyle("QuesLevel", "display", "none"); //悩み度
            YAHOO.util.Dom.setStyle("choose2", "display", "none");
            YAHOO.util.Dom.setStyle("QuesLabel", "display", "none");
            YAHOO.util.Dom.setStyle("TermText", "display", "none");
            YAHOO.util.Dom.setStyle("TermLabel", "display", "none");
            YAHOO.util.Dom.setStyle("OrderLabel", "display", "none");
            YAHOO.util.Dom.setStyle("Button4", "display", "none");
            YAHOO.util.Dom.setStyle("ButtonM", "display", "none");
            document.getElementById("Button4").disabled = true;
        }

        document.getElementById("number").innerHTML = "<b>" + QuesNum + "/" + nEnd + "<b>";
        YAHOO.util.Dom.setStyle("number", "display", "block");
        YAHOO.util.Dom.setStyle("Button1", "display", "block");
        YAHOO.util.Dom.setStyle("RichTextBox2", "display", "none"); //解答(下)
        YAHOO.util.Dom.setStyle("RichTextBox3", "display", "none"); //正誤
        YAHOO.util.Dom.setStyle("TextBox1", "display", "none"); //解答時間
        YAHOO.util.Dom.setStyle("Label2", "display", "block"); //説明
        YAHOO.util.Dom.setStyle("Fixmsg", "display", "block");
        YAHOO.util.Dom.setStyle("register", "display", "block");
        YAHOO.util.Dom.setStyle("register1", "display", "block");
        YAHOO.util.Dom.setStyle("register2", "display", "block");
        YAHOO.util.Dom.setStyle("register3", "display", "block");

        setques();
    }
    //解答ラベルの削除-----------------------------
    function _delete_dom_obj(id_name) {
        var dom_obj = document.getElementById(id_name);
        var dom_obj_parent = dom_obj.parentNode;
        dom_obj_parent.removeChild(dom_obj);
    }
    //スタート
    function Button3_Click() {
        if (OID > nEnd) {
            alert(<?= json_encode(translate('ques.php_1045行目_終了しています')) ?>);
            document.location = "result.php?Qid=" + Qid;
        }
        if (Mouse_Flag == true) {
            return;
        }

        /*
        20250108変更
        if (OID == 0) {
            YAHOO.util.Dom.setStyle("exercise", "display", "block");
        }else{
            YAHOO.util.Dom.setStyle("exercise", "display", "none");
            document.getElementById("number").innerHTML = "<b>" + QuesNum + "/" + nEnd + "<b>";
            YAHOO.util.Dom.setStyle("number", "display", "block");
            document.getElementById("Button4").disabled = true;
            YAHOO.util.Dom.setStyle("Button4", "display", "none");
        }
            */
        YAHOO.util.Dom.setStyle("exercise", "display", "none");
        document.getElementById("number").innerHTML = "<b>" + QuesNum + "/" + nEnd + "<b>";
        YAHOO.util.Dom.setStyle("number", "display", "block");
        document.getElementById("Button4").disabled = true;
        YAHOO.util.Dom.setStyle("Button4", "display", "none");

        document.getElementById("Button3").disabled = true;
        YAHOO.util.Dom.setStyle("Button3", "display", "none");
        YAHOO.util.Dom.setStyle("Button1", "display", "block");

        var previewBox = document.getElementById("AnswerPreview");
        if (previewBox) {
            previewBox.innerHTML = "";
            YAHOO.util.Dom.setStyle("AnswerPreview", "display", "block");
        }

        setques();

    }

    //迷い度決定
    function ButtonM_Click() {

        var cmbQues;

        //if ($QAData["hesitate"] == "" && $QAData["check"] == 0 ) {
        //cmbQues = document.getElementById("QuesLevel");
        //} else if ($QAData["hesitate"] == "" && $QAData["check"] == 1 ){
        //cmbQues = document.getElementById("QuesLevel3");
        //} else if ($QAData["hesitate"] == 0 || $QAData["hesitate"] == 1 || $QAData["hesitate"] == 2 || $QAData["hesitate"] == 3 || $QAData["hesitate"] == 4 || $QAData["hesitate"] == 5 || $QAData["hesitate"] == 6 || $QAData["hesitate"] == 7 || $QAData["hesitate"] == 8 || $QAData["hesitate"] == 9 || $QAData["hesitate"] == 10 || $QAData["hesitate"] == 11 || $QAData["hesitate"] == 12 ){
        //cmbQues = document.getElementById("QuesLevel2");
        //} else {
        //cmbQues = document.getElementById("QuesLevel3");
        //}

        if ($QAData["check"] === 1 || $QAData["hesitate2"] != "") {

            cmbQues = document.getElementById("QuesLevel3");
        } else {
            if ($QAData["hesitate"] === "") {

                cmbQues = document.getElementById("QuesLevel");
            } else if ($QAData["hesitate1"] != "") {
                cmbQues = document.getElementById("QuesLevel2");
            } else {

                cmbQues = document.getElementById("QuesLevel3");
            }
        }

        /*if ($QAData["hesitate"] == "" && $QAData["check"] == 0 ) {
        cmbQues = document.getElementById("QuesLevel");
        } else if ($QAData["check"] == 1 ){
        cmbQues = document.getElementById("QuesLevel3");
        } else if (($QAData["hesitate"] == 0 || $QAData["hesitate"] == 1 || $QAData["hesitate"] == 2 || $QAData["hesitate"] == 3 || $QAData["hesitate"] == 4 || $QAData["hesitate"] == 5 || $QAData["hesitate"] == 6 || $QAData["hesitate"] == 7 || $QAData["hesitate"] == 8 || $QAData["hesitate"] == 9 || $QAData["hesitate"] == 10 || $QAData["hesitate"] == 11 || $QAData["hesitate"] == 12 || $QAData["hesitate"] == 13 || $QAData["hesitate"] == 14 || $QAData["hesitate"] == 15 || $QAData["hesitate"] == 16 || $QAData["hesitate"] == 17 || $QAData["hesitate"] == 18 || $QAData["hesitate"] == 19 || $QAData["hesitate"] == 20 || $QAData["hesitate"] == 21) && $QAData["check"] == 0 ){
        cmbQues = document.getElementById("QuesLevel2");
        } else {
        cmbQues = document.getElementById("QuesLevel3");
        }*/


        $QAData["Understand"] = 5 - (cmbQues.selectedIndex * 1);

        if ($QAData["Understand"] == 5) {
            alert("迷い度が選択されていません");
            return;
        }

        var MyComments = document.getElementsByTagName("textarea");

        cmt = MyComments[0].value;
        if (cmt == "") cmt = "";

        $QAData["comments"] = cmt;



        document.getElementById("Button1").disabled = false;

        YAHOO.util.Dom.setStyle("QuesLevel", "display", "none");
        YAHOO.util.Dom.setStyle("QuesLevel2", "display", "none");
        YAHOO.util.Dom.setStyle("QuesLevel3", "display", "none");
        YAHOO.util.Dom.setStyle("choose2", "display", "none");
        YAHOO.util.Dom.setStyle("TermText", "display", "none");
        YAHOO.util.Dom.setStyle("TermLabel", "display", "none");
        YAHOO.util.Dom.setStyle("OrderLabel", "display", "none");
        YAHOO.util.Dom.setStyle("ButtonM", "display", "none");

        YAHOO.util.Dom.setStyle("hearing", "display", "none"); //自由記述欄修正
        YAHOO.util.Dom.setStyle("comments", "display", "none"); //自由記述欄修正
        YAHOO.util.Dom.setStyle("comments2", "display", "none"); //自由記述欄修正

        last();

    }

    //悩み度変更
    //  function QuesLevelChange() {
    //     var obj;
    //     obj = document.getElementById("QuesLevel");
    //     index = obj.selectedIndex;
    //     if (index != 0) {
    //             YAHOO.util.Dom.setStyle("ButtonM", "display", "block");
    //             document.getElementById("Button1").disabled = false;

    //     }
    // }

    //悩み度決定2
    // function ButtonM2_Click() {

    //    var obj;
    //    obj = document.getElementById("QuesLevel");
    //    index = obj.selectedIndex;
    //    if (index != 0) {
    //            YAHOO.util.Dom.setStyle("ButtonM", "display", "block");
    //            document.getElementById("Button1").disabled = false;

    //    }

    //     var cmbQues;
    //     cmbQues = document.getElementById("QuesLevel");

    //     $QAData["Understand"] = 5 - (cmbQues.selectedIndex * 1);

    //     YAHOO.util.Dom.setStyle("QuesLevel", "display", "none");
    //     YAHOO.util.Dom.setStyle("choose2", "display", "none");
    //     YAHOO.util.Dom.setStyle("TermText", "display", "none");
    //     YAHOO.util.Dom.setStyle("TermLabel", "display", "none");
    //     YAHOO.util.Dom.setStyle("OrderLabel", "display", "none");
    //     YAHOO.util.Dom.setStyle("ButtonM", "display", "none");

    //    last();

    //    }


    //正誤表示
    function print_answer() {
        var correctAnswer;
        var isCorrect = false;

        if (testLangType === 'ja') {
            // 【日本語テストの場合】
            correctAnswer = JapaneseAnswer;

            // ======================= ▼▼▼ ここからが修正箇所です ▼▼▼ =======================
            // 判定を確実にするため、ユーザーの解答と正解文から全ての空白(半角/全角)と句読点(。.)を取り除いてから比較する
            var userAnswerNormalized = MyAnswer.replace(/(\s|　|。|\.)/g, "");
            var correctAnswerNormalized = correctAnswer.replace(/(\s|　|。|\.)/g, "");
            isCorrect = (userAnswerNormalized == correctAnswerNormalized);
            // ======================= ▲▲▲ 修正はここまで ▲▲▲ =======================

        } else {
            // 【英語テストの場合】
            correctAnswer = Answer;
            isCorrect = (MyAnswer == correctAnswer || MyAnswer == Answer1 || MyAnswer == Answer2);
        }

        if (isCorrect) {
            document.getElementById("RichTextBox3").innerHTML = <?= json_encode(translate('ques.php_890行目_正誤O')) ?>;
            YAHOO.util.Dom.setStyle("RichTextBox3", "color", "red");
            TF = 1;
            document.getElementById("RichTextBox2").innerHTML = <?= json_encode(translate('ques.php_893行目_正解')) ?> + correctAnswer;
            YAHOO.util.Dom.setStyle("RichTextBox2", "display", "block");
            AllCorrectAns += 1;
        } else {
            document.getElementById("RichTextBox3").innerHTML = <?= json_encode(translate('ques.php_896行目_正誤X')) ?>;
            YAHOO.util.Dom.setStyle("RichTextBox3", "color", "blue");
            TF = 0;
            document.getElementById("RichTextBox2").innerHTML = <?= json_encode(translate('ques.php_893行目_正解')) ?> + correctAnswer;
            YAHOO.util.Dom.setStyle("RichTextBox2", "display", "block");
        }

        YAHOO.util.Dom.setStyle("RichTextBox3", "display", "block");
        YAHOO.util.Dom.setStyle("choose2", "display", "none");

        var myStoppers;
        var mTimers;
        myStoppers = new Date();
        mTimers = myStoppers.getTime() - myStart.getTime();

        $QAData["WID"] = WID;
        $QAData["Date"] = Answertime;
        $QAData["TF"] = TF;
        $QAData["Time"] = mTimers;
        $QAData["Qid"] = Qid;

        // ▼▼▼ 追加: 正解文をデータ配列に格納 ▼▼▼
        $QAData["CorrectAnswer"] = correctAnswer;

        var $params = 'param1=' + encodeURIComponent($QAData["WID"]) +
            '&param2=' + encodeURIComponent($QAData["Date"]) +
            '&param3=' + encodeURIComponent($QAData["TF"]) +
            '&param4=' + encodeURIComponent($QAData["Time"]) +
            '&param5=' + encodeURIComponent($QAData["Understand"]) +
            '&param6=' + encodeURIComponent($QAData["EndSentence"]) +
            '&param7=' + encodeURIComponent($QAData["hesitate"]) +
            '&param8=' + encodeURIComponent($QAData["hesitate1"]) +
            '&param9=' + encodeURIComponent($QAData["hesitate2"]) +
            '&param10=' + encodeURIComponent($QAData["comments"]) +
            '&param11=' + encodeURIComponent($QAData["check"]) +
            '&param12=' + encodeURIComponent($QAData["CorrectAnswer"]) +
            '&lang=' + encodeURIComponent(testLangType);

        if (!(linedataFlg)) {
            linedataFlg = true;
            new Ajax.Request(URL + 'tmpfile2.php', {
                method: 'get',
                onSuccess: getA,
                onFailure: getE,
                parameters: $params
            });

            function getA(req) {
                //ここでuser_progressを更新する
                $u = "u";
                $params = 'param1=' + encodeURIComponent(OID) +
                    '&param2=' + encodeURIComponent($u) +
                    '&lang=' + encodeURIComponent(testLangType);
                new Ajax.Request(URL + 'dbsyori.php', //本番用
                    {
                        method: 'get',
                        onSuccess: getwriteuser_progress,
                        onFailure: function(req) {
                            getError(req, "dbsyori.php")
                        },
                        parameters: $params
                    });
            }

            function getwriteuser_progress(req) {

            }

            function getE(req) {
                alert("失敗h");
            }
        }
        Mouse_Num = 0;
    }

    //最終処理
    function last() {

        print_answer();

        YAHOO.util.Dom.setStyle("TextBox1", "display", "block");
        if (OID % 5 != 0) {
            YAHOO.util.Dom.setStyle("Button2", "display", "block");
            document.getElementById("Button2").disabled = false;
        }
        YAHOO.util.Dom.setStyle("Button4", "display", "block");
        document.getElementById("Button4").disabled = false;
        //document.getElementById("Button2").disabled = false;
        document.check.checkbox.checked = false;

        if (OID == nEnd) {
            alert(<?= json_encode(translate('ques.php_1058行目_終了ですお疲れ様でした')) ?>);
            //▲マウスデータの取得
            alert(<?= json_encode(translate('ques.php_1062行目_採点を行います')) ?>);
            new Ajax.Request(URL + 'ewrite.php', {
                method: 'get',
                onSuccess: getA,
                onFailure: getE
            });
            //▲マウスデータの取得
            //ドラッグ開始地点の保存
            function getA(req) {
                mybutton = 0;
                alert(req.responseText);
                // ▼▼▼ 修正 ▼▼▼
                document.location = "result.php?Qid=" + Qid + "&lang=" + currentLang;
                //document.location = "result.php?Qid=" + Qid;
            }

            function getE(req) {
                alert(<?= json_encode(translate('ques.php_929行目_書き込み失敗')) ?>);
            }
            return;
        } else if (OID % 5 == 0) {
            var alert_msg = <?= json_encode(translate('ques.php_1074行目_書き込みを行ってください2')) ?> + QuesNum + <?= json_encode(translate('ques.php_998行目_問終了')) ?>;
            alert(alert_msg);
            return;
        }
    }


    //単語ごとの迷い度3段階
    /*$countHearing = [];
    for (i = 0; i <= Mylabels2.length - 1; i++){
        $countHearing[i] = 0;
    }*/
    $countH0 = 999999999;
    $countH0_3 = 0;

    function ButtonH0_Click() {
        //$countH = 0;
        $countH0--;
        $countHearing[0] = $countH0 % 3;
        //alert($countHearing[0]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH1 = 99999999;
    $countH1_3 = 0;

    function ButtonH1_Click() {
        //$countH = 0;
        $countH1--;
        $countHearing[1] = $countH1 % 3;
        //alert($countHearing[1]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH2 = 99999999;
    $countH2_3 = 0;

    function ButtonH2_Click() {
        //$countH = 0;
        $countH2--;
        $countHearing[2] = $countH2 % 3;
        //alert($countHearing[2]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH3 = 99999999;
    $countH3_3 = 0;

    function ButtonH3_Click() {
        //$countH = 0;
        $countH3--;
        $countHearing[3] = $countH3 % 3;
        //alert($countHearing[3]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH4 = 99999999;
    $countH4_3 = 0;

    function ButtonH4_Click() {
        //$countH = 0;
        $countH4--;
        $countHearing[4] = $countH4 % 3;
        //alert($countHearing[4]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH5 = 99999999;
    $countH5_3 = 0;

    function ButtonH5_Click() {
        //$countH = 0;
        $countH5--;
        $countHearing[5] = $countH5 % 3;
        //alert($countHearing[5]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH6 = 99999999;
    $countH6_3 = 0;

    function ButtonH6_Click() {
        //$countH = 0;
        $countH6--;
        $countHearing[6] = $countH6 % 3;
        //alert($countHearing[6]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH7 = 99999999;
    $countH7_3 = 0;

    function ButtonH7_Click() {
        //$countH = 0;
        $countH7--;
        $countHearing[7] = $countH7 % 3;
        //alert($countHearing[7]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH8 = 99999999;

    function ButtonH8_Click() {
        //$countH = 0;
        $countH8--;
        $countHearing[8] = $countH8 % 3;
        //alert($countHearing[8]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH9 = 99999999;

    function ButtonH9_Click() {
        //$countH = 0;
        $countH9--;
        $countHearing[9] = $countH9 % 3;
        //alert($countHearing[9]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH10 = 99999999;

    function ButtonH10_Click() {
        //$countH = 0;
        $countH10--;
        $countHearing[10] = $countH10 % 3;
        //alert($countHearing[10]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH11 = 99999999;

    function ButtonH11_Click() {
        //$countH = 0;
        $countH11--;
        $countHearing[11] = $countH11 % 3;
        //alert($countHearing[11]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH12 = 99999999;

    function ButtonH12_Click() {
        //$countH = 0;
        $countH12--;
        $countHearing[12] = $countH12 % 3;
        //alert($countHearing[12]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH13 = 99999999;

    function ButtonH13_Click() {
        //$countH = 0;
        $countH13--;
        $countHearing[13] = $countH13 % 3;
        //alert($countHearing[13]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH14 = 99999999;

    function ButtonH14_Click() {
        //$countH = 0;
        $countH14--;
        $countHearing[14] = $countH14 % 3;
        //alert($countHearing[14]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH15 = 99999999;

    function ButtonH15_Click() {
        //$countH = 0;
        $countH15--;
        $countHearing[15] = $countH15 % 3;
        //alert($countHearing[15]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH16 = 99999999;

    function ButtonH16_Click() {
        //$countH = 0;
        $countH16--;
        $countHearing[16] = $countH16 % 3;
        //alert($countHearing[16]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH17 = 99999999;

    function ButtonH17_Click() {
        //$countH = 0;
        $countH17--;
        $countHearing[17] = $countH17 % 3;
        //alert($countHearing[17]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH18 = 99999999;

    function ButtonH18_Click() {
        //$countH = 0;
        $countH18--;
        $countHearing[18] = $countH18 % 3;
        //alert($countHearing[18]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH19 = 99999999;

    function ButtonH19_Click() {
        //$countH = 0;
        $countH19--;
        $countHearing[19] = $countH19 % 3;
        //alert($countHearing[19]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }
    $countH20 = 99999999;

    function ButtonH20_Click() {
        //$countH = 0;
        $countH20--;
        $countHearing[20] = $countH20 % 3;
        //alert($countHearing[20]);
        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";
        for (i = 0; i <= Mylabels2.length - 1; i++) {
            HearingHtml += "<input name=\"HearingCheck\" id=\"select" + i + "\" value=\"" + i + "\"  onclick=\"ButtonH" + i + "_Click()\" s=\"" + $countHearing[i] + "\" type=\"button\"><label for=\"select" + i + "\"s=\"" + $countHearing[i] + "\">" +
                sorted_labels[i].innerHTML + "</label>";

        }
        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:350;top:50;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml;
    }

    //ヒアリング
    function Button5_Click() {
        //alert("countHearing"+$countHearing);



        var numC = 0;
        var num = 0;
        var num2 = 0;
        chkvalue = "";
        chkvalue_1 = "";
        chkvalue_2 = "";
        var chkvalue2 = 0;
        var MyForm = document.getElementById("HearingForm");
        var MyTag = MyForm.getElementsByTagName("input");



        chk = document.check.checkbox.checked;
        //alert(document.getElementById("HearingForm")[1].s);
        for (i = 0; i < MyTag.length; i++) {
            if (($countHearing[i] == 1) || ($countHearing[i] == 2)) { //if (MyTag[i].checked) {
                if (numC == 0) chkvalue += MyTag[i].value;
                else chkvalue += "#" + MyTag[i].value;
                numC++;
            }
        }
        for (i = 0; i < MyTag.length; i++) {
            if ($countHearing[i] == 1) { //if (MyTag[i].checked) {
                if (num == 0) chkvalue_1 += MyTag[i].value;
                else chkvalue_1 += "#" + MyTag[i].value;
                num++;
            }
        }
        for (i = 0; i < MyTag.length; i++) {
            if ($countHearing[i] == 2) { //if (MyTag[i].checked) {
                if (num2 == 0) chkvalue_2 += MyTag[i].value;
                else chkvalue_2 += "#" + MyTag[i].value;
                num2++;
            }
        }

        //自由記述欄修正
        YAHOO.util.Dom.setStyle("hearing", "display", "block");



        // var checkbox = document.getElementById('three');
        // checkbox.indeterminate = true;

        var HearingHtml = "";
        HearingHtml = "<form name=\"Hearing\" id=\"HearingForm\"><div class=\"check\">";

        HearingHtml += "</div><textarea id='comments' cols='50' rows='2' style=\" position:absolute;left:30;top:120;display:none; \"></textarea></form>";
        document.getElementById("hearing").innerHTML = HearingHtml; //仮の直しcomments→comment

        // for (i = 0; i <= Mylabels2.length - 1; i++) {
        // YAHOO.util.Dom.setStyle("radiobotton", "display", "block");
        // }

        YAHOO.util.Dom.setStyle("comments", "display", "block");
        YAHOO.util.Dom.setStyle("comments2", "display", "block");

        //alert("両方:"+chkvalue);
        //alert("迷い度1:"+chkvalue_1);
        //alert("迷い度2:"+chkvalue_2);
        var MyComments = document.getElementsByTagName("textarea");

        cmt = MyComments[0].value;
        if (cmt == "") cmt = "";

        if (chk == true) {
            chkvalue2 = 1;
        }
        //document.write(chkvalue2);



        $QAData["comments"] = cmt;
        $QAData["hesitate"] = chkvalue;
        $QAData["hesitate1"] = chkvalue_1;
        $QAData["hesitate2"] = chkvalue_2;
        $QAData["check"] = chkvalue2;

        // if(cmt == "" && chkvalue == "" && chkvalue2 == 0){
        //    alert("チェックまたは記述をしてください。");
        //     return;
        // }

        /*if ($QAData["hesitate"] == "" && $QAData["check"] == 0 ) {
        YAHOO.util.Dom.setStyle("QuesLevel", "display", "block");
        } else if ($QAData["check"] == 1 ){
        YAHOO.util.Dom.setStyle("QuesLevel3", "display", "block");
        } else if (($QAData["hesitate"] == 0 || $QAData["hesitate"] == 1 || $QAData["hesitate"] == 2 || $QAData["hesitate"] == 3 || $QAData["hesitate"] == 4 || $QAData["hesitate"] == 5 || $QAData["hesitate"] == 6 || $QAData["hesitate"] == 7 || $QAData["hesitate"] == 8 || $QAData["hesitate"] == 9 || $QAData["hesitate"] == 10 || $QAData["hesitate"] == 11 || $QAData["hesitate"] == 12 || $QAData["hesitate"] == 13 || $QAData["hesitate"] == 14 || $QAData["hesitate"] == 15 || $QAData["hesitate"] == 16 || $QAData["hesitate"] == 17 || $QAData["hesitate"] == 18 || $QAData["hesitate"] == 19 || $QAData["hesitate"] == 20 || $QAData["hesitate"] == 21) && $QAData["check"] == 0 ){
        YAHOO.util.Dom.setStyle("QuesLevel2", "display", "block");
        } else {
        YAHOO.util.Dom.setStyle("QuesLevel3", "display", "block");
        }*/


        if ($QAData["check"] === 1 || $QAData["hesitate2"] != "") {

            YAHOO.util.Dom.setStyle("QuesLevel3", "display", "block");
        } else {
            if ($QAData["hesitate"] === "") {

                YAHOO.util.Dom.setStyle("QuesLevel", "display", "block");
            } else if ($QAData["hesitate1"] != "") {
                YAHOO.util.Dom.setStyle("QuesLevel2", "display", "block")
            } else {

                YAHOO.util.Dom.setStyle("QuesLevel3", "display", "block");
            }
        }


        //YAHOO.util.Dom.setStyle("QuesLevel", "display", "block");
        //YAHOO.util.Dom.setStyle("comments", "display", "none");//自由記述欄修正
        //YAHOO.util.Dom.setStyle("comments2", "display", "none");
        YAHOO.util.Dom.setStyle("choose2", "display", "block");
        //YAHOO.util.Dom.setStyle("hearing", "display", "none");//自由記述欄修正
        YAHOO.util.Dom.setStyle("hearing2", "display", "none");
        YAHOO.util.Dom.setStyle("hearingT1", "display", "none");
        YAHOO.util.Dom.setStyle("hearingT2", "display", "none");
        YAHOO.util.Dom.setStyle("checkbox", "display", "none");
        YAHOO.util.Dom.setStyle("checkbox2", "display", "none");
        YAHOO.util.Dom.setStyle("Button5", "display", "none");

        YAHOO.util.Dom.setStyle("ButtonM", "display", "block");
        //document.getElementById("Button1").disabled = false;
        //YAHOO.util.Dom.setStyle("ButtonM2", "display", "block");

    }
</script>

<body id=mybody onLoad="ques_Load()" onMouseDown="Form1_MouseDown()" onMouseUp="Form1_MouseUp()">
    <input type="button" id="Button3" value="<?= translate('ques.php_1512行目_スタート') ?>" onclick="Button3_Click()"
        style="width:80px;height:36px;position:absolute;left:768px;top:27px;display: block" />

    <input type="button" id="Button1" value="<?= translate('ques.php_1517行目_決定') ?>" onclick="Button1_Click()"
        style="width:80px;height:36px;position:absolute;left:768px;top:32px;display:none" />

    <form name="Questions">
        <input type="button" id="ButtonM" value="<?= translate('ques.php_1517行目_決定') ?>" onclick="ButtonM_Click()"
            style="width:80px;height:30px;position:absolute;left:768px;top:750px;display:none" />
    </form>

    <form name="Hearing">
        <input type="button" id="Button5" value="<?= translate('ques.php_1517行目_決定') ?>" onclick="Button5_Click()"
            style="width:80px;height:30px;position:absolute;left:768px;top:600px;display:none" />
    </form>

    <input type="button" id="Button2" value="<?= translate('ques.php_1541行目_次の問題') ?>" onclick="Button2_Click()"
        style="width:auto;height:33px;position:absolute;left:670px;top:700px;display:none" />

    <input type="button" id="ButtonE2" value="<?= translate('ques.php_1546行目_問題へ') ?>" onclick="Button2_Click()"
        style="width:75px;height:33px;position:absolute;left:768px;top:600px;display:none" />

    <input type="button" id="Button4" value="<?= translate('ques.php_1551行目_終了') ?>"
        onclick="LineQuestioneForm_Closing()"
        style="width:75px;height:20px;position:absolute;left:780px;top:700px;background-color:pink;display:none" />

    <font id="reference_text_label" color="red" style="position:absolute;left:12;top:7">
        <?= translate('ques.php_1554行目_日本文') ?>
    </font>
    <div id="RichTextBox1" style="background-color:#ffa500;position:absolute;
     left:12;top:27;width:731;height:36;border-style:inset">
        <?= translate('ques.php_1556行目_ここに訳文が表示されます') ?>
    </div>

    <div id="RichTextBox2" style="background-color:#a1ffa1;position:absolute;
     left:12;top:570px;width:650;height:67;border-style:inset;display:none">
        <?= translate('ques.php_1559行目_ここに正解を表示') ?>
    </div>
    <div id="RichTextBox3" style="background-color:#a1ffa1;position:absolute;
     left:670;top:602px;width:90;height:auto;border-style:inset;display:none"><?= translate('ques.php_1562行目_正誤を表示') ?>
    </div>
    <div id="TextBox1" style="background-color:#a1ffa1;position:absolute;
     left:670;top:570px;width:90;height:23;border-style:inset;display:none"><?= translate('ques.php_1565行目_解答時間') ?>
    </div>

    <div id="Label2" style="position:absolute;
     left:12;top:650px;width:300;height:80;font-size:12;background-color:#ffa500;">
        <?= translate('ques.php_1568行目_操作説明') ?></br>
        <b><?= translate('ques.php_1570行目_単語の移動') ?></b></br>
        <b><?= translate('ques.php_1571行目_グループ化') ?></b></br>
    </div>

    <font color="red" style="position:absolute;left:12;top:140"><?= translate('ques.php_1582行目_解答欄') ?></font>

    <div id="answer" style="z-index=10;padding: 10px; border: 4px solid #333333;position:absolute;
    left:12;top:160;width:800;height:380px;font-size:12;"></div>

    <div id="AnswerPreview"
        style="padding: 10px; border: 2px solid #888888; background-color: #eeeeee; position:absolute; left:12px; top:580px; width:800px; height:30px; font-size:18px; color: #333333; line-height: 30px; overflow: hidden; white-space: nowrap;">
    </div>

    <div style="position:absolute;left:12;top:70">
        <font color="red"><?= translate('ques.php_1586行目_問題提示欄') ?></font>
    </div>
    <div id="question" style="padding: 10px; border: 2px solid #333333;position:absolute;
    left:12;top:90;width:800;height:20;font-size:12;"></div>

    <font id="hearing2" color="red" style="position:absolute;left:12;top:570px;display:none">
        <b><?= translate('ques.php_1590行目_迷った単語をクリックしてください') ?></b>
    </font>
    <div id="hearingT2" style="position:absolute;
     left:400;top:570px;width:auto;height:20;font-size:12;background-color:#ff0000;display:none">
        <?= translate('ques.php_1592行目_かなり迷った') ?>
    </div>
    <div id="hearingT1" style="position:absolute;
     left:500;top:570px;width:auto;height:20;font-size:12;background-color:#ffee00;display:none">
        <?= translate('ques.php_1594行目_少し迷った') ?>
    </div>
    <div id="hearing" style="padding: 10px; border: 1px solid #333333;position:absolute;
    left:12;top:600px;width:700;height:60;font-size:36;display:none;background-color: #ffffff">
    </div>

    <font id="comments2" cols='50' rows='2' size='2' style=" position:absolute;left:30;top:700px;display:none;">
        <b><?= translate('ques.php_1600行目_自由にご記入ください') ?></b>
    </font>

    <form name="check" action="">
        <input id="checkbox" type="checkbox" value="全体的にわからなかった"
            style="width:80px;height:30px;position:absolute;left:5px;top:720px;display:none" />
    </form>
    <font id="checkbox2" style="position:absolute;left:70;top:730px;display:none">
        <b><?= translate('ques.php_1606行目_全体的にわからなかった') ?></b>
    </font>

    <div id="myCanvas" style="position:absolute;top:0;left:0;height:900px;width:800px;z-index:-1"></div>
    <div id="myCanvas2" style="position:absolute;top:0;left:0;height:900px;width:800px;z-index:-1"></div>

    <div id="msg" style="position:absolute;
     left:50;top:640px;width:500;height:30;font-size:12;background-color:#ffa500;display:none"></div>

    <div id="Fixmsg" style="position:absolute;
     left:320;top:650px;width:200;height:80;font-size:12;background-color:#ffa500;display:block">
        <?= translate('ques.php_364行目_情報') ?>
    </div>

    <font id="exercise" color="red" style="position:absolute;
     left:768;top:10;width:80;height:18;font-size:18;color:red;display:none">
        <b><?= translate('ques.php_1622行目_例題') ?></b>
    </font>
    <div id="number" style="position:absolute;
     left:768;top:6;width:80;height:18;font-size:18;color:red;display;:none"></div>

    <form name="Questions">
        <label for="QuesLevel" id="QuesLabel" style="position:absolute;left:500px;top:570px;display:none">
            <?= translate('ques.php_1629行目_解答の迷い度') ?></label>
        <select id="QuesLevel" size="5" style=" font-size: 15px; position:absolute;left:500px;top:600px;display:none">
            <option value="choose" disabled="disabled"><?= translate('ques.php_1633行目_迷い度を選択してください') ?></option>
            <option value="level1" selected="selected"><?= translate('ques.php_1634行目_ほとんど迷わなかった') ?></option>
            <option value="level2"><?= translate('ques.php_1635行目_少し迷った') ?></option>
            <option value="level3"><?= translate('ques.php_1636行目_かなり迷った') ?></option>
            <option value="level0"><?= translate('ques.php_1637行目_誤って決定ボタンを押した') ?></option>
        </select>
        <select id="QuesLevel2" size="5" style=" font-size: 15px; position:absolute;left:500px;top:600px;display:none">
            <option value="choose" disabled="disabled"><?= translate('ques.php_1633行目_迷い度を選択してください') ?></option>
            <option value="level1"><?= translate('ques.php_1634行目_ほとんど迷わなかった') ?></option>
            <option value="level2" selected="selected"><?= translate('ques.php_1635行目_少し迷った') ?></option>
            <option value="level3"><?= translate('ques.php_1636行目_かなり迷った') ?></option>
            <option value="level0"><?= translate('ques.php_1637行目_誤って決定ボタンを押した') ?></option>
        </select>
        <select id="QuesLevel3" size="5" style=" font-size: 15px; position:absolute;left:500px;top:600px;display:none">
            <option value="choose" disabled="disabled"><?= translate('ques.php_1633行目_迷い度を選択してください') ?></option>
            <option value="level1"><?= translate('ques.php_1634行目_ほとんど迷わなかった') ?></option>
            <option value="level2"><?= translate('ques.php_1635行目_少し迷った') ?></option>
            <option value="level3" selected="selected"><?= translate('ques.php_1636行目_かなり迷った') ?></option>
            <option value="level0"><?= translate('ques.php_1637行目_誤って決定ボタンを押した') ?></option>
        </select>
        <input type="hidden" id="TermText" value="">
    </form>

    <script type="text/javascript">
        function disableSelection(target) {
            if (typeof target.onselectstart != "undefined") target.onselectstart = function() {
                return false
            }
            else if (typeof target.style.MozUserSelect != "undefined") target.style.MozUserSelect = "none"
            else target.onmousedown = function() {
                return false
            }
            target.style.cursor = "default"
        }
        disableSelection(document.getElementById("question"));
        disableSelection(document.getElementById("answer"));
        disableSelection(document.getElementById("myCanvas"));
        disableSelection(document.getElementById("myCanvas2"));
        disableSelection(document.getElementById("mybody"));
    </script>
    <div id="wordOrderTooltip"
        style="position: absolute; display: none; background-color: black; color: white; padding: 2px 5px; border-radius: 4px; font-size: 12px; z-index: 9999;">
    </div>
</body>

</html>