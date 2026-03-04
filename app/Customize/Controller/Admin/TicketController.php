<?php

namespace Customize\Controller\Admin;

use Customize\Entity\TicketBalance;
use Customize\Entity\TicketUsage;
use Customize\Form\Type\Admin\TicketConsumeType;
use Customize\Repository\TicketBalanceRepository;
use Customize\Repository\TicketUsageRepository;
use Customize\Service\TicketService;
use Eccube\Controller\AbstractController;
use Eccube\Repository\CustomerRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends AbstractController
{
    /** @var TicketBalanceRepository */
    private $ticketBalanceRepository;

    /** @var TicketUsageRepository */
    private $ticketUsageRepository;

    /** @var TicketService */
    private $ticketService;

    /** @var CustomerRepository */
    private $customerRepository;

    public function __construct(
        TicketBalanceRepository $ticketBalanceRepository,
        TicketUsageRepository $ticketUsageRepository,
        TicketService $ticketService,
        CustomerRepository $customerRepository
    ) {
        $this->ticketBalanceRepository = $ticketBalanceRepository;
        $this->ticketUsageRepository = $ticketUsageRepository;
        $this->ticketService = $ticketService;
        $this->customerRepository = $customerRepository;
    }

    /**
     * チケット残高一覧
     *
     * @Route("/%eccube_admin_route%/ticket", name="admin_ticket_index", methods={"GET"})
     * @Template("@admin/Ticket/index.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator): array
    {
        $qb = $this->ticketBalanceRepository->createQueryBuilder('tb')
            ->leftJoin('tb.Customer', 'c')
            ->orderBy('tb.create_date', 'DESC');

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            20
        );

        // 顧客ごとの残高サマリーを計算
        $customerSummary = [];
        /** @var TicketBalance $balance */
        foreach ($pagination as $balance) {
            $customerId = $balance->getCustomer()->getId();
            if (!isset($customerSummary[$customerId])) {
                $customerSummary[$customerId] = [
                    'customer' => $balance->getCustomer(),
                    'total_remaining' => 0,
                ];
            }
            if (!$balance->isExpired()) {
                $customerSummary[$customerId]['total_remaining'] += $balance->getRemainingHours();
            }
        }

        return [
            'pagination' => $pagination,
            'customerSummary' => $customerSummary,
        ];
    }

    /**
     * 消化履歴一覧
     *
     * @Route("/%eccube_admin_route%/ticket/usage", name="admin_ticket_usage", methods={"GET"})
     * @Template("@admin/Ticket/usage.twig")
     */
    public function usage(Request $request, PaginatorInterface $paginator): array
    {
        $qb = $this->ticketUsageRepository->createQueryBuilder('tu')
            ->leftJoin('tu.Customer', 'c')
            ->orderBy('tu.create_date', 'DESC');

        $pagination = $paginator->paginate(
            $qb,
            $request->get('pageno', 1),
            20
        );

        return [
            'pagination' => $pagination,
        ];
    }

    /**
     * チケット消化記録フォーム
     *
     * @Route("/%eccube_admin_route%/ticket/consume", name="admin_ticket_consume", methods={"GET", "POST"})
     * @Template("@admin/Ticket/consume.twig")
     */
    public function consume(Request $request)
    {
        $form = $this->createForm(TicketConsumeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $Customer = $this->customerRepository->find($data['customer_id']);
            if ($Customer === null) {
                $this->addError('指定された顧客が見つかりません。', 'admin');

                return [
                    'form' => $form->createView(),
                ];
            }

            try {
                $this->ticketService->consumeTicket(
                    $Customer,
                    (float) $data['hours'],
                    $data['service_type'],
                    $data['description'] ?? '',
                    $data['work_date'],
                    $data['staff_name']
                );

                $this->addSuccess('チケットを消化しました。', 'admin');

                return $this->redirectToRoute('admin_ticket_usage');
            } catch (\RuntimeException $e) {
                $this->addError($e->getMessage(), 'admin');
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
