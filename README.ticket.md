# Atomic Link チケット販売システム

Atomic Link（株式会社アトミックリンク）が提供するチケット（ポイント）販売システムです。クライアントが事前にポイントを購入し、Web制作・デザイン・DXコンサル等の各種業務をポイントから消化する仕組みを EC-CUBE 4 をベースに構築しています。

## 技術スタック

| カテゴリ | 技術 |
|---------|------|
| EC プラットフォーム | EC-CUBE 4.3.x |
| 言語 | PHP 8.1+ |
| フレームワーク | Symfony 6.4 |
| データベース | MySQL 8.0 |
| コンテナ | Docker / Docker Compose |
| メール（開発用） | MailCatcher |

## 開発環境のセットアップ

### 前提条件

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) がインストール済みであること
- [Git](https://git-scm.com/) がインストール済みであること
- Windows の場合は WSL 2 バックエンドモードの使用を推奨

### 手順

```bash
# 1. リポジトリをクローン
git clone https://github.com/your-org/atomic-link-ticket-system.git
cd atomic-link-ticket-system

# 2. 環境変数ファイルを作成
cp .env.example .env

# 3. EC-CUBE 4.3 のソースコードをクローン
git clone https://github.com/EC-CUBE/ec-cube.git -b 4.3 ec-cube

# 4. Docker コンテナを起動
docker compose up -d

# 5. EC-CUBE の初期インストール（初回のみ）
docker compose exec -u www-data ec-cube bin/console eccube:install -n

# 6. ブラウザでアクセス
# フロント画面:  http://localhost:8080
# 管理画面:      http://localhost:8080/admin
# MailCatcher:   http://localhost:1080
```

### 管理画面ログイン情報（開発環境）

| 項目 | 値 |
|------|-----|
| ログインID | `admin` |
| パスワード | `password` |

> **注意**: 本番環境では必ず変更してください。

### コンテナの停止・再起動

```bash
# 停止
docker compose down

# 再起動
docker compose up -d
```

## ディレクトリ構成

```
atomic-link-ticket-system/
├── docker-compose.yml        # Docker Compose 設定
├── .env.example              # 環境変数テンプレート
├── .gitignore                # Git 除外設定
├── README.md                 # このファイル
├── docs/
│   ├── setup.md              # 環境構築手順（詳細）
│   ├── architecture.md       # カスタマイズ方針メモ
│   └── ticket-design.md      # チケット商品の設計メモ
└── ec-cube/                  # EC-CUBE 4.3 ソースコード（git clone で取得）
```

## 今後の開発予定

- [ ] チケット商品の登録（10時間 / 20時間 / 50時間パック）
- [ ] 決済導入（クレジットカード / 銀行振込）
- [ ] ポイント残高管理機能（カスタマイズ or プラグイン開発）
- [ ] ワークフロー管理システムとの連携
- [ ] チャットボット連携
- [ ] 本番環境へのデプロイ

## ライセンス

EC-CUBE は GPL ライセンスの下で使用しています。

## 参考リンク

- [EC-CUBE 4 公式ドキュメント](https://doc4.ec-cube.net/)
- [EC-CUBE GitHub](https://github.com/EC-CUBE/ec-cube)
- [EC-CUBE インストール方法](https://doc4.ec-cube.net/quickstart/install)
