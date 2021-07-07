<?php

namespace App\Controller;

use App\Entity\Reservante;
use App\Form\Filter\ReservaByEmailFilterType;
use App\Form\ReservanteType;
use App\Repository\CelebracionRepository;
use App\Repository\InvitadoRepository;
use App\Repository\ReservanteRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/reservante")
 * @IsGranted("ROLE_RESERVA")
 */
class ReservanteController extends AbstractController
{
    /**
     * @Route("/", name="reservante_index", methods={"GET"})
     * @param ReservanteRepository $reservanteRepository
     * @return Response
     */
    public function index(ReservanteRepository $reservanteRepository): Response
    {
        return $this->render('reservante/index.html.twig', [
            'reservantes' => $reservanteRepository->findAll(),
        ]);
    }

    /**
     * @Route("/lectura", name="reserva_lectura", methods={"GET","POST"})
     * @param Request $request
     * @param ReservanteRepository $reservanteRepository
     * @return Response
     */
    public function consultaReserva(Request $request, ReservanteRepository $reservanteRepository): Response
    {

        $form = $this->createForm(ReservaByEmailFilterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $celebracion = $form['celebracion']->getData();
            $email = $form['email']->getData();
            $reservante = $reservanteRepository->findOneByReserva($celebracion, $email);
            if (!$reservante) {
                $this->addFlash('info', 'No se encontró reserva');
                return $this->redirectToRoute('reserva_consulta');
            }

            return $this->redirectToRoute('vista_reserva',
                [
                    'celebracion' => $reservante->getCelebracion()->getId(),
                    'email' => $reservante->getEmail()
                ]);
        }

        return $this->render('reserva/lectura_reserva.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/new", name="reservante_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $reservante = new Reservante();
        $form = $this->createForm(ReservanteType::class, $reservante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($reservante);
            $entityManager->flush();

            return $this->redirectToRoute('reservante_index');
        }

        return $this->render('reservante/new.html.twig', [
            'reservante' => $reservante,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="reservante_show", methods={"GET"})
     * @param Reservante $reservante
     * @return Response
     */
    public function show(Reservante $reservante): Response
    {
        return $this->render('reservante/show.html.twig', [
            'reservante' => $reservante,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="reservante_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Reservante $reservante
     * @return Response
     */
    public function edit(Request $request, Reservante $reservante): Response
    {
        $form = $this->createForm(ReservanteType::class, $reservante);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('reservante_index');
        }

        return $this->render('reservante/edit.html.twig', [
            'reservante' => $reservante,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="reservante_delete", methods={"DELETE"})
     * @param Request $request
     * @param Reservante $reservante
     * @return Response
     */
    public function delete(Request $request, Reservante $reservante): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservante->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($reservante);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reservante_index');
    }

    /**
     * @Route("/faltan-datos/invitados", name="reservante_faltan_datos", methods={"GET", "POST"})
     * @param CelebracionRepository $celebracionRepository
     * @param ReservanteRepository $reservanteRepository
     * @return JsonResponse
     */
    public function faltanDatosInvitadosFromReservante(CelebracionRepository $celebracionRepository, ReservanteRepository $reservanteRepository): JsonResponse
    {
        $celebraciones = $celebracionRepository->puedeMostrarse()->getQuery()->getResult();
        $reservantes = $reservanteRepository->faltanDatosInvitadosFromReservante($celebraciones);

        return new JsonResponse($reservantes);
    }
}
