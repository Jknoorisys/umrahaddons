<?php
namespace App\Libraries\pdf;

use App\Libraries\pdf\FPDF;
use App\Libraries\pdf\FormatedString;
use Config\Services;
use Exception;


class ExFPDF extends FPDF
{

   public $encrypted = false;  //whether document is protected
   public $Uvalue;             //U entry in pdf document
   public $Ovalue;             //O entry in pdf document
   public $Pvalue;             //P entry in pdf document
   public $enc_obj_id;         //encryption object id


   public function PageBreak()
   {
      return $this->PageBreakTrigger;
   }

   public function current_font($c)
   {
      if ($c == 'family') {
         return $this->FontFamily;
      } elseif ($c == 'style') {
         return $this->FontStyle;
      } elseif ($c == 'size') {
         return $this->FontSizePt;
      }
   }

   function SetDash($black = null, $white = null)
   {
      if ($black !== null)
         $s = sprintf('[%.3F %.3F] 0 d', $black * $this->k, $white * $this->k);
      else
         $s = '[] 0 d';
      $this->_out($s);
   }

   public function get_color($c)
   {
      if ($c == 'fill') {
         return $this->FillColor;
      } elseif ($c == 'text') {
         return $this->TextColor;
      }
   }

   public function get_page_width()
   {
      return $this->w;
   }

   public function get_margin($c)
   {
      if ($c == 'l') {
         return $this->lMargin;
      } elseif ($c == 'r') {
         return $this->rMargin;
      } elseif ($c == 't') {
         return $this->tMargin;
      }
   }

   public function get_linewidth()
   {
      return $this->LineWidth;
   }

   public function get_orientation()
   {
      return $this->CurOrientation;
   }
   static private $hex = array(
      '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
      'A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15
   );

   public function is_rgb($str)
   {
      $a = true;
      $tmp = explode(',', trim($str, ','));
      foreach ($tmp as $color) {
         if (!is_numeric($color) || $color < 0 || $color > 255) {
            $a = false;
            break;
         }
      }
      return $a;
   }

   public function is_hex($str)
   {
      $a = true;
      $str = strtoupper($str);
      $n = strlen($str);
      if (($n == 7 || $n == 4) && $str[0] == '#') {
         for ($i = 1; $i < $n; $i++) {
            if (!isset(self::$hex[$str[$i]])) {
               $a = false;
               break;
            }
         }
      } else {
         $a = false;
      }
      return $a;
   }

   public function hextodec($str)
   {
      $result = array();
      $str = strtoupper(substr($str, 1));
      $n = strlen($str);
      for ($i = 0; $i < 3; $i++) {
         if ($n == 6) {
            $result[$i] = self::$hex[$str[2 * $i]] * 16 + self::$hex[$str[2 * $i + 1]];
         } else {
            $result[$i] = self::$hex[$str[$i]] * 16 + self::$hex[$str[$i]];
         }
      }
      return $result;
   }
   static private $options = array('F' => '', 'T' => '', 'D' => '');

   public function resetColor($str, $p = 'F')
   {
      if (isset(self::$options[$p]) && self::$options[$p] != $str) {
         self::$options[$p] = $str;
         $array = array();
         if ($this->is_hex($str)) {
            $array = $this->hextodec($str);
         } elseif ($this->is_rgb($str)) {
            $array = explode(',', trim($str, ','));
            for ($i = 0; $i < 3; $i++) {
               if (!isset($array[$i])) {
                  $array[$i] = 0;
               }
            }
         } else {
            $array = array(null, null, null);
            $i = 0;
            $tmp = explode(' ', $str);
            foreach ($tmp as $c) {
               if (is_numeric($c)) {
                  $array[$i] = $c * 256;
                  $i++;
               }
            }
         }
         if ($p == 'T') {
            $this->SetTextColor($array[0], $array[1], $array[2]);
         } elseif ($p == 'D') {
            $this->SetDrawColor($array[0], $array[1], $array[2]);
         } elseif ($p == 'F') {
            $this->SetFillColor($array[0], $array[1], $array[2]);
         }
      }
   }
   static private $font_def = '';

   public function resetFont($font_family, $font_style, $font_size)
   {
      if (self::$font_def != $font_family . '-' . $font_style . '-' . $font_size) {
         self::$font_def = $font_family . '-' . $font_style . '-' . $font_size;
         $this->SetFont($font_family, $font_style, $font_size);
      }
   }

   public function resetStaticData()
   {
      self::$font_def = '';
      self::$options = array('F' => '', 'T' => '', 'D' => '');
   }
   /***********************************************************************
    *
    * Based on FPDF method SetFont
    *
    ************************************************************************/

   private function &FontData($family, $style, $size)
   {
      if ($family == '')
         $family = $this->FontFamily;
      else
         $family = strtolower($family);
      $style = strtoupper($style);
      if (strpos($style, 'U') !== false) {
         $this->underline = true;
         $style = str_replace('U', '', $style);
      }
      if ($style == 'IB')
         $style = 'BI';
      $fontkey = $family . $style;
      if (!isset($this->fonts[$fontkey])) {
         if ($family == 'arial')
            $family = 'helvetica';
         if (in_array($family, $this->CoreFonts)) {
            if ($family == 'symbol' || $family == 'zapfdingbats')
               $style = '';
            $fontkey = $family . $style;
            if (!isset($this->fonts[$fontkey]))
               $this->AddFont($family, $style);
         } else
            $this->Error('Undefined font: ' . $family . ' ' . $style);
      }
      $result['FontSize'] = $size / $this->k;
      $result['CurrentFont'] = &$this->fonts[$fontkey];
      return $result;
   }


   private function setLines(&$fstring, $p, $q)
   {
      $parced_str = &$fstring->parced_str;
      $lines = &$fstring->lines;
      $linesmap = &$fstring->linesmap;
      $cfty = $fstring->get_current_style($p);
      $ffs = $cfty['font-family'] . $cfty['style'];
      if (!isset($fstring->used_fonts[$ffs])) {
         $fstring->used_fonts[$ffs] = &$this->FontData($cfty['font-family'], $cfty['style'], $cfty['font-size']);
      }
      $cw = &$fstring->used_fonts[$ffs]['CurrentFont']['cw'];
      $wmax = $fstring->width * 1000 * $this->k;
      $j = count($lines) - 1;
      $k = strlen($lines[$j]);
      if (!isset($linesmap[$j][0])) {
         $linesmap[$j] = array($p, $p, 0);
      }
      $sl = $cw[' '] * $cfty['font-size'];
      $x = $a = $linesmap[$j][2];
      if ($k > 0) {
         $x += $sl;
         $lines[$j] .= ' ';
         $linesmap[$j][2] += $sl;
      }
      $u = $p;
      $t = '';
      $l = $p + $q;
      $ftmp = '';
      for ($i = $p; $i < $l; $i++) {
         if ($ftmp != $ffs) {
            $cfty = $fstring->get_current_style($i);
            $ffs = $cfty['font-family'] . $cfty['style'];
            if (!isset($fstring->used_fonts[$ffs])) {
               $fstring->used_fonts[$ffs] = &$this->FontData($cfty['font-family'], $cfty['style'], $cfty['font-size']);
            }
            $cw = &$fstring->used_fonts[$ffs]['CurrentFont']['cw'];
            $ftmp = $ffs;
         }
         $x += $cw[$parced_str[$i]] * $cfty['font-size'];
         if ($x > $wmax) {
            if ($a > 0) {
               $t = substr($parced_str, $p, $i - $p);
               $lines[$j] = substr($lines[$j], 0, $k);
               $linesmap[$j][1] = $p - 1;
               $linesmap[$j][2] = $a;
               $x -= ($a + $sl);
               $a = 0;
               $u = $p;
            } else {
               $x = $cw[$parced_str[$i]] * $cfty['font-size'];
               $t = '';
               $u = $i;
            }
            $j++;
            $lines[$j] = $t;
            $linesmap[$j] = array();
            $linesmap[$j][0] = $u;
            $linesmap[$j][2] = 0;
         }
         $lines[$j] .= $parced_str[$i];
         $linesmap[$j][1] = $i;
         $linesmap[$j][2] = $x;
      }
      return;
   }

   public function &extMultiCell($font_family, $font_style, $font_size, $font_color, $w, $txt)
   {
      $result = array();
      if ($w == 0) {
         return $result;
      }
      $this->current_font = array('font-family' => $font_family, 'style' => $font_style, 'font-size' => $font_size, 'font-color' => $font_color);
      $fstring = new FormatedString($txt, $w, $this->current_font);
      $word = '';
      $p = 0;
      $i = 0;
      $n = strlen($fstring->parced_str);
      while ($i < $n) {
         $word .= $fstring->parced_str[$i];
         if ($fstring->parced_str[$i] == "\n" || $fstring->parced_str[$i] == ' ' || $i == $n - 1) {
            $word = trim($word);
            $this->setLines($fstring, $p, strlen($word));
            $p = $i + 1;
            $word = '';
            if ($fstring->parced_str[$i] == "\n" && $i < $n - 1) {
               $z = 0;
               $j = count($fstring->lines);
               $fstring->lines[$j] = '';
               $fstring->linesmap[$j] = array();
            }
         }
         $i++;
      }
      if ($n == 0) {
         return $result;
      }
      $n = count($fstring->lines);
      for ($i = 0; $i < $n; $i++) {
         $result[$i] = $fstring->break_by_style($i);
      }
      return $result;
   }

   private function GetMixStringWidth($line)
   {
      $w = 0;
      foreach ($line['chunks'] as $i => $chunk) {
         $t = 0;
         $cf = &$this->FontData($line['style'][$i]['font-family'], $line['style'][$i]['style'], $line['style'][$i]['font-size']);
         $cw = &$cf['CurrentFont']['cw'];
         $s = implode('', explode(' ', $chunk));
         $l = strlen($s);
         for ($j = 0; $j < $l; $j++) {
            $t += $cw[$s[$j]];
         }
         $w += $t * $line['style'][$i]['font-size'];
      }
      return $w;
   }

   public function CellBlock($w, $lh, &$lines, $align = 'J')
   {
      if ($w == 0) {
         return;
      }
      $ctmp = '';
      $ftmp = '';
      foreach ($lines as $i => $line) {
         $k = $this->k;
         if ($this->y + $lh * $line['height'] > $this->PageBreakTrigger) {
            break;
         }
         $dx = 0;
         $dw = 0;
         if ($line['width'] != 0) {
            if ($align == 'R') {
               $dx = $w - $line['width'] / ($this->k * 1000);
            } elseif ($align == 'C') {
               $dx = ($w - $line['width'] / ($this->k * 1000)) / 2;
            }
            if ($align == 'J') {
               $tmp = explode(' ', implode('', $line['chunks']));
               $ns = count($tmp);
               if ($ns > 1) {
                  $sx = implode('', $tmp);
                  $delta = $this->GetMixStringWidth($line) / ($this->k * 1000);
                  $dw = ($w - $delta) * (1 / ($ns - 1));
               }
            }
         }
         $xx = $this->x + $dx;
         foreach ($line['chunks'] as $tj => $txt) {
            $this->resetFont($line['style'][$tj]['font-family'], $line['style'][$tj]['style'], $line['style'][$tj]['font-size']);
            $this->resetColor($line['style'][$tj]['font-color'], 'T');
            $y = $this->y + 0.5 * $lh * $line['height'] + 0.3 * $line['height'] / $this->k;
            if ($dw) {
               $tmp = explode(' ', $txt);
               foreach ($tmp as $e => $tt) {
                  if ($e > 0) {
                     $xx += $dw;
                     if ($tt == '') {
                        continue;
                     }
                  }
                  $this->Text($xx, $y, $tt);
                  if ($line['style'][$tj]['href']) {
                     $yr = $this->y + 0.5 * ($lh * $line['height'] - $line['height'] / $this->k);
                     $this->Link($xx, $yr, $this->GetStringWidth($txt), $line['height'] / $this->k, $line['style'][$tj]['href']);
                  }
                  $xx += $this->GetStringWidth($tt);
               }
            } else {
               $this->Text($xx, $y, $txt);
               if ($line['style'][$tj]['href']) {
                  $yr = $this->y + 0.5 * ($lh * $line['height'] - $line['height'] / $this->k);
                  $this->Link($xx, $yr, $this->GetStringWidth($txt), $line['height'] / $this->k, $line['style'][$tj]['href']);
               }
               $xx += $this->GetStringWidth($txt);
            }
         }
         unset($lines[$i]);
         $this->y += $lh * $line['height'];
      }
   }

   //////////////////////////////////////////////////////////////

   function SetProtection($permissions=array(), $user_pass='', $owner_pass=null)
    {
        $options = array('print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32 );
        $protection = 192;
        foreach($permissions as $permission)
        {
            if (!isset($options[$permission]))
                $this->Error('Incorrect permission: '.$permission);
            $protection += $options[$permission];
        }

        if ($owner_pass === null)
            $owner_pass = uniqid(rand());
        $this->encrypted = true;
        $this->padding = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08".
                        "\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";
        $this->_generateencryptionkey($user_pass, $owner_pass, $protection);
    }

    function _putstream($s)
    {
        if ($this->encrypted)
            $s = $this->RC4($this->_objectkey($this->n), $s);
        parent::_putstream($s);
    }

    function _textstring($s)
    {
        if (!$this->_isascii($s))
            $s = $this->_UTF8toUTF16($s);
        if ($this->encrypted)
            $s = $this->RC4($this->_objectkey($this->n), $s);
        return '('.$this->_escape($s).')';
    }

    /**
    * Compute key depending on object number where the encrypted data is stored
    */
    function _objectkey($n)
    {
        return substr($this->_md5_16($this->encryption_key.pack('VXxx',$n)),0,10);
    }

    function _putresources()
    {
        parent::_putresources();
        if ($this->encrypted) {
            $this->_newobj();
            $this->enc_obj_id = $this->n;
            $this->_put('<<');
            $this->_putencryption();
            $this->_put('>>');
            $this->_put('endobj');
        }
    }

    function _putencryption()
    {
        $this->_put('/Filter /Standard');
        $this->_put('/V 1');
        $this->_put('/R 2');
        $this->_put('/O ('.$this->_escape($this->Ovalue).')');
        $this->_put('/U ('.$this->_escape($this->Uvalue).')');
        $this->_put('/P '.$this->Pvalue);
    }

    function _puttrailer()
    {
        parent::_puttrailer();
        if ($this->encrypted) {
            $this->_put('/Encrypt '.$this->enc_obj_id.' 0 R');
            $this->_put('/ID [()()]');
        }
    }

    /**
    * Get MD5 as binary string
    */
    function _md5_16($string)
    {
        return pack('H*',md5($string));
    }

    /**
    * Compute O value
    */
    function _Ovalue($user_pass, $owner_pass)
    {
        $tmp = $this->_md5_16($owner_pass);
        $owner_RC4_key = substr($tmp,0,5);
        return $this->RC4($owner_RC4_key, $user_pass);
    }

    /**
    * Compute U value
    */
    function _Uvalue()
    {
        return $this->RC4($this->encryption_key, $this->padding);
    }

    /**
    * Compute encryption key
    */
    function _generateencryptionkey($user_pass, $owner_pass, $protection)
    {
        // Pad passwords
        $user_pass = substr($user_pass.$this->padding,0,32);
        $owner_pass = substr($owner_pass.$this->padding,0,32);

        // Compute O value
        $this->Ovalue = $this->_Ovalue($user_pass,$owner_pass);
        // Compute encyption key
        $tmp = $this->_md5_16($user_pass.$this->Ovalue.chr($protection)."\xFF\xFF\xFF");
        $this->encryption_key = substr($tmp,0,5);
        // Compute U value
        $this->Uvalue = $this->_Uvalue();
        // Compute P value
        $this->Pvalue = -(($protection^255)+1);
    }

    protected function RC4($key, $data)
    {
        static $last_key, $last_state;

        if($key != $last_key)
        {
            $k = str_repeat($key, round(256 / strlen($key)+1));

            $state = range(0, 255);
            $j = 0;
            for ($i=0; $i<256; $i++){
                $t = $state[$i];
                $j = ($j + $t + ord($k[$i])) % 256;
                $state[$i] = $state[$j];
                $state[$j] = $t;
            }
            $last_key = $key;
            $last_state = $state;
        }
        else {
            $state = $last_state;
        }

        $len = strlen($data);
        $a = 0;
        $b = 0;
        $out = '';

        for ($i=0; $i<$len; $i++)
        {
            $a = ($a+1) % 256;
            $t = $state[$a];
            $b = ($b+$t) % 256;
            $state[$a] = $state[$b];
            $state[$b] = $t;
            $k = $state[($state[$a]+$state[$b]) % 256];
            $out .= chr(ord($data[$i]) ^ $k);
        }
        return $out;
    }

}
