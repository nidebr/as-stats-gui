<?php

declare(strict_types=1);

namespace App\Util\Theme;

enum Icon: string
{
    // Menu
    case menu_home = 'ti-home icon';

    // Form
    case filter = 'ti-filter';

    /**
     * @return array<string>
     */
    public static function names(): array
    {
        return \array_map(static function (self $icon) {
            return $icon->name;
        }, self::cases());
    }

    public static function byName(string $name): self
    {
        foreach (self::cases() as $icon) {
            if ($name === $icon->name) {
                return $icon;
            }
        }

        throw new \LogicException(\sprintf('Icône "%s" inconnue, merci d’utiliser une des icônes suivantes : %s', $name, \implode(', ', self::names())));
    }
}
