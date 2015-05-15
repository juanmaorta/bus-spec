<?php

namespace Ulabox\PhpSpec\Extension\BusySpec\Generator;

use Memio\Memio\Config\Build;
use Memio\Model\Argument;
use Memio\Model\File;
use Memio\Model\FullyQualifiedName;
use Memio\Model\Method;
use Memio\Model\Object;
use PhpSpec\Locator\ResourceInterface;

class HandlerSpecGenerator extends AbstractGenerator
{
    const COMMAND_TAG = 'busify_handler_spec';

    protected function getCommandTag()
    {
        return self::COMMAND_TAG;
    }

    protected function generateCodeForResource(ResourceInterface $resource, array $data)
    {
        /** @var Object $structure */
        $structure = Object::make($resource->getSpecClassname());

        $handledClass = $data['handles'];
        $pieces = explode("\\", $handledClass);
        $handledClassShortName = end($pieces);

        $arguments = '$' . implode(', $', $data['params']);


        $structure->extend(Object::make('PhpSpec\ObjectBehavior'));
        $methodBody = <<<BODY
        \$this->shouldHaveType('{$resource->getSrcClassname()}');
BODY;

        $structure->addMethod(Method::make('it_is_initializable')->setBody($methodBody));

        $methodBody = <<<BODY
        throw new PendingException('Pending implementation');
        \$command = new {$handledClassShortName}({$arguments});
        \$this->handle(\$command);
BODY;

        $structure->addMethod(Method::make('it_should_handle')->setBody($methodBody));

        $file = File::make($resource->getSpecFilename());

        $file->addFullyQualifiedName(new FullyQualifiedName('PhpSpec\ObjectBehavior'));
        $file->addFullyQualifiedName(new FullyQualifiedName('Prophecy\Argument'));
        $file->addFullyQualifiedName(new FullyQualifiedName('PhpSpec\Exception\Example\PendingException'));
        $file->addFullyQualifiedName(new FullyQualifiedName($handledClass));
        $file->setStructure($structure);

        $prettyPrinter = Build::prettyPrinter();
        return $prettyPrinter->generateCode($file);
    }

    protected function getPromptMessage(ResourceInterface $resource, $filepath)
    {
        return sprintf(
            "<info>Specification for <value>%s</value> created in <value>%s</value>.</info>\n",
            $resource->getSrcClassname(),
            $filepath
        );
    }

    protected function getSavePath(ResourceInterface $resource)
    {
        return $resource->getSpecFilename();
    }
}
