<?php
namespace API\Error;

/**
 * This describes all possible errors generated when calling to the API
 *
 * author:    Yoel Monsalve
 * date:      2022-03-30
 * modified:  2022-03-30
 *
 */

class APIError extends \Exception {
  public function __construct($message, $code = 0, $description = '') {
    $this->message = $message;
    $this->code    = $code;
    $this->description = $description;
  }
  protected $description = "";
  public function __toString() {
    return "Error ({$this->code}): {$this->message}";
  }
  public function getDescription() {
    return $this->description;
  }
}

class DataError extends APIError {
  public function __construct($description = '') {
    parent::__construct("Error in data", 1, $description);
  }
}

class BadArgumentError extends APIError {
  public function __construct($description = '') {
    parent::__construct("Bad argument", 2, $description);
  }
}

class DatabaseError extends APIError {
  public function __construct($description = '') {
    parent::__construct("Database error. Failed at transacting with DB", 3, $description);
  }
}

class IdError extends APIError {
  public function __construct($description = '') {
    parent::__construct("Invalid ID", 4);
  }
}

class TypeError extends APIError {
  public function __construct($description = '') {
    parent::__construct("Incorrect type", 5, $description);
  }
}

class DataTypeError extends APIError {
  public function __construct($description = '') {
    parent::__construct("Incorrect type in data", 6, $description);
  }
}
