<?php

define('SRT_STATE_SUBNUMBER', 1);
define('SRT_STATE_TIME',      2);
define('SRT_STATE_TEXT',      3);
define('SRT_STATE_BLANK',     4);

class rm{

    public function write(){
        $files = $this->get_files(".");
        foreach($files as $file){
            $ret = $this->convert($file);
            $fileName = str_replace(".en.srt",".zh_CN.srt",$file);
            for($i=0;$i<count($ret);$i++){
                file_put_contents($fileName, $ret[$i]["number"]."\n", FILE_APPEND);
                file_put_contents($fileName, $ret[$i]["startTime"]." --> ".$ret[$i]["stopTime"]."\n", FILE_APPEND);
                file_put_contents($fileName, $ret[$i]["text"]."\n", FILE_APPEND);
            }
            unlink($file);
        }
    }

    public function convert($filename){

        $lines   = file($filename);
        $subs    = array();
        $state   = SRT_STATE_SUBNUMBER;
        $subNum  = 0;
        $subText = '';
        $subTime = '';
        $i=0;

        foreach($lines as $line) {
            $i++;
            switch($state) {
                case SRT_STATE_SUBNUMBER:
                    $subNum = trim($line);
                    $state  = SRT_STATE_TIME;
                    break;

                case SRT_STATE_TIME:
                    $subTime = trim($line);
                    $state   = SRT_STATE_TEXT;
                    break;

                case SRT_STATE_TEXT:
                    if (trim($line) == '' ) {
                        $sub = new stdClass;
                        $sub->number = $subNum;
                        list($sub->startTime, $sub->stopTime) = explode(' --> ', $subTime);
                        $sub->text   = $subText;
                        $subText     = '';
                        $state       = SRT_STATE_SUBNUMBER;

                        $subs[]      = (array)$sub;
                    } else {
                        if (preg_match("/[\x7f-\xff]/", $line)) {  //判断字符串中是否有中文
                            // 中文，不处理
                            $subText =$subText.$line;
                        }
                        if($i==count($lines)){
                            $sub = new stdClass;
                            $sub->number = $subNum;
                            list($sub->startTime, $sub->stopTime) = explode(' --> ', $subTime);
                            $sub->text   = $subText;
                            $subText     = '';
                            $state       = SRT_STATE_SUBNUMBER;

                            $subs[]      = (array)$sub;
                        }
                    }
                    break;
            }
        }
        if ($subs){
            return $subs;
        }
        return false;
    }
    function get_files($dir) {
        $files = array();

        if(!is_dir($dir)) {
            return $files;
        }

        $handle = opendir($dir);
        if($handle) {
            while(false !== ($file = readdir($handle))) {
                if ($file != '.' && $file != '..') {
                    $filename = $dir . "/"  . $file;
                    if(is_file($filename)) {
                        $files[] = $filename;
                    }else {
                        $files = array_merge($files, $this->get_files($filename));
                    }
                }
            }   //  end while
            closedir($handle);
        }
        return $files;
    }   //
}
$rm = new rm();
$rm->write();
