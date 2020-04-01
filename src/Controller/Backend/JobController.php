<?php

namespace App\Controller\Backend;

use App\Entity\Job;
use App\Form\Type\JobType;
use App\Repository\JobRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class JobController extends AbstractController
{
    /**
     * Lister les jobs
     *
     * @Route("/backend/job", name="backend_job_list", methods={"GET"})
     */
    public function list(JobRepository $jobRepository)
    {
        $jobs = $jobRepository->findBy(
            [],
            ['name' => 'ASC']
        );

        return $this->render('backend/job/list.html.twig', [
            'jobs' => $jobs,
        ]);
    }

    /**
     * Ajout d'un job
     *
     * @Route("/backend/job/add", name="backend_job_add", methods={"GET", "POST"})
     */
    public function add(Request $request)
    {
        // On crée une nouvelle entité Job
        $job = new Job();
        dump($job);
        // On crée le formulaire d'ajout du job
        // ... sur lequel on "map" le job
        $form = $this->createForm(JobType::class, $job);

        // On demande au form de "prendre en charge" la requête
        $form->handleRequest($request);

        // Si form est soumis ? Est-il valide ?
        if ($form->isSubmitted() && $form->isValid()) {
            // A ce stade l'entité $job contient déjà toutes les infos du form :)
            // car mappées via le form depuis handleRequest()
            // On sauvegarde le job
            $em = $this->getDoctrine()->getManager();
            $em->persist($job);
            $em->flush($job);

            $this->addFlash('success', 'Métier ajouté.');
            
            // On redirige vers la liste
            return $this->redirectToRoute('backend_job_list');
        }
        
        return $this->render('backend/job/add.html.twig', [
            // createView() permet de récupérer
            // la représentation HTML du form
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un job
     *
     * @Route("/backend/job/edit/{id<\d+>}", name="backend_job_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, $id, Job $job = null)
    {
        if ($job === null) {
            // 404 ?
            throw $this->createNotFoundException('Ce métier n\'existe pas.');
        }

        // On crée le formulaire d'edition du job
        // ... sur lequel on "map" le job
        $form = $this->createForm(JobType::class, $job);

        // On demande au form de "prendre en charge" la requête
        $form->handleRequest($request);

        // Si form est soumis ? Est-il valide ?
        if ($form->isSubmitted() && $form->isValid()) {
            // A ce stade l'entité $job est connue de Doctrine
            // On met à jour l'entité
            $job->setUpdatedAt(new \DateTime());
            // On sauvegarde le job (donc sans persist ici)
            $em = $this->getDoctrine()->getManager();
            $em->flush($job);

            $this->addFlash('success', 'Métier modifié.');

            // On redirige vers la même page
            return $this->redirectToRoute('backend_job_edit', ['id' => $id]);
        }
        
        return $this->render('backend/job/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/backend/job/delete/{id<\d+>}", name="backend_job_delete", methods={"DELETE"})
     */
    public function delete(Job $job = null, Request $request)
    {
        $submittedToken = $request->request->get('token');

        // 'delete-job' is the same value used in the template to generate the token
        if ($this->isCsrfTokenValid('delete-job', $submittedToken)) {

            // Token valide

            if ($job === null) {
                // 404 ?
                throw $this->createNotFoundException('Ce métier n\'existe pas.');
            }
    
            // On remove via Doctrine Manager
            $em = $this->getDoctrine()->getManager();
            $em->remove($job);
            $em->flush($job);
    
            $this->addFlash('success', 'Métier supprimé : '.$job->getName());

        } else {

            // Token invalide

            $this->addFlash('danger', 'Formulaire invalide. Veuillez le renvoyer.');

        }

        return $this->redirectToRoute('backend_job_list');
    }

    /**
     * @Route("/backend/job/{id<\d+>}", name="backend_job_show", methods={"GET"})
     */
    public function show(Job $job = null)
    {
        // si on veut récupérer la main sur la 404
        // = null en valeur par défaut du param
        if ($job === null) {
            // 404 ?
            throw $this->createNotFoundException('Ce métier n\'existe pas.');
        }

        return $this->render('backend/job/show.html.twig', [
            'job' => $job,
        ]);
    }
}
