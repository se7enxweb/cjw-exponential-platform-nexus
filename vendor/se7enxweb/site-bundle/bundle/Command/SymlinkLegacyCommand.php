<?php

declare(strict_types=1);

namespace Netgen\Bundle\SiteBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class SymlinkLegacyCommand extends SymlinkCommand
{
    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'If set, it will destroy existing symlinks before recreating them');
        $this->addOption('web-folder', null, InputOption::VALUE_OPTIONAL, 'Name of the webroot folder to use');
        $this->setDescription('Symlinks legacy files and folders to their proper locations (dummy implementation)');
        $this->setName('ngsite:symlink:legacy');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $output->writeln('<info>ngsite:symlink:legacy is a placeholder and does nothing. You can safely remove this script if not needed.</info>');
        return 0;
    }
}
