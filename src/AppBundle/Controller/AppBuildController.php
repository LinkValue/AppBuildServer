<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use AppBundle\Entity\Application;

class AppBuildController extends Controller
{
    /**
     * Upload application.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        $form = $this->container->get('form.factory')->create(
            $this->container->get('build.application.form_type'),
            $application = new Application(),
            array('intention' => 'creation')
        );
        $form->add('submit', 'submit');

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($application);
                $em->flush();

                return new RedirectResponse($this->container->get('router')->generate(
                    'app_update', array(
                        'id' => $application->getId(),
                    )
                ));
            }
        }

        return $this->render('AppBundle:Application:create.html.twig',
            array(
                'form' => $form->createView(),
                'application' => $application,
            )
        );
    }

    /**
     * Update application.
     *
     * @param Application $application
     * @param Request     $request
     *
     * @return Response
     */
    public function updateAction(Application $application, Request $request)
    {
        $form = $this->container->get('form.factory')->create(
            $this->container->get('build.application.form_type'),
            $application,
            array('intention' => 'edition')
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->container->get('doctrine.orm.entity_manager')->flush();
            }
        }

        return $this->render(
            'AppBundle:Application:update.html.twig',
            array(
                'form' => $form->createView(),
                'application' => $application,
            )
        );
    }

    /**
     * Delete application.
     *
     * @param Application $application
     *
     * @return Response
     */
    public function deleteAction(Application $application)
    {
        $this->container->get('file_helper')->unlinkFile($application->getFilePath());
        $this->container->get('doctrine.orm.entity_manager')->remove($application);

        return new RedirectResponse($this->container->get('router')->generate('app_upload'));
    }

    /**
     * Trigger app download process.
     *
     * @param int $id application id
     *
     * @return Response
     */
    public function downloadAction($id)
    {
        $this->retrieveOr404($id, 'drop.application.loader');

        $response = new RedirectResponse(
            sprintf(
                'itms-services://?action=download-manifest&amp;url=%s',
                urlencode($this->get('router')->generate(
                    'drop_api_application_manifest',
                    array('id' => $id),
                    true
                ))
            ),
            302
        );

        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }

    /**
     * Download given application manifest.
     *
     * @ApiDoc(
     *    description  = "Download given application id manifest",
     *    tags         = { "stable" = "#0CB24F" },
     *    views        = { "default", "mobile" },
     *    section      = "Application",
     *    output       = "Drop\Si\Component\Application\Entity\Application",
     *    requirements = {
     *        { "name" = "id", "dataType" = "integer", "requirement" = "\d+", "description" = "Requested Application id" }
     *    },
     *    statusCodes  = {
     *        200 = "Returned when successful",
     *        404 = "If application not found",
     *        403 = "If method access denied"
     *    }
     * )
     */
    public function manifestAction($id)
    {
        $application = $this->retrieveOr404($id, 'drop.application.loader');

        $response = $this->render(
            sprintf(
                'AppBundle:ApplicationServer/%s:manifest.plist.twig',
                $application->getCode()
            ),
            array(
                'application' => $application,
                'http_scheme' => $this->container->getParameter('http_scheme'),
            )
        );

        $response->headers->set('Content-Type', 'application/octect-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="manifest.plist"');

        return $response;
    }

    /**
     * Download given application built package.
     *
     * @ApiDoc(
     *    description  = "Download given application id built package",
     *    tags         = { "stable" = "#0CB24F" },
     *    views        = { "default", "mobile" },
     *    section      = "Application",
     *    output       = "Drop\Si\Component\Application\Entity\Application",
     *    requirements = {
     *        { "name" = "id", "dataType" = "integer", "requirement" = "\d+", "description" = "Requested Application id" }
     *    },
     *    statusCodes  = {
     *        200 = "Returned when successful",
     *        404 = "If application not found",
     *        403 = "If method access denied"
     *    }
     * )
     */
    public function compiledAction($id)
    {
        $application = $this->retrieveOr404($id, 'drop.application.loader');

        $response = new StreamedResponse(function () use ($application) {
            readfile($application->getFilePath());
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', sprintf(
            'attachment; filename="%s"',
            basename($application->getFilePath())
        ));

        return $response;
    }

    public function getManifestAction(Application $application)
    {
        $response = $this->render(
            'AppBundle:Manifest:manifest.plist.twig',
            array(
                'application' => $application,
            )
        );

        $response->headers->set('Content-Type', 'application/octect-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="manifest.plist"');

        return $response;
    }
}