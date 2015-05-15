<?php

namespace Ulabox\PhpSpec\Extension\BusySpec\Command;

use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ulabox\PhpSpec\Extension\BusySpec\Generator\CommandGenerator;
use Ulabox\PhpSpec\Extension\BusySpec\Generator\CommandSpecGenerator;
use Ulabox\PhpSpec\Extension\BusySpec\Generator\HandlerGenerator;
use Ulabox\PhpSpec\Extension\BusySpec\Generator\HandlerSpecGenerator;

class BusifyCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('busify')
            ->setDefinition(array(
                new InputArgument('class', InputArgument::REQUIRED, 'Full class path of the command for creation'),
                new InputArgument('params', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'parameters to be tracked'),
            ))
            ->setDescription('Creates a command and their handler with the parameters')
            ->addOption('silent', null, InputOption::VALUE_NONE, 'Quiet mode, do not ask nothing, yes to all.')
            /*->setHelp(<<<EOF
The <info>%command.name%</info> command creates an example for a method:
  <info>php %command.full_name% ClassName MethodName</info>
Will generate an example in the ClassNameSpec.
EOF
            )*/
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getContainer();
        $container->configure();

        $commandClass = $input->getArgument('class');
        $params = $input->getArgument('params');
        $handlerClass = sprintf('%sHandler', $commandClass);

        if (!$this->confirmCommand($input, $commandClass, $params)) {
            return;
        }

        $this->createCommand($commandClass, $params);

        if (!$this->confirmHandler($input, $handlerClass)) {
            return;
        }

        $this->createHandler($commandClass, $handlerClass, $params);

        if (!$this->confirmCommandSpec($input, $commandClass)) {
            return;
        }

        $this->createCommandSpec($commandClass, $params);


        // confirm
        if (!$this->confirmHandlerSpec($input, $handlerClass)) {
            return;
        }

        $this->createHandlerSpec($commandClass, $handlerClass, $params);
    }

    private function render($class, $tag, $params)
    {
        $container = $this->getApplication()->getContainer();

        $resource = $container->get('locator.resource_manager')->createResource($class);
        $container->get('code_generator')->generate($resource, $tag, $params);
    }

    private function createCommand($commandClass, $params)
    {
        $this->render($commandClass, CommandGenerator::COMMAND_TAG, [
            'params' => $params
        ]);
    }

    private function createHandler($commandClass, $handlerClass, $params)
    {
        $this->render($handlerClass, HandlerGenerator::COMMAND_TAG, [
            'handles' => $commandClass,
            'params' => $params,
        ]);
    }

    private function createCommandSpec($commandClass, $params)
    {
        $this->render($commandClass, CommandSpecGenerator::COMMAND_TAG, [
            'params' => $params
        ]);
    }

    private function createHandlerSpec($commandClass, $handlerClass, $params)
    {
        $this->render($handlerClass, HandlerSpecGenerator::COMMAND_TAG, [
            'handles'     => $commandClass,
            'params'      => $params
        ]);
    }

    /**
     * @param InputInterface $input
     * @param $classname
     * @return bool
     */
    private function confirmCommand(InputInterface $input, $name, $params)
    {
        if ($input->getOption('silent')) {
            return true;
        }
        $question = sprintf('Do you want to generate command with name "%s" and params "$%s"?', $name, implode(', $', $params));
        $io = $this->getApplication()->getContainer()->get('console.io');
        return $io->askConfirmation($question, true);
    }

    private function confirmHandler(InputInterface $input, $handler)
    {
        if ($input->getOption('silent')) {
            return true;
        }

        $question = sprintf('Do you want to generate handler with name "%s"?', $handler);
        $io = $this->getApplication()->getContainer()->get('console.io');
        return $io->askConfirmation($question, true);
    }

    private function confirmCommandSpec(InputInterface $input, $name)
    {
        if ($input->getOption('silent')) {
            return true;
        }

        $question = sprintf('Do you want to generate specification for command "%s"?', $name);
        $io = $this->getApplication()->getContainer()->get('console.io');
        return $io->askConfirmation($question, true);
    }

    private function confirmHandlerSpec(InputInterface $input, $name)
    {
        if ($input->getOption('silent')) {
            return true;
        }

        $question = sprintf('Do you want to generate specification for handler "%s"?', $name);
        $io = $this->getApplication()->getContainer()->get('console.io');
        return $io->askConfirmation($question, true);
    }
}
