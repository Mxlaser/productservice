<?php
// src/Controller/ProductController.php
namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'product_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $products = $entityManager->getRepository(Product::class)->findAll();
        $data = [];

        foreach ($products as $product) {
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'price' => $product->getPrice(),
                'available' => $product->getAvailable(),
                'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/products', name: 'product_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isAdmin()) {
            return new JsonResponse(['error' => 'Access denied'], JsonResponse::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || empty($data['description']) || empty($data['price']) || !isset($data['available'])) {
            return new JsonResponse(['error' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description']);
        $product->setPrice($data['price']);
        $product->setAvailable($data['available']);
        $product->setCreatedAt(new \DateTime());

        $entityManager->persist($product);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Product created!'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/products/{id}', name: 'product_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'available' => $product->getAvailable(),
            'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s')
        ];

        return new JsonResponse($data);
    }

    #[Route('/products/{id}', name: 'product_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isAdmin()) {
            return new JsonResponse(['error' => 'Access denied'], JsonResponse::HTTP_FORBIDDEN);
        }

        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!empty($data['name'])) {
            $product->setName($data['name']);
        }
        if (!empty($data['description'])) {
            $product->setDescription($data['description']);
        }
        if (!empty($data['price'])) {
            $product->setPrice($data['price']);
        }
        if (isset($data['available'])) {
            $product->setAvailable($data['available']);
        }

        $entityManager->flush();

        return new JsonResponse(['status' => 'Product updated!']);
    }

    #[Route('/products/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isAdmin()) {
            return new JsonResponse(['error' => 'Access denied'], JsonResponse::HTTP_FORBIDDEN);
        }

        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return new JsonResponse(['error' => 'Product not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Product deleted!']);
    }

    private function isAdmin(): bool
    {
        // Simulation de vérification du rôle admin (remplacez par l'intégration avec UserService)
        return true;  // Remplacez cette ligne avec une vraie vérification d'authentification
    }
}
