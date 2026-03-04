# CLAUDE.md

## プロジェクト概要

Atomic Link チケット販売システム。クライアントが事前にポイント（時間）を購入し、Web制作・デザイン・DXコンサル等の業務をポイントから消化する仕組み。

## 技術スタック

| カテゴリ | 技術 |
|---------|------|
| EC プラットフォーム | EC-CUBE 4.3.x |
| 言語 | PHP 8.1+ |
| フレームワーク | Symfony 6.4 |
| テンプレート | Twig |
| データベース | MySQL 8.0 |
| コンテナ | Docker / Docker Compose |
| メール（開発用） | MailCatcher |

## リポジトリ構成

EC-CUBE 公式リポジトリを fork し、カスタマイズブランチ (`feature/ticket-system`) で開発。

```
h-akaike/ec-cube (fork of EC-CUBE/ec-cube)
├── origin   → h-akaike/ec-cube     (push先)
└── upstream → EC-CUBE/ec-cube      (本家、アップデート取り込み用)
```

## ディレクトリ構成

```
ec-cube/                        # EC-CUBE 4.3（fork）
├── app/Customize/              # カスタマイズコード（ここに追加する）
├── app/template/               # テンプレートのカスタマイズ
├── app/DoctrineMigrations/     # マイグレーション
├── src/Eccube/                 # EC-CUBE コア（直接編集しない）
├── bin/console                 # Symfony コンソール
├── docker-compose.yml          # Docker Compose 設定
├── docs/                       # 設計ドキュメント
├── scripts/                    # ユーティリティスクリプト
└── .claude/                    # Claude Code 設定
```

## 開発ルール

### カスタマイズ方針

- EC-CUBE コア（`src/Eccube/`）は直接編集しない
- カスタマイズは `app/Customize/` 配下で行う（Entity, Repository, Controller, Form, Service 等）
- テンプレートのカスタマイズは `app/template/` で行う
- プラグイン機構の活用を優先し、本体改修は最小限にする

### Docker 操作

```bash
# コンテナ起動
docker compose -f docker-compose.yml up -d

# Symfony コンソール
docker compose -f docker-compose.yml exec -u www-data ec-cube bin/console <command>

# キャッシュクリア
docker compose -f docker-compose.yml exec -u www-data ec-cube bin/console cache:clear

# データベースマイグレーション
docker compose -f docker-compose.yml exec -u www-data ec-cube bin/console doctrine:migrations:migrate
```

### アクセス先

- フロント画面: http://localhost:8080
- 管理画面: http://localhost:8080/admin (admin / password)
- MailCatcher: http://localhost:1080
- MySQL: localhost:13306 (dbuser / secret / eccubedb)

## テスト

```bash
# EC-CUBE のテスト実行
docker compose -f docker-compose.yml exec -u www-data ec-cube vendor/bin/phpunit

# カスタマイズのテスト
docker compose -f docker-compose.yml exec -u www-data ec-cube vendor/bin/phpunit tests/Eccube/Tests/Customize/

# 特定テストファイル
docker compose -f docker-compose.yml exec -u www-data ec-cube vendor/bin/phpunit tests/path/to/Test.php
```

## 注意事項

- `ECCUBE_AUTH_MAGIC` や DB パスワードは開発環境用。本番では必ず変更する
- EC-CUBE のアップデート追従を考慮し、コアの変更は避ける
- PHP のコーディングは PSR-12 に準拠する
