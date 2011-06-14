<?php
/*
* Developer : Greg Russell (grgrssll@gmail.com)
* Date : 06/13/2011
*
* Script: Crypt.php
* Description: wrapper for php's mcrypt functions
*
*/
class Crypt{
    
    private $key;
    private $key_size;
    private $resource;
    private $iv_size;
    private $iv;
    private $algorithms;
    private $algorithm;
    private $modes;
    private $mode;
    private $base64;
    
    public function __construct($params){
        # make sure mcrypt is loaded
        if(!extension_loaded('mcrypt')){
            $this->error('mcrypt extension is required for this script');
            return false;
        }
        # make sure key is supplied
        if(!array_key_exists('key', $params) && $params['key']){
            $this->error('key is a required parameter. see listOptions()');
            return false;
        }
        # set params
        $this->key = $params['key'];
        $this->algorithms = mcrypt_list_algorithms();
        $this->modes = mcrypt_list_modes();
        # check availables
        if(!count($this->algorithms)){
            $this->error('there are no available algorithms for mcrypt');
            return false;
        }
        if(!count($this->modes)){
             $this->error('there are no available modes for mcrypt');
             return false;
        }
        # algorithm
        $this->algorithm = $this->algorithms[0];        
        if(array_key_exists('algorithm', $params) && in_array($params['algorithm'], $this->algorithms)){
            $this->algorithm  = $params['algorithm'];
        }
        # mode
        $this->mode = $this->modes[0];
        if(array_key_exists('mode', $params) && in_array($params['mode'], $this->modes)){
            $this->mode = $params['mode'];
        }
        # base 64 encoding
        $this->base64 = true;
        if(array_key_exists('base64', $params) && !$params['base64']){
            $this->base64 = false;
        }
        
        return $this->start();
    }

    private function error($message){
        echo $message;
    }
    
    private function start(){
        $this->resource  = mcrypt_module_open($this->algorithm, '', $this->mode, '');
        $this->key_size  = mcrypt_enc_get_key_size($this->resource);
        $this->key       = substr($this->key, 0, $this->key_size);
        $this->iv_size   = mcrypt_enc_get_iv_size($this->resource);
        $this->iv        = mcrypt_create_iv($this->iv_size, MCRYPT_RAND);
        return mcrypt_generic_init($this->resource, $this->key, $this->iv);
    }
    
    public function encrypt($data){
        mcrypt_generic_init($this->resource, $this->key, $this->iv);
        $encrypted = mcrypt_generic($this->resource, $data);
        return ($this->base64) ? base64_encode($encrypted) : $encrypted;
    }
    
    public function decrypt($data){
        mcrypt_generic_init($this->resource, $this->key, $this->iv);
        $data = ($this->base64) ? base64_decode($data) : $data;
        return mdecrypt_generic($this->resource, $data);
    }
    
    public function close(){
        @mcrypt_generic_deinit($this->resource);
        @mcrypt_module_close($this->resource);
    }
    
    public function listModes(){
        return $this->modes;
    }
    
    public function listAlgorithms(){
        return $this->algorithms;
    }
    
    public function listKeysize(){
        return $this->key_size;
    }

    public function listOptions(){
      $options = "<style>pre{font-size:12px;color: #777;font-family: droid sans mono, monospace;}.key{color:#000;}em{font-style:italic;color:#038;}.required{color:red;}.optional{color:green;}strong{color:#038;}.notes{color:#444;}</style>
      <pre><span class=\"key\">key</span>        => <em>string</em> - <span class=\"required\">(required)</span> <strong>no default</strong> <span class=\"notes\">resized to fit mode/algorithm</span></pre>
      <pre><span class=\"key\">mode</span>       => <em>must be a result of mcrypt_list_modes()</em> - <span class=\"optional\">(optional)</span> <strong>default: first result from mcrypt_list_modes()</strong></pre>
      <pre><span class=\"key\">algorithm</span>  => <em>must be a result of mcrypt_list_algorithms()</em> - <span class=\"optional\">(optional)</span> <strong>default: first result from mcrypt_list_algorithms()</strong></pre>
      <pre><span class=\"key\">base64</span>     => <em>true|false</em> <span class=\"notes\">sets encoding of input/output to base 64</span> - <span class=\"optional\">(optional)</span> <strong>default: true</strong></pre>";
      return $options;
    }
    
    public function getMode(){
        return $this->mode;
    }
    
    public function getAlgorithm(){
        return $this->algorithm;
    }

    public function getBase64Encoding(){
        return $this->base64;
    }
    
    public function setMode($mode){
        $this->mode = (in_array($mode, $this->modes)) ? $mode : $this->modes[0];
        $this->close();
        $this->start();
        return $this->mode;
    }
    
    public function setAlgorithm($algorithm){
        $this->algorithm = (in_array($algorithm, $this->algorithms)) ? $algorithm : $this->algorithms[0];
        $this->close();
        $this->start();
        return $this->algorithm;
    }

    public function setBase64Encoding($base64){
        $this->base64 = ($base64) ? true : false;
        return $this->base64;
    }
}
?>
