<?php

function runParallel(
        array $items,
        int $maxWorkers,
        callable $worker,
        array $context = [],
        ?callable $onResult = null
    ): void {
        $children = [];
        $queue = array_values($items);

        while ($queue || $children) {

            // Spawn workers
            while ($queue && count($children) < $maxWorkers) {
                $item = array_shift($queue);
                $index = count($items) - count($queue) - 1;

                $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
                $pid = pcntl_fork();

                if ($pid === -1) {
                    throw new \RuntimeException('Fork failed');
                }

                if ($pid === 0) {
                    fclose($sockets[0]);
                    $result = $worker($item, $index, getmypid(), $context);
                    fwrite($sockets[1], serialize($result));
                    fclose($sockets[1]);
                    exit(0);
                }

                fclose($sockets[1]);
                $children[$pid] = $sockets[0];
            }

            // Wait for any child
            $endedPid = pcntl_wait($status);
            if ($endedPid > 0 && isset($children[$endedPid])) {
                $socket = $children[$endedPid];
                $data = stream_get_contents($socket);
                fclose($socket);
                unset($children[$endedPid]);

                if ($onResult && $data !== false) {
                    $onResult(unserialize($data));
                }
            }
        }
    }
