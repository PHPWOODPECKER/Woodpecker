<?php
namespace Woodpecker;

/**
 * Improved Cryption Class for Encryption and Decryption
 * 
 * This class provides methods to encrypt and decrypt data using AES-256-GCM.
 * It ensures both confidentiality and integrity of the data.
 */
class Cryption {

  private const ALGORITHM = 'aes-256-gcm';
  private const TAG_LENGTH = 16;

  /**
   * Generate a random initialization vector (IV) for AES-256-GCM.
   *
   * @return string The generated IV.
   */
  private static function generateIv(): string 
  {
    return openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::ALGORITHM));
  }

  /**
   * Encrypts the given data using AES-256-GCM.
   *
   * @param string $data The plaintext data to encrypt.
   * @param string $key The encryption key.
   * @return string The encrypted data in the format: base64(iv + tag + ciphertext).
   */
  public static function encrypt(string $data, string $key): string 
  {
    $iv = self::generateIv();
    $tag = '';

    $ciphertext = openssl_encrypt(
      $data,
      self::ALGORITHM,
      $key,
      OPENSSL_RAW_DATA,
      $iv,
      $tag,
      '',
      self::TAG_LENGTH
    );

    return base64_encode($iv . $tag . $ciphertext);
  }

  /**
   * Decrypts the given encrypted data using AES-256-GCM.
   *
   * @param string $encryptedData The encrypted data in the format: base64(iv + tag + ciphertext).
   * @param string $key The decryption key.
   * @return string The decrypted (plaintext) data.
   * @throws \Exception If decryption fails.
   */
  public static function decrypt(string $encryptedData, string $key): string 
  {
    $decoded = base64_decode($encryptedData);
    $ivLength = openssl_cipher_iv_length(self::ALGORITHM);
    $iv = substr($decoded, 0, $ivLength);
    $tag = substr($decoded, $ivLength, self::TAG_LENGTH);
    $ciphertext = substr($decoded, $ivLength + self::TAG_LENGTH);

    $plaintext = openssl_decrypt(
      $ciphertext,
      self::ALGORITHM,
      $key,
      OPENSSL_RAW_DATA,
      $iv,
      $tag
    );

    if ($plaintext === false) {
      throw new WPException('Decryption failed');
    }

    return $plaintext;
  }
}

