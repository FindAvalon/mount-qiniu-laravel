<?php

namespace Wunsun\Tools\Mount\Commands;

use Illuminate\Console\Command;
use Wunsun\Tools\Mount\Services\MountManager;

class MountCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mount:start {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '编译前端';

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

            $this->info('上传资源中。。。');
            $mountService->before();
            $this->info('编译中。。。');
            exec('gulp --production', $result);
            $this->info('上传编译文件。。。');
            $mountService->start();
            $this->info('操作成功');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
