<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/event')]
class AdminEventController extends AbstractController
{
    #[Route('', name: 'admin_event_index')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findBy([], ['date' => 'DESC']);

        return $this->render('admin/event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/new', name: 'admin_event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Evenement cree avec succes.');

            return $this->redirectToRoute('admin_event_index');
        }

        return $this->render('admin/event/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_event_edit', requirements: ['id' => '\d+'])]
    public function edit(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Evenement modifie avec succes.');

            return $this->redirectToRoute('admin_event_index');
        }

        return $this->render('admin/event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_event_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();

            $this->addFlash('success', 'Evenement supprime avec succes.');
        }

        return $this->redirectToRoute('admin_event_index');
    }
}
