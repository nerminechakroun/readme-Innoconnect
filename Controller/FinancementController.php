<?php
require_once __DIR__ . "/../config.php";
class FinancementController
{
    public function addFinancement($financement)
    {

        $sql = "INSERT INTO financement (montant, typeOperation, titre, date_operation, id_contrat, id_Projet)
SELECT
  :montant,
  :typeOperation,
  :titre,
  :date_operation,
  c.id_contrat,
  :id_Projet
FROM contrat c
WHERE c.id_contrat = :id_contrat;";

        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'montant'        => $financement->getMontant(),
                'typeOperation'  => $financement->getTypeOperation(),
                'titre'          => $financement->getTitre(),
                'date_operation' => $financement->getDateOperation(),
                'id_contrat'     => $financement->getIdContrat(),
                'id_Projet'      => $financement->getIdProjet(),
            ]);
            return true;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function updateFinancement($financement, $id_financement)
    {
        // Valider avant de mettre à jour
        if (! $financement->validate()) {
            return false; // Échec de validation
        }

        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'UPDATE `financement` SET
                    montant = :montant,
                    typeOperation = :typeOperation,
                    titre = :titre,
                    date_operation = :date_operation,
                    id_contrat = :id_contrat,
                    id_Projet = :id_Projet
                WHERE id_financement = :id_financement'
            );
            $query->execute([
                'id_financement' => $id_financement,
                'montant'        => $financement->getMontant(),
                'typeOperation'  => $financement->getTypeOperation(),
                'titre'          => $financement->getTitre(),
                'date_operation' => $financement->getDateOperation(),
                'id_contrat'     => $financement->getIdContrat(),
                'id_Projet'      => $financement->getIdProjet(),
            ]);
            return true;
        } catch (PDOException $e) {
            echo 'Error: ' . $e->getMessage();
            return false;
        }
    }

    public function deleteFinancement($id_financement)
    {
        $db = config::getConnexion();
        try {
            // Begin a transaction
            $db->beginTransaction();

            // Delete the Financement record
            $sqlDeleteFinancement  = "DELETE FROM financement WHERE id_financement = :id_financement";
            $stmtDeleteFinancement = $db->prepare($sqlDeleteFinancement);
            $stmtDeleteFinancement->bindValue(':id_financement', $id_financement);
            $stmtDeleteFinancement->execute();

            // Commit the transaction if everything was successful
            $db->commit();

            return true;
        } catch (Exception $e) {
            // Rollback the transaction if an error occurred
            $db->rollBack();
            die('Error:' . $e->getMessage());
        }
    }
    public function listFinancement()
    {
        $sql = "SELECT * FROM financement;";
        $db  = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function showFinancement($id_financement)
    {
        $sql = "SELECT * FROM `financement` WHERE id_financement = :id_financement";
        $db  = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id_financement' => $id_financement]);

            $financement = $query->fetch();
            return $financement;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
    private function validateFinancement($financement, $isNew)
    {
        $errors = [];

        // Validation du montant
        $montant = $financement->getMontant();
        if (! is_numeric($montant) || $montant <= 0) {
            $errors[] = "Le montant doit être un nombre positif";
        }

        // Validation du type d'opération
        $typeOperation = $financement->getTypeOperation();
        $allowedTypes  = ['encaissement', 'decaissement'];
        if (! in_array($typeOperation, $allowedTypes)) {
            $errors[] = "Type d'opération invalide";
        }

        // Validation du titre
        $titre = $financement->getTitre();
        if (empty($titre)) {
            $errors[] = "Le titre est obligatoire";
        } elseif (strlen($titre) > 100) {
            $errors[] = "Le titre ne doit pas dépasser 100 caractères";
        }

        // Validation de la date
        $date = $financement->getDateOperation();
        if (empty($date)) {
            $errors[] = "La date est obligatoire";
        } else {
            $currentDate = date('Y-m-d');
            if ($date > $currentDate) {
                $errors[] = "La date ne peut pas être dans le futur";
            }
        }

        // Validation des relations
        if (! $this->checkIdExists('contrat', 'id_contrat', $financement->getIdContrat())) {
            $errors[] = "Le contrat sélectionné n'existe pas";
        }

        if (! $this->checkIdExists('projet', 'id_Projet', $financement->getIdProjet())) {
            $errors[] = "Le projet sélectionné n'existe pas";
        }

        return $errors;
    }
    private function checkIdExists($table, $column, $id)
    {
        $db    = config::getConnexion();
        $sql   = "SELECT $column FROM $table WHERE $column = :id";
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
        return $query->rowCount() > 0;
    }

}
