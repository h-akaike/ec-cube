<?php

namespace Customize\Controller;

use Customize\Service\TicketService;
use Eccube\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TicketMyPageController extends AbstractController
{
    /** @var TicketService */
    private $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * マイページ：チケット残高・消化履歴
     *
     * @Route("/mypage/ticket", name="mypage_ticket", methods={"GET"})
     * @Template("Ticket/mypage.twig")
     */
    public function index(Request $request): array
    {
        $Customer = $this->getUser();

        $remainingHours = $this->ticketService->getRemainingHours($Customer);
        $balances = $this->ticketService->getActiveBalances($Customer);
        $usageHistory = $this->ticketService->getUsageHistory($Customer);

        return [
            'remainingHours' => $remainingHours,
            'balances' => $balances,
            'usageHistory' => $usageHistory,
        ];
    }
}
