<?php

namespace app\server;
class SerUpload
{
    /*定义文件上传对象*/
    private $fileOBJ;
    /*
     * 文件上传配置项
     * @param bool $original=true 是否按文件原来的名字上传 [可选，如果是false，那么上传名称就必填]
     * @param string $uploadType 上传类型，默认是all   其他类型（picture，package，media） [可选]
     * @param array $typeArr 上传类型的限制数组 [可选]
     * @param string $filePath 上传路径 [必写]
     * @param string $fileName 上传名称 [可选]
     * @param string $size  文件上传大小 [可选]
     */
    private $config = array(
        'uploadType' => '',
        'original' => true,
        'typeArr' => array(),
        'filePath' => '',
        'fileName' => '',
        'size' => '2m',    //eg:2m,2M,2k,2K,2G,2g    最好与php.ini中的配置一样
        'is_tmp' => false //是否变成临时文件
    );
    /*返回值类型*/
    private $returnType;
    private $win_os_arr = array(
        'WIN32', 'WINNT', 'Windows'
    );

    /*
     * @param string $uploadName,$_FILES[$uploadName]
     * @param string $config,配置项
     * @param string $returnType,返回值类型  'json'或'array'
     */
    public function __construct($uploadName, $config = array(), $returnType = 'json')
    {
        if (!isset($_FILES[$uploadName])) {
            $this->fileOBJ = false;
        } else {
            $this->fileOBJ = $_FILES[$uploadName];
        }
        $this->config = array_merge($this->config, $config);
        $this->returnType = $returnType;

    }

    public function upload()
    {
        if (!$this->fileOBJ) {
            return $this->returnType(false, '文件为空或表单大小超出提交限制！');
        }
        if ($this->fileOBJ['error'] != 0) {
            return $this->returnType(false, $this->system_error());
        }
        switch ($this->config['uploadType']) {
            case 'picture':
                $re = $this->picture();
                break;
            case 'package':
                $re = $this->package();
                break;
            case 'media':
                $re = $this->media();
                break;
            case 'document':
                $re = $this->document();
                break;
            default:
                $re = true;
        }
        if (!$re) {
            return $this->returnType(false, '文件类型有误');
        }
        if (!$this->checkSize()) {
            return $this->returnType(false, '文件大小超出限制');
        }
        if ($this->config['filePath'] == '') {
            return $this->returnType(false, '缺少上传目录参数');
        }
        if (!is_dir($this->config['filePath'])) {
            $dir = iconv("UTF-8", "GBK", $this->config['filePath']);
            mkdir($dir, 0777, TRUE);
        }
        if ($this->config['original']) {
            $fileName = $this->fileOBJ["name"];
            if ($this->config['is_tmp']) {
                $fileName = $fileName . '.tmp';
            }

            if (in_array(PHP_OS, $this->win_os_arr)) {
                $re = move_uploaded_file($this->fileOBJ["tmp_name"], iconv('UTF-8', 'gb2312', $this->config['filePath'] . $fileName));
            } else {
                $re = move_uploaded_file($this->fileOBJ["tmp_name"], $this->config['filePath'] . $fileName);
            }

        } else {
            $suffixArr = explode('.', $this->fileOBJ["name"]);
            $suffix = "." . $suffixArr[count($suffixArr) - 1];
            if ($this->config['fileName'] == '') {
                return $this->returnType(false, '缺少文件名称');
            }
            $fileName = $this->config['fileName'] . $suffix;
            if ($this->config['is_tmp']) {
                $fileName = $fileName . '.tmp';
            }
            /*判断操作系统*/
            if (in_array(PHP_OS, $this->win_os_arr)) {
                $re = move_uploaded_file($this->fileOBJ["tmp_name"], iconv('UTF-8', 'gb2312', $this->config['filePath'] . $fileName));
            } else {
                $re = move_uploaded_file($this->fileOBJ["tmp_name"], $this->config['filePath'] . $fileName);
            }
        }

        if (!$re) {
            return $this->returnType(false, '上传目录有误！');
        }
        return $this->returnType(true, '上传成功！', $fileName);
    }

    /*检查文件大小*/
    private function checkSize()
    {
        $str = $this->config['size'];
        $num = substr($str, 0, -1);
        $letter = substr($str, -1, 1);
        $num = floatval($num);
        $letter = strtolower($letter);
        $return = 0;
        switch ($letter) {
            case 'm':
                $return = $num * 1024 * 1024;
                break;
            case 'k':
                $return = $num * 1024;
                break;
            case 'g':
                $return = $num * 1024 * 1024 * 1024;
                break;
        }
        if ($this->fileOBJ['size'] > $return) {
            return false;
        }
        return true;
    }

    /*图片类型*/
    private function picture()
    {
        $array = array(
            'gif' => '.gif',
            'jpeg' => '.jpeg',
            'pjpeg' => '.pjpeg',
            'png' => '.png',
            'jpg' => '.jpg',
            'vnd.dwg' => '.vnd.dwg',
            'tiff' => '.tiff',
            'tif' => '.tiff',
            'jp2' => '.jp2',
            'psd' => '.psd'
        );
        return $this->typeUpload($array);
    }

    /*包类型*/
    private function package()
    {
        $array = array(
            'zip' => '.zip',
            'tar' => '.tar',
            'gtar' => '.gtar',
            'iso' => '.iso',
            'jar' => '.jar',
            'rar' => '.rar',
            '7z' => '.7z'
        );
        return $this->typeUpload($array);
    }

    /*媒体类型*/
    private function media()
    {
        $array = array(
            '3g2' => '.3g2',
            '3ga' => '.3ga',
            '3gpp' => '.3gpp',
            '3gp' => '.3gp',
            'aac' => '.aac',
            'avi' => '.avi',
            'flv' => '.flv',
            'mp3' => '.mp3',
            'mp4' => '.mp4',
            'webm' => '.webm',
            'ogg' => '.ogg',
            'ogv' => '.ogv',
            'wmv' => '.wmv'
        );
        return $this->typeUpload($array);
    }

    /*文档类型*/
    private function document()
    {
        $array = array(
            'doc' => '.doc',
            'docbook' => '.docbook',
            'docm' => 'docm',
            'docx' => 'docx',
            'xla' => '.xla',
            'xlc' => '.xlc',
            'xld' => '.xld',
            'xll' => '.xll',
            'xlm' => '.xlm',
            'xls' => '.xls',
            'xlt' => '.xlt',
            'xlw' => '.xlw',
            'xlsx' => '.xlsx',
            'xlsm' => '.xlsm',
            'txt' => '.txt',
            'pdf' => '.pdf',
            'ppt' => '.ppt',
            'pptx' => '.pptx'
        );
        return $this->typeUpload($array);
    }

    /*检测类型*/
    private function typeUpload($array)
    {
//        $suffix = strstr($this->fileOBJ["name"], '.');
        $suffixArr = explode('.', $this->fileOBJ["name"]);
        $suffix = "." . $suffixArr[count($suffixArr) - 1];
        $typeArr = $this->config['typeArr'];
        $newArray = array();
        if (!empty($typeArr)) {
            foreach ($typeArr as $k => $v) {
                if (isset($array[$v])) {
                    $newArray[$v] = $array[$v];
                }
            }
            $array = $newArray;
        }
        /*判断上传的类型是否允许*/
        if (in_array($suffix, $array)) {
            return true;
        } else {
            return false;
        }

    }

    /*php内置的错误代码*/
    private function system_error()
    {
        $info = '';
        switch ($this->fileOBJ['error']) {
            case 1:
                $info = '上传的文件超过了 php.ini 中 upload_max_filesize选项限制的值';
                break;
            case 2:
                $info = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值。';
                break;
            case 3:
                $info = '文件只有部分被上传';
                break;
            case 4:
                $info = '没有文件被上传';
                break;
            case 5:
                $info = '找不到临时文件夹';
                break;
            case 6:
                $info = '文件写入失败';
                break;
        }
        return $info;
    }

    /*返回格式化*/
    private function returnType($status, $msg, $fileName = '')
    {
        $type = $this->returnType;
        $arr = array(
            'status' => $status,
            'msg' => $msg,
        );
        if ($status) {
            $arr['fileName'] = $fileName;
        }
        $return = '';
        switch ($type) {
            case 'json':
                $return = json_encode($arr);
                break;
            case 'array':
                $return = $arr;
                break;
        }
        return $return;
    }
}