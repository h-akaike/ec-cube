#!/bin/bash
# ==============================================
# GitHub リポジトリ初期化スクリプト
# ==============================================
# 使い方: bash scripts/init-github.sh
#
# 前提条件:
# - gh CLI がインストール済み（https://cli.github.com/）
# - gh auth login でログイン済み
# ==============================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

REPO_NAME="atomic-link-ticket-system"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN} GitHub リポジトリ初期化${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# gh CLI チェック
if ! command -v gh &> /dev/null; then
    echo -e "${RED}エラー: gh CLI がインストールされていません。${NC}"
    echo "https://cli.github.com/ からインストールしてください。"
    exit 1
fi

# 認証チェック
if ! gh auth status &> /dev/null; then
    echo -e "${RED}エラー: gh CLI にログインしていません。${NC}"
    echo "以下のコマンドでログインしてください:"
    echo "  gh auth login"
    exit 1
fi

echo -e "${YELLOW}[1/3] GitHub リポジトリを作成中...${NC}"

# Organization があるかチェック
echo ""
echo "リポジトリの作成先を選択してください:"
echo "  1) 個人アカウント"
echo "  2) Organization"
read -p "選択 (1/2): " choice

if [ "$choice" = "2" ]; then
    read -p "Organization 名を入力: " org_name
    gh repo create "${org_name}/${REPO_NAME}" --private --description "Atomic Link チケット（ポイント）販売システム - EC-CUBE 4 ベース"
else
    gh repo create "${REPO_NAME}" --private --description "Atomic Link チケット（ポイント）販売システム - EC-CUBE 4 ベース"
fi

echo -e "${GREEN}  リポジトリを作成しました。${NC}"
echo ""

echo -e "${YELLOW}[2/3] Git リポジトリを初期化中...${NC}"

# git 初期化（ec-cube ディレクトリは除外されている前提）
git init
git add .
git commit -m "Initial commit: プロジェクト構成ファイルとドキュメント雛形を追加

- docker-compose.yml: EC-CUBE 4.3 + MySQL 8.0 + MailCatcher
- .env.example: 環境変数テンプレート
- .gitignore: EC-CUBE / Symfony 向け設定
- README.md: プロジェクト概要と開発手順
- docs/setup.md: 詳細な環境構築手順
- docs/architecture.md: カスタマイズ方針メモ（雛形）
- docs/ticket-design.md: チケット商品設計メモ（雛形）
- scripts/setup.sh: 自動セットアップスクリプト"

echo ""

echo -e "${YELLOW}[3/3] GitHub にプッシュ中...${NC}"

git branch -M main

if [ "$choice" = "2" ]; then
    git remote add origin "https://github.com/${org_name}/${REPO_NAME}.git"
else
    GITHUB_USER=$(gh api user -q '.login')
    git remote add origin "https://github.com/${GITHUB_USER}/${REPO_NAME}.git"
fi

git push -u origin main

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN} GitHub リポジトリの初期化が完了しました!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

if [ "$choice" = "2" ]; then
    echo "  リポジトリ: https://github.com/${org_name}/${REPO_NAME}"
else
    echo "  リポジトリ: https://github.com/${GITHUB_USER}/${REPO_NAME}"
fi
echo ""
echo "次のステップ:"
echo "  bash scripts/setup.sh  # 開発環境のセットアップ"
