<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class DbAsInfoRepository
{
    private Connection $cnx;
    private string $dbname;

    public function __construct(string $dbname)
    {
        $dbParams = [
            'driver' => 'pdo_sqlite',
            'path' => $dbname,
        ];

        $this->cnx = DriverManager::getConnection($dbParams);
        $this->dbname = $dbname;
    }

    public function saveData(array $parsedData, SymfonyStyle $io): bool
    {
        $filesystem = new Filesystem();

        if ($filesystem->exists($this->dbname)) {
            $filesystem->rename($this->dbname, \sprintf('%s.bak', $this->dbname), true);
        }

        $filesystem->touch($this->dbname);

        try {
            $sqlCreateTableQuery = 'CREATE TABLE IF NOT EXISTS asinfo (asn INT, country CHAR, name CHAR, description CHAR)';
            $this->cnx->executeQuery($sqlCreateTableQuery);

            $count = 0;

            $io->progressStart(\count($parsedData));

            foreach ($parsedData as $asinfo) {
                try {
                    $db = $this->cnx->createQueryBuilder()
                        ->insert('asinfo')
                        ->values(['asn' => ':asn', 'country' => ':country', 'name' => ':name', 'description' => ':description'])
                        ->setParameter('asn', $asinfo['asn'])
                        ->setParameter('country', $asinfo['country'])
                        ->setParameter('name', $asinfo['name'])
                        ->setParameter('description', $asinfo['description']);

                    $count += $db->executeStatement();
                    $io->progressAdvance();
                } catch (\Exception) {
                }
            }

            $io->progressFinish();

            if ($count) {
                $filesystem->remove(\sprintf('%s.bak', $this->dbname));

                return true;
            }
        } catch (\Exception $e) {
            dump($e->getMessage());
        }

        $filesystem->rename(\sprintf('%s.bak', $this->dbname), $this->dbname, true);

        return false;
    }
}
