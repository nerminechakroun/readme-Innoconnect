<?php
class Financement{
    private $id_financement;
    private $montant;
    private $typeOperation;
    private $titre;
    private $date_operation;
    private $id_contrat;
    private $id_Projet;
    private $errors = [];


    public function __construct( ) {
        $this->montant = null;
        $this->typeOperation = null;
        $this->titre = null;
        $this->date_operation = null;
        $this->id_contrat = null;
        $this->id_Projet = null;
    }

    public function getIdFinancement() {
        return $this->id_financement;
    }

    public function getMontant() {
        return $this->montant;
    }

    public function getDateOperation() {
        return $this->date_operation;
    }

    public function getIdContrat() {
        return $this->id_contrat;
    }

    public function getIdProjet() {
        return $this->id_Projet;
    }
    public function getTypeOperation() {
        return $this->typeOperation;
    }
    public function getTitre() {
        return $this->titre;
    }
    public function setIdFinancement($id_financement) {
        $this->id_financement = $id_financement;
    }
    public function setMontant($montant) {
        $this->montant = $montant;
    }
    public function setDateOperation($date_operation) {
        $this->date_operation = $date_operation;
    }
    public function setIdContrat($id_contrat) {
        $this->id_contrat = $id_contrat;
    }
    public function setIdProjet($id_Projet) {
        $this->id_Projet = $id_Projet;
    }
    public function setTypeOperation($typeOperation) {
        $this->typeOperation = $typeOperation;
    }
    public function setTitre($titre) {
        $this->titre = $titre;
    }

    // Nouvelle méthode pour la validation
    public function validate() {
        $this->errors = [];
        
        // Validation du montant
        if (empty($this->montant)) {
            $this->errors['montant'] = "Le montant est obligatoire";
        } elseif (!is_numeric($this->montant) || $this->montant <= 0) {
            $this->errors['montant'] = "Le montant doit être un nombre positif";
        }
        
        // Validation du type d'opération
        if (empty($this->typeOperation)) {
            $this->errors['typeOperation'] = "Le type d'opération est obligatoire";
        } elseif ($this->typeOperation !== 'encaissement' && $this->typeOperation !== 'decaissement'&& $this->typeOperation !== 'tresorerie finale' ) {
            $this->errors['typeOperation'] = "Le type d'opération doit être 'encaissement' ou 'decaissement'ou 'tresorerie finale'";
        }
        
        // Validation du titre
        if (empty($this->titre)) {
            $this->errors['titre'] = "Le titre est obligatoire";
        } elseif (strlen($this->titre) < 2 || strlen($this->titre) > 100) {
            $this->errors['titre'] = "Le titre doit contenir entre 2 et 100 caractères";
        }
        
        // Validation de la date
        if (empty($this->date_operation)) {
            $this->errors['date_operation'] = "La date est obligatoire";
        } elseif (!strtotime($this->date_operation)) {
            $this->errors['date_operation'] = "Format de date invalide";
        }
        
        // Validation des IDs
        if (empty($this->id_contrat)) {
            $this->errors['id_contrat'] = "L'ID du contrat est obligatoire";
        } elseif (!is_numeric($this->id_contrat)) {
            $this->errors['id_contrat'] = "L'ID du contrat doit être un nombre";
        }
        
        if (empty($this->id_Projet)) {
            $this->errors['id_Projet'] = "L'ID du projet est obligatoire";
        } elseif (!is_numeric($this->id_Projet)) {
            $this->errors['id_Projet'] = "L'ID du projet doit être un nombre";
        }
        
        // Retourne true si pas d'erreurs, false sinon
        return empty($this->errors);
    }
    
    // Récupérer les erreurs
    public function getErrors() {
        return $this->errors;
    }
}
?>