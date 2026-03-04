# デモ用チケット管理システム 実装プラン

## 要件の整理

クライアントが**時間ベースのチケットパック**を購入し、Web制作・デザイン・DXコンサル等の業務をチケットから消化する仕組み。デモレベル（動作確認・プレゼン用）で以下を実現する:

- チケットパック商品の登録（10h / 20h / 50h）
- 購入後に顧客のチケット残高が自動加算
- 管理画面からチケット消化を記録
- 顧客マイページでチケット残高・消化履歴を閲覧
- 管理画面でチケット状況を一覧管理

## 前提条件

| 項目 | 決定事項 |
|------|---------|
| チケット粒度 | 1チケット = 1時間（0.5時間単位で消化可能） |
| パック種類 | 10h (¥100,000) / 20h (¥180,000) / 50h (¥400,000) |
| 有効期限 | 購入日から6ヶ月 |
| 消化順序 | FIFO（古いものから消化） |
| サービス種別 | Web制作 / デザイン / DXコンサル（消化レートは一律1:1） |
| カスタマイズ方式 | `app/Customize/` 配下でのカスタマイズ（コア非改修） |

## アーキテクチャ概要

```
┌─────────────────────────────────────────────┐
│                  Frontend                    │
│  ┌──────────────┐  ┌─────────────────────┐  │
│  │ マイページ    │  │ 管理画面            │  │
│  │ - 残高表示    │  │ - チケット一覧      │  │
│  │ - 消化履歴    │  │ - 消化記録          │  │
│  │ - パック購入  │  │ - 顧客別残高一覧    │  │
│  └──────────────┘  └─────────────────────┘  │
├─────────────────────────────────────────────┤
│                 Controller                   │
│  TicketMyPageController                      │
│  Admin\TicketController                      │
├─────────────────────────────────────────────┤
│                  Service                     │
│  TicketService（残高計算・消化処理）          │
├─────────────────────────────────────────────┤
│                  Entity                      │
│  TicketBalance / TicketUsage                 │
├─────────────────────────────────────────────┤
│               EC-CUBE Core                   │
│  Product / Order / Customer / PurchaseFlow   │
└─────────────────────────────────────────────┘
```

## データベース設計

### テーブル 1: `dtb_ticket_balance`（チケット残高）

| カラム | 型 | 説明 |
|--------|-----|------|
| id | INT (PK, AUTO_INCREMENT) | |
| customer_id | INT (FK → dtb_customer) | 顧客 |
| order_id | INT (FK → dtb_order) | 購入元の受注 |
| total_hours | DECIMAL(10,1) | 購入時間数 |
| used_hours | DECIMAL(10,1) | 消化済み時間数 |
| expires_at | DATETIME | 有効期限 |
| create_date | DATETIME | 作成日時 |
| update_date | DATETIME | 更新日時 |

### テーブル 2: `dtb_ticket_usage`（チケット消化履歴）

| カラム | 型 | 説明 |
|--------|-----|------|
| id | INT (PK, AUTO_INCREMENT) | |
| ticket_balance_id | INT (FK → dtb_ticket_balance) | 対象残高 |
| customer_id | INT (FK → dtb_customer) | 顧客 |
| hours | DECIMAL(10,1) | 消化時間数 |
| service_type | VARCHAR(50) | サービス種別 |
| description | TEXT | 作業内容 |
| work_date | DATE | 作業実施日 |
| staff_name | VARCHAR(100) | 担当者名 |
| create_date | DATETIME | 記録日時 |

## 実装フェーズ

### Phase 1: Entity & Repository（データ層）

**ファイル:**
- `app/Customize/Entity/TicketBalance.php` - チケット残高エンティティ
- `app/Customize/Entity/TicketUsage.php` - チケット消化履歴エンティティ
- `app/Customize/Repository/TicketBalanceRepository.php` - 残高リポジトリ
- `app/Customize/Repository/TicketUsageRepository.php` - 消化履歴リポジトリ

**ポイント:**
- EC-CUBE の `AbstractEntity` を継承
- Doctrine ORM アノテーションでマッピング
- Customer / Order とのリレーション定義

### Phase 2: Service（ビジネスロジック）

**ファイル:**
- `app/Customize/Service/TicketService.php`

**主な機能:**
- `addBalance(Customer, Order, float $hours)` - 残高追加（注文確定時）
- `consumeTicket(Customer, float $hours, string $serviceType, ...)` - チケット消化（FIFO）
- `getRemainingHours(Customer): float` - 残高合計取得
- `getBalances(Customer): array` - パック別残高一覧

### Phase 3: PurchaseFlow 連携（購入→残高自動加算）

**ファイル:**
- `app/Customize/Service/PurchaseFlow/Processor/TicketGrantProcessor.php`
- `app/Customize/Resource/config/services.yaml`（PurchaseFlow へのタグ登録）

**仕組み:**
- EC-CUBE の PurchaseFlow パイプラインに Processor を追加
- 注文確定時にチケット商品を検知し、自動で `TicketBalance` を作成
- チケット商品の判別: 商品名に「チケット」を含む、または専用カテゴリで判別

### Phase 4: 管理画面（チケット管理）

**ファイル:**
- `app/Customize/Controller/Admin/TicketController.php`
  - `index` - 顧客別チケット残高一覧
  - `usage` - 消化履歴一覧
  - `consumeForm` - 消化記録フォーム
  - `consume` - 消化実行
- `app/Customize/Form/Type/Admin/TicketConsumeType.php` - 消化記録フォーム
- `app/template/admin/Ticket/index.twig` - 残高一覧画面
- `app/template/admin/Ticket/usage.twig` - 消化履歴画面
- `app/template/admin/Ticket/consume.twig` - 消化記録画面
- `app/Customize/Nav/TicketNav.php` - 管理画面ナビ追加

### Phase 5: マイページ（顧客向け）

**ファイル:**
- `app/Customize/Controller/TicketMyPageController.php`
  - `index` - チケット残高・消化履歴表示
- `app/template/default/Ticket/mypage.twig` - マイページ画面

### Phase 6: チケット商品の初期データ

**ファイル:**
- `app/DoctrineMigrations/VersionXXXX_TicketSchema.php` - テーブル作成マイグレーション

**初期商品データ:**
- EC-CUBE 管理画面から手動登録（デモ用のため）
- 3種類のチケットパック商品を作成する手順書を用意

## ファイル一覧（全16ファイル）

```
app/Customize/
├── Controller/
│   ├── Admin/
│   │   └── TicketController.php          # 管理画面コントローラ
│   └── TicketMyPageController.php        # マイページコントローラ
├── Entity/
│   ├── TicketBalance.php                 # 残高エンティティ
│   └── TicketUsage.php                   # 消化履歴エンティティ
├── Form/
│   └── Type/
│       └── Admin/
│           └── TicketConsumeType.php     # 消化フォーム
├── Nav/
│   └── TicketNav.php                     # 管理画面ナビ
├── Repository/
│   ├── TicketBalanceRepository.php       # 残高リポジトリ
│   └── TicketUsageRepository.php         # 消化履歴リポジトリ
├── Service/
│   ├── TicketService.php                 # ビジネスロジック
│   └── PurchaseFlow/
│       └── Processor/
│           └── TicketGrantProcessor.php  # 購入時チケット付与
└── Resource/
    └── config/
        └── services.yaml                 # サービス定義

app/template/
├── admin/
│   └── Ticket/
│       ├── index.twig                    # 管理:残高一覧
│       ├── usage.twig                    # 管理:消化履歴
│       └── consume.twig                  # 管理:消化記録
└── default/
    └── Ticket/
        └── mypage.twig                   # マイページ

app/DoctrineMigrations/
└── Version20260304000000.php             # テーブル作成
```

## リスク・注意点

| リスク | 対策 |
|--------|------|
| チケット商品の判別方法 | デモでは商品名に「チケット」を含むかで判別（本番ではカスタムフィールド追加） |
| 有効期限切れの自動処理 | デモでは表示上の警告のみ（バッチ処理は本番で追加） |
| 同時消化の排他制御 | デモでは考慮しない（本番ではロック機構追加） |
| テストコード | デモ優先のためスキップ（本番前に必須追加） |

## 実装順序

1. **Phase 1** → Entity / Repository（データ基盤）
2. **Phase 6** → マイグレーション（テーブル作成）
3. **Phase 2** → Service（ビジネスロジック）
4. **Phase 3** → PurchaseFlow 連携（購入→残高加算）
5. **Phase 4** → 管理画面
6. **Phase 5** → マイページ

## デモシナリオ

1. 管理画面で 3種のチケットパック商品を登録
2. フロントでユーザー登録 → チケットパック購入
3. 注文確定 → チケット残高が自動加算されることを確認
4. 管理画面でチケット消化を記録
5. マイページで残高減少・消化履歴を確認
