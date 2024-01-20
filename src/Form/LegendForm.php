<?php

declare(strict_types=1);

namespace App\Form;

use App\Repository\KnowlinksRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LegendForm extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach (KnowlinksRepository::get() as $knowlink) {
            $builder
                ->add(\sprintf('%s', $knowlink['tag']), CheckboxType::class, [
                    'label' => false,
                    'translation_domain' => false,
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
