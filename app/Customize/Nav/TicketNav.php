<?php

namespace Customize\Nav;

use Eccube\Common\EccubeNav;

class TicketNav implements EccubeNav
{
    public static function getNav(): array
    {
        return [
            'ticket' => [
                'name' => 'チケット管理',
                'icon' => 'fa-ticket',
                'children' => [
                    'ticket_balance' => [
                        'name' => 'チケット残高一覧',
                        'url' => 'admin_ticket_index',
                    ],
                    'ticket_usage' => [
                        'name' => '消化履歴',
                        'url' => 'admin_ticket_usage',
                    ],
                    'ticket_consume' => [
                        'name' => 'チケット消化記録',
                        'url' => 'admin_ticket_consume',
                    ],
                ],
            ],
        ];
    }
}
