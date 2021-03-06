<?php


namespace App\Controller;


use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminUtilityController extends AbstractController
{

    /**
     * @Route("/admin/utility/user", methods={"GET"}, name="admin_utility_user")
     * @param UserRepository $userRepository
     * @param Request $request
     * @return JsonResponse
     * @IsGranted("ROLE_ESCRITOR")
     */
    public function getUserEscritorApi(UserRepository $userRepository, Request $request): JsonResponse
    {
        $role = empty($request->query->get('role'))?'ROLE_NADA' : $request->query->get('role') ;
        $query = $request->query->get('query');

        $user = $userRepository->findAllEmailsRoleAlfa($role, $query);


        return $this->json(['users'=>$user], 200, [], [
            'groups' => ['perfil'],
        ]);
    }

    /**
     * @Route("/admin/list/user", methods={"GET"}, name="admin_list_user")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function usersList(UserRepository $userRepository): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepository->findBy([],['primerNombre'=>'ASC']),
        ]);
    }


}