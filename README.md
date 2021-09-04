# 2021年 システム開発 前期課題 掲示板サービス 構築手順書
依存ソフトウェア
この掲示板サービスを構築・実行するには，以下のソフトウェアが必要です。各環境にあわせて予め導入しておいてください。

git
Docker
Docker Compose
構築手順
## 1. ソースコードの設置
まずソースコードの設置を行います。


git clone git@github.com:T-2719/Sat_12.git
## 2. ビルドと起動
Docker Composeで管理するDockerコンテナ上で実行します。

docker-compose build
docker-compose up
## 3. テーブルの作成
データベース(MySQL)にテーブルを作成します。

起動中に，以下のコマンドでMySQLのCLIクライアントを起動してください。

docker exec -it mysql mysql techc
テーブルを作成するSQLは以下の通りです。


``CREATE TABLE `bbs`(
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `body` TEXT NOT NULL,
    `image_filename` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);
``

## 4. 動作確認
掲示板のページは public/bbs.php です。ブラウザから http://サーバーのアドレス/bbs.php にアクセスし，動作を確認してください。

構築手順は以上です。
