<?php
declare(strict_types=1);

namespace WapplerSystems\ZabbixClient\Command;

/**
 * This file is part of the "zabbix_client" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\ZabbixClient\Domain\Repository\LockRepository;


class LockCommand extends Command {

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure() {
        $this
            ->setDescription('Delete old lock entries')
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

        if($removeTime <= 0) {
            $removeTime = 30;
        }

        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
        $lockRepository = $objectManager->get(LockRepository::class);
        $lockEntries = $lockRepository->deleteLocks($removeTime);

        $io->writeln($lockEntries.' entries older than '.$removeTime.' days deleted!');

        return Command::SUCCESS;
    }
}
