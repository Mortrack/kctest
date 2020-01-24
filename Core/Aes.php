<?php

namespace Core;
use App\Config;

/**
 * AES Encryption
 *
 * Class Ip
 * @package Core
 *
 * @author Miranda Meza César
 * DATE September 22, 2018
 */
class Aes
{
    protected $key;
    protected $data;
    protected $method;

    /**
     * Available OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
     *
     * @var int
     *
     * @author Miranda Meza César
     * DATE September 22, 2018
     */
    protected $options = 0;


    /**
     * Aes constructor.
     *
     * @param null $data
     * @param null $key
     * @param null $blockSize
     * @param string $mode
     *
     * @author Miranda Meza César
     * DATE September 22, 2018
     */
    function __construct($data = null, $key = null, $blockSize = null, $mode = 'CBC')
    {
        $this->setData($data);
        $this->setKey($key);
        $this->setMethode($blockSize, $mode);
    }

    /**
     * @param $data
     *
     * @author Miranda Meza César
     * DATE September 22, 2018
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param $key
     *
     * @author Miranda Meza César
     * DATE September 22, 2018
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * CBC 128 192 256
    CBC-HMAC-SHA1 128 256
    CBC-HMAC-SHA256 128 256
    CFB 128 192 256
    CFB1 128 192 256
    CFB8 128 192 256
    CTR 128 192 256
    ECB 128 192 256
    OFB 128 192 256
    XTS 128 256
     *
     * @param $blockSize
     * @param string $mode
     *
     * @author Miranda Meza César
     * DATE September 22, 2018
     */
    public function setMethode($blockSize, $mode = 'CBC')
    {
        if($blockSize==192 && in_array('', array('CBC-HMAC-SHA1','CBC-HMAC-SHA256','XTS'))){
            $this->method=null;
            throw new Exception('Invlid block size and mode combination!');
        }
            $this->method = 'AES-' . $blockSize . '-' . $mode;
    }

    /**
     * @return bool
     *
     * @author Miranda Meza César
     * DATE September 22, 2018
     */
    public function validateParams()
    {
        if ($this->data != null && $this->method != null ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * it must be the same when you encrypt and decrypt
     *
     * @return string
     *
     * @author Miranda Meza César
     * DATE September 22, 2018
     */
    protected function getIV()
    {
        return Config::AES_IV;
    }

    /**
     * @return string
     *
     * @author Miranda Meza César
     * DATE September 22, 2018
     */
    public function encrypt()
    {
        if ($this->validateParams()) {
            return trim(openssl_encrypt($this->data, $this->method, $this->key, $this->options,$this->getIV()));
        } else {
            throw new Exception('Invalid params!');
        }
    }

    /**
     * @return string
     *
     * @author Miranda Meza César
     * DATE September 22, 2018
     */
    public function decrypt()
    {
        if ($this->validateParams()) {
            $ret=openssl_decrypt($this->data, $this->method, $this->key, $this->options,$this->getIV());
            return   trim($ret);
        } else {
            throw new Exception('Invalid params!');
        }
    }
}
