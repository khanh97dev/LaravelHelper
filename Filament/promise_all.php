<?php

function promise_all(array $callbacks): void
{
    $pids = [];

    foreach ($callbacks as $callback) {
        $pid = pcntl_fork();

        if ($pid == -1) {
            die('Could not fork');
        } elseif ($pid) {
            // Parent process
            $pids[] = $pid;
        } else {
            \DB::reconnect();

            try {
                $callback();
            } catch (\Throwable $e) {
                // Log error nếu cần
                \Log::error('promise_all error: ' . $e->getMessage());
            }

            exit(0);
        }
    }

    // Đợi tất cả child processes hoàn thành
    foreach ($pids as $pid) {
        pcntl_waitpid($pid, $status);
    }
}
