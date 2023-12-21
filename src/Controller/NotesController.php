<?php
 
namespace App\Controller;
 
use App\Entity\Notes;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\NotesRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
 
/**
 * @Route("/notes")
 */
class NotesController extends AbstractController
{
    /**
     * @Route("/notes", name="notes", methods={"GET"})
     */
    public function index(NotesRepository $notesRepository, CategoryRepository $categoryRepository, SessionInterface $session): Response
    {
        if(!empty($session->get('user_auth'))) {
            
            $category = $categoryRepository->findAll();
            
            return $this->render('notes/index.html.twig', [
                'notes' => $notesRepository->findBy(array('user_id' => $session->get('user_auth') )),
                'category' => $category
            ]);
        } else {
            return $this->redirectToRoute('login');
        }
    }

    /**
     * @Route("/notes_list", name="notes_list", methods={"GET"})
     */
    public function notes_list(NotesRepository $notesRepository,ManagerRegistry $doctrine, SessionInterface $session): Response
    {
        if(!empty($session->get('user_auth'))) {
            $data = $notesRepository->findByFields($_GET, $session->get('user_auth'));

            $contents = $this->renderView('notes/list.html.twig', [
                'notes' => $data
            ]);
            return new Response($contents);
        } else {
            return new Response('');
        }
    }
    
 
    /**
     * @Route("/new", name="notes_new", methods={"GET","POST"})
     */
    public function new(Request $request, CategoryRepository $categoryRepository, ManagerRegistry $doctrine, SessionInterface $session): Response
    {
        $notes = new Notes();
        $category = $categoryRepository->findAll();

        if (!empty($_POST)) {
            $data = json_decode($request->getContent(), true);
            $new = new Notes();
            $entityManager = $doctrine->getManager();
            $category = $categoryRepository->find($_POST['category']);
            $new
            ->setTitle($_POST['title'])
            ->setContent($_POST['content'])
            ->setStatus($_POST['status'])
            ->setUserId($session->get('user_auth'))
            ->setCategory($category);
            $entityManager->persist($new);
            $entityManager->flush();
            return $this->redirectToRoute('notes', [], Response::HTTP_SEE_OTHER);
        }
 
        return $this->renderForm('notes/new.html.twig', [
            'note' => $notes,
            'category' => $category
        ]);
    }
 
    /**
     * @Route("/{id}/edit", name="notes_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, NotesRepository $notesRepository,CategoryRepository $categoryRepository, ManagerRegistry $doctrine): Response
    {
        if ($request->GET('id')) {
            
            $entityManager = $doctrine->getManager();
            $notes = $notesRepository->find($request->GET('id'));
            $category = $categoryRepository->findAll();

            if (!empty($_POST)) {
                $data = json_decode($request->getContent(), true);
                $entityManager = $doctrine->getManager();
                $category = $categoryRepository->find($_POST['category']);
                $notes
                ->setTitle($_POST['title'])
                ->setContent($_POST['content'])
                ->setStatus($_POST['status'])
                ->setCategory($category);
                $entityManager->persist($notes);
                $entityManager->flush();
                return $this->redirectToRoute('notes', [], Response::HTTP_SEE_OTHER);
            }
    
            return $this->renderForm('notes/edit.html.twig', [
                'note' => $notes,
                'category' => $category
            ]);
        } else {
            return $this->redirectToRoute('notes', [], Response::HTTP_SEE_OTHER);
        }
    }
 
    /**
     * @Route("/delete/{id}", name="notes_delete", methods={"POST"})
     */
    public function delete(Request $request, NotesRepository $notesRepository, ManagerRegistry $doctrine): Response
    {
        if ($request->GET('id')) {
            $entityManager = $doctrine->getManager();
            $note = $notesRepository->find($request->GET('id'));
            $entityManager->remove($note);
            $entityManager->flush();
            return $this->redirectToRoute('notes', [], Response::HTTP_SEE_OTHER);
        } else {
            return $this->redirectToRoute('notes', [], Response::HTTP_SEE_OTHER);
        }
 
    }
}