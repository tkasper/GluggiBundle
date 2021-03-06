<?php declare(strict_types=1);

namespace Becklyn\GluggiBundle\Usages;

use Becklyn\GluggiBundle\Data\Component;
use Becklyn\GluggiBundle\Data\References;

class DependenciesResolver
{
    /**
     *
     */
    public function resolveDependencies (Component $component) : References
    {
        $fetcher = function (Component $component)
        {
            return $component->getDependencies();
        };

        return $this->resolve($component, $fetcher);
    }


    /**
     *
     */
    public function resolveUsages (Component $component) : References
    {
        $fetcher = function (Component $component)
        {
            return $component->getUsages();
        };

        return $this->resolve($component, $fetcher);
    }


    /**
     *
     */
    private function resolve (Component $component, callable $fetcher) : References
    {
        $direct = [];
        $transitive = [];

        // deduplicate dependencies
        foreach ($fetcher($component) as $directUsage)
        {
            $direct[$directUsage->getFullKey()] = $directUsage;
        }

        if (!empty($direct))
        {
            $transitive = $this->findTransitiveDependencies($component, $direct, $fetcher);
        }

        // remove transitive dependencies, that are already direct dependencies
        foreach ($direct as $usage)
        {
            if (\array_key_exists($usage->getFullKey(), $transitive))
            {
                unset($transitive[$usage->getFullKey()]);
            }
        }


        return new References($direct, $transitive);
    }


    /**
     * @param Component[] $direct
     *
     * @return Component[]
     */
    private function findTransitiveDependencies (Component $component, array $direct, callable $fetcher) : array
    {
        $queue = $direct;
        $alreadyChecked = [
            $component->getFullKey() => true,
        ];
        $result = [];

        while (!empty($queue))
        {
            /** @var Component $queueEntry */
            $queueEntry = \array_pop($queue);

            if (\array_key_exists($queueEntry->getFullKey(), $alreadyChecked))
            {
                continue;
            }

            /** @var Component[] $queueUses */
            $queueUses = $fetcher($queueEntry);

            foreach ($queueUses as $queueUse)
            {
                if (\array_key_exists($queueUse->getFullKey(), $alreadyChecked))
                {
                    continue;
                }

                $result[$queueUse->getFullKey()] = $queueUse;
                $queue[] = $queueUse;
            }

            $alreadyChecked[$queueEntry->getFullKey()] = true;
        }

        return $result;
    }
}
