# 勤怠管理アプリ

## 概要
Laravelを用いて開発した勤怠管理アプリです。
一般ユーザーによる出勤・退勤・休憩の打刻、勤怠修正申請、管理者による勤怠修正・承認機能を提供します。

## 環境構築

1. リポジトリをクローン
```bash
git clone git@github.com:urbexsaku/kintai-app.git
```
2. Docker Desktopを起動

3. プロジェクト直下で、以下のコマンドを実行する
```bash
make init
```

※ make initでは以下を実行します。
- Dockerコンテナのビルド・起動
- Composerパッケージのインストール
- 環境変数ファイルの作成
- アプリケーションキーの生成
- storageおよびbootstrap/cacheの権限設定

4. make init 実行後、以下を実行してください。

```bash
make fresh
```

※ データベースの初期化およびテストデータ投入を実行します

## 使用技術 (実行環境)

- PHP 8.1
- Laravel 8.75
- MySQL 8.0.26
- Nginx 1.21.1

## ER図

![ER図](erd.drawio.png)

## URL

- 一般ユーザー用ログインページ：http://localhost/login
- 管理者用ログインページ：http://localhost/admin/login
- phpMyAdmin：http://localhost:8080

## テスト用アカウント

| ユーザー | メールアドレス | パスワード | メール認証 | 権限 |
|----------|----------------|------------|------------|------|
| ユーザー1 | user1@example.com | password | 済み | 一般ユーザー |
| ユーザー2 | user2@example.com | password | 済み | 一般ユーザー |
| ユーザー3 | user3@example.com | password | 済み | 管理者 |

## テスト実行方法

```bash
docker compose exec php php artisan test
```
