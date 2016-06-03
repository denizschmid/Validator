# Validator
Validator provides functions to validate data from user input and collects the errors from all failed validation for feedback.
The Validator accepts either objects or array as data input. Before a validation can be performed the validation rules must be defined.

To create a Validator just use the default constructer:
```php
  use Dansnet\Validator;
  $validator = new Validator();
```

After the validator was successfully created it can take several rules. The **addRule** method accepts 2 argument. The first argument informs the validator which member or key from the data input should be tested. If the input is an object, the validation class looks for a corresponding member. Otherwise it looks for the value of the defined key. The second argument defines the proper validation rule as a constant. Is everything configured you can start the validation with the appropriate method.
```php
  $validator->addRule("stringmember", Validator::VALIDATE_STRING);
  $validator->validate();
```

##Validation constants

| Constant                       | Effect                             |
| ------------------------------ | ---------------------------------- |
| Validator::VALIDATE_STRING     | Checks if the input is a string    |
| Validator::VALIDATE_INTEGER    | Checks if the input is an integer  |
| Validator::VALIDATE_BOOLEAN    | Checks if the input is a boolean   |
| Validator::VALIDATE_FLOAT      | Checks if the input is a float     |
| Validator::VALIDATE_EMAIL      | Checks if the input is an E-Mail   |
| Validator::VALIDATE_URL        | Checks if the input is an URL      |
| Validator::VALIDATE_DATETIME   | Checks if the input is a date/time |
| Validator::VALIDATE_NOT_EMPTY  | Checks if the input is not empty   |
| Validator::VALIDATE_EXPRESSION | Checks if the expression is valid  |

