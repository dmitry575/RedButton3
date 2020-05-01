<?php
//+------------------------------------------------------------------+
//|                  Copyright c 2012, RedButton Web Sites Generator |
//|                                      http://www.getredbutton.com |
//+------------------------------------------------------------------+

class CModel_crypt
{
    //--- crypto array
    private $crypt_iv = null;
    //--- crypto random string
    private $m_crypt_rand = null;
    private $m_aes_out = null;

    /**
     *
     * @param string $crypt hash random string
     * @param string $password password to connection
     * @return void
     */
    public function SetCryptRand($license)
    {
        $this->m_crypt_rand = $license;
        $out = md5(md5(substr($license, 8, 16), true) . substr($license, 0, 8));
        //---
        for ($i = 0; $i < 16; $i++) {
            $out = md5(CModel_tools::GetFromHex(substr($this->m_crypt_rand, $i * 32, 32)) . CModel_tools::GetFromHex($out));
            $this->crypt_iv[$i] = $out;
        }
    }

    /**
     * Crypt the packet
     * @param string $packet_body
     * @return string|null
     */
    public function CryptPacket($packet_body)
    {
        $result = '';

        $key = $this->crypt_iv[0] . $this->crypt_iv[1];
        $this->m_crypt_out = new CModel_CryptAlg(CModel_tools::GetFromHex($key));
        //---
        $this->m_aes_out = $this->crypt_iv[2];
        $this->m_aes_out = CModel_tools::GetFromHex($this->m_aes_out);
        //--- check aes
        if (empty($this->m_aes_out)) {
            return null;
        }
        //---
        for ($i = 0, $key = 16; $i < strlen($packet_body); $i++) {
            if ($key >= 16) {
                //--- get new key for xor
                $this->m_aes_out = $this->m_crypt_out->encryptBlock($this->m_aes_out);
                //---  key index is 0
                $key = 0;
            }
            //--- xor all bytes
            $result .= chr(ord($packet_body[$i]) ^ ord($this->m_aes_out[$key]));
            $key++;
        }
        //--- return crypt string
        return $result;
    }

    /**
     * @param $packet_body
     * @return string|null
     */
    public function DeCryptPacket($packet_body)
    {
        if ($packet_body == null) return null;
        //---
        if ($this->m_crypt_in == null) {
            $key = $this->crypt_iv[0] . $this->crypt_iv[1];
            $this->m_crypt_in = new CModel_CryptAlg(CModel_tools::GetFromHex($key));
            //--- create aes in array
            $this->m_aes_in = $this->crypt_iv[3];
            $this->m_aes_in = CModel_tools::GetFromHex($this->m_aes_in);
        }
        //---
        if (empty($this->m_aes_in)) {
            return false;
        }
        $out_result = '';
        for ($i = 0, $key = 16; $i < strlen($packet_body); $i++) {
            if ($key >= 16) {
                //--- get new key for xor
                $this->m_aes_in = $this->m_crypt_in->encryptBlock($this->m_aes_in);
                //---
                $key = 0;
            }
            //--- xor all bytes
            $out_result .= chr(ord($packet_body[$i]) ^ ord($this->m_aes_in[$key]));
            $key++;
        }
        return $out_result;
    }

}

?>