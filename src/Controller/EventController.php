<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findUpcomingEvents();

        return $this->render('event/index.html.twig', [
            'events' => $events,
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
}
