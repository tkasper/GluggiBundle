<?php declare(strict_types=1);

namespace Becklyn\GluggiBundle\Usages;

use Becklyn\GluggiBundle\Data\Component;
use Becklyn\GluggiBundle\Data\Error\CompilationError;
use Becklyn\GluggiBundle\Data\Error\GluggiError;
use Becklyn\GluggiBundle\Exception\ComponentNotFoundException;
use Becklyn\GluggiBundle\Type\TypeRegistry;
use Becklyn\GluggiBundle\Usages\Twig\TwigUsagesVisitor;
use Twig\Environment;
use Twig\Error\Error;
use Twig\NodeTraverser;

class DependenciesParser
{
    /**
     * @var Environment
     */
    private $twig;


    /**
     * @var TwigUsagesVisitor
     */
    private $visitor;


    /**
     * @var NodeTraverser
     */
    private $traverser;


    /**
     */
    public function __construct (Environment $twig)
    {
        $this->twig = $twig;
        $this->visitor = new TwigUsagesVisitor();
        $this->traverser = new NodeTraverser($this->twig, [$this->visitor]);
    }


    /**
     * Parses all dependencies.
     */
    public function parseDependencies (TypeRegistry $typeRegistry) : void
    {
        foreach ($typeRegistry->getAll() as $type)
        {
            foreach ($type->getComponents() as $component)
            {
                $this->findAndLinkDependencies($typeRegistry, $component);
            }
        }
    }


    /**
     * Find every component that is used in the given template.
     */
    private function findAndLinkDependencies (TypeRegistry $typeRegistry, Component $component) : void
    {
        try
        {
            $source = $this->twig->getLoader()->getSourceContext($component->getTemplatePath());
            $tokenStream = $this->twig->tokenize($source);
            $module = $this->twig->parse($tokenStream);


            $this->visitor->reset();
            $this->traverser->traverse($module);
            $usages = $this->visitor->getUsages();

            foreach ($usages as $type => $names)
            {
                foreach ($names as $name)
                {
                    $dependency = $typeRegistry->getComponent($type, $name);
                    $component->addDependency($dependency);
                    $dependency->addUsage($component);
                }
            }
        }
        catch (Error $e)
        {
            $component->setError(new CompilationError($e));
        }
        catch (ComponentNotFoundException $e)
        {
            $component->setError(new GluggiError("Invalid import: {$e->getMessage()}"));
        }
    }
}
