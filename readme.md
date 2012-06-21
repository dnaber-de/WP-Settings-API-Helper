# WP Settings API Helper

(Work in progress) A tiny framework to simply add some option fields using the settings API


## Examples
See WP_Settings_API_Helper_Test::init_settings() in ./example.php


### Custom Validation

You can use custom validation and sanitizing callbacks on each field. To handle both, validating and sanitizing in one callback, use the 'validation_callback' parameter and set the 'sanitizing_parameter' to NULL (or an empty string). Both callbacks should follow this sheme ```mixed func_name( $input[, $field ])``` where the returned value is the raw or sanitized input. The first parameter is the users input of this field, the second is an object ot Settings_API_Field class, representing the current field. Inside your callback the method ```$field->set_invalid([ $cause ])``` will set the field to an invalid status and, if set in the validation callback, triggers the calling of the sanitizing callback, if it exists. See »Validation sequence« for more information. The ```$cause``` parameter is used as key for the error messages array.

### Validation sequence

1. Check for ```'validation_callback'``` parameter. This function should return the plain or sanitized value and look like ```mixed func( $input[, &$field ] )```
2. If callback not exists, check for the 'pattern' parameter and matches the input against it with ```preg_match()```
3. If there's no pattern, check if the field is required and empty.
4. If so, or the pattern doesn't matches the value, the field is set to invalid and the appropiate error-message form the field-parameters is set.
5. If the field is set to invalid the ```'sanitize_callback'``` function is called, if exists. This should look like ```mixed func( $input[, &$field ] )```
6. Otherwise a probably existing default value is set if the input is invalid.
