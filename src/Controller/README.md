
# Woodpecker Controller Class

## Overview

The `Controller` class serves as a base controller for applications using the Woodpecker framework. It encapsulates common functionalities that can be shared across various controllers, specifically focused on handling HTTP responses and redirects.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
  - [Response Method](#response-method)
  - [Redirect Method](#redirect-method)

## Features

- **Response Handling:** Provides a method to create a response object.
- **Redirection:** Enables easy redirection to other routes or URLs.

## Installation

To use the `Controller` class, include it in your project and ensure that you have the required namespaces set up. The class requires the presence of `Response` and `Redirect` classes from the `Woodpecker\Support` namespace.


## Usage

Creating a Controller
To create a new controller that extends the Controller class, follow the example below:

```php
namespace App\Controllers;

use Woodpecker\Controller;

class MyController extends Controller {
   public function handleRequest() {
       // Your logic here
   }
}
```

### Response Method
The response() method allows you to create a new instance of the Response class. This method is protected and can be accessed from any subclass of Controller.

```php
public function handleRequest() {
    $response = $this->response();
    return $response->setContent('Hello, World!')->send();
}
```

### Redirect Method
The redirect() method creates a new instance of the Redirect class, which can be used to redirect users to different URLs.

```php
public function redirectToHome() {
    return $this->redirect()->to('/home');
}
```
