<?php

namespace Becklyn\GluggiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


/**
 * Controller for the main gluggi view
 */
class GluggiController extends Controller
{
    /**
     * @Template()
     * @return array
     */
    public function indexAction ()
    {
        return [
            "types" => $this->get("gluggi.finder")->getAllTypes(),
            "pageTitle" => "Index",
        ];
    }



    /**
     * Renders a list of all components in the given type
     *
     * @Template()
     *
     * @param string $type
     *
     * @return array
     */
    public function typeAction (string $type)
    {
        $componentType = $this->get("gluggi.finder")->findType($type);

        if (!$componentType->hasStandaloneComponents())
        {
            throw $this->createNotFoundException(sprintf("No components found in type '%s'.", $type));
        }

        return [
            "type" => $componentType,
            "pageTitle" => $componentType->getName(),
        ];
    }



    /**
     * Renders a component in single view
     * @Template()
     *
     * @param string $type
     * @param string $key
     *
     * @return array
     */
    public function componentAction (string $type, string $key)
    {
        $component = $this->get("gluggi.finder")->findComponent($type, $key);

        if (null === $component || $component->isHidden())
        {
            $message = null === $component
                ? "No component found: '%s/%s'"
                : "The component '%s/%s' has no single view.";

            throw $this->createNotFoundException(sprintf($message, $type, $key));
        }

        return [
            "component" => $component,
            "type" => $component->getType(),
            "pageTitle" => $component->getType()->getName() . " // " . $component->getName(),
        ];
    }



    /**
     * Includes all layout-related CSS <link> tags
     *
     * @Template("@Gluggi/Gluggi/_layoutCSSAssets.html.twig")
     * @return array
     */
    public function layoutCSSAssetsAction ()
    {
        $assets = $this->get("gluggi.assets");

        return [
            "urls" => $assets->getCssUrls(),
        ];
    }



    /**
     * Includes all layout-related JavaScript <script> tags
     *
     * @Template("@Gluggi/Gluggi/_layoutJavaScriptAssets.html.twig")
     * @return array
     */
    public function layoutJavaScriptAssetsAction ()
    {
        $assets = $this->get("gluggi.assets");

        return [
            "urls" => $assets->getJavaScriptUrls(),
        ];
    }
}
