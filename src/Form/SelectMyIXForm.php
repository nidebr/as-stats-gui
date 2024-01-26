<?php

declare(strict_types=1);

namespace App\Form;

use App\Application\ConfigApplication;
use App\Client\PeeringDbClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectMyIXForm extends AbstractType
{
    public function __construct(private PeeringDbClient $peeringDbClient)
    {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $peering_data = $this->peeringDbClient->get(\sprintf('netixlan?asn=%s', ConfigApplication::getAsStatsConfigMyAsn()));

        $data = [];
        if (200 === $peering_data['status_code']) {
            foreach ($peering_data['response']['data'] as $myix) {
                $data[$myix['name']] = $myix['ix_id'];
            }
        }

        ksort($data);

        $builder
            ->add('myix', ChoiceType::class, [
                'label' => false,
                'choices' => $data,
                'choice_translation_domain' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
