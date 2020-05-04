<?php

namespace HuangYi\Shadowfax\Console;

use Swoole\Coroutine;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StopCommand extends Command
{
    /**
     * Set the command name.
     *
     * @var string
     */
    protected static $defaultName = 'stop';

    /**
     * Configure the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setDescription('Stop the Shadowfax server.')
            ->setHelp('This command allows you to stop the Shadowfax server.')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Shadowfax configuration file.')
        ;
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->bootstrap($input);

        Coroutine::create(function () use ($input, $output) {
            $client = $this->httpClient();

            $client->get('/stop');

            if ($client->errCode !== 0) {
                $output->writeln("<error>Cannot connect the Shadowfax controller server. [{$client->errCode}]</error>");

                return;
            }

            if ($client->statusCode !== 200) {
                $output->writeln("<error>Failed to stop the Shadowfax server: {$client->body}</error>");

                return;
            }

            $output->writeln("<info>The Shadowfax server stopped.</info>");
        });

        return 0;
    }
}
