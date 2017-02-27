<?php

namespace Wunsun\Tools\Mount\Commands;

use Illuminate\Console\Command;
use Wunsun\Tools\Mount\Services\MountManager;

class MountResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mount:reset {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '回滚上一步操作';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $name = $this->argument('name');

        try {
            $mountService = new MountManager($name);

            $mountService->reset();

            $this->info('操作成功');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
