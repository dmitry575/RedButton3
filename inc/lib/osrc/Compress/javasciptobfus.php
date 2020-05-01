<?
class JavaScriptObfus
  {
  private $m_symbol = '$i';
  private $m_b_ar = array("___",
                          "__\$",
                          "_\$_",
                          "_\$\$",
                          "\$__",
                          "\$_\$",
                          "\$\$_",
                          "\$\$\$",
                          "\$___",
                          "\$__\$",
                          "\$_\$_",
                          "\$_\$\$",
                          "\$\$__",
                          "\$\$_\$",
                          "\$\$\$_",
                          "\$\$\$\$");

  public function __constructor($symbol)
    {
    $this->m_symbol = $symbol;
    }

  public function pack($text)
    {
    $r = "";
    $s = "";
    $n = NULL;
    $t = NULL;
    for($i = 0; $i < strlen($text); $i++)
      {
      $n = ord($text{$i});
      if($n == 0x22 || $n == 0x5c)
        {
        $s .= "\\\\\\" . $text{$i};
        }
      elseif((0x21 <= $n && $n <= 0x2f) || (0x3A <= $n && $n <= 0x40) || (0x5b <= $n && $n <= 0x60) || (0x7b <= $n && $n <= 0x7f))
        {
        $s .= $text{$i};
        }
      elseif((0x30 <= $n && $n <= 0x39) || (0x61 <= $n && $n <= 0x66))
        {
        if($s)
          {
          $r .= "\"" . $s . "\"+";
          }
        $r .= $this->m_symbol . "." . $this->m_b_ar[($n < 0x40 ? $n - 0x30 : $n - 0x57)] . "+";
        $s = "";
        }
      elseif($n == 0x6c)
        { // 'l'
        if($s)
          {
          $r .= "\"" . $s . "\"+";
          }
        $r .= "(![]+\"\")[" . $this->m_symbol . "._\$_]+";
        $s = "";
        }
      elseif($n == 0x6f)
        { // 'o'
        if($s)
          {
          $r .= "\"" . $s . "\"+";
          }
        $r .= $this->m_symbol . "._\$+";
        $s = "";
        }
      elseif($n == 0x74)
        { // 'u'
        if($s)
          {
          $r .= "\"" . $s . "\"+";
          }
        $r .= $this->m_symbol . ".__+";
        $s = "";
        }
      elseif($n == 0x75)
        { // 'u'
        if($s)
          {
          $r .= "\"" . $s . "\"+";
          }
        $r .= $this->m_symbol . "._+";
        $s = "";
        }
      elseif($n < 128)
        {
        if($s)
          {
          $r .= "\"" . $s;
          }
        else
          {
          $r .= "\"";
          }
        $r .= "\\\\\"+" . preg_replace_callback('/([0-7])/',array($this,'IntPack'), decoct(intval($n)));
        $s = "";
        }
      else
        {
        if($s)
          {
          $r .= "\"" . $s;
          }
        else
          {
          $r .= "\"";
          }
        $r .= "\\\\\"+" . $this->m_symbol . "._+" . preg_replace_callback('/0-9a-f/i',array($this,'HexPack'), dechex(intval($n)));
        $s = "";
        }
      }
    if($s)
      {
      $r .= "\"" . $s . "\"+";
      }
    $r = $this->m_symbol . "=~[];" . $this->m_symbol . "={___:++" . $this->m_symbol . ",\$\$\$\$:(![]+\"\")[" . $this->m_symbol . "],__\$:++" . $this->m_symbol . ",\$_\$_:(![]+\"\")[" . $this->m_symbol . "],_\$_:++" . $this->m_symbol . ",\$_\$\$:({}+\"\")[" . $this->m_symbol . "],\$\$_\$:(" . $this->m_symbol . "[" . $this->m_symbol . "]+\"\")[" . $this->m_symbol . "],_\$\$:++" . $this->m_symbol . ",\$\$\$_:(!\"\"+\"\")[" . $this->m_symbol . "],\$__:++" . $this->m_symbol . ",\$_\$:++" . $this->m_symbol . ",\$\$__:({}+\"\")[" . $this->m_symbol . "],\$\$_:++" . $this->m_symbol . ",\$\$\$:++" . $this->m_symbol . ",\$___:++" . $this->m_symbol . ",\$__\$:++" . $this->m_symbol . "};" . $this->m_symbol . ".\$_=" . "(" . $this->m_symbol . ".\$_=" . $this->m_symbol . "+\"\")[" . $this->m_symbol . ".\$_\$]+" . "(" . $this->m_symbol . "._\$=" . $this->m_symbol . ".\$_[" . $this->m_symbol . ".__\$])+" . "(" . $this->m_symbol . ".\$\$=(" . $this->m_symbol . ".\$+\"\")[" . $this->m_symbol . ".__\$])+" . "((!" . $this->m_symbol . ")+\"\")[" . $this->m_symbol . "._\$\$]+" . "(" . $this->m_symbol . ".__=" . $this->m_symbol . ".\$_[" . $this->m_symbol . ".\$\$_])+" . "(" . $this->m_symbol . ".\$=(!\"\"+\"\")[" . $this->m_symbol . ".__\$])+" . "(" . $this->m_symbol . "._=(!\"\"+\"\")[" . $this->m_symbol . "._\$_])+" . $this->m_symbol . ".\$_[" . $this->m_symbol . ".\$_\$]+" . $this->m_symbol . ".__+" . $this->m_symbol . "._\$+" . $this->m_symbol . ".\$;" . $this->m_symbol . ".\$\$=" . $this->m_symbol . ".\$+" . "(!\"\"+\"\")[" . $this->m_symbol . "._\$\$]+" . $this->m_symbol . ".__+" . $this->m_symbol . "._+" . $this->m_symbol . ".\$+" . $this->m_symbol . ".\$\$;" . $this->m_symbol . ".\$=(" . $this->m_symbol . ".___)[" . $this->m_symbol . ".\$_][" . $this->m_symbol . ".\$_];" . $this->m_symbol . ".\$(" . $this->m_symbol . ".\$(" . $this->m_symbol . ".\$\$+\"\\\"\"+" . $r . "\"\\\"\")())();";
    /*if($palindrome)
      {
      $r = preg_replace('/[,;]$/', '', $r);
      $r = "\"'\\\"+'+\"," . $r . ",'," . implode("", array_reverse(str_split($r))) . ",\"+'+\"\\'\"";
      }*/
    return $r;
    }
  private function IntPack($match){return $this->m_symbol . "." . $this->m_b_ar[(int)$match[1]] . "+";}
  private function HexPack($match){return $this->m_symbol . "." . $this->m_b_ar[hexdec($match[1])] . "+";}
  }

?>
