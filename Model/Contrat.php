<?php
class Contrat {
    private $id_contrat;
    private $innovateur_id;
    private $innovateur_email;
    private $innovateur_nom;
    private $innovateur_signature;
    private $investisseur_id;
    private $investisseur_email;
    private $investisseur_nom;
    private $investisseur_signature;
    private $projet_nom;
    private $type_financement;
    private $montant;
    private $date_signature;    
    private $statut;
    private $errors = [];

    public function __construct(
        $innovateur_id = null,
        $innovateur_email = null,
        $innovateur_nom = null,
        $innovateur_signature = null,
        $investisseur_id = null,
        $investisseur_email = null,
        $investisseur_nom = null,
        $investisseur_signature = null,
        $projet_nom = null,
        $type_financement = null,
        $montant = null,
        $date_signature = null,
        $statut = 'en attente'
    ) {
        $this->innovateur_id = $innovateur_id;
        $this->innovateur_email = $innovateur_email;
        $this->innovateur_nom = $innovateur_nom;
        $this->innovateur_signature = $innovateur_signature;
        $this->investisseur_id = $investisseur_id;
        $this->investisseur_email = $investisseur_email;
        $this->investisseur_nom = $investisseur_nom;
        $this->investisseur_signature = $investisseur_signature;
        $this->projet_nom = $projet_nom;
        $this->type_financement = $type_financement;
        $this->montant = $montant;
        $this->date_signature = $date_signature;
        $this->statut = $statut;
    }

    // Getters
    public function getIdContrat() { return $this->id_contrat; }
    public function getInnovateurId() { return $this->innovateur_id; }
    public function getInnovateurEmail() { return $this->innovateur_email; }
    public function getInnovateurNom() { return $this->innovateur_nom; }
    public function getInnovateurSignature() { return $this->innovateur_signature; }
    public function getInvestisseurId() { return $this->investisseur_id; }
    public function getInvestisseurEmail() { return $this->investisseur_email; }
    public function getInvestisseurNom() { return $this->investisseur_nom; }
    public function getInvestisseurSignature() { return $this->investisseur_signature; }
    public function getProjetNom() { return $this->projet_nom; }
    public function getTypeFinancement() { return $this->type_financement; }
    public function getMontant() { return $this->montant; }
    public function getDateSignature() { return $this->date_signature; }
    public function getStatut() { return $this->statut; }
    public function getErrors() { return $this->errors; }

    // Setters
    public function setIdContrat($id) { $this->id_contrat = $id; }
    public function setInnovateurId($id) { $this->innovateur_id = $id; }
    public function setInnovateurEmail($email) { $this->innovateur_email = $email; }
    public function setInnovateurNom($nom) { $this->innovateur_nom = $nom; }
    public function setInnovateurSignature($signature) { $this->innovateur_signature = $signature; }
    public function setInvestisseurId($id) { $this->investisseur_id = $id; }
    public function setInvestisseurEmail($email) { $this->investisseur_email = $email; }
    public function setInvestisseurNom($nom) { $this->investisseur_nom = $nom; }
    public function setInvestisseurSignature($signature) { $this->investisseur_signature = $signature; }
    public function setProjetNom($nom) { $this->projet_nom = $nom; }
    public function setTypeFinancement($type) { $this->type_financement = $type; }
    public function setMontant($montant) { $this->montant = $montant; }
    public function setDateSignature($date) { $this->date_signature = $date; }
    public function setStatut($statut) { $this->statut = $statut; }

}
?>
