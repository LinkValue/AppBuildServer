<?php

namespace AppBuild\Bundle\UserBundle\Controller;

use AppBuild\Bundle\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;

class SecurityController extends Controller
{
    /**
     * Login.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'AppBuildUserBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }

    /**
     * List all users.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $users = new ArrayCollection($this->getDoctrine()->getRepository('AppBuildUserBundle:User')->findAll());

        list($enabled, $disabled) = $users->partition(function ($i, User $user) {
            return $user->isEnabled();
        });

        return $this->render(
            'AppBuildUserBundle:Security:list.html.twig',
            array('users' => $request->get('enabled', true) ? $enabled : $disabled)
        );
    }

    /**
     * Create a user.
     *
     * @param Request $request
     *
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = new User();
        $form = $this->container->get('form.factory')->create(
            $this->container->get('appbuild.user.user.form_type'),
            $user,
            array('intention' => 'creation')
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            // Encode password
            if (!$password = $form->get('password')->getData()) {
                throw new ValidatorException('Password must be set.');
            }
            $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $password));

            // Set role
            if ($role = $form->get('roles')->getData()) {
                $user->setRoles(array($role));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $this->container->get('translator')->trans('user.create.flash.success'));

            return $this->redirectToRoute('appbuild_user_create');
        }

        return $this->render(
            'AppBuildUserBundle:Security:create.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Update user.
     *
     * @param User    $user
     * @param Request $request
     *
     * @return Response
     */
    public function updateAction(User $user, Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->container->get('form.factory')->create(
            $this->container->get('appbuild.user.user.form_type'),
            $user,
            array('intention' => 'edition')
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // Encode password if it is set
                if ($password = $form->get('password')->getData()) {
                    $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $password));
                }

                // Set role
                if ($role = $form->get('roles')->getData()) {
                    $user->setRoles(array($role));
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('user.update.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                    'appbuild_user_list'
                ));
            }
        }

        return $this->render(
            'AppBuildUserBundle:Security:update.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user,
            )
        );
    }

    /**
     * Delete user.
     *
     * @param User $user
     *
     * @return Response
     */
    public function deleteAction(User $user)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($user);
        $em->flush();

        return new RedirectResponse($this->container->get('router')->generate('appbuild_user_list'));
    }

    /**
     * Toggles the enabled property of the user.
     *
     * @param User    $user
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function toggleEnableAction(User $user, Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $user->setEnabled(!$user->isEnabled());

        $em->persist($user);
        $em->flush();

        return new RedirectResponse(
            $request->headers->get('referer')
            ?: $this->get('router')->generate('appbuild_user_list')
        );
    }

    /**
     * Allow current user to edit his information.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function myAccountAction(Request $request)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();
        $form = $this->container->get('form.factory')->create(
            $this->container->get('appbuild.user.user.form_type'),
            $user,
            array('intention' => 'my-account')
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            // Encode password if it is set
            if ($password = $form->get('password')->getData()) {
                $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $password));
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $this->container->get('translator')->trans('user.my_account.flash.success'));

            return $this->redirectToRoute('appbuild_admin_application_list');
        }

        return $this->render(
            'AppBuildUserBundle:Security:my-account.html.twig',
            array('form' => $form->createView())
        );
    }
}
