<?php
class MY_Form_validation extends CI_Form_validation {
    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function alpha_space($str) {
        return (bool) preg_match('/^[a-zA-Z\s]*$/', $str);
    }
}
