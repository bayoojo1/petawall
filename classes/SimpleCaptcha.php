<?php
class SimpleCaptcha {
    private $expiry_time = 300;
    
    public function generateMathQuestion() {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $operator = rand(0, 1) ? '+' : '-';
        
        if ($operator == '+') {
            $answer = $num1 + $num2;
            $question = "What is {$num1} + {$num2}?";
        } else {
            $answer = $num1 - $num2;
            $question = "What is {$num1} - {$num2}?";
        }
        
        $_SESSION['captcha_answer'] = (string)$answer;
        $_SESSION['captcha_time'] = time();
        
        return $question;
    }
    
    public function validate($user_answer) {
        if (!isset($_SESSION['captcha_answer']) || !isset($_SESSION['captcha_time'])) {
            return false;
        }
        
        if (time() - $_SESSION['captcha_time'] > $this->expiry_time) {
            $this->clear();
            return false;
        }
        
        $is_valid = trim($user_answer) == $_SESSION['captcha_answer'];
        $this->clear();
        
        return $is_valid;
    }
    
    public function clear() {
        unset($_SESSION['captcha_answer']);
        unset($_SESSION['captcha_time']);
    }
}
?>