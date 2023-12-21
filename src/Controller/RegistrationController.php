<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Repository\AdminRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\RegistrationFormType;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"GET","POST"})
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppCustomAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new Admin();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $otp = rand(0000,9999);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                md5(
                    $form->get('plainPassword')->getData()
                )
            );

            $user->setStatus("0")->setOtp($otp);

            $entityManager->persist($user);
            $entityManager->flush();


            $link = $request->getUri() . '_verification/'.$otp;
            $html = "<p>Hello User,</p>";
            $html .= "<p>Please click <a href='".$link."'>here</a> verify your account</p>";
            $html .= "<p>Thank you</p>";

            $filePath = $_SERVER['DOCUMENT_ROOT'].'/../var/emails/'.$user->getId().'.html';
            if (! is_dir($dir = implode('/', explode('/', $filePath, -1))))
            {
                mkdir($dir, 0777, true);
            }
            file_put_contents($filePath, $html);

            return $this->redirectToRoute('login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/register_verification", name="register_verification", methods={"GET"})
     */
    public function register_verification(Request $request, AdminRepository $adminRepository, ManagerRegistry $doctrine): Response
    {
        if ($request->GET('otp')) {
            $entityManager = $doctrine->getManager();
            $user = $adminRepository->findBy(array('otp' => $request->GET('otp')));
            if(!empty($user)) {
                $user = $user[0];
                $user
                ->setStatus("1")
                ->setOtp("");
                $entityManager->persist($user);
                $entityManager->flush();
                return $this->redirectToRoute('login', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('register', [], Response::HTTP_SEE_OTHER);
            }
            } else {
                return $this->redirectToRoute('register', [], Response::HTTP_SEE_OTHER);
            }
    }
}
