<?php

class Scolarite_modele extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    var $skey = "SuPerEncKey2010"; // you can change it

    /*
     * fonctions pour encodage et decodage pour les annees et les semestres dans l url.
     */

    public function safe_b64encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
        return $data;
    }

    public function safe_b64decode($string) {
        $data = str_replace(array('-', '_'), array('+', '/'), $string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    public function encode($value) {

        if (!$value) {
            return false;
        }
        $text = $value;
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->skey, $text, MCRYPT_MODE_ECB, $iv);
        return trim($this->safe_b64encode($crypttext));
    }

    public function decode($value) {

        if (!$value) {
            return false;
        }
        $crypttext = $this->safe_b64decode($value);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->skey, $crypttext, MCRYPT_MODE_ECB, $iv);
        return trim($decrypttext);
    }

    function getDecisionM($decision) {
        $this->db->where('idDecision', $decision);
        $query = $this->db->get('decisionbulletin');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                return $row['decisionM'];
            }
        }
    }

    function get_last_matricule() {
        $this->db->select_max('matriculeEtudiant');
        $query = $this->db->get('etudiant');
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $matricule = $row->matriculeEtudiant;
            }
        }
        if (intval($matricule) < 100000)
            $matricule = 100000;
        return $matricule;
    }

    function get_last_matriculeIUP() {
        $sessionCourante = $this->get_session_courante();
        $anneeUniv = $sessionCourante['annee_univ'][0];
        /*
          $query = $this->db->query("SELECT Distinct `idEtablissement`, `nom` FROM
          `etablissement` ORDER BY `nom`");
          if ($query->num_rows() > 0)
          {
          foreach ($query->result_array() as $row)
          {
          $etablissement['idEtablissement'][] = $row['idEtablissement'];
          $etablissement['nomEtablissement'][] = $row['nom'];
          }
          }
         */
        /* $this->db->select_max('matriculeEtudiant');
          $query = $this->db->get('etudiant'); */
        $query = $this->db->query("Select max(matriculeEtudiant) as maxMat from etudiant where annee_inscription=" . $anneeUniv);

        if ($query->num_rows() > 0) {
            $row = $query->row_array();

            if ($row['maxMat'] != 0)
                $matricule = $row['maxMat'] + 1;
            else { // Insertion du premier etudiant de l'an�e en cours
                //echo "premier etudiant";
                $a = $anneeUniv % 100;
                $matricule = $a * 1000 + 1;
            }
        } else { // Insertion du premier etudiant de l'an�e en cours
            $a = $anneeUniv % 100;
            $matricule = $a * 1000 + 1;
        }
        /* if (intval($matricule) < 100000)
          $matricule = 100000; */
        return $matricule;
    }

    /*
     * 
     * Alfa 09-02-2016
     * Generation des codes anonymats 
     */

    // get_session_courante()

    function generer_code_anonymat() {
        $sessionCourante = $this->get_session_courante();
        $annee = $sessionCourante['annee'][0];
        $semestre = $sessionCourante['semestre'][0];

        $q = $this->db->query("Select count(*) as nb from anonymat where annee=" . $annee . " and semestre=" . $semestre . " and code_ex is not null");
        $res = $q->row_array();

//  Les codes ne sont pas encore g�n�r�s
        if ($res['nb'] == 0) {
            $query = $this->db->query("select matriculeEtudiant from etudiant where actif=1 order by rand()");

            if ($query->num_rows() > 0) {
                $x = -1;
                foreach ($query->result_array() as $row) {
                    $x++;
                    $y = $x;
                    $n = 4 - strlen($y);
                    for ($i = 1; $i <= $n; $i++)
                        $y = '0' . $y;
                    $q = "insert into anonymat values (" . $row['matriculeEtudiant'] . "," . $annee . "," . $semestre . ",'" . $y . "', null) ";
                    $this->db->query($q);
                }
                // generer code anonymat rattrapage

                $query = $this->db->query("select matriculeEtudiant from etudiant where actif=1 order by rand()");
                $x = -1;
                foreach ($query->result_array() as $row) {
                    $x++;
                    $y = $x;
                    $n = 4 - strlen($y);
                    for ($i = 1; $i <= $n; $i++)
                        $y = '0' . $y;
                    $q = "update anonymat set code_rt='" . $y . "'  where matriculeEtudiant=" . $row['matriculeEtudiant'] . " and annee=" . $annee . " and semestre=" . $semestre;
                    $this->db->query($q);
                }


                return 1;
            }
        } else {//echo $q->num_rows().' code deja gener�';
            return 0;
        }
    }

    /*
     * fonction pour enlever les accents
     */

    function stripAccents($string) {
        $accents = array('�', ' ?', '�', '�', '�', '�', '?', '?', '?', '�', '�', '?', '?', '?', '?', '?', ' ?', '�', '�', '�', '�', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '�', ' ?', '�', ' ?', '?', '?', '?', '?', '?', '?', '?', '?', ' ?', '?', '?', '?', '?', '�', '?', '?', '?', '?', '�', '�', '�', '�', '�', '�', '?', ' ?', '?', '�', '?', '?', '?', '?', '�', '?', '?', '?', '?', '?', '?', '?', '�', '�', '�', '�', '?', '?', '?', '?', '?', '?', '?', ' ?', '?', '�', '?', '�', '?', '�', '�', '�', '�', '�', '�', ' ?', '?', '?', '�', '�', '?', ' ?', '?', '?', ' ?', '?', '�', '�', '�', '�', '?', '?', '?', '?', '?', '�', ' ?', '?', '?', '?', '?', '?', '�', '�', '�', '�', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '?', '�', '?', '?', '?', '?', '?', '�', '�', '�', '�', '�', '�', ' ?', '?', ' ?', '�', '?', '?', '?', '?', '�', '?', ' ?', '?', '?', '?', '?', '?', '�', '�', '�', '�', '?', '?', '?', '?', '?', '?', '?', '�', '�', '?', '�', '?', '?', '�', '�', '�', '?', ' ?', '�');
        $letters = array('A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'C', 'C', 'C', 'C', 'D', 'D', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'G', 'G', 'G', 'G', 'H', 'H', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'J', 'J', 'K', 'L', 'L', 'L', 'L', 'L', 'N', 'N', 'N', 'N', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'E', 'R', 'R', 'R', 'S', 'S', 'S', 'S', 'S', 'T', 'T', 'T', 'T', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'W', 'Y', 'Y', 'Y', 'Z', 'Z', 'Z', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'e', 'c', 'c', 'c', 'c', 'c', 'd', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'f', 'g', 'g', 'g', 'g', 'h', 'h', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'j', 'j', 'k', 'k', 'l', 'l', 'l', 'l', 'l', 'n', 'n', 'n', 'n', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'e', 'r', 'r', 'r', 's', 's', 's', 's', 's', 't', 't', 't', 't', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'w', 'y', 'y', 'y', 'z', 'z', 'z', 'T', 't', 'B', 'f', 'D', 'd');
        $string = str_replace($accents, $letters, $string);
        return $string;
    }

    function get_etablissement() {
        $etablissement = NULL;
        $query = $this->db->query("SELECT Distinct `idEtablissement`, `nom`, `regime` FROM 
        `etablissement` ORDER BY `nom`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $etablissement['idEtablissement'][] = $row['idEtablissement'];
                $etablissement['nomEtablissement'][] = $row['nom'];
                $etablissement['regime'][] = $row['regime'];
            }
        }
        return $etablissement;
    }

    function getInfoEmployeModule($module) {
        $infoEmploye = '';
        $this->db->where('sigle', $module);
        $this->db->distinct();
        $this->db->order_by('matriculeEmploye');
        $this->db->order_by('annee');
        $this->db->order_by('semestre');
        $query2 = $this->db->get('groupe');
        if ($query2->num_rows() > 0) {
            foreach ($query2->result_array() as $row2) {
                $this->db->where('matriculeEmploye', $row2['matriculeEmploye']);
                $query = $this->db->get('employe');
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        if ($row['actif'] == 1) {
                            $infoEmploye['matriculeEmploye'][] = $row2['matriculeEmploye'];
                            $infoEmploye['annee'][] = $row2['annee'];
                            $infoEmploye['semestre'][] = $row2['semestre'];
                            $infoEmploye['sigle'][] = $row2['sigle'];
                            $infoEmploye['nom'][] = $row['nom'];
                            $infoEmploye['prenom'][] = $row['prenom'];
                        }
                    }
                }
            }
        }
        return $infoEmploye;
    }

    function getInfoEmploye($idDepartement) {
        $infoEmploye = '';
        $this->db->where(array('idDepartement' => $idDepartement, 'actif' => '1'));
        $this->db->order_by('matriculeEmploye');
        $this->db->distinct();
        $query = $this->db->get('employe');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {

                $infoEmploye['matriculeEmploye'][] = $row['matriculeEmploye'];
                $infoEmploye['login'][] = $row['login'];
                $infoEmploye['nom'][] = $row['nom'];
                $infoEmploye['prenom'][] = $row['prenom'];
            }
        }
        return $infoEmploye;
    }

    /**
     * Fonction qui retourne tous les sigles des cours existants
     * @param Aucun
     * @return Tableau de sigle de cours 
     */
    function recuperer_cours() {
        $sigleCours = NULL;
        $query = $this->db->query("SELECT DISTINCT `sigle` FROM module ORDER BY `sigle`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $sigleCours['sigle'][] = $row['sigle'];
            }
        }
        return $sigleCours;
    }

    /*
     * fonction qui retourne les personnes cles qui vont recevoir 
     * les emails lors de chaque modification des notes par le service de scolarite.
     */

    function get_personnes_cle() {
        $email = '';
        $this->db->where('envoieActif', '1');
        $query = $this->db->get('personneCle');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $email[] = $row['email'];
            }
        }
        return $email;
    }

    /*
     * fonction pour retirer ou motiver une absence 
     * la variable data contient le matricule de l etudiant la date de son absence
     * la periode et le groupe.
     */

    function motivee_retiree_absences($data) {
        $tableauPeriode = array_keys($data['items'], 'on');
        $messageRetour = '';
        $counter = 0; // variable pour les checkbox coche
        if ($data['choixFonction'] == 'motivation') {
            if ($tableauPeriode != Null) {
                for ($i = 0; $i < count($data['date']); $i++) {
                    if (isset($tableauPeriode[$counter]) && $tableauPeriode[$counter] == $i) {
                        $this->db->where(array('matricule' => $data['matriculeEtudiant'][$i], 'idGroupe' => $data['idGroupe'][$i],
                            'date' => $data['date'][$i], 'periode' => $data['periode'][$i],
                            'duree' => $data['duree'][$i]));
                        $query = $this->db->get('absences');
                        $motiveeAbsences = array('absenceMotivee' => 1);
                        if (($query->num_rows() > 0) && ($query->num_rows() < 2)) {
                            foreach ($query->result_array() as $row) {
                                // modif RM 23 f�vreir 2013
                                // $messageRetour .='</br>L\'absence du <b>'.$row['date'].' </b>. Dur�e <b>'.$row['duree'].' '.$row['periode'].' </b>  ';
                                $messageRetour .= '</br>L\'absence du <b>' . $row['date'] . ' ' . $row['periode'] . '</b> de dur�e <b>' . $row['duree'] . ' h ' . ' </b>  ';
                                $messageRetour .= 'de l\'�tudiant <b>' . $data['matriculeEtudiant'][0] .
                                        ' </b> est maintenant motiv�e.<br>';
                            }
                            $this->db->where(array('matricule' => $data['matriculeEtudiant'][$i],
                                'idGroupe' => $data['idGroupe'][$i],
                                'date' => $data['date'][$i], 'periode' => $data['periode'][$i],
                                'duree' => $data['duree'][$i]));
                            $this->db->update('absences', $motiveeAbsences);
                        } elseif ($query->num_rows() >= 2) {
                            $messageRetour .= 'Plusieurs absences identiques existaient pour 
                              l\'�tudiant<b> ' . $data['matriculeEtudiant'][$i] . '</b>
                                  <br> le <b>' .
                                    $data['date'][$i] . ' ' . $data['periode'][$i] . '</b>, 
                                  de dur�e  <b>' .
                                    $data['duree'][$i] . '</b>  h : elles ont toutes �t� motiv�es.<br>';
                            $this->db->where(array('matricule' => $data['matriculeEtudiant'][$i],
                                'idGroupe' => $data['idGroupe'][$i],
                                'date' => $data['date'][$i], 'periode' => $data['periode'][$i],
                                'duree' => $data['duree'][$i]));
                            $this->db->update('absences', $motiveeAbsences);
                        }
                        $counter = $counter + 1;
                    }
                }
            }
        } else {
            if ($tableauPeriode != Null) {
                for ($i = 0; $i < count($data['date']); $i++) {
                    if (isset($tableauPeriode[$counter]) && $tableauPeriode[$counter] == $i) {
                        $this->db->where(array('matricule' => $data['matriculeEtudiant'][$i],
                            'idGroupe' => $data['idGroupe'][$i],
                            'date' => $data['date'][$i], 'periode' => $data['periode'][$i],
                            'duree' => $data['duree'][$i]));
                        $query = $this->db->get('absences');
                        if (($query->num_rows() > 0) && ($query->num_rows() < 2)) {
                            foreach ($query->result_array() as $row) {
                                // Correction RM 23 f�vrier 2013 $messageRetour .='</br>L\'absence du <b>'.$row['date'].' 
                                //     </b>. Dur�e <b>'.$row['duree'].' '. $row['periode'].' </b>  ';
                                $messageRetour .= '</br>L\'absence du <b>' . $row['date'] . ' ' . $row['periode'] . '</b> de dur�e <b>' . $row['duree'] . ' h ' . ' </b>  ';
                                $messageRetour .= 'de l\'�tudiant <b>' .
                                        $data['matriculeEtudiant'][0] . ' </b> est maintenant retir�e.<br>';
                            }
                            $this->db->where(array('matricule' => $data['matriculeEtudiant'][$i],
                                'idGroupe' => $data['idGroupe'][$i],
                                'date' => $data['date'][$i], 'periode' => $data['periode'][$i],
                                'duree' => $data['duree'][$i]));
                            $this->db->delete('absences');
                        } elseif ($query->num_rows() >= 2) {
                            $messageRetour .= 'Plusieurs absences identiques existaient pour 
                              l\'�tudiant<b> ' . $data['matriculeEtudiant'][$i] . '</b> <br> le <b>' .
                                    $data['date'][$i] . ' ' . $data['periode'][$i] . '</b>, de dur�e  <b>' .
                                    $data['duree'][$i] . '</b>  h : elles ont toutes �t� supprim�es.<br>';
                            $this->db->where(array('matricule' => $data['matriculeEtudiant'][$i],
                                'idGroupe' => $data['idGroupe'][$i],
                                'date' => $data['date'][$i], 'periode' => $data['periode'][$i],
                                'duree' => $data['duree'][$i]));
                            $this->db->delete('absences');
                        }

                        $counter = $counter + 1;
                    }
                }
            }
        }
        return $messageRetour;
    }

    /**
     * fonction qui retourne toute la liste des etudiants inscrits dans la table dossier etudiant 
     * @param Aucun
     * @return liste des matricules 
     */
    function get_etudiant_dossierEtudiant($annee, $semestre) {
        $matricules = '';
        $this->db->where(array('annee' => $annee, 'semestre' => $semestre));
        $this->db->distinct();
        $this->db->join('matriculeEtudiant', 'dossieretudiant.matriculeEtudiant =
            planetudes.matriculeEtudiant');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $matricules[] = $row['matriculeEtudiant'];
            }
        }
        return $matricules;
    }

    function generer_matricule() {
        $matricule = $this->get_last_matricule();
        do {
            $matricule += 1;
            if ($matricule > 999999)
                die("Une erreur est survenue dans la g�n�ration du matricule ?!");
            $valid = $this->valider_matricule($matricule);
        }
        while (!$valid);
        return $matricule;
    }

    function generer_matriculeIUP() {
        $matricule = $this->get_last_matriculeIUP();

        return $matricule;
    }

    /*
     * fonction qui retourne le nom du programme
     */

    function get_programme_nom($idProgramme) {
        $this->db->where('idProgramme', $idProgramme);
        $query = $this->db->get('programme');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $row['nom'];
        }
    }

    /*
     * fonction qui retourne le nom du departement
     */

    function get_departement_nom($idDepartement) {
        $this->db->where('idDepartement', $idDepartement);
        $query = $this->db->get('departement');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $row['nom'];
        }
    }

    /*
     * fonction qui retourne le nom et le prenom de l etudiant 
     * et il doit etre actif
     */

    function get_nom_prenom_etudiant($tableauMatricule) {
        $data = '';
        for ($i = 0; $i < count($tableauMatricule); $i++) {
            $this->db->where(array('matriculeEtudiant' => $tableauMatricule[$i], 'actif' => '1'));
            $query = $this->db->get('etudiant');
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                $data['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                $data['nom'][] = $row['nom'];
                $data['prenom'][] = $row['prenom'];
            }
        }
        return $data;
    }

    /*
     * recuperation des informations personnelles de l'�tudiant
     */

    function get_informations_etudiant($matricule) {
        $this->db->where('matriculeEtudiant', $matricule);
        $query = $this->db->get('etudiant');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $data['matricule'] = $row['matriculeEtudiant'];
            $data['nom'] = $row['nom'];
            $data['nomArabe'] = $row['nomArabe'];
            $data['prenom'] = $row['prenom'];
            $data['prenomArabe'] = $row['prenomArabe'];
            $data['prenomPere'] = $row['prenomPere_fr'];
            $data['prenomPere_ar'] = $row['prenomPere_ar'];
            $data['sexe'] = $row['sexe'];
            $data['nin'] = $row['NIN'];
            $data['dateNaissance'] = $row['dateNaissance'];
            $data['dateInscription'] = $row['dateInscription'];
            $data['nationalite'] = $row['nationalite'];
            $data['username'] = $row['login'];
            $data['email'] = $row['email'];
            $data['telephone1'] = $row['telephone'];
            $data['tel2'] = $row['telephone2'];
            $data['telephoneParents'] = $row['telParents'];
            $data['telephoneUrgence'] = $row['telUrgence'];
            $data['contactUrgence'] = $row['contactUrgence'];
            $data['nomParents'] = $row['nomParents'];
            $data['lieuNaissance'] = $row['lieuNaissance'];
            $data['lieuNaissance_ar'] = $row['lieuNaissance_ar'];
            $data['surnom'] = $row['surnom'];
            $data['lienParenteContactUrgence'] = $row['lienParenteUrgence'];
            $data['actif'] = $row['actif'];
            $data['raison'] = $row['raisonInactif'];
            $data['urlPhoto'] = $row['photoEtudiant'];
            if (strpos($data['urlPhoto'], "jpg") > 0)
                $data['urlPhoto'] = str_replace('jpg', 'bmp', $data['urlPhoto']);
            $data['surnom'] = $row['surnom'];

            $idinfoBac = $row['infoBac'];

            $adresseE = $row['idAdresse'];
            $adresse = $row['idAdresseParent'];

            $this->db->where('idAdresse', $adresseE);
            $query = $this->db->get('adresses');
            $row = $query->row_array();
            $data['ligne1'] = $row['ligne1'];
            $data['ligne2'] = $row['ligne2'];
            $data['ligne3'] = $row['ligne3'];
            $data['pays'] = $row['pays'];


            $this->db->where('idAdresse', $adresse);
            $query = $this->db->get('adresses');
            $row = $query->row_array();
            $data['ligne_1'] = $row['ligne1'];
            $data['ligne_2'] = $row['ligne2'];
            $data['ligne_3'] = $row['ligne3'];
            $data['paysP'] = $row['pays'];

            $this->db->where('idInfoBac', $idinfoBac);
            $query = $this->db->get('EtudesAnterieures');
            $row = $query->row_array();
            $data['moyenneBac'] = $row['moyenneBac'];
            // Modif Cheikh 24/11/2015
            $data['nationalite_bac'] = $row['nationalite_bac'];
            $data['num_bac'] = $row['num_bac'];
            //Fin Modif Cheikh 24/11/2015
            $data['anneeObtention'] = $row['anneeObtention'];
            $data['infoBac'] = $row['infoBac'];
            $data['autreDiplome'] = $row['autreDiplome'];
            $data['commentaireAdmission'] = $row['commentaireAdmission'];
            $data['sessionNormale'] = $row['sessionNormale'];
            $idEtab = $row['idEtablissement'];

            $this->db->where('idEtablissement', $idEtab);
            $query = $this->db->get('Etablissement');
            $row = $query->row_array();
            $data['nomEtablissement'] = $row['nom'];
            $data['IDEtablissements'] = $row['idEtablissement'];
            $data['regimeEtablissements'] = $row['regime'];
            $this->db->where('matriculeEtudiant', $matricule);
            $query = $this->db->get('dossieretudiant');
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                $data['programme'] = $row['idProgramme'];
                $data['monGrade'] = $row['grade'];
            }
            return $data;
        }
    }

    function enregistrer_moyenne_semestre1($moyenne, $matricule, $annee) {
        
    }

    function enregistrer_moyenne_semestre2($moyenne, $matricule, $annee) {
        
    }

    function enregistrer_moyenne_semestre3($moyenne, $matricule, $annee) {
        
    }

    function get_programme_name($idProg) {
        $programme_etudiant = NULL;
        $query2 = $this->db->query("SELECT Distinct `nom` FROM `programme` where `idProgramme` = '$idProg'");
        if ($query2->num_rows() > 0) {
            foreach ($query2->result_array() as $row) {
                $programme_etudiant['idProgramme'] = $row['nom'];
            }
        }
        return $programme_etudiant;
    }

    /**
     * Validation du matricule selon la formule de Luhn
     * @param type $matricule
     * @return type 
     */
    function valider_matricule($matricule) {
        $totalMatricule = 0;
        $dnum = 0;
        $test = 0;
        $number_len = strlen($matricule);
        if ($number_len != 6) {
            // Nombre de chiffres entr�s incorrect
            return false;
        } else {
            for ($i = $number_len; $i > 0; $i--) {
                $test++;
                $num = intval(substr($matricule, $i - 1, 1));
                if (($test % 2) != 0) {
                    $totalMatricule += $num;
                } else {
                    $dnum = $num * 2;
                    if ($dnum > 9) {
                        $dnum -= 9;
                    }
                    $totalMatricule += $dnum;
                }
            }

            return ($totalMatricule % 10 == 0);
        }
    }

    function recuperer_matricule_retirer_absences($date, $idGroupe) {
        $matricule = NULL;
        $this->db->where(array('date' => $date, 'idGroupe' => $idGroupe));
        $query = $this->db->get('absences');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $matricule = $row['matricule'];
            }
        }
        return $matricule;
    }

    function valider_date_naissance($annee, $mois, $jour) {
        $messageRetour = 'valide';
        $dateAujourdhui = strftime("%Y");
        $ageAdmission = 16;
        $agelimite = 40;
        $age = $dateAujourdhui - $annee;
        $jourDeChaqueMois = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

        if ($age < 14 || $age > 30) {
            $messageRetour = 'Ann�e de naissance erron�e, l\'�tudiant aurait <b>' . $age . ' </b>ans  ';
        } elseif ($annee < 1950 || $annee > 2100) {
            $messageRetour = 'Date de naissance invalide : seules les ann�es de 1950 � 2100 sont accept�es.';
        } elseif ($mois > 12) {
            $messageRetour = 'Date de naissance invalide : une ann�e n\'a pas plus de 12 mois.';
        } elseif ($jour > 31) {
            $messageRetour = 'Date de naissance invalide : un mois n\'a pas plus de 31 jours.';
        }
        if ($mois < 13) {
            if ($jour > $jourDeChaqueMois[$mois - 1]) {
                $messageRetour = 'Date de naissance invalide : <b>' . $mois . '</b> est un mois de <b>' . $jourDeChaqueMois[$mois - 1] . '</b> jours.';
            }
        }
        if ($mois == 2 && ($this->bissextile($annee) == FALSE) && $jour > 28) {
            $messageRetour = 'Date de naissance invalide : <b>' . $annee . '</b> n\'est pas bissextile, il n\'y a que 28 jours en f�vrier  <b>' . $annee . '</b>';
        }

        return $messageRetour;
    }

    /*
     * modifications des informations personnelles de l'�tudiant
     */

    function modifier_info_etudiant($infos) {
        if (isset($infos['actif'])) {
            $actif = TRUE;
        } else {
            $actif = FALSE;
        }
        if ($actif == TRUE) {
            $data = array(
                'prenom' => trim($infos['prenom']), // trim enl�ve les espaces inutiles 2.2.1
                'nom' => trim($infos['nom']), // trim enl�ve les espaces inutiles 2.2.1
                'email' => $infos['email'],
                'nin' => $infos['nin'],
                'telephone' => $infos['telephone1'],
                'telephone2' => $infos['telephone2'],
                'contactUrgence' => $infos['contactUrgence'],
                'telUrgence' => $infos['telephoneUrgence'],
                'dateNaissance' => $infos['year'] . '-' . $infos['month'] . '-' . $infos['day'],
                'lienParenteUrgence' => $infos['lienParenteContactUrgence'],
                'telParents' => $infos['telephoneParents'],
                'raisonInactif' => $infos['raison'],
                'surnom' => trim($infos['surnom']), // trim enl�ve les espaces inutiles 2.2.1
                'lieuNaissance' => $infos['lieuNaissance'],
                //Ajout du champs - lieu de naissance aranbe au formulaire de la modification
                'lieuNaissance_ar' => $infos['lieuNaissance_ar'],
                //fin de l'ajout 16/08/2018
                'nomParents' => $infos['nomParents'],
                'actif' => $actif,
                'nationalite' => $infos['nationalite'],
                'sexe' => $infos['sexe'],
                'nbreAcces' => 0,
                'nomArabe' => trim($infos['nomArabe']), // modif Cheikh 24/11/2015
                'prenomArabe' => trim($infos['prenomArabe']),
                'prenomPere_fr' => trim($infos['prenomPere']),
                'prenomPere_ar' => trim($infos['prenomPereArabic']) // modif Cheikh 24/11/2015
            );
        } else {
            $data = array(
                'prenom' => trim($infos['prenom']), // trim enl�ve les espaces inutiles 2.2.1
                'nom' => trim($infos['nom']), // trim enl�ve les espaces inutiles 2.2.1     
                'email' => $infos['email'],
                'nin' => $infos['nin'],
                'telephone' => $infos['telephone1'],
                'telephone2' => $infos['telephone2'],
                'contactUrgence' => $infos['contactUrgence'],
                'telUrgence' => $infos['telephoneUrgence'],
                'dateNaissance' => $infos['year'] . '-' . $infos['month'] . '-' . $infos['day'],
                'lienParenteUrgence' => $infos['lienParenteContactUrgence'],
                'telParents' => $infos['telephoneParents'],
                'raisonInactif' => $infos['raison'],
                'surnom' => trim($infos['surnom']), // trim enl�ve les espaces inutiles 2.2.1
                'lieuNaissance' => $infos['lieuNaissance'],
                'nomParents' => $infos['nomParents'],
                'actif' => $actif,
                'nationalite' => $infos['nationalite'],
                'sexe' => $infos['sexe'],
                'nomArabe' => trim($infos['nomArabe']), // modif Cheikh 24/11/2015
                'prenomArabe' => trim($infos['prenomArabe']),
                'prenomPere_fr' => trim($infos['prenomPere']),
                'prenomPere_ar' => trim($infos['prenomPereArabic']) // modif Cheikh 24/11/2015
            );
        }

        $this->db->where('matriculeEtudiant', $infos['matricule']);
        $this->db->update('etudiant', $data);
        $infoBac = NULL;
        $infos_bac = NULL;

        /** information des etudes anterieures * */
        $infos_bac = array(
            'infoBac' => $infos['infoBac'],
            'anneeObtention' => $infos['yearOb'],
            'moyenneBac' => $infos['moyenneBac'],
            'num_bac' => $infos['num_bac'], // Modif Cheikh 24/11
            'nationalite_bac' => $infos['nationalite_bac'], // Modif Cheikh 24/11
            'autreDiplome' => $infos['autreDip'],
            'commentaireAdmission' => $infos['commentaires'],
            'sessionNormale' => $infos['sessionNormale']);
        $this->db->where('matriculeEtudiant', $infos['matricule']);
        $query = $this->db->get('etudiant');

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $idInfoBac = $row['infoBac'];
            $this->db->where('idinfoBac', $idInfoBac);
            $this->db->update('etudesanterieures', $infos_bac);
        }

        // mise a jour du nom de l etablisement

        if (($infos['newEtablissement']) != Null) {
            $this->db->insert('etablissement', array('nom' => $infos['newEtablissement'], 'regime' => $infos['regime']));
            //$this->db->insert('etablissement', array('nom' => $infos['newEtablissement']));
            $id_etablissement = $this->db->insert_id();
        } else {
            $id_etablissement = $infos['nomEtablissement'];
        }

        $this->db->where('idInfoBac', $idInfoBac);

        $query = $this->db->get('etudesanterieures');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $info_etablissement = array('idEtablissement' => $id_etablissement);
            $this->db->where('idInfoBac', $idInfoBac);
            $this->db->update('etudesanterieures', $info_etablissement);
        }


        /* mise � jour de l'adresse de l'�tudiant et des parents */

        $adresse = 0;
        $this->db->where('matriculeEtudiant', $infos['matricule']);
        $query = $this->db->get('etudiant');
        $addresseEtudiant = array('ligne1' => $infos['ligne1'], 'ligne2' => $infos['ligne2'], 'ligne3' => $infos['ligne3'], 'pays' => $infos['pays']);

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $adresse = $row['idAdresse'];
            $this->db->where('idAdresse', $adresse);
            $this->db->update('adresses', $addresseEtudiant);
        }

        $adresseP = 0;
        $query = $this->db->get('etudiant');
        $addresseParent = array('ligne1' => $infos['ligne_1'], 'ligne2' => $infos['ligne_2'], 'ligne3' => $infos['ligne_3'], 'pays' => $infos['paysP']);

        if ($query->num_rows() > 0) {
            $adresseP = $row['idAdresseParent'];
            $this->db->where('idAdresse', $adresseP);
            $this->db->update('adresses', $addresseParent);
        }

        $infoProgramme = array(
            'idProgramme' => $infos['idProgramme'],
            'grade' => $infos['idGrade'],
        );
        $this->db->where('matriculeEtudiant', $infos['matricule']);
        $this->db->update('dossieretudiant', $infoProgramme);
    }

    /*
     * fonction pour generer un login aleatoirement 
     */

    function genRandomString() {
        $length = 5;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $string = '';
        for ($p = 0; $p < $length; $p++) {
            $string .= $characters[mt_rand(0, strlen($characters))];
        }
        return $string;
    }

    /**
     * fontion qui genere un nom d'usager en combinant les 2 premieres lettres 
     * du prenom avec les 3 premieres du nom de famille. 
     * si le nom d'usager existe deja, alors on lui ajout un suffixe (nombre croissant commencant a 1)
     * @param type $firstName
     * @param type $lastName
     * @return type  
     */
    function generer_login($lastName, $firstName) {
        // enlever les espaces dans le nom et le prenom
        $lastName = str_replace(' ', '', $lastName);
        $firstName = str_replace(' ', '', $firstName);
        $lastName = $this->stripAccents($lastName);
        $firstName = $this->stripAccents($firstName);
        if ((strlen($firstName) + strlen($lastName)) < 5) {
            $loginTest = $this->genRandomString();
        } else {
            $login = substr($firstName, strlen($firstName) - 2, 2) . substr($lastName, strlen($lastName) - 3, 3);
            $loginTest = $login;
            $valid = true;
            $suffix = 1;
            do {
                $this->db->where('login', $loginTest);
                $query = $this->db->get('etudiant');

                $this->db->where('login', $loginTest);
                $query2 = $this->db->get('employe');

                if ($query->num_rows() > 0 || $query2->num_rows() > 0) {
                    $valid = false;
                    $loginTest = $login . $suffix++;
                } else
                    $valid = true;
            }while (!$valid);
        }
        return $loginTest;
    }

    /*
     * fonction qui prend en param�tre un matricule et retourne le programme dans lequel l'�tudiant est inscrit.
     */

    function get_programme_etudiant($matricule) {
        $programme_etudiant = NULL;
        $query = $this->db->query("SELECT Distinct `idProgramme` FROM `DossierEtudiant` where `matriculeEtudiant` = '$matricule'");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $idprog = $row['idProgramme'];
                $query2 = $this->db->query("SELECT Distinct `nom` FROM `programme` where `idProgramme` = '$idprog'");
                if ($query2->num_rows() > 0) {
                    foreach ($query2->result_array() as $row) {
                        $programme_etudiant['idProgramme'][] = $row['nom'];
                    }
                }
            }
        }
        return $programme_etudiant;
        print_r($programme_etudiant);
    }

    /*
     * Fonction qui retourne tous les programmes qui existent.
     */

    function get_programme() {
        $programme = NULL;
        $query = $this->db->query("SELECT Distinct `idProgramme`, `nom` FROM `programme` ORDER BY `nom`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $programme['idProgramme'][] = $row['idProgramme'];
                $programme['nomProgramme'][] = $row['nom'];
            }
        }
        return $programme;
    }

    /*
     * Fonction qui retourne tous les grades qui existent.
     */

    function get_grade() {
        $grade = NULL;
        $query = $this->db->query("SELECT Distinct `idGrade` FROM `grade` order by `idGrade` ");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $grade['idGrade'][] = $row['idGrade'];
            }
        }
        return $grade;
    }

    function inscrire_etudiant_programme($post) {
        $tableauMatricule = array_keys($post['items'], 'on');
        $data = '';
        $nombreEtudiant = count($tableauMatricule);
        if (!isset($post)) {
            return false;
        }
        $compteursValide = 0;
        $compteurMessage = 0;

        for ($i = 0; $i < $nombreEtudiant; $i++) {
            $this->db->where(array('matriculeEtudiant' => $tableauMatricule[$i], 'idProgramme' => $post['idProgramme'], 'grade' => $post['grade']));
            $query = $this->db->get('dossieretudiant');
            if ($query->num_rows() > 0) {
                $data['message'][$compteurMessage] = 'L\'�tudiant <b>' . $tableauMatricule[$i] . ' </b>est d�j� inscrit au programme <b>' . $post['idProgramme'] . '</b>';
                $compteurMessage++;
            } else {
                $info = array(
                    'matriculeEtudiant' => $tableauMatricule[$i],
                    'idProgramme' => $post['idProgramme'],
                    'grade' => $post['grade'],
                );
                $data['valide'][$compteursValide] = 'L\'�tudiant <b>' . $tableauMatricule[$i] . ' </b>est bien inscrit au programme <b>' . $post['idProgramme'] . '</b>';
                $compteursValide++;
                //insertion des infos dans le plan etudes
                $this->db->insert('dossieretudiant', $info);
            }
        }
        return $data;
    }

    function saisir_decision($matricule, $decision) {
        if ($decision == 'Null')
            $decision = NULL;
        $data = array(
            'decisionBulletin' => $decision, 'indicateurBulletinACalculer' => 1, 'IndicateurDecisionManuelle' => 1
        );
        $this->db->where('matriculeEtudiant', $matricule);

        $this->db->update('dossieretudiant', $data);
    }

    function get_idGroupe_anneeCourante($sigleCours, $semestre, $annee) {
        $idgr = NULL;
        $query2 = $this->db->query("SELECT `idGroupe` FROM Groupe where `semestre` = '$semestre' AND `annee` = '$annee' AND `sigle` = '$sigleCours'");
        if ($query2->num_rows() > 0) {
            foreach ($query2->result_array() as $row) {
                $idgr['idGroupe'][] = $row['idGroupe'];
            }
        }
        return $idgr;
    }

    function get_old_password($login) {
        $this->db->where('login', $login);

        $query = $this->db->get('employe');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $this->decode($row['pass']);
        }
    }

    function desinscrire_etudiant_module($post) {
        $tableauMatricule = array_keys($post['items'], 'on');
        $data['valide'] = '';
        $data = '';
        $nombreEtudiant = count($tableauMatricule);
        if (!isset($post)) {
            return false;
        }
        $compteurMessage = 0;
        for ($i = 0; $i < $nombreEtudiant; $i++) {
            $this->db->where(array('matriculeEtudiant' => $tableauMatricule[$i], 'annee' => $post['annee'], 'semestre' => $post['session'], 'sigle' => $post['sigle']));
            $query = $this->db->get('planetudes');
            if ($query->num_rows() > 0) {
                $this->db->where(array('matriculeEtudiant' => $tableauMatricule[$i], 'annee' => $post['annee'], 'semestre' => $post['session'], 'sigle' => $post['sigle']));
                $this->db->delete('planetudes');
                $groupe = $this->get_groupe($post['annee'], $post['session'], $post['sigle']);
                for ($k = 0; $k < count($groupe); $k++) {
                    $this->db->where(array('matriculeEtudiant' => $tableauMatricule[$i], 'idGroupe' => $groupe[$k]));
                    $this->db->delete('listeetudiants');
                }
            } else {
                $data['message'][$compteurMessage] = '�tudiant ' . $tableauMatricule[$i] . ' ne peut pas �tre supprim�.';
                $compteurMessage++;
            }
        }
        return $data;
    }

    /*
     * fonction qui retourne si l'etudiant est inscrit dans le module ou non
     */

    function get_etudiants_inscrit_module($matricule, $annee, $session, $sigle) {
        $data = '';
        $counter = 0;
        if (is_array($matricule))
            for ($i = 0; $i < count($matricule); $i++) {
                $this->db->where(array('matriculeEtudiant' => $matricule[$i], 'annee' => $annee, 'semestre' => $session, 'sigle' => $sigle));
                $query = $this->db->get('planetudes');
                if ($query->num_rows() > 0) {
                    $data['estInscrit'][$counter] = '1';
                } else {
                    $data['estInscrit'][$counter] = '0';
                }
                $counter++;
            }

        return $data;
    }

    /*
     * fonction pour retourner la liste des etudiants inscrits dans un groupe
     */

    function get_etudiants_groupe($idGroupe) {
        $sql = "SELECT  DISTINCT etudiant.matriculeEtudiant,etudiant.nom,etudiant.prenom 
        FROM etudiant,listeetudiants 
        WHERE (etudiant.matriculeEtudiant=listeetudiants.matriculeEtudiant) AND (idGroupe='" . $idGroupe . "')";

        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    /*
     * fonction qui prend en parametre le matricule de l'etudiant 
     * et retourne tous les groupes dans lesquels cet etudiant etait inscrit 
     */

    function get_liste_groupe($matriculeEtudiant) {
        $this->db->where('matriculeEtudiant', $matriculeEtudiant);
        $query = $this->db->get('listeetudiants');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $groupe[] = $row['idGroupe'];
            }
            return $groupe;
        }
    }

    /*
     * liste des groupes pour une session et une annee specifique 
     * pour les absences le groupe et la liste des groupes o� un etudiant 
     * etait absent .
     */

    function get_liste_groupe_absences($groupe, $annee, $semestre) {
        $listeGroupe = array();
        if (is_array($groupe)) {
            for ($i = 0; $i < count($groupe); $i++) {
                $this->db->where(array('idGroupe' => $groupe[$i], 'annee' => $annee, 'semestre' => $semestre));
                $query = $this->db->get('groupe');
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        $listeGroupe[] = $row['idGroupe'];
                    }
                }
            }
        }
        return $listeGroupe;
    }

    /*
     * fonction qui retourne une liste d'etudiants qui ont ete absents 
     * dans un semestre donn� et qui sont non motivees.
     */

    function get_liste_global_etudiants_absents($annee, $semestre) {
        $infoAbsences = array(); // variable qui va contenir tous les informations des absents la date la duree ...
        $this->db->where(array('annee' => $annee, 'semestre' => $semestre, 'absenceMotivee' => '0'));

        $this->db->order_by('date');
        $query = $this->db->get('absences');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $infoAbsences['date'][] = $row['date'];
                $infoAbsences['duree'][] = $row['duree'];
                $infoAbsences['periode'][] = $row['periode'];
                $infoAbsences['idGroupe'][] = $row['idGroupe'];
                $infoAbsences['matriculeEtudiant'][] = $row['matricule'];
                $infoAbsences['annee'][] = $row['annee'];
                $infoAbsences['semestre'][] = $row['semestre'];
            }
        }
        return $infoAbsences;
    }

    /*
     * fonction qui retourne une liste d'etudiants qui ont ete absents 
     * � une date donnee et qui sont non motivees. afin de les retirer en cas 
     * d'erreur
     */

    function get_liste_etudiants_aretirer_absents($date, $idGroupe, $matricule) {
        $infoAbsences = array(); // variable qui va contenir tous les informations des absents la date la duree ...
        $this->db->where(array('date' => $date, 'idGroupe' => $idGroupe, 'matricule' => $matricule));
        $query = $this->db->get('absences');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $infoAbsences['date'][] = $row['date'];
                $infoAbsences['duree'][] = $row['duree'];
                $infoAbsences['periode'][] = $row['periode'];
                $infoAbsences['idGroupe'][] = $row['idGroupe'];
                $infoAbsences['matriculeEtudiant'][] = $row['matricule'];
                $infoAbsences['annee'][] = $row['annee'];
                $infoAbsences['semestre'][] = $row['semestre'];
            }
        }
        return $infoAbsences;
    }

    /*
     * fonction qui retourne une liste d'etudiants qui ont motive leur absence  
     * dans un semestre donne .
     */

    function get_liste_absences_motivee_etudiants($annee, $semestre) {
        $infoAbsences = array(); // variable qui va contenir tous les informations des absents la date la duree ...
        $this->db->where(array('annee' => $annee, 'semestre' => $semestre, 'absenceMotivee' => '1'));
        $this->db->order_by('date');
        $query = $this->db->get('absences');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $infoAbsences['date'][] = $row['date'];
                $infoAbsences['duree'][] = $row['duree'];
                $infoAbsences['periode'][] = $row['periode'];
                $infoAbsences['idGroupe'][] = $row['idGroupe'];
                $infoAbsences['matriculeEtudiant'][] = $row['matricule'];
                $infoAbsences['annee'][] = $row['annee'];
                $infoAbsences['semestre'][] = $row['semestre'];
            }
        }
        return $infoAbsences;
    }

    /*
     * fonction qui retourne tous les information d'une absence par un etudiant
     */

    function get_informations_absences($groupe, $matriculeEtudiant, $annee, $semestre) {
        $infoAbsences = array(); // variable qui va contenir tous les informations des absents la date la duree ...
        if (is_array($groupe)) {
            for ($i = 0; $i < count($groupe); $i++) {
                $this->db->where(array('idGroupe' => $groupe[$i], 'annee' => $annee, 'semestre' => $semestre, 'matricule' => $matriculeEtudiant, 'absenceMotivee' => '0'));
                $query = $this->db->get('absences');
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        $infoAbsences['date'][] = $row['date'];
                        $infoAbsences['duree'][] = $row['duree'];
                        $infoAbsences['periode'][] = $row['periode'];
                        $infoAbsences['idGroupe'][] = $row['idGroupe'];
                        $infoAbsences['matriculeEtudiant'][] = $row['matricule'];
                        $infoAbsences['annee'][] = $row['annee'];
                        $infoAbsences['semestre'][] = $row['semestre'];
                    }
                }
            }
        }
        return $infoAbsences;
    }

    /*
     * fonction qui retourne la liste des absents dans un groupe precis
     */

    function get_liste_etudiants_absents($idGroupe, $annee, $semestre) {
        $infoAbsences = array();
        $this->db->where(array('idGroupe' => $idGroupe, 'annee' => $annee, 'semestre' => $semestre));
        $this->db->order_by('date');
        $query = $this->db->get('absences');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $infoAbsences['date'][] = $row['date'];
                $infoAbsences['duree'][] = $row['duree'];
                $infoAbsences['periode'][] = $row['periode'];
                $infoAbsences['idGroupe'][] = $row['idGroupe'];
                $infoAbsences['matriculeEtudiant'][] = $row['matricule'];
                $infoAbsences['annee'][] = $row['annee'];
                $infoAbsences['semestre'][] = $row['semestre'];
            }
        }
        return $infoAbsences;
    }

    /*
     * fonction pour enregistrer les absences des etudiants 
     * le post contient toutes les informations necessaires 
     * pour remplir la table des absences.
     */

    function enregistrer_absences($post) {
        $tableauMatricule = array_keys($post['items'], 'on');

        $date = $this->convert_date($post['date']);
        if (isset($post['duree'])) {
            if (isset($post['duree2'])) {
                $duree = $post['duree'] + $post['duree2'];
            } else {
                $duree = $post['duree'];
            }
        }
        if (isset($post['duree2'])) {
            if (isset($post['duree'])) {
                $duree = $post['duree'] + $post['duree2'];
            } else {
                $duree = $post['duree2'];
            }
        }


        if ($tableauMatricule != NULL) {
            for ($i = 0; $i < count($tableauMatricule); $i++) {
                $info = array(
                    'matricule' => $tableauMatricule[$i],
                    'annee' => $post['annee'],
                    'semestre' => $post['session'],
                    'date' => $date,
                    'periode' => $post['periode'],
                    'duree' => $duree,
                    'absenceMotivee' => '0',
                    'idGroupe' => $post['idGroupe']
                );
                $this->db->insert('absences', $info);
            }
        }
    }

    /*
     * fonction pour convertir la date de DD/MM/YYYY a YYYY-MM-DD
     */

    function convert_date($date) {
        $year = '';
        $month = '';
        $day = '';

        for ($i = 0; $i < strlen($date); $i++) {
            if ($i <= 1) {
                $day .= $date[$i];
            }
            if ($i > 2 && $i < 5) {
                $month .= $date[$i];
            }
            if ($i > 5) {
                $year .= $date[$i];
            }
        }

        $datephp = $year . '-' . $month . '-' . $day;
        return $datephp;
    }

    /*
     * fonction qui retourne la date du debut et la fin des cours
     */

    function get_date_courante() {
        $date_courante = NULL;
        $query = $this->db->get('sessionCourante');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $date_courante['debutCours'] = $row['debutCours'];
            $date_courante['finCours'] = $row['finCours'];
            $date_courante['annee'] = $row['annee'];
            $date_courante['semestre'] = $row['semestre'];
        }
        return $date_courante;
    }

    /*
     * fonction qui retourne si le module est deja valide par le chef de departement 
     * si oui persone n'a le droit d'inscrire un nouveau etudiant
     */

    function get_module_validee($module, $annee, $session) {
        $listeModules = array('scolarite', 'administrateur', 'chef_departement');
        $this->db->or_where_in('etatNote', $listeModules);
        $this->db->where(array('sigle' => $module, 'annee' => $annee, 'semestre' => $session,
            'lien !=' => 'AB', 'cote !=' => 'EQ'));
        $this->db->distinct();
        $query = $this->db->get('planetudes');
        if ($query->num_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    //Alfa
    function verifier_acces_note_valide($matricule, $annee, $semestre, $sigle) {

        $valide = 'false';
        $notePasEncoreEntree = -1;

        for ($i = 0; $i < count($matricule); $i++) {

            $this->db->where(array('matriculeEtudiant' => $matricule[$i],
                'etatNote' => 'professeur', 'sigle' => $sigle, 'annee' => $annee,
                'semestre' => $semestre));

            $query = $this->db->get('planetudes');
            if ($query->num_rows() > 0) {
                $valide = 'true';
            }
        }

        return $valide;
    }

    /*
     * fonction pour inscrire les etudiants dans un module
     */

    function inscrire_etudiant_module_groupe($post) {
        $tableauMatricule = array_keys($post['items'], 'on');
        if (!isset($post['groupe'])) {
            return $data['pasDeGroupe'] = 'pasDeGroupe';
        }
        $tableauGroupe = array_keys($post['groupe'], 'on');
        $nombreEtudiant = count($tableauMatricule);
        $data = NULL;
        $nombreGroupeSelectionne = count($tableauGroupe);
        if (!isset($post)) {
            return false;
        }
        $compteurMessage = 0;
        $comteurInsert = 0;
        for ($i = 0; $i < $nombreEtudiant; $i++) {
            $this->db->where(array('matriculeEtudiant' => $tableauMatricule[$i], 'annee' => $post['annee'], 'semestre' => $post['session'], 'sigle' => $post['sigle']));
            $query = $this->db->get('planetudes');
            if ($query->num_rows() > 0) {
                for ($j = 0; $j < $nombreGroupeSelectionne; $j++) {
                    $this->db->where(array('matriculeEtudiant' => $tableauMatricule[$i], 'idGroupe' => $tableauGroupe[$j]));
                    $query = $this->db->get('listeetudiants');
                    if ($query->num_rows() > 0) {
                        $data['valide'][$compteurMessage] = 'L\'�tudiant <b>' . $tableauMatricule[$i] . '</b> est d�j� inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                        $compteurMessage++;
                    } else {
                        $info = array(
                            'matriculeEtudiant' => $tableauMatricule[$i],
                            'idGroupe' => $tableauGroupe[$j]);
                        $this->db->insert('listeetudiants', $info);
                        $data['insert'][$comteurInsert] = 'L\'�tudiant <b>' . $tableauMatricule[$i] . '</b> est bien inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                        $comteurInsert++;
                    }
                }
            } else {
                if ($post['typeCours'] == 'obligatoire') {
                    $info = array(
                        'matriculeEtudiant' => $tableauMatricule[$i],
                        'sigle' => $post['sigle'],
                        'semestre' => $post['session'],
                        'note' => '-1',
                        'cote' => '',
                        'lien' => 'AV',
                        'etatNote' => 'professeur',
                        'annee' => $post['annee']);
                } elseif ($post['typeCours'] == 'equivalence') {

                    $info = array(
                        'matriculeEtudiant' => $tableauMatricule[$i],
                        'sigle' => $post['sigle'],
                        'semestre' => $post['session'],
                        'note' => '-1',
                        'cote' => '',
                        'lien' => 'EQ',
                        'etatNote' => 'professeur',
                        'annee' => $post['annee']);
                } elseif ($post['typeCours'] == 'horsProgramme') {
                    $info = array(
                        'matriculeEtudiant' => $tableauMatricule[$i],
                        'sigle' => $post['sigle'],
                        'semestre' => $post['session'],
                        'note' => '-1',
                        'cote' => '',
                        'lien' => 'HV',
                        'etatNote' => 'professeur',
                        'annee' => $post['annee']);
                }
                //insertion des infos dans le plan etudes

                $this->db->insert('planetudes', $info);
                for ($j = 0; $j < $nombreGroupeSelectionne; $j++) {
                    $this->db->where(array('matriculeEtudiant' => $tableauMatricule[$i], 'idGroupe' => $tableauGroupe[$j]));
                    $query = $this->db->get('listeetudiants');
                    if ($query->num_rows() > 0) {
                        $data['valide'][$compteurMessage] = 'L\'�tudiant <b>' . $tableauMatricule[$i] . '</b> est d�j� inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                        $compteurMessage++;
                    } else {
                        $info = array(
                            'matriculeEtudiant' => $tableauMatricule[$i],
                            'idGroupe' => $tableauGroupe[$j]);
                        $this->db->insert('listeetudiants', $info);
                        $data['insert'][$comteurInsert] = 'L\'�tudiant <b>' . $tableauMatricule[$i] . '</b> est bien inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                        $comteurInsert++;
                    }
                }
            }
        }
        return $data;
    }

    function cree_nouveau_groupe($infoGroupe) {
        $this->db->set('sigle', $infoGroupe['sigle']);
        $session = $infoGroupe['session'];
        $annee = $infoGroupe['date'];
        $typeGroupe = $infoGroupe['typeGroupe'];
        $sigle = $infoGroupe['sigle'];

        $query = $this->db->query("SELECT   MAX(`numGroupe`) as numGroupe FROM `Groupe` where 
                 typeGroupe ='$typeGroupe' AND sigle  = '$sigle' AND annee ='$annee' AND semestre = '$session' ");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $numGroupe = $row['numGroupe'];
                $numGroupe++;
            }
        }
        //creation de l id Unique 
        $idGroupe = $infoGroupe['sigle'] . '-' . $infoGroupe['date'] . $infoGroupe['session'] . '-' . $infoGroupe['typeGroupe'] . $numGroupe;

        $this->db->set('idGroupe', $idGroupe);
        $this->db->set('numGroupe', $numGroupe);
        $this->db->set('typeGroupe', $infoGroupe['typeGroupe']);
        $this->db->set('matriculeEmploye', $infoGroupe['matEmployer']);
        $this->db->set('semestre', $infoGroupe['session']);
        $this->db->set('annee', $infoGroupe['date']);
        $this->db->insert('Groupe');
        return $numGroupe;
    }

    function modifier_groupe_cours($infosGroupe) {
        $this->db->where('idGroupe', $infosGroupe['idGroupe']);
        $this->db->set(array('matriculeEmploye' => $infosGroupe['matEmployer']));
        $this->db->update('groupe');
    }

    function lettreSemestre($num_groupe) {
        // le num�ro de groupe peut �tre 1 ou 01, 2 ou 02, 3 ou 03.
        // $numGroupe = intval($num_groupe);
        switch (intval($num_groupe)) {
            case 1:
                return ("-P"); // Printemps
                break;
            case 2:
                return ("-E"); // �t�
                break;
            case 3:
                return ("-A"); // Automne
                break;
            default:
                return ("Erreur conversion num�ro de semestre");
                break;
        }
    }

    function lettre_dans_Groupe($groupe) {
        // $groupe vaut ici, par exemple, CCOM273-201301-Groupe-Theorie1
        // remplace le num�ro de groupe (01 dans l'exemple) par une lettre, ce qui va donner CCOM273-P-Groupe-Theorie1
        // fait appel � la fonction lettreSemestre
        $ps = strpos($groupe, "-Groupe"); // position de -Groupe (variable car le sigle est de longueur variable)
        $debut = substr($groupe, 0, $ps - 2);
        $milieu = $this->lettreSemestre(substr($groupe, $ps - 1, $ps + 1)); // remplace 02 par -E, par exemple
        $fin = substr($groupe, $ps, strlen($groupe));
        return($debut . $milieu . $fin); // pour affichage du r�sultat
    }

    function corrigerNumGroupe($groupe) {
        // remplace le num�ro de semestre par  -lettre
        // retire les caract�res -Groupe d'un num�ro de groupe, pour affichage simplifi�
        // exemple :  je passe de CCOM273-201301-Groupe-Theorie1 � CCOM273-2013-P-Theorie1
        if (strpos($groupe, "-Groupe") == 0)
            return ("Erreur-Groupe");
        $texte = $this->lettre_dans_Groupe($groupe);
        $ps = strpos($texte, "-Groupe"); // position de -Groupe (variable car le sigle est de longueur variable)
        $debut = substr($texte, 0, $ps);
        $fin = substr($texte, $ps + 7, strlen($texte));
        return($debut . $fin); // pour affichage du r�sultat
    }

    function supprimer_groupe_cours($idGroupe) {
        $numGroupe_lettre = $this->corrigerNumGroupe($idGroupe);
        // $numGroupe_lettre = $this->lettre_dans_Groupe($idGroupe);	// pour affichage lettre au lien de num�ro de semestre
        $this->db->where('idGroupe', $idGroupe);
        $query1 = $this->db->get('listeEtudiants');
        if ($query1->num_rows() > 0) {
            $data['typeBox'] = 'error_box';
            $data['informations'] = 'Le groupe <b>' . $numGroupe_lettre . '</b> ne peut �tre supprim� car des �tudiants y sont inscrits !';
            return $data;
        }

        $this->db->where('idGroupe', $idGroupe);
        $query2 = $this->db->get('horaire');
        if ($query2->num_rows() > 0) {
            $data['typeBox'] = 'error_box';
            $data['informations'] = 'Le groupe <b>' . $numGroupe_lettre . '</b> ne peut �tre supprim� car il fait partie d\'un horaire !';
            return $data;
        }

        $this->db->where('idGroupe', $idGroupe);
        $this->db->delete('groupe');
        $data['typeBox'] = 'valid_box';
        $data['informations'] = 'Le groupe <b>' . $numGroupe_lettre . '</b> a été supprimé avec succés.';
        return $data;
    }

    function supprimer_module($sigle) {
        $this->db->where('sigle', $sigle);
        $query1 = $this->db->get('groupe');
        if ($query1->num_rows() > 0) {
            $data['typeBox'] = 'error_box';
            $data['informations'] = 'L\'élément <b>' . $sigle . '</b> ne peut pas étre supprimé car des groupes y sont créés.';
            return $data;
        }
        $this->db->where('sigle', $sigle);
        $this->db->delete('modprog');
        $this->db->where('sigle', $sigle);
        $this->db->delete('module');

        $data['typeBox'] = 'valid_box';
        $data['informations'] = 'L\'élément <b>' . $sigle . '</b> a été supprimé avec succés.';
        return $data;
    }

    
    function supprimer_unite($sigle) {//add by MedBakar 16-03-2020
        $this->db->where('sigleunite', $sigle);
        $query1 = $this->db->get('module');
        if ($query1->num_rows() > 0) {
            $data['typeBox'] = 'error_box';
            $data['informations'] = 'Le module <b>' . $sigle . '</b> ne peut pas être supprimé car des éléments y sont créés.';
            return $data;
        }
       
        $this->db->where('sigle', $sigle);
        $this->db->delete('unite');

        $data['typeBox'] = 'valid_box';
        $data['informations'] = 'Le module <b>' . $sigle . '</b> a été supprimé avec succés.';
        return $data;
    }
    
    function getInfosPlanEtudes($matricule, $sigle, $annee, $semestre) {
        $infos = array();
        $res = $this->db->query("SELECT nom, prenom, actif, cote, lien, CASE WHEN note =-1 THEN 'AV' else note END AS note FROM etudiant JOIN planEtudes ON etudiant.matriculeEtudiant = planEtudes.matriculeEtudiant WHERE etudiant.matriculeEtudiant = '$matricule' AND annee = '$annee' AND semestre= '$semestre' AND sigle= '$sigle' ");
        if ($res->num_rows() > 0)
            foreach ($res->result_array() as $row) {
                $infos['nom'] = $row['nom'];
                $infos['prenom'] = $row['prenom'];
                $infos['actif'] = $row['actif'];
                $infos['note'] = $row['note'];
                $infos['cote'] = $row['cote'];
                $infos['lien'] = $row['lien'];
                $infos['annee'] = $annee;
                $infos['matricule'] = $matricule;
                $infos['semestre'] = $semestre;
                $infos['sigle'] = $sigle;
            }
        return $infos;
    }

    function get_horaires_dispo($sigle) {
        $horaires_dispo = array();
        $this->db->distinct();
        $this->db->order_by('annee DESC , semestre DESC');
        $this->db->where('sigle', $sigle);
        $this->db->select('annee , semestre');
        $this->db->from('groupe');
        $this->db->join('horaire', 'groupe.idGroupe = horaire.idGroupe');
        $res = $this->db->get();
//        print($this->db->last_query());
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                switch ($row['semestre']) {
                    case '3':
                        $semestre = "Automne  : ";
                        break;
                    case '2':
                        $semestre = "�t� : ";
                        break;
                    case '1':
                        $semestre = "Printemps: ";
                        break;
                }
                $horaires_dispo[] = array('key' => $semestre . $row['annee'], 'value' => $row['semestre'] . '-' . $row['annee']);
            }
        }
        return $horaires_dispo;
    }

    function cree_nouveau_horaire($periodeGroupe) {

        $idGroupe = ($periodeGroupe[0]['idGroupe']);
        $this->db->where('idGroupe', $idGroupe);
        $this->db->delete('horaire');
        foreach ($periodeGroupe as $pg) {
            $this->db->insert('horaire', $pg);
        }
    }

    function set_password($newPassword, $login) {
        $data = array(
            'pass' => $this->encode($newPassword)
        );
        $this->db->where('login', $login);

        $this->db->update('employe', $data);
    }

    function get_etudiant() {
        $etudiant = NULL;
        $query = $this->db->query("SELECT Distinct `matriculeEtudiant`, `nom`,`prenom` FROM `etudiant` order by `matriculeEtudiant`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $etudiant['matricule'][] = $row['matriculeEtudiant'];
                $etudiant['nom'][] = $row['nom'];
                $etudiant['prenom'][] = $row['prenom'];
            }
        }
        return $etudiant;
    }

    function get_etudiant_planetudes_note($annee, $semestre, $sigle) {
        $matricules = '';

        $this->db->where(array('sigle' => $sigle, 'annee' => $annee, 'semestre' => $semestre,
            'lien !=' => 'AB', 'cote !=' => 'EQ'));
        $res = $this->db->get('planetudes');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $matricules[] = $row['matriculeEtudiant'];
            }
        }

        return $matricules;
    }

    function get_etudiant_planetudes_note_rattrapage($annee, $semestre, $sigle) {
        $matricules = '';

        $this->db->where('sigle', $sigle);
        $this->db->where('annee', $annee);
        $this->db->where('semestre', $semestre);
        $this->db->where('cote', 'FX');
        $this->db->or_where('lien', 'RT');
        $this->db->where('sigle', $sigle);
        $this->db->where('annee', $annee);
        $this->db->where('semestre', $semestre);
        $this->db->distinct();
        $res = $this->db->get('planetudes');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $matricules[] = $row['matriculeEtudiant'];
            }
        }
        return $matricules;
    }

    function get_etudiants_planetudes_delete($sigle, $semestre, $annee) {

        $matricules = '';
        $res = $this->db->query("select * from `planetudes` where `sigle` = '$sigle' and `semestre` = '$semestre'and `annee` = '$annee' and `lien` in ('av' , 'hv')");
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $matricules[] = $row['matriculeEtudiant'];
            }
        }
        return $matricules;
    }

    /*
     * fonction pour creer un etudiant dans le systeme.
     */

    function inscrire_etudiant($informations, $matricule) {
//insert de etablissement
        if (($informations['newEtablissement']) != Null) {
            $this->db->insert('etablissement', array('nom' => $infos['newEtablissement'], 'regime' => $infos['regime']));

            $id_etablissement = $this->db->insert_id();
        } else {
            $id_etablissement = $informations['school'];
        }


        /** information des etudes anterieures * */
        /* modification par cheikh 23/11/2015 pour ajout numbac et nationalit� bac */
        $infos_bac = array(
            'moyenneBac' => $informations['moyenne'],
            'anneeObtention' => $informations['yearD'],
            'infoBac' => $informations['infoBac'],
            'autreDiplome' => $informations['autreDip'],
            'commentaireAdmission' => $informations['comments'],
            'sessionNormale' => $informations['sessionNormale'],
            'idEtablissement' => $id_etablissement,
            'nationalite_bac' => $informations['nationalitebac'],
            'num_bac' => $informations['numbac']);

//insertion des infos des etudes anterieures
        $this->db->insert('etudesanterieures', $infos_bac);
        $id_etudes_anterieures = $this->db->insert_id();


        /** adresses * */
        $addresseEtudiant = array('ligne1' => $informations['line1'],
            'ligne2' => $informations['line2'], 'ligne3' => $informations['line3'],
            'pays' => $informations['country']); //lingne3 pour la ville de l'etudiant
        $addresseParent = array('ligne1' => $informations['lineP1'],
            'ligne2' => $informations['lineP2'], 'ligne3' => $informations['lineP3'],
            'pays' => $informations['countryP']); //lineP3 pour la ville des parents

        $this->db->insert('adresses', $addresseEtudiant);
        $id_adresse_etudiant = $this->db->insert_id();

        $this->db->insert('adresses', $addresseParent);
        $id_adresse_parent = $this->db->insert_id();
        // Modif Cheikh recuperation de l'ann�e d'inscription. 
        $sessionCourante = $this->get_session_courante();
        $annee_inscription = $sessionCourante['annee_univ'][0];


        /** infos generales * */
        $infos_generales = array(
            'matriculeEtudiant' => $informations['code'],
            'nom' => $informations['lastName'],
            'prenom' => $informations['firstName'],
            'sexe' => $informations['sexe'],
            'dateNaissance' => $informations['year'] . '-' . $informations['month'] . '-' . $informations['day'],
            'nationalite' => $informations['citizen'],
            'NIN' => $informations['nin'],
            'infoBac' => $id_etudes_anterieures,
            'login' => $informations['accessCode'],
            'pass' => $this->encode($informations['pass']),
            'email' => $informations['email'],
            'idAdresse' => $id_adresse_etudiant,
            'idAdresseParent' => $id_adresse_parent,
            'telephone' => $informations['phone'],
            'telephone2' => $informations['phone2'],
            'telParents' => $informations['phoneP'],
            'telUrgence' => $informations['emergencyPhone'],
            'contactUrgence' => $informations['emergencyContact'],
            'lienParenteUrgence' => $informations['emergencyLink'],
            'lieuNaissance' => $informations['lieuNaissance'],
            /*  AJOUT DU CHAMP lieunaissance_ar | POUR l'insertion | 15/08/2018 L_N_Ar  */
            'lieuNaissance_ar' => $informations['lieuNaissance_ar'],
            /*  FIN de la modification */
            'surnom' => $informations['surnom'],
            'nomParents' => $informations['nomParents'],
            'actif' => true,
            'photoEtudiant' => $informations['code'] . '.jpg',
            'annee_inscription' => $annee_inscription,
            'nomArabe' => $informations['lastNameArabic'],
            'prenomArabe' => $informations['firstNameArabic'],
            'created_by' => $informations['created_by'],
            'prenomPere_fr' => $informations['prenomPere'],
            'prenomPere_ar' => $informations['prenomPereArabic']
        );



        $this->db->insert('etudiant', $infos_generales);

        $infoProgramme = array(
            'matriculeEtudiant' => $matricule,
            'idProgramme' => $informations['idProgramme'],
            'grade' => $informations['idGrade'],
        );
        $this->db->insert('dossieretudiant', $infoProgramme);
    }

    function ecrire_log($noteAvant, $noteApres, $coteAvant, $coteApres, $sigle) {
        $format = 'DATE_ATOM';
        $time = time();

        $date = standard_date($format, $time);

        $fp = fopen("logNotes/" . $this->session->userdata('matriculeEmploye') . ".txt", "a");
        $str = "Date :" . $date . " Matricule: [" . $this->session->userdata('matriculeEmploye') . "]- Module : [" . $sigle . "]- Note avant: [" . $noteAvant .
                "]- Note apr�s:[" . $noteApres . "]- Cote avant: [" . $coteAvant . "]- Cote apr�s: [" . $coteApres . "]\r\n";
        fputs($fp, $str);
        fclose($fp);
    }

    /*
     * fonction qui ajoute un module dans la base de donnees 
     */

 /*   function creer_nouveau_module($infoModule) {
        $data = '';
//        $infoModule['hrCours'] = str_replace(',', '.', trim($infoModule['hrCours']));
//        $infoModule['hrTD'] = str_replace(',', '.', trim($infoModule['hrTD']));
//        $infoModule['hrTP'] = str_replace(',', '.', trim($infoModule['hrTP']));
        $infoModule['hrPerso'] = str_replace(',', '.', trim($infoModule['hrPerso']));
        //Debut Modif Cheikh 26/11/2015
        $infoModule['volumeCM'] = str_replace(',', '.', trim($infoModule['volumeCM']));
        $infoModule['volumeTD'] = str_replace(',', '.', trim($infoModule['volumeTD']));
        $infoModule['volumeTP'] = str_replace(',', '.', trim($infoModule['volumeTP']));
        //Fin Modif Cheikh 26/11/2015
        $this->db->where(array('sigle' => strtoupper($infoModule['sigle'])));
        $query = $this->db->get('module');
        if ($query->num_rows() > 0) {
            $data['informations'] = 'Le sigle <b>' . $infoModule['sigle'] . '</b> existe déja.';
            $data['valide'] = false;
            return $data;
        } else {

            //insertion du module
            $this->db->set('sigle', strtoupper($infoModule['sigle']));
            $this->db->set('idCycle', $infoModule['Cycle']);
            $this->db->set('typeModule', $infoModule['typeMod']);
            $this->db->set('titre', $infoModule['titreMod']);
            $this->db->set('description', $infoModule['description']);
            $this->db->set('idDepartement', $infoModule['departement']);
            $this->db->set('nbCredits', $infoModule['nbrCredits']);
             $this->db->set('coefficient', $infoModule['coefficient']);//add by MedBakar 26-02-2020
            $this->db->set('hrsPerso', $infoModule['hrPerso']);
//            $this->db->set('hrsCours', $infoModule['hrCours']);
//            $this->db->set('hrsTD', $infoModule['hrTD']);
//            $this->db->set('hrsTP', $infoModule['hrTP']);
            // Debut Modif Cheikh 26/11/2016
            $this->db->set('volumeCM', $infoModule['volumeCM']);
            $this->db->set('volumeTD', $infoModule['volumeTD']);
            $this->db->set('volumeTP', $infoModule['volumeTP']);
            // Fin Modif Cheikh 26/11/2016
            $this->db->set('prerequisCredits', $infoModule['nbrCreditsPre']);
            $this->db->set('prerequisModule', $infoModule['prerequis']);
            $this->db->set('corequisModule', $infoModule['corequis']);
            $this->db->set('semestreActivation', $infoModule['annee'] . $infoModule['session']);
            $this->db->set('professeurResponsable', $infoModule['prof']);
            $this->db->set('sigleunite', $infoModule['unite']);
            $this->db->set('semestreDesactivation', null);
            $this->db->insert('Module');

            //insertion dans modprog
            if (is_array($infoModule['progs'])) {
                foreach ($infoModule['progs'] as $prog) {
                    $info = array(
                        'sigle' => strtoupper($infoModule['sigle']), /* ajout strtoupper Roger 2.1.3 */
 /*                       'idProgramme' => $prog);
                    $this->db->insert('modprog', $info);
                }
            }

            $data['informations'] = "Le module de sigle  <b>" . strtoupper($infoModule['sigle']) . '</b> a �t� cr��.';
            $data['valide'] = true;
        }
        return $data;
    }
*/
    //*-------------------------------------------------
        function creer_nouveau_module($infoModule) {
        $data = '';
//        $infoModule['hrCours'] = str_replace(',', '.', trim($infoModule['hrCours']));
//        $infoModule['hrTD'] = str_replace(',', '.', trim($infoModule['hrTD']));
//        $infoModule['hrTP'] = str_replace(',', '.', trim($infoModule['hrTP']));
        $infoModule['hrPerso'] = str_replace(',', '.', trim($infoModule['hrPerso']));
        //Debut Modif Cheikh 26/11/2015
        $infoModule['volumeCM'] = str_replace(',', '.', trim($infoModule['volumeCM']));
        $infoModule['volumeTD'] = str_replace(',', '.', trim($infoModule['volumeTD']));
        $infoModule['volumeTP'] = str_replace(',', '.', trim($infoModule['volumeTP']));
        //Fin Modif Cheikh 26/11/2015
        $this->db->where(array('sigle' => strtoupper($infoModule['sigle'])));
        $query = $this->db->get('module');
        if ($query->num_rows() > 0) {
            $data['informations'] = 'Le sigle <b>' . $infoModule['sigle'] . '</b> existe déja.';
            $data['valide'] = false;
            return $data;
        } else {

            //insertion du module
            $this->db->set('sigle', strtoupper($infoModule['sigle']));
            $this->db->set('idCycle', $infoModule['Cycle']);
            $this->db->set('typeModule', $infoModule['typeMod']);
            $this->db->set('titre', $infoModule['titreMod']);
            $this->db->set('description', $infoModule['description']);
            $this->db->set('idDepartement', $infoModule['departement']);
            $this->db->set('nbCredits', $infoModule['nbrCredits']);
             $this->db->set('coefficient', $infoModule['coefficient']);//add by MedBakar 26-02-2020
            $this->db->set('hrsPerso', $infoModule['hrPerso']);
//            $this->db->set('hrsCours', $infoModule['hrCours']);
//            $this->db->set('hrsTD', $infoModule['hrTD']);
//            $this->db->set('hrsTP', $infoModule['hrTP']);
            // Debut Modif Cheikh 26/11/2016
            $this->db->set('volumeCM', $infoModule['volumeCM']);
            $this->db->set('volumeTD', $infoModule['volumeTD']);
            $this->db->set('volumeTP', $infoModule['volumeTP']);
            // Fin Modif Cheikh 26/11/2016
            //$this->db->set('prerequisCredits', $infoModule['nbrCreditsPre']);
            //$this->db->set('prerequisModule', $infoModule['prerequis']);
            //$this->db->set('corequisModule', $infoModule['corequis']);
            $this->db->set('semestreActivation', $infoModule['annee'] . $infoModule['session']);
            $this->db->set('professeurResponsable', $infoModule['prof']);
            $this->db->set('sigleunite', $infoModule['unite']);
            $this->db->set('semestreDesactivation', null);
            $this->db->insert('Module');

            //insertion dans modprog
            //if (is_array($infoModule['progs'])) {
             //   foreach ($infoModule[] as $prog) {
                    $info = array(
                        'sigle' => strtoupper($infoModule['sigle']), /* ajout strtoupper Roger 2.1.3 */
                        'idProgramme' => $infoModule['departement']);
                  //  echo"<br>modprog";
                 //   print_r($info);
                    $this->db->insert('modprog', $info);
              //  }
            //}

            $data['informations'] = "L'élément de sigle  <b>" . strtoupper($infoModule['sigle']) . '</b> a été créé.';
            $data['valide'] = true;
        }
        return $data;
    }
    //*-----------
    function generer_mdp() {
        $mdp = "";
        $possible = "123456789abcdefghijlkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRTUVWXYZ";
        $maxlength = 10;
        $i = 0;
        $numbers = 0;
        while (strlen($mdp) < $maxlength) {
            $char = substr($possible, mt_rand(0, strlen($possible) - 1), 1);
            if (intval($char) > 0)
                $numbers++;

//le mot de passe doit contenir au moins un chiffre
//si nous n<avons pas eu encore de chiffre on force le dernier charactere a etre un chiffre
            if (strlen($mdp) == 9 && $numbers == 0)
                $char = mt_rand(1, 9);

            $mdp .= $char;
        }
        return $mdp;
    }

// *****************************************************Fonctions BD **************************************
    function get_id_departement($nomDep) {
        $idDep = NULL;
        $query = $this->db->query("SELECT DISTINCT `idDepartement` FROM departement WHERE `nom` = '$nomDep'");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $idDep['idDep'] = $row['idDepartement'];
            }
        }
        return $idDep;
    }

    function get_infos_groupe($idGroupe) {
        $infos = array();
        $this->db->where('idGroupe', $idGroupe);
        $query = $this->db->get('groupe');

        return ($query->result_array());
    }

    function get_periode_groupe($idGroupe) {
        $periode = NULL;
        $query = $this->db->query("SELECT `idPeriode`,`idLocal`  FROM `horaire` WHERE `idGroupe` = '$idGroupe'");
        $query2 = $this->db->query("SELECT `sigle`,`typeGroupe`, `numGroupe`   FROM `groupe` WHERE `idGroupe` = '$idGroupe'");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $periode['idGroupe'][] = $idGroupe;
                $periode['idPeriode'][] = $row['idPeriode'];
                $periode['idLocal'][] = $row['idLocal'];

                if ($query2->num_rows() > 0) {
                    foreach ($query2->result_array() as $row) {
                        $periode['sigle'][] = $row['sigle'];
                        $periode['typeGroupe'][] = $row['typeGroupe'];
                        $periode['numGroupe'][] = $row['numGroupe'];
                    }
                }
            }
        }
        return $periode;
    }

    function get_idGroupe($sigleGroupe) {

        $idgr = NULL;
        $query = $this->db->query("SELECT DISTINCT `idGroupe` FROM groupe WHERE `sigle` ='$sigleGroupe'  order by `idGroupe`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $idgr['idGroupe'][] = $row['idGroupe'];
            }
        }
        return $idgr;
    }

    /*
     * retourne tous les sigle de cours qui existe dans le systeme
     */

    function get_cours_actif() {
        $sessionCourante = $this->get_session_courante();
        $sessionCourante = $sessionCourante['annee'][0] . $sessionCourante['semestre'][0];
        $sigle = '';
        $query = $this->db->get('module');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                if ($row['semestreDesactivation'] == NULL)
                    if ($row['semestreActivation'] <= $sessionCourante)
                        $sigle['sigleCours'][] = $row['sigle'];
                    elseif ($row['semestreDesactivation'] >= $sessionCourante)
                        if ($row['semestreActivation'] <= $sessionCourante)
                            $sigle['sigleCours'][] = $row['sigle'];
            }
        }
        return $sigle;
    }

    function get_cours_actif_enseignant() {
        $sessionCourante = $this->get_session_courante();
        $sessionCourante = $sessionCourante['annee'][0] . $sessionCourante['semestre'][0];

        $sigle = '';
        $query = $this->db->get('module');
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                if ($row['semestreDesactivation'] == NULL) {
                    if ($row['semestreActivation'] <= $sessionCourante) {
                        $sigle['sigleCours'][] = $row['sigle'];
                        $sigle['titre'][] = $row['titre'];
                        $sigle['matriculeResponsable'][] = $row['professeurResponsable']; /* Roger */
                        $this->db->where('matriculeEmploye', $row['professeurResponsable']);
                        $query2 = $this->db->get('employe');
                        if ($query2->num_rows() > 0) {
                            foreach ($query2->result_array() as $row2) {
                                $sigle['nomResponsable'][] = $row2['nom'];
                                $sigle['prenomResponsable'][] = $row2['prenom'];
                            }
                        }
                    }
                } elseif ($row['semestreDesactivation'] >= $sessionCourante) {
                    if ($row['semestreActivation'] <= $sessionCourante) {
                        $sigle['sigleCours'][] = $row['sigle'];
                        $sigle['titre'][] = $row['titre'];
                        $this->db->where('matriculeEmploye', $row['professeurResponsable']);
                        $query2 = $this->db->get('employe');
                        if ($query2->num_rows() > 0) {
                            foreach ($query2->result_array() as $row2) {
                                $sigle['nomResponsable'][] = $row2['nom'];
                                $sigle['prenomResponsable'][] = $row2['prenom'];
                            }
                        }
                    }
                }
            }
        }
        return $sigle;
    }

    function recuperer_sigle_cours() {
        $sigleCours = NULL;
        $query = $this->db->query("SELECT Distinct `sigle` FROM groupe order by `sigle`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $sigleCours['sigleCours'][] = $row['sigle'];
            }
        }
        return $sigleCours;
    }

    function recuperer_programme() {
        $progs = NULL;
        $query = $this->db->query("SELECT `idProgramme` ,`nom`  FROM programme  order by `nom`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $progs['idProg'][] = $row['idProgramme'];
                $progs['nom'][] = $row['nom'];
            }
        }
        return $progs;
    }

    /*
     * fonction qui recupere de la bd les infos du cycle
     */

    function recuperer_cycle() {
        $cycle = NULL;
        $query = $this->db->query("SELECT Distinct `idCycle`, `nom` FROM `cycle` order by `nom`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $cycle['nomCycle'][] = $row['nom'];
                $cycle['idCycle'][] = $row['idCycle'];
            }
        }
        return $cycle;
    }

    /*
     * fonctiion qui recupere de la bd les infos du departement
     */

    function recuperer_liste_sigles($etudiant, $annee, $semestre) {
        $sigles = array();

        $this->db->where(array('matriculeEtudiant' => $etudiant, 'annee' => $annee, 'semestre' => $semestre));
        $this->db->select('sigle');
        $this->db->from('listeetudiants');
        $this->db->join('groupe', 'listeetudiants.idGroupe = groupe.idGroupe');

        $res = $this->db->get();
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $sigles[] = $row['sigle'];
            }
        }
        return $sigles;
    }

    function recuperer_liste_cours($etudiants, $annee, $semestre) {
        $cours = array();
        if (is_array($etudiants)) {
            foreach ($etudiants as $etudiant) {
                $sigles = $this->recuperer_liste_sigles($etudiant, $annee, $semestre);

                if (is_array($sigles)) {
                    foreach ($sigles as $sigle)
                        if (!in_array($sigle, $cours))
                            $cours[] = $sigle;
                }
            }
        }
        return $cours;
    }

    function recuperer_departement() {
        $info_departement = NULL;
        $query = $this->db->query("SELECT  DISTINCT `nom`, `idDepartement` FROM `departement`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info_departement['nomDep'][] = $row['nom'];
                $info_departement['idDepartement'][] = $row['idDepartement'];
            }
        }
        return $info_departement;
    }

    function recuperer_note_par_sigle($c, $etudiants, $annee, $semestre) {
        $resultat = '';
        if (is_array($etudiants)) {
            foreach ($etudiants as $matricule) {
                $this->db->where(array('matriculeEtudiant' => $matricule, 'annee' => $annee, 'semestre' => $semestre, 'sigle' => $c));
                $this->db->distinct();
                $this->db->order_by('note');
                $res = $this->db->get('planetudes');
                if ($res->num_rows() > 0) {
                    foreach ($res->result_array() as $row) {
                        $resultat[$matricule] = $row['note'];
                    }
                } else
                    $resultat[$matricule] = 'N/A';
            }
        }
        return $resultat;
    }

    function recuperer_note_par_classe($etudiants, $annee, $semestre) {
        $resultat = '';

        $cours = $this->recuperer_liste_cours($etudiants, $annee, $semestre);
        if (is_array($cours))
            foreach ($cours as $c) {
                $resultat[] = array('sigle' => $c,
                    'notes' => $this->recuperer_note_par_sigle($c, $etudiants, $annee, $semestre));
            }

        return $resultat;
    }

    /*
     * Cette methode permet de mettre a jour un module 
     * 
     * @param - info_module: les informations du module
     */

    function mettre_a_jour_module($info_module) {
        array_pop($info_module);
//        $info_module['hrCours'] = str_replace(',', '.', trim($info_module['hrCours']));
//        $info_module['hrTD'] = str_replace(',', '.', trim($info_module['hrTD']));
//        $info_module['hrTP'] = str_replace(',', '.', trim($info_module['hrTP']));
        
        $info_module['volumeCM'] = str_replace(',', '.', trim($info_module['volumeCM']));
        $info_module['volumeTD'] = str_replace(',', '.', trim($info_module['volumeTD']));
        $info_module['volumeTP'] = str_replace(',', '.', trim($info_module['volumeTP']));
        $info_module['volumeProjet'] = str_replace(',', '.', trim($info_module['volumeProjet']));
        $this->db->where('sigle', $info_module['sigle']);
        $info = array(
            'sigle' => $info_module['sigle'],
            'typeModule' => $info_module['typeModule'],
            'professeurResponsable' => $info_module['professeurResponsable'],
            'sigleunite' => $info_module['unite'],
            'description' => $info_module['description'],
            'titre' => $info_module['titreMod'],
            'nbCredits' => $info_module['nbrCredits'],
            'coefficient' => $info_module['coefficient'],
            'idDepartement' => $info_module['idDepartement'],
            'idCycle' => $info_module['idCycle'],
            'volumeCM' => $info_module['volumeCM'],
            'volumeTD' => $info_module['volumeTD'],
            'volumeTP' => $info_module['volumeTP'],
            'hrsPerso' => $info_module['volumeProjet']);

        $this->db->update('module', $info);

        // supprimer les anciens programme
        $this->db->where('sigle', $info_module['sigle']);
        $this->db->delete('modprog');
        //ajouter un programme
        for ($i = 0; $i < count($info_module['progs']); $i++) {
            $infoProgramme = array('idProgramme' => $info_module['progs'][$i],
                'sigle' => $info_module['sigle']);
            $this->db->insert('modprog', $infoProgramme);
        }
    }

    /*
     * Fonction qui r�cup�re les informations du groupe
     * soit le sigle de module,numero de groupe et l'enseignant
     */

    function recuperer_info_groupe($idGroupe) {
        $info_module = NULL;
        $query = $this->db->query("SELECT * FROM groupe WHERE idGroupe='" . $idGroupe . "'");

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info_module = $row;
                $info_module['sigle'] = $row['sigle'];
                $info_module['numGroupe'] = $row['numGroupe'];
                $info_module['typeGroupe'] = $row['typeGroupe'];
                $this->db->where('matriculeEmploye', $row['matriculeEmploye']);
                $res = $this->db->get('employe');
                if ($res->num_rows() > 0) {
                    foreach ($res->result_array() as $row1) {
                        $info_module['nom'] = $row1['nom'];
                        $info_module['prenom'] = $row1['prenom'];
                    }
                }
                $this->db->where('sigle', $row['sigle']);
                $res = $this->db->get('module');
                if ($res->num_rows() > 0) {
                    foreach ($res->result_array() as $row1) {
                        $info_module['titre'] = $row1['titre'];
                        $info_module['nbCredits'] = $row1['nbCredits'];
                    }
                }
            }
        }
        return $info_module;
    }

    /*
     * fonction qui recupere les informations du module.
     * Nom et prenom du professeur responsable,
     * Titre du module et le nbre de credits
     * 
     * @param - sigle: le sigle du cours a recuperer
     */

    function recuperer_module($sigle) {
        $info_module = NULL;
        $query = $this->db->query("SELECT * FROM module WHERE sigle='" . $sigle . "'");

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info_module = $row;
                $info_module['titre'] = $row['titre'];
                $info_module['nbCredits'] = $row['nbCredits'];

                $this->db->where('matriculeEmploye', $info_module['professeurResponsable']);
                $res = $this->db->get('employe');
                if ($res->num_rows() > 0) {
                    foreach ($res->result_array() as $row1) {
                        $info_module['nom'] = $row1['nom'];
                        $info_module['prenom'] = $row1['prenom'];
                    }
                }
                // Unite
                $this->db->where('sigle', $info_module['sigleunite']);
                $res = $this->db->get('unite');
                if ($res->num_rows() > 0) {
                    foreach ($res->result_array() as $row1) {
                        $info_module['titreunite'] = $row1['titre'];
                    }
                }


                $this->db->where('sigle', $sigle);
                $res = $this->db->get('modprog');
                if ($res->num_rows() > 0) {
                    foreach ($res->result_array() as $row) {
                        $info_module['programme'] = $row['idProgramme'];
                    }
                }
            }
        }
        return $info_module;
    }

    function get_mod_prog_info($sigle) {
        $info_module = '';
        $this->db->where('sigle', $sigle);
        $res = $this->db->get('modprog');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $info_module['idProgramme'][] = $row['idProgramme'];
                $this->db->where('idProgramme', $row['idProgramme']);
                $query = $this->db->get('programme');
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row2) {
                        $info_module['nomProgramme'][] = $row2['nom'];
                    }
                }
            }
        }
        return $info_module;
    }

    function recuperer_note_par_classe_conseil($matriculeEtudiant, $annee, $semestre, $sigle) {
        $data = '';
        for ($i = 0; $i < count($matriculeEtudiant) && $matriculeEtudiant != null; $i++) {
            $this->db->where(array('matriculeEtudiant' => $matriculeEtudiant[$i], 'sigle' => $sigle, 'annee' => $annee, 'semestre' => $semestre));

            $query = $this->db->get('planetudes');
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                $data[$i]['note'] = $row['note'];
                $data[$i]['cote'] = $row['cote'];
                $data[$i]['lien'] = $row['lien'];
            }
        }

        return $data;
    }

    /*
     * fonction qui recupere le nom est la matricule des employer de la BD
     */

    function recuperer_enseignant() {
        $employer = NULL;
        $query = $this->db->query("SELECT  `matriculeEmploye`, 
            `idDepartement`,   `nom`, `prenom` FROM `employe`
            where idDepartement is not null and idDepartement
            != 'DPT-SRV' order by `matriculeEmploye`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $employer['matriculeEmploye'][] = $row['matriculeEmploye'];
                $employer['idDep'][] = $row['idDepartement'];
                $employer['nom'][] = $row['nom'];
                $employer['prenom'][] = $row['prenom'];
            }
        }
        return $employer;
    }

    function get_professeurs() {
        $employer = NULL;
        $query = $this->db->get('employe');
        if ($query->result_array() > 0)
            foreach ($query->result_array() as $row) {
                $profil = explode(',', $row['idProfil']);
                foreach ($profil as $professorProfile) {
                    if ($professorProfile == 'professeur') {
                        $employer['matricule'][] = $row['matriculeEmploye'];
                        $employer['nom'][] = $row['nom'];
                        $employer['prenom'][] = $row['prenom'];
                    }
                }
            }
        return $employer;
    }

// added by Hafedh 22/10/2015
    function get_unites() {
        $unite = NULL;
        $query = $this->db->get('unite');
        if ($query->result_array() > 0)
            foreach ($query->result_array() as $row) {

                $unite['sigle'][] = $row['sigle'];
                $unite['titre'][] = $row['titre'];
                $unite['description'][] = $row['description'];
                $unite['idProgramme'][] = $row['idProgramme'];
                $unite['semestre'][] = $row['semestre'];
                $unite['semestreAct'][] = $row['semestreAct'];
                $unite['anneeAct'][] = $row['anneeAct'];
            }
        return $unite;
    }

    function get_session_de_desactivation($sigle) {
        $this->db->where(array('sigle' => $sigle));
        $res = $this->db->get('module');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                return $row['semestreDesactivation'];
            }
        }
        return NULL;
    }

    function reactiver_module($sigle) {
        $this->db->where('sigle', $sigle);
        $this->db->set('semestreDesactivation', NULL);
        $this->db->update('module');
    }

    /* function descativer_module($sigle, $annee, $semestre)
      // Retourne FALSE si des �tudiants sont inscrits au semestre de d�sactivation
      // Fonction annul�e, car si inscrits � ce semestre, c'est OK, et d'autre part pb �ventuels si �quivalence pous tard.
      // Remplac�e par desactiver_module($sigle, $annee, $semestre)
      {
      $this->db->where(array('sigle'=> $sigle,'annee' => $annee, 'semestre' => $semestre));
      $query = $this->db->get('planetudes');
      if ($query->num_rows() > 0)
      {
      return FALSE;
      }
      else
      {
      $date = $annee.$semestre;
      $this->db->where('sigle', $sigle);
      $this->db->set('semestreDesactivation', $date);
      $this->db->update('module');
      return TRUE;
      }
      }
     */

    function desactiver_module($sigle, $annee, $semestre) {
    // Retourne FALSE si le semestre de d�sactivation choisi est ant�ieur au semestre d'activation
    // R��criture de descativer_module pour 2.2.1
        // R�cup�ration des infos d'activation du module.
        $informations_module = $this->scolarite_modele->recuperer_module($sigle);
        $semestre_activation = $informations_module['semestreActivation'];
        $semestre_desactivation_propose = $annee . $semestre;

        if ($semestre_desactivation_propose < $semestre_activation) {
            return FALSE;
        } else {
            $date = $annee . $semestre;
            $this->db->where('sigle', $sigle);
            $this->db->set('semestreDesactivation', $date);
            $this->db->update('module');
            return TRUE;
        }
    }

    function recuperer_sigle_groupe() {
        $sigle_groupe = NULL;
        $query = $this->db->query("SELECT `idGroupe` FROM `groupe` order by `idGroupe`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $sigle_groupe['idGroupe'][] = $row['idGroupe'];
            }
        }
        return $sigle_groupe;
    }

    function recuperer_sigle_groupe_horaire($infoDateAnnee) {
        $sigle_groupe = NULL;
        $query = NULL;
        $query = $this->db->query("SELECT `idGroupe`FROM `groupe` WHERE `annee` = '$infoDateAnnee[date]' AND `semestre` ='$infoDateAnnee[session]' AND `sigle` = '$infoDateAnnee[choixCours]'");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $sigle_groupe['idGroupe'][] = $row['idGroupe'];
            }
        }
        return $sigle_groupe;
    }

    function get_groupe($annee, $semestre, $sigle) {
        $groupe = '';
        $this->db->where(array('sigle' => $sigle, 'annee' => $annee, 'semestre' => $semestre));
        $this->db->order_by('idGroupe');
        $res = $this->db->get('groupe');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $groupe[] = $row['idGroupe'];
            }
        }
        return $groupe;
    }

    function get_groupe_calendar($annee, $semestre, $sigle) {
        $groupe = '';
        $this->db->where(array('sigle' => $sigle, 'annee' => $annee, 'semestre' => $semestre));
        $this->db->order_by('idGroupe');
        $res = $this->db->get('groupe');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $groupe[] = $row['idGroupe'];
            }
        }
        return $groupe;
    }

    function get_etat_note_etudiants($matricule, $annee, $session, $sigle) {
        if ($matricule != '') {
            for ($i = 0; $i < count($matricule); $i++) {
                $this->db->where(array('matriculeEtudiant' => $matricule[$i], 'sigle' => $sigle, 'annee' => $annee, 'semestre' => $session));
                $query = $this->db->get('planetudes');
                if ($query->num_rows() > 0) {
                    $row = $query->row_array();
                    if ($row['etatNote'] != 'scolarite' && $row['etatNote'] != 'administrateur')
                        return FALSE;
                }
            }
        }
        return TRUE;
    }

    function recuperer_nom_etudiant($matricule) {
        $data = '';

        if ($matricule != '') {
            $this->db->where('matriculeEtudiant', $matricule);
            $query = $this->db->get('etudiant');
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                $data['nom'] = $row['nom'];
                $data['prenom'] = $row['prenom'];
            }
        }
        return $data;
    }

    //Alfa 17-02-2016
    function get_name_code_etudiants($matricule) {
        $courant = $this->get_session_courante();
        $semestre = $courant['semestre'][0];
        $annee = $courant['annee'][0];

        for ($i = 0; $i < sizeof($matricule); $i++) {
            $sql = "select anonymat.matriculeEtudiant as matriculeEtudiant, nom, prenom, code_ex, code_rt from anonymat natural join etudiant where annee=" . $annee . " and semestre=" . $semestre . " and anonymat.matriculeEtudiant=" . $matricule[$i];
            // $this->db->where('matriculeEtudiant', $matricule[$i]);
            //$query = $this->db->get('etudiant');
            $query = $this->db->query($sql);
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                $data[$i]['nom'] = $row['nom'];
                $data[$i]['prenom'] = $row['prenom'];
                $data[$i]['code_ex'] = $row['code_ex'];
                $data[$i]['code_rt'] = $row['code_rt'];
                $data[$i]['matriculeEtudiant'] = $row['matriculeEtudiant'];
            }
        }
        return $data;
    }

    function get_name_etudiants($matricule) {
        $data = '';
        if (!is_array($matricule)) {
            $this->db->where('matriculeEtudiant', $matricule);
            $query = $this->db->get('etudiant');
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                $data['nom'] = $row['nom'];
                $data['prenom'] = $row['prenom'];
            }
        } else {
            if ($matricule != '') {
                for ($i = 0; $i < count($matricule); $i++) {
                    $this->db->where('matriculeEtudiant', $matricule[$i]);
                    $query = $this->db->get('etudiant');
                    if ($query->num_rows() > 0) {
                        $row = $query->row_array();
                        $data[$i]['nom'] = $row['nom'];
                        $data[$i]['prenom'] = $row['prenom'];
                    }
                }
            }
        }
        return $data;
    }

    function recuperer_liste_etudiants($sigle, $annee, $semestre) {
        $matricules = '';
        $this->db->where(array('sigle' => $sigle, 'annee' => $annee, 'semestre' => $semestre));
        $this->db->select('matriculeEtudiant');
        $this->db->distinct();
        $this->db->from('planetudes');
        $this->db->order_by('note', 'desc');

        $res = $this->db->get();

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $matricules[] = $row['matriculeEtudiant'];
            }
        }
        return $matricules;
    }

    function recuperer_liste_etudiants_conseil($sigle, $annee, $semestre, $typeRapport, $session = 'N',$idGroupe='') {
        $abondon = 'AB';
        $equivalence = 'EQ';
        $matricules = '';
              
        if ($session != 'R') {
          if($idGroupe==''){
            $this->db->where(array('p.sigle' => $sigle, 'p.annee' => $annee,
                'p.semestre' => $semestre, 'p.lien !=' => $abondon, 'p.cote !=' => $equivalence,'l.idGroupe !='=>$idGroupe));
          }else{
            $this->db->where(array('p.sigle' => $sigle, 'p.annee' => $annee,
                'p.semestre' => $semestre, 'p.lien !=' => $abondon, 'p.cote !=' => $equivalence,'l.idGroupe ='=>$idGroupe));
        } }else {
           if($idGroupe==''){
                $this->db->where(array('p.sigle' => $sigle, 'p.annee' => $annee,
                'p.semestre' => $semestre, 'p.lien !=' => $abondon, 'p.cote !=' => $equivalence, 'p.note <' => 10,'l.idGroupe !='=>$idGroupe));
           }else
            $this->db->where(array('p.sigle' => $sigle, 'p.annee' => $annee,
                'p.semestre' => $semestre, 'p.lien !=' => $abondon, 'p.cote !=' => $equivalence, 'p.note <' => 10,'l.idGroupe ='=>$idGroupe));
        }
        
        //$this->db->from('planetudes p'); 
        // $this->db->join('anonymat a', 'a.matriculeEtudiant=p.matriculeEtudiant','left');
        //$this->db->select('p.matriculeEtudiant,a.code_ex');
        // $this->db->distinct();
        // $this->db->from('planetudes');
        $this->db->select('p.matriculeEtudiant');
        $this->db->distinct();

        $this->db->from('planetudes p');
        //$this->db->join('anonymat a', 'p.matriculeEtudiant=a.matriculeEtudiant and p.annee=a.annee and p.semestre=a.semestre');
        $this->db->join('anonymat a JOIN listeetudiants l', 'p.matriculeEtudiant=a.matriculeEtudiant and p.annee=a.annee and p.semestre=a.semestre and `p`.`matriculeEtudiant`=`l`.`matriculeEtudiant`');

       
        if ($typeRapport == 'tri par matricule') {
            $this->db->order_by('p.matriculeEtudiant');
        } else {
            $this->db->order_by('a.code_ex', 'asc');
        }

        $res = $this->db->get();
// print($this->db->last_query());//MAB test
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $matricules[] = $row['matriculeEtudiant'];
            }
        }
        return $matricules;
    }

    /*
     * liste des etudiants inscrits dans un cours sans les etudiants qui ont de s equivalence 
     * ou qui sont abondonnee
     */

    function recuperer_liste_etudiants_module($sigle, $annee, $semestre) {
        $matricules = '';

        $this->db->where(array('sigle' => $sigle, 'annee' => $annee,
            'semestre' => $semestre, 'lien!=' => 'AB', 'cote!=' => 'AB'));
        $this->db->select('matriculeEtudiant');
        $this->db->distinct();
        $this->db->from('planetudes');
        $this->db->order_by('note', 'desc');

        $res = $this->db->get();

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $matricules[] = $row['matriculeEtudiant'];
            }
        }
        return $matricules;
    }

    function get_etudiants($programme) {
        $matricules = '';
        $this->db->where(array('idProgramme' => $programme));
        $this->db->select('matriculeEtudiant');
        $this->db->distinct();
        $res = $this->db->get('dossieretudiant');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $this->db->where(array('matriculeEtudiant' => $row['matriculeEtudiant'], 'actif' => 1));
                $query = $this->db->get('etudiant');
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        $matricules[] = $row['matriculeEtudiant'];
                    }
                }
            }
        }
        return $matricules;
    }

    /*
     * fonction pour modifier les notes si les cotes ne sont pas encore gener�es
     */

    function enregistrer_note_modifie_sans_cote($data, $index, $note) {
        $this->db->where(array('matriculeEtudiant' => $data['matricule'][$index], 'sigle' => $data['sigle'], 'annee' => $data['annee'], 'semestre' => $data['session']));
        $query = $this->db->get('planetudes');
        if ($query->num_rows() > 0) {
            if ($note != NULL) {
                $info = array(
                    'note' => $note
                );
                $this->db->where(array('matriculeEtudiant' => $data['matricule'][$index], 'sigle' => $data['sigle'], 'annee' => $data['annee'], 'semestre' => $data['session']));
                $this->db->update('planetudes', $info);
            }
        }
        $this->db->where('matriculeEtudiant', $data['matricule'][$index]);
        $query2 = $this->db->get('dossierEtudiant');
        if ($query2->num_rows() > 0) {
            $info = array(
                'indicateurBulletinACalculer' => '1'
            );
            $this->db->where('matriculeEtudiant', $data['matricule'][$index]);
            $this->db->update('dossierEtudiant', $info);
        }
    }

    /*
     * fonction pour modifier les notes, enregistre les cotes
     */

    function enregistrer_note_modifie($data, $index, $note) {
        $this->db->where(array('matriculeEtudiant' => $data['matricule'][$index], 'sigle' => $data['sigle'], 'annee' => $data['annee'], 'semestre' => $data['session']));
        $query = $this->db->get('planetudes');
        if ($query->num_rows() > 0) {
            if ($note != NULL) {
                $info = array(
                    'note' => $note,
                    'cote' => strtoupper($data['cote' . $index])
                );
                $this->db->where(array('matriculeEtudiant' => $data['matricule'][$index], 'sigle' => $data['sigle'], 'annee' => $data['annee'], 'semestre' => $data['session']));
                $this->db->update('planetudes', $info);
                $this->db->where('matriculeEtudiant', $data['matricule'][$index]);
                $query2 = $this->db->get('dossierEtudiant');
                if ($query2->num_rows() > 0) {
                    $info = array(
                        'indicateurBulletinACalculer' => '1'
                    );
                    $this->db->where('matriculeEtudiant', $data['matricule'][$index]);
                    $this->db->update('dossierEtudiant', $info);
                }
            }
        }
    }

    function get_note_cote_etudiant($matriculeEtudiant, $annee, $session, $sigle) {
        $data = '';
        for ($i = 0; $i < count($matriculeEtudiant) && $matriculeEtudiant != null; $i++) {
            $this->db->where(array('matriculeEtudiant' => $matriculeEtudiant[$i], 'sigle' => $sigle, 'annee' => $annee, 'semestre' => $session));
            $query = $this->db->get('planetudes');
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                $data[$i]['note'] = $row['note'];
                $data[$i]['cote'] = $row['cote'];
            }
        }
        return $data;
    }

    function get_cote($matriculeEtudiant, $annee, $session, $sigle) {
        $data = '';

        for ($i = 0; $i < count($matriculeEtudiant) && $matriculeEtudiant != NULL; $i++) {
            $this->db->where(array('matriculeEtudiant' => $matriculeEtudiant[$i], 'sigle' => $sigle, 'annee' => $annee, 'semestre' => $session));
            $query = $this->db->get('planetudes');
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                $data[$i]['cote'] = $row['cote'];
            }
        }
        return $data;
    }

    function get_lien($matriculeEtudiant, $annee, $session, $sigle) {
        $lien = NULL;

        $this->db->where(array('matriculeEtudiant' => $matriculeEtudiant, 'sigle' => $sigle, 'annee' => $annee, 'semestre' => $session));
        $query = $this->db->get('planetudes');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $lien = $row['lien'];
        }
        return $lien;
    }

    function get_notes_isvalid($matriculeEtudiant, $annee, $session, $sigle) {
        for ($i = 0; $i < count($matriculeEtudiant) && $matriculeEtudiant != null; $i++) {
            $this->db->where(array('matriculeEtudiant' => $matriculeEtudiant[$i], 'sigle' => $sigle, 'annee' => $annee, 'semestre' => $session));
            $query = $this->db->get('planetudes');
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                if ($row['etatNote'] != 'scolarite')
                    return false;
            }
        }
        return true;
    }

    function etudiant_deja_inscrit($matricule, $sigle, $annee, $semestre) {
        $this->db->where(array('matriculeEtudiant' => $matricule, 'sigle' => $sigle, 'annee' => $annee, 'semestre' => $semestre));
        $query = $this->db->get("planetudes");
        return $query->num_rows() > 0;
    }

    /*
     * fonction qui sert juste a ecrire le titre si les cotes sont deja validees ou non 
     */

    function get_notes_isvalid_titre($matriculeEtudiant, $annee, $session, $sigle) {
        for ($i = 0; $i < count($matriculeEtudiant) && $matriculeEtudiant != null; $i++) {
            $this->db->where(array('matriculeEtudiant' => $matriculeEtudiant[$i], 'sigle' => $sigle, 'annee' => $annee, 'semestre' => $session));
            $query = $this->db->get('planetudes');
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                if ($row['etatNote'] == 'administrateur') {
                    return false;
                }
            }
        }
        return true;
    }

    function get_info_etudiant_rattrapage($annee, $session, $sigle) {
        $data = array();

        $this->db->where(array('sigle' => $sigle, 'annee' => $annee, 'semestre' => $session, 'lien' => 'RT'));
        $res = $this->db->get('planetudes');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $data['matricule'][] = $row['matriculeEtudiant'];
                $data['note'][] = $row['note'];
                $data['cote'][] = $row['cote'];
            }
        }

        return $data;
    }

    function enregistrer_note_rattrapage($post, $index, $note, $cote) {
        $tableaMatricule = array_keys($post, 'on');
        if (count($tableaMatricule) == 0) {
            return FALSE;
        } else {

            $this->db->where(array('matriculeEtudiant' => $tableaMatricule[$index], 'sigle' => $post['sigle'],
                'annee' => $post['annee'], 'semestre' => $post['session']));
            $query = $this->db->get('planetudes');
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $noteAvant = $row['note'];
                    $coteAvant = $row['cote'];
                }
                $info = array(
                    'cote' => $cote,
                    'note' => $note,
                    'lien' => 'RT'
                );
                $this->db->where(array('matriculeEtudiant' => $tableaMatricule[$index], 'sigle' => $post['sigle'],
                    'annee' => $post['annee'], 'semestre' => $post['session']));
                $this->db->update('planetudes', $info);
                $this->ecrire_log($noteAvant, $note, $coteAvant, $cote, $post['sigle']);
                $this->db->where('matriculeEtudiant', $tableaMatricule[$index]);
                $query2 = $this->db->get('dossierEtudiant');
                if ($query2->num_rows() > 0) {
                    $info = array(
                        'indicateurBulletinACalculer' => '1'
                    );
                    $this->db->where('matriculeEtudiant', $tableaMatricule[$index]);
                    $this->db->update('dossierEtudiant', $info);
                }
            }

            return TRUE;
        }
    }

    function enregistrer_cote($data) {
        if (is_array($data))
            if (is_array($data['matricule']) && is_array($data['cote']) && (count($data['matricule']) == count($data['cote']))) {
                for ($i = 0; $i < count($data['matricule']); $i++) {

                    $this->db->where(array('matriculeEtudiant' => $data['matricule'][$i], 'sigle' => $data['sigle'], 'annee' => $data['annee'], 'semestre' => $data['session']));
                    $query = $this->db->get('planetudes');
                    if ($query->num_rows() > 0) {
                        $info = array(
                            'cote' => $data['cote'][$i],
                            'etatNote' => 'administrateur'
                        );
                        $this->db->where(array('matriculeEtudiant' => $data['matricule'][$i], 'sigle' => $data['sigle'], 'annee' => $data['annee'], 'semestre' => $data['session']));
                        $this->db->update('planetudes', $info);
                    }
                    $this->db->where('matriculeEtudiant', $data['matricule'][$i]);
                    $query2 = $this->db->get('dossierEtudiant');
                    if ($query2->num_rows() > 0) {
                        $info = array(
                            'indicateurBulletinACalculer' => '1'
                        );
                        $this->db->where('matriculeEtudiant', $data['matricule'][$i]);
                        $this->db->update('dossierEtudiant', $info);
                    }
                }
            }
    }

    /*
     * fonction qui retourne tout les decisions existante
     */

    function get_decision() {
        $decision = NULL;
        $this->db->order_by('decisionM');
        $query = $this->db->get('decisionbulletin');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {

                $decision['idDecision'][] = $row['idDecision'];
                $decision['decisionM'][] = $row['decisionM'];
            }
        }
        return $decision;
    }

    /*
     * fonctio qui prends en parametre le matricule d'un etudiant et retourne 
     * sa decision de bulletin
     */

    function get_decision_etudiant($matriculeEtudiant) {
        $decision = NULL;
        $this->db->where('matriculeEtudiant', $matriculeEtudiant);
        $query = $this->db->get('dossieretudiant');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $decision = $row['decisionBulletin'];
            }
        }

        if ($decision == Null) {
            return 'Null';
        }

        // on recupere le nom de la decision
        else {
            $decisions = NULL;
            $this->db->where('idDecision', $decision);
            $query = $this->db->get('decisionbulletin');
            if ($query->num_rows() > 0) {
                $decisions['idDecision'] = $decision;
                foreach ($query->result_array() as $row) {
                    $decisions['decisionM'] = $row['decisionM'];
                }
            }
            return $decisions;
        }
    }

    function recuperer_annee() {
        $annees = NULL;
        $this->db->select('annee');
        $this->db->distinct();
        $this->db->order_by('annee', 'desc');
        $query = $this->db->get('groupe');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $annees['date'][] = $row['annee'];
            }
        }
        return $annees;
    }

    function recuperer_matricule_etudiant() {
        $matricule = NULL;
        $query = $this->db->query("SELECT DISTINCT `matriculeEtudiant` FROM `etudiant`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $matricule['matriculeEtudiant'][] = $row['matriculeEtudiant'];
            }
        }
        return $matricule;
    }

    /*
     * 
     */

    function recuperer_periode() {
        $periode_groupe = NULL;
        $query = $this->db->query("SELECT `idPeriode` FROM `periode`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $periode_groupe['idPeriode'][] = $row['idPeriode'];
            }
        }
        return $periode_groupe;
    }

    /*
     * 
     */

    function recuperer_salle() {
        $salle_periode = NULL;
        $query = $this->db->query("SELECT `idLocal` FROM `local` order by `idLocal`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $salle_periode['idLocal'][] = $row['idLocal'];
            }
        }
        return $salle_periode;
    }

    /**
     * retourne la liste profs ou etudiant selon les parametres
     *
     */
    function get_liste($columns, $from, $key, $value) {

        $resultat = Null;
        $query = 'Select distinct ';
        foreach ($columns as $column)
            $query .= $column . ",";
        $query = substr($query, 0, strlen($query) - 1);
        $query .= " from " . $from . "  where " . $key . " = '" . $value . "'and actif=1";
        $res = $this->db->query($query);
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $resultat[] = $row;
            }
        }

        return $resultat;
    }

    function get_etudiant_inscrit_module($sigle, $annee, $semestre) {
        $matricules = array();
        $this->db->where(array('annee' => $annee, 'semestre' => $semestre, 'sigle' => $sigle));
        $this->db->distinct();
        $this->db->select('matriculeEtudiant');
        $res = $this->db->get('planetudes');

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $matricules[] = $row['matriculeEtudiant'];
            }
        }
        return $matricules;
    }

    function recuperer_etudiants_actifs_annee($annee, $semestre) {
        $matricules = array();
        $this->db->where(array('annee' => $annee, 'semestre' => $semestre));
        $this->db->distinct();
        $this->db->select('matriculeEtudiant');
        $res = $this->db->get('planetudes');

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $matricules[] = $row['matriculeEtudiant'];
            }
        }
        return $matricules;
    }

    function recuperer_etudiants_actifs($annee, $semestre) {
        $matricules = array();
        $this->db->where(array('annee' => $annee, 'semestre' => $semestre));
        // $this->db->where("etatNote like 'administrateur'");
        $this->db->distinct();
        $this->db->select('matriculeEtudiant');
        $res = $this->db->get('planetudes');

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $matricules[] = $row['matriculeEtudiant'];
            }
        }
        return $matricules;
    }

    function bulletin_a_calculer($matricule) {
        $this->db->where(array('matriculeEtudiant' => $matricule));
        $res = $this->db->get('dossierEtudiant');

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                if ($row['indicateurBulletinACalculer'] == 1)
                    return true;
                else
                    return false;
            }
        }
//si premier semestre
        return true;
    }

    function verifier_entrees_plan_etudes($matricule) {
        $this->db->where(array('matriculeEtudiant' => $matricule));
        //$this->db->where("etatNote like 'administrateur'");
        $res = $this->db->get('planetudes');

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $tab_validation = $this->entrees_valides($row['note'], $row['cote'], $row['lien']);

                if (!$tab_validation['valide']) {
                    $this->ecrire_message_erreur($matricule, $row['sigle'], $row['semestre'], $row['annee'], $tab_validation['erreur_msg']);
                    return false;
                }
            }
            return true;
        }
        $this->ecrire_message_erreur($matricule, '', '', '', "Erreur : il n'y a aucune entr�e pour l'etudiant " + $matricule);
    }

    function entrees_valides($note, $cote, $lien) {
        $tab_validation = array('valide' => true, 'erreur_msg' => '');
        if (strtoupper(trim($note)) == "AV")
            $note = -1;
        switch (trim($note)) {
            case -1:
                if (trim($cote) != '') {
                    if (strtolower(trim($cote)) == 'eq' || strtolower(trim($cote)) == 'er') {
                        if (trim($lien) != '' && strtolower(trim($lien)) != "hp" && strtolower(trim($lien)) != "rp" && strtolower(trim($lien)) != "hv" && strtolower(trim($lien)) != "hh" && strtolower(trim($lien)) != "ob") {
                            $tab_validation['valide'] = false;
                            $tab_validation['erreur_msg'] = '�quivalence: le lien ne peut �tre que vide ou HP *' . $lien . "*";
                        }
                    } else {
                        if (strtolower(trim($lien)) == 'av') {
                            $tab_validation['valide'] = false;
                            $tab_validation['erreur_msg'] = 'Note � venir: note et cote doivent �tre vides';
                        } else {
                            $tab_validation['valide'] = false;
                            $tab_validation['erreur_msg'] = 'Pas de note : la cote ne peut �tre d�finie';
                        }
                    }
                } else {
                    if (trim($lien) == '' || (strtolower(trim($lien)) != "ab" && strtolower(trim($lien)) != 'av' && strtolower(trim($lien)) != "ar" && strtolower(trim($lien)) != "hv" && strtolower(trim($lien)) != "eq" && strtolower(trim($lien)) != "er")) {

                        $tab_validation['valide'] = false;
                        $tab_validation['erreur_msg'] = 'Note absente';
                    }
                }
                break;

            default:

                if (trim($cote) == '') {
                    if (trim($lien) == '') {
                        $tab_validation['valide'] = false;
                        $tab_validation['erreur_msg'] = 'Note valide mais cote absente.';
                    } elseif (strtolower(trim($lien)) == 'av') {
                        $tab_validation['valide'] = false;
                        $tab_validation['erreur_msg'] = 'Note � venir�: note et cote doivent �tre vides.';
                    } elseif (strtolower(trim($lien)) == 'ec' && strtolower(trim($note)) >= 10) {
                        $tab_validation['valide'] = false;
                        $tab_validation['erreur_msg'] = 'Note d�au moins 10�: le lien ne peut pas �tre EC.';
                    } elseif (strtolower(trim($lien)) == 'ab' || strtolower(trim($lien)) == 'ar') {
                        $tab_validation['valide'] = false;
                        $tab_validation['erreur_msg'] = 'Abandon�: note et cote doivent �tre vides';
                    }
                } else {
                    if (strtolower(trim($cote)) == 'eq' || strtolower(trim($cote)) == 'er') {
                        $tab_validation['valide'] = false;
                        $tab_validation['erreur_msg'] = '�quivalence�: aucune note n�est possible.';
                    } elseif (strtolower(trim($note)) < 8 && strtolower(trim($note)) > -1 && strtolower(trim($cote)) != 'f') {
                        $tab_validation['valide'] = false;
                        $tab_validation['erreur_msg'] = 'Note < 8�: la cote ne peut �tre que F.';
                    } elseif (strtolower(trim($note)) >= 8 && trim($note) < 10 && strtolower(trim($cote)) != 'fx') {
                        $tab_validation['valide'] = false;
                        $tab_validation['erreur_msg'] = 'Note comprise entre 8 et 9,99�: la cote ne peut �tre que FX.';
                    } elseif (strtolower(trim($note)) >= 10 && (strtolower(trim($cote)) == 'fx' || strtolower(trim($cote)) == 'f')) {
                        $tab_validation['valide'] = false;
                        $tab_validation['erreur_msg'] = 'Note d�au moins 10�: la cote ne peut �tre ni F ni FX.';
                    } elseif (strtolower(trim($lien)) == 'ab' || strtolower(trim($lien)) == 'ar') {
                        $tab_validation['valide'] = false;
                        $tab_validation['erreur_msg'] = 'Abandon�: note et cote doivent �tre vides.';
                    } elseif (strtolower(trim($note)) >= 10 && strtolower(trim($lien)) == 'ec') {
                        $tab_validation['valide'] = false;
                        $tab_validation['erreur_msg'] = 'Note d�au moins 10�: le lien ne peut pas �tre EC.';
                    }
                }

                break;
        }
        return $tab_validation;
    }

    function inserer_plan_detudes($matricule, $sigle, $semestre, $annee, $note, $cote, $lien) {
        $this->db->set(array('matriculeEtudiant' => $matricule, 'annee' => $annee, 'sigle' => $sigle,
            'semestre' => $semestre, 'note' => $note, 'cote' => $cote, 'lien' => $lien, 'etatNote' => 'administrateur'));
        $this->db->insert('planetudes');

        $this->db->where(array('matriculeEtudiant' => $matricule));
        $this->db->update('dossieretudiant', array('indicateurBulletinACalculer' => 1));

        return true;
    }

    function retirer_plan_etudes($matricule, $annee, $semestre, $sigle) {
        $this->db->where(array('matriculeEtudiant' => $matricule, 'annee' => $annee, 'semestre' => $semestre, 'sigle' => $sigle));
        $this->db->delete("planetudes");
    }

    function modifier_plan_etudes($matricule, $sigle, $semestre, $annee, $note, $cote, $lien) {
        $this->db->where(array('matriculeEtudiant' => $matricule, 'sigle' => $sigle, 'semestre' => $semestre, 'annee' => $annee));
        $this->db->update('planetudes', array('note' => $note, 'cote' => $cote, 'lien' => $lien));

        $this->db->where(array('matriculeEtudiant' => $matricule));
        $this->db->update('dossieretudiant', array('indicateurBulletinACalculer' => 1));

        return true;
    }

    function verifier_cours_repris($matricule) {
        $cours_uniques = array();
        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->where("(lien not in ('AB','AR') or lien is null ) ");

        $this->db->order_by('annee desc , semestre desc');
        $res = $this->db->get('planetudes');
        // print_r($res->result_array()); die;

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                if (in_array($row['sigle'], $cours_uniques))
                    $this->assigner_cours_repris($matricule, $row['sigle'], $row['semestre'], $row['annee'], $row['lien']);
                else
                    $cours_uniques[] = $row['sigle'];
            }
        }
        return true;
    }

    function verifier_cours_repris_annuel($matricule) {
        $annees = $this->recuperer_annees_a_calculer($matricule);
        if (is_array($annees) && !empty($annees))
            foreach ($annees as $annee)
                $this->verifier_cours_repris_par_annee($matricule, $annee);
        return true;
    }

    function verifier_cours_repris_par_annee($matricule, $annee) {
        $cours_uniques = array();

        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->where("((semestre = 3 and annee =" . ($annee - 1) . ") or 
            (semestre = 1 and annee =" . $annee . ") or 
                (semestre = 2 and annee =" . $annee . ")) and 
                (lien not in ('AB','AR') or lien is null ) ");

        $this->db->where(array('matriculeEtudiant' => $matricule));
        $this->db->order_by('annee desc , semestre desc');
        $res = $this->db->get('planetudes');

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                if (in_array($row['sigle'], $cours_uniques))
                    $this->assigner_cours_repris($matricule, $row['sigle'], $row['semestre'], $row['annee'], $row['lien']);
                else {
                    $this->assigner_cours_non_repris($matricule, $row['sigle'], $row['semestre'], $row['annee'], $row['lien']);
                    $cours_uniques[] = $row['sigle'];
                }
            }
        }
        return true;
    }

    function verifier_notes_cours_repris($matricule) {

        $this->db->where(array('matriculeEtudiant' => $matricule, 'lien' => 'EC'));
        $this->db->select(array('sigle', 'annee', 'semestre'));
        $res = $this->db->get('planetudes');

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row)
                if ($this->module_deja_reussi($matricule, $row['sigle'], $row['annee'], $row['semestre']))
                    $this->assigner_succes_module($matricule, $row['sigle'], $row['annee'], $row['semestre']);
        }
        return true;
    }

    function module_deja_reussi($matricule, $sigle, $annee, $semestre) {
        $this->db->where(array('matriculeEtudiant' => $matricule, 'sigle' => $sigle, 'note >=' => 10));
        $this->db->where("((annee < " . $annee . ") or (annee = " . $annee . " and semestre < " . $semestre . "))");
        $res = $this->db->get('planetudes');

        return ($res->num_rows() > 0);
    }

    function assigner_succes_module($matricule, $sigle, $annee, $semestre) {
        $this->db->where(array('matriculeEtudiant' => $matricule, 'sigle' => $sigle, 'annee' => $annee, 'semestre' => $semestre, 'lien' => 'EC'));
        $this->db->set(array('note' => 10, 'cote' => 'E', 'lien' => null));
        $this->db->update('planetudes');
    }

    function assigner_cours_non_repris($matricule, $sigle, $semestre, $annee, $lien) {

        switch (strtolower(trim($lien))) {
            case "hs":
                $nouv_lien = "HR";
                break;
            case "hh":
                $nouv_lien = "HP";
                break;
            case "er":
                $nouv_lien = "EQ";
                break;
            case "ar":
                $nouv_lien = "AB";
                break;
            case "rp":
                $nouv_lien = null;
                break;
            case "rr":
                $nouv_lien = "RT";
                break;
            default:
                $nouv_lien = strtoupper(trim($lien));
                break;
        }
        $this->db->where(array('matriculeEtudiant' => $matricule, 'sigle' => $sigle, 'semestre' => $semestre, 'annee' => $annee));
        if ($nouv_lien != null)
            $this->db->update('planetudes', array('lien' => $nouv_lien));
        else
            $this->db->update('planetudes', array('lien' => null));
    }

    function assigner_cours_repris($matricule, $sigle, $semestre, $annee, $lien) {

        switch (strtolower(trim($lien))) {
            case "hr":
            case "hs":
                $nouv_lien = "HS";
                break;
            case "hp":
            case "hh":
                $nouv_lien = "HH";
                break;
            case "eq":
            case "er":
                $nouv_lien = "ER";
                break;
            case "ab":
            case "ar":
                $nouv_lien = "AR";
                break;
            case "rt":
            default:
                $nouv_lien = "RP";
                break;
        }

        $this->db->where(array('matriculeEtudiant' => $matricule, 'sigle' => $sigle, 'semestre' => $semestre, 'annee' => $annee));
        $this->db->update('planetudes', array('lien' => $nouv_lien));
    }

    function verifier_cours_echoues($matricule) {
        $this->db->where('matriculeEtudiant =' . $matricule . ' and note is not null and note < 10 and note > -1 
                            and (lien not in ("AB","HP","RP","HR","HS") or lien is null)');
        $this->db->order_by('annee desc , semestre desc');
        $res = $this->db->get('planetudes');

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $this->assigner_echec_cours($matricule, $row['sigle'], $row['annee'], $row['semestre'], $row['lien']);
            }
        }
        return true;
    }

    function presence_echecs_pour_mention($matricule, $annee) {
        /* v�rifie s'il y a au moins 1 �chec (note <10) repris ou non. Ajout RM 6 f�vrier 2013
          pour chacun des 3 semestres d'une ann�e scolaire. Module OB ou HP ou autre, repris ou non.
          Crit�re : pas de mention si �chec (repris plus tard ou non) pour cette ann�e scolaire.
          L'ann�e transmise est Array ([3] => 2011 [1] => 2012) pour 2011-2012, semestres 3 puis 1, par exemple (tableau associatif).
         */
        foreach ($annee as $sem_scol => $an_scol) {  //on boucle sur les 3 semestres, s'ils existent
            // j'extrais ici le semestre $sem_scol et l'ann�e $an_scol 	print("$sem_scol = $an_scol<br/>") ;
            $this->db->where('matriculeEtudiant =' . $matricule . ' and note is not null and note < 10 and note > -1
				and annee =' . $an_scol . ' and semestre = ' . $sem_scol);
            $res = $this->db->get('planetudes');
            if ($res->num_rows() > 0) {
                return TRUE;
                break; // au moins un �chec trouv� : on arr�te la recherche
            }
        }
        return FALSE;
    }

    function assigner_echec_cours($matricule, $sigle, $annee, $semestre, $lien) {
        //if (strtolower(trim($lien)) == 'ob' || strtolower(trim($lien)) == 'av') {

        $this->db->where(array('matriculeEtudiant' => $matricule, 'sigle' => $sigle, 'semestre' => $semestre, 'annee' => $annee));
        $this->db->update('planetudes', array('lien' => 'EC'));
        // }
    }

    function calculer_moyennes_semestrielles($matricule) {
        $this->db->distinct();
        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->select(array('semestre', 'annee'));
        $res = $this->db->get('planetudes');

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $this->calculer_moyenne_sem($matricule, $row['annee'], $row['semestre']);
            }
        }
        return true;
    }

    function calculer_moyenne_sem($matricule, $annee, $semestre) {

        //ne seront pas calcules dans la moyenne
        $liensNonValides = array('av', 'ab', 'ar', 'er', 'hp', 'hh', 'hv', 'eq', 'hs');
        $totalCredits = 0;
        $totalNotes = 0;

        $this->db->where(array('matriculeEtudiant' => $matricule, 'annee' => $annee, 'semestre' => $semestre));
        // $this->db->where("etatNote like 'administrateur'");
        $this->db->select(array('note', 'nbCredits', 'lien', 'cote'));
        $this->db->from('planetudes');
        $this->db->join('module', 'planetudes.sigle = module.sigle');

        $res = $this->db->get();
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                if (!in_array(strtolower(trim($row['lien'])), $liensNonValides) && strtolower(trim($row['cote'])) == 'eq') {
                    if (strtolower(trim($row['lien'])) == 'ec') {
                        $totalCredits += $row['nbCredits'];
                        $totalNotes += $row['nbCredits'] * $row['note'];
                    } else {
                        $totalCredits += $row['nbCredits'];
                        $totalNotes += $row['nbCredits'] * $row['note'];
                    }
                } else {
                    $totalCredits += $row['nbCredits'];
                    $totalNotes += $row['nbCredits'] * $row['note'];
                }
            }
        }

        //on calcule la moyenne seulement s'il y a des modules presents
        if ($totalCredits > 0)
            $this->assigner_moyenne($matricule, $annee, $semestre, $totalNotes, $totalCredits);
        else
            $this->assigner_moyenne($matricule, $annee, $semestre, 0, 1);
    }

    function assigner_moyenne($matricule, $annee, $semestre, $totalNotes, $totalCredits) {

        $moyenne = $totalNotes / $totalCredits;
        $programmes_grades = $this->recuperer_progs_grades_etudiants($matricule);

        switch ($semestre) {
            case 1:
                $colonne = 'moyennePrintemps';
                $anneeScolaire = $annee;
                break;
            case 2:
                $colonne = 'moyenneEte';
                $anneeScolaire = $annee;
                break;
            case 3:
                $colonne = 'moyenneAutomne';
                $anneeScolaire = $annee + 1;
                break;
            default:
                echo "Le semestre entree n'est pas valide";
                return;
        }


        if ($this->donnees_annuelles_presentes($matricule, $anneeScolaire)) {
            //des donnees sont deja presente pour cette annee donc on fait un update

            $this->db->where(array('matriculeEtudiant' => $matricule, 'annee' => $anneeScolaire));
            $this->db->set($colonne, $moyenne);
            $this->db->update('bulletinannuel');
        } else {
            //aucune entree pour cette annee alors on ajoute une nouvelle ligne
            if (is_array($programmes_grades)) {
                foreach ($programmes_grades as $pg) {

                    $this->db->set(array('matriculeEtudiant' => $matricule, 'annee' => $anneeScolaire, $colonne => $moyenne, 'idProgramme' => $pg['prog'], 'grade' => $pg['grade']));

                    $this->db->insert('bulletinannuel');
                }
            }
        }
    }

    function donnees_annuelles_presentes($matricule, $annee) {
        $this->db->where(array('matriculeEtudiant' => $matricule, 'annee' => $annee));
        $res = $this->db->get('bulletinannuel');
        if ($res->num_rows() > 0)
            return true;
        return false;
    }

    function recuperer_progs_grades_etudiants($matricule) {

        $progs_grades = array();

        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->select(array('idProgramme', 'grade'));
        $res = $this->db->get('dossieretudiant');

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $progs_grade [] = array('prog' => $row['idProgramme'], 'grade' => $row['grade']);
            }
        }
        return $progs_grade;
    }

    function calculer_moyennes_annuelles($matricule) {

        $annees = $this->recuperer_annees_a_calculer($matricule);
        if (is_array($annees)) {
            foreach ($annees as $annee) {
                $this->calculer_moyenne_annuelle($matricule, $annee);
            }
        }
        return true;
    }

    function recuperer_annees_a_calculer($matricule) {
        $annees = array();
        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->distinct();
        $this->db->select('annee');
        $res = $this->db->get('bulletinannuel');

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $annees[] = $row['annee'];
            }
        }
        return $annees;
    }

    function calculer_moyenne_annuelle($matricule, $annee) {
        $totalNotes = 0;
        $totalCredits = 0;
        $totalCreditsValides = 0;
        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->where("((semestre = 3 and annee =" . ($annee - 1) . ") or 
            (semestre = 1 and annee =" . $annee . ") or 
                (semestre = 2 and annee =" . $annee . ")) and 
               ( lien not in ('AV','HV','AB','AR','ER','HH','HP','RP','EQ','HS') or lien is null ) ");
        $this->db->order_by('annee desc , semestre desc');
        $this->db->select(array('module.sigle', 'note', 'nbCredits', 'lien', 'cote'));
        $this->db->from('planetudes');
        $this->db->join('module', 'planetudes.sigle = module.sigle');
        $res = $this->db->get();

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                // if (strtolower(trim($row['cote'])) == 'eq')
                if (strtolower(trim($row['lien'])) == 'eq')
                    $totalCreditsValides += $row['nbCredits'];
                elseif (strtolower(trim($row['lien'])) == 'ec') {
                    $totalCredits += $row['nbCredits'];
                    $totalNotes += $row['nbCredits'] * $row['note'];
                } else {
                    if (strtolower(trim($row['lien'])) == 'hr' && $row['note'] < 10) {
                        // considerer comme un echec
                        $totalCredits += $row['nbCredits'];
                        $totalNotes += $row['nbCredits'] * $row['note'];
                    } else {
                        $totalCredits += $row['nbCredits'];
                        $totalCreditsValides += $row['nbCredits'];
                        $totalNotes += $row['nbCredits'] * $row['note'];
                    }
                }
            }
        }
        $this->assigner_moyenne_annuelle($matricule, $annee, $totalCredits, $totalCreditsValides, $totalNotes);
    }

    function assigner_moyenne_annuelle($matricule, $annee, $totalCredits, $totalCreditsValides, $totalNotes) {
        $moyenne = 0;
        if ($totalCredits > 0)
            $moyenne = $totalNotes / $totalCredits;

        if ($this->donnees_annuelles_presentes($matricule, $annee)) {
            //des donnees sont deja presente pour cette annee donc on fait un update

            $this->db->where(array('matriculeEtudiant' => $matricule, 'annee' => $annee));
            $this->db->set(array('nbCreditsValides' => $totalCreditsValides, 'moyenneAnnee' => $moyenne));
            $this->db->update('bulletinannuel');
        } else
            $this->ecrire_message_erreur($matricule, '', '', $annee, 'Aucune donn�e pour cette annee dans la table bulletinannuel.');
    }

    function calculer_moyenne_generale($matricule) {
        $totalNotes = 0;
        $totalCredits = 0;
        $totalCreditsValides = 0;
        $siglesPresents = array();

        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->where("(lien not in ('av','hv','ab','ar','er','hh','hp','rp','eq','hr','hs') or lien is null)");
        //$this->db->where("etatNote like 'administrateur'");
        $this->db->order_by('annee desc , semestre desc');
        $this->db->select(array('module.sigle', 'note', 'nbCredits', 'lien', 'cote'));
        $this->db->from('planetudes');
        $this->db->join('module', 'planetudes.sigle = module.sigle');
        $res = $this->db->get();
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                if (strtolower(trim($row['lien'])) == 'eq' || strtolower(trim($row['cote'])) == 'eq')
                    $totalCreditsValides += $row['nbCredits'];
                elseif ((strtolower(trim($row['lien'])) == 'hp') && ((strtolower(trim($row['cote'])) == 'f' || strtolower(trim($row['cote'])) == 'fx'))) {
                    //on ne comptabilise pas un cours hors programme echoue
                    //on ne fait rien
                } elseif (strtolower(trim($row['lien'])) == 'ec') {
                    $totalCredits += $row['nbCredits'];
                    $totalNotes += $row['nbCredits'] * $row['note'];
                } elseif (!in_array(strtolower(trim($row['sigle'])), $siglesPresents)) {
                    $siglesPresents[] = $row['sigle'];
                    $totalCredits += $row['nbCredits'];
                    $totalCreditsValides += $row['nbCredits'];
                    $totalNotes += $row['nbCredits'] * $row['note'];
                }
            }
        }
        $totalSemestres = $this->recuperer_semestres_completes($matricule);
        /*         * **********Si une decision manuelle est entree alors on garde cette decision et on remet le flag a 0 comme quoi la decision manuelle est prise en compte qu'une seule fois************** */

        if ($this->decision_manuelle_existante($matricule)) {
            $decision = $this->get_decision_courante($matricule);
            $this->retirer_decision_manuelle($matricule);
        } else
            $decision = $this->generer_decision($matricule);

        /*         * ************************ */
        $this->assigner_moyenne_generale($matricule, $totalCredits, $totalCreditsValides, $totalNotes, $totalSemestres, $decision);

        return true;
    }

    function decision_manuelle_existante($matricule) {
        $this->db->where('matriculeEtudiant', $matricule);
        $res = $this->db->get('dossieretudiant');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                if ($row['IndicateurDecisionManuelle'] == 1)
                    return true;
            }
        }
        return false;
    }

    function retirer_decision_manuelle($matricule) {
        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->set(array('indicateurDecisionManuelle' => 0));

        $this->db->update('dossierEtudiant');
    }

    function get_decision_courante($matricule) {
        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->select('decisionBulletin');
        $res = $this->db->get('dossierEtudiant');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                return $row['decisionBulletin'];
            }
        }
        return "";
    }

    function generer_decision($matricule) {
        if ($this->dernier_semestre_complete($matricule) == 3) {
            return 'POURS';
        } else {
            if (($this->derniere_moyenne_annuelle($matricule) >= 11.995) && ($this->nb_echecs_non_repris($matricule) == 0))
                return 'POURS';
            return 'ATTNT';
        }
    }

    function dernier_semestre_complete($matricule) {
        $semestre = 3;
        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->limit(1);
        $this->db->order_by('annee desc');
        $res = $this->db->get('bulletinannuel');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                if ($row['moyennePrintemps'] != null)
                    $semestre = 2;
            }
        } else
            $this->ecrire_message_erreur($matricule, '', '', '', 'Aucune entr�e annuelle pour cet �tudiant.');
        return $semestre;
    }

    function derniere_moyenne_annuelle($matricule) {
        $moyenne = 0;
        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->limit(1);
        $this->db->order_by('annee desc');
        $res = $this->db->get('bulletinannuel');
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $moyenne = $row['moyenneAnnee'];
            }
        } else
            $this->ecrire_message_erreur($matricule, '', '', '', 'Aucune entr�e annuelle pour cet �tudiant.');
        return $moyenne;
    }

    function nb_echecs_non_repris($matricule) {

        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->where("lien like 'EC'");
        $this->db->where("etatNote like 'administrateur'");
        $res = $this->db->get('planetudes');
        return $res->num_rows();
    }

    function recuperer_semestres_completes($matricule) {
        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->where("semestre != 2 ");
        $this->db->where("lien not in ('AV','HV')");
        $this->db->where("etatNote like 'administrateur'");
        $this->db->distinct();
        $this->db->select('annee , semestre');
        $res = $this->db->get('planetudes');
        return $res->num_rows();
    }

    function assigner_moyenne_generale($matricule, $totalCredits, $totalCreditsValides, $totalNotes, $totalSemestres, $decision) {

        if ($totalCredits == 0)
            $moyenne = 0;
        else
            $moyenne = $totalNotes / $totalCredits;

        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->set(array('nbSemestresCompletes' => $totalSemestres, 'creditsValides' => $totalCreditsValides,
            'moyenneGenerale' => $moyenne, 'decisionBulletin' => $decision, 'indicateurBulletinACalculer' => 0));

        $this->db->update('dossierEtudiant');
    }

    function ecrire_message_erreur($matricule, $sigle, $semestre, $annee, $msg) {
        $url = 'erreursBulletins/';
        $ficher = $matricule . ".txt";
        $fh = fopen($url . $ficher, 'w') or die("Erreur lors de l'ouverture du fichier erreursBulletins ?!");
        $date = getdate();

        $message = $date['weekday'] . " " . $date['mday'] . "-" . $date['month'] . "-" . $date['year'] . " " . $date['hours'] . ":" . $date['minutes'] . ":" . $date['seconds'] . "\n";
        $message .= 'Annee: ' . $annee . "\n";
        $message .= 'Semestre: ' . $semestre . "\n";
        $message .= 'Erreur: ' . $msg . "\n";
        fwrite($fh, $message);

        fclose($fh);
    }

    function get_informations($login) {
        $this->db->where('matriculeEtudiant', $login);
        // $this->db->where('actif', 1);
        $query = $this->db->get('etudiant');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $data['matricule'] = $row['matriculeEtudiant'];
            $data['nom'] = $row['nom'];
            $data['nin'] = $row['NIN'];
            $data['actif'] = $row['actif'];
            $data['raisonInactif'] = $row['raisonInactif'];
            $data['prenom'] = $row['prenom'];
            $data['sexe'] = $row['sexe'];
            $data['dateNaissance'] = $row['dateNaissance'];
            $data['nationalite'] = $row['nationalite'];
            $data['username'] = $row['login'];
            $data['email'] = $row['email'];
            $data['telephone1'] = $row['telephone'];
            $data['telephone2'] = $row['telephone2'];
            $data['telephoneParents'] = $row['telParents'];
            $data['telephoneUrgence'] = $row['telUrgence'];
            $data['contactUrgence'] = $row['contactUrgence'];
            $data['lienParenteContactUrgence'] = $row['lienParenteUrgence'];
            $data['urlPhoto'] = 'IUP' . $row['photoEtudiant'];
            if (strpos($data['urlPhoto'], "jpg") > 0)
                $data['urlPhoto'] = str_replace('jpg', 'bmp', $data['urlPhoto']);
            $data['nomParents'] = $row['nomParents'];
            $data['surnom'] = $row['surnom'];
            $data['lieuNaissance'] = $row['lieuNaissance'];

            $idinfoBac = $row['infoBac'];


            $adresseE = $row['idAdresse'];
            $adresse = $row['idAdresseParent'];

            $this->db->where('idAdresse', $adresseE);
            $query = $this->db->get('adresses');
            $row = $query->row_array();
            $data['ligne1'] = $row['ligne1'];
            $data['ligne2'] = $row['ligne2'];
            $data['ligne3'] = $row['ligne3'];
            $data['pays'] = $row['pays'];


            $this->db->where('idAdresse', $adresse);
            $query = $this->db->get('adresses');
            $row = $query->row_array();
            $data['ligne_1'] = $row['ligne1'];
            $data['ligne_2'] = $row['ligne2'];
            $data['ligne_3'] = $row['ligne3'];
            $data['paysP'] = $row['pays'];

            $this->db->where('idInfoBac', $idinfoBac);
            $query = $this->db->get('EtudesAnterieures');
            $row = $query->row_array();
            $data['infoBac'] = $row['infoBac'];
            $data['anneeObtention'] = $row['anneeObtention'];
            $data['moyenneBac'] = $row['moyenneBac'];
            $data['autreDiplome'] = $row['autreDiplome'];
            $data['commentaireAdmission'] = $row['commentaireAdmission'];
            $data['sessionNormale'] = $row['sessionNormale'];

            $idEtab = $row['idEtablissement'];

            $this->db->where('idEtablissement', $idEtab);
            $query = $this->db->get('etablissement');
            $row = $query->row_array();
            if ($query->num_rows() > 0) {
                $data['nomEtablissement'] = $row['nom'];
                $data['programme_etudiant'][] = $this->get_programme_etudiant($data['matricule']);
            }

            $this->db->where('matriculeEtudiant', $data['matricule']);
            $query = $this->db->get('dossieretudiant');
            $row = $query->row_array();
            if ($query->num_rows() > 0) {
                $data['grade'] = $row['grade'];
            }

            return $data;
        }
    }

    function recuperer_info_personnelles($matricule) {

        $data = NULL;
        $this->db->where('matriculeEtudiant', $matricule);
        $query = $this->db->get('etudiant');

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $data['matricule'] = $row['matriculeEtudiant'];
            $data['nom'] = $row['nom'];
            $data['prenom'] = $row['prenom'];
            // ajout surnom 30-12-2014
            $data['surnom'] = $row['surnom'];
            $data['sexe'] = $row['sexe'];
            $data['dateNaissance'] = $row['dateNaissance'];
        }
        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->select('nom');
        $this->db->join('programme', 'dossieretudiant.idProgramme = programme.idProgramme');
        $query = $this->db->get('dossieretudiant');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $data['programme'] = $row['nom'];
        }

        return $data;
    }

    function recuperer_annees($matricule) {

        $semestres = NULL;
        $this->db->where('matriculeEtudiant', $matricule);
        $this->db->distinct();
        $this->db->select(array('annee', 'semestre'));
        $this->db->order_by("annee ASC, semestre ASC");
        $query = $this->db->get('planetudes');

        $anneesVisitees = array();
        $annee_a_visiter = 0;

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $annee_a_visiter = $row['annee'];
                if ($row['semestre'] == 3)
                    $annee_a_visiter ++;

                if (!in_array($annee_a_visiter, $anneesVisitees)) {
                    $annee = $row['annee'];

                    $anneesVisitees[] = $annee;
                    $data[$annee] = $this->recuperer_semestres($matricule, $annee);

                    $semestres_Temp = $this->recuperer_annee_scolaire($data, $annee);
                    if (is_array($semestres_Temp) && !empty($semestres_Temp))
                        $semestres[$annee] = $semestres_Temp;

                    if ($row['semestre'] == 3) {
                        // echo("je rentre ici avec ".$annee );
                        $data[$annee + 1] = $this->recuperer_semestres($matricule, $annee + 1);
                        $semestres[$annee + 1] = $this->recuperer_annee_scolaire($data, $annee + 1);
                        $anneesVisitees[] = $annee + 1;
                    }
                }
            }
        }
        return $semestres;
    }

    function recuperer_semestres($matricule, $annee) {
        $semestres = NULL;
        $where = array('matriculeEtudiant' => $matricule, 'annee' => $annee);
        $this->db->where($where);
        $this->db->distinct();
        $this->db->select('semestre');
        $this->db->order_by("semestre");
        $query = $this->db->get('planetudes');

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $semestres['semestres'][] = $row['semestre'];
            }
        }

        return $semestres['semestres'];
    }

    function recuperer_annee_scolaire($data, $annee) {
        $semestres_annee = NULL;
        $annee_precedente = intval($annee) - 1;
        if (array_key_exists($annee_precedente, $data)) {
            if ($data[$annee_precedente] != null && in_array('3', $data[$annee_precedente])) {
                $semestres_annee['3'] = $annee_precedente;
            }
        }
        if (!empty($data[$annee])) {
            foreach ($data[$annee] as $semestre) {
                if ($semestre != '3')
                    $semestres_annee[$semestre] = $annee;
            }
        }
        return $semestres_annee;
    }

    function recuperer_cours_semestre($matricule, $annee, $semestre) {
        $notes_semestre = NULL;

        $where = array('matriculeEtudiant' => $matricule, 'annee' => $annee, 'semestre' => $semestre);
        $this->db->where($where);
        $this->db->select(array('sigle', 'note', 'cote', 'lien'));
        $this->db->order_by("sigle");
        $query = $this->db->get('planetudes');
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $notes_semestre[$row['sigle']] = $row;
            }
        }

        foreach ($notes_semestre as $module) {
            $this->db->where('sigle', $module['sigle']);
            $this->db->select(array('titre', 'nbCredits'));
            $query = $this->db->get('module');
            if ($query->num_rows() > 0) {
                $row = $query->row_array();
                $module['titre'] = $row['titre'];
                $module['credits'] = $row['nbCredits'];
            }
            $notes_semestre[$module['sigle']] = $module;
        }

        return $notes_semestre;
    }

    function recuperer_moyenne_semestre($matricule, $annee, $semestre) {
        $moyenne = NULL;
        $where = array('matriculeEtudiant' => $matricule, 'annee' => $annee);
        $this->db->where($where);
        $this->db->select($semestre);
        $query = $this->db->get('bulletinannuel');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $moyenne = $row[$semestre];
        }
        return $moyenne;
    }

    function recuperer_moyenne_annuelle($matricule, $annee) {
        $moyenne = NULL;
        $where = array('matriculeEtudiant' => $matricule, 'annee' => $annee);
        $this->db->where($where);
        $this->db->select('moyenneAnnee');
        $query = $this->db->get('bulletinannuel');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $moyenne = $row['moyenneAnnee'];
        }
        return $moyenne;
    }

    function recuperer_moyenne_generale($matricule) {
        $moyenne = 0;

        $this->db->where(array('matriculeEtudiant' => $matricule));
        $this->db->select(array('moyenneGenerale'));
        $this->db->from('dossieretudiant');

        $res = $this->db->get();
        if ($res->num_rows() > 0) {
            $row = $res->row_array();
            $moyenne = $row['moyenneGenerale'];
        }
        return $moyenne;
    }

    function recuperer_nbCredits_annuel($matricule, $annee) {
        $nbCredits = NULL;
        $where = array('matriculeEtudiant' => $matricule, 'annee' => $annee);
        $this->db->where($where);
        $this->db->select('nbCreditsValides');
        $query = $this->db->get('bulletinannuel');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $nbCredits = $row['nbCreditsValides'];
        }
        return $nbCredits;
    }

    function recuperer_nbCredits_semestriel($matricule, $annee, $semestre) {
        //ne seront pas calcules dans la moyenne
        $liensNonValides = array('av', 'ab', 'ar', 'eq', 'er', 'hp', 'hh', 'hv', 'ec', 'rp');
        $totalCredits = 0;

        $this->db->where(array('matriculeEtudiant' => $matricule, 'annee' => $annee, 'semestre' => $semestre));
        $this->db->select(array('nbCredits', 'lien'));
        $this->db->from('planetudes');
        $this->db->join('module', 'planetudes.sigle = module.sigle');

        $res = $this->db->get();
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                if (!in_array(strtolower(trim($row['lien'])), $liensNonValides)) {
                    $totalCredits += $row['nbCredits'];
                }
            }
            return $totalCredits;
        }
    }

    function recuperer_nbCredits_general($matricule) {
        $nbCredits = 0;

        $this->db->where(array('matriculeEtudiant' => $matricule));
        $this->db->select(array('creditsValides'));
        $this->db->from('dossieretudiant');

        $res = $this->db->get();
        if ($res->num_rows() > 0) {
            $row = $res->row_array();
            $nbCredits = $row['creditsValides'];
        }
        return $nbCredits;
    }

    function recuperer_decision($matricule, $sexe) {
        $decision = 0;

        $this->db->where(array('matriculeEtudiant' => $matricule));
        $this->db->select(array('decisionBulletin'));
        $this->db->from('dossieretudiant');
        $this->db->join('decisionBulletin', 'dossieretudiant.decisionBulletin = decisionbulletin.idDecision');


        $res = $this->db->get();
        if ($res->num_rows() > 0) {
            $row = $res->row_array();
            $decision = $row['decisionBulletin'];
        }

        if ($sexe == 'M')
            $this->db->select('decisionM')->from('decisionbulletin')->where('idDecision', $decision);
        else
            $this->db->select('decisionF')->from('decisionbulletin')->where('idDecision', $decision);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            if ($sexe == 'M')
                $decision = $row['decisionM'];
            else
                $decision = $row['decisionF'];
        }

        return $decision;
    }

    /*
     * fonction qui retourne la session courante.
     */

    function get_session_courante() {
        $session_courante = NULL;
        $query = $this->db->query("SELECT DISTINCT `annee`, `semestre`, `annee_univ`,`semestre_reel` FROM sessionCourante");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $session_courante['annee'][] = $row['annee'];
                $session_courante['semestre'][] = $row['semestre'];
                $session_courante['semestre_reel'][] = $row['semestre_reel'];
                $session_courante['annee_univ'][] = $row['annee_univ'];
            }
        }
        return $session_courante;
    }

    /*
     * Ajout 2.2.1. Fonction qui retourne la session courante avec dates de d�but et fin des cours.
     */

    function get_details_semestre_courant() {
        $semestre_courant = NULL;
        $query = $this->db->query("SELECT DISTINCT `annee`, `semestre`, `debutCours`, `finCours` FROM sessionCourante");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $semestre_courant['annee'][] = $row['annee'];
                $semestre_courant['semestre'][] = $row['semestre'];
                $semestre_courant['debutCours'][] = $row['debutCours'];
                $semestre_courant['finCours'][] = $row['finCours'];
            }
        }
        return $semestre_courant;
    }

    // Alfa 19-7-2015
    function get_notes_partielles($matricule, $sigle, $annee, $session) {

        for ($i = 0; $i < sizeof($matricule); $i++) {
            $this->db->where(array('matriculeEtudiant' => $matricule[$i], 'annee' => $annee, 'semestre' => $session,
                'sigle' => $sigle
            ));
            $this->db->order_by('idEvaluation', 'asc');
            $query = $this->db->get('notespartielles');


            if ($query->num_rows() > 0) {
                $rows = $query->result_array();
                $data[$i]['note'] = $rows;
            } else {
                $data[$i]['note'] = '';
            }
        }
        return $data;
    }

    // Hafedh
    function get_noms_evaluations($sigle) {
        $courant = $this->get_session_courante();
        $semestre = $courant['semestre'][0];
        $annee = $courant['annee'][0];
        $q1 = "SELECT nomEvaluation, ponderation, evaluation.idEvaluation FROM  `moduleevaluation` NATURAL JOIN evaluation WHERE  `sigle`='" . $sigle . "' and semestre=" . $semestre . " and annee=" . $annee . " order by evaluation.idEvaluation asc";
        $query1 = $this->db->query($q1);
        $nomsEvalPond = $query1->result_array();
        return $nomsEvalPond;
    }

    function get_evaluations($annee, $semestre, $sigle) {



        $q1 = "select E.idEvaluation from  evaluation E NATURAL JOIN moduleevaluation M where M.sigle='" . $sigle . "' and annee=" . $annee . " and semestre=" . $semestre;
        $query1 = $this->db->query($q1);
        $A1 = $query1->result_array();

        $q2 = "select idEvaluation from evaluation where idEvaluation NOT IN (
select evaluation.idEvaluation from  evaluation  NATURAL JOIN moduleevaluation  where sigle='" . $sigle . "' ) ";

        $query2 = $this->db->query($q2);
        $A2 = $query2->result_array();
        $ponderations = array();
        $tab = array();
        $res = array();
        $n1 = count($A1);
        $n2 = count($A2);
        for ($i = 0; $i < $n1; $i++) {
            $id = $A1[$i]['idEvaluation'];
            $q = $this->db->query("select *  FROM evaluation where idEvaluation=" . $id);
            $tab[] = $q->row_array();

            $q = $this->db->query("select ponderation  FROM  moduleevaluation where idEvaluation=" . $id . " and sigle='" . $sigle . "'");
            $ponderations[] = $q->row_array();
        }

        for ($i = 0; $i < $n2; $i++) {
            $id = $A2[$i]['idEvaluation'];
            $q = $this->db->query("select *  FROM evaluation where idEvaluation=" . $id);
            $tab[] = $q->row_array();
            $ponderations[] = '';
        }
        $res = array('n1' => $n1, 'n2' => $n2, 'tab' => $tab, 'sigle' => $sigle, 'ponderations' => $ponderations);
        return $res;
    }

    function set_evaluations($annee, $semestre, $sigle) {
        $n = $_POST['nbr'];
        $msg = "";
        for ($i = 1; $i <= $n; $i++) {
            if (empty($_POST['pond' . $i]))
                $pond = 0.0;
            else
                $pond = $_POST['pond' . $i];

            if (isset($_POST['ev' . $i])) {
                //  ajouter s'il n'existe pas d ja ou modifier s'il existe
                $q = $this->db->query("select count(*) as nb from moduleevaluation where idEvaluation=" . $_POST['ev' . $i] . " and sigle='" . $sigle . "' and semestre=" . $semestre . " and annee=" . $annee);
                $res = $q->row_array();
                //ajout
                if ($res['nb'] == 0) {
                    $q = $this->db->query("insert into moduleevaluation values ('" . $sigle . "'," . $_POST['ev' . $i] . "," . $pond . "," . $semestre . "," . $annee . " ) ");
                    $msg .= "Evaluation " . $_POST['ev' . $i] . " : ajoute " . '<br>';
                } else {
                    //echo  $_POST['pond'.$i].' <br>';
                    // echo "update moduleevaluation set ponderation=".$pond." where idEvaluation=".$_POST['ev'.$i]." and sigle='".$sigle."' and semestre=".$semestre. " and annee=".$annee;
                    $q = $this->db->query("update moduleevaluation set ponderation=" . $pond . " where idEvaluation=" . $_POST['ev' . $i] . " and sigle='" . $sigle . "' and semestre=" . $semestre . " and annee=" . $annee);
                    $msg .= "Evaluation " . $_POST['ev' . $i] . " : modifie " . '<br>';
                }
            } else {
                //  suuprmier s'il existe d j 
                $q = $this->db->query("select count(*) as nb from moduleevaluation where idEvaluation=" . $_POST['case' . $i] . " and sigle='" . $sigle . "' and semestre=" . $semestre . " and annee=" . $annee);
                $res = $q->row_array();
                //ajout
                if ($res['nb'] != 0) {

                    $q = $this->db->query("delete from moduleevaluation where  idEvaluation=" . $_POST['case' . $i] . " and sigle='" . $sigle . "' and semestre=" . $semestre . " and annee=" . $annee);
                    $q = $this->db->query("delete from notespartielles where  idEvaluation=" . $_POST['case' . $i] . " and sigle='" . $sigle . "' and semestre=" . $semestre . " and annee=" . $annee);

                    $msg .= "Evaluation " . $_POST['case' . $i] . " : supprime " . '<br>';
                }
            }
        }

        /* $q1="select E.idEvaluation from  evaluation E NATURAL JOIN moduleevaluation M where M.sigle='".$sigle."'";
          $query1 = $this->db->query($q1);
          $A1=$query1->result_array(); */
        $res = array('msg' => $msg, 'sigle' => $sigle);
        return $res;
    }

    //Debut Modif Cheikh 25/11

    function get_informationsAtt($matricule) {
        $this->db->where('matriculeEtudiant', $matricule);
        // $this->db->where('actif', 1);
        $query = $this->db->get('infoatest');
        if ($query->num_rows() > 0) {

            $row = $query->row_array();

            $data['matricule'] = $row['matriculeEtudiant'];
            $data['nom'] = $row['nom'];
            $data['nomArabe'] = $row['nomArabe'];
            $data['prenom'] = $row['prenom'];
            $data['prenomArabe'] = $row['prenomArabe'];
            $data['dateNaissance'] = $row['dateNaissance'];
            $data['idProgramme'] = $row['idProgramme'];
            $data['nomProg'] = $row['nomProg'];
            $data['nomProgArabe'] = $row['nomProgArabe'];
            /* if($row['nbSemestresCompletes']== Null){
              $data['semestre'] = 1;
              }
              else{
              $data['semestre'] = $row['nbSemestresCompletes']+1;
              }
             */

            $data['urlPhoto'] = 'IUP' . $row['photoEtudiant'];
            if (strpos($data['urlPhoto'], "jpg") > 0) {
                $data['urlPhoto'] = str_replace('jpg', 'gif', $data['urlPhoto']);
            } else if (strpos($data['urlPhoto'], "bmp") > 0) {
                $data['urlPhoto'] = str_replace('bmp', 'gif', $data['urlPhoto']);
            }
            $courante = $this->get_session_courante();
            //print_r($courante);
            $annee = $courante['annee_univ'][0];
            $semestre_reel = $courante['semestre_reel'][0];
            $data['anneeuniv'] = $annee;
            $data['semestre'] = $this->get_Niveau_Inscrit($row['matriculeEtudiant'], $annee);
            $semestre = $data['semestre'];
            //if($semestre_reel %2==0)
            {
                $q = 'select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.sigle,M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and mod(u. semestre,2)=0 and p.annee=' . ($annee + 1) . ' and p.semestre=1 and p.matriculeetudiant=' . $matricule . ' order by U.sigle asc';
                //$q="select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M, unite U where M.sigleunite=U.sigle and semestre=".$semestre." and U.idProgramme='".$row['idProgramme']."' order by U.sigle asc";
                //echo $q; 
                $q = $this->db->query($q);
                $data['maqSemPair'] = $q->result_array();
                // $q="select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M, unite U where M.sigleunite=U.sigle and semestre=".($semestre-1)." and U.idProgramme='".$row['idProgramme']."' order by U.sigle asc";
                $q = 'select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre,M.sigle, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and mod(u. semestre,2)=1 and p.annee=' . ($annee) . ' and p.semestre=3 and p.matriculeetudiant=' . $matricule . ' order by U.sigle asc';
                // echo $q;
                $q = $this->db->query($q);
                $data['maqSemImpair'] = $q->result_array();
                // Cheikh recuperer le nombrre d'elements, volume CM, volume TD par module et semestre
                // $q="select * from semestremodules where semestre=2"." and programme='".$row['idProgramme']."' order by titre asc";
                $q = 'select M.sigleUnite as sigleModule, U.sigle as sigleUnite,U.titre as titre, sum(`volumeCM`) as cm,sum(`volumeTD`) as td,sum(`volumeTP`) as tp,count(*) as nb, sum(M.nbCredits ) as credits from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and mod(u.semestre,2)=0 and p.annee=' . ($annee + 1) . ' and p.semestre=1 and p.matriculeetudiant=' . $matricule . ' group by 1,2,3 order by U.titre asc';
                $q = $this->db->query($q);
                $data['statElemModPair'] = $q->result_array();
                $q = 'select M.sigleUnite as sigleModule,U.sigle as sigleUnite, U.titre as titre, sum(`volumeCM`) as cm,sum(`volumeTD`) as td,sum(`volumeTP`) as tp,count(*) as nb, sum(M.nbCredits ) as credits from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and mod(u.semestre,2)=1 and p.annee=' . ($annee) . ' and p.semestre=3 and p.matriculeetudiant=' . $matricule . ' group by 1,2,3 order by U.titre asc';
                //echo $q;
                $q = $this->db->query($q);
                $data['statElemModImpair'] = $q->result_array();
            }
            /*   else{
              // $q="select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M, unite U where M.sigleunite=U.sigle and semestre=".$semestre." and U.idProgramme='".$row['idProgramme']."' order by U.sigle asc";
              $q='select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and u. semestre=1 and p.annee='.($annee). ' and p.semestre=3 and p.matriculeetudiant='.$matricule.' order by U.sigle asc';
              $q=$this->db->query($q);
              $data['maqSemImpair']=$q->result_array();
              // $q="select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD,U.titre as titreUnite from module M, unite U where M.sigleunite=U.sigle and semestre=".($semestre+1)." and U.idProgramme='".$row['idProgramme']."' order by U.sigle asc";
              $q='select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and u. semestre=2 and p.annee='.($annee+1). ' and p.semestre=1 and p.matriculeetudiant='.$matricule.' order by U.sigle asc';
              $q=$this->db->query($q);
              $data['maqSemPair']=$q->result_array();
              // Cheikh recuperer le nombrre d'elements, volume CM, volume TD par module et semestre
              $q="select * from semestremodules where semestre=".$semestre." and programme='".$row['idProgramme']."' order by titre asc";
              $q=$this->db->query($q);
              $data['statElemModImpair']=$q->result_array();
              $q="select * from semestremodules where semestre=".($semestre+1)." and programme='".$row['idProgramme']."' order by titre asc";
              $q=$this->db->query($q);
              $data['statElemModPair']=$q->result_array();
              } */
        }
        return $data;
    }

    function get_informationsAtt_d($matricule) {
        $this->db->where('matriculeEtudiant', $matricule);
        // $this->db->where('actif', 1);
        $query = $this->db->get('infoatest');
        if ($query->num_rows() > 0) {

            $row = $query->row_array();

            $data['matricule'] = $row['matriculeEtudiant'];
            $data['nom'] = $row['nom'];
            $data['nomArabe'] = $row['nomArabe'];
            $data['prenom'] = $row['prenom'];
            $data['prenomPere_fr'] = $row['prenomPere_fr'];
            $data['prenomPere_ar'] = $row['prenomPere_ar'];
            $data['prenomArabe'] = $row['prenomArabe'];
            $data['dateNaissance'] = $row['dateNaissance'];
            $data['idProgramme'] = $row['idProgramme'];
            $data['nomProg'] = $row['nomProg'];
            $data['nomProgArabe'] = $row['nomProgArabe'];
            $data['info'] = $this->get_informations_etudiant($matricule);
            //  $data['niveau'] = $this->get_Niveau_Inscrit_d($matricule,$annee="");
            /* if($row['nbSemestresCompletes']== Null){
              $data['semestre'] = 1;
              }
              else{
              $data['semestre'] = $row['nbSemestresCompletes']+1;
              }
             */

            $data['urlPhoto'] =  $row['photoEtudiant'];
            if (strpos($data['urlPhoto'], "jpg") > 0) {
                $data['urlPhoto'] = str_replace('jpg', 'gif', $data['urlPhoto']);
            } else if (strpos($data['urlPhoto'], "bmp") > 0) {
                $data['urlPhoto'] = str_replace('bmp', 'gif', $data['urlPhoto']);
            }
            $courante = $this->get_session_courante();
            //print_r($courante);
            $annee = $courante['annee_univ'][0];
            $semestre_reel = $courante['semestre_reel'][0];
            $data['anneeuniv'] = $annee;
            $data['semestre'] = $this->get_Niveau_Inscrit($row['matriculeEtudiant'], $annee);
            $semestre = $data['semestre'];
            $courante = $this->get_session_courante();
            //print_r($courante);
            $annee = $courante['annee_univ'][0];
            $semestre_reel = $courante['semestre_reel'][0];
            $data['anneeuniv'] = $annee;
            $is_red = $this->is_redoublant($row['matriculeEtudiant']);

            $l = $this->get_Niveau_Inscrit($row['matriculeEtudiant'], $annee);
            $data['semestre'] = $this->get_Niveau_Inscrit($row['matriculeEtudiant'], $annee);
            ;
            // if($is_red=="0"){
            // print_r($l+1);
            $data['niveau'] = ($l + 1) / 2;
            // }else{
            // print_r($l);
            //   $data['niveau']=$l;
            // }
            //if($semestre_reel %2==0)
            {
                $q = 'select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.sigle,M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and mod(u. semestre,2)=0 and p.annee=' . ($annee + 1) . ' and p.semestre=1 and p.matriculeetudiant=' . $matricule . ' order by U.sigle asc';
                //$q="select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M, unite U where M.sigleunite=U.sigle and semestre=".$semestre." and U.idProgramme='".$row['idProgramme']."' order by U.sigle asc";
                //echo $q; 
                $q = $this->db->query($q);
                $data['maqSemPair'] = $q->result_array();
                // $q="select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M, unite U where M.sigleunite=U.sigle and semestre=".($semestre-1)." and U.idProgramme='".$row['idProgramme']."' order by U.sigle asc";
                $q = 'select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre,M.sigle, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and mod(u. semestre,2)=1 and p.annee=' . ($annee) . ' and p.semestre=3 and p.matriculeetudiant=' . $matricule . ' order by U.sigle asc';
                // echo $q;
                $q = $this->db->query($q);
                $data['maqSemImpair'] = $q->result_array();
                // Cheikh recuperer le nombrre d'elements, volume CM, volume TD par module et semestre
                // $q="select * from semestremodules where semestre=2"." and programme='".$row['idProgramme']."' order by titre asc";
                /* $q='select M.sigleUnite as sigleModule, U.sigle as sigleUnite,U.titre as titre, sum(`volumeCM`) as cm,sum(`volumeTD`) as td,sum(`volumeTP`) as tp,count(*) as nb, sum(M.nbCredits ) as credits from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and mod(u.semestre,2)=0 and p.annee='.($annee+1). ' and p.semestre=1 and p.matriculeetudiant='.$matricule. ' group by 1,2,3 order by U.titre asc';
                  $q=$this->db->query($q);
                  $data['statElemModPair']=$q->result_array();
                  $q='select M.sigleUnite as sigleModule,U.sigle as sigleUnite, U.titre as titre, sum(`volumeCM`) as cm,sum(`volumeTD`) as td,sum(`volumeTP`) as tp,count(*) as nb, sum(M.nbCredits ) as credits from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and mod(u.semestre,2)=1 and p.annee='.($annee). ' and p.semestre=3 and p.matriculeetudiant='.$matricule. ' group by 1,2,3 order by U.titre asc';
                  //echo $q;
                  $q=$this->db->query($q);
                  $data['statElemModImpair']=$q->result_array(); */
            }
            /*   else{
              // $q="select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M, unite U where M.sigleunite=U.sigle and semestre=".$semestre." and U.idProgramme='".$row['idProgramme']."' order by U.sigle asc";
              $q='select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and u. semestre=1 and p.annee='.($annee). ' and p.semestre=3 and p.matriculeetudiant='.$matricule.' order by U.sigle asc';
              $q=$this->db->query($q);
              $data['maqSemImpair']=$q->result_array();
              // $q="select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD,U.titre as titreUnite from module M, unite U where M.sigleunite=U.sigle and semestre=".($semestre+1)." and U.idProgramme='".$row['idProgramme']."' order by U.sigle asc";
              $q='select M.sigle as sigleModule, M.nbCredits , U.credits, U.sigle as sigleUnite, U.semestre, M.titre as titreModule, M.volumeCM, M.volumeTD, U.titre as titreUnite from module M,planetudes p, unite U where M.sigleunite=U.sigle and m.sigle=p.sigle and u. semestre=2 and p.annee='.($annee+1). ' and p.semestre=1 and p.matriculeetudiant='.$matricule.' order by U.sigle asc';
              $q=$this->db->query($q);
              $data['maqSemPair']=$q->result_array();
              // Cheikh recuperer le nombrre d'elements, volume CM, volume TD par module et semestre
              $q="select * from semestremodules where semestre=".$semestre." and programme='".$row['idProgramme']."' order by titre asc";
              $q=$this->db->query($q);
              $data['statElemModImpair']=$q->result_array();
              $q="select * from semestremodules where semestre=".($semestre+1)." and programme='".$row['idProgramme']."' order by titre asc";
              $q=$this->db->query($q);
              $data['statElemModPair']=$q->result_array();
              } */
        }
        return $data;
    }

    function ajouter_unite($sigle, $titre, $description, $semestre, $credits,$coefficient, $programme, $anneeAct, $semestreAct) {
        $this->db->where('sigle', $sigle);
        $query = $this->db->get('unite');
        if ($query->num_rows() > 0) {
            return false;
        } else {
            $data = array(
                'sigle' => $sigle,
                'titre' => $titre,
                'description' => $description,
                'semestre' => intval($semestre),
                'credits' => intval($credits),
                'coefficient' => intval($coefficient),
                'idProgramme' => $programme,
                'anneeAct' => intval($anneeAct),
                'semestreAct' => intval($semestreAct)
            );
            $this->db->insert('unite', $data);
            return true;
        }
    }

    /*
     * fonction qui recupere les informations de l'unit�.
     *
     * Titre de l'unit�e, nbre de credits
     * 
     * @param - sigle: le sigle du cours a recuperer
     */

    function recuperer_unite($sigle) {
        $info_unite = NULL;
        $query = $this->db->query("SELECT * FROM unite WHERE sigle='" . $sigle . "'");

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info_unite = $row;
                $info_unite['titre'] = $row['sigle'];
                $info_unite['titre'] = $row['titre'];
                $info_unite['credits'] = $row['credits'];
                $info_unite['description'] = $row['description'];
                $info_unite['semestre'] = $row['semestre'];
                $info_unite['anneeAct'] = $row['anneeAct'];
                $info_unite['semestreAct'] = $row['semestreAct'];
                $info_unite['idProgramme'] = $row['idProgramme'];
            }
        }
        return $info_unite;
    }

    /*
     * Cette methode permet de mettre a jour une unit� 
     * 
     * @param - info_unite: les informations de l'unit�
     */

    function mettre_a_jour_unite($info_unite) {
        array_pop($info_unite);
        $info_unite['sigle'] = trim($info_unite['sigle']);
        $info_unite['titre'] = trim($info_unite['titre']);
        $info_unite['description'] = trim($info_unite['description']);
        $info_unite['semestre'] = trim($info_unite['semestre']);
        $info_unite['anneeAct'] = trim($info_unite['anneeAct']);
        $info_unite['semestreAct'] = trim($info_unite['semestreAct']);
        $info_unite['idProgramme'] = trim($info_unite['idProgramme']);
        $info_unite['credits'] = trim($info_unite['credits']);
         $info_unite['coefficient'] = trim($info_unite['coefficient']);

        $this->db->where('sigle', $info_unite['sigle']);
        $info = array(
            'sigle' => $info_unite['sigle'],
            'titre' => $info_unite['titre'],
            'description' => $info_unite['description'],
            'semestre' => $info_unite['semestre'],
            'anneeAct' => $info_unite['anneeAct'],
            'semestreAct' => $info_unite['semestreAct'],
            'idProgramme' => $info_unite['idProgramme'],
                'credits'=>$info_unite['credits'],
            'coefficient'=>$info_unite['coefficient']
                );

        $this->db->update('unite', $info);
    }

    // Fin Modif Cheikh 26/11/2015
    // Debut Modif Cheikh 01/01/2016 : c'est la f�te !!
    /*
     * fonction qui retourne la session courante.
     */
    function get_semestres_courants() {

        $semestres = NULL;
        $query = $this->db->query("SELECT DISTINCT `annee`, `semestre`, `annee_univ`,`semestre_reel` FROM sessionCourante");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {

                if ($row['semestre_reel'] % 2 == 0) { // les semestres paiires
                    $semestres[0] = 2;
                    $semestres[1] = 4;
                    $semestres[2] = 6;
                } else { // les semestre impaires
                    $semestres[0] = 1;
                    $semestres[1] = 3;
                    $semestres[2] = 5;
                }
            }
        }
        return $semestres;
    }

    function get_semestres_courants_calendar() {

        $semestres = NULL;
        $query = $this->db->query("SELECT DISTINCT `annee`, `semestre`, `annee_univ`,`semestre_reel` FROM sessionCourante_calendar");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {

                if ($row['semestre_reel'] % 2 == 0 || $row['semestre'] == 1) { // les semestres paiires
                    $semestres[0] = 2;
                    $semestres[1] = 4;
                    $semestres[2] = 6;
                } else { // les semestre impaires
                    $semestres[0] = 1;
                    $semestres[1] = 3;
                    $semestres[2] = 5;
                }
            }
        }
        return $semestres;
    }

    //fonction qui retourne tous les etudiants d'un programme qui sont � s-1
    // pour les inscrire aux modules de s
    // Attention, �a ne verifie pas encore s'ils ont valid� ou non le s-1
    function get_etudiants_par($idProgramme, $idCycle, $semestre) {
        $sql = "SELECT es.matriculeEtudiant from etudiantsemestre es,dossieretudiant e where es.matriculeEtudiant = e.matriculeEtudiant and 
                   e.idProgramme ='" . $idProgramme . "' and e.grade='" . $idCycle . "' and es.dern_sem = " . ($semestre - 1);

       // echo $sql;
        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    //fonction qui retourne tous les �l�ments d'un programme pour le semestre s
    function get_elements_par($idProgramme, $idCycle, $semestre) {
        $sql = "SELECT * from elementsactifs where idProgramme ='" . $idProgramme . "' and idCycle=" . $idCycle . " and semestre = " . $semestre;


        $query = $this->db->query($sql);

        $elementsactifs = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {

                $elementsactifs['sigle'][] = $row['sigle'];
                $elementsactifs['idDepartement'][] = $row['idDepartement'];
                $elementsactifs['nbCredits'][] = $row['nbCredits'];
                $elementsactifs['idCycle'][] = $row['idCycle'];
                $elementsactifs['professeurResponsable'][] = $row['professeurResponsable'];
                $elementsactifs['sigleunite'][] = $row['sigleunite'];
                $elementsactifs['idProgramme'][] = $row['idProgramme'];
                $elementsactifs['semestre'][] = $row['semestre'];
            }

            return $elementsactifs;
        }
    }

    //fonction qui verifie si un groupe pour un module est
    //d�ja cr�� dans le semestre et l'ann�e  donn�e
    function exist_un_groupe($sigle, $anne, $semestre) {
        $sql = "SELECT * from groupe where sigle ='" . $sigle . "' and semestre = " . $semestre . " and annee = " . $annee;


        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    //retourne un groupe s'il existe pour une module 
    // dans le semestre et l'ann�e  donn�e
    function get_groupe_par($sigle, $annee, $semestre) {
        $sql = "SELECT * from groupe where sigle ='" . $sigle . "' and semestre = " . $semestre . " and annee = " . $annee;
        //echo    $sql;   

        $query = $this->db->query($sql);

        $groupe = NULL;
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $groupe = $row['idGroupe'];
                return $groupe;
            }
        } else {
            return NULL;
        }
    }

    //add by MedBakar 08-04-2020
    //retourne le groupe ou le nbr des etudiants est petits s'il existe pour une module 
    // dans le semestre et l'ann�e  donn�e
    function get_min_groupe_par($sigle, $semestre, $annee) {
       // $sigle='M011';
        $sql = "select g.idGroupe,count(l.idGroupe) nbrEtudiants from groupe g,listeEtudiants l where l.idGroupe=g.idGroupe"
                . " and g.sigle ='" . $sigle . "' and g.semestre = " . $semestre . " and g.annee = " . $annee."   group by g.idGroupe";
        //echo    $sql;   

        $query = $this->db->query($sql);

        $groupe = NULL;
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $groupe[] =array('idGroupe'=> $row['idGroupe'],'nbrEtudiants'=> $row['nbrEtudiants']);
                
            }
            return $groupe;
        } else {
            return NULL;
        }
    }

    //fonction qui verifie si un �tudiant est d�ja inscrit dans un groupe  pour un module 
    // donn� au semestre et ann�e donn�s
    function est_dans_groupe($sigle, $annee, $semestre, $matriculeEtudiant) {
        $sql = "SELECT l.matriculeEtudiant  from listeetudiants l, groupe g where l.idGroupe =g.idGroupe and g.sigle ='" . $sigle . "' and g.semestre = " . $semestre . " and g.annee = " . $annee ." and l.matriculeEtudiant='".$matriculeEtudiant."'";


        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

//add by MedBAkar 08-04-2020    
    //fonction qui verifie si un �tudiant est d�ja inscrit dans un groupe  pour un module 
    // donn� au semestre et ann�e donn�s
    //on verifie si l'etudiant n'est pas encore inscrit dans un groupe de meme type en cas ou
    // il est inscrit dans un autre groupe de meme type on le  retourne 
     //s'il n;est pas inscrit on l'ajoute alors a le groupe fornis en parametre
    function est_dans_groupe_de_type($sigle, $annee, $semestre, $matriculeEtudiant,$types,$group) {
if(empty($matriculeEtudiant)){
        $sql = "SELECT l.idGroupe  from listeetudiants l, groupe g where l.idGroupe =g.idGroupe  "
                . "and g.sigle ='" . $sigle . "' and g.semestre = " . $semestre . " and g.annee = " . $annee ." "
                . " and g.idGroupe like '%".$types."%'";
}else{
    $sql = "SELECT l.idGroupe  from listeetudiants l, groupe g where l.idGroupe =g.idGroupe  "
                . "and g.sigle ='" . $sigle . "' and g.semestre = " . $semestre . " and g.annee = " . $annee ." "
                . "and l.matriculeEtudiant='".$matriculeEtudiant."' and g.idGroupe like '%".$types."%'";

}

        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
              return $row['idGroupe'];
            }
        } else {
            return $group;
        }
    }
//fin add MedBakar
    //fonction qui v�rifie si le bulletin semestriel est d�ja renseign� pour un etudiant, module donn�
    function bulltinSemestrielDejaRens($sigle, $matriculeEtudiant) {
        $sql = "SELECT matriculeEtudiant  from bulletinsemestriel where sigle ='" . $sigle . "' and matriculeEtudiant = " . $matriculeEtudiant;


        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // Fin Modif Cheikh le 01/01/2016
    // debut Modif Hafedh et Cheikh
    function inscrire_etudiant_groupe($post) {
        $tableauMatricule = $post['items'];

        //print_r($tableauMatricule);
        if (!isset($post['groupe'])) {
            return $data['pasDeGroupe'] = 'pasDeGroupe';
        }

        $tableauGroupe = $post['groupe'];
        $nombreEtudiant = count($tableauMatricule);
        $data = NULL;
        $nombreGroupeSelectionne = count($tableauGroupe);
        if (!isset($post)) {
            return false;
        }
        $compteurMessage = 0;
        $comteurInsert = 0;
        for ($i = 0; $i < $nombreEtudiant; $i++) {
            $this->db->where(array('matriculeEtudiant' => $tableauMatricule[$i], 'annee' => $post['annee'], 'semestre' => $post['semestre'], 'sigle' => $post['sigle']));
            $query = $this->db->get('planetudes');
            if ($query->num_rows() > 0) {
                for ($j = 0; $j < $nombreGroupeSelectionne; $j++) {
                    $this->db->where(array('matriculeEtudiant' => $tableauMatricule[$i], 'idGroupe' => $tableauGroupe[$j]));
                    $query = $this->db->get('listeetudiants');
                    if ($query->num_rows() > 0) {
                        $data['valide'][$compteurMessage] = 'L\'�tudiant <b>' . $tableauMatricule[$i] . '</b> est d�j� inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                        $compteurMessage++;
                    } else {
                        $info = array(
                            'matriculeEtudiant' => $tableauMatricule[$i],
                            'idGroupe' => $tableauGroupe[$j]);
                        $this->db->insert('listeetudiants', $info);
                        $data['insert'][$comteurInsert] = 'L\'�tudiant <b>' . $tableauMatricule[$i] . '</b> est bien inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                        $comteurInsert++;
                    }
                }
            } else {
                if ($post['typeCours'] == 'obligatoire') {
                    $info = array(
                        'matriculeEtudiant' => $tableauMatricule[$i],
                        'sigle' => $post['sigle'],
                        'semestre' => $post['semestre'],
                        'note' => '-1',
                        'cote' => '',
                        'lien' => 'AV',
                        'etatNote' => 'professeur',
                        'annee' => $post['annee']);
                } elseif ($post['typeCours'] == 'equivalence') {

                    $info = array(
                        'matriculeEtudiant' => $tableauMatricule[$i],
                        'sigle' => $post['sigle'],
                        'semestre' => $post['semestre'],
                        'note' => '-1',
                        'cote' => '',
                        'lien' => 'EQ',
                        'etatNote' => 'professeur',
                        'annee' => $post['annee']);
                } elseif ($post['typeCours'] == 'horsProgramme') {
                    $info = array(
                        'matriculeEtudiant' => $tableauMatricule[$i],
                        'sigle' => $post['sigle'],
                        'semestre' => $post['semestre'],
                        'note' => '-1',
                        'cote' => '',
                        'lien' => 'HV',
                        'etatNote' => 'professeur',
                        'annee' => $post['annee']);
                }
                //insertion des infos dans le plan etudes

                $this->db->insert('planetudes', $info);

                for ($j = 0; $j < $nombreGroupeSelectionne; $j++) {
                    $this->db->where(array('matriculeEtudiant' => $tableauMatricule[$i], 'idGroupe' => $tableauGroupe[$j]));
                    $query = $this->db->get('listeetudiants');
                    if ($query->num_rows() > 0) {
                        $data['valide'][$compteurMessage] = 'L\'�tudiant <b>' . $tableauMatricule[$i] . '</b> est d�j� inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                        $compteurMessage++;
                    } else {
                        $info = array(
                            'matriculeEtudiant' => $tableauMatricule[$i],
                            'idGroupe' => $tableauGroupe[$j]);
                        $this->db->insert('listeetudiants', $info);
                        $data['insert'][$comteurInsert] = 'L\'�tudiant <b>' . $tableauMatricule[$i] . '</b> est bien inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                        $comteurInsert++;
                    }
                }
            }
        }
        return $data;
    }

    function inscrire_etudiant_group($post) {
        $tableauMatricule = $post['items'];

        //($tableauMatricule); 
        if (!isset($post['groupe'])) {
            return $data['pasDeGroupe'] = 'pasDeGroupe';
        }

        $tableauGroupe = $post['groupe'];
        $nombreEtudiant = count($tableauMatricule);
        $data = NULL;
        $nombreGroupeSelectionne = count($tableauGroupe);
        
        if (!isset($post)) {
            return false;
        }
        $compteurMessage = 0;
        $comteurInsert = 0;
        //for ($i = 0; $i < $nombreEtudiant; $i++) 
        //{
        $this->db->where(array('matriculeEtudiant' => $tableauMatricule, 'annee' => $post['annee'], 'semestre' => $post['semestre'], 'sigle' => $post['sigle']));
        $query = $this->db->get('planetudes');
        if ($query->num_rows() > 0) {
            for ($j = 0; $j < $nombreGroupeSelectionne; $j++) {
                $this->db->where(array('matriculeEtudiant' => $tableauMatricule, 'idGroupe' => $tableauGroupe[$j]));
                $query = $this->db->get('listeetudiants');
                if ($query->num_rows() > 0) {
                    $data['valide'][$compteurMessage] = 'L\'�tudiant <b>' . $tableauMatricule . '</b> est d�j� inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                    $compteurMessage++;
                } else {
                    $info = array(
                        'matriculeEtudiant' => $tableauMatricule,
                        'idGroupe' => $tableauGroupe[$j]);
                    $this->db->insert('listeetudiants', $info);
                    $data['insert'][$comteurInsert] = 'L\'�tudiant <b>' . $tableauMatricule . '</b> est bien inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                    $comteurInsert++;
                }
            }
        } else {
            if ($post['typeCours'] == 'obligatoire') {
                $info = array(
                    'matriculeEtudiant' => $tableauMatricule,
                    'sigle' => $post['sigle'],
                    'semestre' => $post['semestre'],
                    'note' => '-1',
                    'cote' => '',
                    'lien' => 'AV',
                    'etatNote' => 'professeur',
                    'annee' => $post['annee']);
            } elseif ($post['typeCours'] == 'equivalence') {

                $info = array(
                    'matriculeEtudiant' => $tableauMatricule,
                    'sigle' => $post['sigle'],
                    'semestre' => $post['semestre'],
                    'note' => '-1',
                    'cote' => '',
                    'lien' => 'EQ',
                    'etatNote' => 'professeur',
                    'annee' => $post['annee']);
            } elseif ($post['typeCours'] == 'horsProgramme') {
                $info = array(
                    'matriculeEtudiant' => $tableauMatricule,
                    'sigle' => $post['sigle'],
                    'semestre' => $post['semestre'],
                    'note' => '-1',
                    'cote' => '',
                    'lien' => 'HV',
                    'etatNote' => 'professeur',
                    'annee' => $post['annee']);
            }
            //insertion des infos dans le plan etudes

            $this->db->insert('planetudes', $info);

            for ($j = 0; $j < $nombreGroupeSelectionne; $j++) {
                $this->db->where(array('matriculeEtudiant' => $tableauMatricule, 'idGroupe' => $tableauGroupe[$j]));
                $query = $this->db->get('listeetudiants');
                if ($query->num_rows() > 0) {
                    $data['valide'][$compteurMessage] = 'L\'�tudiant <b>' . $tableauMatricule . '</b> est d�j� inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                    $compteurMessage++;
                } else {
                    $info = array(
                        'matriculeEtudiant' => $tableauMatricule,
                        'idGroupe' => $tableauGroupe[$j]);
                    $this->db->insert('listeetudiants', $info);
                   // return $this->db->last_query();
                    $data['insert'][$comteurInsert] = 'L\'�tudiant <b>' . $tableauMatricule . '</b> est bien inscrit dans le groupe <b>' . $tableauGroupe[$j] . '</b>';
                    $comteurInsert++;
//return $info;                    
//return $this->db->last_query();
                }
            }
          //  return'nnn';
        }
        //}
        return $data;
    }

    //ajouter un programme
    function insererBulletinSemestriel($data) {

        //$data['note']
        $this->db->insert('bulletinsemestriel', $data);
    }

    //Debut Modif Cheikh pour recuperation donn�es du r�l�ve d'un �tudiant
    function getInfoBulletinEtudiant($matriculeEtudiant) {

        //$data['note'] et_informations_etudiant
        $info = $this->get_informations_etudiant($matriculeEtudiant);
        $result = null;
        $result['matriculeEtudiant'] = $matriculeEtudiant;
        $result['nom'] = $info['nom'];
        $result['prenom'] = $info['prenom'];
        $result['nomArabe'] = $info['nomArabe'];
        $result['prenomArabe'] = $info['prenomArabe'];
        $result['nom'] = $info['nom'];
        $result['nin'] = $info['nin'];
        $result['prenomPere'] = $info['prenomPere'];
        $result['prenomPere_ar'] = $info['prenomPere_ar'];
        $result['dateNaissance'] = $info['dateNaissance'];
        $result['contact'] = $info['telephone1'];
        $result['lieuNaissance'] = $info['lieuNaissance'];
        $result['lieuNaissance_ar'] = $info['lieuNaissance_ar'];
        $result['niveau'] = 'LLL111';
        $result['genre'] = $info['sexe'];
        $result['dateInscription'] = $info['dateInscription'];
        ;
        $result['programme'] = $info['programme'];
        $this->db->where(array('idProgramme' => $info['programme']));
        $query = $this->db->get('programme');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $result['programme'] = $row['nom'];
            $result['idProgramme'] = $row['idProgramme'];
            $result['programmeArabe'] = $row['nomArabe'];
        }
        return $result;
    }

    function getSemestreResult($matriculeEtudiant, $semestre) {

        $data = null;

        $str = "select s.decision, s.credits_val,e.note from semestre_decision_t s,  etudiant_sem_note e where s.semestre = e.semestre and s.matriculeEtudiant=e.matriculeEtudiant  and s.matriculeEtudiant =  " .
                $matriculeEtudiant . " and s.semestre = " . $semestre;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $data['matriculeEtudiant'] = $matriculeEtudiant;
            $data['semestre'] = $semestre;
            $data['ects'] = $row['credits_val'];
            $data['decision'] = $row['decision'];
            if (substr($data['decision'], 0, 3) == 'Ajo') {
                $data['validation'] = 'NV';
            } else {
                $data['validation'] = 'V';
            }
            $data['note'] = $row['note'];
            $data['inscrit'] = 'OK';
        } else {
            $data['inscrit'] = 'NO';
        }
        return $data;
    }

    function getModulesDecision($module, $matriculeEtudiant, $semestre) {
        $str = "SELECT m.decision as decision, m.credits_val as ects, u.titre as titre, e.note as note from modules_decision_t m, unite u, etudiant_mod_note e where m.idModule=u.sigle and m.idModule = e.idModule and m.matriculeEtudiant=e.matriculeEtudiant  and m.matriculeEtudiant =  " .
                $matriculeEtudiant . " and m.idModule = '" . $module . "' and m.semestre = " . $semestre;
        $moduleDec = array();
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {

            $row = $query->result_array();
            //  echo  print_r($row);
            $moduleDec['titre'] = $row[0]['titre'];
            $moduleDec['decision'] = $row[0]['decision'];

            $moduleDec['ects'] = $row[0]['ects'];
            $moduleDec['note'] = $row[0]['note'];
        }
        return $moduleDec;
    }

    function getModulesResult($matriculeEtudiant, $semestre) {

        //$data['note']
        $str = "SELECT  r.`idModule`, r.`sigle`, r.`note`, r.`capit`,r.ects, m.titre, ifNull(n.notecc,0) as notecc,ifNull(n.noteExam,0) as noteExam, ifNull(n.noteRT,0) as noteRT  FROM `releve_t` r, 
notes_globales_dern_t n,module m where r.`matriculeEtudiant` = n.`matriculeEtudiant` and r.`sigle` = n.`sigle` and
r.`semestre` = n.`semestre` and r.matriculeEtudiant = " . $matriculeEtudiant . " and r.semestre =" .
                $semestre . "  and r.sigle = m.sigle order by 1,2";
        // echo $str;
        $modules = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                if ($modules == NULL) {
                    $modules = array();
                    $modules[$row['idModule']] = array();
                    $moduleDec = $this->getModulesDecision($row['idModule'], $matriculeEtudiant, $semestre);
                    $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                    $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                    $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                    $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                } else if (!array_key_exists($row['idModule'], $modules)) {
                    $modules[$row['idModule']] = array();
                    $moduleDec = $this->getModulesDecision($row['idModule'], $matriculeEtudiant, $semestre);
                    $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                    $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                    $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                    $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                }
                $modules[$row['idModule']]['elements'][$row['sigle']] = array();
                $modules[$row['idModule']]['elements'][$row['sigle']]['nfe'] = $row['note'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['capit'] = $row['capit'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ncc'] = $row['notecc'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsn'] = $row['noteExam'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsr'] = $row['noteRT'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ects'] = $row['ects'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['titre'] = $row['titre'];
                ;
                $modules[$row['idModule']]['nb'] ++;
            }
        }
        //echo print_r($modules);
        return $modules;
    }

    function getModulesResults($matriculeEtudiant, $semestre) {

        //$data['note']
        $str = "SELECT  r.`idModule`, r.`sigle`, r.`note`, r.`capit`,r.ects, m.titre, ifNull(n.notecc,0) as notecc,ifNull(n.noteExam,0) as noteExam, ifNull(n.noteRT,0) as noteRT  FROM `releve_t` r, 
notes_globales_dern_t n,module m where r.`matriculeEtudiant` = n.`matriculeEtudiant` and r.`sigle` = n.`sigle` and
r.`semestre` = n.`semestre` and r.matriculeEtudiant = " . $matriculeEtudiant . " and r.semestre =" .
                $semestre . "  and r.sigle = m.sigle order by 1,2";
        // echo $str;
        $modules = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                if ($modules == NULL) {
                    $modules = array();
                    $modules[$row['idModule']] = array();
                    $moduleDec = $this->getModulesDecision($row['idModule'], $matriculeEtudiant, $semestre);
                    $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                    $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                    $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                    $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                } else if (!array_key_exists($row['idModule'], $modules)) {
                    $modules[$row['idModule']] = array();
                    $moduleDec = $this->getModulesDecision($row['idModule'], $matriculeEtudiant, $semestre);
                    $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                    $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                    $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                    $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                }
                $modules[$row['idModule']]['elements'][$row['sigle']] = array();
                $modules[$row['idModule']]['elements'][$row['sigle']]['nfe'] = $row['note'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['capit'] = $row['capit'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ncc'] = $row['notecc'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsn'] = $row['noteExam'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsr'] = $row['noteRT'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ects'] = $row['ects'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['titre'] = $row['titre'];
                ;
                $modules[$row['idModule']]['nb'] ++;
            }
        }
        //echo print_r($modules);
        return $modules;
    }

    ///emmin bultin Historique
    function getModulesResult1($matriculeEtudiant, $semestre) {

        //$data['note'n
        $str = "SELECT   n.`noteCC`,n.`noteExam`,n.`noteRT`,n.sigle,m.nbCredits,n.annee FROM `unite` u,
module m,notes_globales n where   n.matriculeEtudiant = " . $matriculeEtudiant . " and n.semestre =" .
                $semestre . "  and n.sigle = m.sigle and m.sigleunite = u.sigle ";
        // $str;
        $notes = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            $notes = array();
            foreach ($query->result_array() as $row) {
                $notes[] = $row;
            }
        }
        //  echo  print_r($modules);
        return $notes;
    }

    // emmin bultin Historique
    function getModulesResult4($matriculeEtudiant, $semestre) {

        //$data['note']
        $str = "SELECT   u.sigle as `sigleunite` ,n.sigle as `siglemodule` ,count(*)  FROM `unite` u, 
module m,notes_globales n where   n.matriculeEtudiant = " . $matriculeEtudiant . " and u.semestre =" .
                $semestre . "  and n.sigle = m.sigle and m.sigleunite = u.sigle  group by 1,2";
        //echo $str;
        $countNoteEl = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            $countNoteEl = array();
            foreach ($query->result_array() as $row) {
                $countNoteEl[] = $row;
            }
        }
        //  echo  print_r($modules);
        return $countNoteEl;
    }

    // emmin bultin Historique
    function getModulesResult3($matriculeEtudiant, $semestre) {

        //$data['note'] ,sum(nbCredits)
        $str = "SELECT  distinct n.`sigle` as `sigleunite`,m.sigleunite as `sigleModuele`, m.titre,m.nbCredits FROM `unite` u, 
module m,evaluation e,notes_globales n where   n.matriculeEtudiant = " . $matriculeEtudiant . " and u.semestre =" .
                $semestre . "  and n.sigle = m.sigle and m.sigleunite = u.sigle  order by 1,2";
        //echo $str;
        $modules = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            $modules = array();
            foreach ($query->result_array() as $row) {
                $modules[] = $row;
            }
        }
        //  echo  print_r($modules);
        return $modules;
    }

    //emmin bultin Historique
    function getModulesResult2($matriculeEtudiant, $semestre) {

        //$data['note']
        $str = "SELECT  distinct  u.`sigle`, u.titre FROM `unite` u, 
module m,evaluation e,notespartielles n where e.idEvaluation=n.idEvaluation and n.matriculeEtudiant = " . $matriculeEtudiant . " and u.semestre =" .
                $semestre . "  and n.sigle = m.sigle and m.sigleunite = u.sigle  order by 1,2";
        ///echo $str;
        $unites = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            $unites = array();
            foreach ($query->result_array() as $row) {
                $unites[] = $row;
            }
        }
        //  echo  print_r($modules);
        return $unites;
    }

    // Fin Modif pour r�l�v� de notes
    //fonction qui mets � jour  bulletinsemestriel
    // � partir du planetudes
    // mets � jours des tables auxilaires � partir des vues
    function preparer_modules_decision_t($programme, $semestre) {
        $str1 = "delete from modules_decision_t where semestre=" . $semestre . " and idModule in (select sigle from unite where idProgramme='" . $programme . "')";
        $this->db->query($str1);
        $str2 = "insert into  modules_decision_t select * from  modules_decision where semestre=" . $semestre . " and idModule in (select sigle from unite where idProgramme='" . $programme . "')";
        $this->db->query($str2);
    }

    function preparer_releve_t($programme, $semestre) {
        $str1 = "delete from releve_t where semestre=" . $semestre . " and idModule in (select sigle from unite where idProgramme='" . $programme . "')";
        $this->db->query($str1);
        $str2 = "insert into  releve_t select * from  releve where semestre=" . $semestre . " and idModule in (select sigle from unite where idProgramme='" . $programme . "')";
        //echo $str2 . '<br>';
        $this->db->query($str2);
    }

    function preparer_semestre_decision_t($programme, $semestre) {
        $str1 = "delete from semestre_decision_t where semestre=" . $semestre . " and matriculeEtudiant in (select matriculeEtudiant from dossierEtudiant where idProgramme='" . $programme . "')";
        $this->db->query($str1);
        $str2 = "insert into  semestre_decision_t select * from  semestre_decision where semestre=" . $semestre . " and matriculeEtudiant in (select matriculeEtudiant from dossierEtudiant where idProgramme='" . $programme . "')";
        $this->db->query($str2);
    }

    function preparer_notes_globales_dern_t($programme, $semestre) {
        $str1 = "delete from notes_globales_dern_t where semestre=" . $semestre . " and matriculeEtudiant in (select matriculeEtudiant from dossierEtudiant where idProgramme='" . $programme . "')";
        $this->db->query($str1);
        $str2 = "insert into  notes_globales_dern_t select * from  notes_globales_dern where semestre=" . $semestre . " and matriculeEtudiant in (select matriculeEtudiant from dossierEtudiant where idProgramme='" . $programme . "')";
        $this->db->query($str2);
    }

    function preparer_releves($programme, $semestre) {
        $dat = $this->get_date_courante();
        $annee_cour = $dat['annee'];
        $semestre_cour = $dat['semestre'];
        // TODO voir comment generer suelment les notes du semestre courants
        $str = "select p.matriculeEtudiant, p.sigle, p.note,m.nbcredits, u.semestre,u.sigle as idModule from planetudes p, module m, 
        unite u where  p.sigle=m.sigle and m.sigleunite=u.sigle and u.semestre=" . $semestre . " and u.idProgramme='" . $programme . "'";
     //   echo $str . '<br><br>';

        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            //Mis � jour de la table bulletinsemestriel
            foreach ($query->result_array() as $row) {
                $str2 = 'select b.matriculeEtudiant from bulletinsemestriel b where b.matriculeEtudiant =' . $row['matriculeEtudiant'] . " and b.sigle='" . $row['sigle'] . "'";
                $query2 = $this->db->query($str2);
                if ($query2->num_rows() > 0) {
                    $str2 = "update  bulletinsemestriel set note = " . $row['note'] . ", annee =" . $annee_cour . " where matriculeEtudiant =" . $row['matriculeEtudiant'] . " and sigle='" . $row['sigle'] . "'";
                    $this->db->query($str2);
                } else { // l'etudiant et l'element ne sont pas encore dans bulletinsemestriel, on les insere
                    $str3 = "insert into bulletinsemestriel(matriculeEtudiant, sigle, idModule,semestre, idProgramme,grade, annee,coef, nbcredits,note) values(" . $row['matriculeEtudiant'] . ",'"
                            . $row['sigle'] . "','" . $row['idModule'] . "', " . $row['semestre'] . ",'" . $programme . "', 4," . $annee_cour . ',' . $row['nbcredits'] . ',' . $row['nbcredits'] . ',' . $row['note'] . ')';
                    $query3 = $this->db->query($str3);
                }
            }
            //transfert des vues dans les tables auxilaires
            $this->preparer_modules_decision_t($programme, $semestre);
            $this->preparer_semestre_decision_t($programme, $semestre);
            $this->preparer_notes_globales_dern_t($programme, $semestre);
            $this->preparer_releve_t($programme, $semestre);
        }
    }

// debut modif PV
    function get_etudiant_anne_semestre($annee = 2015, $semestre = 3, $idProgramme, $semEtude, $session, $tri) {// A revoir pour que ça marche avec les autrs années
        $etudiants = NULL;
        $query = NULL;
        $anneeRat = $annee;
        if ($session == 1) {

            $this->db->where(array('i.annee' => $annee, 'i.semestre' => $semestre, 'i.semEtude' => $semEtude, 'i.idProgramme' => $idProgramme));

            $this->db->select('i.matriculeEtudiant');
            $this->db->distinct();

            $this->db->from('inscription i');
            $this->db->join('anonymat a', 'i.matriculeEtudiant=a.matriculeEtudiant and i.annee=a.annee and i.semestre=a.semestre');


            if ($tri == 1) {
                $this->db->order_by('i.matriculeEtudiant');
            } else {
                $this->db->order_by('a.code_ex');
            }

            $res = $this->db->get();
        } else {
            if ($semestre == 3) {
                $annee = ($annee % 100) * 100 + (($annee % 100) + 1);
            } else {
                $annee = ($annee % 100 - 1) * 100 + (($annee % 100));
            }
            // $this->db->where(array('annee' => $annee,'semestre' => $semEtude,'idProgramme'=>$idProgramme));
            //  $this->db->distinct();
            //$query = $this->db->get('sessionnairetmp');
            /*   $this->db->where(array('i.annee' => $annee,'i.semestre' => $semestre,'i.semestreB' => $semEtude,'i.idProgramme'=>$idProgramme));

              $this->db->select('i.matriculeEtudiant');
              $this->db->distinct();

              $this->db->from('sessionnairetmp i');
              $this->db->join('anonymat a','i.matriculeEtudiant=a.matriculeEtudiant and i.year=a.annee and i.semestreB=a.semestre');
             */
            // print($year);
            //$res=$this->db->query($sql1);

            if ($tri == 1) {
                $sql1 = "SELECT i.`matriculeEtudiant` FROM `sessionnairetmp` i,anonymat a WHERE i.`idProgramme`='" . $idProgramme . "' and i.`semestreB`=$semestre and i.`annee`=$annee and i.`semestre`=$semEtude and i.matriculeEtudiant=a.matriculeEtudiant and i.year=a.annee and i.semestreB=a.semestre  and i.matriculeEtudiant in(select p.matriculeEtudiant from 
planetudes p,module m,unite u where p.sigle=m.sigle and u.sigle=m.sigleunite and u.semestre=$semEtude and p.annee=$anneeRat and p.semestre=$semestre ) order by  a.matriculeEtudiant ";

                $res = $this->db->query($sql1);
            } else {
                $sql1 = "SELECT i.`matriculeEtudiant` FROM `sessionnairetmp` i,anonymat a WHERE i.`idProgramme`='" . $idProgramme . "' and i.`semestreB`=$semestre and i.`annee`=$annee and i.`semestre`=$semEtude and i.matriculeEtudiant=a.matriculeEtudiant and i.year=a.annee and i.semestreB=a.semestre and i.matriculeEtudiant in(select p.matriculeEtudiant from 
planetudes p,module m,unite u where p.sigle=m.sigle and u.sigle=m.sigleunite and u.semestre=$semEtude and p.annee=$anneeRat and p.semestre=$semestre ) order by  a.code_ex";
                //echo $sql1;
                $res = $this->db->query($sql1);
            }

            //$res = $this->db->get();
        }
        //print_r($query->result_array());
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $etudiants[] = $row['matriculeEtudiant'];
            }
        }
        // print_r($etudiants);
        return $etudiants;
    }

    public function getEtudiants_PV($annee = 2015, $semestre = 3, $idProgramme, $semEtude, $session, $tri) {

        $matricules = $this->scolarite_modele->get_etudiant_anne_semestre($annee, $semestre, $idProgramme, $semEtude, $session, $tri);
//        echo "<h1>hhhhaaaaaaaaaaaa</h1>";
//        print_r($matricules);
//        echo count($matricules);
//        echo "<h1>hhhhaaaaaaaaaaaa</h1>";
        $etudiants = NULL;
        $sessionCourante = $this->get_session_courante();
        $annee_cour = $sessionCourante['annee'][0];
        /*
          if($annee==$annee_cour){ // cella fait la meme chose qu'avant
          if(isset($matricules )) {
          $i = 0;
          $etudiants = array();

          for($i=0; $i<count($matricules); ++$i) {
          $matriculeEtudiant = $matricules[$i];
          $etudiants[$matricules[$i]] = array();
          $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
          $semRes = $this->scolarite_modele->getSemestreResult($matriculeEtudiant, $semEtude);
          $moduleRes = $this->scolarite_modele->getModulesResult($matriculeEtudiant, $semEtude);
          $anonymat=$this->scolarite_modele->get_etudiant_annonymat($matriculeEtudiant,$session,$annee,$semestre);
          $etudiants[$matricules[$i]]['info'] = $infoEtudiant;
          $etudiants[$matricules[$i]]['semestre'] = $semRes;
          $etudiants[$matricules[$i]]['modules'] = $moduleRes;
          $etudiants[$matricules[$i]]['annee'] = $annee;
          $etudiants[$matricules[$i]]['numSem'] = $semEtude;
          $etudiants[$matricules[$i]]['idProgramme'] = $idProgramme;
          $etudiants[$matricules[$i]]['anonymat'] = $anonymat;
          }

          }
          }else { */ // traiter les annÃ©es prÃ©cÃ©dentes
        //  echo '*************** je suis ici donc je suis bien**************';

        if (isset($matricules)) {
            // echo '*************** il y a des etudiants**************';
            $i = 0;
            $etudiants = array();
            $list_e = '';
            for ($i = 0; $i < count($matricules);  ++$i)
                $list_e = $list_e . $matricules[$i] . ',';
            $list_e = $list_e . $matricules[count($matricules) - 1];
            //$list_e="13044";
            $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant_liste($list_e);
            $semRes = $this->scolarite_modele->getSemestreResult_bis_liste($list_e, $semEtude, $annee);
            $moduleRes = $this->scolarite_modele->getModulesResult_bis_liste($list_e, $semEtude, $annee);
            $anonymat = $this->scolarite_modele->get_etudiant_annonymat_liste($list_e, $session, $annee, $semestre);
            $pvCC = $this->scolarite_modele->getpvFraude($list_e, $semestre, $annee, $evaluation = 1);
            $pvSN = $this->scolarite_modele->getpvFraude($list_e, $semestre, $annee, $evaluation = 2);
            $pvSR = $this->scolarite_modele->getpvFraude($list_e, $semestre, $annee, $evaluation = 4);

            $abCC = $this->scolarite_modele->getpvAbsence($list_e, $semestre, $annee, $evaluation = 1);
            $abSN = $this->scolarite_modele->getpvAbsence($list_e, $semestre, $annee, $evaluation = 2);

            $abSR = $this->scolarite_modele->getpvAbsence($list_e, $semestre, $annee, $evaluation = 4);

            $zeroCC = $this->scolarite_modele->getNotezero($list_e, $semestre, $annee, $evaluation = 1);
            $zeroSN = $this->scolarite_modele->getNotezero($list_e, $semestre, $annee, $evaluation = 2);

            $zeroSR = $this->scolarite_modele->getNotezero($list_e, $semestre, $annee, $evaluation = 4);
            $element_ratt_en_gras = $this->scolarite_modele->getElement_ratt($list_e, $semestre, $annee, $evaluation = 4);

// print_r($element_ratt_en_gras['matriculeEtudiant']);
            for ($j = 0; $j < count($matricules);  ++$j) {
                $etudiants[$matricules[$j]]['numSem'] = $semEtude;
                $etudiants[$matricules[$j]]['idProgramme'] = $idProgramme;
            }
            if (isset($pvCC['matriculeEtudiant'])) {
                for ($m = 0; $m < count($pvCC['matriculeEtudiant']);  ++$m) {
                    $etudiants[$pvCC['matriculeEtudiant'][$m]]['pvCC']['sigle'][] = $pvCC['sigle'][$m];
                    $etudiants[$pvCC['matriculeEtudiant'][$m]]['pvCC']['matriculeEtudiant'][] = $pvCC['matriculeEtudiant'][$m];
                }
            }
            if (isset($pvSN['matriculeEtudiant'])) {
                for ($p = 0; $p < count($pvSN['matriculeEtudiant']);  ++$p) {
                    $etudiants[$pvSN['matriculeEtudiant'][$p]]['pvSN']['sigle'][] = $pvSN['sigle'][$p];
                    $etudiants[$pvSN['matriculeEtudiant'][$p]]['pvSN']['matriculeEtudiant'][] = $pvSN['matriculeEtudiant'][$p];
                }
            }
            if (isset($pvSR['matriculeEtudiant'])) {
                for ($p = 0; $p < count($pvSR['matriculeEtudiant']);  ++$p) {
                    $etudiants[$pvSR['matriculeEtudiant'][$p]]['pvSR']['sigle'][] = $pvSR['sigle'][$p];
                    $etudiants[$pvSR['matriculeEtudiant'][$p]]['pvSR']['matriculeEtudiant'][] = $pvSR['matriculeEtudiant'][$p];
                }
            }
            if (isset($abCC['matriculeEtudiant'])) {
                for ($m = 0; $m < count($abCC['matriculeEtudiant']);  ++$m) {
                    $etudiants[$abCC['matriculeEtudiant'][$m]]['abCC']['sigle'][] = $abCC['sigle'][$m];
                    $etudiants[$abCC['matriculeEtudiant'][$m]]['abCC']['matriculeEtudiant'][] = $abCC['matriculeEtudiant'][$m];
                }
            }
            if (isset($abSN['matriculeEtudiant'])) {
                for ($p = 0; $p < count($abSN['matriculeEtudiant']);  ++$p) {
                    $etudiants[$abSN['matriculeEtudiant'][$p]]['abSN']['sigle'][] = $abSN['sigle'][$p];
                    $etudiants[$abSN['matriculeEtudiant'][$p]]['abSN']['matriculeEtudiant'][] = $abSN['matriculeEtudiant'][$p];
                }
            }
            if (isset($abSR['matriculeEtudiant'])) {
                for ($p = 0; $p < count($abSR['matriculeEtudiant']);  ++$p) {
                    $etudiants[$abSR['matriculeEtudiant'][$p]]['abSR']['sigle'][] = $abSR['sigle'][$p];
                    $etudiants[$abSR['matriculeEtudiant'][$p]]['abSR']['matriculeEtudiant'][] = $abSR['matriculeEtudiant'][$p];
                }
            }
            if (isset($zeroCC['matriculeEtudiant'])) {
                for ($m = 0; $m < count($zeroCC['matriculeEtudiant']);  ++$m) {
                    $etudiants[$zeroCC['matriculeEtudiant'][$m]]['zeroCC']['sigle'][] = $zeroCC['sigle'][$m];
                    $etudiants[$zeroCC['matriculeEtudiant'][$m]]['zeroCC']['matriculeEtudiant'][] = $zeroCC['matriculeEtudiant'][$m];
                }
            }
            if (isset($zeroSN['matriculeEtudiant'])) {
                for ($m = 0; $m < count($zeroSN['matriculeEtudiant']);  ++$m) {
                    $etudiants[$zeroSN['matriculeEtudiant'][$m]]['zeroSN']['sigle'][] = $zeroSN['sigle'][$m];
                    $etudiants[$zeroSN['matriculeEtudiant'][$m]]['zeroSN']['matriculeEtudiant'][] = $zeroSN['matriculeEtudiant'][$m];
                }
            }
            if (isset($zeroSR['matriculeEtudiant'])) {
                for ($m = 0; $m < count($zeroSR['matriculeEtudiant']);  ++$m) {
                    $etudiants[$zeroSR['matriculeEtudiant'][$m]]['zeroSR']['sigle'][] = $zeroSR['sigle'][$m];
                    $etudiants[$zeroSR['matriculeEtudiant'][$m]]['zeroSR']['matriculeEtudiant'][] = $zeroSR['matriculeEtudiant'][$m];
                }
            }
            if (isset($element_ratt_en_gras['matriculeEtudiant'])) {
                for ($m = 0; $m < count($element_ratt_en_gras['matriculeEtudiant']);  ++$m) {
                    $etudiants[$element_ratt_en_gras['matriculeEtudiant'][$m]]['element_ratt_en_gras']['sigle'][] = $element_ratt_en_gras['sigle'][$m];
                    $etudiants[$element_ratt_en_gras['matriculeEtudiant'][$m]]['element_ratt_en_gras']['matriculeEtudiant'][] = $element_ratt_en_gras['matriculeEtudiant'][$m];
                }
            }

            for ($k = 0; $k < count($anonymat['matriculeEtudiant']); $k++) {
                $etudiants[$anonymat['matriculeEtudiant'][$k]]['anonymat'] = $anonymat['code'][$k];
            }
            for ($i = 0; $i < count($infoEtudiant['matriculeEtudiant']); $i++) {
                $etudiants[$infoEtudiant['matriculeEtudiant'][$i]]['info']['matriculeEtudiant'] = $infoEtudiant['matriculeEtudiant'][$i];
                $etudiants[$infoEtudiant['matriculeEtudiant'][$i]]['info']['nom'] = $infoEtudiant['nom'][$i];
                $etudiants[$infoEtudiant['matriculeEtudiant'][$i]]['info']['prenom'] = $infoEtudiant['prenom'][$i];
                $etudiants[$infoEtudiant['matriculeEtudiant'][$i]]['info']['programme'] = $infoEtudiant['programme'][$i];
            }
            for ($i = 0; $i < count($semRes['matriculeEtudiant']); $i++) {
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['matriculeEtudiant'] = $semRes['matriculeEtudiant'][$i];
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['semestre'] = $semRes['semestre'][$i];
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['ects'] = $semRes['ects'][$i];
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['decision'] = $semRes['decision'][$i];
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['validation'] = $semRes['validation'][$i];
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['inscrit'] = $semRes['inscrit'][$i];
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['note'] = $semRes['note'][$i];
            }
            foreach ($moduleRes as $matriculeEtudiant => $modules) {
                foreach ($modules as $idModule => $module) {
                    //  echo "hhh"; print_r($module); echo"bbb";
                    $etudiants[$matriculeEtudiant]['modules'][$idModule]['titre'] = $module['titre'];
                    $etudiants[$matriculeEtudiant]['modules'][$idModule]['decision'] = $module['decision'];
                    $etudiants[$matriculeEtudiant]['modules'][$idModule]['nm'] = $module['nm'];
                    $etudiants[$matriculeEtudiant]['modules'][$idModule]['ects'] = $module['ects'];
                    $etudiants[$matriculeEtudiant]['modules'][$idModule]['nb'] = $module['nb'];

                    // $etudiants[$matriculeEtudiant]['modules'][$idModule]=$idModule;
                    // $etudiants[$matriculeEtudiant]['modules'][$idModule]['titre']=$module['titre'];
                    foreach ($module['elements'] as $sigle => $modul) {
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['titre'] = $modul['titre'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['nfe'] = $modul['nfe'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['status'] = $modul['status'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['capit'] = $modul['capit'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['ncc'] = $modul['ncc'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['nsn'] = $modul['nsn'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['nsr'] = $modul['nsr'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['ects'] = $modul['ects'];

                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['titre'] = $modul['titre'];
                    }
                }
            }
            $limite = count($matricules) / 2;
            if (count($matricules) % 2 == 1)
                $limite = (count($matricules) - 1) / 2;
            /*   for($i=0; $i<$limite; ++$i) {
              $matriculeEtudiant = $matricules[$i];
              $etudiants[$matricules[$i]] = array();

              //echo 'info etudiant passé';

              //echo '$semRes passé';

              // echo '$moduleRes';
              $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
              $semRes = $this->scolarite_modele->getSemestreResult_bis($matriculeEtudiant, $semEtude,$annee);
              $moduleRes = $this->scolarite_modele->getModulesResult_bis($matriculeEtudiant, $semEtude,$annee);
              $anonymat=$this->scolarite_modele->get_etudiant_annonymat($matriculeEtudiant,$session,$annee,$semestre);
              //echo '$anonymat';
              $etudiants[$matricules[$i]]['info'] = $infoEtudiant;
              $etudiants[$matricules[$i]]['semestre'] = $semRes;
              $etudiants[$matricules[$i]]['modules'] = $moduleRes;
              $etudiants[$matricules[$i]]['annee'] = $annee;
              $etudiants[$matricules[$i]]['numSem'] = $semEtude;
              $etudiants[$matricules[$i]]['idProgramme'] = $idProgramme;
              $etudiants[$matricules[$i]]['anonymat'] = $anonymat;
              } */

            /*  for($i=0; $i<count($matricules); ++$i) {
              $matriculeEtudiant = $matricules[$i];
              $etudiants[$matricules[$i]] = array();

              //echo 'info etudiant passé';

              //echo '$semRes passé';

              // echo '$moduleRes';
              $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
              $semRes = $this->scolarite_modele->getSemestreResult_bis($matriculeEtudiant, $semEtude,$annee);
              $moduleRes = $this->scolarite_modele->getModulesResult_bis($matriculeEtudiant, $semEtude,$annee);
              $anonymat=$this->scolarite_modele->get_etudiant_annonymat($matriculeEtudiant,$session,$annee,$semestre);
              //echo '$anonymat';
              $etudiants[$matricules[$i]]['info'] = $infoEtudiant;
              $etudiants[$matricules[$i]]['semestre'] = $semRes;
              $etudiants[$matricules[$i]]['modules'] = $moduleRes;
              $etudiants[$matricules[$i]]['annee'] = $annee;
              $etudiants[$matricules[$i]]['numSem'] = $semEtude;
              $etudiants[$matricules[$i]]['idProgramme'] = $idProgramme;
              $etudiants[$matricules[$i]]['anonymat'] = $anonymat;
              } */

            // }
        }
        return $etudiants;
    }

    function get_etudiant_annonymat($matricule, $session, $annee, $semestre) {

        $sql = "select code_ex, code_rt from anonymat  where annee=" . $annee . " and semestre=" . $semestre . " and anonymat.matriculeEtudiant=" . $matricule;
        // $this->db->where('matriculeEtudiant', $matricule[$i]);
        //$query = $this->db->get('etudiant');
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                if ($session == 1) {
                    return $row['code_ex'];
                } else {
                    return $row['code_ex'];
                }
            }
        }

        return NULL;
    }

    function getInfoModule($sigle) {
        $sql = " SELECT M.`titre` as titreModule, U.titre as titreUnite, M.idDepartement as idDepartement, U.sigle as sigleUnite FROM `module` M, Unite U where M.sigleunite=U.sigle and M.sigle='" . $sigle . "'";
        $q = $this->db->query($sql);
        $res = $q->row_array();

        return $res;
    }

// Hafedh 02-07-2016
    function getStat($idProgramme, $annee) {
        if ($idProgramme == 'tout')
            $prog = array("LGTR", "MAEF", "MAN", "RXTEL");
        else
            $prog = array($idProgramme);



        $data = array();

        for ($i = 0; $i < count($prog); $i++) {
            $idProgramme = $prog[$i];
            $query = "SELECT count(distinct `matriculeEtudiant`) as nbEtu1 FROM `niveau_inscrits` where annee=" . $annee . " and (semestre=3)  and niveau=1 and `idProgramme`='" . $idProgramme . "'";
            $q = $this->db->query($query);
            $res = $q->row_array();
            $data[$i]['nbEtu1'] = $res['nbEtu1'];

            $query = "SELECT count(distinct `matriculeEtudiant`) as nbEtu2 FROM `niveau_inscrits` where annee=" . $annee . " and (semestre=3)  and niveau=2 and `idProgramme`='" . $idProgramme . "'";
            $q = $this->db->query($query);
            $res = $q->row_array();
            $data[$i]['nbEtu2'] = $res['nbEtu2'];

            $query = "SELECT count(distinct `matriculeEtudiant`) as nbEtu3 FROM `niveau_inscrits` where annee=" . $annee . " and (semestre=3)  and niveau=3 and `idProgramme`='" . $idProgramme . "'";
            $q = $this->db->query($query);
            $res = $q->row_array();
            $data[$i]['nbEtu3'] = $res['nbEtu3'];
        }

        return $data;
    }

    function getLocaux() {
        $query = "select idLocal from local";
        $query = $this->db->query($query);

        $locaux = NULL;

        if ($query->num_rows() > 0) {
            $locaux = array();
            foreach ($query->result_array() as $row) {
                $locaux[] = $row;
            }
        }
        return $locaux;
    }

    function getNomEmploye($matriculeEmploye) {
        $query = "select prenom, nom from employe where matriculeEmploye='" . $matriculeEmploye . "'";
        $q = $this->db->query($query);
        $res = $q->row_array();

        return $res['prenom'] . ' ' . $res['nom'];
    }

    function getModulesEnseignes($matriculeEmploye, $annee, $semestre) {

        $query = "SELECT M.sigle, M.titre FROM `module` M where M.`professeurResponsable`='" . $matriculeEmploye .
                "' or M.sigle in (select distinct sigle from groupe where matriculeEmploye='" . $matriculeEmploye . "' and annee=" . $annee . " and semestre=" . $semestre . ") " .
                " or M.sigle in (select distinct sigle from enseignement where matriculeEmploye='" . $matriculeEmploye . "' and annee=" . $annee . " and semestre=" . $semestre . ") ";

        // echo $query;

        $modules = NULL;
        $query = $this->db->query($query);
        if ($query->num_rows() > 0) {
            $modules = array();
            foreach ($query->result_array() as $row) {
                $modules[] = $row;
            }
        }

        return $modules;
    }

    function getAllModules($annee, $semestre,$autre_sem='') {
if(empty($autre_sem))
        $query = "select distinct sigle from groupe where annee=" . $annee . " and semestre=" . $semestre;
else{
    $query = "select distinct sigle from groupe where annee=" . $annee . " and (semestre=" . $semestre." or semestre=" . $autre_sem.")";
}
      //  echo $query;

        $modules = NULL;
        $query = $this->db->query($query);
        if ($query->num_rows() > 0) {
            $modules = array();
            foreach ($query->result_array() as $row) {
                $modules[] = $row;
            }
        }

        return $modules;
    }

    function enregistrer_horaire($anneeC, $semestreC, $matriculeEmploye, $date, $heureD, $duree, $sigle, $groupe, $type, $idLocal = 'SALLE1', $commentaire = '') {
        $date2 = $this->convert_date($date);

        $query = "insert into enseignement values ( " . $matriculeEmploye . ',' . "'" . $date2 . "'" . ',' . $heureD . ',' . $duree . ',' . $sigle . ',' . $groupe . ',' . $idLocal . ',' . $anneeC . ',' . $semestreC . ',' . $type . ',' . $commentaire . ")";
        //echo $query;
        $this->db->query($query);
    }

    // 19-03-2019 Alioune
    // retourne le nombre d'heure qui restent de la charge ,  de la matiere($sigle) et de type de cours($type) 
    // des le debut de cours de semestre -> aujordhuit
    function nombre_heures_enseignees_module($sigle, $type) {
        $type_a_verifier = "";
        if ($type == "cours")
            $type_a_verifier = "volumeCM";
        if ($type == "tp")
            $type_a_verifier = "volumeTP";
        if ($type == "td")
            $type_a_verifier = "volumeTD";
        $query = "SELECT $type_a_verifier - SUM(duree) AS 'rest'
        FROM enseignement en INNER JOIN  module m ON en.sigle=m.sigle
        WHERE 
        en.sigle  LIKE '$sigle' 
        AND en.type='$type' 
        AND en.date>=(select debutcours from sessioncourante_calendar) 
        AND en.date<=NOW()";
      //  echo "$query";
        $requete = $this->db->query($query);
        $resultat = NULL;

        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    // des versions de fonctions pour pv afon de gérer l'atrchive
    //TODO : A=Traiter l'année courante de la même façon
    function getSemestreResult_bis($matriculeEtudiant, $semestre, $annee) {

        $data = null;

        $str = "select s.decision, s.credits_val,e.note from semestre_decision_t_bis s,  etudiant_sem_note_t_bis e where s.semestre = e.semestre and s.annee = e.annee and s.matriculeEtudiant=e.matriculeEtudiant  and s.matriculeEtudiant =  " .
                $matriculeEtudiant . " and s.semestre = " . $semestre . " and s.annee = " . $annee;
        // echo $str;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $data['matriculeEtudiant'] = $matriculeEtudiant;
            $data['semestre'] = $semestre;
            $data['ects'] = $row['credits_val'];
            $data['decision'] = $row['decision'];
            if (substr($data['decision'], 0, 3) == 'Ajo') {
                $data['validation'] = 'NV';
            } else {
                $data['validation'] = 'V';
            }
            $data['note'] = $row['note'];
            $data['inscrit'] = 'OK';
        } else {
            $data['inscrit'] = 'NO';
        }
        // echo 'getsemestreresult_bis'.$matriculeEtudiant;
        return $data;
    }

    function getModulesDecision_bis($module, $matriculeEtudiant, $semestre, $annee) {
        $str = "SELECT   m.decision as decision, u.credits as ects, u.titre as titre, e.note as note from modules_decision_t_bis m, unite u, etudiant_mod_note_bis e where m.idModule=u.sigle and m.idModule = e.idModule and m.matriculeEtudiant=e.matriculeEtudiant and m.annee=e.annee and m.semestre=e.semestre  and m.matriculeEtudiant =  " .
                $matriculeEtudiant . " and m.idModule = '" . $module . "' and m.semestre = " . $semestre . " and m.annee = " . $annee . " ";
        // echo $str;
        $moduleDec = array();
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {

            $row = $query->result_array();
            //  echo  print_r($row);
            $moduleDec['titre'] = $row[0]['titre'];
            $moduleDec['decision'] = $row[0]['decision'];

            $moduleDec['ects'] = $row[0]['ects'];
            $moduleDec['note'] = $row[0]['note'];
        }
        //  echo 'getModulesDecision_bis'.$matriculeEtudiant;
        return $moduleDec;
    }

    function getModulesResult_bis($matriculeEtudiant, $semestre, $annee) {

        //$data['note']
        $str = null;
        if ($semestre % 2 == 1) {
            $str = "SELECT    0 as status  ,r.`idModule`, r.`sigle`, r.`note`, r.`capit`,r.ects, m.titre, ifNull(n.notecc,0) as notecc,ifNull(n.noteExam,0) as noteExam, n.noteRT as noteRT  FROM `releve_t_bis` r, 
notes_globales_dern_t_bis n,module m where r.`matriculeEtudiant` = n.`matriculeEtudiant` and r.`sigle` = n.`sigle` and
r.`semestre` = n.`semestre` and r.annee=(2000+floor(n.annee/100)) and r.matriculeEtudiant = " . $matriculeEtudiant . " and r.semestre =" .
                    $semestre . " and r.annee =" . $annee . "  and r.sigle = m.sigle  union select 1 as status  ,u.sigle as idModule,m.sigle as sigle, 0 as note,'NC' as capit,m.nbCredits as ects,m.titre,0 as notecc,0 as noteExam,0 as noteRT from unite u,module m,elements_caches e where e.sigle=m.sigle and u.sigle=m.sigleunite and e.semestre=3 and e.annee=2017  and e.matriculeEtudiant = " . $matriculeEtudiant . " order by 1,2";
        } else {
            $str = "SELECT  0 as status  ,r.`idModule`, r.`sigle`, r.`note`, r.`capit`,r.ects, m.titre, ifNull(n.notecc,0) as notecc,ifNull(n.noteExam,0) as noteExam, ifNull(n.noteRT,0) as noteRT  FROM `releve_t_bis` r, 
notes_globales_dern_t_bis n,module m where r.`matriculeEtudiant` = n.`matriculeEtudiant` and r.`sigle` = n.`sigle` and
r.`semestre` = n.`semestre` and r.annee=(2001+floor(n.annee/100)) and r.matriculeEtudiant = " . $matriculeEtudiant . " and r.semestre =" .
                    $semestre . " and r.annee =" . $annee . "  and r.sigle = m.sigle  union select 1 as status   ,u.sigle as idModule,m.sigle as sigle, 0 as note,'NC' as capit,m.nbCredits as ects,m.titre,0 as notecc,0 as noteExam,0 as noteRT from unite u,module m,elements_caches e where e.sigle=m.sigle and u.sigle=m.sigleunite and e.semestre=1 and e.annee=2018 and e.matriculeEtudiant = " . $matriculeEtudiant . "  order by 1,2";
        }
        //echo $str;

        $modules = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            // $i=0;
            foreach ($query->result_array() as $row) {
                $moduleDec = NULL;
                if ($modules == NULL) {

                    $modules = array();
                    $moduleDec = $this->getModulesDecision_bis($row['idModule'], $matriculeEtudiant, $semestre, $annee);
                    if ($moduleDec != NULL) {
                        $modules[$row['idModule']] = array();
                        $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                        $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                        $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                        $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    }
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                } else if (!array_key_exists($row['idModule'], $modules)) {

                    $moduleDec = $this->getModulesDecision_bis($row['idModule'], $matriculeEtudiant, $semestre, $annee);
                    if ($moduleDec != NULL) {
                        $modules[$row['idModule']] = array();
                        $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                        $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                        $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                        $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    }
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                }
                //if($modules[$row['idModule']]['elements']!=NULL){
                $modules[$row['idModule']]['elements'][$row['sigle']] = array();
                $modules[$row['idModule']]['elements'][$row['sigle']]['status'] = $row['status'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nfe'] = $row['note'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['capit'] = $row['capit'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ncc'] = $row['notecc'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsn'] = $row['noteExam'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsr'] = $row['noteRT'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ects'] = $row['ects'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['titre'] = $row['titre'];
                ;
                $modules[$row['idModule']]['nb'] ++;
                // }
            }
        }
        //echo print_r($modules);
        // echo 'getModulesResult_bis'.$matriculeEtudiant;
        return $modules;
    }

    function getEtudiants_Niveau($idProgramme, $annee, $niveau) {
        $etudiants = '';
        /* ancien code
          $this->db->where(array('annee' => $annee-1,'niveau' => $niveau,'idProgramme' => $idProgramme));
          $this->db->distinct();
          $this->db->from('niveau');
          $this->db->join('dossieretudiant', 'dossieretudiant.matriculeEtudiant =
          niveau.matriculeEtudiant');
         */
        $this->db->where(array('annee' => $anne, 'niveau' => $niveau, 'idProgramme' => $idProgramme));
        $this->db->distinct();
        $this->db->from('niveau_inscrits');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $etudiants[] = $row['matriculeEtudiant'];
            }
        }
        return $etudiants;
    }

    function getRedoublants_niveau($idProgramme, $annee, $niveau) {
        $etudiants = '';
        $this->db->where(array('annee' => $annee - 1, 'niveau' => $niveau, 'dossieretudiant.idProgramme' => $idProgramme));
        $this->db->distinct();
        $this->db->from('redoublant');
        $this->db->join('dossieretudiant', 'dossieretudiant.matriculeEtudiant =
            redoublant.matriculeEtudiant');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $etudiants[] = $row['matriculeEtudiant'];
            }
        }
        return $etudiants;
    }

    function getElements_a_ratt($annee, $niveau, $matricule) {
        $etudiants = '';
        $this->db->where(array('rattrapage.matriculeEtudiant' => $matricule, 'rattrapage.annee' => $annee, 'niveau.annee' => $annee - 1, 'niveau.niveau' => $niveau));
        $this->db->select('rattrapage.matriculeEtudiant,rattrapage.sigle,rattrapage.semestre,rattrapage.semratt,rattrapage.annee');
        $this->db->distinct();
        $this->db->from('rattrapage');
         $this->db->join('niveau', 'niveau.matriculeEtudiant =rattrapage.matriculeEtudiant');
        $this->db->order_by('semratt');
        $this->db->order_by('sigle');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $etudiants[] = $row['matriculeEtudiant'];
            }
        }
        return $etudiants;
    }

    function getElements_a_inscrire($idProgramme, $annee, $niveau) {
        $result = null;
        $etudiants = $this->getEtudiants_Niveau($idProgramme, $annee, $niveau);
        $redoublants = $this->getRedoublants_niveau($idProgramme, $annee, $niveau);
        $elem_a_ratt = $this->getElements_a_ratt($idProgramme, $annee, $niveau);
//        $maquette = $this->trouver_maquette($idProgramme, $annee, $niveau);
        $result['listeEtu'] = $etudiants;
        $result['listeRed'] = $redoublants;
        $result['listeRat'] = $elem_a_ratt;
        for ($i = 0; $i < count($etudiants); ++$i) {
            $result[$etudiant[$i]] = array();
        }
        return $result;
    }

    // fonction qui retourne le niveau de l'etudiant numero $matricule en premeir semestre de l'annee $annee,
    //selon les element dont il est inscrits
    function getEtudiatNiveauInscrit($matricule, $annee) {
        $query = "select max(niveau) as niveau from niveau_inscrits where matriculeetudiant=" . $matricule . " and annee<=" . $annee . " and semestre=3";
        //echo $query;
        $q = $this->db->query($query);
        $res = $q->row_array();

        return $res['niveau'];
    }

    function getListeHeuresEnseignement() {
        $sessionCourante = $this->get_session_courante();
        $annee = $sessionCourante['annee'][0];
        $semestre = $sessionCourante['semestre'][0];

        $query = "select A.matriculeEmploye, CONCAT( B.nom, ' ',  B.prenom) as nom,A.date,A.heureD,A.duree,CONCAT('[', A.sigle,']', ' ',m.titre) as sigle,CONCAT('[', u.semestre,']', ' ',u.idProgramme) as sf,CONCAT('[', A.idLocal,']', ' ',l.description) as idLocal, B.compteBancaire,A.type,A.commentaire  from enseignement A, employe B,module m,local l,unite u where l.idLocal=A.idLocal and A.sigle=m.sigle and m.sigleunite=u.sigle and A.matriculeEmploye=B.matriculeEmploye and A.annee=" . $annee . " and A.semestre=" . $semestre;
        $query = $this->db->query($query);

        $listeH = NULL;

        if ($query->num_rows() > 0) {
            $listeH = array();
            foreach ($query->result_array() as $row) {
                $listeH[] = $row;
            }
        }
        return $listeH;
    }

    function getListeHeuresEnseignement_element($idGroupe, $start, $end, $employe) {
        $sessionCourante = $this->get_session_courante_calendar();
        $annee = $sessionCourante['annee'][0];
        $semestre = $sessionCourante['semestre'][0];

        //$query="select A.matriculeEmploye, CONCAT( B.nom, ' ',  B.prenom) as nom,A.date,A.heureD,A.duree,CONCAT('[', A.sigle,']', ' ',m.titre) as sigle,CONCAT('[', u.semestre,']', ' ',u.idProgramme) as sf,CONCAT('[', A.idLocal,']', ' ',l.description) as idLocal, B.compteBancaire,A.type,A.commentaire  from enseignement A, employe B,module m,local l,unite u where l.idLocal=A.idLocal and A.sigle=m.sigle and m.sigleunite=u.sigle and A.matriculeEmploye=B.matriculeEmploye and A.matriculeEmploye='".$employe."' and  A.idGroupe='".$idGroupe."' and A.annee=".$annee."  and A.semestre=".$semestre." order by 3";
        $query = "SELECT A.matriculeEmploye, CONCAT( B.nom, ' ', B.prenom) AS nom, A.date, A.heureD, A.duree, CONCAT('[', A.sigle,']', ' ',m.titre) AS sigle, CONCAT('[', u.semestre,']', ' ',u.idProgramme) AS sf, CONCAT('[', A.idLocal,']', ' ',l.description) AS idLocal, B.compteBancaire, A.type, A.commentaire "
                . "FROM enseignement A, employe B, module m, local l, unite u, groupe g "
                . "WHERE l.idLocal=A.idLocal AND A.idGroupe=g.idGroupe and A.sigle=m.sigle AND m.sigleunite=u.sigle AND A.matriculeEmploye=B.matriculeEmploye and A.date>=(select debutCours from sessioncourante_calendar) AND A.date<=NOW()  AND A.matriculeEmploye='" . $employe . "' AND A.idGroupe='" . $idGroupe . "'AND A.annee=" . $annee . " AND A.semestre=" . $semestre . " "
                . "union SELECT g.matriculeEmploye, CONCAT( em.nom, ' ', em.prenom) AS nom, DATE_FORMAT(ev.start,'%Y-%m-%d') AS date , DATE_FORMAT(ev.start,'%H') as heureD, '0' AS duree, CONCAT('[', g.sigle,']', ' ',m.titre) AS sigle, CONCAT('[', u.semestre,']', ' ',u.idProgramme) AS sf, CONCAT('[', ev.id_salle,']', ' ',l.description) AS idLocal, em.compteBancaire , IF(ev.dow='CM', 'cours', IF(ev.dow='TP', 'tp', 'td')) AS type, 'Non fait' As 'commentaire' "
                . "FROM events ev, groupe g, employe em, module m, unite u, local l "
                . "WHERE ev.idGroupe=g.idGroupe AND g.matriculeEmploye=em.matriculeEmploye AND g.sigle=m.sigle AND m.sigleunite=u.sigle and l.idLocal=ev.id_salle AND ev.start>=(select debutCours from sessioncourante_calendar) AND ev.end<=NOW() AND g.matriculeEmploye='" . $employe . "'AND g.idGroupe='" . $idGroupe . "'AND g.annee=" . $annee . " AND g.semestre=" . $semestre . "  AND (g.idGroupe,g.matriculeEmploye,DATE_FORMAT(ev.start,'%Y-%m-%d'),cast(DATE_FORMAT(ev.start,'%H') as unsigned) ) not in (select idGroupe,matriculeEmploye,date,heureD from enseignement) ORDER BY 3";

        //echo $query;
        $query = $this->db->query($query);

        $listeH = NULL;

        if ($query->num_rows() > 0) {
            $listeH = array();
            foreach ($query->result_array() as $row) {
                $listeH[] = $row;
            }
        }
        return $listeH;
    }

    function getHistoriqueB($matriculeEtudiant, $semestre) {

        $query = "select distinct annee from notespartielles p,module m,unite u where  p.sigle=m.sigle and m.sigleunite=u.sigle and p.matriculeEtudiant=" . $matriculeEtudiant . " and u.semestre=" . $semestre;

        // echo $query;

        $annee = NULL;
        $query = $this->db->query($query);
        if ($query->num_rows() > 0) {
            $annee = array();
            foreach ($query->result_array() as $row) {
                $annee[] = $row;
            }
        }

        return $annee;
    }

    function getMoeyenneLicence($matriculeEtudiant) {

        //$data['note']
        $str = "SELECT  r.`idModule`, r.`sigle`, r.`note`, r.`capit`,r.ects, m.titre, ifNull(n.notecc,0) as notecc,ifNull(n.noteExam,0) as noteExam, ifNull(n.noteRT,0) as noteRT  FROM `releve_t` r, 
notes_globales_dern_t n,module m where r.`matriculeEtudiant` = n.`matriculeEtudiant` and r.`sigle` = n.`sigle` and
r.`semestre` = n.`semestre` and r.matriculeEtudiant = " . $matriculeEtudiant . " and r.semestre =" .
                $semestre . "  and r.sigle = m.sigle order by 1,2";

        $modules = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                if ($modules == NULL) {
                    $modules = array();
                    $modules[$row['idModule']] = array();
                    $moduleDec = $this->getModulesDecision($row['idModule'], $matriculeEtudiant, $semestre);
                    $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                    $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                    $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                    $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                } else if (!array_key_exists($row['idModule'], $modules)) {
                    $modules[$row['idModule']] = array();
                    $moduleDec = $this->getModulesDecision($row['idModule'], $matriculeEtudiant, $semestre);
                    $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                    $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                    $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                    $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                }
                $modules[$row['idModule']]['elements'][$row['sigle']] = array();
                $modules[$row['idModule']]['elements'][$row['sigle']]['nfe'] = $row['note'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['capit'] = $row['capit'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ncc'] = $row['notecc'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsn'] = $row['noteExam'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsr'] = $row['noteRT'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ects'] = $row['ects'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['titre'] = $row['titre'];
                ;
                $modules[$row['idModule']]['nb'] ++;
            }
        }
        return $modules;
    }

    function getMoyenSemstre() {
        $resultModule = array();
        $mg = 0;
        $ss = 0;
        if ($unites != null) {
            foreach ($unites as $unite) {
                $nbCredits = 0;
                $moyenne = 0;
                $i = 0;
                $mg++;
                $ss++;
                $resultModule[$unite['sigle']][] = array();
                $r = array_values($unite);
                foreach ($modules as $module) {

                    $m = array_values($module);

                    if ($m[1] == $r[0]) {

                        foreach ($notes as $note) {
                            $n = array_values($note);

                            if ($n[3] == $m[0]) {
                                $i++;
                                $ncc = $n[0];
                                $nsc = $n[1];
                                $nsr = $n[2];

                                $max = max($nsc, $nsr);
                                $nfe = ((($max * 3) + ($ncc * 2)) / 5);
                                $nb = $n[4];



                                //echo $nbCredits;
                            }
                        }

                        $nbCredits += $m[3];
                        $s = $nfe * $nb;
                        $moyenne += $s;
                    }
                }

                // echo  $resultModule[$unite['sigle']]['nfe'];
                //echo $moyenne;
                //echo $nbCredits;
                $resultModule[$unite['sigle']]['cdt'] = $nbCredits;
                $resultModule[$unite['sigle']]['nfe'] = ($moyenne / $nbCredits);
                $resultModule[$unite['sigle']]['i'] = $i;
                $mg += $resultModule[$unite['sigle']]['nfe'] = ($moyenne / $nbCredits);
            }
        }
       // if ($s == 1)
         //   echo $ss;
        return $moy = ($mg - $ss) / $ss;
    }

    function getCreditValide($matriculeEtudiant) {

        //$data['note']
        $str = 'SELECT s1.semestre as semestre,s1.`credits_val` as crdt,s1.`annee` as annee FROM `credit_valide_sem` s1 WHERE s1.matriculeetudiant=' . $matriculeEtudiant;
        //$str ="SELECT  s.credits_val as crdt from semestre_decision_t s where     s.matriculeEtudiant =".;
        //echo $str;
        $credit_val = array();
        $my = 0;
        $cdtv = 0;
        $i = 0;
        $annee = -1;
        $semestre = 0;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                //$row =  $query->result_array(); 
                $credit_val['crdt'] = $row['crdt'];
                $cdtv += $credit_val['crdt'];
                //pour récupérer la derniére année et semestre d'inscription
                if ($annee <= $row['annee']) {
                    $annee = $row['annee'];
                    $semestre = $row['semestre'];
                }
            }
        }
        //echo print_r($my/6);
        $credit_val = $cdtv;
        // $result = array();
        $result['totalCredit'] = $credit_val;
        $result['annee'] = $annee;
        $result['semestre'] = $semestre;
        print_r($result);
        //print_r($credit_val);
        return $result;
    }

    function getAnne_obt_diplome($matriculeEtudiant) {

        //$data['note']
        $str = "SELECT max(e.annee) as annee from etudiant_sem_note_bis e where   e.matriculeEtudiant =" . $matriculeEtudiant;
        //echo $str;

        $query = $this->db->query($str);
        $Annee_obt_d['annee'] = array();
        foreach ($query->result_array() as $row) {
            //  $moyenne[$row[matriculeEtudiant]]= array();
            //$row =  $query->result_array(); 
            $Annee_obt_d['annee'] = $row['annee'];
        }

        return $Annee_obt_d;
    }

    function getMoyenne($matriculeEtudiant) {

        //$data['note']
        $str = "SELECT e.semestre as semestre,  max(e.note) as moyenne from etudiant_sem_note_bis e where   e.matriculeEtudiant =" . $matriculeEtudiant . " and semestre=1 union SELECT e.semestre as semestre,  max(e.note) as moyenne from etudiant_sem_note_bis e where   e.matriculeEtudiant =" . $matriculeEtudiant . " and semestre=2 union SELECT e.semestre as semestre,  max(e.note) as moyenne from etudiant_sem_note_bis e where   e.matriculeEtudiant =" . $matriculeEtudiant . " and semestre=1 union SELECT e.semestre as semestre,  max(e.note) as moyenne from etudiant_sem_note_bis e where   e.matriculeEtudiant =" . $matriculeEtudiant . " and semestre=3 union SELECT e.semestre as semestre,  max(e.note) as moyenne from etudiant_sem_note_bis e where   e.matriculeEtudiant =" . $matriculeEtudiant . " and semestre=4 union SELECT e.semestre as semestre,  max(e.note) as moyenne from etudiant_sem_note_bis e where   e.matriculeEtudiant =" . $matriculeEtudiant . " and semestre=1 union SELECT e.semestre as semestre,  max(e.note) as moyenne from etudiant_sem_note_bis e where   e.matriculeEtudiant =" . $matriculeEtudiant . " and semestre=5 union SELECT e.semestre as semestre,  max(e.note) as moyenne from etudiant_sem_note_bis e where   e.matriculeEtudiant =" . $matriculeEtudiant . " and semestre=6  ";
        //echo $str;
        $moyenne = array();
        $my = 0;
        $cdtv = 0;
        $i = 0;
        $query = $this->db->query($str);
        $moyenne['moyenne'] = array();
        foreach ($query->result_array() as $row) {
            //  $moyenne[$row[matriculeEtudiant]]= array();
            //$row =  $query->result_array(); 
            $moyenne['moyenne'] = $row['moyenne'];
            $my += $moyenne['moyenne'];
            $i++;
        }

        $moyenne = $my / $i;
    //    echo print_r($moyenne);
        return $moyenne;
    }

    function getAnnee() {

        $anneeU = null;

        $str = "select distinct annee from notespartielles order by annee desc";
        //echo $str;
        // $anneeU=array();
        $anneeU = array();
        $query = $this->db->query($str);
        //$max = $query->result_array()['annee'];
        foreach ($query->result_array() as $row) {
            //if ($row['annee'] != 2019)
                $anneeU[] = $row['annee'];
        }

        //echo  print_r($anneeU);

        return $anneeU;
    }

    function getSemstreInAnne($matriculeEtudiant, $annee) {
        $SemestreAnnee = null;
        //modification 10/10/2018 remplacer la vue niveau par la table passage_t
        $str = 'select distinct n.niveau as semestre,n.ects as etcs,n.annee as annee from passage_t n where   n.matriculeEtudiant=' . $matriculeEtudiant . ' and n.annee<=' . $annee . ' order by 3 DESC';
        //echo $str;

        $res = $this->db->query($str);

        if ($res->num_rows() > 0) {
            $SemestreAnnee = $res->row_array();
        }

        return $SemestreAnnee;
    }

    function get_elements_par_sigle($sigle) {
        $sql = "SELECT * from elementsactifs where sigle='" . $sigle . "'";


        $query = $this->db->query($sql);

        $elementsactifs = array();
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {

                $elementsactifs['sigle'] = $row['sigle'];
                $elementsactifs['idDepartement'] = $row['idDepartement'];
                $elementsactifs['nbCredits'] = $row['nbCredits'];
                $elementsactifs['idCycle'] = $row['idCycle'];
                $elementsactifs['professeurResponsable'] = $row['professeurResponsable'];
                $elementsactifs['sigleunite'] = $row['sigleunite'];
                $elementsactifs['idProgramme'] = $row['idProgramme'];
                $elementsactifs['semestre'] = $row['semestre'];
            }

            return $elementsactifs;
        }
    }

    function get_module_rattrapes_impaire($matriculeEtudiant, $annee) {
        //$data['note']
        $an = $annee;
        $SemstreInAnne = $this->getSemstreInAnne($matriculeEtudiant, $an);
        $sql = '';



        $sql = "SELECT  r.`idModule`,m.sigle as sigle, m.nbCredits as ects, m.titre,u.semestre as semestre FROM unite u, `releve_t_bis` r,module m  where 
 r.sigle=m.sigle and u.sigle=r.idModule and r.matriculeEtudiant = " . $matriculeEtudiant . " and (MOD(r.semestre,2)=1)
 and r.annee =" . ($annee - 1) . "  and r.capit='NC'  union SELECT u.sigle as idModule, e.sigle as sigle ,m.nbCredits as ects, m.titre,u.semestre as semestre  FROM `elements_caches_bis` e,module m,unite u WHERE e.sigle=m.sigle and u.sigle=m.sigleunite and e.matriculeEtudiant = " . $matriculeEtudiant . " and (MOD(u.semestre,2)=1)
 and e.annee =" . ($annee - 1) . " order by 5 asc";


        //echo $sql;
        $crdit = $SemstreInAnne['etcs'];

        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    function get_module_rattrapes_paire($matriculeEtudiant, $annee) {

        //$data['note']
        $an = $annee;
        $sql = '';
        $SemstreInAnne = $this->getSemstreInAnne($matriculeEtudiant, $an);

        $sql = '';
        $sql = "SELECT  r.`idModule`,m.sigle as sigle, m.nbCredits as ects, m.titre,u.semestre as semestre FROM unite u, `releve_t_bis` r,module m  where 
 r.sigle=m.sigle and u.sigle=r.idModule and r.matriculeEtudiant = " . $matriculeEtudiant . " and  (MOD(r.semestre, 2)=0)
 and r.annee =" . $annee . "  and r.capit='NC' union SELECT u.sigle as idModule, e.sigle as sigle ,m.nbCredits as ects, m.titre,u.semestre as semestre  FROM `elements_caches_bis` e,module m,unite u WHERE e.sigle=m.sigle and u.sigle=m.sigleunite and e.matriculeEtudiant = " . $matriculeEtudiant . " and (MOD(u.semestre,2)=0)
 and e.annee =" . $annee . " order by 5 asc";
       // echo $sql;

        $crdit = $SemstreInAnne['etcs'];


        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    function get_module_paire_a_etudies($idProgramme, $semestre, $matriculeEtudiant, $an) {
        $an = $_POST['annee'];
        ;
        $idProgramme = $this->get_programme_E($matriculeEtudiant);
        $SemstreInAnne = $this->getSemstreInAnne($matriculeEtudiant, $an);
        if ($semestre != -1) { // n'est pas un nouveau etudiant
            if ($SemstreInAnne['semestre'] == 1) {
                $semestre = 2;
            } elseif ($SemstreInAnne['semestre'] == 2) {
                $semestre = 4;
            } elseif ($SemstreInAnne['semestre'] == 3) {
                $semestre = 6;
            } elseif ($SemstreInAnne['semestre'] == 4) {
                echo'L\'étudiant a deja son licence';
            }
            $crdit = $SemstreInAnne['etcs'];
        } else { // nouveau inscrit
            $semestre = 2;
            $crdit = 0;
        }


        //$semestre=1;
        $sql = '';
        $sql = 'SELECT u.`sigle` as idModule,m.`sigle` as sigle, m.nbCredits as ects, m.titre FROM unite u,programme p,module m  where 
 u.sigle=m.sigleunite  and p.idProgramme = "' . $idProgramme . '" and u.semestre =' .
                $semestre . ' and p.idProgramme=u.idProgramme and semestreActivation<=' . $an . '3 and (semestreDesactivation>' . $an . '3 or semestreDesactivation is null or semestreDesactivation="") order by 1,2';
        // echo $sql;



        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    function get_module_impaire_a_etudies($idProgramme, $semestre, $matriculeEtudiant, $annee) {
        //$data['note']
        $an = $_POST['annee'];
        $idProgramme = $this->get_programme_E($matriculeEtudiant);
        $SemstreInAnne = $this->getSemstreInAnne($matriculeEtudiant, $an);
        if ($semestre != -1) { // n'est pas un nouveau etudiant
            if ($SemstreInAnne['semestre'] == 1) {
                $semestre = 1;
            } elseif ($SemstreInAnne['semestre'] == 2) {
                $semestre = 3;
            } elseif ($SemstreInAnne['semestre'] == 3) {
                $semestre = 5;
            } elseif ($SemstreInAnne['semestre'] == 4) {
                echo'L\'étudiant a deja son licence';
            }
            $crdit = $SemstreInAnne['etcs'];
        } else { // nouveau inscrit
            $semestre = 1;
            $crdit = 0;
        }






        //$semestre=1;
        $sql = '';
        $sql = 'SELECT u.`sigle` as idModule,m.`sigle` as sigle, m.nbCredits as ects, m.titre FROM unite u,programme p,module m  where 
 u.sigle=m.sigleunite  and p.idProgramme = "' . $idProgramme . '" and u.semestre =' .
                $semestre . ' and p.idProgramme=u.idProgramme and semestreActivation<=' . $an . '3 and (semestreDesactivation>' . $an . '3 or semestreDesactivation is null or semestreDesactivation="")order by 1,2';
        // echo $sql;



        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    function get_programme_E($matriculeEtudiant) {
        $str = 'SELECT d.`idProgramme` as idProgramme FROM dossieretudiant d  where 
 d.matriculeEtudiant =' .
                $matriculeEtudiant;
        //echo $str;


        $query = $this->db->query($str);

        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return $row['idProgramme'];
        }
    }

    function get_infoEleve($annee, $num_bac) {
        $sql = '';

        $sql = 'SELECT * from  autorisation_e e where e.anneeAutorisation=' . $annee . ' and e.num_bac = ' . $num_bac;
        //  echo $sql;
        $res = $this->db->query($sql);
        $info = null;
        if ($res->num_rows() > 0) {
            $info = $res->row_array();
        }
        //print_r($info);
        return $info;
    }

    function get_infoRecu($matriculeEtudiant) {
        $sql = '';
        $infoRecu = null;
        $sql = 'SELECT * from  recu_inscription r where r.matriculeEtudiant = ' . $matriculeEtudiant;
        $res = $this->db->query($sql);

        if ($res->num_rows() > 0) {
            $infoRecu = $res->row_array();
        }

        return $infoRecu;
    }

    function modifier_autoriser_etudiant($num_bac, $data, $matriculeE, $idProgramme) {
        $this->db->where('num_bac', $num_bac);
        $this->db->update('autorisation_e', $data);

        $this->db->where('matriculeEtudiant', $matriculeE);
        $this->db->set(array('idProgramme' => $idProgramme));
        $this->db->update('etuprog');

        $this->db->where('matriculeEtudiant', $matriculeE);
        $this->db->set(array('idProgramme' => $idProgramme));
        $this->db->update('dossieretudiant');
    }

    function get_matricule_Et($num_bac, $annee) {
        $sql = '';

        $sql = 'SELECT e.matriculeEtudiant  from  etudiant e ,etudesanterieures n WHERE n.num_bac=' . $num_bac . ' and n.anneeObtention=' . $annee . ' and e.infobac=n.idinfobac';
        $matriculeE = null;


        $res = $this->db->query($sql);

        if ($res->num_rows() > 0) {
            $matriculeE = $res->row_array();
        }

        return $matriculeE;
    }

    function get_redoublant_Et($matriculeEtudiant, $annee) {
        $sql = '';

// si probleme faie annee-1
        $sql = 'SELECT * FROM `redoublant` where `matriculeEtudiant`=' . $matriculeEtudiant . ' and annee=' . ($annee);
        $redoublant = null;
       // echo $sql;

        $res = $this->db->query($sql);

        if ($res->num_rows() > 0) {
            $redoublant = $res->row_array();
        }

        return $redoublant;
    }

//trouver le niveu d'inscription de l'etudiant au premier semestre de l'annee passée en paramètre
    function get_Niveau_Inscrit($matriculeEtudiant, $annee) {

        $sql = 'SELECT max(niveau) as niveau FROM `niveau_inscrits` WHERE `matriculeetudiant`=' . $matriculeEtudiant . ' and ((annee=' . $annee . ' and semestre=3) or (annee= ' . ($annee + 1) . ' and semestre=1))';

        //echo $sql;
        $res = $this->db->query($sql);
        $semestre = 0;

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $semestre = $row['niveau'];
            }
        }

        return ($semestre * 2 - 1);
    }

//les etudiants d'une filere pour une annee (semestres impairs)-----algo pour placement des etudiant
    function etudant_filiere($annee) {
        $result = null;
        $arr = array('RXTEL', 'MAN', 'MAEF', 'LGTR');
        foreach ($arr as $programme) {
            //  *****nouveaus inscrits+redoublants
            $sql1 = '';
            $sql1 = 'select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,modules_non_valides_t_bis m,niveau n where d.matriculeEtudiant=m.matriculeEtudiant and d.matriculeEtudiant=n.matriculeEtudiant and m.semestre=1 and d.idProgramme="' . $programme . '" and m.annee=' . ($annee - 1) . ''
                    . '  union select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d  where   d.idProgramme="' . $programme . '"  and d.matriculeEtudiant like "16%"';
            //print_r($sql1);

            $query = $this->db->query($sql1);
            $etud = $query->result();
            $result['nouv&redoubl_s1'][] = $etud;
            $result['nombreEtud_nouv&redoubl_s1'][] = count($etud);
            //  print_r($result['nombreEtud_nouv&redoubl_s1']);
            //  *****s3(rien a rattrape en s1)
            $sql2 = '';
            $sql2 = 'select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,niveau n where 
                    d.matriculeEtudiant=n.matriculeEtudiant and n.niveau=2 and d.idProgramme="' . $programme . '" and n.annee=' . ($annee - 1) . '  and d.matriculeEtudiant not in(select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,modules_non_valides_t_bis m,niveau n where d.matriculeEtudiant=m.matriculeEtudiant and d.matriculeEtudiant=n.matriculeEtudiant and m.semestre=1 and d.idProgramme="' . $programme . '" and m.annee=' . ($annee - 1) . ')';
            // echo $sql2;
            //print_r($sql2);
            $query = $this->db->query($sql2);
            $etud = $query->result();
            $result['s3_no_ratt_s1'][] = $etud;
            $result['nombreEtud_no_ratt_s1'][] = count($etud);


            //  *****s3( a rattrape en s1)
            $sql3 = 'select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,niveau n where 
                    d.matriculeEtudiant=n.matriculeEtudiant and n.niveau=2 and d.idProgramme="' . $programme . '" and n.annee=' . ($annee - 1) . '  and d.matriculeEtudiant  in(select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,modules_non_valides_t_bis m,niveau n where d.matriculeEtudiant=m.matriculeEtudiant and d.matriculeEtudiant=n.matriculeEtudiant and m.semestre=1 and d.idProgramme="' . $programme . '" and m.annee=' . ($annee - 1) . ')';
            //  echo $sql3;

            $query = $this->db->query($sql3);
            $etud = $query->result();
            $result['s3_ratt_s1'][] = $etud;
            $result['nombreEtud_s3_ratt_s1'][] = count($etud);

//*****s5(rien a rattrape en s1 et pas en s3)
            $sql4 = '';
            $sql4 = 'select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,niveau n where 
                    d.matriculeEtudiant=n.matriculeEtudiant and n.niveau=3 and d.idProgramme="' . $programme . '" and n.annee=' . ($annee - 1) . '  and d.matriculeEtudiant not in('
                    . 'select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,modules_non_valides_t_bis m,niveau n where d.matriculeEtudiant=m.matriculeEtudiant and d.matriculeEtudiant=n.matriculeEtudiant and m.semestre=1 and d.idProgramme="' . $programme . '" and m.annee=' . ($annee - 1) . ' union select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,modules_non_valides_t_bis m,niveau n where d.matriculeEtudiant=m.matriculeEtudiant and d.matriculeEtudiant=n.matriculeEtudiant and m.semestre=3 and d.idProgramme="' . $programme . '" and m.annee=' . ($annee - 1) . ')';
            // echo $sql4;

            $query = $this->db->query($sql4);
            $etud = $query->result();
            $result['s5_no_ratt'][] = $etud;
            $result['nombreEtud_s5_no_ratt'][] = count($etud);

//*****s5(rattrape en s1 et s3 )
            $sql5 = '';
            $sql5 = 'select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,niveau n where 
                    d.matriculeEtudiant=n.matriculeEtudiant and n.niveau=3 and d.idProgramme="' . $programme . '" and n.annee=' . ($annee - 1) . '  and d.matriculeEtudiant  in('
                    . 'select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,modules_non_valides_t_bis m,niveau n where d.matriculeEtudiant=m.matriculeEtudiant and d.matriculeEtudiant=n.matriculeEtudiant and m.semestre=1 and d.idProgramme="' . $programme . '" and m.annee=' . ($annee - 1) . ' union select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,modules_non_valides_t_bis m,niveau n where d.matriculeEtudiant=m.matriculeEtudiant and d.matriculeEtudiant=n.matriculeEtudiant and m.semestre=3 and d.idProgramme="' . $programme . '" and m.annee=' . ($annee - 1) . ')';
            // echo $sql5;

            $query = $this->db->query($sql5);
            $etud = $query->result();
            $result['s5_ratt_s1&s3'][] = $etud;
            $result['nombreEtud_s5_ratt_s1&s3'][] = count($etud);

//*****s5(rattrape en s1 et pas  s3 )
            $sql6 = '';
            $sql6 = 'select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,niveau n where 
                    d.matriculeEtudiant=n.matriculeEtudiant and n.niveau=3 and d.idProgramme="' . $programme . '" and n.annee=' . ($annee - 1) . '  and d.matriculeEtudiant  in('
                    . 'select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,modules_non_valides_t_bis m,niveau n where d.matriculeEtudiant=m.matriculeEtudiant and d.matriculeEtudiant=n.matriculeEtudiant and m.semestre=1 and d.idProgramme="' . $programme . '" and m.annee=' . ($annee - 1) . ') and d.matriculeEtudiant  not in( select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,modules_non_valides_t_bis m,niveau n where d.matriculeEtudiant=m.matriculeEtudiant and d.matriculeEtudiant=n.matriculeEtudiant and m.semestre=3 and d.idProgramme="' . $programme . '" and m.annee=' . ($annee - 1) . ')';
            //  echo $sql6;

            $query = $this->db->query($sql6);
            $etud = $query->result();
            $result['s5_ratt_s1&_no_s3'][] = $etud;
            $result['nombreEtud_s5_ratt_s1&_no_s3'][] = count($etud);

//*****s5(rattrape en s3 et pas  s1 )
            $sql7 = '';
            $sql7 = 'select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,niveau n where 
                    d.matriculeEtudiant=n.matriculeEtudiant and n.niveau=3 and d.idProgramme="' . $programme . '" and n.annee=' . ($annee - 1) . '  and d.matriculeEtudiant  in('
                    . 'select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,modules_non_valides_t_bis m,niveau n where d.matriculeEtudiant=m.matriculeEtudiant and d.matriculeEtudiant=n.matriculeEtudiant and m.semestre=3 and d.idProgramme="' . $programme . '" and m.annee=' . ($annee - 1) . ') and d.matriculeEtudiant  not in( select d.matriculeEtudiant as matriculeEtudiant from dossieretudiant d ,modules_non_valides_t_bis m,niveau n where d.matriculeEtudiant=m.matriculeEtudiant and d.matriculeEtudiant=n.matriculeEtudiant and m.semestre=1 and d.idProgramme="' . $programme . '" and m.annee=' . ($annee - 1) . ')';
            //  echo $sql7;


            $query = $this->db->query($sql7);
            $query = $this->db->query($sql7);
            $etud = $query->result();
            $result['s5_ratt_s3&_no_s1'][] = $etud;
            $result['nombreEtud_s5_ratt_s3&_no_s1'][] = count($etud);
        }


        return $result;
    }

    function getSemestreResult_bis_historique($matriculeEtudiant, $semestre) {

        $data = null;

        $str = "select s.decision, s.credits_val,e.note from semestre_decision_t_bis s,  etudiant_sem_note_t_bis e where s.semestre = e.semestre and s.annee = e.annee and s.matriculeEtudiant=e.matriculeEtudiant  and s.matriculeEtudiant =  " .
                $matriculeEtudiant . " and s.semestre = " . $semestre;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $data['matriculeEtudiant'] = $matriculeEtudiant;
            $data['semestre'] = $semestre;
            $data['ects'] = $row['credits_val'];
            $data['decision'] = $row['decision'];
            if (substr($data['decision'], 0, 3) == 'Ajo') {
                $data['validation'] = 'NV';
            } else {
                $data['validation'] = 'V';
            }
            $data['note'] = $row['note'];
            $data['inscrit'] = 'OK';
        } else {
            $data['inscrit'] = 'NO';
        }
        // echo 'getsemestreresult_bis'.$matriculeEtudiant;
        return $data;
    }

    function getModulesDecision_bis_historique($module, $matriculeEtudiant, $semestre, $annee) {
        $str = "SELECT   m.decision as decision, m.credits_val as ects, u.titre as titre, e.note as note from modules_decision_t_bis m, unite u, etudiant_mod_note_bis e where m.idModule=u.sigle and m.idModule = e.idModule and m.matriculeEtudiant=e.matriculeEtudiant and m.annee=e.annee and m.semestre=e.semestre  and m.matriculeEtudiant =  " .
                $matriculeEtudiant . " and m.idModule = '" . $module . "' and m.semestre = '. $semestre.' and m.annee = " . $annee;
        //echo $str;
        $moduleDec = array();
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {

            $row = $query->result_array();
            //  echo  print_r($row);
            $moduleDec['titre'] = $row[0]['titre'];
            $moduleDec['decision'] = $row[0]['decision'];

            $moduleDec['ects'] = $row[0]['ects'];
            $moduleDec['note'] = $row[0]['note'];
        }
        if ($query->num_rows() == 0) {
            $str1 = "SELECT   m.decision as decision, m.credits_val as ects, u.titre as titre, e.note as note from modules_decision_t_bis m, unite u, etudiant_mod_note_bis e where m.idModule=u.sigle and m.idModule = e.idModule and m.matriculeEtudiant=e.matriculeEtudiant and m.annee=e.annee and m.semestre=e.semestre  and m.matriculeEtudiant =  " .
                    $matriculeEtudiant . " and m.idModule = '" . $module . "' and m.semestre = '. $semestre.' and m.annee = " . $annee - 1;
            //echo $str;
            $moduleDec = array();
            $query = $this->db->query($str1);

            $row = $query->result_array();
            //  echo  print_r($row);
            $moduleDec['titre'] = $row[0]['titre'];
            $moduleDec['decision'] = $row[0]['decision'];

            $moduleDec['ects'] = $row[0]['ects'];
            $moduleDec['note'] = $row[0]['note'];
        }
        //  echo 'getModulesDecision_bis'.$matriculeEtudiant;
        return $moduleDec;
    }

    function getModulesResult_bis_historique($matriculeEtudiant, $semestre) {

        //$data['note']
        $str = null;
        if ($semestre % 2 == 1) {
            $str = "SELECT  r.`idModule`, r.`sigle`, r.`note`, r.`capit`,r.ects, m.titre, ifNull(n.notecc,0) as notecc,ifNull(n.noteExam,0) as noteExam, ifNull(n.noteRT,0) as noteRT  FROM `releve_t_bis` r, 
notes_globales_dern_t_bis n,module m where r.`matriculeEtudiant` = n.`matriculeEtudiant` and r.`sigle` = n.`sigle` and
r.`semestre` = n.`semestre` and r.annee=(2000+floor(n.annee/100)) and r.matriculeEtudiant = " . $matriculeEtudiant . " and r.semestre =" .
                    $semestre . "  and r.sigle = m.sigle order by 1,2";
        } else {
            $str = "SELECT  r.`idModule`, r.`sigle`, r.`note`, r.`capit`,r.ects, m.titre, ifNull(n.notecc,0) as notecc,ifNull(n.noteExam,0) as noteExam, ifNull(n.noteRT,0) as noteRT  FROM `releve_t_bis` r, 
notes_globales_dern_t_bis n,module m where r.`matriculeEtudiant` = n.`matriculeEtudiant` and r.`sigle` = n.`sigle` and
r.`semestre` = n.`semestre` and r.annee=(2001+floor(n.annee/100)) and r.matriculeEtudiant = " . $matriculeEtudiant . " and r.semestre =" .
                    $semestre . "   and r.sigle = m.sigle order by 1,2";
        }

        //  echo $str;
        $modules = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            // $i=0;
            foreach ($query->result_array() as $row) {

                $moduleDec = NULL;
                if ($modules == NULL) {

                    $modules = array();
                    $moduleDec = $this->getModulesDecision_bis_historique($row['idModule'], $matriculeEtudiant, $semestre, $annee + 1);
                    if ($moduleDec != NULL) {
                        $modules[$row['idModule']] = array();
                        $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                        $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                        $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                        $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    }
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                } else if (!array_key_exists($row['idModule'], $modules)) {

                    $moduleDec = $this->getModulesDecision_bis_historique($row['idModule'], $matriculeEtudiant, $semestre, $annee + 1);
                    if ($moduleDec != NULL) {
                        $modules[$row['idModule']] = array();
                        $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                        $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                        $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                        $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    }
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                }
                //if($modules[$row['idModule']]['elements']!=NULL){
                $modules[$row['idModule']]['elements'][$row['sigle']] = array();
                $modules[$row['idModule']]['elements'][$row['sigle']]['nfe'] = $row['note'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['capit'] = $row['capit'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ncc'] = $row['notecc'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsn'] = $row['noteExam'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsr'] = $row['noteRT'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ects'] = $row['ects'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['titre'] = $row['titre'];
                ;
                $modules[$row['idModule']]['nb'] ++;
                // }
            }
        }
        //echo print_r($modules);
        // echo 'getModulesResult_bis'.$matriculeEtudiant;
        return $modules;
    }

    function get_diplome($matriculeE) {
        $sql = 'select * from diplome where No_Dip=' . $matriculeE;
        //  echo $sql;
        //$query = $this->db->query($sql);
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    function get_all_infoCarte($idProgramme, $annee, $debut, $fin)//modified My MedBakar 11-06-2020
    {
        $anne = $annee + 1;
        $condition='';
        for($i=0;$i<count($idProgramme);$i++){
            $condition.="( d.idProgramme like'" . $idProgramme[$i] . "') AND ";
        }
        $sql1 = "SELECT distinct p.`matriculeetudiant`, 'L3' as niveau,e.nom nomE,e.prenom,e.prenomPere_fr ,e.dateNaissance,e.lieuNaissance,f.nom,i.num_bac,i.infobac,"
                . "e.nationalite,d.idProgramme from planetudes p,etudiant e, dossieretudiant d,programme f,etudesanterieures i,module m,unite u where "
                . "p.matriculeetudiant=e.matriculeetudiant and p.matriculeetudiant=d.matriculeetudiant and e.infobac=i.idinfobac and d.matriculeetudiant>=$debut and"
                . " d.matriculeetudiant<=$fin and d.idProgramme=f.idProgramme  and $condition ((p.annee=$annee and p.semestre=3 and p.sigle=m.sigle and m.sigleUnite=u.sigle"
                . " and u.semestre=5) or (p.annee=$annee+1 and p.semestre=1 and p.sigle=m.sigle and m.sigleUnite=u.sigle and u.semestre=6)) 
                union 
                SELECT distinct p1.`matriculeetudiant`, 'L2' as niveau,e.nom nomE,e.prenom,e.prenomPere_fr,e.dateNaissance,e.lieuNaissance,f.nom,i.num_bac,i.infobac, e.nationalite,
                d.idProgramme from planetudes p1,etudiant e, dossieretudiant d,programme f,etudesanterieures i,module m1,unite u1 where p1.matriculeetudiant=e.matriculeetudiant 
                and p1.matriculeetudiant=d.matriculeetudiant and d.matriculeetudiant>=$debut and d.matriculeetudiant<=$fin and d.idProgramme=f.idProgramme and e.infobac=i.idinfobac"
                . " and $condition ((p1.annee=$annee and p1.semestre=3 and p1.sigle =m1.sigle and m1.sigleUnite=u1.sigle and u1.semestre=3) or (p1.annee=$annee+1 and p1.semestre=1 "
                . "and p1.sigle=m1.sigle and m1.sigleUnite=u1.sigle and u1.semestre=4)) and not exists ( select p2.matriculeetudiant from planetudes p2,module m2,unite u2 "
                . "where p2.matriculeetudiant=p1.matriculeetudiant and ((p2.annee=$annee and p2.semestre=3 and p2.sigle=m2.sigle and m2.sigleUnite=u2.sigle and u2.semestre=5)"
                . " or (p2.annee=$annee+1 and p2.semestre=1 and p2.sigle=m2.sigle and m2.sigleUnite=u2.sigle and u2.semestre=6))) 
                union 
                SELECT distinct p1.`matriculeetudiant`, 'L1' as niveau,e.nom nomE,e.prenom,e.prenomPere_fr,e.dateNaissance,e.lieuNaissance,f.nom,i.num_bac,i.infobac, e.nationalite,d.idProgramme 
                    from planetudes p1 ,etudiant e, dossieretudiant d,programme f,etudesanterieures i,module m1,unite u1 where p1.matriculeetudiant=e.matriculeetudiant and p1.matriculeetudiant=d.matriculeetudiant 
                    and d.idProgramme=f.idProgramme and e.infobac=i.idinfobac and d.matriculeetudiant>=$debut and d.matriculeetudiant<=$fin and $condition ((p1.annee=$annee and p1.semestre=3 and p1.sigle=m1.sigle "
                . "and m1.sigleUnite=u1.sigle and u1.semestre=1) or (p1.annee=$annee+1 and p1.semestre=1 and p1.sigle=m1.sigle and m1.sigleUnite=u1.sigle and u1.semestre=2)) "
                . "and not exists ( select p2.matriculeetudiant from planetudes p2,module m2,unite u2 where p2.matriculeetudiant=p1.matriculeetudiant and ((p2.annee=$annee and "
                . "p2.semestre=3 and ((p2.sigle=m2.sigle and m2.sigleUnite=u2.sigle and u2.semestre=5) or (p2.sigle=m2.sigle and m2.sigleUnite=u2.sigle and u2.semestre=3))) or (p2.annee=$annee+1 and p2.semestre=1 and "
                . "((p2.sigle=m2.sigle and m2.sigleUnite=u2.sigle and u2.semestre=6) or( p2.sigle=m2.sigle and m2.sigleUnite=u2.sigle and u2.semestre=4)))))";
//        echo"<br>$sql1<br>";
        /*$sql1 = "SELECT distinct p.`matriculeetudiant`, 'L3' as niveau,e.nom nomE,e.prenom,e.prenomPere_fr ,e.dateNaissance,e.lieuNaissance,f.nom,i.num_bac,i.infobac,e.nationalite,d.idProgramme from planetudes p,etudiant e, dossieretudiant d,programme f,etudesanterieures i where p.matriculeetudiant=e.matriculeetudiant and p.matriculeetudiant=d.matriculeetudiant and e.infobac=i.idinfobac and d.matriculeetudiant>=" . $debut . " and d.matriculeetudiant<=" . $fin . " and d.idProgramme=f.idProgramme and ( d.idProgramme like'" . $LGTR . "' or d.idProgramme='" . $RXTL . "' or d.idProgramme like'" . $MAN . "' or d.idProgramme like'" . $MAEF . "') and ((p.annee=" . $annee . " and p.semestre=3 and p.sigle like '___5%') or (p.annee=" . $anne . " and p.semestre=1 and p.sigle like '___6%'))

                    union
                    SELECT distinct p1.`matriculeetudiant`, 'L2' as niveau,e.nom nomE,e.prenom,e.prenomPere_fr,e.dateNaissance,e.lieuNaissance,f.nom,i.num_bac,i.infobac,e.nationalite,d.idProgramme from planetudes p1,etudiant e, dossieretudiant d,programme f,etudesanterieures i where
                    p1.matriculeetudiant=e.matriculeetudiant and p1.matriculeetudiant=d.matriculeetudiant and d.matriculeetudiant>=" . $debut . " and d.matriculeetudiant<=" . $fin . "  and d.idProgramme=f.idProgramme  and e.infobac=i.idinfobac and (d.idProgramme like'" . $LGTR . "' or d.idProgramme like'" . $RXTL . "' or d.idProgramme like'" . $MAN . "' or d.idProgramme='" . $MAEF . "')   and ((p1.annee=" . $annee . " and p1.semestre=3 and p1.sigle like '___3%') or (p1.annee=" . ($annee + 1) . " and p1.semestre=1 and p1.sigle like '___4%')) and not exists ( select p2.matriculeetudiant from planetudes p2 where p2.matriculeetudiant=p1.matriculeetudiant and ((p2.annee=" . $annee . " and p2.semestre=3 and p2.sigle like '___5%') or (p2.annee=" . $anne . " and p2.semestre=1 and p2.sigle like '___6%')))

                    union
                    SELECT distinct p1.`matriculeetudiant`, 'L1' as niveau,e.nom nomE,e.prenom,e.prenomPere_fr,e.dateNaissance,e.lieuNaissance,f.nom,i.num_bac,i.infobac,e.nationalite,d.idProgramme from  planetudes p1 ,etudiant e, dossieretudiant d,programme f,etudesanterieures i where p1.matriculeetudiant=e.matriculeetudiant and p1.matriculeetudiant=d.matriculeetudiant and d.idProgramme=f.idProgramme  and e.infobac=i.idinfobac and  d.matriculeetudiant>=" . $debut . " and d.matriculeetudiant<=" . $fin . "  and (d.idProgramme like'" . $LGTR . "' or d.idProgramme like'" . $RXTL . "' or d.idProgramme='" . $MAN . "' or d.idProgramme like'" . $MAEF . "')  and ((p1.annee=" . $annee . " and p1.semestre=3 and p1.sigle like '___1%') or (p1.annee=" . $anne . " and p1.semestre=1 and p1.sigle like '___2%')) and not exists ( select p2.matriculeetudiant from planetudes p2 where p2.matriculeetudiant=p1.matriculeetudiant and ((p2.annee=" . $annee . " and p2.semestre=3 and (p2.sigle like '___5%' or p2.sigle like '___3%')) or (p2.annee=" . $anne . " and p2.semestre=1 and (p2.sigle like '___6%' or p2.sigle like '___4%'))))";
       */ // echo $sql1;
        
        $query = $this->db->query($sql1);
        $result = $query->result();
        // print_r($result);
        return $result;
    }

    function get_etudiants_isncrits135($annee) {
        // $anne=$annee+1;

        $sql1 = '';
        $sql1 = "SELECT * from niveau_ins2016Bis";
        /*  $sql1 ="SELECT distinct p.`matriculeetudiant`, '3' as niveau,d.idProgramme from planetudes p, dossieretudiant d where  p.matriculeetudiant=d.matriculeetudiant  and ((p.annee=".$annee." and p.semestre=3 and p.sigle like '___5%') )

          union
          SELECT distinct p1.`matriculeetudiant`, '2' as niveau,d.idProgramme from planetudes p1, dossieretudiant d where
          p1.matriculeetudiant=d.matriculeetudiant and  ((p1.annee=".$annee." and p1.semestre=3 and p1.sigle like '___3%') ) and not exists ( select p2.matriculeetudiant from planetudes p2 where p2.matriculeetudiant=p1.matriculeetudiant and ((p2.annee=".$annee." and p2.semestre=3 and p2.sigle like '___5%')))

          union
          SELECT distinct p1.`matriculeetudiant`, '1' as niveau,d.idProgramme from  planetudes p1, dossieretudiant d where  p1.matriculeetudiant=d.matriculeetudiant and ((p1.annee=".$annee." and p1.semestre=3 and p1.sigle like '___1%') ) and not exists ( select p2.matriculeetudiant from planetudes p2 where p2.matriculeetudiant=p1.matriculeetudiant and ((p2.annee=".$annee." and p2.semestre=3 and (p2.sigle like '___5%' or p2.sigle like '___3%'))))";
         */
        //echo $sql1;
        $result = array();

        $query = $this->db->query($sql1);
        if ($query->num_rows() > 0) {
            // $i=0;
            $result['RXTEL'] = array();
            $result['RXTEL']['s5'] = null;
            $result['RXTEL']['s1'] = null;
            $result['RXTEL']['s3'] = null;

            $result['LGTR'] = array();
            $result['LGTR']['s5'] = null;
            $result['LGTR']['s1'] = null;
            $result['LGTR']['s3'] = null;


            $result['MAN'] = array();
            $result['MAN']['s5'] = null;
            $result['MAN']['s1'] = null;
            $result['MAN']['s3'] = null;


            $result['MAEF'] = array();
            $result['MAEF']['s5'] = null;
            $result['MAEF']['s1'] = null;
            $result['MAEF']['s3'] = null;

            foreach ($query->result_array() as $row) {
                if ($row['idProgramme'] == 'RXTEL') {
                    if ($row['niveau'] == 3) {
                        $result['RXTEL']['s5'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 2) {
                        $result['RXTEL']['s3'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 1) {
                        $result['RXTEL']['s1'][] = $row['matriculeetudiant'];
                    }
                } else if ($row['idProgramme'] == 'MAN') {
                    if ($row['niveau'] == 3) {
                        $result['MAN']['s5'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 2) {
                        $result['MAN']['s3'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 1) {
                        $result['MAN']['s1'][] = $row['matriculeetudiant'];
                    }
                } else if ($row['idProgramme'] == 'MAEF') {
                    if ($row['niveau'] == 3) {
                        $result['MAEF']['s5'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 2) {
                        $result['MAEF']['s3'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 1) {
                        $result['MAEF']['s1'][] = $row['matriculeetudiant'];
                    }
                } else if ($row['idProgramme'] == 'LGTR') {
                    if ($row['niveau'] == 3) {
                        $result['LGTR']['s5'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 2) {
                        $result['LGTR']['s3'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 1) {
                        $result['LGTR']['s1'][] = $row['matriculeetudiant'];
                    }
                }
            }
        }
        //print_r($result);
        return $result;
    }

    function get_all_infotick() {
        $sql1 = '';
        $sql1 = "SELECT distinct n.num_exam,n.`matriculeetudiant`,l.niveau,l.idProgramme,e.nom,e.prenom from etudiant e,numero_exam n,niveau_ins2016bis_pair l where
n.matriculeetudiant=e.matriculeetudiant and n.matriculeetudiant=l.matriculeetudiant order by 1 ";
        // echo $sql1;

        $result = array();

        $query = $this->db->query($sql1);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $data = array(
                    'matriculeEtudiant' => $row['matriculeetudiant'],
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'niveau' => $row['niveau'],
                    'idProgramme' => $row['idProgramme']);
                $result[$row['num_exam']][] = $data;
            }
        }
        return $result;
    }

    function get_max_fin() {
        $sql1 = '';
        $sql1 = "SELECT max(num_exam) as max_num from numero_exam ";
        // echo $sql1;

        $query = $this->db->query($sql1);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $result = $row['max_num'];
            }
        }

        //print_r($result);
        return $result;
    }

    function get_all_infoSalle() {
        $sql1 = '';
        $sql1 = "SELECT * FROM salles";
        //echo $sql1;

        $query = $this->db->query($sql1);
        $result = $query->result();
        //print_r($result);
        return $result;
    }

    function get_all_elements_nc($semestre, $annee, $programme, $idEvaluation) {
        $sql1 = '';
        $sql1 = "  SELECT nt.matriculeEtudiant,nt.sigle,nt.note as note,nt.idEvaluation,u.semestre,nt.annee FROM notespartielles nt,noncap_bis n,unite u WHERE nt.sigle=n.sigle and nt.`matriculeEtudiant`=n.`matriculeEtudiant`  and n.`note`>=7 and nt.`annee`=" . ($annee - 1) . "  and n.annee=" . ($annee - 1) . " and u.sigle=n.idModule and u.semestre=" . $semestre . " and nt.idEvaluation=" . $idEvaluation . " and u.idProgramme='" . $programme . "' union (SELECT nt.matriculeEtudiant,nt.sigle,nt.note as note,nt.idEvaluation,u.semestre,nt.annee FROM notespartielles nt,noncap_bis n,unite u WHERE nt.sigle=n.sigle and nt.`matriculeEtudiant`=n.`matriculeEtudiant`  and n.`note`>=7 and nt.`annee`=" . ($annee - 2) . "  and n.annee=" . ($annee - 2) . " and u.sigle=n.idModule and u.semestre=" . $semestre . " and nt.idEvaluation=" . $idEvaluation . " and u.idProgramme='" . $programme . "' and "
                . " (nt.matriculeEtudiant,nt.sigle,nt.note,nt.idEvaluation,u.semestre ) not in (SELECT nt.matriculeEtudiant,nt.sigle,nt.note as note,nt.idEvaluation,u.semestre FROM notespartielles nt,noncap_bis n,unite u WHERE nt.sigle=n.sigle and nt.`matriculeEtudiant`=n.`matriculeEtudiant`  and n.`note`>=7 and nt.`annee`=" . ($annee - 1) . "  and n.annee=" . ($annee - 1) . " and u.sigle=n.idModule and u.semestre=" . $semestre . " and nt.idEvaluation=" . $idEvaluation . " and u.idProgramme='" . $programme . "')) union (SELECT nt.matriculeEtudiant,nt.sigle,nt.note as note,nt.idEvaluation,u.semestre,nt.annee FROM notespartielles nt,noncap_bis n,unite u WHERE nt.sigle=n.sigle and nt.`matriculeEtudiant`=n.`matriculeEtudiant`  and n.`note`>=7 and nt.`annee`=" . ($annee - 3) . "  and n.annee=" . ($annee - 3) . " and u.sigle=n.idModule and u.semestre=" . $semestre . " and nt.idEvaluation=" . $idEvaluation . " and u.idProgramme='" . $programme . "' and "
                . " (nt.matriculeEtudiant,nt.sigle,nt.note,nt.idEvaluation,u.semestre ) not in (SELECT nt.matriculeEtudiant,nt.sigle,nt.note as note,nt.idEvaluation,u.semestre FROM notespartielles nt,noncap_bis n,unite u WHERE nt.sigle=n.sigle and nt.`matriculeEtudiant`=n.`matriculeEtudiant`  and n.`note`>=7 and nt.`annee`=" . ($annee - 1) . "  and n.annee=" . ($annee - 1) . " and u.sigle=n.idModule and u.semestre=" . $semestre . " and nt.idEvaluation=" . $idEvaluation . " and u.idProgramme='" . $programme . "')) ";

        //echo $sql1;
        $result = array();

        $query = $this->db->query($sql1);
        if ($query->num_rows($query) > 0) {
            foreach ($query->result_array() as $row) {
                $semestre = 3;
                if (($row['semestre'] % 2 == 0)) {
                    $semestre = 1;
                }

                // $sql1='';
                //$sql2 ="SELECT n.matriculeEtudiant,u.sigle as idModule,n.sigle,n.note,u.semestre FROM `notespartielles` n,unite u,module m,evaluation e WHERE m.sigle=n.sigle and m.sigleunite=u.sigle and n.`annee`=".($annee) ." and e.idEvaluation=n.idEvaluation and n.idEvaluation='".$idEvaluation."' and n.sigle='".$row['sigle']."' and n.matriculeEtudiant=".$row['matriculeEtudiant']." and u.semestre=".$row['semestre']." and u.idProgramme='".$programme."'";
                // $result=array();
                //    $query = $this->db->query($sql2);
                if ($query->num_rows() > 0) {/*
                  foreach ($query->result_array() as $row2)
                  {


                  if($row2['note']!=-1){ */
                    $str3 = "update  notespartielles set note = " . $row['note'] . " where idEvaluation='" . $idEvaluation . "' and annee =" . $annee . " and matriculeEtudiant =" . $row['matriculeEtudiant'] . " and sigle='" . $row['sigle'] . "'";
                    $this->db->query($str3);
                    //   echo 'dev';
                }
                /* } */ else {


                    $str = "insert into  notespartielles   values (" . $row['matriculeEtudiant'] . ",'" . $row['sigle'] . "','" . $row['idEvaluation'] . "'," . $row['note'] . "," . $semestre . "," . $annee . ") ";
                    // echo $str;
                    $this->db->query($str);
                    // echo 'devoir inserted';;
                }
            }
        }

        return $result;
    }

    function transfert_note() {
        $sql3 = '';
        $sql3 = "SELECT n.matriculeEtudiant,u.sigle as idModule,n.sigle,n.note,u.semestre FROM `notespartielles` n,unite u,module m,evaluation e WHERE m.sigle=n.sigle and m.sigleunite=u.sigle and n.`annee`=" . ($annee) . " and e.idEvaluation=n.idEvaluation and (n.idEvaluation=1 or n.idEvaluation=2)and n.sigle='" . $row['sigle'] . "' and n.matriculeEtudiant=" . $row['matriculeEtudiant'] . " and u.semestre=" . $row['semestre'] . " and u.idProgramme='" . $idProgramme . "'";


        $result = array();

        $query1 = $this->db->query($sql3);

        $sql4 = '';
        $sql4 = "SELECT n.matriculeEtudiant,u.sigle as idModule,n.sigle,n.note,u.semestre FROM `notespartielles` n,unite u,module m,evaluation e WHERE m.sigle=n.sigle and m.sigleunite=u.sigle and n.`annee`=" . ($annee) . " and e.idEvaluation=n.idEvaluation and n.idEvaluation=2 and n.sigle='" . $row['sigle'] . "' and n.matriculeEtudiant=" . $row['matriculeEtudiant'] . " and u.semestre=" . $row['semestre'] . " and u.idProgramme='" . $idProgramme . "'";


        $result = array();

        $query2 = $this->db->query($sql4);

        foreach ($query1->result_array() as $row1) {
            foreach ($query2->result_array() as $row) {

                if (($row['note'] != -1)or ( $row1['note'] != -1)) {
                    $this->get_all_elements_nc($semestre, $annee, $idProgramme, $sigle, $idEvaluation = 1);
                    $this->get_all_elements_nc($semestre, $annee, $idProgramme, $sigle, $idEvaluation = 2);
                } else {
                    $this->get_all_elements_nc($semestre, $annee, $idProgramme, $sigle, $idEvaluation = 1);
                    $this->get_all_elements_nc($semestre, $annee, $idProgramme, $sigle, $idEvaluation = 2);
                    $this->get_all_elements_nc($semestre, $annee, $idProgramme, $sigle, $idEvaluation = 4);
                }
            }
        }
        return $result;
    }

    function get_all_element_nc($semestre, $annee, $idProgramme, $sigle, $idEvaluation) {

        $sql1 = '';
        $sql1 = "  SELECT nt.matriculeEtudiant,nt.sigle,nt.note as note,nt.idEvaluation,u.semestre,nt.annee FROM notespartielles nt,noncap_bis n,unite u WHERE nt.sigle=n.sigle and nt.`matriculeEtudiant`=n.`matriculeEtudiant` and u.semestre=" . $semestre . " and n.`note`>=7 and nt.`annee`=" . ($annee - 1) . " and nt.sigle='" . $sigle . "' and n.annee=" . ($annee - 1) . " and u.sigle=n.idModule and nt.idEvaluation=" . $idEvaluation . " and u.idProgramme='" . $idProgramme . "' ";

        //echo $sql1;
        $result = array();

        $query = $this->db->query($sql1);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $semestre = 3;
                if (($row['semestre'] % 2 == 0)) {
                    $semestre = 1;
                }

                $sql1 = '';
                $sql2 = "SELECT n.matriculeEtudiant,u.sigle as idModule,n.sigle,n.note,u.semestre FROM `notespartielles` n,unite u,module m,evaluation e WHERE m.sigle=n.sigle and m.sigleunite=u.sigle and n.`annee`=" . ($annee) . " and e.idEvaluation=n.idEvaluation and n.idEvaluation='" . $idEvaluation . "' and n.sigle='" . $row['sigle'] . "' and n.matriculeEtudiant=" . $row['matriculeEtudiant'] . " and u.semestre=" . $row['semestre'] . " and u.idProgramme='" . $idProgramme . "'";


                $result = array();

                $query = $this->db->query($sql2);


                if ($query->num_rows() > 0) {
                    foreach ($query1->result_array() as $row3) {
                        if ($row2['note'] == -1) {
                            $str3 = "update  notespartielles set note = " . $row['note'] . " where idEvaluation='" . $idEvaluation . "' and annee =" . $annee . " and matriculeEtudiant =" . $row['matriculeEtudiant'] . " and sigle='" . $row['sigle'] . "'";
                            $this->db->query($str3);
                            //  echo 'dev';
                        }
                    }
                } else {


                    $str = "insert into  notespartielles   values (" . $row['matriculeEtudiant'] . ",'" . $row['sigle'] . "','" . $row['idEvaluation'] . "'," . $row['note'] . "," . $semestre . "," . $annee . ") ";
                    //echo $str;
                    $this->db->query($str);
                    //   echo 'devoir inserted';;
                }
            }
        }
        return $result;
    }

    public function Mis_jour_PV($annee = 2015, $semestre = 3, $idProgramme) {
        $semestrep = 3;
        if ($semestre % 2 == 0) {
            $semestrep = 1;
        }
        $sql1 = '';
        $sql1 = "update `planetudes` set `etatNote`='professeur' WHERE `semestre`=" . $semestrep . " and annee=" . $annee . " and sigle in( select m.sigle from module m,unite u where u.semestre=" . $semestre . " and u.sigle =m.sigleunite and u.idProgramme='" . $idProgramme . "')";
      //  echo $sql1;
        $query = $this->db->query($sql1);

        $sql2 = '';
        $sql2 = "update `planetudes` set `etatNote`='scolarite' WHERE `semestre`=" . $semestrep . " and annee=" . $annee . " and sigle in( select m.sigle from module m,unite u where u.semestre=" . $semestre . " and u.sigle =m.sigleunite and u.idProgramme='" . $idProgramme . "')";
       // echo $sql2;
        $query = $this->db->query($sql2);

        $sql3 = '';
        $sql3 = "delete from notes_globales_dern_t_bis";
        $query = $this->db->query($sql3);

        $sql4 = '';
        $sql4 = "insert into notes_globales_dern_t_bis select * from notes_globales";
        $query = $this->db->query($sql4);

        $sql5 = '';
        $sql5 = "delete from planetudesmoduleelem_t";
        $query = $this->db->query($sql5);

        $sql6 = '';
        $sql6 = "insert into planetudesmoduleelem_t select * from planetudesmoduleelem";
        $query = $this->db->query($sql6);

        $sql7 = '';
        $sql7 = "delete from etudiant_sem_note_t_bis";
        $query = $this->db->query($sql7);

        $sql8 = '';
        $sql8 = "insert into etudiant_sem_note_t_bis select * from etudiant_sem_note_bis";
        $query = $this->db->query($sql8);

        $sql9 = '';
        $sql9 = "delete from capseul_t_bis;";
        $query = $this->db->query($sql9);

        $sql10 = '';
        $sql10 = "insert into capseul_t_bis select * from capseul_bis";
        $query = $this->db->query($sql10);

        $sql11 = '';
        $sql11 = "delete from capinterne_t_bis";
        $query = $this->db->query($sql11);

        $sql12 = '';
        $sql12 = "insert into capinterne_t_bis select * from capinterne_bis";
        $query = $this->db->query($sql12);

        $sql13 = '';
        $sql13 = "delete from capexterne_t_bis";
        $query = $this->db->query($sql13);

        $sql14 = '';
        $sql14 = "insert into capexterne_t_bis select * from capexterne_bis";
        $query = $this->db->query($sql14);
        $sql15 = '';
        $sql15 = "delete from noncap_t_bis";
        $query = $this->db->query($sql15);
        $sql16 = '';
        $sql16 = "insert into noncap_t_bis select * from noncap_bis";
        $query = $this->db->query($sql16);
        $sql17 = '';
        $sql17 = "delete from modules_non_valides_t_bis";
        $query = $this->db->query($sql17);
        $sql18 = '';
        $sql18 = "insert into modules_non_valides_t_bis select * from modules_non_valides_bis";
        $query = $this->db->query($sql18);
        $sql19 = '';
        $sql19 = "delete from modules_valides_t_bis";
        $query = $this->db->query($sql19);
        $sql20 = '';
        $sql20 = "insert into modules_valides_t_bis select * from modules_valides_bis";
        $query = $this->db->query($sql20);
        $sql21 = '';
        $sql21 = "delete from modules_decision_t_bis";
        $query = $this->db->query($sql21);
        $sql22 = '';
        $sql22 = "insert into modules_decision_t_bis select * from modules_decision_bis";
        $query = $this->db->query($sql22);
        $sql23 = '';
        $sql23 = "delete from releve_t_bis";
        $query = $this->db->query($sql23);
        $sql24 = '';
        $sql24 = "insert into releve_t_bis select * from releve_bis";
        $query = $this->db->query($sql24);
        $sql25 = '';
        $sql25 = "delete from semestre_decision_t_bis";
        $query = $this->db->query($sql25);
        $sql26 = '';
        $sql26 = "insert into semestre_decision_t_bis select * from semestre_decision_bis";
        $query = $this->db->query($sql26);
    }

// debut modif optimisation 



    function getInfoBulletinEtudiant_liste($matriules) {

        //$data['note'] et_informations_etudiant

        $sql = 'select e.*,p.nom as programme from etudiant e, dossieretudiant d,programme p where d.idProgramme= p.idProgramme and e.matriculeetudiant=d.matriculeetudiant and  e.matriculeetudiant in (' . $matriules . ') order by matriculeEtudiant';
        // echo $sql;
        $infos = array();
        $res = $this->db->query($sql);

        if ($res->num_rows() > 0) {

            foreach ($res->result_array() as $row) {
                $infos['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                $infos['nom'][] = $row['nom'];
                $infos['prenom'][] = $row['prenom'];
                $infos['nomArabe'][] = $row['nomArabe'];
                $infos['prenomArabe'][] = $row['prenomArabe'];
                // $infos['nom'][] = $row['nom'];
                $infos['dateNaissance'][] = $row['dateNaissance'];
                $infos['contact'][] = '';
                $infos['lieuNaissance'][] = $row['lieuNaissance'];
                $infos['niveau'][] = 'LLL111';
                $infos['genre'][] = $row['sexe'];
                $infos['dateInscription'][] = $row['dateInscription'];
                ;
                $infos['programme'][] = $row['programme'];
            }
        }
        //  print_r($)

        return $infos;
    }

    function getSemestreResult_bis_liste($matricules, $semestre, $annee) {

        $data = array();

        $str = "select s.matriculeEtudiant, s.decision, s.credits_val,e.note from semestre_decision_t_bis s,  etudiant_sem_note_t_bis e where s.semestre = e.semestre and s.annee = e.annee and s.matriculeEtudiant=e.matriculeEtudiant  and s.matriculeEtudiant  in (" .
                $matricules . ") and s.semestre = " . $semestre . " and s.annee = " . $annee . ' order by 1 ';
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                $data['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                $data['semestre'][] = $semestre;
                $data['ects'][] = $row['credits_val'];
                $data['decision'][] = $row['decision'];
                if (substr($row['decision'], 0, 3) == 'Ajo') {
                    $data['validation'][] = 'NV';
                } else {
                    $data['validation'][] = 'V';
                }
                $data['note'][] = $row['note'];
                $data['inscrit'][] = 'OK';
            }
        } else {
            $data['inscrit'][] = 'NO';
        }


        // echo 'getsemestreresult_bis'.$matriculeEtudiant;
        return $data;
    }

    function getModulesResult_bis_liste($matricules, $semestre, $annee) {

        //$data['note']
        $str = null;
        if ($semestre % 2 == 1) {
            $str = "SELECT  0 as status,r.matriculeEtudiant, r.`idModule`, r.`sigle`, r.`note`, r.`capit`,r.ects, m.titre, ifNull(n.notecc,0) as notecc,ifNull(n.noteExam,0) as noteExam, ifNull(n.noteRT,0) as noteRT  FROM `releve_t_bis` r, 
notes_globales_dern_t_bis n,module m where r.`matriculeEtudiant` = n.`matriculeEtudiant` and r.`sigle` = n.`sigle` and
r.`semestre` = n.`semestre` and r.annee=(2000+floor(n.annee/100)) and r.matriculeEtudiant in  (" . $matricules . ") and r.semestre =" .
                    $semestre . " and r.annee =" . $annee . "  and r.sigle = m.sigle union select 1 as status  ,e.matriculeEtudiant,u.sigle as idModule,m.sigle as sigle, 0 as note,'NC' as capit,m.nbCredits as ects,m.titre,0 as notecc,0 as noteExam,0 as noteRT from unite u,module m,elements_caches e where e.sigle=m.sigle and u.sigle=m.sigleunite and e.semestre=3 and e.annee=$annee  and e.matriculeEtudiant in  (" . $matricules . ") and  u.semestre =" . $semestre . " order by 1,2,3";
        } else {
            $str = "SELECT 0 as status,  r.matriculeEtudiant, r.`idModule`, r.`sigle`, r.`note`, r.`capit`,r.ects, m.titre, ifNull(n.notecc,0) as notecc,ifNull(n.noteExam,0) as noteExam, ifNull(n.noteRT,0) as noteRT  FROM `releve_t_bis` r, 
notes_globales_dern_t_bis n,module m where r.`matriculeEtudiant` = n.`matriculeEtudiant` and r.`sigle` = n.`sigle` and
r.`semestre` = n.`semestre` and r.annee=(2001+floor(n.annee/100)) and r.matriculeEtudiant in  (" . $matricules . ") and r.semestre =" .
                    $semestre . " and r.annee =" . $annee . "  and r.sigle = m.sigle union select 1 as status  ,e.matriculeEtudiant,u.sigle as idModule,m.sigle as sigle, 0 as note,'NC' as capit,m.nbCredits as ects,m.titre,0 as notecc,0 as noteExam,0 as noteRT from unite u,module m,elements_caches e where e.sigle=m.sigle and u.sigle=m.sigleunite and e.semestre=1 and e.annee=$annee  and e.matriculeEtudiant in  (" . $matricules . ") and  u.semestre =" . $semestre . " order by 1,2,3";
        }
        // echo $str;

        $modules = array();
        $moduleDec_l = $this->getModulesDecision_bis_liste($matricules, $semestre, $annee);

        //print_r(count ($moduleDec_l['matriculeEtudiant']));
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            $i = 0;

            foreach ($query->result_array() as $row) {
                $modules[$i] = NULL;
                $moduleDec = NULL;
                $moduleDec_titre = NULL;
                $moduleDec_decision = NULL;
                $moduleDec_note = NULL;
                $moduleDec_ects = NULL;
                if ($modules[$i] == NULL) {

                    $modules[$i] = array();
                    // recuperer la ligne pour le module 
                    //   $j=0;
                    // for($j = 0; $j<count($moduleDec_l['idModule']);$j++){
                    //   print_r($moduleDec_l['idModule']);
                    //       if( $moduleDec_l['idModule'][$j] = $row['idModule'] && $moduleDec_l['matriculeEtudiant'][$j] = $row['matriculeEtudiant'] )
                    //             break;
                    // } }
                    //  $modules['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                    for ($j = 0; $j < count($moduleDec_l['idModule']); $j++) {
                        if ($moduleDec_l['idModule'][$j] == $row['idModule'] && $moduleDec_l['matriculeEtudiant'][$j] == $row['matriculeEtudiant']) {
                            $moduleDec_titre = $moduleDec_l['titre'][$j];
                            $moduleDec_decision = $moduleDec_l['decision'][$j];
                            $moduleDec_note = $moduleDec_l['note'][$j];
                            $moduleDec_ects = $moduleDec_l['ects'][$j];


                            //  print_r($moduleDec['titre']);
                            if ($moduleDec_l != NULL) {

                                // $modules[$row['matriculeEtudiant']][$row['idModule']][] = array();
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['titre'] = $moduleDec_titre;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['decision'] = $moduleDec_decision;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['nm'] = $moduleDec_note;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['ects'] = $moduleDec_ects;
                            }
                        }
                    }
                    $modules[$row['matriculeEtudiant']][$row['idModule']]['nb'] = 0;
                    // $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][] = array();
                    //   }
                } else if (!array_key_exists($row['idModule'], $modules)) {
                    //   $modules['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                    $j = 0;
                    for ($j = 0; $j < count($moduleDec_l['idModule']); $j++) {
                        if ($moduleDec_l['idModule'][$j] == $row['idModule'] && $moduleDec_l['matriculeEtudiant'][$j] == $row['matriculeEtudiant']) {
                            $moduleDec_titre = $moduleDec_l['titre'][$j];
                            $moduleDec_decision = $moduleDec_l['decision'][$j];
                            $moduleDec_note = $moduleDec_l['note'][$j];
                            $moduleDec_ects = $moduleDec_l['ects'][$j];



                            // $moduleDec = $moduleDec_l;

                            if ($moduleDec_l != NULL) {
                                // $modules[$row['matriculeEtudiant']][$row['idModule']][] = array();
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['titre'] = $moduleDec_titre;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['decision'] = $moduleDec_decision;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['nm'] = $moduleDec_note;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['ects'] = $moduleDec_ects;
                            }
                        }
                    }
                    $modules[$row['matriculeEtudiant']][$row['idModule']]['nb'] = 0;
                    //$modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][] = array();
                }
                //if($modules[$row['idModule']]['elements']!=NULL){
                //$modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']] =array();

                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['status'] = $row['status'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['nfe'] = $row['note'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['capit'] = $row['capit'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['ncc'] = $row['notecc'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['nsn'] = $row['noteExam'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['nsr'] = $row['noteRT'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['ects'] = $row['ects'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['titre'] = $row['titre'];

                $modules[$row['matriculeEtudiant']][$row['idModule']]['nb'] ++;
                // }

                $i++;
            }
        }
        //  print_r($modules);
        // echo 'getModulesResult_bis'.$matriculeEtudiant;
        return $modules;
    }

    function getModulesDecision_bis_liste($matricules, $semestre, $annee) {
        $str = "SELECT m.matriculeEtudiant as matriculeEtudiant,m.idModule as idModule, m.decision as decision, m.credits_val as ects, u.titre as titre, e.note as note from modules_decision_t_bis m, unite u, etudiant_mod_note_bis e where m.idModule=u.sigle and m.idModule = e.idModule and m.matriculeEtudiant=e.matriculeEtudiant and m.annee=e.annee and m.semestre=e.semestre  and m.matriculeEtudiant in   (" .
                $matricules . ") and " . "  m.semestre = " . $semestre . " and m.annee = " . $annee . '   order by 1,2';
        //echo $str;
        // union select e.matriculeEtudiant,u.sigle as idModule ,"NV" as decision ,"0" as ects , u.titre as titre, "0" as note from elements_caches e,unite u, module m where e.sigle=m.sigle and u.sigle=m.sigleunite and e.matriculeEtudiant in('.$matricules.') and e.semestre = '. $semestre.' and e.annee = '. $annee. ' 
        $moduleDec = array();
        $query = $this->db->query($str);

        if ($query->num_rows() > 0) {
            $i = 0;
            foreach ($query->result_array() as $row) {

                //echo  print_r($row);
                $moduleDec['idModule'][] = $row['idModule'];
                $moduleDec['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                $moduleDec['titre'][] = $row['titre'];
                $moduleDec['decision'][] = $row['decision'];
                $moduleDec['ects'][] = $row['ects'];
                $moduleDec['note'][] = $row['note'];
            }
            $i++;
        }
        //echo 'getModulesDecision_bis'.$matriculeEtudiant;
        // print_r($moduleDec);
        // echo 'modules decision =';

        return $moduleDec;
        //   print_r($moduleDec);
    }

    function get_etudiant_annonymat_liste($matricules, $session, $annee, $semestre) {

        $sql = "select matriculeEtudiant,code_ex, code_rt from anonymat  where annee=" . $annee . " and semestre=" . $semestre . " and anonymat.matriculeEtudiant in (" . $matricules . ")";
        // $this->db->where('matriculeEtudiant', $matricule[$i]);
        //$query = $this->db->get('etudiant');
        // $code_ex=null;
        // $code_rt=null;
        // echo $sql;
        $info = null;
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                if ($session == 1) {
                    $info['code'][] = $row['code_ex'];
                } else {
                    $info['code'][] = $row['code_rt'];
                }
            }

            return $info;
        }

        return NULL;
    }

    function get_etudiants_isncrits246($annee) {
        // $anne=$annee+1;
       $sql1 = '';
        $sql1 = "SELECT * from niveau_ins2016 where matriculeetudiant in (select matriculeetudiant from etu_element_ratt)";
        // $sql1="SELECT * from niveau_ins2016";
        /* $sql1 ="SELECT distinct p.`matriculeetudiant`, '3' as niveau,d.idProgramme from planetudes p, dossieretudiant d where  p.matriculeetudiant=d.matriculeetudiant  and ((p.annee=2017 and p.semestre=1 and p.sigle like '___6%') and p.matriculeEtudiant not in(SELECT `matriculeEtudiant` FROM `etudiant_sem_note_bis` WHERE `semestre`=3 and annee=2016 and note<1) )

          union
          SELECT distinct p1.`matriculeetudiant`, '2' as niveau,d.idProgramme from planetudes p1, dossieretudiant d where
          p1.matriculeetudiant=d.matriculeetudiant and  ((p1.annee=2017 and p1.semestre=1 and p1.sigle like '___4%') ) and not exists ( select p2.matriculeetudiant from planetudes p2 where p2.matriculeetudiant=p1.matriculeetudiant and ((p2.annee=2017 and p2.semestre=1 and p2.sigle like '___6%'))and p1.matriculeEtudiant not in(SELECT `matriculeEtudiant` FROM `etudiant_sem_note_bis` WHERE `semestre`=3 and annee=2016 and note<1))

          union
          SELECT distinct p1.`matriculeetudiant`, '1' as niveau,d.idProgramme from  planetudes p1, dossieretudiant d where  p1.matriculeetudiant=d.matriculeetudiant and ((p1.annee=2017 and p1.semestre=1 and p1.sigle like '___2%') ) and not exists ( select p2.matriculeetudiant from planetudes p2 where p2.matriculeetudiant=p1.matriculeetudiant and ((p2.annee=2017 and p2.semestre=1 and (p2.sigle like '___6%' or p2.sigle like '___4%'))) and p1.matriculeEtudiant not in(SELECT `matriculeEtudiant` FROM `etudiant_sem_note_bis` WHERE `semestre`=3 and annee=2016 and note<1))";
         */
        //echo $sql1;
        $result = array();

        $query = $this->db->query($sql1);
        if ($query->num_rows() > 0) {
            // $i=0;
            $result['RXTEL'] = array();
            $result['RXTEL']['s6'] = null;
            $result['RXTEL']['s2'] = null;
            $result['RXTEL']['s4'] = null;

            $result['LGTR'] = array();
            $result['LGTR']['s6'] = null;
            $result['LGTR']['s2'] = null;
            $result['LGTR']['s4'] = null;


            $result['MAN'] = array();
            $result['MAN']['s6'] = null;
            $result['MAN']['s2'] = null;
            $result['MAN']['s4'] = null;


            $result['MAEF'] = array();
            $result['MAEF']['s6'] = null;
            $result['MAEF']['s2'] = null;
            $result['MAEF']['s4'] = null;

            foreach ($query->result_array() as $row) {
                if ($row['idProgramme'] == 'RXTEL') {
                    if ($row['niveau'] == 3) {
                        $result['RXTEL']['s6'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 2) {
                        $result['RXTEL']['s4'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 1) {
                        $result['RXTEL']['s2'][] = $row['matriculeetudiant'];
                    }
                } else if ($row['idProgramme'] == 'MAN') {
                    if ($row['niveau'] == 3) {
                        $result['MAN']['s6'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 2) {
                        $result['MAN']['s4'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 1) {
                        $result['MAN']['s2'][] = $row['matriculeetudiant'];
                    }
                } else if ($row['idProgramme'] == 'MAEF') {
                    if ($row['niveau'] == 3) {
                        $result['MAEF']['s6'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 2) {
                        $result['MAEF']['s4'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 1) {
                        $result['MAEF']['s2'][] = $row['matriculeetudiant'];
                    }
                } else if ($row['idProgramme'] == 'LGTR') {
                    if ($row['niveau'] == 3) {
                        $result['LGTR']['s6'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 2) {
                        $result['LGTR']['s4'][] = $row['matriculeetudiant'];
                    } else if ($row['niveau'] == 1) {
                        $result['LGTR']['s2'][] = $row['matriculeetudiant'];
                    }
                }
            }
        }
        //print_r($result);
        return $result;
    }

    function getSallesEffectif() {
        $result = array();
        $sql = 'select id_salle,num_debut,num_fin from salles order by num_debut';
        $req = $this->db->query($sql);
        $row = $req->result_array();
        foreach ($row as $value) {
            $result[] = $value['num_fin'] - $value['num_debut'] + 1;
        }
        return $result;
    }

    function getPourcentageParSalle() {
        $CapSalles = $this->getSallesEffectif();
        $i = 0;
        $s = 0;
        $pourCentages = array();
        for ($i = 0; $i < count($CapSalles); $i++) {
            $s += $CapSalles[$i];
        }
        for ($i = 0; $i < count($CapSalles); $i++) {
            $pourCentages[] = $CapSalles[$i] / $s;
        }

        return $pourCentages;
    }

    function getSallesDebut() {
        $result = array();
        $sql = 'select id_salle,num_debut,num_fin from salles order by num_debut';
        $req = $this->db->query($sql);
        $row = $req->result_array();
        foreach ($row as $value) {
            $result[] = $value['num_debut'];
        }
        return $result;
    }

    function get_salle_exam() {
        $result = array();
        $sql = 'select id_salle from salles order by num_debut';
        $req = $this->db->query($sql);
        $row = $req->result_array();
        foreach ($row as $value) {
            $result[] = $value['id_salle'];
        }
        return $result;
    }

    function get_niveau() {
        $result = array();
        $sql = 'select distinct niveau from niveau_ins2016 ';
        $req = $this->db->query($sql);
        $row = $req->result_array();
        foreach ($row as $value) {
            $result[] = $value['niveau'];
        }
        return $result;
    }

    function list_etu_salle_exam($programme, $niveau, $salle) {
        $result = array();
        $sql = "select n.matriculeetudiant AS matriculeetudiant,n.niveau AS niveau,n.idProgramme AS idProgramme,concat(concat(e.nom,' '),e.prenom) AS nom_c,x.num_exam AS no_exam,s.id_salle AS id_salle from (((iup.niveau_ins2016 n join iup.etudiant e) join iup.numero_exam x) join iup.salles s) where ((n.matriculeetudiant = e.matriculeEtudiant) and (n.matriculeetudiant = x.matriculeEtudiant) and (x.num_exam between s.num_debut and s.num_fin))";
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    function get_elt_nc($matriculeEtudiant, $semestre, $annee = 2016) {
        $result = array();
        //$sql='select matriculeetudiant,sigle niveau from noncap_bis  where annee=.'($annee-1)." and semestre=".$semestre.'';
        $sql = "select sigle niveau from noncap_bis  where   matriculeetudiant=" . $matriculeEtudiant . " and semestre = " . $semestre . " and annee = " . ($annee - 1) . ' ';
        //echo $str;
        $req = $this->db->query($sql);
        $row = $req->result_array();
        foreach ($row as $value) {
            $result[] = $value['sigle'];
        }
        return $result;
    }

    function getModulesResult_bis_h($matriculeEtudiant, $semestre, $annee) {

        //$data['note']
        $str = null;
        if ($semestre % 2 == 1) {
            $str = "SELECT    r.annee ,r.`idModule`, r.`sigle`, r.`note`, r.`capit`,r.ects, m.titre, ifNull(n.notecc,0) as notecc,ifNull(n.noteExam,0) as noteExam, ifNull(n.noteRT,0) as noteRT  FROM `releve_t_bis` r, 
notes_globales_dern_t_bis n,module m where r.`matriculeEtudiant` = n.`matriculeEtudiant` and r.`sigle` = n.`sigle` and
r.`semestre` = n.`semestre` and r.annee=(2000+floor(n.annee/100)) and r.matriculeEtudiant = " . $matriculeEtudiant . " and r.semestre =" .
                    $semestre . "  and r.annee<=" . $annee . " and r.sigle = m.sigle union select e.annee,u.sigle as idModule,m.sigle as sigle, 0 as note,'NC' as capit,m.nbCredits as ects,m.titre,0 as notecc,0 as noteExam,0 as noteRT from unite u,module m,elements_caches e where e.sigle=m.sigle and u.sigle=m.sigleunite and e.semestre=3 and e.annee=$annee  and e.matriculeEtudiant='" . $matriculeEtudiant . "' and  u.semestre =" . $semestre . "   order by 1,2,3";
        } else {
            $str = "SELECT  r.annee,0 as status  ,r.`idModule`, r.`sigle`, r.`note`, r.`capit`,r.ects, m.titre, ifNull(n.notecc,0) as notecc,ifNull(n.noteExam,0) as noteExam, ifNull(n.noteRT,0) as noteRT  FROM `releve_t_bis` r, 
notes_globales_dern_t_bis n,module m where r.`matriculeEtudiant` = n.`matriculeEtudiant` and r.`sigle` = n.`sigle` and
r.`semestre` = n.`semestre` and r.annee=(2001+floor(n.annee/100)) and r.matriculeEtudiant = " . $matriculeEtudiant . " and r.semestre =" .
                    $semestre . "   and r.annee<=" . ($annee) . " and r.sigle = m.sigle  union select e.annee,1 as status  ,u.sigle as idModule,m.sigle as sigle, 0 as note,'NC' as capit,m.nbCredits as ects,m.titre,0 as notecc,0 as noteExam,0 as noteRT from unite u,module m,elements_caches e where e.sigle=m.sigle and u.sigle=m.sigleunite and e.semestre=1 and e.annee=$annee  and e.matriculeEtudiant='" . $matriculeEtudiant . "' and  u.semestre =" . $semestre . "   order by 1,2,3";
        }
     //   echo $str;

        $modules = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            // $i=0;
            foreach ($query->result_array() as $row) {
                $moduleDec = NULL;
                if ($modules == NULL) {

                    $modules = array();
                    $moduleDec = $this->getModulesDecision_bis($row['idModule'], $matriculeEtudiant, $semestre, $annee);
                    if ($moduleDec != NULL) {
                        $modules[$row['idModule']] = array();
                        $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                        $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                        $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                        $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    }
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                } else if (!array_key_exists($row['idModule'], $modules)) {

                    $moduleDec = $this->getModulesDecision_bis($row['idModule'], $matriculeEtudiant, $semestre, $annee);
                    if ($moduleDec != NULL) {
                        $modules[$row['idModule']] = array();
                        $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                        $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                        $modules[$row['idModule']]['nm'] = $moduleDec['note'];
                        $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                    }
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                }
                //if($modules[$row['idModule']]['elements']!=NULL){
                $modules[$row['idModule']]['elements'][$row['sigle']][] = array();
                $modules[$row['idModule']]['elements'][$row['sigle']]['annee'][] = $row['annee'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ncc'][] = $row['notecc'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsn'][] = $row['noteExam'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsr'][] = $row['noteRT'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nfe'][] = $row['note'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['capit'][] = $row['capit'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ects'][] = $row['ects'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['titre'][] = $row['titre'];
                ;
                $modules[$row['idModule']]['nb'] ++;
                // }
            }
        }
        //
        //echo print_r($modules['INF111']);
        // echo 'getModulesResult_bis'.$matriculeEtudiant;
        return $modules;
    }

    function statistique($semestre, $annee) {
        $result = array();
        $sql = 'select id_salle from salles order by num_debut';
        $req = $this->db->query($sql);
        $row = $req->result_array();
        foreach ($row as $value) {
            $result[] = $value['id_salle'];
        }
        return $result;
    }

    function getStatistique($idProgramme, $annee, $semstre) {
        if ($idProgramme == 'tout')
            $prog = array("LGTR", "MAEF", "MAN", "RXTEL");
        else
            $prog = array($idProgramme);



        $data = array();

        for ($i = 0; $i < count($prog); $i++) {
            $idProgramme = $prog[$i];
            $query = "SELECT count(distinct `matriculeEtudiant`) as nbEtu1 FROM `niveau_inscrits` where annee=" . $annee . " and (semestre=3)  and niveau=1 and `idProgramme`='" . $idProgramme . "'";
            $q = $this->db->query($query);
            $res = $q->row_array();
            $data[$i]['nbEtu1'] = $res['nbEtu1'];

            $query = "SELECT count(distinct `matriculeEtudiant`) as nbEtu2 FROM `niveau_inscrits` where annee=" . $annee . " and (semestre=3)  and niveau=2 and `idProgramme`='" . $idProgramme . "'";
            $q = $this->db->query($query);
            $res = $q->row_array();
            $data[$i]['nbEtu2'] = $res['nbEtu2'];

            $query = "SELECT count(distinct `matriculeEtudiant`) as nbEtu3 FROM `niveau_inscrits` where annee=" . $annee . " and (semestre=3)  and niveau=3 and `idProgramme`='" . $idProgramme . "'";
            $q = $this->db->query($query);
            $res = $q->row_array();
            $data[$i]['nbEtu3'] = $res['nbEtu3'];
        }

        return $data;
    }

    function stages_traveaux($matriculeEtudiant) {

        $stage_etudiant = NULL;
        $query = $this->db->query("SELECT Distinct `idProgramme` FROM `DossierEtudiant` where `matriculeEtudiant` = '$matriculeEtudiant'");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $idprog = $row['idProgramme'];
                $query2 = $this->db->query("SELECT * FROM `stages_travaux` where `idProgramme` = '$idprog'");
                if ($query2->num_rows() > 0) {
                    foreach ($query2->result_array() as $row) {
                        $stage_etudiant['titre'][] = $row['titre'];
                        $stage_etudiant['sigle'][] = $row['sigle'];
                        $stage_etudiant['duree'][] = $row['duree'];
                        $stage_etudiant['ects'][] = $row['ects'];
                    }
                }
            }
        }

        return $stage_etudiant;
    }

    function info_bulltin($matriculeEtudiant) {
        $rest = substr($matriculeEtudiant, 0, 2);
        $str = "SELECT distinct count(d.`matriculeetudiant`) as nb  FROM `dossieretudiant` d WHERE d.`matriculeetudiant`<=" . $matriculeEtudiant . " and d.`matriculeEtudiant` like '" . $rest . "%'   union all SELECT distinct count(d.`matriculeetudiant`) as nb  FROM `dossieretudiant` d WHERE d.`matriculeetudiant`<=" . $matriculeEtudiant . " and d.`matriculeetudiant` like '" . $rest . "%' and d.idProgramme=(select idProgramme from dossieretudiant where matriculeetudiant=" . $matriculeEtudiant . ")";
        
        $info = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info[] = $row['nb'];
            }
        }
        return $info;
        //print_r($info);
    }

    function max_annee_ajournee($matriculeEtudiant, $semestre, $decision = "Ajourné(e)") {
        $str = "SELECT max(annee) as max FROM `semestre_decision_t_bis` WHERE `semestre`=" . $semestre . " and `matriculeEtudiant`=" . $matriculeEtudiant . " and `decision`='" . $decision . "'";
       // echo $str;
        $info = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info = $row['max'];
            }
        }
        return $info;
    }

    function min_annee_admis($matriculeEtudiant, $semestre, $decision = "Admis(e)") {
        $str = "SELECT min(annee) as min  FROM `semestre_decision_t_bis` WHERE `semestre`=" . $semestre . " and `matriculeEtudiant`=" . $matriculeEtudiant . " and (`decision`='" . $decision . "' or decision='Compense')";
        // echo $str;
        $info = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info = $row['min'];
            }
        }
        return $info;
    }

    public function get_events($start, $end) {
        return $this->db
                        ->where("start >=", $start)
                        ->where("end <=", $end)
                        ->get("calendar_events");
    }

    public function add_event($data) {
        $this->db->insert("calendar_events", $data);
    }

    public function get_event($id) {
        return $this->db->where("ID", $id)->get("calendar_events");
    }

    public function update_event($id, $data) {
        $this->db->where("ID", $id)->update("calendar_events", $data);
    }

    public function delete_event($id) {
        $this->db->where("ID", $id)->delete("calendar_events");
    }

    function module_elements($idProgramme, $semestre) {

        $module_elements = NULL;


        $query2 = $this->db->query("SELECT * FROM `infomodule_element` where `Filiere` = '$idProgramme' and  semestre = " . $semestre . "");
        if ($query2->num_rows() > 0) {
            foreach ($query2->result_array() as $row) {
                $module_elements['titre'][] = $row['titre'];
                $module_elements['sigle'][] = $row['sigle'];
            }
        }

        return $module_elements;
    }

    function getData($loadType, $loadId, $annee, $semestre, $type, $matriculeEmploye) {

        $query = null;
       // echo '$$$$$$$$$$$$$$$$$$$$$$$$$$'.$loadType;
        if ($loadType == "groupe") {
            /* $fieldList='idGroupe';
              $table='groupe';
              $fieldName='sigle';
              $orderByField='idGroupe';
              $this->db->select($fieldList);
              $this->db->from($table);
              $this->db->where($fieldName, $loadId);
              $this->db->where("annee", $annee);
              $this->db->order_by($orderByField, 'asc');
              $query=$this->db->get(); */
            $fieldName = 'idGroupe';
            $fieldName1 = 'annee';
            $table = 'groupe';
            $query = $this->db->query("SELECT * FROM `groupe` where `sigle` = '" . $loadId . "' and  annee = " . $annee . "");
        } elseif ($loadType == "employe") {

            $fieldName = 'idGroupe';
            $this->db->distinct();
            $this->db->order_by('matriculeEmploye ');
            $this->db->where($fieldName, $loadId);
            $this->db->select('employe.matriculeEmploye as matriculeEmploye , employe.nom as nom,employe.prenom as prenom');
            $this->db->from('groupe');
            $this->db->join('employe', 'groupe.matriculeEmploye = employe.matriculeEmploye');
            $query = $this->db->get();
        } elseif ($loadType == "infomodule_element") {
            $fieldName = 'Filiere';
            $fieldName1 = 'semestre';
            $table = 'infomodule_element';
            if ($type == "1") {
                $query = $this->db->query("SELECT * FROM `infomodule_element` where `Filiere` = '" . $loadId . "' and  semestre = " . $semestre . "");
            } elseif ($type == "2") {
                $query = $this->db->query("SELECT m.* FROM `infomodule_element` m,groupe g where g.sigle=m.sigle and g.matriculeEmploye='" . $matriculeEmploye . "'");
                $this->db->insert("r_events", array('test' => "SELECT m.* FROM `infomodule_element` m,groupe g where g.sigle=m.sigle and g.matriculeEmploye='" . $matriculeEmploye . "'"));
            } elseif ($type == "3") {
                $query = $this->db->query("SELECT * FROM `infomodule_element`");
            }
        }



        return $query;
    }

    function getInfoElements($loadType, $loadId, $annee, $semestre, $type, $matriculeEmploye) {

        $query = null;
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];
        if ($loadType == "infomodule_element" or $loadType == "infomodule_elementMF" or $loadType == "infomodule_elementMN" or $loadType == "infomodule_elementRT" or $loadType == "infomodule_elementLT") {
            $fieldName = 'Filiere';
            $fieldName1 = 'semestre';
            $table = 'infomodule_element';

            if ($type == "1") {
                $query = $this->db->query("select `m`.`sigle` AS `sigle`,`m`.`titre` AS `titre`,`u`.`idProgramme` AS `idProgramme`,`u`.`semestre` AS `semestre` from (`iup`.`module` `m` join `iup`.`unite` `u`) where ((`m`.`sigleunite` = `u`.`sigle`) and `m`.`sigle` in (select distinct `iup`.`planetudes`.`sigle` from `iup`.`planetudes` where ((`iup`.`planetudes`.`annee` = $anneeCourante) and u.`idProgramme` = '" . $loadId . "' and  u.semestre = " . $semestre . " and (`iup`.`planetudes`.`semestre` = $semestreCourant))))");
            } elseif ($type == "2") {
                $query = $this->db->query("SELECT m.* FROM `infomodule_element` m,groupe g where g.sigle=m.sigle and g.matriculeEmploye='" . $matriculeEmploye . "'");
                //$this->db->insert("r_events", array('test'=>"SELECT m.* FROM `infomodule_element` m,groupe g where g.sigle=m.sigle and g.matriculeEmploye='".$matriculeEmploye."'"));
            } elseif ($type == "3") {
                $query = $this->db->query("SELECT * FROM `infomodule_element`");
            }
        } elseif ($loadType == "anc_element") {
            $query = $this->db->query("SELECT distinct p.sigle,m.titre FROM `planetudes` p,module m,unite u where p.sigle=m.sigle and u.sigle=m.sigleunite and u.semestre=$semestre and p.semestre=$semestreCourant and p.annee=$anneeCourante  and u.idProgramme='" . $loadId . "' and p.sigle not in(select sigle from infomodule_element)  ");
        } elseif ($loadType == "tous_element") {
            $query = $this->db->query("SELECT distinct p.sigle,m.titre FROM `planetudes` p,module m,unite u where p.sigle=m.sigle and u.sigle=m.sigleunite and u.semestre=$semestre and p.semestre=$semestreCourant and p.annee=$anneeCourante  and u.idProgramme='" . $loadId . "' ");
        } elseif ($loadType == "etudiant_element") {
            //  $this->db->insert("r_events", array('test'=>"SELECT  p.matriculeetudiant,concat(e.prenom,' ',e.nom)as nom  FROM `planetudes` p,etudiant e where  p.semestre=$semestreCourant and p.annee=$anneeCourante  and p.sigle='".$matriculeEmploye."' and p.matriculeetudiant=e.matriculeetudiant"));

            $query = $this->db->query("SELECT  p.matriculeetudiant,concat(e.prenom,' ',e.nom)as nom  FROM `planetudes` p,etudiant e where  p.semestre=$semestreCourant and p.annee=$anneeCourante  and p.sigle='" . $matriculeEmploye . "' and p.matriculeetudiant=e.matriculeetudiant ");
        }



        return $query;
    }

    public function add_r_events($data) {
        $this->db->insert("r_events", $data);
    }

    function max_id_r_events() {
        $str = "SELECT max(id) as max  FROM `r_events` ";

        $info = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info = $row['max'];
            }
        }
        return $info;
    }

    function update_events($id, $rd, $type, $type1, $local, $start, $end, $idGroupe, $prof, $title, $typecm) {
        $sql = "select r.start,r.end,r.id from events e,r_events r  where r.id=e.id_repeat and e.id=" . $id;


        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {



                if ($rd == "1") {
                    //$q="delete from events  where id_repeat='".$row['id_repeat']."' and start>'".$row['start']."'";
                    //$this->db->query($q);
                    if ($type == "1") {

                        $this->update_unique_event($id, $rd, $type, $type1, $local, $start, $end, $idGroupe, $prof, $title,$typecm);
                    } elseif ($type == "2") {


                        $q = " INSERT INTO `events` (
                    `title` ,
                    `start` ,
                    `end` ,
                    `id_salle`,
                    `idGroupe`,
                    `id_repeat`,
                    `dow`
                    
                    )
                    VALUES (
                    '" . $title . "',
                    '" . $start . "',
                    '" . $end . "', '" . $local . "', '" . $idGroupe . "', '" . $row['id'] . "', '" . $typecm . "'
                    )";
                        $this->db->query($q);
                        // $this->db->insert("r_events", array('test'=>$q));
                    } elseif ($type == "3") {

                        $q = " INSERT INTO `events` (
                    `title` ,
                    `start` ,
                    `end` ,
                    `id_salle`,
                    `idGroupe`,
                    `id_repeat`,
                    `dow`
                    
                    )
                    VALUES (
                    '" . $title . "',
                    '" . $start . "',
                    '" . $end . "', '" . $local . "', '" . $idGroupe . "', '" . $row['id'] . "', '" . $typecm . "'
                    )";
                        // $this->db->insert("r_events", array('test'=>$q));

                        $this->db->query($q);
                    }
                } elseif ($rd == "3") {
                    if ($type == "1") {

                        $this->update_unique_event($id, $rd, $type, $type1, $local, $start, $end, $idGroupe, $prof, $title, $typecm);
                    } elseif ($type == "2") {


                        $q = " INSERT INTO `events` (
                    `title` ,
                    `start` ,
                    `end` ,
                    `id_salle`,
                    `idGroupe`,
                    `id_repeat`,
                    `dow`
                    
                    )
                    VALUES (
                    '" . $title . "',
                    '" . $start . "',
                    '" . $end . "', '" . $local . "', '" . $idGroupe . "', '" . $row['id'] . "', '" . $typecm . "'
                    )";
                        $this->db->query($q);
                        //$this->db->insert("r_events", array('test'=>$q));
                    } elseif ($type == "3") {

                        $q = " INSERT INTO `events` (
                    `title` ,
                    `start` ,
                    `end` ,
                    `id_salle`,
                    `idGroupe`,
                    `id_repeat`,
                    `dow`
                    
                    )
                    VALUES (
                    '" . $title . "',
                    '" . $start . "',
                    '" . $end . "', '" . $local . "', '" . $idGroupe . "', '" . $row['id'] . "', '" . $typecm . "'
                    )";
                        //$this->db->insert("r_events", array('test'=>$q));

                        $this->db->query($q);
                    }
                }
            }
        } else {
            if ($rd == "1") {
                $this->update_unique_event($id, $rd, $type, $type1, $local, $start, $end, $idGroupe, $prof, $title, $typecm);
            } else {
                $this->delete_unique_event($id);
            }
        }
    }

    function get_employee() {
        $employe = NULL;
        $query = $this->db->query("SELECT Distinct `matriculeEmploye`, `nom`,`prenom` FROM `employe` ORDER BY `prenom`,`nom`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $employe['matriculeEmploye'][] = $row['matriculeEmploye'];
                $employe['nom'][] = $row['nom'];
                $employe['prenom'][] = $row['prenom'];
            }
        }
        return $employe;
    }

    function update_g_employee($matriculeEmploye, $idGroupe) {
        $q = "update groupe set matriculeEmploye='" . $matriculeEmploye . "'  where    idGroupe='" . $idGroupe . "'";
        $this->db->query($q);
        $this->db->insert('r_events', array('test' => $q));
        // $q="update events set matriculeEmploye='".$matriculeEmploye."'  where    idGroupe='".$idGroupe."'";
        //  $this->db->query($q);
    }

    function get_events1($start, $end, $s, $p, $chek1, $local, $employe1) {
        $events = NULL;
        $sql = "";
        $sql1 = "";
        if ($chek1 == "1") {
            $sql = " select e.`id`, e.`start` ,e.`end` ,e.`title`,e.id_salle as local,e.idGroupe,concat(m.prenom,' ',m.nom) as matriculeEmploye,m.matriculeEmploye as matricule,m1.titre,e.dow as type,e.id_repeat,m.telephone1 FROM  `events` e,employe m,module m1,groupe g,unite u where m1.sigle=g.sigle and u.sigle=m1.sigleunite and e.idGroupe=g.idGroupe and g.matriculeEmploye=m.matriculeEmploye and u.idProgramme='" . $p . "' and u.semestre=" . $s . "  and  (date(e.start) >= '$start' AND date(e.start) <= '$end')";
        } elseif ($chek1 == "2") {
            $sql = "SELECT   e.`id`, e.`start` ,e.`end` ,e.`title`,e.id_salle as local,e.idGroupe,concat(m.prenom,' ',m.nom) as matriculeEmploye,m.matriculeEmploye as matricule,m1.titre,e.dow as type,e.id_repeat,m.telephone1 FROM  `events` e,employe m,module m1,groupe g where m1.sigle=g.sigle and e.idGroupe=g.idGroupe and g.matriculeEmploye=m.matriculeEmploye  and g.matriculeEmploye='$employe1'  and  (date(e.start) >= '$start' AND date(e.start) <= '$end')";
        } elseif ($chek1 == "3") {
            $sql = "SELECT  e.`id`, e.`start` ,e.`end` ,e.`title`,e.id_salle as local,e.idGroupe,concat(m.prenom,' ',m.nom) as matriculeEmploye,m.matriculeEmploye as matricule,m1.titre,e.dow as type,e.id_repeat,m.telephone1 FROM  `events` e,employe m,module m1,groupe g where m1.sigle=g.sigle and e.idGroupe=g.idGroupe and g.matriculeEmploye=m.matriculeEmploye  and e.id_salle='" . $local . "' and  (date(e.start) >= '$start' AND date(e.start) <= '$end')";
        }
        // $this->db->insert('r_events', array('test' => $sql));
        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $events[] = $row;
            }
        }


        // $events[] = $row;

        $result['events'] = $events;
        $result['start'] = $start;
        $result['end'] = $end;
        return $result;
    }

    function r_events($id) {
        $str = "SELECT r.*   FROM `r_events` r,events e where e.id_repeat=r.id and e.id=" . $id;

        $info = NULL;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info['start'] = $row['start'];
                $info['end'] = $row['end'];
                $info['id'] = $row['id'];
            }
        }
        return $info;
    }

    function delete_events_ulterieurs($id_r, $start) {
        $q = "delete from events  where id_repeat='" . $id_r . "' and start>'" . $start . "'";
        $this->db->query($q);
    }

    function delete_unique_event($id) {
        $q = "delete from events  where id=" . $id;
        $this->db->query($q);
    }

    function delete_all_events($id_r,$start='') {
        $q = "delete from events  where id_repeat='" . $id_r . "' and start>'" . $start . "'";
        $this->db->query($q);
        $q1 = "delete from events  where id_repeat='" . $id_r . "' and end<'" . $start . "'";
        $this->db->query($q1);
    }

    function update_unique_event($id, $rd, $type, $type1, $local, $start, $end, $idGroupe, $prof, $title, $typecm) {
        $q = "update events set start='" . $start . "' , end='" . $end . "' , idGroupe='" . $idGroupe . "', id_salle='" . $local . "', title='" . $title . "', dow='" . $typecm . "' where id=" . $id;
        //$this->db->insert('r_events', array('test' =>  $q));
        $this->db->query($q);
    }

    // une fonction  qui permet de rassembler les évenements de même horaire  d'un module à un seul évenement
    function get_events1_groupe_mm_h($start, $end, $s, $p, $chek1, $local, $employe1) {
        $events = NULL;
        $sql = "";
        $sql1 = "";

        /*   $sql2="select e.id from events e where  (date(e.start) >= '$start' AND date(e.start) <= '$end')";
          $query2 = $this->db->query($sql2);
          if ($query2->num_rows() > 0) {
          foreach ($query2->result_array() as $row1) {
          $sql3="select distinct e.* from events e,events e2 where e.id=".$row1['id']." and (((date(e.start) <= date(e2.start)) and (date(e.end) = date(e2.end))) or ((date(e.start) >= date(e2.start)) and (date(e.end) >= date(e2.end))) or ((date(e.start) >= date(e2.start)) and (date(e.end) <= date(e2.end))) or ((date(e.start) <= date(e2.start)) and (date(e.end) >= date(e2.start)))) and e.id_salle=e2.id_salle";
          $query3 = $this->db->query($sql3);
          if ($query3->num_rows() > 0) {
          $chv=1;
          }}} */
        if ($chek1 == "1") {
            $sql1 = " select distinct g.sigle,e.`id`, e.`start` ,e.`end` ,e.`title`,e.id_salle as local,e.idGroupe,m.nom as matriculeEmploye,m1.titre,e.dow,e.id_repeat FROM  `events` e,employe m,module m1,groupe g,unite u where m1.sigle=g.sigle and u.sigle=m1.sigleunite and e.idGroupe=g.idGroupe and g.matriculeEmploye=m.matriculeEmploye and u.idProgramme='" . $p . "' and u.semestre=" . $s . "  and  (date(e.start) >= '$start' AND date(e.start) <= '$end')";
        } elseif ($chek1 == "2") {
            $sql1 = "SELECT  distinct g.sigle, e.`id`, e.`start` ,e.`end` ,e.`title`,e.id_salle as local,e.idGroupe,m.nom as matriculeEmploye,m1.titre,e.dow,e.id_repeat FROM  `events` e,employe m,module m1,groupe g where m1.sigle=g.sigle and e.idGroupe=g.idGroupe and g.matriculeEmploye=m.matriculeEmploye  and g.matriculeEmploye='$employe1'  and  (date(e.start) >= '$start' AND date(e.start) <= '$end')";
        } elseif ($chek1 == "3") {
            $sql1 = "SELECT  distinct g.sigle,e.`id`, e.`start` ,e.`end` ,e.`title`,e.id_salle as local,e.idGroupe,m.nom as matriculeEmploye,m1.titre,e.dow,e.id_repeat FROM  `events` e,employe m,module m1,groupe g where m1.sigle=g.sigle and e.idGroupe=g.idGroupe and g.matriculeEmploye=m.matriculeEmploye  and e.id_salle='" . $local . "' and  (date(e.start) >= '$start' AND date(e.start) <= '$end')";
        }
        $query1 = $this->db->query($sql1);
        if ($query1->num_rows() > 0) {
            foreach ($query1->result_array() as $row) {

                if ($chek1 == "1") {
                    $sql = "SELECT e.`id`, e.`start` ,e.`end` ,e.`title`,e.id_salle as local,e.idGroupe,m.nom as matriculeEmploye,m1.titre,e.dow,e.id_repeat FROM  `events` e,employe m,module m1,groupe g,unite u where m1.sigle=g.sigle and u.sigle=m1.sigleunite and e.idGroupe=g.idGroupe and g.matriculeEmploye=m.matriculeEmploye and u.idProgramme='" . $p . "' and u.semestre=" . $s . " and g.sigle='" . $row['sigle'] . "' and e.start='" . $row['start'] . "' and e.end='" . $row['end'] . "'";
                    //$this->db->insert('r_events', array('test' => $sql));
                } elseif ($chek1 == "2") {
                    $sql = "SELECT   e.`id`, e.`start` ,e.`end` ,e.`title`,e.id_salle as local,e.idGroupe,m.nom as matriculeEmploye,m1.titre,e.dow,e.id_repeat FROM  `events` e,employe m,module m1,groupe g where m1.sigle=g.sigle and e.idGroupe=g.idGroupe and g.matriculeEmploye=m.matriculeEmploye  and g.matriculeEmploye='$employe1' and g.sigle='" . $row['sigle'] . "' and e.start='" . $row['start'] . "' and e.end='" . $row['end'] . "'";
                    // $this->db->insert('r_events', array('test' => $employe1));
                } elseif ($chek1 == "3") {
                    $sql = "SELECT  e.`id`, e.`start` ,e.`end` ,e.`title`,e.id_salle as local,e.idGroupe,m.nom as matriculeEmploye,m1.titre,e.dow,e.id_repeat FROM  `events` e,employe m,module m1,groupe g where m1.sigle=g.sigle and e.idGroupe=g.idGroupe and g.matriculeEmploye=m.matriculeEmploye  and e.id_salle='" . $local . "' and g.sigle='" . $row['sigle'] . "' and e.start='" . $row['start'] . "'  and e.end='" . $row['end'] . "'";
                }
                $query = $this->db->query($sql);
                $salle1 = "";
                $prof1 = "";

                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row1) {
                        $salle1 = $salle1 . $row1['local'] . ",";
                        $prof1 = $prof1 . $row1['matriculeEmploye'] . ",";
                        /* $events['chv'][] = $row['chv'];
                          $events['id'][] = $row['id'];
                          $events['start'][] = $row['start'];
                          $events['end'][] = $row['end'];
                          $events['title'][] = $row['title'];
                          $events['local'][] = $row['local'].",";
                          $events['idGroupe'][] = $row['idGroupe'];
                          $events['matriculeEmploye'][] = $row['matriculeEmploye'];
                          $events['titre'][] = $row['titre'];
                          $events['dow'][] = $row['dow'];
                          $events['id_repeat'][] = $row['id_repeat']; */
                        //$events[] = $row;
                    }
                }
                $events['id'] = $row['id'];
                $events['start'] = $row['start'];
                $events['end'][] = $row['end'];
                $events['title'][] = $row['title'];
                $events['local'][] = $salle1;
                $events['idGroupe'][] = $row['idGroupe'];
                $events['matriculeEmploye'][] = $prof1;
                $events['titre'][] = $row['titre'];
                $events['dow'][] = $row['dow'];
                $events['id_repeat'][] = $row['id_repeat'];
            }
        }
        // $events[] = $row;

        $result['events'] = $events;
        $result['start'] = $start;
        $result['end'] = $end;
        return $result;
    }

    function chevauchement($startTimeM, $endTimeM, $prof, $local2, $id) {
        $sql3 = "select distinct e.id,e.start,e.end,e.id_salle from events e where    (((e.start <= '$startTimeM') and (e.end <= '$endTimeM') and  (e.end > '$startTimeM')) or ((e.start <= '$startTimeM') and ('$endTimeM' <= e.end) )  or  (('$startTimeM' <= e.start) and ('$endTimeM' >= e.end  ))) and e.id!=$id and e.id_salle='" . $local2 . "'";
        $query3 = $this->db->query($sql3);
        //   $this->db->insert('r_events', array('test' => $sql3));
        $result = null;
        if ($query3->num_rows() > 0) {
            foreach ($query3->result_array() as $row) {
                $result[] = $row;
            }
        }
        return $result;
    }

    function chevauchement2($startTimeM, $endTimeM, $prof, $local2, $id) {
        $sql3 = "select distinct e.id,e.start,e.end,e.id_salle from events e,groupe g where g.idGroupe=e.idGroupe and  (((e.start <= '$startTimeM') and (e.end <= '$endTimeM') and  (e.end > '$startTimeM')) or ((e.start <= '$startTimeM') and ('$endTimeM' <= e.end) )  or  (('$startTimeM' <= e.start) and ('$endTimeM' >= e.end  ))) and e.id!=$id  and g.matriculeEmploye='" . $prof . "'";
        $query3 = $this->db->query($sql3);
        // $this->db->insert('r_events', array('test' => $sql3));
        $result = null;
        if ($query3->num_rows() > 0) {
            foreach ($query3->result_array() as $row) {
                $result[] = $row;
            }
        }
        return $result;
    }

    function enregistrer_absences_horaire($post) {
        if(isset($post['matricule'])){
        $tableauMatricule = $post['matricule'];
        //$post['date']
        //$date = $this->convert_date($post['date']);
        $date = $post['date'];
        $absenceMotive='0';
        if(!empty($post['motive'])){
            $absenceMotive='1';
        }
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];
        //print_r($tableauMatricule);
        if ($tableauMatricule != NULL) {
            for ($i = 0; $i < count($tableauMatricule); $i++) {
                
                $info = array(
                    'matricule' => $tableauMatricule[$i],
                    'annee' => $anneeCourante,
                    'semestre' => $semestreCourant,
                    'date' => $date,
                    'periode' => "",
                    'duree' => $post['duree'],
                    'absenceMotivee' => $absenceMotive,
                    'idGroupe' => $post['groupe'],
                    'motive'=>$post['motive']
                );
                $this->db->insert('absences', $info);
            }
        }
        }
    }

    function control_autorisation($num_bac, $annee) {
        $sql3 = "select * from bac_mauritania where annee=" . $annee . " and num_bac='" . $num_bac . "'";
        $query3 = $this->db->query($sql3);
        // $this->db->insert('r_events', array('test' => $sql3));
        $result = null;
        if ($query3->num_rows() > 0) {
            foreach ($query3->result_array() as $row) {
                $result[] = $row;
            }
        }
        return $result;
    }

    function get_groupe_ens($annee, $session, $matricule) {
        $groupe = '';

        // $this->db->where(array('matriculeEmploye' => $matricule, 'annee' => $annee, 'semestre' => $semestre));
        //$this->db->order_by('idGroupe');
        //$res = $this->db->get('groupe');
        $sql = "";
        if ($session == "03") {
            $sql = "select g.idGroupe from groupe g,module m,unite u where g.sigle=m.sigle and u.sigle=m.sigleunite and g.matriculeEmploye='" . $matricule . "' and g.annee=" . $annee . " and u.semestre%2=1";
        } else {
            $sql = "select g.idGroupe from groupe g,module m,unite u where g.sigle=m.sigle and u.sigle=m.sigleunite and g.matriculeEmploye='" . $matricule . "' and g.annee=" . $annee . " and u.semestre%2=0";
        }
        $res = $this->db->query($sql);
        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $groupe[] = $row['idGroupe'];
            }
        }
        return $groupe;
    }

    function get_detail_groupe($idGroupe, $employe) {
        $sql = "SELECT  m.sigle,m.description,m.volumeTD as hrsTD,m.volumeTP as hrsTP,m.volumeCM as hrsCours,m.hrsPerso as volumeProjet,m.titre,u.sigle as idModule,u.titre as module,u.idProgramme,u.semestre,e.* from groupe g,employe e,unite u,module m 
        WHERE m.sigleunite=u.sigle and m.sigle=g.sigle AND g.idGroupe='" . $idGroupe . "' and e.matriculeEmploye='" . $employe . "' and e.matriculeEmploye=g.matriculeEmploye";

        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    function Correspondance() {
        $sql = "SELECT  anc_sigle,nouv_sigle  from correspondances";

        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {

                $q = "update notespartielles  set sigle='" . $row['nouv_sigle'] . "' where sigle='" . $row['anc_sigle'] . "' and matriculeetudiant in ( SELECT matriculeEtudiant FROM `redoublant` WHERE `annee`=2016 and niveau=1) ";
                //$q="update notespartielles  set sigle='".$row['nouv_sigle']."' where sigle='".$row['anc_sigle']."' and matriculeetudiant=15264 ";
                $this->db->query($q);
                $q = "update planetudes set sigle='" . $row['nouv_sigle'] . "' where sigle='" . $row['anc_sigle'] . "'and matriculeetudiant in ( SELECT matriculeEtudiant FROM `redoublant` WHERE `annee`=2016 and niveau=1) ";
                // $q="update planetudes set sigle='".$row['nouv_sigle']."' where sigle='".$row['anc_sigle']."'and matriculeetudiant=15264";
                $this->db->query($q);
            }
            // return $data;
        }
    }

    function get_Niveau_Inscrit_d($matriculeEtudiant, $annee) {
        $sessionCourante = $this->get_session_courante();
        $annee = $sessionCourante['annee_univ'][0];
        $sql = 'SELECT niveau FROM `niveau_inscrits` WHERE `matriculeetudiant`=' . $matriculeEtudiant . ' and annee=' . $annee;


        $res = $this->db->query($sql);
        $niveau = 0;

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $niveau = $row['niveau'];
            }
        }

        return $niveau;
    }

    function is_redoublant($matriculeEtudiant) {
        $sql = 'SELECT * FROM `redoublant` WHERE `matriculeetudiant`=' . $matriculeEtudiant;


        $res = $this->db->query($sql);
        $niveau = 0;

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $niveau = $row['niveau'];
            }
        }

        return $niveau;
    }

    function get_taux_horaire($grade) {
        $sql = 'SELECT concat(c.statu,c.grade) as grade,taux_horaire FROM `categorieenseignant` c WHERE concat(c.statu,c.grade)="' . $grade . '"';


        $res = $this->db->query($sql);
        $grade = null;

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $grade['grade'] = $row['grade'];
                $grade['taux_horaire'] = $row['taux_horaire'];
            }
        }

        return $grade;
    }

    function enseignement() {
        $sessionCourante = $this->get_session_courante_calendar();
        $annee = $sessionCourante['annee'][0];
        $semestre = $sessionCourante['semestre'][0];
        $sql = "SELECT  e.*,e1.nom,e1.prenom from enseignement e,employe e1 where e1.matriculeEmploye=e.matriculeEmploye and e.annee=$annee and e.semestre=$semestre ";

        $query = $this->db->query($sql);


        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    function modifier_heure_enseignement($heureD, $date, $duree, $type, $sigle, $groupe, $matricule, $ancheureD, $ancdate) {
        $q = "update enseignement  set heureD='" . $heureD . "',date='" . $date . "',duree='" . $duree . "',type='" . $type . "' where sigle='" . $sigle . "' and idGroupe='" . $groupe . "' and matriculeEmploye='" . $matricule . "' and heureD='" . $ancheureD . "' and date='" . $ancdate . "' ";
        //  echo $q;
        $this->db->query($q);
    }

    function delete_heure_enseignement($heureD, $date, $duree, $type, $sigle, $groupe, $matricule) {
        $q = "delete from  enseignement   where sigle='" . $sigle . "' and idGroupe='" . $groupe . "' and matriculeEmploye='" . $matricule . "' and heureD='" . $heureD . "' and date='" . $date . "' ";
        //  echo $q;
        $this->db->query($q);
    }

    function get_statut() {
        $sql = 'SELECT * from enseignantstatu';


        $res = $this->db->query($sql);
        $grade = null;

        if ($res->num_rows() > 0) {
            foreach ($res->result_array() as $row) {
                $grade['statut'][] = $row['statu'];
            }
        }

        return $grade;
    }

    function afficher_paiement_heures($dateD, $dateF, $statut) {
        $sql = 'SELECT e.matriculeEmploye,e.duree,e.type from enseignement e,employe a where a.matriculeEmploye=e.matriculeEmploye and a.grade like "' . $statut . '%" and e.date>="' . $dateD . '" and e.date<="' . $dateF . '"';
        $res = $this->db->query($sql);
        $sql1 = 'SELECT distinct  e.matriculeEmploye,c.taux_horaire,concat(a.prenom," ",a.nom) as nomprenom,a.compteBancaire,a.NIN,a.banque   from `categorieenseignant` c, enseignement e,employe a where a.matriculeEmploye=e.matriculeEmploye and  e.date>="' . $dateD . '" and e.date<="' . $dateF . '" and  concat(c.statu,c.grade)=a.grade and  a.grade like "' . $statut . '%"';
        $res1 = $this->db->query($sql1);
        $info = null;
        // $info['cm']=0;
        // $info['td']=0;
        // $info['tp']=0;
        //$info['eq']=0;
        // $info['montant']=0;

        if ($res1->num_rows() > 0) {
            foreach ($res1->result_array() as $row1) {
                $info[$row1['matriculeEmploye']]['total'] = 0;
                $info[$row1['matriculeEmploye']]['montant'] = 0;
                $info[$row1['matriculeEmploye']]['dureeCM'] = 0;
                $info[$row1['matriculeEmploye']]['dureeTD'] = 0;
                $info[$row1['matriculeEmploye']]['dureeTP'] = 0;
                if ($res->num_rows() > 0) {
                    foreach ($res->result_array() as $row) {
                        if ($row['matriculeEmploye'] == $row1['matriculeEmploye']) {

                            if ($row['type'] == 'cours') {
                                $info[$row1['matriculeEmploye']]['dureeCM'] += $row['duree'];
                            }
                            // $info[$row['matriculeEmploye']]['dureeTD']=0;
                            if ($row['type'] == 'td') {
                                $info[$row1['matriculeEmploye']]['dureeTD'] += $row['duree'];
                            }
                            // $info[$row['matriculeEmploye']]['dureeTP']=0;
                            if ($row['type'] == 'tp') {
                                $info[$row1['matriculeEmploye']]['dureeTP'] += $row['duree'];
                            }
                        }
                    }
                }

                $info[$row1['matriculeEmploye']]['compteBancaire'] = $row1['compteBancaire'];
                $info[$row1['matriculeEmploye']]['taux_horaire'] = $row1['taux_horaire'];
                $info[$row1['matriculeEmploye']]['nomprenom'] = $row1['nomprenom'];
                $info[$row1['matriculeEmploye']]['nomprenom'] = $row1['nomprenom'];
                $info[$row1['matriculeEmploye']]['NIN'] = $row1['NIN'];
                $info[$row1['matriculeEmploye']]['banque'] = $row1['banque'];
                $info[$row1['matriculeEmploye']]['total'] = $info[$row1['matriculeEmploye']]['dureeCM'] + ($info[$row1['matriculeEmploye']]['dureeTD'] * (2 / 3)) + ($info[$row1['matriculeEmploye']]['dureeTP'] * (1 / 2));
                $info[$row1['matriculeEmploye']]['montant'] = $info[$row1['matriculeEmploye']]['total'] * $row1['taux_horaire'];
                //$info['cm']+=$info[$row1['matriculeEmploye']]['dureeCM'];
                //$info['td']+=$info[$row1['matriculeEmploye']]['dureeTD'];
                // $info['tp']+=$info[$row1['matriculeEmploye']]['dureeTP'];
                //$info['eq']+=$info[$row1['matriculeEmploye']]['total'];
                //$info['montant']+=$info[$row1['matriculeEmploye']]['montant'];
            }
        }

        return $info;
    }

    function get_employe_paiement($dateD, $dateF, $statut) {
        $sql1 = 'SELECT distinct  e.matriculeEmploye  from `categorieenseignant` c, enseignement e,employe a where a.matriculeEmploye=e.matriculeEmploye and  e.date>="' . $dateD . '" and e.date<="' . $dateF . '" and  concat(c.statu,c.grade)=a.grade and  a.grade like "' . $statut . '%"';
        $res1 = $this->db->query($sql1);
        $info = null;

        if ($res1->num_rows() > 0) {
            foreach ($res1->result_array() as $row) {
                $info[] = $row;
            }
        }

        return $info;
    }

    function getDetailEvent($matriculeEmploye, $date, $heureD) {
        $d = $date . " " . $heureD . ":00:00";
        //   $date=date('Y-m-d h:m:s',  strtotime($d));
        $sql1 = 'SELECT   e.dow as type,a.matriculeEmploye  from `groupe` g, events e,employe a where g.matriculeEmploye=a.matriculeEmploye and g.idGroupe=e.idGroupe and  g.matriculeEmploye="' . $matriculeEmploye . '" and  e.start="' . $d . '" ';
        $res1 = $this->db->query($sql1);
        $info = null;

        if ($res1->num_rows() > 0) {
            foreach ($res1->result_array() as $row) {
                $info[] = $row;
            }
        }

        return $info;
    }

    /*
      function get_employe_suivi($dateD,$dateF){

      $sql = 'SELECT e.matriculeEmploye,a.idDepartement,concat(a.prenom," ",a.nom) as nom,e.HP,e.HE,e.No from heueres_prv_eff e,semainssemestre s,employe a where a.matriculeEmploye=e.matriculeEmploye and s.No=e.No and s.start>="'.$dateD.'" and s.end<="'.$dateF.'"  ';
      // echo $sql;
      $res = $this->db->query($sql);

      $res = $this->db->query($sql);

      $info =null;

      if ($res->num_rows() > 0)
      {

      // $k=0;
      foreach ($res->result_array() as $row) {
      //   $k++;
      $info[$row['matriculeEmploye']]['THP']=0;
      $info[$row['matriculeEmploye']]['THE']=0;
      $info[$row['matriculeEmploye']]['ABS']=0;
      $info[$row['matriculeEmploye']]['pourc']=0;
      $info[$row['matriculeEmploye']]['idDepartement']=$row['idDepartement'];
      $info[$row['matriculeEmploye']]['nom']=$row['nom'];
      $info[$row['matriculeEmploye']]['HP'][]=$row['HP'];
      $info[$row['matriculeEmploye']]['THP']+=$row['HP'];
      $info[$row['matriculeEmploye']]['HE'][]=$row['HE'];
      $info[$row['matriculeEmploye']]['THE']+=$row['HE'];
      $info[$row['matriculeEmploye']]['ABS']=$info[$row['matriculeEmploye']]['THP']-$info[$row['matriculeEmploye']]['THE'];
      $info[$row['matriculeEmploye']]['pourc']=($info[$row['matriculeEmploye']]['ABS']/ $info[$row['matriculeEmploye']]['THP'])*100;
      // $info[$row['matriculeEmploye']]['NB']=$row['nb'];;
      }
      }
      return $info;

      } */

    function get_nb_sem_suivi($dateD, $dateF) {
        $sql1 = 'SELECT (max(s.No)-min(s.No)+1) as nb from heueres_prv_eff e,semainssemestre s,employe a where a.matriculeEmploye=e.matriculeEmploye and s.No=e.No and s.start>="' . $dateD . '" and s.end<="' . $dateF . '"   ';
     //   echo $sql1;
        $info = null;
        $res1 = $this->db->query($sql1);
        if ($res1->num_rows() > 0) {
            foreach ($res1->result_array() as $row1) {
                $info = $row1['nb'];
            }
        }
        return $info;
    }

    function get_employe_suivi($dateD, $dateF) {





        $info = null;
        $sql2 = 'SELECT distinct e.matriculeEmploye from heur_prevu_semaine e,semainssemestre s where  s.No=e.No and s.start>="' . $dateD . '" and s.end<="' . $dateF . '" order by s.No asc ';
        // echo $sql;
        $res2 = $this->db->query($sql2);
        if ($res2->num_rows() > 0) {
            $k = 0;
            foreach ($res2->result_array() as $row2) {
                $sql = 'SELECT e.matriculeEmploye,a.idDepartement,concat(a.prenom,"  ",a.nom) as nom,e.duree,e.No from heur_prevu_semaine e,semainssemestre s,employe a where a.matriculeEmploye=e.matriculeEmploye and s.No=e.No and e.matriculeEmploye="' . $row2['matriculeEmploye'] . '" and s.start>="' . $dateD . '" and s.end<="' . $dateF . '" order by s.No asc ';
                // echo $sql;
                $info[$row2['matriculeEmploye']]['THP'] = 0;
                $res = $this->db->query($sql);
                if ($res->num_rows() > 0) {
                    $k = 0;
                    foreach ($res->result_array() as $row) {
                        if ($row['matriculeEmploye'] == $row2['matriculeEmploye']) {
                            $k++;
                            //     if(($row['matriculeEmploye']==$row1['matriculeEmploye'])and ($row['No']==$row1['No']) ){
                            // $info[$row['matriculeEmploye']]['THP']=0;
                            // $info[$row['matriculeEmploye']]['THE']=0;
                            //  $info[$row['matriculeEmploye']]['ABS']=0;
                            //  $info[$row['matriculeEmploye']]['pourc']=0;
                            $info[$row['matriculeEmploye']]['idDepartement'] = $row['idDepartement'];
                            $info[$row['matriculeEmploye']]['nom'] = $row['nom'];
                            $info[$row['matriculeEmploye']]['HP'][] = $row['duree'];
                            $info[$row2['matriculeEmploye']]['THP'] += $row['duree'];
                            //$info[$row['matriculeEmploye']]['HE'][]=$row1['duree'];
                            // $info[$row['matriculeEmploye']]['THE']+=$row1['duree'];
                            // $info[$row['matriculeEmploye']]['ABS']=$info[$row['matriculeEmploye']]['THP']-$info[$row['matriculeEmploye']]['THE'];
                            // $info[$row['matriculeEmploye']]['pourc']=($info[$row['matriculeEmploye']]['ABS']/ $info[$row['matriculeEmploye']]['THP'])*100;
                            $info[$row['matriculeEmploye']]['NB'] = $k;
                        }
                    }
                    // }
                }
            }
        }
        $sql3 = 'SELECT distinct  e.matriculeEmploye from heurs_eff_sem e,semainssemestre s where  s.No=e.No and s.start>="' . $dateD . '" and s.end<="' . $dateF . '"  order by s.No asc  ';
        // echo $sql;
        $res3 = $this->db->query($sql3);
        if ($res3->num_rows() > 0) {

            // $k=0;
            foreach ($res3->result_array() as $row3) {
                $sql1 = 'SELECT e.matriculeEmploye,a.idDepartement,concat(a.prenom," ",a.nom) as nom,e.duree,e.No from heurs_eff_sem e,semainssemestre s,employe a where a.matriculeEmploye=e.matriculeEmploye and s.No=e.No and e.matriculeEmploye="' . $row3['matriculeEmploye'] . '" and s.start>="' . $dateD . '" and s.end<="' . $dateF . '"  order by s.No asc  ';
                // echo $sql;
                $info[$row3['matriculeEmploye']]['THE'] = 0;
                $res1 = $this->db->query($sql1);
                if ($res1->num_rows() > 0) {

                    // $k=0;
                    foreach ($res1->result_array() as $row1) {

                        //     if(($row['matriculeEmploye']==$row1['matriculeEmploye'])and ($row['No']==$row1['No']) ){
                        /// $info[$row['matriculeEmploye']]['THP']=0;
                        if ($row1['matriculeEmploye'] == $row3['matriculeEmploye']) {
                            //  $info[$row['matriculeEmploye']]['ABS']=0;
                            //  $info[$row['matriculeEmploye']]['pourc']=0;
                            $info[$row1['matriculeEmploye']]['idDepartement'] = $row1['idDepartement'];
                            $info[$row1['matriculeEmploye']]['nom'] = $row1['nom'];
                            $info[$row1['matriculeEmploye']]['HE'][] = $row1['duree'];
                            // $info[$row1['matriculeEmploye']]['THE']+=$row1['duree'];
                            //$info[$row['matriculeEmploye']]['HE'][]=$row1['duree'];
                            $info[$row3['matriculeEmploye']]['THE'] += $row1['duree'];
                            // $info[$row['matriculeEmploye']]['ABS']=$info[$row['matriculeEmploye']]['THP']-$info[$row['matriculeEmploye']]['THE'];
                            // $info[$row['matriculeEmploye']]['pourc']=($info[$row['matriculeEmploye']]['ABS']/ $info[$row['matriculeEmploye']]['THP'])*100;
                            //$info[$row['matriculeEmploye']]['NB']=$k;
                        }
                    }
                }
            }
        }
        return $info;
    }
    /*
        Alioune Zeyn 20/06/2019 AZDEBUT ajout d'un nouveau planning
     *      */
    //verification de l'existance du planning
    function planning_exists($annee,$semestre,$session){
        $requete="SELECT COUNT(id) as nb FROM planing WHERE annee='$annee' and semestre='$semestre' and session='$session'";
        //echo $requete;
        $query = $this->db->query($requete);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                if($row['nb']>0){
                    return true;
                }else {
                    return false;
                }
            }
        }else return false;
    }
    //ajout d'un planning et insertion de ses jours
    function ajouter_planning($annee,$semestre,$session,$jours){
        //insertion du nouveau planning
        $this->db->insert('planing',array('annee'=>$annee,'semestre'=>$semestre,'session'=>$session));
        //selection de idPlanning d
        $select_idPlanning = $this->db->get_where('planing', array('annee'=>$annee,'semestre'=>$semestre,'session'=>$session));
        //inintialisation de idPlanning pour verifier son existance
        $idPlanning=-1;
        foreach ($select_idPlanning->result() as $row)
        {
            $idPlanning=$row->id;
        }
        //s'il n'y a pas de planning
        if($idPlanning==-1){
            return false;
        }else{ // s'il y a idPlanning 
            //insertion des jours du planning
            $jours_planning=array();
            for($i=0;$i<count($jours);$i++){
                $jours_planning[$i]['idPlaning']=$idPlanning;
                $jours_planning[$i]['date']=$jours[$i];
            }
            $this->db->insert_batch('planningjournee',$jours_planning);
            return true;
        }
    }
    /* end Alioune AZFIN*/
    
    function get_crenau() {
        $crenau = NULL;
        $query = $this->db->query("SELECT  `id`, `heurD`,duree FROM `creneau` ORDER BY `heurD`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $crenau['id'][] = $row['id'];
                $crenau['heurD'][] = $row['heurD'];
                $crenau['duree'][] = $row['duree'];
            }
        }
        return $crenau;
    }

    function get_planningjournee($planning) {
        $planningjournee = NULL;
        $query = $this->db->query("SELECT  `id`, `date` FROM `planningjournee` where idPlaning='" . $planning . "'  ORDER BY `date`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $planningjournee['id'][] = $row['id'];
                $planningjournee['date'][] = $row['date'];
            }
        }
        return $planningjournee;
    }

    function get_planing() {
        $planing = NULL;
        $query = $this->db->query("SELECT  `id`, `annee`, `semestre`, `session` FROM `planing` ORDER BY `annee`,`semestre`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $planing['id'][] = $row['id'];
                $planing['annee'][] = $row['annee'];
                $planing['semestre'][] = $row['semestre'];
                $planing['session'][] = $row['session'];
            }
        }
        return $planing;
    }
        
    function get_planing_by_id($idPlaning) {
        $planing = NULL;
        $query = $this->db->query("SELECT  `id`, `annee`, `semestre`, `session` FROM `planing` where id= '$idPlaning'  ORDER BY `annee`,`semestre`");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $planing['id'][] = $row['id'];
                $planing['annee'][] = $row['annee'];
                $planing['semestre'][] = $row['semestre'];
                $planing['session'][] = $row['session'];
            }
        }
        return $planing;
    }
    function m_planning_exam($planing) {
        $sql = "SELECT  e.*,concat(c.heurD,' ',c.duree) as crenau,concat(p.annee,' ',p.semestre,' ',p.session) as planning,j.date as journee,m.titre,u.idProgramme,u.semestre  from planningexam e,planningjournee j,creneau c,planing p,module m,unite u where u.sigle=m.sigleunite and m.sigle=e.sigle and e.idPlaning=$planing and e.idPlaning=p.id and e.idJournee= j.id and e.idCrenau=c.id";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    function planning_exam() {
        $sql = "SELECT  p.id,concat(p.annee,' ',p.semestre,' ',p.session) as planning from planing p ";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    function planning_examens($idProgramme, $planing) {
        $sessionCourante = $this->scolarite_modele->get_session_courante_calendar();
        $anneeC = $sessionCourante['annee'][0];
        $semestreC = $sessionCourante['semestre'][0];
        $sql = "";
        $sql_sess = "select session from planing where id = " . $planing;
        $query = $this->db->query($sql_sess);
        $session = "CC";
        foreach ($query->result_array() as $row) {
            $session = $row['session'];
        }
        if ($session == 'RT')
            $session = 2;
        else
            $session = 1;
        if ($idProgramme == "tous") {

            $sql = "select * from(SELECT concat(a.prenom,' ',a.nom)as nom,a.telephone1,p.sigle,p.heurD as heurD,p.idProgramme,p.titre,p.semestre,p.date as date,p.idJournee,p.idPlaning,n.nbre from plannig_exam_bisbis p   join  Nbre_etu_element n  join  employe a join groupe g  on  p.sigle=n.sigle and a.matriculeEmploye=g.matriculeEmploye and g.sigle=p.sigle and g.idGroupe like '%$anneeC$semestreC%' and p.idPlaning='" . $planing . "' and n.sess =" . $session . "  union SELECT c.heurD as nom,c.heurD as telephone1,'' as `sigle`,c.`heurD`,v.`idProgramme`,c.heurD as `titre`,c.heurD as `semestre`,j.`date`,v.`idJournee`,v.`idPlaning`,0 as nbre FROM `creneau_vide` v,creneau c,planningjournee j WHERE v.idCrenau=c.id and j.id=v.idJournee and v.idPlaning='" . $planing . "') a order by date,heurD,idProgramme asc";



            // echo $sql;
        } else {
            $sql = "select * from(SELECT concat(a.prenom,' ',a.nom)as nom,a.telephone1,p.sigle,p.heurD as heurD,p.idProgramme,p.titre,p.semestre,p.date as date,p.idJournee,p.idPlaning,n.nbre from plannig_exam_bisbis p   join  Nbre_etu_element n  join  employe a join groupe g  on  p.sigle=n.sigle and a.matriculeEmploye=g.matriculeEmploye and g.sigle=p.sigle and g.idGroupe like '%$anneeC$semestreC%' and p.idPlaning='" . $planing . "' and p.idProgramme='" . $idProgramme . "' and n.sess =" . $session . "  union SELECT c.heurD as nom,c.heurD as telephone1,'' as `sigle`,c.`heurD`,v.`idProgramme`,c.heurD as `titre`,c.heurD as `semestre`,j.`date`,v.`idJournee`,v.`idPlaning`,0 as nbre FROM `creneau_vide` v,creneau c,planningjournee j WHERE v.idCrenau=c.id and j.id=v.idJournee and v.idPlaning='" . $planing . "' and v.idProgramme='" . $idProgramme . "') a order by date,heurD,idProgramme asc";

            // echo $sql;
        }
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            $data = array();
            $i = 0;
            foreach ($query->result_array() as $row) {
                $data[$row['idJournee']][$row['idProgramme']]['sigle'][] = $row['sigle'];
                $data[$row['idJournee']][$row['idProgramme']]['heurD'][] = $row['heurD'];
                $data[$row['idJournee']][$row['idProgramme']]['idProgramme'][] = $row['idProgramme'];
                $data[$row['idJournee']][$row['idProgramme']]['titre'][] = $row['titre'];
                $data[$row['idJournee']][$row['idProgramme']]['nbre'][] = $row['nbre'];
                $data[$row['idJournee']][$row['idProgramme']]['semestre'][] = $row['semestre'];
                $data[$row['idJournee']][$row['idProgramme']]['nom'][] = $row['nom'];
                $data[$row['idJournee']][$row['idProgramme']]['telephone'][] = $row['telephone1'];
            }
            return $data;
            //print_r($data);
        }
    }

    function planning_totaux($idProgramme, $planing) {
        $sql = "";
        $sql_sess = "select session from planing where id = " . $planing;
        $query = $this->db->query($sql_sess);
        $session = "CC";
        foreach ($query->result_array() as $row) {
            $session = $row['session'];
        }
        if ($session == 'RT')
            $session = 2;
        else
            $session = 1;
        $sql = "";
        if ($idProgramme == "tous") {
            $sql = "SELECT p.heurD,p.idProgramme,p.date,p.idJournee AS idJournee1,p.idPlaning,sum(n.nbre) as totaux from plannig_exam_bisbis p left join  Nbre_etu_element  n on  p.sigle=n.sigle and n.sess =" . $session . "  group by  p.heurD,p.idJournee order by  p.heurD asc ";

            //  echo $sql;
        } else {
            $sql = "SELECT p.heurD,p.idProgramme,p.date,p.idJournee AS idJournee1,p.idPlaning,sum(n.nbre) as totaux from plannig_exam_bisbis p left join Nbre_etu_element  n on p.sigle=n.sigle  and p.idProgramme='" . $idProgramme . "' and n.sess =" . $session . " group by  p.heurD,p.idJournee order by  p.heurD asc ";

            // echo $sql;
        }
        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            $data = null;
            foreach ($query->result_array() as $row) {   //$data[$row['idJournee']]=array();
                $data[$row['idJournee1']]['totaux'][] = $row['totaux'];
                $data[$row['idJournee1']]['date'] = $row['date'];
                // $data['info']['totaux'][] = $row['totaux'];
            }
            return $data;
        }
    }

    function delete_palnning_exam($id) {
        $sql = "delete from planningexam where id=" . $id;
        // echo $sql;
        $query = $this->db->query($sql);
    }

    function avancement_enseignement($annee, $session, $idProgramme) {
        $semestre = 3;
        if ($session == "03") {
            $semestre = 3;
        } else {
            $semestre = 1;
        }
        $sql = "SELECT e.`sigle`,m.titre,sum(e.duree) as heureE, m.`volumeCM`+m.`volumeTD`+m.`volumeTP` as heureP, u.semestre,u.idProgramme FROM `enseignement` e,module m,unite u,infomodule_element i WHERE u.sigle=m.sigleunite and m.sigle=e.sigle and m.sigle=i.sigle and e.annee=$annee and e.semestre=$semestre and u.idProgramme='" . $idProgramme . "'   group by e.sigle order by u.semestre";
        // echo $sql;
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {
            $data = null;
            foreach ($query->result_array() as $row) {   //$data[$row['idJournee']]=array();
                // $data['semestre'] = $row['semestre'];
                $data[$row['semestre']][$row['sigle']]['sigle'] = $row['sigle'];
                $data[$row['semestre']][$row['sigle']]['titre'] = $row['titre'];
                $data[$row['semestre']][$row['sigle']]['heureP'] = $row['heureP'];
                $data[$row['semestre']][$row['sigle']]['heureE'] = $row['heureE'];
                ///$data[$row['semestre']]['TheureE'] += $row['heureE'];
                // $data['info']['totaux'][] = $row['totaux'];
                //  $data[] = $row;
            }
            return $data;
        }
    }

    function correspondance_elements() {
        $elements = "";
        $query = $this->db->query("SELECT  `id`, `anc_sigle`,nouv_sigle FROM `correspondance` ");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $elements[] = $row;
                //  $elements['anc_sigle'][] = $row['anc_sigle'];
                //  $elements['nouv_sigle'][] = $row['nouv_sigle'];
            }
        }
        return $elements;
    }

    function pv_fraudes() {
        $elements = "";
        $query = $this->db->query("SELECT  p.*,m.titre FROM `pv_fraudes` p,module m where m.sigle=p.sigle ");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $elements[] = $row;
                //  $elements['anc_sigle'][] = $row['anc_sigle'];
                //  $elements['nouv_sigle'][] = $row['nouv_sigle'];
            }
        }
        return $elements;
    }

    function ajouter_correspondance($anc_sigle, $nouv_sigle) {
        $q = "insert into correspondance (anc_sigle,nouv_sigle)  values ('" . $anc_sigle . "','" . $nouv_sigle . "') ";
        $this->db->query($q);
        //  $this->db->insert("r_events", array('test'=>$q));
    }

    function delete_correspondance($id) {
        $sql = "delete from correspondance where id=" . $id;
        // echo $sql;
        $query = $this->db->query($sql);
    }

    function ajouter_pv_fraudes($matriculeetudiant, $sigle, $semestre, $annee, $session, $note, $statu) {
        $sem = 3;
        if (($semestre % 2) == 0) {
            $sem = 1;
        } else {
            $sem = 3;
        }
        $q = "insert into pv_fraudes (matriculeEtudiant,	sigle,idEvaluation,note,semestre,annee,date_creation,statu)  values ('" . $matriculeetudiant . "','" . $sigle . "','" . $session . "','" . $note . "','" . $sem . "','" . $annee . "',CURDATE(),$statu) ";
        $this->db->query($q);
        if ($statu == 1) {
            $q1 = "update notespartielles set note=0 where  matriculeEtudiant='" . $matriculeetudiant . "' and idEvaluation=$session and sigle='" . $sigle . "' and semestre= $sem and annee=$annee";
            $this->db->query($q1);
        }
        //  $this->db->insert("r_events", array('test'=>$q));
    }

    function delete_pv_fraudes($id) {
        $sql = "delete from pv_fraudes where id=" . $id;
        // echo $sql;
        $query = $this->db->query($sql);
    }

    function modifier_pv_fraudes($id, $note, $session, $statu) {
        if ($statu == "1") {
            $q = "update pv_fraudes set idEvaluation=$session,note=$note,date_annulation=CURDATE(),statu=$statu  where id=" . $id;
            $this->db->query($q);
            $query = $this->db->query("select * from pv_fraudes where id=" . $id);
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $q1 = "update notespartielles set note=0 where  matriculeEtudiant='" . $row['matriculeEtudiant'] . "' and idEvaluation=" . $row['idEvaluation'] . " and sigle='" . $row['sigle'] . "' and semestre=" . $row['semestre'] . " and annee=" . $row['annee'];
                    $this->db->query($q1);
                }
            }
        } else {
            $q = "update pv_fraudes set idEvaluation=$session,note=$note,date_annulation=CURDATE(),statu=$statu   where id=" . $id;
            $this->db->query($q);
            $query = $this->db->query("select * from pv_fraudes where id=" . $id);
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $q1 = "update notespartielles set note='" . $row['note'] . "' where  matriculeEtudiant='" . $row['matriculeEtudiant'] . "' and idEvaluation=" . $row['idEvaluation'] . " and sigle='" . $row['sigle'] . "' and semestre=" . $row['semestre'] . " and annee=" . $row['annee'];
                    $this->db->query($q1);
                }
            }
        }
        //  $this->db->insert("r_events", array('test'=>$q));
    }

    function get_date_courante_calendar() {
        $date_courante = NULL;
        $query = $this->db->get('sessionCourante_calendar');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            $date_courante['debutCours'] = $row['debutCours'];
            $date_courante['finCours'] = $row['finCours'];
            $date_courante['annee'] = $row['annee'];
            $date_courante['semestre'] = $row['semestre'];
        }
        return $date_courante;
    }

    function get_session_courante_calendar() {
        $session_courante = NULL;
        $query = $this->db->query("SELECT DISTINCT `annee`, `semestre`, `annee_univ`,`semestre_reel`,debutCours,finCours FROM sessionCourante_calendar");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $session_courante['annee'][] = $row['annee'];
                $session_courante['semestre'][] = $row['semestre'];
                $session_courante['semestre_reel'][] = $row['semestre_reel'];
                $session_courante['annee_univ'][] = $row['annee_univ'];
                $session_courante['debutCours'][] = $row['debutCours'];
                $session_courante['finCours'][] = $row['finCours'];
            }
        }
        return $session_courante;
    }

    function getpvFraude($matricules, $semestre, $annee, $evaluation) {

        $data = array();

        $str = "select * from pv_fraudes  where matriculeEtudiant  in (" .
                $matricules . ") and statu=1 and  idEvaluation=" . $evaluation . " and  semestre = " . $semestre . " and annee = " . $annee;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                $data['sigle'][] = $row['sigle'];
                $data['idEvaluation'][] = $row['idEvaluation'];
                $data['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                $data['note'][] = $row['note'];
            }
        }
        // echo 'getsemestreresult_bis'.$matriculeEtudiant;
        return $data;
    }

    function getpvAbsence($matricules, $semestre, $annee, $evaluation) {

        $data = array();

        $str = "select * from notespartielles  where matriculeEtudiant  in (" .
                $matricules . ") and  idEvaluation=" . $evaluation . "  and note='-1' and semestre = " . $semestre . " and annee = " . $annee;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                $data['sigle'][] = $row['sigle'];
                $data['idEvaluation'][] = $row['idEvaluation'];
                $data['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                $data['note'][] = $row['note'];
            }
        }
        // echo 'getsemestreresult_bis'.$matriculeEtudiant;
        return $data;
    }

    function getNotezero($matricules, $semestre, $annee, $evaluation) {

        $data = array();

        $str = "select * from pv_fraudes  where matriculeEtudiant  in (" .
                $matricules . ") and  idEvaluation=" . $evaluation . " and note=0 and semestre = " . $semestre . " and annee = " . $annee;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                $data['sigle'][] = $row['sigle'];
                $data['idEvaluation'][] = $row['idEvaluation'];
                $data['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                $data['note'][] = $row['note'];
            }
        }
        // echo 'getsemestreresult_bis'.$matriculeEtudiant;
        return $data;
    }

    function getElement_ratt($matricules, $semestre, $annee, $evaluation) {

        $data = array();

        $str = "select * from notespartielles  where matriculeEtudiant  in (" .
                $matricules . ") and  idEvaluation=" . $evaluation . " and note is not null and semestre = " . $semestre . " and annee = " . $annee;
        //  echo $str;
        $query = $this->db->query($str);
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {
                $data['sigle'][] = $row['sigle'];
                $data['idEvaluation'][] = $row['idEvaluation'];
                $data['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                $data['note'][] = $row['note'];
            }
        }
        // echo 'getsemestreresult_bis'.$matriculeEtudiant;
        return $data;
    }

    function afficher_fiches_suivi($idProgramme, $semestre) {
        $data = array();

        $sql = "SELECT e.`idGroupe`,g.matriculeEmploye,m.grade FROM `events` e,groupe g,employe m ,module m1,unite u,sessioncourante_calendar s WHERE g.sigle=m1.sigle and u.sigle=m1.sigleunite and u.idProgramme='" . $idProgramme . "' and u.semestre='" . $semestre .
                "' and m.matriculeEmploye=g.matriculeEmploye and g.idGroupe=e.idGroupe and e.start>=s.debutCours and e.end<=s.finCours ";
        //  echo $sql;
        $query = $this->db->query($sql);
        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $row) {

                $sessionCourante = $this->scolarite_modele->get_session_courante_calendar();
                $anneeCourante = $sessionCourante['annee'][0];
                $semestreCourant = $sessionCourante['semestre'][0];
                //tous les etudiants inscrits dans un groupe specifie
                $start = date('Y-m-d', strtotime('-1 days', strtotime("")));
                $strtotime = date("o-\WW");
                $end = date('Y-m-d', strtotime('+6 days', strtotime($strtotime)));
                $data[$row['idGroupe']]['listeH'] = $this->scolarite_modele->getListeHeuresEnseignement_element($row['idGroupe'], $start, $end, $row['matriculeEmploye']);
                //  print_r($data);
                $data[$row['idGroupe']]['detail'] = $this->scolarite_modele->get_detail_groupe($row['idGroupe'], $row['matriculeEmploye']);
                //  echo $data['detail']['groupe'];
                $data[$row['idGroupe']]['taux_horaire'] = $this->scolarite_modele->get_taux_horaire($row['grade']);
                $data[$row['idGroupe']]['etudiant'] = $this->scolarite_modele->get_etudiants_groupe($row['idGroupe']);
                //   $data[$row['idGroupe']]['annee'] = $anneeCourante;
                ///  $data[$row['idGroupe']]['session'] = $semestreCourant;
                $debutCours = $sessionCourante['debutCours'][0];
                ;
                $data[$row['idGroupe']]['semaine'] = "Periode du  " . date('d/m/Y', strTotime($debutCours)) . " au " . date('d/m/Y', strTotime($end)) . "";
                //$data['idGroupe'][] = $row['idGroupe'];
            }
        }
        // echo 'getsemestreresult_bis'.$matriculeEtudiant;
        return $data;
    }

    function passage_niveau($annee, $niveau) {
        // echo "gh";
        if ($niveau == "1") {
            ///          echo "gh";
///          print_r($annee." ".$regle." ".$niveau);
            $del = "delete  from passage_t where  annee=$annee and matriculeetudiant in (select matriculeetudiant from l1  where ((annee=$annee and semestre=3)or (annee=$annee+1 and semestre=1) ) and matriculeetudiant not in(select matriculeetudiant from credit_valide_sem where semestre in (3,4,5,6))  )";
///          ECHO $del;
            $l = $this->db->query($del);

            if ($annee < 2017) {
                
                $sql = "select matriculeetudiant from l1  where ((annee=$annee and semestre=3)or (annee=$annee+1 and semestre=1) ) and matriculeetudiant not in(select matriculeetudiant from credit_valide_sem where semestre in (3,4,5,6))  ";
                $query = $this->db->query($sql);
                $etu = "";
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        $etu[] = $row['matriculeetudiant'];
                    }
                }
                $list_e = '';
                for ($i = 0; $i < count($etu);  ++$i) {
                    $list_e = $list_e . $etu[$i] . ',';
                }
                $list_e = $list_e . $etu[count($etu) - 1];
                $info = $this->get_moyenne_niveau_decision($list_e);
                // print_r($info);
                $sql1 = "insert into passage_t values";
                foreach ($info as $matriculeEtudiant => $infos) {
                    if (($infos['MGL1'] / 2) >= 10 and $infos['ECTSL1'] >= 30) {
                        $sql1 = $sql1 . "(" . $matriculeEtudiant . ",2," . $annee . "," . $infos['ECTSL1'] . "," . ($infos['MGL1'] / 2) . ",1),";
                        // $this->db->query($sql1);
                    } else {
                        $sql1 = $sql1 . "(" . $matriculeEtudiant . ",1," . $annee . "," . $infos['ECTSL1'] . "," . ($infos['MGL1'] / 2) . ",1),";
                        //$this->db->query($sql1);
                    }
                }
                $sql2 = substr($sql1, 0, -1);
                $this->db->query($sql2);
            } else {
                $sql = "select matriculeetudiant from l1  where ((annee=$annee and semestre=3)or (annee=$annee+1 and semestre=1) ) and matriculeetudiant not in(select matriculeetudiant from credit_valide_sem where semestre in (3,4,5,6))  ";
                $query = $this->db->query($sql);
                $etu = "";
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        $etu[] = $row['matriculeetudiant'];
                    }
                }
                $list_e = '';
                for ($i = 0; $i < count($etu);  ++$i) {
                    $list_e = $list_e . $etu[$i] . ',';
                }
                $list_e = $list_e . $etu[count($etu) - 1];
                $info = $this->get_moyenne_niveau_decision($list_e);
                //  print_r($info);
                $sql1 = "insert into passage_t values";
                foreach ($info as $matriculeEtudiant => $infos) {
                    // echo $matriculeEtudiant." gg";
                    if (($infos['MGL1'] / 2) >= 10 and $infos['ECTSL1'] >= 39) {
                        $sql1 = $sql1 . "(" . $matriculeEtudiant . ",2," . $annee . "," . $infos['ECTSL1'] . "," . ($infos['MGL1'] / 2) . ",2),";

                    } else {
                        $sql1 = $sql1 . "(" . $matriculeEtudiant . ",1," . $annee . "," . $infos['ECTSL1'] . "," . ($infos['MGL1'] / 2) . ",2),";
                        // $this->db->query($sql1);
                    }
                }

                $sql2 = substr($sql1, 0, -1);
                $this->db->query($sql2);
            }
        } elseif ($niveau == "2") {
            $test = "delete  from passage_t where  annee=$annee  and matriculeetudiant in(select matriculeetudiant from l2  where ((annee=$annee and semestre=3)or (annee=$annee+1 and semestre=1) ) and matriculeetudiant not in(select matriculeetudiant from credit_valide_sem where semestre in (5,6)) )";
            $this->db->query($test);
            if ($annee <= 2017) {

                $sql = "select matriculeetudiant from l2  where annee=$annee and semestre=3 and matriculeetudiant not in(select matriculeetudiant from credit_valide_sem where semestre in (5,6))  ";
                $query = $this->db->query($sql);
                $etu = "";
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        $etu[] = $row['matriculeetudiant'];
                    }
                }
                $list_e = '';
                for ($i = 0; $i < count($etu);  ++$i) {
                    $list_e = $list_e . $etu[$i] . ',';
                }
                $list_e = $list_e . $etu[count($etu) - 1];
                $info = $this->get_moyenne_niveau_decision($list_e);
                //   print_r($info);
                $sql1 = " insert into passage_t values";
                foreach ($info as $matriculeEtudiant => $infos) {
                    if (($infos['ECTSL1']) + ($infos['ECTSL2']) >= 90) {
                        // echo "hh";
                        $sql1 = $sql1 . "(" . $matriculeEtudiant . ",3," . $annee . "," . $infos['ECTSL2'] . "," . ($infos['MGL2'] / 2) . ",1),";
                        //$this->db->query($sql1);
                    } else {
                        $sql1 = $sql1 . "(" . $matriculeEtudiant . ",2," . $annee . "," . $infos['ECTSL2'] . "," . ($infos['MGL2'] / 2) . ",1),";
                        //$this->db->query($sql1);
                    }
                }
                $sql2 = substr($sql1, 0, -1);
                $this->db->query($sql2);
            } else {
                $sql = "select matriculeetudiant from l2  where ((annee=$annee and semestre=3)or (annee=$annee+1 and semestre=1) ) and matriculeetudiant not in(select matriculeetudiant from credit_valide_sem where semestre in (5,6))  ";
                $query = $this->db->query($sql);
                $etu = "";
                if ($query->num_rows() > 0) {
                    foreach ($query->result_array() as $row) {
                        $etu[] = $row['matriculeetudiant'];
                    }
                }
                $list_e = '';
                for ($i = 0; $i < count($etu);  ++$i) {
                    $list_e = $list_e . $etu[$i] . ',';
                }
                $list_e = $list_e . $etu[count($etu) - 1];
                $info = $this->get_moyenne_niveau_decision($list_e);
                //   print_r($info);
                $sql1 = " insert into passage_t values";
                foreach ($info as $matriculeEtudiant => $infos) {
                    if (($infos['MGL1'] / 2) >= 10 and $infos['ECTSL1'] == 60 and ( $infos['MGL2'] / 2) >= 10 and $infos['ECTSL2'] >= 39) {

                        $sql1 = $sql1 . "(" . $matriculeEtudiant . ",3," . $annee . "," . $infos['ECTSL2'] . "," . ($infos['MGL2'] / 2) . ",2),";
                        $this->db->query($sql1);
                    } else {
                        $sql1 = $sql1 . "(" . $matriculeEtudiant . ",2," . $annee . "," . $infos['ECTSL2'] . "," . ($infos['MGL2'] / 2) . ",2),";
                        $this->db->query($sql1);
                    }
                }
                $sql2 = substr($sql1, 0, -1);
                $this->db->query($sql2);
            }
        } else {
            $test = "delete  from passage_t where  annee=$annee and matriculeetudiant in(select matriculeetudiant from l3  where ((annee=$annee and semestre=3)or (annee=$annee+1 and semestre=1) ))";
            $this->db->query($test);
            $sql = "select matriculeetudiant from l3  where ((annee=$annee and semestre=3)or (annee=$annee+1 and semestre=1) )";
            $query = $this->db->query($sql);
            $etu = "";
            if ($query->num_rows() > 0) {
                foreach ($query->result_array() as $row) {
                    $etu[] = $row['matriculeetudiant'];
                }
            }
            $list_e = '';
            for ($i = 0; $i < count($etu);  ++$i) {
                $list_e = $list_e . $etu[$i] . ',';
            }
            $list_e = $list_e . $etu[count($etu) - 1];
            $info = $this->get_moyenne_niveau_decision($list_e);

            //print_r($info);
            $sql1 = " insert into passage_t values";
            foreach ($info as $matriculeEtudiant => $infos) {
                if (($infos['MGL1'] / 2) >= 10 and $infos['ECTSL1'] == 60 and ( $infos['MGL2'] / 2) >= 10 and $infos['ECTSL2'] == 60 and ( $infos['MGL3'] / 2) >= 10 and $infos['ECTSL3'] == 60) {

                    $sql1 = $sql1 . "(" . $matriculeEtudiant . ",4," . $annee . "," . $infos['ECTSL3'] . "," . ($infos['MGL3'] / 2) . ",1),";
                } else {
                    $sql1 = $sql1 . "(" . $matriculeEtudiant . ",3," . $annee . "," . $infos['ECTSL3'] . "," . ($infos['MGL3'] / 2) . ",1),";
                }
            }
            $sql2 = substr($sql1, 0, -1);
            $this->db->query($sql2);
            
        }
    }

    function get_decision_passage($annee, $matricule) { /* echo $semestre."hh";
      $niveau=0;
      if($semestre=="1" or $semestre=="2"){
      $niveau=1;
      }elseif($semestre=="3" or $semestre=="4"){
      $niveau=2;
      }elseif($semestre=="5" or $semestre=="6"){
      $niveau=3;
      } */
        $sql1 = "SELECT matriculeetudiant,niveau,max(ects) as ects ,MG,regle  FROM passage_t where annee=$annee and matriculeetudiant=" . $matricule;
      //  echo $sql1;
        $query = $this->db->query($sql1);

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info['matriculeetudiant'] = $row['matriculeetudiant'];
                $info['niveau'] = $row['niveau'];
                $info['ects'] = $row['ects'];
                $info['MG'] = $row['MG'];
                $info['regle'] = $row['regle'];
            }
            return $info;
        }
    }

    function get_decision_redoublant($annee, $niveau) {
        $sql1 = "";
        if ($niveau == "2") {
            $sql1 = "SELECT distinct `matriculeEtudiant` FROM `l2` WHERE (`matriculeEtudiant`,`annee`) not in(select `matriculeEtudiant`,`annee` from passage_t where annee=" . $annee . ") and `annee`=" . $annee;
        } elseif ($niveau == "3") {
            $sql1 = "SELECT distinct `matriculeEtudiant` FROM `l3` WHERE (`matriculeEtudiant`,`annee`) not in(select `matriculeEtudiant`,`annee` from passage_t where annee=" . $annee . ") and `annee`=" . $annee;
        }//echo $sql1;
        $query = $this->db->query($sql1);
        $etu = "";
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $etu[] = $row['matriculeetudiant'];
            }
        }
        $list_e = '';
        for ($i = 0; $i < count($etu);  ++$i) {
            $list_e = $list_e . $etu[$i] . ',';
        }
        $list_e = $list_e . $etu[count($etu) - 1];

        //  print_r($list_e);
        if ($niveau == "2") {
            if ($annee <= 2017) {

                $sql = "insert into passage_t select l1.matriculeetudiant, 2 as niveau ,l1.annee ,sum(l1.credits_val) as ects, sum(e.note)/4 as MG from semestre_decision_bis l1, etudiant_sem_note_bis e where l1.`matriculeEtudiant`= e.`matriculeEtudiant` and l1.`annee` = e.annee and   l1.`matriculeEtudiant` in(" . $list_e . ")  and e.annee=$annee  and l1.semestre = e.semestre and l1.semestre in(1,2,3,4) group by 1,2,3 having((sum(l1.credits_val) >=30 and  sum(l1.credits_val) <90)   and  sum(e.note)/2 >= 10.0)";
                $this->db->query($sql);
            } else {
                $sql = "insert into passage_t select l1.matriculeetudiant, 2 as niveau ,l1.annee ,sum(l1.credits_val) as ects, sum(e.note)/4 as MG from semestre_decision_bis l1, etudiant_sem_note_bis e where l1.`matriculeEtudiant`= e.`matriculeEtudiant` and l1.`annee` = e.annee  and  l1.`matriculeEtudiant` in(" . $list_e . ")   and e.annee=$annee and  and l1.semestre = e.semestre and l1.semestre in(1,2,3,4) group by 1,2,3 having((sum(l1.credits_val) >=39 and  sum(l1.credits_val) <110)   and  sum(e.note)/2 >= 10.0)";
                $this->db->query($sql);
            }
        } elseif ($niveau == "3") {
            if ($annee <= 2019) {

                $sql = "insert into passage_t select l1.matriculeetudiant, 3 as niveau ,l1.annee ,sum(l1.credits_val) as ects, sum(e.note)/6 as MG from semestre_decision_bis l1, etudiant_sem_note_bis e where l1.`matriculeEtudiant`= e.`matriculeEtudiant` and l1.`annee` = e.annee and   l1.`matriculeEtudiant` in(" . $list_e . ")  and e.annee=$annee  and l1.semestre = e.semestre and l1.semestre in(1,2,3,4,5,6) group by 1,2,3 having((sum(l1.credits_val) >=90 and  sum(l1.credits_val) <180)   and  sum(e.note)/4 >= 10.0)";
                $this->db->query($sql);
            } else {
                $sql = "insert into passage_t select l1.matriculeetudiant, 3 as niveau ,l1.annee ,sum(l1.credits_val) as ects, sum(e.note)/6 as MG from semestre_decision_bis l1, etudiant_sem_note_bis e where l1.`matriculeEtudiant`= e.`matriculeEtudiant` and l1.`annee` = e.annee  and  l1.`matriculeEtudiant` in(" . $list_e . ")   and e.annee=$annee and  and l1.semestre = e.semestre and l1.semestre in(1,2,3,4,6) group by 1,2,3 having((sum(l1.credits_val) >=110 and  sum(l1.credits_val) <180)   and  sum(e.note)/4 >= 10.0)";
                $this->db->query($sql);
            }
        }
    }

    function get_moyenne_niveau($matricule) {
        $sql1 = "SELECT matriculeetudiant,semestre,credits_val,note  FROM credit_valide_sem where matriculeetudiant=$matricule";
        //  echo $sql1;
        $query = $this->db->query($sql1);


        $info = array();
        $info["MGL1"] = 0;
        $info["ECTSL1"] = 0;
        $info["MGL2"] = 0;
        $info["ECTSL2"] = 0;
        $info["MGL3"] = 0;
        $info["ECTSL3"] = 0;
        //
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {        //print_r($row);
                if ($row['semestre'] == "1" or $row['semestre'] == "2") {

                    $info["MGL1"] += $row['note'];
                    $info["ECTSL1"] += $row['credits_val'];
                }
                if ($row['semestre'] == "3" or $row['semestre'] == "4") {

                    $info["MGL2"] += $row['note'];
                    $info["ECTSL2"] += $row['credits_val'];
                }
                if ($row['semestre'] == "5" or $row['semestre'] == "6") {

                    $info["MGL3"] += $row['note'];
                    $info["ECTSL3"] += $row['credits_val'];
                }
            }
        }
        return $info;
        //  print_r($info);
    }

    function get_moyenne_niveau_decision($matricule) {
        $sql1 = "SELECT matriculeetudiant,semestre,credits_val,note  FROM credit_valide_sem where matriculeetudiant in($matricule)";
        //  echo $sql1;
        $query = $this->db->query($sql1);


        $info = array();
        //
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {        //print_r($row);
                if ($row['semestre'] == "1") {

                    $info[$row['matriculeetudiant']]["MGL1"] = $row['note'];
                }
                if ($row['semestre'] == 2) {
                    $info[$row['matriculeetudiant']]["MGL1"] += $row['note'];
                }
                if ($row['semestre'] == "1") {
                    $info[$row['matriculeetudiant']]["ECTSL1"] = $row['credits_val'];
                }
                if ($row['semestre'] == "2") {
                    $info[$row['matriculeetudiant']]["ECTSL1"] += $row['credits_val'];
                }


                if ($row['semestre'] == "3") {

                    $info[$row['matriculeetudiant']]["MGL2"] = $row['note'];
                }
                if ($row['semestre'] == 4) {
                    $info[$row['matriculeetudiant']]["MGL2"] += $row['note'];
                }
                if ($row['semestre'] == "3") {
                    $info[$row['matriculeetudiant']]["ECTSL2"] = $row['credits_val'];
                }
                if ($row['semestre'] == "4") {
                    $info[$row['matriculeetudiant']]["ECTSL2"] += $row['credits_val'];
                }


                if ($row['semestre'] == "5") {

                    $info[$row['matriculeetudiant']]["MGL3"] = $row['note'];
                }
                if ($row['semestre'] == 6) {
                    $info[$row['matriculeetudiant']]["MGL3"] += $row['note'];
                }
                if ($row['semestre'] == "5") {
                    $info[$row['matriculeetudiant']]["ECTSL3"] = $row['credits_val'];
                }
                if ($row['semestre'] == "6") {
                    $info[$row['matriculeetudiant']]["ECTSL3"] += $row['credits_val'];
                }
            }
        }
        return $info;
        //  print_r($info);
    }

    function info_fiche_emarger($planing, $semestre, $annee, $session, $sigle, $salle) {
        $sql1 = "";
        if ($session == "RT") {
            $sql1 = "select distinct t.`sigle` AS `sigle`,t.`matriculeEtudiant` AS `matriculeetudiant`,concat(e.prenom,' ',e.nom) as nom_c,n.niveau,x.num_exam,n.idProgramme,s.id_salle,s.groupe from salles s,numero_exam x,niveau_ins2016 n,etudiant e,releve_t_bis t where t.sigle='" . $sigle . "'  and t.matriculeEtudiant=n.matriculeEtudiant and t.matriculeEtudiant=x.matriculeEtudiant and t.matriculeEtudiant=e.matriculeEtudiant  and t.capit='NC' and t.annee=$annee and  s.id_salle='" . $salle . "' and  `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin`  ";
            // echo $sql1;
        } else {
            $sql1 = "select distinct p.`sigle` AS `sigle`,p.`matriculeEtudiant` AS `matriculeetudiant`,concat(e.prenom,' ',e.nom) as nom_c,n.niveau,x.num_exam,n.idProgramme,s.id_salle,s.groupe from `planetudes` p,salles s,numero_exam x,niveau_ins2016 n,etudiant e where (p.`annee` = $annee) and (p.`semestre` = $semestre) and p.sigle='" . $sigle . "'  and p.matriculeEtudiant=n.matriculeEtudiant and p.matriculeEtudiant=x.matriculeEtudiant and p.matriculeEtudiant=e.matriculeEtudiant and  s.id_salle='" . $salle . "' and  `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin`  ";
            // echo $sql1;
        }

        $query = $this->db->query($sql1);
        $info = array();
        //
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info[] = $row;
            }
        }
        return $info;
    }

    function info_fiche_emarger_nb($planing, $semestre, $annee, $session, $sigle, $salle) {
        $sql1 = "";
        if ($session == "RT") {
            $sql1 = "select distinct count(t.matriculeEtudiant) as nb,t.`sigle` AS `sigle`,m.titre,u.semestre,s.groupe  from salles s,numero_exam x,module m,unite u,releve_t_bis t where t.sigle='" . $sigle . "' and u.sigle=m.sigleunite  and t.matriculeEtudiant=x.matriculeEtudiant  and t.capit='NC' and t.annee=$annee and t.sigle=m.sigle and s.id_salle='" . $salle . "' and `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin` ";
            // echo $sql1;
        } else {
            $sql1 = "select distinct count(p.matriculeEtudiant) as nb,p.`sigle` AS `sigle`,m.titre,u.semestre,s.groupe  from `planetudes` p,salles s,numero_exam x,module m,unite u where (p.`annee` = $annee) and (p.`semestre` = $semestre) and p.sigle='" . $sigle . "' and u.sigle=m.sigleunite  and p.matriculeEtudiant=x.matriculeEtudiant and p.sigle=m.sigle and s.id_salle='" . $salle . "' and `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin` ";
            // echo $sql1;
        }

        $query = $this->db->query($sql1);
        $info = array();
        //
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info[] = $row;
            }
        }
        return $info;
    }

    function liste_et_salle_exam($planing, $semestre, $annee, $session, $niveau, $programme, $salle) {
        $sql1 = "";
      //  echo $programme;
        if ($session == "RT") {
            if ($salle == "-1") {
                if (strcmp($programme, 'tous') != 0) {
                    $sql1 = "select distinct t.`matriculeEtudiant` AS `matriculeetudiant`,concat(e.prenom,' ',e.nom) as nom_c,n.niveau,x.num_exam,n.idProgramme,s.id_salle from salles s,numero_exam x,niveau_ins2016 n,etudiant e,releve_t_bis t where  n.niveau=$niveau  and t.matriculeEtudiant=n.matriculeEtudiant and t.matriculeEtudiant=x.matriculeEtudiant and t.matriculeEtudiant=e.matriculeEtudiant and n.idProgramme='" . $programme . "'  and t.annee=$annee and t.capit='NC'  and  `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin`  order by 5,1 ";
                } else
                    $sql1 = "select distinct t.`matriculeEtudiant` AS `matriculeetudiant`,concat(e.prenom,' ',e.nom) as nom_c,n.niveau,x.num_exam,n.idProgramme,s.id_salle from salles s,numero_exam x,niveau_ins2016 n,etudiant e,releve_t_bis t where  n.niveau=$niveau  and t.matriculeEtudiant=n.matriculeEtudiant and t.matriculeEtudiant=x.matriculeEtudiant and t.matriculeEtudiant=e.matriculeEtudiant  and t.annee=$annee and t.capit='NC'  and  `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin` order by 5,1 ";
              //  echo $sql1;
            }else {
                if (strcmp($programme, 'tous') != 0) {
                    $sql1 = "select distinct T.`matriculeEtudiant` AS `matriculeetudiant`,concat(e.prenom,' ',e.nom) as nom_c,n.niveau,x.num_exam,n.idProgramme,s.id_salle from `planetudes` p,salles s,numero_exam x,niveau_ins2016 n,etudiant e,releve_t_bis t where n.niveau=$niveau  and t.matriculeEtudiant=n.matriculeEtudiant and t.matriculeEtudiant=x.matriculeEtudiant and t.matriculeEtudiant=e.matriculeEtudiant and  s.id_salle='" . $salle . "' and  n.idProgramme='" . $programme . "'  and t.annee=$annee and t.capit='NC' and  `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin` order by 5,1 ";
                } else
                    $sql1 = "select distinct T.`matriculeEtudiant` AS `matriculeetudiant`,concat(e.prenom,' ',e.nom) as nom_c,n.niveau,x.num_exam,n.idProgramme,s.id_salle from `planetudes` p,salles s,numero_exam x,niveau_ins2016 n,etudiant e,releve_t_bis t where n.niveau=$niveau  and t.matriculeEtudiant=n.matriculeEtudiant and t.matriculeEtudiant=x.matriculeEtudiant and t.matriculeEtudiant=e.matriculeEtudiant and  s.id_salle='" . $salle . "'  and t.annee=$annee and t.capit='NC' and  `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin` order by 5,1 ";
               // echo $sql1;
            }
        }else {
            if ($salle == "-1") {
                if (strcmp($programme, 'tous') != 0) {
                    $sql1 = "select distinct p.`matriculeEtudiant` AS `matriculeetudiant`,concat(e.prenom,' ',e.nom) as nom_c,n.niveau,x.num_exam,n.idProgramme,s.id_salle from `planetudes` p,salles s,numero_exam x,niveau_ins2016 n,etudiant e where (p.`annee` = $annee) and (p.`semestre` = $semestre) and n.niveau=$niveau  and p.matriculeEtudiant=n.matriculeEtudiant and p.matriculeEtudiant=x.matriculeEtudiant and p.matriculeEtudiant=e.matriculeEtudiant and n.idProgramme='" . $programme . "'   and  `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin`  order by 5,1";
                } else
                    $sql1 = "select distinct p.`matriculeEtudiant` AS `matriculeetudiant`,concat(e.prenom,' ',e.nom) as nom_c,n.niveau,x.num_exam,n.idProgramme,s.id_salle from `planetudes` p,salles s,numero_exam x,niveau_ins2016 n,etudiant e where (p.`annee` = $annee) and (p.`semestre` = $semestre) and n.niveau=$niveau  and p.matriculeEtudiant=n.matriculeEtudiant and p.matriculeEtudiant=x.matriculeEtudiant and p.matriculeEtudiant=e.matriculeEtudiant  and  `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin` order by 5,1 ";
              //  echo $sql1;
            }else {
                if (strcmp($programme, 'tous') != 0) {
                    $sql1 = "select distinct p.`matriculeEtudiant` AS `matriculeetudiant`,concat(e.prenom,' ',e.nom) as nom_c,n.niveau,x.num_exam,n.idProgramme,s.id_salle from `planetudes` p,salles s,numero_exam x,niveau_ins2016 n,etudiant e where (p.`annee` = $annee) and (p.`semestre` = $semestre) and n.niveau=$niveau  and p.matriculeEtudiant=n.matriculeEtudiant and p.matriculeEtudiant=x.matriculeEtudiant and p.matriculeEtudiant=e.matriculeEtudiant and  s.id_salle='" . $salle . "' and  n.idProgramme='" . $programme . "'  and  `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin` order by 5,1 ";
                } else
                    $sql1 = "select distinct p.`matriculeEtudiant` AS `matriculeetudiant`,concat(e.prenom,' ',e.nom) as nom_c,n.niveau,x.num_exam,n.idProgramme,s.id_salle from `planetudes` p,salles s,numero_exam x,niveau_ins2016 n,etudiant e where (p.`annee` = $annee) and (p.`semestre` = $semestre) and n.niveau=$niveau  and p.matriculeEtudiant=n.matriculeEtudiant and p.matriculeEtudiant=x.matriculeEtudiant and p.matriculeEtudiant=e.matriculeEtudiant and  s.id_salle='" . $salle . "'  and  `x`.`num_exam` between `s`.`num_debut` and `s`.`num_fin` order by 5,1 ";
               // echo $sql1;
            }
        }

        $query = $this->db->query($sql1);
        $info = array();
        //
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info[] = $row;
            }
        }
        return $info;
    }

    function get_info_planning($planing, $sigle, $session) {
        $sql1 = "";
        if ($session == "RT") {
            $sql1 = "select * from plannig_exam_bis where sigle ='" . $sigle . "' and idPlaning=$planing ";
        } else {

            $sql1 = "select * from plannig_exam_bis where sigle ='" . $sigle . "' and idPlaning=$planing ";
        }

        $query = $this->db->query($sql1);
        $info = array();
        //
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info[] = $row;
            }
        }
        return $info;
    }

    function get_info_anonymat($annee, $semestre, $programme, $session, $niveau) {
        $sql1 = "";
        if ($programme == "-1") {
            if ($session == "RT") {
                
            } else {

                $sql1 = "select `a`.`matriculeEtudiant` AS `matriculeEtudiant`,`a`.`code_ex` AS `code_ex`,concat(`e`.`prenomPere_fr`,' ',`e`.`nom`,' ',`e`.`prenom`) AS `nom`,`n`.`niveau` AS `niveau`,`d`.`idProgramme` AS `idProgramme` from (((`iup`.`anonymat` `a` join `iup`.`dossieretudiant` `d`) join `iup`.`etudiant` `e`) join `iup`.`niveau_inscrits` `n`) where ((`a`.`annee` = $annee) and (`a`.`semestre` = $semestre) and (`a`.`matriculeEtudiant` = `e`.`matriculeEtudiant`) and (`d`.`matriculeEtudiant` = `e`.`matriculeEtudiant`) and (`n`.`matriculeetudiant` = `e`.`matriculeEtudiant`) and (`n`.`annee` = $annee) and (`n`.`semestre` = $semestre)) order by `e`.`matriculeEtudiant`,`d`.`idProgramme`,`n`.`niveau`";
            }
        } else {
            if ($session == "RT") {
                
            } else {

                $sql1 = "select `a`.`matriculeEtudiant` AS `matriculeEtudiant`,`a`.`code_ex` AS `code_ex`,concat(`e`.`prenomPere_fr`,' ',`e`.`nom`,' ',`e`.`prenom`) AS `nom`,`n`.`niveau` AS `niveau`,`d`.`idProgramme` AS `idProgramme` from (((`iup`.`anonymat` `a` join `iup`.`dossieretudiant` `d`) join `iup`.`etudiant` `e`) join `iup`.`niveau_inscrits` `n`) where ((`a`.`annee` = $annee) and (`a`.`semestre` = $semestre) and (`a`.`matriculeEtudiant` = `e`.`matriculeEtudiant`) and (`d`.`matriculeEtudiant` = `e`.`matriculeEtudiant`) and (`n`.`matriculeetudiant` = `e`.`matriculeEtudiant`) and (`n`.`annee` = $annee) and (`n`.`semestre` = $semestre)) and d.idProgramme='" . $programme . "' and n.niveau=$niveau order by `e`.`matriculeEtudiant`,`d`.`idProgramme`,`n`.`niveau`";
            }
        }

        $query = $this->db->query($sql1);
        $info = array();
        //
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info[] = $row;
            }
        }
        return $info;
    }

    function max_semestre($matricule) {
        $query = "select max(semestre) as semestre from releve_t_bis where matriculeetudiant=" . $matricule;
        //echo $query;
        $q = $this->db->query($query);
        $res = $q->row_array();
        $semestre = $res["semestre"];
        return $semestre;
    }

    function Mg_zero($idProgramme, $semestre, $annee) {
        $query = "select count(e.matriculeEtudiant) as nb_mg_zero from etudiant_sem_note_t_bis e,dossieretudiant d WHERE e.`matriculeEtudiant`=d.`matriculeEtudiant` and e.semestre=$semestre and annee=$annee and e.note=0 and d.idProgramme='" . $idProgramme . "'";
        // $query;
        $q = $this->db->query($query);
        $res = $q->row_array();
        $nb_mg_zero = $res['nb_mg_zero'];
        return $nb_mg_zero;
    }

    function get_statistique_pv($idProgramme, $semestre, $annee, $semestreCourant) {
        $sql1 = "SELECT distinct count(s.`matriculeEtudiant`) as nb,s.decision FROM `semestre_decision_t_bis` s,dossieretudiant d WHERE s.`matriculeEtudiant`=d.`matriculeEtudiant` and s.semestre=$semestre and annee=$annee and d.idProgramme='" . $idProgramme . "'  and s.`matriculeEtudiant` in(select `matriculeEtudiant` from planetudes p,module m,unite u where u.sigle=m.sigleunite and m.sigle=p.sigle and u.semestre=$semestre and p.semestre=$semestreCourant and p.annee=$annee)  group by s.decision";
        //echo $sql1;
        $query = $this->db->query($sql1);
        $nb_mg_zero = $this->Mg_zero($idProgramme, $semestre, $annee);
        $info = array("nb_admis" => 0, "nb_compense" => 0, "nb_ajourne" => 0, "nb_reussite" => 0, "nb_totale" => 0, "taux_reussite" => 0, "taux_abondon" => 0, "nb_mg_zero" => 0, "taux_nb_mg_zero" => $nb_mg_zero);
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {

                if ($row["decision"] == "Admis(e)") {
                    $info["nb_admis"] = $row['nb'];
                }if ($row["decision"] == "Compense") {
                    $info["nb_compense"] = $row['nb'];
                }if ($row["decision"] == "Ajourné(e)") {
                    $info["nb_ajourne"] = $row['nb'];
                }
                $info['nb_reussite'] = $info['nb_admis'] + $info['nb_compense'];

                $info['nb_totale'] = $info['nb_reussite'] + $info['nb_ajourne'];
                $info['taux_reussite'] = $info['nb_reussite'] / $info['nb_totale'] * 100;
                $info['taux_abondon'] = $info['nb_ajourne'] / $info['nb_totale'] * 100;
                $info['taux_nb_mg_zero'] = $info['nb_mg_zero'] / $info['nb_totale'] * 100;
            }
            return $info;
        }
    }

    function info_etudiants() {
        $elements = "";
        $query = $this->db->query("select *  from etudiant");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $elements[] = $row;
                //  $elements['anc_sigle'][] = $row['anc_sigle'];
                //  $elements['nouv_sigle'][] = $row['nouv_sigle'];
            }
        }
        return $elements;
    }

    function getInfoElements_etudiant($loadType, $loadId, $semestre, $matricule) {

        $query = null;

        if ($loadType == "etudiant_elements") {
            //  $this->db->insert("r_events", array('test'=>"SELECT  p.matriculeetudiant,concat(e.prenom,' ',e.nom)as nom  FROM `planetudes` p,etudiant e where  p.semestre=$semestreCourant and p.annee=$anneeCourante  and p.sigle='".$matriculeEmploye."' and p.matriculeetudiant=e.matriculeetudiant"));

            $query = $this->db->query("SELECT distinct p.sigle,m.titre  FROM `planetudes` p,module m,unite u  where m.sigle=p.sigle and u.sigle=m.sigleunite and p.matriculeetudiant=$matricule and u.semestre=$semestre ");
        }



        return $query;
    }

    function getInfoElement_note($loadType, $loadId, $evaluation, $matricule, $sigle) {

        $query = null;

        if ($loadType == "note_element") {
            //  $this->db->insert("r_events", array('test'=>"SELECT  p.matriculeetudiant,concat(e.prenom,' ',e.nom)as nom  FROM `planetudes` p,etudiant e where  p.semestre=$semestreCourant and p.annee=$anneeCourante  and p.sigle='".$matriculeEmploye."' and p.matriculeetudiant=e.matriculeetudiant"));

            $query = $this->db->query("SELECT * from  notespartielles    where matriculeetudiant=$matricule and sigle='" . $sigle . "' and  idEvaluation=$evaluation ");
        }



        return $query;
    }

    function ajouter_modif_note_temp($semestre, $sigle, $matriculeEtudiant, $note, $session, $annee) {
        $login = $this->session->userdata('login');
        $query = $this->db->query("insert into modification_note_temp(matriculeEtudiant,sigle,idEvaluation,note,semestre,annee,etat) values($matriculeEtudiant,'$sigle',$session,$note,$semestre,$annee,0)");
        $query1 = $this->db->query("insert into historique_modification_note(matriculeEtudiant,sigle,idEvaluation,note,semestre,annee,login,etat) values($matriculeEtudiant,'$sigle',$session,$note,$semestre,$annee,'$login',0)");
    }

    function info_liste_modification_note() {
        $info = "";
        $query = $this->db->query("select t.*,m.titre,u.semestre as sem  from modification_note_temp t,module m,unite u  where etat=0 and m.sigle=t.sigle and u.sigle=m.sigleunite");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info[] = $row;
                //  $elements['anc_sigle'][] = $row['anc_sigle'];
                //  $elements['nouv_sigle'][] = $row['nouv_sigle'];
            }
        }
        return $info;
    }

    function info_historique_modification_note() {
        $info = "";
        $query = $this->db->query("select t.*,m.titre,u.semestre as sem,concat(e.prenom,' ',e.nom ) as nom  from historique_modification_note t,module m,unite u,employe e  where etat=0 and e.login=t.login and  m.sigle=t.sigle and u.sigle=m.sigleunite");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info[] = $row;
                //  $elements['anc_sigle'][] = $row['anc_sigle'];
                //  $elements['nouv_sigle'][] = $row['nouv_sigle'];
            }
        }
        return $info;
    }

    function valider_modification_note($semestre, $sigle, $matriculeEtudiant, $note, $idEvaluation, $annee) {
        $login = $this->session->userdata('login');
        $query = $this->db->query("update notespartielles  set note=$note where idEvaluation=$idEvaluation and semestre=$semestre and sigle='$sigle' and matriculeEtudiant=$matriculeEtudiant and annee=$annee ");
        $query1 = $this->db->query("update modification_note_temp  set etat=1 where idEvaluation=$idEvaluation and semestre=$semestre and sigle='$sigle' and matriculeEtudiant=$matriculeEtudiant and annee=$annee ");
        $query2 = $this->db->query("insert into historique_modification_note(matriculeEtudiant,sigle,idEvaluation,note,semestre,annee,login,etat) values($matriculeEtudiant,'$sigle',$session,$note,$semestre,$annee,'$login',1)");
    }

    function maj_note_etudiant($matricule) {
        $query = $this->db->query("delete from notes_globales_dern_t_bis where matriculeetudiant=$matricule");
        $query = $this->db->query("insert into notes_globales_dern_t_bis select * from notes_globales where matriculeetudiant=$matricule");
        $query = $this->db->query("delete from planetudesmoduleelem_t where matriculeetudiant=$matricule");
        $query = $this->db->query("insert into planetudesmoduleelem_t select * from planetudesmoduleelem where matriculeEtudiant=$matricule");
        $query = $this->db->query("delete from etudiant_sem_note_t_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("insert into etudiant_sem_note_t_bis select * from etudiant_sem_note_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("delete from capseul_t_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("insert into capseul_t_bis select * from capseul_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("delete from capinterne_t_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("insert into capinterne_t_bis select * from capinterne_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("delete from capexterne_t_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("insert into capexterne_t_bis select * from capexterne_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("delete from noncap_t_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("insert into noncap_t_bis select * from noncap_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("delete from modules_non_valides_t_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("insert into modules_non_valides_t_bis select * from modules_non_valides_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("delete from modules_valides_t_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("insert into modules_valides_t_bis select * from modules_valides_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("delete from modules_decision_t_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("insert into modules_decision_t_bis select * from modules_decision_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("delete from releve_t_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("insert into releve_t_bis select * from releve_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("delete from semestre_decision_t_bis where matriculeEtudiant=$matricule");
        $query = $this->db->query("insert into semestre_decision_t_bis select * from semestre_decision_bis where matriculeEtudiant=$matricule

");
    }

    //Ajout des fonctions d'impression de la carte d'etudiant
    function verifier_existance_etudiant($matricule) {
        $requete = $this->db->query("select count(*) as nfois from etudiant where matriculeetudiant='$matricule'");
        $resultat = NULL;

        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    function getIdProgramme($matricule) {
        $requete = $this->db->query("select idProgramme as idProgramme from dossieretudiant where matriculeetudiant='$matricule'");
        $resultat = NULL;

        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    //fin de l'ajout
    
    /*
     * ajoutÃ© par Alioune Zeyn le 17/08/2018
     *
     *
     */
    
    // le role de cette fonction est de retourner le resultat de la requete qu'elle recoit en parametre
    function get_listes_etats($requete)
    {
        $requete = $this->db->query($requete);
        $resultat = NULL;
        
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    // fin ajout
    
    
    
    
    // teste traitement script
    function execute_requeste($query) {
        $info = "";
        $query = $this->db->query("select t.*,m.titre,u.semestre as sem  from modification_note_temp t,module m,unite u  where etat=0 and m.sigle=t.sigle and u.sigle=m.sigleunite");
        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $info[] = $row;
            }
        }
        return $info;
    }
    function test_script_view_traitement(){
        
        // requete de la view inscrits_a_s 
        $query_1="select distinct `p`.`matriculeEtudiant` AS `matriculeEtudiant`, `p`.`annee` AS `annee`, `p`.`semestre` AS `semestre` from `planetudes` `p`";
        $result=$this->execute_requeste($query_1);
        print_r($result);
        return $result;
        
    }
    
    function getParametresGenerauxCourants(){
        $resultat=null;
        $requete="select * from parametres_generaux where isnull(date_fin) ";
        $query = $this->db->query($requete);
        if ($query->num_rows() > 0) {
            $resultat=$query->result_array();
        }
        return $resultat;
    }
     public function get_parametres_genreaux(){
        $data = '';
        //$this->db->where('date_fin', 'is null');
        //$query = $this->db->get('parametres_generaux');
        $query=$this->db->query('select * from parametres_generaux where date_fin is null');
        foreach ($query->result() as $row) {
            $data = $row;
        }
        return $data;
    }
    
    function Recup_Parametre_Generaux(){
       //  $data = '';
        //$this->db->where('date_fin', 'is null');
        //$query = $this->db->get('parametres_generaux');
        $query=$this->db->query('select * from parametres_generaux where date_fin is null ');
       $data = array();
        if ($query->num_rows() > 0) {
            
            foreach ($query->result_array() as $row) {
                $data[] = $row;
            }
        }
        
        return $data;
    }


//ajouter par MedBakar 02-04-2020    
    function list_etudiant_inscrit_pour_sigle($sigle,$annee){
        $data=array();
        $result=$this->db->query("select matriculeEtudiant from planetudes where sigle='".$sigle."' and annee='".$annee."'");
    if($result->num_rows()>0){
        foreach($result->result_array() as $rows){
            $data[]=$rows;
        }
    }
        return $data;
    }
    function former_req_inscrir_ds_groupe($list_mat,$sigle){
//        for($cpt=0;$cpt<count($list_mat);$cpt++){
//        $this->db->query("insert into listeetudiants values('".$list_mat[$cpt]."','".$sigle."')");
//        }
    if(!empty($list_mat)){    
$query='';       
// $query="INSERT INTO listeetudiants values";
        for($cpt=0;$cpt<count($list_mat);$cpt++){
           // if($cpt>0)
                $query.=" , ";
            $query.="('".$list_mat[$cpt]."','".$sigle."')";
        }
         //$this->db->query($query);
        // print($this->db->last_query());
        return $query;
        
        }
    }
    function inscrir_ds_groupe($query){
       // echo "INSERT INTO listeetudiants values ".substr($query,2);
     if(!empty($query))   
                 $this->db->query("INSERT INTO listeetudiants values ".substr($query,2));
    }
    function ges_groupes($niveau,$programme,$nbGroupeCM,$nbGroupeTD,$nbGroupeTP,$date){
        if(empty($nbGroupeCM))
            $nbGroupeCM=0;
          if(empty($nbGroupeTD))
              $nbGroupeTD=0;
            if(empty($nbGroupeTP))
              $nbGroupeTP=0;
        //on verifi si le nbr de groupe est deja stocke
       $result= $this->db->query("SELECT * FROM ges_groupes where niveau='".$niveau."' AND programme='".$programme."' AND annee='".substr($date,2).substr($date+1,2)."'" );
     //print($this->db->last_query());
       if($result->num_rows()>0){
          $this->db->query("UPDATE ges_groupes set CM=CM+'".$nbGroupeCM."' ,TD=TD+'".$nbGroupeTD."' , TP=TP+'".$nbGroupeTP."' where niveau='".$niveau."' AND programme='".$programme."' AND annee='".substr($date,2).substr($date+1,2)."'");
      }
      else{
        $req="INSERT INTO `ges_groupes`(`niveau`, `programme`, `CM`, `TD`, `TP`, `annee`)values('".$niveau."','".$programme."','".$nbGroupeCM."','".$nbGroupeTD."','".$nbGroupeTP."','".substr($date,2).substr($date+1,2)."')";
      $this->db->query($req);
      }
      
      }
 //fin ajout MedBakar 10-04-2020
      function list_etudiant_inscrit_pour_sigle_ajax($sigle,$annee){
        $data=array();
        $result=$this->db->query("SELECT distinct e.matriculeEtudiant,nom,prenom FROM `planetudes` p,etudiant e where e.matriculeEtudiant=p.matriculeEtudiant and sigle='".$sigle."' and annee='".$annee."'");
    if($result->num_rows()>0){
        foreach($result->result_array() as $rows){
            $data[]=$rows;
        }
    }
        return $data;
    }
    
    //verification du mot de passe pour la modification des notes
    function validation_modification_notes_eliminatoires($employe,$pass) {/*AZ*/
        $this->db->where('matriculeEmploye',$employe);
        $this->db->where('pass like binary "' . $this->encode($pass) . '"', NULL, FALSE);
        $query = $this->db->get('employe');
        if ($query->num_rows() > 0) {
            $row = $query->row_array();
            return true;
        }
        return false;
    }
	
	


      function regle_passage($annee=' '){//add by MedBakar 10-09-2020
          if(empty($annee))
        $query = $this->db->query("SELECT * from regles_passage where date_fin is null ");
      else
        $query = $this->db->query("SELECT * from regles_passage where DATE_FORMAT(date_debut, '%Y')<=$annee and (DATE_FORMAT(date_fin, '%Y')>=$annee or date_fin is null or date_fin='')  ");
          $regle =array();
          if ($query->num_rows() > 0) {
              foreach ($query->result_array() as $row) {
                  $regle[$row['cycle']][$row['niveau']]['credit'] = $row['credit'];
                  $regle[$row['cycle']][$row['niveau']]['moyenne'] = $row['moyenne'];
                  $regle[$row['cycle']][$row['niveau']]['MGL1'] = $row['MGL1'];
                  $regle[$row['cycle']][$row['niveau']]['ECTSL1'] = $row['ECTSL1'];
                  $regle[$row['cycle']][$row['niveau']]['MGL2'] = $row['MGL2'];
                  $regle[$row['cycle']][$row['niveau']]['ECTSL2'] = $row['ECTSL2'];
              }
          }
//          print_r($regle);
          return $regle;

      }

/*
     * Fonction retourne les elements ratrappee selon une liste a etre recevoire en parametre
     * et que ses matieres son de niveau passee , c a d que si l'etudiant est redoublant
     *elle ne retourne pas ses matieres
 * add by MedBakar
     */
    function get_module_rattrapes_paire_new($liste,$niveau) {
            $sem='';
        if($niveau==1)
            $sem=' 1,2 ';
        else
            if($niveau==2)
                $sem=' 3,4 ';
            else
                if($niveau==3)
                    $sem=' 5,6 ';
    
        $sql = '';
        $sql = "SELECT  u.`sigle` idModule,m.sigle as sigle, m.nbCredits as ects, m.titre,u.semestre as semestre FROM unite u,module m  where 
 u.sigle=m.sigleUnite and m.sigle in($liste) and  (MOD(u.semestre, 2)=0) and u.semestre not in($sem) order by 5 asc";
//        echo $sql;


        $query = $this->db->query($sql);
        if($query->num_rows>0)
        $result = $query->result();
        else 
            return null;
        
        return $result;
    }
    /*
     * Fonction retourne les elements ratrappee selon une liste a etre recevoire en parametre
     * et que ses matieres son de niveau passee , c a d que si l'etudiant est redoublant
     *elle ne retourne pas ses matieres
     * add by MedBakar
     */
    function get_module_rattrapes_impaire_new($liste,$niveau) {
        $sem='';
        if($niveau==1)
            $sem=' 1,2 ';
        else
            if($niveau==2)
                $sem=' 3,4 ';
            else
                if($niveau==3)
                    $sem=' 5,6 ';
        
        $sql = "SELECT  u.`sigle` idModule,m.sigle as sigle, m.nbCredits as ects, m.titre,u.semestre as semestre FROM unite u,module m  where 
 u.sigle=m.sigleUnite and m.sigle in($liste) and  (MOD(u.semestre, 2)=1) and u.semestre not in($sem) order by 5 asc";
//        echo $sql;


        $query = $this->db->query($sql);
         if($query->num_rows>0)
        $result = $query->result();
        else 
            return null;
        
        return $result;
    }
    
   
    /*
     * add by MedBakar
     */
    function get_module_paire_a_etudies_new($idProgramme, $niveau, $matriculeEtudiant, $an,$listeElementCapitPaire) {
//        $an = $_POST['annee'];
        
        $idProgramme = $this->get_programme_E($matriculeEtudiant);
        if($niveau==1)
            $semestre=2;
        else
            if($niveau==2)
                $semestre=4;
            else
                if($niveau==3)
                    $semestre=6;
                else
                echo'L\'étudiant a deja son licence';
        
        

        //$semestre=1;
        $sql = '';
        $sql = 'SELECT u.`sigle` as idModule,m.`sigle` as sigle, m.nbCredits as ects, m.titre FROM unite u,programme p,module m  where 
                u.sigle=m.sigleunite  and p.idProgramme = "' . $idProgramme . '" and u.semestre =' .$semestre;
        if(!empty($listeElementCapitPaire))
            $sql.=' and m.sigle not in ('.$listeElementCapitPaire.') ';
        $sql.= ' and p.idProgramme=u.idProgramme and semestreActivation<=' . $an . '3 and (semestreDesactivation>' . $an . '3 or semestreDesactivation is null or semestreDesactivation="") order by 1,2';
//         echo $sql;



        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }
    /*
     * add by MedBakar
     */
     function get_module_impaire_a_etudies_new($idProgramme, $niveau, $matriculeEtudiant, $annee,$listeElementCapitImpaire) {
        //$data['note']
        $an = $annee;
        $idProgramme = $this->get_programme_E($matriculeEtudiant);
        $semestre=0;
        if($niveau==1)
            $semestre=1;
        else
            if($niveau==2)
                $semestre=3;
            else
                if($niveau==3)
                    $semestre=5;
                else
                    echo'L\'étudiant a deja son licence';





        //$semestre=1;
        $sql = '';
        
        $sql = 'SELECT u.`sigle` as idModule,m.`sigle` as sigle, m.nbCredits as ects, m.titre FROM unite u,programme p,module m  where 
                 u.sigle=m.sigleunite  and p.idProgramme = "' . $idProgramme . '" and u.semestre ='.$semestre ;
        if(!empty($listeElementCapitImpaire))
            $sql.=' and m.sigle not in ('.$listeElementCapitImpaire.') ';
        $sql.=' and p.idProgramme=u.idProgramme and semestreActivation<=' . $an . '3 and (semestreDesactivation>' . $an . '3 or semestreDesactivation is null or semestreDesactivation="")order by 1,2';
//         echo '<br>get_module_impaire_a_etudies<br>'.$sql.'<br>';
//echo $sql;


        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;
    }

    
      
}



