<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Filesystem\Filesystem;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );


            $user->setTariff($request->get('tariff'));
            $entityManager->persist($user);
            $entityManager->flush();

            $this->createUserSpace($user->getId());


            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    private function createUserSpace($userId) {
        $coreFileSystem = new Filesystem();

        $userFolder = $_SERVER['DOCUMENT_ROOT'] . '/storage/user_' . $userId;
        $userDisk = $userFolder . '/' . 'disk';
        $userBasket = $userFolder . '/' . 'basket';


        if(!is_dir($userFolder)) {
            $coreFileSystem->mkdir($userFolder);

            if(is_dir($userFolder)) {
                $coreFileSystem->mkdir($userDisk);
                $coreFileSystem->mkdir($userBasket);
            }
        }
    }


}
