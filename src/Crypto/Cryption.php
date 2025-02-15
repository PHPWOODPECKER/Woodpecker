<?php
namespace Woodpecker;
// Import the namespace for handling exceptions and errors in the project
use Woodpecker\Execptions;
/**
 * ADW Class for Encryption and Decryption
 * 
 * This class provides methods to encrypt and decrypt data using multiple encryption algorithms.
 * It supports a variety of symmetric encryption methods, and it automatically handles 
 * initialization vectors (IVs) for block ciphers.
 */
class Cryption {

  private static $algorithms = [
        'aes-256-cbc',
        'des-ede3-cbc',
        'bf-cbc',
        'rc4',
        'camellia-256-cbc'
    ];

  /**
   * Generate an initialization vector (IV) for a given cipher.
   *
   * @param string $crypt The encryption algorithm to use.
   * @return string The generated IV.
   */
  private static function generateIv(string $crypt): string {
    $iv = openssl_cipher_iv_length($crypt);
    return $iv === 0 ? '' : openssl_random_pseudo_bytes($iv);
  }

  /**
   * Encrypts the given data using a series of algorithms.
   *
   * @param string $data The plaintext data to encrypt.
   * @param string $key The encryption key.
   * @return string The encrypted data.
   */
  public static function encrypt(string $data, string $key): string {
    $encryptedData = $data;
    
    foreach (self::$algorithms as $algorithm){
      $iv = self::generateIv($algorithm);
      $encryptedData = openssl_encrypt($encryptedData, $algorithm, $key, 0, $iv);
      
      if (in_array($algorithm, ['aes-256-cbc', 'des-ede3-cbc', 'bf-cbc', 'camellia-256-cbc'])) {
        $encryptedData = base64_encode($encryptedData . "::" . base64_encode($iv));
      }
    }
    return $encryptedData;
  }

  /**
   * Decrypts the given encrypted data using a series of algorithms.
   *
   * @param string $encryptedData The encrypted data to decrypt.
   * @param string $key The decryption key.
   * @return string The decrypted (plaintext) data.
   */
  public static function decrypt(string $encryptedData, string $key): string {
    $decryptedData = $encryptedData;
    
    foreach (array_reverse(self::$algorithms) as $algorithm){
      if (in_array($algorithm, ['aes-256-cbc', 'des-ede3-cbc', 'bf-cbc', 'camellia-256-cbc'])) {
        list($encryptedDataPart, $iv) = explode("::", base64_decode($decryptedData), 2);
        $iv = base64_decode($iv);
        $decryptedData = openssl_decrypt($encryptedDataPart, $algorithm, $key, 0, $iv);
      } else {
        $decryptedData = openssl_decrypt($decryptedData, $algorithm, $key);
      }
    }

    return $decryptedData;
  }
}