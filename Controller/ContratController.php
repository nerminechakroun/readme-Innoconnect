<?php
require_once __DIR__ . "/../config.php";
require_once '../../Model/Contrat.php';

class ContratController {
    // Ajouter un nouveau contrat
    public function addContrat(Contrat $contrat) {
        try {
            // Requête SQL paramétrée
            $sql = "INSERT INTO contrat (
                innovateur_id, innovateur_email, innovateur_nom, innovateur_signature,
                investisseur_id, investisseur_email, investisseur_nom, investisseur_signature,
                projet_nom, type_financement, montant, date_signature, statut
            ) VALUES (
                :i_id, :i_email, :i_nom, :i_sig,
                :inv_id, :inv_email, :inv_nom, :inv_sig,
                :projet, :type, :montant, :date_sig, :statut
            )";

            $db = config::getConnexion();
            $query = $db->prepare($sql);
            
            $query->execute([
                'i_id' => $contrat->getInnovateurId(),
                'i_email' => $contrat->getInnovateurEmail(),
                'i_nom' => $contrat->getInnovateurNom(),
                'i_sig' => $contrat->getInnovateurSignature(),
                'inv_id' => $contrat->getInvestisseurId(),
                'inv_email' => $contrat->getInvestisseurEmail(),
                'inv_nom' => $contrat->getInvestisseurNom(),
                'inv_sig' => $contrat->getInvestisseurSignature(),
                'projet' => $contrat->getProjetNom(),
                'type' => $contrat->getTypeFinancement(),
                'montant' => $contrat->getMontant(),
                'date_sig' => $contrat->getDateSignature(),
                'statut' => $contrat->getStatut()
            ]);

            return $db->lastInsertId();

        } catch (PDOException $e) {
            throw new Exception("Erreur base de données : " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Erreur métier : " . $e->getMessage());
        }
    }

    public function updateContrat(Contrat $contrat, int $idContrat) {
        try {
            $sql = "UPDATE contrat SET
                innovateur_id = :i_id,
                innovateur_email = :i_email,
                innovateur_nom = :i_nom,
                innovateur_signature = :i_sig,
                investisseur_id = :inv_id,
                investisseur_email = :inv_email,
                investisseur_nom = :inv_nom,
                investisseur_signature = :inv_sig,
                projet_nom = :projet,
                type_financement = :type,
                montant = :montant,
                date_signature = :date_sig,
                statut = :statut
            WHERE id_contrat = :id";
    
            $db = config::getConnexion();
            $query = $db->prepare($sql);
    
            // Bind all parameters
            $query->execute([
                'i_id' => $contrat->getInnovateurId(),
                'i_email' => $contrat->getInnovateurEmail(),
                'i_nom' => $contrat->getInnovateurNom(),
                'i_sig' => $contrat->getInnovateurSignature(), // Add this line if needed
                'inv_id' => $contrat->getInvestisseurId(),
                'inv_email' => $contrat->getInvestisseurEmail(),
                'inv_nom' => $contrat->getInvestisseurNom(),
                'inv_sig' => $contrat->getInvestisseurSignature(), // Add this line if needed
                'projet' => $contrat->getProjetNom(),
                'type' => $contrat->getTypeFinancement(),
                'montant' => $contrat->getMontant(),
                'date_sig' => $contrat->getDateSignature(),
                'statut' => $contrat->getStatut(),
                'id' => $idContrat,
            ]);
    
            return $query->rowCount() > 0;
    
        } catch (PDOException $e) {
            throw new Exception("Erreur de mise à jour : " . $e->getMessage());
        }
    }

    public function deleteContrat(int $idContrat) {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();

            // Suppression des financements liés
            $query = $db->prepare("DELETE FROM financement WHERE id_contrat = :id");
            $query->execute(['id' => $idContrat]);

            // Suppression du contrat
            $query = $db->prepare("DELETE FROM contrat WHERE id_contrat = :id");
            $query->execute(['id' => $idContrat]);

            $db->commit();
            return true;

        } catch (Exception $e) {
            $db->rollBack();
            throw new Exception("Erreur de suppression : " . $e->getMessage());
        }
    }

    public function getAllContrats() {
        try {
            $db = config::getConnexion();
            $query = $db->query("SELECT * FROM contrat ORDER BY date_signature DESC");
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur de récupération : " . $e->getMessage());
        }
    }

    public function signerContrat(int $idContrat, string $role) {
        try {
            $db = config::getConnexion();
            
            // Récupération du statut actuel
            $current = $this->getContratById($idContrat);
            $nouveauStatut = $this->determinerNouveauStatut($current['statut'], $role);

            // Mise à jour du statut
            $query = $db->prepare("UPDATE contrat SET statut = :statut WHERE id_contrat = :id");
            $query->execute(['id' => $idContrat, 'statut' => $nouveauStatut]);

            return true;

        } catch (Exception $e) {
            throw new Exception("Erreur de signature : " . $e->getMessage());
        }
    }

    // Méthodes privées utilitaires
    private function getContratById(int $idContrat) {
        $db = config::getConnexion();
        $query = $db->prepare("SELECT * FROM contrat WHERE id_contrat = :id");
        $query->execute(['id' => $idContrat]);
        return $query->fetch(PDO::FETCH_ASSOC);
    }

    private function determinerNouveauStatut(string $statutActuel, string $role) {
        $transitions = [
            'en attente' => [
                'innovateur' => 'signé par l\'innovateur',
                'investisseur' => 'signé par l\'investisseur'
            ],
            'signé par l\'innovateur' => [
                'investisseur' => 'validé'
            ],
            'signé par l\'investisseur' => [
                'innovateur' => 'validé'
            ]
        ];

        return $transitions[$statutActuel][$role] ?? $statutActuel;
    }
}
?>