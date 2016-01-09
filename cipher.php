<?php
// each client gets their own passphrase
static $passphrases = array('PICA9' => 's^&6^#$ecretd pasJ3JGEIsphrxxxX1XXa26se', // pica9 (vwsp and vwspcan)
'IRIS' => 's^&6^#$ecretd pasJ3JGEIsphrxxxX1XXa26se', // pica9 (vwsp and vwspcan)
'CLICKTRACKER' => 's^&6^#$ecfrfefGEIsnpux8--$X2se', // internal click tracker for vwsp
'WIN' => 's^#$ecwretd pasJ3sphwwwwrxxxX1*(0XXa26se');// winchester

class Cipher {
    private $securekey, $iv;
    function __construct($textkey) {
        $this -> securekey = hash('sha256', $textkey, TRUE);
        $this -> iv = mcrypt_create_iv(32);
    }

    function encrypt($input) {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this -> securekey, $input, MCRYPT_MODE_ECB, $this -> iv));
    }

    function decrypt($input) {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this -> securekey, base64_decode($input), MCRYPT_MODE_ECB, $this -> iv));
    }

}
?>