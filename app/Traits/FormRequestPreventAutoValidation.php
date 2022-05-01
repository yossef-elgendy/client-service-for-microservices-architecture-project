<?php
namespace App\Traits;

trait FormRequestPreventAutoValidation
{
  public function validateResolved()
  {
    $this->prepareForValidation();

    if (!$this->passesAuthorization()) {
      $this->failedAuthorization();
    }

    // $instance = $this->getValidatorInstance();

    // if ($instance->fails()) {
    // $this->failedValidation($instance);
    // }

    $this->passedValidation();
  }
}
