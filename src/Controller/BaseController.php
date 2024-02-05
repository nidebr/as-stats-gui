<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\ConfigApplication;
use App\Form\TopAsForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\LocaleSwitcher;

abstract class BaseController extends AbstractController
{
    protected ConfigApplication $configApplication;
    protected array $base_data = [];

    public function __construct(
        ConfigApplication $configApplication,
        RequestStack $requestStack,
        LocaleSwitcher $localeSwitcher,
    ) {
        $this->configApplication = $configApplication;
        $this->base_data = self::getBaseData($requestStack);

        $localeSwitcher->setLocale($this->configApplication::getLangage());
    }

    private function getBaseData(
        RequestStack $requestStack,
    ): array {
        try {
            $request = $requestStack->getCurrentRequest()->query->all(); /* @phpstan-ignore-line */
            $top = $this->configApplication::getAsStatsConfigTop();

            if (\array_key_exists('top', $request)) {
                if ($request['top'] > 200) {
                    $top = $request['top'] = 200;
                } elseif (0 !== (int) $request['top']) {
                    $top = (int) $request['top'];
                }
            }

            return [
                'release' => $this->configApplication::getRelease(),
                'top_interval' => $this->configApplication::getAsStatsConfigTopInterval(),
                'request' => $request,
                'top' => $top,
            ];
        } catch (\Exception) {
            return [
                'release' => null,
                'top_interval' => [],
                'request' => [],
                'top' => null,
            ];
        }
    }

    public function addFormTopAs(Request $request): FormView
    {
        $form = $this->createForm(TopAsForm::class);
        $form->handleRequest($request);

        return $form->createView();
    }
}
