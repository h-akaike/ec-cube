# 環境構築手順

## 前提条件

| ソフトウェア | バージョン | 確認コマンド |
|------------|-----------|-------------|
| Docker Desktop | 4.x 以上 | `docker --version` |
| Docker Compose | 2.x 以上（Docker Desktop に同梱） | `docker compose version` |
| Git | 2.x 以上 | `git --version` |

> **Windows の場合**: Docker Desktop の WSL 2 バックエンドモードを有効にし、Linux ファイルシステム上にワークスペースを作成することを推奨します。

## セットアップ手順

### 1. リポジトリのクローン

```bash
git clone https://github.com/your-org/atomic-link-ticket-system.git
cd atomic-link-ticket-system
```

### 2. 環境変数ファイルの作成

```bash
cp .env.example .env
```

必要に応じて `.env` の値を編集してください。

### 3. EC-CUBE ソースコードの取得

```bash
git clone https://github.com/EC-CUBE/ec-cube.git -b 4.3 ec-cube
```

### 4. Docker コンテナの起動

```bash
docker compose up -d
```

初回起動時は Docker イメージのダウンロードに数分かかります。

### 5. EC-CUBE の初期インストール

```bash
docker compose exec -u www-data ec-cube bin/console eccube:install -n
```

> **重要**: 必ず `-u www-data` オプションを付けて実行してください。`-n` は非対話モードです。

### 6. アクセス確認

| 画面 | URL |
|------|-----|
| フロント画面 | http://localhost:8080 |
| 管理画面 | http://localhost:8080/admin |
| MailCatcher（メール確認） | http://localhost:1080 |

### 管理画面ログイン情報

- ログインID: `admin`
- パスワード: `password`

## よくあるトラブルと対処法

### ポート 8080 が既に使用されている

他のアプリケーションがポート 8080 を使用している場合、`docker-compose.yml` のポートマッピングを変更してください。

```yaml
ports:
  - "9090:80"  # 8080 を 9090 に変更
```

### MySQL コンテナが起動しない

データボリュームが破損している可能性があります。ボリュームを削除して再起動してください。

```bash
docker compose down -v
docker compose up -d
```

### eccube:install でエラーが発生する

MySQL コンテナが完全に起動する前にインストールコマンドを実行すると失敗することがあります。数十秒待ってから再実行してください。

```bash
# MySQL の起動状態を確認
docker compose exec mysql mysqladmin ping -h localhost -u root -proot

# 起動を確認してからインストール
docker compose exec -u www-data ec-cube bin/console eccube:install -n
```

### パーミッションエラーが発生する

EC-CUBE の `var/` ディレクトリの権限が不適切な場合があります。

```bash
docker compose exec ec-cube chown -R www-data:www-data /var/www/html/var
docker compose exec ec-cube chmod -R 775 /var/www/html/var
```

### Windows (WSL2) でファイル変更が反映されない

WSL 2 環境では、Windows 側のファイルシステム（`/mnt/c/` 以下）で作業するとパフォーマンスが低下し、ファイル監視が正常に動作しないことがあります。Linux ファイルシステム（`~/` 以下）にプロジェクトを配置してください。

## コンテナの管理

```bash
# コンテナの停止
docker compose down

# コンテナの再起動
docker compose up -d

# ログの確認
docker compose logs -f ec-cube

# EC-CUBE コンテナに入る
docker compose exec -u www-data ec-cube bash

# MySQL に接続
docker compose exec mysql mysql -u dbuser -psecret eccubedb

# キャッシュのクリア
docker compose exec -u www-data ec-cube bin/console cache:clear
```
