<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Fake\Prompter;
use Fake\ReRunner;
use Matcher\ApplicationOutputMatcher;
use Matcher\ExitStatusMatcher;
use Matcher\ValidJUnitXmlMatcher;
use PhpSpec\Console\Application;
use PhpSpec\Matcher\MatchersProviderInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Tester\ApplicationTester;
use Ulabox\PhpSpec\Extension\BusySpec\Extension;

/**
 * Defines application features from the specific context.
 */
class ApplicationContext implements Context, MatchersProviderInterface
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var integer
     */
    private $lastExitCode;

    /**
     * @var ApplicationTester
     */
    private $tester;

    /**
     * @var Prompter
     */
    private $prompter;

    /**
     * @var ReRunner
     */
    private $reRunner;

    private $arguments;

    /**
     * @beforeScenario
     */
    public function setupApplication()
    {
        $this->application = new Application('2.1-dev');
        $this->application->setAutoExit(false);

        $extension = new Extension();
        $extension->load($this->application->getContainer());

        $this->tester = new ApplicationTester($this->application);

        $this->setupReRunner();
        $this->setupPrompter();
    }

    private function setupPrompter()
    {
        $this->prompter = new Prompter();

        $this->application->getContainer()->set('console.prompter', $this->prompter);
    }

    private function setupReRunner()
    {
        $this->reRunner = new ReRunner;
        $this->application->getContainer()->set('process.rerunner.platformspecific', $this->reRunner);
    }

    /**
     * @param string $option
     * @param array $arguments
     */
    private function addOptionToArguments($option, array &$arguments)
    {
        if ($option) {
            if (preg_match('/(?P<option>[a-z-]+)=(?P<value>[a-z.]+)/', $option, $matches)) {
                $arguments[$matches['option']] = $matches['value'];
            } else {
                $arguments['--' . trim($option, '"')] = true;
            }
        }
    }

    /**
     * @Given /^I run phpspec (?P<command>.+) for class "(?P<class>[^"]+)" with params "(?P<params>[^"]*)"$/
     * @Given /^I run phpspec (?P<command>.+) for class "(?P<class>[^"]+)" with params "(?P<params>[^"]*) in (?P<quiet>.+) mode"$/
     */
    public function iRunPhpspec($command = 'busify', $class = null, $params = null, $quiet = false)
    {
        $arguments = array (
            'command' => $command
        );

        if ($class) {
            $arguments['class'] = strtr($class, '/', "\\");
        }

        if ($params) {
            $arguments['params'] = explode(" ", $params);
        }

        if ($quiet) {
            $this->addOptionToArguments('quiet', $arguments);
        }

        $this->arguments = $arguments;

        //$this->lastExitCode = $this->tester->run($this->arguments, array('interactive' => false));
    }

    /**
     * @Then having phpspec program ended
     */
    public function iWaitForPhpspecResult()
    {
        $this->lastExitCode = $this->tester->run($this->arguments, array('interactive' => true));
    }

    /**
     * @Then /^I answer "([^"]*)" to the prompt$/
     */
    public function iAnswerToThePrompt($answer)
    {
        $this->prompter->setAnswer($answer=='y');
    }

    /**
     * @Then the exit code should be :code
     */
    public function theExitCodeShouldBe($code)
    {
        expect($this->lastExitCode)->toBeLike($code);
    }

    /**
     * @Then I should be prompted with:
     */
    public function iShouldBePromptedWith(PyStringNode $question)
    {
        $prompt = $this->prompter->getPrompt();
        expect($prompt)->toBeLike((string)$question);
    }






    /**
     * @Then I should see :output
     * @Then I should see:
     */
    public function iShouldSee($output)
    {
        expect($this->tester)->toHaveOutput((string)$output);
    }

    /**
     * @Then I should be prompted for code generation
     */
    public function iShouldBePromptedForCodeGeneration()
    {
        expect($this->prompter)->toHaveBeenAsked();
    }

    /**
     * @Then I should not be prompted for code generation
     */
    public function iShouldNotBePromptedForCodeGeneration()
    {
        expect($this->prompter)->toNotHaveBeenAsked();
    }

    /**
     * @Then the suite should pass
     */
    public function theSuiteShouldPass()
    {
        expect($this->lastExitCode)->toBeLike(0);
    }

    /**
     * @Then :number example(s) should have been skipped
     */
    public function exampleShouldHaveBeenSkipped($number)
    {
        expect($this->tester)->toHaveOutput("($number skipped)");
    }

    /**
     * @Then :number example(s) should have been run
     */
    public function examplesShouldHaveBeenRun($number)
    {
        expect($this->tester)->toHaveOutput("$number examples");
    }

    /**
     * Custom matchers
     *
     * @return array
     */
    public function getMatchers()
    {
        return array(
            new ApplicationOutputMatcher()
        );
    }
}
