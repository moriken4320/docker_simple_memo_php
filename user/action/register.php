<?php
    require '../../common/database.php';

    // パラメータ取得
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];

    // DB接続処理
    $database_handler = getDatabaseConnection();

    try {
      // インサートSQLを作成して実行
      // prepare = 用意
      if ($statement = $database_handler->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)')) {
        // password_hash = ハッシュ値を作成する(直接確認できないように)
          $password = password_hash($user_password, PASSWORD_DEFAULT);

          // htmlspecialchars = 特殊文字エスケープ
          $statement->bindParam(':name', htmlspecialchars($user_name));
          $statement->bindParam(':email', $user_email);
          $statement->bindParam(':password', $password);
          // execute = 実行
          $statement->execute();
      }
    } catch (Throwable $e) {
        echo $e->getMessage();
        exit;
    }

    // メモ投稿画面にリダイレクト(index.php は省略できるようにdocker環境で設定されている)
    header('Location: ../../memo/');
    exit;