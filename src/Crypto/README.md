# Woodpecker Cryption Class

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Awesome](https://awesome.re/badge.svg)](https://awesome.re)

The `Cryption` class provides a robust and easy-to-use interface for encrypting and decrypting data in PHP. It supports multiple encryption algorithms, automatic initialization vector (IV) handling, and a streamlined approach to securing your data.

## Overview

This class is designed to simplify the process of encrypting and decrypting data using various symmetric encryption algorithms. It is particularly useful in scenarios where data security is paramount, such as protecting sensitive information in web applications or securing data during transmission.

### Features

-   **Multiple Encryption Algorithms:** Supports a variety of encryption algorithms including:
    -   `aes-256-cbc`
    -   `des-ede3-cbc`
    -   `bf-cbc`
    -   `rc4`
    -   `camellia-256-cbc`
-   **Automatic IV Handling:** Automatically generates and manages initialization vectors (IVs) for block ciphers, enhancing security.
-   **Simple Interface:** Easy-to-use `encrypt` and `decrypt` methods for quick implementation.
-   **Namespaced:** Organized under the `Woodpecker` namespace to prevent naming conflicts.
-   **Exception Handling:** Integrates with the project's exception handling for robust error management.

## Installation

To use the `Cryption` class, simply include it in your project:

```php
use Woodpecker\Cryption;

$data = "Sensitive information to protect";
$key = "SuperSecretKey123";

// Encrypt the data
$encryptedData = Cryption::encrypt($data, $key);
echo "Encrypted: " . $encryptedData . "\n";

// Decrypt the data
$decryptedData = Cryption::decrypt($encryptedData, $key);
echo "Decrypted: " . $decryptedData . "\n";

