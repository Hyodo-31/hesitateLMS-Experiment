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
    Mouse["stick"] = "";    // くっついた時の構成単語
    Mouse["divide1"] = "";  // 分離した残りの単語群1
    Mouse["divide2"] = "";  // 分離した残りの単語群2 (中抜きなどで2つに割れた場合)
    Mouse["NOrder"] = "";   // その時点での並び順
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
    var GroupOffsets = []; // グループメンバーの相対位置保存用

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
    Array.prototype.random = function () {
        this.sort(function (a, b) {
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
        Array.prototype.contains = function (value) {
            for (var i in this) {
                if (this.hasOwnProperty(i) && this[i] === value) {
                    return true;
                }
            }
            return false;
        }
    }

    function createGroupObject(members) {
        var minL = Infinity,
            maxR = -Infinity,
            minT = Infinity,
            maxB = -Infinity;
        for (var i = 0; i < members.length; i++) {
            var r = YAHOO.util.Dom.getRegion(members[i]);
            minL = Math.min(minL, r.left);
            maxR = Math.max(maxR, r.right);
            minT = Math.min(minT, r.top);
            maxB = Math.max(maxB, r.bottom);
        }
        return {
            y: minT, // 代表Y
            left: minL,
            right: maxR,
            top: minT,
            bottom: maxB,
            members: members
        };
    }

    // レジスタの下線（黒）と、ホバー時の赤枠を描画する関数
    // targetGroup: ホバー中のグループ（赤枠用）。nullなら赤枠なし。
    // 【修正関数2】レジスタの下線（黒）と、ホバー時の赤枠を描画する
    // 【修正】描画関数
    // 【修正】描画関数
    // 第3引数 forceInclude を追加 (trueならドラッグ中の単語も含めて描画)
    function draw_register_lines(targetGroup, insertInfo, forceInclude) {
        BPen2.clear();

        if (forceInclude === undefined) forceInclude = false;

        // ★修正点: forceIncludeを渡す (隙間80, ドラッグ中も含めるか？)
        var groups = getAnswerGroups(20, forceInclude);

        BPen2.setColor("black");
        BPen2.setStroke(2);

        for (var i = 0; i < groups.length; i++) {
            var g = groups[i];
            // メンバーが2つ未満なら線は引かない
            //if (g.members.length < 2) continue;

            // ★追加チェック: メンバー間の最大隙間をチェックし、
            // 万が一データ上で繋がっていても、見た目で離れていれば線を引かない(分割する)処理

            // X座標順にソートされたメンバーリストを取得
            var members = g.members.slice(0).sort(function (a, b) {
                return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
            });

            var startX = YAHOO.util.Dom.getRegion(members[0]).left;
            var endX = YAHOO.util.Dom.getRegion(members[0]).right;
            var currentY = g.bottom + 2;

            for (var j = 1; j < members.length; j++) {
                var prevR = YAHOO.util.Dom.getRegion(members[j - 1]);
                var currR = YAHOO.util.Dom.getRegion(members[j]);

                // 隙間が 50px 以上空いていたら、そこで線を一旦切る
                if (currR.left - prevR.right > 50) {
                    // ここまでの線を引く (要素が2つ以上あった場合のみ)
                    if (prevR.right > startX + 10) { // ある程度長さがある場合
                        BPen2.drawLine(startX - 5 + cx, currentY + cy, prevR.right + 5 + cx, currentY + cy);
                    }
                    // 新しい線の開始地点
                    startX = currR.left;
                }
            }
            // 最後の線を引く
            var lastR = YAHOO.util.Dom.getRegion(members[members.length - 1]);
            // startX と lastR.right が同じ単語内の場合は引かない（単体扱い）
            if (lastR.left != startX) {
                BPen2.drawLine(startX - 5 + cx, currentY + cy, lastR.right + 5 + cx, currentY + cy);
            }
        }

        // 赤枠
        if (targetGroup) {
            BPen2.setColor("red");
            BPen2.setStroke(3);
            var w = (targetGroup.right - targetGroup.left) + 20;
            var h = (targetGroup.bottom - targetGroup.top) + 10;
            BPen2.drawRect(targetGroup.left - 10 + cx, targetGroup.top - 5 + cy, w, h);
        }

        // 縦線
        if (insertInfo) {
            BPen2.setColor("blue");
            BPen2.setStroke(4);
            var lineTop = insertInfo.top - 10;
            var lineBottom = insertInfo.bottom + 10;
            BPen2.drawLine(insertInfo.x + cx, lineTop + cy, insertInfo.x + cx, lineBottom + cy);
        }

        BPen2.paint();
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
        //スラッシュ入れる用
        BPen3 = new jsGraphics("myCanvas2");
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
            onFailure: function (req) {
                getError(req, "linemouse.php")
            }
        });

        function getm(res) { }
        //======================================================

        //解答データのうち最大のOIDを計算。要は次に出題する問題を算出する。
        var $a = "a"; //モード制御用
        $params = 'param1=' + encodeURIComponent($a) + '&lang=' + encodeURIComponent(testLangType);
        new Ajax.Request(URL + 'load.php', {
            method: 'get',
            onSuccess: getOID,
            onFailure: function (req) {
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

        window.addEventListener('unload', function () {
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
                        onFailure: function (req) {
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
                        onFailure: function (req) {
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
                            onFailure: function (req) {
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
                                onFailure: function (req) {
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
                                    onFailure: function (req) {
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
                                    dd[i].onMouseDown = function (e) {
                                        MyLabels_MouseDown(this.getDragEl())
                                    }
                                    dd[i].onMouseUp = function (e) {
                                        MyLabels_MouseUp(this.getDragEl())
                                    }
                                    dd[i].onDrag = function (e) {
                                        MyLabels_MouseMove(this.getDragEl())
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
                                    onFailure: function (req) {
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
                                        onFailure: function (req) {
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
                                                onFailure: function (req) {
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
        if (event.y <= 150) { // 問題提示欄の境界を少し調整
            d_flag = 0;
        } else if (event.y <= 550 && event.y > 160) { // 解答欄
            d_flag = 4;
        } else {
            d_flag = -1; // それ以外（ボタンエリアなど）は無効
        }
        /*else if (event.y <= 380 && event.y > 260) { // レジスタ1
                   d_flag = 1;
               } else if (event.y <= 460 && event.y > 380) { // レジスタ2
                   d_flag = 2;
               } else if (event.y > 460) { // レジスタ3
                   d_flag = 3;
               }*/
        if (Mouse_Flag == false) {
            return;
        }
        // d_flagが無効な場合は処理を抜ける（念のため）
        if (d_flag == -1) return;
        //マウスカーソルを十字に
        document.body.style.cursor = "crosshair";

        //グループ化されたラベルの初期化
        for (i = 0; i <= MyControls.length - 1; i++) {
            YAHOO.util.Dom.setStyle(MyControls[i], "color", "black");
        }
        MyControls = new Array();
        //開始点の取得
        sPos.x = event.x + cx;
        sPos.y = event.y + cy;
        ePos.x = event.x + cx;
        ePos.y = event.y + cy;
        //document.getElementById("msg").innerHTML = "Form1_MouseDown";
        MV = true;
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
                // ▼▼▼ 追加 ▼▼▼
                '&param9=' + encodeURIComponent($Mouse_Data["stick"] || "") +
                '&param10=' + encodeURIComponent($Mouse_Data["divide1"] || "") +
                '&param11=' + encodeURIComponent($Mouse_Data["divide2"] || "") +
                '&param12=' + encodeURIComponent($Mouse_Data["NOrder"] || "") +
                // ▲▲▲ 追加 ▲▲▲
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

    function draw() {
        BPen.clear();

        //レジスタ3をドラッグ中
        /*if (d_flag == 3) {
            if (ePos.y <= 460) { // 375 -> 460 (+85px, 厳密な境界に合わせる)
                ePos.y = 460;
            } else if (ePos.y >= 540) { // 480 -> 540 (+60px)
                ePos.y = 540;
            }
        } */
        if (d_flag == 0) { //問題提示欄をドラッグ中
            if (ePos.y >= 150) { // 130 -> 150 (他の判定ロジックと境界を統一)
                ePos.y = 150;
            }
        } else { //その他
            //最終解答欄だった場合
            if (d_flag == 4) {
                if (ePos.y <= 160) { // 130 -> 150 (他の判定ロジックと境界を統一)
                    ePos.y = 160;
                } else if (ePos.y >= 550) { // 215 -> 540 (新しい下限)
                    ePos.y = 550;
                }
            }
            //レジスタ1だった場合
            /*if (d_flag == 1) {
                if (ePos.y <= 260) { // 215 -> 260
                    ePos.y = 260;
                } else if (ePos.y >= 380) { // 295 -> 380
                    ePos.y = 380;
                }
            }
            //レジスタ2だった場合
            if (d_flag == 2) {
                if (ePos.y <= 380) { // 295 -> 380
                    ePos.y = 380;
                } else if (ePos.y >= 460) { // 375 -> 460
                    ePos.y = 460;
                }
            }*/
        }
        //消える描画でドラッグ中の四角形を描く
        //左上、右上、左下、右下、の４方向からのドラッグに対応
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
        //もし選択範囲にラベルがあれば赤色に色づけ
        //選択範囲が解除されたら黒色に戻る処理も実装
        //どの欄を対象にしているか、フラグにより判別
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

        //色付け。このへんはあまりいじってません。
        for (i = 0; i <= g_array.length - 1; i++) {
            //一時退避
            //退避ラベルならスキップ・・・必要なし
            //範囲選択をすべて抱合⇒一部抱合に変更
            MLi = YAHOO.util.Dom.getRegion(g_array[i]);
            if (sPos.x <= ePos.x && sPos.y <= ePos.y) { //左上---------------------------
                if ((sPos.x < MLi.right && sPos.y < MLi.bottom) && (ePos.x > MLi.left && ePos.y > MLi.top)) {
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "red");
                } else {
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "black");
                }
            } //左上ここまで--------------------------------------------------
            else if (sPos.x <= ePos.x && sPos.y >= ePos.y) { //左下
                if ((sPos.x < MLi.right && sPos.y > MLi.top) && (ePos.x > MLi.left && ePos.y < MLi.bottom)) {
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "red");
                } else {
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "black");
                }
            } else if (sPos.x > ePos.x && sPos.y < ePos.y) { //右上
                if ((sPos.x > MLi.left && sPos.y < MLi.bottom) && (ePos.x < MLi.right && ePos.y > MLi.top)) {
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "red");
                } else {
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "black");
                }
            } else if (sPos.x > ePos.x && sPos.y > ePos.y) { //右下
                if ((sPos.x > MLi.left && sPos.y > MLi.top) && (ePos.x < MLi.right && ePos.y < MLi.bottom)) {
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "red");
                } else {
                    YAHOO.util.Dom.setStyle(g_array[i], "color", "black");
                }
            }
        } //forここまで-----------------------------------------
    }

    //ソート関数ここから----------------------------------------------------------
    function MyLabelSort(sender, ex, ey) {
        var mylabelarray3 = new Array();
        var X_p = 0;
        var Y_p = 0;
        if (array_flag2 == 0) {
            mylabelarray3 = Mylabels.slice(0);
            X_p = DefaultX;
            Y_p = DefaultY;
        }
        /*else if (array_flag2 == 1) {
                   mylabelarray3 = Mylabels_r1.slice(0);
                   X_p = DefaultX_r1;
                   Y_p = DefaultY_r1;
               } else if (array_flag2 == 2) {
                   mylabelarray3 = Mylabels_r2.slice(0);
                   X_p = DefaultX_r2;
                   Y_p = DefaultY_r2;
               } else if (array_flag2 == 3) {
                   mylabelarray3 = Mylabels_r3.slice(0);
                   X_p = DefaultX_r3;
                   Y_p = DefaultY_r3;
               }*/
        else if (array_flag2 == 4) { // 解答欄
            var isGroupMove = MyControls.length > 0;

            // 1. ドラッグ情報取得
            var dragFirst = (isGroupMove) ? MyControls[0] : sender;
            var dragRegionFirst = YAHOO.util.Dom.getRegion(dragFirst);
            var dragWidth = 0;
            var minL = dragRegionFirst.left;
            var dragL, dragR;

            if (isGroupMove) {
                var maxR = -Infinity;
                minL = Infinity;
                for (var i = 0; i < MyControls.length; i++) {
                    var r = YAHOO.util.Dom.getRegion(MyControls[i]);
                    minL = Math.min(minL, r.left);
                    maxR = Math.max(maxR, r.right);
                }
                dragWidth = maxR - minL;
                dragL = minL;
                dragR = maxR;
            } else {
                dragWidth = dragRegionFirst.right - dragRegionFirst.left;
                dragL = dragRegionFirst.left;
                dragR = dragRegionFirst.right;
            }
            var dragCX = minL + (dragWidth / 2);
            var dragCY = dragRegionFirst.top + (dragRegionFirst.bottom - dragRegionFirst.top) / 2;

            // 2. ターゲット探索
            var groups = getAnswerGroups(25, false);
            var targetGroup = null;
            var thresholdY = 50;
            var marginX = 50;
            var minDiff = Infinity;

            for (var i = 0; i < groups.length; i++) {
                var group = groups[i];
                var groupCY = group.top + (group.bottom - group.top) / 2;
                if (Math.abs(dragCY - groupCY) < thresholdY) {
                    if (dragR > group.left - marginX && dragL < group.right + marginX) {
                        var distX = Math.max(0, group.left - dragR, dragL - group.right);
                        var distY = Math.abs(dragCY - groupCY);
                        var diff = Math.sqrt(distX * distX + distY * distY);
                        if (diff < minDiff) {
                            minDiff = diff;
                            targetGroup = group;
                        }
                    }
                }
            }

            // 3. 座標決定 & 割り込み処理
            var finalTop = dragRegionFirst.top;
            var finalLeft = minL;
            var padding = 17;

            if (targetGroup) {
                var bestPos = getInsertPosition(targetGroup, dragCX);
                var insertIndex = bestPos.index;
                var insertX = bestPos.x;

                // ▼▼▼ 修正点: 既存の隙間チェックを行い、無駄なシフト（右ズレ）を防ぐ ▼▼▼

                // メンバーをソート
                var members = targetGroup.members.slice(0).sort(function (a, b) {
                    return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
                });

                // 「ずらす必要があるか？」の判定フラグ
                var needShift = true;

                // ケースA: 先頭への挿入 (index 0)
                if (insertIndex == 0) {
                    // 現在の左端(members[0])と、ドラッグしている単語の左端(dragL)との距離を測る
                    // すでに十分なスペース（ドラッグ幅分）が空いているなら、ずらさない
                    // 例: 左端の単語をピックアップして、そのまま戻した場合など
                    if (members.length > 0) {
                        var firstElmX = YAHOO.util.Dom.getRegion(members[0]).left;
                        var gap = firstElmX - dragL;
                        // ギャップがドラッグ幅の8割以上あれば「既に空いている」とみなす
                        if (gap > dragWidth * 0.8) {
                            needShift = false;
                            // 既に空いている場所にスナップさせる（基準位置 - 幅 - 余白）
                            finalLeft = firstElmX - dragWidth - padding;
                        }
                    }
                }
                // ケースB: 末尾への挿入
                else if (insertIndex >= members.length) {
                    needShift = false; // 末尾なら後ろをずらす必要はない
                    var lastElm = members[members.length - 1];
                    var lastR = YAHOO.util.Dom.getRegion(lastElm);
                    finalLeft = lastR.right + padding;
                }
                // ケースC: 中間への挿入
                else {
                    var prevElm = members[insertIndex - 1];
                    var nextElm = members[insertIndex];
                    var prevR = YAHOO.util.Dom.getRegion(prevElm);
                    var nextR = YAHOO.util.Dom.getRegion(nextElm);

                    var gap = nextR.left - prevR.right;
                    // 隙間が十分あればずらさない
                    if (gap > dragWidth + padding) {
                        needShift = false;
                        finalLeft = prevR.right + padding;
                    }
                }

                // シフトが必要な場合のみ実行
                if (needShift) {
                    var shiftAmount = dragWidth + padding;
                    for (var i = insertIndex; i < members.length; i++) {
                        var elm = members[i];
                        var currentR = YAHOO.util.Dom.getRegion(elm);
                        YAHOO.util.Dom.setX(elm, currentR.left + shiftAmount);
                    }
                    // 自分自身の位置設定（シフト時は insertX に合わせる）
                    if (insertIndex > 0) {
                        var prevElm = members[insertIndex - 1];
                        var prevR = YAHOO.util.Dom.getRegion(prevElm);
                        finalLeft = prevR.right + padding;
                    } else {
                        finalLeft = insertX;
                    }
                }

                finalTop = targetGroup.top;
                // ▲▲▲ 修正ここまで ▲▲▲

            } else {
                // 自由配置モード
            }

            // 4. 座標適用
            if (isGroupMove) {
                var diffX = finalLeft - minL;
                var diffY = finalTop - dragRegionFirst.top;
                for (var i = 0; i < MyControls.length; i++) {
                    var r = YAHOO.util.Dom.getRegion(MyControls[i]);
                    YAHOO.util.Dom.setX(MyControls[i], r.left + diffX);
                    YAHOO.util.Dom.setY(MyControls[i], r.top + diffY);
                    YAHOO.util.Dom.setStyle(MyControls[i], "border", "none");
                }
            } else {
                YAHOO.util.Dom.setX(sender, finalLeft);
                YAHOO.util.Dom.setY(sender, finalTop);
                YAHOO.util.Dom.setStyle(sender, "border", "none");
            }

            // 5. 配列更新 & 整列
            var mylabelarray3 = Mylabels_ea.slice(0);
            var labelsToAdd = isGroupMove ? MyControls : [sender];
            for (var k = 0; k < labelsToAdd.length; k++) {
                var exists = mylabelarray3.some(function (label) {
                    return label.id === labelsToAdd[k].id;
                });
                if (!exists) mylabelarray3.push(labelsToAdd[k]);
            }
            Mylabels_ea = mylabelarray3.slice(0);

            if (targetGroup) {
                // 念のため再取得して整列
                var currentGroups = getAnswerGroups(20, true);
                var groupToArrange = null;
                for (var i = 0; i < currentGroups.length; i++) {
                    var g = currentGroups[i];
                    for (var m = 0; m < g.members.length; m++) {
                        if (g.members[m].id == sender.id) {
                            groupToArrange = g;
                            break;
                        }
                    }
                    if (groupToArrange) break;
                }

                if (groupToArrange) {
                    // 整列基準位置: もし左端に挿入（かつシフトなし）したなら、自分の位置を基準にする
                    var anchorX = targetGroup.left;
                    if (finalLeft < targetGroup.left) {
                        anchorX = finalLeft;
                    }
                    rearrangeSpecificGroup(groupToArrange, anchorX);
                } else {
                    draw_register_lines(null, null, true);
                }
            } else {
                draw_register_lines(null, null, true);
            }

            return mylabelarray3;
        }

        // ... (関数の残りの部分は変更ありません) ...
        var i;
        var j;
        var k;

        var hLabel;
        hLabel = sender;
        var aNum = new Array();
        var aCount = 0;
        for (i = 0; i <= mylabelarray3.length - 1; i++) {
            aNum.push(i);
            aCount++;
        }
        var iLabel = new Array();
        var item = MyControls.indexOf(hLabel);

        if (MyControls.length > 0) {
            if (array_flag2 == 0) {
                for (j = 0; j < MyControls.length; j++) {
                    for (i = 0; i < Mylabels2.length; i++) {
                        if (Mylabels2[i].id == MyControls[j].id) {
                            break;
                        }
                    }
                    mylabelarray3.splice(i, 1, MyControls[j]);
                    Mylabels = mylabelarray3.slice(0);
                    YAHOO.util.Dom.setX(Mylabels[i], Mylabels_left[i]);
                    YAHOO.util.Dom.setY(Mylabels[i], 100);
                }
                return mylabelarray3;
            }
            var X1 = YAHOO.util.Dom.getRegion(MyControls[0]);
            for (m = 0; m <= mylabelarray3.length; m++) {
                if (m == mylabelarray3.length) {
                    break;
                }
                var X2 = YAHOO.util.Dom.getRegion(mylabelarray3[m]);
                if (X1.left <= X2.left) {
                    break;
                }
            }

            for (k = 0; k < MyControls.length; k++) {
                mylabelarray3.splice(m + k, 0, MyControls[k])
            }
            for (i = 0; i < mylabelarray3.length; i++) {
                if (i == 0) {
                    YAHOO.util.Dom.setX(mylabelarray3[0], X_p);
                    YAHOO.util.Dom.setY(mylabelarray3[0], Y_p);
                } else {
                    var X1 = YAHOO.util.Dom.getRegion(mylabelarray3[i - 1]);
                    YAHOO.util.Dom.setX(mylabelarray3[i], X1.right + 17);
                    YAHOO.util.Dom.setY(mylabelarray3[i], Y_p);
                }
            }
        } else {
            if (array_flag2 == 0) {
                for (i = 0; i < Mylabels2.length; i++) {
                    if (Mylabels2[i].id == hLabel.id) {
                        break;
                    }
                }
                mylabelarray3.splice(i, 1, hLabel);
                Mylabels = mylabelarray3.slice(0);
                YAHOO.util.Dom.setX(Mylabels[i], Mylabels_left[i]);
                YAHOO.util.Dom.setY(Mylabels[i], 100);
                return mylabelarray3;
            }
            var X1 = YAHOO.util.Dom.getRegion(hLabel);
            for (j = 0; j <= mylabelarray3.length; j++) {
                if (j == mylabelarray3.length) {
                    break;
                }
                var X2 = YAHOO.util.Dom.getRegion(mylabelarray3[j]);
                if (X1.left <= X2.left) {
                    break;
                }
            }
            mylabelarray3.splice(j, 0, hLabel)
            for (i = 0; i < mylabelarray3.length; i++) {
                if (i == 0) {
                    YAHOO.util.Dom.setX(mylabelarray3[0], X_p);
                    YAHOO.util.Dom.setY(mylabelarray3[0], Y_p);
                } else {
                    var X1 = YAHOO.util.Dom.getRegion(mylabelarray3[i - 1]);
                    YAHOO.util.Dom.setX(mylabelarray3[i], X1.right + 17);
                    YAHOO.util.Dom.setY(mylabelarray3[i], Y_p);
                }
            }
        }
        if (array_flag2 == 1) {
            Mylabels_r1 = mylabelarray3.slice(0);
        } else if (array_flag2 == 2) {
            Mylabels_r2 = mylabelarray3.slice(0);
        } else if (array_flag2 == 3) {
            Mylabels_r3 = mylabelarray3.slice(0);
        } else if (array_flag2 == 4) {
            Mylabels_ea = mylabelarray3.slice(0);
        }
        return mylabelarray3;
    }
    //マウスが上に来たらラベルの見た目を変えたり、グループ化やレジスタラベルの対応---------------
    function MyLabels_MouseEnter(e) {

        // ======================= ▼▼▼ ここからが修正箇所です ▼▼▼ =======================
        var tooltip = document.getElementById('wordOrderTooltip');
        var hoveredLabel = this; // マウスが乗っているラベル

        // ラベルが解答欄にあるかチェック
        var isInAnswerArea = Mylabels_ea.some(function (label) {
            return label.id === hoveredLabel.id;
        });

        if (isInAnswerArea) {
            // 【変更】X座標のみのソートではなく、行（Y座標）を考慮したソート関数を使用
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
                tooltip.innerHTML = orderIndex + 1; // 1から始まる番号を表示
                var hoveredRegion = YAHOO.util.Dom.getRegion(hoveredLabel);

                tooltip.style.left = (hoveredRegion.left) + 'px';
                tooltip.style.top = (hoveredRegion.top - 20) + 'px';
                tooltip.style.display = 'block';
            }
        }
        // ======================= ▲▲▲ 修正はここまで ▲▲▲ =======================

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

    //★★ラベルクリック時。引っこ抜くときの作業とかしてるよ
    function MyLabels_MouseDown(sender) {
        // クリックした単語(sender)が、現在選択中のグループ(MyControls)に含まれていない場合、
        // それは「以前の選択とは無関係な新しいドラッグ」なので、古い選択状態を解除して空にする。
        if (MyControls.indexOf(sender) == -1) {
            for (var i = 0; i < MyControls.length; i++) {
                // 色やスタイルを元に戻す
                YAHOO.util.Dom.setStyle(MyControls[i], "color", "black");
                YAHOO.util.Dom.setStyle(MyControls[i], "background-color", "transparent");
            }
            MyControls = []; // 配列を空にする
        }
        // これを行わないと、内部的な配列順序でドラッグが開始され、単語が入れ替わって見えるバグが起きる
        if (MyControls.length > 0) {
            MyControls.sort(function (a, b) {
                var rA = YAHOO.util.Dom.getRegion(a);
                var rB = YAHOO.util.Dom.getRegion(b);
                return rA.left - rB.left;
            });
        }
        myStop = new Date();
        var mylabelarray = new Array();

        //どこのラベル郡にsenderが入ってるのか判定。ついでにどの位置に入っていたのかも。
        //idでやってます。頭悪いです。ごめんなさい。
        //もっといい簡潔な方法があったら書き換えてください。
        //単語何番目にあるんです？？グループ化してないとき
        var index_sender = 0;
        //単語何番目にあるんです？？グループ化してるとき
        var index_sender_g = 0;
        //もうグループ化してあるところは分かっているはず(d_flagで判定してるはず)なので
        //グループ化の先頭は何番目に入ってるのか？調べる
        //↑のコメントは多分出海さんなので文句は出海さんへお願いします
        //問題提示欄
        for (i = 0; i < Mylabels.length; i++) {
            if (Mylabels[i] == undefined) {
                continue;
            }
            if (Mylabels[i].id == sender.id) {
                array_flag = 0;
                index_sender = i;
            }
        }
        //レジスタ1
        for (i = 0; i < Mylabels_r1.length; i++) {
            if (Mylabels_r1[i].id == sender.id) {
                array_flag = 1;
                index_sender = i;
            }
        }
        //レジスタ2
        for (i = 0; i < Mylabels_r2.length; i++) {
            if (Mylabels_r2[i].id == sender.id) {
                array_flag = 2;
                index_sender = i;
            }
        }
        //レジスタ3
        for (i = 0; i < Mylabels_r3.length; i++) {
            if (Mylabels_r3[i].id == sender.id) {
                array_flag = 3;
                index_sender = i;
            }
        }
        //最終解答欄
        for (i = 0; i < Mylabels_ea.length; i++) {
            if (Mylabels_ea[i].id == sender.id) {
                array_flag = 4;
                index_sender = i;
            }
        }
        //もしグループ化されているなら
        if (MyControls.length > 0) {
            var g_array = new Array();
            if (array_flag == 1) {
                g_array = Mylabels_r1.slice(0);
            } else if (array_flag == 2) {
                g_array = Mylabels_r2.slice(0);
            } else if (array_flag == 3) {
                g_array = Mylabels_r3.slice(0);
            } else if (array_flag == 4) {
                g_array = Mylabels_ea.slice(0);
            }

            //グループ化の先頭が何番目に入っているか？
            for (i = 0; i < g_array.length; i++) {
                if (g_array[i] == undefined) {
                    continue;
                }
                if (g_array[i].id == MyControls[0].id) {
                    index_sender_g = i;
                }
            }
        }
        if (array_flag == 0) {
            mylabelarray = Mylabels.slice(0);
        } else if (array_flag == 1) {
            mylabelarray = Mylabels_r1.slice(0);
        } else if (array_flag == 2) {
            mylabelarray = Mylabels_r2.slice(0);
        } else if (array_flag == 3) {
            mylabelarray = Mylabels_r3.slice(0);
        } else if (array_flag == 4) {
            mylabelarray = Mylabels_ea.slice(0);
        }
        //グループ化されたラベルの初期化とか、hLabelに退避とか
        Mld = true;
        var hLabel = sender;
        DragL = sender; //IEのバグ対応
        IsDragging = true;

        // グループ化されている場合、ドラッグ開始時点での相対位置を記録する
        // グループ化されている場合、ドラッグ開始時点で横一列に整列させるための相対位置を計算する
        if (MyControls.length > 0) {
            GroupOffsets = [];

            // 1. 現在のX座標順（左にある順）にソートする
            MyControls.sort(function (a, b) {
                return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
            });

            // 2. ドラッグの基準となる単語（マウスで掴んだ単語）が、ソート後の配列のどこにいるか特定する
            var senderIndex = -1;
            for (var i = 0; i < MyControls.length; i++) {
                if (MyControls[i].id === sender.id) {
                    senderIndex = i;
                    break;
                }
            }

            // 3. 横一列に並べたときの「理想の相対位置」を計算する
            var tempX = [];
            var currentX = 0;
            var padding = 17; // 単語間の隙間

            // 先頭を0とした場合の各単語のX位置を算出
            for (var i = 0; i < MyControls.length; i++) {
                tempX.push(currentX);
                var r = YAHOO.util.Dom.getRegion(MyControls[i]);
                // 次の単語のために、現在の幅 + 隙間 を加算
                currentX += (r.right - r.left) + padding;
            }

            // 4. マウスで掴んだ単語(sender)の位置を基準(0)にするよう全体をシフト
            // これにより、掴んだ単語はマウス位置に留まり、他がその横に整列します
            var baseOffset = (senderIndex !== -1) ? tempX[senderIndex] : 0;
            var senderRegion = YAHOO.util.Dom.getRegion(sender);

            for (var i = 0; i < MyControls.length; i++) {
                // 新しいオフセットを保存 (Yは0にして横並びにする)
                var offsetX = tempX[i] - baseOffset;

                GroupOffsets.push({
                    x: offsetX,
                    y: 0
                });

                // ★重要: ドラッグ開始直後に見た目も即座に整列させる
                if (MyControls[i].id !== sender.id) {
                    YAHOO.util.Dom.setX(MyControls[i], senderRegion.left + offsetX);
                    YAHOO.util.Dom.setY(MyControls[i], senderRegion.top); // Y座標をsenderに合わせる
                }
            }
        }
        //一時退避・・・レジスタなくすからよい？
        var DPos = 0;
        DLabel = "";
        //グループ化ラベルを#で連結する グループラベル
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

        //Mylabelsで引っこ抜きがあったとき(array_flag==0だったとき)は、問題を詰める作業は行わない。
        if (array_flag == 0) {
            delete mylabelarray[index_sender];
        } else {
            //グループ化の場合
            if (MyControls.length > 0) {
                mylabelarray.splice(index_sender_g, MyControls.length);
            } else {
                mylabelarray.splice(index_sender, 1);
            }
            // 解答欄(array_flag == 4)以外の場合だけ、穴埋め（位置ズレ）処理を行うように制限する
            // これにより、解答欄をクリックした瞬間の「左端が右にズレる」現象が止まります

            if (array_flag != 4) { // ←【重要】このif文で囲ってください

                //各フラグに合わせて、デフォルトのYの値を変える。
                var X_p = 0;
                var Y_p = 0;
                var DestX = 0;
                var DestY = 0;

                // レジスタ用座標設定（元のコードにあるはずです）
                if (array_flag == 1) {
                    X_p = DefaultX_r1;
                    Y_p = DefaultY_r1;
                } else if (array_flag == 2) {
                    X_p = DefaultX_r2;
                    Y_p = DefaultY_r2;
                } else if (array_flag == 3) {
                    X_p = DefaultX_r3;
                    Y_p = DefaultY_r3;
                }

                //相対位置の計算
                var hl = YAHOO.util.Dom.getRegion(hLabel);
                DestX = hl.left + event.x - DiffPoint.x;
                DestY = hl.top + event.y - DiffPoint.y;

                //元の位置にあるラベルの位置を決定（ここがズレの原因！）
                for (i = 0; i <= mylabelarray.length; i++) {
                    if (i == 0) {
                        YAHOO.util.Dom.setX(mylabelarray[i], X_p);
                        YAHOO.util.Dom.setY(mylabelarray[i], Y_p);
                    } else {
                        var al = YAHOO.util.Dom.getRegion(mylabelarray[i - 1]);
                        YAHOO.util.Dom.setX(mylabelarray[i], al.right + 17);
                        YAHOO.util.Dom.setY(mylabelarray[i], Y_p);
                    }
                }
            }
        }

        // ======================= ▼▼▼ MouseDown (分離判定) 修正版 ▼▼▼ =======================
        var divide1_str = "";
        var divide2_str = "";

        // array_flag == 4 (解答欄) の場合のみ判定
        if (array_flag == 4) {
            // 1. 現在のグループ状態を取得
            var currentGroups = getAnswerGroups(20, true);
            var parentGroup = null;

            // 2. ドラッグしようとしている単語(sender)が所属するグループを探す
            for (var i = 0; i < currentGroups.length; i++) {
                for (var j = 0; j < currentGroups[i].members.length; j++) {
                    if (currentGroups[i].members[j].id == sender.id) {
                        parentGroup = currentGroups[i];
                        break;
                    }
                }
                if (parentGroup) break;
            }

            // 3. グループが見つかり、かつメンバーが複数（＝ここから引き剥がすと分離が起きる）
            if (parentGroup && parentGroup.members.length > 1) {
                console.log("【MouseDown】グループからの分離を検知しました");

                // ドラッグ対象（および一緒に動くグループ）のIDリスト
                var draggingIds = [];
                if (typeof MyControls !== 'undefined' && MyControls.length > 0) {
                    for (var k = 0; k < MyControls.length; k++) draggingIds.push(MyControls[k].id);
                } else {
                    draggingIds.push(sender.id);
                }

                // 残されるメンバーを抽出
                var remainingMembers = [];
                for (var i = 0; i < parentGroup.members.length; i++) {
                    if (draggingIds.indexOf(parentGroup.members[i].id) === -1) {
                        remainingMembers.push(parentGroup.members[i]);
                    }
                }

                // ▼▼▼ 追加：残ったメンバーを詰める処理 (穴埋め) ▼▼▼
                if (remainingMembers.length > 0) {
                    // (1) 残ったメンバーを左から右の順序でソート（崩れないように）
                    remainingMembers.sort(function(a, b) {
                        return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
                    });

                    // (2) 詰めるための開始位置（元のグループの左端）を取得
                    var startX = parentGroup.left;
                    var startY = parentGroup.top;
                    var padding = 17; // 以前のソートロジックと同じ隙間

                    // (3) 参考プログラムのロジックで詰める
                    for (var m = 0; m < remainingMembers.length; m++) {
                        var targetEl = remainingMembers[m];
                        
                        // 位置を設定
                        if (m == 0) {
                            // 先頭はグループの開始位置へ
                            YAHOO.util.Dom.setX(targetEl, startX);
                            YAHOO.util.Dom.setY(targetEl, startY);
                        } else {
                            // 2つ目以降は、前の要素の右端 + 17px の位置へ
                            // 直前の要素の情報を取得（DOM更新直後のため計算で位置を出すのが確実ですが、ここではgetRegionを使用）
                            var prevEl = remainingMembers[m - 1];
                            var prevR = YAHOO.util.Dom.getRegion(prevEl);
                            
                            // ※注意: setXした直後にgetRegionするとブラウザによっては反映が遅れる場合がありますが、
                            // 参考ロジックに従い X1.right + 17 の形式を採用します。
                            // もしズレる場合は、ここを計算値 (currentX += width + padding) に変更してください。
                            YAHOO.util.Dom.setX(targetEl, prevR.right + padding);
                            YAHOO.util.Dom.setY(targetEl, startY);
                        }
                    }
                }
                // ▲▲▲ 追加ここまで ▲▲▲

                // 残ったメンバーがどういう塊になるか再計算
                if (remainingMembers.length > 0) {
                    var remainingGroups = getGroupsFromList(remainingMembers);

                    // 1つ目の塊
                    if (remainingGroups.length > 0) {
                        var ids1 = [];
                        for (var m = 0; m < remainingGroups[0].members.length; m++) ids1.push(remainingGroups[0].members[m].id);
                        divide1_str = ids1.join("#");
                    }
                    // 2つ目の塊
                    if (remainingGroups.length > 1) {
                        var ids2 = [];
                        for (var m = 0; m < remainingGroups[1].members.length; m++) ids2.push(remainingGroups[1].members[m].id);
                        divide2_str = ids2.join("#");
                    }
                    console.log("Divide1: " + divide1_str + " / Divide2: " + divide2_str);
                }
            }
        }

        // データをセット
        $Mouse_Data["stick"] = "";
        $Mouse_Data["divide1"] = divide1_str;
        $Mouse_Data["divide2"] = divide2_str;
        $Mouse_Data["NOrder"] = "";
        // ======================= ▲▲▲ MouseDown 修正ここまで ▲▲▲ =======================

        //経過時間取得-----
        mTime = myStop.getTime() - myStart.getTime();
        //----------------
        var X = YAHOO.util.Dom.getRegion(hLabel);
        //▼マウスデータの取得
        $Mouse_Data["WID"] = WID;
        $Mouse_Data["Time"] = mTime;
        $Mouse_Data["X"] = X.left;
        $Mouse_Data["Y"] = X.top;
        $Mouse_Data["DragDrop"] = 2;
        $Mouse_Data["DropPos"] = DPos;
        $Mouse_Data["hlabel"] = hLabel.id;
        $Mouse_Data["Label"] = DLabel;
        Mouse_Num += 1;

        var $params = 'param1=' + encodeURIComponent($Mouse_Data["WID"]) +
            '&param2=' + encodeURIComponent($Mouse_Data["Time"]) +
            '&param3=' + encodeURIComponent($Mouse_Data["X"]) +
            '&param4=' + encodeURIComponent($Mouse_Data["Y"]) +
            '&param5=' + encodeURIComponent($Mouse_Data["DragDrop"]) +
            '&param6=' + encodeURIComponent($Mouse_Data["DropPos"]) +
            '&param7=' + encodeURIComponent($Mouse_Data["hlabel"]) +
            '&param8=' + encodeURIComponent($Mouse_Data["Label"]) +
            '&param9=' + encodeURIComponent($Mouse_Data["stick"] || "") +
            '&param10=' + encodeURIComponent($Mouse_Data["divide1"] || "") +
            '&param11=' + encodeURIComponent($Mouse_Data["divide2"] || "") +
            '&param12=' + encodeURIComponent($Mouse_Data["NOrder"] || "") +
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
            Mld = false;
        }

        function getE(req) {
            alert("失敗d");
        }

        DiffPoint = new Point(event.x, event.y);
        var obj = document.getElementById("TermText");
        if (obj.style.display == 'block') {
            var lblText = "";
            var TextNum;
            if (hLabel.id == LabelNum - 1) {
                lblText = hLabel.innerHTML;
                TextNum = document.Questions.TermText.value.indexOf(lblText.substring(0, lblText.length - 1));
                if (TextNum == -1) {
                    lblText = lblText.substring(0, lblText.length - 1) + ".";
                } else {
                    lblText = "";
                }
            } else {
                TextNum = document.Questions.TermText.value.indexOf(hLabel.innerHTML);
                if (TextNum == -1) {
                    lblText = lblText + hLabel.innerHTML + ".";
                }
            }
            document.Questions.TermText.value += lblText;
            TermTextChange();
        }

        if (array_flag == 0) {
            Mylabels = mylabelarray.slice(0);
        } else if (array_flag == 1) {
            Mylabels_r1 = mylabelarray.slice(0);
        } else if (array_flag == 2) {
            Mylabels_r2 = mylabelarray.slice(0);
        } else if (array_flag == 3) {
            Mylabels_r3 = mylabelarray.slice(0);
        } else if (array_flag == 4) {
            Mylabels_ea = mylabelarray.slice(0);
        }
    }

    //★★ラベルを離した時の作業。問題文の形を変えたりいろいろ
    function MyLabels_MouseUp(sender) {
        //枠の色リセット
        document.getElementById("question").style.borderColor = "black";
        //document.getElementById("register1").style.borderColor = "black";
        //document.getElementById("register2").style.borderColor = "black";
        //document.getElementById("register3").style.borderColor = "black";
        document.getElementById("answer").style.borderColor = "black";
        var mylabelarray2 = new Array();
        //イベントが起こったy座標の判定。それによって単語をどこに落とすか決める。
        /*if (event.y <= 150) {
            array_flag2 = 0;
        } else if (event.y <= 560 && event.y > 150) { // 解答欄の判定範囲を広げる (240 -> 260)
            array_flag2 = 4;
        } else {
            array_flag2 = -1; // 範囲外
        }else if (event.y <= 380 && event.y > 260) { // レジスタ1の判定範囲を下にずらす
            array_flag2 = 1;
        } else if (event.y <= 460 && event.y > 380) { // レジスタ2の判定範囲を下にずらす
            array_flag2 = 2;
        } else if (event.y > 460) { // レジスタ3の判定範囲を下にずらす
            array_flag2 = 3;
        }*/

        // 解答欄（150px超 ～ 560px以下）にドロップされた場合のみ解答欄扱いにする
        if (event.y > 160 && event.y <= 550 && event.x >= 12 && event.x <= 812) {
            array_flag2 = 4;
        }
        // それ以外（一番上の問題提示欄、または一番下の無効エリア）は
        // すべて「問題提示欄(0)」として扱い、強制的に元の場所に戻す
        else {
            array_flag2 = 0;
        }

        if (array_flag2 == 0) {
            mylabelarray2 = Mylabels.slice(0);
        }
        /*else if (array_flag2 == 1) {
                   mylabelarray2 = Mylabels_r1.slice(0);
               } else if (array_flag2 == 2) {
                   mylabelarray2 = Mylabels_r2.slice(0);
               } else if (array_flag2 == 3) {
                   mylabelarray2 = Mylabels_r3.slice(0);
               } */
        else if (array_flag2 == 4) {
            mylabelarray2 = Mylabels_ea.slice(0);
        } else {
            // 範囲外にドロップされた場合、移動処理を行わずに関数を終了する
            // (IsDraggingフラグを下げる処理等は必要だが、配置替えはしない)
            IsDragging = false;
            // 必要なら元の位置に戻す再描画処理などを呼ぶか、そのまま抜ける
            // ここではドラッグ状態の解除だけ行い、returnする
            // ★ドラッグ終了処理（色戻しなど）だけは通すため、ここでのreturnは慎重に。
            // 下記の「if (IsDragging != true)」以降の処理で、array_flag2が無効なら何もしないように制御されます。
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

        // ======================= ▼▼▼ MouseUp (結合・順序判定) 修正版 ▼▼▼ =======================
        var stick_str = "";
        var norder_str = "";

        // array_flag2 == 4 (解答欄にドロップされた) かつ IsDraggingでない(処理完了直前)
        if (array_flag2 == 4) {
            console.log("【MouseUp】解答欄へのドロップを検知しました");

            // --- 1. Stick判定 (結合) ---
            // 配置換え後の最新グループ状態を取得
            var currentGroups = getAnswerGroups(20, false);
            var myGroup = null;

            // ドロップした単語(sender)が含まれるグループを探す
            for (var i = 0; i < currentGroups.length; i++) {
                for (var j = 0; j < currentGroups[i].members.length; j++) {
                    if (currentGroups[i].members[j].id == sender.id) {
                        myGroup = currentGroups[i];
                        break;
                    }
                }
                if (myGroup) break;
            }

            // グループがあり、メンバーが2つ以上なら「くっついた」とみなす
            if (myGroup && myGroup.members.length > 1) {
                // X座標順にソートしてIDを連結
                var sortedMembers = myGroup.members.slice(0).sort(function (a, b) {
                    return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
                });
                var ids = [];
                for (var m = 0; m < sortedMembers.length; m++) ids.push(sortedMembers[m].id);
                stick_str = ids.join("#");
                console.log("Stick検出: " + stick_str);
            } else {
                console.log("Stick検出なし（単独配置）");
            }

            // --- 2. NOrder判定 (順序) ---
            // 全体のソート順を取得
            var allSorted = getSortedAnswerLabels();

            // 対象IDリスト
            var targetIds = [];
            if (typeof MyControls !== 'undefined' && MyControls.length > 0) {
                for (var k = 0; k < MyControls.length; k++) targetIds.push(MyControls[k].id);
            } else {
                targetIds.push(sender.id);
            }

            // 順位（1始まり）を探す
            var ranks = [];
            for (var t = 0; t < targetIds.length; t++) {
                for (var i = 0; i < allSorted.length; i++) {
                    if (allSorted[i].id == targetIds[t]) {
                        ranks.push(i + 1);
                        break;
                    }
                }
            }
            ranks.sort(function (a, b) { return a - b });
            norder_str = ranks.join("#");
            console.log("NOrder: " + norder_str);
        }

        // データをセット
        $Mouse_Data["stick"] = stick_str;
        $Mouse_Data["divide1"] = "";
        $Mouse_Data["divide2"] = "";
        $Mouse_Data["NOrder"] = norder_str;
        // ======================= ▲▲▲ MouseUp 修正ここまで ▲▲▲ =======================

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

        var $params = 'param1=' + encodeURIComponent($Mouse_Data["WID"]) +
            '&param2=' + encodeURIComponent($Mouse_Data["Time"]) +
            '&param3=' + encodeURIComponent($Mouse_Data["X"]) +
            '&param4=' + encodeURIComponent($Mouse_Data["Y"]) +
            '&param5=' + encodeURIComponent($Mouse_Data["DragDrop"]) +
            '&param6=' + encodeURIComponent($Mouse_Data["DropPos"]) +
            '&param7=' + encodeURIComponent($Mouse_Data["hlabel"]) +
            '&param8=' + encodeURIComponent($Mouse_Data["Label"]) +
            '&param9=' + encodeURIComponent($Mouse_Data["stick"] || "") +
            '&param10=' + encodeURIComponent($Mouse_Data["divide1"] || "") +
            '&param11=' + encodeURIComponent($Mouse_Data["divide2"] || "") +
            '&param12=' + encodeURIComponent($Mouse_Data["NOrder"] || "") +
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
        } else if (array_flag2 == 1) {
            Mylabels_r1 = mylabelarray2.slice(0);
        } else if (array_flag2 == 2) {
            Mylabels_r2 = mylabelarray2.slice(0);
        } else if (array_flag2 == 3) {
            Mylabels_r3 = mylabelarray2.slice(0);
        } else if (array_flag2 == 4) {
            Mylabels_ea = mylabelarray2.slice(0);
            resolveCollisions()
        }
        // ▼▼▼【最終手段】配列と見た目の完全同期処理 ▼▼▼
        var new_ea = [];

        for (var i = 0; i < Mylabels2.length; i++) {
            var el = Mylabels2[i];
            var region = YAHOO.util.Dom.getRegion(el);

            // 解答欄の範囲内チェック
            var isValidY = (region.top > 160 && region.top <= 550);
            var isValidX = (region.left >= 12 && region.left <= 812);

            if (isValidY && isValidX) {
                new_ea.push(el);
            }
        }

        // X座標順に並び替え
        new_ea.sort(function (a, b) {
            return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
        });

        // グローバル変数を上書き
        Mylabels_ea = new_ea;
        // ▲▲▲ 同期処理ここまで ▲▲▲

        MyControls = [];
        updateAnswerPreview();
    }

    // 衝突解決のメイン関数
    function resolveCollisions() {
        // 現在の全グループを取得（この時点では重なっている可能性がある）
        var groups = getAnswerGroups(20, false);

        // 処理の優先順位を決める（左上のものほど動かさない、右下のものほど動かす）
        groups.sort(function (a, b) {
            if (Math.abs(a.top - b.top) > 20) return a.top - b.top;
            return a.left - b.left;
        });

        // 玉突き事故を考慮して数回繰り返す
        for (var iter = 0; iter < 5; iter++) {
            var movedCount = 0;

            // 総当たりで重なりチェック
            for (var i = 0; i < groups.length; i++) {
                for (var j = i + 1; j < groups.length; j++) {
                    var g1 = groups[i];
                    var g2 = groups[j];

                    // 単純な重なり判定（接触しているか）
                    var isOverlap = !(g1.right < g2.left ||
                        g1.left > g2.right ||
                        g1.bottom < g2.top ||
                        g1.top > g2.bottom);

                    if (isOverlap) {
                        // 重なっていたら、後ろにあるほう(g2)を移動させる
                        // 空きスペースを探す
                        var safePos = searchEmptySpot(g2, groups);

                        if (safePos) {
                            // 移動実行
                            moveGroup(g2, safePos.x, safePos.y);
                            movedCount++;
                        }
                    }
                }
            }
            // 誰も動かなかったら解決完了
            if (movedCount === 0) break;
        }
    }

    // グループ移動用ヘルパー（座標情報の更新を含む）
    function moveGroup(group, newX, newY) {
        var offsetX = newX - group.left;
        var offsetY = newY - group.top;

        // メンバー全員を移動
        for (var k = 0; k < group.members.length; k++) {
            var el = group.members[k];
            var r = YAHOO.util.Dom.getRegion(el);
            YAHOO.util.Dom.setX(el, r.left + offsetX);
            YAHOO.util.Dom.setY(el, r.top + offsetY);
        }
        // 計算用データも更新（これをしないと次のループで誤判定する）
        var w = group.right - group.left;
        var h = group.bottom - group.top;
        group.left = newX;
        group.top = newY;
        group.right = newX + w;
        group.bottom = newY + h;
    }
    // ======================= ▲▲▲ 追加ここまで ▲▲▲ =======================

    // 解答欄にある単語をY座標でグルーピングする関数
    // 解答欄にある単語をY座標でグルーピングする関数
    // 【修正】解答欄の単語をグループ化する (単純な距離判定)
    // 引数を受け取るように修正し、forceIncludeを機能させる
    // 【修正】解答欄の単語をグループ化する
    function getAnswerGroups(thresholdX, forceInclude) {
        if (thresholdX === undefined) thresholdX = 20;
        if (forceInclude === undefined) forceInclude = false;

        var groups = [];
        var thresholdY = 15; // 行判定の許容範囲

        // 1. Y座標によるクラスタリング
        var yClusters = [];
        for (var i = 0; i < Mylabels_ea.length; i++) {
            var label = Mylabels_ea[i];

            // ドラッグ中の単語を除外するかどうかの判定
            if (IsDragging && !forceInclude) {
                // DragL（現在ドラッグ中の要素）と一致する場合はスキップ
                if (DragL && label.id == DragL.id) continue;
                // グループ移動中のメンバーならスキップ
                if (MyControls.length > 0) {
                    var isDraggingMember = false;
                    for (var m = 0; m < MyControls.length; m++) {
                        if (MyControls[m].id == label.id) {
                            isDraggingMember = true;
                            break;
                        }
                    }
                    if (isDraggingMember) continue;
                }
            }

            var region = YAHOO.util.Dom.getRegion(label);
            var added = false;

            for (var j = 0; j < yClusters.length; j++) {
                // Y座標が近いものを同じ行とみなす
                if (Math.abs(yClusters[j].baseY - region.top) < thresholdY) {
                    yClusters[j].members.push(label);
                    added = true;
                    break;
                }
            }
            if (!added) {
                yClusters.push({
                    baseY: region.top,
                    members: [label]
                });
            }
        }

        // 2. X座標距離による分割
        for (var k = 0; k < yClusters.length; k++) {
            var cluster = yClusters[k];
            // 左から右へソート
            cluster.members.sort(function (a, b) {
                return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
            });

            var currentSubGroup = [cluster.members[0]];
            for (var i = 1; i < cluster.members.length; i++) {
                var prev = cluster.members[i - 1];
                var curr = cluster.members[i];
                var prevRegion = YAHOO.util.Dom.getRegion(prev);
                var currRegion = YAHOO.util.Dom.getRegion(curr);

                // 単語間の距離が thresholdX 以上なら別グループにする
                if (currRegion.left - prevRegion.right > thresholdX) {
                    groups.push(createGroupObject(currentSubGroup));
                    currentSubGroup = [curr];
                } else {
                    currentSubGroup.push(curr);
                }
            }
            if (currentSubGroup.length > 0) {
                groups.push(createGroupObject(currentSubGroup));
            }
        }
        return groups;
    }

    // ======================= ▼▼▼ 修正：ソート用共通関数 ▼▼▼ =======================
    // 単語群を「ひとつの塊」とみなし、その「左端(X座標)」が小さい順にソートする関数
    function getSortedAnswerLabels() {
        // 1. 既存の関数を使って、視覚的なまとまり（グループ）を取得
        // しきい値25px、ドラッグ中は含めない
        var groups = getAnswerGroups(25, false);

        // 2. グループの「左端（left）」を比較して、小さい順（左→右）に並べ替え
        // ※ここではY座標（高さ）は一切無視され、X座標の開始位置だけで順序が決まります
        groups.sort(function (a, b) {
            return a.left - b.left;
        });

        // 3. グループ内の単語を展開して、1つの配列にまとめる
        var sortedList = [];
        for (var i = 0; i < groups.length; i++) {
            var members = groups[i].members;
            // グループ内の単語は getAnswerGroups の時点ですでに左から右に並んでいます
            for (var j = 0; j < members.length; j++) {
                sortedList.push(members[j]);
            }
        }
        return sortedList;
    }
    // ======================= ▲▲▲ 修正ここまで ▲▲▲ =======================

    // ======================= ▼▼▼ 修正：プレビュー更新関数 ▼▼▼ =======================
    function updateAnswerPreview() {
        var previewBox = document.getElementById("AnswerPreview");
        if (!previewBox) return;

        // 1. ソートされた単語リストを取得
        var sorted = getSortedAnswerLabels();

        // 2. 単語の配列を作成
        var words = [];
        for (var i = 0; i < sorted.length; i++) {
            if (sorted[i].innerHTML == "/") continue;
            words.push(sorted[i].innerHTML);
        }

        // 3. 英語の場合は文頭を大文字化（配列の最初の要素を操作）
        if (testLangType !== 'ja' && words.length > 0) {
            words[0] = words[0].charAt(0).toUpperCase() + words[0].slice(1);
        }

        // 4. HTML生成（単語ごとにspanで囲み、余白を設定）
        var html = "";
        for (var i = 0; i < words.length; i++) {
            // ★ここでの margin-right の数値を変えれば間隔を調整できます
            html += '<span style="display:inline-block; margin-right: 15px;">' + words[i] + '</span>';
        }

        previewBox.innerHTML = html;
    }
    // ======================= ▲▲▲ 修正ここまで ▲▲▲ =======================

    // 【追加】指定したグループ内での挿入位置を計算
    function getInsertPosition(group, checkX) {
        var members = group.members;
        // X座標でソート
        members.sort(function (a, b) {
            return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
        });

        // 候補地点: [左端, 単語間..., 右端]
        var candidates = [];

        // 左端
        var firstReg = YAHOO.util.Dom.getRegion(members[0]);
        candidates.push({
            x: firstReg.left,
            index: 0
        });

        // 単語間
        for (var i = 0; i < members.length - 1; i++) {
            var curr = members[i];
            var next = members[i + 1];
            var currReg = YAHOO.util.Dom.getRegion(curr);
            var nextReg = YAHOO.util.Dom.getRegion(next);
            // 隙間の中央
            var midX = currReg.right + (nextReg.left - currReg.right) / 2;
            candidates.push({
                x: midX,
                index: i + 1
            });
        }

        // 右端
        var lastReg = YAHOO.util.Dom.getRegion(members[members.length - 1]);
        candidates.push({
            x: lastReg.right,
            index: members.length
        });

        // 最も近い候補を探す
        var best = candidates[0];
        var minDiff = Math.abs(checkX - best.x);

        for (var i = 1; i < candidates.length; i++) {
            var diff = Math.abs(checkX - candidates[i].x);
            if (diff < minDiff) {
                minDiff = diff;
                best = candidates[i];
            }
        }
        return best; // {x: 挿入X座標, index: 挿入順序}
    }

    // 【追加】指定したグループのみを整列させる（割り込み用）
    // 【追加】指定したグループのみを整列させる（割り込み後の整列用）
    // 【修正】指定したグループのみを整列させる
    // 第2引数 forceStartLeft を追加（指定した座標を開始位置とする）
    function rearrangeSpecificGroup(group, forceStartLeft) {
        var padding = 17;
        var members = group.members;

        // 1. メンバーをX座標順にソート
        members.sort(function (a, b) {
            return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
        });

        // 2. 左端の基準位置を決める
        var currentX;
        if (forceStartLeft !== undefined && forceStartLeft !== null) {
            // 引数で指定があればそれを使う（ねじ込み時の位置ズレ防止）
            currentX = forceStartLeft;
        } else {
            // 指定がなければ現在の最左端を探す
            currentX = Infinity;
            for (var i = 0; i < members.length; i++) {
                var r = YAHOO.util.Dom.getRegion(members[i]);
                if (r.left < currentX) currentX = r.left;
            }
        }

        var currentY = group.top;

        // 3. 配置
        for (var j = 0; j < members.length; j++) {
            var elm = members[j];
            var r = YAHOO.util.Dom.getRegion(elm);
            var w = r.right - r.left;

            YAHOO.util.Dom.setX(elm, currentX);
            YAHOO.util.Dom.setY(elm, currentY);

            currentX += w + padding;
        }

        // 4. 再描画
        draw_register_lines(null, null, true);
    }

    //★★マウスでラベルをドラッグ中。動かしてるときだからここで挿入線をアレしたりコレしたり
    function MyLabels_MouseMove(sender) {
        if (IsDragging != true) {
            return;
        }
        var hLabel = sender;

        // グループ化されている場合、記録したオフセットに従って他の単語を追従させる
        if (MyControls.length > 0) {
            // ドラッグ中の単語(hLabel)はYUIライブラリがマウスに合わせて動かしてくれている
            var baseRegion = YAHOO.util.Dom.getRegion(hLabel);

            for (var i = 0; i < MyControls.length; i++) {
                // ドラッグ中の単語自身以外を追従させる
                if (MyControls[i].id !== hLabel.id) {
                    // 保存しておいた相対位置（GroupOffsets）を使って配置
                    // これにより、ドラッグ開始時の「見た目のまま」移動します
                    if (GroupOffsets[i]) {
                        YAHOO.util.Dom.setX(MyControls[i], baseRegion.left + GroupOffsets[i].x);
                        YAHOO.util.Dom.setY(MyControls[i], baseRegion.top + GroupOffsets[i].y);
                    }
                }
            }
        }

        var line_flag = -1;
        var line_array = new Array();
        var line_x = 0;
        var line_y = 0;
        var line_y2 = 0;
        var lstart_x = 0;
        var lstart_y = 0;
        //枠の色リセット
        document.getElementById("question").style.borderColor = "black";
        //document.getElementById("register1").style.borderColor = "black";
        //document.getElementById("register2").style.borderColor = "black";
        //document.getElementById("register3").style.borderColor = "black";
        document.getElementById("answer").style.borderColor = "black";

        // CSS borderリセット
        for (var i = 0; i < Mylabels_ea.length; i++) {
            YAHOO.util.Dom.setStyle(Mylabels_ea[i], "border", "none");
        }

        var line_flag = -1;

        var isValidX = (event.x >= 12 && event.x <= 812);
        if (event.y <= 150) line_flag = 0;
        else if (event.y > 150 && event.y <= 550 && isValidX) line_flag = 4;

        if (line_flag == 4) {
            document.getElementById("answer").style.borderColor = "red";

            // ドラッグ範囲計算
            var dragL = Infinity,
                dragR = -Infinity,
                dragT = Infinity,
                dragB = -Infinity;
            if (MyControls.length > 0) {
                for (var i = 0; i < MyControls.length; i++) {
                    var r = YAHOO.util.Dom.getRegion(MyControls[i]);
                    dragL = Math.min(dragL, r.left);
                    dragR = Math.max(dragR, r.right);
                    dragT = Math.min(dragT, r.top);
                    dragB = Math.max(dragB, r.bottom);
                }
            } else {
                var r = YAHOO.util.Dom.getRegion(sender);
                dragL = r.left;
                dragR = r.right;
                dragT = r.top;
                dragB = r.bottom;
            }
            var dragCY = dragT + (dragB - dragT) / 2;
            var dragCX = dragL + (dragR - dragL) / 2; // 中心Xも一応計算しておく(挿入位置計算用)

            var groups = getAnswerGroups(25, false);
            var targetGroup = null;
            var thresholdY = 50; // 縦の許容範囲（広めに維持）
            var marginX = 50; // 横の接近許容範囲
            var minDiff = Infinity;

            for (var i = 0; i < groups.length; i++) {
                var group = groups[i];
                var groupCY = group.top + (group.bottom - group.top) / 2;

                if (Math.abs(dragCY - groupCY) < thresholdY) {

                    // ▼▼▼ 修正: 中心ではなく「範囲の重複・接近」をチェック ▼▼▼
                    // 条件: 「ドラッグの右端」が「グループの左端-margin」より右にあり、かつ
                    //       「ドラッグの左端」が「グループの右端+margin」より左にある場合
                    if (dragR > group.left - marginX && dragL < group.right + marginX) {

                        // 距離計算（端同士の距離を使用）
                        // 重なっている場合は0、離れている場合はその距離
                        var distX = Math.max(0, group.left - dragR, dragL - group.right);
                        var distY = Math.abs(dragCY - groupCY);

                        // 三平方の定理で距離を出す（Xが重なっていればYの距離だけになる）
                        var diff = Math.sqrt(distX * distX + distY * distY);

                        if (diff < minDiff) {
                            minDiff = diff;
                            targetGroup = group;
                        }
                    }
                    // ▲▲▲ 修正ここまで ▲▲▲
                }
            }

            var insertInfo = null;
            if (targetGroup) {
                var bestPos = getInsertPosition(targetGroup, dragCX);
                insertInfo = {
                    x: bestPos.x,
                    top: targetGroup.top,
                    bottom: targetGroup.bottom
                };
            }

            draw_register_lines(targetGroup, insertInfo);

        } else if (line_flag == 0) {
            var line_array = Mylabels.slice(0);
            var lstart_x = DefaultX;
            var lstart_y = DefaultY;
            document.getElementById("question").style.borderColor = "red";
            draw3();
            // ... (問題提示欄用の挿入線描画ロジックがあればここに記述) ...
            // ※今回は解答欄メインの修正のため、問題提示欄の挙動は既存維持とします
        } else {
            draw3();
        }

        MytoFo = true;
        Form1_MouseMove(sender);
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

    // ======================= ▼▼▼ 追加：汎用グループ化関数 ▼▼▼ =======================
    // 任意の単語リストからグループ構造を生成する（getAnswerGroupsの汎用版）
    function getGroupsFromList(labelList) {
        var groups = [];
        var thresholdY = 15;
        var thresholdX = 20;

        // 1. Y座標によるクラスタリング
        var yClusters = [];
        for (var i = 0; i < labelList.length; i++) {
            var label = labelList[i];
            var region = YAHOO.util.Dom.getRegion(label);
            var added = false;
            for (var j = 0; j < yClusters.length; j++) {
                if (Math.abs(yClusters[j].baseY - region.top) < thresholdY) {
                    yClusters[j].members.push(label);
                    added = true;
                    break;
                }
            }
            if (!added) yClusters.push({
                baseY: region.top,
                members: [label]
            });
        }

        // 2. X座標による分割とグループオブジェクト生成
        for (var k = 0; k < yClusters.length; k++) {
            var cluster = yClusters[k];
            cluster.members.sort(function (a, b) {
                return YAHOO.util.Dom.getRegion(a).left - YAHOO.util.Dom.getRegion(b).left;
            });

            var currentSubGroup = [cluster.members[0]];
            for (var i = 1; i < cluster.members.length; i++) {
                var prev = cluster.members[i - 1];
                var curr = cluster.members[i];
                var prevR = YAHOO.util.Dom.getRegion(prev);
                var currR = YAHOO.util.Dom.getRegion(curr);

                if (currR.left - prevR.right > thresholdX) {
                    groups.push(createGroupObject(currentSubGroup));
                    currentSubGroup = [curr];
                } else {
                    currentSubGroup.push(curr);
                }
            }
            if (currentSubGroup.length > 0) groups.push(createGroupObject(currentSubGroup));
        }
        return groups;
    }
    // ======================= ▲▲▲ 追加ここまで ▲▲▲ =======================

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
        function getA(req) { }

        function getE(req) {
            alert("失敗g");
        }

        // ▼▼▼ ここから下のブロックを、ごっそり差し替えてください ▼▼▼

        // 【変更】単純なX座標ソートではなく、行（Y座標）を考慮したソート関数を使用
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

        // ▲▲▲ 差し替えはここまで ▲▲▲

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
        // ▼▼▼ 追加：プレビュー欄をクリアして再表示 ▼▼▼
        var previewBox = document.getElementById("AnswerPreview");
        if (previewBox) {
            previewBox.innerHTML = "";
            YAHOO.util.Dom.setStyle("AnswerPreview", "display", "block");
        }
        // ▲▲▲ 追加ここまで ▲▲▲

        // ▼▼▼ 追加：キャンバスの描画（下線など）をクリアする ▼▼▼
        if (typeof BPen2 !== 'undefined') BPen2.clear(); // 下線（黒線）を消す
        if (typeof BPen !== 'undefined') BPen.clear();   // グループ化の枠線があれば消す
        // ▲▲▲ 追加ここまで ▲▲▲

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

        // ▼▼▼ 追加：プレビュー欄を再表示 ▼▼▼
        var previewBox = document.getElementById("AnswerPreview");
        if (previewBox) {
            previewBox.innerHTML = "";
            YAHOO.util.Dom.setStyle("AnswerPreview", "display", "block");
        }
        // ▲▲▲ 追加ここまで ▲▲▲

        // ▼▼▼ 追加：キャンバスの描画（下線など）をクリアする ▼▼▼
        if (typeof BPen2 !== 'undefined') BPen2.clear(); // 下線（黒線）を消す
        if (typeof BPen !== 'undefined') BPen.clear();   // グループ化の枠線があれば消す
        // ▲▲▲ 追加ここまで ▲▲▲
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
                        onFailure: function (req) {
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
            if (typeof target.onselectstart != "undefined") target.onselectstart = function () {
                return false
            }
            else if (typeof target.style.MozUserSelect != "undefined") target.style.MozUserSelect = "none"
            else target.onmousedown = function () {
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