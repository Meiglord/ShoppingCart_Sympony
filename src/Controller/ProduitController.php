<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Panier;
use App\Form\ProduitType;
use App\Form\PanierType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 *  @Route("/{_locale}")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route("/produit", name="produit")
     */
    public function index(Request $request,  TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $fichier = $form->get('photoUpload')->getData();
            if ($fichier) {
                $nomFichier = uniqid() . '.' . $fichier->guessExtension();

                try {
                    $fichier->move(
                        $this->getParameter('upload_dir'),
                        $nomFichier
                    );
                } catch (FileException $e) {
                    $this->addFlash("danger", $translator->trans('fichier.erreur'));
                    return $this->redirectToRoute('home');
                }
                $produit->setPhoto($nomFichier);
            }
            $this->addFlash("success", $translator->trans('flash.produit.cree'));

            $em->persist($produit);
            $em->flush();
        }

        $produits = $em->getRepository(Produit::class)->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'form_ajout_produit' => $form->createView()
        ]);
    }

    /**
     * @Route("/produit/{id}", name="un_produit")
     */
    public function produit(Request $request, Produit $produit = null, TranslatorInterface $translator)
    {
        if ($produit != null) {
            $panier = new Panier($produit);
            $form = $this->createForm(PanierType::class, $panier);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($panier->getQuantite() <= $produit->getQuantite()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($panier);
                    $em->flush();
                    $this->addFlash('success', $translator->trans('flash.produit.ajoutpanier'));
                } else {
                    $this->addFlash('danger', $translator->trans('flash.produit.stock'));
                }
            }

            return $this->render('produit/produit.html.twig', [
                'produit' => $produit,
                'form_ajout_panier' => $form->createView()
            ]);
        } else {
            return $this->redirectToRoute('produit');
        }
    }

    /**
     * @Route("/produit/delete/{id}", name="delete_produit")
     */
    public function delete(Produit $produit = null, TranslatorInterface $translator)
    {
        if ($produit != null) {
            if ($produit->getPhoto() != null) {
                unlink($this->getParameter('upload_dir') . $produit->getPhoto());
            }
            $em = $this->getDoctrine()->getManager();
            $em->remove($produit);
            $em->flush();
            $this->addFlash('success', $translator->trans('flash.produit.supprimer'));
        }
        return $this->redirectToRoute('produit');
    }
}
