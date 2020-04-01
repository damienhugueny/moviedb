<?php

namespace App\Controller\Backend;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/backend/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('backend/user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * pour obtenir l'encoder de mots de passe qui est basé sur ce que j'ai specifié dans mon security.yml je l'injecte en tant que dependance (UserPasswordEncoderInterface) au meme titre que la variable qui contient l'objet Request
     * 
     * @Route("/add", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        //rappel handlerequest met a jour l'objet user avec les info du form
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //ici j'encode le mot de passe de mon utilisateur en lui passant l'objet user en cours et en second parametre le mot a encoder => celui que l'utilisateur vient de me saisir
            $encodedPassword = $passwordEncoder->encodePassword($user, $user->getPassword());

            //une fois le mot de passe encodé j'ecrases l'exitant avec celui ci
            $user->setPassword($encodedPassword);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('backend/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('backend/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // SI un mot de pase a été renseigné, on modifie celui du User

            // Via Champ non mappé automatiquement sur l'entité
            // https://symfony.com/doc/current/forms.html#unmapped-fields
            if ($form->get('password')->getData()) {
                // Encodons-le
                $encodedPassword = $passwordEncoder->encodePassword($user, $form->get('password')->getData());
                // Mettons-le dans le user
                $user->setPassword($encodedPassword);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('backend/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
