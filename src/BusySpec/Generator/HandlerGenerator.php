<?php

namespace Ulabox\PhpSpec\Extension\BusySpec\Generator;


use Memio\Memio\Config\Build;
use Memio\Model\Argument;
use Memio\Model\File;
use Memio\Model\Method;
use Memio\Model\Object;
use PhpSpec\Locator\ResourceInterface;

final class HandlerGenerator extends AbstractGenerator
{
    const COMMAND_TAG = 'busify_handler';

    protected function getCommandTag()
    {
        return self::COMMAND_TAG;
    }

    protected function generateCodeForResource(ResourceInterface $resource, array $data)
    {
        $structure = Object::make($resource->getSrcClassname());
        $structure->makeFinal();

        $handle = Method::make('handle');
        $handle->addArgument(Argument::make($data['handles'], 'command'));
        $handle->setBody("        // TODO write your own implementation");
        $structure->addMethod($handle);


        $file = File::make($resource->getSrcFilename())
            ->setStructure($structure)
        ;

        $prettyPrinter = Build::prettyPrinter();
        return $prettyPrinter->generateCode($file);
    }


    /**
     * @param ResourceInterface $resource
     * @param string            $filepath
     *
     * @return string
     */
    protected function getPromptMessage(ResourceInterface $resource, $filepath)
    {
        return sprintf(
            "<info>Handler <value>%s</value> created in <value>%s</value>.</info>\n",
            $resource->getSrcClassname(),
            $filepath
        );
    }
}
