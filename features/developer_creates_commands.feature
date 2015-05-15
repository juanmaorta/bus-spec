Feature: Developer asks for command creation
  As a Developer
  I want to automate creating commands and handlers
  In order to avoid repetitive tasks and interruptions in development flow

  Scenario: Generating a class
    Given I run phpspec busify for class "CodeGeneration/Sample/Test" with params "id name"
    When I answer "y" to the prompt
    And I answer "y" to the prompt
    And I answer "y" to the prompt
    And I answer "y" to the prompt
    Then having phpspec program ended
    And I should be prompted with:
    """
    Do you want to generate command with name "CodeGeneration\Sample\Test" and params "$id, $name"? [Y/n]
    """
    And I should be prompted with:
    """
    Do you want to generate handler with name "CodeGeneration\Sample\TestHandler"? [Y/n]
    """
    And I should see "Command CodeGeneration\Sample\Test created in "
    And I should see "Handler CodeGeneration\Sample\TestHandler created in "
    And I should see "Specification for CodeGeneration\Sample\Test created in "
    And I should see "Specification for CodeGeneration\Sample\TestHandler created in "
    And a new class should be generated in the "src/CodeGeneration/Sample/Test.php":
    """
<?php

    namespace CodeGeneration\Sample;

    final class Test
    {
        private $id;

        private $name;

        public function __construct($id, $name)
        {
            $this->id = $id;
            $this->name = $name;
        }

        public function id()
        {
            return $this->id;
        }

        public function name()
        {
            return $this->name;
        }
    }

    """
    And a new class should be generated in the "src/CodeGeneration/Sample/TestHandler.php":
    """
<?php

    namespace CodeGeneration\Sample;

    final class TestHandler
    {
        public function handle(Test $command)
        {
            // TODO write your own implementation
        }
    }

    """
    And a new class should be generated in the "spec/CodeGeneration/Sample/TestSpec.php":
    """
<?php

  namespace spec\CodeGeneration\Sample;

  use PhpSpec\ObjectBehavior;
  use Prophecy\Argument;
  use PhpSpec\Exception\Example\PendingException;

  class TestSpec extends ObjectBehavior
  {
        public function it_is_initializable()
        {
            $this->shouldHaveType('CodeGeneration\Sample\Test');
        }

        public function it_should_retrieve_id_getter_value()
        {
            throw new PendingException('pending implementation');
            $expectation = 'put value here';
            $this->id()->shouldBeLike($expectation);
        }

        public function it_should_retrieve_name_getter_value()
        {
            throw new PendingException('pending implementation');
            $expectation = 'put value here';
            $this->name()->shouldBeLike($expectation);
        }
  }

    """
    And a new class should be generated in the "spec/CodeGeneration/Sample/TestHandlerSpec.php":
    """
<?php

  namespace spec\CodeGeneration\Sample;

  use PhpSpec\ObjectBehavior;
  use Prophecy\Argument;
  use PhpSpec\Exception\Example\PendingException;
  use CodeGeneration\Sample\Test;

  class TestHandlerSpec extends ObjectBehavior
  {
        public function it_is_initializable()
        {
            $this->shouldHaveType('CodeGeneration\Sample\TestHandler');
        }

        public function it_should_handle()
        {
            throw new PendingException('Pending implementation');
            $command = new Test($id, $name);
            $this->handle($command);
        }
  }

    """

  Scenario: Calling the command but aborting the creation
    Given I run phpspec busify for class "CodeGeneration/Sample/Test" with params "id name"
    When I answer "n" to the prompt
    Then having phpspec program ended
    Then there is no file "src/CodeGeneration/Sample/Test.php"
    And there is no file "src/CodeGeneration/Sample/TestHandler.php"
    And there is no file "spec/CodeGeneration/Sample/TestSpec.php"
    And there is no file "spec/CodeGeneration/Sample/TestHandlerSpec.php"

  Scenario: Generating a class skipping specifications
    Given I run phpspec busify for class "CodeGeneration/Sample/Test" with params "id email"
    When I answer "y" to the prompt
    And I answer "y" to the prompt
    And I answer "n" to the prompt
    And I answer "n" to the prompt
    Then having phpspec program ended
    And I should be prompted with:
    """
    Do you want to generate command with name "CodeGeneration\Sample\Test" and params "$id, $email"? [Y/n]
    """
    And a new class should be generated in the "src/CodeGeneration/Sample/Test.php":
    """
<?php

    namespace CodeGeneration\Sample;

    final class Test
    {
        private $id;

        private $email;

        public function __construct($id, $email)
        {
            $this->id = $id;
            $this->email = $email;
        }

        public function id()
        {
            return $this->id;
        }

        public function email()
        {
            return $this->email;
        }
    }

    """
    And a new class should be generated in the "src/CodeGeneration/Sample/TestHandler.php":
    """
<?php

    namespace CodeGeneration\Sample;

    final class TestHandler
    {
        public function handle(Test $command)
        {
            // TODO write your own implementation
        }
    }

    """
    And there is no file "spec/CodeGeneration/Sample/TestSpec.php"
    And there is no file "spec/CodeGeneration/Sample/TestHandlerSpec.php"

  Scenario: Calling the command but aborting the creation on already existant files
    Given I already have a file in "src/CodeGeneration/Sample/Test.php"
    And I run phpspec busify for class "CodeGeneration/Sample/Test" with params "id name"
    And I answer "y" to the prompt
    When having phpspec program ended
    Then I should be prompted with:
    """
    Do you want to generate command with name "CodeGeneration\Sample\Test" and params "$id, $name"? [Y/n]
    """
    And I should be prompted with:
    """
    File "Test.php" already exists. Overwrite? [y/N]
    """