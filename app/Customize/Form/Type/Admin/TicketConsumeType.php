<?php

namespace Customize\Form\Type\Admin;

use Eccube\Repository\CustomerRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class TicketConsumeType extends AbstractType
{
    /** @var CustomerRepository */
    private $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer_id', TextType::class, [
                'label' => '顧客ID',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('hours', NumberType::class, [
                'label' => '消化時間数',
                'scale' => 1,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Positive(),
                ],
            ])
            ->add('service_type', ChoiceType::class, [
                'label' => 'サービス種別',
                'choices' => [
                    'Web制作' => 'Web制作',
                    'デザイン' => 'デザイン',
                    'DXコンサル' => 'DXコンサル',
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => '作業内容',
                'required' => false,
            ])
            ->add('work_date', DateType::class, [
                'label' => '作業実施日',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('staff_name', TextType::class, [
                'label' => '担当者名',
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ]);
    }
}
