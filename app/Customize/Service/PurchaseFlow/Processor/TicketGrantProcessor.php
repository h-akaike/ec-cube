<?php

namespace Customize\Service\PurchaseFlow\Processor;

use Customize\Service\TicketService;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\Order;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Eccube\Service\PurchaseFlow\PurchaseProcessor;

/**
 * 注文確定時にチケット残高を自動付与する PurchaseProcessor
 */
class TicketGrantProcessor implements PurchaseProcessor
{
    /** チケット商品の判別キーワード */
    private const TICKET_KEYWORD = 'チケット';

    /** 商品名から時間数へのマッピング */
    private const HOUR_PATTERNS = [
        '10' => 10.0,
        '20' => 20.0,
        '50' => 50.0,
    ];

    /** @var TicketService */
    private $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * 仮確定処理（何もしない）
     */
    public function prepare(ItemHolderInterface $target, PurchaseContext $context)
    {
        // チケット付与は commit で行う
    }

    /**
     * 確定処理：チケット商品を検知して残高を追加
     */
    public function commit(ItemHolderInterface $target, PurchaseContext $context)
    {
        if (!$target instanceof Order) {
            return;
        }

        $Customer = $target->getCustomer();
        if ($Customer === null) {
            return;
        }

        foreach ($target->getOrderItems() as $orderItem) {
            $productName = $orderItem->getProductName();
            if ($productName === null) {
                continue;
            }

            if (mb_strpos($productName, self::TICKET_KEYWORD) === false) {
                continue;
            }

            $hours = $this->detectHours($productName);
            if ($hours <= 0) {
                continue;
            }

            $quantity = $orderItem->getQuantity();
            for ($i = 0; $i < $quantity; $i++) {
                $this->ticketService->addBalance($Customer, $hours, $target);
            }
        }
    }

    /**
     * ロールバック処理（何もしない - デモ用）
     */
    public function rollback(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        // デモ用のためロールバックは未実装
    }

    /**
     * 商品名から時間数を検出
     */
    private function detectHours(string $productName): float
    {
        foreach (self::HOUR_PATTERNS as $pattern => $hours) {
            if (mb_strpos($productName, $pattern) !== false) {
                return $hours;
            }
        }

        return 0.0;
    }
}
