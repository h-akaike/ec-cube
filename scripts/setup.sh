#!/bin/bash
# ==============================================
# Atomic Link Ticket System - 初期セットアップスクリプト
# ==============================================
# 使い方: bash scripts/setup.sh
#
# このスクリプトは以下を実行します:
# 1. EC-CUBE 4.3 のソースコードをクローン
# 2. 環境変数ファイルを作成
# 3. Docker コンテナを起動
# 4. EC-CUBE の初期インストール
# ==============================================

set -e

# 色付き出力
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN} Atomic Link Ticket System Setup${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# 前提条件チェック
echo -e "${YELLOW}[1/5] 前提条件を確認中...${NC}"

if ! command -v docker &> /dev/null; then
    echo -e "${RED}エラー: Docker がインストールされていません。${NC}"
    echo "https://www.docker.com/products/docker-desktop/ からインストールしてください。"
    exit 1
fi

if ! command -v git &> /dev/null; then
    echo -e "${RED}エラー: Git がインストールされていません。${NC}"
    exit 1
fi

if ! docker info &> /dev/null; then
    echo -e "${RED}エラー: Docker Desktop が起動していません。起動してから再実行してください。${NC}"
    exit 1
fi

echo -e "${GREEN}  Docker: $(docker --version)${NC}"
echo -e "${GREEN}  Docker Compose: $(docker compose version)${NC}"
echo -e "${GREEN}  Git: $(git --version)${NC}"
echo ""

# EC-CUBE ソースコードのクローン
echo -e "${YELLOW}[2/5] EC-CUBE 4.3 をクローン中...${NC}"

if [ -d "ec-cube" ]; then
    echo "  ec-cube/ ディレクトリは既に存在します。スキップします。"
else
    git clone https://github.com/EC-CUBE/ec-cube.git -b 4.3 ec-cube
    echo -e "${GREEN}  EC-CUBE 4.3 のクローンが完了しました。${NC}"
fi
echo ""

# 環境変数ファイルの作成
echo -e "${YELLOW}[3/5] 環境変数ファイルを作成中...${NC}"

if [ -f ".env" ]; then
    echo "  .env ファイルは既に存在します。スキップします。"
else
    cp .env.example .env
    echo -e "${GREEN}  .env ファイルを作成しました。${NC}"
fi
echo ""

# Docker コンテナの起動
echo -e "${YELLOW}[4/5] Docker コンテナを起動中...${NC}"
docker compose up -d
echo ""

# MySQL の起動を待機
echo "  MySQL の起動を待機中..."
until docker compose exec mysql mysqladmin ping -h localhost -u root -proot --silent 2>/dev/null; do
    echo "  MySQL 起動中..."
    sleep 3
done
echo -e "${GREEN}  MySQL が起動しました。${NC}"
echo ""

# EC-CUBE のインストール
echo -e "${YELLOW}[5/5] EC-CUBE を初期インストール中...${NC}"
docker compose exec -u www-data ec-cube bin/console eccube:install -n
echo ""

# 完了メッセージ
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN} セットアップが完了しました!${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "  フロント画面:  http://localhost:8080"
echo "  管理画面:      http://localhost:8080/admin"
echo "  MailCatcher:   http://localhost:1080"
echo ""
echo "  管理画面ログイン:"
echo "    ID:       admin"
echo "    Password: password"
echo ""
echo -e "${YELLOW}コンテナの停止: docker compose down${NC}"
echo -e "${YELLOW}コンテナの再起動: docker compose up -d${NC}"
