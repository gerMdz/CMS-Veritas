<?php

namespace App\Controller;

use App\Entity\Invitado;
use App\Form\InvitadoType;
use App\Repository\CelebracionRepository;
use App\Repository\InvitadoRepository;
use App\Service\ObtenerDatosHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncode;

/**
 * @Route("/admin/invitado")
 * @IsGranted("ROLE_RESERVA")
 */
class InvitadoController extends AbstractController
{
    /**
     * @Route("/", name="invitado_index", methods={"GET"})
     * @param InvitadoRepository $invitadoRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param CelebracionRepository $celebracionRepository
     * @return Response
     */
    public function index(InvitadoRepository $invitadoRepository, PaginatorInterface $paginator, Request $request, CelebracionRepository $celebracionRepository): Response
    {
        $q = $request->query->get('c');
        $busq = $request->query->get('busq');
        $celebracion = null;
        if(isset($q)){
            $celebracion = $celebracionRepository->find($q);
        }
        $queryBuilder = $invitadoRepository->searchQueryBuilder($q, $busq);
        $invitados = $paginator->paginate(
            $queryBuilder, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/
        );
        return $this->render('invitado/index.html.twig', [
            'invitados' => $invitados,
            'celebracion' => $celebracion
        ]);
    }

    /**
     * @Route("/update_ausente", name="invitado_update_ausente", methods={"GET"})
     * @param Request $request
     * @param InvitadoRepository $invitadoRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function updateAusente(Request $request, InvitadoRepository $invitadoRepository, EntityManagerInterface $entityManager)
    {
        $q = $request->query->get('c');
        $invitados = $invitadoRepository->getAusentesCelebracion($q);
        foreach ($invitados as $invitado){
            /** @var Invitado $invitado */
            $invitado->setIsPresente(false);
            $entityManager->persist($invitado);
        }
        $entityManager->flush();
        $ausentes = $invitadoRepository->countAusentesByCelebracion($q);

        return new JsonResponse(['ausentes' => $ausentes]);

    }

    /**
     * @Route("/new", name="invitado_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $invitado = new Invitado();
        $form = $this->createForm(InvitadoType::class, $invitado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($invitado);
            $entityManager->flush();

            return $this->redirectToRoute('invitado_index');
        }

        return $this->render('invitado/new.html.twig', [
            'invitado' => $invitado,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="invitado_show", methods={"GET"})
     * @param Invitado $invitado
     * @return Response
     */
    public function show(Invitado $invitado): Response
    {
        return $this->render('invitado/show.html.twig', [
            'invitado' => $invitado,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="invitado_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Invitado $invitado): Response
    {
        $form = $this->createForm(InvitadoType::class, $invitado);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('invitado_index');
        }

        return $this->render('invitado/edit.html.twig', [
            'invitado' => $invitado,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/{id}", name="invitado_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Invitado $invitado): Response
    {
        if ($this->isCsrfTokenValid('delete'.$invitado->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($invitado);
            $entityManager->flush();
        }

        return $this->redirectToRoute('invitado_index');
    }

    /**
     * @Route("/cambia_presente", name="cambia_presente", methods={"GET", "POST"})
     * @param Request $request
     * @param InvitadoRepository $invitadoRepository
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    public function cambiaPresente(Request $request, InvitadoRepository $invitadoRepository, EntityManagerInterface $em)
    {
        $id = $request->get('id');
        $invitado = $invitadoRepository->find($id);
        $invitado->setIsPresente(!$invitado->getIsPresente());
        $em->persist($invitado);
        $em->flush();
        return new JsonResponse(['presente' => $invitado->getIsPresente()]);

    }

    /**
     * @Route("/faltan-datos/celebracion", name="invitados_faltan_datos", methods={"GET", "POST"})
     * @param CelebracionRepository $celebracionRepository
     * @param InvitadoRepository $invitadoRepository
     * @return JsonResponse
     */
    public function faltanDatosInvitados(CelebracionRepository $celebracionRepository, InvitadoRepository $invitadoRepository): JsonResponse
    {
        $celebraciones = $celebracionRepository->puedeMostrarse()->getQuery()->getResult();
        $invitados = $invitadoRepository->faltanDatosInvitados($celebraciones)->getQuery()->getArrayResult();

        return new JsonResponse($invitados);
    }
}
