<?php

namespace Wunsun\Tools\Mount\Commands;

use Illuminate\Console\Command;

class MountConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mount:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成配置文件和数据库迁移文件';

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
        $configFilename = __dir__.'/../mount.php';

        $dbFilename = __dir__.'/../migrations/2017_02_27_094424_create_mount_records_table.stub';

        if (file_exists($configFilename) && file_exists($dbFilename)) {
            $config = fopen(config_path().'/mount.php', "w+");
            fwrite($config, file_get_contents($configFilename));
            fclose($config);

            $db = fopen(database_path().'/migrations/cms/2017_02_27_094424_create_mount_records_table.php', "w+");
            fwrite($db, file_get_contents($dbFilename));
            fclose($db);

            echo "\n配置成功\n";
        }
    }
}
