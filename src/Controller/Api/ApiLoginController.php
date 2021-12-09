<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/login')]
class ApiLoginController extends AbstractController
{
    #[Route('/', name: 'api_login', methods: ['POST'])]
    public function loginAction(#[CurrentUser] ?User $user, EntityManagerInterface $em): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $em->getRepository(User::class)->generateUniqueToken(); // somehow create an API token for $user
        $user->setToken($token);
        $em->persist($user);
        $em->flush();

        return $this->json([
            'user'  => $user->getUserIdentifier(),
            'token' => $token,
        ]);
    }
}
