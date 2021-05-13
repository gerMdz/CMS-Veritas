<?php

namespace App\Controller;

use App\Entity\Entrada;
use App\Entity\Principal;
use App\Entity\Section;
use App\Form\SectionFormType;
use App\Repository\EntradaRepository;
use App\Repository\SectionRepository;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\QueryException;
use Exception;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SectionController
 * @package App\Controller
 * @Route("/admin/section")
 */
class SectionController extends BaseController
{
    /**
     * @param SectionRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     * @Route("/", name="admin_section_list")
     */
    public function list(SectionRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
//        $seccion = $repository->getSections()->getQuery()->getResult();
        $seccion = $repository->getSections();

        $secciones = $paginator->paginate(
            $seccion, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/
        );

        return $this->render('section_admin/list.html.twig', [
            'sections' => $secciones
        ]);
    }

    /**
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param UploaderHelper $uploaderHelper
     * @return Response
     * @throws Exception
     * @Route("/new", name="admin_section_new")
     * @IsGranted("ROLE_EDITOR")
     */
    public function new(EntityManagerInterface $em, Request $request, UploaderHelper $uploaderHelper): Response
    {
        $form = $this->createForm(SectionFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Section $section */
            $section = $form->getData();

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();

            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadEntradaImage($uploadedFile, false);
                $section->setImageFilename($newFilename);
            }
            $em->persist($section);
            $em->flush();

            $this->addFlash('success', 'Sección creada. Gracias por su contribución');

            return $this->redirectToRoute('admin_section_list');
        }
        return $this->render('section_admin/new.html.twig', [
            'sectionForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_section_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Section $section
     * @param UploaderHelper $uploaderHelper
     * @return Response
     * @IsGranted("MANAGE", subject="section")
     * @throws Exception
     */
    public function edit(Request $request, Section $section, UploaderHelper $uploaderHelper): Response
    {
        $form = $this->createForm(SectionFormType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadEntradaImage($uploadedFile, $section->getImageFilename());
                $section->setImageFilename($newFilename);
            }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_section_list');
        }

        return $this->render('section_admin/edit.html.twig', [
            'section' => $section,
            'sectionForm' => $form->createView(),
        ]);
    }



    /**
     * @Route("/index/{id}", name="admin_index_delete_section", methods={"DELETE"})
     * @param Section $section
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function deleteIndexSection(Section $section, EntityManagerInterface $entityManager): Response
    {
        $indexAlameda = $section->getIndexAlamedas();


        $section->removeIndexAlameda($indexAlameda[0]);

        $entityManager->flush();
        return new Response(null, 204);
    }

    /**
     * @Route("/entrada/{id}/{entrada}", name="admin_entrada_delete_section", methods={"DELETE"})
     * @param Section $section
     * @param Entrada $entrada
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function deleteEntradaSection(Section $section, Entrada $entrada, EntityManagerInterface $entityManager): Response
    {

        $section->removeEntrada($entrada);

        $entityManager->flush();
        return new Response(null, 204);
    }

    /**
     * @Route("/principal/{id}/{principal}", name="admin_principal_delete_section", methods={"DELETE"})
     * @param Section $section
     * @param Principal $principal
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function deletePrincipalSection(Section $section, Principal $principal, EntityManagerInterface $entityManager): Response
    {
        if($principal->getSecciones() !== null) {
            $section->removePrincipale($principal);
        }

        $section->setPrincipal(null);
        $entityManager->flush();
        return new Response(null, 204);
    }

    /**
     * @Route("/muestra/seccion/{id}")
     * @param Section $section
     * @param EntradaRepository $entradaRepository
     * @return Response
     * @throws QueryException
     */
    public function mostrarSection(Section $section, EntradaRepository $entradaRepository): Response
    {

        $entradas = $entradaRepository->findAllEntradasBySeccion($section->getId());

        $twig = $section->getModelTemplate().".html.twig";
        return $this->render('sections/'.$twig,[
           'entradas' => $entradas,
            'section' => $section
        ]);
    }

    /**
     * @Route("/{id}", name="admin_section_show", methods={"GET"})
     * @param Section $section
     * @return Response
     */
    public function show(Section $section): Response
    {
        return $this->render('section_admin/show.html.twig', [
            'section' => $section,
        ]);
    }


}
