<?php
$dsn = 'mysql:dbname=データベース名;host=localhost';
$user = 'ユーザー名';
$pass = 'パスワード';

try {
    $pdo = new PDO($dsn, $user, $pass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    // 新しいテーブルを作成
    $sql = "CREATE TABLE IF NOT EXISTS new_table (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        comment TEXT NOT NULL,
        date DATETIME NOT NULL
    )";
    $pdo->exec($sql);

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit();
}

$filename = 'm03-03.txt';

// 編集番号指定フォーム処理
if (isset($_POST['edit'])) {
    $editNumber = filter_input(INPUT_POST, 'editNumber');
    $editPassword = filter_input(INPUT_POST, 'editPassword');

    if (!empty($editNumber) && !empty($editPassword) && $editPassword === "kaeru") {
        // データベースから投稿を取得する
        $id = $editNumber;
        $sql = 'SELECT * FROM new_table WHERE id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        if ($row) {
            $editName = $row['name'];
            $editComment = $row['comment'];
        }
    }
}

// 投稿処理
if (isset($_POST['submit'])) {
    $name = filter_input(INPUT_POST, 'name');
    $comment = filter_input(INPUT_POST, 'comment');
    $password = filter_input(INPUT_POST, 'password');
    $editTargetNumber = filter_input(INPUT_POST, 'editTargetNumber');

    if (!empty($name) && !empty($comment) && $password === "okuru") {
        if (empty($editTargetNumber)) {
            // データベースに新しい投稿を挿入する
            $sql = $pdo->prepare("INSERT INTO new_table (name, comment, date) VALUES (:name, :comment, :date)");
            $sql->bindParam(':name', $name);
            $sql->bindParam(':comment', $comment);
            $date = getCurrentDateTime();
            $sql->bindParam(':date', $date);
            $sql->execute();
        } else {
            // データベースの投稿を更新する
            $id = $editTargetNumber;
            $sql = 'UPDATE new_table SET name=:name, comment=:comment WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':comment', $comment);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
}


// 削除処理
if (isset($_POST['delete'])) {
    $deleteNumber = filter_input(INPUT_POST, 'deleteNumber');
    $deletePassword = filter_input(INPUT_POST, 'deletePassword');

    if (!empty($deleteNumber) && !empty($deletePassword) && $deletePassword === "kesu") {
        // 投稿を削除する
        $id = $deleteNumber;
        $sql = 'DELETE FROM new_table WHERE id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>掲示板</title>
</head>
<body>
<?php
if (isset($editName) && isset($editComment)) {
    echo "<p>編集モード</p>";
} else {
    echo "<p>新規登録モード</p>";
}
?>

<form method="POST" action="">
    <label for="name">名前:</label>
    <input type="text" name="name" value="<?php echo isset($editName) ? $editName : ''; ?>"><br>
    <label for="comment">コメント:</label>
    <input type="text" name="comment" value="<?php echo isset($editComment) ? $editComment : ''; ?>"><br>
    <label for="password">パスワード:</label>
    <input type="password" name="password"><br>
    <input type="hidden" name="editTargetNumber" value="<?php echo isset($editNumber) ? $editNumber : ''; ?>">
    <input type="submit" name="submit" value="送信">
</form>

<form method="POST" action="">
    <label for="deleteNumber">削除対象番号:</label>
    <input type="number" name="deleteNumber"><br>
    <label for="deletePassword">パスワード:</label>
    <input type="password" name="deletePassword"><br>
    <input type="submit" name="delete" value="削除">
</form>

<form method="POST" action="">
    <label for="editNumber">編集対象番号:</label>
    <input type="number" name="editNumber"><br>
    <label for="editPassword">パスワード:</label>
    <input type="password" name="editPassword"><br>
    <input type="submit" name="edit" value="編集">
</form>

<?php
// 投稿データの表示
$sql = 'SELECT * FROM new_table';
$stmt = $pdo->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll();
foreach ($results as $row) {
    echo "投稿番号: " . $row['id'] . "<br>";
    echo "名前: " . $row['name'] . "<br>";
    echo "コメント: " . $row['comment'] . "<br>";
    echo "投稿日時: " . $row['date'] . "<br><br>";
}

?>

<?php
function getCurrentDateTime()
{
    return date('Y/m/d H:i:s');
}
?>

</body>
</html>
