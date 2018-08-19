<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Author;
use App\Form\AuthorFormType;
use App\Entity\BlogPost;
use App\Form\EntryFormType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * @Route("/admin")
 */
class AdminController extends Controller
{

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $authorRepository;

    /** @var \Doctrine\Common\Persistence\ObjectRepository */
    private $blogPostRepository;

    /**
     * @param EntityManagerInterface $entityManager
     */

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->blogPostRepository = $entityManager->getRepository('App:BlogPost');
        $this->authorRepository = $entityManager->getRepository('App:Author');
    }


    /**
     * @Route("/author/create", name="author_create")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAuthorAction(Request $request, UserPasswordEncoderInterface $encoder)
    {

            $author = new Author();
            $form = $this->createForm(AuthorFormType::class, $author,[ ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $hash = $encoder->encodePassword($author, $author->getPlainPassword());


                $author->setPassword($hash);

                $this->entityManager->persist($author);
                $this->entityManager->flush($author);


                //$request->getSession()->set('user_is_author', true);
                $this->addFlash('success', 'Congratulations! You are now an author.');

                return $this->redirectToRoute('homepage');
            }

            return $this->render('admin/create_author.html.twig', [
                'form' => $form->createView()
            ]);
        }


    /**
     * @Route("/create-entry", name="admin_create_entry")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createEntryAction(Request $request)
    {
        $blogPost = new BlogPost();
    $author = $this->authorRepository->find(2);
        //$author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
        //$blogPost->setAuthor($author);
        $blogPost->setAuthor($author);
        $form = $this->createForm(EntryFormType::class, $blogPost);
        $form->handleRequest($request);

        // Check is valid
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($blogPost);
            $this->entityManager->flush($blogPost);

            $this->addFlash('success', 'Congratulations! Your post is created');

            return $this->redirectToRoute('admin_entries');
        }

        return $this->render('admin/entry_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/", name="admin_index")
     * @Route("/entries", name="admin_entries")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function entriesAction()
    {
        //$author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
$blogPosts= $this->blogPostRepository->findBy(['author'=>$this->authorRepository->find(2)]);
        //$blogPosts = [];

   /*     if ($author) {
            $blogPosts = $this->blogPostRepository->findByAuthor($author);
        }*/

        return $this->render('admin/entries.html.twig', [
            'blogPosts' => $blogPosts
        ]);
    }

    /**
     * @Route("/delete-entry/{entryId}", name="admin_delete_entry")
     *
     * @param $entryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteEntryAction($entryId)
    {
        $blogPost = $this->blogPostRepository->findOneById($entryId);
        //$author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());

/*        if (!$blogPost || $author !== $blogPost->getAuthor()) {
            $this->addFlash('error', 'Unable to remove entry!');

            return $this->redirectToRoute('admin_entries');
        }*/

        $this->entityManager->remove($blogPost);
        $this->entityManager->flush();

        $this->addFlash('success', 'Entry was deleted!');

        return $this->redirectToRoute('admin_entries');
    }

    /**
     * @Route("/edit-entry/{slug}", name="admin_edit_entry")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editEntryAction(BlogPost $blogPost, Request $request)
    {

        $author = $this->authorRepository->find(2);
        //$author = $this->authorRepository->findOneByUsername($this->getUser()->getUserName());
        //$blogPost->setAuthor($author);
        $blogPost->setAuthor($author);
        $form = $this->createForm(EntryFormType::class, $blogPost);
        $form->handleRequest($request);

        // Check is valid
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($blogPost);
            $this->entityManager->flush($blogPost);

            $this->addFlash('success', 'Congratulations! Your post is created');

            return $this->redirectToRoute('admin_entries');
        }

        return $this->render('admin/entry_edit_form.html.twig', [
            'form' => $form->createView(),
            'blogPost'=>$blogPost
        ]);
    }

    }
