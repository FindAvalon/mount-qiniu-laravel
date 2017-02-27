<?php

namespace Wunsun\Tools\Mount\Services;

use Wunsun\Tools\Mount\Models\MountRecord;

class MountManager
{
    protected $model;

    protected $lastIndex;

    protected $blade = '';
    protected $assets = [];

    protected $urls = [];

    public function __construct($name)
    {
        $config = config('mount');

        if (isset($config[$name])) {
            $this->blade = $name;
            $this->assets = $config[$name];

            $this->model = new MountRecord();


            $lastRecord = $this->model->where('name', $this->blade)->where('status', 1)->orderBy('index', 'desc')->first();
            $this->lastIndex = empty($lastRecord) ? 1: $lastRecord->index + 1;
        } else {
            throw new \Exception('未找到指定name');
        }
    }

    public function reset($name = '')
    {
        $nowIndex = $this->lastIndex - 1;

        $list = $this->model->where('name', $this->blade)->where('status', 1)->get();

        foreach ($list as $item) {

            $originData = json_decode($item->origin_data, true);
            $mountedData = json_decode($item->mounted_data, true);
            $length = count($originData);

            for ($i = 0; $i < $length; $i++) {
                $content = file_get_contents($item->filename);
                $content = str_replace($mountedData[$i], $originData[$i], $content);
                file_put_contents($item->filename, $content);
            }

            $item->status = 0;
            $item->save();
        }
    }

    /**
     * 前端编译前，上传资源文件
     */
    public function before()
    {
        if ($this->model->where('name', $this->blade)->where('status', 1)->count() > 0) {
            $this->reset();
        }
        foreach ($this->assets as $item) {
            $this->updateAssets($item);
        }
    }

    public function createData($data, $realPath)
    {
        $mountedData = [];
        $newData = [];
        foreach ($data as $filename) {
            if (file_exists(public_path().$filename)) {
                if ($newFilename = $this->upload($filename)) {

                    $mountedData[] = $newFilename;
                    $newData[] = $filename;

                    $pageContent = file_get_contents($realPath);

                    $pageContent = str_replace($filename, $newFilename, $pageContent);
                    file_put_contents($realPath, $pageContent);
                }
            }
        }
        $this->model->create([
            'name'          => $this->blade,
            'filename'     => $realPath,
            'origin_data'  => json_encode($newData),
            'mounted_data'   => json_encode($mountedData),
            'status'        => 1,
            'index'         => $this->lastIndex
        ]);
    }

    /**
     * 遍历指定文件夹找出需要上传的文件
     * @param $dir
     */
    public function circulate($dir)
    {
        foreach ($dir as $item) {
            if (!in_array($item->getFilename(), ['.', '..'])) {
                if ($item->isDir()) {
                    $this->circulate(new \DirectoryIterator($item->getRealPath()));
                } elseif ($item->isFile()) {
                    $realPath = $item->getRealPath();
                    $content = file_get_contents($realPath);
                    if ($data = $this->findTarget($content)) {
                        $this->createData($data, $realPath);
                    }
                }
            }
        }
    }


    /**
     * 将文件上传到七牛云
     * @param $filename
     * @return string
     */
    public function upload($filename)
    {
        $arr = explode('.', $filename);
        $ext = '.'.array_pop($arr);
        $disk = \Storage::disk('qiniu');
        $content = file_get_contents(public_path().$filename);
        $md5 = md5($content);
        if ($disk->put($md5, $content)) {
            $disk->move($md5, $md5.$ext);
            if ($newFilename = $disk->downloadUrl($md5.$ext)) {
                return $newFilename;
            }
        }
        return '';
    }

    /**
     * 循环资源目录
     * @param $assets
     */
    public function updateAssets($assets)
    {
        $path = join('/assets/', [resource_path(), $assets]);
        $dir = new \DirectoryIterator($path);

        $this->circulate($dir);
    }

    /**
     * 将编译后的文件上传
     * @param string $name
     * @throws \Exception
     */
    public function start($name = '')
    {
        $filename = resource_path()."/views/{$this->blade}.blade.php";

        if (file_exists($filename)) {
            $content = file_get_contents($filename);

            if ($data = $this->findTarget($content)) {
                $this->createData($data, $filename);
            }
        } else {
            throw new \Exception('未找到指定文件');
        }
    }

    /**
     * 解析出页面中的资源文件
     * @param $pattern
     * @param $sign
     * @param $content
     * @return array
     */
    public function parse($pattern, $sign, &$content)
    {
        preg_match_all($pattern, $content, $result);

        $urls = [];

        if (count($result) > 0) {
            foreach ($result[0] as $item) {
                if (($startIndex = strpos($item, $sign)) !== false) {
                    $startIndex += strlen($sign);
                    $endIndex = strpos($item, '"', $startIndex);
                    $src = substr($item, $startIndex, $endIndex - $startIndex);
                    $urls[] = $src;
                }
            }
        }
        return $urls;
    }

    /**
     * 找到资源文件
     * @param $content
     * @return array
     */
    public function findTarget($content)
    {
        $data = array_merge(
            $this->parse('/(<script[\w\W]+src[\w\W]+<\/script>|<img[\w\W]+src[\w\W]+>)/U', 'src="', $content),
            $this->parse('/<link[\w\W]+href[\w\W]+>/U', 'href="', $content),
            $this->parse('/url\(\"[\w\W]+\"\)/U', 'url("', $content)
        );
        return $data;
    }
}