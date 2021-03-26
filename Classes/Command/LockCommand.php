<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use WapplerSystems\ZabbixClient\Domain\Repository\LockRepository;

class LockCommand extends Command {

    /**
     * @var \WapplerSystems\ZabbixClient\Domain\Repository\LockRepository
     */
    private $lockRepository = null;

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure() {
        $this
            ->setDescription('zabbix_client lock')
            ->setHelp('Usage example: php typo3 zabbix_client:lock --remove')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('remove', 'r', InputOption::VALUE_REQUIRED, 'removes old entries, if value is given removes all entries which are older then value. Value = int (day).'),
                ])
            );
    }

    /**
     * Executes the command for showing sys_log entries
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int error code
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $io->title('zabbix_client - Lock');

        $removeTime = intval($input->getOption('remove'));

        if($removeTime > 0) {
            $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
            $lockRepository = $objectManager->get('WapplerSystems\\ZabbixClient\\Domain\\Repository\\LockRepository');
            $lockEntries = $lockRepository->deleteLocks($removeTime);

            $io->writeln($lockEntries.' old locks deleted! ');

            return Command::SUCCESS;
        }

        $io->writeln('');
        $io->error('Input value "remove" not valid! Have to be bigger than 0.');

        return Command::FAILURE;
    }
}
