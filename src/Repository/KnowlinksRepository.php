<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\ConfigApplication;
use App\Exception\ConfigErrorException;
use App\Exception\KnownLinksEmptyException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class KnowlinksRepository
{
    /**
     * @throws ConfigErrorException
     * @throws KnownLinksEmptyException
     */
    public static function get(): array
    {
        $knownlinksfile = ConfigApplication::getAsStatsConfigKnownLinksFile();

        try {
            $lines = (array) \file($knownlinksfile, FILE_SKIP_EMPTY_LINES);
        } catch (\Exception) {
            throw new FileNotFoundException('File set on \'knownlinksfile\' variable on asstats.yml not found.');
        }

        $knownlinks = [];

        foreach ($lines as $line) {
            $line = \trim(\sprintf('%s', $line));

            if (\preg_match('/(^\\s*#)|(^\\s*$)/', $line)) {
                continue; /* empty line or comment */
            }

            [$routerip, $ifindex, $tag, $descr, $color] = \preg_split('/\\t+/', $line); /* @phpstan-ignore-line */
            $known = false;

            foreach ($knownlinks as $link) {
                if (\in_array($tag, $link, true)) {
                    $known = true;
                }
            }

            if (!$known) {
                $knownlinks[] = [
                    'routerip' => $routerip,
                    'ifindex' => $ifindex,
                    'tag' => $tag,
                    'descr' => $descr,
                    'color' => $color,
                ];
            }
        }

        if ([] === $knownlinks) {
            throw new KnownLinksEmptyException('File knownlinks file is empty');
        }

        return $knownlinks;
    }

    public static function select(array $selectedLink): array
    {
        $selected_links = [];

        foreach ($selectedLink as $tag => $check) {
            if ($check) {
                $selected_links[] = $tag;
            }
        }
        return $selected_links;
    }
}
