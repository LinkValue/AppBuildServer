<?php

namespace Majora\OTAStore\ApplicationBundle\Service;

use Majora\OTAStore\ApplicationBundle\Entity\Build;
use Symfony\Component\Routing\Router;

/**
 * BuildLinkBuilder.
 */
class BuildLinkBuilder
{
    /**
     * @var Router
     */
    private $router;

    /**
     * construct.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param Build $build
     * @param bool  $absoluteUrl
     *
     * @return string
     */
    public function getDownloadLink(Build $build, $absoluteUrl = true)
    {
        return $this->router->generate(
            'majoraotastore_admin_build_download',
            [
                'application_id' => $build->getApplication()->getId(),
                'id' => $build->getId(),
            ],
            ($absoluteUrl) ? Router::ABSOLUTE_URL : Router::ABSOLUTE_PATH
        );
    }
}
