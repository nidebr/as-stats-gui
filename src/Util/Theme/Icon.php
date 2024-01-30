<?php

declare(strict_types=1);

namespace App\Util\Theme;

enum Icon: string
{
    // Menu
    case menu_home = 'ti-home icon';
    case menu_view = 'ti-chart-histogram icon';
    case menu_ix = 'ti-list-details icon';
    case menu_linksusage = 'ti-package icon';

    // Form
    case filter = 'ti-filter';

    // Alert
    case alert_triangle = 'ti-alert-triangle';
    case alert_circle = 'ti-alert-circle';
    case info_circle = 'ti-info-circle';
    case check = 'ti-check';

    case top_hours = 'ti-clock';
    case top_ip = 'ti-chart-pie';
    case up = 'ti-arrow-up';
    case down = 'ti-arrow-down';
    case search = 'ti-search';

    case custom_links_history_external = 'ti-external-link';

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
