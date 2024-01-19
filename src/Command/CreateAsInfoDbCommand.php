<?php

declare(strict_types=1);

namespace App\Command;

use App\Exception\ConfigErrorException;
use App\Repository\DbAsInfoRepository;
use App\Util\UnusedFunction;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'app:create-asinfo-db')]
class CreateAsInfoDbCommand extends Command
{
    private SymfonyStyle $io;

    private array $ranges = [
        '1-65535',
        '131072-141625',
        '196608-213403',
        '262144-272796',
        '327680-329727',
        '393216-399260',
    ];

    /**
     * @throws ConfigErrorException
     */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $rangeData = '';
        try {
            foreach ($this->ranges as $ranges) {
                $range = \explode('-', $ranges);
                $rangeData .= \sprintf('seq %s %s; ', $range[0], $range[1]);
            }
        } catch (\Exception) {
            $this->io->warning('Problem with range variable.');

            return Command::FAILURE;
        }

        $sendCommand = \sprintf('(echo begin; echo verbose; for i in $(%s); do echo "AS$i"; done; echo end )', \rtrim($rangeData));

        $this->io->writeln('');
        $this->io->write('Get data ');
        $getData = $this->getData($sendCommand);

        if ('' === $getData || '0' === $getData) {
            $this->io->warning('Unable to get data.');

            return Command::FAILURE;
        }

        $this->io->writeln('[OK]');
        $this->io->writeln('');
        $this->io->write('Parse data ');
        $parsedData = $this->parseData($getData);

        if ([] === $parsedData) {
            $this->io->warning('No data to process.');

            return Command::FAILURE;
        }

        $this->io->writeln('[OK]');
        $this->io->writeln('');
        $this->io->write('Store data in database ');
        $this->io->writeln('');
        $this->io->writeln('');
        if (!$this->storeData($parsedData)) {
            $this->io->warning('No data stored in database.');

            return Command::FAILURE;
        }

        $this->io->success('Update ASInfo database.');

        return Command::SUCCESS;
    }

    private function getData(string $data): string
    {
        $commandLine = \sprintf('%s | netcat whois.cymru.com 43', $data);

        $process = Process::fromShellCommandline($commandLine);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->io->error('problem with netcat command.');

            return '';
        }

        return $process->getOutput();
    }

    private function parseData(string $dataNetcat): array
    {
        $allData = (array) \preg_split("/\r\n|\n|\r/", $dataNetcat);

        $return = [];
        foreach ($allData as $line) {
            if (\preg_match('/(^\\s*#)|(^\\s*$)/', \sprintf('%s', $line))) {
                continue; /* empty line or comment */
            }

            $line = \trim(\sprintf('%s', $line));

            try {
                [$asn, $country, $rir, $date, $info] = \explode('|', $line);

                UnusedFunction::unused($date);
                if (!\trim($info) || !\trim($rir)) {
                    continue;
                }

                try {
                    [$data, $country] = \explode(',', $info);
                } catch (\Exception) {
                    $data = '';
                }

                if ('-Private Use AS-' === $data) {
                    $data = 'Private Use AS';
                }

                $return[] = [
                    'asn' => \trim($asn),
                    'country' => \trim($country),
                    'name' => \trim($data),
                    'description' => \trim($info),
                ];
            } catch (\Exception) {
                continue;
            }
        }

        return $return;
    }

    /**
     * @throws ConfigErrorException
     */
    private function storeData(array $parsedData): bool
    {
        $database = new DbAsInfoRepository();

        return $database->saveData($parsedData, $this->io);
    }
}
