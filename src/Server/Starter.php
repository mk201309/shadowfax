<?php

namespace HuangYi\Shadowfax\Server;

use HuangYi\Shadowfax\Composer;
use HuangYi\Shadowfax\ContainerRewriter;
use Swoole\Http\Server;

class Starter extends Controller
{
    /**
     * The swoole server events.
     *
     * @var array
     */
    protected $events = [
        'Start', 'ManagerStart', 'WorkerStart', 'Request', 'Task',
        'WorkerStop', 'ManagerStop', 'Shutdown',
    ];

    /**
     * Start the server.
     *
     * @return void
     */
    public function start()
    {
        $this->rewriteContainer();

        $server = $this->createServer();

        $this->output->writeln(sprintf(
            '<info>Starting the Shadowfax server: %s:%d</info>',
            $server->host,
            $server->port
        ));

        $this->unregisterAutoload();

        $server->start();
    }

    /**
     * Rewrite the illuminate container.
     *
     * @return void
     */
    protected function rewriteContainer()
    {
        $rewriter = new ContainerRewriter;

        $rewriter->rewrite();

        $this->shadowfax->instance(ContainerRewriter::class, $rewriter);
    }

    /**
     * Create the server.
     *
     * @return \Swoole\Http\Server
     */
    protected function createServer()
    {
        $server = new Server(
            $this->getHost(),
            $this->getPort(),
            $this->getMode()
        );

        $server->set($this->config('server', []));

        foreach ($this->events as $name) {
            if ($name == 'Start' && $server->mode == SWOOLE_BASE) {
                continue;
            }

            $eventClass = "\\HuangYi\\Shadowfax\\Events\\{$name}Event";

            $server->on($name, [new $eventClass($this->output), 'handle']);
        }

        return $server;
    }

    /**
     * Unregister autoload.
     *
     * @return void
     */
    protected function unregisterAutoload()
    {
        $this->shadowfax->make(Composer::class)->unregister();
    }

    /**
     * Get the server host.
     *
     * @return string
     */
    protected function getHost()
    {
        if ($host = $this->input->getOption('host')) {
            return $host;
        }

        return $this->config('host', '127.0.0.1');
    }

    /**
     * Get the server port.
     *
     * @return int
     */
    protected function getPort()
    {
        if ($port = $this->input->getOption('port')) {
            return (int) $port;
        }

        return $this->config('host', '1215');
    }

    /**
     * Get the server mode.
     *
     * @return int
     */
    protected function getMode()
    {
        return $this->config('mode', 'process') == 'base' ?
            SWOOLE_BASE : SWOOLE_PROCESS;
    }
}