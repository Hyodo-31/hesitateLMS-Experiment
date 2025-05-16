<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>教師用ダッシュボード</title>
    <link rel="stylesheet" href="../style/machineLearning_styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body>
    <style>
        /* テーブルのスクロール表示設定 */
        #table-container {
            max-height: 400px; /* 表示領域の高さを指定 */
            overflow-y: auto;  /* 縦スクロールを有効にする */
            border: 1px solid #ccc; /* 境界線 */
        }

        /* テーブルのスタイル */
        #results-table {
            width: 100%; /* テーブル幅を100%に */
            border-collapse: collapse;
        }

        #results-table th, #results-table td {
            padding: 8px;
            border: 1px solid #ddd; /* セルの境界線 */
        }
        #cluster-data{
            max-width: 800px;
            width: 100%;      /* 親要素の幅に合わせる */
            height: auto;     /* 高さを自動調整 */
            margin: 0 auto; /* 左右のマージンを自動で中央揃え */
        }
    </style>

    <?php
        require "../dbc.php";
        require "log_write.php";
        // セッション変数をクリアする（必要に応じて）
        unset($_SESSION['conditions']);
        // GET パラメータが指定されている場合のみセッションに保存または上書き
        if (isset($_GET['students']) && !empty($_GET['students'])) {
            $_SESSION['group_students'] = $_GET['students'];
            echo $_SESSION['group_students'];
        }
        // ユニークなIDを生成
        $uniqueId = uniqid(bin2hex(random_bytes(4)));
        $timestamp = date('YmdHis');
    ?>
    <header>
        <div class="logo">データ分析ページ</div>
        <nav>
            <ul>
                <li><a href="teachertrue.php">ホーム</a></li>
                <li><a href="machineLearning_sample.php">迷い推定・機械学習</a></li>
                <li><a href="register-student.php">新規学生登録</a></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <aside>
            <ul>
                <li><a href="#">ダッシュボード</a></li>
                <li><a href="machineLearning_sample.php">迷い推定・機械学習</a></li>
                <li><a href="register-student.php">新規学生登録</a></li>
            </ul>
        </aside>
        <main>
            <p id="loadTime"></p>
        <script>
            window.addEventListener('load', function() {
                var loadTime = performance.now();
                console.log('ページの表示時間: ' + loadTime.toFixed(2) + 'ミリ秒');
                document.getElementById('loadTime').textContent = 'ページの表示時間: ' + loadTime.toFixed(2) + 'ミリ秒';
            });
        </script>
        <?php
                        require "../dbc.php";
                        $teacher_id = $_SESSION['MemberID'];

                        $stmt = $conn->prepare("SELECT * FROM `groups` WHERE TID = ?");
                        if (!$stmt) {
                            die("prepare() failed: " . $conn->error);
                        }
                        $stmt->bind_param("i", $teacher_id);
                        $stmt->execute();
                        if (!$stmt) {
                            die("prepare() failed: " . $conn->error);
                        }
                        $result = $stmt->get_result();
                        
                        $groups = [];
                        if($result->num_rows > 0) {
                            //学習者グループがある場合
                            while($row = $result->fetch_assoc()) {
                                $group_id = $row['group_id'];
                                $group_name = $row['group_name'];

                                $stmt_groupmember = $conn->prepare("SELECT * FROM group_members WHERE group_id = ?");
                                $stmt_groupmember->bind_param("i", $group_id);
                                $stmt_groupmember->execute();
                                $result_groupmember = $stmt_groupmember->get_result();
                                $group_students = [];
                                while($member = $result_groupmember->fetch_assoc()) {
                                    $students_id = $member['uid'];
                                    //学生ごとの正解数と解答数を取得
                                    $stmt_scores = $conn -> prepare("SELECT 
                                                            COUNT(*) AS total_answers,
                                                            SUM(CASE WHEN TF = 1 THEN 1 ELSE 0 END) AS correct_answers,
                                                            SUM(Time) AS total_time
                                                            FROM linedata WHERE uid = ?");
                                    $stmt_scores->bind_param("i", $students_id);
                                    $stmt_scores->execute();
                                    $result_scores = $stmt_scores->get_result();
                                    $score_data = $result_scores->fetch_assoc();
                                    $correct_answers = $score_data['correct_answers'];
                                    $total_answers = $score_data['total_answers'];
                                    $total_time = $score_data['total_time'];
                                    $accuracy_rate = $total_answers > 0 ? number_format(($correct_answers / $total_answers) * 100,2) : 0;
                                    $notaccuracy_rate = 100 - $accuracy_rate;
                                    $accuracy_time = $total_answers > 0 ? number_format(($total_time / 1000) / $total_answers,2) : 0;

                                    $stmt_scores->close();
                                    $result_scores->free(); // メモリ解放

                                    //学生ごとの名前を取得
                                    $stmt_name = $conn->prepare("SELECT Name FROM students WHERE uid = ?");
                                    $stmt_name->bind_param("i", $students_id);
                                    $stmt_name->execute();
                                    $result_name = $stmt_name->get_result();
                                    $name_data = $result_name->fetch_assoc();
                                    $name = $name_data['Name'];
                                    $stmt_name->close();
                                    $result_name->free();

                                    //学生ごとの正解数を格納
                                    $group_students[] = [
                                        'student_id' => $students_id,
                                        'name' => $name,
                                        'accuracy' => $accuracy_rate,
                                        'notaccuracy' => $notaccuracy_rate,
                                        'time' => $accuracy_time
                                    ];
                                }
                                // グループデータを配列に追加
                                $groups[] = [
                                    'group_name' => $group_name,
                                    'group_id' => $group_id,
                                    'students' => $group_students
                                ];
                                $stmt_groupmember->close();
                                $result_groupmember->free();
                            }
                        }else{
                            // 学習者グループがない場合
                            echo "<p>学習者グループがありません</p>";
                        }

                        $stmt->close();
                        $conn->close();
                       
                    ?>
            <?php
                require "../dbc.php";
                // フォームからの入力を受け取る
                $UIDrange = isset($_POST['UIDrange']) ? $_POST['UIDrange'] : null;
                $WIDrange = isset($_POST['WIDrange']) ? $_POST['WIDrange'] : null;
                $UIDsearch = isset($_POST['UID']) ? $_POST['UID'] : null; // 配列として受け取る
                $WIDsearch = isset($_POST['WID']) ? $_POST['WID'] : null; // 配列として受け取る
                $TFsearch = isset($_POST['TFsearch']) ? $_POST['TFsearch'] : null;
                $TimeRange = isset($_POST['TimeRange']) ? $_POST['TimeRange'] : null;
                $Timesearch = isset($_POST['Timesearch']) ? $_POST['Timesearch'] : null;
                $TimesearchMin = isset($_POST['Timesearch-min']) ? $_POST['Timesearch-min'] : null;
                $TimesearchMax = isset($_POST['Timesearch-max']) ? $_POST['Timesearch-max'] : null;

                $useData = isset($_POST['useData']) ? $_POST['useData'] : "";
                $selectedGroup = isset($_POST['selectedGroup']) ? $_POST['selectedGroup'] : "";


                $sql = "SELECT * FROM linedata";
                // WHERE 句の条件を保持する配列
                $conditions = [];
                // UIDの条件を追加
                if ($useData === 'groupdata') {
                    if (empty($selectedGroup)) {
                        // グループが選択されていない場合の処理
                        echo "<script>alert('作成したグループを選択してください。あああ');</script>";
                    } else {
                        // グループが選択されている場合の処理
                        echo "選択されたグループID: " . htmlspecialchars($selectedGroup, ENT_QUOTES, 'UTF-8');
                        // ここで、データベースクエリや他の処理を追加
                        $sql_getUID = "SELECT uid FROM group_members WHERE group_id = ?";
                        $stmt = $conn->prepare($sql_getUID);
                        $stmt->bind_param("i", $selectedGroup);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $UIDs = [];
                        while ($row = $result->fetch_assoc()) {
                            $UIDs[] = $row['uid'];
                        }
                        $stmt->close();
                        $result->free();
                    }
                        // UID配列をカンマ区切りの文字列に変換
                        $UIDlist = implode("','", array_map(function($uid) use ($conn) {
                            return mysqli_real_escape_string($conn, $uid);
                        }, $UIDs));
                        $conditions[] = "UID IN ('" . $UIDlist . "')";
                } elseif ($useData === 'alalldata') {
                    // 2019年度のA大学全データが選択された場合の処理
                    echo "2019年度のA大学全データが選択されました。";
                } else {
                    // その他の場合
                    echo "選択が無効です。";
                }
                //$conditionの中身を確認
                echo "conditions: " . implode(", ", $conditions);
                /*
                if (!empty($UIDsearch)) {
                    // UID配列をカンマ区切りの文字列に変換
                    $UIDlist = implode("','", array_map(function($uid) use ($conn) {
                        return mysqli_real_escape_string($conn, $uid);
                    }, $UIDsearch));
                    
                    if ($UIDrange === 'not') {
                        $conditions[] = "UID NOT IN ('" . $UIDlist . "')";
                    } else {
                        $conditions[] = "UID IN ('" . $UIDlist . "')";
                    }
                }

                // WIDの条件を追加
                if (!empty($WIDsearch)) {
                    // WID配列をカンマ区切りの文字列に変換
                    $WIDlist = implode("','", array_map(function($wid) use ($conn) {
                        return mysqli_real_escape_string($conn, $wid);
                    }, $WIDsearch));
                    
                    if ($WIDrange === 'not') {
                        $conditions[] = "WID NOT IN ('" . $WIDlist . "')";
                    } else {
                        $conditions[] = "WID IN ('" . $WIDlist . "')";
                    }
                }
                    */
                // 正誤の条件を追加
                if (isset($TFsearch)) {
                    $conditions[] = "TF = '" . mysqli_real_escape_string($conn, $TFsearch) . "'";
                }
                // 解答時間の条件を追加
                if (!empty($TimeRange) && !empty($Timesearch)) {
                    switch ($TimeRange) {
                        case 'above':
                            $conditions[] = "Time >= '" . mysqli_real_escape_string($conn, $Timesearch) . "'";
                            break;
                        case 'below':
                            $conditions[] = "Time <= '" . mysqli_real_escape_string($conn, $Timesearch) . "'";
                            break;
                        case 'range':
                            if (!empty($TimesearchMin) && !empty($TimesearchMax)) {
                                $conditions[] = "Time BETWEEN '" . mysqli_real_escape_string($conn, $TimesearchMin) . "' AND '" . mysqli_real_escape_string($conn, $TimesearchMax) . "'";
                            }
                            break;
                    }
                }

                // 条件が一つでもあればWHERE句を追加&SQLと条件をsessionに保存
                if (!empty($conditions)) {
                    $sql .= " WHERE " . join(" AND ", $conditions);
                    $_SESSION['conditions'] = $conditions;
                    //echo $_SESSION['conditions'];
                    //echo "!emptyの条件を満たしています．<br>";
                }else{
                    //echo "emptyの条件を満たしていません。<br>";
                }
                // $_SESSION['conditions']が設定されているかどうかを確認します
                /*
                if (isset($_SESSION['conditions']) && !empty($_SESSION['conditions'])) {
                    //echo '$_SESSION["conditions"]が設定されています．<br>';
                    // ここに$_SESSION['conditions']を使用するコードを追加します
                } else {
                    //echo '$_SESSION["conditions"]は設定されていません．<br>';
                }
                    */
                $_SESSION['sql'] = $sql;
                echo $_SESSION['sql'];



                // SQL実行  
                $result = mysqli_query($conn, $sql);


            ?>
            <?php
                //デバッグ用のコード
                // フォームがPOSTされた場合
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    echo "<h2>POSTされたデータ:</h2>";

                    
                    // UIDの選択値を表示
                    /*
                    if (isset($_POST['UIDrange'])) {
                        //echo "UID範囲: " . htmlspecialchars($_POST['UIDrange']) . "<br>";
                    }

                    if (isset($_POST['UID'])) {
                        echo "選択されたUID:<br>";
                        foreach ($_POST['UID'] as $uid) {
                            //echo htmlspecialchars($uid) . "<br>";
                        }
                    }
                        */

                    // WIDの選択値を表示
                    /*
                    if (isset($_POST['WIDrange'])) {
                        //echo "WID範囲: " . htmlspecialchars($_POST['WIDrange']) . "<br>";
                    }

                    if (isset($_POST['WID'])) {
                        echo "選択されたWID:<br>";
                        foreach ($_POST['WID'] as $wid) {
                            //echo htmlspecialchars($wid) . "<br>";
                        }
                    }
                        */

                    // 正誤の選択値を表示
                    if (isset($_POST['TFsearch'])) {
                        //echo "正誤: " . htmlspecialchars($_POST['TFsearch']) . "<br>";
                    }

                    // 解答時間の選択値を表示
                    if (isset($_POST['TimeRange'])) {
                        //echo "解答時間の範囲: " . htmlspecialchars($_POST['TimeRange']) . "<br>";
                    }

                    if (isset($_POST['Timesearch'])) {
                        //echo "解答時間: " . htmlspecialchars($_POST['Timesearch']) . "<br>";
                    }

                    if (isset($_POST['Timesearch-min']) && isset($_POST['Timesearch-max'])) {
                        //echo "解答時間の範囲: " . htmlspecialchars($_POST['Timesearch-min']) . " ～ " . htmlspecialchars($_POST['Timesearch-max']) . "<br>";
                    }
                }

            ?>
            <?php
                if($_SERVER["REQUEST_METHOD"] == "POST") {
                    if(isset($_POST['featureLabel'])) {
                        $allresult = array();
                        //取得したデータに応じてSQLを生成
                        $tempwhere = array();
                        $sql = "SELECT UID,WID,Understand,attempt,";
                        $sql_test = "SELECT UID,WID,Understand,attempt,";
                        $selectcolumn = implode(",", $_POST['featureLabel']);
                        $sql.= $selectcolumn." FROM featurevalue";   //データベースの列名が入っている． 教師データ
                        $sql_test.= $selectcolumn . " FROM test_featurevalue";
                        
                        //csvfileに書く用の変数
                        $column_name = "UID,WID,Understand,attempt,";
                        $column_name.= $selectcolumn;
                        //デバッグ
                        //echo "生成されたSQLは",$sql,"です<br>";
                        if(isset($_SESSION['group_students']) && !empty($_SESSION['group_students'])) {
                            $tempgroupsql = "";
                            $tempgroupsql .=  "SELECT UID,WID,Understand," . $selectcolumn . " FROM featurevalue1 WHERE UID IN (" . $_SESSION['group_students'] . ")";
                            //echo "グループSQLは",$tempgroupsql,"です<br>";
                            $result_groupsql = mysqli_query($conn, $tempgroupsql);
                            while($row = mysqli_fetch_assoc($result_groupsql)){
                                $allresult_group[] = $row;
                            }
                            //csvfileに記述
                            //echo getcwd();
                            //$filename = "/home/yamakawa/public_html/hesitateLMS/teacher/pydata/testdata_{$uniqueId}_{$timestamp}.csv";
                            $filename = "/xampp/htdocs/hesitateLMS/teacher/pydata/testdata_{$uniqueId}_{$timestamp}.csv";   //ここを変更した
                            //$filename = './pydata/testdata.csv';

                            // ファイルを開こうとする
                            $fp_group = fopen($filename, 'w');

                            // fopen が失敗した場合のエラー処理
                            if (!$fp_group) {
                                $error = error_get_last();
                                die("ファイルを開けませんでした: " . $error['message']);
                            }

                            fputcsv($fp_group, explode(',', $column_name));
                            foreach($allresult_group as $row) {
                                fputcsv($fp_group, $row);
                            }
                            fclose($fp_group);
                            echo "csvファイル_groupを生成しました";

                        }

                        //WHERE句の追加
                        if(isset($_SESSION['conditions']) && !empty($_SESSION['conditions'])) {
                            $tempwhere = $_SESSION['conditions'];
                        }

                        // WHERE句の追加
                        if (!empty($tempwhere)) {
                            $sql .= " WHERE " . implode(" AND ", $tempwhere);
                        }

                        // 最終的なSQLをデバッグ用に出力
                        echo "最終的な生成されたSQL(教師データ)は " . $sql . " です<br>";
                        echo "最終的な生成されたSQL(テストデータ)は " . $sql_test . " です<br>";
                        // ここでSQLを実行する
                        $result = mysqli_query($conn, $sql);
                        //データベースの行数取得
                        $rows = mysqli_num_rows($result);
                        
                        //echo "抽出したデータ数は",$rows,"件です<br>";

                        while($row = mysqli_fetch_assoc($result)){
                            $allresult[] = $row;
                        }
                        $result_test = mysqli_query($conn, $sql_test);
                        $rows_test = mysqli_num_rows($result_test);

                        while($row = mysqli_fetch_assoc($result_test)){
                            $allresult_test[] = $row;
                        }
                        //csvfileに記述
                        //カラム名のみ先にcsvに記述

                        //$fp = fopen('./pydata/test.csv', 'w');
                        $test_filename = "./pydata/test_{$uniqueId}_{$timestamp}.csv";          #教師データ
                        $fp = fopen($test_filename, 'w');
                        $testdata_filename = "./pydata/testdata_{$uniqueId}_{$timestamp}.csv";  #テストデータ
                        $fp_test = fopen($testdata_filename, 'w');

                        fputcsv($fp, explode(',', $column_name));
                        foreach($allresult as $row){
                            fputcsv($fp, $row);
                        }
                        fclose($fp);

                        fputcsv($fp_test, explode(',', $column_name));
                        foreach($allresult_test as $row){
                            fputcsv($fp_test, $row);
                        }
                        fclose($fp_test);

                        echo "csvファイルを生成しました" . "ファイル名:" . $test_filename;
                    }else{
                        //javascriptでアラートを出す．
                        echo '<script type="text/javascript">alert("データを選択してください");</script>';
                    }


                }
                
                
            ?>
            <!--
            <section id = "class-overview" class="overview">
                <div align ="center">
                    <h2>学習者グループ概要</h2>
                </div>
                <font size = "5">
                    <div class="overview-contents">
                        <div id = "groupstu-info">
                            <h3>■グルーピング学習者数:
                                <?php
                                    // URLに学習者IDが含まれているか確認
                                    /*
                                    if (isset($_SESSION['group_students']) && !empty($_SESSION['group_students'])) {
                                        // `students`パラメータから学習者IDを取得して配列に変換
                                        $student_ids = explode(',', $_SESSION['group_students']);

                                        // 学習者IDをカウント
                                        $student_count = count($student_ids);

                                        // 学習者数を表示
                                        echo $student_count . "人";
                                    } else {
                                        // URLに学習者情報が含まれていない場合のメッセージ
                                        echo "学習者グループはありません";
                                    }
                                ?>
                            </h3>
                        </div>
                        <div id = "groupques-info">
                            <h3>■全データ数:
                                <?php
                                    // データベースからデータ数を取得
                                    // URLに学習者IDが含まれているか確認
                                    if (isset($_SESSION['group_students']) && !empty($_SESSION['group_students'])) {
                                        // `students`パラメータから学習者IDを取得して配列に変換
                                        $student_ids = explode(',', $_SESSION['group_students']);

                                        // `UID`リストをSQLクエリ用の文字列に変換
                                        $uid_list = implode("','", array_map('intval', $student_ids));

                                        // データベースから指定されたUIDに基づいて行数を取得
                                        $query = "SELECT COUNT(*) AS data_count FROM featurevalue1 WHERE UID IN ('$uid_list')";
                                        $result = mysqli_query($conn, $query);


                                        // データ数を取得して表示
                                        if ($result) {
                                            $row = mysqli_fetch_assoc($result);
                                            $data_count = $row['data_count'];
                                            echo $data_count . "件";
                                        } else {
                                            echo "データがありません";
                                        }
                                    } else {
                                        // URLに学習者情報が含まれていない場合のメッセージ
                                        echo "データがありません";
                                    }
                                        */
                                ?>
                            </h3>
                        </div>
                    </div>
                </font>
            </section>
                                -->
            <section class="group-chart">
        <h2>作成したグループの成績</h2>
        <div id="group-chart-container"></div>
    </section>

    <script>
        function openFeatureModalgraph(index, isOverall) {
                    console.log('index:', index);
                    selectedGroupIndex = index;
                    document.getElementById('feature-modal-graph').style.display = 'block';

                    // 特徴量選択後の適用ボタンに対して適切な配列とインデックスを設定
                    document.getElementById('apply-features-btn').onclick = function() {
                        applySelectedFeatures(isOverall ? existingOverallCharts : existingClassCharts, index, isOverall);
                    };
                }
                //モーダルを閉じる
                function closeFeatureModalgraph() {
                    document.getElementById('feature-modal-graph').style.display = 'none';
                    document.getElementById('feature-form').reset();
                }
        const groupData = <?php echo json_encode($groups); ?>;
        console.log(groupData);

        document.addEventListener("DOMContentLoaded", function () {
        const container = document.getElementById('group-chart-container');

        groupData.forEach((group, index) => {
            const groupContainer = document.createElement('div');
            groupContainer.classList.add('class-card');
            groupContainer.innerHTML = `
                <h3>${group.group_name}
                    <button onclick="openFeatureModalgraph(${index}, false)">グラフ描画特徴量</button>
                </h3>
                <div class="chart-row">
                    <canvas id="dual-axis-chart-${index}"></canvas>
                </div>
            `;

        container.appendChild(groupContainer);

        const labels = group.students.map(student => student.name);
        const notaccuracyData = group.students.map(student => student.notaccuracy);
        const timeData = group.students.map(student => student.time);
        //console.log(labels);
        //console.log(notaccuracyData);
        //console.log(timeData);

        createDualAxisChart(
            document.getElementById(`dual-axis-chart-${index}`).getContext('2d'),
            labels,
            notaccuracyData,
            timeData,
            '不正解率(%)',
            '解答時間(秒)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 99, 132, 0.6)',
            '不正解率(%)',
            '解答時間(秒)',
            existingClassCharts,  // クラス別グラフ用の配列
            index
        );
    });
});

    </script>
    <script>
        // クラス別グラフを管理する配列
        let existingClassCharts = [];
        function createDualAxisChart(ctx, labels, data1, data2, label1, label2, color1, color2, yText1, yText2, chartArray, chartIndex) {
    // 既存のチャートがある場合は破棄
    if (chartArray[chartIndex]) {
        chartArray[chartIndex].destroy();
    }

    // 新しいチャートを作成し、指定された配列に保存
    chartArray[chartIndex] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: label1,
                    data: data1,
                    backgroundColor: color1,
                    borderColor: color1,
                    yAxisID: 'y1',
                    borderWidth: 1
                },
                {
                    label: label2,
                    data: data2,
                    backgroundColor: color2,
                    borderColor: color2,
                    yAxisID: 'y2',
                    borderWidth: 1
                }
            ]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'ユーザー名',
                        font: {
                            size: 20
                        }
                    },
                    ticks: {
                        font: {
                            size: 16
                        }
                    }
                },
                y1: {
                    title: {
                        display: true,
                        text: yText1,
                        font: {
                            size: 20
                        }
                    },
                    ticks: {
                        font: {
                            size: 16
                        }
                    },
                    position: 'left',
                    beginAtZero: true
                },
                y2: {
                    title: {
                        display: true,
                        text: yText2,
                        font: {
                            size: 20
                        }
                    },
                    ticks: {
                        font: {
                            size: 16
                        }
                    },
                    position: 'right',
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    labels: {
                        font: {
                            size: 20
                        }
                    }
                }
            }
        }
    });
}

function applySelectedFeatures(chartArray, chartIndex, isOverall) {
                    const selectedFeatures = Array.from(document.querySelectorAll('#feature-form input[type="checkbox"]:checked'))
                        .map(input => input.value);
                    //console.log("applySelectedFeatures:", selectedFeatures);
                    //console.log("ChartArray:", chartArray);
                    //console.log("ChartIndex:", chartIndex);

                    // `notaccuracy`が選択されているか確認
                    if (selectedFeatures.includes('notaccuracy')) {
                        if (selectedFeatures.length !== 2) {
                            alert("2つの特徴量を選択してください。");
                            return;
                        }
                        const otherFeature = selectedFeatures.find(feature => feature !== 'notaccuracy');

                        // クライアント側のデータから不正解率データを取得
                        let group = isOverall ? classData[chartIndex] : groupData[chartIndex];
                        console.log('group:', group);
                        const labels = group.students.map(student => student.name);
                        const notaccuracyData = group.students.map(student => student.notaccuracy);

                        if (!otherFeature) {
                            alert("不正解率と一緒にもう1つの特徴量を選択してください。");
                            return;
                        }

                        // サーバーにリクエストするパラメータを設定（`notaccuracy`は含めない）
                        const studentIDs = isOverall
                            ? group.class_students.map(student => student.student_id).join(',')
                            : group.students.map(student => student.student_id).join(',');

                        const params = new URLSearchParams({
                            features: otherFeature,
                            studentIDs: studentIDs
                        });

                        // もう1つの特徴量のデータをfetchで取得
                        fetch('fetch_feature_data.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: params.toString()
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                console.error('サーバーエラー:', data.error);
                                alert(data.error);
                                return;
                            }

                            const otherFeatureData = data.map(item => item.featureA_avg);

                            const canvasId = isOverall
                                ? `class-dual-axis-chart-${chartIndex}`
                                : `dual-axis-chart-${chartIndex}`;

                            createDualAxisChart(
                                document.getElementById(canvasId).getContext('2d'),
                                labels,
                                notaccuracyData,
                                otherFeatureData,
                                '不正解率(%)',
                                `${otherFeature} 平均`,
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(255, 99, 132, 0.6)',
                                '不正解率(%)',
                                `${otherFeature} 平均`,
                                chartArray,
                                chartIndex
                            );

                            closeFeatureModalgraph();
                        })
                        .catch(error => {
                            console.error('エラー:', error);
                        });
                    } else {
                        // 通常の2つの特徴量での処理
                        if (selectedFeatures.length !== 2) {
                            alert("2つの特徴量を選択してください。");
                            return;
                        }

                        let group = isOverall ? classData[chartIndex] : groupData[chartIndex];

                        const studentIDs = isOverall
                            ? group.class_students.map(student => student.student_id).join(',')
                            : group.students.map(student => student.student_id).join(',');

                        const params = new URLSearchParams({
                            features: selectedFeatures.join(','),
                            studentIDs: studentIDs
                        });

                        fetch('fetch_feature_data.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: params.toString()
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) {
                                console.error('サーバーエラー:', data.error);
                                alert(data.error);
                                return;
                            }

                            const labels = data.map(item => item.name);
                            const featureAData = data.map(item => item.featureA_avg);
                            const featureBData = data.map(item => item.featureB_avg);

                            const canvasId = isOverall
                                ? `class-dual-axis-chart-${chartIndex}`
                                : `dual-axis-chart-${chartIndex}`;

                            createDualAxisChart(
                                document.getElementById(canvasId).getContext('2d'),
                                labels,
                                featureAData,
                                featureBData,
                                `${selectedFeatures[0]} 平均`,
                                `${selectedFeatures[1]} 平均`,
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(255, 99, 132, 0.6)',
                                `${selectedFeatures[0]} 平均`,
                                `${selectedFeatures[1]} 平均`,
                                chartArray,
                                chartIndex
                            );

                            closeFeatureModalgraph();
                        })
                        .catch(error => {
                            console.error('エラー:', error);
                        });
                    }
                }
    </script>


            <section class="progress-chart">
                <h2>特徴量選択</h2>
                <div id="feature-modal-area">
                    <button class="feature-button" onclick="openFeatureModal()">
                        <span class="icon">🔍</span> 特徴量を選択
                    </button>
                </div>
            </section>
            
            
            <script>
                function openFeatureModal(){
                    document.getElementById("feature-modal").style.display = "block";
                }

                function closeFeatureModal(){
                    document.getElementById("feature-modal").style.display = "none";
                }
            </script>
        
        <div id="feature-modal-graph" class = "modal">
                <div class="modal-content">
                    <span class="close" onclick="closeFeatureModalgraph()">&times;</span>
                    <h3>特徴量を選択してください</h3>
                    <form id="feature-form">
                        <label><input type="checkbox" name="feature" value="notaccuracy"> 不正解率 (%)</label><br>
                        <label><input type="checkbox" name="feature" value="Time"> 解答時間 (秒)</label><br>
                        <label><input type="checkbox" name="feature" value="distance"> 距離</label><br>
                        <label><input type="checkbox" name="feature" value="averageSpeed"> 平均速度</label><br>
                        <label><input type="checkbox" name="feature" value="maxSpeed"> 最高速度</label><br>
                        <label><input type="checkbox" name="feature" value="thinkingTime"> 考慮時間</label><br>
                        <label><input type="checkbox" name="feature" value="answeringTime"> 第一ドロップ後解答時間</label><br>
                        <label><input type="checkbox" name="feature" value="totalStopTime"> 合計静止時間</label><br>
                        <label><input type="checkbox" name="feature" value="maxStopTime"> 最大静止時間</label><br>
                        <label><input type="checkbox" name="feature" value="totalDDIntervalTime"> 合計DD間時間</label><br>
                        <label><input type="checkbox" name="feature" value="maxDDIntervalTime"> 最大DD間時間</label><br>
                        <label><input type="checkbox" name="feature" value="maxDDTime"> 合計DD時間</label><br>
                        <label><input type="checkbox" name="feature" value="minDDTime"> 最小DD時間</label><br>
                        <label><input type="checkbox" name="feature" value="DDCount"> 合計DD回数</label><br>
                        <label><input type="checkbox" name="feature" value="groupingDDCount"> グループ化DD回数</label><br>
                        <label><input type="checkbox" name="feature" value="groupingCountbool"> グループ化有無</label><br>
                        <label><input type="checkbox" name="feature" value="xUturnCount"> x軸Uターン回数</label><br>
                        <label><input type="checkbox" name="feature" value="yUturnCount"> y軸Uターン回数</label><br>
                        <label><input type="checkbox" name="feature" value="register_move_count1"> レジスタ➡レジスタへの移動回数</label><br>
                        <label><input type="checkbox" name="feature" value="register_move_count2"> レジスタ➡レジスタ外への移動回数</label><br>
                        <label><input type="checkbox" name="feature" value="register_move_count3"> レジスタ外➡レジスタへの移動回数</label><br>
                        <label><input type="checkbox" name="feature" value="register01count1"> レジスタ➡レジスタへの移動有無</label><br>
                        <label><input type="checkbox" name="feature" value="register01count2"> レジスタ外➡レジスタへの移動有無</label><br>
                        <label><input type="checkbox" name="feature" value="register01count3"> レジスタ外➡レジスタへの移動有無</label><br>
                        <label><input type="checkbox" name="feature" value="registerDDCount"> レジスタ外➡レジスタへの移動有無</label><br>
                        <label><input type="checkbox" name="feature" value="xUturnCountDD"> x軸UターンDD回数</label><br>
                        <label><input type="checkbox" name="feature" value="yUturnCountDD">y軸UターンDD回数</label><br>
                        <label><input type="checkbox" name="feature" value="FromlastdropToanswerTime"> レジスタ外➡レジスタへの移動有無DD</label><br>
                        <button type="button" id="apply-features-btn">適用</button>
                    </form>
                </div>
            </div>



            <div id = "feature-modal" class = "modal">
                <div class = "moda-content-machineLearning">
                    <span class = "close" onclick="closeFeatureModal()">&times;</span>
                    <form action="machineLearning_sample.php" id = "machineLearningForm" method="post" target="_blank">
                        <table class="table2">
                            <tr>
                                <th>使用データ</th>
                                <td>
                                    <label for="groupdata">
                                        <input type = "radio" class="feature-modal-checkbox" id = "groupdata" name = "useData" value = "groupdata">
                                        作成したグループデータのみ
                                    </label>
                                    <!--プルダウンメニュー-->
                                    
                                    <select id = "selectedGroup" name = "selectedGroup" style="display: none;">
                                        <option value = "">選択してください</option>
                                        <?php
                                        
                                            $sql = "SELECT g.group_id, g.group_name
                                                    FROM `groups` g
                                                    WHERE g.TID = ?";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param('s', $_SESSION['MemberID']);
                                            
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            
                                            while($row = $result -> fetch_assoc()){
                                                echo "<option value = '{$row['group_id']}'>{$row['group_name']}</option>";
                                            }
                                            $stmt -> close();
                                        ?>
                                    </select>
                                        
                                </td>
                                <script>
                                    
                                    document.addEventListener('DOMContentLoaded', () => {
                                        const groupDataRadio = document.getElementById('groupdata');
                                        const groupDropdown = document.getElementById('selectedGroup');
                                        const form = document.getElementById('machineLearningForm');

                                        

                                        // ラジオボタンのクリックイベント
                                        groupDataRadio.addEventListener('change', () => {
                                            if (groupDataRadio.checked) {
                                                groupDropdown.style.display = 'block'; // プルダウンを表示
                                            }
                                            
                                        });

                                        // プルダウンの選択イベント
                                        groupDropdown.addEventListener('change', () => {
                                            console.log("選択された値:", groupDropdown.value);
                                        });

                                        // 他のラジオボタンが選択された場合にプルダウンを隠す（他のラジオボタンの例）
                                        document.querySelectorAll('input[name="useData"]').forEach(radio => {
                                            if (radio.id !== 'groupdata') {
                                                radio.addEventListener('change', () => {
                                                    groupDropdown.style.display = 'none'; // プルダウンを非表示
                                                });
                                            }
                                        });
                                        // フォーム送信時のバリデーション
                                        form.addEventListener('submit', (e) => {
                                            if (groupDataRadio.checked && groupDropdown.value === '') {
                                                e.preventDefault();
                                                alert('作成したグループを選択してください。');
                                                groupDropdown.focus();
                                            }
                                        });
                                    });
                                
                                </script>
                                <td>
                                    <label for="alldata">
                                        <input type = "radio" class="feature-modal-checkbox" id = "alldata" name = "useData" value = "alalldata">
                                        2019年度のA大学全データ
                                    </label>
                                </td>
                            </tr>
                            <!--20250117消去-->
                            <!--ここから
                            <tr>
                                <th>UID</th>
                                <td>
                                    <select name="UIDrange">
                                        <option value = "include">含む</option>
                                        <option value = "not">以外</option>
                                    </select>
                                </td>
                                <td>
                                   ここにfeaturevalueテーブルのUIDをチェックボックスで表示
                                    <?php
                                    /*
                                        $sql = "SELECT distinct UID FROM featurevalue";
                                        $res = $conn->query($sql);
                                        $counter = 0; // カウンタを初期化
                                        while($rows = $res -> fetch_assoc()){
                                            echo "<input type='checkbox' name='UID[]' value = '{$rows['UID']}'>{$rows['UID']}";
                                            $counter++; // カウンタをインクリメント
                                            // カウンタが4の倍数になった時に改行を挿入
                                            if($counter % 4 == 0){
                                                echo "<br>";
                                            }
                                        }
                                        */
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>WID</th>
                                <td>
                                    <select name="WIDrange">
                                        <option value = "include">含む</option>
                                        <option value = "not">以外</option>
                                    </select>
                                </td>
                                <td>
                                    <?php
                                    /*
                                        $sql = "SELECT distinct WID FROM featurevalue";
                                        $res = $conn->query($sql);
                                        $counter = 0;
                                        while($rows = $res -> fetch_assoc()){
                                            echo "<input type='checkbox' name='WID[]' value = '{$rows['WID']}'>{$rows['WID']}";
                                            $counter++;
                                            if($counter % 10 == 0){
                                                echo "<br>";
                                            }
                                        }
                                        */
                                    ?>
                                </td>
                            </tr>
                            ここまで-->
                            <!-- 分類器選択ボタン -->
                            <tr>
                                <th>分類器選択</th>
                                <td colspan="2">
                                    <button type="button" onclick="selectClassifier('A')">分類器A</button>
                                    <button type="button" onclick="selectClassifier('B')">分類器B</button>
                                    <button type="button" onclick="selectClassifier('C')">分類器C</button>
                                </td>
                            </tr>
                            <tr>
                                <th>解答全体</th>
                                <td colspan="2">
                                    <ul class = "itemgroup">
                                        <li><label for="featuretime"><input type = "checkbox" class="feature-modal-checkbox" id = "featuretime" name = "featureLabel[]" value = "time">解答時間</label></li>
                                        <li><label for="featuredistance"><input type = "checkbox" class="feature-modal-checkbox" id = "featuredistance" name = "featureLabel[]" value = "distance">移動距離</label></li>
                                        <li><label for="featurespeed"><input type = "checkbox" class="feature-modal-checkbox" id ="featurespeed"  name = "featureLabel[]" value = "averageSpeed">平均速度</label></li>
                                        <li><label for="featuremaxspeed"><input type = "checkbox" class="feature-modal-checkbox" id ="featuremaxspeed" name = "featureLabel[]" value = "maxSpeed">最大速度</label></li>
                                    </ul>
                                    <ul class="itemgroup">
                                        <li><label for="totalstoptime"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "totalStopTime">合計静止時間</label></li>
                                        <li><label for="maxstoptime"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "maxStopTime">最大静止時間</label></li>

                                    </ul>
                                    <ul class="itemgroup">
                                        <li><label for="stopcount"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "stopcount">静止回数</label></li>
                                        <li><label for="FromlastdropToanswerTime"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "FromlastdropToanswerTime">最終dropから解答終了までの時間</label></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <th>Uターン</th>
                                <td colspan="2">
                                    <ul class="itemgroup">
                                        <li><label for="xUturncount"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "xUTurnCount">X軸Uターン回数</label></li>
                                        <li><label for="yUturncount"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "yUTurnCount">Y軸Uターン回数</label></li>
                                        <li><label for="xUturncountDD"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "xUTurnCountDD">次回DragまでのX軸Uターン回数</label></li>
                                        <li><label for="yUturncountDD"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "yUTurnCountDD">次回DragまでのY軸Uターン回数</label></li>
                                    </ul>
                                </td>
                            <tr>
                                <th>第一ドラッグ</th>
                                <td colspan="2">
                                    <ul class = "itemgroup">
                                        <li><label for="featurethinkingtime"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "thinkingTime">第一ドラッグ前時間</label></li>
                                        <li><label for="answeringtime"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "answeringTime">第一ドロップ後から解答終了を押すまでの時間</label></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <th>DD</th>
                                <td colspan="2">
                                    <ul class="itemgroup">
                                        <!--<li><label for="totalDDtime"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "totalDDTime">合計DD時間</label></li>-->
                                        <li><label for="maxDDtime"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "maxDDTime">最大DD時間</label></li>
                                        <li><label for="minDDtime"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "minDDTime">最小DD時間</label></li>
                                        <li><label for="DDcount"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "DDCount">DD回数</label></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <th>DD間</th>
                                <td colspan="2">
                                    <ul class="itemgroup">
                                        <li><label for="maxDDintervaltime"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "maxDDIntervalTime">最大DD間時間</label></li>
                                        <!--<li><label for="minDDintervaltime"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "minDDIntervalTime">最小DD間時間</label></li>-->
                                        <li><label for="totalDDintervaltime"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "totalDDIntervalTime">合計DD間時間</label></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <th>グループ化</th>
                                <td colspan="2">
                                    <ul class="itemgroup">
                                        <li><label for="groupingDDcount"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "groupingDDCount">グループ化中にDDした回数</label></li>
                                        <li><label for="groupingDDcountbool"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "groupingCountbool">グループ化の有無</label></li>
                                    </ul>
                                </td>
                            </tr>
                            <tr>
                                <th>レジスタ</th>
                                <td colspan="2">
                                    <ul class="itemgroup">        
                                        <li><label for="register_move_count1"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "register_move_count1">レジスタ移動回数1</label></li>
                                        <li><label for="register_move_count2"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "register_move_count2">レジスタ移動回数2</label></li>
                                        <li><label for="register_move_count3"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "register_move_count3">レジスタ移動回数3</label></li>
                                        <li><label for="register_move_count4"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "register_move_count4">レジスタ移動回数4</label></li>
                                    </ul>
                                    <ul class="itemgroup">
                                        <li><label for="register01count1"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "register01count1">レジスタ使用回数1</label></li>
                                        <li><label for="register01count2"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "register01count2">レジスタ使用回数2</label></li>
                                        <li><label for="register01count3"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "register01count3">レジスタ使用回数3</label></li>
                                        <li><label for="register01count4"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "register01count4">レジスタ使用回数4</label></li>
                                    </ul>
                                    <ul class="itemgroup">
                                        <li><label for="registerDDcount"><input type = "checkbox" class="feature-modal-checkbox" name = "featureLabel[]" value = "registerDDCount">レジスタ内DD回数</label></li>
                                    </ul>
                                </td>
                            </tr>
                        <!--</div>-->
                        </table>
                        <input type="submit" id="machineLearningcons" value="機械学習">
                        <button type="button" id="reset-button" onclick="resetCheckboxes()">リセット</button>
                    </form>
                </div>
            </div>
            <script>
                // 全てのチェックボックスをリセット（選択を解除）
                function resetCheckboxes() {
                    const checkboxes = document.querySelectorAll("input[type='checkbox']");
                    checkboxes.forEach(checkbox => checkbox.checked = false);
                }

                // 分類器を選択した時に該当する特徴量をチェックする関数
                function selectClassifier(classifier) {
                    resetCheckboxes();  // 全てのチェックボックスをリセット

                    // feature-modal内のチェックボックスを特定
                    const modalCheckboxes = document.querySelectorAll("#feature-modal .feature-modal-checkbox");

                    function checkFeature(value) {
                        modalCheckboxes.forEach(checkbox => {
                            if (checkbox.value === value) {
                                checkbox.checked = true;
                            }
                        });
                    }

                    // 分類器Aの特徴量
                    if (classifier === 'A') {
                        checkFeature('time');            // 解答時間
                        checkFeature('distance');        // 移動距離
                        checkFeature('averageSpeed');    // 平均速度
                        checkFeature('maxSpeed');        // 最大速度
                        checkFeature('thinkingTime');    // 第一ドラッグ前時間
                        checkFeature('answeringTime');   // 第一ドロップ後から解答終了までの時間
                        checkFeature('maxStopTime');     // 最大静止時間
                        checkFeature('xUTurnCount');     // X軸Uターン回数
                        checkFeature('yUTurnCount');     // Y軸Uターン回数
                        checkFeature('DDCount');         // D&D回数
                        checkFeature('maxDDTime');       // 最大D&D時間
                        checkFeature('maxDDIntervalTime'); // 最大D&D前時間
                        checkFeature('totalDDIntervalTime'); // 合計D&D間時間
                    }

                    // 分類器Bの特徴量（分類器Aに追加する特徴量）
                    if (classifier === 'B') {
                        selectClassifier('A');  // 分類器Aを選択
                        checkFeature('groupingDDCount');  // グループ化中にDDした回数
                        checkFeature('groupingCountbool');  // グループ化の有無
                    }

                    // 分類器Cの特徴量（分類器Aに追加する特徴量）
                    if (classifier === 'C') {
                        selectClassifier('A');  // 分類器Aを選択
                        checkFeature('register_move_count1');  // レジスタ移動回数1
                        checkFeature('register01count1');      // レジスタ使用回数1
                        checkFeature('register_move_count2');  // レジスタ移動回数2
                        checkFeature('register01count2');      // レジスタ使用回数2
                    }
                }

            </script>

            <section class="individual-details">
                <div class="machinelearning-result">
                    <h2>機械学習結果</h2>
                    <div class="contents">
                        <h3>解答情報</h3>       
                        <?php
                            require "../dbc.php";
                            if($_SERVER["REQUEST_METHOD"] == "POST"){
                                $pyscript = "./machineLearning/sampleSHAP.py";
                                $countF = 0;
                                $csvFile = "./machineLearning/results_actual_{$uniqueId}_{$timestamp}.csv";
                                $metricsFile = "./machineLearning/evaluation_metrics_{$uniqueId}_{$timestamp}.json";

                                //exec("python3 {$pyscript} {$test_filename} {$testdata_filename} {$csvFile} {$metricsFile} 2>&1", $output, $status);
                                exec("python {$pyscript} {$test_filename} {$testdata_filename} {$csvFile} {$metricsFile} 2>&1", $output, $status);  //XAMPP版に変更したもの
                                //デスクトップ版の方は情報を返してくれるプログラムをここに書いてる

                                if($status != 0){
                                    echo "実行エラー: ステータスコード " . $status;
                                    echo "エラーメッセージ:\n" . implode("\n", $output);
                                } else {
                                    // Pythonの実行が成功したら、結果のCSVをテーブルtemporary_resultsに格納
                                    $selectedFeatures = $_POST["featureLabel"];
                                    $details = [
                                        'selectedFeatures' => $selectedFeatures
                                    ];
                                    $resultPaths = [
                                        'csv_file' => $csvFile,
                                        'metrics_file' => $metricsFile,
                                    ];
                                    logActivity($conn, $_SESSION['MemberID'], 'machine_learning_completed', $details, $resultPaths);
                                    if (file_exists($metricsFile)) {
                                        $metrics = json_decode(file_get_contents($metricsFile), true);
                                    } else {
                                        //echo "評価指標のデータが見つかりません。";
                                    }
                                    if (($handle = fopen($csvFile, "r")) !== FALSE) {
                                        // CSVファイル全体を読み込む
                                        // 最初の行はヘッダーとして取得
                                        $header = fgetcsv($handle, 1000, ",");
                                        // 既存データを削除
                                        $deleteQuery = "DELETE FROM temporary_results WHERE teacher_id = ?";
                                        $stmtDelete = $conn->prepare($deleteQuery);
                                        $stmtDelete->bind_param("i", $_SESSION['MemberID']);
                                        $stmtDelete->execute();
                                        $stmtDelete->close();
                                        //挿入用クエリを準備
                                        $insertquery = "INSERT INTO temporary_results (UID,WID,Understand,teacher_id,attempt)
                                                        VALUES (?,?,?,?,?)";
                                        $csvData = [];
                                        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                            $csvData[] = $data; // 全てのデータを配列に保存
                                            //データベースに書き込む
                                            $stmt = $conn->prepare($insertquery);
                                            $stmt->bind_param("iiisi", $data[0], $data[1], $data[2], $_SESSION['MemberID'], $data[3]);
                                            $stmt->execute();

                                        }
                                        fclose($handle);
                                        $stmt->close();
                                        // 全データを取得
                                        $topData = $csvData;  // 全データを $topData に割り当て
                                        // 正解率、不正解率を保存している配列
                                        $studentStats = []; // UIDをキーにしたデータ構造
                                        // UIDごとの迷い率を計算するためにデータを集計
                                        $uidData = []; // UIDごとのデータを格納
                                        


                                        foreach ($csvData as $data) {
                                            $uid = $data[0];
                                            $understand = $data[2]; // Predictes_Understand カラム

                                            if (!isset($uidData[$uid])) {
                                                $uidData[$uid] = [
                                                    'total' => 0,
                                                    'hesitate' => 0,
                                                ];
                                            }
                                            $uidData[$uid]['total']++;
                                            if ($understand == 2) { // 迷い有り
                                                $uidData[$uid]['hesitate']++;
                                            }
                                        }

                                        // データベースから名前や正解率、不正解率を取得し、迷い率を追加
                                        foreach ($uidData as $uid => $counts) {
                                            // 名前を取得
                                            $getNameQuery = "SELECT Name FROM students WHERE UID = ?";
                                            $stmt = $conn->prepare($getNameQuery);
                                            $stmt->bind_param("i", $uid);
                                            $stmt->execute();
                                            $nameResult = $stmt->get_result();
                                            $name = $nameResult->fetch_assoc()['Name'];

                                            // 正解率、不正解率を計算 (linedataテーブルを使用)
                                            $getAccuracyQuery = "SELECT COUNT(*) AS total_answers, 
                                                                SUM(CASE WHEN TF = 1 THEN 1 ELSE 0 END) AS correct_answers 
                                                                FROM linedata WHERE UID = ?";
                                            $stmt = $conn->prepare($getAccuracyQuery);
                                            $stmt->bind_param("i", $uid);
                                            $stmt->execute();
                                            $accuracyresult = $stmt->get_result();
                                            $scoreData = $accuracyresult->fetch_assoc();
                                            $totalAnswers = $scoreData['total_answers'];
                                            $correctAnswers = $scoreData['correct_answers'];
                                            $accuracyRate = $totalAnswers > 0 ? ($correctAnswers / $totalAnswers) * 100 : 0;
                                            $notAccuracyRate = 100 - $accuracyRate;

                                            // 迷い率を計算
                                            $total = $counts['total'];
                                            $hesitate = $counts['hesitate'];
                                            $hesitationRate = ($total > 0) ? ($hesitate / $total) * 100 : 0;

                                            // 配列にデータを追加
                                            $studentStats[$uid] = [
                                                'uid' => $uid,
                                                'name' => $name,
                                                'accuracy' => number_format($accuracyRate, 2),
                                                'notAccuracy' => number_format($notAccuracyRate, 2),
                                                'hesitation' => number_format($hesitationRate, 2),
                                            ];
                                        }
                                        $stmt -> close();
                                        // 以下、データ表示の処理

                                        //データベースから正解率と各特徴量の平均値をとってきて，学習者ごとに配列に保存
                                        //重複無しでUIDを取得
                                        $sql_UID = "SELECT DISTINCT UID FROM temporary_results WHERE teacher_id = ?";
                                        $stmt_UID = $conn->prepare($sql_UID);
                                        $stmt_UID->bind_param("i", $_SESSION['MemberID']);
                                        $stmt_UID->execute();
                                        $res_UID = $stmt_UID->get_result();
                                        $UIDs = [];
                                        while($row_UID = $res_UID->fetch_assoc()){
                                            $UIDs[] = $row_UID['UID'];
                                        }
                                        //取得した学習者ID表示，つまり$UIDsを表示
                                        /*
                                        foreach($UIDs as $UID){
                                            echo "{$UID}<br>";
                                        }
                                        */
                                        //取得した学習者IDごとにtest_featurevalueから特徴量を取得して平均値を計算する
                                        //Understand，attempt，date，checkは計算しない
                                        //accuracyとUnderstandは別途計算．test_featurevalueには入っていないため
                                        //accuracyはlinedataのTFを使って計算
                                        //Understandはtemporary_resultsのUnderstandを使って計算
                                        $sql_feature = "SELECT * FROM test_featurevalue WHERE UID = ?";
                                        $stmt_feature = $conn->prepare($sql_feature);
                                        $average_feature_values = [];
                                        foreach($UIDs as $UID){
                                            $stmt_feature->bind_param("i", $UID);
                                            $stmt_feature->execute();
                                            $res_feature = $stmt_feature->get_result();
                                            $feature_values = [];
                                            while($row_feature = $res_feature->fetch_assoc()){
                                                foreach($row_feature as $feature_name => $feature_value){
                                                    if($feature_name == "UID" || $feature_name == "Understand" || $feature_name == "attempt" || $feature_name == "date" || $feature_name == "check"){
                                                        continue;
                                                    }
                                                    if(!isset($feature_values[$feature_name])){
                                                        $feature_values[$feature_name] = 0;
                                                    }
                                                    $feature_values[$feature_name] += $feature_value;
                                                }
                                            }
                                            //平均値を計算
                                            $average_feature_values[$UID] = [];
                                            foreach($feature_values as $feature_name => $feature_value){
                                                $average_feature_values[$UID][$feature_name] = $feature_value / $res_feature->num_rows;
                                            }
                                        }
                                        //正解率計算
                                        //正解率を取得
                                        $sql_accuracy = "SELECT COUNT(*) AS total_answers, SUM(CASE WHEN TF = 1 THEN 1 ELSE 0 END) AS correct_answers FROM linedata WHERE UID = ?";
                                        $stmt_accuracy = $conn->prepare($sql_accuracy);
                                        $accuracy_values = [];
                                        foreach($UIDs as $UID){
                                            $stmt_accuracy->bind_param("i", $UID);
                                            $stmt_accuracy->execute();
                                            $res_accuracy = $stmt_accuracy->get_result();
                                            $accuracyRow = $res_accuracy->fetch_assoc();  // 一度だけ fetch_assoc() を呼ぶ
                                            // NULLチェック
                                            $accuracy_values[$UID] = [
                                                'total_answers' => $accuracyRow ? $accuracyRow['total_answers'] : 0,
                                                'correct_answers' => $accuracyRow ? $accuracyRow['correct_answers'] : 0,
                                            ];
                                        }
                                        //average_feature_valuesに正解率を追加
                                        //不正解率も追加
                                        foreach($average_feature_values as $UID => $features){
                                            $accuracy_rate = $accuracy_values[$UID]['total_answers'] > 0 ? ($accuracy_values[$UID]['correct_answers'] / $accuracy_values[$UID]['total_answers']) * 100 : 0;
                                            $average_feature_values[$UID]['accuracy'] = $accuracy_rate;
                                            $average_feature_values[$UID]['notAccuracy'] = 100 - $accuracy_rate;
                                        }
                                        //迷い率を取得
                                        //迷い率を取得
                                        $sql_hesitation = "SELECT Understand FROM temporary_results WHERE UID = ? AND teacher_id = ?";
                                        $stmt_hesitation = $conn->prepare($sql_hesitation);
                                        $hesitation_values = [];
                                        foreach($UIDs as $UID){
                                            $stmt_hesitation->bind_param("ii", $UID, $_SESSION['MemberID']);
                                            $stmt_hesitation->execute();
                                            $res_hesitation = $stmt_hesitation->get_result();
                                            $total = 0;
                                            $hesitate = 0;
                                            while($row_hesitation = $res_hesitation->fetch_assoc()){
                                                $total++;
                                                if($row_hesitation['Understand'] == 2){
                                                    $hesitate++;
                                                }
                                            }
                                            $hesitation_rate = $total > 0 ? ($hesitate / $total) * 100 : 0;
                                            $hesitation_values[$UID] = $hesitation_rate;
                                        }
                                        //average_feature_valuesに迷い率を追加
                                        foreach($average_feature_values as $UID => $features){
                                            $average_feature_values[$UID]['hesitation'] = $hesitation_values[$UID];
                                        }

                                        
                                        //取得した平均値を表示
                                        /*
                                        foreach($average_feature_values as $UID => $features){
                                            echo "{$UID}<br>";
                                            foreach($features as $feature_name => $feature_value){
                                                echo "{$feature_name}:{$feature_value}<br>";
                                            }
                                        }
                                        */
                                        



                                        ?>
                                        <div id = "table-container">
                                            <table border="1" id="results-table" class="table2">
                                                <tr>
                                        <?php
                                        foreach ($header as $col_name) {
                                            if($col_name == "Understand"){
                                                echo "<th>迷いの有無</th>";
                                            }else if($col_name == "attempt"){
                                                continue;
                                            }else{
                                                echo "<th>" . htmlspecialchars($col_name) . "</th>";
                                            }
                                        }
                                        echo "<th>正誤</th>";
                                        echo "<th>軌跡再現リンク</th>";
                                        echo '</tr>';
                                        foreach ($topData as $data) {
                                            $uid = $data[0];
                                            $wid = $data[1];
                                            $understand = $data[2];
                                            $attempt = $data[3];
                                            //var_dump($uid);
                                            //var_dump($wid);

                                            // linedata テーブルから該当する UID と WID に基づいて TF を取得
                                            $getTFQuery = "SELECT TF FROM linedata WHERE UID = ? AND WID = ?";
                                            $stmt = $conn->prepare($getTFQuery);
                                            $stmt->bind_param('ii', $uid, $wid);
                                            $stmt->execute();
                                            $tf_result = $stmt->get_result();
                                            $tf_result = $tf_result->fetch_assoc();
                                            $tf_value = $tf_result['TF'];

                                            // HTMLテーブルに行を追加
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($uid) . "</td>";
                                            //widの横に-attemptを追加
                                            echo "<td>" . htmlspecialchars($wid) . "-" . htmlspecialchars($attempt) . "</td>";
                                            
                                            echo "<td>";
                                            if ($understand == 4) {
                                                echo "迷い無し";
                                            } elseif ($understand == 2) {
                                                echo "<span style='color: red; font-weight: bold;'>迷い有り</span>";
                                            } else {
                                                echo "不明";
                                            }
                                            echo "</td>";
                                            echo "<td>";
                                            
                                            if ($tf_value == '1') {
                                                echo "正解";
                                            } elseif ($tf_value == '0') {
                                                echo "<span style='color: red; font-weight: bold;'>不正解</span>";
                                            } else {
                                                echo "N/A";
                                            }
                                            echo "</td>";
                                            //echo "<td><a href=\"./mousemove/mousemove.php?uid=" . urlencode($uid) . "&wid=" . urlencode($wid) . "\">軌跡再現</a></td>";
                                            echo "<td><a href=\"./mousemove/mousemove.php?UID=" . urlencode($uid) . "&WID=" . urlencode($wid) . "\" target=\"_blank\" rel=\"noopener noreferrer\">軌跡再現</a></td>";
                                            echo "</tr>";
                                        }
                                        echo '</table>';
                                    } else {
                                        echo "結果のCSVファイルを読み込めませんでした。";
                                    }
                                }
                            }
                        ?>
                        </div>
                    </div>
                </div>
                <div id="clustering-modal" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="closeClusteringModal()">&times;</span>
                        <form id="clustering-feature-form">
                        <h3>クラスタ数を入力してください</h3>
                            <input type="number" id="clustering-input" min="1" max="10" value="2">
                        <h3>クラスタリング特徴量を選択してください</h3>
                            <label><input type="checkbox" name="feature" value="notAccuracy"> 不正解率 (%)</label><br>
                            <label><input type="checkbox" name="feature" value="hesitation"> 迷い率</label><br>
                            <button type="button" id="apply-clustering-btn">適用</button>
                        </form>
                    </div>
                </div>
                <script>
                    // クラスタリングモーダルを開く
                    function openClusteringModal(index) {
                        document.getElementById('clustering-modal').style.display = 'block';
                    }

                    // クラスタリングモーダルを閉じる
                    function closeClusteringModal() {
                        document.getElementById('clustering-modal').style.display = 'none';
                        document.getElementById('clustering-feature-form').reset();
                    }
                    // 特徴量を送信してクラスタリングを実行
                    document.getElementById('apply-clustering-btn').onclick = function () {
                        const selectedFeatures = Array.from(document.querySelectorAll('#clustering-feature-form input[type="checkbox"]:checked'))
                            .map(input => input.value);
                        if (selectedFeatures.length !== 2) {
                            alert("2つの特徴量を選択してください。");
                            return;
                        }
                        // クラスタ数を取得
                        const clusterCount = document.getElementById('clustering-input').value;

                        // studentStatsから必要なデータを収集
                        const studentData = <?php echo json_encode(array_values($studentStats)); ?>;

                        const params = new URLSearchParams({
                            features: selectedFeatures.join(','),
                            clusterCount: clusterCount,  // クラスタ数を追加
                            studentData: JSON.stringify(studentData)
                        });

                        fetch('perform_clustering_hesitate_accuracy.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: params.toString()
                        })
                            .then(response => response.text()) // JSON の代わりにテキストとして受け取る
                            .then(data => {
                                //console.log("サーバーからのレスポンス:", data); // レスポンスを確認
                                try {
                                    jsonData = JSON.parse(data); // JSON に変換
                                    if (jsonData.error) {
                                        alert(jsonData.error);
                                        return;
                                    }
                                    closeClusteringModal();
                                    displayClusteringResultsFromJSON(jsonData,selectedFeatures);
                                    displayClusteringResults_groupFromJSON(jsonData); // 追加
                                } catch (e) {
                                    console.error('JSON 解析エラー:', e);
                                    console.error('レスポンス内容:', data);
                                }
                            })
                            .catch(error => {
                                console.error('エラー:', error);
                                alert('クラスタリング中にエラーが発生しました。もう一度お試しください。');
                            });

                    };
                    function displayClusteringResults_groupFromJSON(jsonData) {
                        const container = document.getElementById('cluster-data');
                        //console.log(jsonData);
                        if (!container) {
                            console.error('cluster-data コンテナが見つかりません。');
                            return;
                        }

                        // クラスタごとのデータを格納
                        const clusters = {};
                        jsonData.forEach(student => {
                            const cluster = student.cluster;
                            if (!clusters[cluster]) {
                                clusters[cluster] = [];
                            }
                            clusters[cluster].push(student);
                        });

                        // クラスタごとに表示
                        Object.keys(clusters).forEach(clusterKey => {
                            const students = clusters[clusterKey];

                            // クラスタ情報のコンテナを作成
                            const clusterDiv = document.createElement('div');
                            clusterDiv.className = 'cluster-group';
                            clusterDiv.style.marginBottom = '5px';
                            clusterDiv.style.padding = '10px';
                            clusterDiv.style.borderRadius = '5px';

                            // チェックボックスとクラスタタイトル
                            const clusterHeader = document.createElement('h3');
                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.value = clusterKey;
                            checkbox.className = 'cluster-checkbox';

                            clusterHeader.textContent = `クラスタ ${clusterKey}`;
                            clusterHeader.prepend(checkbox);
                            clusterDiv.appendChild(clusterHeader);

                            // 学生リストを表示
                            const studentList = document.createElement('ul');
                            studentList.style.listStyleType = 'none';
                            studentList.style.paddingLeft = '0';

                            students.forEach(student => {
                                const listItem = document.createElement('li');
                                listItem.textContent = `UID: ${student.uid}`;
                                studentList.appendChild(listItem);
                            });

                            clusterDiv.appendChild(studentList);
                            container.appendChild(clusterDiv);
                        });

                        // グループ化ボタンを作成
                        const groupButton = document.createElement('button');
                        groupButton.textContent = 'グループ化';
                        groupButton.style.marginTop = '10px';
                        groupButton.onclick = () => {
                            groupSelectedClusters(clusters);
                        };
                        container.appendChild(groupButton);
                    }

                    // グループ化する関数
                    function groupSelectedClusters(clusters) {
                        const selectedCheckboxes = document.querySelectorAll('.cluster-checkbox:checked');

                        if (selectedCheckboxes.length === 0) {
                            alert('少なくとも1つのクラスタを選択してください。');
                            return;
                        }

                        // 選択されたクラスタごとのデータを収集
                        const clustersData = [];
                        selectedCheckboxes.forEach(checkbox => {
                            const clusterKey = checkbox.value;
                            const clusterName = `クラスタ ${clusterKey}`;  // クラスタ名をそのままグループ名に使用
                            const clusterData = clusters[clusterKey];
                            const studentIds = clusterData.map(student => student.uid);

                            clustersData.push({
                                group_name: clusterName,
                                students: studentIds
                            });
                        });

                        // サーバーにリクエストを送信
                        fetch('group_students.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(clustersData)  // JSON形式で送信
                        })
                        .then(response => response.text())
                        .then(data => {
                            alert('選択されたクラスタのグループ化が完了しました。');
                            //console.log(data);
                            // ページ再読み込み
                            window.location.reload();
                        })
                        .catch(error => {
                            console.error('エラー:', error);
                            alert('グループ登録中にエラーが発生しました。');
                        });
                    }

                    function displayClusteringResultsFromJSON(jsonData, selectedFeatures) {
                        const container = document.getElementById('cluster-data');
                        if (!container) {
                            console.error('cluster-data コンテナが見つかりません。');
                            return;
                        }
                        container.innerHTML = ''; // 前の内容をクリア

                        // 新しい Canvas を作成
                        const canvas = document.createElement('canvas');
                        canvas.id = 'cluster-visualization';
                        canvas.style.maxwidth = 800;
                        canvas.style.maxheight = 400;
                        container.appendChild(canvas);

                        const ctx = canvas.getContext('2d');

                        // クラスタごとの色を定義（不足分はランダムで生成）
                        const clusterColors = [
                            'rgba(255, 0, 0, 0.7)',  // クラスタ0の色(赤)
                            'rgba(0, 255, 0, 0.7)', // クラスタ1の色（青）
                            'rgba(0, 0, 255, 0.7)', // クラスタ2の色（緑）
                            'rgba(255, 255, 0, 0.7)', // クラスタ3の色（黄）
                            'rgba(113, 0, 255, 0.7)', // クラスタ4の色（紫）
                        ];

                        // クラスタ数が色の数を超えた場合、自動で色を追加
                        function getClusterColor(index) {
                            if (index < clusterColors.length) {
                                return clusterColors[index];
                            }
                            // ランダムで色を生成
                            return `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.7)`;
                        }

                        // 各クラスタのデータポイントを格納
                        const datasets = {};
                        jsonData.forEach(student => {
                            const cluster = student.cluster;
                            if (!datasets[cluster]) {
                                datasets[cluster] = {
                                    label: `Cluster ${cluster}`,
                                    data: [],
                                    backgroundColor: getClusterColor(cluster),
                                    pointRadius: 6
                                };
                            }
                            datasets[cluster].data.push({
                                x: parseFloat(student[selectedFeatures[0]]),
                                y: parseFloat(student[selectedFeatures[1]]),
                                label: `UID: ${student.uid}`
                            });
                        });

                        // Chart.js用のデータセット
                        const scatterDatasets = Object.values(datasets);

                        // 既存のチャートがある場合は破棄
                        if (window.clusteringChartInstance) {
                            window.clusteringChartInstance.destroy();
                        }

                        // Chart.jsで散布図を描画
                        window.clusteringChartInstance = new Chart(ctx, {
                            type: 'scatter',
                            data: {
                                datasets: scatterDatasets
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: 'top'
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return `${context.raw.label}: (${context.raw.x}, ${context.raw.y})`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        title: {
                                            display: true,
                                            text: selectedFeatures[0]
                                        }
                                    },
                                    y: {
                                        title: {
                                            display: true,
                                            text: selectedFeatures[1]
                                        }
                                    }
                                }
                            }
                        });
                    }



                </script>

                <div class="class-data" id="group-data-container">
                    <div class="class-card">
                        <h3>
                            <button onclick="openClusteringModal(0)">クラスタリング</button>
                        </h3>
                        <div class="chart-row">
                            <canvas id="result-Chart"></canvas>
                        </div>
                        <div id="clustering-results-container" class="clustering-results">
                            <!-- クラスタリング結果をここに表示 -->
                        </div>
                    </div>
                    
                </div>
        </section>
        
        <div id = "detail-info" class = "class-card">
            <h2>学習者の詳細情報</h2>
                <!---プルダウンメニュー--->
                <label for = "uid-select">学習者名:(UID)</label>
                <select id = "uid-select">
                    <option value = "">選択してください</option>
                    <?php
                        
                        $getUsersQuery = "SELECT DISTINCT tr.uid,s.Name FROM temporary_results tr 
                                            LEFT JOIN students s ON tr.uid = s.uid 
                                            WHERE teacher_id = ?";
                        $stmt = $conn->prepare($getUsersQuery);
                        $stmt->bind_param("i", $_SESSION['MemberID']);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['uid'] . '">' . $row['Name'] . ' (' . $row['uid'] . ')</option>';
                        }
                        $stmt->close();
                    ?>
                </select>
                <!-- 学習者情報の表示 -->
                <div id="student-details">
                    <div id ="student-details-maininfo"></div>
                    <div id = "student-details-grammar"></div>
                </div>
                <label for = "wid-select"></label>
                <select id = "wid-select">
                    <option value = "">選択してください</option>
                </select>
                <div id = "wid-details">
                    <div id = "wid-details-maininfo-stu"></div>
                    <div id = "wid-details-maininfo-all"></div>
                <script>
                    //uidが選択されたときにwidを表示するためのscript
                    document.addEventListener('DOMContentLoaded', function() {
                        const uidSelect = document.getElementById('uid-select');
                        const widSelect = document.getElementById('wid-select');
                        const studentDetailsmaininfo = document.getElementById('student-details-maininfo');
                        const widDetailsmaininfostu = document.getElementById('wid-details-maininfo-stu');
                        const widDetailsmaininfoall = document.getElementById('wid-details-maininfo-all');

                        //学習者選択時の処理
                        uidSelect.addEventListener('change', async function() {
                            const selectedUid = uidSelect.value;

                            //プルダウンのリセット
                            widSelect.innerHTML = '<option value = "">ロード中</option>';
                            if(!selectedUid){
                                //学習者が選択されていない場合
                                widSelect.innerHTML = '<option value = "">学習者を選択してください</option>';
                                studentDetailsmaininfo.innerHTML = '<p>学習者情報を選択してください。</p>';
                                return;
                            }
                            try{
                                //サーバーからデータを取得
                                //問題データの取得
                                const widResponse = await fetch(`get_wid.php?uid=${selectedUid}`);
                                if(!widResponse.ok){
                                    throw new Error(`HTTP error! status: ${widResponse.status}`);
                                }
                                const widData = await widResponse.json();
                                //プルダウンメニューを更新
                                widSelect.innerHTML = '<option value = "">選択してください</option>';
                                widData.forEach(wid => {
                                                            widSelect.innerHTML += `<option value="${wid.WID}">
                                                                ${wid.WID}: ${wid.Sentence}: 難易度${wid.level}: 迷い:${wid.Understand} 
                                                                ${wid.Understand === '迷い有り' ? '(★)' : ''}
                                                            </option>`;
                                                        });



                                //**学習者情報の取得 */
                                
                                const studentResponse = await fetch(`get_student_info.php?uid=${selectedUid}`);
                                if(!studentResponse.ok){
                                    throw new Error(`HTTP error! status: ${studentResponse.status}`);
                                }
                                const studentData = await studentResponse.json();
                                const studentDatainfo = studentData.userinfo;
                                console.log("student",studentData);
                                console.log("studentinfo",studentDatainfo);
                                console.log("Name:" , studentDatainfo.Name);

                                // 学習者情報の表示/
                                studentDetailsmaininfo.innerHTML = `
                                                <div id = "student-info-title" style = "display:flex; gap: 10px;">
                                                <h3>学習者名:${studentDatainfo.Name}</h3>
                                                <h3>クラス名:${studentDatainfo.ClassID}</h3>
                                                <h3>TOEICレベル:${studentDatainfo.toeic_level}</h3>
                                                <h3>英検レベル:${studentDatainfo.eiken_level}</h3>
                                                </div>

                                                <div id = "student-info-accuracy" style = "display:flex; gap: 10px;">
                                                <p>総解答数:${studentDatainfo.total_answers}</p>
                                                <p>正解率:${studentDatainfo.accuracy}%</p>
                                                <p>迷い率:${studentDatainfo.hesitation_rate}%</p>
                                                </div>
                                                `;
                                //文法項目データを表示する関数
                                displayGrammarStats(studentData.grammarStats);
                            }catch(error){
                                widSelect.innerHTML = '<option value = "">エラー</option>';
                                console.error(error);
                            }
                        });
                        //問題選択時の処理
                        widSelect.addEventListener('change', async function() {
        const selectedWid = this.value;
        const selectedUid = uidSelect.value;
        console.log("selectedWid", selectedWid);
        console.log("selectedUid", selectedUid);

        if(!selectedWid || !selectedUid){
            widDetailsmaininfostu.innerHTML = '<p>学習者情報を選択してください。</p>';
            return;
        }

        try{
            // 解答情報の取得
            const answerResponse = await fetch(`get_answer_info.php?uid=${selectedUid}&wid=${selectedWid}`);
            if(!answerResponse.ok){
                throw new Error(`HTTP error! status:${answerResponse.status}`);
            }
            const answerDetails = await answerResponse.json();
            console.log("answerDetails", answerDetails);

            // 初期データの取得
            const quesaccuracy = answerDetails.quesaccuracy ?? "N/A"; 
            const queshesitation_rate = answerDetails.queshesitation_rate ?? "N/A"; 
            const labelinfo = answerDetails.labelinfo;
            console.log("labelinfo", labelinfo); 

            const detailsArray = Object.values(answerDetails).filter(item => typeof item === "object" && Array.isArray(item) === false);

            const attempt1 = answerDetails.widinfo.find(detail => detail.attempt == 1);

            // attempt選択用のselect要素を作成
            const attemptSelect = document.createElement('select');
            attemptSelect.id = 'attempt-select';
            attemptSelect.innerHTML = '<option value="">選択してください</option>';
            answerDetails.widinfo.forEach(detail => {
                const option = document.createElement('option');
                option.value = detail.attempt;
                option.textContent = `Attempt ${detail.attempt}`;
                attemptSelect.appendChild(option);
            });

            // 全体表示の設定
            if (attempt1) {
                widDetailsmaininfoall.innerHTML = `
                    <div style="border: 1px solid #ccc; padding: 15px; border-radius: 8px; background-color: #f9f9f9;">
                        <h3 style="color: #333; text-align: center; margin-bottom: 20px;">問題情報</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                            <div style="flex: 1; min-width: 250px;">
                                <p><strong>正解率:</strong> ${quesaccuracy}%</p>
                                <p><strong>迷い率:</strong> ${queshesitation_rate}%</p>
                                <p><strong>正解文:</strong> ${attempt1.Sentence}</p>
                            </div>
                            <div style="flex: 1; min-width: 250px;">
                                <p><strong>日本語文:</strong> ${attempt1.Japanese}</p>
                                <p><strong>文法項目:</strong> ${attempt1.grammar}</p>
                                <p><strong>単語数:</strong> ${attempt1.wordnum}</p>
                            </div>
                        </div>
                    </div>
                `;

                // Label情報の表示
                if (labelinfo && Array.isArray(labelinfo) && labelinfo.length > 0) {
                    const tableContainer = document.createElement('div');
                    tableContainer.style = 'margin-top: 20px; width: 100%; display: flex; flex-direction: row; gap: 20px;';

                    const table = document.createElement('table');
                    table.innerHTML = `
                        <thead>
                            <tr style="background-color: #f0f0f0; border-bottom: 2px solid #ccc;">
                                <th style="padding: 10px;">グループ化された単語</th>
                                <th style="padding: 10px;">正解数</th>
                                <th style="padding: 10px;">不正解数</th>
                                <th style="padding: 10px;">迷いあり数</th>
                                <th style="padding: 10px;">迷いなし数</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    `;

                    const tbody = table.querySelector('tbody');
                    labelinfo.forEach(item => {
                        const row = document.createElement('tr');
                        row.style = "border-bottom: 1px solid #ddd;";

                        const cells = [
                            { value: item.Label, style: "padding: 10px;" },
                            { value: item.TF_1_Count, style: "padding: 10px; text-align: center;" },
                            { value: item.TF_0_Count, style: "padding: 10px; text-align: center;" },
                            { value: item.Understand_2_Count, style: "padding: 10px; text-align: center;" },
                            { value: item.Understand_4_Count, style: "padding: 10px; text-align: center;" }
                        ];

                        cells.forEach(cellData => {
                            const cell = document.createElement('td');
                            cell.textContent = cellData.value;
                            cell.style = cellData.style;
                            row.appendChild(cell);
                        });

                        tbody.appendChild(row);
                    });

                    tableContainer.appendChild(table);
                    widDetailsmaininfoall.appendChild(tableContainer);
                } else {
                    widDetailsmaininfoall.innerHTML += '<p>Label情報が見つかりませんでした。</p>';
                }
            } else {
                widDetailsmaininfoall.innerHTML = '<p>初期表示用のデータが見つかりません。</p>';
            }

            // widDetailsmaininfostu の設定
            widDetailsmaininfostu.innerHTML = ''; // 既存の内容をクリア

            // attemptSelect を追加
            widDetailsmaininfostu.appendChild(attemptSelect);

            // attempt-details コンテナを追加
            const attemptDetailsContainer = document.createElement('div');
            attemptDetailsContainer.id = 'attempt-details';
            widDetailsmaininfostu.appendChild(attemptDetailsContainer);

            // ★修正: Label 表示を含む詳細を組み立てる関数を用意
            function getAttemptDetailHTML(detail) {
                // detail.Label があればそのまま、なければ「グルーピングが行われていません」
                const labelText = detail.Label 
                    ? detail.Label 
                    : 'グルーピングが行われていません';

                return `
                    <p>回答日時: ${detail.Date}</p>
                    <p>最終回答文: ${detail.EndSentence}</p>
                    <p>解答時間: ${detail.Time}秒</p>
                    <p>正誤: ${detail.TF}</p>
                    <p>迷い: ${detail.Understand}</p>
                    <p>Label: ${labelText}</p>
                `;
            }

            // attempt=1 があれば初期表示
            if (attempt1) {
                attemptSelect.value = 1;
                attemptDetailsContainer.innerHTML = getAttemptDetailHTML(attempt1);
            } else {
                attemptDetailsContainer.innerHTML = '<p>試行回数 1 の情報が見つかりません。</p>';
            }

            // attemptSelect の change イベント
            attemptSelect.addEventListener('change', function () {
                console.log("Attempt changed");
                const selectedAttempt = this.value;
                console.log("selectedAttempt", selectedAttempt);
                const selectedDetail = answerDetails.widinfo.find(detail => detail.attempt == selectedAttempt);

                if (selectedDetail) {
                    // ★修正: getAttemptDetailHTML() で Label を含む情報を描画
                    attemptDetailsContainer.innerHTML = getAttemptDetailHTML(selectedDetail);
                } else {
                    attemptDetailsContainer.innerHTML = '<p>選択された試行回数の情報が見つかりません。</p>';
                }
            });

        } catch (error) {
            console.error(error);
            widDetailsmaininfostu.innerHTML = '<p>データの取得に失敗しました。</p>';
        }
                        });
                        function displayGrammarStats(grammarStats) {
                            
                            const grammarStatsDiv = document.getElementById('student-details-grammar');
                            console.log("grammarStats :" , grammarStats);
                            //追加
                            // 全体を横並びにするためのスタイル
                            grammarStatsDiv.style.display = 'flex';
                            grammarStatsDiv.style.flexDirection = 'row'; // 横並び
                            grammarStatsDiv.style.justifyContent = 'space-between'; // 要素間のスペースを調整
                            grammarStatsDiv.style.alignItems = 'flex-start'; // 上揃え

                            //追加
                            // テーブルHTMLの生成
                            let tableHTML = `
                                <div style="flex: 1; padding-right: 20px;"> <!-- テーブル用のdiv -->
                                    <table class = "table2">
                                        <thead>
                                            <tr>
                                                <th>文法項目</th>
                                                <th>総解答数</th>
                                                <th>正解数</th>
                                                <th>迷い数</th>
                                                <th>不正解率</th>
                                                <th>迷い率</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                            `;
                            /*
                            // テーブルヘッダー
                            let tableHTML = `
                                <table border="1">
                                    <thead>
                                        <tr>
                                            <th>文法項目</th>
                                            <th>総解答数</th>
                                            <th>正解数</th>
                                            <th>迷い数</th>
                                            <th>正解率</th>
                                            <th>迷い率</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;
                            */
                            // グラフ用のデータ準備
                            const labels = [];
                            const accuracyData = [];
                            const hesitationData = [];

                            // 各文法項目のデータをテーブル行として追加
                            for (const [grammar, stats] of Object.entries(grammarStats)) {
                                notaccuracy_grammar = (100 - stats.accuracy).toFixed(2);
                                tableHTML += `
                                    <tr>
                                        <td>${stats.grammar}</td>
                                        <td>${stats.total_answers}</td>
                                        <td>${stats.correct_answers}</td>
                                        <td>${stats.hesitate_count}</td>
                                        <td>${notaccuracy_grammar}%</td>
                                        <td>${stats.hesitation_rate}%</td>

                                    </tr>
                                `;
                                // グラフ用のデータ追加
                                labels.push(stats.grammar);
                                accuracyData.push(notaccuracy_grammar);
                                hesitationData.push(stats.hesitation_rate);
                            }
                            /*
                            // テーブルフッター
                            tableHTML += `
                                    </tbody>
                                </table>
                            `;
                            */
                            // テーブル閉じタグ
                            tableHTML += `
                                        </tbody>
                                    </table>
                                </div>
                            `;
                            /*
                            // グラフ用のキャンバス要素追加
                        tableHTML += `
                            <canvas id="grammarChart"></canvas>
                        `;
                        */
                        // グラフ用のHTML
    const chartHTML = `
        <div style="flex: 1;"> <!-- グラフ用のdiv -->
            <canvas id="grammarChart"></canvas>
        </div>
    `;

                            // HTMLに設定
                            grammarStatsDiv.innerHTML = tableHTML + chartHTML;
                            // グラフの描画
                            const ctx = document.getElementById('grammarChart').getContext('2d');
                            new Chart(ctx, {
                                type: 'bar', // 棒グラフを指定
                                data: {
                                    labels: labels,
                                    datasets: [
                                        {
                                            label: '不正解率 (%)',
                                            data: accuracyData,
                                            backgroundColor: 'rgba(75, 192, 192, 0.6)', // 青系
                                            borderColor: 'rgba(75, 192, 192, 1)',
                                            borderWidth: 1,
                                        },
                                        {
                                            label: '迷い率 (%)',
                                            data: hesitationData,
                                            backgroundColor: 'rgba(255, 99, 132, 0.6)', // 赤系
                                            borderColor: 'rgba(255,99,132,1)',
                                            borderWidth: 1,
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    plugins: {
                                        title: {
                                            display: true,
                                            text: '文法項目ごとの正解率と迷い率',
                                            font: {
                                                size: 20, // フォントサイズを24pxに設定
                                            }
                                        },
                                        tooltip: {
                                            mode: 'index',
                                            intersect: false,
                                            callbacks: {
                                                label: function(context) {
                                                    return `${context.dataset.label}: ${context.parsed.y}%`;
                                                }
                                            }
                                        },
                                        legend: {
                                            position: 'top',
                                            labels: {
                                                font: {
                                                    size: 20, // 凡例のフォントサイズを16pxに設定
                                                },
                                                color: '#333', // 凡例のテキストの色を設定（オプション）
                                            }
                                        },
                                    },
                                    scales: {
                                        x: {
                                            title: {
                                                display: true,
                                                text: '文法項目',
                                                font: {
                                                size: 20, // Y軸ラベルのフォントサイズを20pxに設定
                                            }
                                                
                                            },
                                            stacked: false, // グループ化のために積み上げなし
                                        },
                                        y: {
                                            beginAtZero: true,
                                            max: 100,
                                            title: {
                                                display: true,
                                                text: '割合 (%)',
                                                font: {
                                                size: 20, // Y軸ラベルのフォントサイズを20pxに設定
                                            },
                                            color: '#333', // Y軸ラベルの色を設定（オプション）
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    });
                    
                </script>
        </div>
                    
                <div id = "cluster-data"></div>
            <script>
                function createDualAxisChart(ctx, labels, data1, data2, label1, label2, color1, color2, yText1, yText2, chartArray, chartIndex) {
                    // 既存のチャートがある場合は破棄
                    if (chartArray[chartIndex]) {
                        chartArray[chartIndex].destroy();
                    }

                    // 新しいチャートを作成し、指定された配列に保存
                    chartArray[chartIndex] = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: label1,
                                    data: data1,
                                    backgroundColor: color1,
                                    borderColor: color1,
                                    yAxisID: 'y1',
                                    borderWidth: 1
                                },
                                {
                                    label: label2,
                                    data: data2,
                                    backgroundColor: color2,
                                    borderColor: color2,
                                    yAxisID: 'y2',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'ユーザー名',
                                        font: {
                                            size: 20
                                        }
                                    },
                                    ticks: {
                                        font: {
                                            size: 16
                                        }
                                    }
                                },
                                y1: {
                                    title: {
                                        display: true,
                                        text: yText1,
                                        font: {
                                            size: 20
                                        }
                                    },
                                    ticks: {
                                        font: {
                                            size: 16
                                        }
                                    },
                                    position: 'left',
                                    beginAtZero: true
                                },
                                y2: {
                                    title: {
                                        display: true,
                                        text: yText2,
                                        font: {
                                            size: 20
                                        }
                                    },
                                    ticks: {
                                        font: {
                                            size: 16
                                        }
                                    },
                                    position: 'right',
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                legend: {
                                    labels: {
                                        font: {
                                            size: 20
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                // PHPからstudentStatsを取得
                const studentData = <?php echo json_encode(array_values($studentStats)); ?>;
                //console.log(studentData); // デバッグ用

                if (studentData.length > 0) {
                    const labels = studentData.map(data => data.name);
                    const notAccuracyRates = studentData.map(data => parseFloat(data.notAccuracy));
                    const hesitationRates = studentData.map(data => parseFloat(data.hesitation));

                    const ctx = document.getElementById('result-Chart').getContext('2d');
                    const chartArray = []; // チャート配列を管理
                    createDualAxisChart(
                        ctx,
                        labels,
                        notAccuracyRates,
                        hesitationRates,
                        '不正解率 (%)',
                        '迷い率 (%)',
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        '不正解率 (%)',
                        '迷い率 (%)',
                        chartArray,
                        0 // インデックスは0で管理
                    );
                } else {
                    document.getElementById('result-Chart').textContent = "まだ迷い推定が行われていません";
                }
            </script>
        </main>
    </div>
</body>
</html>
