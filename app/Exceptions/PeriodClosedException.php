<?php

namespace App\Exceptions;

use Exception;

class PeriodClosedException extends Exception
{
    protected $message = 'The selected period is closed for target entry.';
    
    public function __construct($year = null, $month = null, $message = null)
    {
        if ($year && $month) {
            $this->message = "Period {$year}-{$month} is closed for target entry.";
        }
        
        if ($message) {
            $this->message = $message;
        }
        
        parent::__construct($this->message);
    }
}
