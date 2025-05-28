<?php

namespace Ximdex\StructuredData\Rules;

use Ximdex\StructuredData\Models\Schema;
use Illuminate\Support\Carbon;
use Ximdex\StructuredData\Models\AvailableType;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValueInAvailableTypeRule extends InAvailableTypeRule implements ValidationRule
{
    const TIME_FORMAT = 'h:i:s';
    const DATE_FORMAT = 'd-m-Y';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // First run parent logic
        parent::validate($attribute, $value, function (string $message) use ($fail) {
            $fail($message);
        });

        $value = $this->value;

        if ($this->availableType->type === Schema::THING_TYPE) {
            if (!$this->supportMultiValidation) {
                $fail("The :attribute must be a valid item for type '{$this->availableType->type}'.");
            }
            return;
        }

        foreach ($value as &$val) {
            if (is_array($val)) {
                $val = $val['value'] ?? null;
            }

            switch ($this->availableType->type) {
                case AvailableType::BOOLEAN_TYPE:
                    if (!in_array($val, ['true', 'false', '1', '0'], true)) {
                        $fail("The :attribute must be a valid boolean.");
                    }
                    break;

                case AvailableType::NUMBER_TYPE:
                    if (!is_numeric($val)) {
                        $fail("The :attribute must be a valid number.");
                    }
                    break;

                case AvailableType::DATE_TYPE:
                    if (!$this->checkDateTimesFormat(self::DATE_FORMAT, $val)) {
                        $fail("The :attribute must be a valid date (format: " . self::DATE_FORMAT . ").");
                    }
                    break;

                case AvailableType::TIME_TYPE:
                    if (!$this->checkDateTimesFormat(self::TIME_FORMAT, $val)) {
                        $fail("The :attribute must be a valid time (format: " . self::TIME_FORMAT . ").");
                    }
                    break;

                case AvailableType::DATETIME_TYPE:
                    if (!$this->checkDateTimesFormat(self::DATE_FORMAT . ' ' . self::TIME_FORMAT, $val)) {
                        $fail("The :attribute must be a valid datetime (format: " . self::DATE_FORMAT . ' ' . self::TIME_FORMAT . ").");
                    }
                    break;

                default:
                    break;
            }
        }
    }

    private function checkDateTimesFormat(string $format, string $val): bool
    {
        try {
            $parsed = Carbon::createFromFormat($format, $val);
            return $parsed !== false;
        } catch (\Exception) {
            return false;
        }
    }
}

// class ValueInAvailableTypeRule extends InAvailableTypeRule
// {
//     const TIME_FORMAT = 'h:i:s';
    
//     CONST DATE_FORMAT = 'd-m-Y';
    
//     /**
//      * Check if the given value is supported in the property available type
//      * 
//      * {@inheritDoc}
//      * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::passes()
//      */
//     public function passes($attribute, $value)
//     {
//         if (parent::passes($attribute, $value) === false) {
//             return false;
//         }
//         $value = $this->value;
//         if ($this->availableType->type == Schema::THING_TYPE) {
            
//             // Type only support an item
//             return $this->supportMultiValidation;
//         }
//         $result = true;
//         foreach ($value as & $val) {
            
//             // If value contains the type, get only the value given
//             if (is_array($val)) {
//                 $val = $val['value'];
//             }
//             switch ($this->availableType->type) {
                
//                 // Check if value is a valid boolean type
//                 case AvailableType::BOOLEAN_TYPE:
//                     if (! in_array($val, ['true', 'false', '1', '0'])) {
//                         $result = false;
//                     }
//                     break;
    
//                 // Check if value is a valid number type
//                 case AvailableType::NUMBER_TYPE:
//                     if (! is_numeric($val)) {
//                         $result = false;
//                     }
//                     break;
                
//                 // Check if value is a valid date type
//                 case AvailableType::DATE_TYPE:
//                     $result = $this->checkDateTimesFormat(self::DATE_FORMAT, $val);
//                     break;
                
//                 // Check if value is a valid time type
//                 case AvailableType::TIME_TYPE:
//                     $result = $this->checkDateTimesFormat(self::TIME_FORMAT, $val);
//                     break;
                
//                 // Check if value is a valid date time type
//                 case AvailableType::DATETIME_TYPE:
//                     $result = $this->checkDateTimesFormat(self::DATE_FORMAT . ' ' . self::TIME_FORMAT, $val);
//                     break;
                
//                 // Another types
//                 default:
//                     break;
//             }
//         }
//         return $result;
//     }
    
//     /**
//      * {@inheritDoc}
//      * @see \Ximdex\StructuredData\Rules\InAvailableTypeRule::message()
//      */
//     public function message()
//     {
//         if (! $this->availableType) {
//             return parent::message();
//         }
//         return "The :attribute must be a valid type ({$this->availableType->type}) for {$this->availableType->propertySchema->label} property";
//     }
    
//     private function checkDateTimesFormat(string $format, string $val): bool
//     {
//         try {
//             $val = Carbon::createFromFormat($format, $val);
//             if ($val === false) {
//                 return false;
//             }
//         } catch (\Exception $e) {
//             return false;
//         }
//         return true;
//     }
// }
