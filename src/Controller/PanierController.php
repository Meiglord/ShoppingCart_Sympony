<?php

namespace App\Controller;

use App\Entity\Panier;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 *  @Route("/{_locale}")
 */
class PanierController extends AbstractController
{
    /**
     * @Route("/", name="panier")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $panier = $em->getRepository(Panier::class)->findAll();

        return $this->render('panier/index.html.twig', [
            'panier' => $panier,
            'controller_name' => 'PanierController',
        ]);
    }

    /**
     * @Route("/panier/delete/{id}", name="delete_produit_from_panier")
     */
    public function delete(Panier $produit = null, TranslatorInterface $translator)
    {
        if ($produit != null) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($produit);
            $em->flush();
            $this->addFlash("success", $translator->trans('flash.panier.supprimer'));
        } else {
            $this->addFlash("danger", $translator->trans('flash.panier.passupprimer'));
        }
        return $this->redirectToRoute('panier');
    }


    /**
     * @Route("/panier/acheter", name="panier_acheter")
     */
    public function panierAcheter(Panier $produit = null, TranslatorInterface $translator)
    {
        if ($produit != null) {
            $em = $this->getDoctrine()->getManager();
            $produit->setEtat(true);
            $em->persist($produit);
            $em->flush();
            $this->addFlash("success", $translator->trans('flash.panier.acheter'));
        } else {
            $this->addFlash("danger", $translator->trans('flash.panier.pasacheter'));
        }
        return $this->redirectToRoute('panier');
    }
}
