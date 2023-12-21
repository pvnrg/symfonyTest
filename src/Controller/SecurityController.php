<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\AdminRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login", methods={"GET","POST"})
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('notes');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error, 'active_error' =>'']);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): void
    {
        $session->set('user_auth', 0);
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/login_auth", name="login_auth", methods={"POST"})
     */
    public function login_auth(AdminRepository $adminRepository, ManagerRegistry $doctrine,SessionInterface $session)
    {
        $email = $_REQUEST['email'];
        $password = $_REQUEST['password'];

        $entityManager = $doctrine->getManager();
        $user = $adminRepository->findBy(array('email' => $email));
        if(!empty($user)) {
            $user = $user[0];

            if($user->getStatus() == 0) {
                $this->addFlash('danger', 'Account is not verified!');
                return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
            } else {
                $checkpassword = md5(
                    $password
                );

                if($user->getPassword() == $checkpassword) {
                    $session->set('user_auth', $user->getId());

                    return $this->redirectToRoute('notes', [], Response::HTTP_SEE_OTHER);
                } else {
                    $this->addFlash('danger', 'Password not match with email!');
                    return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
                }
            }
        } else {
            $this->addFlash('danger', 'Email is not exist!');
            return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
        }
    }
}
