<?php

namespace Ulabox\PhpSpec\Extension\BusySpec;

use Ulabox\PhpSpec\Extension\BusySpec\Command\BusifyCommand;
use PhpSpec\Extension\ExtensionInterface;
use PhpSpec\ServiceContainer;
use Ulabox\PhpSpec\Extension\BusySpec\Generator\CommandGenerator;
use Ulabox\PhpSpec\Extension\BusySpec\Generator\CommandSpecGenerator;
use Ulabox\PhpSpec\Extension\BusySpec\Generator\HandlerGenerator;
use Ulabox\PhpSpec\Extension\BusySpec\Generator\HandlerSpecGenerator;

class Extension implements ExtensionInterface
{
    /**
     * @param ServiceContainer $container
     */
    public function load(ServiceContainer $container)
    {
        $container->set(
            sprintf('code_generator.generators.%s', CommandGenerator::COMMAND_TAG),
            function (ServiceContainer $c) {
                return new CommandGenerator(
                    $c->get('console.io')
                );
            }
        );

        $container->set(
            sprintf('code_generator.generators.%s', HandlerGenerator::COMMAND_TAG),
            function (ServiceContainer $c) {
                return new HandlerGenerator(
                    $c->get('console.io')
                );
            }
        );

        $container->set(
            sprintf('code_generator.generators.%s', CommandSpecGenerator::COMMAND_TAG),
            function (ServiceContainer $c) {
                return new CommandSpecGenerator(
                    $c->get('console.io')
                );
            }
        );

        $container->set(
            sprintf('code_generator.generators.%s', HandlerSpecGenerator::COMMAND_TAG),
            function (ServiceContainer $c) {
                return new HandlerSpecGenerator(
                    $c->get('console.io')
                );
            }
        );

        $container->setShared('console.commands.busify', function () {
            return new BusifyCommand();
        });
    }
}
