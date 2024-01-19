<?php

declare(strict_types=1);

namespace App\Twig\EventSubscriber;

use App\Util\Annotation\Menu;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

readonly class MenuAnnotationControllerEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private Environment $twig)
    {
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'handle',
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function handle(ControllerEvent $event): void
    {
        $controllers = $event->getController();

        if (!is_array($controllers)) {
            return;
        }

        [$controller, $methodName] = $controllers;

        if (!$controller instanceof AbstractController) {
            return;
        }

        $methodAttribute = $this->getMethodeAttribute($controller, $methodName);

        if ($methodAttribute instanceof Menu) {
            $this->registerMenuToTwig($methodAttribute);

            return;
        }

        $classAttribute = $this->getClassAttribute($controller);

        if ($classAttribute instanceof Menu) {
            $this->registerMenuToTwig($classAttribute);

            return;
        }

        $this->registerNullMenuToTwig();
    }

    private function registerNullMenuToTwig(): void
    {
        $this->twig->addGlobal('current_menu', null);
    }

    private function registerMenuToTwig(Menu $menu): void
    {
        $this->twig->addGlobal('current_menu', $menu->getDomain());
    }

    private function getClassAttribute(AbstractController $controller): ?Menu
    {
        $reflectionClass = new \ReflectionClass($controller);

        $attributes = $reflectionClass->getAttributes(Menu::class);

        if (1 !== \count($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    /**
     * @throws \ReflectionException
     */
    private function getMethodeAttribute(AbstractController $controller, string $methodName): ?Menu
    {
        $reflectionObject = new \ReflectionObject($controller);
        $reflectionMethod = $reflectionObject->getMethod($methodName);

        $attributes = $reflectionMethod->getAttributes(Menu::class);

        if (1 !== \count($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }
}
