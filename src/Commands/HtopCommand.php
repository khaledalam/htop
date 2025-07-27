<?php

namespace Htop\Commands;

use Htop\Storage\StorageManager;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class HtopCommand extends Command
{
    protected $signature = 'htop
        {--f= : Filter by URL path (e.g., /api)}
        {--ui : Open browser UI}
        {--url : Open browser UI}';

    protected $description = 'Real-time Laravel request monitor';

    public function handle()
    {
        if ($this->option('ui') || $this->option('url')) {

            // Start Reverb if not running
            if (stripos(PHP_OS, 'WIN') === false) {
                $this->info('Checking reverb..');
                exec("pgrep -f 'artisan reverb:start' || nohup php artisan reverb:start > /dev/null 2>&1 &");
            }

            $url = config('htop.ui_url', route('htop'));
            $this->info("Opening browser at: $url");
            if (PHP_OS_FAMILY === 'Darwin') {
                exec("open $url");
            } elseif (PHP_OS_FAMILY === 'Windows') {
                exec("start $url");
            } else {
                exec("xdg-open $url");
            }

            return Command::SUCCESS;
        }

        $this->info('HTop is running... (press CTRL+C or q to quit)');
        $output = new ConsoleOutput;

        system('stty cbreak -echo');       // Raw mode: capture key presses
        stream_set_blocking(STDIN, false); // Non-blocking key read
        echo "\033[?25l";                  // Hide cursor

        $lastHash = null;

        while (true) {
            $char = fread(STDIN, 1);
            if ($char === 'q') {
                break;
            }

            $data = app(StorageManager::class)->all();

            $filter = $this->option('url');
            if ($filter) {
                $data = array_filter($data, fn ($r) => str_contains($r->path ?? '', $filter));
            }

            $currentHash = md5(json_encode($data));

            if ($currentHash !== $lastHash) {
                $this->renderTable($output, $data);
                $lastHash = $currentHash;
            }

            usleep(500000);
        }

        system('stty sane');
        echo "\033[?25h";
        $this->info("\nExited.");
    }

    protected function renderTable(ConsoleOutput $output, array $data)
    {
        // Move cursor to top without clearing screen
        $output->write("\033[H");

        $output->writeln('HTop — Laravel Requests Monitor (CTRL+C or q to quit)');
        $output->writeln(str_repeat('─', 70));

        $rows = array_map(function ($r) {
            return [
                $r->method ?? '',
                $r->path ?? '',
                $r->status ?? '',
                $r->duration ?? '',
                $r->timestamp ?? '',
            ];
        }, $data);

        $table = new Table($output);
        $table
            ->setHeaders(['Method', 'Path', 'Status', 'Duration (ms)', 'Time'])
            ->setRows($rows);
        $table->render();
    }
}
