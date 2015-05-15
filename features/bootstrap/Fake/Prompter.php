<?php

namespace Fake;

use PhpSpec\Console\Prompter as PrompterInterface;

class Prompter implements PrompterInterface
{
    private $answers = [];
    private $hasBeenAsked = false;
    private $question;
    private $prompts = [];

    public function setAnswer($answer)
    {
        $this->answers[] = $answer;
    }

    public function askConfirmation($question, $default = true)
    {
        $this->hasBeenAsked = true;
        $this->question = $question;
        $this->prompts[] = preg_replace('/\s+/', ' ', trim(strip_tags($this->question)));

        $answer = current($this->answers);
        next($this->answers);

        return $answer;
    }

    public function hasBeenAsked($question = null)
    {
        if (!$question) {
            return $this->hasBeenAsked;
        }

        return $this->hasBeenAsked
            && $this->getCleanAnswer() == preg_replace('/\s+/', ' ', $question) ;
    }

    public function getPrompt()
    {
        $prompt = current($this->prompts);
        next($this->prompts);

        return $prompt;
    }

    public function getCleanAnswer()
    {
        return preg_replace('/\s+/', ' ', trim(strip_tags($this->question)));
    }
}
