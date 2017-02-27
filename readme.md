#Mount

##安装与配置

###安装依赖：
>  composer require zgldh/qiniu-laravel-storage

>  在config/filesystems.php中disk项添加以下配置：
    
    'qiniu' => [
        'driver'  => 'qiniu',
        'domains' => [
            'default'   => '', //你的七牛域名
            'https'     => '',         //你的HTTPS域名
            'custom'    => '',     //你的自定义域名
        ],
        'access_key'=> '',  //AccessKey
        'secret_key'=> '',  //SecretKey
        'bucket'    => '',  //Bucket名字
        'notify_url'=> '',  //持久化处理回调地址
    ],

>  在config／app.php中的providers项中添加以下配置：

    zgldh\QiniuStorage\QiniuFilesystemServiceProvider::class
    
    
###添加Command

>  在app/Console/Kernel.php中的$commands变量里添加以下代码


    \Wunsun\Tools\Mount\Commands\MountCommand::class,
    \Wunsun\Tools\Mount\Commands\MountResetCommand::class,
    \Wunsun\Tools\Mount\Commands\MountConfigCommand::class
    
###生成配置文件及数据库迁移文件

>  执行以下命令
>  php artisan mount:install 
>  composer dumpauto
>  php artisan migrate --path=database/migrations/cms


##编译文件

>  在编译前需先配置编译文件
>  打开config/mount.php,得到以下代码

    return [
       'admin' => [
           'js/admin'
       ],
       'app' => [
           'js/app'
       ]
    ];

>其中数组的key代表name（下面编译时会用到），其必须是resources/views中的一个视图文件，可以是包含多级路径，如：admin/sys，sys/index等。
>数组key对应的值则为对应的前端资源列表。如admin对应reserces/assets/js/admin,其中reserces/assets可省略不写。也可以包含多个资源文件夹，如admin需要用到js/admin和js/wunsun两个资源文件夹

>配置完后就可以执行php artisan mount:start {name} (上面配置中的key)进行编译

##撤销编译

>执行php artisan mount:reset {name} (配置中的key)可以回滚一次编译
