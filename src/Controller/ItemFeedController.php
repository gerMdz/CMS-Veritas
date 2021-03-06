<?php

namespace App\Controller;

use App\Entity\ItemFeed;
use App\Form\ItemFeedType;
use App\Repository\ItemFeedRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/itemfeed")
 */
class ItemFeedController extends AbstractController
{
    /**
     * @Route("/", name="item_feed_index", methods={"GET"})
     */
    public function index(ItemFeedRepository $itemFeedRepository): Response
    {
        return $this->render('item_feed/index.html.twig', [
            'item_feeds' => $itemFeedRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="item_feed_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $itemFeed = new ItemFeed();
        $form = $this->createForm(ItemFeedType::class, $itemFeed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($itemFeed);
            $entityManager->flush();

            return $this->redirectToRoute('item_feed_index');
        }

        return $this->render('item_feed/new.html.twig', [
            'item_feed' => $itemFeed,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="item_feed_show", methods={"GET"})
     */
    public function show(ItemFeed $itemFeed): Response
    {
        return $this->render('item_feed/show.html.twig', [
            'item_feed' => $itemFeed,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="item_feed_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, ItemFeed $itemFeed): Response
    {
        $form = $this->createForm(ItemFeedType::class, $itemFeed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('item_feed_index');
        }

        return $this->render('item_feed/edit.html.twig', [
            'item_feed' => $itemFeed,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="item_feed_delete", methods={"DELETE"})
     */
    public function delete(Request $request, ItemFeed $itemFeed): Response
    {
        if ($this->isCsrfTokenValid('delete'.$itemFeed->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($itemFeed);
            $entityManager->flush();
        }

        return $this->redirectToRoute('item_feed_index');
    }
}
