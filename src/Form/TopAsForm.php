<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class TopAsForm extends AbstractType
{
    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('get')
            ->add('top', IntegerType::class, [
                'label' => false,
                'translation_domain' => false,
                'attr' => [
                    'placeholder' => 'Top AS',
                ],
                'constraints' => [
                    new Range(
                        notInRangeMessage: 'Value between {{ min }} and {{ max }}.',
                        min: 1,
                        max: 200
                    ),
                ],
            ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
