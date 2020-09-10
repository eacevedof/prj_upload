<?php
namespace App\Services;
use \Exception;
use TheFramework\Components\Config\ComponentConfig;

class UploadMultipleService extends AppService
{
    private $files;
    private $post;
    private $rootpath;
    private $urls;
    private $arprocess;

    private const INVALID_EXTENSIONS = [
        "php","js","py","html","phar","java","sh","htaccess","jar"
    ];

    private $resources_url;

    public function __construct($post,$files)
    {
        $this->post = $post;
        $this->files = $files["files"] ?? [];
        $this->rootpath = $this->get_env("APP_UPLOADROOT");
        $this->resources_url = $this->get_env("APP_RESOURCES_URL");
    }

    public static function get_maxsize()
    {
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        //$memory_limit = (int)(ini_get('memory_limit'));//en prod me devuelve -1
        $upload_mb = min($max_upload, $max_post);
        //en prod son 64M para upload y post
        //lg("get_maxsize(): upload_max_filesize:$max_upload, post_max_size:$max_post","get_maxsize");
        return $upload_mb;
    }
    
    private static function _get_maxsize(){
        $size = self::get_maxsize()."MB";
        return get_in_bytes($size);
    }

    private function _get_domains()
    {
        $sPathfile = $_ENV["APP_DOMAINS"] ?? __DIR__.DIRECTORY_SEPARATOR."domains.json";
        //print($sPathfile);die;
        $arconf = (new ComponentConfig($sPathfile))->get_content();
        return $arconf;
    }

    private function _is_valid()
    {
        if(!trim($this->rootpath)) throw new Exception("missing env UPLOADROOT");
        if(!$this->post) throw new Exception("Empty post");
        if(!$this->files) throw new Exception("Empty files");

        if(!isset($this->post["folderdomain"]) || trim($this->post["folderdomain"])==="") throw new Exception("No domain selected");
        if(!in_array(trim($this->post["folderdomain"]),$this->_get_domains())) throw new Exception("Forbidden folderdomain: {$this->post["folderdomain"]}");

        $this->arprocess = $this->_get_valid_files();
        if(!$this->arprocess) throw new Exception("No files to process");
    }

    private function _get_basename($rawname){return basename($rawname);}

    private function _get_extension($pathfile){return pathinfo($pathfile, PATHINFO_EXTENSION);}

    private function _get_saved($pathfinal,$ipos){
        $r = move_uploaded_file($this->arprocess[$ipos]["tmp_name"],$pathfinal);
        if(!$r) {
            $error = "Error moving: {$this->arprocess[$ipos]["tmp_name"]} to $pathfinal";
            $this->logd($this->arprocess,"arprocess of $ipos");
            $this->logd($error);
            $this->add_error($error);
        }
        return $r;
    }

    private function _is_invalid($extension)
    {
        $extension = trim($extension);
        $extension = strtolower($extension);
        return in_array($extension,self::INVALID_EXTENSIONS);
    }

    private function _is_oversized($size){return $size > $this->_get_maxsize();}

    private function _get_valid_files()
    {
        $arvalid = [];
        foreach ($this->files as $inputfile => $arfile){

        }
        return $arvalid;
    }

    private function _get_cleaned($filename)
    {
        $cleaned = strtolower($filename);
        $cleaned = str_replace(" ","-",$cleaned);
        return $cleaned;
    }

    private function _upload_single($ipos)
    {
        $extension = $this->_get_extension($this->arprocess[$ipos]["name"]);
        if($this->_is_invalid($extension)){
            $error = "file: {$this->arprocess[$ipos]["name"]} not uploaded. Itcontains forbidden extension";
            $this->add_error($error);
            return;
        }

        if(((int) $this->arprocess[$ipos]["size"]) === 0 ){
            $maxsize = $this->_get_maxsize();
            $error = "filesize is: 0. May be it is bigger than allowed ($maxsize bytes)";
            $this->add_error($error);
            return;
        }

        if($this->_is_oversized((int)$this->arprocess[$ipos]["size"])){
            $maxsize = $this->_get_maxsize();
            $error = "file: {$this->arprocess[$ipos]["name"]} is larger ({$this->arprocess[$ipos]["size"]}) than allowed {$maxsize}";
            $this->add_error($error);
            return;
        }

        $today = date("Ymd");
        $folderdomain = trim($this->post["folderdomain"]);
        $pathdest = "{$this->rootpath}/$folderdomain/{$today}";
        if(!file_exists($pathdest)) mkdir($pathdest, 0777, true);
        $filename = $this->_get_basename($this->arprocess[$ipos]["name"]);

        $now = date("His");
        $fileclean = $this->_get_cleaned($filename);
        $filefinal = "{$now}-{$fileclean}";
        $pathfinal = "{$pathdest}/$filefinal";
        if(is_file($pathfinal)) unlink($pathfinal);

        $r = $this->_get_saved($pathfinal,$ipos);
        if(!$r) $this->add_error("An error ocurred while moving file: $filename to final dir");
        else
            $this->urls[$ipos] = $this->resources_url."/$folderdomain/$today/$filefinal";
    }

    private function _upload()
    {
        $keys = array_keys($this->arprocess);
        foreach ($keys as $ipos)
            $this->_upload_single($ipos);
    }

    public function get_uploaded()
    {
        $this->_is_valid();
        $this->_upload();
        return $this->urls;
    }
}

/*
'fil-one' =>
array (
'name' => 'trello-397002984.jpg',
'type' => 'image/jpeg',
'tmp_name' => '/private/var/folders/yt/g9dtf4cj40s6m5b4m_rzjz8m0000gn/T/phpKQHL21',
'error' => 0,
'size' => 504284,
),*
*/