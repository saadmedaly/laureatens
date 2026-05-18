<?php

class bulletin_modele extends CI_Model
{

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('scolarite_modele');
    }

    // ajoute 20:18 04/04/2019
    // Alioune ZEYN
    // script de mise a jour |
    // OBJECTIF -> transformation des views (sql) en algorithmes (codes) [PHP]
    
    // les matricules des etudiants qui existent dans la table notespartielles
    function matricules_etudiants()
    {
        $this->db->select('matriculeetudiant as matriculeEtudiant');
        $this->db->distinct();
        $this->db->order_by('matriculeEtudiant', 'ASC');
        $requete = $this->db->get('notespartielles');
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    // les notes partielles pour tous les etudiants | selection de la base de donnees
    function notespartielles()
    {
        $this->db->select('matriculeEtudiant, annee, semestre,sigle,note as note,idEvaluation');
        $this->db->order_by('matriculeEtudiant', 'ASC');
        $requete = $this->db->get('notespartielles');
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                if ($row['note'] == "" or $row['note'] < 0)
                    $row['note'] = 0;
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    // notespartielles pour une liste d'etudiants
    function notespartielles_for_list($list, $semestre, $departement, $annee)
    {
        if(empty($list))
            $list='-1';
        // $requete = $this->db->query("select matriculeEtudiant,annee,semestre,sigle,replaceWithZero(ifnull(note,0)) as note,note as exact_note,idEvaluation from notespartielles where matriculeEtudiant in ($list) order by matriculeEtudiant");
        // $requete = $this->db->query("select n.matriculeEtudiant,n.annee,n.semestre,n.sigle,replaceWithZero(ifnull(n.note,0)) as note,n.note as exact_note,n.idEvaluation,m.sigleunite,u.semestre as semestreUnite, m.nbCredits from notespartielles n,dossieretudiant de,module m,unite u where n.matriculeEtudiant=de.matriculeetudiant and n.sigle=m.sigle and m.sigleunite =u.sigle and de.idprogramme='$departement' and u.semestre='$semestre' and n.annee<='$annee' and n.matriculeetudiant not in (14162,18375) /*and n.matriculeetudiant in ($list)*/ order by n.matriculeEtudiant,u.sigle,m.sigle,n.annee,n.idEvaluation");
        $requete = $this->db->query("select n.matriculeEtudiant,n.annee,n.semestre,n.sigle,replaceWithZero(ifnull(n.note,0)) as note,n.note as exact_note,n.idEvaluation,m.sigleunite,u.semestre as semestreUnite, m.nbCredits from notespartielles n,module m,unite u where  n.sigle=m.sigle and m.sigleunite =u.sigle and u.semestre='$semestre' and n.annee<='$annee'  and n.matriculeetudiant in ($list)  order by n.matriculeEtudiant,u.sigle,m.sigle,n.annee,n.idEvaluation");
        
        // and n.matriculeetudiant in ($list)
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    function inscrits_a_s_for_list($list, $semestre, $departement, $annee)
    {if(empty($list))
        $list='-1';
        // $req="select distinct `p`.`matriculeEtudiant` AS `matriculeEtudiant`,`p`.`annee` AS `annee`,`p`.`semestre` AS `semestre` from `planetudes` `p`,dossieretudiant de ,module m,unite u where p.matriculeetudiant=de.matriculeetudiant and de.idprogramme='$departement' and p.sigle=m.sigle and m.sigleunite = u.sigle and u.semestre ='$semestre' and p.matriculeetudiant in ($list) order by p.matriculeetudiant";
        // $req="select distinct `p`.`matriculeEtudiant` AS `matriculeEtudiant`,`p`.`annee` AS `annee`,`p`.`semestre` AS `semestre` from `planetudes` `p`, dossieretudiant de where p.matriculeetudiant=de.matriculeetudiant and de.idprogramme='$departement' and p.annee<='$annee' and p.matriculeetudiant not in (14162,18375) /*and p.matriculeetudiant in ($list)*/ order by p.matriculeetudiant ,p.sigle,p.annee";
        $req = "select distinct `p`.`matriculeEtudiant` AS `matriculeEtudiant`,`p`.`annee` AS `annee`,`p`.`semestre` AS `semestre` from `planetudes` `p` where p.annee<='$annee'  and p.matriculeetudiant in ($list)  order by p.matriculeetudiant ,p.sigle,p.annee";
        
        // and p.matriculeEtudiant in ($list)
        $requete = $this->db->query($req);
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    // inscrits dans l'annee et semestre | selection de la base de donnees
    function inscrits_a_s()
    {
        $this->db->select('`matriculeEtudiant` AS `matriculeEtudiant`, `annee` AS `annee`, `semestre` AS `semestre` ');
        $this->db->order_by('`matriculeEtudiant`,annee,semestre', 'ASC');
        $this->db->distinct();
        $requete = $this->db->get('planetudes');
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    // les notes partielles pour un seul etudiant | selection de la base de donnees
    function notespartielles_pour($matriculeEtudiant)
    {
        $requete = $this->db->query("select p.matriculeEtudiant,p.annee,p.semestre,p.sigle,replaceWithZero(ifnull(n.note,0)) as note,idEvaluation from notespartielles n"
                . " right join planetudes p on (n.matriculeetudiant=p.matriculeetudiant and n.sigle=p.sigle and n.annee=p.annee and n.semestre=p.semestre)where p.matriculeEtudiant='$matriculeEtudiant'  order by p.matriculeEtudiant");
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        
        return $resultat;
    }

    function notespartielles_pour_etudiant_semestre($matriculeEtudiant, $semestre)//MedBakar-esq fonctionne 
    {
        $requete = $this->db->query("select matriculeEtudiant,n.annee,n.semestre,m.sigle,replaceWithZero(ifnull(note,0)) as note,idEvaluation,u.semestre as semMod from notespartielles n , module m , unite u where matriculeEtudiant='$matriculeEtudiant' and n.sigle = m.sigle and m.sigleunite=u.sigle and u.semestre=$semestre   order by matriculeEtudiant");
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    // inscrits dans l'annee et semestre par etudiant | selection de la base de donnees
    function inscrits_a_s_pour($matriculeEtudiant)
    {
        $requete = $this->db->query("select distinct `p`.`matriculeEtudiant` AS `matriculeEtudiant`, `p`.`annee` AS `annee`, `p`.`semestre` AS `semestre`  from `planetudes` `p` where matriculeEtudiant='$matriculeEtudiant' order by 1,2,3");
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    // selection de la table [module] ->(les matieres en realite) | selection de la base de donnees
    function module()
    {
        $requete = $this->db->query("select * from module order by sigle");
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    function module_pour_sem_dep($semestre, $departement)
    {
        $requete = $this->db->query("SELECT m.*,u.semestre FROM `module` m , unite u where m.sigleunite = u.sigle and semestre = $semestre and idDepartement like '$departement' order by m.sigle");
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    // selection de la table [unite] ->(les modules en realite) | selection de la base de donnees
    function unite()
    {
        $requete = $this->db->query("select * from unite order by sigle");
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    function unite_pour_sem_dep($semestre, $departement)
    {
        $requete = $this->db->query("select * from unite where semestre = $semestre and idProgramme like '$departement' order by sigle");
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = array();
            foreach ($requete->result_array() as $row) {
                $resultat[] = $row;
            }
        }
        return $resultat;
    }

    // get coefficients
    function getCoefficients($cycle)
    {
        $this->db->select('cc as cc ,exam as exam');
        $this->db->where(array(
            'dateDesactivation' => null,
            'cycle' => $cycle
        ));
        $requete = $this->db->get('coefficient');
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = $requete->result_array()[0];
        }
        return $resultat;
    }

    // retourne la note eliminatoire courante de matiere
    function get_note_elimination_matiere_courante($cycle)
    {
        $this->db->select('note as note');
        $this->db->where(array(
            'date_fin' => null,
            'cycle' => $cycle
        ));
        $requete = $this->db->get('note_elimination_matiere');
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = $requete->result_array()[0];
            $resultat = $resultat['note'];
        }
        return $resultat;
    }

    // pour extraire la partie de la table qui concerne l'etudiant $matriculeEtudiant, les tables consenee sont (notespartielles,inscrit_a_s)
    function extract_table_for_etudiant($table, $matriculeEtudiant)
    {
        $result = array();
        $cpt = 0;
        
        foreach ($table as $key => $value) {
            if ($value['matriculeEtudiant'] == $matriculeEtudiant) {
                $result[$cpt] = $value;
                $cpt ++;
            } else {
                if ($cpt > 0)
                    break;
                continue;
            }
        }
        return $result;
    }

    // retourne un array de cette facon array([matricule]=>array([debut]=>indexDebut,[fin]=> indexFin))
    function indexation_matricules_notespartielles($notespartielles)
    { 
//        $r=array('l'=>'h');
//        return $r ;
//     
        if(empty($notespartielles)){
            return null;
        } 
        $colonne = array_column($notespartielles, 'matriculeEtudiant');
        array_multisort($colonne, SORT_ASC, $notespartielles);
        $matricules = array();
        foreach ($notespartielles as $i => $value) {
            if ($i == 0) {
                $matricules[$value['matriculeEtudiant']]['debut'] = $i;
                if(count($notespartielles) == 1){
                    $matricules[$value['matriculeEtudiant']]['fin'] = $i;
                
                }
         
                continue;
            } // $matricules[$value['matriculeEtudiant']]['matriculeEtudiant']=$value['matriculeEtudiant'];
            if ($value['matriculeEtudiant'] != $notespartielles[$i - 1]['matriculeEtudiant']) {
                $matricules[$notespartielles[$i - 1]['matriculeEtudiant']]['fin'] = $i - 1;
                $matricules[$value['matriculeEtudiant']]['debut'] = $i;
                if (($i + 1) == count($notespartielles))
                    $matricules[$value['matriculeEtudiant']]['fin'] = $i;
                continue;
            }
            if (($i + 1) == count($notespartielles))
                $matricules[$value['matriculeEtudiant']]['fin'] = $i;
        }
        return $matricules;
    }

    // extraction des notespartielles avec l'indexation
    function get_notespartielles_with_indexation_for($matriculeEtudiant, $notespartielles, $indexation)
    {
        if (array_key_exists($matriculeEtudiant, $indexation)) {
            $output = array_slice($notespartielles, $indexation[$matriculeEtudiant]['debut'], $indexation[$matriculeEtudiant]['fin'] - $indexation[$matriculeEtudiant]['debut'] + 1);
            return $output;
        }
    }

    /* debut code view [notespartielles_a_s] */
    /*
     * fonction pour generer une table(array) qui simule la vue [notespartielles_a_s] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function notespartielles_a_s($matriculeEtudiant, $inscrits_a_s, $notespartielles, $indexation)
    {
        $cpt = 0;
        $notespartielles_a_s = array();
        $debut = 0;
        for ($i = 0, $i_count = count($inscrits_a_s); $i < $i_count; $i ++) {
            if (! array_key_exists($inscrits_a_s[$i]['matriculeEtudiant'], $indexation)) {
                // echo $inscrits_a_s[$i]['matriculeEtudiant'] ." _ M _";
                continue;
            }
            $row = array();
            $row['annee'] = $inscrits_a_s[$i]['annee'];
            
            for ($j = $indexation[$inscrits_a_s[$i]['matriculeEtudiant']]['debut']; $j < $indexation[$inscrits_a_s[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($cpt == 0) {
                    $debut = 0;
                } elseif ($notespartielles_a_s[$cpt - 1]['matriculeEtudiant'] != $notespartielles[$j]['matriculeEtudiant']) {
                    $debut = $cpt;
                }
                
                $index_annee_a_max = $this->max_annee_notespartielles($notespartielles, $indexation[$inscrits_a_s[$i]['matriculeEtudiant']]['debut'], $indexation[$inscrits_a_s[$i]['matriculeEtudiant']]['fin'], $notespartielles[$j]['matriculeEtudiant'], $notespartielles[$j]['sigle'], $inscrits_a_s[$i]['annee']);
                // si l'annee
                if ($index_annee_a_max == (- 1)) {
                    continue;
                } else {
                    if ($notespartielles[$j]['annee'] == $notespartielles[$index_annee_a_max]['annee']) {
                        // verification de l'existence du meme enregistrement (distinct)
                        $row['matriculeEtudiant'] = $notespartielles[$j]['matriculeEtudiant'];
                        $row['semestre'] = $notespartielles[$j]['semestre'];
                        if(!empty($notespartielles[$j]['semestreUnite']))
                        $row['semestre_etud'] = $notespartielles[$j]['semestreUnite'];
                        $row['sigle'] = $notespartielles[$j]['sigle'];
                        $row['note'] = $notespartielles[$j]['note'];
                        $row['idEvaluation'] = $notespartielles[$j]['idEvaluation'];
                        // si le meme enregistrement existe deja on passe a un autre
                        if ($this->notespartielles_a_s_contains($notespartielles_a_s, $row, $debut)) {
                            continue;
                        }
                        $notespartielles_a_s[$cpt] = $row;
                        $cpt ++;
                    }
                }
            }
        }
        
        return $notespartielles_a_s;
    }

    // retourne true si la ligne [$row] existe, retourne false sinon --existe dans la table
    function notespartielles_a_s_contains($notespartielles_a_s, $row, $debut = 0)
    {
        for ($i = $debut, $i_count = count($notespartielles_a_s); $i < count($notespartielles_a_s); $i ++) {
            if ($row['annee'] == $notespartielles_a_s[$i]['annee'])
                if ($row['semestre'] == $notespartielles_a_s[$i]['semestre'])
                    if (strcasecmp($row['sigle'], $notespartielles_a_s[$i]['sigle']) == 0)
                        if ($row['note'] == $notespartielles_a_s[$i]['note'])
                            if ($row['idEvaluation'] == $notespartielles_a_s[$i]['idEvaluation']) {
                                return true;
                            }
        }
        return false;
    }

    // retourne l'index de l'annee max utilisee par notespartielles_a_s
    function max_annee_notespartielles($notespartielles, $debut, $fin, $matriculeEtudiant, $sigle, $annee)
    {
        $max = - 1;
        $index = - 1;
        for ($i = $debut, $i_count = $fin + 1; $i < $i_count; $i ++) {
            if ($notespartielles[$i]['matriculeEtudiant'] == $matriculeEtudiant)
                if (strcasecmp($sigle, $notespartielles[$i]['sigle']) == 0) {
                    if ($notespartielles[$i]['annee'] >= $max) {
                        if ($notespartielles[$i]['annee'] <= $annee) {
                            $max = $notespartielles[$i]['annee'];
                            $index = $i;
                        }
                    }
                }
        }
        return $index;
    }

    /* fin code view [notespartielles_a_s] */
    
    /* Debut extraction des notes de cc,exam et ratrappage */
    /*
     * extraire les notes selon $idEvaluation->(1:cc,2:exam,4:rattrapage)
     * cette fonction est utilisee par les fonctions(view) [cc],[exam],[examRT]
     * elle retourne les notes de l'etudiant selon l'idEvaluation
     */
    function extraire_notes($matriculeEtudiant, $notespartielles_a_s, $module, $unite, $idEvaluation)
    {
        $result = array();
        $cpt = 0;
        for ($i = 0, $np_count = count($notespartielles_a_s); $i < $np_count; $i ++) {
            if (! $notespartielles_a_s[$i]['matriculeEtudiant'] == $matriculeEtudiant)
                continue;
            $j = $this->trouver_index_sigle($notespartielles_a_s[$i]['sigle'], $module);
            if ($j == - 1)
                continue;
            $k = $this->trouver_index_sigle($module[$j]['sigleunite'], $unite);
            if ($k == - 1)
                continue;
            
            if ($notespartielles_a_s[$i]['idEvaluation'] == $idEvaluation  || ($idEvaluation==1 && empty($notespartielles_a_s[$i]['idEvaluation']))) {
                $result[$cpt]['matriculeEtudiant'] = $notespartielles_a_s[$i]['matriculeEtudiant'];
                $result[$cpt]['sigle'] = $notespartielles_a_s[$i]['sigle'];
                $result[$cpt]['note'] = $notespartielles_a_s[$i]['note'];
                $result[$cpt]['ev'] = 'CC';
                /*
                 * pour l'annee universitaire:
                 * ex pour annee=2019 si semstre=3 annee=1920
                 * si semestre=1 annee=1819
                 */
                if ($notespartielles_a_s[$i]['semestre'] == "3") {
                    $result[$cpt]['annee'] = $this->annee_universitaire($notespartielles_a_s[$i]['annee']);
                } elseif ($notespartielles_a_s[$i]['semestre'] == "1") {
                    $result[$cpt]['annee'] = $this->annee_universitaire($notespartielles_a_s[$i]['annee'] - 1);
                }
                $result[$cpt]['idEvaluation'] = $notespartielles_a_s[$i]['idEvaluation'];
                //pour un etudiant qui n'a pas de note partielle, on met l'id evaluation =1 pour eviter le nul
                if($idEvaluation==1 && empty($notespartielles_a_s[$i]['idEvaluation'])){
                    $result[$cpt]['idEvaluation'] = 1;
                }
                $result[$cpt]['semestre'] = $unite[$k]['semestre'];
                $result[$cpt]['year'] = $notespartielles_a_s[$i]['annee'];
                $result[$cpt]['semestreB'] = $notespartielles_a_s[$i]['semestre'];
                
                $cpt ++;
            }
        }
        
        return $result;
    }

    // retourne l'index de la ligne dont le sigle = $sigle ; $table soit =module ou unite
    function trouver_index_sigle($sigle, $table)
    {
        $index1 = - 1;
        for ($i = 0; $i < count($table); $i ++) {
            // if($table[$i]['sigle']==$sigle){
            if (strcasecmp($table[$i]['sigle'], $sigle) == 0) {
                $index1 = $i;
                break;
            }
        }
        return $index1;
    }

    // role: input=2019 -> output=1920
    function annee_universitaire($annee)
    {
        return substr($annee, - 2) . "" . (substr($annee, - 2) + 1);
    }

    // retourne si la note est null[ou vide], sinon retourne la note
    function replaceWithZero($note)
    {
        if ($note == "" or $note < 0)
            return 0;
        else
            return $note;
    }

    /* fin tache extraction des notes */
    
    /* debut code view [cc] */
    /*
     * fonction pour generer une table(array) qui simule la vue [cc] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function cc($matriculeEtudiant, $notespartielles_a_s, $module, $unite)
    {
        return $this->extraire_notes($matriculeEtudiant, $notespartielles_a_s, $module, $unite, 1);
    }

    /* fin code view [cc] */
    
    /* Debut code view [exam] */
    /*
     * fonction pour generer une table(array) qui simule la vue [exam] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function exam($matriculeEtudiant, $notespartielles_a_s, $module, $unite)
    {
        return $this->extraire_notes($matriculeEtudiant, $notespartielles_a_s, $module, $unite, 2);
    }

    /* fin code view [exam] */
    
    /* Debut code view [examRT] */
    /*
     * fonction pour generer une table(array) qui simule la vue [examRT] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     * utilise [func extraction_notes]
     */
    function examRT($matriculeEtudiant, $notespartielles_a_s, $module, $unite)
    {
        return $this->extraire_notes($matriculeEtudiant, $notespartielles_a_s, $module, $unite, 4);
    }

    /* fin code view [exam] */
    
    /* Debut code view [cc_exam] */
    /*
     * fonction pour generer une table(array) qui simule la vue [cc_exam] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function cc_exam($matriculeEtudiant, $cc, $exam)
    {
        $cc_exam = array();
        $cpt = 0;
        for ($i = 0; $i < count($cc); $i ++) {
            if ($cc[$i]['matriculeEtudiant'] != $matriculeEtudiant)
                continue;
            $cc_exam[$cpt]['matriculeEtudiant'] = $cc[$i]['matriculeEtudiant'];
            $cc_exam[$cpt]['sigle'] = $cc[$i]['sigle'];
            $cc_exam[$cpt]['noteCC'] = $cc[$i]['note'];
            // premierement noteExam=0.0, si on trouve une note pour l'exam on la ramplace
            $cc_exam[$cpt]['noteExam'] = '0.0';
            $cc_exam[$cpt]['annee'] = $cc[$i]['annee'];
            $cc_exam[$cpt]['semestre'] = $cc[$i]['semestre'];
            $cc_exam[$cpt]['year'] = $cc[$i]['year'];
            $cc_exam[$cpt]['semestreB'] = $cc[$i]['semestreB'];
            for ($j = 0; $j < count($exam); $j ++) {
                if ($cc[$i]['sigle'] == $exam[$j]['sigle']) {
                    if ($cc[$i]['semestre'] == $exam[$j]['semestre']) {
                        if ($cc[$i]['annee'] == $exam[$j]['annee']) {
                            $cc_exam[$cpt]['noteExam'] = $exam[$j]['note'];
                            // dans l'ensemble (sigle,semestre,annee) il n'y a qu'une seulle note d'exam
                            // donc on fait break;
                            break;
                        }
                    }
                }
            }
            $cpt ++;
        }
        for ($i = 0; $i < count($exam); $i ++) {
            if ($exam[$i]['matriculeEtudiant'] != $matriculeEtudiant)
                continue;
            $row['matriculeEtudiant'] = $exam[$i]['matriculeEtudiant'];
            $row['sigle'] = $exam[$i]['sigle'];
            // premierement noteCC=0.0 , si on trouve une autre valeur on lui ramplace
            $row['noteCC'] = '0.0';
            $row['noteExam'] = $exam[$i]['note'];
            $row['annee'] = $exam[$i]['annee'];
            $row['semestre'] = $exam[$i]['semestre'];
            $row['year'] = $exam[$i]['year'];
            $row['semestreB'] = $exam[$i]['semestreB'];
            for ($j = 0; $j < count($cc); $j ++) {
                if ($exam[$i]['sigle'] == $cc[$j]['sigle']) {
                    if ($exam[$i]['semestre'] == $cc[$j]['semestre']) {
                        try {
                            if ($exam[$i]['annee'] == $cc[$j]['annee']) {
                                $row['noteCC'] = $cc[$j]['note'];
                                // dans l'ensemble (sigle,semestre,annee) il n'y a qu'une seulle note cc
                                // donc on fait break;
                                break;
                            }
                        } catch (Exception $exp) {
                            echo "<h1>Exception, $matriculeEtudiant</h1>";
                        }
                    }
                }
            }
            // on verifi pour ne pas doubler un enregistrement
            if ($this->cc_exam_contains($cc_exam, $row)) {
                continue;
            }
            // affectation cc_exam<-row
            $cc_exam[$cpt] = $row;
            $cpt ++;
        }
        return $cc_exam;
    }

    // retourne true si la ligne [$row] existe, retourne false sinon --existe dans la table
    function cc_exam_contains($cc_exam, $row, $debut = 0)
    {
        for ($i = $debut; $i < count($cc_exam); $i ++) {
            if ($row['matriculeEtudiant'] == $cc_exam[$i]['matriculeEtudiant'])
                if ($row['annee'] == $cc_exam[$i]['annee'])
                    // if ($row['sigle'] == $cc_exam[$i]['sigle'])
                    if (strcasecmp($row['sigle'], $cc_exam[$i]['sigle']) == 0)
                        if ($row['noteCC'] == $cc_exam[$i]['noteCC'])
                            if ($row['noteExam'] == $cc_exam[$i]['noteExam'])
                                if ($row['semestre'] == $cc_exam[$i]['semestre'])
                                    if ($row['year'] == $cc_exam[$i]['year'])
                                        if ($row['semestreB'] == $cc_exam[$i]['semestreB'])
                                            return true;
        }
        return false;
    }

    /* fin code view [cc_exam] */
    
    /* Debut code view [notes_globales] */
    /*
     * fonction pour generer une table(array) qui simule la vue [notes_globales] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function notes_globales_v2($matriculeEtudiant, $notespartielles_a_s, $module, $unite)
    {
        $infos = array();
        $cc = array();
        $exam = array();
        $examRT = array();
        $cpt_cc = 0;
        $cpt_exam = 0;
        $cpt_examRT = 0;
        
        // classificatin des notes selon idEvaluation cc,exam,ratrap
        for ($i = 0, $np_count = count($notespartielles_a_s); $i < $np_count; $i ++) {
            // if (! $notespartielles_a_s[$i]['matriculeEtudiant'] == $matriculeEtudiant)
            // continue;
            $j = $this->trouver_index_sigle($notespartielles_a_s[$i]['sigle'], $module);
            if ($j == - 1) {
                continue;
            }
            
            $k = $this->trouver_index_sigle($module[$j]['sigleunite'], $unite);
            if ($k == - 1) {
                continue;
            }
            $infos['matriculeEtudiant'] = $notespartielles_a_s[$i]['matriculeEtudiant'];
            $infos['sigle'] = $notespartielles_a_s[$i]['sigle'];
            $infos['note'] = $notespartielles_a_s[$i]['note'];
            $infos['ev'] = 'CC';
            if ($notespartielles_a_s[$i]['semestre'] == "3") {
                $infos['annee'] = $this->annee_universitaire($notespartielles_a_s[$i]['annee']);
            } elseif ($notespartielles_a_s[$i]['semestre'] == "1") {
                $infos['annee'] = $this->annee_universitaire($notespartielles_a_s[$i]['annee'] - 1);
            }
            $infos['idEvaluation'] = $notespartielles_a_s[$i]['idEvaluation'];
            $infos['semestre'] = $unite[$k]['semestre'];
            $infos['year'] = $notespartielles_a_s[$i]['annee'];
            $infos['semestreB'] = $notespartielles_a_s[$i]['semestre'];
            $idEvaluation = $notespartielles_a_s[$i]['idEvaluation'];
            $infos['index_module'] = $j;
            $infos['index_unite'] = $k;
            switch ($idEvaluation) {
                case 1:
                    $cc[$cpt_cc] = $infos;
                    $cpt_cc ++;
                    break;
                case 2:
                    $exam[$cpt_exam] = $infos;
                    $cpt_exam ++;
                    break;
                case 4:
                    $examRT[$cpt_examRT] = $infos;
                    $cpt_examRT ++;
                    break;
            } /*
               * if ($idEvaluation==1) {
               * $cc[$cpt_cc] = $infos;
               * $cpt_cc ++;
               * }elseif($idEvaluation==2){
               * $exam[$cpt_exam] = $infos;
               * $cpt_exam ++;
               * }elseif($idEvaluation==4){
               * $examRT[$cpt_examRT] = $infos;
               * $cpt_examRT ++;
               * }
               */
        }
        
        $indexation_cc = $this->indexation_matricules_notespartielles($cc);
        $indexation_exam = $this->indexation_matricules_notespartielles($exam);
        $indexation_examRT = $this->indexation_matricules_notespartielles($examRT);
        // echo "count: indexation_cc: ".count($indexation_cc).", count: indexation_exam: ".count($indexation_exam).", count: indexation_examRT: ".count($indexation_examRT).
        $cc_exam = array();
        $cpt_cc_exam = 0;
        $cc_exam_debut = 0;
        for ($i = 0, $count_cc = count($cc); $i < $count_cc; $i ++) {
            $row['matriculeEtudiant'] = $cc[$i]['matriculeEtudiant'];
            $row['sigle'] = $cc[$i]['sigle'];
            $row['noteCC'] = $cc[$i]['note'];
            // premierement noteExam=0.0, si on trouve une note pour l'exam on la ramplace
            $row['noteExam'] = '0.0';
            $row['annee'] = $cc[$i]['annee'];
            $row['semestre'] = $cc[$i]['semestre'];
            $row['year'] = $cc[$i]['year'];
            $row['semestreB'] = $cc[$i]['semestreB'];
            
            // if(!array_key_exists ( $cc[$i]['matriculeEtudiant'] , $indexation_exam )){
            if (! isset($indexation_exam[$cc[$i]['matriculeEtudiant']])) {
                $indexation_exam[$cc[$i]['matriculeEtudiant']]['debut'] = 1;
                $indexation_exam[$cc[$i]['matriculeEtudiant']]['fin'] = 0;
            }
            for ($j = $indexation_exam[$cc[$i]['matriculeEtudiant']]['debut']; $j < $indexation_exam[$cc[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($cc[$i]['matriculeEtudiant'] == $exam[$j]['matriculeEtudiant'])
                    // if ($cc[$i]['sigle'] == $exam[$j]['sigle']) {
                    if (strcasecmp($cc[$i]['sigle'], $exam[$j]['sigle']) == 0) {
                        if ($cc[$i]['semestre'] == $exam[$j]['semestre']) {
                            if ($cc[$i]['annee'] == $exam[$j]['annee']) {
                                $row['noteExam'] = $exam[$j]['note'];
                                // dans l'ensemble (sigle,semestre,annee) il n'y a qu'une seulle note d'exam
                                // donc on fait break;
                                break;
                            }
                        }
                    }
            }
            if ($cpt_cc_exam > 0)
                if ($cc_exam[$cpt_cc_exam - 1]['matriculeEtudiant'] != $cc[$i]['matriculeEtudiant'])
                    $cc_exam_debut = $cpt_cc_exam;
            
            if ($this->cc_exam_contains($cc_exam, $row, $cc_exam_debut)) {
                continue;
            }
            $cc_exam[$cpt_cc_exam] = $row;
            $cpt_cc_exam ++;
        }
        for ($i = 0, $count_exam = count($exam); $i < $count_exam; $i ++) {
            $row['matriculeEtudiant'] = $exam[$i]['matriculeEtudiant'];
            $row['sigle'] = $exam[$i]['sigle'];
            // premierement noteCC=0.0 , si on trouve une autre valeur on lui remplace
            $row['noteCC'] = '0.0';
            $row['noteExam'] = $exam[$i]['note'];
            $row['annee'] = $exam[$i]['annee'];
            $row['semestre'] = $exam[$i]['semestre'];
            $row['year'] = $exam[$i]['year'];
            $row['semestreB'] = $exam[$i]['semestreB'];
            for ($j = $indexation_cc[$exam[$i]['matriculeEtudiant']]['debut'], $count_cc = count($cc); $j < $indexation_cc[$exam[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($cc[$j]['matriculeEtudiant'] == $exam[$i]['matriculeEtudiant'])
                    // if ($exam[$i]['sigle'] == $cc[$j]['sigle']) {
                    if (strcasecmp($exam[$i]['sigle'], $cc[$j]['sigle']) == 0) {
                        if ($exam[$i]['semestre'] == $cc[$j]['semestre']) {
                            // pour verifier si l'etudiant n'a pas un probleme avec l'annee
                            try {
                                if ($exam[$i]['annee'] == $cc[$j]['annee']) {
                                    $row['noteCC'] = $cc[$j]['note'];
                                    // dans l'ensemble (sigle,semestre,annee) il n'y a qu'une seulle note cc
                                    // donc on fait break;
                                    break;
                                }
                            } catch (Exception $exp) {
                                echo "<h1>Exception, $matriculeEtudiant</h1>";
                            }
                        }
                    }
            }
            // on verifi pour ne pas doubler un enregistrement
            if ($this->cc_exam_contains($cc_exam, $row)) {
                continue;
            }
            // affectation cc_exam<-row
            $cc_exam[$cpt_cc_exam] = $row;
            $cpt_cc_exam ++;
        }
        
        $notes_globales = array();
        $cpt = 0;
        for ($i = 0, $count_cc_exam = count($cc_exam); $i < $count_cc_exam; $i ++) {
            $notes_globales[$cpt]['matriculeEtudiant'] = $cc_exam[$i]['matriculeEtudiant'];
            $notes_globales[$cpt]['sigle'] = $cc_exam[$i]['sigle'];
            $notes_globales[$cpt]['noteCC'] = $cc_exam[$cpt]['noteCC'];
            $notes_globales[$cpt]['noteExam'] = $cc_exam[$cpt]['noteExam'];
            // premierement noteRT=0.0, si on trouve une note pour l'exam on la ramplace
            $notes_globales[$cpt]['noteRT'] = '0.0';
            $notes_globales[$cpt]['annee'] = $cc_exam[$i]['annee'];
            $notes_globales[$cpt]['semestre'] = $cc_exam[$i]['semestre'];
            $notes_globales[$cpt]['year'] = $cc_exam[$i]['year'];
            $notes_globales[$cpt]['semestreB'] = $cc_exam[$i]['semestreB'];
            if (! isset($indexation_examRT[$cc_exam[$i]['matriculeEtudiant']])) {
                $cpt ++;
                // echo "- ".$cc_exam[$i]['matriculeEtudiant'];
                continue; // $indexation_cc[$exam[$i]['matriculeEtudiant']]['debut']
            }
            for ($j = $indexation_examRT[$cc_exam[$i]['matriculeEtudiant']]['debut'], $count_examRt = count($examRT); $j < $count_examRt; $j ++) {
                if ($cc_exam[$i]['matriculeEtudiant'] == $examRT[$j]['matriculeEtudiant'])
                    // if ($cc_exam[$i]['sigle'] == $examRT[$j]['sigle']) {
                    if (strcasecmp($cc_exam[$i]['sigle'], $examRT[$j]['sigle']) == 0) {
                        if ($cc_exam[$i]['semestre'] == $examRT[$j]['semestre']) {
                            if ($cc_exam[$i]['annee'] == $examRT[$j]['annee']) {
                                $notes_globales[$cpt]['noteRT'] = $examRT[$j]['note'];
                                // dans l'ensemble (sigle,semestre,annee) il n'y a qu'une seulle note d'exam
                                // donc on fait break;
                                break;
                            }
                        }
                    }
            }
            $cpt ++;
        }
        
        // $cc_exam=$this->tri_cc_exam_bis_par_annee($cc_exam);
        $colonne = array_column($cc_exam, 'matriculeEtudiant');
        array_multisort($colonne, SORT_ASC, $cc_exam);
        $indexation_cc_exam = $this->indexation_matricules_notespartielles($cc_exam);
        
        for ($i = 0, $count_examRt = count($examRT); $i < $count_examRt; $i ++) {
            $row['matriculeEtudiant'] = $examRT[$i]['matriculeEtudiant'];
            $row['sigle'] = $examRT[$i]['sigle'];
            // premierement noteCC=0.0 et noteExam==0.0, si on trouve une autre valeur on lui ramplace
            $row['noteCC'] = '0.0';
            $row['noteExam'] = '0.0';
            $row['noteRT'] = $examRT[$i]['note'];
            $row['annee'] = $examRT[$i]['annee'];
            $row['semestre'] = $examRT[$i]['semestre'];
            $row['year'] = $examRT[$i]['year'];
            $row['semestreB'] = $examRT[$i]['semestreB'];
            for ($j = $indexation_cc_exam[$examRT[$i]['matriculeEtudiant']]['debut']; $j < $indexation_cc_exam[$examRT[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                // if($examRT[$i]['matriculeEtudiant']==$cc_exam[$j]['matriculeEtudiant'])
                // if ($examRT[$i]['sigle'] == $cc_exam[$j]['sigle']) {
                if (strcasecmp($examRT[$i]['sigle'], $cc_exam[$j]['sigle']) == 0) {
                    if ($examRT[$i]['semestre'] == $cc_exam[$j]['semestre']) {
                        if ($examRT[$i]['annee'] == $cc_exam[$j]['annee']) {
                            $row['noteCC'] = $cc_exam[$j]['noteCC'];
                            $row['noteExam'] = $cc_exam[$j]['noteExam'];
                            // dans l'ensemble (sigle,semestre,annee) il n'y a qu'une seulle note cc
                            // donc on fait break;
                            break;
                        }
                    }
                }
            }
            // on verifi pour ne pas doubler un enregistrement
            if ($this->notes_globales_contains($notes_globales, $row)) {
                continue;
            }
            // affectation cc_exam<-row
            $notes_globales[$cpt] = $row;
            $cpt ++;
        }
        echo "<br>count_module: " . count($module) . " , count_unite: " . count($unite) . "<br>count_cc: " . count($cc) . " ,count_examRT:" . count($examRT) . " ,count_exam:" . count($exam) . " , count_cc_exam: " . count($cc_exam) . " count_notspatlles" . count($notespartielles_a_s) . "<br>";
        return $notes_globales;
    }

    // tri par matricule
    function tri_cc_exam_bis_par_annee($cc_exam)
    {
        for ($i = 0; $i < count($cc_exam) - 1; $i ++) {
            for ($j = $i + 1; $j < count($cc_exam); $j ++) {
                if ($cc_exam[$i]['matriculeEtudiant'] > $cc_exam[$j]['matriculeEtudiant']) {
                    $tmp = $cc_exam[$i];
                    $cc_exam[$i] = $cc_exam[$j];
                    $cc_exam[$j] = $tmp;
                }
            }
        }
        return $cc_exam;
    }

    function notes_globales($matriculeEtudiant, $cc_exam, $examRT)
    {
        $notes_globales = array();
        $cpt = 0;
        for ($i = 0; $i < count($cc_exam); $i ++) {
            $notes_globales[$cpt]['matriculeEtudiant'] = $cc_exam[$i]['matriculeEtudiant'];
            $notes_globales[$cpt]['sigle'] = $cc_exam[$i]['sigle'];
            $notes_globales[$cpt]['noteCC'] = $cc_exam[$cpt]['noteCC'];
            $notes_globales[$cpt]['noteExam'] = $cc_exam[$cpt]['noteExam'];
            // premierement noteRT=0.0, si on trouve une note pour l'exam on la ramplace
            $notes_globales[$cpt]['noteRT'] = '0.0';
            $notes_globales[$cpt]['annee'] = $cc_exam[$i]['annee'];
            $notes_globales[$cpt]['semestre'] = $cc_exam[$i]['semestre'];
            $notes_globales[$cpt]['year'] = $cc_exam[$i]['year'];
            $notes_globales[$cpt]['semestreB'] = $cc_exam[$i]['semestreB'];
            for ($j = 0; $j < count($examRT); $j ++) {
                // if ($cc_exam[$i]['sigle'] == $examRT[$j]['sigle']) {
                if (strcasecmp($cc_exam[$i]['sigle'], $examRT[$j]['sigle']) == 0) {
                    if ($cc_exam[$i]['semestre'] == $examRT[$j]['semestre']) {
                        if ($cc_exam[$i]['annee'] == $examRT[$j]['annee']) {
                            $notes_globales[$cpt]['noteRT'] = $examRT[$j]['note'];
                            // dans l'ensemble (sigle,semestre,annee) il n'y a qu'une seulle note d'exam
                            // donc on fait break;
                            break;
                        }
                    }
                }
            }
            $cpt ++;
        }
        for ($i = 0; $i < count($examRT); $i ++) {
            $row['matriculeEtudiant'] = $examRT[$i]['matriculeEtudiant'];
            $row['sigle'] = $examRT[$i]['sigle'];
            // premierement noteCC=0.0 et noteExam==0.0, si on trouve une autre valeur on lui ramplace
            $row['noteCC'] = '0.0';
            $row['noteExam'] = '0.0';
            $row['noteRT'] = $examRT[$i]['note'];
            $row['annee'] = $examRT[$i]['annee'];
            $row['semestre'] = $examRT[$i]['semestre'];
            $row['year'] = $examRT[$i]['year'];
            $row['semestreB'] = $examRT[$i]['semestreB'];
            for ($j = 0; $j < count($cc_exam); $j ++) {
                // if ($examRT[$i]['sigle'] == $cc_exam[$j]['sigle']) {
                if (strcasecmp($examRT[$i]['sigle'], $cc_exam[$j]['sigle']) == 0) {
                    if ($examRT[$i]['semestre'] == $cc_exam[$j]['semestre']) {
                        if ($examRT[$i]['annee'] == $cc_exam[$j]['annee']) {
                            $row['noteCC'] = $cc_exam[$j]['noteCC'];
                            $row['noteExam'] = $cc_exam[$j]['noteExam'];
                            // dans l'ensemble (sigle,semestre,annee) il n'y a qu'une seulle note cc
                            // donc on fait break;
                            break;
                        }
                    }
                }
            }
            // on verifi pour ne pas doubler un enregistrement
            if ($this->notes_globales_contains($notes_globales, $row)) {
                continue;
            }
            // affectation cc_exam<-row
            $notes_globales[$cpt] = $row;
            $cpt ++;
        }
        return $notes_globales;
    }
                
    // verification de l'existence du meme enregistrement pour eliminer les doublons
    function notes_globales_contains($notes_globales, $row)
    {
        for ($i = 0; $i < count($notes_globales); $i ++) {
            if ($row['matriculeEtudiant'] == $notes_globales[$i]['matriculeEtudiant'])
                if ($row['annee'] == $notes_globales[$i]['annee'])
                    // if ($row['sigle'] == $notes_globales[$i]['sigle'])
                    if (strcasecmp($row['sigle'], $notes_globales[$i]['sigle']) == 0)
                        if ($row['noteCC'] == $notes_globales[$i]['noteCC'])
                            if ($row['noteExam'] == $notes_globales[$i]['noteExam'])
                                if ($row['noteRT'] == $notes_globales[$i]['noteRT'])
                                    if ($row['semestre'] == $notes_globales[$i]['semestre'])
                                        if ($row['year'] == $notes_globales[$i]['year'])
                                            if ($row['semestreB'] == $notes_globales[$i]['semestreB'])
                                                return true;
        }
        return false;
    }

    // version plus rapide
    function notes_globales_v3($matriculeEtudiant, $notespartielles_a_s)
    {
       
        $notes_globales = array();
        $cpt = 0;
        
        $indexation = $this->indexation_matricules_notespartielles($notespartielles_a_s);
        
        $debut = 0;
        
        for ($i = 0, $np_count = count($notespartielles_a_s); $i < $np_count; $i ++) {
            $row = array();
            $row['matriculeEtudiant'] = $notespartielles_a_s[$i]['matriculeEtudiant'];
            $row['sigle'] = $notespartielles_a_s[$i]['sigle'];
            $row['year'] = $notespartielles_a_s[$i]['annee'];
            $row['semestreB'] = $notespartielles_a_s[$i]['semestre'];
            if ($this->notes_globales_contains_an_sem_sig($notes_globales, $row, $debut)) {
                continue;
            }
            if ($notespartielles_a_s[$i]['semestre'] == "3") {
                $row['annee'] = $this->annee_universitaire($notespartielles_a_s[$i]['annee']);
            } elseif ($notespartielles_a_s[$i]['semestre'] == "1") {
                $row['annee'] = $this->annee_universitaire($notespartielles_a_s[$i]['annee'] - 1);
            }
           // $row['semestre'] = $notespartielles_a_s[$i]['sigle'][3];
             $row['semestre'] = $notespartielles_a_s[$i]['semestre_etud'];///*/*[3]<= must be changed by MedBakar 12-03-2020 --errorISMS
            $row['noteCC'] = 0.00;
            $row['noteExam'] = 0.00;
            $row['noteRT'] = 0.00;
            
            for ($i_2 = $indexation[$notespartielles_a_s[$i]['matriculeEtudiant']]['debut'], $np_count_2 = $indexation[$notespartielles_a_s[$i]['matriculeEtudiant']]['fin'] + 1; $i_2 < $np_count_2; $i_2 ++) {
                if (strcasecmp($notespartielles_a_s[$i]['sigle'], $notespartielles_a_s[$i_2]['sigle']) == 0) {
                    if ($notespartielles_a_s[$i]['semestre'] == $notespartielles_a_s[$i_2]['semestre']) {
                        if ($notespartielles_a_s[$i]['annee'] == $notespartielles_a_s[$i_2]['annee']) {
                            if ($notespartielles_a_s[$i_2]['idEvaluation'] == 1) {
                                $row['noteCC'] = $notespartielles_a_s[$i_2]['note'];
                            } elseif ($notespartielles_a_s[$i_2]['idEvaluation'] == 2) {
                                $row['noteExam'] = $notespartielles_a_s[$i_2]['note'];
                            } elseif ($notespartielles_a_s[$i_2]['idEvaluation'] == 4) {
                                $row['noteRT'] = $notespartielles_a_s[$i_2]['note'];
                            }
                        }
                    }
                }
                if ($i_2 == $indexation[$notespartielles_a_s[$i]['matriculeEtudiant']]['fin']) {
                    if (count($notes_globales) > 0 and $cpt > 0) {
                        if ($notes_globales[$cpt - 1]['matriculeEtudiant'] != $notespartielles_a_s[$i_2]['matriculeEtudiant']) {
                            $debut = $cpt;
                        }
                    }
                    if ($this->notes_globales_contains_($notes_globales, $row, $debut)) {
                        continue;
                    }
                    // affectation cc_exam<-row
                    $notes_globales[$cpt] = $row;
                    $cpt ++;
                }
            }
        }
        return $notes_globales;
    }

    // ********************
    function notes_globales_contains_($notes_globales, $row, $debut)
    {
        for ($i = $debut; $i < count($notes_globales); $i ++) {
            if ($row['matriculeEtudiant'] == $notes_globales[$i]['matriculeEtudiant'])
                if ($row['annee'] == $notes_globales[$i]['annee'])
                    // if ($row['sigle'] == $notes_globales[$i]['sigle'])
                    if (strcasecmp($row['sigle'], $notes_globales[$i]['sigle']) == 0)
                        if ($row['noteCC'] == $notes_globales[$i]['noteCC'])
                            if ($row['noteExam'] == $notes_globales[$i]['noteExam'])
                                if ($row['noteRT'] == $notes_globales[$i]['noteRT'])
                                    if ($row['semestre'] == $notes_globales[$i]['semestre'])
                                        if ($row['year'] == $notes_globales[$i]['year'])
                                            if ($row['semestreB'] == $notes_globales[$i]['semestreB'])
                                                return true;
        }
        return false;
    }

    function notes_globales_contains_an_sem_sig($notes_globales, $row, $debut)
    {
        for ($i = $debut; $i < count($notes_globales); $i ++) {
            if ($row['matriculeEtudiant'] == $notes_globales[$i]['matriculeEtudiant'])
                if ($row['year'] == $notes_globales[$i]['year'])
                    if (strcasecmp($row['sigle'], $notes_globales[$i]['sigle']) == 0)
                        if ($row['semestreB'] == $notes_globales[$i]['semestreB'])
                            return true;
        }
        return false;
    }

    /* fin code view [notes_globales] */
    
    /* Debut code view [planetudesmoduleelem] */
    /*
     * fonction pour generer une table(array) qui simule la vue [planetudesmoduleelem] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     * [/\] utilise les coeficients de cc et examen ->$coef_cc,$coef_exam
     * !!! cette fonction ne traite pas les element cahes pour le moment 08-04-2019 1:33
     */
    function planetudesmoduleelem($matriculeEtudiant, $notes_globales, $module, $unite, $coef_cc = 0.4, $coef_exam = 0.6)
    {
        $planetudesmoduleelem = array();
        $cpt = 0;
        for ($i = 0; $i < count($notes_globales); $i ++) {
            $j = $this->trouver_index_sigle($notes_globales[$i]['sigle'], $module);
            if ($j == - 1) {
                continue;
            }
            
            $k = $this->trouver_index_sigle($module[$j]['sigleunite'], $unite);
            if ($k == - 1) {
                continue;
            }
            // if($notes_globales[$i]['sigle']==$module[$j]['sigle']){
            // if($module[$j]['sigleunite']==$unite[$k]['sigle']){
            $planetudesmoduleelem[$cpt]['matriculeEtudiant'] = $notes_globales[$i]['matriculeEtudiant'];
            
            $planetudesmoduleelem[$cpt]['sigle'] = $notes_globales[$i]['sigle'];
            $planetudesmoduleelem[$cpt]['annee'] = $notes_globales[$i]['year'];
            $planetudesmoduleelem[$cpt]['semestre'] = $unite[$k]['semestre'];
            $planetudesmoduleelem[$cpt]['module'] = $unite[$k]['sigle'];
            // calcul de note
            
            $note = ($coef_cc * ($notes_globales[$i]['noteCC'])) + ($coef_exam * (max($notes_globales[$i]['noteExam'], $notes_globales[$i]['noteRT'])));
            
            //plafonner la noteFinal d'une matiere apres Ratrappage par 10  --add by MedBakar 11-03-2020
            if($note>10 && $notes_globales[$i]['noteRT']!=0)
                $note=10;
            
            $planetudesmoduleelem[$cpt]['note'] = number_format($note, 2);
            $planetudesmoduleelem[$cpt]['nbCredits'] = $module[$j]['nbCredits'];
            $planetudesmoduleelem[$cpt]['coefficient'] = $module[$j]['coefficient'];// add By MedBakar 25-02-2020
            
            $planetudesmoduleelem[$cpt]['semestreB'] = $notes_globales[$i]['semestre'];
            
            $cpt ++;
            // }
            // }
        }
        
        // les element caches !
        return $planetudesmoduleelem;
    }

    // pour plusieurs etudiants
    function planetudesmoduleelem_tous($notes_globales, $module, $coef_cc = 0.4, $coef_exam = 0.6)
    {
        $planetudesmoduleelem = array();
        $cpt = 0;
        for ($i = 0; $i < count($notes_globales); $i ++) {
            $j = $this->trouver_index_sigle($notes_globales[$i]['sigle'], $module);
            if ($j == - 1) {
                continue;
            }
            /*
             * $k = $this->trouver_index_sigle($module[$j]['sigleunite'], $unite);
             * if ($k == - 1){
             * continue;
             * }
             */
            // if($notes_globales[$i]['sigle']==$module[$j]['sigle']){
            // if($module[$j]['sigleunite']==$unite[$k]['sigle']){
            $planetudesmoduleelem[$cpt]['matriculeEtudiant'] = $notes_globales[$i]['matriculeEtudiant'];
            
            $planetudesmoduleelem[$cpt]['sigle'] = $notes_globales[$i]['sigle'];
            
            $planetudesmoduleelem[$cpt]['annee'] = $notes_globales[$i]['year'];
            $planetudesmoduleelem[$cpt]['semestre'] = $module[$j]['semestre'];
            $planetudesmoduleelem[$cpt]['module'] = $module[$j]['sigleunite'];
            // calcul de note
            
            
                         
            $note = ($coef_cc * ($notes_globales[$i]['noteCC'])) + ($coef_exam * (max($notes_globales[$i]['noteExam'], $notes_globales[$i]['noteRT'])));
          
            //plafonner la noteFinal d'une matiere apres Ratrappage par 10 --add by MedBakar 11-03-2020
            if($note>10 && $notes_globales[$i]['noteRT']!=0)
                $note=10;
            
            $planetudesmoduleelem[$cpt]['note'] = number_format($note, 2);
            $planetudesmoduleelem[$cpt]['nbCredits'] = $module[$j]['nbCredits'];
             $planetudesmoduleelem[$cpt]['coefficient'] = $module[$j]['coefficient'];//add by MedBakar 08-03-2020
                        
            $planetudesmoduleelem[$cpt]['semestreB'] = $notes_globales[$i]['semestre'];
            
            $cpt ++;
            // }
            // }
        }
        
        // les element caches !
        return  $planetudesmoduleelem;
    }

    /* fin code view [planetudesmoduleelem] */
    
    /* Debut code view [etudiant_sem_note_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [etudiant_sem_note_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */

    function etudiant_sem_note_bis($matriculeEtudiant, $planetudesmoduleelem)
    {
        $etudiant_sem_note_bis = array();
        $cpt = 0;
        for ($i = 0, $max_i = count($planetudesmoduleelem); $i < $max_i; $i ++) {
            $sommeNotes = 0;
            $sommeCredits = 0;
            $sommeCoefficients = 0;
            for ($j = 0; $j < count($planetudesmoduleelem); $j ++) {
                if ($planetudesmoduleelem[$i]['matriculeEtudiant'] == $planetudesmoduleelem[$j]['matriculeEtudiant']) {
                    if ($planetudesmoduleelem[$i]['semestre'] == $planetudesmoduleelem[$j]['semestre']) {
                        if ($planetudesmoduleelem[$i]['annee'] == $planetudesmoduleelem[$j]['annee']) {
//                            $sommeNotes += ($planetudesmoduleelem[$j]['note']) * (int) ($planetudesmoduleelem[$j]['nbCredits']);//modified by MedBakar 25-02-2020
//                            $sommeCredits += (int) ($planetudesmoduleelem[$j]['nbCredits']);
                            $sommeNotes += ($planetudesmoduleelem[$j]['note']) * (int) ($planetudesmoduleelem[$j]['coefficient']);//modified by MedBakar 25-02-2020
                            $sommeCoefficients += (int) ($planetudesmoduleelem[$j]['coefficient']);
                            $sommeCredits += (int) ($planetudesmoduleelem[$j]['nbCredits']);
                        }
                    }
                }
            }
            $row['matriculeEtudiant'] = $planetudesmoduleelem[$i]['matriculeEtudiant'];
            $row['semestre'] = $planetudesmoduleelem[$i]['semestre'];
            $row['annee'] = $planetudesmoduleelem[$i]['annee'];
            if ($this->etudiant_sem_note_bis_contains($etudiant_sem_note_bis, $row)) {
                continue;
            }
            $etudiant_sem_note_bis[$cpt] = $row;
//            if ($sommeCredits != 0) {
//                $my = ($sommeNotes / $sommeCredits);
//                $etudiant_sem_note_bis[$cpt]['note'] = number_format($my, 2); // number_format(($sommeNotes*($planetudesmoduleelem[$i]['nbCredits']))/$sommeCredits, 2);
//            } 
            if ($sommeCoefficients != 0) {
                $my = ($sommeNotes / $sommeCoefficients);
                $etudiant_sem_note_bis[$cpt]['note'] = number_format($my, 2); // number_format(($sommeNotes*($planetudesmoduleelem[$i]['nbCredits']))/$sommeCredits, 2);
            }else
                $etudiant_sem_note_bis[$cpt]['note'] = '0.00';
            $cpt ++;
        }
        return $etudiant_sem_note_bis;
    }

    // version plusieurs etudiant
    function etudiant_sem_note_bis_tous($planetudesmoduleelem, $indexation_pe)
    {
        $etudiant_sem_note_bis = array();
        $cpt = 0;
        $debut = 0;
        for ($i = 0, $max_i = count($planetudesmoduleelem); $i < $max_i; $i ++) {
            $sommeNotes = 0;
            $sommeCredits = 0;
            $sommeCoefficients=0;
            for ($j = $indexation_pe[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut']; $j < $indexation_pe[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($planetudesmoduleelem[$i]['matriculeEtudiant'] == $planetudesmoduleelem[$j]['matriculeEtudiant']) {
                    if ($planetudesmoduleelem[$i]['semestre'] == $planetudesmoduleelem[$j]['semestre']) {
                        if ($planetudesmoduleelem[$i]['annee'] == $planetudesmoduleelem[$j]['annee']) {
//                            $sommeNotes += ($planetudesmoduleelem[$j]['note']) * (int) ($planetudesmoduleelem[$j]['nbCredits']);
//                            $sommeCredits += (int) ($planetudesmoduleelem[$j]['nbCredits']);
                             $sommeNotes += ($planetudesmoduleelem[$j]['note']) * (int) ($planetudesmoduleelem[$j]['coefficient']); //modified  by MedBakar 08-03-2020
                            $sommeCredits += (int) ($planetudesmoduleelem[$j]['nbCredits']);
                            $sommeCoefficients += (int) ($planetudesmoduleelem[$j]['coefficient']);  //add by MedBakar 08-03-2020
                            
                        }
                        
                    }
                }
            }
            $row['matriculeEtudiant'] = $planetudesmoduleelem[$i]['matriculeEtudiant'];
            $row['semestre'] = $planetudesmoduleelem[$i]['semestre'];
            $row['annee'] = $planetudesmoduleelem[$i]['annee'];
            
            if (count($etudiant_sem_note_bis) > 0 and $cpt > 0) {
                if ($etudiant_sem_note_bis[$cpt - 1]['matriculeEtudiant'] != $planetudesmoduleelem[$i]['matriculeEtudiant']) {
                    $debut = $cpt;
                }
            }
            if ($this->etudiant_sem_note_bis_contains($etudiant_sem_note_bis, $row, $debut)) {
                continue;
            }
            $etudiant_sem_note_bis[$cpt] = $row;
//            if ($sommeCredits != 0) {
//                $my = ($sommeNotes / $sommeCredits);
//                $etudiant_sem_note_bis[$cpt]['note'] = number_format($my, 2); // number_format(($sommeNotes*($planetudesmoduleelem[$i]['nbCredits']))/$sommeCredits, 2);
//            } 
             if ($sommeCoefficients != 0) {
                $my = ($sommeNotes / $sommeCoefficients);
                $etudiant_sem_note_bis[$cpt]['note'] = number_format($my, 2); // number_format(($sommeNotes*($planetudesmoduleelem[$i]['nbCredits']))/$sommeCredits, 2);
            } else
                $etudiant_sem_note_bis[$cpt]['note'] = '0.00';
            $cpt ++;
        }
        return $etudiant_sem_note_bis;
    }

    // verifier l'existence de [row] pour eliminer les doublons
    function etudiant_sem_note_bis_contains($etudiant_sem_note_bis, $row, $debut = 0)
    {
        for ($i = $debut; $i < count($etudiant_sem_note_bis); $i ++) {
            if ($row['matriculeEtudiant'] == $etudiant_sem_note_bis[$i]['matriculeEtudiant']) {
                if ($row['annee'] == $etudiant_sem_note_bis[$i]['annee']) {
                    if ($row['semestre'] == $etudiant_sem_note_bis[$i]['semestre']) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /* fin code view [etudiant_sem_note_bis] */
    
    /* Debut code view [capseul_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [capseul_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function capseul_bis($matriculeEtudiant, $planetudesmoduleelem, $noteValidationMatiere = 10)
    {
        $capseul_bis = array();
        $cpt = 0;
        for ($i = 0; $i < count($planetudesmoduleelem); $i ++) {
            if ($planetudesmoduleelem[$i]['note'] >= $noteValidationMatiere) {
                $capseul_bis[$cpt]['idModule'] = $planetudesmoduleelem[$i]['module'];
                $capseul_bis[$cpt]['matriculeEtudiant'] = $planetudesmoduleelem[$i]['matriculeEtudiant'];
                $capseul_bis[$cpt]['sigle'] = $planetudesmoduleelem[$i]['sigle'];
                $capseul_bis[$cpt]['semestre'] = $planetudesmoduleelem[$i]['semestre'];
                $capseul_bis[$cpt]['annee'] = $planetudesmoduleelem[$i]['annee'];
                $capseul_bis[$cpt]['note'] = $planetudesmoduleelem[$i]['note'];
                $capseul_bis[$cpt]['capit'] = 'C';
                $capseul_bis[$cpt]['ects'] = $planetudesmoduleelem[$i]['nbCredits'];
                $capseul_bis[$cpt]['coefficient'] = $planetudesmoduleelem[$i]['coefficient'];//add by MedBakar 25-02-2020 
                $cpt ++;
            }
        }
        return $capseul_bis;
    }

    /* fin code view [capseul_bis] */
    
    /* Debut code view [etudiant_mod_note_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [etudiant_mod_note_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     * (mat: 16165, mod: MEF510, note: 4.48)
     */
    function etudiant_mod_note_bis($matriculeEtudiant, $planetudesmoduleelem)
    {
        $etudiant_mod_note_bis = array();
        $cpt = 0;
        for ($i = 0; $i < count($planetudesmoduleelem); $i ++) {
            $sommeNotes = 0;
            $sommeCredits = 0;
            $sommeCoefficient=0;
            for ($j = 0; $j < count($planetudesmoduleelem); $j ++) {
                if ($planetudesmoduleelem[$i]['matriculeEtudiant'] == $planetudesmoduleelem[$j]['matriculeEtudiant']) {
                    if ($planetudesmoduleelem[$i]['module'] == $planetudesmoduleelem[$j]['module']) {
                        if ($planetudesmoduleelem[$i]['semestre'] == $planetudesmoduleelem[$j]['semestre']) {
                            if ($planetudesmoduleelem[$i]['annee'] == $planetudesmoduleelem[$j]['annee']) {
//                                $sommeNotes += ($planetudesmoduleelem[$j]['note']) * (int) ($planetudesmoduleelem[$j]['nbCredits']);
//                                $sommeCredits += (int) ($planetudesmoduleelem[$j]['nbCredits']);
                                $sommeNotes += ($planetudesmoduleelem[$j]['note']) * (int) ($planetudesmoduleelem[$j]['coefficient']);
                                $sommeCredits += (int) ($planetudesmoduleelem[$j]['nbCredits']);
                                $sommeCoefficient += (int) ($planetudesmoduleelem[$j]['coefficient']);
                            }
                        }
                    }
                }
            }
            $row['matriculeEtudiant'] = $planetudesmoduleelem[$i]['matriculeEtudiant'];
            $row['idModule'] = $planetudesmoduleelem[$i]['module'];
            $row['semestre'] = $planetudesmoduleelem[$i]['semestre'];
            $row['annee'] = $planetudesmoduleelem[$i]['annee'];
            if ($this->etudiant_mod_note_bis_contains($etudiant_mod_note_bis, $row)) {
                continue;
            }
            $etudiant_mod_note_bis[$cpt] = $row;
//            if ($sommeCredits != 0) {
//                $my = ($sommeNotes / $sommeCredits);
//                $etudiant_mod_note_bis[$cpt]['note'] = number_format($my, 2); // number_format(($sommeNotes*($planetudesmoduleelem[$i]['nbCredits']))/$sommeCredits, 2);
//            }
              if ($sommeCoefficient != 0) {
                $my = ($sommeNotes / $sommeCoefficient);
                $etudiant_mod_note_bis[$cpt]['note'] = number_format($my, 2); // number_format(($sommeNotes*($planetudesmoduleelem[$i]['nbCredits']))/$sommeCredits, 2);
            }else
                $etudiant_mod_note_bis[$cpt]['note'] = '0.00';
            $cpt ++;
        }
        return $etudiant_mod_note_bis;
    }

    function etudiant_mod_note_bis_list($planetudesmoduleelem, $indexation_planetudes)
    {
        // cette fois-ci on utilise l'indexation pour planetudesmoduleelem
        // $indexation_planetudes=$this->indexation_matricules_notespartielles($planetudesmoduleelem);
        $etudiant_mod_note_bis = array();
        $cpt = 0;
        $debut = 0;
        if(!empty($indexation_planetudes))
        foreach ($indexation_planetudes as $matricule => $ligne) {
            for ($i = $ligne['debut']; $i < $ligne['fin'] + 1; $i ++) {
                $sommeNotes = 0;
                $sommeCredits = 0;
                $sommeCoefficients=0;
                for ($j = $ligne['debut']; $j < $ligne['fin'] + 1; $j ++) {
                    if ($planetudesmoduleelem[$i]['matriculeEtudiant'] == $planetudesmoduleelem[$j]['matriculeEtudiant']) {
                        if ($planetudesmoduleelem[$i]['module'] == $planetudesmoduleelem[$j]['module']) {
                            if ($planetudesmoduleelem[$i]['semestre'] == $planetudesmoduleelem[$j]['semestre']) {
                                if ($planetudesmoduleelem[$i]['annee'] == $planetudesmoduleelem[$j]['annee']) {
//                                    $sommeNotes += ($planetudesmoduleelem[$j]['note']) * (int) ($planetudesmoduleelem[$j]['nbCredits']);
//                                    $sommeCredits += (int) ($planetudesmoduleelem[$j]['nbCredits']);
                                    $sommeNotes += ($planetudesmoduleelem[$j]['note']) * (int) ($planetudesmoduleelem[$j]['coefficient']);//modified by MedBakar 08-03-2020
                                    $sommeCredits += (int) ($planetudesmoduleelem[$j]['nbCredits']);
                                    $sommeCoefficients += (int) ($planetudesmoduleelem[$j]['coefficient']);//add by MedBakar 08-03-2020
                                }
                            }
                        }
                    }
                }
                $row['matriculeEtudiant'] = $planetudesmoduleelem[$i]['matriculeEtudiant'];
                $row['idModule'] = $planetudesmoduleelem[$i]['module'];
                $row['semestre'] = $planetudesmoduleelem[$i]['semestre'];
                $row['annee'] = $planetudesmoduleelem[$i]['annee'];
                
                if (count($etudiant_mod_note_bis) > 0 and $cpt > 0) {
                    if ($etudiant_mod_note_bis[$cpt - 1]['matriculeEtudiant'] != $planetudesmoduleelem[$i]['matriculeEtudiant']) {
                        $debut = $cpt;
                    }
                }
                if ($this->etudiant_mod_note_bis_contains($etudiant_mod_note_bis, $row, $debut)) {
                    continue;
                }
                $etudiant_mod_note_bis[$cpt] = $row;
//                if ($sommeCredits != 0) {
//                    $my = ($sommeNotes / $sommeCredits);
//                    $etudiant_mod_note_bis[$cpt]['note'] = number_format($my, 2); // number_format(($sommeNotes*($planetudesmoduleelem[$i]['nbCredits']))/$sommeCredits, 2);
//                } 
                  if ($sommeCoefficients != 0) {
                    $my = ($sommeNotes / $sommeCoefficients);
                    $etudiant_mod_note_bis[$cpt]['note'] = number_format($my, 2); // number_format(($sommeNotes*($planetudesmoduleelem[$i]['nbCredits']))/$sommeCredits, 2);
                } else
                    $etudiant_mod_note_bis[$cpt]['note'] = '0.00';
                $cpt ++;
            }
        }
        return $etudiant_mod_note_bis;
    }

    // verifier l'existence de [row] pour eliminer les doublonns
    function etudiant_mod_note_bis_contains($etudiant_mod_note_bis, $row, $debut = 0)
    {
        for ($i = $debut; $i < count($etudiant_mod_note_bis); $i ++) {
            if ($row['matriculeEtudiant'] == $etudiant_mod_note_bis[$i]['matriculeEtudiant']) {
                if ($row['idModule'] == $etudiant_mod_note_bis[$i]['idModule']) {
                    if ($row['annee'] == $etudiant_mod_note_bis[$i]['annee']) {
                        if ($row['semestre'] == $etudiant_mod_note_bis[$i]['semestre']) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /* fin code view [etudiant_mod_note_bis] */
    
    /* Debut code view [nombreelementselimines_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [nombreelementselimines_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     * !!! ici parametrage de la note d'elimination 'matiere-element' pour la licence
     * pour le moment [09/04/2019] - $noteEliminationMatiere=7
     */
    function nombreelementselimines_bis($matriculeEtudiant, $planetudesmoduleelem, $noteEliminationMatiere)
    {
        $indexation = $this->indexation_matricules_notespartielles($planetudesmoduleelem);
        $nombreelementselimines_bis = array();
        $cpt = 0;
        for ($i = 0; $i < count($planetudesmoduleelem); $i ++) {
            // counter correspond au champs nombre de cette view[$nombreelementselimines_bis]
            $counter = 0;
            for ($j = $indexation[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut']; $j < $indexation[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                // if ($planetudesmoduleelem[$i]['matriculeEtudiant'] == $planetudesmoduleelem[$j]['matriculeEtudiant']) {
                if ($planetudesmoduleelem[$i]['module'] == $planetudesmoduleelem[$j]['module']) {
                    if ($planetudesmoduleelem[$i]['semestre'] == $planetudesmoduleelem[$j]['semestre']) {
                        if ($planetudesmoduleelem[$i]['annee'] == $planetudesmoduleelem[$j]['annee']) {
                            if ($planetudesmoduleelem[$j]['note'] < $noteEliminationMatiere) {
                                $counter ++;
                            }
                        }
                    }
                }
                // }
            }
            if ($counter > 0) {
                $row['matriculeEtudiant'] = $planetudesmoduleelem[$i]['matriculeEtudiant'];
                $row['idModule'] = $planetudesmoduleelem[$i]['module'];
                $row['semestre'] = $planetudesmoduleelem[$i]['semestre'];
                $row['annee'] = $planetudesmoduleelem[$i]['annee'];
                if ($this->nombreelementselimines_bis_contains($nombreelementselimines_bis, $row)) {
                    continue;
                }
                $nombreelementselimines_bis[$cpt] = $row;
                $nombreelementselimines_bis[$cpt]['nombre'] = $counter;
                $cpt ++;
            }
        }
        return $nombreelementselimines_bis;
    }

    // pour plusieurs etudiant
    function nombreelementselimines_bis_tous($matriculeEtudiant, $planetudesmoduleelem, $noteEliminationMatiere, $indexation)
    {
        // $indexation=$this->indexation_matricules_notespartielles($planetudesmoduleelem);
        $nombreelementselimines_bis = array();
        $cpt = 0;
        $debut = 0;
        for ($i = 0; $i < count($planetudesmoduleelem); $i ++) {
            // counter correspond au champs nombre de cette view[$nombreelementselimines_bis]
            $counter = 0;
            for ($j = $indexation[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut']; $j < $indexation[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                // if ($planetudesmoduleelem[$i]['matriculeEtudiant'] == $planetudesmoduleelem[$j]['matriculeEtudiant']) {
                if ($planetudesmoduleelem[$i]['module'] == $planetudesmoduleelem[$j]['module']) {
                    if ($planetudesmoduleelem[$i]['semestre'] == $planetudesmoduleelem[$j]['semestre']) {
                        if ($planetudesmoduleelem[$i]['annee'] == $planetudesmoduleelem[$j]['annee']) {
                            if ($planetudesmoduleelem[$j]['note'] < $noteEliminationMatiere) {
                                $counter ++;
                            }
                        }
                    }
                }
                // }
            }
            if ($counter > 0) {
                $row['matriculeEtudiant'] = $planetudesmoduleelem[$i]['matriculeEtudiant'];
                $row['idModule'] = $planetudesmoduleelem[$i]['module'];
                $row['semestre'] = $planetudesmoduleelem[$i]['semestre'];
                $row['annee'] = $planetudesmoduleelem[$i]['annee'];
                if (count($nombreelementselimines_bis) > 0 and $cpt > 0) {
                    if ($nombreelementselimines_bis[$cpt - 1]['matriculeEtudiant'] != $planetudesmoduleelem[$i]['matriculeEtudiant']) {
                        $debut = $cpt;
                    }
                }
                if ($this->nombreelementselimines_bis_contains($nombreelementselimines_bis, $row, $debut)) {
                    continue;
                }
                $nombreelementselimines_bis[$cpt] = $row;
                $nombreelementselimines_bis[$cpt]['nombre'] = $counter;
                $cpt ++;
            }
        }
        return $nombreelementselimines_bis;
    }

    // verifier l'existence de [row] pour eliminer les doublons
    function nombreelementselimines_bis_contains($nombreelementselimines_bis, $row, $debut = 0)
    {
        for ($i = $debut; $i < count($nombreelementselimines_bis); $i ++) {
            if ($row['matriculeEtudiant'] == $nombreelementselimines_bis[$i]['matriculeEtudiant']) {
                if ($row['idModule'] == $nombreelementselimines_bis[$i]['idModule']) {
                    if ($row['annee'] == $nombreelementselimines_bis[$i]['annee']) {
                        if ($row['semestre'] == $nombreelementselimines_bis[$i]['semestre']) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /* fin code view [nombreelementselimines_bis] */
    
    /* Debut code view [compense_interne_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [compense_interne_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     * Retourne la liste des modules avec une reponse (0 ou 1) signifiant que le module accepte la compensation ou non
     * !!! ici parametrage de la note de validation 'module-unite' pour la licence
     * pour le moment [09/04/2019] -> $noteValidationMatiere=10;
     */
    function compense_interne_bis($matriculeEtudiant, $etudiant_mod_note_bis, $nombreelementselimines_bis, $noteValidation_module = 10)
    {
        $compense_interne_bis = array();
        $cpt = 0;
        // $debut=0;
        $indexation_nb_elem_elim = $this->indexation_matricules_notespartielles($nombreelementselimines_bis);
        for ($i = 0; $i < count($etudiant_mod_note_bis); $i ++) {
            $row['matriculeEtudiant'] = $etudiant_mod_note_bis[$i]['matriculeEtudiant'];
            $row['idModule'] = $etudiant_mod_note_bis[$i]['idModule'];
            $row['annee'] = $etudiant_mod_note_bis[$i]['annee'];
            $row['semestre'] = $etudiant_mod_note_bis[$i]['semestre'];
            /*
             * if(count($compense_interne_bis)>0 and $cpt>0){
             * if($compense_interne_bis[$cpt-1]['matriculeEtudiant']!=$etudiant_mod_note_bis[$i]['matriculeEtudiant']){
             * $debut=$cpt;
             * }
             * }
             */
            $reponse = '0';
            if ($etudiant_mod_note_bis[$i]['note'] >= $noteValidation_module) {
                // echo $row['idModule']."_n_".$etudiant_mod_note_bis[$i]['note']."_m_";
                if (! isset($indexation_nb_elem_elim[$etudiant_mod_note_bis[$i]['matriculeEtudiant']]))
                    $reponse = '1';
                else{
                    if(!isset($indexation_nb_elem_elim[$etudiant_mod_note_bis[$i]['matriculeEtudiant']]['fin'])){
                        $indexation_nb_elem_elim[$etudiant_mod_note_bis[$i]['matriculeEtudiant']]['fin']=count($nombreelementselimines_bis)-1;
                    }
                    if (! $this->idModule_exist_in_nombreelementselimines_bis($nombreelementselimines_bis, $row, $indexation_nb_elem_elim[$etudiant_mod_note_bis[$i]['matriculeEtudiant']]['debut'], $indexation_nb_elem_elim[$etudiant_mod_note_bis[$i]['matriculeEtudiant']]['fin'] + 1)) {
                        $reponse = '1';
                    }
                }
            }
            $compense_interne_bis[$cpt] = $row;
            $compense_interne_bis[$cpt]['reponse'] = $reponse;
            $cpt ++;
        }
        return $compense_interne_bis;
    }

    // verifier l'existence de [row] pour eliminer les doublonns
    function idModule_exist_in_nombreelementselimines_bis($nombreelementselimines_bis, $row, $debut = 0, $fin = -1)
    {
        if ($fin == - 1)
            $fin = count($nombreelementselimines_bis);
        $reponse = false;
        for ($i = $debut; $i < $fin; $i ++) {
            if ($nombreelementselimines_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                if ($nombreelementselimines_bis[$i]['idModule'] == $row['idModule'])
                    if ($nombreelementselimines_bis[$i]['annee'] == $row['annee'])
                        if ($nombreelementselimines_bis[$i]['semestre'] == $row['semestre']) {
                            $reponse = true;
                            break;
                        }
        }
        return $reponse;
    }

    /* fin code view [compense_interne_bis] */
    
    /* Debut code view [capinterne_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue (sql) [capinterne_bis]
     * Pour un seul etudiant $matriculeEtudiant
     * !!! ici on utilise la note de validation 'matiere-element' pour la licence
     */
    function capinterne_bis($matriculeEtudiant, $planetudesmoduleelem, $compense_interne_bis, $noteValidation)
    {
        $capinterne_bis = array();
        $cpt = 0;
        $indexation = $this->indexation_matricules_notespartielles($compense_interne_bis);
        for ($i = 0; $i < count($planetudesmoduleelem); $i ++) {
            $row['idModule'] = $planetudesmoduleelem[$i]['module'];
            $row['matriculeEtudiant'] = $planetudesmoduleelem[$i]['matriculeEtudiant'];
            $row['sigle'] = $planetudesmoduleelem[$i]['sigle'];
            $row['semestre'] = $planetudesmoduleelem[$i]['semestre'];
            $row['annee'] = $planetudesmoduleelem[$i]['annee'];
            $row['note'] = $planetudesmoduleelem[$i]['note'];
            $row['capit'] = 'CI';
            $row['ects'] = $planetudesmoduleelem[$i]['nbCredits'];
             $row['coefficient'] = $planetudesmoduleelem[$i]['coefficient'];//add by MedBakar 25-02-2020
            
            if ($planetudesmoduleelem[$i]['note'] < $noteValidation) {
                
                if ($this->idModule_exist_in_compense_interne_bis_reponse_1($compense_interne_bis, $row, $indexation[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut'], $indexation[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] + 1)) {
                    $capinterne_bis[$cpt] = $row;
                    $cpt ++;
                }
            }
        }
        return $capinterne_bis;
    }

    // verifi si le module $row['idModule'] exist dans la table compense_interne_bis avec reponse=1
    // verifi si le module est compense
    function idModule_exist_in_compense_interne_bis_reponse_1($compense_interne_bis, $row, $debut = 0, $fin = -1)
    {
        if ($fin == - 1)
            $fin = count($compense_interne_bis);
        for ($i = $debut; $i < $fin; $i ++) {
            if ($compense_interne_bis[$i]['reponse'] == '1')
                if ($compense_interne_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                    // if ($compense_interne_bis[$i]['idModule'] == $row['idModule'])
                    if (strcasecmp($compense_interne_bis[$i]['idModule'], $row['idModule']) == 0)
                        if ($compense_interne_bis[$i]['annee'] == $row['annee'])
                            if ($compense_interne_bis[$i]['semestre'] == $row['semestre'])
                                return true;
        }
        return false;
    }

    /* fin code view [capinterne_bis] */
    
    /* Debut code view [moduleselimines_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [moduleselimines_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     * !!! ici on utilise la note d'elimination 'module-element' pour la licence
     * !!! et la note d'elimination matiere [licence]
     */
    function moduleselimines_bis($matriculeEtudiant, $etudiant_mod_note_bis, $planetudesmoduleelem, $noteEliminationModule, $noteEliminationMatiere)
    {
        $moduleselimines_bis = array();
        $cpt = 0;
        for ($i = 0; $i < count($etudiant_mod_note_bis); $i ++) {
            // compteur
            $counter = 0;
            for ($j = 0; $j < count($etudiant_mod_note_bis); $j ++) {
                if ($etudiant_mod_note_bis[$i]['matriculeEtudiant'] == $etudiant_mod_note_bis[$j]['matriculeEtudiant']) {
                    if ($etudiant_mod_note_bis[$i]['annee'] == $etudiant_mod_note_bis[$j]['annee']) {
                        if ($etudiant_mod_note_bis[$i]['semestre'] == $etudiant_mod_note_bis[$j]['semestre']) {
                            if ($etudiant_mod_note_bis[$j]['note'] < $noteEliminationModule) {
                                $counter ++;
                                continue;
                            }
                            $row1['matriculeEtudiant'] = $etudiant_mod_note_bis[$i]['matriculeEtudiant'];
                            $row1['annee'] = $etudiant_mod_note_bis[$i]['annee'];
                            $row1['semestre'] = $etudiant_mod_note_bis[$i]['semestre'];
                            $row1['idModule'] = $etudiant_mod_note_bis[$i]['idModule'];
                            if ($this->exist_element_elimine_dans_ce_module($planetudesmoduleelem, $row1, $noteEliminationMatiere)) {
                                $counter ++;
                            }
                        }
                    }
                }
            }
            if ($counter > 0) {
                $row['matriculeEtudiant'] = $etudiant_mod_note_bis[$i]['matriculeEtudiant'];
                $row['annee'] = $etudiant_mod_note_bis[$i]['annee'];
                $row['semestre'] = $etudiant_mod_note_bis[$i]['semestre'];
                if (! $this->moduleselimines_bis_contains($moduleselimines_bis, $row)) {
                    $moduleselimines_bis[$cpt] = $row;
                    $moduleselimines_bis[$cpt]['nombre'] = $counter;
                    $cpt ++;
                }
            }
        }
        
        return $moduleselimines_bis;
    }

    // verification s'il y a une $noteEliminationMatiere dans une matiere de cet $module
    function exist_element_elimine_dans_ce_module($planetudesmoduleelem, $row, $noteEliminationMatiere, $debut = 0, $fin = -1)
    {
        if ($fin == - 1)
            $fin = count($planetudesmoduleelem);
        for ($i = $debut; $i < $fin; $i ++) {
            if ($planetudesmoduleelem[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                if ($planetudesmoduleelem[$i]['module'] == $row['idModule'])
                    if ($planetudesmoduleelem[$i]['annee'] == $row['annee'])
                        if ($planetudesmoduleelem[$i]['semestre'] == $row['semestre'])
                            if ($planetudesmoduleelem[$i]['note'] < $noteEliminationMatiere)
                                return true;
        }
        return false;
    }

    // verifier l'existence de [row] pour eliminer les doublonns
    function moduleselimines_bis_contains($moduleselimines_bis, $row, $debut = 0)
    {
        for ($i = $debut; $i < count($moduleselimines_bis); $i ++) {
            if ($moduleselimines_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                if ($moduleselimines_bis[$i]['semestre'] == $row['semestre'])
                    if ($moduleselimines_bis[$i]['annee'] == $row['annee'])
                        return true;
        }
        return false;
    }

    // pour plusieur etudiants
    function moduleselimines_bis_tous($etudiant_mod_note_bis, $planetudesmoduleelem, $indexation_planetudes, $noteEliminationModule, $noteEliminationMatiere)
    {
        $moduleselimines_bis = array();
        $cpt = 0;
        $indexation_e_m_n_b = $this->indexation_matricules_notespartielles($etudiant_mod_note_bis);
        $debut = 0;
        for ($i = 0; $i < count($etudiant_mod_note_bis); $i ++) {
            // compteur
            $counter = 0;
            for ($j = $indexation_e_m_n_b[$etudiant_mod_note_bis[$i]['matriculeEtudiant']]['debut']; $j < $indexation_e_m_n_b[$etudiant_mod_note_bis[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($etudiant_mod_note_bis[$i]['matriculeEtudiant'] == $etudiant_mod_note_bis[$j]['matriculeEtudiant']) {
                    if ($etudiant_mod_note_bis[$i]['annee'] == $etudiant_mod_note_bis[$j]['annee']) {
                        if ($etudiant_mod_note_bis[$i]['semestre'] == $etudiant_mod_note_bis[$j]['semestre']) {
                            if ($etudiant_mod_note_bis[$j]['note'] < $noteEliminationModule) {
                                $counter ++;
                                continue;
                            }
                            $row1['matriculeEtudiant'] = $etudiant_mod_note_bis[$i]['matriculeEtudiant'];
                            $row1['annee'] = $etudiant_mod_note_bis[$i]['annee'];
                            $row1['semestre'] = $etudiant_mod_note_bis[$i]['semestre'];
                            $row1['idModule'] = $etudiant_mod_note_bis[$i]['idModule'];
                            if ($this->exist_element_elimine_dans_ce_module($planetudesmoduleelem, $row1, $noteEliminationMatiere, $indexation_planetudes[$etudiant_mod_note_bis[$i]['matriculeEtudiant']]['debut'], $indexation_planetudes[$etudiant_mod_note_bis[$i]['matriculeEtudiant']]['fin'] + 1)) {
                                $counter ++;
                            }
                        }
                    }
                }
            }
            
            if ($counter > 0) {
                if (count($moduleselimines_bis) > 0 and $cpt > 0) {
                    if ($moduleselimines_bis[$cpt - 1]['matriculeEtudiant'] != $etudiant_mod_note_bis[$i]['matriculeEtudiant']) {
                        $debut = $cpt;
                    }
                }
                $row['matriculeEtudiant'] = $etudiant_mod_note_bis[$i]['matriculeEtudiant'];
                $row['annee'] = $etudiant_mod_note_bis[$i]['annee'];
                $row['semestre'] = $etudiant_mod_note_bis[$i]['semestre'];
                if (! $this->moduleselimines_bis_contains($moduleselimines_bis, $row, $debut)) {
                    $moduleselimines_bis[$cpt] = $row;
                    $moduleselimines_bis[$cpt]['nombre'] = $counter;
                    $cpt ++;
                }
            }
        }
        
        return $moduleselimines_bis;
    }

    /* fin code view [moduleselimines_bis] */
    
    /* Debut code view [compense_externe_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [compense_externe_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     * !!! ici on utilise la note de validation 'module-unite' pour la licence
     */
    function compense_externe_bis($matriculeEtudiant, $etudiant_sem_note_bis, $moduleselimines_bis, $noteValidation_module)
    {
        $compense_externe_bis = array();
        $cpt = 0;
        $indexation = $this->indexation_matricules_notespartielles($moduleselimines_bis);
        //echo count($moduleselimines_bis);
        /* echo "<pre>";
        echo "</pre>"; */
        for ($i = 0; $i < count($etudiant_sem_note_bis); $i ++) {
            $row['matriculeEtudiant'] = $etudiant_sem_note_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $etudiant_sem_note_bis[$i]['semestre'];
            $row['annee'] = $etudiant_sem_note_bis[$i]['annee'];
            $reponse = '0';
            if ($etudiant_sem_note_bis[$i]['note'] >= $noteValidation_module) {
                
                /*if (!isset($indexation[$etudiant_sem_note_bis[$i]['matriculeEtudiant']]['fin'] ))
                    $indexation[$etudiant_sem_note_bis[$i]['matriculeEtudiant']]['fin'] =count($moduleselimines_bis)-1;
                */if (! isset($indexation[$etudiant_sem_note_bis[$i]['matriculeEtudiant']]))
                    $reponse = '1';
                elseif (! $this->idModule_exist_in_moduleselimines_bis($moduleselimines_bis, $row, $indexation[$etudiant_sem_note_bis[$i]['matriculeEtudiant']]['debut'], $indexation[$etudiant_sem_note_bis[$i]['matriculeEtudiant']]['fin'] + 1)) {
                    $reponse = '1';
                }
            }
            $compense_externe_bis[$cpt] = $row;
            $compense_externe_bis[$cpt]['reponse'] = $reponse;
            $cpt ++;
        }
        return $compense_externe_bis;
    }

    // verifier l'existence de [row] pour eliminer les doublonns
    // verifi si le module exist parmi les modules elimimes
    function idModule_exist_in_moduleselimines_bis($moduleselimines_bis, $row, $debut = 0, $fin = -1)
    {
        if ($fin == - 1)
            $fin = count($moduleselimines_bis);
        for ($i = $debut; $i < $fin; $i ++) {
            if ($moduleselimines_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                if ($moduleselimines_bis[$i]['semestre'] == $row['semestre'])
                    if ($moduleselimines_bis[$i]['annee'] == $row['annee'])
                        return true;
        }
        return false;
    }

    /* fin code view [compense_externe_bis] */
    
    /* Debut code view [capexterne_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [capexterne_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     * !!! ici on utilise la note de validation 'module:matiere' pour la licence
     * !!! ici on utilise la $noteEliminationMatiere 'matiere'
     */
    function capexterne_bis($matriculeEtudiant, $planetudesmoduleelem, $compense_interne_bis, $compense_externe_bis, $noteValidationMatiere, $noteEliminationMatiere)
    {
        $capexterne_bis = array();
        $cpt = 0;
        for ($i = 0; $i < count($planetudesmoduleelem); $i ++) {
            if ($planetudesmoduleelem[$i]['note'] < $noteValidationMatiere) {
                $row['idModule'] = $planetudesmoduleelem[$i]['module'];
                $row['matriculeEtudiant'] = $planetudesmoduleelem[$i]['matriculeEtudiant'];
                $row['sigle'] = $planetudesmoduleelem[$i]['sigle'];
                $row['semestre'] = $planetudesmoduleelem[$i]['semestre'];
                $row['annee'] = $planetudesmoduleelem[$i]['annee'];
                $row['note'] = $planetudesmoduleelem[$i]['note'];
                $row['capit'] = 'CE';
                $row['ects'] = $planetudesmoduleelem[$i]['nbCredits'];
                $row['coefficient'] = $planetudesmoduleelem[$i]['coefficient'];
                
                if (! $this->exist_element_elimine_dans_ce_module($planetudesmoduleelem, $row, $noteEliminationMatiere)) {
                    if (! $this->idModule_exist_in_compense_interne_bis_reponse_1($compense_interne_bis, $row)) {
                        if ($this->exist_in_compense_externe_bis($compense_externe_bis, $row)) {
                            $capexterne_bis[$cpt] = $row;
                            $cpt ++;
                        }
                    }
                }
            }
        }
        
        return $capexterne_bis;
    }

    // pour plusieur etudiants
    function capexterne_bis_tous($planetudesmoduleelem, $indexation_planetudes, $compense_interne_bis, $compense_externe_bis, $noteValidationMatiere, $noteEliminationMatiere)
    {
        $capexterne_bis = array();
        $cpt = 0;
        $indexation_comp_inter = $this->indexation_matricules_notespartielles($compense_interne_bis);
        $indexation_comp_exter = $this->indexation_matricules_notespartielles($compense_externe_bis);
        for ($i = 0; $i < count($planetudesmoduleelem); $i ++) {
            if ($planetudesmoduleelem[$i]['note'] < $noteValidationMatiere) {
                $row['idModule'] = $planetudesmoduleelem[$i]['module'];
                $row['matriculeEtudiant'] = $planetudesmoduleelem[$i]['matriculeEtudiant'];
                $row['sigle'] = $planetudesmoduleelem[$i]['sigle'];
                $row['semestre'] = $planetudesmoduleelem[$i]['semestre'];
                $row['annee'] = $planetudesmoduleelem[$i]['annee'];
                $row['note'] = $planetudesmoduleelem[$i]['note'];
                $row['capit'] = 'CE';
                $row['ects'] = $planetudesmoduleelem[$i]['nbCredits'];
                 $row['coefficient'] = $planetudesmoduleelem[$i]['coefficient']; //add by MedBakar 08-03-2020
                
                if (! $this->exist_element_elimine_dans_ce_module($planetudesmoduleelem, $row, $noteEliminationMatiere, $indexation_planetudes[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut'], $indexation_planetudes[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] + 1)) {
                    if (! isset($indexation_comp_inter[$planetudesmoduleelem[$i]['matriculeEtudiant']])) {
                        if (isset($indexation_comp_exter[$planetudesmoduleelem[$i]['matriculeEtudiant']])) {
                            if ($this->exist_in_compense_externe_bis($compense_externe_bis, $row, $indexation_comp_exter[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut'], $indexation_comp_exter[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] + 1)) {
                                $capexterne_bis[$cpt] = $row;
                                $cpt ++;
                            }
                        }
                    } elseif (! $this->idModule_exist_in_compense_interne_bis_reponse_1($compense_interne_bis, $row, $indexation_comp_inter[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut'], $indexation_comp_inter[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] + 1)) {
                        if (isset($indexation_comp_exter[$planetudesmoduleelem[$i]['matriculeEtudiant']])) {
                            if ($this->exist_in_compense_externe_bis($compense_externe_bis, $row, $indexation_comp_exter[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut'], $indexation_comp_exter[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] + 1)) {
                                $capexterne_bis[$cpt] = $row;
                                $cpt ++;
                            }
                        }
                    }
                }
            }
        }
        
        return $capexterne_bis;
    }

    // verifi si (matriculeEtudiant,semestre,annee) existe dans la table compenede_externe_bis WHERE reponse=1
    function exist_in_compense_externe_bis($compense_externe_bis, $row, $debut = 0, $fin = -1)
    {
        if ($fin == - 1)
            $fin = count($compense_externe_bis);
        for ($i = $debut; $i < $fin; $i ++) {
            if ($compense_externe_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                if ($compense_externe_bis[$i]['semestre'] == $row['semestre'])
                    if ($compense_externe_bis[$i]['annee'] == $row['annee'])
                        if ($compense_externe_bis[$i]['reponse'] == '1')
                            return true;
        }
        return false;
    }

    /* fin code view [capexterne_bis] */
    
    /* Debut code view [noncap_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [noncap_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function noncap_bis($matriculeEtudiant, $planetudesmoduleelem, $capseul_bis, $capinterne_bis, $capexterne_bis)
    {
        $noncap_bis = array();
        $cpt = 0;
        $indexation_capseul = $this->indexation_matricules_notespartielles($capseul_bis);
        $indexation_capin = $this->indexation_matricules_notespartielles($capinterne_bis);
        $indexation_capex = $this->indexation_matricules_notespartielles($capexterne_bis);
        for ($i = 0; $i < count($planetudesmoduleelem); $i ++) {
            
            $row['idModule'] = $planetudesmoduleelem[$i]['module'];
            $row['matriculeEtudiant'] = $planetudesmoduleelem[$i]['matriculeEtudiant'];
            $row['sigle'] = $planetudesmoduleelem[$i]['sigle'];
            $row['semestre'] = $planetudesmoduleelem[$i]['semestre'];
            $row['annee'] = $planetudesmoduleelem[$i]['annee'];
            
            if (! isset($indexation_capseul[$planetudesmoduleelem[$i]['matriculeEtudiant']])) {
                // debut>fin donc pas d'iteration
                $indexation_capseul[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut'] = 3;
                $indexation_capseul[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] = 0;
            }
            if (! isset($indexation_capex[$planetudesmoduleelem[$i]['matriculeEtudiant']])) {
                // debut>fin donc pas d'iteration
                $indexation_capex[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut'] = 3;
                $indexation_capex[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] = 0;
            }
            if (! isset($indexation_capin[$planetudesmoduleelem[$i]['matriculeEtudiant']])) {
                // debut>fin donc pas d'iteration
                $indexation_capin[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut'] = 3;
                $indexation_capin[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] = 0;
            }
            if (! $this->existe_capitalisation($capseul_bis, $row, $indexation_capseul[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut'], $indexation_capseul[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] + 1)) {
                if (! $this->existe_capitalisation($capinterne_bis, $row, $indexation_capin[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut'], $indexation_capin[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] + 1)) {
                    if (! $this->existe_capitalisation($capexterne_bis, $row, $indexation_capex[$planetudesmoduleelem[$i]['matriculeEtudiant']]['debut'], $indexation_capex[$planetudesmoduleelem[$i]['matriculeEtudiant']]['fin'] + 1)) {
                        $row['note'] = $planetudesmoduleelem[$i]['note'];
                        $row['capit'] = 'NC';
                        $row['ects'] = $planetudesmoduleelem[$i]['nbCredits'];
                        $row['coefficient'] = $planetudesmoduleelem[$i]['coefficient'];//add by MedBakar 25-02-2020 
                        
                        $noncap_bis[$cpt] = $row;
                        $cpt ++;
                    }
                }
            }
        }
        return $noncap_bis;
    }

    // verification de l'existence d'une capitalisation
    function existe_capitalisation($cap, $row, $debut = 0, $fin = -1)
    {
        if ($fin == - 1)
            $fin = count($cap);
        for ($i = $debut; $i < $fin; $i ++) {
            if ($cap[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                if (strcasecmp($row['sigle'], $cap[$i]['sigle']) == 0)
                    if ($cap[$i]['annee'] == $row['annee'])
                        // if($cap[$i]['semestre']==$row['semestre'])
                        return true;
        }
        return false;
    }

    /* fin code view [noncap_bis] */
    
    /* Debut code view [releve_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [releve_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function releve_bis($matriculeEtudiant, $capseul_bis, $capinterne_bis, $capexterne_bis, $noncap_t_bis)
    {
        $releve_bis = array();
        $cpt = 0;
        for ($i = 0; $i < count($capseul_bis); $i ++) {
            $releve_bis[$cpt] = $capseul_bis[$i];
            $cpt ++;
        }
        for ($i = 0; $i < count($capinterne_bis); $i ++) {
            $releve_bis[$cpt] = $capinterne_bis[$i];
            $cpt ++;
        }
        for ($i = 0; $i < count($capexterne_bis); $i ++) {
            $releve_bis[$cpt] = $capexterne_bis[$i];
            $cpt ++;
        }
        for ($i = 0; $i < count($noncap_t_bis); $i ++) {
            $releve_bis[$cpt] = $noncap_t_bis[$i];
            $cpt ++;
        }
        $colonne = array_column($releve_bis, 'matriculeEtudiant');
        array_multisort($colonne, SORT_ASC, $releve_bis);
        return $releve_bis;
    }

    // pour plusieur etudiants
    function releve_bis_tous($capseul_bis, $capinterne_bis, $capexterne_bis, $noncap_t_bis, $indexation)
    {
        $releve_bis = array();
        $cpt = 0;
        $seul = array();
        $ext = array();
        $int = array();
        $nonCap = array();
        foreach ($indexation as $matricule => $ligne) {
            for ($i = 0; $i < count($capseul_bis); $i ++) {
                if ($matricule == $capseul_bis[$i]['matriculeEtudiant']) {
                    $releve_bis[$cpt] = $capseul_bis[$i];
                    $cpt ++;
                    $seul[$matricule] = true;
                } else {
                    if (isset($seul[$matricule]))
                        break;
                }
            }
            for ($i = 0; $i < count($capinterne_bis); $i ++) {
                if ($matricule == $capinterne_bis[$i]['matriculeEtudiant']) {
                    $releve_bis[$cpt] = $capinterne_bis[$i];
                    $cpt ++;
                    $ext[$matricule] = true;
                } else {
                    if (isset($ext[$matricule]))
                        break;
                }
            }
            for ($i = 0; $i < count($capexterne_bis); $i ++) {
                if ($matricule == $capexterne_bis[$i]['matriculeEtudiant']) {
                    $releve_bis[$cpt] = $capexterne_bis[$i];
                    $cpt ++;
                    $int[$matricule] = true;
                } else {
                    if (isset($int[$matricule]))
                        break;
                }
            }
            for ($i = 0; $i < count($noncap_t_bis); $i ++) {
                if ($matricule == $noncap_t_bis[$i]['matriculeEtudiant']) {
                    $releve_bis[$cpt] = $noncap_t_bis[$i];
                    $cpt ++;
                    $nonCap[$matricule] = true;
                } else {
                    if (isset($nonCap[$matricule]))
                        break;
                }
            }
        }
        return $releve_bis;
    }

    /* fin code view [releve_bis] */
    
    /* Debut code view [modules_non_valides_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [modules_non_valides_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function modules_non_valides_bis($matriculeEtudiant, $releve_bis)
    {
        $modules_non_valides_bis = array();
        $cpt = 0;
        for ($i = 0; $i < count($releve_bis); $i ++) {
            if ($releve_bis[$i]['capit'] != 'NC') {
                continue;
            }
            $row['idModule'] = $releve_bis[$i]['idModule'];
            $row['matriculeEtudiant'] = $releve_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $releve_bis[$i]['semestre'];
            $row['annee'] = $releve_bis[$i]['annee'];
            $row['decision'] = 'NV';
            // si le module est deja traite on passe a l'uteration suivente
            if ($this->exist_in_modules_non_valides_bis($modules_non_valides_bis, $row)) {
                continue;
            }
            $sommeCredits = 0;
            for ($j = 0; $j < count($releve_bis); $j ++) {
                if ($releve_bis[$i]['capit'] == $releve_bis[$j]['capit'])
                    if ($releve_bis[$i]['idModule'] == $releve_bis[$j]['idModule'])
                        if ($releve_bis[$i]['matriculeEtudiant'] == $releve_bis[$j]['matriculeEtudiant'])
                            if ($releve_bis[$i]['semestre'] == $releve_bis[$j]['semestre'])
                                if ($releve_bis[$i]['annee'] == $releve_bis[$j]['annee']) {
                                    $sommeCredits += $releve_bis[$j]['ects'];
                                }
            }
            $modules_non_valides_bis[$cpt] = $row;
            $modules_non_valides_bis[$cpt]['creditsValid'] = $sommeCredits;
            $cpt ++;
        }
        return $modules_non_valides_bis;
    }

    // version pour plusieur etudiants
    function modules_non_valides_bis_tous($matriculeEtudiant, $releve_bis, $indexation_releve)
    {
        $modules_non_valides_bis = array();
        $cpt = 0;
        $debut = 0;
        for ($i = 0; $i < count($releve_bis); $i ++) {
            if ($releve_bis[$i]['capit'] != 'NC') {
                continue;
            }
            $row['idModule'] = $releve_bis[$i]['idModule'];
            $row['matriculeEtudiant'] = $releve_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $releve_bis[$i]['semestre'];
            $row['annee'] = $releve_bis[$i]['annee'];
            $row['decision'] = 'NV';
            if (count($modules_non_valides_bis) > 0 and $cpt > 0) {
                if ($modules_non_valides_bis[$cpt - 1]['matriculeEtudiant'] != $releve_bis[$i]['matriculeEtudiant']) {
                    $debut = $cpt;
                }
            }
            // si le module est deja traite on passe a l'uteration suivente
            if ($this->exist_in_modules_non_valides_bis($modules_non_valides_bis, $row, $debut)) {
                continue;
            }
            $sommeCredits = 0;
            for ($j = $indexation_releve[$releve_bis[$i]['matriculeEtudiant']]['debut']; $j < $indexation_releve[$releve_bis[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($releve_bis[$i]['capit'] == $releve_bis[$j]['capit'])
                    // if ($releve_bis[$i]['idModule'] == $releve_bis[$j]['idModule'])
                    if (strcasecmp($releve_bis[$i]['idModule'], $releve_bis[$j]['idModule']) == 0)
                        // if ($releve_bis[$i]['matriculeEtudiant'] == $releve_bis[$j]['matriculeEtudiant'])
                        if ($releve_bis[$i]['semestre'] == $releve_bis[$j]['semestre'])
                            
                            if ($releve_bis[$i]['annee'] == $releve_bis[$j]['annee']) {
                                $sommeCredits += $releve_bis[$j]['ects'];
                            }
            }
            $modules_non_valides_bis[$cpt] = $row;
            $modules_non_valides_bis[$cpt]['creditsValid'] = $sommeCredits;
            $cpt ++;
        }
        return $modules_non_valides_bis;
    }

    // verifi l'existence pour eliminer les doublons
    function exist_in_modules_non_valides_bis($modules_non_valides_bis, $row, $debut = 0)
    {
        for ($i = $debut; $i < count($modules_non_valides_bis); $i ++) {
            if ($modules_non_valides_bis[$i]['idModule'] == $row['idModule'])
                if ($modules_non_valides_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                    if ($modules_non_valides_bis[$i]['semestre'] == $row['semestre'])
                        if ($modules_non_valides_bis[$i]['annee'] == $row['annee'])
                            if ($modules_non_valides_bis[$i]['decision'] == $row['decision'])
                                return true;
        }
        return false;
    }

    /* fin code view [modules_non_valides_bis] */
    
    /* Debut code view [modules_v_sans_compense_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [modules_v_sans_compense_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function modules_v_sans_compense_bis($matriculeEtudiant, $releve_bis)
    {
        $modules_v_sans_compense_bis = array();
        $cpt = 0;
        for ($i = 0; $i < count($releve_bis); $i ++) {
            $row['idModule'] = $releve_bis[$i]['idModule'];
            $row['matriculeEtudiant'] = $releve_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $releve_bis[$i]['semestre'];
            $row['annee'] = $releve_bis[$i]['annee'];
            $row['capit'] = $releve_bis[$i]['capit'];
            if ($this->exist_in_modules_v_sans_compense_bis($modules_v_sans_compense_bis, $row)) {
                continue;
            }
            if (! $this->exist_in_releve_bis_capit_NC_CE($releve_bis, $row)) {
                unset($row['capit']);
                $modules_v_sans_compense_bis[$cpt] = $row;
                $modules_v_sans_compense_bis[$cpt]['decision'] = 'V';
                $cpt ++;
            }
        }
        return $modules_v_sans_compense_bis;
    }

    // pour plusieur etudiants
    function modules_v_sans_compense_bis_tous($releve_bis, $indexation_releve)
    {
        $modules_v_sans_compense_bis = array();
        $cpt = 0;
        $debut = 0;
        for ($i = 0; $i < count($releve_bis); $i ++) {
            $row['idModule'] = $releve_bis[$i]['idModule'];
            $row['matriculeEtudiant'] = $releve_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $releve_bis[$i]['semestre'];
            $row['annee'] = $releve_bis[$i]['annee'];
            $row['capit'] = $releve_bis[$i]['capit'];
            if ($cpt > 0) {
                if ($modules_v_sans_compense_bis[$cpt - 1]['matriculeEtudiant'] != $releve_bis[$i]['matriculeEtudiant']) {
                    $debut = $cpt;
                }
            }
            if ($this->exist_in_modules_v_sans_compense_bis($modules_v_sans_compense_bis, $row, $debut)) {
                continue;
            }
            if (! $this->exist_in_releve_bis_capit_NC_CE($releve_bis, $row, $indexation_releve[$releve_bis[$i]['matriculeEtudiant']]['debut'], $indexation_releve[$releve_bis[$i]['matriculeEtudiant']]['fin'] + 1)) {
                unset($row['capit']);
                $modules_v_sans_compense_bis[$cpt] = $row;
                $modules_v_sans_compense_bis[$cpt]['decision'] = 'V';
                $cpt ++;
            }
        }
        return $modules_v_sans_compense_bis;
    }

    // verifi s'il y a une matiere non capitalisee ou capitalise avec capitalisation externe
    function exist_in_releve_bis_capit_NC_CE($releve_bis, $row, $debut = 0, $fin = -1)
    {
        if ($fin == - 1)
            $fin = count($releve_bis);
        for ($i = $debut; $i < $fin; $i ++) {
            // if ($releve_bis[$i]['idModule'] == $row['idModule'])
            if (strcasecmp($releve_bis[$i]['idModule'], $row['idModule']) == 0)
                if ($releve_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                    if ($releve_bis[$i]['semestre'] == $row['semestre'])
                        if ($releve_bis[$i]['annee'] == $row['annee'])
                            if (($releve_bis[$i]['capit'] == 'NC') or ($releve_bis[$i]['capit'] == 'CE'))
                                return true;
        }
        return false;
    }

    // verification pour eliminer les doublons
    function exist_in_modules_v_sans_compense_bis($modules_v_sans_compense_bis, $row, $debut = 0)
    {
        for ($i = $debut; $i < count($modules_v_sans_compense_bis); $i ++) {
            // if ($modules_v_sans_compense_bis[$i]['idModule'] == $row['idModule'])
            if (strcasecmp($modules_v_sans_compense_bis[$i]['idModule'], $row['idModule']) == 0)
                if ($modules_v_sans_compense_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                    if ($modules_v_sans_compense_bis[$i]['semestre'] == $row['semestre'])
                        if ($modules_v_sans_compense_bis[$i]['annee'] == $row['annee'])
                            return true;
        }
        return false;
    }

    /* fin code view [modules_v_sans_compense_bis] */
    
    /* Debut code view [module_v_avec_compense_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [module_v_avec_compense_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function module_v_avec_compense_bis($matriculeEtudiant, $releve_bis)
    {
        $module_v_avec_compense_bis = array();
        $cpt = 0;
        for ($i = 0; $i < count($releve_bis); $i ++) {
            $row['idModule'] = $releve_bis[$i]['idModule'];
            $row['matriculeEtudiant'] = $releve_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $releve_bis[$i]['semestre'];
            $row['annee'] = $releve_bis[$i]['annee'];
            $row['capit'] = $releve_bis[$i]['capit'];
            if ($this->exist_in_module_v_avec_compense_bis($module_v_avec_compense_bis, $row)) {
                continue;
            }
            if ($this->exist_in_releve_bis_capit_CE($releve_bis, $row)) {
                if (! $this->exist_in_releve_bis_capit_NC($releve_bis, $row)) {
                    unset($row['capit']);
                    
                    $module_v_avec_compense_bis[$cpt] = $row;
                    $module_v_avec_compense_bis[$cpt]['decision'] = 'VC';
                    $cpt ++;
                }
            }
        }
        return $module_v_avec_compense_bis;
    }

    // pour plusieur etudiants
    function module_v_avec_compense_bis_tous($releve_bis, $indexation_rel)
    {
        $module_v_avec_compense_bis = array();
        $cpt = 0;
        $debut = 0;
        for ($i = 0; $i < count($releve_bis); $i ++) {
            $row['idModule'] = $releve_bis[$i]['idModule'];
            $row['matriculeEtudiant'] = $releve_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $releve_bis[$i]['semestre'];
            $row['annee'] = $releve_bis[$i]['annee'];
            $row['capit'] = $releve_bis[$i]['capit'];
            if ($cpt > 0) {
                if ($module_v_avec_compense_bis[$cpt - 1]['matriculeEtudiant'] != $releve_bis[$i]['matriculeEtudiant']) {
                    $debut = $cpt;
                }
            }
            if ($this->exist_in_module_v_avec_compense_bis($module_v_avec_compense_bis, $row, $debut)) {
                continue;
            }
            if ($this->exist_in_releve_bis_capit_CE($releve_bis, $row, $indexation_rel[$releve_bis[$i]['matriculeEtudiant']]['debut'], $indexation_rel[$releve_bis[$i]['matriculeEtudiant']]['fin'] + 1)) {
                if (! $this->exist_in_releve_bis_capit_NC($releve_bis, $row, $indexation_rel[$releve_bis[$i]['matriculeEtudiant']]['debut'], $indexation_rel[$releve_bis[$i]['matriculeEtudiant']]['fin'] + 1)) {
                    unset($row['capit']);
                    
                    $module_v_avec_compense_bis[$cpt] = $row;
                    $module_v_avec_compense_bis[$cpt]['decision'] = 'VC';
                    $cpt ++;
                }
            }
        }
        return $module_v_avec_compense_bis;
    }

    // verifi s'il y a une matiere non capitalisee ou capitalise dans un module $row['module']
    function exist_in_releve_bis_capit_NC($releve_bis, $row)
    {
        for ($i = 0; $i < count($releve_bis); $i ++) {
            if ($releve_bis[$i]['idModule'] == $row['idModule'])
                if ($releve_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                    if ($releve_bis[$i]['semestre'] == $row['semestre'])
                        if ($releve_bis[$i]['annee'] == $row['annee'])
                            if ($releve_bis[$i]['capit'] == 'NC')
                                return true;
        }
        return false;
    }

    // verifi s'il y a une matiere capitalisee avec capitalisation externe dans un module $row['module']
    function exist_in_releve_bis_capit_CE($releve_bis, $row, $debut = 0, $fin = -1)
    {
        if ($fin == - 1)
            $fin = count($releve_bis);
        for ($i = $debut; $i < $fin; $i ++) {
            if ($releve_bis[$i]['idModule'] == $row['idModule'])
                if ($releve_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                    if ($releve_bis[$i]['semestre'] == $row['semestre'])
                        if ($releve_bis[$i]['annee'] == $row['annee'])
                            if ($releve_bis[$i]['capit'] == 'CE')
                                return true;
        }
        return false;
    }

    // verification pour eliminer les doublons (pour garder uniquement les resultat distincts)
    function exist_in_module_v_avec_compense_bis($module_v_avec_compense_bis, $row, $debut = 0)
    {
        for ($i = $debut; $i < count($module_v_avec_compense_bis); $i ++) {
            if ($module_v_avec_compense_bis[$i]['idModule'] == $row['idModule'])
                if ($module_v_avec_compense_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                    if ($module_v_avec_compense_bis[$i]['semestre'] == $row['semestre'])
                        if ($module_v_avec_compense_bis[$i]['annee'] == $row['annee'])
                            return true;
        }
        return false;
    }

    /* fin code view [module_v_avec_compense_bis] */
    
    /* Debut code view [modules_valides_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [modules_valides_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    function modules_valides_bis($matriculeEtudiant, $modules_v_sans_compense_bis, $module_v_avec_compense_bis)
    {
        /*
         * $modules_valides_bis = array();
         * $cpt = 0;
         * for ($i = 0; $i < count($modules_v_sans_compense_bis); $i ++) {
         * $modules_valides_bis[$cpt] = $modules_v_sans_compense_bis[$i];
         * $cpt ++;
         * }
         */
        $modules_valides_bis = $modules_v_sans_compense_bis;
        $cpt = count($modules_v_sans_compense_bis);
        
        for ($i = 0; $i < count($module_v_avec_compense_bis); $i ++) {
            $modules_valides_bis[$cpt] = $module_v_avec_compense_bis[$i];
            $cpt ++;
        }
        $colonne = array_column($modules_valides_bis, 'matriculeEtudiant');
        array_multisort($colonne, SORT_ASC, $modules_valides_bis);
        return $modules_valides_bis;
    }

    /* fin code view [modules_valides_bis] */
    
    /* Debut code view [modules_decision_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [modules_decision_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     */
    // $indexation_planetudes
    function modules_decision_bis($matriculeEtudiant, $planetudesmoduleelem, $modules_valides_bis, $modules_non_valides_bis, $noteValidationMatiere)
    {
        $indexation_planetudes = $this->indexation_matricules_notespartielles($planetudesmoduleelem);
        $modules_decision_bis = array();
        $cpt = 0;
        for ($i = 0; $i < count($modules_valides_bis); $i ++) {
            $row['idModule'] = $modules_valides_bis[$i]['idModule'];
            $row['matriculeEtudiant'] = $modules_valides_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $modules_valides_bis[$i]['semestre'];
            $row['annee'] = $modules_valides_bis[$i]['annee'];
            $row['decision'] = $modules_valides_bis[$i]['decision'];
            
            if ($this->exist_in_modules_decision_bis($modules_decision_bis, $row)) {
                continue;
            }
            $sommeCredits = 0;
            $trouve = false;
            for ($j = $indexation_planetudes[$modules_valides_bis[$i]['matriculeEtudiant']]['debut']; $j < $indexation_planetudes[$modules_valides_bis[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($modules_valides_bis[$i]['idModule'] == $planetudesmoduleelem[$j]['module'])
                    if ($modules_valides_bis[$i]['matriculeEtudiant'] == $planetudesmoduleelem[$j]['matriculeEtudiant'])
                        if ($modules_valides_bis[$i]['semestre'] == $planetudesmoduleelem[$j]['semestre'])
                            if ($modules_valides_bis[$i]['annee'] == $planetudesmoduleelem[$j]['annee']) {
                                $sommeCredits += $planetudesmoduleelem[$j]['nbCredits'];
                                $trouve = true;
                            }
            }
            if ($trouve) {
                $modules_decision_bis[$cpt] = $row;
                $modules_decision_bis[$cpt]['credits_val'] = $sommeCredits;
                $cpt ++;
            }
        }
        for ($i = 0; $i < count($modules_non_valides_bis); $i ++) {
            $row['idModule'] = $modules_non_valides_bis[$i]['idModule'];
            $row['matriculeEtudiant'] = $modules_non_valides_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $modules_non_valides_bis[$i]['semestre'];
            $row['annee'] = $modules_non_valides_bis[$i]['annee'];
            $row['decision'] = 'NV';
            if ($this->exist_in_modules_decision_bis($modules_decision_bis, $row)) {
                continue;
            }
            $sommeCredits = 0;
            for ($j = $indexation_planetudes[$modules_non_valides_bis[$i]['matriculeEtudiant']]['debut']; $j < $indexation_planetudes[$modules_non_valides_bis[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($modules_non_valides_bis[$i]['idModule'] == $planetudesmoduleelem[$j]['module'])
                    if ($modules_non_valides_bis[$i]['matriculeEtudiant'] == $planetudesmoduleelem[$j]['matriculeEtudiant'])
                        if ($modules_non_valides_bis[$i]['semestre'] == $planetudesmoduleelem[$j]['semestre'])
                            if ($modules_non_valides_bis[$i]['annee'] == $planetudesmoduleelem[$j]['annee'])
                                if ($planetudesmoduleelem[$j]['note'] >= $noteValidationMatiere) {
                                    $sommeCredits += $planetudesmoduleelem[$j]['nbCredits'];
                                }
            }
            $modules_decision_bis[$cpt] = $row;
            $modules_decision_bis[$cpt]['credits_val'] = $sommeCredits;
            $cpt ++;
        }
        return $modules_decision_bis;
    }

    // pour plusieur etudiants
    function modules_decision_bis_tous($planetudesmoduleelem, $indexation_planetudes, $modules_valides_bis, $modules_non_valides_bis, $noteValidationMatiere)
    {
        $modules_decision_bis = array();
        $cpt = 0;
        for ($i = 0; $i < count($modules_valides_bis); $i ++) {
            $row['idModule'] = $modules_valides_bis[$i]['idModule'];
            $row['matriculeEtudiant'] = $modules_valides_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $modules_valides_bis[$i]['semestre'];
            $row['annee'] = $modules_valides_bis[$i]['annee'];
            $row['decision'] = $modules_valides_bis[$i]['decision'];
            
            if ($this->exist_in_modules_decision_bis($modules_decision_bis, $row)) {
                continue;
            }
            $sommeCredits = 0;
            $trouve = false;
            for ($j = $indexation_planetudes[$modules_valides_bis[$i]['matriculeEtudiant']]['debut']; $j < $indexation_planetudes[$modules_valides_bis[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($modules_valides_bis[$i]['idModule'] == $planetudesmoduleelem[$j]['module'])
                    if ($modules_valides_bis[$i]['matriculeEtudiant'] == $planetudesmoduleelem[$j]['matriculeEtudiant'])
                        if ($modules_valides_bis[$i]['semestre'] == $planetudesmoduleelem[$j]['semestre'])
                            if ($modules_valides_bis[$i]['annee'] == $planetudesmoduleelem[$j]['annee']) {
                                $sommeCredits += $planetudesmoduleelem[$j]['nbCredits'];
                                $trouve = true;
                            }
            }
            if ($trouve) {
                $modules_decision_bis[$cpt] = $row;
                $modules_decision_bis[$cpt]['credits_val'] = $sommeCredits;
                $cpt ++;
            }
        }
        for ($i = 0; $i < count($modules_non_valides_bis); $i ++) {
            $row['idModule'] = $modules_non_valides_bis[$i]['idModule'];
            $row['matriculeEtudiant'] = $modules_non_valides_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $modules_non_valides_bis[$i]['semestre'];
            $row['annee'] = $modules_non_valides_bis[$i]['annee'];
            $row['decision'] = 'NV';
            if ($this->exist_in_modules_decision_bis($modules_decision_bis, $row)) {
                continue;
            }
            $sommeCredits = 0;
            for ($j = $indexation_planetudes[$modules_non_valides_bis[$i]['matriculeEtudiant']]['debut']; $j < $indexation_planetudes[$modules_non_valides_bis[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($modules_non_valides_bis[$i]['idModule'] == $planetudesmoduleelem[$j]['module'])
                    if ($modules_non_valides_bis[$i]['matriculeEtudiant'] == $planetudesmoduleelem[$j]['matriculeEtudiant'])
                        if ($modules_non_valides_bis[$i]['semestre'] == $planetudesmoduleelem[$j]['semestre'])
                            if ($modules_non_valides_bis[$i]['annee'] == $planetudesmoduleelem[$j]['annee'])
                                if ($planetudesmoduleelem[$j]['note'] >= $noteValidationMatiere) {
                                    $sommeCredits += $planetudesmoduleelem[$j]['nbCredits'];
                                }
            }
            $modules_decision_bis[$cpt] = $row;
            $modules_decision_bis[$cpt]['credits_val'] = $sommeCredits;
            $cpt ++;
        }
        $colonne = array_column($modules_decision_bis, 'matriculeEtudiant');
        array_multisort($colonne, SORT_ASC, $modules_decision_bis);
        return $modules_decision_bis;
    }

    // verifi l'existence pour grouper les enregistrements (elimner les repetitions)
    function exist_in_modules_decision_bis($modules_decision_bis, $row)
    {
        for ($i = 0; $i < count($modules_decision_bis); $i ++) {
            if ($modules_decision_bis[$i]['idModule'] == $row['idModule'])
                if ($modules_decision_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                    if ($modules_decision_bis[$i]['semestre'] == $row['semestre'])
                        if ($modules_decision_bis[$i]['annee'] == $row['annee'])
                            if ($modules_decision_bis[$i]['decision'] == $row['decision'])
                                return true;
        }
        return false;
    }

    /* fin code view [modules_decision_bis] */
    
    /* Debut code view [semestre_decision_bis] */
    /*
     * fonction pour generer une table(array) qui simule la vue [semestre_decision_bis] (sql)
     * Pour un seul etudiant $matriculeEtudiant
     * La decision [Ajourne(e)] ne peut pas etre affichee correctement
     * c'est a partager sous plusieurs fonctions
     */
    // version complette de la view
    function semestre_decision_bis($matriculeEtudiant, $modules_decision_bis)
    {
        $indexation = $this->indexation_matricules_notespartielles($modules_decision_bis);
        $semestre_decision_bis = array();
        $cpt = 0;
        $debut = 0;
        for ($i = 0; $i < count($modules_decision_bis); $i ++) {
            if ($modules_decision_bis[$i]['decision'] != 'V')
                continue;
            $sommeCredits_val = 0;
            $difVal = false;
            for ($j = $indexation[$modules_decision_bis[$i]['matriculeEtudiant']]['debut']; $j < $indexation[$modules_decision_bis[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($modules_decision_bis[$i]['matriculeEtudiant'] == $modules_decision_bis[$j]['matriculeEtudiant'])
                    if ($modules_decision_bis[$i]['semestre'] == $modules_decision_bis[$j]['semestre'])
                        if ($modules_decision_bis[$i]['annee'] == $modules_decision_bis[$j]['annee']) {
                            if ($modules_decision_bis[$j]['decision'] != 'V') {
                                $difVal = true;
                                break;
                            }
                            $sommeCredits_val += $modules_decision_bis[$j]['credits_val'];
                        }
            }
            if ($difVal)
                continue;
            $row['matriculeEtudiant'] = $modules_decision_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $modules_decision_bis[$i]['semestre'];
            $row['annee'] = $modules_decision_bis[$i]['annee'];
            $row['decision'] = 'Admis(e)';
            if ($cpt > 0) {
                if ($semestre_decision_bis[$cpt - 1]['matriculeEtudiant'] != $modules_decision_bis[$i]['matriculeEtudiant']) {
                    $debut = $cpt;
                }
            }
            if ($this->exist_in_semestre_decision_bis($semestre_decision_bis, $row, $debut)) {
                continue;
            }
            $semestre_decision_bis[$cpt] = $row;
            $semestre_decision_bis[$cpt]['credits_val'] = $sommeCredits_val;
            $cpt ++;
        }
        for ($i = 0; $i < count($modules_decision_bis); $i ++) {
            if ($modules_decision_bis[$i]['decision'] == 'NV')
                continue;
            $row['matriculeEtudiant'] = $modules_decision_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $modules_decision_bis[$i]['semestre'];
            $row['annee'] = $modules_decision_bis[$i]['annee'];
            if ($cpt > 0) {
                if ($semestre_decision_bis[$cpt - 1]['matriculeEtudiant'] != $modules_decision_bis[$i]['matriculeEtudiant']) {
                    $debut = $cpt;
                }
            }
            if ($this->exist_in_semestre_decision_bis($semestre_decision_bis, $row, $debut))
                continue;
            $sommeCredits_val = 0;
            $NV = false;
            $VC = false;
            for ($j = $indexation[$modules_decision_bis[$i]['matriculeEtudiant']]['debut']; $j < $indexation[$modules_decision_bis[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($modules_decision_bis[$i]['matriculeEtudiant'] == $modules_decision_bis[$j]['matriculeEtudiant'])
                    if ($modules_decision_bis[$i]['semestre'] == $modules_decision_bis[$j]['semestre'])
                        if ($modules_decision_bis[$i]['annee'] == $modules_decision_bis[$j]['annee']) {
                            if ($modules_decision_bis[$j]['decision'] == 'NV') {
                                $NV = true;
                                break;
                            }
                            if ($modules_decision_bis[$j]['decision'] == 'VC')
                                $VC = true;
                            $sommeCredits_val += $modules_decision_bis[$j]['credits_val'];
                        }
            }
            if ($NV)
                continue;
            if (! $VC)
                continue;
            $row['decision'] = 'Compense';
            $semestre_decision_bis[$cpt] = $row;
            $semestre_decision_bis[$cpt]['credits_val'] = $sommeCredits_val;
            $cpt ++;
        }
        for ($i = 0; $i < count($modules_decision_bis); $i ++) {
            $row['matriculeEtudiant'] = $modules_decision_bis[$i]['matriculeEtudiant'];
            $row['semestre'] = $modules_decision_bis[$i]['semestre'];
            $row['annee'] = $modules_decision_bis[$i]['annee'];
            if ($this->exist_in_semestre_decision_bis($semestre_decision_bis, $row)) {
                continue;
            }
            $sommeCredits_val = 0;
            $NV = false;
            for ($j = $indexation[$modules_decision_bis[$i]['matriculeEtudiant']]['debut']; $j < $indexation[$modules_decision_bis[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                if ($modules_decision_bis[$i]['matriculeEtudiant'] == $modules_decision_bis[$j]['matriculeEtudiant'])
                    if ($modules_decision_bis[$i]['semestre'] == $modules_decision_bis[$j]['semestre'])
                        if ($modules_decision_bis[$i]['annee'] == $modules_decision_bis[$j]['annee']) {
                            if ($modules_decision_bis[$j]['decision'] == 'NV') {
                                $NV = true;
                            }
                            $sommeCredits_val += $modules_decision_bis[$j]['credits_val'];
                        }
            }
            if (! $NV)
                continue;
            $row['decision'] = 'Ajourné(e)';
            $semestre_decision_bis[$cpt] = $row;
            $semestre_decision_bis[$cpt]['credits_val'] = $sommeCredits_val;
            $cpt ++;
        }
        $colonne = array_column($semestre_decision_bis, 'matriculeEtudiant');
        array_multisort($colonne, SORT_ASC, $semestre_decision_bis);
        return $semestre_decision_bis;
    }

    // verifi l'existence pour grouper les enregistrements (elimner les repetitions)
    function exist_in_semestre_decision_bis($semestre_decision_bis, $row, $debut = 0)
    {
        for ($i = $debut; $i < count($semestre_decision_bis); $i ++) {
            if ($semestre_decision_bis[$i]['matriculeEtudiant'] == $row['matriculeEtudiant'])
                if ($semestre_decision_bis[$i]['semestre'] == $row['semestre'])
                    if ($semestre_decision_bis[$i]['annee'] == $row['annee'])
                        if ($semestre_decision_bis[$i]['decision'] == $row['decision'])
                            return true;
        }
        return false;
    }

    // resultat du semestre
    function getSemestreResult_bis($matriculeEtudiant, $semestre, $annee, $semestre_decision_bis, $etudiant_sem_note_bis)
    {
        //echo 'semestre =  '.$semestre;
        $data = null;
        foreach ($semestre_decision_bis as $key => $value) {
            foreach ($etudiant_sem_note_bis as $key2 => $value2) {
                if ($value['matriculeEtudiant'] == $matriculeEtudiant)
                    if ($value2['matriculeEtudiant'] == $matriculeEtudiant)
                        if ($value['semestre'] == $semestre)
                            if ($value2['semestre'] == $semestre)
                                if ($value['annee'] == $annee)
                                    if ($value2['annee'] == $annee) {
                                        $data['matriculeEtudiant'] = $matriculeEtudiant;
                                        $data['semestre'] = $semestre;
                                        $data['ects'] = $value['credits_val'];
                                        $data['decision'] = $value['decision'];
                                        $data['note'] = $value2['note'];
                                        break;
                                    }
            }
        }
       
        if (count($data) != 0) {
            if (substr($data['decision'], 0, 3) == 'Ajo') {
                $data['validation'] = 'NV';
            } else {
                $data['validation'] = 'V';
            }
            $data['inscrit'] = 'OK';
        } else {
            $data['inscrit'] = 'NO';
        }
        return $data;
    }

    function tri_releve_bis_par_annee($releve_bis)
    {
        for ($i = 0; $i < count($releve_bis) - 1; $i ++) {
            for ($j = $i + 1; $j < count($releve_bis); $j ++) {
                if ($releve_bis[$i]['annee'] > $releve_bis[$j]['annee']) {
                    $tmp = $releve_bis[$i];
                    $releve_bis[$i] = $releve_bis[$j];
                    $releve_bis[$j] = $tmp;
                }
            }
        }
        return $releve_bis;
    }

    function getModulesResult_bis_h_partie_1($matriculeEtudiant, $semestre, $annee, $releve_bis, $notes_globales, $module)
    {
        $releve_bis = $this->tri_releve_bis_par_annee($releve_bis);
        $resultat = array();
        $cpt = 0;
        foreach ($releve_bis as $key_r => $value_r) {
            if ($value_r['matriculeEtudiant'] == $matriculeEtudiant)
                if ($value_r['semestre'] == $semestre)
                    if ($value_r['annee'] <= $annee)
                        foreach ($notes_globales as $key_n => $value_n) {
                            if ($value_n['matriculeEtudiant'] == $matriculeEtudiant)
                                if ($value_r['semestre'] == $value_n['semestre'])
                                    if (strcasecmp($value_r['sigle'], $value_n['sigle']) == 0) {
                                        $an = substr($value_n['annee'], 0, 2);
                                        if ($semestre % 2 == 1) {
                                            $ann = 2000 + (int) $an;
                                        } else {
                                            $ann = 2001 + (int) $an;
                                        }
                                        if ($value_r['annee'] == $ann)
                                            foreach ($module as $key_m => $value_m) {
                                                if (strcasecmp($value_r['sigle'], $value_m['sigle']) == 0) {
                                                    $resultat[$cpt]['annee'] = $value_r['annee'];
                                                    $resultat[$cpt]['idModule'] = $value_r['idModule'];
                                                    $resultat[$cpt]['sigle'] = $value_r['sigle'];
                                                    $resultat[$cpt]['note'] = $value_r['note'];
                                                    $resultat[$cpt]['capit'] = $value_r['capit'];
                                                    $resultat[$cpt]['ects'] = $value_r['ects'];     
                                                    $resultat[$cpt]['coefficient'] = $value_r['coefficient'];
                                                    $resultat[$cpt]['titre'] = $value_m['titre'];
                                                    $resultat[$cpt]['notecc'] = $value_n['noteCC'];
                                                    $resultat[$cpt]['noteExam'] = $value_n['noteExam'];
                                                    $resultat[$cpt]['noteRT'] = $value_n['noteRT'];
                                                    $cpt ++;
                                                }
                                            }
                                    }
                        }
        }
        
        return $resultat;
    }

    // retourne la decision nde validation d'un module, credits, tire, note dans un semestre et une annee pour un etudiant
    function getModulesDecision_bis($module, $matriculeEtudiant, $semestre, $annee, $modules_decision_bis, $etudiant_mod_note_bis, $unite)
    {
        $resultat = array();
        foreach ($modules_decision_bis as $key_m => $value_m) {
            if ($value_m['matriculeEtudiant'] == $matriculeEtudiant) 
                // if($value_m['idModule']==$module)
                if (strcasecmp($value_m['idModule'], $module) == 0)
                    if ($value_m['semestre'] == $semestre)
                        if ($value_m['annee'] == $annee)
                            foreach ($etudiant_mod_note_bis as $key_e => $value_e) {
                                if ($value_e['matriculeEtudiant'] == $matriculeEtudiant)
                                    // if($value_m['idModule']==$value_e['idModule'])
                                    if (strcasecmp($value_m['idModule'], $value_e['idModule']) == 0)
                                         if ($value_m['annee'] == $value_e['annee'])
                                            if ($value_m['semestre'] == $value_e['semestre'])
                                                foreach ($unite as $key_u => $value_u) {
                                                    // if($value_m['idModule']==$value_u['sigle']){
                                                    if (strcasecmp($value_m['idModule'], $value_u['sigle']) == 0) {
                                                        $resultat['titre'] = $value_u['titre'];
                                                        $resultat['decision'] = $value_m['decision'];
                                                        $resultat['ects'] = $value_u['credits'];
                                                        $resultat['note'] = $value_e['note'];
                                                         $resultat['coefficient'] = $value_u['coefficient'];//add by MedBakar 25-02-2020
                                                        return $resultat;
                                                    }
                                                    }
                            }
        }

        return $resultat;
    }

    // resultat des modules contient l'historique des elements
    function getModulesResult_bis_h($matriculeEtudiant, $semestre, $annee, $releve_bis, $notes_globales, $module, $modules_decision_bis, $etudiant_mod_note_bis, $unite)
    {
        $resultat = $this->getModulesResult_bis_h_partie_1($matriculeEtudiant, $semestre, $annee, $releve_bis, $notes_globales, $module);
      
        $modules = NULL;
        if (count($resultat) > 0) {
            foreach ($resultat as $row) {
                $moduleDec = NULL;
                if ($modules == NULL) {
                    $modules = array();
                    $moduleDec = $this->getModulesDecision_bis($row['idModule'], $matriculeEtudiant, $semestre, $annee, $modules_decision_bis, $etudiant_mod_note_bis, $unite);
                    if ($moduleDec != NULL) {
                        $modules[$row['idModule']] = array();
                        $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                        $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                        $modules[$row['idModule']]['nm'] = $moduleDec['note']; //********
                        $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                        $modules[$row['idModule']]['coefficient'] = $moduleDec['coefficient'];//add by MedBakar 25-02-2020
                    }
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                } else if (! array_key_exists($row['idModule'], $modules)) {
                    $moduleDec = $this->getModulesDecision_bis($row['idModule'], $matriculeEtudiant, $semestre, $annee, $modules_decision_bis, $etudiant_mod_note_bis, $unite);
                    //echo "<br>";
                    if ($moduleDec != NULL) {
                        $modules[$row['idModule']] = array();
                        $modules[$row['idModule']]['titre'] = $moduleDec['titre'];
                        $modules[$row['idModule']]['decision'] = $moduleDec['decision'];
                        $modules[$row['idModule']]['nm'] = $moduleDec['note'];//***********
                        $modules[$row['idModule']]['ects'] = $moduleDec['ects'];
                        $modules[$row['idModule']]['coefficient'] = $moduleDec['coefficient'];//add by MedBakar 25-02-2020
                    }
                    $modules[$row['idModule']]['nb'] = 0;
                    $modules[$row['idModule']]['elements'] = array();
                }
                $modules[$row['idModule']]['elements'][$row['sigle']][] = array();
                $modules[$row['idModule']]['elements'][$row['sigle']]['annee'][] = $row['annee'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ncc'][] = $row['notecc'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsn'][] = $row['noteExam'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nsr'][] = $row['noteRT'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['nfe'][] = $row['note'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['capit'][] = $row['capit'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['ects'][] = $row['ects'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['coefficient'][] = $row['coefficient'];
                $modules[$row['idModule']]['elements'][$row['sigle']]['titre'][] = $row['titre'];
                $modules[$row['idModule']]['nb'] ++;
            }
        }
        
        return $modules;
    }

    // il y a une view qui a le meme nom, on vas la remplacer par cette fonction
    function credit_valide_sem($matriculeEtudiant, $semestre_decision_bis, $etudiant_sem_note_bis)
    {
        $resultat = array();
        $cpt = 0;
        foreach ($semestre_decision_bis as $key => $s1) {
            if ($s1['matriculeEtudiant'] != $matriculeEtudiant)
                continue;
            // verification de l'existance pour le regroupement
            $exist = false;
            foreach ($resultat as $r) {
                if ($r['matriculeEtudiant'] == $matriculeEtudiant)
                    if ($r['semestre'] == $s1['semestre'])
                        $exist = true;
            }
            if ($exist)
                continue;
            $row['matriculeEtudiant'] = $matriculeEtudiant;
            $row['semestre'] = $s1['semestre'];
            $max_credits_val = 0;
            $max_annee = 0;
            foreach ($semestre_decision_bis as $key_2 => $s2) {
                if ($s1['matriculeEtudiant'] == $s2['matriculeEtudiant'])
                    if ($s1['semestre'] == $s2['semestre']) {
                        if ($max_annee < $s2['annee'])
                            $max_annee = $s2['annee'];
                        if ($max_credits_val < $s2['credits_val'])
                            $max_credits_val = $s2['credits_val'];
                    }
            }
            $max_note = 0;
            foreach ($etudiant_sem_note_bis as $key_3 => $e) {
                if ($s1['matriculeEtudiant'] == $e['matriculeEtudiant'])
                    if ($s1['semestre'] == $e['semestre'])
                        if ($s1['annee'] = $e['annee'])
                            if ($max_note < $e['note'])
                                $max_note = $e['note'];
            }
            $resultat[$cpt] = $row;
            $resultat[$cpt]['credits_val'] = $max_credits_val;
            $resultat[$cpt]['note'] = $max_note;
            $resultat[$cpt]['annee'] = $max_annee - 1;
            $cpt ++;
        }
        return $resultat;
    }

    function get_moyenne_niveau($matriculeEtudiant, $semestre_decision_bis, $etudiant_sem_note_bis)
    {
        $info = array();
        $info["MGL1"] = 0;
        $info["ECTSL1"] = 0;
        $info["MGL2"] = 0;
        $info["ECTSL2"] = 0;
        $info["MGL3"] = 0;
        $info["ECTSL3"] = 0;
        $credit_valide_sem = $this->credit_valide_sem($matriculeEtudiant, $semestre_decision_bis, $etudiant_sem_note_bis);
        if (count($credit_valide_sem) > 0) {
            foreach ($credit_valide_sem as $row) {
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
    }

    // max semestre fait par l'etudiant matriculeEtudiant
    function max_semestre($matriculeEtudiant, $releve_bis)
    {
        $max = - 1;
        foreach ($releve_bis as $key => $value) {
            
            if ($value['matriculeEtudiant'] == $matriculeEtudiant)
                if ($value['semestre'] > $max)
                    $max = $value['semestre'];
        }
       
        if ($max == - 1)
            return null;
            return $max;
    }
    
    
    // min annee ou l'etudiant est admis dans le semestre
    function min_annee_admis($matriculeEtudiant, $semestre_decision_bis, $semestre, $decision = "Admis(e)")
    {
        $min = 999 * 999;
        foreach ($semestre_decision_bis as $key => $value) {
            if ($value['matriculeEtudiant'] == $matriculeEtudiant)
                if ($value['semestre'] == $semestre)
                    if ($value['decision'] == $decision or $value['decision'] == 'Compense')
                        if ($value['annee'] < $min)
                            $min = $value['annee'];
        }
        if ($min == 999 * 999)
            return null;
        return $min;
    }
   //l'une de ses deux donctions remplace l'autre -------DEBUT-----
    // max annee ou l'etudiant est ajourne dans le semestre
    function max_annee_admis($matriculeEtudiant, $semestre_decision_bis, $semestre, $decision = "Ajourné(e)")
    {
        $max = - 1;
        foreach ($semestre_decision_bis as $key => $value) {
            if ($value['matriculeEtudiant'] == $matriculeEtudiant)
                if ($value['semestre'] == $semestre)
                    if ($value['decision'] == $decision)
                        if ($value['annee'] > $max)
                            $max = $value['annee'];
        }
        if ($max == - 1)
            return null;
            return $max;
    }
    
     // max annee ou l'etudiant est ajourne dans le semestre
    function max_annee_ajournee($matriculeEtudiant, $semestre_decision_bis, $semestre, $decision = "Ajourné(e)")
    {
        $max = - 1;
        foreach ($semestre_decision_bis as $key => $value) {
            if ($value['matriculeEtudiant'] == $matriculeEtudiant)
                if ($value['semestre'] == $semestre)
                    if ($value['decision'] == $decision)
                        if ($value['annee'] > $max)
                            $max = $value['annee'];
        }
        if ($max == - 1)
            return null;
            return $max;
    }
   //l'une de ses deux donctions remplace l'autre----------------------FIN-------
    function examination_liste()//MedBakar -esq utilise prq initialiser(static)         Non pas utilise
    {
        $semestre = 1;
        $departement = 'LGTR';
        $annee = 2018;
        
        $matricules = array(13051, 13149, 14106, 14134, 14136, 14152, 15023, 15095, 15097, 15122, 15144, 15192, 15251, 15271, 15286, 16190, 16201, 16217, 16231, 16262, 16267, 17021, 17047, 17064, 17072, 17093, 17096, 17098, 17112, 17136, 17171, 17184, 17193, 17200, 17202, 17212, 17215, 17216, 17226, 17229, 17230, 17233, 17239, 17247, 17248, 17249, 17253, 18015, 18018, 18021, 18022, 18023, 18025, 18029, 18030, 18032, 18035, 18038, 18039, 18040, 18041, 18043, 18046, 18052, 18058, 18059, 18071, 18075, 18082, 18083, 18084, 18085, 18087, 18091, 18094, 18096, 18099, 18102, 18111, 18112, 18113, 18116, 18117, 18124, 18126, 18132, 18137, 18138, 18139, 18147, 18162, 18165, 18171, 18172, 18175, 18179, 18187, 18197, 18199, 18200, 18201, 18202, 18203, 18204, 18207, 18209, 18210, 18216, 18218, 18219, 18221, 18222, 18223, 18224, 18227, 18235, 18238, 18241, 18242, 18246, 18247, 18250, 18252, 18255, 18259, 18260, 18261, 18263, 18264, 18270, 18271, 18272, 18274, 18277, 18278, 18279, 18283, 18291, 18295, 18297, 18299, 18300, 18302, 18303, 18306, 18307, 18313, 18315, 18317, 18319, 18324, 18327, 18328, 18330, 18334, 18336, 18342, 18344, 18345, 18347, 18351, 18352, 18353, 18354, 18355, 18356, 18357, 18358, 18360, 18362, 18363, 18364, 18365, 18367, 18368, 18370, 18372, 18373, 18377, 18378, 18379, 18380, 18381, 18387, 18389, 18390, 18399, 18404, 18404 );
        // le traitement doit etre ici
        
        $etudiants = array();
        $list_e = '';
        for ($i = 0; $i < count($matricules) - 1; ++ $i)
            $list_e = $list_e . $matricules[$i] . ',';
        $list_e = $list_e . $matricules[count($matricules) - 1];
        unset($i);
        /*
         * echo "liste=$list_e<br>";
         * echo "count_list: ".count($list_e)." , ";
         */
        
        $notespartielles = $this->notespartielles_for_list($list_e, $semestre, $departement, $annee);
        $inscrits_a_s = $this->inscrits_a_s_for_list($list_e, $semestre, $departement, $annee);
        $module = $this->module_pour_sem_dep($semestre, $departement);
        $unite = $this->unite_pour_sem_dep($semestre, $departement);
        
        $coefficients = $this->getCoefficients(4);
        $coef_cc = $coefficients['cc'];
        $coef_exam = $coefficients['exam'];
        
        $noteEliminationMatiere = 7;
        $noteEliminationMatiere = $this->get_note_elimination_matiere_courante(4);
        $noteValidationMatiere = 10;
        $noteValidation_module = 10;
        $noteEliminationModule = 9;
        
        $notespartielles_a_s = array();
        $notes_globales = array();
        $planetudesmoduleelem = array();
        $indexation_planetudes = array();
        $etudiant_sem_note_bis = array();
        $capseul_bis = array();
        $etudiant_mod_note_bis = array();
        $nombreelementselimines_bis = array();
        $compense_interne_bis = array();
        $capinterne_bis = array();
        $moduleselimines_bis = array();
        $compense_externe_bis = array();
        $capexterne_bis = array();
        $noncap_bis = array();
        $releve_bis = array();
        $indexation_rel = array();
        $modules_non_valides_bis = array();
        $modules_v_sans_compense_bis = array();
        $module_v_avec_compense_bis = array();
        $modules_valides_bis = array();
        $modules_decision_bis = array();
        $semestre_decision_bis = array();
        
        $indexation = $this->indexation_matricules_notespartielles($notespartielles);
        $indexation_i = $this->indexation_matricules_notespartielles($inscrits_a_s);
        
        echo "<br> count_indexation_notepatl: " . count($indexation) . " , count_indexation_i: " . count($indexation_i) . " <br>";
        
        $notespartielles_a_s = $this->notespartielles_a_s(0, $inscrits_a_s, $notespartielles, $indexation);
        
        $notes_globales = $this->notes_globales_v3(0, $notespartielles_a_s);
        
        $planetudesmoduleelem = $this->planetudesmoduleelem_tous($notes_globales, $module, $coef_cc, $coef_exam);
        
        $indexation_planetudes = $this->indexation_matricules_notespartielles($planetudesmoduleelem);
        $etudiant_sem_note_bis = $this->etudiant_sem_note_bis_tous($planetudesmoduleelem, $indexation_planetudes);
        $capseul_bis = $this->capseul_bis(0, $planetudesmoduleelem, $noteValidationMatiere);
        
        $etudiant_mod_note_bis = $this->etudiant_mod_note_bis_list($planetudesmoduleelem, $indexation_planetudes);
        $nombreelementselimines_bis = $this->nombreelementselimines_bis_tous(0, $planetudesmoduleelem, $noteEliminationMatiere, $indexation_planetudes);
        $compense_interne_bis = $this->compense_interne_bis(0, $etudiant_mod_note_bis, $nombreelementselimines_bis, $noteValidation_module);
        $capinterne_bis = $this->capinterne_bis(0, $planetudesmoduleelem, $compense_interne_bis, $noteValidationMatiere);
        
        // $this->db->insert_batch('capinterne_bis_test', $capinterne_bis);
        
        $moduleselimines_bis = $this->moduleselimines_bis_tous($etudiant_mod_note_bis, $planetudesmoduleelem, $indexation_planetudes, $noteEliminationModule, $noteEliminationMatiere);
        $compense_externe_bis = $this->compense_externe_bis(0, $etudiant_sem_note_bis, $moduleselimines_bis, $noteValidation_module);
        // $capexterne_bis=$this->capexterne_bis(0,$planetudesmoduleelem,$compense_interne_bis,$compense_externe_bis,$noteValidationMatiere,$noteEliminationMatiere);
        $capexterne_bis = $this->capexterne_bis_tous($planetudesmoduleelem, $indexation_planetudes, $compense_interne_bis, $compense_externe_bis, $noteValidationMatiere, $noteEliminationMatiere);
        
        $noncap_bis = $this->noncap_bis(0, $planetudesmoduleelem, $capseul_bis, $capinterne_bis, $capexterne_bis);
        $releve_bis = $this->releve_bis(0, $capseul_bis, $capinterne_bis, $capexterne_bis, $noncap_bis);
        // $releve_bis=$this->releve_bis_tous( $capseul_bis, $capinterne_bis, $capexterne_bis, $noncap_bis,$indexation_planetudes);
        $indexation_rel = $this->indexation_matricules_notespartielles($releve_bis);
        $modules_non_valides_bis = $this->modules_non_valides_bis_tous(0, $releve_bis, $indexation_rel);
        $modules_v_sans_compense_bis = $this->modules_v_sans_compense_bis_tous($releve_bis, $indexation_rel);
        
        // $this->db->insert_batch('modules_v_sans_compense_bis_test_2', $modules_v_sans_compense_bis);
        
        $module_v_avec_compense_bis = $this->module_v_avec_compense_bis_tous($releve_bis, $indexation_rel);
        $modules_valides_bis = $this->modules_valides_bis(0, $modules_v_sans_compense_bis, $module_v_avec_compense_bis);
        $modules_decision_bis = $this->modules_decision_bis_tous($planetudesmoduleelem, $indexation_planetudes, $modules_valides_bis, $modules_non_valides_bis, $noteValidationMatiere);
        $semestre_decision_bis = $this->semestre_decision_bis(0, $modules_decision_bis);
        return $notespartielles_a_s;
    }

    function getSemestreResult_bis_liste($matricules, $semestre_decision_bis, $etudiant_sem_note_bis, $semestre, $annee)
    {
        $data = array();
        $semestreResult_bis_liste = array();
        $cpt = 0;
        $indexation_sem_note_bis = $this->indexation_matricules_notespartielles($etudiant_sem_note_bis);
        for ($i = 0; $i < count($semestre_decision_bis); $i ++) {
            if ($semestre_decision_bis[$i]['semestre'] == $semestre)
                if ($semestre_decision_bis[$i]['annee'] == $annee) {
                    if (! isset($indexation_sem_note_bis[$semestre_decision_bis[$i]['matriculeEtudiant']]['fin']))
                        $indexation_sem_note_bis[$semestre_decision_bis[$i]['matriculeEtudiant']]['fin'] = count($etudiant_sem_note_bis) - 1;
                    for ($j = $indexation_sem_note_bis[$semestre_decision_bis[$i]['matriculeEtudiant']]['debut']; $j < $indexation_sem_note_bis[$semestre_decision_bis[$i]['matriculeEtudiant']]['fin'] + 1; $j ++) {
                        if ($semestre_decision_bis[$i]['matriculeEtudiant'] == $etudiant_sem_note_bis[$j]['matriculeEtudiant'])
                            if ($semestre_decision_bis[$i]['semestre'] == $etudiant_sem_note_bis[$j]['semestre'])
                                if ($semestre_decision_bis[$i]['annee'] == $etudiant_sem_note_bis[$j]['annee']) {
                                    $semestreResult_bis_liste[$cpt]['matriculeEtudiant'] = $semestre_decision_bis[$i]['matriculeEtudiant'];
                                    $semestreResult_bis_liste[$cpt]['decision'] = $semestre_decision_bis[$i]['decision'];
                                    $semestreResult_bis_liste[$cpt]['credits_val'] = $semestre_decision_bis[$i]['credits_val'];
                                    $semestreResult_bis_liste[$cpt]['note'] = $etudiant_sem_note_bis[$j]['note'];
                                    $cpt ++;
                                }
                    }
                }
        }
        //$data1 = $semestreResult_bis_liste;
        if (count($semestreResult_bis_liste) > 0) {
            foreach ($semestreResult_bis_liste as $row) {
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
        return $data;
    }

    public function afficher_pv_new($POST)
    {
        //recuperation des infos envoye par $_POST
        $session = $POST['session'];
        $semestre = $POST['semestre'];
        $annee = $POST['annee'];
        $tri = $POST['tri'];
        $idProgramme = $POST['idProgramme'];
        $departement = $idProgramme;
        //recuperation de la session courante
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];
        $semestreCourant = 3;
        if ($semestre % 2 == 0) {
            $annee = $annee + 1;
            $semestreCourant = 1;
        }
        //selection des etudiants inscrits dans ce departement
        $matricules = $this->scolarite_modele->get_etudiant_anne_semestre($annee, $semestreCourant, $idProgramme, $semestre, $session, $tri);
        
//formatage des matricules ,Ex matricules=array(m1,m2,m3)->String "m1,m2,m3"
        $i = 0;
        $list_e = '';
        for ($i = 0; $i < count($matricules); ++ $i)
            $list_e = $list_e . $matricules[$i] . ',';
        $list_e = $list_e . $matricules[count($matricules) - 1];
        
        $notespartielles = $this->notespartielles_for_list($list_e, $semestre, $departement, $annee);
        $inscrits_a_s = $this->inscrits_a_s_for_list($list_e, $semestre, $departement, $annee);
        $module = $this->module_pour_sem_dep($semestre, $departement);
        $unite = $this->unite_pour_sem_dep($semestre, $departement);
        //coefficients
        $coefficients = $this->getCoefficients(4);
        $coef_cc = $coefficients['cc'];
        $coef_exam = $coefficients['exam'];
        //selection des notes d'elimination
        //$noteEliminationMatiere = 7;
        $noteEliminationMatiere = $this->get_note_elimination_matiere_courante(4);
        $noteEliminationModule =$this->get_note_elimination_module_courante(4);// 9;
        $noteValidationMatiere = 10;
        $noteValidation_module = 10;
        //initialisations
        $notespartielles_a_s = array();
        $notes_globales = array();
        $planetudesmoduleelem = array();
        $indexation_planetudes = array();
        $etudiant_sem_note_bis = array();
        $capseul_bis = array();
        $etudiant_mod_note_bis = array();
        $nombreelementselimines_bis = array();
        $compense_interne_bis = array();
        $capinterne_bis = array();
        $moduleselimines_bis = array();
        $compense_externe_bis = array();
        $capexterne_bis = array();
        $noncap_bis = array();
        $releve_bis = array();
        $indexation_rel = array();
        $modules_non_valides_bis = array();
        $modules_v_sans_compense_bis = array();
        $module_v_avec_compense_bis = array();
        $modules_valides_bis = array();
        $modules_decision_bis = array();
        $semestre_decision_bis = array();
        //calcul et traitement 
       $indexation = $this->indexation_matricules_notespartielles($notespartielles);
       $indexation_i = $this->indexation_matricules_notespartielles($inscrits_a_s);
       $notespartielles_a_s = $this->notespartielles_a_s(0, $inscrits_a_s, $notespartielles, $indexation);//valide//*-*-
     //  return $notespartielles_a_s;//*-*
       $notes_globales = $this->notes_globales_v3(0, $notespartielles_a_s);//petit modif
      // return  $notes_globales;
       $planetudesmoduleelem = $this->planetudesmoduleelem_tous($notes_globales, $module, $coef_cc, $coef_exam);//add//valide
      
       $indexation_planetudes = $this->indexation_matricules_notespartielles($planetudesmoduleelem);//v
      $etudiant_sem_note_bis = $this->etudiant_sem_note_bis_tous($planetudesmoduleelem, $indexation_planetudes);//noteSemestre
        $capseul_bis = $this->capseul_bis(0, $planetudesmoduleelem, $noteValidationMatiere);//valide
         
        $etudiant_mod_note_bis = $this->etudiant_mod_note_bis_list($planetudesmoduleelem, $indexation_planetudes);//moyenModule
        $nombreelementselimines_bis = $this->nombreelementselimines_bis_tous(0, $planetudesmoduleelem, $noteEliminationMatiere, $indexation_planetudes);
        $compense_interne_bis = $this->compense_interne_bis(0, $etudiant_mod_note_bis, $nombreelementselimines_bis, $noteValidation_module);
        $capinterne_bis = $this->capinterne_bis(0, $planetudesmoduleelem, $compense_interne_bis, $noteValidationMatiere);
 
        $moduleselimines_bis = $this->moduleselimines_bis_tous($etudiant_mod_note_bis, $planetudesmoduleelem, $indexation_planetudes, $noteEliminationModule, $noteEliminationMatiere);
        $compense_externe_bis = $this->compense_externe_bis(0, $etudiant_sem_note_bis, $moduleselimines_bis, $noteValidation_module);
        // $capexterne_bis=$this->capexterne_bis(0,$planetudesmoduleelem,$compense_interne_bis,$compense_externe_bis,$noteValidationMatiere,$noteEliminationMatiere);
        $capexterne_bis = $this->capexterne_bis_tous($planetudesmoduleelem, $indexation_planetudes, $compense_interne_bis, $compense_externe_bis, $noteValidationMatiere, $noteEliminationMatiere);
      
        $noncap_bis = $this->noncap_bis(0, $planetudesmoduleelem, $capseul_bis, $capinterne_bis, $capexterne_bis);
        $releve_bis = $this->releve_bis(0, $capseul_bis, $capinterne_bis, $capexterne_bis, $noncap_bis);//valide
     // return $releve_bis;
        $indexation_rel = $this->indexation_matricules_notespartielles($releve_bis);
        $modules_non_valides_bis = $this->modules_non_valides_bis_tous(0, $releve_bis, $indexation_rel);//?
        $modules_v_sans_compense_bis = $this->modules_v_sans_compense_bis_tous($releve_bis, $indexation_rel);
          
        $module_v_avec_compense_bis = $this->module_v_avec_compense_bis_tous($releve_bis, $indexation_rel);
        $modules_valides_bis = $this->modules_valides_bis(0, $modules_v_sans_compense_bis, $module_v_avec_compense_bis);
        $modules_decision_bis = $this->modules_decision_bis_tous($planetudesmoduleelem, $indexation_planetudes, $modules_valides_bis, $modules_non_valides_bis, $noteValidationMatiere);
       
        $semestre_decision_bis = $this->semestre_decision_bis(0, $modules_decision_bis);
 //elle recupere tous les donnees       
        $etudiants = array();
        //--------
        $etudiants = $this->getEtudiants_PV($matricules,$list_e,$annee, $semestreCourant, $idProgramme, $semestre, $session, $tri,$semestre_decision_bis, $etudiant_sem_note_bis,$releve_bis, $notes_globales, $module,$modules_decision_bis, $etudiant_mod_note_bis,$unite);
      //return $etudiants;
        $stat = $this->get_statistique_pv($semestre_decision_bis,$etudiant_sem_note_bis,$annee);
        
        $anneeSc = 100 * ($annee % 100) + $annee % 100 + 1;
        if ($semestre % 2 == 0) {
            $anneeSc = ($annee % 100 - 1) * 100 + $annee % 100;
        }
        $data = array(
            "statistique" => $stat,
            'etudiants' => $etudiants,
            'anneeSc' => $anneeSc,
            'idProgramme' => $idProgramme,
            'semestre' => $semestre,
            'sess' => $session
        );
        return $data;
    }
    
    //----------------------------
    function getModulesResult_bis_liste_h_partie_1( $semestre, $annee, $releve_bis, $notes_globales, $module)
        {
      //return    $notes_globales;
        $indexation_n_g=$this->indexation_matricules_notespartielles($notes_globales);
     // return  $indexation_n_g;
            $resultat = array();
            $cpt = 0;
            foreach ($releve_bis as $key_r => $value_r) {
                //if ($value_r['matriculeEtudiant'] == $matriculeEtudiant)
                    if ($value_r['semestre'] == $semestre)
                        if ($value_r['annee'] == $annee)
                            for($i=$indexation_n_g[$value_r['matriculeEtudiant']]['debut'];$i<$indexation_n_g[$value_r['matriculeEtudiant']]['fin']+1;$i++){
                            //foreach ($notes_globales as $key_n => $value_n) {
                                if ($notes_globales[$i]['matriculeEtudiant'] == $value_r['matriculeEtudiant'])
                                    if ($value_r['semestre'] == $notes_globales[$i]['semestre'])
                                        // if ($value_r['sigle'] == $notes_globales[$i]['sigle']) {
                                        if (strcasecmp($value_r['sigle'], $notes_globales[$i]['sigle']) == 0) {
                                            $an = substr($notes_globales[$i]['annee'], 0, 2);
                                            if ($semestre % 2 == 1) {
                                                $ann = 2000 + (int) $an;   
                                            } else {
                                                $ann = 2001 + (int) $an;
                                            }
                                            if ($value_r['annee'] == $ann)
                                                foreach ($module as $key_m => $value_m) {
                                                    // if ($value_r['sigle'] == $value_m['sigle']) {
                                                
                                                    if (strcasecmp($value_r['sigle'], $value_m['sigle']) == 0) {
                                                        
                                                        $resultat[$cpt]['matriculeEtudiant'] = $value_r['matriculeEtudiant'];
                                                        $resultat[$cpt]['status'] = 0;
                                                        $resultat[$cpt]['annee'] = $value_r['annee'];
                                                        $resultat[$cpt]['idModule'] = $value_r['idModule'];
                                                        $resultat[$cpt]['sigle'] = $value_r['sigle'];
                                                        $resultat[$cpt]['note'] = $value_r['note'];
                                                        $resultat[$cpt]['capit'] = $value_r['capit'];
                                                        $resultat[$cpt]['ects'] = $value_r['ects'];
                                                        $resultat[$cpt]['coefficient'] = $value_m['coefficient'];//qwertyu
                                                        $resultat[$cpt]['titre'] = $value_m['titre'];
                                                        $resultat[$cpt]['notecc'] = $notes_globales[$i]['noteCC'];
                                                        $resultat[$cpt]['noteExam'] = $notes_globales[$i]['noteExam'];
                                                        $resultat[$cpt]['noteRT'] = $notes_globales[$i]['noteRT'];
                                                        $cpt ++;
                                                    }
                                                }
                                        }
                            }
            }
            
            return $resultat;
        }
    function getModulesResult_bis_liste($matricules, $semestre, $annee, $releve_bis, $notes_globales, $module,$modules_decision_bis, $etudiant_mod_note_bis,$unite)
    {
        
        $modulesResult_bis_liste=$this->getModulesResult_bis_liste_h_partie_1( $semestre, $annee, $releve_bis, $notes_globales, $module);
       //return $modulesResult_bis_liste;
        $modules = array();
        $moduleDec_l = $this->getModulesDecision_bis_liste( $semestre, $annee, $modules_decision_bis, $etudiant_mod_note_bis, $unite);
        
        if (count($modulesResult_bis_liste) > 0) {
            $i = 0;
            foreach ($modulesResult_bis_liste as $row) {
                $modules[$i] = NULL;
                $moduleDec = NULL;
                $moduleDec_titre = NULL;
                $moduleDec_decision = NULL;
                $moduleDec_note = NULL;
                $moduleDec_ects = NULL;
                $moduleDec_coefficient = NULL;
                if ($modules[$i] == NULL) {
                    $modules[$i] = array();
                    for ($j = 0; $j < count($moduleDec_l['idModule']); $j ++) {
                        if ($moduleDec_l['idModule'][$j] == $row['idModule'] && $moduleDec_l['matriculeEtudiant'][$j] == $row['matriculeEtudiant']) {
                            $moduleDec_titre = $moduleDec_l['titre'][$j];
                            $moduleDec_decision = $moduleDec_l['decision'][$j];
                            $moduleDec_note = $moduleDec_l['note'][$j];
                            $moduleDec_ects = $moduleDec_l['ects'][$j];
                            $moduleDec_coefficient = $moduleDec_l['coefficient'][$j];
                            if ($moduleDec_l != NULL) {
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['titre'] = $moduleDec_titre;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['decision'] = $moduleDec_decision;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['nm'] = $moduleDec_note;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['ects'] = $moduleDec_ects;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['coefficient'] = $moduleDec_coefficient;// add by MedBakar 09-03-2020
                            }
                        }
                    }
                    $modules[$row['matriculeEtudiant']][$row['idModule']]['nb'] = 0;
                } else if (! array_key_exists($row['idModule'], $modules)) {
                    $j = 0;
                    for ($j = 0; $j < count($moduleDec_l['idModule']); $j ++) {
                        if ($moduleDec_l['idModule'][$j] == $row['idModule'] && $moduleDec_l['matriculeEtudiant'][$j] == $row['matriculeEtudiant']) {
                            $moduleDec_titre = $moduleDec_l['titre'][$j];
                            $moduleDec_decision = $moduleDec_l['decision'][$j];
                            $moduleDec_note = $moduleDec_l['note'][$j];
                            $moduleDec_ects = $moduleDec_l['ects'][$j];
                            $moduleDec_coefficient= $moduleDec_l['coefficient'][$j];
                            if ($moduleDec_l != NULL) {
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['titre'] = $moduleDec_titre;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['decision'] = $moduleDec_decision;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['nm'] = $moduleDec_note;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['ects'] = $moduleDec_ects;
                                $modules[$row['matriculeEtudiant']][$row['idModule']]['coefficient'] = $moduleDec_coefficient;//add by MedBakar 09-03-2020
                            }
                        }
                    }
                    $modules[$row['matriculeEtudiant']][$row['idModule']]['nb'] = 0;
                }
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['status'] = $row['status'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['nfe'] = $row['note'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['capit'] = $row['capit'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['ncc'] = $row['notecc'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['nsn'] = $row['noteExam'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['nsr'] = $row['noteRT'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['ects'] = $row['ects'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['coefficient'] = $row['coefficient'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['elements'][$row['sigle']]['titre'] = $row['titre'];
                $modules[$row['matriculeEtudiant']][$row['idModule']]['nb'] ++;
                $i ++;
            }
        }
        return $modules;
    }
    function getModulesDecision_bis_liste( $semestre, $annee , $modules_decision_bis, $etudiant_mod_note_bis, $unite)
    {
        $resultat = array();
        $cpt=0;
        $indexation_etud_mod_note=$this->indexation_matricules_notespartielles($etudiant_mod_note_bis);
        foreach ($modules_decision_bis as $key_m => $value_m) {
                        if ($value_m['semestre'] == $semestre)
                            if ($value_m['annee'] == $annee)
                                for ($i=$indexation_etud_mod_note[$value_m['matriculeEtudiant']]['debut'];$i<$indexation_etud_mod_note[$value_m['matriculeEtudiant']]['fin']+1;$i++){
                                    if ($etudiant_mod_note_bis[$i]['matriculeEtudiant'] == $value_m['matriculeEtudiant'])
                                            if (strcasecmp($value_m['idModule'], $etudiant_mod_note_bis[$i]['idModule']) == 0)
                                                if ($value_m['annee'] == $etudiant_mod_note_bis[$i]['annee'])
                                                    if ($value_m['semestre'] == $etudiant_mod_note_bis[$i]['semestre'])
                                                        foreach ($unite as $key_u => $value_u) {
                                                            if (strcasecmp($value_m['idModule'], $value_u['sigle']) == 0) {
                                                                $resultat[$cpt]['matriculeEtudiant'] = $value_m['matriculeEtudiant'];
                                                                $resultat[$cpt]['idModule'] = $value_m['idModule'];
                                                                $resultat[$cpt]['decision'] = $value_m['decision'];
                                                                $resultat[$cpt]['ects'] = $value_m['credits_val'];
                                                                $resultat[$cpt]['coefficient'] = $value_u['coefficient'];//add by MedBakar 09-03-2020
                                                                $resultat[$cpt]['titre'] = $value_u['titre'];
                                                                $resultat[$cpt]['note'] = $etudiant_mod_note_bis[$i]['note'];//**
                                                                $cpt++;
                                                            }
                                                        }
                                }
        }
        $moduleDec = array();
        if (count($resultat) > 0) {
            $i = 0;
            foreach ($resultat as $row) {
                $moduleDec['idModule'][] = $row['idModule'];
                $moduleDec['matriculeEtudiant'][] = $row['matriculeEtudiant'];
                $moduleDec['titre'][] = $row['titre'];
                $moduleDec['decision'][] = $row['decision'];
                $moduleDec['ects'][] = $row['ects'];
                $moduleDec['coefficient'][] = $row['coefficient'];
                $moduleDec['note'][] = $row['note'];//***
            }
            $i ++;
        }
        return $moduleDec;
    }
    
    function get_statistique_pv($semestre_decision_bis,$etudiant_sem_note_bis,$annee)
    {
        $info = array(
            "nb_admis" => 0,
            "nb_compense" => 0,
            "nb_ajourne" => 0,
            "nb_reussite" => 0,
            "nb_totale" => 0,
            "taux_reussite" => 0,
            "taux_abondon" => 0,
            "nb_mg_zero" => 0,
            "taux_nb_mg_zero" => 0
        );
        if(!empty($etudiant_sem_note_bis)){
        foreach ($semestre_decision_bis as $i => $line){
            if($line['annee']==$annee){
                if($line['decision']=='Admis(e)')
                    $info['nb_admis']++;
                elseif($line['decision']=='Compense')
                    $info['nb_compense']++;
                elseif($line['decision']=='Ajourné(e)')
                    $info['nb_ajourne']++;
            }
        }
        foreach ($etudiant_sem_note_bis as $i => $line){
            if($line['annee']==$annee){
                if($line['note']==0){
                    $info["taux_nb_mg_zero"]++;
                }
            }
        }
        $info['nb_reussite'] = $info['nb_admis'] + $info['nb_compense'];
        $info['nb_totale'] = $info['nb_reussite'] + $info['nb_ajourne'];
        $info['taux_reussite'] = $info['nb_reussite'] / $info['nb_totale'] * 100;
        $info['taux_abondon'] = $info['nb_ajourne'] / $info['nb_totale'] * 100;
        $info['taux_nb_mg_zero'] = $info['nb_mg_zero'] / $info['nb_totale'] * 100;
        }
        return $info;
    }
    
    public function getEtudiants_PV($matricules,$list_e,$annee = 2015, $semestre = 3, $idProgramme, $semEtude, $session, $tri,$semestre_decision_bis, $etudiant_sem_note_bis,$releve_bis, $notes_globales, $module,$modules_decision_bis, $etudiant_mod_note_bis,$unite)
    {
        
        $etudiants = NULL;
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $annee_cour = $sessionCourante['annee'][0];
        if (isset($matricules)) {
            $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant_liste($list_e);
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
            
            $SemestreResult_bis_liste = $this->getSemestreResult_bis_liste($list_e, $semestre_decision_bis, $etudiant_sem_note_bis, $semEtude, $annee);
            
            $semRes = $SemestreResult_bis_liste; // $this->scolarite_modele->getSemestreResult_bis_liste($list_e, $semEtude, $annee);
        
            $moduleRes = $this->getModulesResult_bis_liste($list_e, $semEtude, $annee, $releve_bis, $notes_globales, $module,$modules_decision_bis, $etudiant_mod_note_bis,$unite);
         // return $moduleRes;
          for ($j = 0; $j < count($matricules); ++ $j) {
                $etudiants[$matricules[$j]]['numSem'] = $semEtude;
                $etudiants[$matricules[$j]]['idProgramme'] = $idProgramme;
            }
            if (isset($pvCC['matriculeEtudiant'])) {
                for ($m = 0; $m < count($pvCC['matriculeEtudiant']); ++ $m) {
                    $etudiants[$pvCC['matriculeEtudiant'][$m]]['pvCC']['sigle'][] = $pvCC['sigle'][$m];
                    $etudiants[$pvCC['matriculeEtudiant'][$m]]['pvCC']['matriculeEtudiant'][] = $pvCC['matriculeEtudiant'][$m];
                }
            }
            if (isset($pvSN['matriculeEtudiant'])) {
                for ($p = 0; $p < count($pvSN['matriculeEtudiant']); ++ $p) {
                    $etudiants[$pvSN['matriculeEtudiant'][$p]]['pvSN']['sigle'][] = $pvSN['sigle'][$p];
                    $etudiants[$pvSN['matriculeEtudiant'][$p]]['pvSN']['matriculeEtudiant'][] = $pvSN['matriculeEtudiant'][$p];
                }
            }
            if (isset($pvSR['matriculeEtudiant'])) {
                for ($p = 0; $p < count($pvSR['matriculeEtudiant']); ++ $p) {
                    $etudiants[$pvSR['matriculeEtudiant'][$p]]['pvSR']['sigle'][] = $pvSR['sigle'][$p];
                    $etudiants[$pvSR['matriculeEtudiant'][$p]]['pvSR']['matriculeEtudiant'][] = $pvSR['matriculeEtudiant'][$p];
                }
            }
            if (isset($abCC['matriculeEtudiant'])) {
                for ($m = 0; $m < count($abCC['matriculeEtudiant']); ++ $m) {
                    $etudiants[$abCC['matriculeEtudiant'][$m]]['abCC']['sigle'][] = $abCC['sigle'][$m];
                    $etudiants[$abCC['matriculeEtudiant'][$m]]['abCC']['matriculeEtudiant'][] = $abCC['matriculeEtudiant'][$m];
                }
            }
            if (isset($abSN['matriculeEtudiant'])) {
                for ($p = 0; $p < count($abSN['matriculeEtudiant']); ++ $p) {
                    $etudiants[$abSN['matriculeEtudiant'][$p]]['abSN']['sigle'][] = $abSN['sigle'][$p];
                    $etudiants[$abSN['matriculeEtudiant'][$p]]['abSN']['matriculeEtudiant'][] = $abSN['matriculeEtudiant'][$p];
                }
            }
            if (isset($abSR['matriculeEtudiant'])) {
                for ($p = 0; $p < count($abSR['matriculeEtudiant']); ++ $p) {
                    $etudiants[$abSR['matriculeEtudiant'][$p]]['abSR']['sigle'][] = $abSR['sigle'][$p];
                    $etudiants[$abSR['matriculeEtudiant'][$p]]['abSR']['matriculeEtudiant'][] = $abSR['matriculeEtudiant'][$p];
                }
            }
            if (isset($zeroCC['matriculeEtudiant'])) {
                for ($m = 0; $m < count($zeroCC['matriculeEtudiant']); ++ $m) {
                    $etudiants[$zeroCC['matriculeEtudiant'][$m]]['zeroCC']['sigle'][] = $zeroCC['sigle'][$m];
                    $etudiants[$zeroCC['matriculeEtudiant'][$m]]['zeroCC']['matriculeEtudiant'][] = $zeroCC['matriculeEtudiant'][$m];
                }
            }
            if (isset($zeroSN['matriculeEtudiant'])) {
                for ($m = 0; $m < count($zeroSN['matriculeEtudiant']); ++ $m) {
                    $etudiants[$zeroSN['matriculeEtudiant'][$m]]['zeroSN']['sigle'][] = $zeroSN['sigle'][$m];
                    $etudiants[$zeroSN['matriculeEtudiant'][$m]]['zeroSN']['matriculeEtudiant'][] = $zeroSN['matriculeEtudiant'][$m];
                }
            }
            if (isset($zeroSR['matriculeEtudiant'])) {
                for ($m = 0; $m < count($zeroSR['matriculeEtudiant']); ++ $m) {
                    $etudiants[$zeroSR['matriculeEtudiant'][$m]]['zeroSR']['sigle'][] = $zeroSR['sigle'][$m];
                    $etudiants[$zeroSR['matriculeEtudiant'][$m]]['zeroSR']['matriculeEtudiant'][] = $zeroSR['matriculeEtudiant'][$m];
                }
            }
            if (isset($element_ratt_en_gras['matriculeEtudiant'])) {
                for ($m = 0; $m < count($element_ratt_en_gras['matriculeEtudiant']); ++ $m) {
                    $etudiants[$element_ratt_en_gras['matriculeEtudiant'][$m]]['element_ratt_en_gras']['sigle'][] = $element_ratt_en_gras['sigle'][$m];
                    $etudiants[$element_ratt_en_gras['matriculeEtudiant'][$m]]['element_ratt_en_gras']['matriculeEtudiant'][] = $element_ratt_en_gras['matriculeEtudiant'][$m];
                }
            }
            
            for ($k = 0; $k < count($anonymat['matriculeEtudiant']); $k ++) {
                $etudiants[$anonymat['matriculeEtudiant'][$k]]['anonymat'] = $anonymat['code'][$k];
            }
            for ($i = 0; $i < count($infoEtudiant['matriculeEtudiant']); $i ++) {
                $etudiants[$infoEtudiant['matriculeEtudiant'][$i]]['info']['matriculeEtudiant'] = $infoEtudiant['matriculeEtudiant'][$i];
                $etudiants[$infoEtudiant['matriculeEtudiant'][$i]]['info']['nom'] = $infoEtudiant['nom'][$i];
                $etudiants[$infoEtudiant['matriculeEtudiant'][$i]]['info']['prenom'] = $infoEtudiant['prenom'][$i];
                $etudiants[$infoEtudiant['matriculeEtudiant'][$i]]['info']['programme'] = $infoEtudiant['programme'][$i];
            }
            for ($i = 0; $i < count($semRes['matriculeEtudiant']); $i ++) {
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['matriculeEtudiant'] = $semRes['matriculeEtudiant'][$i];
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['semestre'] = $semRes['semestre'][$i];
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['ects'] = $semRes['ects'][$i];
                //$etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['coefficient'] = $semRes['coefficient'][$i];//add
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['decision'] = $semRes['decision'][$i];
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['validation'] = $semRes['validation'][$i];
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['inscrit'] = $semRes['inscrit'][$i];
                $etudiants[$semRes['matriculeEtudiant'][$i]]['semestre']['note'] = $semRes['note'][$i];
            }
            foreach ($moduleRes as $matriculeEtudiant => $modules) {
                foreach ($modules as $idModule => $module) {
                    $etudiants[$matriculeEtudiant]['modules'][$idModule]['titre'] = $module['titre'];
                    $etudiants[$matriculeEtudiant]['modules'][$idModule]['decision'] = $module['decision'];
                    $etudiants[$matriculeEtudiant]['modules'][$idModule]['nm'] = $module['nm'];
                    $etudiants[$matriculeEtudiant]['modules'][$idModule]['ects'] = $module['ects'];
                    $etudiants[$matriculeEtudiant]['modules'][$idModule]['coefficient'] = $module['coefficient'];//add
                    $etudiants[$matriculeEtudiant]['modules'][$idModule]['nb'] = $module['nb'];
                    foreach ($module['elements'] as $sigle => $modul) {
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['titre'] = $modul['titre'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['nfe'] = $modul['nfe'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['status'] = $modul['status'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['capit'] = $modul['capit'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['ncc'] = $modul['ncc'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['nsn'] = $modul['nsn'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['nsr'] = $modul['nsr'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['ects'] = $modul['ects'];
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['coefficient'] = $modul['coefficient'];//add
                        $etudiants[$matriculeEtudiant]['modules'][$idModule]['elements'][$sigle]['titre'] = $modul['titre'];
                    }
                }
            }
            $limite = count($matricules) / 2;
            if (count($matricules) % 2 == 1)
                $limite = (count($matricules) - 1) / 2;
        }
        return $etudiants;
    }
    
    
    
    
    //fonction retourne la note eliminatoire des modules --add by MedBakar 
    function get_note_elimination_module_courante($cycle)
    {
        $this->db->select('note as note');
        $this->db->where(array(
            'date_fin' => null,
            'cycle' => $cycle
        ));
        $requete = $this->db->get('note_elimination_module');
        $resultat = NULL;
        if ($requete->num_rows() > 0) {
            $resultat = $requete->result_array()[0];
            $resultat = $resultat['note'];
        }
        return $resultat;
    }

    
    //-------------debut retourne list des ratrapeur par MedBakar le 16-03-2020----
     public function get_list_ratrappeur($POST)
    {
        //recuperation des infos envoye par $_POST
        $session =1 ;
        $semestre = $POST['semestre'];
        $annee = $POST['annee'];
        $tri = 1;
        $idProgramme = $POST['idProgramme'];
        $departement = $idProgramme;
        //recuperation de la session courante
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];
        $semestreCourant = 3;
        if ($semestre % 2 == 0) {
            $annee = $annee + 1;
            $semestreCourant = 1;
        }
        //selection des etudiants inscrits dans ce departement
        $matricules = $this->scolarite_modele->get_etudiant_anne_semestre($annee, $semestreCourant, $idProgramme, $semestre, $session, $tri);
//        return $matricules;
//formatage des matricules ,Ex matricules=array(m1,m2,m3)->String "m1,m2,m3"
        $i = 0;
        $list_e = '';
        for ($i = 0; $i < count($matricules); ++ $i)
            $list_e = $list_e . $matricules[$i] . ',';
        $list_e = $list_e . $matricules[count($matricules) - 1];
        
        $notespartielles = $this->notespartielles_for_list($list_e, $semestre, $departement, $annee);
        $inscrits_a_s = $this->inscrits_a_s_for_list($list_e, $semestre, $departement, $annee);
        $module = $this->module_pour_sem_dep($semestre, $departement);
        $unite = $this->unite_pour_sem_dep($semestre, $departement);
        //coefficients
        $coefficients = $this->getCoefficients(4);
        $coef_cc = $coefficients['cc'];
        $coef_exam = $coefficients['exam'];
        //selection des notes d'elimination
        //$noteEliminationMatiere = 7;
        $noteEliminationMatiere = $this->get_note_elimination_matiere_courante(4);
        $noteEliminationModule =$this->get_note_elimination_module_courante(4);// 9;
        $noteValidationMatiere = 10;
        $noteValidation_module = 10;
        //initialisations
        $notespartielles_a_s = array();
        $notes_globales = array();
        $planetudesmoduleelem = array();
        $indexation_planetudes = array();
        $etudiant_sem_note_bis = array();
        $capseul_bis = array();
        $etudiant_mod_note_bis = array();
        $nombreelementselimines_bis = array();
        $compense_interne_bis = array();
        $capinterne_bis = array();
        $moduleselimines_bis = array();
        $compense_externe_bis = array();
        $capexterne_bis = array();
        $noncap_bis = array();
        $releve_bis = array();
        $indexation_rel = array();
        $modules_non_valides_bis = array();
        $modules_v_sans_compense_bis = array();
        $module_v_avec_compense_bis = array();
        $modules_valides_bis = array();
        $modules_decision_bis = array();
        $semestre_decision_bis = array();
        //calcul et traitement 
        $indexation = $this->indexation_matricules_notespartielles($notespartielles);
       $indexation_i = $this->indexation_matricules_notespartielles($inscrits_a_s);
       $notespartielles_a_s = $this->notespartielles_a_s(0, $inscrits_a_s, $notespartielles, $indexation);//valide//*-*-
      // return $notespartielles;//*-*
       $notes_globales = $this->notes_globales_v3(0, $notespartielles_a_s);
//      echo"<br>-------------------<br>";
       //return  $notes_globales;
       $planetudesmoduleelem = $this->planetudesmoduleelem_tous($notes_globales, $module, $coef_cc, $coef_exam);//add//valide
      
       $indexation_planetudes = $this->indexation_matricules_notespartielles($planetudesmoduleelem);//v
      $etudiant_sem_note_bis = $this->etudiant_sem_note_bis_tous($planetudesmoduleelem, $indexation_planetudes);//noteSemestre
        $capseul_bis = $this->capseul_bis(0, $planetudesmoduleelem, $noteValidationMatiere);//valide
         
        $etudiant_mod_note_bis = $this->etudiant_mod_note_bis_list($planetudesmoduleelem, $indexation_planetudes);//moyenModule
        $nombreelementselimines_bis = $this->nombreelementselimines_bis_tous(0, $planetudesmoduleelem, $noteEliminationMatiere, $indexation_planetudes);
        $compense_interne_bis = $this->compense_interne_bis(0, $etudiant_mod_note_bis, $nombreelementselimines_bis, $noteValidation_module);
        $capinterne_bis = $this->capinterne_bis(0, $planetudesmoduleelem, $compense_interne_bis, $noteValidationMatiere);
 
        $moduleselimines_bis = $this->moduleselimines_bis_tous($etudiant_mod_note_bis, $planetudesmoduleelem, $indexation_planetudes, $noteEliminationModule, $noteEliminationMatiere);
        $compense_externe_bis = $this->compense_externe_bis(0, $etudiant_sem_note_bis, $moduleselimines_bis, $noteValidation_module);
        // $capexterne_bis=$this->capexterne_bis(0,$planetudesmoduleelem,$compense_interne_bis,$compense_externe_bis,$noteValidationMatiere,$noteEliminationMatiere);
        $capexterne_bis = $this->capexterne_bis_tous($planetudesmoduleelem, $indexation_planetudes, $compense_interne_bis, $compense_externe_bis, $noteValidationMatiere, $noteEliminationMatiere);
      
        $noncap_bis = $this->noncap_bis(0, $planetudesmoduleelem, $capseul_bis, $capinterne_bis, $capexterne_bis);
        $releve_bis = $this->releve_bis(0, $capseul_bis, $capinterne_bis, $capexterne_bis, $noncap_bis);//valide
        return $releve_bis;     
     }
     
      function get_year($matricule,$semestre){// add by MedBakar 04-05-2020
        $year=-1;
        if(!empty($matricule) && !empty($semestre)){
        $result=$this->db->query("SELECT Max(n.annee) year FROM `notespartielles` n,module m, unite u where n.sigle=m.sigle and m.sigleUnite=u.sigle and n.matriculeEtudiant=$matricule and u.semestre=$semestre");
        if($result->num_rows() >0){
            $result=$result->result_array()[0];
            $year=$result['year'];
        }
        }
        return $year;
    }
     
    function get_correspondance($query){ // add by MedBAkar 2020
        $correspondance=array();
        //$i=0;
        $result=$this->db->query($query);
        if($result->num_rows() >0){
            foreach($result->result_array() as $row){
                $correspondance[$row['anc_sigle']]=$row['nouv_sigle'];
                if(!empty($row['titre']))
                $correspondance['titre'][$row['nouv_sigle']]=$row['titre'];
                if(!empty($row['sigleUnite']))
                $correspondance['sigleUnite'][$row['anc_sigle']]=$row['sigleUnite'];
              //  $i++;
            }
        }
        //print_r($correspondance);
        return $correspondance;
    }
     
    //add by MedBakar 15-05-2020
    //fonction permet d'ordonner la table des correspondances en associant chaque element a sa derniere correspondance
    //exemple:  $correspondance=array('a'=>'b','c'=>'d','b'=>'c','g'=>'h','h'=>'a');
    //result:  $correspondance=array('a'=>'d','c'=>'d','b'=>'d','g'=>'d','h'=>'d');
    function last_maquete($correspondance){
//        $correspondance=array('a'=>'b','c'=>'d','b'=>'c','g'=>'h','h'=>'a');
//        print_r($correspondance);
        //debut traitementget_dec
        foreach($correspondance as $anc=>$nouv){
            if(strpos($anc,'itre')==false)
                if($anc!=$nouv)
                    if(!empty($correspondance[$nouv]))
                        while(!empty($correspondance[$nouv])){
                           $correspondance[$anc]=$correspondance[$nouv];
                           $nouv=$correspondance[$nouv];
                        }
        }
//        echo"<br>";
        /*
         for($i=0;$i<count($correspondance);$i++){
            for($j=0;$j<count($correspondance);$j++){
                if($j!=$i)
                 if($correspondance[$i]['nouv']==$correspondance[$j]['anc']){
                    $correspondance[$i]['nouv']=$correspondance[$j]['nouv'];
                    $j=-1;
                }
            }
        }
         */
//          echo'<br>';
//            print_r($correspondance);
        $data=$correspondance;
//        $query="";
//          foreach($correspondance as $anc=>$nouv){
//              //for($i=0;$i<count($correspondance);$i++){
//            //  $data[$correspondance[$anc]]=$nouv;
//              
//              $query.= "'".$nouv."' , ";
//              }
//            $result=  $this->db->query("SELECT sigle,titre FROM module WHERE sigle in(".substr($query,0,-2).")");
//            print($this->db->last_query());  
//            if($result->num_rows()>0){
//                  foreach($result->result_array() as $row){
//                      $data['titre'][$row['sigle']]=$row['titre'];
//                  }
//              }
        //  print_r($data);
//          return $data;
        return $correspondance;
        
    }
    
     
}