<?php
    session_start(); //データを次の画面に引き継ぐために使用
    require '../../common/validation.php';
    require '../../common/database.php';

    // パラメータ取得
    $user_name = $_POST['user_name'];
    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];

    // バリデーション
    $_SESSION['errors'] = [];

    // - 空チェック
    emptyCheck($_SESSION['errors'], $user_name, "ユーザー名を入力してください。");
    emptyCheck($_SESSION['errors'], $user_email, "メールアドレスを入力してください。");
    emptyCheck($_SESSION['errors'], $user_password, "パスワードを入力してください。");

    // - 文字数チェック
    stringMaxSizeCheck($_SESSION['errors'], $user_name, "ユーザー名は255文字以内で入力してください。");
    stringMaxSizeCheck($_SESSION['errors'], $user_email, "メールアドレスは255文字以内で入力してください。");
    stringMaxSizeCheck($_SESSION['errors'], $user_password, "パスワードは255文字以内で入力してください。");
    stringMinSizeCheck($_SESSION['errors'], $user_password, "パスワードは8文字以上で入力してください。");

    if(!$_SESSION['errors']) {
        // - メールアドレスチェック
        mailAddressCheck($_SESSION['errors'], $user_email, "正しいメールアドレスを入力してください。");

        // - ユーザー名・パスワード半角英数チェック
        halfAlphanumericCheck($_SESSION['errors'], $user_name, "ユーザー名は半角英数字で入力してください。");
        halfAlphanumericCheck($_SESSION['errors'], $user_password, "パスワードは半角英数字で入力してください。");

        // - メールアドレス重複チェック
        mailAddressDuplicationCheck($_SESSION['errors'], $user_email, "既に登録されているメールアドレスです。");
    }

    if($_SESSION['errors']) {
        header('Location: ../../user/');
        exit;
    }

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

          // ユーザー情報保持
          $_SESSION['user'] = [
            'name' => $user_name,
            'id' => $database_handler->lastInsertId()
          ];
      }
    } catch (Throwable $e) {
        echo $e->getMessage();
        exit;
    }

    // メモ投稿画面にリダイレクト(index.php は省略できるようにdocker環境で設定されている)
    header('Location: ../../memo/');
    exit;