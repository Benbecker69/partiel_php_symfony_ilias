<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Reservation;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EventRepository $eventRepository, ReservationRepository $reservationRepository): Response
    {
        $events = $eventRepository->findUpcomingEvents();

        // Récupérer les IDs des événements réservés par l'utilisateur connecté
        $reservedEventIds = [];
        if ($this->getUser()) {
            $userReservations = $reservationRepository->findBy(['user' => $this->getUser()]);
            foreach ($userReservations as $reservation) {
                $reservedEventIds[] = $reservation->getEvent()->getId();
            }
        }

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'reservedEventIds' => $reservedEventIds,
        ]);
    }

    #[Route('/event/{id}', name: 'app_event_show', requirements: ['id' => '\d+'])]
    public function show(Event $event, ReservationRepository $reservationRepository): Response
    {
        $hasReserved = false;

        // Vérifier si l'utilisateur connecté a déjà réservé
        if ($this->getUser()) {
            $existingReservation = $reservationRepository->findOneByUserAndEvent($this->getUser(), $event);
            $hasReserved = $existingReservation !== null;
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'hasReserved' => $hasReserved,
        ]);
    }

    #[Route('/event/{id}/reserve', name: 'app_event_reserve', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function reserve(
        Event $event,
        Request $request,
        ReservationRepository $reservationRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('reserve' . $event->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $user = $this->getUser();

        // Vérifier si l'utilisateur a déjà réservé
        $existingReservation = $reservationRepository->findOneByUserAndEvent($user, $event);
        if ($existingReservation) {
            $this->addFlash('error', 'Vous avez deja reserve pour cet evenement.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        // Vérifier si l'événement n'est pas complet
        if ($event->getReservations()->count() >= $event->getCapacity()) {
            $this->addFlash('error', 'Cet evenement est complet.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        // Créer la réservation
        $reservation = new Reservation();
        $reservation->setUser($user);
        $reservation->setEvent($event);
        // createdAt est défini automatiquement dans le constructeur

        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->addFlash('success', 'Votre reservation a ete confirmee !');

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
}
