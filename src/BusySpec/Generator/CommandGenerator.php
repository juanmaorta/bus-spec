<?php

namespace Ulabox\PhpSpec\Extension\BusySpec\Generator;

use Memio\Memio\Config\Build;
use Memio\Model\Argument;
use Memio\Model\File;
use Memio\Model\Method;
use Memio\Model\Object;
use Memio\Model\Phpdoc\MethodPhpdoc;
use Memio\Model\Phpdoc\ReturnTag;
use Memio\Model\Property;
use PhpSpec\Locator\ResourceInterface;

final class CommandGenerator extends AbstractGenerator
{
    const COMMAND_TAG = 'busify_command';

    protected function getCommandTag()
    {
        return self::COMMAND_TAG;
    }

    protected function generateCodeForResource(ResourceInterface $resource, array $data)
    {
        $structure = Object::make($resource->getSrcClassname());
        $structure->makeFinal();

        $construct = Method::make('__construct');
        $structure->addMethod($construct);
        $constructBody = [];
        foreach ($data['params'] as $param) {
            $structure->addProperty(Property::make($param));
            $body = <<<METHOD
        return \$this->{$param};
METHOD;
            $construct->addArgument(Argument::make('mixed', $param));
            $constructBody[] = "        \$this->{$param} = \${$param};";
            $method = Method::make($param)->setBody($body);
            $structure->addMethod($method);
        }
        $construct->setBody(implode("\n", $constructBody));
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
        "<info>Command <value>%s</value> created in <value>%s</value>.</info>\n",
            $resource->getSrcClassname(),
            $filepath
        );
    }
}
