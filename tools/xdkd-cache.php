<?php

class XdkdCache {
  private $base_path;

  public function __construct()
  {
      $upload_dir = wp_upload_dir();
      $this->base_path = $upload_dir["basedir"].'/xdkd-cache';
      if(!is_dir($this->base_path)) {
        if(!mkdir($this->base_path, 0700)) {
          throw new \Exception("Unable to create XdkdCache folder");
        }
      }

      // TODO : remove after test
      // $file_path = $this->base_path.'/index.txt';
      // try {
      //   $bool = file_put_contents($file_path, "hello world");
      //     } catch (Exception $e) {
      //   echo 'Exception reçue : ',  $e->getMessage(), "\n";
      // }
  }

  public function getCache($key, $file_path) {
    $cache_file_name = $this->getCacheFileName($file_path);
    $cache_file_path = $this->base_path.'/'.$cache_file_name.'.json';

    if(MD5($cache_file_name) !== $key) {
      throw new \Exception("Bad cache key");
    }
    if(!is_readable($cache_file_path)) {
      return false;
    }
    $content = file_get_contents($cache_file_path);
    $decoded_content = json_decode($content);

    return $decoded_content;
  }

  public function writeCache($file_path, $content) {
    $cache_file_name  = $this->getCacheFileName($file_path);
    $cache_file_path = $this->base_path . '/' . $cache_file_name.'.json';
    $encoded_content = json_encode($content);

    if(!is_writable($this->base_path)) {
      throw new \Exception("XdkdCache folder not writable", 1);
    }

    if(file_put_contents($cache_file_path, $encoded_content) === false) {
      throw new \Exception("Fail to writeCache", 1);
    }

    return MD5($cache_file_name);
  }

  private function getCacheFileName($file_path) {
    return preg_replace('/\//','-',$file_path);
  }

  public function clearCache() {
    $files = glob($this->base_path.'/*');
    $result = true;
    foreach($files as $file){ // iterate files
      if(is_file($file))
        $result &= unlink($file); // delete file
    }
    return $result;
  }

  public function clearFileCache($cache_file_name) {
    $cache_file_path = $this->base_path . '/' . $cache_file_name.'.json';
    $files = glob($cache_file_path);
    $result = true;
    foreach($files as $file){ // iterate files
      if(is_file($file))
        $result &= unlink($file); // delete file
    }
    return $result;
  }
}
