<?php

namespace Majora\OTAStore\ApplicationBundle\Controller\Admin;

use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Majora\OTAStore\ApplicationBundle\Form\Type\ApplicationType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationController extends BaseController
{
    /**
     * List current user Applications.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        if (!($isAskingForEnabled = $request->get('enabled', true)) && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $applications = $this->getUserApplications();

        list($enabled, $disabled) = $applications->partition(function ($i, Application $application) {
            return $application->isEnabled();
        });

        return $this->render(
            'MajoraOTAStoreApplicationBundle:Application:list.html.twig',
            array('applications' => ($isAskingForEnabled) ? $enabled : $disabled)
        );
    }

    /**
     * Create application.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->container->get('form.factory')->create(
            ApplicationType::class,
            $application = new Application(),
            array('csrf_token_id' => ApplicationType::TOKEN_CREATION)
        );

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($application);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('application.create.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                    'majoraotastore_admin_application_list'
                ));
            }
        }

        return $this->render('MajoraOTAStoreApplicationBundle:Application:create.html.twig',
            array(
                'form' => $form->createView(),
                'application' => $application,
                'currentUserId' => $this->getUser()->getId(),
                'applicationSupportIOS' => Application::SUPPORT_IOS,
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
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->container->get('form.factory')->create(
            ApplicationType::class,
            $application,
            array('csrf_token_id' => ApplicationType::TOKEN_EDITION)
        );

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($application);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('application.update.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                    'majoraotastore_admin_application_list'
                ));
            }
        }

        return $this->render(
            'MajoraOTAStoreApplicationBundle:Application:update.html.twig',
            array(
                'form' => $form->createView(),
                'application' => $application,
                'currentUserId' => $this->getUser()->getId(),
                'applicationSupportIOS' => Application::SUPPORT_IOS,
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
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($application);
        $em->flush();

        return new RedirectResponse($this->container->get('router')->generate('majoraotastore_admin_application_list'));
    }

    /**
     * Toggles the enabled property of the application.
     *
     * @param Application $application
     * @param Request     $request
     *
     * @return RedirectResponse
     */
    public function toggleEnableAction(Application $application, Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $application->setEnabled(!$application->isEnabled());

        $em->persist($application);
        $em->flush();

        return new RedirectResponse($request->headers->get('referer') ?:
            $this->container->get('router')->generate(
                'majoraotastore_admin_application_list'
            )
        );
    }
}
