<?php


namespace console\homepc;

use models\common\CmdBase;
use models\common\param\Param;
use models\common\sys\Sys;

ini_set('memory_limit', '3072M');


class CmdFile extends CmdBase
{

    private $file_name_ver = '2023_02_02';

    private $rootDir      = '';
    private $isPreivew    = true;
    private $rubbishWords = [];
    private $fixNameMap   = [];


    public function init()
    {
        parent::init();
    }

    /*
      cd /mnt/f/doc/bfcode/porter/hammer; ./hammer homepc/file moveThunderDownload --env=dev0
     sqlite3 '/mnt/e/Program Files (x86)/Thunder Network/Thunder/Profiles/TaskDb_bak.dat'
     */
    public function moveThunderDownload2()
    {
        $root_dir           = '/mnt/f/迅雷下载';
        $dir1_filenames     = scandir($root_dir);
        $dir1_filenames_cnt = count($dir1_filenames) - 2;
        $i                  = 0;
        $torrent_exts       = ['torrent'];
        $downloading_exts   = ['td'];
        $video_exts         = ['mp4', 'ts', 'avi', 'wmv', 'mkv', 'mov', 'srt'];

        $dirs_map = [
            'files_cnt'    => [],
            'torrents_cnt' => [],
            'no_videos'    => [],
            'seems_ok'     => [],
        ];

        foreach ($dir1_filenames as $dir1_filename)
        {
            if (in_array($dir1_filename, ['.', '..']))
            {
                continue;
            }
            $i = $i + 1;
            echo "\n{$i}/{$dir1_filenames_cnt}/{$dir1_filename}\n";
            $dir1_full_filename = "{$root_dir}/{$dir1_filename}";
            if (is_dir($dir1_full_filename))
            {
                $video_files       = [];
                $downloading_files = [];
                $torrent_files     = [];

                $files = [];


                $dir2_filenames = scandir($dir1_full_filename);
                foreach ($dir2_filenames as $dir2_filename)
                {
                    if (in_array($dir2_filename, ['.', '..']))
                    {
                        continue;
                    }
                    $dir2_full_filename  = "{$dir1_full_filename}/{$dir2_filename}";
                    $dir2_lower_filename = strtolower($dir2_filename);

                    if (is_file($dir2_full_filename))
                    {
                        $file_ext      = '';
                        $filename_name = '';
                        preg_match('/^(.*)\.(\w+)$/i', $dir2_lower_filename, $ar);
                        if (count($ar) === 3)
                        {
                            $filename_name = $ar[1];
                            $file_ext      = $ar[2];
                            if (in_array($file_ext, $torrent_exts))
                            {
                                $torrent_files[] = $dir2_filename;
                            }
                            else if (in_array($file_ext, $downloading_exts))
                            {
                                $downloading_files[] = $dir2_filename;
                            }
                            else if (in_array($file_ext, $video_exts))
                            {
                                $video_files[] = $dir2_filename;
                            }
                            else
                            {
                                var_dump($ar);
                                die("\n这个后缀怎么处理?\n");
                            }
                            $files[] = $dir2_filename;
                        }
                        else
                        {
                            var_dump($ar);
                            die("\n有问题 找不到后缀 看看吧\n");
                        }
                    }


                }

                $files_cnt       = count($files);
                $videos_cnt      = count($video_files);
                $torrents_cnt    = count($torrent_files);
                $downloading_cnt = count($downloading_files);
                $dir_info        = [
                    'dir'          => $dir1_filename,
                    'videos'       => $video_files,
                    'downloading'  => $downloading_files,
                    'files_cnt'    => $files_cnt,
                    'torrents_cnt' => $torrents_cnt,
                ];

                if ($files_cnt < 2)
                {
                    //die("文件数量异常");
                    $dirs_map['files_cnt'][] = $dir_info;
                }
                else
                {
                    if ($torrents_cnt !== 1)
                    {
                        //die("种子数量异常");
                        $dirs_map['torrents_cnt'][] = $dir_info;
                    }
                    else
                    {
                        if ($downloading_cnt === 0)
                        {
                            if ($videos_cnt < 1)
                            {
                                //die("没有下载的，但是又没有视频，也是异常");
                                $dirs_map['no_videos'][] = $dir_info;
                            }
                            else
                            {
                                $dirs_map['seems_ok'][$dir1_filename] = $dir_info;
                            }
                        }
                    }
                }
            }
        }
        echo "\n文件数量异常\n";
        $tmp_cnt = count($dirs_map['files_cnt']);
        $i       = 0;
        foreach ($dirs_map['files_cnt'] as $dir_info)
        {
            $i++;
            echo "\n文件数量异常{$i}/{$tmp_cnt}";
            var_export($dir_info);
            echo "\n";
        }


        echo "\n种子数量数量异常\n";
        $tmp_cnt = count($dirs_map['torrents_cnt']);
        $i       = 0;
        foreach ($dirs_map['torrents_cnt'] as $dir_info)
        {
            $i++;
            echo "\n种子数量数量异常{$i}/{$tmp_cnt}";
            var_export($dir_info);
            echo "\n";
        }


        echo "\n没视频\n";
        $tmp_cnt = count($dirs_map['no_videos']);
        $i       = 0;
        foreach ($dirs_map['no_videos'] as $dir_info)
        {
            $i++;
            echo "\n没视频{$i}/{$tmp_cnt}";
            var_export($dir_info);
            echo "\n";
        }


        echo "\n待确定\n";
        $tmp_cnt = count($dirs_map['seems_ok']);
        ksort($dirs_map['seems_ok']);
        $i = 0;
        foreach ($dirs_map['seems_ok'] as $dir_info)
        {
            $i++;
            echo "\n待确定{$i}/{$tmp_cnt}\n";
            //var_export($dir_info);
            echo json_encode($dir_info, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            echo "\n";
        }


    }

    /*
         cd /mnt/f/doc/bfcode/porter/hammer; ./hammer homepc/file moveThunderDownload --env=dev0
        sqlite3 '/mnt/e/Program Files (x86)/Thunder Network/Thunder/Profiles/TaskDb_bak.dat'
        */
    public function moveThunderDownload()
    {
        $from_dir = '/mnt/f/迅雷下载';
        $to_dir   = '/mnt/f/tmp2';

        $db_file = '/mnt/e/Program Files (x86)/Thunder Network/Thunder/Profiles/TaskDb.dat';
        //$db_file  = '/mnt/e/Program Files (x86)/Thunder Network/Thunder/Profiles/TaskDb_bak.dat';

        $sqlite = new \SQLite3($db_file);
        // $sqlite->open($db_file);
        var_export($sqlite);
        if (!$sqlite)
        {
            echo $sqlite->lastErrorMsg();
        }
        else
        {
            echo "Opened database successfully\n";
        }
        $sql    = <<<EOF
      select * from TaskBase;
EOF;
        $result = $sqlite->query('select SavePath,Status,Name from TaskBase where Status=10;');
        var_export($result);

        $i   = 0;
        $arr = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC))
        {
            $arr[$i] = $row;
            $i       += 1;
        }
        var_export($arr);


        echo "\n\n\n 请确认 迅雷已经关闭 [yes] !!!!!!!!!!!!!!!!!!!!!!!!!\n\n\n";
        $stdin = trim(fgets(STDIN));

        if ($stdin === 'yes')
        {
            echo "OK,go on……\n";
        }
        else
        {
            die("\n 停止 \n");
        }

        $cnt = count($arr);
        $i   = 0;
        foreach ($arr as $ar)
        {
            $i++;

            $src_file = "{$from_dir}/{$ar['Name']}";
            $to_file  = "{$to_dir}/{$ar['Name']}";
            echo "\n{$i}/{$cnt} {$src_file} -> {$to_file}\n";
            if (!file_exists($src_file))
            {
                die("\nsrc_file_not_exist:{$src_file}\n");
            }
            if (file_exists($to_file))
            {
                die("\nto_file_has_exist:{$to_file}\n");
            }
            rename($src_file, $to_file);
        }


    }


    /*
      cd /mnt/f/doc/bfcode/porter/hammer; ./hammer homepc/file clearDownload --env=dev0 >> ~/tmp2.txt
     */


    public function moveFiles()
    {
        $src_dir = $this->params->getStringNotNull('src');
        if (!is_dir($src_dir))
        {
            throw new \Exception("dir error:{$src_dir}");
        }
        $dst_dir = $this->params->getStringNotNull('dst');
        if (!is_dir($dst_dir))
        {
            throw new \Exception("dir error:{$dst_dir}");
        }
        $input_file = $this->params->getStringNotNull('csv');
        if (!is_file($input_file))
        {
            throw new \Exception("csv file error:{$input_file}");
        }
        $f        = fopen($input_file, 'r');
        $fileLine = 0;
        while (!feof($f))
        {
            $fileLine++;
            $file_name    = trim(fgets($f));
            $src_filename = "{$src_dir}/{$file_name}";
            $dst_filename = "{$dst_dir}/{$file_name}";
            $src_flag     = file_exists($src_filename) ? 'ok' : 'not exist';
            $dst_flag     = file_exists($dst_filename) ? 'exist' : 'ok';
            echo "\n{$fileLine} {$file_name} \n src:{$src_flag} {$src_filename} \ndst:{$dst_flag}\n";
            if ($src_flag === 'ok' && $dst_flag === 'ok')
            {
                var_dump(is_writable($src_filename), is_writable($dst_dir), rename($src_filename, $dst_filename));
            }
            else
            {
                echo "\n***********************************\n";
            }

        }
        fclose($f);
    }


    public function formatName()
    {

        $root_dir   = '/mnt/f/tmp2';
        $is_preview = !($this->params->tryGetString('preview') === 'no');

        $dir1_filenames = scandir($root_dir);
        $del_exts       = ['html', 'htm', 'exe', 'torrent', 'txt', 'chm'];
        $video_exts     = ['mp4', 'ts', 'avi', 'wmv', 'mkv', 'mov', 'srt'];
        $img_exts       = ['gif', 'jpg', 'jpeg', 'png'];
        $rubbish_words  = explode(',', '『,』,【,】,【】,[,],〖,〗,《,》,@,顶级無碼,sex8.cc,guochan2048.com,Night24.com,2048社区,-big2048.com,SEX8.CC,Weagogo,kpxvs.com,miohot0428,jav20s8.com,Woxav.Com,fun2048.com,最新流出,最新,钻石级,❤️,推荐,会所尊享,❤,推荐,蜜桃,传媒,特辑,新作,SEX8.CC,爱剪辑,我的视频');
        $fixed_name_map = ['加勒比' => 'carib-', '一本道' => '1pondo-', 'HEYZO' => 'heyzo-'];

        $dir1_filenames_cnt = count($dir1_filenames) - 2;
        $i                  = 0;
        foreach ($dir1_filenames as $dir1_filename)
        {
            if (in_array($dir1_filename, ['.', '..']))
            {
                continue;
            }
            if ($this->isVersionedName($dir1_filename))
            {
                continue;
            }
            $i = $i + 1;
            // echo "\n{$i}/{$dir1_filenames_cnt}/{$dir1_filename}\n";


            $dir1_full_filename  = "{$root_dir}/{$dir1_filename}";
            $curr_filename_lower = strtolower($dir1_full_filename);
            if (strstr($curr_filename_lower, '.jpg') || strstr($curr_filename_lower, '.png') || strstr($curr_filename_lower, '.jpeg') || strstr($curr_filename_lower, '.gif'))
            {
                unlink($dir1_full_filename);
                continue;
            }

            //  echo "{$curr_filename}\n";
            if (is_file($dir1_full_filename))
            {
                foreach ($del_exts as $del_ext)
                {
                    if (strstr($dir1_filename, ".{$del_ext}"))
                    {
                        //   var_dump($filename, $del_ext);
                        unlink($dir1_full_filename);
                    }
                }
                if (strstr($dir1_filename, '.zip'))
                {
                    $cmd = "unzip -o {$dir1_full_filename} -d {$root_dir}";
                    $cmd = "unzip -O GBK {$dir1_full_filename} -d {$root_dir}";

                    echo "\nUNZIP:{$cmd}\n";

                    exec($cmd, $ar);
                    var_dump($cmd, $ar);
                }

                $file_ext      = '';
                $filename_name = '';
                preg_match('/^(.*)\.(\w+)$/i', $dir1_filename, $ar);
                if (count($ar) === 3)
                {
                    $filename_name = $ar[1];
                    $file_ext      = $ar[2];
                }
                else
                {
                    var_dump($ar);
                    die("\n有问题 看看吧\n");
                }

                $new_name = $this->formatNameWord('单个文件', $dir1_full_filename, $filename_name, $rubbish_words, $fixed_name_map);

                //$new_name = trim(str_replace($rubbish_words, '', $filename_name));

                $tmp_str = preg_replace('/^([\d\-\.]?)+\s/i', '$2', $new_name);
                if ($tmp_str !== $new_name)
                {
                    echo ">>番号:\n";
                    var_dump($tmp_str);
                    $new_name = $tmp_str;
                }
                preg_match_all('/([a-zA-Z0-9]+\.[a-zA-Z]+)/i', $new_name, $tm_ar);
                if (count($tm_ar) && count($tm_ar[1]))
                {
                    echo ">>去网址:\n";
                    var_dump($new_name, $tm_ar);
                    // $new_name = $tmp_str;
                    $new_name2 = str_replace($tm_ar[1][0], '', $new_name);
                    echo "{$new_name}\n{$new_name2}\n";

                }

                if ($new_name[0] === '-')
                {
                    //  echo ">>去垃圾开头:\n";
                    $tmp_str = preg_replace('/^(-)/i', '$2', $new_name);
                    //var_dump($tmp_str);
                    $new_name = $tmp_str;
                }


                $new_name = $this->getSn($filename_name);
                // $new_name = $this->getDescFilename($filename_name);

                $new_name = join(' ', array_unique(explode(' ', $new_name)));
                $new_name = Param::convertStrType($new_name, 'TOSBC');

                $new_name = $this->addNameVersion($new_name);


                if ($new_name !== $filename_name)
                {
                    if (file_exists("{$root_dir}/{$new_name}.{$file_ext}"))
                    {
                        $new_name = "{$new_name}_" . rand(1, 1000);
                    }

                    echo "dir1_file move_file:{$dir1_full_filename} -> {$root_dir}/{$new_name}.{$file_ext}   [一级文件，简单格式化]\n";
                    if ($is_preview)
                    {
                        echo "\n注意是预览，未修改\n";
                    }
                    else
                    {
                        rename($dir1_full_filename, "{$root_dir}/{$new_name}.{$file_ext}");
                    }

                }


                continue;
            }


            if (is_dir($dir1_full_filename))
            {
                $video_file = '';
                $videos_cnt = 0;
                $files_cnt  = 0;
                $imgs_cnt   = 0;
                $dir_cnt    = 0;

                $dir2_filenames = array_slice(scandir($dir1_full_filename), 2);

                foreach ($dir2_filenames as $dir2_filename)
                {
                    if (in_array($dir2_filename, ['.', '..']))
                    {
                        continue;
                    }
                    $dir_cnt   = $dir_cnt + 1;
                    $files_cnt = $files_cnt + 1;

                    $dir2_full_filename  = "{$dir1_full_filename}/{$dir2_filename}";
                    $dir2_lower_filename = strtolower($dir2_filename);

                    //如果是目录，直接移出去
                    if (is_dir($dir2_full_filename))
                    {
                        echo "is_dir\n";
                        $dir2_filename = trim($dir2_filename);
                        // $new_filename=$this->formatNameWord('',$new_filename);
                        $dir2_filename = $this->formatNameWord('二级文件夹整体', $dir1_full_filename, $dir2_filename, $rubbish_words, $fixed_name_map);

                        $desc_name = $this->getDescFilename($dir2_filename);

                        $new_dir2_filename = $dir2_filename;
                        if ($this->getDescFilenameLength($desc_name) < 15)
                        {
                            $new_dir2_filename = "{$dir1_filename} {$dir2_filename}";
                            echo "原dir name 描述信息too short，需要拼接一级dir name \nold:{$dir2_filename} \nnew:{$new_dir2_filename}\n";

                        }

                        $new_dir2_filename = $this->addNameVersion($new_dir2_filename);
                        if (is_file("{$root_dir}/{$new_dir2_filename}"))
                        {
                            $new_dir2_filename = "{$new_dir2_filename}_" . rand(1, 1000);
                        }
                        echo "dir1_dir2 move_dir:{$dir2_full_filename} -> {$root_dir}/{$new_dir2_filename}  [移动二级 二级文件夹]\n";
                        if ($is_preview)
                        {
                            echo "\n注意是预览，未修改\n";
                        }
                        else
                        {
                            rename($dir2_full_filename, "{$root_dir}/{$new_dir2_filename}");
                        }
                        continue;
                    }


                    if (is_file($dir2_full_filename))
                    {
                        $is_del = false;
                        foreach ($del_exts as $del_ext)
                        {
                            if (strstr($dir2_lower_filename, ".{$del_ext}"))
                            {
                                var_dump($del_ext);
                                $is_del = true;
                                break;
                            }
                        }
                        if ($is_del)
                        {
                            echo "delete_file:{$dir2_full_filename}\n";
                            unlink($dir2_full_filename);
                            continue;
                        }
                        if (strstr($dir2_lower_filename, '.zip'))
                        {
                            $cmd = "unzip -O GBK {$dir2_full_filename} -d {$root_dir}";
                            echo "\nUNZIP:{$cmd}\n";

                            exec($cmd, $ar);
                            var_dump($cmd, $ar);
                        }


                        $files_cnt = $files_cnt + 1;
                        foreach ($video_exts as $video_ext)
                        {
                            if (strstr($dir2_lower_filename, ".{$video_ext}"))
                            {
                                $video_file = $dir2_filename;
                                $videos_cnt = $videos_cnt + 1;
                            }

                        }

                        foreach ($img_exts as $img_ext)
                        {
                            if (strstr($dir2_lower_filename, ".{$img_ext}"))
                            {
                                $imgs_cnt = $imgs_cnt + 1;
                            }

                        }

                    }
                }

                if ($videos_cnt === 1)
                {
                    $file_ext      = '';
                    $only_filename = '';
                    preg_match('/^(.*)\.(\w+)$/i', $video_file, $ar);
                    if (count($ar) === 3 || !in_array($ar[2], $video_exts))
                    {
                        $only_filename = $ar[1];
                        $file_ext      = $ar[2];
                    }
                    else
                    {
                        var_dump($ar);
                        die("\n有问题 看看吧\n");
                    }
                    $filename_name          = trim($only_filename);
                    $formated_filename_name = $this->formatNameWord('单个文件dir', $dir1_full_filename, $filename_name, $rubbish_words, $fixed_name_map);


                    $sned_filename_name = $this->getSn($formated_filename_name);


                    $desc_name = $this->getDescFilename($sned_filename_name);

                    $desc_verifyed_filename = $sned_filename_name;
                    if ($this->getDescFilenameLength($desc_name) < 15)
                    {
                        $desc_verifyed_filename = "{$dir1_filename} {$desc_verifyed_filename}";
                        echo "原dir name 描述信息too short，需要拼接一级dir name \nold:{$only_filename} \nnew:{$desc_verifyed_filename}\n";

                    }

                    $versioned_filename_name = $this->addNameVersion($desc_verifyed_filename);


                    $repeat_checked_filename = $versioned_filename_name;
                    if (is_file("{$root_dir}/{$versioned_filename_name}.{$file_ext}"))
                    {
                        echo "有重复";
                        $rand                    = rand(1, 10000);
                        $repeat_checked_filename = "{$versioned_filename_name}_{$rand}.{$file_ext}";
                    }

                    $new_full_filename = "{$root_dir}/{$repeat_checked_filename}.{$file_ext}";
                    echo "only_video move_file:{$dir1_full_filename}/{$video_file} -> {$new_full_filename}\n";
                    if ($is_preview)
                    {
                        echo "\n注意是预览，未修改\n";
                    }
                    else
                    {
                        rename("{$dir1_full_filename}/{$video_file}", $new_full_filename);
                    }


                    continue;
                }
                if ($videos_cnt < 1 && $imgs_cnt < 10 && $dir_cnt < 1)
                {
                    echo "is_rmdir?:{$dir1_full_filename}  没有子目录，没有视频，图片小于10 输入[y] 删除掉\n";
                    var_dump($dir2_filenames);
                    $stdin = trim(fgets(STDIN));

                    if ($stdin === 'y')
                    {
                        echo "rmdir:{$dir1_full_filename}\n";
                        foreach ($dir2_filenames as $dir2_filename)
                        {
                            unlink("{$dir1_full_filename}/{$dir2_filename}");
                        }

                        rmdir($dir1_full_filename);
                    }
                    else
                    {
                        echo "不删除,跳过";
                    }
                }
                if ($files_cnt === 0)
                {
                    echo "rmdir:{$dir1_full_filename}\n";
                    rmdir($dir1_full_filename);
                }
                else
                {
                    //  var_dump(count($sub_curr_filenames));
                }

                continue;


            }


        }
    }

    private function formatNameWord($flag, $full_fileanme, $old_str, $rubbish_words, $fixed_name_map)
    {
        $new_str = $this->trimNameVersion($old_str);
        $new_str = str_replace($rubbish_words, ' ', $old_str);
        foreach ($fixed_name_map as $old_tag => $fixed_name)
        {
            if (strstr($full_fileanme, $old_tag))
            {
                $new_str = "{$fixed_name} " . str_replace($old_tag, '#', $new_str);
            }
        }


        //$new_name = trim(str_replace($rubbish_words, '', $filename_name));

        $tmp_str = preg_replace('/^([\d\-\.]?)+\s/i', '$2', $new_str);
        if ($tmp_str !== $new_str)
        {
            echo ">>番号:\n";
            var_dump($tmp_str);
            $new_str = $tmp_str;
        }
        preg_match_all('/([a-zA-Z0-9]+\.[a-zA-Z]+)/i', $new_str, $tm_ar);
        if (count($tm_ar) && count($tm_ar[1]))
        {
            echo ">>去网址:\n";
            var_dump($new_str, $tm_ar);
            // $new_name = $tmp_str;
            $new_name2 = str_replace($tm_ar[1][0], '', $new_str);
            echo "{$new_str}\n{$new_name2}\n";

        }

        if ($new_str[0] === '-')
        {
            //  echo ">>去垃圾开头:\n";
            $tmp_str = preg_replace('/^(-)/i', '$2', $new_str);
            //var_dump($tmp_str);
            $new_str = $tmp_str;
        }


        $strs    = array_unique(explode('#', $new_str));
        $new_str = join(' ', $strs);

        if ($old_str !== $new_str)
        {
            echo "\n******************************* formatNameWord {$flag} <\n";
            var_dump($old_str);
            var_dump($new_str);
            echo "\n******************************* formatNameWord {$flag} >\n";
        }
        // return $new_str;
        return $new_str;
    }

    /**
     * 去除英文数词 特殊符号
     * @param $only_filename
     * @return string|string[]|null
     */
    private function getDescFilename($only_filename)
    {
        $only_filename = $this->trimNameVersion($only_filename);
        //$trimed_str = preg_replace('/\s|[\x21-\x7e-A-Za-z0-9]/i', '', $only_filename);
        $trimed_str = preg_replace('/[\x21-\x7e-A-Za-z0-9]/i', '', $only_filename);

        if ($trimed_str !== $only_filename)
        {
            echo "\n描述性文件名:[$trimed_str]   原文件名:[$only_filename]\n";
        }
        return $trimed_str;
    }

    private function getDescFilenameLength($versioned_filename)
    {
        return mb_strlen($this->trimNameVersion($versioned_filename));
    }

    private function trimNameVersion($versioned_filename)
    {
        return str_replace(" _name_ver_{$this->file_name_ver}_", '', $versioned_filename);
    }

    private function addNameVersion($last_path_name)
    {
        return $this->isVersionedName($last_path_name) ? $last_path_name : "{$last_path_name} _name_ver_{$this->file_name_ver}_";
    }

    private function isVersionedName($last_path_name)
    {
        return strstr($last_path_name, "_name_ver_") === false ? false : true;
    }

    private function getSn($str)
    {

        $str = $this->trimNameVersion($str);

        $str     = trim($str);
        $old_str = $str;
        //var_dump($str);
        preg_match_all('/[\d\w]+\.[\w]+/i', $str, $ar);
        //var_dump($ar);
        if (count($ar[0]) === 1)
        {
            $str = str_replace($ar[0][0], '', $str);
        }

        preg_match_all('/[\w\d]+[\d\w\-\s]+\d/i', $str, $ar);
        if (count($ar[0]) > 0)
        {
            //   var_dump($ar);

            $tmp_ar = [];
            foreach ($ar[0] as $tmp_str)
            {
                $tmp_str = trim($tmp_str);
                $tmp_int = intval($tmp_str);
                if (strval($tmp_int) !== $tmp_str)
                {
                    $tmp_ar[] = $tmp_str;
                }
            }
            $str = join(' ', $tmp_ar) . str_replace($tmp_ar, '', $str);
        }
        if ($old_str !== $str)
        {
            echo "\n******************************* getSn <\n";
            var_dump($old_str);
            var_dump($str);
            echo "\n******************************* getSn >\n";
        }


        return $str;
    }


    public function delFiles()
    {

        $input_file = '/mnt/f/tmp_del.txt';
        if (!is_file($input_file))
        {
            throw new \Exception("csv file error:{$input_file}");
        }
        $f        = fopen($input_file, 'r');
        $fileLine = 0;
        while (!feof($f))
        {
            $fileLine++;
            $file_name = trim(fgets($f));
            if (empty($file_name))
                continue;
            if (strstr($file_name, '//'))
            {
                echo "\nskip {$fileLine} {$file_name}\n";
                continue;
            }

            $file_name = str_replace('F:/', '/mnt/f/', $file_name);
            echo "\n{$fileLine} {$file_name} \n";
            if (is_file($file_name) && is_writable($file_name))
            {
                echo "\n try";
                unlink($file_name);

            }
            else
            {
                echo "\n xxx";
            }

        }
        fclose($f);
    }

    //转换效果不理想，直接用老的，删除新的
    //  cd /mnt/f/doc/bfcode/porter/hammer; ./hammer homepc/file markOld --env=dev0
    public function markOld()
    {
        echo "\n 转换效果不理想，直接重命名老的源文件，删除新的转化结果, 继续请输入[  mei cuo ]\n";
        $stdin = trim(fgets(STDIN));

        if ($stdin !== 'mei cuo')
        {
            die("请确认后再执行");
        }
        $input_file = '/mnt/f/tmp_mark_old.txt';
        if (!is_file($input_file))
        {
            throw new \Exception("csv file error:{$input_file}");
        }
        $f        = fopen($input_file, 'r');
        $fileLine = 0;
        while (!feof($f))
        {
            $fileLine++;
            $old_file_name = trim(fgets($f));
            if (empty($old_file_name))
                continue;
            if (strstr($old_file_name, '//'))
            {
                echo "\nskip {$fileLine} {$old_file_name}\n";
                continue;
            }

            $new_file_name   = preg_replace('/(\.\w+)$/i', '~1.mp4', $old_file_name);
            $final_file_name = preg_replace('/(\.\w+)$/i', ' _final_ $1', $old_file_name);

            $new_file_exist_flag = 'no';
            $old_file_size       = 0;
            $new_file_size       = 0;
            $delete_flag         = '<';


            echo "\n{$fileLine} old:{$old_file_name} new:{$new_file_name}\n";
            if (is_file($old_file_name) && is_writable($old_file_name))
            {
                $old_file_handle = fopen($old_file_name, "r");
                $old_file_fstat  = fstat($old_file_handle);
                fclose($old_file_handle);
                $old_file_size = intval($old_file_fstat["size"] / 1024 / 1024);

                if (is_file($new_file_name) && is_writable($new_file_name))
                {
                    $new_file_exist_flag = 'yes';
                    $new_file_handle     = fopen($new_file_name, "r");
                    $new_file_fstat      = fstat($new_file_handle);
                    fclose($new_file_handle);
                    $new_file_size = intval($new_file_fstat["size"] / 1024 / 1024);
                    if ($new_file_size >= $old_file_size)
                    {
                        $delete_flag = '>=';
                    }
                    else
                    {
                        echo "\n error: {$new_file_name}";
                    }
                }
                else
                {
                    echo "\n new file not exist: {$new_file_name}";
                }
            }
            else
            {
                echo "\n old file not exist:{$old_file_name}";
            }
            echo "\n old:{$old_file_size}  [  {$delete_flag}  ] new: {$new_file_exist_flag} {$new_file_size}\n ";
            if ($delete_flag === '>=')
            {
                echo "\n{$final_file_name}";
                rename($old_file_name, $final_file_name);
                unlink($new_file_name);
            }

        }
        fclose($f);
    }

    // cd /mnt/f/doc/bfcode/porter/hammer; ./hammer homepc/file getDeleteList --env=dev0
    public function getDeleteList()
    {
        $input_file = '/mnt/f/tmp_pre_name.txt';
        if (!is_file($input_file))
        {
            throw new \Exception("csv file error:{$input_file}");
        }
        $f                = fopen($input_file, 'r');
        $fileLine         = 0;
        $old_biggers      = ["\n"];
        $new_biggers      = ["\n"];
        $nochange_biggers = ["\n"];

        while (!feof($f))
        {
            $fileLine++;
            $file_name = trim(fgets($f));
            if (empty($file_name))
                continue;
            $src_name = '/mnt/f/tmp2/' . $file_name;
            $new_name = preg_replace('/(\.\w+)$/i', '~1.mp4', $src_name);
            echo "\n{$fileLine} \n";
            if (is_file($src_name) && is_writable($src_name))
            {
                $old_file_handle = fopen($src_name, "r");
                //获取文件的统计信息
                $old_file_fstat = fstat($old_file_handle);
                // echo "文件名：".basename($new_name)."<br>";
                // echo "文件大小：".round($fstat["size"]/1024/1024,2)."Mb\n";
                //echo "最后修改时间：".date("Y-m-d h:i:s",$fstat["mtime"])."\n";
                // echo "create时间：".date("Y-m-d h:i:s",$fstat["ctime"])."\n";
                fclose($old_file_handle);
                $old_size = intval($old_file_fstat["size"] / 1024 / 1024);

                echo "\n src ok {$src_name} {$old_size}.Mb";
                if (is_file($new_name) && is_writable($new_name))
                {

                    $new_file_handle = fopen($new_name, "r");
                    //获取文件的统计信息
                    $new_file_fstat = fstat($new_file_handle);
                    fclose($new_file_handle);
                    $new_size = intval($new_file_fstat["size"] / 1024 / 1024);
                    echo "\n newfile ok {$new_name} {$new_size}.Mb";

                    $flag_str = '';
                    if ($new_size > $old_size)
                    {
                        $tmp_index     = (count($new_biggers) - 1) / 2;
                        $flag_str      = "//index:{$tmp_index} old:{$old_size} < new:{$new_size}  fail ";
                        $new_biggers[] = $src_name;
                        $new_biggers[] = $flag_str;
                    }
                    if ($new_size < $old_size)
                    {
                        $tmp_index     = (count($old_biggers) - 1) / 2;
                        $flag_str      = "//index:{$tmp_index} old:{$old_size} > new:{$new_size}  nice ";
                        $old_biggers[] = $src_name;
                        $old_biggers[] = $flag_str;

                    }
                    if ($new_size === $old_size)
                    {
                        $tmp_index          = (count($nochange_biggers) - 1) / 2;
                        $flag_str           = "//index:{$tmp_index} old:{$old_size} === new:{$new_size}";
                        $nochange_biggers[] = $src_name;
                        $nochange_biggers[] = $flag_str;
                    }
                    echo "\n{$flag_str}\n";
                }
                else
                {
                    echo "\n dst not exist {$new_name}";
                }

            }
            else
            {
                echo "\n src not exist {$src_name}";
            }

        }
        fclose($f);

        echo "\n\n\n //要查看 新文件更大 ??? \n\n\n";
        $stdin = trim(fgets(STDIN));

        if ($stdin === 'yes')
        {
            echo join("\n", $new_biggers);
        }


        echo "\n\n\n //要查看 老文件更大 ??? \n\n\n";
        $stdin = trim(fgets(STDIN));

        if ($stdin === 'yes')
        {
            echo join("\n", $old_biggers);
        }
        echo "\n";

        echo "\n\n\n //要查看 没改变大小的吗 ??? \n\n\n";
        $stdin = trim(fgets(STDIN));

        if ($stdin === 'yes')
        {
            echo join("\n", $nochange_biggers);
        }
        echo "\n";

    }


    public function formatName2()
    {
        Sys::app()->initPrinter();
        Sys::app()->getPrinter()->setBaseTabNumber(0)->newTabEcho('init', '初始化根目录');
        $this->rootDir   = '/mnt/f/tmp2';
        $this->isPreivew = !($this->params->tryGetString('preview') === 'no');

        VersionControl::init();
        $rootScanedDir         = new ScanedDir(false, $this->rootDir);
        $rootScanedDir->isRoot = true;

        //                Sys::app()->getPrinter()->newTabEcho('1.2', '1.2');
        //                Sys::app()->getPrinter()->newTabEcho('1.2.3', '1.2.3');
        //                Sys::app()->getPrinter()->newTabEcho('1.2.3.4', '1.2.3.4');
        //                Sys::app()->getPrinter()->endTabEcho('1.2.3.4', '1.2.3.4');
        //                Sys::app()->getPrinter()->endTabEcho('1.2.3', '1.2.3');
        //                Sys::app()->getPrinter()->endTabEcho('1.2', '1.2');


        Sys::app()->getPrinter()->endTabEcho('init', '初始化 根目录 ok');


        Sys::app()->getPrinter()->newTabEcho('scan_lev1_files', '扫描一级文件');

        foreach ($rootScanedDir->scanedFiles as $deep1ScanFile)
        {
            if ($deep1ScanFile->isVersionedName())
            {
                //其实加不加都无所谓，主要为了打个提醒
                continue;
            }
            //   echo "1级子文件 {$deep1ScanFile->filePath}\n";
            Sys::app()->getPrinter()->tabEcho("1级子文件 {$deep1ScanFile->filePath}\n");

            if ($this->dealFile($deep1ScanFile))
            {
                if ($deep1ScanFile->isVideo())
                {
                    Sys::app()->getPrinter()->newTabEcho('scan_lev1_files.file.isVideo', "\n属于视频，准备处理");

                    Sys::app()->getPrinter()->newTabEcho('scan_lev1_files.file.getFormatedName', "获取规范名称");
                    $versioned_filename_name = $deep1ScanFile->formatVideoFile();
                    Sys::app()->getPrinter()->endTabEcho('scan_lev1_files.file.getFormatedName', "获取规范名称");

                    $repeat_checked_filename = $versioned_filename_name;
                    if (is_file("{$this->rootDir}/{$versioned_filename_name}.{$deep1ScanFile->ext}"))
                    {
                        Sys::app()->getPrinter()->tabEcho("有重复\n");
                        $rand                    = rand(1, 10000);
                        $repeat_checked_filename = "{$versioned_filename_name}_{$rand}.{$deep1ScanFile->ext}";
                    }

                    $new_full_filename = "{$this->rootDir}/{$repeat_checked_filename}.{$deep1ScanFile->ext}";
                    Sys::app()->getPrinter()->tabEcho("only_video move_file:{$deep1ScanFile->filePath} -> {$new_full_filename}\n");
                    if ($this->isPreivew)
                    {
                        Sys::app()->getPrinter()->tabEcho("注意是预览，未修改\n");
                    }
                    else
                    {
                        rename($deep1ScanFile->filePath, $new_full_filename);
                    }
                    Sys::app()->getPrinter()->endTabEcho('scan_lev1_files.file.isVideo', "\n属于视频，处理完毕\n\n\n\n\n");

                }
            }
        }
        Sys::app()->getPrinter()->endTabEcho('scan_lev1_files', '扫描一级文件');

        Sys::app()->getPrinter()->newTabEcho('scan_lev1_dirs', '扫描一级目录');

        foreach ($rootScanedDir->scanedDirs as $deep1ScanedDir)
        {
            if ($deep1ScanedDir->isVersionedName())
            {
                //其实加不加都无所谓，主要为了打个提醒
                continue;
            }
            Sys::app()->getPrinter()->newTabEcho('lev_1_dir.forEach', "1级子目录 {$deep1ScanedDir->fullPath}");


            Sys::app()->getPrinter()->newTabEcho('lev_1_dir.lev_2_files.forEach', " 2级子文件 开始遍历");

            $videos_cnt = 0;
            $imgs_cnt   = 0;
            foreach ($deep1ScanedDir->scanedFiles as $deep2ScanFile)
            {
                //echo "1级子目录 2级子文件 {$deep2ScanFile->filePath}\n";
                if ($deep2ScanFile->isReadableFile())
                {
                    if ($deep2ScanFile->isVideo())
                    {
                        $videos_cnt = $videos_cnt + 1;
                    }
                    else if ($deep2ScanFile->isImg())
                    {
                        $imgs_cnt = $imgs_cnt + 1;
                    }
                }
            }
            Sys::app()->getPrinter()->endTabEcho('lev_1_dir.lev_2_files.forEach', " 2级子文件 结束遍历");

            // Sys::app()->getPrinter()->tabEcho("1级子目录 {$deep1ScanedDir->fullPath}\n");

            Sys::app()->getPrinter()->newTabEcho('lev_1_dir.lev_2_dirs.forEach', " 2级子目录 开始遍历");

            foreach ($deep1ScanedDir->scanedDirs as $deep2ScanedDir)
            {
                echo "2级子目录 {$deep2ScanedDir->fullPath}\n";

                Sys::app()->getPrinter()->newTabEcho('scan_lev1_files.file.getFormatedName', "获取规范名称");
                $formatedName = $deep2ScanedDir->getFormatedName();
                Sys::app()->getPrinter()->endTabEcho('scan_lev1_files.file.getFormatedName', "获取规范名称");

                $repeat_checked_filename = "{$this->rootDir}/{$formatedName}";
                if (is_dir("{$this->rootDir}/{$formatedName}"))
                {
                    Sys::app()->getPrinter()->tabEcho("有重复\n");
                    $rand                    = rand(1, 10000);
                    $repeat_checked_filename = "{$repeat_checked_filename}_{$rand}";
                }

                $new_full_filename = "{$repeat_checked_filename}";
                Sys::app()->getPrinter()->tabEcho("move_dir :{$deep2ScanedDir->fullPath} -> {$new_full_filename}\n");
                if ($this->isPreivew)
                {
                    Sys::app()->getPrinter()->tabEcho("注意是预览，未修改\n");
                }
                else
                {
                    rename($deep2ScanedDir->fullPath, $new_full_filename);
                }

            }
            Sys::app()->getPrinter()->endTabEcho('lev_1_dir.lev_2_dirs.forEach', " 2级子目录 结束遍历");

            Sys::app()->getPrinter()->endTabEcho('lev_1_dir.forEach', "1级子目录 遍历结束 dirs:[{$deep1ScanedDir->dirsCnt}] files:[{$deep1ScanedDir->filesCnt}]  videos:[$videos_cnt] imgs:[$imgs_cnt]\n");

            if ($deep1ScanedDir->dirsCnt === 0)
            {
                Sys::app()->getPrinter()->tabEcho("无子目录");

                if ($deep1ScanedDir->filesCnt === 0)
                {
                    Sys::app()->getPrinter()->tabEcho("无文件 移除");
                    rmdir($deep1ScanedDir->fullPath);
                }
                else if ($deep1ScanedDir->filesCnt === 1)
                {
                    Sys::app()->getPrinter()->tabEcho("名下一个文件");

                    if ($videos_cnt === 1)
                    {
                        $deep1ScanedFile = $deep1ScanedDir->scanedFiles[0];
                        Sys::app()->getPrinter()->tabEcho("子文件 为纯一个视频 \nold:{$deep1ScanedFile->filePath} ");

                        if ($deep1ScanedFile->isVideo())
                        {
                            Sys::app()->getPrinter()->newTabEcho('scan_lev2_files.file.isVideo', "\n属于视频，准备处理");

                            Sys::app()->getPrinter()->newTabEcho('scan_lev2_files.file.getFormatedName', "获取规范名称");
                            $versioned_filename_name = $deep1ScanedFile->formatVideoFile();
                            Sys::app()->getPrinter()->endTabEcho('scan_lev2_files.file.getFormatedName', "获取规范名称");

                            $repeat_checked_filename = $versioned_filename_name;
                            if (is_file("{$this->rootDir}/{$versioned_filename_name}.{$deep1ScanedFile->ext}"))
                            {
                                Sys::app()->getPrinter()->tabEcho("有重复\n");
                                $rand                    = rand(1, 10000);
                                $repeat_checked_filename = "{$versioned_filename_name}_{$rand}.{$deep1ScanedFile->ext}";
                            }

                            $new_full_filename = "{$this->rootDir}/{$repeat_checked_filename}.{$deep1ScanedFile->ext}";
                            Sys::app()->getPrinter()->tabEcho("only_video move_file:{$deep1ScanedFile->filePath} -> {$new_full_filename}\n");
                            if ($this->isPreivew)
                            {
                                Sys::app()->getPrinter()->tabEcho("注意是预览，未修改\n");
                            }
                            else
                            {
                                rename($deep1ScanedFile->filePath, $new_full_filename);
                            }
                            Sys::app()->getPrinter()->endTabEcho('scan_lev2_files.file.isVideo', "\n属于视频，处理完毕\n\n\n\n\n");

                        }

                    }
                }
                else
                {
                    Sys::app()->getPrinter()->tabEcho("名下多个文件");

                    $versioned_dir_name = $deep1ScanedDir->addNameVersion($deep1ScanedDir->onlyName);
                    if ($videos_cnt > 1 && $deep1ScanedDir->filesCnt === $videos_cnt)
                    {
                        Sys::app()->getPrinter()->tabEcho("子文件都是【视频】，应该加版本了 \nold:{$deep1ScanedDir->fullPath} \nnew:{$rootScanedDir->fullPath}/{$versioned_dir_name}\n");

                        if ($this->isPreivew)
                        {
                            Sys::app()->getPrinter()->tabEcho("注意是预览，未修改\n");
                        }
                        else
                        {
                            rename($deep1ScanedDir->fullPath, "{$rootScanedDir->fullPath}/{$versioned_dir_name}");
                        }

                    }

                    if ($imgs_cnt > 1 && $deep1ScanedDir->filesCnt === $imgs_cnt)
                    {
                        Sys::app()->getPrinter()->tabEcho("子文件都是【图片】，应该加版本了 \nold:{$deep1ScanedDir->fullPath} \nnew:{$rootScanedDir->fullPath}/{$versioned_dir_name}\n");
                        if ($this->isPreivew)
                        {
                            Sys::app()->getPrinter()->tabEcho("注意是预览，未修改\n");
                        }
                        else
                        {
                            rename($deep1ScanedDir->fullPath, "{$rootScanedDir->fullPath}/{$versioned_dir_name}");
                        }
                    }

                }
            }


        }
        Sys::app()->getPrinter()->endTabEcho('scan_lev1_dirs', '扫描一级目录');

        /* foreach ($rootScanedDir->dirs as $deep_1_name)
         {

             if (is_file($deep_1_path))
             {
                 $scanfile = new ScanedFile($rootScanedDir, $deep_1_path);


                 $new_name = $this->formatNameWord('单个文件', $deep_1_path, $scanfile->onlyName, $rubbish_words, $fixed_name_map);

                 $new_name = $this->getSn($new_name);
                 // $new_name = $this->getDescFilename($filename_name);

                 $new_name = join(' ', array_unique(explode(' ', $new_name)));
                 $new_name = Param::convertStrType($new_name, 'TOSBC');

                 $new_name = $this->addNameVersion($new_name);


                 if ($new_name !== $scanfile->onlyName)
                 {
                     if (file_exists("{$root_dir}/{$new_name}.{$scanfile->ext}"))
                     {
                         $new_name = "{$new_name}_" . rand(1, 1000);
                     }

                     echo "dir1_file move_file:{$deep_1_path} -> {$root_dir}/{$new_name}.{$scanfile->ext}   [一级文件，简单格式化]\n";
                     if ($is_preview)
                     {
                         echo "\n注意是预览，未修改\n";
                     }
                     else
                     {
                         rename($deep_1_path, "{$root_dir}/{$new_name}.{$scanfile->ext}");
                     }

                 }
                 else
                 {
                     echo "\n!!!!!!名字一样，未处理\n";
                 }
             }
             else if (is_dir($deep_1_path))
             {
                 $video_file = '';
                 $videos_cnt = 0;
                 $files_cnt  = 0;
                 $imgs_cnt   = 0;
                 $dir_cnt    = 0;

                 $dir2_filenames = array_slice(scandir($deep_1_path), 2);



                 if ($videos_cnt < 1 && $imgs_cnt < 10 && $dir_cnt < 1)
                 {
                     echo "is_rmdir?:{$dir1_full_filename}  没有子目录，没有视频，图片小于10 输入[y] 删除掉\n";
                     var_dump($dir2_filenames);
                     $stdin = trim(fgets(STDIN));

                     if ($stdin === 'y')
                     {
                         echo "rmdir:{$dir1_full_filename}\n";
                         foreach ($dir2_filenames as $dir2_filename)
                         {
                             unlink("{$dir1_full_filename}/{$dir2_filename}");
                         }

                         rmdir($dir1_full_filename);
                     }
                     else
                     {
                         echo "不删除,跳过";
                     }
                 }
                 if ($files_cnt === 0)
                 {
                     echo "rmdir:{$deep_1_path}\n";
                     rmdir($deep_1_path);
                 }
                 else
                 {
                     //  var_dump(count($sub_curr_filenames));
                 }

                 continue;


             }
         }*/
    }


    public function dealFile(ScanedFile $scanedFile)
    {
        if ($scanedFile->isDelFile())
        {
            unlink($scanedFile->filePath);
            return false;
        }

        if ($scanedFile->isZipFile())
        {
            $cmd = "unzip -o {$scanedFile->filePath} -d {$this->rootDir}";
            $cmd = "unzip -O GBK {$scanedFile->filePath} -d {$this->rootDir}";

            echo "\nUNZIP:{$cmd}\n";

            exec($cmd, $ar);
            var_dump($cmd, $ar);
            return false;
        }
        return true;
    }


}

Class VersionControl
{
    private $file_name_ver = '2023_02_02';
    public  $onlyName      = '';

    public static $rubbishWords = [];
    public static $fixNameMap   = [];

    public function trimNameVersion($versioned_filename)
    {
        return str_replace(" _name_ver_{$this->file_name_ver}_", '', $versioned_filename);
    }

    public function addNameVersion($onlyName)
    {
        return $this->isVersionedName($onlyName) ? $onlyName : "{$onlyName} _name_ver_{$this->file_name_ver}_";
    }

    public function isVersionedName()
    {
        return strstr($this->onlyName, "_name_ver_") === false ? false : true;
    }


    public static function init()
    {
        self::$rubbishWords = explode(',', '『,』,【,】,【】,[,],〖,〗,《,》,@,顶级無碼,sex8.cc,guochan2048.com,Night24.com,2048社区,-big2048.com,SEX8.CC,Weagogo,kpxvs.com,miohot0428,jav20s8.com,Woxav.Com,fun2048.com,最新流出,最新,钻石级,❤️,推荐,会所尊享,❤,推荐,蜜桃,传媒,特辑,新作,SEX8.CC,爱剪辑,我的视频');
        self::$fixNameMap   = ['加勒比' => 'carib-', '一本道' => '1pondo-', 'HEYZO' => 'heyzo-'];
    }

    public function formatNameWord($flag, $full_fileanme, $old_str)
    {
        $new_str = $this->trimNameVersion($old_str);
        $new_str = str_replace(self::$rubbishWords, ' ', $old_str);
        foreach (self::$fixNameMap as $old_tag => $fixed_name)
        {
            if (strstr($full_fileanme, $old_tag))
            {
                $new_str = "{$fixed_name} " . str_replace($old_tag, '#', $new_str);
            }
        }

        //$new_name = trim(str_replace($rubbish_words, '', $filename_name));

        $tmp_str = preg_replace('/^([\d\-\.]?)+\s/i', '$2', $new_str);
        if ($tmp_str !== $new_str)
        {
            //   Sys::app()->getPrinter()->tabEcho(">>番号:\n");
            // Sys::app()->getPrinter()->dump($tmp_str);
            $new_str = $tmp_str;
        }
        preg_match_all('/([a-zA-Z0-9]+\.[a-zA-Z]+)/i', $new_str, $tm_ar);
        if (count($tm_ar) && count($tm_ar[1]))
        {
            //   Sys::app()->getPrinter()->tabEcho(">>去网址:\n");
            //  Sys::app()->getPrinter()->dump($new_str, $tm_ar);
            // $new_name = $tmp_str;
            $new_name2 = str_replace($tm_ar[1][0], '', $new_str);
            Sys::app()->getPrinter()->tabEcho("去网址\n{$new_str}\n{$new_name2}\n");

        }

        if ($new_str[0] === '-')
        {
            //  echo ">>去垃圾开头:\n";
            $tmp_str = preg_replace('/^(-)/i', '$2', $new_str);
            //var_dump($tmp_str);
            $new_str = $tmp_str;
        }


        $strs    = array_unique(explode('#', $new_str));
        $new_str = join(' ', $strs);

        if ($old_str !== $new_str)
        {
            Sys::app()->getPrinter()->tabEcho("\n******************************* formatNameWord {$flag} \nold:[{$old_str}] \nnew[{$new_str}]<\n");
        }
        // return $new_str;
        return $new_str;
    }


    public function getSn($str)
    {

        $str = $this->trimNameVersion($str);

        $str     = trim($str);
        $old_str = $str;
        //var_dump($str);
        preg_match_all('/[\d\w]+\.[\w]+/i', $str, $ar);
        //var_dump($ar);
        if (count($ar[0]) === 1)
        {
            $str = str_replace($ar[0][0], '', $str);
        }

        preg_match_all('/[\w\d]+[\d\w\-\s]+\d/i', $str, $ar);
        if (count($ar[0]) > 0)
        {
            //   var_dump($ar);

            $tmp_ar = [];
            foreach ($ar[0] as $tmp_str)
            {
                $tmp_str = trim($tmp_str);
                $tmp_int = intval($tmp_str);
                if (strval($tmp_int) !== $tmp_str)
                {
                    $tmp_ar[] = $tmp_str;
                }
            }
            $str = join(' ', $tmp_ar) . str_replace($tmp_ar, '', $str);
        }
        if ($old_str !== $str)
        {
            Sys::app()->getPrinter()->tabEcho("\n******************************* getSn \nold:[{$old_str}] \nnew[{$str}]<\n");

        }


        return $str;
    }

    /**
     * 去除英文数词 特殊符号
     * @param $only_filename
     * @return string|string[]|null
     * @throws \Exception
     */
    public function getDescFilename($only_filename)
    {
        $only_filename = $this->trimNameVersion($only_filename);
        //$trimed_str = preg_replace('/\s|[\x21-\x7e-A-Za-z0-9]/i', '', $only_filename);
        $trimed_str = preg_replace('/[\x21-\x7e-A-Za-z0-9]/i', '', $only_filename);

        if ($trimed_str !== $only_filename)
        {
            Sys::app()->getPrinter()->tabEcho("\n描述性文件名:[$trimed_str]   原文件名:[$only_filename]\n");
        }
        return $trimed_str;
    }

    public function getDescFilenameLength($versioned_filename)
    {
        return mb_strlen($this->trimNameVersion($versioned_filename));
    }


}

Class ScanedFile extends VersionControl
{
    public static $del_exts   = ['html', 'htm', 'exe', 'torrent', 'txt', 'chm', 'lnk', 'url'];
    public static $video_exts = ['mp4', 'ts', 'avi', 'wmv', 'mkv', 'mov', 'srt'];
    public static $img_exts   = ['gif', 'jpg', 'jpeg', 'png'];

    public $ext      = '';
    public $onlyName = '';
    public $baseName = '';
    public $dirPath  = '';
    public $filePath = '';

    /**
     * @var ScanedDir
     */
    public $parentScanedDir;

    public function __construct(ScanedDir $scanedDir, $base_file_name)
    {
        $this->parentScanedDir = $scanedDir;
        $this->filePath        = "{$scanedDir->fullPath}/{$base_file_name}";

        $ar             = pathinfo($this->filePath);
        $this->dirPath  = $ar['dirname'];
        $this->ext      = strtolower($ar['extension']);
        $this->onlyName = $ar['filename'];//无路径 也无后缀
        $this->baseName = $ar['basename'];
        // [dirname] => /some/path
        //    [basename] => .test
        //    [extension] => test
        //    [filename] =>
    }

    public function isVideo()
    {
        return in_array($this->ext, self::$video_exts);
    }

    public function isImg()
    {
        return in_array($this->ext, self::$img_exts);

    }

    public function isDelFile()
    {
        return in_array($this->ext, self::$del_exts);
    }

    public function isZipFile()
    {
        return in_array($this->ext, ['zip']);
    }

    public function isReadableFile()
    {
        if ($this->isDelFile())
        {
            unlink($this->filePath);
            return false;
        }

        if ($this->isZipFile())
        {
            $cmd = "unzip -o {$this->filePath} -d {$this->dirPath}";
            $cmd = "unzip -O GBK {$this->filePath} -d {$this->dirPath}";

            echo "\nUNZIP:{$cmd}\n";

            exec($cmd, $ar);
            var_dump($cmd, $ar);
            return false;
        }
        return true;
    }

    public function formatVideoFile()
    {
        $filename_name = trim($this->baseName);
        Sys::app()->getPrinter()->newTabEcho('scan_lev1_files.file.getFormatedName.format', "尝试1");

        $formated_filename_name = $this->formatNameWord('单个文件dir', $this->filePath, $this->onlyName);
        Sys::app()->getPrinter()->endTabEcho('scan_lev1_files.file.getFormatedName.format', "尝试1");

        Sys::app()->getPrinter()->newTabEcho('scan_lev1_files.file.getFormatedName.getSN', "序号");
        $sned_filename_name = $this->getSn($formated_filename_name);
        Sys::app()->getPrinter()->endTabEcho('scan_lev1_files.file.getFormatedName.getSN', "序号");

        Sys::app()->getPrinter()->newTabEcho('scan_lev1_files.file.getFormatedName.getDesc', "获取描述信息");
        $desc_name = $this->getDescFilename($sned_filename_name);
        Sys::app()->getPrinter()->endTabEcho('scan_lev1_files.file.getFormatedName.getDesc', "获取描述信息");

        $desc_verifyed_filename = $sned_filename_name;
        if ($this->getDescFilenameLength($desc_name) < 15)
        {
            $desc_verifyed_filename = "{$this->parentScanedDir->onlyName} {$desc_verifyed_filename}";
            Sys::app()->getPrinter()->tabEcho("原dir name 描述信息too short，需要拼接一级dir name \nold:{$this->onlyName} \nnew:{$desc_verifyed_filename}\n");
        }
        $desc_verifyed_filename = join(' ', array_unique(explode(' ', $desc_verifyed_filename)));
        $desc_verifyed_filename = Param::convertStrType($desc_verifyed_filename, 'TOSBC');

        return $this->addNameVersion($desc_verifyed_filename);


    }

}

Class ScanedDir extends VersionControl
{
    public $dirsCnt  = 0;
    public $filesCnt = 0;

    public $dirs    = [];
    public $files   = [];
    public $dellist = [];

    public $isRoot   = false;
    public $fullPath = '';
    public $dirName  = '';
    public $onlyName = '';
    /**
     * @var ScanedDir|bool
     */
    public $parentScanedDir = false;

    /**
     * @var ScanedDir[]
     */
    public $scanedDirs = [];
    /**
     * @var ScanedFile[]
     */
    public $scanedFiles = [];

    static $i = 0;

    public function __construct($parentScanedDir, $dirName)
    {
        if ($parentScanedDir)
        {
            $this->parentScanedDir = $parentScanedDir;
            $this->fullPath        = "{$this->parentScanedDir->fullPath}/{$dirName}";
            $this->dirName         = $dirName;
            $this->onlyName        = $dirName;
        }
        else
        {
            $this->fullPath = $dirName;
            $this->isRoot   = true;
            $this->scanSelfDir();
        }
        if ($this->isVersionedName() === false && $this->isRoot === false)
        {
            //            var_dump($dirName);
            //            self::$i++;
            //            if(self::$i>2){
            //                throw new \Exception('dd');
            //                //debug_print_backtrace();die;
            //            }
            $this->scanSelfDir();
        }

    }

    public function scanSelfDir()
    {
        $sub_names = scandir($this->fullPath);
        // var_dump($this->fullPath);
        // echo "fullPath:{$this->fullPath}\n";
        foreach ($sub_names as $sub_name)
        {
            if (in_array($sub_name, ['.', '..']))
            {//scan 的结果，这两个不一定排在最前面，所以不能用array_slice
                continue;
            }
            $sub_path       = "{$this->fullPath}/{$sub_name}";
            $lower_sub_name = strtolower($sub_name);


            //如果是目录，直接移出去
            if (is_dir($sub_path))
            {
                $this->dirsCnt = $this->dirsCnt + 1;
                //  echo "\nsub {$this->fullPath}/{$sub_name} \n";
                $this->scanedDirs[] = new ScanedDir($this, $sub_name);
            }

            if (is_file($sub_path))
            {
                $this->scanedFiles[] = new ScanedFile($this, $sub_name);
                $this->filesCnt      = $this->filesCnt + 1;

            }
        }
    }


    public function getFormatedName()
    {
        Sys::app()->getPrinter()->newTabEcho('dir.getFormatedName.format', "尝试1");

        $formated_filename_name = $this->formatNameWord('单个文件dir', $this->fullPath, $this->onlyName);
        Sys::app()->getPrinter()->endTabEcho('dir.getFormatedName.format', "尝试1");

        Sys::app()->getPrinter()->newTabEcho('dir.getFormatedName.getSN', "序号");
        $sned_filename_name = $this->getSn($formated_filename_name);
        Sys::app()->getPrinter()->endTabEcho('dir.getFormatedName.getSN', "序号");

        Sys::app()->getPrinter()->newTabEcho('dir.getFormatedName.getDesc', "获取描述信息");
        $desc_name = $this->getDescFilename($sned_filename_name);
        Sys::app()->getPrinter()->endTabEcho('dir.getFormatedName.getDesc', "获取描述信息");

        $desc_verifyed_filename = $sned_filename_name;
        if ($this->getDescFilenameLength($desc_name) < 15)
        {
            $desc_verifyed_filename = "{$this->parentScanedDir->onlyName} {$desc_verifyed_filename}";
            Sys::app()->getPrinter()->tabEcho("原dir name 描述信息too short，需要拼接一级dir name \nold:{$this->onlyName} \nnew:{$desc_verifyed_filename}\n");

        }
        $desc_verifyed_filename = join(' ', array_unique(explode(' ', $desc_verifyed_filename)));
        $desc_verifyed_filename = Param::convertStrType($desc_verifyed_filename, 'TOSBC');

        // return $this->addNameVersion($desc_verifyed_filename);
        return $desc_verifyed_filename;

    }

}