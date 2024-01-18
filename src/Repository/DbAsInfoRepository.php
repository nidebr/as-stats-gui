<?php

declare(strict_types=1);

namespace App\Repository;

use App\Application\ConfigApplication;
use App\Exception\ConfigErrorException;
use App\Exception\DbErrorException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class DbAsInfoRepository
{
    private Connection $cnx;
    private string $dbname;

    /**
     * @throws ConfigErrorException
     * @throws Exception
     */
    public function __construct()
    {
        $dbParams = [
            'driver' => 'pdo_sqlite',
            'path' => ConfigApplication::getAsStatsConfigAsInfoFile(),
        ];

        $this->cnx = DriverManager::getConnection($dbParams);
        $this->dbname = ConfigApplication::getAsStatsConfigAsInfoFile();
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
                    return false;
                }
            }

            $io->progressFinish();

            if ($count) {
                $filesystem->remove(\sprintf('%s.bak', $this->dbname));

                return true;
            }
        } catch (\Exception) {
            return false;
        }

        $filesystem->rename(\sprintf('%s.bak', $this->dbname), $this->dbname, true);

        return false;
    }

    public function getAsInfo(int $asn): array
    {
        try {
            return (array) $this->cnx->createQueryBuilder()
                ->select('*')
                ->from('asinfo')
                ->where('asn = :asn')
                ->setParameter('asn', $asn)
                ->fetchAssociative();
        } catch (Exception) {
            throw new DbErrorException(\sprintf('Problem with ASInfo DB files %s', $this->dbname));
        }
    }
}
