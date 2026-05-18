<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Scolarite extends CI_Controller {


	var $passwordChef = '12';
        var $passwordCheikh = '301266';
// modifier $passwordChef = '' pour qu'aucun mot de passe ne soit demandé
    // Bien que ce ne soit pas une obligation, n'accepter que des lettres non accentuées et des chiffres, pas d'espace non plus.
	
	function __construct() { 
        parent::__construct();
        if (!in_array('scolarite', $this->session->userdata('profil')))
            redirect();
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->load->library('bulletin');
        $this->load->library('Classes/PHPExcel');
        $this->load->model('scolarite_modele');
        $this->load->model('professeur_modele');
        /*
         * AZ chargement de bulletin_modele pour le traitement des notes
         * */
        $this->load->model('bulletin_modele');
        /*AZ fin de chargement*/
        $this->load->model('connexion_modele');
        $this->load->model('search_modele');
        $this->load->helper('date');
        $this->load->library('email');
         $this->load->library("Pdf");
        $this->clear_output();
        $this->lang->load('iup','french');
    }

    function clear_output() {
        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
        $this->output->set_header("Cache-Control: post-check=0, pre-check=0", false);
        $this->output->set_header("Pragma: no-cache");
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

    function index($lang='') {
        $this->lang->load('iup',$lang==''? 'french':$lang);
        //menu
        /*
        $data['menu_etudiant']=$this->lang->line('menu_etudiant');
        $data['menu_Absence']=$this->lang->line('menu_Absence');
        $data['menu_Employe']=$this->lang->line('menu_Employe');
        $data['menu_Element']=$this->lang->line('menu_Element');
        $data['menu_Module']=$this->lang->line('menu_Module');
        $data['menu_Groupe']=$this->lang->line('menu_Groupe');
        $data['menu_NotesBulletins']=$this->lang->line('menu_NotesBulletins');
        $data['menu_Etat']=$this->lang->line('menu_Etat');
        $data['menu_Parametre']=$this->lang->line('menu_Parametre');
        $data['menu_statistique']=$this->lang->line('menu_statistique');
        $data['menu_langue']=$this->lang->line('menu_langue');
       
        
        //sous menu etudiant
        $data['sous_menu_etudiant_cree']=$this->lang->line('sous_menu_etudiant_cree');
        $data['sous_menu_etudiant_info']=$this->lang->line('sous_menu_etudiant_info');
        $data['sous_menu_etudiant_Modifier']=$this->lang->line('sous_menu_etudiant_Modifier');
        $data['sous_menu_etudiant_inscr_M']=$this->lang->line('sous_menu_etudiant_inscr_M');
        $data['sous_menu_etudiant_deinscr_M']=$this->lang->line('sous_menu_etudiant_deinscr_M');
        $data['sous_menu_etudiant_G_liste']=$this->lang->line('sous_menu_etudiant_G_liste');
        $data['sous_menu_etudiant_G_fiche']=$this->lang->line('menu_NotesBulletins');
        $data['sous_menu_etudiant_G_fiche']=$this->lang->line('sous_menu_etudiant_G_fiche');
        $data['sous_menu_etudiant_Att_ins']=$this->lang->line('sous_menu_etudiant_Att_ins');
        $data['sous_menu_etudiant_Att_DIPL']=$this->lang->line('sous_menu_etudiant_Att_DIPL');
        
        //sous menu  Absences
        $data['sous_menu_Absences_entrer']=$this->lang->line('sous_menu_Absences_entrer');
        $data['sous_menu_Absences_RM']=$this->lang->line('sous_menu_Absences_RM');
        $data['sous_menu_Absences_rapportE']=$this->lang->line('sous_menu_Absences_rapportE');
        $data['sous_menu_Absences_rapportG']=$this->lang->line('sous_menu_Absences_rapportG');
        $data['sous_menu_Absences_rapportGL']=$this->lang->line('sous_menu_Absences_rapportGL');
        $data['sous_menu_Absences_M']=$this->lang->line('sous_menu_Absences_M');
        $data['Accueil_Scolarité']=$this->lang->line('Accueil_Scolarité');
        
        //sous menu  employes
        $data['sous_menu_employes_liste']=$this->lang->line('sous_menu_employes_liste');
        $data['sous_menu_employes_E']=$this->lang->line('sous_menu_employes_E');
        $data['sous_menu_employes_H']=$this->lang->line('sous_menu_employes_H');
        $data['sous_menu_employes_R']=$this->lang->line('sous_menu_employes_R');
         
        //sous menu  employes
        $data['sous_menu_element_c']=$this->lang->line('sous_menu_element_c');
        $data['sous_menu_element_ch']=$this->lang->line('sous_menu_element_ch');
        $data['sous_menu_element_del']=$this->lang->line('sous_menu_element_del');
        
        
        //sous menu  groupe
        $data['sous_menu_groupe_insp']=$this->lang->line('sous_menu_groupe_insp');
        $data['sous_menu_groupe_cons_mod_E']=$this->lang->line('sous_menu_groupe_cons_mod_E');
        $data['sous_menu_groupe_AH']=$this->lang->line('sous_menu_groupe_AH');
        $data['sous_menu_groupe_EM']=$this->lang->line('sous_menu_groupe_EM');
        
        //sous menu  NOTES
        $data['sous_menu_notes_fiche']=$this->lang->line('sous_menu_notes_fiche');
        $data['sous_menu_notes_CA']=$this->lang->line('sous_menu_notes_CA');
        $data['sous_menu_notes_NM']=$this->lang->line('sous_menu_notes_NM');
        
         //sous menu  groupe
        $data['sous_menu_groupe_insp']=$this->lang->line('sous_menu_groupe_insp');
        $data['sous_menu_groupe_cons_mod_E']=$this->lang->line('sous_menu_groupe_cons_mod_E');
        $data['sous_menu_groupe_AH']=$this->lang->line('sous_menu_groupe_AH');
        $data['sous_menu_groupe_EM']=$this->lang->line('sous_menu_groupe_EM');
         * 
         */
        $this->load->view('scolarite/index');
    }

	
	function verifier_chef_scolarite($pass)
	{
		if($pass == $this->passwordChef)
		return true;
		else
		return false;
	}

    function generer_cote_view() 
    {
	if (strlen($this->passwordChef) ==0) {	// si pas de mot de passe : on ne demande rien
		$this->clear_output();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['sigle'] = '';
        $data['titre'] = 'Veuillez choisir l\'année et le semestre';
        $data['action'] = 'generer_cote';
        $this->load->view("scolarite/choix_cours_cote", $data);
    }	
	else
	{	// mot de passe demandé, c'est plus compliqué
	        $this->clear_output();
		$this->form_validation->set_rules('pass', 'mot de passe', 'required');
		if (isset($_POST['pass']))
			$this->form_validation->set_rules('pass', 'mot de passe',
			'callback_verifier_chef_scolarite['.$_POST['pass'].']');
		if ($this->form_validation->run()) 
		{
			$data['courante'] = $this->scolarite_modele->get_session_courante();
			$data['annee'] = $this->scolarite_modele->recuperer_annee();
			$data['sigle'] = '';
			$data['titre'] = 'Veuillez choisir l\'année et le semestre';
			$data['action'] = 'generer_cote';
			$this->load->view("scolarite/choix_cours_cote", $data);
		}
		else
		{
			$data['typeInterface'] = 'generer_cote_view';
			$data['titre'] = 'L\'accès à ce sous-menu est réservé.<br> Entrez le mot de passe.';
            $this->load->view("scolarite/chef_scolarite_pass", $data);
		}
        
    }
	}
	
    function choix_cours() 
    {
        $data = NULL;
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['sigle'] = '';
        $this->load->view("scolarite/choix_cours", $data);
    }

    function rattrapage() {
        $this->clear_output();
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['titre'] = 'Veuillez choisir l\'année et le semestre ';
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['rattrapage'] = 'rattrapage';
        $this->load->view("scolarite/choix_cours_cote", $data);
    }

    function choix_cours_cote() 
	{
		if (strlen($this->passwordChef) ==0) {	// si pas de mot de passe : on ne demande rien
		$this->clear_output();
        $data = NULL;
        $data['titre'] = 'Veuillez choisir l\'année et le semestre ';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['sigle'] = '';
        $this->load->view("scolarite/choix_cours_cote", $data);
    }
	else	// on demande le mot de passe
	{
		$data = NULL;
        $this->clear_output();
		$this->form_validation->set_rules('pass', 'mot de passe', 'required');
		if (isset($_POST['pass']))
			$this->form_validation->set_rules('pass', 'mot de passe',
			'callback_verifier_chef_scolarite['.$_POST['pass'].']');
		if ($this->form_validation->run()) 
		{
			
			$data['titre'] = 'Choix de l\'année et du semestre du module ';
			$data['annee'] = $this->scolarite_modele->recuperer_annee();
			$data['courante'] = $this->scolarite_modele->get_session_courante();
			$data['sigle'] = '';
			$this->load->view("scolarite/choix_cours_cote", $data);
		}
		else
		{
			$data['typeInterface'] = 'choix_cours_cote';
			$data['titre'] = 'L\'accès à ce sous-menu est réservé.<br> Entrez le mot de passe.';
            $this->load->view("scolarite/chef_scolarite_pass", $data);
		}
    }
	}

    function desinscrire_etudiant_module() 
    {
        $this->clear_output();
        $data = NULL;
        $data['titre'] = 'Désinscrire des étudiants d\'un élément';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['inscrire'] = 'desinscrire';
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view("scolarite/choisir_annee_session", $data);
    }

    /*
     * recherche etudiant 
     */

    function modifier_info_personnelle_etudiant() 
    {
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'modifier_info_etudiant';
        $id_action = 'matriculeEtudiant';

        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);
        $titre = 'Modifier les informations d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    /*
     * modifie info personnelles de l'étudiant dans la BD  par matricule
     */

    function modifier_info_etudiant($matricule) 
    {
         
        $data = $this->scolarite_modele->get_informations_etudiant($matricule);
        $data['monProgramme'] = $this->scolarite_modele->get_programme_name($data['programme']);

        $data['programmeListe'] = $this->scolarite_modele->get_programme();
        $data['gradeListe'] = $this->scolarite_modele->get_grade();
        $data['etablissements'] = $this->scolarite_modele->get_etablissement();
        $this->load->view('scolarite/modifier_profil_etudiant', $data);
    }
    

    /*
     * fonction pour modifier les informations personnelles de l'étudiant
     */

    function modifier_information_perso_etudiant() 
    {
       
        $this->form_validation->set_rules('email', 'E-mail', 'valid_email');
        $phoneValid = $this->valider_phone($_POST['telephone1']);
        $phoneValid2 = $this->valider_phone($_POST['telephone2']);
        $phoneParentsValid = $this->valider_phone($_POST['telephoneParents']);
        $phoneUrgenceValid = $this->valider_phone($_POST['telephoneUrgence']);

        $dateNaissanceIsValid = $this->valider_date_naissance($_POST['year'], $_POST['month'], $_POST['day']);
        $getDateNaissanceBD = $this->scolarite_modele->get_informations_etudiant($_POST['matricule']);
        $dateNais = explode("-", $getDateNaissanceBD['dateNaissance']);
        if ($dateNaissanceIsValid != 'valide')
        {
            $_POST['year'] = $dateNais[0];
            $_POST['month'] = $dateNais[1];
            $_POST['day'] = $dateNais[2];
        }
        $validation = true;
        
        if (isset($_POST['yearOb'])) {
            if ($_POST['yearOb'] != '0000')
                $validation = false;
        }
        $this->form_validation->set_rules('nin', 'Le NIN', 'is_numeric');

        if (!isset($_POST['actif']) && ($_POST['raison'] == NULL)) 
        {
            $data['typeBox'] = 'warning_box';
            $data['informations'] = 'Désactivation de l\'étudiant <b>' . $_POST['matricule'] . ' </b> : indiquez la date et la raison de l\'abandon.';
            $this->load->view('scolarite/valider_info_personnelles', $data);
        } 
        else 
        {
            if (($_POST['yearOb'] >= 2005 && $_POST['yearOb'] <= 2050) || ($_POST['yearOb'] == NULL)) 
            {
                if ($this->form_validation->run()) 
                {
                    if (!$phoneParentsValid || !$phoneUrgenceValid || !$phoneValid || !$phoneValid2) 
                    {
                        $data['typeBox'] = 'error_box';
                        $data['informations'] = 'Erreur de saisie de données - Veuillez corriger les numéros de téléphone. ';
                        $this->load->view('scolarite/valider_info_personnelles', $data);
                    } 
                    else 
                    {
                        if ($this->isValide($_POST['moyenneBac']) != NULL) {
                            $_POST['moyenneBac'] = $this->isValide($_POST['moyenneBac']);
                            if (($dateNaissanceIsValid == 'valide')) 
                            {
                                $this->scolarite_modele->modifier_info_etudiant($_POST);
                                $data['typeBox'] = 'valid_box';
                                $data['informations'] = 'Les informations de l\'étudiant <b>' . $_POST['matricule'] . ' </b>ont été modifiées avec succès.';
                                $this->load->view('scolarite/valider_info_personnelles', $data);
                            } 
                            elseif ($dateNaissanceIsValid != 'valide') 
                            {
                                unset($_POST['year']);
                                unset($_POST['month']);
                                unset($_POST['day']);
                                $data['typeBox'] = 'error_box';
                                $data['informations'] = $dateNaissanceIsValid;
                                $this->load->view('scolarite/valider_info_personnelles', $data);
                            }
                        } else {
                            unset($_POST['moyenneBac']);


                            $data['typeBox'] = 'error_box';
                            $data['informations'] = 'la moyenne de bac n\'est pas valide';
                            $this->load->view('scolarite/valider_info_personnelles', $data);
                        }
                    }
                } else {
                    $data['typeBox'] = 'error_box';
                    $data['informations'] = 'E-mail ou Numéro d\'identité nationale n\'est pas valide.';
                    $this->load->view('scolarite/valider_info_personnelles', $data);
                }
            } else {
                $data['typeBox'] = 'error_box';
                $data['informations'] = 'L\'année d\'obtention du baccalauréat n\'est pas valide. Elle doit être entre 2005 et 2050. ';
                $this->load->view('scolarite/valider_info_personnelles', $data);
            }
        }
    }

    function inscrire_etudiant_module() 
    {
        $data = NULL;
        $data['titre'] = 'Inscrire des étudiants à un élément';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['inscrire'] = 'inscrire';
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view("scolarite/choisir_annee_session", $data);
    }

    function inscrire_etudiant_programme() {
        $this->clear_output();
        $data['titre'] = 'Inscrire des étudiants dans un programme et un grade.';
        $data['etudiant'] = $this->scolarite_modele->get_etudiant();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $data['grade'] = $this->scolarite_modele->get_grade();
        $this->load->view("scolarite/ajouter_etudiant_programme", $data);
    }

    function inscrire_etudiants_programme_validation() {
        $data = NULL;
        $data['informations'] = '';
        $data['validation'] = '';
        $data['typeBox'] = '';
        if (isset($_POST['items']) && isset($_POST['idProgramme'])) {
            $data['messageRetour'] = $this->scolarite_modele->inscrire_etudiant_programme($_POST);
            if (isset($data['messageRetour']['message']) || isset($data['messageRetour']['valide'])) {
                if (isset($data['messageRetour']['message'])) {
                    $data['typeBox'] = 'error_box';
                    $data['retour'] = 'retourProgramme';
                    for ($i = 0; $i < count($data['messageRetour']['message']); $i++) {
                        $data['informations'] .= '</br>';
                        $data['informations'] .= $data['messageRetour']['message'][$i];
                    }
                }
                if (isset($data['messageRetour']['valide'])) {
                    $data['validation'] .= '<div class="valid_box">';
                    for ($i = 0; $i < count($data['messageRetour']['valide']); $i++) {

                        $data['validation'] .= '</br>';
                        $data['validation'] .= $data['messageRetour']['valide'][$i];
                    }
                    $data['validation'] .='</div>';
                }
                $this->load->view("scolarite/modification_confirme", $data);
            } else {
                $data['retour'] = 'retourProgramme';
                $data['typeBox'] = 'valid_box';
                $data['informations'] = 'Tous les étudiants sélectionnés ont bien été inscrits';
                $this->load->view("scolarite/modification_confirme", $data);
            }
        } else {
            $data['typeBox'] = 'error_box';
            $data['retour'] = 'retourProgramme';
            $data['informations'] = 'Veuillez sélectionner au moins un étudiant et un programme.';
            $this->load->view("scolarite/modification_confirme", $data);
        }
    }

    function inscrire_etudiants_module($annee, $session, $programme, $sigle) {
        $data = NULL;
        $annee = $this->decode(($annee));
        $session = $this->decode(($session));
        $programme = $this->decode(($programme));
        
        $moduleValide = $this->scolarite_modele->get_module_validee($sigle,$annee,$session);
        if ($moduleValide) 
        {
            $data['typeBox'] = 'warning_box';
            $data['informations'] = 'Erreur : les notes du module <b> ' . $sigle . ' </b>sont 
                déjà validées.';
            $this->load->view("scolarite/modification_confirme", $data);
        }
        else 
        {
            $semestreDesactivation = $this->scolarite_modele->get_session_de_desactivation($sigle);
            if ($semestreDesactivation == Null) 
            {
                $data['matricule'] = $this->scolarite_modele->get_etudiants($programme);
                $data['estInscrit'] = $this->scolarite_modele->get_etudiants_inscrit_module($data['matricule'], $annee, $session, $sigle);
                $data['infoEtudiant'] = $this->scolarite_modele->get_name_etudiants($data['matricule']);
                $data['groupe'] = $this->scolarite_modele->get_groupe($annee, $session, $sigle);
                $data['annee'] = $annee;
                $data['session'] = $session;
                $data['sigle'] = $sigle;
                $data['titre'] = 'Inscrire des étudiants au module <b>' . $sigle . ' </b>au semestre <b>' . $this->get_session_nom($session) . ' ' . $annee;
                $this->load->view("scolarite/inscrire_etudiants_module", $data);
            } 
            else 
            {
                $year = '';
                for ($i = 0; $i < 4; $i++) {
                    $year .= $semestreDesactivation[$i];
                }
                $data['typeBox'] = 'warning_box';
                $data['informations'] = 'Le module <b> ' . $sigle . ' </b>est désactivé le semestre <b>' .
                        $this->get_session_nom($semestreDesactivation[4]) . ' ' .
                        $year . '.</b>';
                $this->load->view("scolarite/modification_confirme", $data);
            }
        }
    }

    function desinscrire_etudiants_module($annee, $session, $sigle) {
        $annee = $this->decode($annee);
        $session = $this->decode($session);
        $data = '';
        $moduleValide = $this->scolarite_modele->get_module_validee($sigle,$annee,$session);
        if ($moduleValide) {
            $data['typeBox'] = 'warning_box';
            $data['informations'] = 'Les notes du module  <b>' . $sigle . ' </b>sont validées, il n\'est
                pas possible de désinscrire un étudiant.';
            $this->load->view("scolarite/modification_confirme", $data);
        } else {
            $data['matricule'] = $this->scolarite_modele->get_etudiants_planetudes_delete($sigle, $session, $annee);
            $data['estInscrit'] = $this->scolarite_modele->get_etudiants_inscrit_module($data['matricule'], $annee, $session, $sigle);
            $data['infoEtudiant'] = $this->scolarite_modele->get_name_etudiants($data['matricule']);
            $data['annee'] = $annee;
            $data['session'] = $session;
            $data['sigle'] = $sigle;
            $data['desinscrire'] = 'desinscrire';
            $data['titre'] = 'Désinscrire des étudiants du module <b>' . $sigle . ' </b>au semestre <b>' . $this->get_session_nom($session) . ' ' . $annee . ' </b>';
            $this->load->view("scolarite/inscrire_etudiants_module", $data);
        }
    }

    function inscrire_etudiants_cours_validation() {
        $data = NULL;
        $data['informations'] = '';
        $data['validation'] = '';
        $data['typeBox'] = '';
        $data['messageRetour'] = '';
        //desinscrire etudiant
        if (isset($_POST['desinscrire'])) 
        {
            if(isset($_POST['items']))
            {
            $data['messageRetour'] = $this->scolarite_modele->desinscrire_etudiant_module($_POST);
                if (isset($data['messageRetour']['message'])) 
                {

                    $data['typeBox'] = 'error_box';
                    $data['retour'] = 'desretour';
                    for ($i = 0; $i < count($data['messageRetour']['message']); $i++) 
                    {
                        $data['informations'] .= '</br>';
                        $data['informations'] .= $data['messageRetour']['message'][$i];
                    }
                    $this->load->view("scolarite/modification_confirme", $data);
                } 
                else 
                {
                    $data['retour'] = 'desretour';
                    $data['typeBox'] = 'valid_box';
                    $data['informations'] = 'Tous les étudiants sélectionnés ont bien été désinscrits.';
                    $this->load->view("scolarite/modification_confirme", $data);
                }
            }
            else
            {
                $data['typeBox'] = 'error_box';
                $data['informations'] = 'Aucun étudiant n\'a été sélectionné.';
                $this->load->view("scolarite/modification_confirme", $data);
            }
        } 
        else {
            //Inscrire Etudiant
            if (isset($_POST['items']) && isset($_POST['groupe'])) {
                $data['messageRetour'] = $this->scolarite_modele->inscrire_etudiant_module_groupe($_POST);
                if (isset($data['messageRetour']['valide']) || (isset($data['messageRetour']['insert']))) {
                    if (isset($data['messageRetour']['valide'])) {
                        $data['typeBox'] = 'error_box';
                        $data['retour'] = 'retour';
                        for ($i = 0; $i < count($data['messageRetour']['valide']); $i++) {
                            $data['informations'] .= '</br>';
                            $data['informations'] .= $data['messageRetour']['valide'][$i];
                        }
                    }
                    if (isset($data['messageRetour']['insert'])) {
                        $data['validation'] .= '<div class="valid_box">';
                        for ($i = 0; $i < count($data['messageRetour']['insert']); $i++) {
                            $data['validation'] .= '</br>';
                            $data['validation'] .= $data['messageRetour']['insert'][$i];
                        }
                        $data['validation'] .='</div>';
                    }
                    $this->load->view("scolarite/modification_confirme", $data);
                }
            } else {
                $data['typeBox'] = 'error_box';
                $data['retour'] = 'retour';
                $data['informations'] = 'Veuillez sélectionner un étudiant et un groupe';
                $this->load->view("scolarite/modification_confirme", $data);
            }
        }
    }

    function afficher_cours() {
        //$module = $this->scolarite_modele->get_module_planetudes($_POST['annee'],$_POST['semestre']);
        $tables = array("module", "groupe");
        $join_keys = array("groupe.sigle = module.sigle");
        $db_columns = array('groupe.sigle as sigleGroupe', 'module.titre as titreModule');
        $result_columns = array('sigleGroupe', 'titreModule');
        $grid_columns = array('Module', 'Titre');
        if (isset($_POST['action'])) {
            $action = $_POST['action'] . '/' . $this->encode($this->input->post('annee')) . '/' . $this->encode($this->input->post('session'));
        } elseif (isset($_POST['inscrire'])) {
            if ($_POST['inscrire'] == 'inscrire') {
                $action = 'inscrire_etudiants_module/' . $this->encode($this->input->post('annee')) . '/' . $this->encode($this->input->post('session')) . '/' . $this->encode($this->input->post('programme'));
            } else {
                $action = 'desinscrire_etudiants_module/' . $this->encode($this->input->post('annee')) . '/' . $this->encode($this->input->post('session')) . '/' . $this->encode($this->input->post('programme'));
            }
        } elseif (isset($_POST['rattrapage'])) {
            $action = 'etudiant_rattrapage/' . $this->encode($this->input->post('annee')) . '/' . $this->encode($this->input->post('session'));
        } else {
            $action = 'modifier_note/' . $this->encode($this->input->post('annee')) . '/' . $this->encode($this->input->post('session'));
        }
        $id_action = '';


        $where = "WHERE groupe.annee = " . $this->input->post('annee') . " AND groupe.semestre = " . $this->input->post('session');
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);


        $titre = 'Liste des modules offerts au semestre ' . $this->get_session_nom($_POST['session']) . ' ' . $_POST['annee'];
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    }

    function etudiant_rattrapage($annee, $session, $sigle) {
        $annee = $this->decode($annee);
        $session = $this->decode($session);
        $data['info_etudiant_rattrapage'] = $this->scolarite_modele->get_info_etudiant_rattrapage($annee, $session, $sigle);
        $data['matricule'] = $this->scolarite_modele->get_etudiant_planetudes_note_rattrapage($annee, $session, $sigle);
        if ($data['matricule'] == '') {
            $data['aucunEtudiant'] = 'aucun';
        }
        $data['infoEtudiant'] = $this->scolarite_modele->get_name_etudiants($data['matricule']);
        $data['annee'] = $annee;
        $data['session'] = $session;
        $data['sigle'] = $sigle;

        $this->load->view('scolarite/enregistrer_rattrapage', $data);
    }

    function cote_valide($cote) {
        $coteValide = array('A', 'B', 'C', 'D', 'E', 'F', 'FX');
        $cote = strtoupper($cote);
        if (!in_array($cote, $coteValide)) {
            return NULL;
        }
        return $cote;
    }

    function enregistrer_note_rattrapage() {

        $tableauMatricule = array_keys($_POST, 'on');
        $tailleEtudiant = count($tableauMatricule);
        $data['informations'] = '';
        $k = 0;
        
        $trioValide = true;
        $messageInvalid = "";
        for ($i = 0; $i < $tailleEtudiant; $i++) {
            $note = $this->isValide($_POST['names' . $tableauMatricule[$i]]);
            $cote = $this->cote_valide($_POST['cote' . $tableauMatricule[$i]]);
            $lien = $this->scolarite_modele->get_lien($tableauMatricule[$i],$_POST['annee'],$_POST['session'],$_POST['sigle']);
            
            $validation = $this->scolarite_modele->entrees_valides($note, $cote, $lien);
            if(! $validation['valide'])
            {
                $trioValide = false;
                $messageInvalid .= "<b>".$tableauMatricule[$i]."</b> : ".$validation['erreur_msg']."<br>";
            }      
       }
       if($trioValide)
       {
        for ($i = 0; $i < $tailleEtudiant; $i++) {
            $note = $this->isValide($_POST['names' . $tableauMatricule[$i]]);
            $cote = $this->cote_valide($_POST['cote' . $tableauMatricule[$i]]);
            $lien = $this->scolarite_modele->get_lien($tableauMatricule[$i],$_POST['annee'],$_POST['session'],$_POST['sigle']);
            if ($note != NULL && $cote != NULL) {
                $data['return'] = $this->scolarite_modele->enregistrer_note_rattrapage($_POST, $i, $note, $cote);
                $this->ecrire_log($note, $note, $cote, $cote, $_POST['sigle']);
                if ($data['return'] == TRUE) {
                    $data['noteValide'][$i] = 'true';
                } else {
                    $data['noteValide'][$i] = 'false';
                }
            } else {
                $data['noteValide'][$i] = 'false';
                $data['messageDeRetour'][$k] = 'La cote et la note n\'ont pas été enregistrées. Veuillez vérifier les champs sélectionnés, ils ne sont pas  bien remplis pour l\'étudiant :' . $tableauMatricule[$i] . '</br>';
                $k++;
            }
        }
        $counter = 0;
        for ($i = 0; $i < $tailleEtudiant; $i++) {

            if ($data['noteValide'][$i] == 'true') {
                $counter++;
            }
        }
        if ($counter == $tailleEtudiant) {
            $data['typeBox'] = 'valid_box';
            $data['informations'] = 'La note et la cote de rattrapage sont bien enregistrées.';
            $data['retour'] = 'rattrapage';
            $this->load->view('scolarite/modification_confirme', $data);
        } else {
            for ($j = 0; $j < count($data['messageDeRetour']); $j++) {
                $data['informations'] .= $data['messageDeRetour'][$j];
            }
            $data['typeBox'] = 'error_box';
            $data['retour'] = 'rattrapage';
            $this->load->view('scolarite/modification_confirme', $data);
        }
       }
       else
       {
            $data['typeBox'] = 'error_box';
            $data['informations'] = $messageInvalid;
            $data['retour'] = 'rattrapage';
            $this->load->view('scolarite/modification_confirme', $data);
       }
    }

    function decision_manuelle() 
    {
    if (strlen($this->passwordChef) == 0 ) {	// si pas de mot de passe : on ne demande rien  
		$data = null;
        $data['titre'] = 'Modifier Décision de poursuite des études.';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $this->load->view('scolarite/choix_semestre_decision_manuelle', $data);
    }
	else
	{	// on demande un mot de passe
		$data = null;
		$this->clear_output();
		$this->form_validation->set_rules('pass', 'mot de passe', 'required');
		if (isset($_POST['pass']))
			$this->form_validation->set_rules('pass', 'mot de passe',
			'callback_verifier_chef_scolarite['.$_POST['pass'].']');
		if ($this->form_validation->run()) 
		{
			$data['titre'] = 'Modifier Décision de poursuite des études.';
			$data['annee'] = $this->scolarite_modele->recuperer_annee();
			$data['courante'] = $this->scolarite_modele->get_session_courante();
			$this->load->view('scolarite/choix_semestre_decision_manuelle', $data);
		}
		else
		{
			$data['typeInterface'] = 'decision_manuelle';
			$data['titre'] = 'L\'accès à ce sous-menu est réservé.<br> Entrez le mot de passe.';
            $this->load->view("scolarite/chef_scolarite_pass", $data);
		}
    }
	}

    function afficher_liste_etudiants_decision_manuelle()
    {
        $tables = array("dossieretudiant", "planetudes","etudiant");
        $join_keys = array("dossieretudiant.matriculeEtudiant = planetudes.matriculeEtudiant",
            "planetudes.matriculeEtudiant = etudiant.matriculeEtudiant");
        $db_columns = array('planetudes.matriculeEtudiant as matricule',
            'etudiant.nom as nom', 'etudiant.prenom as prenom',
            'dossieretudiant.decisionBulletin as decision');
        $result_columns = array('matricule','nom', 'prenom','decision');
        $grid_columns = array('Matricule','Nom', 'Prénom','Décision');

        $action = 'afficher_decision_etudiant/' . $this->encode($this->input->post('annee')) . '/' . 
                $this->encode($this->input->post('semestre'));
        $id_action = '';


        $where = "WHERE planetudes.annee = " . $this->input->post('annee') .
                " AND planetudes.semestre = " . $this->input->post('semestre')." order by matricule";

        $result = $this->search_modele->getSearchResult($tables, $db_columns, 
                $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);


        $titre = 'Liste des étudiants inscrits au semestre <b>'.$this->get_session_nom
                ($_POST['semestre']).'  '.$_POST['annee'].'.</b>';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
        
    }
    
    function afficher_decision_etudiant($annee,$semestre,$matriculeEtudiant)
    {
        $infoEtudiant = $this->scolarite_modele->get_informations_etudiant($matriculeEtudiant);
        $data['decision'] = $this->scolarite_modele->get_decision();
        $data['decisionEtudiant'] = $this->scolarite_modele->get_decision_etudiant($matriculeEtudiant);
        $data['matricule'] = $matriculeEtudiant;
        $data['nom'] = $infoEtudiant['nom'];
        $data['prenom'] = $infoEtudiant['prenom'];
        $this->load->view("scolarite/saisir_decision", $data);
    }
    function valider_decision() 
    {
     $this->scolarite_modele->saisir_decision($_POST['matricule'], $_POST['decision']);
        $data['informations'] = 'La  <b> Décision de poursuite des études </b>  
            pour l\'étudiant <b>' . $_POST['matricule'] .
         ' </b>est maintenant <b>'.  $this->scolarite_modele->getDecisionM($_POST['decision'])
                .'.</b></br>';
        $data['typeBox'] = 'valid_box';
        $this->load->view('scolarite/modification_confirme', $data);
    }

    function generer_cote($annee, $session, $sigle) 
    {
        $annee = $this->decode($annee);
        $session = $this->decode($session);
		
        $data['matricule'] = $this->scolarite_modele->get_etudiant_planetudes_note($annee, $session, $sigle);

        $data['infoEtudiant'] = $this->scolarite_modele->get_name_etudiants($data['matricule']);

        $data['noteEtudiant'] = $this->scolarite_modele->get_note_cote_etudiant($data['matricule'], $annee, $session, $sigle);

        $data['annee'] = $annee;
        $data['session'] = $session;
        $data['sigle'] = $sigle;
        $isValid = $this->scolarite_modele->get_notes_isvalid($data['matricule'], $annee, $session, $sigle);
        if ($isValid == true) {
            $data['submit_button'] = '<input type="submit" name="submit" id="submit" value="Valider" onclick="return confirm(\'Voulez-vous valider les notes et les cotes ?\');" />';
            $data['generer_cote_button'] = '<button onclick="generer_cote(' . sizeof($data['matricule']) . ');">Générer les cotes</button>';
            $this->load->view("scolarite/generer_cote", $data);
        } else {
            if ($this->scolarite_modele->get_notes_isvalid_titre($data['matricule'], $annee, $session, $sigle)) {
                $data['message'] = 'Les notes ne sont pas encore approuvées par le chef de département.';
                $data['coteEtudiant'] = $this->scolarite_modele->get_cote($data['matricule'], $annee, $session, $sigle);
                $this->load->view("scolarite/generer_cote", $data);
            } else {
                $data['message'] = 'Les notes et les cotes sont déjà validées.';
                $data['coteEtudiant'] = $this->scolarite_modele->get_cote($data['matricule'], $annee, $session, $sigle);
                $this->load->view("scolarite/generer_cote", $data);
            }
        }
	}

    function valider_cote($tableau) {
        $coteValide = array('A', 'B', 'C', 'D', 'E', 'F', 'FX');
        $error_message = '';
        $counter = 0;
        if (is_array($tableau))
            if (is_array($tableau['cote'])) {
                for ($i = 0; $i < count($tableau['cote']); $i++) {
                    if (!in_array($tableau['cote'][$i], $coteValide)) {
                        $error_message.= 'La cote ' . $tableau['cote'][$i] . ' de l\'étudiant [' . $tableau['matricule'][$i] . '] n\'est pas valide.</br>';
                        $counter++;
                    }
                }
            }

        if ($counter == count($tableau['cote'])) {
            $error_message = 'Erreur : il faut générer les cotes avant de valider les notes et les cotes.';
        }
        return $error_message;
    }

    function enregistrer_cote() {

        $isValid = $this->scolarite_modele->get_notes_isvalid($_POST['matricule'], $_POST['annee'], $_POST['session'], $_POST['sigle']);

        if ($isValid == true) {
            /* Ajout pour valider le trio note cote lien */
            $matricules = $_POST['matricule'];
            $notes = $_POST['note'];
            $cotes = $_POST['cote'];
            
            $trioValide = true;
            $messageInvalid = "";
            if(is_array($matricules) && count($matricules) > 0  && count($notes) == count($cotes) && count($notes) == count($matricules))
            {
                for($i = 0; $i < count($matricules); $i++)
                {
                    $lien = $this->scolarite_modele->get_lien($matricules[$i], $_POST['annee'], $_POST['session'], $_POST['sigle']);
                    $validation = $this->scolarite_modele->entrees_valides($notes[$i], $cotes[$i], $lien);
                    if(! $validation['valide'])
                    {
                        $trioValide = false;
                        $messageInvalid .= "<b>".$matricules[$i]."</b> : ".$validation['erreur_msg']."<br>";
                    }
                }
            }
            else
                die("probleme !!");
            
            if ($trioValide)
            {
            /***/

                $messageDeValidation = $this->valider_cote($_POST);
                if ($messageDeValidation == '') {
                    $this->scolarite_modele->enregistrer_cote($_POST);
                    $data['typeBox'] = 'valid_box';
                    $data['retour'] = "generer_note_cote";
                    $data['informations'] = 'Les cotes et les notes du module <b>' . $_POST['sigle'] . ' </b>ont été validées pour la création des bulletins.</br>';
                    $this->load->view('scolarite/modification_confirme', $data);
                } else {
                    $data['typeBox'] = 'error_box';
                    $data['retour'] = "generer_note_cote";
                    $data['informations'] = "<b>Les cotes n'ont pas été générées .  </b></br>" . $messageDeValidation;
                    $this->load->view('scolarite/modification_confirme', $data);
                }
            }
            else
            {
                $data['typeBox'] = 'error_box';
                $data['informations'] = $messageInvalid;
                $data['retour'] = "generer_note_cote";
                $this->load->view('scolarite/modification_confirme', $data);
            }
        }
    }

    function modifier_note($annee, $session, $sigle) {
        $annee = $this->decode($annee);
        $session = $this->decode($session);
        $data['matricule'] = $this->scolarite_modele->get_etudiant_planetudes_note($annee, $session, $sigle);
        if ($data['matricule'] == NULL) {
            $data['aucunEtudiant'] = 'aucunEtudiant';
        }
        $data['infoEtudiant'] = $this->scolarite_modele->get_name_etudiants($data['matricule']);
        $data['noteEtudiantCote'] = $this->scolarite_modele->get_note_cote_etudiant($data['matricule'], $annee, $session, $sigle);

        $etatNoteScola = $this->scolarite_modele->get_etat_note_etudiants($data['matricule'], $annee, $session, $sigle);
        $isValid = $this->scolarite_modele->get_notes_isvalid($data['matricule'], $annee, $session, $sigle);
        $data['annee'] = $annee;
        $data['session'] = $session;
        $data['sigle'] = $sigle;
        if ($isValid == true) {
            $data['boutton_enregistrer'] = '<input type="submit" name="submit" id="submit" value="Enregistrer" />';
            $this->load->view('scolarite/modifier_note', $data);
        } else {
            if ($etatNoteScola) {
                $data['message'] = 'Notes et cotes sont déjà validées. Attention, 
                    il faut recalculer le bulletin après tout changement de note ou de cote.';
            } else {
                $data['message'] = 'Les notes ne sont pas encore validées par le 
                    chef de département.';
            }
            $data['disabled'] = 'disabled';

            $this->load->view('scolarite/modifier_note', $data);
        }
    }

    /*
     * Fonction isValid pour valider la note qui doit etre entre 0 et 20 et remplacer les virgules par des .
     */

    function isValide($note) 
    {
       $note = str_replace(",", ".", $note);
        if(is_numeric($note) && $note<=20 && $note>=0)
        {
            if(strlen($note)<=5)
                return $note;
            else
                return NULL;
        }
        else
            return NULL;
    }

    function send_mail($to, $message) {
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'ssl://smtp.googlemail.com';
        $config['smtp_port'] = '465';
        $config['smtp_user'] = 'emim.sge@gmail.com';
        $config['smtp_pass'] = 'emim_sge123';
        $config['charset'] = 'utf-8';
        $config['mailtype'] = 'text'; // or html
        $config['validation'] = TRUE; // bool whether to validate email or not     
        
        $this->email->initialize($config);

        $this->email->set_newline("\r\n");
        $this->email->from('emim.sge@gmail.com', 'EMIM');
        $this->email->to($to);

        $this->email->subject('Changement dans un plan d\'études dans LAURIAT');
        $this->email->message($message);
        
        if(!$this->email->send()) 
        {
             redirect('scolarite/echoue_email');
        } 
    }
    
    function echoue_email()
    {
          $data['typeBox'] = 'error_box';
          $data['informations'] = 'Erreur : l\'envoi des messages lors des
              modifications de notes est activé, mais la connexion est  
              impossible. Veuillez faire une copie d\'écran de ce message
              et la transmettre à l\'administrateur-réseau.';
          $this->load->view('scolarite/modification_confirme', $data);
    }

    /*
     * Fonction pour ecrire les fichiers de log de chaque note modifiée.
     */

    function ecrire_log_sans_cote($noteAvant, $noteApres, $sigle,$annee,$semestre,$matriculeEtudiant) {
        $format = 'DATE_ATOM';
        $time = time();

        $date = standard_date($format, $time);

        $fp = fopen("logNotes/" . $this->session->userdata('matriculeEmploye') . ".txt", "a");
        $str = "Date :" . $date . " GMT \n Matricule de l'employé: [" . $this->session->userdata('matriculeEmploye') . "] 
               Module : [" . $sigle . "]- Étudiant : [".$matriculeEtudiant."] - Semestre :".$annee."-".$semestre.
                "\nNote avant: [" . $noteAvant ."]- Note après:[" . $noteApres . "].\r\n";
        fputs($fp, $str);
        fclose($fp);
        $personneCle = $this->scolarite_modele->get_personnes_cle();
        if ($personneCle != NULL) {
            for ($i = 0; $i < count($personneCle); $i++) {
                $this->send_mail($personneCle[$i], $str);
            }
        }
    }

    /*
     * Fonction pour ecrire les fichiers de log de chaque note modifiée.
     */

	function SemestreTexte($semestre)
	{
		switch(intval($semestre))	// correction 2.2.2 : ajout intval
		{ case '1' :
			return "Printemps";
			break;
		case '2' :
			return "Ete";
			break;
		case '3' :
			return "Automne";
			break;
		default :
			return "??";
		}
	}
		
		function ecrire_log($noteAvant, $noteApres, $coteAvant, $coteApres, $sigle ,
            $annee, $semestre, $matriculeEtudiant) 
    {
        $personneCle = $this->scolarite_modele->get_personnes_cle();
        if ($personneCle != NULL) {
            $format = 'DATE_ATOM';
            $time = time();

            $date = standard_date($format, $time);

            $fp = fopen("logNotes/" . $this->session->userdata('matriculeEmploye') . ".txt", "a");
            $str = "Date :" . $date . " GMT \n Matricule de l'employé: [" . $this->session->userdata('matriculeEmploye') . "] 
                Module : [" . $sigle . "]- Étudiant : [".$matriculeEtudiant."] - Semestre :".$annee." ".$this->SemestreTexte($semestre).
                "\nNote avant: [" . $noteAvant ."]- Note après:[" . $noteApres . "]
                 \nCote avant: [" . $coteAvant . "]- Cote après: [" . $coteApres . "].\r\n";
            fputs($fp, $str);
            fclose($fp);

            for ($i = 0; $i < count($personneCle); $i++) {
                $to = $personneCle[$i];
                $this->send_mail($to, $str);
            }
        }
    }

     /*
     * Fonction pour ecrire les fichiers de log de chaque note modifiée.
     */

    function ecrire_log_plan_etudes($noteAvant, $noteApres, $coteAvant, 
                                    $coteApres,$lienAvant,$lienApres, $sigle,
                                    $annee, $semestre, $matricule) 
    {
        $personneCle = $this->scolarite_modele->get_personnes_cle();
        if ($personneCle != NULL) {
            $format = 'DATE_ATOM';
            $time = time();
            
            $date = standard_date($format, $time);
			$fp = fopen("logNotes/" . $this->session->userdata('matriculeEmploye') . ".txt", "a");
            $str = "Date :" . $date . "
             - Employé : [" . $this->session->userdata('matriculeEmploye') . "] - Étudiant : [".$matricule."] 
             - Module : [" . $sigle . "] - Année : [".$annee."] - Semestre : [".$this->SemestreTexte($semestre)."]
             - Note avant : [" . $noteAvant ."] - Note après : [" . $noteApres . "]
             - Cote avant : [" . $coteAvant . "] - Cote après : [" . $coteApres . "]
             - Lien avant : [".$lienAvant."] - Lien après : [".$lienApres."].\r\n\r\n";
            fputs($fp, $str);
            fclose($fp);

            for ($i = 0; $i < count($personneCle); $i++) {
                $to = $personneCle[$i];
                $this->send_mail($to, $str);
            }
        }
    }
    /*
     * Fonction pour enregistrer les notes modifiees par le service de scolarite.
     */

    function enregistrer_note() 
    {
        $data['messageDeRetour'] = '';
        $data['informations'] = '';
        $tailleEtudiant = count($_POST['matricule']);
        $k = 0;
        for ($i = 0; $i < $tailleEtudiant; $i++) {
            $note = $this->isValide($_POST['note' . $i]);
            //on teste si les cotes ne sont pas generees. Il suffit d'en avoir une seule.
            //pour savoir que les cotes sont generees ou non
            if ($_POST['coteAncienne0'] != '') {
                $cote = $this->cote_valide($_POST['cote' . $i]);
                //On teste si les notes et les cotes sont valides.
                if ($note != NULL && $cote != Null) {
                    //dans le cas si on change une note ou une cote on envoie un email pour la personne cle.
                    if ($_POST['note' . $i] != $_POST['noteAncienne' . $i] || $_POST['cote' . $i] != $_POST['coteAncienne' . $i]) {
                        $this->ecrire_log($_POST['noteAncienne' . $i],
                                $_POST['note' . $i], $_POST['coteAncienne' . $i],
                                $_POST['cote' . $i], $_POST['sigle'],$_POST['annee'],
                                $_POST['session'],$_POST['matricule'][$i]);
                        $this->scolarite_modele->enregistrer_note_modifie($_POST, $i, $note);
                    }
                    $data['post'] = $_POST;
                    $data['noteValide'][$i] = 'true';
                } else {
                    $data['noteValide'][$i] = 'false';
                    $data['messageDeRetour'][$k] = 'La note [' . $this->input->post('note' . $i) . '] ou la cote [' . $this->input->post('cote' . $i) .
                            '] pour l\'étudiant :[' . $_POST['matricule'][$i] . '] n\'est pas valide </br>';
                    $k++;
                }
            }
            //les cotes ne sont pas encore generees
            else {
                if ($note != NULL) {
                    if ($_POST['note' . $i] != $_POST['noteAncienne' . $i]) {
                        $this->ecrire_log_sans_cote($_POST['noteAncienne' . $i],
                                $_POST['note' . $i], $_POST['sigle'],$_POST['annee'],
                                $_POST['session'],$_POST['matricule'][$i]);
                        $this->scolarite_modele->enregistrer_note_modifie_sans_cote($_POST, $i, $note);
                    }
                    $data['post'] = $_POST;
                    $data['noteValide'][$i] = 'true';
                } else {
                    $data['noteValide'][$i] = 'false';
                    $data['messageDeRetour'][$k] = 'La note [' . $this->input->post('note' . $i) . '] pour l\'étudiant :[' . $_POST['matricule'][$i] . '] n\'est pas valide </br>';
                    $k++;
                }
            }
        }
        $counter = 0;
        for ($i = 0; $i < $tailleEtudiant; $i++) {

            if ($data['noteValide'][$i] == 'true') {
                $counter++;
            }
        }
        if ($counter == $tailleEtudiant) {
            $data['typeBox'] = 'valid_box';
            $data['informations'] = 'Les notes du module <b>'. $_POST['sigle']. '</b> ont bien été modifiées.';
            $this->load->view('scolarite/modification_confirme', $data);
        } else {
            for ($j = 0; $j < count($data['messageDeRetour']); $j++) {
                $data['informations'] .= $data['messageDeRetour'][$j];
            }
            $data['typeBox'] = 'error_box';
            $this->load->view('scolarite/modification_confirme', $data);
        }
    }

    function valider_note_view() {
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['sigle'] = '';
        $this->load->view("scolarite/choix_cours_validation", $data);
    }

    function afficher_cours_validation() {
        $tables = array("module", "groupe");
        $join_keys = array("groupe.sigle = module.sigle");
        $db_columns = array('groupe.sigle as sigleGroupe', 'module.titre as titreModule');
        $result_columns = array('sigleGroupe', 'titreModule');
        $grid_columns = array('Module', 'Titre');

        $action = 'valider_note/' . $this->encode($this->input->post('annee')) . '/' . $this->encode($this->input->post('session'));
        $id_action = '';


        $where = "WHERE annee = " . $this->input->post('annee') . " AND semestre = " . $this->input->post('session');

        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);


        $titre = 'Afficher Liste des cours';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    }

    function valider_note($annee, $session, $sigle) {
        $annee = $this->decode($annee);
        $session = $this->decode($session);
        $data = NULL;
        $data['matricule'] = $this->scolarite_modele->get_etudiant_planetudes_note($annee, $session, $sigle);
        $data['infoEtudiant'] = $this->scolarite_modele->get_name_etudiants($data['matricule']);
        $data['noteEtudiant'] = $this->scolarite_modele->get_note_etudiant_valide($data['matricule']);
        $data['annee'] = $annee;
        $data['session'] = $session;
        $data['sigle'] = $sigle;
        $this->load->view('scolarite/modifier_note', $data);
    }

    function cree_groupe_cours() {
        $data = NULL;
        $this->form_validation->set_rules('sigle', 'sigle ', 'STRING');
        if ($this->form_validation->run()) {
                    $infoGroupe = $this->input->post();
				// ajout 2.2.1 : on refuse le cas où le semestre de création du groupe est
				//					inférieur au semestre d'activation du module
				//					ou supérieur au semestre de désactivation (si ce dernier existe).
				$sem_creation = $infoGroupe['date'].intval($infoGroupe['session']); // retournera 20131 pour Printemps 2013
				$infos_module = $this-> scolarite_modele->recuperer_module($infoGroupe['sigle']);
				if (isset($infos_module['semestreDesactivation'])) {
					$valide = (($infos_module['semestreActivation'] <= $sem_creation) && ($infos_module['semestreDesactivation'] >= $sem_creation)); }
					else { $valide = ($infos_module['semestreActivation'] <= $sem_creation); }
				if ($valide == FALSE) {
					$tropTot = ($sem_creation < $infos_module['semestreActivation']);
					$data['typeBox'] = 'error_box';
					$data['informations'] = 'Le groupe ne peut être créé car le semestre choisi <b>'.$this->get_session_nom(intval($infoGroupe['session'])).' '. $infoGroupe['date'];
						if ($tropTot == TRUE) {
							$data['informations'] = $data['informations'] . '</b> est antérieur au semestre d\'activation <b>';
								$data['informations'] = $data['informations']. $this->get_session_nom(substr($infos_module['semestreActivation'],4,1)) .' ' . substr($infos_module['semestreActivation'],0,4); 
							}
							else {
								$data['informations'] = $data['informations'] . '</b> est postérieur au semestre de désactivation <b>';
								$data['informations'] = $data['informations'].$this->get_session_nom(substr($infos_module['semestreDesactivation'],4,1)) .' '  . substr($infos_module['semestreDesactivation'],0,4); 
							}
						$data['informations'] = $data['informations'] . '</b> du module <b>'.$infoGroupe['sigle'].'</b>.';

						$infos_module['semestreActivation'].' ou supérieur au semestre de désactivation.';
					$this->load->view('scolarite/modification_confirme', $data);	// sortir ??

				}	// fin ajout 2.2.1
				else {
                                   // print_r($infoGroupe);
					$numeroGroupe = $this->scolarite_modele->cree_nouveau_groupe($infoGroupe);
					$data['typeBox'] = 'valid_box';
					$data['informations'] = 'Le groupe<b> ' . $infoGroupe['sigle'] . '-' . $infoGroupe['date'] .  
						$this->scolarite_modele->lettreSemestre($infoGroupe['session']) . '-' . $infoGroupe['typeGroupe'] . '-' . 
						$numeroGroupe . ' </b> a été créé.'; 
					$this->load->view('scolarite/modification_confirme', $data);
					}
        } else {
            $data_sigle_cours = $this->scolarite_modele->get_cours_actif_enseignant();
			$data_session_courante = $this->scolarite_modele->get_session_courante();
            $data_employer = $this->scolarite_modele->recuperer_enseignant();
			if ($data_sigle_cours == Null) {
                $data['typeBox'] = 'warning_box';
				$data['informations'] = 'Aucun module n\'est encore créé.';
                $this->load->view('scolarite/modification_confirme', $data);
            } else {
                $data = array('matriculeEmploye' => $data_employer['matriculeEmploye'], 'nom' => $data_employer['nom'], 'prenom' => $data_employer['prenom'], 'sigleCours' => $data_sigle_cours['sigleCours'],
                    'nomResponsable' => $data_sigle_cours['nomResponsable'], 'prenomResponsable' => $data_sigle_cours['prenomResponsable'],
                   'matriculeResponsable' => $data_sigle_cours['matriculeResponsable'], 'sigleTitre' => $data_sigle_cours['titre'],'annee' => $data_session_courante['annee'][0], 'semestre' => $data_session_courante['semestre'][0]);
				   /* 'annee' => $data_session_courante['annee'][0], 'semestre' => $data_session_courante['semestre'][0]); Roger Ajout matriculeResponsable  2.1.3 */ 
			$this->load->view("scolarite/creer_groupes_cours", $data);
          
			}
        }
    }

    public function plan_etudes() 
	{
		$this->clear_output();
		if (strlen($this->passwordChef) > 0 ) {	 //RM	
			$this->form_validation->set_rules('pass', 'mot de passe', 'required');
		}
		if ((isset($_POST['pass'])) and strlen($this->passwordChef) > 0 )	// RM
			$this->form_validation->set_rules('pass', 'mot de passe',
			'callback_verifier_chef_scolarite['.$_POST['pass'].']');
		if (($this->form_validation->run()) or  strlen($this->passwordChef) == 0)	// RM
		{
			$tables = array("etudiant ,planetudes, dossierEtudiant, programme");
			$join_keys = '';
			$db_columns = array('etudiant.matriculeEtudiant as matricule',
				"case when semestre = 1 then 'Printemps' when semestre = 2 then '&#201;t&#233;'
					when semestre = 3 then 'Automne' end as semestre", 'annee', 'sigle', 'programme.nom as nom', "case when note = -1 then 'AV' when note != -1 then note end as note", 'cote',
				"case when lien = 'HH' or 'HV' then 'HP' when lien = 'AR' then 'AB' when lien = 'RR' then 'RT' when lien ='ER' then 'EQ' ELSE lien end as lien ",
				"case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
			$result_columns = array('matricule', 'annee', 'semestre', 'nom', 'sigle', 'note', 'cote', 'lien', 'actif');
			$grid_columns = array('Matricule', 'Ann&#233;e', 'Semestre', 'Programme', 'Sigle', 'Note', 'Cote', 'Lien (Obs.)', 'Actif ?');

			$action = 'edit_plan_etudes/';
			$id_action = '';
			$more_than_one_id = array('matricule', 'annee', 'semestre', 'sigle');


			$where = "where etudiant.matriculeEtudiant = planetudes.matriculeEtudiant and etudiant.matriculeEtudiant = dossieretudiant.matriculeEtudiant and dossierEtudiant.idProgramme = programme.idProgramme  order by etudiant.matriculeEtudiant Asc, annee Desc, semestre Desc";

			$result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns, $more_than_one_id);


			$titre = "Consulter/Modifier le plan d'&#233;tudes";
			$controlleur = "scolarite";
			$data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


			$this->load->view("recherche_parametree", $data);
		}
		else
		{
			$data['typeInterface'] = 'plan_etudes';
			$data['titre'] = 'L\'accès à ce sous-menu est réservé.<br> Entrez le mot de passe.';
            $this->load->view("scolarite/chef_scolarite_pass", $data);
		}
    }


    public function edit_plan_etudes($matricule, $annee, $semestre, $sigle) {
        $sem = '';
        switch ($semestre) {
            case 'Automne':
                $sem = 3;
                break;
            case 'Printemps':
                $sem = 1;
                break;
            case '%C3%89t%C3%A9':   //ETE: 
                $sem = 2;
                break;
            default:
                die($semestre . " non valide !");
        }
        $infos = $this->scolarite_modele->getInfosPlanEtudes($matricule, $sigle, $annee, $sem);
        $infos['disabled'] = true;
        $this->load->view('scolarite/afficher_modifier_plan_etudes', $infos);
    }

    function modifier_plan_etudes() {
        $infos = $_POST;
        $this->form_validation->set_rules('matricule', 'Matricule', 'required');
        if ($this->form_validation->run()) {

            $infos['disabled'] = false;
            $note = str_replace(',','.',trim($_POST['note']));
            if($note == null)
                $note = "AV";
            $cote = trim($_POST['cote']);
            $lien = trim($_POST['lien']);
            if($lien == '')
                $lien = null;

            if (!(floatval($note) >= 0 && floatval($note) <= 20 && is_numeric($note) || strtoupper($note) == 'AV')) {
                $infos['errorMsg'] = "La note: " . $note . " n'est pas valide !";
                $infos['class'] = "error_box";
                $this->load->view('scolarite/afficher_modifier_plan_etudes', $infos);
            } else {

                if (strtoupper($note) == 'AV')
                    $note = -1;
                $validation = $this->scolarite_modele->entrees_valides($note, $cote, $lien);
                if ($validation['valide']) 
                {
                    $matricule = $_POST['matricule'];
                    $semestre = $_POST['semestre'];
                    $annee = $_POST['annee'];
                    $sigle = $_POST['sigle'];
                    if ($this->scolarite_modele->modifier_plan_etudes($matricule, $sigle, $semestre, $annee, $note, $cote, $lien)) 
                    {
                        if($note == $_POST['noteAvant'] && $cote == $_POST['coteAvant'] && $lien == $_POST['lienAvant'])
                        {
                            $data['typeBox'] = 'valid_box';
                            $data['informations'] = 'Le plan d\'étude de l\'étudiant <b>'.
                                    $matricule.' </b>a été modifié avec succès.';
                            $this->load->view('scolarite/modification_confirme', $data);
                        }
                        else
                        {
                            $this->ecrire_log_plan_etudes($_POST['noteAvant'], $note, $_POST['coteAvant'], $cote, 
                              $_POST['lienAvant'],$lien, $sigle, $annee, $semestre,$matricule);
                            $data['typeBox'] = 'valid_box';
                            $data['informations'] = 'Le plan d\'étude de l\'étudiant <b>'.
                                    $matricule.' </b>a été modifié avec succès.';
                            $this->load->view('scolarite/modification_confirme', $data);
                        }
                    } 
                    else 
                    {
                        die("Un problème est survenu, veuillez communiquer avec le développeur !");
                    }
                } else {
                    $infos['errorMsg'] = $validation['erreur_msg'];
                    $infos['class'] = "error_box";
                    $this->load->view('scolarite/afficher_modifier_plan_etudes', $infos);
                }
            }
        } 
        else 
        {
            $infos = $_POST;
            $infos['errorMsg'] = "Une erreur inconnue est survenue ! Veuillez ré-essayer";
            $infos['class'] == "error_box";
            $this->load->view('scolarite/afficher_modifier_plan_etudes', $infos);
        }
    }

    /*
     * fonction qui load la vue de recherche parametree afin de modifier les cycles
     */

    public function choix_cours_a_modifier() {
        $tables = array("module");
        $join_keys = null;
        $db_columns = array('sigle', 'titre', 'typeModule', 'idDepartement');
        $grid_columns = array('Module', 'Titre', 'Type', "Département");
        $action = 'modifier_groupe_cours';
        $id_action = 'sigle';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action);
        $titre = 'Choisissez un module ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    public function modifier_groupe_cours($sigle, $idGroupe = '') {
        //little work arround  ** dont remove it **
        if ($sigle == 'modifier_groupe_cours_action')
            redirect('scolarite/modifier_groupe_cours_action/' . $idGroupe);

        $tables = array("groupe");
        $join_keys = null;
        $db_columns = array('idGroupe', 'numGroupe', 'typeGroupe', 'annee', "case when semestre = 1 then 'Printemps'
    when semestre = 2 then '&#201;t&#233;' when semestre = 3 then 'Automne' end as semestre ");
        $grid_columns = array('Groupe', 'Numéro du groupe', 'Type', "Année", 'Semestre');
        $result_columns = array('idGroupe', 'numGroupe', 'typeGroupe', 'annee','semestre');
        $action = 'modifier_groupe_cours_action';
        $id_action = 'idGroupe';
        $db_where = "where sigle = '" . $sigle . "' order by sigle";
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $db_where,$result_columns);
        $titre = 'Les groupes du module <b>' . $sigle . '</b>';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    function consulter_groupe() {
        $tables = array("module");
        $join_keys = null;
        $db_columns = array('sigle', 'titre', 'typeModule', 'idDepartement');
        $grid_columns = array('Module', 'Titre', 'Type', "Département");
        $action = 'consulter_groupe_module';
        $id_action = 'sigle';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action);
        $titre = 'Choisissez un module ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    function consulter_groupe_module($sigle) {

        $tables = array("groupe");
        $join_keys = null;
        $db_columns = array('idGroupe', 'numGroupe', 'typeGroupe', 'annee', "case when semestre = 1 then 'Printemps'
            when semestre = 2 then '&#201;t&#233;' when semestre = 3 then 'Automne' end as semestre ");
        $result_columns = array('idGroupe','numGroupe','typeGroupe','annee','semestre');
        $grid_columns = array('Groupe', 'Numéro du groupe', 'Type', "Année", 'Semestre');
        $action = '#';
        $id_action = 'idGroupe';
        $db_where = "where sigle = '" . $sigle . "' order by sigle";
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $db_where,$result_columns);
        $titre = 'Les groupes du module <b>' . $sigle . '</b>';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    /*
     * fonction qui modifie le cycle par les valeurs post du formulaire et redirige vers l interface de 
     * voir tous les cycles
     */

    public function modifier_groupe_cours_action($idGroupe = '') {

        $this->form_validation->set_rules('sigle', 'Sigle', '');
        if ($this->form_validation->run()) {
            $this->scolarite_modele->modifier_groupe_cours($_POST);
            $data['typeBox'] = 'valid_box';
            $numGroupe_lettre = $this->scolarite_modele->lettre_dans_Groupe($_POST['idGroupe']);
			$data['informations'] = 'Le groupe <b>' . $numGroupe_lettre . '</b> a été modifié avec succès.';
			// $data['informations'] = 'Le groupe <b>' . $_POST['idGroupe'] . '</b> a été modifié avec succès !';
            $this->load->view('scolarite/modification_confirme', $data);
        } else {
            $infos = $this->scolarite_modele->get_infos_groupe($idGroupe);
            $data_employer = $this->scolarite_modele->recuperer_enseignant();
            $data = array('matriculeEmploye' => $data_employer['matriculeEmploye'], 'nom' => $data_employer['nom'], 'prenom' => $data_employer['prenom'], 'sigleCours' => $infos[0]['sigle'],
                'prof_resp' => $infos[0]['matriculeEmploye'], 'annee' => $infos[0]['annee'], 'semestre' => $infos[0]['semestre'], 'type_info' => $infos[0]['typeGroupe'], 'idGroupe' => $idGroupe);
            $this->load->view('scolarite/modifier_groupe_cours', $data);
        }
    }

    public function choix_cours_a_supprimer() {
        $tables = array("module");
        $join_keys = null;
        $db_columns = array('sigle', 'titre', 'typeModule', 'idDepartement');
        $grid_columns = array('Module', 'Titre', 'Type', "Département");
        $action = 'supprimer_groupe_cours';
        $id_action = 'sigle';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action);
        $titre = 'Supprimer un groupe : choisissez le module ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    /*public function supprimer_groupe_cours($sigle, $idGroupe = '') {
        //little work arround  ** dont remove it **
        if ($sigle == 'supprimer_groupe_cours_action')
            redirect('scolarite/supprimer_groupe_cours_action/' . $idGroupe);

        $tables = array("groupe");
        $join_keys = null;
        $db_columns = array('idGroupe', 'numGroupe', 'typeGroupe', 'annee', "case when semestre = 1 then 'Paire'
            when semestre = 2 then '&#201;t&#233;' when semestre = 3 then 'Impaire' end as semestre ");
        $grid_columns = array('Id du groupe', 'Numéro du groupe', 'Type', "Année", 'Semestre');
        $result_columns = array('idGroupe', 'numGroupe', 'typeGroupe', 'annee','semestre');
        $action = 'supprimer_groupe_cours_action';
        $id_action = 'idGroupe';
        $db_where = "where sigle = '" . $sigle . "' order by sigle";
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, 
                $action, $id_action, $db_where,$result_columns);

        $titre = 'Les groupes du module <b>' . $sigle . '.</b>' . ' Sélectionnez le groupe à supprimer.';
        $controlleur = "scolarite";
        $confirmation = "Êtes-vous sûr de vouloir supprimer ce groupe ?";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result, 'confirmation' => $confirmation);
        $this->load->view("recherche_parametree", $data);
    }
*/
    public function supprimer_groupe_cours($sigle, $idGroupe = '') {
        //little work arround  ** dont remove it **
        if ($sigle == 'supprimer_groupe_cours_action')
            redirect('scolarite/supprimer_groupe_cours_action/' . $idGroupe);

        $tables = array("groupe left join listeetudiants on groupe.idGroupe=listeetudiants.idGroupe");
        $join_keys = null;
        $db_columns = array('Groupe.idGroupe', 'numGroupe', 'typeGroupe', 'annee', "case when semestre = 1 then 'Paire'
            when semestre = 2 then '&#201;t&#233;' when semestre = 3 then 'Impaire' end as semestre ","count(listeetudiants.idGroupe) as `nbre d'etudiant`");
        $grid_columns = array('Id du groupe', 'Numéro du groupe', 'Type', "Année", 'Semestre','nbre d\'etudiant');
        $result_columns = array('idGroupe', 'numGroupe', 'typeGroupe', 'annee','semestre','nbre d\'etudiant');
        $action = 'supprimer_groupe_cours_action';
        $id_action = null;
        $db_where = "where sigle = '" . $sigle . "' group by 1 order by sigle ";
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, 
                $action, $id_action, $db_where,$result_columns);
//print_r($result);
       $titre = 'Les groupes du module <b>' . $sigle . '.</b>' . ' Sélectionnez le groupe à supprimer.';
        $controlleur = "scolarite";
        $confirmation = "Êtes-vous sûr de vouloir supprimer ce groupe ?";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result, 'confirmation' => $confirmation);
        $this->load->view("recherche_parametree", $data);
    
        
    }

    /*
     * fonction qui modifie le cycle par les valeurs post du formulaire et redirige ver l interface de 
     * voir tous les cycles 
     */
    //Modified 90% by MedBakar 09-04-2020
/*Mab*/
    public function supprimer_groupe_cours_action($idGroupe) {
        //recupe les etudiants inscit dans le groupe
        $query=$this->db->query("SELECT matriculeEtudiant FROM `listeetudiants` where idGroupe like'".$idGroupe."'");
        if($query->num_rows>0){
            $matricules=array();
            foreach($query->result_array() as $row){
                $matricules[]=$row['matriculeEtudiant'];
            }
            
            $type=substr($idGroupe,0,strrpos($idGroupe,'T')+2);// prendre seulement le debut jusqu'au Th,TD ou TP 
           $requete='';
           
//recup les autres groupe du meme type
           $query=$this->db->query("SELECT idGroupe FROM `groupe` where idGroupe like'%".$type."%' and idGroupe not like '".$idGroupe."'");
           if($query->num_rows>0){
               $groupes=array();
               foreach($query->result_array() as $rows){
                   $groupes[]=$rows['idGroupe'];
               }
               $borne_duGroupe=number_format(count($matricules)/count($groupes),0);
               
               $group=0;
                $multiple=1;
                $mat_groupe=array();
    /*Mab*/
          for($cpt=0;$cpt<count($matricules);$cpt++){
             if($cpt==$borne_duGroupe*$multiple && $group<count($groupes)-1){
                 $group++;
                 $multiple++;
             }
            // $groupes[$group][$i]=$matricules[$cpt]['matriculeEtudiant'];
             $requete.=", ($matricules[$cpt],'$groupes[$group]')";
            
         }
         $this->db->where('idGroupe',$idGroupe);
         $this->db->delete('listeetudiants');
//         echo substr($requete,2).'<br>';
//         print_r($groupes);
         $this->scolarite_modele->inscrir_ds_groupe($requete);
           }
        //diviser les etudiant et former la requete d'insertion
        //executer le
            
        }
        $confirmation = $this->scolarite_modele->supprimer_groupe_cours($idGroupe);
        $this->load->view('scolarite/modification_confirme', $confirmation);
    }
//Fin Modif MedBakar
    function selection_annee_horaire() {
		$infoDateAnnee = NULL;
        $this->form_validation->set_rules('annee', 'Numéro du groupe', 'string');
        if ($this->form_validation->run()) {

            $infoDateAnnee = $this->input->post();
            $this->choisir_groupe_horaire($infoDateAnnee);
        } else {
             $data_sigle_cours = $this->scolarite_modele->get_cours_actif_enseignant();
            $data_session_courante = $this->scolarite_modele->get_session_courante();
            
            $data_session_courante = $this->scolarite_modele->get_session_courante();
            $listeCours = $this->scolarite_modele->recuperer_cours();
            if ($listeCours == NULL) {
                $data['typeBox'] = 'warning_box';
                $data['informations'] = 'Veuillez créer au moins un module.';
                $this->load->view('scolarite/modification_confirme', $data);
            } else {
				$data = array('sigleCours' => $data_sigle_cours['sigleCours'],
                    'titre' => $data_sigle_cours['titre'],
                    'annee' => $data_session_courante['annee'][0], 
                    'semestre' => $data_session_courante['semestre'][0]);
                $this->load->view("scolarite/choisir_annee_horaire", $data);
            }
        }
    }

    function choisir_groupe_horaire($infoDateAnnee = '') {
        $idGroupe = NULL;
        $info_sigle_groupe = NULL;
        $this->form_validation->set_rules('idGroupe', 'Numéro du groupe', 'STRING');
        if ($this->form_validation->run() && isset($_POST['idGroupe'])) {
            $idGroupe = $this->input->post();
            if ($idGroupe['idGroupe'] != '') {
                $this->saisir_horaire_cours($idGroupe['idGroupe']);
            }
        } else {
            $info_sigle_groupe = $this->scolarite_modele->recuperer_sigle_groupe_horaire($infoDateAnnee);
            // $idGroupe = $info_sigle_groupe['idGroupe'];
            if ($info_sigle_groupe != NULL) {
                $this->load->view('scolarite/choix_groupe_horaire', $info_sigle_groupe);
            } else {
                $data['typeBox'] = 'warning_box';
                $data['informations'] = 'Il n\'existe aucun groupe pour le module <b>' . $_POST['choixCours'] . ' </b> au semestre <b>' .
                        $this->get_session_nom($_POST['session']) . ' ' . $_POST['date'] . '.</b>';
                $this->load->view('scolarite/modification_confirme', $data);
                // $this->selection_annee_horaire();
            }
        }
    }

    /*
     * fonction pour saisir les horaires des groupes
     */

    function saisir_horaire_cours($groupeHoraire = '') {
        $this->form_validation->set_rules('locale', 'locale ', 'String');
        if (($this->form_validation->run()) && isset($_POST['locale']) && isset($_POST['horaire'])) {

            $infoHoraire = $this->input->post();
            foreach ($infoHoraire['locale'] as $loc) {
                $activeLoc[] = $loc;
            }
            foreach ($infoHoraire['horaire'] as $info) {
                $data_horaire[] = explode(".", $info);
            }

            foreach ($data_horaire as $infH) {

                $local_periode[] = array('idGroupe' => $infoHoraire['idGroupe'], 'idPeriode' => ($infH[1] - 1 ) * 14 + ($infH[2] - 7),
                    'idLocal' => $activeLoc[$infH[0]]);
            }


            $this->scolarite_modele->cree_nouveau_horaire($local_periode);
            $data['typeBox'] = 'valid_box';
            // Correction RM 27 février 2013 $data['informations'] = 'L\'horaire du groupe <b>'.$_POST['idGroupe']. 
               $data['informations'] = 'L\'horaire du groupe <b>'.$this->scolarite_modele->corrigerNumGroupe($_POST['idGroupe']). 
			   ' </b>a bien été enregistré';
            $this->load->view('scolarite/modification_confirme', $data);
        } else {
            $periodeGroupeExistant['detailHoraire'][] = $this->scolarite_modele->get_periode_groupe($groupeHoraire);
            $info_periode = $this->scolarite_modele->recuperer_periode();
            $info_salle = $this->scolarite_modele->recuperer_salle();
            $guideInformatif = '<div class="warning_box">Veuillez choisir au moins une période pour enregistrer 
                                        l\'horaire. </div>';
            $data_idGroupe_periode = array('sigleGroupe' => $_POST['idGroupe'], 'local' => $info_salle['idLocal'],
                'periodeCoche' => $periodeGroupeExistant, 'information' => $guideInformatif);
            $this->load->view("scolarite/creer_horaire_cours", $data_idGroupe_periode);
        }
    }

    /*
     * fonction pour la consultation des horaires de cours 
     */

    function consulter_horaire_cours() {
        $this->form_validation->set_rules('choixCours', 'choix de cours', 'required');
        if ($this->form_validation->run()) {
            $data['sigle'] = $this->input->post('choixCours');
            $data['horaires_disponibles'] = $this->scolarite_modele->get_horaires_dispo($data['sigle']);
print_r($data);
            $this->load->view('scolarite/choix_horaire', $data);
        } else {
            $listeCours = $this->scolarite_modele->recuperer_cours();
                         $data_sigle_cours = $this->scolarite_modele->get_cours_actif_enseignant();
            $data_session_courante = $this->scolarite_modele->get_session_courante();
            if ($listeCours == NULL) {
                $data['typeBox'] = 'warning_box';
                $data['informations'] = 'Veuillez créer au moins un module.';
                $this->load->view('scolarite/modification_confirme', $data);
            } else {
                
                $data = array('sigleCours' => $data_sigle_cours['sigleCours'],
                    'titre' => $data_sigle_cours['titre'],
                    'annee' => $data_session_courante['annee'][0], 
                    'semestre' => $data_session_courante['semestre'][0]);
                $this->load->view('scolarite/choix_cours', $data);
            }
        }
    }

    /*
     * fonction pour savoir si l'année est bissextile ou non
     * If Mod(An,400)= 0  then Bissextile = True
      else	if Mod(An,100) = 0 then Bissextile = False
      else	if Mod(An,4) = 0 then Bissextile = True
      else Bissextile = False.

     */

    function bissextile($annee) {
        $bissextile = FALSE;
        if (($annee % 400) == 0) {
            $bissextile = TRUE;
        } elseif (($annee % 100) == 0) {
            $bissextile = FALSE;
        } elseif (($annee % 4) == 0) {
            $bissextile = TRUE;
        } else {
            $bissextile = FALSE;
        }
        return $bissextile;
    }

    /*
     * fonction pour valider la date de naissance
     */

    function valider_date_naissance($annee, $mois, $jour) {
        $messageRetour = 'valide';
        $dateAujourdhui = strftime("%Y");
        $ageAdmission = 16;
        $agelimite = 40;
        $age = $dateAujourdhui - $annee;
        $jourDeChaqueMois = array(31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if ($age < 14 || $age > 30) {
            $messageRetour = 'Année de naissance erronée, l\'étudiant aurait <b>' . $age . ' </b>ans  ';
        } elseif ($annee < 1950 || $annee > 2100 || $annee == 0 ) {
            $messageRetour = 'Date de naissance invalide : seules les années de 1950 à 2100 sont acceptées';
        } elseif ($mois > 12) {
            $messageRetour = 'Date de naissance invalide : une année n\'a pas plus de 12 mois';
        } elseif ($jour > 31) {
            $messageRetour = 'Date de naissance invalide : un mois n\'a pas plus de 31 jours';
        }
        if($jour == 0)
        {
            $messageRetour = 'Date de naissance invalide : le jour de naissance est incorrect.';
            return $messageRetour;
        }
        if($mois == 0)
        {
            $messageRetour = 'Date de naissance invalide : le mois de naissance est incorrect.';
            return $messageRetour;
        }
        else
        {
            if ($mois < 13) 
            {
                if ($jour > $jourDeChaqueMois[$mois - 1]) {
                    $messageRetour = 'Date de naissance invalide : <b>' . $mois . '</b> est un mois de <b>' . $jourDeChaqueMois[$mois - 1] . '</b> jours';
                }
            }
            if ($mois == 2 && ($this->bissextile($annee) == FALSE) && $jour > 28) {
                $messageRetour = 'Date de naissance invalide : <b>' . $annee . '</b> n\'est pas bissextile, il n\'y a que 28 jours en février  <b>' . $annee . '</b>';
            }
        }

        return $messageRetour;
    }

    function valider_NIN($nin) {
        if ((is_numeric($nin) && strlen($nin) < 11) || strlen($nin) == 0) {
            $messageRetour = 'valide';
        } else {
            $messageRetour = 'nonValide';
        }
        return $messageRetour;
    }

    function GetValidDate($year = 1970, $month = 1, $day = 1) {
        //** provide a default value and convert the day of the month given. Day
        //** cannot be less than 1 or greater than 31.

        $day = min(31, max(1, intval($day)));

        //** provide a default value and convert the month number given. Month cannot
        //** be less than 1 or greater than 12.

        $month = min(12, max(1, intval($month)));

        //** provide a default value and convert the day of the year number given.

        $year = max(1970, intval($year));

        //** handle compensating for day runover if the number of days selected is
        //** greater than those allowable for the selected month.

        switch ($month) {
            //** if FEBRUARY the selected day must be corrected and a leap year must be
            //** acounted for as well.
            //** CHANGED JAN 26/2004 - corrected for leap year error.

            case 2 :
                if ($day > 28)
                    $day = ($year % 4 == 0 && ($year % 100 != 0 || $year % 400 == 0)) ? 29 : 28;
                break;

            //** only maximum of thirty days in these months.

            case 4 : //** APRIL
            case 6 : //** JUNE
            case 9 : //** SEPTEMBER
            case 11 : //** NOVEMBER

                $day = min(30, $day);
                break;
        }
        //** construct the appropriate date timestamp from the parameters provided.

        return mktime(0, 0, 0, $month, $day, $year);
    }

    function saisir_adresse() {
        $infos['idProgramme'] = $_POST['idProgramme'];
        $infos['serie'] = $_POST['serie'];
        $infos['num_bac'] = $_POST['num_bac'];
        $infos['annee'] = $_POST['annee'];
        $infos['personne_ressource'] = $_POST['personne_ressource'];
        $infos['contacts'] = $_POST['contacts'];
        $infos['prenomPere'] = $_POST['prenomPere'];
        $infos['prenomPereArabic'] = $_POST['prenomPereArabic'];
        $this->form_validation->set_rules('email', 'email', 'valid_email');
        $this->form_validation->set_rules('lastName', 'Nom', 'required');
		$this->form_validation->set_rules('firstName', 'Prénom', 'required');
        $ninIsValid = $this->valider_NIN($_POST['nin']);
        $phoneValid = $this->valider_phone($_POST['emergencyPhone']);
        $data = $_POST;
        $messageRetour = $this->valider_date_naissance($_POST['year'], $_POST['month'], $_POST['day']);
        if ($this->form_validation->run() && $messageRetour == 'valide' && $ninIsValid == 'valide' && $phoneValid) {
            $infos['infos_perso'] = $_POST;
            $this->load->view('scolarite/saisir_adresse', $infos);
        } else {
            if ($messageRetour != 'valide') {
                unset($data['year']);
                unset($data['month']);
                unset($data['day']);
                $data['messageRetour'] = '<div class="error_box">' . $messageRetour . '</div>';
            }
            if ($ninIsValid == 'nonValide') {
                unset($data['nin']);
                $data['messageRetourNin'] = '<div class="error_box">Le numéro d\'identité nationale n\'est pas valide.</div>';
            }
            if (!$phoneValid) {
                unset($data['emergencyPhone']);
                $data['messageRetourEmergencyPhone'] = '<div class="error_box">Le numéro de téléphone du contact en cas d\'urgence n\'est pas valide.</div>';
            }
			$this->load->view('scolarite/ajouter_etudiant', $data);
        }
    }

    /*
     * fonction pour convertir le code du semestre par son nom 
     * elle prend en paramètre le numéro du semestre soit(1,2,3)
     * et elle retourne le nom (Automne,ete,printemps)
     */

    function get_session_nom($numeroSemestre) 
    {
        if ($numeroSemestre == 3) 
        {
            return 'Automne';
        } 
        elseif ($numeroSemestre == 2) 
        {
            return 'Été';
        } 
        else 
        {
            return 'Printemps';
        }
    }
    
    function generer_excel_liste_enseingant()
    {
        $nomFichier = '';
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $date = utf8_encode(strftime("%d-%B-%Y"));
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0);
        /// formatage du Titre de la liste.
        $objSheet->getDefaultStyle()->getFont()->setName('Courier New');
        $objSheet->setCellValue('A1', 'INSTITUT SUPERIEUR DES METIERS DE LA STATISTIQUE');
        $objSheet->getStyle('A1')->getFont()->setBold(TRUE);
   
        $nomFichier = 'Enseignants_du_departement_'.$_POST['departement'].'_'.$date;
            $objSheet->setCellValue('A3', 'Personnel actif de : '.
                    $this->scolarite_modele->get_departement_nom($_POST['departement']));
            $objSheet->setCellValue('A4','Liste produite le '.$date);
        
            $objSheet->setCellValue('C6','Matricule');
            $objSheet->setCellValue('D6','Nom');
            $objSheet->setCellValue('E6','Prénom');
            $objSheet->setCellValue('F6','Code d\'accès');
            
            for($i=0;$i<count($_POST['matriculeEmploye']);$i++)
            {
                $objSheet->setCellValueByColumnAndRow(2,7+$i,$_POST['matriculeEmploye'][$i]);
                $objSheet->setCellValueByColumnAndRow(3,7+$i,$_POST['nom'][$i]);
                $objSheet->setCellValueByColumnAndRow(4,7+$i,$_POST['prenom'][$i]);
                $objSheet->setCellValueByColumnAndRow(5,7+$i,$_POST['login'][$i]);
            }
            for($i=2;$i<6;$i++)
            {
                $objSheet->getStyleByColumnAndRow($i,6)->getFont()->setBold(TRUE);
                for($j=0;$j<=count($_POST['matriculeEmploye']);$j++)
                {
                    $objSheet->getStyleByColumnAndRow( $i,$j+6)->getBorders()->applyFromArray(
                       array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
                }
            }
            

        /////////////////////////////////////////////////////////////////////////////////////////////////   
        $objXLS->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
        $objXLS->getActiveSheet()->setTitle('Liste_enseignants');
        $objXLS->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');

        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        $objWriter->save('php://output');
    }
    
    function generer_excel_liste_enseingant_module()
    {
         $nomFichier = '';
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $date = utf8_encode(strftime("%d-%B-%Y"));
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0);
        /// formattage du Titre de la liste.
        $objSheet->getDefaultStyle()->getFont()->setName('Courier New');
        $objSheet->setCellValue('A1', 'INSTITUT UNIVERSITAIRE PROFESSIONNEL');
        $objSheet->getStyle('A1')->getFont()->setBold(TRUE);
   
        
            $objSheet->setCellValue('A3', 'Liste des enseignants du module '.$_POST['sigle'][0]);
            $objSheet->setCellValue('A4','Liste produite le '.$date);
        
            $objSheet->setCellValue('C6','Matricule');
            $objSheet->setCellValue('D6','Nom');
            $objSheet->setCellValue('E6','Prénom');
            $objSheet->setCellValue('F6','Sigle');
            $objSheet->setCellValue('G6','Année');
            $objSheet->setCellValue('H6','Semestre');
            
            for($i=0;$i<count($_POST['matriculeEmploye']);$i++)
            {
                $objSheet->setCellValueByColumnAndRow(2,7+$i,$_POST['matriculeEmploye'][$i]);
                $objSheet->setCellValueByColumnAndRow(3,7+$i,$_POST['nom'][$i]);
                $objSheet->setCellValueByColumnAndRow(4,7+$i,$_POST['prenom'][$i]);
                $objSheet->setCellValueByColumnAndRow(5,7+$i,$_POST['sigle'][$i]);
                $objSheet->setCellValueByColumnAndRow(6,7+$i,$_POST['annee'][$i]);
                $objSheet->setCellValueByColumnAndRow(7,7+$i,$_POST['semestre'][$i]);
                $nomFichier = 'Enseignants_'.$_POST['sigle'][$i].'_'.$date;
            }
            for($i=2;$i<8;$i++)
            {
                $objSheet->getStyleByColumnAndRow($i,6)->getFont()->setBold(TRUE);
                for($j=0;$j<=count($_POST['matriculeEmploye']);$j++)
                {
                    $objSheet->getStyleByColumnAndRow( $i,$j+6)->getBorders()->applyFromArray(
                       array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
                }
            }
            

        /////////////////////////////////////////////////////////////////////////////////////////////////   
        $objXLS->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
        $objXLS->getActiveSheet()->setTitle('Liste_enseignants');
        $objXLS->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');

        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        $objWriter->save('php://output');
    }

    /*
     * fonction qui est chargée de la consultation des horaires pour les cours 
     */

    function afficher_horaire_cours() {

        $this->form_validation->set_rules('date', 'L\'année et le semestre', 'required');
        $this->form_validation->set_rules('sigle', 'sigle', 'required');
        if ($this->form_validation->run()) {

            $date = explode("-", $this->input->post('date'));
            $sigle = $this->input->post('sigle');

            switch ($date[0]) {
                case '3':
                    $semestre = "Automne ";
                    break;
                case '2':
                    $semestre = "Été ";
                    break;
                case '1':
                    $semestre = "Printemps ";
                    break;
            }


            $periodeGroupe = NULL;
            $periodeGroupe['titre'] = "Horaire du module " . $sigle . " : " . $semestre . $date[1];
            $periodeGroupe['detailHoraire'] = NULL;
            $dataIdGroupe = $this->scolarite_modele->get_idGroupe_anneeCourante($sigle, $date[0], $date[1]);

            if (is_array($dataIdGroupe)) {
                foreach ($dataIdGroupe['idGroupe'] as $idG) {
                    $periodeGroupe['detailHoraire'][] = $this->scolarite_modele->get_periode_groupe($idG);
                }
            }
            $this->load->view('scolarite/consulter_horaire_cours', $periodeGroupe);
        } else {
            $this->load->view('scolarite/index');
        }
    }

    /*
     * fonction pour valider les numeros de telephone.
     */

    function valider_phone($phone) {
        $caractereValide = array('+', '-', '/', '\\', '(', ')', ' ', '');
        if ($phone == NULL) {
            return TRUE;
        } else {
            for ($i = 0; $i < strlen($phone); $i++) {
                if (!is_numeric($phone[$i]) && !in_array($phone[$i], $caractereValide)) {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    /*
     * premiere fontion pour l'ajout de l etudiant .
     * il fait appel a la vue principale pour ajouter etudiant(nom, prenom, date de naissance ...).
     */

    function ajouter_etudiant($annee,$num_bac) {
       $infos['info'] = $this->scolarite_modele->get_infoEleve($annee,$num_bac);
       while($infos['info']==null){
           $annee=$annee-1;
           $infos['info'] = $this->scolarite_modele->get_infoEleve($annee-1,$num_bac);
       }
        $control_autorisation = $this->scolarite_modele->control_autorisation($annee,$num_bac);
          
        //echo(json_encode($control_autorisation));

       $infoss = $this->scolarite_modele->get_matricule_Et($num_bac,$annee);
       if ($infoss!=null) {
           echo"Etudiant deja inscrit, son maricule ".$infoss['matriculeEtudiant'];
       }elseif ($control_autorisation=="") {
            $this->load->view('scolarite/ajouter_etudiant',$infos);
              }else{
          echo"Attention !! cet étudiant ".$num_bac ." n'est parmi les admis en Bac RIM ".$annee." ";
    
       }
       
       
    }
/*
 function ajouter_etudiant($num_bac) {
       $infos['info'] = $this->scolarite_modele->get_infoEleve($num_bac);
       
     
       $infoss = $this->scolarite_modele->get_matricule_Et($num_bac);
       if ($infoss!=null) {
           echo"Etudiant deja inscrit, son maricule ".$infoss['matriculeEtudiant'];
       }else{
           
           $this->load->view('scolarite/ajouter_etudiant',$infos);
       }
       
       
    } 
 */
    /*
     * fonction pour qui fait appel a la troisieme interface pour ajouter un etudiant 
     * et appelle l interface pour saisir les informations de bac.
     */

    function saisir_infos_bac() 
    {
        $infos['idProgramme'] = $_POST['idProgramme'];
        $infos['serie'] = $_POST['serie'];
        $infos['num_bac'] = $_POST['num_bac'];
        $infos['annee'] = $_POST['annee'];
        $infos['infos_perso'] = $_POST;
        $infos['messageRetour'] = '';
        $errorMessage = '';
        $errorphone2 = false;
        $errorphoneP = false;
        $infos['infos_perso']['programme'] = $this->scolarite_modele->get_programme();
        $infos['infos_perso']['grade'] = $this->scolarite_modele->get_grade();
        if ($this->valider_phone($_POST['phone'])) 
        {
            if ($_POST['phone2'] != NULL) 
            {
                if (!$this->valider_phone($_POST['phone2'])) 
                {
                    unset($infos['infos_perso']['phone2']);
                    $errorMessage .= ' Le deuxième numéro de téléphone n\'est pas valide.';
                    $errorphone2 = TRUE;
                }
            }
            if ($_POST['phoneP'] != NULL) // telephone des parents
            {
                if (!$this->valider_phone($_POST['phoneP'])) 
                {
                    unset($infos['infos_perso']['phoneP']);
                    $errorphoneP = TRUE;
                    $errorMessage .= ' Le numéro de téléphone des parents n\'est
                        pas valide. \n';
                }
            }
            if ($errorphone2 || $errorphoneP) 
            {
                $infos['messageRetour'] = '<div class="error_box">' . 
                        $errorMessage . ' Les numéros de téléphone ne peuvent  
                            contenir que des chiffres et les caractères suivants :
                            + - / \ ( )</div>';

                if ($errorphone2)
                    unset($infos['infos_perso']['phone2']);

                if ($errorphoneP)
                    unset($infos['infos_perso']['phoneP']);
                $this->load->view('scolarite/saisir_adresse', $infos);
            }
            else 
            {
                $infos['etablissement'] = $this->scolarite_modele->get_etablissement();
                $this->load->view('scolarite/saisir_infos_bac', $infos);
            }
        } 
        else 
        {
            $infos['messageRetour'] = '<div class="error_box">Les numéros de téléphone ne peuvent contenir que des chiffres et les caractères suivants : + - / \ ( )</div>';
            unset($infos['infos_perso']['phone']);
            $this->load->view('scolarite/saisir_adresse', $infos);
        }
    }

    /*
     * la derniere fonction qui ajoute l etudiant dans la base de donnee en cas de succes 
     * en cas de probleme l utilisateur doit corriger ses entrees
     */

    function generer_infos() {
        $validation = true;
        if (isset($_POST['yearD'])) {
            if ($_POST['yearD'] != 'AAAA')
                $validation = false;
        }
        $this->form_validation->set_rules('yearD', 'Annee', 'greater_than[2004]|less_than[2051]');
        
        if ($this->form_validation->run() || $validation) {
            //si la moyenne est envoyée il faut la valider si c'est null on n'a pas besoin de la valider 
            if ($this->isValide($_POST['moyenne']) || $_POST['moyenne'] == Null) {
                if ($_POST['moyenne'] == NULL) {
                    $_POST['moyenne'] = 0;
                } else {
                    $_POST['moyenne'] = $this->isValide($_POST['moyenne']);
                }
                $matricule = $this->scolarite_modele->generer_matriculeIUP();
                
                
                $login = $this->scolarite_modele->generer_login($this->input->post('firstName'), $this->input->post('lastName'));
                $pass = $this->scolarite_modele->generer_mdp();
				$infos['infos_perso'] = $_POST;
				$infos['infos_perso']['lastName'] = trim($infos['infos_perso']['lastName']); // 2.2.1 enlever les espaces inutiles
				$infos['infos_perso']['firstName'] = trim($infos['infos_perso']['firstName']); // 2.2.1 enlever les espaces inutiles
				$infos['infos_perso']['surnom'] = trim($infos['infos_perso']['surnom']); // 2.2.1 enlever les espaces inutiles
                                $infos['infos_perso']['prenomPere'] = trim($infos['infos_perso']['prenomPere']); // 2.2.1 enlever les espaces inutiles
                                $infos['infos_perso']['prenomPereArabic'] = trim($infos['infos_perso']['prenomPereArabic']); // 2.2.1 enlever les espaces inutiles
                $infos['infos_perso']['code'] = $matricule;
                $infos['infos_perso']['accessCode'] = $login;
                $infos['infos_perso']['pass'] = $pass;
                // Modif Cheikh  24/11/2015 pour les nom et prenom en arabe
                $infos['infos_perso']['lastNameArabic'] = trim($infos['infos_perso']['lastNameArabic']);
                $infos['infos_perso']['firstNameArabic'] = trim($infos['infos_perso']['firstNameArabic']);
$infos['infos_perso']['created_by']=$this->session->userdata('login');
                $this->scolarite_modele->inscrire_etudiant($infos['infos_perso'], $matricule);

                //Photo
                
                 $target_dir = "../../photos/";
       $file=$target_dir.'default.gif';
      

$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
//echo $target_file;
$image_info = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
$image_width = $image_info[0];
$image_height = $image_info[1];

//echo "width : ".$image_width.'<br>';
//echo "height : ".$image_height.'<br>';

/* if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $_SERVER['DOCUMENT_ROOT']."iup/photos/ ".$matricule.'.gif')) {
        //echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
       */         
                //
                $data['code'] = $matricule;
                $data['accessCode'] = $login;
                $data['pass'] = $pass;
                $data['inscrireStudent'] = 'inscrire';
                $data['typeBox'] = 'valid_box';
                $data['informations'] = 'L\'étudiant a été inscrit avec succès dans le programme <b>' . $this->scolarite_modele->get_programme_nom($_POST['idProgramme']) . '. </b>';
                $this->load->view('scolarite/infos_etudiant', $data);
            } 
            else 
            {
                unset($_POST['moyenne']);
                $infos['infos_perso'] = $_POST;
                $infos['programme'] = $this->scolarite_modele->get_programme();
                $infos['grade'] = $this->scolarite_modele->get_grade();
                $infos['etablissement'] = $this->scolarite_modele->get_etablissement();
                $this->load->view('scolarite/saisir_infos_bac', $infos);
            }
        } 
        else 
        {

            unset($_POST['yearD']);
            unset($_POST['dayD']);
            unset($_POST['monthD']);
            $infos['infos_perso'] = $_POST;
            $infos['etablissement'] = $this->scolarite_modele->get_etablissement();

            $this->load->view('scolarite/saisir_infos_bac', $infos);
        }
    }

    function inscrire_etudiant() {
        $this->scolarite_modele->inscrire_etudiant($_POST);
        $data['inscrireStudent'] = 'inscrire';
        $data['typeBox'] = 'valid_box';
        $data['informations'] = 'L\'étudiant a bien été inscrit.';
        $this->load->view('scolarite/modification_confirme', $data);
    }

    public function mot_de_passe() {
        $data['errorMessage'] = '';
        $this->load->view('scolarite/modifier_mot_de_passe', $data);
    }

	// Ajout 2.2.1 pour afficher les détails du semestre courant
	public function semestre_courant() {
        $data['courant'] = $this->scolarite_modele->get_details_semestre_courant();
		$Annee = $data['courant']['annee'][0]; 
		$Semestre = $data['courant']['semestre'][0];
		$DebutCours = $data['courant']['debutCours'][0];
		$FinCours = $data['courant']['finCours'][0];
		// print('>' . $Annee . '-' . $Semestre . '=' . $DebutCours . '+' . $FinCours); die;
		$this->load->view('scolarite/afficher_semestre_courant', $data);
    }	

	
    /*
     * fonction qui valide le mot de passe lors de sa modification
     */

    function password_check($password) {
        if (
                ctype_alnum($password) // numbers & digits only 
                && strlen($password) > 7 // at least 8 chars 
                && strlen($password) < 11 // at most 20 chars 
                && ((preg_match('`[a-z]`', $password) || (preg_match('`[A-Z]`', $password))) )
                && preg_match('`[0-9]`', $password) // at least one digit 
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /*
     * fonction qui modifie le mot de passe et le valide 
     * en cas d erreur elle redirige vers des pages d erreurs
     */

    public function modifier_mot_de_passe() 
    {
        if ($this->scolarite_modele->get_old_password($this->session->userdata('login')) == $this->input->post('old_password')) 
        {
            $passwordIsValid = $this->password_check($this->input->post('new_password'));
            if ($passwordIsValid == TRUE) 
            {
                if ($this->input->post('new_password') == $this->input->post('confirmed_password')) 
                {
                    $this->scolarite_modele->set_password($this->input->post('new_password'), $this->session->userdata('login'));
                    $data['typeBox'] = 'valid_box';
                    $data['informations'] = 'Le mot de passe a été modifié avec succès.';
                    $this->load->view('scolarite/modification_confirme', $data);
                } 
                else 
                {
                    $data['errorMessage'] = '<div class="error_box">
                            Les deux nouveaux mots de passe ne sont pas identiques.
                            </div>';
                    $this->load->view('scolarite/modifier_mot_de_passe', $data);
                }
            } 
            else 
            {
                $data['errorMessage'] = '<div class="error_box">
                        Le mot de passe doit contenir 8 à 10 caractères dont au moins une lettre et un chiffre.
                        </div>';
                $this->load->view('scolarite/modifier_mot_de_passe', $data);
            }
        } 
        else 
        {
            $data['errorMessage'] = '<div class="error_box">
                       L\'ancien mot de passe est erroné.
                        </div>';
            $this->load->view('scolarite/modifier_mot_de_passe', $data);
        }
    }

    /*
     * Cette methode permet d'appeler la recherche parametrisable d'un etudiant
     *
     * @param - to_do_action: page vers laquelle aller lorsqu'on clique sur un element
     *        - id_action: specifie le parametre a envoyer apres la selection d'un element
     */

    function rechercher_etudiant($to_do_action, $id_action) 
    {
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
//        $action = $to_do_action;
        $id_action='matriculeEtudiant';
         $action = $to_do_action.$id_action[18];
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Consulter les informations d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    }

    /*
     * Fonction appeler pour trouver l'etudiant a consulter
     *
     */

    function trouver_etudiant_a_consulter() 
    {
        $this->rechercher_etudiant('consulter_etudiant', 'login');
   
    }
    
    /*
 * Choix de la lague de l'attesation
 * 
 */
     function choix_annee_attestation()
    {
        $titre = 'Choix de l\'annee attesation';
        $controlleur = "scolarite";
        $annes=$this->scolarite_modele->getAnnee();
        $data = array('titre' => $titre, 'controlleur' => $controlleur,'annee'=>$annes);

        
        $this->load->view("scolarite/choix_annee_attestation", $data);
    }
    function choix_langue_attestation()
    {
        $titre = 'Choix de la langue pour l\'attesation';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur);

        
        $this->load->view("scolarite/choix_langue_attestation", $data);
    }
     function choix_langue_attestation_d()
    {
        $titre = 'Choix de la langue pour l\'attesation';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur);

        
        $this->load->view("scolarite/choix_langue_attestation_d", $data);
    }
    /*
     * Fait appel a la vue de consultation d'un etudiant.
     * @param -id : le parametre avec lequel identifier l'etudiant selectionne
     */

    function consulter_etudiant($id) 
    {
//        echo"<br>MED: $id<br>";
        $informations_etudiant = $this->scolarite_modele->get_informations($id);
//        print_r($informations_etudiant);
        $this->load->view("scolarite/consulter_info_personnelle_etudiant", $informations_etudiant);
    }
//
//    function trouver_module_a_supprimer() 
//    {
//        
//        $tables = array("module","employe");
//        $join_keys = array('module.professeurResponsable=employe.matriculeEmploye');
//        $db_columns = array('sigle', 'typeModule', 'titre',"concat('employe.prenom',' ','employe.nom') as EnsRespo");
//        $db_result = array('sigle','typeModule', 'titre','EnsRespo');
//        $grid_columns = array('Sigle', 'Type d\'élément', 'Titre d\'élément','Enseignent responsable');
//        $action = 'supprimer_module';
//        $id_action = 'sigle';
//
//        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action);
//
//        $titre = 'Supprimer un élément ';
//        $controlleur = "scolarite";
//        $confirmation ='Êtes vous sûr de vouloir supprimer cette élément? ';
//        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result,'confirmation'=>$confirmation);
//        $this->load->view("recherche_parametree", $data);
//    }
    //----------------------------
    function trouver_module_a_supprimer() {//add & modified by MedBakar 15-03-2020
        // Sert à Consulter module et à Modifier module.
		$tables = array("module", "departement", "cycle","employe");
        $join_keys = array('module.idDepartement = departement.idDepartement', 'module.idCycle = cycle.idCycle','module.professeurResponsable=employe.matriculeEmploye');   
        $db_columns = array('sigle','typeModule', 'titre',"concat(employe.prenom,' ',employe.nom) as EnsRespo");
        $db_result = array('sigle','typeModule', 'titre','EnsRespo');
        $grid_columns = array('Sigle', 'Type d\'élément', 'Titre d\'élément','Enseignent responsable');
        $db_order = 'order by sigle';
         $action = 'supprimer_module';
        $id_action = 'sigle';

        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $db_order, $db_result);
	
        $titre = 'Supprimer un élément ';
        $controlleur = "scolarite";
        $confirmation ='Êtes vous sûr de vouloir supprimer cette élément? ';
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result,'confirmation'=>$confirmation);
        $this->load->view("recherche_parametree", $data);
    }
    //-----------

    function supprimer_module($sigle) 
    {
        $confirmation = $this->scolarite_modele->supprimer_module($sigle);
        $this->load->view('scolarite/modification_confirme', $confirmation);
    }

    function valider_module($programme,$cycle,$departement)
    {
        $messageRetour = '';
        if($departement == 'DPT-SRV')
        {
            $messageRetour = 'Un module ne peut pas être associé au Service administratif.';
        }
        if(($programme == 'PROG-CP' && $cycle != 1) ||
                ($programme != 'PROG-CP' && $cycle == 1))
        {
            $messageRetour ='Le programme préparatoire ne peut être associé 
                qu\'au Cycle préparatoire.';
        }
        if(($programme == 'PROG-CP' && $departement != 'DPT-CPI') ||
                ($programme != 'PROG-CP' && $departement == 'DPT-CPI'))
        {
            $messageRetour = 'Le programme préparatoire ne peut être associé qu\'au
                département Cycle préparatoire.';
        }
        return $messageRetour;
    }

    function module_check($module)
    {
       if(!preg_match('/[^0-9A-Za-z_-]/',$module))
        {
            return TRUE;
        }
        else
        {
            $this->form_validation->set_message('module_check', 'Erreur : le Sigle d\'un module ne
                peut contenir que des lettres, des chiffres, et les caractères - et _');
            return FALSE;
        }
    }
    /*
     * cette fonction est la responsable de la verification 
     * et la validation du formulaire d'ajout du module ainsi
     * que l ajout du module dans la BD
     */

    function creer_module() 
    {
        //ligne suivante uniquement pour bug internet explorer
        $this->form_validation->set_rules('sigle', '<b>Sigle du module</b>', 'required|max_length[10]|alpha_dash');
        $this->form_validation->set_rules('titreMod', '<b>Titre du Module</b>' ,'max_length[100]');
        $quadruplet = '';
        if ($_POST != NULL) 
        {
           // $hrTP = $this->isValide($_POST['hrTP']);
           // $hrCours = $this->isValide($_POST['hrCours']);
           // $hrTD = $this->isValide($_POST['hrTD']);
            $hrPerso = $this->isValide($_POST['hrPerso']);
            //Debut Modif Cheikh 26/11/2015
            $volumeCM = $this->isValide($_POST['volumeCM']);
            $volumeTD = $this->isValide($_POST['volumeTD']);
            $volumeTP = $this->isValide($_POST['volumeTP']);
            
            //Fin Modif Cheikh 26/11/2015
	
			//$quadruplet = $hrTP + $hrCours + $hrTD + $hrPerso;
        }
        $this->form_validation->set_rules('nbrCredits', '<b>Nombre de credits</b>', 'integer');
         $this->form_validation->set_rules('coefficient', '<b>le Coefficient</b>', 'integer');
        //$this->form_validation->set_rules('nbrCredits', '<b>Correspondance crédits et 
	//		quadruplet horaire</b>', 'callback_verifier_credits_et_quadruplet[' . $quadruplet . ']');
        if ($this->form_validation->run()) 
        {
            $infoModule = $this->input->post();
            //print_r($infoModule);
            $data=$this->scolarite_modele->creer_nouveau_module($infoModule);
            if (!$data['valide']) 
            {
                $data['typeBox'] = 'error_box';
            } 
            else 
            {
                $data['typeBox'] = 'valid_box';
            }
            $this->load->view('scolarite/modification_confirme', $data);
           
        } 
        else 
        {
           
            $info_departement = $this->scolarite_modele->recuperer_departement();
            $data_cycle = $this->scolarite_modele->recuperer_cycle();
            $professeurs = $this->scolarite_modele->get_professeurs();
            $unites = $this->scolarite_modele->get_unites();
            if ($professeurs == NULL || $unites==NULL) {
                $data['typeBox'] = 'warning_box';
                if($professeurs == NULL)
                {
                $data['informations'] = 'Veuillez créer au moins un enseignant.';
                $this->load->view('scolarite/modification_confirme', $data);
                }
                else
                {
                $data['informations'] = 'Veuillez créer au moins un module.';
                $this->load->view('scolarite/modification_confirme', $data);
                }
            } 
            else {
                
                $data_dep_cycle = array('cycle' => $data_cycle['idCycle'],
                    'nomCycle' => $data_cycle['nomCycle'], 'idDep' => $info_departement['idDepartement'],
                    'nomDep' => $info_departement['nomDep'], 'professeurs' => $professeurs, 'unites'=>$unites);
///print_r( $data_dep_cycle['idDep'][1]);
                $this->load->view("scolarite/ajouter_module", $data_dep_cycle);
            }
        }
    }

    /*
     * fonction qui valide le quadruplet nombre d heures avec le nombre
     * de credit du module, heure theorie, td, tp, perso.
     */

    function verifier_credits_et_quadruplet($nbrCredits, $quadruplet) 
    {
        if((int)$nbrCredits == $nbrCredits)
        {
			if 	((($nbrCredits*1.5) == $quadruplet) && $nbrCredits<=60) 	
            {
                return TRUE;
            } 
            else 
            {
                if (is_numeric($nbrCredits))
                    $this->form_validation->set_message('verifier_credits_et_quadruplet', 'La correspondance entre le nombre de crédits et le quadruplet horaire n\'est pas correcte.');
                else
                    $this->form_validation->set_message('verifier_credits_et_quadruplet', 'Le nombre de crédits doit être une valeur numérique.');
                return FALSE;
            } 
        }
        else
        {
             $this->form_validation->set_message('verifier_credits_et_quadruplet', 'Le nombre de crédits doit être un entier.');
             return FALSE;
        }
        
    }

    /*
     * fonction qui ajoute un module et retourne le succes ou l'echec 
     * de l'insertion
     */

    function inserer_module() 
    {
        if (is_array($this->input->post('progs'))) 
        {
            $infoModule = $this->input->post();
      //      echo"<br>inserer_module<br>";
//print_r( $infoModule);
            $data[] =''; //$this->scolarite_modele->creer_nouveau_module($infoModule);
            if (!$data['valide']) 
            {
                $data['typeBox'] = 'error_box';
            } 
            else 
            {
                $data['typeBox'] = 'valid_box';
            }
          //  $this->load->view('scolarite/modification_confirme', $data);
        } 
        else 
        {
            $data['warning'] = 'Vous devez obligatoirement affecter ce module à un programme.';
            $data['hidden'] = $this->input->post();
            $data['programme'] = $this->scolarite_modele->get_programme();
            $this->load->view('scolarite/selection_programmes', $data);
        }
    }

    /*
     * Cette methode permet d'appeler la recherche parametrisable d'un module
     *
     * @param - to_do_action: page vers laquelle aller lorsqu'on clique sur un element
     *        - id_action: specifie le parametre a envoyer apres la selection d'un element
     */

    function rechercher_module($to_do_action, $id_action) {
        // Sert à Consulter module et à Modifier module.
		$tables = array("module", "departement", "cycle","employe");
        $join_keys = array('module.idDepartement = departement.idDepartement', 'module.idCycle = cycle.idCycle','module.professeurResponsable=employe.matriculeEmploye');
       // $db_columns = array('sigle', 'titre', 'departement.nom as nomDep', 'cycle.nom as nomCycle');
       // $db_result = array('sigle', 'titre', 'nomDep', 'nomCycle');
        //$grid_columns = array('Code', 'Intitulé', 'Département', 'Cycle');
         $db_columns = array('sigle', 'titre','module.nbCredits as Credit','coefficient as Coef','volumeCM','volumeTD','volumeTP','hrsPerso as volumeProjet','hrsPerso+volumeCM+volumeTD+volumeTP as vSem',"concat(employe.prenom,' ',employe.nom) as EnsRespo");
        $db_result = array('sigle', 'titre','Credit' ,'Coef','volumeCM','volumeTD','volumeTP','volumeProjet','vSem','EnsRespo');
        $grid_columns = array('Code', 'Intitulé', 'Credit','Coef','volume CM','volume TD','volume TP','volume Projet','volume semesterielle','Ens. responsable');
        $db_order = 'order by sigle';
        $action = $to_do_action;

        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $db_order, $db_result);
		// 2.2.1 Ajustement du ttire en fonction de l'action à réaliser
		$action_debut = SUBSTR($to_do_action,0,8);
        if ($action_debut == 'modifier') {
			$titre = 'Modifier un élément'; }
			else {
			$titre = 'Consulter un élément';
		}
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);

        $this->load->view("recherche_parametree", $data);
    }

    /*
     * Fonction appeler pour trouver le module a modifier
     *
     */

    function trouver_module_a_modifier() 
    {
        $this->rechercher_module('modifier_module', 'sigle');
    }

    /*
     * Fait appel a la vue de modification d'un module.
     * @param -id : le sigle du cours.
     */

    function extereneAnneeSession($date) 
    {
        $data = '';
        $data['anneeDes'] = '';
        $data['semestre'] = $this->get_session_nom($date[4]);
        for ($i = 0; $i < (strlen($date) - 1); $i++) {
            $data['anneeDes'].=$date[$i];
        }
        return $data;
    }
    
    function generer_liste_professeur_responsable()
    {
        $session_courante = $this->scolarite_modele->get_session_courante();
        $anneeActuel = $session_courante['annee'][0].$session_courante['semestre'][0];
        $tables = array("module","employe");
        $join_keys = array('matriculeEmploye = professeurResponsable');
        $db_columns = array('matriculeEmploye', 'prenom','nom'," case when actif = 1 
            then 'O' when actif = 0 then 'N' end as actif ",'sigle','titre');
        $result_columns = array('matriculeEmploye', 'prenom','nom', 'actif','sigle','titre');
        $grid_columns = array('Matricule', 'Prénom', 'Nom','Enseignant Actif ?', 'Sigle','Titre');
        $action = '#';
        $id_action = '';
        $db_where = "where semestreActivation <= $anneeActuel and semestreDesactivation is Null or semestreDesactivation >= $anneeActuel";
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $db_where,$result_columns);
        $titre = 'Enseignants responsables des modules actifs ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }
    /*
     * fonction pour modifier un module
     */
    function modifier_module($id) 
    {

        $informations_module = $this->scolarite_modele->recuperer_module($id);  
		$sessionDeDesactivation = $this->scolarite_modele->get_session_de_desactivation($id);
        if ($sessionDeDesactivation == NULL) 
        {
            $informations_module['semestreActif'] = $sessionDeDesactivation;
        } 
        else 
        {
            $informations_module['semestreActif'] = $this->extereneAnneeSession($sessionDeDesactivation);
        }
        $informations_module['programme'] = $this->scolarite_modele->get_mod_prog_info($id);
        $infoDep = $this->scolarite_modele->recuperer_departement();
        $infoCycle = $this->scolarite_modele->recuperer_cycle();
        $informations_module['allProgramme'] = $this->scolarite_modele->get_programme();
        $informations_module['nomDep'] = $infoDep['nomDep'];
        $informations_module['idDep'] = $infoDep['idDepartement'];
        $informations_module['nom_Cycle'] = $infoCycle['nomCycle'];
        $informations_module['id_Cycle'] = $infoCycle['idCycle'];
        $info_departement = $this->scolarite_modele->recuperer_departement();
        $data_cycle = $this->scolarite_modele->recuperer_cycle();
        $professeurs = $this->scolarite_modele->get_professeurs();
        $unites = $this->scolarite_modele->get_unites();

        $data_dep_cycle = array('cycle' => $data_cycle['idCycle'],
            'nomCycle' => $data_cycle['nomCycle'], 'idDep' => $info_departement['idDepartement'],
            'nomDep' => $info_departement['nomDep'], 'professeurs' => $professeurs, 'unites'=>$unites);

        $informations_module['professeurs'] = $data_dep_cycle['professeurs'];
        $informations_module['unites'] = $data_dep_cycle['unites'];
        $informations_module['idModule'] = $id;
		$this->load->view("scolarite/modifier_module", $informations_module);
    }

    function reactiver_module() 
    {

        $this->scolarite_modele->reactiver_module($_POST['sigle']);
        $session_courante = $this->scolarite_modele->get_session_courante();
        $informations['typeBox'] = 'valid_box';
        $informations['informations'] = 'Le module <b>' . $_POST['sigle'] . '</b> a été réactivé avec succès.';
        $this->load->view('scolarite/modification_confirme', $informations);
    }

/*	Fonction réécrite 2.2.1 pur valider le semestre de désactivation
    function desactiver_module_valide() {
		$sigle = $this->input->post('sigle');
		$desactive = $this->scolarite_modele->descativer_module($sigle, $_POST['annee'], $_POST['session']);
		if ($desactive == TRUE) {
            $informations['typeBox'] = 'valid_box';
            $informations['informations'] = 'Le dernier semestre actif pour le module <b>' . $sigle . '</b> est <b>' . $this->get_session_nom($_POST['session']) . ' ' . $_POST['annee'] . '</b>';
            $this->load->view('scolarite/modification_confirme', $informations);
        } else {
            $informations['typeBox'] = 'error_box';
            $informations['informations'] = 'Le module ne peut pas être désactivé au semestre <b>' .
                    $this->get_session_nom($_POST['session']) . ' ' . $_POST['annee'] . '</b> 
                        car des étudiants y sont inscrits.';
            $this->load->view('scolarite/modification_confirme', $informations);
        }
    } */
	
	function desactiver_module_valide() {	// réécrite pour 2.2.1
		$sigle = $this->input->post('sigle');
		$desactive = $this->scolarite_modele->desactiver_module($sigle, $_POST['annee'], $_POST['session']);
			$informations_module = $this->scolarite_modele->recuperer_module($sigle); 
			$Activation = $informations_module['semestreActivation'];
			$semestre_activation = $this->SemestreTexte(substr($Activation,4,1));
			$annee_activation = substr($Activation,0,4);

		if ($desactive == TRUE) {
            $informations['typeBox'] = 'valid_box';
            $informations['informations'] = 'Le dernier semestre actif pour le module <b>' . $sigle . '</b> est <b>' . $this->get_session_nom($_POST['session']) . ' ' . $_POST['annee'] . '.</b>';
            $this->load->view('scolarite/modification_confirme', $informations);
        } else {
            $informations['typeBox'] = 'error_box';
            $informations['informations'] = 'Le module <b>'.$sigle.' </b>ne peut pas être désactivé au semestre <b>' .
                    $this->get_session_nom($_POST['session']) . ' ' . $_POST['annee'] . '</b> 
                        car ce semestre est antérieur au semestre d\'activation <b>'.$semestre_activation.' '.$annee_activation .'</b> de ce module.';
            $this->load->view('scolarite/modification_confirme', $informations);
        }
    }	

    function desactiver_module() {
        $data['sigle'] = $_POST['sigle'];
		$data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
		$this->load->view('scolarite/semestre_desactivation', $data);
    }
	
    /*
     * fonction pour choisir l'année et le semestre pour afficher les notes d'un module 
     */
    function consulter_note_global()
    {
        $data = NULL;
        $data['titre'] = 'Choisir Année et semestre';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $this->load->view('scolarite/choix_annee_semestre_conseil', $data);
    }
    /*
     * fonction qui affiche les modules qui se donnent dans un semestre precis
     * afin de consulter les notes pour le conseil de classe.
     */
    function afficher_module_a_cnsulter()
    {
        $annee = $_POST['annee'];
        $semestre = $_POST['semestre'];
        $tables = array("groupe", "module");
        $join_keys = array('groupe.sigle = module.sigle');
        $db_columns = array('groupe.sigle as sigle', 'titre');
        $result_columns = array('sigle', 'titre');
        $grid_columns = array('Sigle', 'Titre');
        $action = 'consulter_note_par_classe/' . $this->encode($_POST['annee']) .
                '/' . $this->encode($_POST['semestre']);
        $id_action = '';
        $where = "where annee = $annee and semestre = $semestre";

        $titre = '<h2>Rapport global pour le Conseil de classe 2/3</h2>
            <h2>Sélectionner le module de référence</h2>
            <h3><span style="font-size:12px;">(le Rapport global donne les notes de tous les modules suivis par les 
            étudiants inscrits dans ce module.)</span></h3>';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }
    
    function consulter_note_par_classe($annee, $semestre,$sigle)
    {
        $typeRapport = 'tri par matricule';
		$data['sigle'] = $sigle;
        $annee = $this->decode($annee);
        $semestre = $this->decode($semestre);
        $data['annee'] = $annee;
        $data['semestre'] = $semestre;
        $data['matricules'] = $this->scolarite_modele->recuperer_liste_etudiants_conseil($sigle, $annee, $semestre, $typeRapport);
        $data['resultas'] = $this->scolarite_modele->recuperer_note_par_classe($data['matricules'], $annee, $semestre);
        $this->load->view('scolarite/details_notes_classe', $data);
    }
    
    function generer_rappor_conseil()
    {
        $nomFichier = '';
 
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $date = utf8_encode(strftime("%d-%B-%Y"));
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0);
        
		// formatage du Titre de la liste.
        $objSheet->getDefaultStyle()->getFont()->setName('Courier New');
        $objSheet->setCellValue('A1', 'INSTITUT UNIVERSITAIRE PROFESSIONNEL');
        $objSheet->getStyle('A1')->getFont()->setBold(TRUE);
        
        $titreFichierExcel = 'Rapport global pour le Conseil de classe ';
        $objSheet->setCellValue('A3',$titreFichierExcel );
        $objSheet->setCellValue('A6', 'Semestre : ' . $this->get_session_nom($_POST['semestre']).' '.
                $_POST['annee']);
        $objSheet->setCellValue('A7', 'Module de reference : ' . $_POST['moduleReference']);
        $objSheet->setCellValue('A4', 'Date : ' . $date);
        $objSheet->setCellValue('A8', 'Le Rapport global donne les notes de tous les modules suivis par les étudiants inscrits dans ce module.');
        $objSheet->setCellValue('A10', 'Remarque 1 : le symbole * indique une cote EQ (Equivalence) ou un lien AB (abandon).');
        $objSheet->setCellValue('A11', 'Remarque 2 : le symbole - indique que l etudiant n\'est pas inscrit � ce module.');
        $objSheet->setCellValue('B13', 'Matricules\Modules');


        $counterMoyenneH = 0;
        $counterMoyenneV = 0;
        $sommeH = 0;
        $sommeV = 0;
                 
        $nombreDesEtudiants = count($_POST['matricules']);
        $nombreDeModule = count($_POST) - 5 ;//le 5 est pour la variable export, matricule, semestre, annee et module de reference
        for($i=0 ;$i<count($_POST['matricules']) ;$i++)
        {
            $objSheet->setCellValueByColumnAndRow(1,$i+14,$_POST['matricules'][$i]);
        }
        //Ecrire contenu du tableau
        for($i=0; $i<$nombreDeModule;$i++)
        {
            $objSheet->setCellValueByColumnAndRow(2+$i,13,$_POST[$i]['sigle']);
            for($j=0 ;$j< $nombreDesEtudiants;$j++)
            {
                if($_POST[$i]['notes'][$j] == '-1' || $_POST[$i]['notes'][$j] == 'N/A')
                {
                    if($_POST[$i]['notes'][$j] == '-1')
                    {
                        $objSheet->setCellValueByColumnAndRow(2+$i,14+$j,'*');
                    }
                    else
                    {
                        $objSheet->setCellValueByColumnAndRow(2+$i,14+$j,'-');
                    }
                }
                else
                {
                    $objSheet->setCellValueByColumnAndRow(2+$i,14+$j,  round($_POST[$i]['notes'][$j],2));
                }
                
            }
        }
        // $objSheet->setCellValueByColumnAndRow(2+$nombreDeModule,13,'Moyenne'); retrait de la moyenne horizontale 2.2.1
        $objSheet->setCellValueByColumnAndRow(1,14+$nombreDesEtudiants,'Moyenne');
        
		//calcul de moyenne verticale
        for($i=2;$i<$nombreDeModule+2 ;$i++)
        {
            $moyenne = 0;
            $sommeV = 0;
            $counterMoyenneV = 0;
            for($j=14; $j<$nombreDesEtudiants+14 ;$j++)
            {
               if($objSheet->getCellByColumnAndRow($i, $j)->getValue() != '-' && 
                       $objSheet->getCellByColumnAndRow($i, $j)->getValue() != '*')
               {
                   $sommeV += $objSheet->getCellByColumnAndRow($i, $j)->getValue();
                   $counterMoyenneV++;
               }
               if($counterMoyenneV !=0)
               $moyenne = $sommeV/$counterMoyenneV;
               $objSheet->setCellValueByColumnAndRow($i,14+$nombreDesEtudiants,$moyenne);
               
            }
        }
            $objSheet->getStyle('C1:Z80')->getNumberFormat()->setFormatCode('0.00');	// pour avoir 2 décimales

        /*calcul de la moyenne horizontale : retiré dans 2.2.1 car cette moyenne n'est pas reliée à la vraie moyenne d'un étudiant,
			ce qui peut prêter à confusion
		for($j=14; $j<$nombreDesEtudiants+14 ;$j++)
        {
            $moyenne = 0;
            $sommeH = 0;
            $objSheet->getStyle('C1:M80')->getNumberFormat()->setFormatCode('0.00');	// pour avoir 2 décimales
			$counterMoyenneH = 0;
            for($i=2;$i<$nombreDeModule+2 ;$i++)
            {
                  if($objSheet->getCellByColumnAndRow($i, $j)->getValue() != '-' && 
                       $objSheet->getCellByColumnAndRow($i, $j)->getValue() != '*')
                    {
                        $sommeH += $objSheet->getCellByColumnAndRow($i, $j)->getValue();
                        $counterMoyenneH++;
                    }
                    if($counterMoyenneH !=0)
                    $moyenne = $sommeH/$counterMoyenneH;
                    $objSheet->setCellValueByColumnAndRow($nombreDeModule+2,$j,$moyenne);
            }
        }
       */
        // for($i=1;($i<$nombreDeModule+3);$i++)  correction 2.2.1 car moyenne horizontale retirée
		for($i=1;($i<$nombreDeModule+2);$i++)
        {
            $isBold = FALSE;
            for($j=13 ;($j<$nombreDesEtudiants+15);$j++)
            {
                if($isBold == FALSE)
                {
                    $objSheet->getStyleByColumnAndRow($i, $j)->getBorders()->applyFromArray(
                       array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
                    $objSheet->getStyleByColumnAndRow($i, $j)->getFont()->setBold(TRUE);
                    $objSheet->getStyleByColumnAndRow($i, $j)->getFont()->setSize(13);
                    $isBold = TRUE;
                }
                else
                {
                    $objSheet->getStyleByColumnAndRow($i, $j)->getAlignment()->setHorizontal
                            (PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $objSheet->getStyleByColumnAndRow($i, $j)->getBorders()->applyFromArray(
                       array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                    );
                }
            }
        }
        $objSheet->getColumnDimension('B')->setAutoSize(TRUE);
        $objSheet->getStyleByColumnAndRow(2,$nombreDesEtudiants+15)->getFont()->setBold(TRUE);
     
 $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');

       $nomFichier = 'Conseil_global_'.$date;
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');

        $objWriter->save('php://output');
    }
    
    function consulter_conseil_classe()
    {
                $data = NULL;
        $data['titre'] = 'Rapport par module pour le Conseil de classe 1/3</br>Choisir Année et Semestre';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $this->load->view('scolarite/choix_annee_semestre_classe', $data);
    }
    
   
    
    function afficher_module_classe_conseil()
    {
                $annee = $_POST['annee'];
        $semestre = $_POST['semestre'];
          $tables = array("groupe", "module");
            $join_keys = array('groupe.sigle = module.sigle');
            $db_columns = array('groupe.sigle as sigle', 'titre');
            $result_columns = array('sigle', 'titre');
            $grid_columns = array('Sigle', 'Titre');
            $action = 'consulter_note_module_conseil/' . $this->encode($_POST['annee'])
                    . '/' . $this->encode($_POST['semestre']);
            $id_action = '';
            $where = "where annee = $annee and semestre = $semestre";

            $titre = 'Rapport par module pour le Conseil de classe 2/3</br>Choisir le Module </br>
                <span style="font-size:13px;">(Le <b>Rapport par module </b>donne les notes et les cotes de tous
                 les étudiants. Un rapport détaillé des cotes est fourni).</b></span>';
            $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);
            $controlleur = "scolarite";
            $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
            $this->load->view("recherche_parametree", $data);
    }
    function afficher_module_evluation()
    {
                $annee = $_POST['annee'];
        $semestre = $_POST['semestre'];
          $tables = array("groupe", "module");
            $join_keys = array('groupe.sigle = module.sigle');
            $db_columns = array('groupe.sigle as sigle', 'titre');
            $result_columns = array('sigle', 'titre');
            $grid_columns = array('Sigle', 'Titre');
            $action = 'evaluation_module/' . $_POST['annee']
                    . '/' . $_POST['semestre'];
            $id_action = '';
            $where = "where annee = $annee and semestre = $semestre";

            $titre = 'Rapport par module pour le Conseil de classe 2/3</br>Choisir le Module </br>
                <span style="font-size:13px;">(Le <b>Rapport par module </b>donne les notes et les cotes de tous
                 les étudiants. Un rapport détaillé des cotes est fourni).</b></span>';
            $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);
            $controlleur = "scolarite";
            $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
            $this->load->view("recherche_parametree", $data);
    }
    
    function consulter_note_module_conseil($annee, $semestre,$sigle)
    {
        $typeRapport = 'tri par module';
		$annee = $this->decode($annee);
        $semestre = $this->decode($semestre);
        $data['sigle'] = $sigle;
//        $data['annee'] = $annee;
        $data['semestre'] = $semestre;
        $data['matricules'] = $this->scolarite_modele->recuperer_liste_etudiants_conseil($sigle, $annee, $semestre,$typeRapport);
        $data['resultas'] = $this->scolarite_modele->recuperer_note_par_classe_conseil($data['matricules'], $annee, $semestre,$sigle);
        $this->load->view('scolarite/details_note_cote_module', $data);
    }
    /*
     * fonction pour compter le nombre de match d'un string dans les valeurs d'un tableau
     */
    function countWhere($tableau,$string)
    {
        $counter = 0;
        for($i=0;$i<count($tableau);$i++)
        {
            if($tableau[$i]==$string)
                $counter++;
        }
        return $counter;
    }
    //Alfa
    function generer_rappor_note_module()
    {
        
        
       
        $nomFichier = '';
 
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $date = utf8_encode(strftime("%d-%B-%Y"));
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0);
        
		// formatage du Titre de la liste.
        $objSheet->getDefaultStyle()->getFont()->setName('Courier New');
        $objSheet->setCellValue('A1', 'INSTITUT UNIVERSITAIRE PROFESSIONNEL');
        $objSheet->getStyle('A1')->getFont()->setBold(TRUE);
        
        $titreFichierExcel = 'Rapport pour le conseil de classe ';
        $objSheet->setCellValue('A3',$titreFichierExcel );
        $objSheet->setCellValue('A6', 'Semestre : ' . $this->get_session_nom($_POST['semestre']).' '.
                $_POST['annee']);
        $objSheet->setCellValue('A7', 'Module : ' . $_POST['sigle']);
        $objSheet->setCellValue('A4', 'Date : ' . $date);
        $annee=$_POST['annee'];
        $sem=$_POST['codeSemestre'];
        
        $nombreDesEtudiants = count($_POST['matricules']);
        //$nombreDeModule = count($_POST) - 5 ;//le 5 est pour la variable export, matricule, semestre, annee et module de reference
       $indexNote=0;
       $nomsEvaluations=$this->scolarite_modele->get_noms_evaluations($_POST['sigle']);
       $nbevaluation=count($nomsEvaluations);
        for($i=0;$i<$nbevaluation;$i++)
                    { //$evaluationsID[]=$nomsEvaluations[$i]['idEvaluation'];
                     $objSheet->setCellValueByColumnAndRow($i+2,8, $nomsEvaluations[$i]['ponderation']);
                     $objSheet->setCellValueByColumnAndRow($i+2,9,$nomsEvaluations[$i]['nomEvaluation']);
                    }
                    $objSheet->setCellValueByColumnAndRow($i+2,9,'moyenne');
                     $noteEtudiant = $this->scolarite_modele->get_notes_partielles($_POST['matricules'],$_POST['sigle'],$annee,$sem);
          
          
        for($i=0 ;$i<count($_POST['matricules']) ;$i++)
        {
            
             for($j=0;$j<$nbevaluation;$j++)
                    { //$evaluationsID[]=$nomsEvaluations[$j]['idEvaluation'];
                     //$objSheet->setCellValueByColumnAndRow($j+2,10+$i,$nomsEvaluations[$j]['nomEvaluation'].' ('. $nomsEvaluations[$j]['ponderation']. ') ');
                   $noteP=0;
                    if(!empty($noteEtudiant[$i]['note']))
                    {//teste pour raiter les evaluations qui sont dans moduleevaluation mais pas encore dans notespartielles
                     if(isset($noteEtudiant[$i]['note'][$j]['note']))
                     $noteP=$noteEtudiant[$i]['note'][$j]['note'];
                    }
                   $objSheet->setCellValueByColumnAndRow($j+2,10+$i,$noteP);
                   

                    }
                 
            $objSheet->setCellValueByColumnAndRow(1,$i+10,$_POST['matricules'][$i]);
            $objSheet->setCellValueByColumnAndRow($nbevaluation+2,$i+10,$_POST['resultats'][$indexNote]['note']);
                 
             $indexNote+=3;
        }

        
        // put code hier ..
        
        // end
        
        
        
 //$objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');

       $nomFichier = 'Conseil_'.$_POST['sigle'].'_'.$_POST['semestre'].$_POST['annee'];
        $objWriter = PHPExcel_IOFactory::createwriter($objXLS, 'Excel5');
        header('Content-Type', 'application/msexcel;charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
ob_end_clean();

        $objWriter->save('php://output');
    }
    /*
     * Fonction pour mettre à jour le module avec les nouvelles informations
     */

    function is_float($number) 
    {
        if (is_numeric($number)) 
        {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    
    function isValideHr($hr)
    {
        $hr = str_replace(",", ".", $hr);
        if(is_numeric($hr) && $hr<=90 && $hr>=0)
        {
            if(strlen($hr)<=5)
                return $hr;
            else
            {
                return NULL;
            }
        }
        else
        {
            return NULL;
        }
    }
    /*
     * fonction pour mettre a jour module 
     */
    function mettre_a_jour_module() 
    {
        $this->form_validation->set_rules('sigle', 'Sigle du module', 'required');
        $this->form_validation->set_rules('titreMod', '<b>Titre du Module</b>', 'max_length[100]');
       
//        $hrTP = $this->isValideHr($_POST['hrTP']);
//        $hrCours = $this->isValideHr($_POST['hrCours']);
//        $hrTD = $this->isValideHr($_POST['hrTD']);
        $volumeCM = $this->isValideHr($_POST['volumeCM']);
         $volumeTD = $this->isValideHr($_POST['volumeTD']);
         $volumeTP = $this->isValideHr($_POST['volumeTP']);
          $volumeProjet = $this->isValideHr($_POST['volumeProjet']);//volume projet
         
         
	/*if($hrTD == NULL || $hrCours==NULL || $hrPerso == NULL || $hrTP == NULL)
        {
             $informations['typeBox'] = 'error_box';
                $informations['informations'] = 'Les modifications ont échoué.';
                $this->load->view('scolarite/modification_confirme', $informations);
        }
        else*/
        {
            //$quadruplet = $hrTP + $hrCours + $hrTD + $hrPerso;
            //$this->form_validation->set_rules('nbrCredits', 'Correspondance crédits et quadruplet horaire', 'callback_verifier_credits_et_quadruplet[' . $quadruplet . ']');

            $informations = NULL;
            if(isset($_POST['progs']))
            {
                if ($this->form_validation->run() ) 
                {
                    $info_module = $this->input->post();
                    $this->scolarite_modele->mettre_a_jour_module($info_module);
                    $informations['typeBox'] = 'valid_box';
                    $informations['informations'] = 'L\'élément <b>' . $_POST['sigle'] . '</b> a été modifié avec succès.';
                    $this->load->view('scolarite/modification_confirme', $informations);
                } 
                else 
                {
                    $this->modifier_module($_POST['idModule']);
                }
            }
            else
            {
                $informations['typeBox'] = 'error_box';
                $informations['informations'] = 'Il faut sélectionner au moins un programme.';
                $this->load->view('scolarite/modification_confirme', $informations);
            }
        }
    }

    /*
     * Fonction appeler pour trouver le module a consulter
     *
     */

    function trouver_module_a_consulter() {
        $this->rechercher_module('consulter_module', 'sigle');
    }
    
    /*alfa */

    function evaluation_module($annee, $semestre, $id) {
        
      
        
        $result = $this->scolarite_modele->get_evaluations($annee, $semestre, $id);
	$data['result']=$result;
        
        $data['annee']=$annee;
        $data['semestre']=$semestre;
       
      
         $matricules = $this->scolarite_modele->recuperer_liste_etudiants($id, $annee, $semestre);
        
         $data['notesPasValide'] = $this->scolarite_modele->verifier_acces_note_valide($matricules,$annee,$semestre,$id);
         
      
        	

        $this->load->view("scolarite/evaluation_module",$data);
    }
    
     function confirmation_evaluation_module($annee, $semestre, $id) {
        
      
        $result = $this->scolarite_modele->set_evaluations($annee, $semestre, $id);
	$data['result']=$result;	
        $data['annee']=$annee;
        $data['semestre']=$semestre;
        $this->load->view("scolarite/confirmation_evaluation_module",$data);
    }
    
    /*
     * Fait appel a la vue de modification d'un module.
     * @param -id : le parametre avec lequel identifier le module selectionne
     */

    function consulter_module($id) 
    {
        $informations_module = $this->scolarite_modele->recuperer_module($id);
        $infoDep = $this->scolarite_modele->recuperer_departement();
        $infoCycle = $this->scolarite_modele->recuperer_cycle();
        $informations_module['programme'] = $this->scolarite_modele->get_mod_prog_info($id);
        $informations_module['nomDep'] = $infoDep['nomDep'];
        $informations_module['idDep'] = $infoDep['idDepartement'];
        $informations_module['nom_Cycle'] = $infoCycle['nomCycle'];
        $informations_module['id_Cycle'] = $infoCycle['idCycle'];
        if($informations_module['professeurResponsable'] == '') 
        {
            $informations_module['nom'] = '';
            $informations_module['prenom'] = '';
        }
        if($informations_module['sigleunite'] == '') 
        {
            $informations_module['titreunite'] = '';
        }
        
        $this->load->view("scolarite/consulter_module", $informations_module);
    }

    function rechercher_professeurs() 
    {
        $tables = array("employe", "departement");
        $join_keys = array('employe.idDepartement = departement.idDepartement');
        $db_columns = array('matriculeEmploye', 'employe.nom as nomEmploye', 'employe.prenom as prenomEmploye', 'departement.nom as nomDepartement', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEmploye', 'nomEmploye', 'prenomEmploye', 'nomDepartement', "actif");

        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Département', 'Actif ?');
		  $db_where = "order by matriculeEmploye";
        $action = '#';
        $id_action = 'matriculeEmploye';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $db_where, $result_columns);

        $titre = 'Liste des employés ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    function afficher_enseignant_module()
    {
        $infoEnseignant = '';
        $infoEnseignant['infoEnseignant'] = $this->scolarite_modele->getInfoEmployeModule($_POST['choixModule']);
        $infoEnseignant['titre'] = 'Enseignants du module <b>'.$_POST['choixModule'].' </b>';
        $infoEnseignant['departement'] = $_POST['choixModule'];
        $this->load->view('scolarite/afficher_liste_enseignant_module',$infoEnseignant);
    }
    
    function afficher_enseignant_departement()
    {
        $infoEnseignant = '';
        $infoEnseignant['infoEnseignant'] = $this->scolarite_modele->getInfoEmploye($_POST['choixDepartement']);
        if( $this->scolarite_modele->get_departement_nom($_POST['choixDepartement'])== 
                'Service administratif')
        {
            $infoEnseignant['titre'] = 'Personnel actif du <b>'.
                $this->scolarite_modele->get_departement_nom($_POST['choixDepartement']).' </b>';
        }
        else
        {
             $infoEnseignant['titre'] = 'Enseignants actifs du département <b>'.
             $this->scolarite_modele->get_departement_nom($_POST['choixDepartement']).' </b>';
   
        }

        $infoEnseignant['departement'] = $_POST['choixDepartement'];
        $this->load->view('scolarite/afficher_liste_enseignant',$infoEnseignant);

    }
    function generer_liste_professeur() 
    {
        $genererListe = false;
        $this->form_validation->set_rules('choixType', 'Le choix du type', 'required');
        if ($this->form_validation->run()) {
            $data['typeListe'] = 'professeur';
            switch ($this->input->post('choixType')) 
            {
                case 'departement':
                    $data['titre'] = "Choisissez le département :";
                    $departements = $this->scolarite_modele->recuperer_departement();
                    if ($departements == NULL) 
                    {
                        $genererListe = true;
                        $info['typeBox'] = 'warning_box';
                        $info['informations'] = 'Veuillez inscrire au moins un étudiant dans un module.';
                        $this->load->view('scolarite/modification_confirme', $info);
                    } 
                    else 
                    {
                        $data['departements'] = $departements;
						$this->load->view('scolarite/choisir_departement_pour_liste',$data);
                    }
                    break;
                case 'module':
                    $data['titre'] = "Choisissez le module :";
					$sigles = $this->scolarite_modele->recuperer_sigle_cours();
					if ($sigles == NULL) {
                        $genererListe = true;
                        $info['typeBox'] = 'warning_box';
                        $info['informations'] = 'Veuillez créer au moins un module.';
						$this->load->view('scolarite/modification_confirme', $info);
                    } 
                    else 
                    {
                        $data['module'] = $sigles['sigleCours'];
						$this->load->view('scolarite/choisir_departement_pour_liste',$data);
                        
                        /*$data['hidden'] = array('from' => 'employe join groupe on 
                            employe.matriculeEmploye = groupe.matriculeEmploye
                            join module on groupe.sigle = module.sigle join programme',
                            'key' => 'module.sigle', 'columns' => array
                                ('employe.matriculeEmploye', 'employe.nom', 'employe.prenom'),
                            'titre' => 'Enseignants actifs du module ');*/
                    }
                    break;
            }

        } 
        else 
        {
            $this->load->view('scolarite/choix_type_liste_prof');
        }
    }
    
    

    /*
     * fonction qui dirige la fonction generer liste dependemment du choix de l'utilisateur 
     * soit par annee, module, groupe ou programme.
     */

    function generer_liste_etudiant() {
        $genererListe = false;
        $this->form_validation->set_rules('choixType', 'Le choix du type', 'required');
        if ($this->form_validation->run()) {
            $data['typeListe'] = 'etudiant';
             $annees = $this->scolarite_modele->recuperer_annee();
                    $courante = $this->scolarite_modele->get_date_courante();
            switch ($this->input->post('choixType')) {
                case 'annee':
                    $data['titre'] = "Choisissez le semestre";
                   
                    if ($annees == NULL) {
                        $genererListe = true;
                        $info['typeBox'] = 'warning_box';
                        $info['informations'] = 'Veuillez inscrire au moins un étudiant dans un module !';
                        $this->load->view('scolarite/modification_confirme', $info);
                    } else {
                        $data['value'] = $annees['date'];
                        $data['option'] = $annees['date'];
                        $data['case'] = 'annee';
                        $data['courante'] = $courante;
                        $data['hidden'] = array('from' => 'etudiant join listeEtudiants
							on etudiant.matriculeEtudiant = listeEtudiants.matriculeEtudiant
							join groupe on groupe.idGroupe = listeEtudiants.idGroupe', 'key' => 'groupe.annee',
                            'columns' => array('etudiant.matriculeEtudiant', 'etudiant.nom', 'etudiant.prenom'),
                            'titre' => 'Étudiants actifs au semestre <b>');
                    }
                    break;
                case 'groupe':
                    $data['titre'] = "Choisissez le groupe";
                    $progs = $this->scolarite_modele->recuperer_sigle_groupe();
                    if ($progs == NULL) {
                        $genererListe = true;
                        $info['typeBox'] = 'warning_box';
                        $info['informations'] = 'Veuillez inscrire au moins un étudiant dans un module !';
                        $this->load->view('scolarite/modification_confirme', $info);
                    } else {
						$data['value'] = $progs['idGroupe'];
						$data['case'] = 'groupe';
						$data['option'] = $progs['idGroupe'];
                        $data['hidden'] = array('from' => 'etudiant join listeEtudiants
							on etudiant.matriculeEtudiant = listeEtudiants.matriculeEtudiant', 'key' => 'listeEtudiants.idGroupe',
                            'columns' => array('etudiant.matriculeEtudiant', 'etudiant.nom', 'etudiant.prenom'),
                            'titre' => 'Étudiants du groupe ');
                    }
                    break;
                case 'module':
                    $data['titre'] = "Choisissez le module";
                    $data['case'] = 'module';
                    $sigles = $this->scolarite_modele->recuperer_sigle_cours();
                    if ($sigles == null) {
                        $genererListe = true;
                        $info['typeBox'] = 'warning_box';
                        $info['informations'] = 'Veuillez inscrire au moins un étudiant dans un module !';
                        $this->load->view('scolarite/modification_confirme', $info);
                    } else {
                        $data['annee'] = $this->scolarite_modele->recuperer_annee();
                        $data['value'] = $sigles['sigleCours'];
                        $data['option'] = $sigles['sigleCours'];
                        $data['courante'] = $courante;
                        $data['hidden'] = array('from' => 'etudiant join listeEtudiants
							on etudiant.matriculeEtudiant = listeEtudiants.matriculeEtudiant join groupe on groupe.idGroupe = listeEtudiants.idGroupe', 'key' => 'groupe.sigle',
                            'columns' => array('etudiant.matriculeEtudiant', 'etudiant.nom', 'etudiant.prenom'),
                            'titre' => 'Étudiants du module ');
                    }
                    break;

                case 'programme':
                    $data['titre'] = "Choisissez le programme";
                    $data['case'] = 'programme';
                    $data['annee'] = $this->scolarite_modele->recuperer_annee();
                    $programmes = $this->scolarite_modele->recuperer_programme();
                    $data['value'] = $programmes['idProg'];
                    $data['option'] = $programmes['nom'];
                    $data['hidden'] = array('from' => 'etudiant join dossierEtudiant
                        on etudiant.matriculeEtudiant = dossierEtudiant.matriculeEtudiant', 'key' => 'dossierEtudiant.idProgramme',
                        'columns' => array('etudiant.matriculeEtudiant', 'etudiant.nom', 'etudiant.prenom'),
                        'titre' => 'Étudiants actifs du programme ');
                    break;
            }
            //on teste si aucun etudiant n'est inscrit à un module.
            if ($genererListe == false) {
                $this->generer_liste($data);
            }
        } else {
            $this->load->view('scolarite/choix_type_liste_etudiant');
        }
    }

    /*
     * Fonction qui génère les fichiers Excel pour les listes des étudiants
     */

    function generer_excel() {

        $nombreEtudiant = 0;
        $nomFichier = '';
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $date = utf8_encode(strftime("%d-%B-%Y"));
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0);
        /// formatage du Titre de la liste.
        $objSheet->getDefaultStyle()->getFont()->setName('Courier New');
        $objSheet->setCellValue('A1', 'INSTITUT SUPERIEUR DES METIERS DE LA STATISTIQUE');
        $objSheet->getStyle('A1')->getFont()->setBold(TRUE);
        if ($_POST['typeListe'] == 'professeur') {
            $nombreEtudiant = count($_POST['liste']);
			$nomFichier = 'Enseignants_'.$_POST['choix'].'_'.$date;
            $objSheet->setCellValue('A3', ' Liste des enseignants actifs (liste produite le '.$date.')');
        }
        else
        {
            if(strstr($_POST['choix'],'Groupe') || (strstr($_POST['choix'],'PROG')))
            {
                $nombreEtudiant = count($_POST['liste']);
            } else {
                $nombreEtudiant = count($_POST['liste']['matriculeEtudiant']);
            }
            $objSheet->setCellValue('A3', $_POST['titre'] . ' (liste produite le ' . $date . ')');
        }

        //je teste si la liste generee est une liste de module, de groupe, d'annee ou de programme
        //et je retourne l information necessaire pour generer le header de la liste.

        if (strstr($_POST['choix'], 'PROG')) {
            $nomFichier = 'Etudiants_' . $_POST['choix'];
            $objSheet->setCellValue('B5', 'Programme :');
            $objSheet->getStyle('B5')->getAlignment()->applyFromArray(
                    array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'rotation' => 0,
                        'wrap' => true,
                        'font' => array('bold' => true)
                    )
            );
            $objSheet->setCellValue('C5', $this->scolarite_modele->get_programme_nom($_POST['choix']));
        } elseif (strstr($_POST['choix'], 'Groupe')) {
			$nombreEtudiant = count($_POST['liste']);
			$nomFichier = 'Etudiants_' . $_POST['choix'];
            $titre = 'au groupe ' . $_POST['choix'];
			$info_module = $this->scolarite_modele->recuperer_info_groupe($_POST['choix']);
			            
			$objSheet->setCellValue('B5', 'Module :');
            $objSheet->getStyle('B5')->getAlignment()->applyFromArray(
                    array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'rotation' => 0,
                        'wrap' => true,
                        'font' => array('bold' => true)
                    )
            );
            $objSheet->setCellValue('C5', $info_module['sigle'] . ' ' . $info_module['titre'] . ' (' . $info_module['nbCredits'] . ' crédits)');
            $objSheet->setCellValue('B6', 'Groupe :');
            $objSheet->getStyle('B6')->getAlignment()->applyFromArray(
                    array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'rotation' => 0,
                        'wrap' => true,
                        'font' => array('bold' => true)
                    )
            );
            $objSheet->setCellValue('C6', $info_module['typeGroupe'] . ' numéro ' . $info_module['numGroupe']);
            $objSheet->setCellValue('B7', 'Enseignant :');
            $objSheet->getStyle('B7')->getAlignment()->applyFromArray(
                    array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'rotation' => 0,
                        'wrap' => true,
                        'font' => array('bold' => true)
                    )
            );
            $objSheet->setCellValue('C7', $info_module['prenom'] . ' ' . $info_module['nom']);
        } elseif (is_numeric($_POST['choix'])) {
            $titre = 'à l\'année ' . $_POST['choix'];
            $nomFichier = 'Etudiants_' . $_POST['semestre'] . '_' . $_POST['choix'];
        } elseif (strstr($_POST['choix'], 'DPT')) {
            $objSheet->setCellValue('B5', 'Département :');
            $objSheet->getStyle('B5')->getAlignment()->applyFromArray(
                    array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'rotation' => 0,
                        'wrap' => true,
                        'font' => array('bold' => true)
                    )
            );
            $objSheet->setCellValue('C5', $this->scolarite_modele->get_departement_nom($_POST['choix']));
        } elseif ($_POST['typeListe'] != 'professeur') {
            $nomFichier = 'Etudiants_' . $_POST['semestre'] . '_' . $_POST['annee'] . '_' . $_POST['choix'];
            $info_module = $this->scolarite_modele->recuperer_module($_POST['choix']);
            $objSheet->setCellValue('B6', 'Module :');
            $objSheet->getStyle('B6')->getAlignment()->applyFromArray(
                    array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'rotation' => 0,
                        'wrap' => true,
                        'font' => array('bold' => true))
            );
            $objSheet->setCellValue('C6', $_POST['choix'] . ' ' . $info_module['titre'] . ' (' . $info_module['nbCredits'] . ' crédits)');
            if ($_POST['typeListe'] == 'professeur') {
                $objSheet->setCellValue('B7', 'Responsable :');
            } else {
                $objSheet->setCellValue('B7', 'Enseignant responsable :');
            }
            $objSheet->getStyle('B7')->getAlignment()->applyFromArray(
                    array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'rotation' => 0,
                        'wrap' => true,
                        'font' => array('bold' => true)
                    )
            );
            $objSheet->setCellValue('C7', $info_module['prenom'] . ' ' . $info_module['nom']);
        }

        //Écrire les titres du tableau
        $objSheet->setCellValue('D9', 'Date :');
        $objSheet->setCellValue('B10', 'Matricule ');
        $objSheet->getStyle('B10')->getAlignment()->applyFromArray(
                array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'rotation' => 0,
                    'wrap' => true,
                    'font' => array('bold' => true)
                )
        );
        $objSheet->setCellValue('C10', 'Nom  et Prénom');
        $objSheet->getStyle('C10')->getAlignment()->applyFromArray(
                array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'rotation' => 0,
                    'font' => array('bold' => true),
                    'wrap' => true
                )
        );
        $objSheet->setCellValue('D10', 'Signature');
        $objSheet->getStyle('D10')->getAlignment()->applyFromArray(
                array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'rotation' => 0,
                    'font' => array('bold' => true),
                    'wrap' => true
                )
        );
        ///// remplir le tableau avec le nom des etudiants 
        $index = 11; //index de header du tableau de la liste
        if ($_POST['typeListe'] == 'professeur' || strstr($_POST['choix'], 'Groupe') || strstr($_POST['choix'], 'PROG')) {
            for ($i = 0; $i < $nombreEtudiant; $i = $i + 3) {
                if (strstr($_POST['choix'], 'Groupe') || strstr($_POST['choix'], 'PROG')) {
                    $objSheet->setCellValue('B' . $index, $_POST['liste'][$i]['matriculeEtudiant']);
                } else {
                    $objSheet->setCellValue('B' . $index, $_POST['liste'][$i]['matriculeEmploye']);
                }
                $objSheet->getStyle('B' . $index)->getAlignment()->applyFromArray(
                        array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            'rotation' => 0,
                            'wrap' => true)
                );
                $objSheet->setCellValue('C' . $index, $_POST['liste'][$i + 1]['nom'] .
                        ', ' . $_POST['liste'][$i + 2]['prenom']);

                $objSheet->setCellValue('D' . $index, '    ');
                $objSheet->getStyle('D' . $index)->getAlignment()->applyFromArray(
                        array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            'rotation' => 0,
                            'wrap' => true
                        )
                );
                $index++;
            }
            ////////////////////////////////////////////////////////////////////////////////////////////
            //je fais une boucle sur tous les etudiants plus j'ajoute 3 cases des titres(Matricule,Nom et prenom...)
            for ($i = 1; $i <= ($nombreEtudiant / 3) + 1; $i++) {
                $index = 9;
                $index = $index + $i;
                $objSheet->getStyle('B' . $index)->getBorders()->applyFromArray(
                        array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
                $objSheet->getStyle('C' . $index)->getBorders()->applyFromArray(
                        array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
                $objSheet->getStyle('D' . $index)->getBorders()->applyFromArray(
                        array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
            }
        } elseif ($_POST['typeListe'] == 'etudiant') {
            for ($i = 0; $i < $nombreEtudiant; $i++) {
                $objSheet->setCellValue('B' . $index, $_POST['liste']['matriculeEtudiant'][$i]);
                $objSheet->getStyle('B' . $index)->getAlignment()->applyFromArray(
                        array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            'rotation' => 0,
                            'wrap' => true
                        )
                );
                $objSheet->setCellValue('C' . $index, $_POST['liste']['nom'][$i] . ', ' . $_POST['liste']['prenom'][$i]);
                $objSheet->setCellValue('D' . $index, '    ');
                $objSheet->getStyle('D' . $index)->getAlignment()->applyFromArray(
                        array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            'rotation' => 0,
                            'wrap' => true
                        )
                );
                $index++;
            }
            ////////////////////////////////////////////////////////////////////////////////////////////
            //je fais une boucle sur tous les etudiants plus j'ajoute 3 cases des titres(Matricule,Nom et prenom...)
            for ($i = 1; $i <= $nombreEtudiant + 1; $i++) {
                $index = 9;
                $index = $index + $i;
                $objSheet->getStyle('B' . $index)->getBorders()->applyFromArray(
                        array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
                $objSheet->getStyle('C' . $index)->getBorders()->applyFromArray(
                        array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
                $objSheet->getStyle('D' . $index)->getBorders()->applyFromArray(
                        array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
            }
        }

        /////////////////////////////////////////////////////////////////////////////////////////////////   
        $objXLS->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
        $objXLS->getActiveSheet()->setTitle('Titre1');
        $objXLS->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');

        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        $objWriter->save('php://output');
   
        $data['typeBox'] = 'valid_box';
        $data['informations'] = 'Fichier géneré avec succès.';
        $this->load->view('scolarite/modification_confirme', $data);
    }

    function generer_liste($data = '') 
    {
		$result = NULL;
        $this->form_validation->set_rules('choix', 'choix', 'required');
        if ($this->form_validation->run()) 
        {
            $result['choix'] = $_POST['choix'];
            $result['typeListe'] = $_POST['typeListe'];
            //en cas de module si cette variable existe alors l utilisateur veux voir une liste
            //d etudiants par module.
            if (isset($_POST['annee'])) 
            {
                $result['table'] = $this->scolarite_modele->get_nom_prenom_etudiant($this->scolarite_modele->
                                get_etudiant_inscrit_module($_POST['choix'], $_POST['annee'], $_POST['session']));

                $result['semestre'] = $this->get_session_nom($_POST['session']);
                $result['annee'] = $_POST['annee'];
                $result['titre'] = 'Étudiants inscrits au module ' . $_POST['choix'] .
                        ' au semestre ' . $this->get_session_nom($_POST['session']) . '-' . $_POST['annee'];

                $result['module'] = 'module'; // variable pour l envoyer a la vue pour que la vue sache ce qu'elle doit afficher ou non
            }
            //le cas de l'annee n est pas identique avec les autres cas 
            elseif (isset($_POST['session'])) 
            {
                $result['semestre'] = $this->get_session_nom($_POST['session']);
                $result['table'] = $this->scolarite_modele->get_nom_prenom_etudiant($this->scolarite_modele->recuperer_etudiants_actifs_annee($_POST['choix'], $_POST['session']));
                $result['titre'] = $this->input->post('titre') . ": " . $result['semestre'] . '-' . $this->input->post('choix');
            } 
            else 
            {
                $result['table'] = $this->scolarite_modele->get_liste($_POST['columns'], $_POST['from'], $_POST['key'], $_POST['choix']);
                $result['titre'] = $this->input->post('titre') . ": " . $this->input->post('choix');

            }
            if (strstr($_POST['choix'], 'PROG')) 
            {
                $result['table'] = $this->scolarite_modele->get_liste($_POST['columns'], $_POST['from'], $_POST['key'], $_POST['choix']);
                $result['titre'] = $this->input->post('titre') . ": " . $this->input->post('choix');
            }


            if ($this->input->post('choix') == 'DPT-ADM')
                $result['titre'] = "Employés du département: " . $this->input->post('choix');

            if (!is_array($result['table'])) 
            {

                $data['typeBox'] = 'warning_box';
                if ($_POST['typeListe'] == 'professeur') 
                {

                    $data['informations'] = 'Le module <b>' . $_POST['choix'] . ' </b> n\'a été attribué à aucun enseignant.';
                } else {
                    //Annee
                    if (isset($_POST['session'])) {
						// $data['informations'] = 'Aucun étudiant n\'est inscrit au semestre <b>' .
                            //correction RM 27 février 2013   $this->get_session_nom($_POST['session']) . ' - ' . $_POST['choix'] . ' </b>';
						$data['informations'] = 'Aucun étudiant n\'est inscrit au module <b>' .$_POST['choix'] . ' </b>' . 
						'au semestre <b>' . $this->get_session_nom($_POST['session']) . ' ' . $_POST['annee'] . ' </b>.';
					/*   au module <b>' . $_POST['choix'] . '</b>'; */
					}
                    //Module
                    else {
						$data['informations'] = 'Aucun étudiant n\'est inscrit au programme <b>' . 
							$this->scolarite_modele->get_programme_nom($_POST['choix']) .'</b>.';	//correction 2.2.1
                    }
                }
                $this->load->view("scolarite/modification_confirme", $data);
            } 
            else 
            {
                $this->load->view('scolarite/afficher_liste', $result);
            }
        } 
        else 
        {
            $this->load->view('scolarite/choix_type_liste', $data);
        }
    }

    /*
     * fonction qui affiche tous les groupes qui sont offerts durant l annee courante 
     */

    function ajouter_absences() {
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];

        $tables = array("groupe");
        $join_keys = null;
        $db_columns = array('idGroupe', 'numGroupe', 'typeGroupe', 'annee',"case when semestre = 1 then 'Printemps' when semestre = 2 then '&#201;t&#233;'
                when semestre = 3 then 'Automne' end as semestre");
        $db_result_columns = array('idGroupe', 'numGroupe', 'typeGroupe', 'annee', 'semestre');
        $grid_columns = array('Groupe', 'Numéro du groupe', 'Type', "Année", 'Semestre');
        $action = 'afficher_etudiant_groupe';
        $id_action = 'idGroupe';
        $db_where = "where annee = $anneeCourante and semestre = $semestreCourant";
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $db_where,$db_result_columns);
        $titre = 'Les groupes offerts au semestre courant : <b>' . $this->get_session_nom($semestreCourant) . '  ' . $anneeCourante . '</b>';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    /*
     * fonction qui affiche tous les etudiants inscrits dans un groupe precis
     */

    function afficher_etudiant_groupe($idGroupe) {
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];
        //tous les etudiants inscrits dans un groupe specifie
        $data['etudiant'] = $this->scolarite_modele->get_etudiants_groupe($idGroupe);
        $data['annee'] = $anneeCourante;
        $data['session'] = $semestreCourant;
        $data['idGroupe'] = $idGroupe;
         /* Correction 23 février 2013 RM $data['titre'] = 'Liste des étudiants du groupe ' . $idGroupe . ', semestre ' .
                $this->get_session_nom($semestreCourant) . ' ' . $anneeCourante . '
					    . Cocher le ou les étudiants absents.'; */
		$data['titre'] = 'Liste des étudiants du groupe ' . $this->scolarite_modele->corrigerNumGroupe($idGroupe) . ', semestre ' .
                $this->get_session_nom($semestreCourant) . ' ' . $anneeCourante . '
						. Cocher le ou les étudiants absents.';
        $this->load->view('scolarite/entrer_absences', $data);
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
                $day .=$date[$i];
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
     * fonction qui converti le type de date Atom en yyyy-mm-dd
     */

    function convert_date_ATOM($date) {
        $dateConverti = '';
        for ($i = 0; $i < 10; $i++) {
            $dateConverti .= $date[$i];
        }
        return $dateConverti;
    }

    /*
     * fonction pour enregistrer les absences entrer par la secretaire
     */

    function enregistrer_absences() {
        $format = 'DATE_ATOM';
        $time = time();

        $date = $this->convert_date_ATOM(standard_date($format, $time));
        //on teste si aucun etudiant n est selectionne
        if (!isset($_POST['items'])) {
            $data['titre'] = 'Entrer Absences';
            $data['type'] = 'error_box';
            $data['informations'] = 'Erreur : il faut sélectionner au moins un étudiant.';
            $this->load->view('scolarite/confirmation_absences', $data);
        } else {
            //on teste si la duree n'est pas selectionne
            if (!isset($_POST['duree']) && !isset($_POST['duree2'])) {
                $data['titre'] = 'Entrer Absences';
                $data['type'] = 'error_box';
                $data['informations'] = 'Erreur : vous avez oublié de spécifier la durée de l\'absence';
                $this->load->view('scolarite/confirmation_absences', $data);
            } else {
                if (isset($_POST['duree']) && isset($_POST['duree2'])) {
                    if (($_POST['duree'] == 0) && ($_POST['duree2'] == 0.0)) {
                        $data['titre'] = 'Entrer Absences';
                        $data['type'] = 'error_box';
                        $data['informations'] = 'Erreur : 0 n\'est pas une durée valide pour entrer les absences.';
                        $this->load->view('scolarite/confirmation_absences', $data);
                    } else {
                        if (!isset($_POST['periode'])) {
                            $data['titre'] = 'Entrer Absences';
                            $data['type'] = 'error_box';
                            $data['informations'] = 'Erreur : vous avez oublié de spécifier la période de l\'absence';
                            $this->load->view('scolarite/confirmation_absences', $data);
                        } else {
                            $dateCourante = $this->scolarite_modele->get_date_courante();
                            //si la date choisis n'est pas dans l intervalle des date du debu et fin des cours
                            if ($this->convert_date($_POST['date']) < $dateCourante['debutCours'] || $this->convert_date($_POST['date']) > $dateCourante['finCours']) {
                                $data['titre'] = 'Entrer Absences';
                                $data['type'] = 'error_box';

                                $data['informations'] = 'Erreur:  la date choisie <b>' . $this->convert_date($_POST['date']) . ' </b>ne fait pas partie du semestre courant <b>' .
                                        $this->get_session_nom($dateCourante['semestre']) . ' ' . $dateCourante['annee'] . '. </b>Si le semestre courant est en erreur, contactez l\'administrateur réseau.';
                                $this->load->view('scolarite/confirmation_absences', $data);
                            } else {
                                //on teste si la date choisie est dans le futur
                                if ($this->convert_date($_POST['date']) > $date) {
                                    $data['titre'] = 'Entrer Absences';
                                    $data['type'] = 'error_box';
                                    $data['informations'] = 'Erreur : on ne peut choisir une date postérieure à la date présente.';
                                    $this->load->view('scolarite/confirmation_absences', $data);
                                } else {
                                    $this->scolarite_modele->enregistrer_absences($_POST);
                                    $data['titre'] = 'Entrer Absences';
                                    $data['type'] = 'valid_box';
									/* Corrigé 23 février 2013 RM $data['informations'] = 'Les absences au groupe <b>' . $_POST['idGroupe'] . '</b> ont bien été enregistrées.'; */
                                    $data['informations'] = 'Les absences au groupe <b>' . $this->scolarite_modele->corrigerNumGroupe($_POST['idGroupe']) . '</b> ont bien été enregistrées.';
                                    $this->load->view('scolarite/confirmation_absences', $data);
                                }
                            }
                        }
                    }
                } else {
                    if (isset($_POST['duree'])) {
                        if ($_POST['duree'] == 0) {


                            $data['titre'] = 'Entrer Absences';
                            $data['type'] = 'error_box';
                            $data['informations'] = 'Erreur : 0 n\'est pas une durée valide pour entrer les absences.';
                            $this->load->view('scolarite/confirmation_absences', $data);
                        } else {
                            if (!isset($_POST['periode'])) {
                                $data['titre'] = 'Entrer Absences';
                                $data['type'] = 'error_box';
                                $data['informations'] = 'Erreur : vous avez oublié de spécifier la période de l\'absence.';
                                $this->load->view('scolarite/confirmation_absences', $data);
                            } else {
                                $dateCourante = $this->scolarite_modele->get_date_courante();
                                //si la date choisie n'est pas dans l intervalle des dates du debut et fin des cours
                                if ($this->convert_date($_POST['date']) < $dateCourante['debutCours'] || $this->convert_date($_POST['date']) > $dateCourante['finCours']) {
                                    $data['titre'] = 'Entrer Absences';
                                    $data['type'] = 'error_box';

                                    $data['informations'] = 'Erreur:  la date choisie <b>' . $this->convert_date($_POST['date']) . ' </b>ne fait pas partie du semestre courant <b>' .
                                            $this->get_session_nom($dateCourante['semestre']) . ' ' . $dateCourante['annee'] . '. </b>Si le semestre courant est en erreur, contactez l\'administrateur réseau.';
                                    $this->load->view('scolarite/confirmation_absences', $data);
                                } else {
                                    //on teste si la date choisi est dans le futur
                                    if ($this->convert_date($_POST['date']) > $date) {
                                        $data['titre'] = 'Entrer Absences';
                                        $data['type'] = 'error_box';
                                        $data['informations'] = 'Erreur : on ne peut choisir une date postérieure à la date présente';
                                        $this->load->view('scolarite/confirmation_absences', $data);
                                    } else {
                                        $this->scolarite_modele->enregistrer_absences($_POST);
                                        $data['titre'] = 'Entrer Absences';
                                        $data['type'] = 'valid_box';
										$data['informations'] = 'Les absences au groupe <b>' . $this->scolarite_modele->corrigerNumGroupe($_POST['idGroupe']) . '</b> ont bien été enregistrées.';
                                        /* $data['informations'] = 'Les absences au groupe <b>' . $_POST['idGroupe'] . '</b> ont bien été enregistrées.'; */
                                        $this->load->view('scolarite/confirmation_absences', $data);
                                    }
                                }
                            }
                        }
                    } else {
                        if ($_POST['duree2'] == 0) {
                            $data['titre'] = 'Entrer Absences';
                            $data['type'] = 'error_box';
                            $data['informations'] = 'Erreur : 0 n\'est pas une durée valide pour entrer une absence.';
                            $this->load->view('scolarite/confirmation_absences', $data);
                        } else {
                            if (!isset($_POST['periode'])) {
                                $data['titre'] = 'Entrer Absences';
                                $data['type'] = 'error_box';
                                $data['informations'] = 'Erreur : vous avez oublié de spécifier la période de l\'absence.';
                                $this->load->view('scolarite/confirmation_absences', $data);
                            } else {
                                $dateCourante = $this->scolarite_modele->get_date_courante();
                                //si la date choisie n'est pas dans l intervalle des dates du debut et fin des cours
                                if ($this->convert_date($_POST['date']) < $dateCourante['debutCours'] || $this->convert_date($_POST['date']) > $dateCourante['finCours']) {
                                    $data['titre'] = 'Entrer Absences';
                                    $data['type'] = 'error_box';

                                    $data['informations'] = 'Erreur:  La date choisie <b>' . $this->convert_date($_POST['date']) . ' </b>ne fait pas partie du semestre courant <b>' .
                                            $this->get_session_nom($dateCourante['semestre']) . ' ' . $dateCourante['annee'] . '. </b>Si le semestre courant est en erreur, contactez l\'administrateur réseau.';
                                    $this->load->view('scolarite/confirmation_absences', $data);
                                } else {
                                    //on teste si la date choisi est dans le futur
                                    if ($this->convert_date($_POST['date']) > $date) {
                                        $data['titre'] = 'Entrer Absences';
                                        $data['type'] = 'error_box';
                                        $data['informations'] = 'Erreur : on ne peut choisir une date postérieure à la date présente.';
                                        $this->load->view('scolarite/confirmation_absences', $data);
                                    } else {
                                        $this->scolarite_modele->enregistrer_absences($_POST);
                                        $data['titre'] = 'Entrer Absences';
                                        $data['type'] = 'valid_box';
										$data['informations'] = 'Les absences au groupe <b>' .  $this->scolarite_modele->corrigerNumGroupe($_POST['idGroupe']) . '</b> ont bien été enregistrées.';
                                        /* Correction 23 février 2013 RM $data['informations'] = 'Les absences au groupe <b>' . $_POST['idGroupe'] . '</b> ont bien été enregistrées.'; */
                                        $this->load->view('scolarite/confirmation_absences', $data);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /*
     * fonction pour supprimer l'absence en cas d'erreur ou motive une absence
     * on affiche la date et le semestre afin de trouver l'etudiant .
     */

    function retirer_absences() {
        $data = NULL;
        $data['titre'] = 'Choisir la date et le semestre';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $this->load->view('scolarite/choisir_date_absences', $data);
    }

    /*
     * fonction pour afficher les groupe ou un etudiant etait absent a une date precise
     */

    function afficher_groupe_abseces() {
//        $date = $this->convert_date($_POST['date']);
        $semestre = $_POST['session'];
        $annee = $_POST['annee'];
       $date =$_POST['date'];
        $tables = array("absences", "groupe");
        $join_keys = array('absences.idGroupe = groupe.idGroupe');
        $db_columns = array('absences.idGroupe as idGroupe', 'groupe.numGroupe as numGroupe', 'groupe.typeGroupe as typeGroupe', 'absences.annee as an', 'absences.semestre as session');
        $db_result = array('idGroupe', 'numGroupe', 'typeGroupe', 'an', 'session');
        $grid_columns = array('Groupe', 'Numéro du groupe', 'Type', "Année", 'Semestre');
        $action = 'afficher_etudiant_groupe_absences/'.$this->encode($date);
        $id_action = '';//absences.idGroupe
        $db_where = "where absences.annee = $annee and absences.semestre = $semestre and date ='$date"."'";
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action,$db_where, $db_result);
        $titre = 'Les groupes offerts au semestre <b>'.$this->get_session_nom($semestre).' '.$annee.
                '</b>, pour lesquels il y a des absences le <b>'.$date .'. </b>';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    //fonction pour afficher les etudiants absents dans un groupe
    function afficher_etudiant_groupe_absences($date,$idGroupe,$matricule = '')
    {
        $date = $this->decode($date);
        if(!file_exists("idGroupe.txt"))
        {
            $fp = fopen("idGroupe.txt", "w");
            file_put_contents("idGroupe.txt", $idGroupe);
            fclose($fp);
        }
        //important don't remove ********
        if($idGroupe == 'afficher_absences_pour_etudiant')    
        { 
            redirect('scolarite/afficher_absences_pour_etudiant/' .  $this->encode($date).
                    '/'.  $this->encode($matricule));
        }

        $tables = array("absences", "etudiant");
        $join_keys = array('absences.matricule = etudiant.matriculeEtudiant');
        $db_columns = array('absences.matricule as matricule', 'nom', 'prenom');
        $db_result = array('matricule', 'nom', 'prenom');
        $grid_columns = array('Matricule', 'Nom', 'Prénom');
        $action = 'afficher_absences_pour_etudiant/';
        $id_action = 'matricule';//absences.idGroupe';
        $db_where = "where date =' $date' and idGroupe = '$idGroupe '";
        $result = $this->search_modele->getSearchResult($tables, $db_columns, 
                $grid_columns, $join_keys, $action, $id_action,$db_where,
                $db_result);
        $titre = 'Les étudiants ayant été absents le  <b>'.$date.' </b>';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    //fonction pour charger la vue afin et envoyer les données nécessaires envoyées par les deux fonctions en haut
    //afin d'effectuer un retrait ou une motivation d'absences pour un etudiant.
    function afficher_absences_pour_etudiant($date,$matricule)
    {
        
        if(file_exists("idGroupe.txt"))
        {
            $fp = fopen("idGroupe.txt", "r");
            $idGroupe = file_get_contents("idGroupe.txt");
            fclose($fp);
            unlink("idGroupe.txt");
            $date = $this->decode($date);
            $matricule = $this->decode($matricule);
            $infoAbsences = $this->scolarite_modele->get_liste_etudiants_aretirer_absents($date,$idGroupe,$matricule);
            $infoAbsences['titre'] ='Retrait/Motivation des absences';
            $this->load->view('scolarite/retirer_absences',$infoAbsences);
        }
    }

    //fonction pour enregistrer soit le retrait ou la motivation d'une absence pour un étudiant.
    function retirer_absences_etudiant() 
    {
        
        if (!isset($_POST['choixFonction'])) 
        {
            $data['titre'] = 'Retirer/Motiver une absence';
            $data['type'] = 'error_box';
            $data['informations'] = 'Veuillez choisir entre les options Retrait et Motivation.';
            $this->load->view('scolarite/confirmation_absences', $data);
        } 
        else 
        {
            if (!isset($_POST['items']))
            {
                $data['titre'] = 'Retirer/Motiver une absence';
                $data['type'] = 'error_box';
                $data['informations'] = 'Veuillez sélectionner la ou les périodes que vous désirez retirer ou motiver.';
                $this->load->view('scolarite/confirmation_absences', $data);
            } 
            else 
            {
                //$tableauPeriode = array_keys($_POST['items'], 'on');
                $messageRetour = $this->scolarite_modele->motivee_retiree_absences($_POST);
                $data['titre'] = 'Retirer/Motiver une absence';
                $data['type'] = 'valid_box';

                $data['informations'] = $messageRetour;
                $this->load->view('scolarite/confirmation_absences', $data);
            }
        }
    }

    /*
     * fonction qui affiche tous les étudiants qui existent dans le système
     */

    function generer_rapport_absences_etudiant() {
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'choisir_annee_semestre_absences';
        $id_action = 'matriculeEtudiant';

        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);
        $titre = 'Générer le rapport d\'absences pour un étudiant';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    /*
     * fonction pour choisir l'année et le semestre afin de filtrer les groupes dans lesquels l'étudiant est inscrit
     */

    function choisir_annee_semestre_absences($matriculeEtudiant) {
        $data = NULL;
        $data['titre'] = 'Choisir Année et semestre';
        $data['matriculeEtudiant'] = $matriculeEtudiant;
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $this->load->view('scolarite/choisir_annee_semestre_absences', $data);
    }

    function afficher_absences() {
        // liste groupe contient tous les groupes dans lesquels l'étudiant a été inscrit n'importe quelle année
        $infoEtudiant = $this->scolarite_modele->recuperer_nom_etudiant($_POST['matriculeEtudiant']);
        $liste_groupe = $this->scolarite_modele->get_liste_groupe($_POST['matriculeEtudiant']);
        if (count($liste_groupe) == NULL) {
            $data['titre'] = 'Générer le rapport des absences';
            $data['type'] = 'warning_box';
            $data['informations'] = 'L\'étudiant <b>' . $_POST['matriculeEtudiant'] . ' : '.
                    $infoEtudiant['nom'].',  '.$infoEtudiant['prenom'].'</b> n\'est  
                        inscrit dans aucun groupe.';
            $this->load->view('scolarite/confirmation_absences', $data);
        } else {
            $liste_groupe_etudiant = $this->scolarite_modele->get_liste_groupe_absences($liste_groupe, $_POST['annee'], $_POST['session']);
            if (count($liste_groupe_etudiant) == NULL) {
                $data['titre'] = 'Générer le rapport des absences';
                $data['type'] = 'warning_box';
                $data['informations'] = 'L\'étudiant <b>' . $_POST['matriculeEtudiant'] . ' : '.
                    $infoEtudiant['nom'].',  '.$infoEtudiant['prenom'].'</b> n\'est  
                        inscrit dans aucun groupe durant le semestre  <b>' .
                        $this->get_session_nom($_POST['session']) . '  ' . $_POST['annee'] . ' </b> ';
                $this->load->view('scolarite/confirmation_absences', $data);
            } else {
                $data['infoAbsences'] = $this->scolarite_modele->get_informations_absences($liste_groupe_etudiant, $_POST['matriculeEtudiant'], $_POST['annee'], $_POST['session']);
                if (count($data['infoAbsences']) == NULL) {
                    $data['titre'] = 'Générer le rapport des absences';
                    $data['type'] = 'valid_box';
                    $data['informations'] = 'Aucune absence au semestre <b>' . $this->get_session_nom($_POST['session']) . '  ' .
                            $_POST['annee'] . ' </b> pour l\'étudiant <b>' . $_POST['matriculeEtudiant'] . ' : '.
                    $infoEtudiant['nom'].',  '.$infoEtudiant['prenom'].'</b>';
                    $this->load->view('scolarite/confirmation_absences', $data);
                } 
                else 
                {
                    $nomPrenom = $this->scolarite_modele->recuperer_nom_etudiant($data['infoAbsences']['matriculeEtudiant'][0]);
                    $data['titre'] = 'Absences non motivées pour l\'étudiant ' 
                    . $data['infoAbsences']['matriculeEtudiant'][0] . ' : '.
                            $nomPrenom['nom'].',  '.$nomPrenom['prenom'].' au semestre  ' .
                            $this->get_session_nom($data['infoAbsences']['semestre'][0]) . '  ' . $data['infoAbsences']['annee'][0];
                    $this->load->view('scolarite/afficher_absences_etudiant', $data);
                }
            }
        }
    }

    /*
     * fonction qui genere un rapport d'absence d'un etudiant
     */

    /*
     * fonction pour exporter un fichier excel qui contient tous les absences d'un etudiant
     */

    function generer_rapport() {
        // $infoAbsences = $_POST['infoAbsences'];
        $nomFichier = '';
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        
        $date =  utf8_encode(strftime("%d-%b-%Y"));
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0);
        /// formattage du Titre de la liste.
        $objSheet->getDefaultStyle()->getFont()->setName('Courier New');
        $objSheet->setCellValue('A1', 'INSTITUT UNIVERSITAIRE PROFESSIONNEL');
        $objSheet->getStyle('A1')->getFont()->setBold(TRUE);
        $nomPrenom = $this->scolarite_modele->recuperer_nom_etudiant($_POST['infoAbsences']['matriculeEtudiant'][0]);
        $titreFichierExcel = ' Absences non motivées pour l\'étudiant ' .
               $_POST['infoAbsences']['matriculeEtudiant'][0] . ' : '.
                            $nomPrenom['nom'].'  '.$nomPrenom['prenom'].' au semestre  ' .
                $this->get_session_nom($_POST['infoAbsences']['semestre'][0]) . '  ' .
                $_POST['infoAbsences']['annee'][0];
        $objSheet->setCellValue('A3',$titreFichierExcel );
        $objSheet->setCellValue('B4', 'Date :' . $date);


        $compteurVertical = 8;
        $objSheet->getStyle('B4')->getAlignment()->applyFromArray(
                array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'rotation' => 0,
                    'wrap' => true,
                    'font' => array('bold' => true)
                )
        );
        $groupe = array_unique($_POST['infoAbsences']['idGroupe']); // groupe qui contient des variables de tous les groupes mais distinct
        $compteurHorizontale = 2; // 2 represente le C
        //boucle pour ecrire le titre de tout les groupes qui existent
        for ($i = 0; $i < count($_POST['infoAbsences']['idGroupe']); $i++) {
            //on teste si l'index est supprimer lors de l'appel de la fonction array_unique
            if (isset($groupe[$i])) {
                $objSheet->setCellValueByColumnAndRow($compteurHorizontale, '7', $this->abreger_nom_groupe($groupe[$i]));
                $objXLS->getActiveSheet()->getColumnDimensionByColumn($compteurHorizontale)->setAutoSize(true);
                $compteurHorizontale++;
            }
            $_POST['infoAbsences']['indexHorizontale'] [$i] = $compteurHorizontale; //tableau pour stocker les index afin de rentrer les bon duree
        }
        //boucle pour afficher les dates et la duree de chaque absences
        for ($i = 0; $i < count($_POST['infoAbsences']['date']); $i++) {
            $objSheet->setCellValue('B' . $compteurVertical, $_POST['infoAbsences']['date'][$i] . '  ' . $_POST['infoAbsences']['periode'][$i]);
            for ($j = 0; $j < count($_POST['infoAbsences']['idGroupe']); $j++) {
                if (isset($groupe[$j])) {
                    if ($groupe[$j] == $_POST['infoAbsences']['idGroupe'][$i]) {
                        $objSheet->setCellValueByColumnAndRow($_POST['infoAbsences']['indexHorizontale'][$i] - 1, $compteurVertical, $_POST['infoAbsences']['duree'][$i]);
                    }
                }
            }
            $compteurVertical++;
        }
        $objSheet->setCellValue('B' . $compteurVertical, 'Total ');
        $compteurHorizontaleTotal = 2;

        //boucle pour calculer le total des heures que l'etudaint etait absent a un groupe
        for ($i = 0; $i < count($groupe); $i++) {
            $sum = 0;
            for ($j = 8; $j < $compteurVertical; $j++) {
                $sum += $objSheet->getCellByColumnAndRow($compteurHorizontaleTotal, $j)->getValue();
            }
            $objSheet->setCellValueByColumnAndRow($compteurHorizontaleTotal, $compteurVertical, $sum);
            $compteurHorizontaleTotal++; //le compteur doit rester en dernier 
        }

        //boucle pour calculer la somme des heures que l'etudiant etait absent pour chaque journne
        $objSheet->setCellValueByColumnAndRow($compteurHorizontale, 7, 'Total');
        for ($j = 8; $j < $compteurVertical; $j++) {
            $sum = 0;
            for ($i = 2; $i < $compteurHorizontale; $i++) {
                $sum += $objSheet->getCellByColumnAndRow($i, $j)->getValue();
            }
            $objSheet->setCellValueByColumnAndRow($compteurHorizontaleTotal, $j, $sum);
        }

        //boucle pour dessiner le tableau
        for ($k = 1; $k < $compteurHorizontale + 1; $k++) {
            for ($j = 7; $j < $compteurVertical + 1; $j++) {
                if (($k == $compteurHorizontale) || ($j == 7) || ($j == $compteurVertical)) {
                    $objSheet->getStyleByColumnAndRow($k, $j)->getFont()->setBold(TRUE);
                }

                $objSheet->getStyleByColumnAndRow($k, $j)->getBorders()->applyFromArray(
                        array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
            }
        }

        $objXLS->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
        $nomFichier = 'Absences_' . $_POST['infoAbsences']['matriculeEtudiant'][0] . '_' . $this->get_session_nom($_POST['infoAbsences']['semestre'][0]) . '_' .
                $_POST['infoAbsences']['annee'][0];

        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');

        $objWriter->save('php://output');
    }

    /*
     * fonction pour changer le groupe plus court enlever groupe et mettre juste les deux lettres
     * significatif pour le type du groupe
     */

	function abreger_nom_groupe($nomGroupe)
    {
        $nomAbrege = '';
        if (strpos($nomGroupe, "01-Groupe") > 0) $nomAbrege = str_replace('01-Groupe', '-P', $nomGroupe);	// P = Printemps
			else
				if (strpos($nomGroupe, "02-Groupe") > 0) $nomAbrege = str_replace('02-Groupe', '-E', $nomGroupe);	// E = Été
			else 
				$nomAbrege = str_replace('03-Groupe', '-A', $nomGroupe);	// A = Automne
		$nomAbrege = str_replace('Theorie', 'Thr', $nomAbrege);
        $nomAbrege = str_replace('Projet', 'Prj', $nomAbrege);
        $nomAbrege = str_replace('Stage', 'Stg', $nomAbrege);
        return $nomAbrege;
    }

    function generer_rapport_absences_motivees() {
        $data['titre'] = 'Générer le rapport des absences motivées pour un semestre donné .';
         $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $this->load->view("scolarite/choisir_semestre_absence_motive", $data);
    }

    function afficher_absences_motivee() 
    {
        
        $infoAbsences = $this->scolarite_modele->get_liste_absences_motivee_etudiants($_POST['annee'], $_POST['session']);
        $annee = $_POST['annee'];
        $semestre = $_POST['session'];
        if (count($infoAbsences) == NULL) {
            $data['titre'] = 'Générer le rapport des absences motivées.';
            $data['type'] = 'valid_box';
            $data['informations'] = 'Aucune absence motivée au semestre <b>' . $this->get_session_nom($semestre) . '  ' . $annee;
            $this->load->view('scolarite/confirmation_absences', $data);
        } else {
            $absence = true;
            $this->ecrire_rapport_absences_global($infoAbsences, $absence);
        }
    }

    function generer_rapport_global_etudiant() 
    {
        $data['titre'] = 'Générer le rapport global des absences non motivées pour le semestre';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $this->load->view("scolarite/choisir_semestre_global", $data);
    }

    function afficher_etudiant_rapport_global() {
        $session = $this->scolarite_modele->get_session_courante();
        $infoAbsences = $this->scolarite_modele->get_liste_global_etudiants_absents($_POST['annee'], $_POST['session']);
        $annee = $session['annee'][0];
        $semestre = $_POST['session'];
        if (count($infoAbsences) == NULL) {
            $data['titre'] = 'Générer le rapport des absences';
            $data['type'] = 'valid_box';
            $data['informations'] = 'Aucune absence au semestre <b>' . $this->get_session_nom($semestre) . '  ' . $annee;
            $this->load->view('scolarite/confirmation_absences', $data);
        } else {
            $absence = false;
            $this->ecrire_rapport_absences_global($infoAbsences, $absence);
        }
    }

    function ecrire_rapport_absences_global($infoAbsences, $absence) {

        $nomFichier = '';
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $date = utf8_encode(strftime("%d %B %Y"));;
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0);
        /// formattage du Titre de la liste.
        $objSheet->getDefaultStyle()->getFont()->setName('Courier New');
        $objSheet->setCellValue('A1', 'INSTITUT UNIVERSITAIRE PROFESSIONNEL');
        $objSheet->getStyle('A1')->getFont()->setBold(TRUE);
        if (!$absence) {
            $objSheet->setCellValue('A3', ' Absences non motivées au semestre  ' . $this->get_session_nom($infoAbsences['semestre'][0]) . '  ' . $infoAbsences['annee'][0]);
        } else {
            $objSheet->setCellValue('A3', ' Absences motivées au semestre  ' . $this->get_session_nom($infoAbsences['semestre'][0]) . '  ' . $infoAbsences['annee'][0]);
        }
        $objSheet->setCellValue('B4', 'Date : ' . $date);
        $compteurVertical = 8;
        $objSheet->getStyle('B4')->getAlignment()->applyFromArray(
                array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'rotation' => 0,
                    'wrap' => true,
                    'font' => array('bold' => true)
                )
        );

        $matricule = array_unique($infoAbsences['matriculeEtudiant']); // groupe qui contient des variables de tous les groupes mais distinct
        sort($matricule);
        $compteurHorizontale = 2; // 2 represente le C
        //boucle pour ecrire le titre de tout les groupes qui existent
        for ($i = 0; $i < count($matricule); $i++) {

            $objSheet->setCellValueByColumnAndRow($compteurHorizontale, '7', $matricule[$i]);
            $objXLS->getActiveSheet()->getColumnDimensionByColumn($compteurHorizontale)->setAutoSize(true);
            $compteurHorizontale++;
        }

        //boucle pour afficher les dates et la duree de chaque absences
        for ($i = 0; $i < count($infoAbsences['date']); $i++) {
            $objSheet->setCellValue('B' . $compteurVertical, $infoAbsences['date'][$i] . '  ' . $infoAbsences['periode'][$i]);
            for ($j = 0; $j < count($matricule); $j++) {
                if ($matricule[$j] == $infoAbsences['matriculeEtudiant'][$i]) {
                    $objSheet->setCellValueByColumnAndRow($j + 2, $compteurVertical, $infoAbsences['duree'][$i]);
                }
            }
            $compteurVertical++;
        }

        $objSheet->setCellValue('B' . $compteurVertical, 'Total ');
        $compteurHorizontaleTotal = 2;

        //boucle pour calculer le total des heures que l'etudaint etait absent a un groupe
        for ($i = 0; $i < count($matricule); $i++) {
            $sum = 0;
            for ($j = 8; $j < $compteurVertical; $j++) {
                $sum += $objSheet->getCellByColumnAndRow($compteurHorizontaleTotal, $j)->getValue();
            }
            $objSheet->setCellValueByColumnAndRow($compteurHorizontaleTotal, $compteurVertical, $sum);
            $compteurHorizontaleTotal++; //le compteur doit rester en dernier 
        }

        //boucle pour calculer la somme des heures que l'etudiant etait absent pour chaque journne
        $objSheet->setCellValueByColumnAndRow($compteurHorizontale, 7, 'Total');
        for ($j = 8; $j < $compteurVertical; $j++) {
            $sum = 0;
            for ($i = 2; $i < $compteurHorizontale; $i++) {
                $sum += $objSheet->getCellByColumnAndRow($i, $j)->getValue();
            }
            $objSheet->setCellValueByColumnAndRow($compteurHorizontaleTotal, $j, $sum);
        }

        //boucle pour dessiner le tableau
        for ($k = 7; $k < $compteurHorizontale; $k++) {
            for ($j = 7; $j < $compteurVertical; $j++) {
                $objSheet->getStyleByColumnAndRow($i, $j)->getBorders()->applyFromArray(
                        array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
            }
        }

        //boucle pour dessiner le tableau
        for ($k = 1; $k < $compteurHorizontale + 1; $k++) {
            for ($j = 7; $j < $compteurVertical + 1; $j++) {
                if (($k == $compteurHorizontale) || ($j == 7) || ($j == $compteurVertical)) {
                    $objSheet->getStyleByColumnAndRow($k, $j)->getFont()->setBold(TRUE);
                }

                $objSheet->getStyleByColumnAndRow($k, $j)->getBorders()->applyFromArray(
                        array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
            }
        }
        $objXLS->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);

        if (!$absence) {
            $nomFichier = 'Absences_global_' . $this->get_session_nom($infoAbsences['semestre'][0]) . '_' . $infoAbsences['annee'][0];
        } else {
            $nomFichier = 'Absences_motivees_' . $this->get_session_nom($infoAbsences['semestre'][0]) . '_' . $infoAbsences['annee'][0];
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');

        $objWriter->save('php://output');
    }

    /*
     * fonction pour afficher une vue pour que l utilisateur peut choisir l annee et le semestre 
     * pour nous aider a filtrer les groupes
     */

    function generer_rapport_absences_groupe() {
        $data['titre'] = 'Générer le rapport des absences par groupe.';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $this->load->view("scolarite/choisir_annee_session_rapport_abs", $data);
    }

    /*
     * fonction qui affiche tous les groupes qui se donnent a un semestre precis
     */

    function afficher_groupe_rapport() {
        $annee = $_POST['annee'];
        $semestre = $_POST['session'];
        $tables = array("groupe");
        $join_keys = null;
        $db_columns = array('idGroupe', 'numGroupe', 'typeGroupe', 'annee', "case when semestre = 1 then 'Printemps' when semestre = 2 then '&#201;t&#233;'
                when semestre = 3 then 'Automne' end as semestre");
        $db_result_columns = array('idGroupe', 'numGroupe', 'typeGroupe', 'annee','semestre');
        $grid_columns = array('Groupe', 'Numéro du groupe', 'Type', "Année", 'Semestre');
        $action = 'afficher_rapport_groupe_absences/' . $this->encode($annee) . '/' . $this->encode($semestre);

        $id_action = 'idGroupe';

        $db_where = "where annee = $annee and semestre = $semestre";
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $db_where,$db_result_columns);
        $titre = 'Les groupes offerts au semestre  <b>' . $this->get_session_nom($_POST['session']) . '  ' . $_POST['annee'] . '</b>';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        $this->load->view("recherche_parametree", $data);
    }

    function afficher_rapport_groupe_absences($annee, $semestre, $idGroupe) {
        $annee = $this->decode($annee);
        $semestre = $this->decode($semestre);
        $infoAbsences = $this->scolarite_modele->get_liste_etudiants_absents($idGroupe, $annee, $semestre);
        if (count($infoAbsences) == NULL) {
            $data['titre'] = 'Générer le rapport des absences';
            $data['type'] = 'valid_box';
            $data['informations'] = 'Aucune absence au semestre <b>' . $this->get_session_nom($semestre) . '  ' .
                    $annee . ' </b> pour le groupe <b>' . $idGroupe . '</b>';
            $this->load->view('scolarite/confirmation_absences', $data);
        } else {
            $data['infoAbsences'] = $infoAbsences;
            /* RM modif 26 février 2013
			$data['titre'] = 'Absences non motivées au groupe <b>' . $this->abreger_nom_groupe($idGroupe)
                    . ' </b> <br>au semestre <b>' . $this->get_session_nom($semestre) . ' ' . $annee . ' </b>'; */
			$data['titre'] = 'Absences non motivées au groupe <b>' . $this->scolarite_modele->corrigerNumGroupe($idGroupe)
                    . ' </b> <br>au semestre <b>' . $this->get_session_nom($semestre) . ' ' . $annee . ' </b>';
            $this->load->view("scolarite/afficher_rapport_groupe_absences", $data);
        }
    }

    /*
     * fonction pour ecrire le rapport en excel pour 
     * les liste d'absences par groupe.
     */

    function ecrire_rapport_absences_groupe() {

        $nomFichier = '';
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $date = utf8_encode(strftime("%d %B %Y"));
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0);
        /// formatage du Titre de la liste.
        $objSheet->getDefaultStyle()->getFont()->setName('Courier New');
        $objSheet->setCellValue('A1', 'INSTITUT UNIVERSITAIRE PROFESSIONNEL');
        $objSheet->getStyle('A1')->getFont()->setBold(TRUE);
        /* Modif RM 26 février 2013
		$objSheet->setCellValue('A3', ' Absences non motivées au groupe ' . $this->abreger_nom_groupe($_POST['infoAbsences']['idGroupe'][0]) . ' au semestre  ' .
                $this->get_session_nom($_POST['infoAbsences']['semestre'][0]) . '  ' . $_POST['infoAbsences']['annee'][0]); */
        $objSheet->setCellValue('A3', ' Absences non motivées au groupe ' . $this->scolarite_modele->corrigerNumGroupe($_POST['infoAbsences']['idGroupe'][0]) . ' au semestre  ' .
                $this->get_session_nom($_POST['infoAbsences']['semestre'][0]) . '  ' . $_POST['infoAbsences']['annee'][0]);
		$objSheet->setCellValue('B4', 'Date :' . $date);
        $compteurVertical = 8;
        $objSheet->getStyle('B4')->getAlignment()->applyFromArray(
                array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    'rotation' => 0,
                    'wrap' => true,
                    'font' => array('bold' => true)
                )
        );

        $matricule = array_unique($_POST['infoAbsences']['matriculeEtudiant']); // groupe qui contient des variable de tout les groupes mais distinct
        sort($matricule);
        $compteurHorizontale = 2; // 2 represente le C
        //boucle pour ecrire le titre de tous les groupes qui existent
        for ($i = 0; $i < count($matricule); $i++) {
            $objSheet->setCellValueByColumnAndRow($compteurHorizontale, '7', $matricule[$i]);
            $objXLS->getActiveSheet()->getColumnDimensionByColumn($compteurHorizontale)->setAutoSize(true);
            $compteurHorizontale++;
        }

        //boucle pour afficher les dates et la duree de chaque absence
        for ($i = 0; $i < count($_POST['infoAbsences']['date']); $i++) {
            $objSheet->setCellValue('B' . $compteurVertical, $_POST['infoAbsences']['date'][$i] . '  ' . $_POST['infoAbsences']['periode'][$i]);
            for ($j = 0; $j < count($matricule); $j++) {
                if ($_POST['infoAbsences']['matriculeEtudiant'][$i] == $matricule[$j]) {
                    $objSheet->setCellValueByColumnAndRow($j + 2, $compteurVertical, $_POST['infoAbsences']['duree'][$i]);
                }
            }
            $compteurVertical++;
        }


        $objSheet->setCellValue('B' . $compteurVertical, 'Total ');
        $compteurHorizontaleTotal = 2;

        //boucle pour calculer le total des heures où l'etudiant etait absent a un groupe
        for ($i = 0; $i < count($matricule); $i++) {
            $sum = 0;
            for ($j = 8; $j < $compteurVertical; $j++) {
                $sum += $objSheet->getCellByColumnAndRow($compteurHorizontaleTotal, $j)->getValue();
            }
            $objSheet->setCellValueByColumnAndRow($compteurHorizontaleTotal, $compteurVertical, $sum);
            $compteurHorizontaleTotal++; //le compteur doit rester en dernier 
        }

        //boucle pour calculer la somme des heures où l'etudiant etait absent pour chaque jour
        $objSheet->setCellValueByColumnAndRow($compteurHorizontale, 7, 'Total');
        for ($j = 8; $j < $compteurVertical; $j++) {
            $sum = 0;
            for ($i = 2; $i < $compteurHorizontale; $i++) {
                $sum += $objSheet->getCellByColumnAndRow($i, $j)->getValue();
            }
            $objSheet->setCellValueByColumnAndRow($compteurHorizontaleTotal, $j, $sum);
        }
        //boucle pour dessiner les lignes du tableau
        for ($k = 7; $k < $compteurHorizontale; $k++) {
            for ($j = 7; $j < $compteurVertical; $j++) {
                $objSheet->getStyleByColumnAndRow($i, $j)->getBorders()->applyFromArray(
                        array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
            }
        }

        //boucle pour dessiner les lignes du  tableau
        for ($k = 1; $k < $compteurHorizontale + 1; $k++) {
            for ($j = 7; $j < $compteurVertical + 1; $j++) {
                if (($k == $compteurHorizontale) || ($j == 7) || ($j == $compteurVertical)) {
                    $objSheet->getStyleByColumnAndRow($k, $j)->getFont()->setBold(TRUE);
                }

                $objSheet->getStyleByColumnAndRow($k, $j)->getBorders()->applyFromArray(
                        array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
            }
        }
        $objXLS->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
		// corriection RM 26 février 2013 $nomFichier = 'Absences_' . $this->abreger_nom_groupe($_POST['infoAbsences']['idGroupe'][0]);
        $nomFichier = 'Absences_' . $this->scolarite_modele->corrigerNumGroupe($_POST['infoAbsences']['idGroupe'][0]);
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');

        $objWriter->save('php://output');
    }

    function generer_bulletin($type) 
    {
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        switch ($type) 
        {
            case 1:
                $data['redirection'] = 'generer_bulletin_lot';
                $data['titre'] = 'Générer Bulletins en lot';
                $this->load->view('scolarite/choix_periode', $data);
                break;
            case 2:
                $data['redirection'] = 'choix_etudiant';
                $data['titre'] = 'Générer Bulletin individuel';
                $this->load->view('scolarite/choix_periode', $data);
                break;
            default:
                $this->load->view('scolarite/index');
                break;
        }
    }

    function generer_bulletin_lot() {

        $this->form_validation->set_rules('annee', 'Annee', 'required');
        $this->form_validation->set_rules('semestre', 'Semestre', 'required');

		if ($this->form_validation->run()) {

            $data['generes'] = array();
            $data['erreurs'] = array();
            $data['deja_calcules'] = array();
			$data['annee'] = $this->input->post('annee');		// ajout 2.2.2
			$data['semestre'] = $this->semestreTexte($this->input->post('semestre')); // ajout 2.2.2
			
            $matricules = $this->scolarite_modele->recuperer_etudiants_actifs($this->input->post('annee'), $this->input->post('semestre'));
			if (is_array($matricules)) 
            {
                foreach ($matricules as $matricule) 
                {
                    $confirmation = $this->generer_bulletin_individuel($matricule, '1');	// génère le fichier php du bulletin ... et autres détails

                    switch ($confirmation) 
                    {
                        case -1:
                            $data['erreurs'][] = $matricule;
                            break;
                        case 0 :
                            $data['deja_calcules'][] = $matricule;
                            break;
                        case 1:
                            $data['generes'][] = $matricule;
                            break;
                        default:
                            die("Une erreur inconnue est survenue lors de la génération des bulletins !");
                            break;
                    }
                }
            }
			$this->load->view("scolarite/resultats_bulletins", $data);	// affichage des résultats
        }
        else
            $this->load->view('scolarite/index');
    }

    function generer_bulletin_individuel($matricule, $mode = '') 
    {
        $data['generes'] = array();
        $data['erreurs'] = array();
        $data['deja_calcules'] = array();
        $data['annee'] = 'vide';		// ajout 2.2.2. Poour compatibilité avec la génération des bulletins en lot.
		$data['semestre'] = 'vide'; 	// ajout 2.2.2, idem quoique inutile
        $valide = false;
        switch ($mode) 
        {
            case '':
                $valide = true;
                break;
            case 1:
                $valide = true;
                break;
            default:
                break;
        }
        //vérification sécuritaire
        if (!$valide)
        {
            $this->load->view('scolarite/index');	// ?? RM 6 juin 2013 : ne sert à rien, $valide toujours mis à true.
        }
        $valide = false;
        if ($this->scolarite_modele->bulletin_a_calculer($matricule)) {
            if ($this->scolarite_modele->verifier_entrees_plan_etudes($matricule)) {
                if ($this->scolarite_modele->verifier_cours_repris_annuel($matricule))
                    if ($this->scolarite_modele->verifier_cours_echoues($matricule))
                        if ($this->scolarite_modele->verifier_notes_cours_repris($matricule))
                           if($this->scolarite_modele->calculer_moyennes_semestrielles($matricule))
                                if ($this->scolarite_modele->calculer_moyennes_annuelles($matricule))
                                    if ($this->scolarite_modele->verifier_cours_repris($matricule))
                                        if ($this->scolarite_modele->calculer_moyenne_generale($matricule))
                                            if ($this->produire_bulletin($matricule))
                                                $valide = true;

                if ($valide) 
                {
                    if ($mode == 1)
                        return 1;
                    $data['generes'][] = $matricule;
                }
                else 
                {
                    if ($mode == 1)
                        return -1;
                    $data['erreurs'][] = $matricule;
                }
            }
            else {
                if ($mode == 1)
                    return -1;
                $data['erreurs'][] = $matricule;
            }
        }
        else 
        { 
            //bulletin n'est pas à calculer
            if ($mode == 1)
                return 0;
            $data['deja_calcules'][] = $matricule;
        }
        $this->load->view("scolarite/resultats_bulletins", $data);
    }


    function produire_bulletin($matricule) {
        $NB_MODULE_PAGE = 29;			// réduit de 31 à 29 pour 2 mentions éventuelles (1 par an) RM 8 février 2013
        $NB_MODULE_DERNIERE_PAGE = 25;	// réduit de 27 à 25 pour 2 mentions éventuelles (1 par an) RM 8 février 2013
        
        $info_personnelles = $this->scolarite_modele->recuperer_info_personnelles($matricule);
        $annees = $this->scolarite_modele->recuperer_annees($matricule);

        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $dateEmission = utf8_encode(strftime("%d %B %Y"));

        $info_personnelles['dateEmission'] = $dateEmission;
        
        $bulletin = new Bulletin();

        $bulletin->Initialiser();
        $bulletin->NouvellePage($info_personnelles);
        $index_annees = 0;

        $nbModules = 0;
        $lastYear = 0;
        $nbAnnees = 0;
        $compteurAnnees = 0;
        if($annees != null)
            $nbAnnees = count ($annees);
        
        foreach ($annees as $annee) {
            $compteurAnnees ++;
            
            if ($annee == null)
                continue;
            
            /**Faire le compte des modules pour une page si plus de 30 modules passer à une autre page**/
            $lastYear = 0;
            foreach (array_keys($annee) as $semestre) {
                $notes_semestres = $this->scolarite_modele->recuperer_cours_semestre($matricule, $annee[$semestre], $semestre);
               
                if(is_array($notes_semestres))
                {
                    $lastYear += count($notes_semestres);
                }
            }
            /****/
            $nbModules += $lastYear;
            
          if (($index_annees != 0 && ($index_annees % 2 == 0)) || ($nbModules > $NB_MODULE_PAGE && $compteurAnnees < $nbAnnees) ||($nbModules > $NB_MODULE_DERNIERE_PAGE && $compteurAnnees == $nbAnnees)) 
          {
                $nbModules = $lastYear;
                $bulletin->AjouterLabelSuiteReleve();
                $bulletin->NouvellePage($info_personnelles);
            }
            $bulletin->AjouterAnnee();
            foreach (array_keys($annee) as $semestre) {
                $notes_semestres = $this->scolarite_modele->recuperer_cours_semestre($matricule, $annee[$semestre], $semestre);
                $bulletin->AjouterSemestre($annee[$semestre], $semestre, $notes_semestres);
                if ($semestre != '3')
                    $periode = $annee[$semestre];
                else 
                    $periode = $annee[$semestre]+1;
            }

            $estSemestre = count($annee) == -1;
            if ($estSemestre == TRUE) {
                $moyenne;
                foreach (array_keys($annee) as $semestre) {
                    $nbCredits = null;
                    switch ($semestre) {
                        case 1:
                            $periode = 'de printemps ' . $annee['1'];
                            $nbCredits = $this->scolarite_modele->recuperer_nbCredits_semestriel($matricule, $annee['1'], $semestre);
                            $moyenne = $this->scolarite_modele->recuperer_moyenne_semestre($matricule, $annee['1'], 'moyennePrintemps');
							break;
                        case 2:
                            $periode = 'd\'ete ' . $annee['2'];
                            $nbCredits = $this->scolarite_modele->recuperer_nbCredits_semestriel($matricule, $annee['2'], $semestre);
                            $moyenne = $this->scolarite_modele->recuperer_moyenne_semestre($matricule, $annee['2'], 'moyenneEte');
							break;
                        case 3:
                            $periode = 'd\'automne ' . $annee['3'];
                            $nbCredits = $this->scolarite_modele->recuperer_nbCredits_semestriel($matricule, $annee['3'], $semestre);
                            $moyenne = $this->scolarite_modele->recuperer_moyenne_semestre($matricule, $annee['3'] + 1, 'moyenneAutomne');
							break;
                    }
                }
				$bulletin->AjouterMoyenne(TRUE, $periode, $moyenne, $nbCredits);
			} else {
                $moyenne = $this->scolarite_modele->recuperer_moyenne_annuelle($matricule, $periode);
                $nbCredits = $this->scolarite_modele->recuperer_nbCredits_annuel($matricule, $periode);
                $periode = ($periode - 1) . '-' . $periode;
                $bulletin->AjouterMoyenne(FALSE, $periode, $moyenne, $nbCredits);
				// fonction pour mention RM 6 février 2013
					if ($nbCredits > 50) {	// mention pour une année complète seulement, minimum 51 crédits
					$PasEchecs = $this->scolarite_modele->presence_echecs_pour_mention($matricule, $annee); // ajout
						if ($moyenne >=12.995 && ! $PasEchecs) {	// ajout de la mention éventuelle en fin d'année scolaire
					$bulletin->AjouterMention($moyenne); // ajout
					}
					}
                $bulletin->AjouterLigneVierge();
            }
            $index_annees++;
        }
        
        $bulletin->AjouterLabelFinReleve();

        $nbCreditsGeneral = $this->scolarite_modele->recuperer_nbCredits_general($matricule);
        $moyenneGenerale = $this->scolarite_modele->recuperer_moyenne_generale($matricule);
        $decision = $this->scolarite_modele->recuperer_decision($matricule, $info_personnelles['sexe']);
        $bulletin->AjouterSanction($nbCreditsGeneral, $moyenneGenerale, $decision);
        $bulletin->Generer($matricule, 'scolarite'); // 
        return TRUE;
    }

	
    function choix_etudiant() {
        $this->form_validation->set_rules('annee', 'Annee', 'required');
        $this->form_validation->set_rules('semestre', 'Semestre', 'required');
        if ($this->form_validation->run()) {
            $tables = array('planetudes', 'etudiant');
            $join_keys = array('etudiant.matriculeEtudiant = planetudes.matriculeEtudiant');
            $db_columns = array('etudiant.matriculeEtudiant as matricule', 'nom', 'prenom');
            $result_columns = array('matricule', 'nom', 'prenom');
            $grid_columns = array('Matricule', 'Nom', 'Prénom');

            $action = 'generer_bulletin_individuel';
            $id_action = '';
            $where = "WHERE annee = " . $this->input->post('annee') . " AND semestre = " . $this->input->post('semestre');

            $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);

            $titre = 'Choisir un étudiant';
            $controlleur = "scolarite";
            $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


            $this->load->view("recherche_parametree", $data);
        }
        else
            $this->load->view('scolarite/index');
    }

    function choix_etudiant_ajout_equivalence() 
	{
		if (strlen($this->passwordChef) > 0) {	// si pas de mot de passe : on ne demande rien
			$this->form_validation->set_rules('pass', 'mot de passe', 'required');
		}
		if ((isset($_POST['pass'])) and strlen($this->passwordChef) > 0)
			$this->form_validation->set_rules('pass', 'mot de passe',
			'callback_verifier_chef_scolarite['.$_POST['pass'].']');
		if (($this->form_validation->run()) or strlen($this->passwordChef) == 0)
		{
			$tables = array("etudiant , dossierEtudiant, programme");
			$join_keys = '';
			$db_columns = array('etudiant.matriculeEtudiant as matricule', 'etudiant.nom as nom', 'etudiant.prenom',
				'programme.nom as prg', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ", 'dossierEtudiant.idProgramme as idPrg');
			$result_columns = array('matricule', 'nom', 'prenom', 'prg', 'actif');
			$grid_columns = array('Matricule', 'Nom', 'Prénom', 'Programme', 'Actif ?');

			$action = 'ajouter_equivalence/';
			$id_action = '';
			$more_than_one_id = array('matricule', 'actif', 'idPrg');

			$where = "where etudiant.matriculeEtudiant = dossieretudiant.matriculeEtudiant and dossierEtudiant.idProgramme = programme.idProgramme  order by etudiant.matriculeEtudiant Asc";

			$result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns, $more_than_one_id);
			$titre = "Ajouter une &#233;quivalence: choisir un &#233;tudiant";
			$controlleur = "scolarite";
			$data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
			$this->load->view("recherche_parametree", $data);
		}
		else
		{
			$data['typeInterface'] = 'choix_etudiant_ajout_equivalence';
			$data['titre'] = 'L\'accès à ce sous-menu est réservé.<br> Entrez le mot de passe.';
            $this->load->view("scolarite/chef_scolarite_pass", $data);
		}
    }

    function ajouter_equivalence($matricule = null, $actif = null, $idProg = null) 
    {
       
        $this->form_validation->set_rules('matricule', 'matricule', 'required');
        if ($this->form_validation->run()) 
        {
            $matricule = $_POST['matricule'];
            $infoEtudiants = $this->scolarite_modele->get_name_etudiants($matricule);
            $note = -1;
            $cote = 'EQ';
            $idProg = $_POST['idProg'];
            $sigle = $_POST['sigle'];
            $lien = 'OB';
            //if ($lien == "EQ")
            //    $lien = "OB";
            $validation = $this->scolarite_modele->entrees_valides($note, $cote, $lien);
            // if ($validation['valide']) {		Modif RM 5 mars 2013
			if ($validation['valide'] && (strlen(trim($sigle))) > 0) {	// test sur le sigle pour exclure le sigle vide qui est offert par défaut
                $date['semestre'] = $_POST['semestre'];
                $date['annee'] = $_POST['annee'];
                if (!$this->scolarite_modele->etudiant_deja_inscrit($matricule, $sigle, $date['annee'], $date['semestre'])) 
                {
                    if ($this->scolarite_modele->inserer_plan_detudes($matricule, $sigle, $date['semestre'], $date['annee'], $note, $cote, $lien)) {
                        $data['typeBox'] = 'valid_box';
                        $data['informations'] = 'Équivalence entrée:  <b>Matricule:</b>' . $matricule . " <b>Sigle:</b> " . $sigle . " <b>Année: </b>" . $date['annee'] . " <b>Semestre:</b>" . $date['semestre'];
                        $this->load->view('scolarite/modification_confirme', $data);
                    } else {
                        die("Un problème est survenu, veuillez communiquer avec le développeur !");
                    }
                } 
                else 
                {
                    $data['annee'] = $this->scolarite_modele->recuperer_annee();
                    $data['courante'] = $this->scolarite_modele->get_session_courante();
                    $data['errorMsg'] = "Cet étudiant est déjà inscrit au sigle: " . $sigle . " en " . $date['annee'] . "-" . $date['semestre'];
                    $data['class'] = "error_box";
                    $data['cours'] = $this->scolarite_modele->recuperer_cours();
                    $data['matricule'] = $matricule;
                    $infoEtudiants = $this->scolarite_modele->get_name_etudiants($matricule);
                    $data['actif'] = $actif;
                    $data['idProg'] = $idProg;
                    $data['nom'] = $infoEtudiants['nom'];
                    $data['prenom'] = $infoEtudiants['prenom'];
                    $this->load->view('scolarite/ajouter_equivalence', $data);
                }
           
            } 
            elseif (strlen(trim($sigle)) == 0 ) {	// sigle vide : on a choisi l'option par défaut vide ! Ajout de ce elseif RM 5 mars 2013
					$data['annee'] = $this->scolarite_modele->recuperer_annee();
                    $data['courante'] = $this->scolarite_modele->get_session_courante();
                    $data['errorMsg'] = "Vous devez sélectionner un sigle ! ";
                    $data['class'] = "error_box";
					$data['cours'] = $this->scolarite_modele->recuperer_cours();
                    $data['matricule'] = $matricule;
                    $infoEtudiants = $this->scolarite_modele->get_name_etudiants($matricule);
                    $data['actif'] = $actif;
                    $data['idProg'] = $idProg;
                    $data['nom'] = $infoEtudiants['nom'];
                    $data['prenom'] = $infoEtudiants['prenom'];
                    $this->load->view('scolarite/ajouter_equivalence', $data);				
			}
			else 
            {
                $data['annee'] = $this->scolarite_modele->recuperer_annee();
                $data['courante'] = $this->scolarite_modele->get_session_courante();
                $data['errorMsg'] = $validation['erreur_msg'];
                $data['class'] = "error_box";
                $data['cours'] = $this->scolarite_modele->recuperer_cours();
                $data['matricule'] = $matricule;
                 $infoEtudiants = $this->scolarite_modele->get_name_etudiants($matricule);
                $data['actif'] = $actif;
                $data['idProg'] = $idProg;
                $data['nom'] = $infoEtudiants['nom'];
                $data['prenom'] = $infoEtudiants['prenom'];
                $this->load->view('scolarite/ajouter_equivalence', $data);
            }
        } 
        else 
        {
            $data['annee'] = $this->scolarite_modele->recuperer_annee();
            $data['courante'] = $this->scolarite_modele->get_session_courante();
            $data['cours'] = $this->scolarite_modele->recuperer_cours();
            $data['matricule'] = $matricule;
             $infoEtudiants = $this->scolarite_modele->get_name_etudiants($matricule);
            $data['actif'] = $actif;
            $data['idProg'] = $idProg;
            $data['nom'] = $infoEtudiants['nom'];
            $data['prenom'] = $infoEtudiants['prenom'];
            $this->load->view("scolarite/ajouter_equivalence", $data);
        }
    }
	

    function choix_etudiant_retrait_equivalence()
    {
	if (strlen($this->passwordChef) > 0) {	// RM
		$this->form_validation->set_rules('pass', 'mot de passe', 'required');
	}	
		if ((isset($_POST['pass'])) and strlen($this->passwordChef) > 0) // RM
			$this->form_validation->set_rules('pass', 'mot de passe',
			'callback_verifier_chef_scolarite['.$_POST['pass'].']');
		if (($this->form_validation->run()) or strlen($this->passwordChef) ==0)		{	// RM
			$tables = array("etudiant ,planetudes, dossierEtudiant, programme");
			$join_keys = '';
			$db_columns = array('etudiant.matriculeEtudiant as matricule',
				"case when semestre = 1 then 'Printemps' when semestre = 2 then '&#201;t&#233;'
					when semestre = 3 then 'Automne' end as semestre", 'annee', 'sigle', 'programme.nom as nom', "case when note = -1 then 'AV' when note != -1 then note end as note", 'cote',
				"case when lien = 'HH' or 'HV' then 'HP' when lien = 'AR' then 'AB' when lien ='RR' then 'RT' when lien ='ER' then 'EQ' ELSE lien end as lien ",
				"case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ",'semestre as semId');
			$result_columns = array('matricule', 'annee', 'semestre', 'nom', 'sigle',  'cote', 'lien', 'actif');
			$grid_columns = array('Matricule', 'Ann&#233;e', 'Semestre', 'Programme', 'Sigle', 'Cote', 'Lien', 'Actif ?');

			$action = 'retirer_equilvalence/';
			$id_action = '';
			$more_than_one_id = array('matricule', 'annee', 'semId', 'sigle');


			$where = "where etudiant.matriculeEtudiant = planetudes.matriculeEtudiant and etudiant.matriculeEtudiant = dossieretudiant.matriculeEtudiant and dossierEtudiant.idProgramme = programme.idProgramme  
					  and cote = 'EQ' order by etudiant.matriculeEtudiant Asc, annee Desc, semestre Desc";

			$result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns, $more_than_one_id);


			$titre = "Supprimer une &#233;quivalence";
			$controlleur = "scolarite";
			$confirmation = "Êtes-vous sûr de vouloir supprimer cette équivalence ?";
			$data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result, 'confirmation' => $confirmation);
			

			$this->load->view("recherche_parametree", $data);
		}
		else
		{
			$data['typeInterface'] = 'choix_etudiant_retrait_equivalence';
			$data['titre'] = 'L\'accès à ce sous-menu est réservé.<br> Entrez le mot de passe.';
            $this->load->view("scolarite/chef_scolarite_pass", $data);
		}
    }

    
    function retirer_equilvalence($matricule=null, $annee=null, $semestre=null, $sigle=null) 
    {
        if($matricule && $annee && $semestre && $sigle)
        {
            $this->scolarite_modele->retirer_plan_etudes($matricule,$annee,$semestre,$sigle);
            $data['typeBox'] = 'valid_box';
            // $data['informations'] = 'Équivalence retirée:  <b>Matricule:</b>' . $matricule . " <b>Sigle:</b> " . $sigle . " <b>Année: </b>" . $annee . " <b>Semestre:</b>" . $semestre;
			$data['informations'] = 'Équivalence retirée:  <b>Matricule:</b>' . $matricule . " <b>Sigle:</b> " . $sigle . " <b>Année: </b>" . $annee . 
				" <b>Semestre:</b>" . $this->scolarite_modele->lettreSemestre($semestre);
            $this->load->view('scolarite/modification_confirme', $data);
        }
        else
            $this->index ();
    }
    //Debut Modif Cheikh : controlleur pour impression d'une attestation
    
     //Debut Modif Cheikh : controlleur pour impression d'une attestation
    function trouver_etudiant_pour_attestation() 
    {
        
        $this->rechercher_etudiantAtt('consulter_attestation/'.$_REQUEST['langue'], 'matriculeEtudiant');
    }
     function trouver_etudiant_pour_attestation_d() 
    {
        
        $this->rechercher_etudiantAtt_d('consulter_attestation_d/'.$_REQUEST['langue'], 'matriculeEtudiant');
    }

    function rechercher_etudiantAtt($to_do_action, $id_action) 
    {
       $langue=$_POST['langue'];
        $tables = array("infoatest");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom','nomArabe', 'prenom', 'prenomArabe');
        $result_columns = array('matriculeEtudiant', 'nom','nomArabe', 'prenom', 'prenomArabe');
        $grid_columns = array('matriculeEtudiant', 'nom','nomArabe', 'prenom', 'prenomArabe');
        $action = $to_do_action;

        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Consulter l\'attestation d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result,'langue'=>$langue);


        $this->load->view("recherche_parametree", $data);
    }
    function rechercher_etudiantAtt_d($to_do_action, $id_action) 
    {
       $langue=$_POST['langue'];
        $tables = array("infoatest");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom','nomArabe', 'prenom', 'prenomArabe');
        $result_columns = array('matriculeEtudiant', 'nom','nomArabe', 'prenom', 'prenomArabe');
        $grid_columns = array('matriculeEtudiant', 'nom','nomArabe', 'prenom', 'prenomArabe');
        $action = $to_do_action;

        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Consulter l\'attestation d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result,'langue'=>$langue);


        $this->load->view("recherche_parametree", $data);
    }
    /*
     function consulter_attestation( $id,$annee) 
    {
        $informations_attest = $this->scolarite_modele->get_informationsAtt($id,$annee);
        $informations_attest['annee']=$annee;
        $this->load->view("scolarite/consulter_info_attestation", $informations_attest);
    } 
     */
   function consulter_attestation($langue, $id) 
    {
        $informations_attest = $this->scolarite_modele->get_informationsAtt($id);
        $informations_attest['langue']=$langue;
        $this->load->view("scolarite/consulter_info_attestation", $informations_attest);
    } 
    function consulter_attestation_d($langue, $id) 
    {
        $informations_attest = $this->scolarite_modele->get_informationsAtt_d($id);
      // print_r($informations_attest);
                                
        $informations_attest['langue']=$langue;
        $informations_attest['parametres']=(array)$this->scolarite_modele->get_parametres_genreaux();
        $this->load->view("scolarite/consulter_info_attestation_d", $informations_attest);
    } 
    /*
     * cette fonction est la responsable de la verification 
     * et la validation du formulaire d'ajout d'u module'une unité ainsi
     * que l ajout de l'unité dans la BD
     */

    public function ajouter_unite() 
    {
        $this->load->view('scolarite/ajouter_unite','');
    }

    /*
     * fonction qui affiche un message de succès lors de l'ajout d'une unité dans la base de données
   	 */

    public function ajouter_unite_succe() 
    {
        $this->form_validation->set_rules('semestre', 'semestre', 'numeric|required');
        $this->form_validation->set_rules('sigle', 'sigle', 'required');
        $this->form_validation->set_rules('titre', 'titre', 'required');
        $data = '';
        if ($this->form_validation->run()) 
        {
            $data['typeBox'] = 'valid_box';
            $data['informations'] = 'le module  <b>'.$this->input->post('titre').'</b> a été ajouté avec succès.';
            $informations = $this->scolarite_modele->ajouter_unite($this->input->post('sigle'), $this->input->post('titre'),$this->input->post('description'), 
                    $this->input->post('semestre'), $this->input->post('credits'), $this->input->post('coefficient'),$this->input->post('programme'),$this->input->post('anneeAct'),$this->input->post('semestreAct'));
            if ($informations) 
            {
                $this->load->view('scolarite/modification_confirme', $data);
            } 
            else 
            {
                $data['typeBox'] = 'error_box';
                $data['informations'] = 'Ce module existe déjà. ';
                $this->load->view('scolarite/modification_confirme', $data);
            }
        } 
        else 
        {
            $this->ajouter_unite();
        }
    }
     /*
     * Fonction appeler pour trouver l'unité a modifier
     *
     */

    function trouver_unite_a_modifier() 
    {
        $this->rechercher_unite('modifier_unite', 'sigle');
    }
    /*
     * Cette methode permet d'appeler la recherche parametrable d'une unité
     *
     * @param - to_do_action: page vers laquelle aller lorsqu'on clique sur un element
     *        - id_action: specifie le parametre a envoyer apres la selection d'un element
     */

    function rechercher_unite($to_do_action, $id_action) {
        // Sert à Consulter unite et à Modifier unité.
		$tables = array("unite", "programme");
        $join_keys = array('unite.idProgramme = programme.idProgramme');
        $db_columns = array('sigle', 'titre','credits','coefficient', 'semestre','anneeAct','if(semestreAct=3,\'Impaire\',if(semestreAct=2,\'ni paire, ni impair\',\'Paire\')) as semestreAct','programme.nom as programme');
        $db_result =  array('sigle', 'titre','credits','coefficient', 'semestre','anneeAct','semestreAct','programme');
        $grid_columns =  array('Code', 'Intitulé','Credits','Coefficient', 'Semestre d\'études','Année d\'activation','semestre d\'activation','Programme');
        $db_order = 'order by sigle';
        $action = $to_do_action;

        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $db_order, $db_result);
		// 2.2.1 Ajustement du ttire en fonction de l'action à réaliser
		$action_debut = SUBSTR($to_do_action,0,8);
        if ($action_debut == 'modifier') {
			$titre = 'Modifier un module'; }
			else {
			$titre = 'Consulter un module';
		}
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);

        $this->load->view("recherche_parametree", $data);
    }
    /*
     * fonction pour modifier un unité
     */
    function modifier_unite($id) 
    {

        $informations_unite = $this->scolarite_modele->recuperer_unite($id);  
	// TODO  ; le code en bas est a remetre des qu'on ajoute al notion de desactivation sur une unite
     /*   $sessionDeDesactivation = $this->scolarite_modele->get_session_de_desactivation($id);
        if ($sessionDeDesactivation == NULL) 
        {
            $informations_module['semestreActif'] = $sessionDeDesactivation;
        } 
        else 
        {
            $informations_module['semestreActif'] = $this->extereneAnneeSession($sessionDeDesactivation);
        }*/
        $infoProg = $this->scolarite_modele->recuperer_programme();
        $informations_unite['nomProg'] = $infoProg['nom'];
        $informations_unite['idProg'] = $infoProg['idProg'];
        $informations_unite['idUnite'] = $id;
	$this->load->view("scolarite/modifier_unite", $informations_unite);
    }
     
    /*
     * Alfa 09-02-2016
     * generation anonymat
     * 
     */
    
    function generer_code_anonymat()
    {
        $data = NULL;
        $data['titre'] = 'Génération des codes anonymat';
        $data['res']=$this->scolarite_modele->generer_code_anonymat();
        //$data['courante'] = $this->scolarite_modele->get_session_courante();
        $this->load->view('scolarite/confirmation_gen_anonymat', $data);
    }
    
    /*
     * fonction pour mettre a jour unite 
     */
    function mettre_a_jour_unite() 
    {
        $this->form_validation->set_rules('sigle', 'Code du module', 'required');
        $this->form_validation->set_rules('titre', '<b>Titre du module</b>', 'max_length[34]');
        if(!$this->isValidSemestreEtudes($_POST['semestre']))
        {
                $informations['typeBox'] = 'error_box';
                $informations['informations'] = 'Semestre d\études invalide.';
                $this->load->view('scolarite/modification_confirme', $informations);
        }
        else
        {
            //$quadruplet = $hrTP + $hrCours + $hrTD + $hrPerso;
            //$this->form_validation->set_rules('nbrCredits', 'Correspondance crédits et quadruplet horaire', 'callback_verifier_credits_et_quadruplet[' . $quadruplet . ']');

            $informations = NULL;
            if ($this->form_validation->run() ) 
                {
                    $info_unite = $this->input->post();
                    $this->scolarite_modele->mettre_a_jour_unite($info_unite);
                    $informations['typeBox'] = 'valid_box';
                    $informations['informations'] = 'L\'élément <b>' . $_POST['sigle'] . '</b> a été modifié avec succès.';
                    $this->load->view('scolarite/modification_confirme', $informations);
                } 
                else 
                {
                    $this->modifier_unite($_POST['idUnite']);
                }
            
            
        }
    }
    function isValidSemestreEtudes($s)
    {
        $s = trim($s);
        if(is_numeric($s) && $s<=6 && $s>=1)
        {
            
           return true;
        }
        else
        {
            return false;
        }
    }
    // Fin Modification Cheikh 
    
    // Debut Modif Cheikh 01/01/2016 oui c'est le 01/01/2016 :-)
    function inscrire_classe() {
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['semestres'] = $this->scolarite_modele->get_semestres_courants();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $data['grade'] = $this->scolarite_modele->get_grade();
        $this->load->view("scolarite/inscrire_classe", $data);
    }
    
    //Alfa 15-02-2016
     function liste_anonymat_exam()
    {
         $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['semestres'] = $this->scolarite_modele->get_semestres_courants();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $data['grade'] = $this->scolarite_modele->get_grade();
        $this->load->view("scolarite/liste_anonymat_exam", $data);       
         
    }
    
    
    // Alfa 15-02-2016
    public function generer_liste_anonymat_exam() 
    {
        $idProgramme = $_POST['idProgramme'];
        $semestre = $_POST['semestre'];
        $grade =  $_POST['grade'];
        $idCycle=4; // attention ici on gere uniquelement Licence
        $sessionCourante = $this->scolarite_modele->get_session_courante();
            $annee = $sessionCourante['annee'][0];
            
            
       $tables = array("groupe", "module", "unite");
            $join_keys = array('groupe.sigle = module.sigle', 'module.sigleunite=unite.sigle');
            $db_columns = array('groupe.sigle as sigle', 'module.titre as titre');
            $result_columns = array('sigle', 'titre');
            $grid_columns = array('Sigle', 'Titre');
            $action = 'afficher_code_anonymat_exam/' . $this->encode($annee)
                    . '/' . $this->encode($_POST['semestre']);
            $id_action = '';
            $where = "where annee = $annee and unite.semestre = $semestre and idProgramme='".$idProgramme."'";


            $titre = 'Génération de la liste anonymat exam <br>Choisir un élément</br>';
            $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);
            $controlleur = "scolarite";
            $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
            $this->load->view("recherche_parametree", $data);
            
       
        
    }
    
     function afficher_code_anonymat_exam($annee, $semestre,$sigle)
    {
        
        $typeRapport = 'tri par matricule';
		$annee = $this->decode($annee);
        $semestre = $this->decode($semestre);
    //passage du semestre S1, S2, S3, S4, S5, S6 au 3 (automne) et 1 (printemps) 
        if(($semestre%2)==1)
        $semestre=3;
    else
        $semestre=1;
    
        $data['sigle'] = $sigle;
//        $data['annee'] = $annee;
        $data['semestre'] = $semestre;
        $matricules = $this->scolarite_modele->recuperer_liste_etudiants_conseil($sigle, $annee, $semestre,$typeRapport);
        $data['info']=$this->scolarite_modele->get_name_code_etudiants($matricules);
       $data['infoModule']=$this->scolarite_modele->getInfoModule($sigle);
        $this->load->view('scolarite/code_anonymat_exam', $data);
        
    }
    
    
    
    /* fonction qui crée des groupes, associe des étudiants aux groupes, 
     * insert des donnees dans la table notessemestriels 
       	 */

    public function inscription_pedagogique() 
    {
        $idProgramme = $_POST['idProgramme'];
        $semestre = $_POST['semestre'];
        $grade =  $_POST['grade'];
        $idCycle=4; // attention ici on gere uniquelement Licence
      
        $data = '';
        //if ($this->form_validation->run()) 
        {
            
            //
            $sessionCourante = $this->scolarite_modele->get_session_courante();
            $anneeCourante = $sessionCourante['annee'][0];
            $semestreCourant = $sessionCourante['semestre'][0];
            // 1. selectionner tous les elements du programme et semestre donnee
            
            
            // tableaux pour inscrire un étudienat dans des groupes
            
                        
            // elements et etuditants de niveau==$semestre 
            $modules = $this->scolarite_modele->get_elements_par($idProgramme,$idCycle, $semestre );
        
            
            
            $etudiants =$this->scolarite_modele->get_etudiants_par($idProgramme,$grade, $semestre );
     
//2. parcourir les élements 
             
            for($i=0; $i< count($modules['sigle']); ++$i) {
                //1.et crée un groupe,  pour chacun si ce n'est pas déja le cas
                $sigle = $modules['sigle'][$i];
                $enseignantResp = $modules['professeurResponsable'][$i];
                // Voir s  il y a un groupe pour l element $sigle 
                $groupe = $this->scolarite_modele->get_groupe_par($sigle, $anneeCourante,$semestreCourant);
                $groupeExist = true;
                if($groupe ==NULL){ // pas de groupe, on crée un
                    $groupeExist = false;
                    $infoGroupe = array();
                    $infoGroupe['sigle'] = $sigle;
                    $infoGroupe['idGroupe'] = $sigle.'-'.$anneeCourante.$semestreCourant.'-Groupe-Theorie1';
                    $infoGroupe['numGroupe'] = 1;
                    $infoGroupe['date'] = $anneeCourante;
                    $infoGroupe['session'] = $semestreCourant;
                    $infoGroupe['typeGroupe'] = 'Groupe-Theorie';
                    $infoGroupe['matEmployer'] =$enseignantResp;
                    $this->scolarite_modele->cree_nouveau_groupe($infoGroupe);
                    $groupe = $infoGroupe['idGroupe'];
                }
                 
                //2. assoier tous lés éléves au semestre concerne dans le groupe, vérifie si l'eleve n'exsite pas déja dans le groupe
               $items = array();
                for($j=0; $j< count($etudiants); $j++) {
                    $matriculeEtudiant = $etudiants[$j]['matriculeEtudiant'];
// si l'etudiant n'existe pas dans le groupe on l'ajoute
                    if($groupeExist == false || !$this->scolarite_modele->est_dans_groupe($sigle, $anneeCourante,$semestreCourant, $matriculeEtudiant)) {
                        
                        $items[] = $matriculeEtudiant;
                    }
                   //3 pour chaque eleve qui fait l'élément pour la prémiere fois, renseigner la table bulletnsemestriel si ce n'est pas déja rensiegné
               if(!$this->scolarite_modele->bulltinSemestrielDejaRens($sigle, $matriculeEtudiant)) {
                        
                   $infoBulletinSem = array();
                   $infoBulletinSem['matriculeEtudiant'] = $matriculeEtudiant;
                   $infoBulletinSem['sigle'] =$sigle;
                   $infoBulletinSem['semestre'] =$semestre;
                   $infoBulletinSem['annee'] =$anneeCourante;
                   $infoBulletinSem['idModule'] =$modules['sigleunite'][$i];
                   $infoBulletinSem['idProgramme'] =$idProgramme;
                   $infoBulletinSem['grade'] =$grade;
                   $infoBulletinSem['coef'] =$modules['nbCredits'][$i];   
                   $infoBulletinSem['nbcredits'] =$modules['nbCredits'][$i]; 
                   $this->scolarite_modele->insererBulletinSemestriel($infoBulletinSem);
                 }
              }
                
                $data['items'] = $items;
                $data['sigle'] = $sigle;
                $data['groupe'] = array();
                $data['groupe'][] = $groupe;
                $data['annee'] = $anneeCourante;
                $data['semestre'] = $semestreCourant;
                $data['typeCours'] = 'obligatoire';
                $data['messageRetour']=$this->scolarite_modele->inscrire_etudiant_groupe($data);
                
                
            
                
                    }
        }
            $data['retour'] = 'inscrire_classe';
                $data['typeBox'] = 'valid_box';
                $data['informations'] = 'Tous les étudiants sélectionnés ont bien été inscrits';    
     $this->load->view("scolarite/modification_confirme", $data);  
        
    }
    
     // Fin Modif Cheikh 01/01/2016 oui c'est le 01/01/2016 :-)
    function choix_semestre_releve($matriculeEtudiant){
         $controlleur = "scolarite";
         $titre = "Choisir un semestre";
         $data = array('titre' => $titre,'controlleur' => $controlleur,'matriculeEtudiant'=>$matriculeEtudiant);
   // $this->load->view("recherche_parametree", $data);
         $sessionCourante = $this->scolarite_modele->get_session_courante();
         $annee=$sessionCourante['annee'];
         $data['annee'] = $annee;   
         $this->load->view("scolarite/choix_semestre_releve",$data);
}
    function trouver_etudiant_bulletin_individuel() 
    {
        //$semestre = $_POST['semestre'];
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'choix_semestre_releve';
        $id_action ='matriculeEtudiant';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Consulter le rélévé des notes d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    }
    function trouver_etudiant_attestation_Diplome() 
    {
        //$semestre = $_POST['semestre'];
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'voir_attestation';
        $id_action ='matriculeEtudiant';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Consulter l\'attestation du diplôme d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    }
    public function voir_bulletin() 
    {
        $matriculeEtudiant= $_POST['matriculeEtudiant'];
        $semestre =  $_POST['semestre'];
        $annee =  $_POST['annee'];
        /*if($semestre == null)
            $semestre = 1;
        else if($semestre ='' || $semestre <1 || $semestre > 6)*/
                //$semestre = 1;
        $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
        $niveau = $this->scolarite_modele->getEtudiatNiveauInscrit($matriculeEtudiant,$annee);
        $infoEtudiant['niveau']="L".$niveau;
        //$semRes = $this->scolarite_modele->getSemestreResult($matriculeEtudiant, $semestre);
        //$moduleRes = $this->scolarite_modele->getModulesResult($matriculeEtudiant, $semestre);
        $semRes=null;
        $moduleRes=null;
        $moduleNc=null;
         if($semestre%2==1){
            $semRes = $this->scolarite_modele->getSemestreResult_bis($matriculeEtudiant, $semestre,$annee);
            $moduleRes = $this->scolarite_modele->getModulesResult_bis($matriculeEtudiant, $semestre,$annee);
             $moduleNc= $this->scolarite_modele->get_elt_nc($matriculeEtudiant,$annee,$semestre);
            // print_r($moduleNc['sigle']);
             
         }else{
            $semRes = $this->scolarite_modele->getSemestreResult_bis($matriculeEtudiant, $semestre,$annee);
            $moduleRes = $this->scolarite_modele->getModulesResult_bis($matriculeEtudiant, $semestre,$annee);
            $moduleNc= $this->scolarite_modele->get_elt_nc($matriculeEtudiant,$annee,$semestre);
            
         }
        
        
        $data = array('nc' => $moduleNc,'info' => $infoEtudiant, 'semestre' => $semRes, 'modules' => $moduleRes, 'annee' => $annee,'numSem'=>$semestre);
        $this->load->view('scolarite/consulter_info_releve', $data);
        
    }
    // Debut Modif Cheikh 16/02/2016 
    function generer_releves() {
         $sessionCourante = $this->scolarite_modele->get_session_courante();
         $annee=$sessionCourante['annee'];
         $data['annee'] = $annee;
         $data['programme'] = $this->scolarite_modele->get_programme();
         $this->load->view("scolarite/generer_releves",$data);
    }
    function preparer_releves() {
         $idProgramme = $_POST['idProgramme'];
         $semestre = $_POST['semestre'];
         $annee =  $_POST['annee'];
         //$this->scolarite_modele->preparer_releves($idProgramme,$semestre );
         
         $this->scolarite_modele->Mis_jour_PV($annee,$semestre,$idProgramme);
         $data['retour'] = 'generer_releves';
         $data['typeBox'] = 'valid_box';
         $data['informations'] = 'opération réussie';    
         $this->load->view("scolarite/modification_confirme", $data);
         //copier les notes du planetudes à bulletinsemstriel (ou modifier si exste déja)
         // inserer les donner dan les vues : releves, decisionssemestre, decisionmodules dans des tables 
         // pour acceler les traitment : il faut vider ces tables en utilidant le filtre idprogramme,semestre ou tous si 
    }
    
// Debut Modif Cheikh 28/04/2016 
    function generer_pv() {
         $data['programme'] = $this->scolarite_modele->get_programme();
         $sessionCourante = $this->scolarite_modele->get_session_courante();
         $annee=$sessionCourante['annee'];
         $data['annee'] = $annee;
         $this->load->view("scolarite/generer_pv",$data);
    }
    
     public function afficher_pv() 
    {
        $session= $_POST['session'];
        $semestre =  $_POST['semestre'];
        $annee =  $_POST['annee'];
        $tri=$_POST['tri'];
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];
        $idProgramme =  $_POST['idProgramme'];
        $semestreCourant=3;
        if($semestre %2 == 0){
           $annee = $annee+1;
           $semestreCourant=1;
       }
        $etudiants = $this->scolarite_modele->getEtudiants_PV($annee,$semestreCourant,$idProgramme,$semestre,$session,$tri);
        //print_r($etudiants);
        $stat = $this->scolarite_modele->get_statistique_pv($idProgramme,$semestre,$annee,$semestreCourant);
         // print_r($stat);      
        $anneeSc = 100*($annee%100) + $annee%100 +1;
       if($semestre%2==0){
           $anneeSc = ($annee%100-1)*100+ $annee%100;
       }
        $data = array("statistique"=>$stat,'etudiants'=>$etudiants,'anneeSc'=>$anneeSc,'idProgramme'=>$idProgramme,'semestre'=>$semestre,'sess'=>$session);
        $this->load->view('scolarite/consulter_info_pv', $data);
        
    }

     function fiche_note()
    {
         $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['semestres'] = $this->scolarite_modele->get_semestres_courants();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $data['grade'] = $this->scolarite_modele->get_grade();
        $this->load->view("scolarite/fiche_note", $data);       
         
    }
    // Alfa 12-06-2016
    public function fiche_note_element() 
    {
        $session=$_POST['session'];
        $evaluation=$_POST['evaluation'];
        $idProgramme = $_POST['idProgramme'];
        $semestre = $_POST['semestre'];
       // echo'semestre ='.$semestre;
        $grade =  $_POST['grade'];
        $tri1= $_POST['tri'];
       
        $idCycle=4; // attention ici on gere uniquelement Licence
        $sessionCourante = $this->scolarite_modele->get_session_courante();
            $annee = $sessionCourante['annee'][0];
            
            
       $tables = array("groupe", "module", "unite");
            $join_keys = array('groupe.sigle = module.sigle', 'module.sigleunite=unite.sigle');
            $db_columns = array('groupe.sigle as sigle', 'module.titre as titre');
            $result_columns = array('sigle', 'titre');
            $grid_columns = array('Sigle', 'Titre');
            $action = 'choix_Groupe/' . $this->encode($annee)
                    . '/' . $this->encode($_POST['semestre']). '/' . $this->encode($session). '/' . $this->encode($evaluation). '/' .$tri1.'/afficher_fiche_note';
            $id_action = '';
            $where = "where annee = $annee and unite.semestre = $semestre and idProgramme='".$idProgramme."'";


            $titre = 'Génération du fiche de notes <br>Choisir un élément</br>';
            $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);
            $controlleur = "scolarite";
            $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
            $this->load->view("recherche_parametree", $data);
            
           
    }
    
    function choix_Groupe($annee, $semestre,$session,$evaluation,$tri1='',$function,$sigle=''){ //MAB
//    echo"<br>$annee, $semestre,$session,$evaluation,$tri1,$function,<br>$sigle<br>";
      //  echo $sigle."test done".$annee;
        //passage du semestre S1, S2, S3, S4, S5, S6 au 3 (automne) et 1 (printemps) 
       $semestreEtud= $this->decode($semestre);
        if(($semestre%2)==1)
        $semestreEtud=3;
    else
        $semestreEtud=1;
        $groupes=$this->scolarite_modele->get_groupe_calendar($this->decode($annee),$semestreEtud, $sigle);
     //print_r($groupes);   
        $data=array('titre'=>$function,'annee'=>$annee,'semestre'=>$semestre,'session'=>$session,'evaluation'=>$evaluation,'tri1'=>$tri1,'sigle'=>$sigle,'Groupes'=>$groupes);
        $this->load->view("scolarite/choix_groupe", $data);
    }
     
function afficher_fiche_note($annee, $semestre,$session,$evaluation,$tri1,$sigle)
    {
    $info= $this->input->post();
   // print_r($info['Groupe']);
    $idGroupe=$info['Groupe'];
    //echo"<br>idGroupe".$idGroupe."<br>";
        $session=$this->decode($session);
        $evaluation=$this->decode($evaluation);
        //echo 'ff'.$sigle.'ggg';
         $typeRapport ='';
         
         
         //echo $tri1;
	$annee = $this->decode($annee);
        $annee_univ=$annee.'-'.($annee+1);
        $semestre = $this->decode($semestre);
        $semestreEtude=$semestre;
    //passage du semestre S1, S2, S3, S4, S5, S6 au 3 (automne) et 1 (printemps) 
        if(($semestre%2)==1){
        $semestre=3;
        }else{
        $semestre=1;
        $annee_univ=($annee-1).'-'.($annee);        
        }
        $data['sigle'] = $sigle;
//        $data['annee'] = $annee;
        
       $data['evaluation']=$evaluation;
        $data['semestre'] = $semestreEtude;
        if($evaluation=='cc')
           $typeRapport = 'tri par matricule';
        if($evaluation=='examen' && $session=='N')
             if(($tri1=="m")){
                // echo $tri1;
            $typeRapport = 'tri par matricule';
             }
         if($evaluation=='examen' && $session=='R')
              
             if(($tri1=="m")){
                 //echo $tri1;
            $typeRapport = 'tri par matricule';
             }
             
        $matricules = $this->scolarite_modele->recuperer_liste_etudiants_conseil($sigle, $annee, $semestre,$typeRapport, $session,$idGroupe);
        if(! empty($matricules))
        {
        $data['info']=$this->scolarite_modele->get_name_code_etudiants($matricules);
       $data['infoModule']=$this->scolarite_modele->getInfoModule($sigle);
       $data['session']=$session;
       $data['evaluation']=$evaluation;
       $data['tri']= $typeRapport;
       $data['groupe']= $idGroupe;
       $data['annee_univ']= $annee_univ;
       $data['param_generaux']=$this->scolarite_modele->Recup_Parametre_Generaux();
       //echo $typeRapport;
       //print_r($data);
        if($evaluation=='cc')
       $this->load->view('scolarite/afficher_fiche_note_cc', $data);
      
       if($evaluation=='examen' && $session=='N')
       $this->load->view('scolarite/afficher_fiche_note_examen', $data);
       
       if($evaluation=='examen' && $session=='R')
       $this->load->view('scolarite/afficher_fiche_note_rt', $data);
        }
        else
        { 
          $res['sigle']=$sigle;
        $this->load->view('scolarite/fiche_note_element_vide', $res);
        }
       
    }
    

// Alfa 02-07-2016
     function stat_param()
    {
         $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['semestres'] = $this->scolarite_modele->get_semestres_courants();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $data['grade'] = $this->scolarite_modele->get_grade();
        $this->load->view("scolarite/stat_param", $data);       
         
    }
    
     // Alfa 02-07-2016
     function afficher_stat()
    {
        $idProgramme = $_POST['idProgramme'];
        $annee = $_POST['annee'];
        $res=$this->scolarite_modele->getStat($idProgramme, $annee);
        $data=array('annee'=>$annee, 'idProgramme'=>$idProgramme, 'data'=>$res);
        $this->load->view("scolarite/afficher_stat", $data);       
         
    }
    //ALFA 02072016e
     public function choixAnneeProgFicheInsc() 
    {
        
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $annee = $sessionCourante['annee'];
        $data = array('annee'=>$annee);
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view('scolarite/choisir_annee_programme_ficheInsc', $data);
        
    }  
   public function generer_ficheInscription() /*elle n'a pas encore termine debogue par MedBakar 11-06-2020*/
    {
        
        $annee =  $_POST['annee'];
        $niveau =  $_POST['niveau'];
        $idProgramme =  $_POST['idProgramme'];
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];
        $etudiants = $this->scolarite_modele->getElements_a_inscrire($idProgramme, $annee, $niveau);
        $data = array('etudiants'=>$etudiants,'anneeSc'=>$annee,'idProgramme'=>$idProgramme,'annee'=>$annee,'niveau'=>$niveau);
        $this->load->view('scolarite/consulter_fiches_inscription_pedagogique', $data);
        
    }   

    function fiche_etud()
    {
         $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['semestres'] = $this->scolarite_modele->get_semestres_courants();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $data['grade'] = $this->scolarite_modele->get_grade();
        $this->load->view("scolarite/fiche_etud", $data);       
         
    }
   

// Alfa 12-06-2016
    public function fiche_etud_element() 
    {
        $session=$_POST['session'];
        $evaluation=$_POST['evaluation'];
        $idProgramme = $_POST['idProgramme'];
        $semestre = $_POST['semestre'];
        $grade =  $_POST['grade'];
        $idCycle=4; // attention ici on gere uniquelement Licence
        $sessionCourante = $this->scolarite_modele->get_session_courante();
            $annee = $sessionCourante['annee'][0];
            
            
       $tables = array("groupe", "module", "unite");
            $join_keys = array('groupe.sigle = module.sigle', 'module.sigleunite=unite.sigle');
            $db_columns = array('groupe.sigle as sigle', 'module.titre as titre');
            $result_columns = array('sigle', 'titre');
            $grid_columns = array('Sigle', 'Titre');
            $action = 'choix_Groupe/' . $this->encode($annee)
                    . '/' . $this->encode($_POST['semestre']). '/' . $this->encode($session). '/' . $this->encode($evaluation). '/a/afficher_fiche_etud';
            $id_action = '';
            $where = "where annee = $annee and unite.semestre = $semestre and idProgramme='".$idProgramme."'";


            $titre = 'Génération de la liste d\'émargement <br>Choisir un élément</br>';
            $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);
            $controlleur = "scolarite";
            $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
            $this->load->view("recherche_parametree", $data);
            
           
    }
    

function afficher_fiche_etud($annee, $semestre,$session,$evaluation,$tri1='',$sigle)//modified by MedBakar 18-06-2020 to support groupes
    {
    $info= $this->input->post();//add by MedBakar 18-06-2020
   // print_r($info['Groupe']);
    $idGroupe=$info['Groupe'];//add by MedBakar 18-06-2020
    
        $session=$this->decode($session);
        $evaluation=$this->decode($evaluation);
        
        $typeRapport = 'tri par matricule';
		$annee = $this->decode($annee);
        $semestre = $this->decode($semestre);
        $annee_univ=$annee.'-'.($annee+1);
        $semestreEtude=$semestre;
    //passage du semestre S1, S2, S3, S4, S5, S6 au 3 (automne) et 1 (printemps) 
        if(($semestre%2)==1){
        $semestre=3;
        }else{
        $semestre=1;
        $annee_univ=($annee-1).'-'.$annee;
    }
        $data['sigle'] = $sigle;
//        $data['annee'] = $annee;
        $data['semestre'] = $semestreEtude;
//        echo"<br>$sigle, $annee, $semestre,$typeRapport, $session<br>";
        $matricules = $this->scolarite_modele->recuperer_liste_etudiants_conseil($sigle, $annee, $semestre,$typeRapport, $session,$idGroupe);
        if(! empty($matricules))
        {
        $data['info']=$this->scolarite_modele->get_name_code_etudiants($matricules);
       $data['infoModule']=$this->scolarite_modele->getInfoModule($sigle);
       $data['session']=$session;
       $data['evaluation']=$evaluation;
        $data['annee_univ']=  $annee_univ;// add by MedBakar 23-06-2020
       $data['groupe']= $idGroupe;//add by MedBakar 18-06-2020
       $data['param_generaux']=$this->scolarite_modele->Recup_Parametre_Generaux();//add by MedBakar 18-06-2020
       if($evaluation=='cc')
       $this->load->view('scolarite/afficher_fiche_etud_cc', $data);
       if($evaluation=='examen' && $session=='N')
       $this->load->view('scolarite/afficher_fiche_etud_examen', $data);
       
       if($evaluation=='examen' && $session=='R')
       $this->load->view('scolarite/afficher_fiche_etud_rt', $data);
        }
        else
        { 
          $res['sigle']=$sigle;
        $this->load->view('scolarite/fiche_note_element_vide', $res);
        }
       
    } 
    
      public function saisir_horaire($matriculeEmploye) 
    {
        
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeC = $sessionCourante['annee'][0];
        $semestreC = $sessionCourante['semestre'][0];
        
        $modules=$this->scolarite_modele->getModulesEnseignes($matriculeEmploye, $anneeC, $semestreC);
        $locaux=$this->scolarite_modele->getLocaux();
        $allModules=$this->scolarite_modele->getAllModules($anneeC, $semestreC);
        
        $nomEmploye=$this->scolarite_modele->getNomEmploye($matriculeEmploye);
        
        $data = array('semestreC'=>$semestreC,'anneeC'=>$anneeC,'modules'=>$modules, 'nomEmploye'=>$nomEmploye,'matriculeEmploye'=>$matriculeEmploye,'locaux'=>$locaux, 'allModules'=>$allModules);
        $this->load->view('scolarite/saisir_horaire', $data);
        
    }
    
     public function saisir_horaire_module() 
    {
         $format = 'DATE_ATOM';
        $time = time();

        $date = $this->convert_date_ATOM(standard_date($format, $time));
        //on teste si aucun etudiant n est selectionne
       
            //on teste si la duree n'est pas selectionne
          
               
                       
                          
                                //on teste si la date choisie est dans le futur
                               
                     
                                        $this->scolarite_modele->enregistrer_absences_horaire($_POST);
                                        $data['titre'] = 'Entrer Absences';
                                        $data['type'] = 'valid_box';
										$informations = 'Les absences au groupe <b>' . $this->scolarite_modele->corrigerNumGroupe($_POST['groupe']) . '</b> ont bien été enregistrées.';
                                        /* $data['informations'] = 'Les absences au groupe <b>' . $_POST['idGroupe'] . '</b> ont bien été enregistrées.'; */
                                    
                 
        
         $anneeC=$_POST['anneeC'];
         $semestreC=$_POST['semestreC'];
         $matriculeEmploye=$_POST['matriculeEmploye'];
         $nomEmploye=$_POST['nomEmploye'];
         $date=date('Y-m-d',strtotime($_POST['date']));
        // echo $date;
         $heureD=$_POST['heureD'];
         $duree=$_POST['duree'];
         $sigle=$_POST['sigle'];
          $groupe=$_POST['groupe'];
         $commentaire=$_POST['commentaire'];
         $type=$_POST['type'];
         $idLocal=$_POST['idLocal'];
         $autreElement='';
         if(isset($_POST['autreElement']))
         $autreElement=$_POST['autreElement'];
     if($sigle =='')
         $sigle=$autreElement;
         
$this->scolarite_modele->enregistrer_horaire($anneeC,$semestreC,"'".$matriculeEmploye."'",$date,$heureD,$duree,"'".$sigle."'","'".$groupe."'", "'".$type."'","'".$idLocal."'", "'".$commentaire."'");
        

$data = array('informations'=>$informations,'nomEmploye'=>$nomEmploye, 'matriculeEmploye'=>$matriculeEmploye,'heureD'=>$heureD, 'duree'=>$duree);
   $this->load->view('scolarite/confirmation_saisir_horaire', $data);
        
    }

    function generer_heures_enseignement()
    {
        
     $listeH=$this->scolarite_modele->getListeHeuresEnseignement();
    
     
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $date = utf8_encode(strftime("%d-%B-%Y"));
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0);
        
	$semestre=3;
        $annee=2015;
      
                //formatage du Titre de la liste.
      // $objSheet->getDefaultStyle()->getFont()->setName('Courier New');
      // $objSheet->setCellValue('A1', 'INSTITUT UNIVERSITAIRE PROFESSIONNEL');
      //  $objSheet->getStyle('A1')->getFont()->setBold(TRUE);
        
       // $titreFichierExcel = 'Heures d\'enseignement du semestre courant';
       // $objSheet->setCellValue('B1',$titreFichierExcel );
      //  $objSheet->setCellValue('A6', 'Semestre : ' .$semestre .' '.$annee);
   
      //  $objSheet->setCellValue('A4', 'Date : ' . $date);
      $objSheet->getDefaultStyle()->getFont()->setName('Arial');
       $objSheet->setCellValue('A1', 'Etat d\'avencement des cours dispensés');
        $objSheet->getStyle('A1')->getFont()->setBold(TRUE);
       
      $objSheet->setCellValueByColumnAndRow(0,2,'Matricule');
      $objSheet->setCellValueByColumnAndRow(1,2,'Nom et Prénom ');  
      
       $objSheet->setCellValueByColumnAndRow(2,2,'Coordonnées bancaires ');
      $objSheet->setCellValueByColumnAndRow(3,2,'Date'); 
      $objSheet->setCellValueByColumnAndRow(4,2,'HeureDébut');
      $objSheet->setCellValueByColumnAndRow(5,2,'Durée');
       $objSheet->setCellValueByColumnAndRow(6,2,'Elément');
       $objSheet->setCellValueByColumnAndRow(7,2,'Semestre et Filière');
       $objSheet->setCellValueByColumnAndRow(8,2,'Salle');
      $objSheet->setCellValueByColumnAndRow(9,2,'Type');
       $objSheet->setCellValueByColumnAndRow(10,2,'Observation');
       
       $k=3;
       
       for($i=0; $i<count($listeH); $i++)
       { $type="";
           if($listeH[$i]['type']=="cours"){
           $type="CM";
       }elseif($listeH[$i]['type']=="td"){
            $type="TD";
       }elseif($listeH[$i]['type']=="tp"){
            $type="TP";
       }
        $objSheet->setCellValueByColumnAndRow(0,$k,$listeH[$i]['matriculeEmploye']);
      $objSheet->setCellValueByColumnAndRow(1,$k,$listeH[$i]['nom']);  
       $objSheet->setCellValueByColumnAndRow(2,$k,$listeH[$i]['compteBancaire']);
      $objSheet->setCellValueByColumnAndRow(3,$k,$listeH[$i]['date']); 
      $objSheet->setCellValueByColumnAndRow(4,$k,$listeH[$i]['heureD']);
      $objSheet->setCellValueByColumnAndRow(5,$k,$listeH[$i]['duree']);
       $objSheet->setCellValueByColumnAndRow(6,$k,$listeH[$i]['sigle']);
       $objSheet->setCellValueByColumnAndRow(7,$k,$listeH[$i]['sf']);
       $objSheet->setCellValueByColumnAndRow(8,$k,$listeH[$i]['idLocal']);
      $objSheet->setCellValueByColumnAndRow(9,$k,$type);
     
       $objSheet->setCellValueByColumnAndRow(10,$k,$listeH[$i]['commentaire']);
       $k++;
       
       }
       
     

       $nomFichier = 'ListeHeures';
        $objWriter = PHPExcel_IOFactory::createwriter($objXLS, 'Excel5');
        header('Content-Type', 'application/msexcel;charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
ob_end_clean();

        $objWriter->save('php://output');
    }

///emmin voir_bulletin historique
    function voir_bulletinV2() 
    {
         $matriculeEtudiant= $_POST['matriculeEtudiant'];
        $semestre =  $_POST['semestre'];
       // $annee =  $_POST['annee'];
       
                             
                /*if($semestre == null)
            $semestre = 1;
        else if($semestre ='' || $semestre <1 || $semestre > 6)*/
                //$semestre = 1;
        $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
        $niveau = $this->scolarite_modele->getEtudiatNiveauInscrit($matriculeEtudiant,$annee);
        $infoEtudiant['niveau']="L".$niveau;
        //$semRes = $this->scolarite_modele->getSemestreResult($matriculeEtudiant, $semestre);
        //$moduleRes = $this->scolarite_modele->getModulesResult($matriculeEtudiant, $semestre);
        $semRes=null;
        $moduleRes=null;
        $moduleNc=null;
         if($semestre%2==1){
            $semRes = $this->scolarite_modele->getSemestreResult_bis($matriculeEtudiant, $semestre,$annee);
            $moduleRes = $this->scolarite_modele->getModulesResult_bis_h($matriculeEtudiant, $semestre,$annee);
            $moduleNc= $this->scolarite_modele->get_elt_nc($matriculeEtudiant,$annee,$semestre);
            // print_r($moduleNc['sigle']);
             
         }else{
            $semRes = $this->scolarite_modele->getSemestreResult_bis($matriculeEtudiant, $semestre,$annee);
            $moduleRes = $this->scolarite_modele->getModulesResult_bis_h($matriculeEtudiant, $semestre,$annee);
            $moduleNc= $this->scolarite_modele->get_elt_nc($matriculeEtudiant,$annee,$semestre);
            
         }
        
        
        $data = array('nc' => $moduleNc,'info' => $infoEtudiant, 'semestre' => $semRes, 'modules' => $moduleRes, 'annee' => $annee,'numSem'=>$semestre);
        $this->load->view('scolarite/consulter_info_releveV2', $data);
        
    }
    //emmin 
    
    
    function trouver_etudiant_bulletin_nouveau() 
    {
        //$semestre = $_POST['semestre'];
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'choix_semestre_bulltin';
        $id_action ='matriculeEtudiant';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Consulter le rélévé des notes d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    }
    //emmin
   function choix_semestre_bulltin($matriculeEtudiant){
    $controlleur = "scolarite";
    $titre = "Choisir un semestre";
    $data = array('titre' => $titre,'controlleur' => $controlleur,'matriculeEtudiant'=>$matriculeEtudiant);
   // $this->load->view("recherche_parametree", $data);
    $this->load->view("scolarite/choix_semestre_bulltin",$data);
}
   function voir_attestation($matriculeEtudiant) 
    {
        //$matriculeEtudiant= $_POST['matriculeEtudiant'];
        //$semestre =  $_POST['semestre'];
        /*if($semestre == null)
            $semestre = 1;
        else if($semestre ='' || $semestre <1 || $semestre > 6)*/
                //$semestre = 1;
        $result = $this->scolarite_modele->getCreditValide($matriculeEtudiant);
        $crdit_val=$result['totalCredit'];
        $anneeD=$result['annee'];
        $semestreD=$result['semestre'];
        //si le dernier semestre est pair l'annee d'obtention est (anneeD-1)/anneeD
        //if($semestreD %2==0){
         //   $anneeD = $anneeD-1;
        //}
        if($crdit_val==180){
           $info_stage=$this->scolarite_modele-> stages_traveaux($matriculeEtudiant);
         //  print_r($info_stage);
           
        $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
        $moyenne = $this->scolarite_modele->getMoyenne($matriculeEtudiant);
         //$anneeD = $this->scolarite_modele->getAnne_obt_diplome($matriculeEtudiant);
       //pourquoi un deuxième appel à cette fonction ??? 
       // $crdit_val = $this->scolarite_modele->getCreditValide($matriculeEtudiant);
        
        $mention=null;
        if($moyenne<12){
            $mention="Passable";
        }else if(($moyenne>=12)and ($moyenne<14)){
             $mention="Assez Bien";
        }else if(($moyenne>=14)and ($moyenne<16)){
             $mention="Bien";
        }else if(($moyenne>=16)and ($moyenne<18)){
             $mention="Trés Bien";
        }else if(($moyenne>=18)and ($moyenne<=20)){
             $mention="Ex";
        }
        //echo 'annee = '.$anneeD;
        
        $data = array('stages_travaux'=>$info_stage,'anneeD'=>$anneeD,'info' => $infoEtudiant, 'moyenne' => $moyenne, 'crditVal' =>$crdit_val,'mension' =>$mention);
        $this->load->view('scolarite/attestation_Diplome', $data);
        }else{
           
           $str= 'Crédits Capitalisés :	'.$crdit_val.'  est inferieur à 180';
           $this->session->set_flashdata('message', 'Crédits Capitalisés :	'.$crdit_val.'  est inferieur à 180');
           $data['informations'] = 'Crédits Capitalisés :	'.$crdit_val.'  est inferieur à 180';
             $this->load->view('scolarite/attestation_diplome_msg', $data);
           
            
        }
    }
     function voir_attestation_ins() 
    {
         $matriculeEtudiant= $_POST['matriculeEtudiant'];
        //$semestre =  $_POST['semestre'];
         $annee =  $_POST['annee'];
         $semestreA=$this->scolarite_modele->getSemstreInAnne($matriculeEtudiant,$annee);
         $data =null;
         if(empty($semestreA)){
            // echo 'Nouveau etudiant : '.$matriculeEtudiant;
             $semestre=1;

            $idProgramme=$this->scolarite_modele->get_programme($matriculeEtudiant);
            $modules_a_etudies_impaire = $this->scolarite_modele->get_module_impaire_a_etudies($idProgramme,-1,$matriculeEtudiant,$annee); 
            $modules_a_etudies_paire = $this->scolarite_modele->get_module_paire_a_etudies($idProgramme,-1, $matriculeEtudiant,$annee);
            $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
            $data = array('redoublant' => null,'niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelist' => null,'moduleR_impairelist' => null,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee,'nouveau'=>'Y');
         }else{
              $redoublant = $this->scolarite_modele->get_redoublant_Et( $matriculeEtudiant,$annee);
            //  print_r($redoublant);
            $semestre=$semestreA['semestre'];
             if($matriculeEtudiant==16278){
               $semestre=3;
               $redoublant=null;
           }
            $idProgramme=$this->scolarite_modele->get_programme($matriculeEtudiant);
            $modulesR_impaire = $this->scolarite_modele->get_module_rattrapes_impaire($matriculeEtudiant,$annee); 
            $modulesR_paire = $this->scolarite_modele->get_module_rattrapes_paire($matriculeEtudiant, $annee);
            $modules_a_etudies_impaire = $this->scolarite_modele->get_module_impaire_a_etudies($idProgramme,$semestre,$matriculeEtudiant,$annee); 
            $modules_a_etudies_paire = $this->scolarite_modele->get_module_paire_a_etudies($idProgramme,$semestre, $matriculeEtudiant,$annee);
           
            $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
            $data = array('redoublant' => $redoublant,'niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelist' => $modulesR_paire,'moduleR_impairelist' => $modulesR_impaire,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee);
         }
         
       
       
        //$data = array('niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelists2' => $modulesR_paire_s2,'moduleR_pairelists4' => $modulesR_paire_s4,'moduleR_pairelist' => $modulesR_paire,'moduleR_impairelist' => $modulesR_impaire,'moduleR_impairelists1' => $modulesR_impaire_s1,'moduleR_impairelists3' => $modulesR_impaire_s3,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee);
        
        $this->load->view('scolarite/consulter_info_attestation_ins', $data);
        
    
        
    }
    function voir_attestation_ins_d() 
    {
         $matriculeEtudiant= $_POST['matriculeEtudiant'];
        //$semestre =  $_POST['semestre'];
         $annee =  $_POST['annee'];
         $semestreA=$this->scolarite_modele->getSemstreInAnne($matriculeEtudiant,$annee);
         $data =null;
         $niveau = $this->scolarite_modele->getEtudiatNiveauInscrit($matriculeEtudiant,$annee);
         $code = $this->scolarite_modele-> info_bulltin($matriculeEtudiant); 
        
        $infoEtudiant['niveau']="L".$niveau;
        $dataE = $this->scolarite_modele->get_informations_etudiant($matriculeEtudiant);
//print_r($semestreA);
         if(empty($semestreA)){
            // echo 'Nouveau etudiant : '.$matriculeEtudiant;
             $semestre=1; 
            $idProgramme=$this->scolarite_modele->get_programme($matriculeEtudiant);
            $modules_a_etudies_impaire = $this->scolarite_modele->get_module_impaire_a_etudies($idProgramme,-1,$matriculeEtudiant,$annee); 
            $modules_a_etudies_paire = $this->scolarite_modele->get_module_paire_a_etudies($idProgramme,-1, $matriculeEtudiant,$annee);
            $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
            $data = array('numSem'=>$semestre,'code'=>$code,'infoE'=>$dataE,'redoublant' => null,'niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelist' => null,'moduleR_impairelist' => null,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee,'nouveau'=>'Y');
         }else{
             $redoublant = $this->scolarite_modele->get_redoublant_Et( $matriculeEtudiant,$annee);
           
            $semestre=$semestreA['semestre'];
           if($matriculeEtudiant==16278){
               $semestre=3;
               $redoublant=null;
           }

            $idProgramme=$this->scolarite_modele->get_programme($matriculeEtudiant);
            $modulesR_impaire = $this->scolarite_modele->get_module_rattrapes_impaire($matriculeEtudiant,$annee); 
            $modulesR_paire = $this->scolarite_modele->get_module_rattrapes_paire($matriculeEtudiant, $annee);
            $modules_a_etudies_impaire = $this->scolarite_modele->get_module_impaire_a_etudies($idProgramme,$semestre,$matriculeEtudiant,$annee); 
           // print_r($modules_a_etudies_impaire);
            $modules_a_etudies_paire = $this->scolarite_modele->get_module_paire_a_etudies($idProgramme,$semestre, $matriculeEtudiant,$annee);
             $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
            $data = array('numSem'=>$semestre,'code'=>$code,'infoE'=>$dataE,'redoublant' => $redoublant,'niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelist' => $modulesR_paire,'moduleR_impairelist' => $modulesR_impaire,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee);
         }
         
      
       
        //$data = array('niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelists2' => $modulesR_paire_s2,'moduleR_pairelists4' => $modulesR_paire_s4,'moduleR_pairelist' => $modulesR_paire,'moduleR_impairelist' => $modulesR_impaire,'moduleR_impairelists1' => $modulesR_impaire_s1,'moduleR_impairelists3' => $modulesR_impaire_s3,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee);
       // print_r($data);
        $data['parametres']=(array)$this->scolarite_modele->get_parametres_genreaux();
//       print_r($data);
//       
        $this->load->view('scolarite/consulter_info_attestation_ins_d', $data);
        
    
        
    }
     
    function choix_programme_annee($matriculeEtudiant){
    $controlleur = "scolarite";
    $titre = "Choisir l'annee";
    
   // $this->load->view("recherche_parametree", $data);
    $annes=$this->scolarite_modele->getAnnee();
    $progs = $this->scolarite_modele->get_programme();
        $data = array('titre' => $titre,'controlleur' => $controlleur,'matriculeEtudiant'=>$matriculeEtudiant,'annee'=>$annes,'programme'=>$progs);
    $this->load->view("scolarite/choix_programme_annee",$data);
}
 function choix_programme_annee_d($matriculeEtudiant){
    $controlleur = "scolarite";
    $titre = "Choisir l'annee";
    
   // $this->load->view("recherche_parametree", $data);
    $annes=$this->scolarite_modele->getAnnee();
    $progs = $this->scolarite_modele->get_programme();
        $data = array('titre' => $titre,'controlleur' => $controlleur,'matriculeEtudiant'=>$matriculeEtudiant,'annee'=>$annes,'programme'=>$progs);
    $this->load->view("scolarite/choix_programme_annee_d",$data);
}
 function recu_ins($matriculeEtudiant){
    $controlleur = "scolarite";
    $titre = "Choisir l'annee";
    
     
}
 function trouver_etudiant_ins_attestation() 
    {
        //$semestre = $_POST['semestre'];
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'choix_programme_annee';
        $id_action ='matriculeEtudiant';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Choisir un étudiant pour imprimer la fiche d\'inscription';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    
    }
     function trouver_etudiant_ins_attestation_d() 
    {
        //$semestre = $_POST['semestre'];
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'choix_programme_annee_d';
        $id_action ='matriculeEtudiant';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Choisir un étudiant pour imprimer la fiche d\'inscription';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    
    }
    function trouver_etudiant_recu_ins() 
    {
        //$semestre = $_POST['semestre'];
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'choix_annee';
        $id_action ='matriculeEtudiant';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'reçu d\'inscription d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    }       
    ##########autorisation etudiant#########
    function autoriser_etudiant()
    {

         
            $data = array(
                'num_bac' => $this->input->post('num_bac'),
                'annee' => $this->input->post('annee'),
                'nom' => $this->input->post('nom'),
                'prenom' => $this->input->post('prenom'),
                'serie' => $this->input->post('serie'),
                'idProgramme' => $this->input->post('idProgramme'),
                'personne_ressource' => $this->input->post('personne_ressource'),
                'contacts' => $this->input->post('contacts'),
                 'prenomPere_fr' => $this->input->post('prenomPere'),
                 'anneeAutorisation' => $this->input->post('anneeAutorisation'),
                
            );

            //insert the form data into database
            $this->db->insert('autorisation_e', $data);

            //display success message
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Etudiant ajouté!!!</div>');
            redirect('scolarite/autorisation_etudiant');
        

    }
    function modifier_autoriser_etudiant()
    {

           $num_bac = $this->input->post('num_bac');
            $annee = $this->input->post('annee');
            $data = array(
                'nom' => $this->input->post('nom'),
                'prenom' => $this->input->post('prenom'),
                'serie' => $this->input->post('serie'),
                'idProgramme' => $this->input->post('idProgramme'),
                'personne_ressource' => $this->input->post('personne_ressource'),
                'contacts' => $this->input->post('contacts'),
                 'prenomPere_fr' => $this->input->post('prenomPere'),
                 'anneeAutorisation' => $this->input->post('anneeAutorisation'),
                
            );
            $matriculeE=  $this->input->post('matriculeE');
            $idProgramme = $this->input->post('idProgramme');
            //insert the form data into database
            
            $this->scolarite_modele->modifier_autoriser_etudiant($num_bac,$data,$matriculeE,$idProgramme);
             $this->db->where('num_bac', $num_bac);
             $this->db->where('annee', $annee);
            $this->db->update('autorisation_e', $data);

            //display success message
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Autorisation modifié!!!</div>');
            redirect('scolarite/trouver_etudiant_pour_modifier_autorisation');
        

    }
    function autorisation_etudiant(){
       $controlleur = "scolarite";
       
        $progs = $this->scolarite_modele->get_programme();
        $data_session_courante = $this->scolarite_modele->get_session_courante();
        
        $data = array('programme'=>$progs,'annee' => $data_session_courante['annee'][0], 
                    'semestre' => $data_session_courante['semestre'][0]);
      //  print_r($data);
         $this->load->view("scolarite/autorisation_etudiant",$data);
    }
    
   function trouver_etudiant_a_autorisation() 
    {  $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $annee=$anneeCourante;
        $tables = array("autorisation_e");
        $join_keys = '';
        $where ="where anneeAutorisation=".$annee;
        $db_columns = array('num_bac', 'annee', 'nom','prenom','serie','idProgramme');
        $result_columns = array('num_bac', 'annee', 'nom', 'prenom','serie','idProgramme');
        $grid_columns = array('Matricule BAC', 'Annee', 'Nom', 'Prénom','Serie','Programme');
        $action = 'ajouter_etudiant/' . $annee.'/';
        $id_action ='';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);

        $titre = 'Inscription d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);

        $this->load->view("recherche_parametree", $data);
    }
    function trouver_etudiant_pour_modifier_autorisation() 
    {
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $annee=$anneeCourante;
        $tables = array("autorisation_e");
        $join_keys = '';
        $where =" where annee=".$annee;
        $db_columns = array('num_bac', 'annee', 'nom','prenom','serie','idProgramme');
        $result_columns = array('num_bac', 'annee', 'nom', 'prenom','serie','idProgramme');
        $grid_columns = array('Matricule BAC', 'Annee', 'Nom', 'Prénom','Serie','Programme');
        $action = 'modifier_autorisation_etudiant/'.$annee;
        $id_action ='num_bac';

        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action,$where, $result_columns);

        $titre = 'Iscription d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);

        $this->load->view("recherche_parametree", $data);
    }
    
    function modifier_autorisation_etudiant($num_bac,$annee){
       $controlleur = "scolarite";
       $progs = $this->scolarite_modele->get_programme();
       $infos['matriculeE'] = $this->scolarite_modele->get_matricule_Et($num_bac,$annee);
        
        
        $infos['programme'] =$progs;
        $infos['info'] = $this->scolarite_modele->get_infoEleve($num_bac,$annee);
      //  print_r($infos);
         $this->load->view("scolarite/modifier_autorisation_etudiant",$infos);
    }
      function voir_recu_ins() 
    {
        $matricule= $_POST['matriculeEtudiant'];
         $annee =  $_POST['annee'];
 
         $niveau=null;
         
         $semestreA=$this->scolarite_modele->getSemstreInAnne($matricule,$annee);
        // print_r($semestreA);
        // while($semestreA['annee']<=$annee){
        
             if($semestreA['semestre']==1){
             $niveau="L1";}
            elseif($semestreA['semestre']==2){
                $niveau="L2";
            }
            elseif($semestreA['semestre']==3){
                $niveau="L3";
            }
            elseif($semestreA['semestre']==4){
                
       }//}
       
            
            
            
            
            
            $infoEtudiant = $this->scolarite_modele->get_informations_etudiant($matricule);
            $infoEtud = $this->scolarite_modele->getInfoBulletinEtudiant($matricule);
            //print_r($infoEtudiant);
          // print_r($infoEtud);
             $infoRecu = $this->scolarite_modele->get_infoRecu($matricule);
             if($infoRecu!=null){
        $data = array('infoR' => $infoRecu,'niveau' => $niveau,'niveau' => $niveau,'info' => $infoEtudiant,'matriculeEtudiant'=>$matricule,'annee'=>$annee,'infoEtud'=>$infoEtud);
    $this->load->view("scolarite/recu_ins",$data);
             }else{
                 
                 $data_saisi=date('d/m/Y');
            $data = array(
                'date_saisi' => $data_saisi,
                'matriculeEtudiant' => $matricule,
            );

            //insert the form data into database
            $this->db->insert('recu_inscription', $data);
                        
            //display success message
            $infoRecu = $this->scolarite_modele->get_infoRecu($matricule);
             $data = array('infoR' => $infoRecu,'niveau' => $niveau,'niveau' => $niveau,'info' => $infoEtudiant,'matriculeEtudiant'=>$matricule,'annee'=>$annee,'infoEtud'=>$infoEtud);
             
             $this->load->view("scolarite/recu_ins",$data);
            
         }
    }
 function choix_annee($matriculeEtudiant){
    $controlleur = "scolarite";
    $titre = "Choisir l'annee";
    
   // $this->load->view("recherche_parametree", $data);
    $annes=$this->scolarite_modele->getAnnee();
        $data = array('titre' => $titre,'controlleur' => $controlleur,'matriculeEtudiant'=>$matriculeEtudiant,'annee'=>$annes);
    $this->load->view("scolarite/choix_annee",$data);
}
function trouver_etudiant_fich_ins() 
    {
        //$semestre = $_POST['semestre'];
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'choix_annee_ins';
        $id_action ='matriculeEtudiant';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Choisir un étudiant pour imprimer la fiche d\'inscription';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    
    }
     function choix_annee_ins($matriculeEtudiant){
    $controlleur = "scolarite";
    $titre = "Choisir l'annee";
    
   // $this->load->view("recherche_parametree", $data);
    $annes=$this->scolarite_modele->getAnnee();
    $progs = $this->scolarite_modele->get_programme();
        $data = array('titre' => $titre,'controlleur' => $controlleur,'matriculeEtudiant'=>$matriculeEtudiant,'annee'=>$annes,'programme'=>$progs);
    $this->load->view("scolarite/choix_annee_ins",$data);
}
// function voir_fiche_ins() 
//    {
//        $matriculeEtudiant= $_POST['matriculeEtudiant'];
//        //$semestre =  $_POST['semestre'];
//         $annee =  $_POST['annee'];
//         $semestreA=$this->scolarite_modele->getSemstreInAnne($matriculeEtudiant,$annee);
//         
//         $semestre=$semestreA['semestre'];
//         $data =null;
//         if(empty($semestreA)){ // nouveau inscrit pas de notes
//            // echo 'Nouveau etudiant : '.$matriculeEtudiant;
//             $semestre=1;
//
//            $idProgramme=$this->scolarite_modele->get_programme($matriculeEtudiant);
//            $modules_a_etudies_impaire = $this->scolarite_modele->get_module_impaire_a_etudies($idProgramme,-1,$matriculeEtudiant,$annee); 
//            $modules_a_etudies_paire = $this->scolarite_modele->get_module_paire_a_etudies($idProgramme,-1, $matriculeEtudiant,$annee);
//            $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
//            $data = array('redoublant' => null,'niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelist' => null,'moduleR_impairelist' => null,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee,'nouveau'=>'Y');
//         }else{
//              $redoublant = $this->scolarite_modele->get_redoublant_Et  ( $matriculeEtudiant,$annee);
//              if($matriculeEtudiant==16278){
//                  $redoublant=null;
//              }
//            $idProgramme=$this->scolarite_modele->get_programme($matriculeEtudiant);
//            $modulesR_impaire = $this->scolarite_modele->get_module_rattrapes_impaire($matriculeEtudiant,$annee); 
//            $modulesR_paire = $this->scolarite_modele->get_module_rattrapes_paire($matriculeEtudiant, $annee);
//            $modules_a_etudies_impaire = $this->scolarite_modele->get_module_impaire_a_etudies($idProgramme,$semestre,$matriculeEtudiant,$annee); 
//            $modules_a_etudies_paire = $this->scolarite_modele->get_module_paire_a_etudies($idProgramme,$semestre, $matriculeEtudiant,$annee);
//           
//       
//            $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
//          
//              //$data = array('niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelists2' => $modulesR_paire_s2,'moduleR_pairelists4' => $modulesR_paire_s4,'moduleR_pairelist' => $modulesR_paire,'moduleR_impairelist' => $modulesR_impaire,'moduleR_impairelists1' => $modulesR_impaire_s1,'moduleR_impairelists3' => $modulesR_impaire_s3,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee);
//            $data = array('redoublant' => $redoublant,'niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelist' => $modulesR_paire,'moduleR_impairelist' => $modulesR_impaire,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee);
//         }
//        $data['parametres']=(array)$this->scolarite_modele->get_parametres_genreaux();
//        $this->load->view('scolarite/ins_pedagogique_elts', $data);
// 
//        
//    }
    
     function voir_fiche_ins() //new Modified 90% by MedBakar 15-09-2020 -> 05-10-2020 
    {
        $matriculeEtudiant= $_POST['matriculeEtudiant'];
        $info=$this->get_decision_passsage($matriculeEtudiant);
//        print_r($info);
//        return;
        //$semestre =  $_POST['semestre'];
         $annee =  $_POST['annee'];
//         $semestreA=$this->scolarite_modele->getSemstreInAnne($matriculeEtudiant,$annee);///passage_t a enlever
//         echo"<br>SemestreA<br>";
//         print_r($semestreA);
//         echo"<br> above print<br>";
//         $semestre=$semestreA['semestre'];//niveau
         
      
         $niveau=$info['niveau'];
         if($niveau==4){
               $data['retour'] = '';
                $data['typeBox'] = 'error_box';
                $data['informations'] = 'L\'étudiant a déja sa licence';    
                $this->load->view("scolarite/modification_confirme", $data);
                return;
         }
         $redoublant=$info['redoublant'];
         $listeElementCapitPaire='';
         $listeElementCapitImpaire='';
         
           $data =null;
         if($redoublant != 0){
             /*//debut--21-09-2020
             //on recupere les matieres deja valide pour qu'on l'affiche pas dans la maquette(cas etudiant redoublant ayant valide qlq element)
             //il rest a integrer les correspondances 
            if(!empty($info['capit']['Paire']))
             foreach($info['capit']['Paire'] as $element=>$capit){
                if($capit!='NC')
                $listeElementCapitPaire.=", '$element' ";
            }
            if(!empty($info['capit']['Impaire']))
             foreach($info['capit']['Impaire'] as $element=>$capit){
                if($capit!='NC')
                 $listeElementCapitImpaire.=", '$element' ";
            }
             $listeElementCapitImpaire=substr($listeElementCapitImpaire,1);
             $listeElementCapitPaire=substr($listeElementCapitPaire,1);
             
            //fin--21-09-2020
             */
             $data['redoublant'] = $redoublant;/*9999*/
         $module_a_etudie=$this->get_module_a_etudie_new($niveau, $matriculeEtudiant,$annee,'');
            $modules_a_etudies_impaire =$module_a_etudie['impaire'];
            $modules_a_etudies_paire =$module_a_etudie['paire'];
            
            
         }else
            $data['redoublant'] = null;
//          $data['redoublant'] = null;
         
            
          $module_a_etudie=$this->get_module_a_etudie_new($niveau, $matriculeEtudiant,$annee,$info['capit']);
            $modules_a_etudies_impaire =$module_a_etudie['impaire'];
            $modules_a_etudies_paire =$module_a_etudie['paire']; 
            $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
//        print_r($module_a_etudie);
//         return;
       
             
         $data['niveau']= $niveau;
         $data['modules_a_etudies_pairelist'] = $modules_a_etudies_paire;
         $data['modules_a_etudies_impairelist'] = $modules_a_etudies_impaire;
         
         $modules_ratrappes_new=$this->get_modules_ratrappes($info['ratrap'],$niveau);
             $data['moduleR_pairelist'] = $modules_ratrappes_new['paire'];
             $data['moduleR_impairelist'] = $modules_ratrappes_new['impaire'];
         
       
         
         $data['info'] = $infoEtudiant;
         print_r($data['info']);
         $data['semestre'] = $niveau; 
         $data['annee'] = $annee;
//         echo'<br>';
//         print_r($data);
               
         $data['parametres']=(array)$this->scolarite_modele->get_parametres_genreaux();
         $this->load->view('scolarite/ins_pedagogique_elts_new', $data);
//         $this->load->view('scolarite/ins_pedagogique_elts', $data);
         return;
         
        
    }
    // Hafedh 24-09-2016
    function modifier_photo_etudiant($matricule) 
    {
       $target_dir = "../../photos/";
       $file=$target_dir.'default.gif';
      

$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
//echo $target_file;
$image_info = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
$image_width = $image_info[0];
$image_height = $image_info[1];

//echo "width : ".$image_width.'<br>';
//echo "height : ".$image_height.'<br>';

/* if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $_SERVER['DOCUMENT_ROOT']."iup/photos/IUP".$matricule.'.gif')) {
        //echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
  */  
    $this->modifier_info_etudiant($matricule) ;
    
    }

  //old ins pedagogique  
    /*   public function fiche_inscription_pedagogique() 
    {
        
     
       // $idProgramme = $_POST['idProgramme'];
        //$semestre = $_POST['semestre'];
       // $grade =  $_POST['grade'];
       // $idCycle=4; // attention ici on gere uniquelement Licence
      
        $data = '';
        //if ($this->form_validation->run()) 
        {
            
            //
            $sessionCourante = $this->scolarite_modele->get_session_courante();
            $anneeCourante = $sessionCourante['annee'][0];
            $semestreCourant = $sessionCourante['semestre'][0];
            // 1. selectionner tous les elements du programme et semestre donnee
            
            
            // tableaux pour inscrire un étudienat dans des groupes
            
                        
            // elements et etuditants de niveau==$semestre 
           // $modules = $this->scolarite_modele->get_elements_par($idProgramme,$idCycle, $semestre );
        
            
            
            //$etudiants =$this->scolarite_modele->get_etudiants_par($idProgramme,$grade, $semestre );
            $etudiants=$_POST['matriculeEtudiant'];
     
//2. parcourir les élements 
             //print_r($_POST['sigleImpaire']);
            for($i=0; $i< count($_POST['sigleImpaire']); ++$i) {
                //1.et crée un groupe,  pour chacun si ce n'est pas déja le cas
                $sigle = $_POST['sigleImpaire'][$i];
                $enseignantResp = 'E0011';//prof saisi 
                // Voir s  il y a un groupe pour l element $sigle 
                $groupe = $this->scolarite_modele->get_groupe_par($sigle, $anneeCourante,$semestreCourant);
                $groupeExist = true;
                if($groupe ==NULL){ // pas de groupe, on crée un
                    $groupeExist = false;
                    $infoGroupe = array();
                    $infoGroupe['sigle'] = $sigle;
                    $infoGroupe['idGroupe'] = $sigle.'-'.$anneeCourante.$semestreCourant.'-Groupe-Theorie1';
                    $infoGroupe['numGroupe'] = 1;
                    $infoGroupe['date'] = $anneeCourante;
                    $infoGroupe['session'] = $semestreCourant;
                    $infoGroupe['typeGroupe'] = 'Groupe-Theorie';
                    $infoGroupe['matEmployer'] =$enseignantResp;
                    $this->scolarite_modele->cree_nouveau_groupe($infoGroupe);
                    $groupe = $infoGroupe['idGroupe'];
                    //echo"Pas de groupe Impaire";
                }
                 
                //2. assoier tous lés éléves au semestre concerne dans le groupe, vérifie si l'eleve n'exsite pas déja dans le groupe
               $items = $_POST['matriculeEtudiant'];
                //for($j=0; $j< count($_POST['matriculeEtudiant']); $j++) {
                    $matriculeEtudiant = $_POST['matriculeEtudiant'];
// si l'etudiant n'existe pas dans le groupe on l'ajoute
                    if($groupeExist == false || !$this->scolarite_modele->est_dans_groupe($sigle, $anneeCourante,$semestreCourant, $matriculeEtudiant)) {
                        
                        $items = $matriculeEtudiant;
                    }
                   //3 pour chaque eleve qui fait l'élément pour la prémiere fois, renseigner la table bulletnsemestriel si ce n'est pas déja rensiegné
                        $module = $this->scolarite_modele->get_elements_par_sigle($_POST['sigleImpaire'][$i]);
//                        if(!$this->scolarite_modele->bulltinSemestrielDejaRens($sigle, $matriculeEtudiant)) {
//                            print_r($module);
//                   $infoBulletinSem = array();
//                  $infoBulletinSem['matriculeEtudiant'] = $matriculeEtudiant;
//                   $infoBulletinSem['sigle'] =$module['sigle'];
//                   $infoBulletinSem['semestre'] =$module['semestre'];
//                   $infoBulletinSem['annee'] =$anneeCourante;
//                   $infoBulletinSem['idModule'] =$module['sigleunite'];
//                   $infoBulletinSem['idProgramme'] =$module['idProgramme'];
//                   $infoBulletinSem['grade'] =$module['idCycle'];
//                   $infoBulletinSem['coef'] =$module['nbCredits'];   
//                   $infoBulletinSem['nbcredits'] =$module['nbCredits']; 
//                   $this->scolarite_modele->insererBulletinSemestriel($infoBulletinSem);
//                 }
             // }
                
                $data['items'] = $items;
                $data['sigle'] = $sigle;
                $data['groupe'] = array();
                $data['groupe'][] = $groupe;
                $data['annee'] = $anneeCourante;
                $data['semestre'] = $semestreCourant;
                $data['typeCours'] = 'obligatoire';
                $data['messageRetour']=$this->scolarite_modele->inscrire_etudiant_group($data);
                
                
            
                
                    }
                  if(!empty($_POST['siglePaire']))
                    for($i=0; $i< count($_POST['siglePaire']); ++$i) {
                //1.et crée un groupe,  pour chacun si ce n'est pas déja le cas
                $sigle = $_POST['siglePaire'][$i];
                $enseignantResp = 'E0011';
                // Voir s  il y a un groupe pour l element $sigle 
                $groupe = $this->scolarite_modele->get_groupe_par($sigle, $anneeCourante+1,1);
                $groupeExist = true;
                if($groupe ==NULL){ // pas de groupe, on crée un
                    $groupeExist = false;
                    $infoGroupe = array();
                    $infoGroupe['sigle'] = $sigle;
                    $infoGroupe['idGroupe'] = $sigle.'-'.($anneeCourante+1).'1-Groupe-Theorie1';
                    $infoGroupe['numGroupe'] = 1;
                    $infoGroupe['date'] = $anneeCourante+1;
                    $infoGroupe['session'] = 1;
                    $infoGroupe['typeGroupe'] = 'Groupe-Theorie';
                    $infoGroupe['matEmployer'] =$enseignantResp;
                    $this->scolarite_modele->cree_nouveau_groupe($infoGroupe);
                    $groupe = $infoGroupe['idGroupe'];
                  //  echo"Pas de groupe Paire";
                }
                 
                //2. assoier tous lés éléves au semestre concerne dans le groupe, vérifie si l'eleve n'exsite pas déja dans le groupe
               $items = $_POST['matriculeEtudiant'];
              // for($j=0; $j< count($_POST['matriculeEtudiant']); $j++) {
                    $matriculeEtudiant = $_POST['matriculeEtudiant'];
// si l'etudiant n'existe pas dans le groupe on l'ajoute
                    if($groupeExist == false || !$this->scolarite_modele->est_dans_groupe($sigle, $anneeCourante+1,1, $matriculeEtudiant)) {
                        
                        $items = $matriculeEtudiant;
                    }
                   //3 pour chaque eleve qui fait l'élément pour la prémiere fois, renseigner la table bulletnsemestriel si ce n'est pas déja rensiegné
                         $module = $this->scolarite_modele->get_elements_par_sigle($_POST['siglePaire'][$i]);
//                         if(!$this->scolarite_modele->bulltinSemestrielDejaRens($sigle, $matriculeEtudiant)) {
//                        
//                   $infoBulletinSem = array();
//                   $infoBulletinSem['matriculeEtudiant'] = $matriculeEtudiant;
//                   $infoBulletinSem['sigle'] =$module['sigle'];
//                   $infoBulletinSem['semestre'] =$module['semestre'];
//                   $infoBulletinSem['annee'] =$anneeCourante;
//                   $infoBulletinSem['idModule'] =$module['sigleunite'];
//                   $infoBulletinSem['idProgramme'] =$module['idProgramme'];
//                   $infoBulletinSem['grade'] =$module['idCycle'];
//                   $infoBulletinSem['coef'] =$module['nbCredits'];   
//                   $infoBulletinSem['nbcredits'] =$module['nbCredits']; 
//                   $this->scolarite_modele->insererBulletinSemestriel($infoBulletinSem);
//                 }
           //   }
                
                $data['items'] = $items;
                $data['sigle'] = $sigle;
                $data['groupe'] = array();
                $data['groupe'][] = $groupe;
                $data['annee'] = $anneeCourante+1;
                $data['semestre'] = 1;
                $data['typeCours'] = 'obligatoire';
                $data['messageRetour']=$this->scolarite_modele->inscrire_etudiant_group($data);
                
                
            
                
                    }
        }
            $data['retour'] = 'inscrire_classe';
                $data['typeBox'] = 'valid_box';
                $data['informations'] = 'L\' étudiant a bien été inscrit';    
     $this->load->view("scolarite/modification_confirme", $data);
        
    }
    */
    
  /*  //new ins pedagogique Modified by MedBakar 06-04-2020
     public function fiche_inscription_pedagogique() 
    {
        
     
       // $idProgramme = $_POST['idProgramme'];
        //$semestre = $_POST['semestre'];
       // $grade =  $_POST['grade'];
       // $idCycle=4; // attention ici on gere uniquelement Licence
      
        $data = '';
        //if ($this->form_validation->run()) 
        {
            
            //
            $sessionCourante = $this->scolarite_modele->get_session_courante();
            $anneeCourante = $sessionCourante['annee'][0];
            $semestreCourant = $sessionCourante['semestre'][0];
            // 1. selectionner tous les elements du programme et semestre donnee
            
            
            // tableaux pour inscrire un étudienat dans des groupes
            
                        
            // elements et etuditants de niveau==$semestre 
           // $modules = $this->scolarite_modele->get_elements_par($idProgramme,$idCycle, $semestre );
        
            
            
            //$etudiants =$this->scolarite_modele->get_etudiants_par($idProgramme,$grade, $semestre );
            $etudiants=$_POST['matriculeEtudiant'];
     
//2. parcourir les élements 
             //print_r($_POST['sigleImpaire']);
            for($i=0; $i< count($_POST['sigleImpaire']); ++$i) {
                //1.et crée un groupe,  pour chacun si ce n'est pas déja le cas
                $sigle = $_POST['sigleImpaire'][$i];
                $enseignantResp = 'E0011';//matricule ens prof saisi 
                // Voir s  il y a un groupe pour l element $sigle 
                $groupe = $this->scolarite_modele->get_groupe_par($sigle, $anneeCourante,$semestreCourant);
                $groupeExist = true;
                if($groupe ==NULL){ // pas de groupe
                    $groupeExist = false;
//                    $infoGroupe = array();
//                    $infoGroupe['sigle'] = $sigle;
//                    $infoGroupe['idGroupe'] = $sigle.'-'.$anneeCourante.$semestreCourant.'-Groupe-Theorie1';
//                    $infoGroupe['numGroupe'] = 1;
//                    $infoGroupe['date'] = $anneeCourante;
//                    $infoGroupe['session'] = $semestreCourant;
//                    $infoGroupe['typeGroupe'] = 'Groupe-Theorie';
//                    $infoGroupe['matEmployer'] =$enseignantResp;
                    //$this->scolarite_modele->cree_nouveau_groupe($infoGroupe);
                    //$groupe = $infoGroupe['idGroupe'];
                    //echo"Pas de groupe Impaire";
                    
                    $data['typeBox'] = 'error_box';
                    $data['informations'] = 'Les groupes de  l\'annee Impaire ou bien ce du '.$sigle.' ne sont pas encore créée';    
                     $this->load->view("scolarite/modification_confirme", $data);
                     return;
                }
                 
                //2. assoier tous lés éléves au semestre concerne dans le groupe, vérifie si l'eleve n'exsite pas déja dans le groupe
               $items = $_POST['matriculeEtudiant'];
              // echo "Post".$_POST['matriculeEtudiant'];
                //for($j=0; $j< count($_POST['matriculeEtudiant']); $j++) {
                    $matriculeEtudiant = $_POST['matriculeEtudiant'];
// si l'etudiant n'existe pas dans le groupe on l'ajoute
//                    if($groupeExist == false || !$this->scolarite_modele->est_dans_groupe($sigle, $anneeCourante,$semestreCourant, $matriculeEtudiant)) {
//                       $items = $matriculeEtudiant;
//                    }
                   //3 pour chaque eleve qui fait l'élément pour la prémiere fois, renseigner la table bulletnsemestriel si ce n'est pas déja rensiegné
                        //$module = $this->scolarite_modele->get_elements_par_sigle($_POST['sigleImpaire'][$i]);
//                        if(!$this->scolarite_modele->bulltinSemestrielDejaRens($sigle, $matriculeEtudiant)) {
//                            print_r($module);
//                   $infoBulletinSem = array();
//                  $infoBulletinSem['matriculeEtudiant'] = $matriculeEtudiant;
//                   $infoBulletinSem['sigle'] =$module['sigle'];
//                   $infoBulletinSem['semestre'] =$module['semestre'];
//                   $infoBulletinSem['annee'] =$anneeCourante;
//                   $infoBulletinSem['idModule'] =$module['sigleunite'];
//                   $infoBulletinSem['idProgramme'] =$module['idProgramme'];
//                   $infoBulletinSem['grade'] =$module['idCycle'];
//                   $infoBulletinSem['coef'] =$module['nbCredits'];   
//                   $infoBulletinSem['nbcredits'] =$module['nbCredits']; 
//                   $this->scolarite_modele->insererBulletinSemestriel($infoBulletinSem);
//                 }
             // }
//                
                $data['items'] = $items;
                $data['sigle'] = $sigle;
                $data['groupe'] = array();
                $types=array();
                //on recupere le groupe ou ses etudiants son le min 
                  $array = $this->recup_groupe_min($sigle,$semestreCourant,$anneeCourante);
                  //on verifie si l'etudiant n'est pas encore inscrit dans un groupe de meme type en cas ou il est inscrit dans un autre groupe de meme type on le  retourne 
                   //s'il n'est pas inscrit on l'ajoute alors a le groupe fournis en parametre
                  foreach($array as $type=>$group){
                    $data['groupe'][]=$this->scolarite_modele->est_dans_groupe_de_type($sigle, $anneeCourante,$semestreCourant, $matriculeEtudiant,$type,$group);
                  }
                 
                $data['annee'] = $anneeCourante;
                $data['semestre'] = $semestreCourant;
                $data['typeCours'] = 'obligatoire';
                //print_r($data);
                 
                $data['messageRetour']=$this->scolarite_modele->inscrire_etudiant_group($data);
                 }
                  if(!empty($_POST['siglePaire']))
                    for($i=0; $i< count($_POST['siglePaire']); ++$i) {
                //1.et crée un groupe,  pour chacun si ce n'est pas déja le cas
                $sigle = $_POST['siglePaire'][$i];
                $enseignantResp = 'E0011';
                // Voir s  il y a un groupe pour l element $sigle 
                $groupe = $this->scolarite_modele->get_groupe_par($sigle, $anneeCourante+1,1);
                $groupeExist = true;
                if($groupe ==NULL){ // pas de groupe, on crée un
                    $groupeExist = false;
//                    $infoGroupe = array();
//                    $infoGroupe['sigle'] = $sigle;
//                    $infoGroupe['idGroupe'] = $sigle.'-'.($anneeCourante+1).'1-Groupe-Theorie1';
//                    $infoGroupe['numGroupe'] = 1;
//                    $infoGroupe['date'] = $anneeCourante+1;
//                    $infoGroupe['session'] = 1;
//                    $infoGroupe['typeGroupe'] = 'Groupe-Theorie';
//                    $infoGroupe['matEmployer'] =$enseignantResp;
                 //   $this->scolarite_modele->cree_nouveau_groupe($infoGroupe);
                    //$groupe = $infoGroupe['idGroupe'];
                  //  echo"Pas de groupe Paire";
                    $data['typeBox'] = 'error_box';
                    $data['informations'] = 'Les groupes de l\'annee Paires ou bien ce du '.$sigle.' ne sont pas encores  crées ';    
                     $this->load->view("scolarite/modification_confirme", $data);
                     return;//en cas ou un groupe n'est pas encore cree on affiche msg d'erreur et arrete le traitement
                }
                 
                //2. assoier tous lés éléves au semestre concerne dans le groupe, vérifie si l'eleve n'exsite pas déja dans le groupe
               $items = $_POST['matriculeEtudiant'];
              // for($j=0; $j< count($_POST['matriculeEtudiant']); $j++) {
                    $matriculeEtudiant = $_POST['matriculeEtudiant'];
// si l'etudiant n'existe pas dans le groupe on l'ajoute
//                    if($groupeExist == false || !$this->scolarite_modele->est_dans_groupe($sigle, $anneeCourante+1,1, $matriculeEtudiant)) {
//                        
//                        $items = $matriculeEtudiant;
//                    }
                   //3 pour chaque eleve qui fait l'élément pour la prémiere fois, renseigner la table bulletnsemestriel si ce n'est pas déja rensiegné
              //           $module = $this->scolarite_modele->get_elements_par_sigle($_POST['siglePaire'][$i]);
//                         if(!$this->scolarite_modele->bulltinSemestrielDejaRens($sigle, $matriculeEtudiant)) {
//                        
//                   $infoBulletinSem = array();
//                   $infoBulletinSem['matriculeEtudiant'] = $matriculeEtudiant;
//                   $infoBulletinSem['sigle'] =$module['sigle'];
//                   $infoBulletinSem['semestre'] =$module['semestre'];
//                   $infoBulletinSem['annee'] =$anneeCourante;
//                   $infoBulletinSem['idModule'] =$module['sigleunite'];
//                   $infoBulletinSem['idProgramme'] =$module['idProgramme'];
//                   $infoBulletinSem['grade'] =$module['idCycle'];
//                   $infoBulletinSem['coef'] =$module['nbCredits'];   
//                   $infoBulletinSem['nbcredits'] =$module['nbCredits']; 
//                   $this->scolarite_modele->insererBulletinSemestriel($infoBulletinSem);
//                 }
           //   }
                
                         
                         
                $data['items'] = $items;
                $data['sigle'] = $sigle;
                $data['groupe'] = array();
                $types=array();
                //on recupere le groupe ou ses etudiants son le min 
                  $array = $this->recup_groupe_min($sigle,1,$anneeCourante+1);
            //on verifie si l'etudiant n'est pas encore inscrit dans un groupe de meme type en cas ou il est inscrit dans un autre groupe de meme type on le  retourne 
             //s'il n'est pas inscrit on l'ajoute alors a le groupe fornis en parametre
                  foreach($array as $type=>$group){
                    $data['groupe'][]=$this->scolarite_modele->est_dans_groupe_de_type($sigle, $anneeCourante+1,1, $matriculeEtudiant,$type,$group);
                  }
      
                $data['annee'] = $anneeCourante+1;
                $data['semestre'] = 1;
                $data['typeCours'] = 'obligatoire';
                $data['messageRetour']=$this->scolarite_modele->inscrire_etudiant_group($data);
              // print_r($data); 
                
            
                
                    }
        }
            $data['retour'] = 'inscrire_classe';
                $data['typeBox'] = 'valid_box';
                $data['informations'] = 'L\' étudiant a bien été inscrit';    
     $this->load->view("scolarite/modification_confirme", $data);
        
    }
    //Fin Modif MedBakar
    */
        public function fiche_inscription_pedagogique()  //new 06-10-2020
    {    
         $sessionCourante = $this->scolarite_modele->get_session_courante();
            $anneeCourante = $sessionCourante['annee'][0];
            $semestreCourant = $sessionCourante['semestre'][0];
//            print_r($_POST['siglePaire']);
        if(!empty($_POST['sigleImpaire']) || !empty($_POST['siglePaire'])){   
            $matriculeEtudiant= $_POST['matriculeEtudiant'];
            $sigleImpaire=array();
           if(!empty($_POST['sigleImpaire'])){
               $sigleImpaire=$_POST['sigleImpaire'];
                       
           } 
            $siglePaire=array();
            if(!empty($_POST['siglePaire'])){
               $siglePaire=$_POST['siglePaire'];
               
           } 
        //$semestre =  $_POST['semestre'];
         $annee =  $_POST['annee'];
         $niveau =  $_POST['niveau'];
 
       //traitement element cache -- add by MedBakar 20-06-2020
//         if(!empty($_POST['sigleImpaire']) || !empty($_POST['siglePaire']))
         
          $info=$this->get_decision_passsage($matriculeEtudiant);/*ppp*/
//          print_r($info);
          if(!empty($info['redoublant']))
            $this->prep_old_maquette_redoublants($matriculeEtudiant,$niveau,$info['redoublant'],$annee);//add by MedBAkar
//          return;
          $module_a_etudie=$this->get_module_a_etudie_new($niveau, $matriculeEtudiant,$annee,$info['capit']);
            $modules_a_etudies_impaire =$module_a_etudie['impaire'];
            $modules_a_etudies_paire =$module_a_etudie['paire']; 
       //  $this->traitement_element_cache_new($matriculeEtudiant,$annee,$semestreCourant, $anneeCourante,$sigleImpaire,$siglePaire,$niveau,$modules_a_etudies_impaire,$modules_a_etudies_paire);//add by MedBakar
        // $this->traitement_element_cache($matriculeEtudiant,$annee,$semestreCourant, $anneeCourante,$sigleImpaire,$siglePaire,$niveau);
          
            
            /*
             * A noter que se traitement d'ici vers le bas doit etre ameliore pour que si on veux inscrire un etdiant et il y'a des groupes l'etudiant doit etre ajoute
             * a le groupe ou contient le plus petit nombre
             * et s'il ya des groupes pour certains matiere les autres ou il n'ont pas de groupe l'etudiant doit etre inscrit dans planetudes seulement
             * il faut voir l'ancien forme de l'inscription de l'ISMS comenté en haut --MedBakar 06-10-2020
             */
            $query_planetudes='';
            if(!empty($_POST['sigleImpaire']))
            for($i=0; $i< count($_POST['sigleImpaire']); ++$i) {      
                $sigle = $_POST['sigleImpaire'][$i];
                $enseignantResp = 'E0011';
                $semestre=3;
                $query_planetudes.=",( $matriculeEtudiant,'".$sigle."','".$semestre."','-1','','AV','professeur','".$annee."' )";
            }
            if(!empty($query_planetudes)){
                //on fait l'insertion
                $this->db->query("INSERT INTO planetudes values ".substr($query_planetudes,1));
            }
            
            $query_planetudes='';
            if(!empty($_POST['siglePaire']))
            for($i=0; $i< count($_POST['siglePaire']); ++$i) {      
                $sigle = $_POST['siglePaire'][$i];
                $enseignantResp = 'E0011';
                $semestre=1;
                $query_planetudes.=",( $matriculeEtudiant,'".$sigle."','".$semestre."','-1','','AV','professeur','".($annee+1)."' )";
            }
            if(!empty($query_planetudes)){
                //on fait l'insertion
                $this->db->query("INSERT INTO planetudes values ".substr($query_planetudes,1));
            }
                 $data['retour'] = 'inscrire_classe';
                $data['typeBox'] = 'valid_box';
                $data['informations'] = 'L\' étudiant a bien été inscrit';    
     $this->load->view("scolarite/modification_confirme", $data);
    }
    else{
        
            $data['retour'] = 'inscrire_classe';
                $data['typeBox'] = 'error_box';
                $data['informations'] = 'il faut choisir au moins un élément';    
     $this->load->view("scolarite/modification_confirme", $data);
        
    }
            return;   

    }
    //add By MedBAkar 08-04-2020
    //function to return the groupes who the number of student enrolled is min
    function recup_groupe_min($sigle,$semsetre,$annee){
        $groupe=$this->scolarite_modele->get_min_groupe_par($sigle,$semsetre,$annee);
       // print_r($groupe);
        $td='';
        $tp='';
        $cm='';
        $cm_nb=1000;
        $td_nb=1000;
        $tp_nb=1000;
        //on chereche le min des groupes /type
        for($i=0;$i<count($groupe);$i++){
            if(strstr($groupe[$i]['idGroupe'],'Theorie')   ){
                if($cm_nb>=$groupe[$i]['nbrEtudiants']){
                $cm=$groupe[$i]['idGroupe'];
                $cm_nb=$groupe[$i]['nbrEtudiants'];}
            }
            else
                if(strstr($groupe[$i]['idGroupe'],'TD')   ){
                if($td_nb>=$groupe[$i]['nbrEtudiants']){
                $td=$groupe[$i]['idGroupe'];
                $td_nb=$groupe[$i]['nbrEtudiants'];}
            }
            else
                if(strstr($groupe[$i]['idGroupe'],'TP')   ){
                if($tp_nb>=$groupe[$i]['nbrEtudiants']){
                $tp=$groupe[$i]['idGroupe'];
                $tp_nb=$groupe[$i]['nbrEtudiants'];
                
                }
            }
        }
       // echo"CM-".$cm."<br>TD-".$td."<br>TP-".$tp;
        //return array($cm,$td,$tp);
        $array=array();
        if(!empty($cm))
        $array['Theorie']=$cm;
        if(!empty($td))
        $array['TD']=$td;
        if(!empty($tp))
        $array['TP']=$tp;
        
       // print_r($array);
        return $array;
    }
    
    
    function autorisation_acce() 
	{
            //employe qui peut modifier la note
            $employe=$this->session->userdata('matriculeEmploye');
            
		if (strlen($this->passwordCheikh) ==0) {	// si pas de mot de passe : on ne demande rien
		$this->clear_output();
                 $data_session_courante = $this->scolarite_modele->get_session_courante();
       
         $data = array('programme'=>$progs,'annee' => $data_session_courante['annee'][0], 
                    'semestre' => $data_session_courante['semestre'][0]);
     
        $this->load->view("scolarite/autorisation_etudiant",$data);
    }
	else	// on demande le mot de passe
	{
		$data = NULL;
        $this->clear_output();
		$this->form_validation->set_rules('pass', 'mot de passe', 'required');
		
    if (isset($_POST['pass']) AND $this->scolarite_modele->validation_modification_notes_eliminatoires($employe,$_POST['pass'])) // Si le mot de passe est bon
            
    {
                       $progs = $this->scolarite_modele->get_programme();
                        $data_session_courante = $this->scolarite_modele->get_session_courante();
       
                        $data = array('programme'=>$progs,'annee' => $data_session_courante['annee'][0], 
                    'semestre' => $data_session_courante['semestre'][0]);
     
			$this->load->view("scolarite/autorisation_etudiant",$data);
    }
    else // Sinon, on affiche un message d'erreur
    {
            $data['typeInterface'] = 'autorisation_etudiant';
			$data['titre'] = ' L\'accès à ce sous-menu est réservé.<br> Entrez le mot de passe.';
            $this->load->view("scolarite/chef_pass", $data);
    }
    
			
			
			
		
    }
	}
        function autorisation_acce_modif() 
	{
		if (strlen($this->passwordCheikh) ==0) {	// si pas de mot de passe : on ne demande rien
		$this->clear_output();
       
        $this->trouver_etudiant_pour_modifier_autorisation();
    }
	else	// on demande le mot de passe
	{
		$data = NULL;
        $this->clear_output();
		$this->form_validation->set_rules('pass', 'mot de passe', 'required');
		 if (isset($_POST['pass']) AND $_POST['pass'] ==  $this->passwordCheikh) // Si le mot de passe est bon
    {
                    $this->trouver_etudiant_pour_modifier_autorisation();
    }	

		else
		{
			$data['typeInterface'] = 'trouver_etudiant_pour_modifier_autorisation';
			$data['titre'] = 'L\'accès à ce sous-menu est réservé.<br> Entrez le mot de passe.';
            $this->load->view("scolarite/chef_pass_modif", $data);
		}
    }
	}
        
         function choix_annee_programme(){
    $controlleur = "scolarite";
    $titre = "Choisir l'annee";
    
   // $this->load->view("recherche_parametree", $data);
    $annes=$this->scolarite_modele->getAnnee();
    $progs = $this->scolarite_modele->get_programme();
        $data = array('titre' => $titre,'controlleur' => $controlleur,'annee'=>$annes,'programme'=>$progs);
    $this->load->view("scolarite/choix_annee_programme",$data);
}
    
  function voir_etudiants_filiere() 
    {
       
         $annee =  $_POST['annee'];
         
         $etudiants=$this->scolarite_modele->get_etudiants_isncrits246($annee);
         
         $repart=$this->rapartition_equilibree($annee,$etudiants);
          $data = array('etudiants' => $etudiants);
         $this->load->view('scolarite/etudiants_filiere', $data);
 
        
    }  
      public function voir_bulletin_nouvel() 
    {
            $matriculeEtudiant= $_POST['matriculeEtudiant'];
        $semestre =  $_POST['semestre'];
       // $annee =  $_POST['annee'];
        $annee =0;
        $data="";
         $s = $this->scolarite_modele->max_semestre($matriculeEtudiant) ;
      //print_r("ggg".$s);
        if($semestre==0){
           $this->load->view('scolarite/head_bulletin', $data);
        for($i=1;$i<$s+1;$i++){
        $semestre=$i;
        $min = $this->scolarite_modele->min_annee_admis($matriculeEtudiant,$semestre,$decision="Admis(e)") ;
       /*echo"**************************";
        echo($min."hh");
        echo"**************************"; */
        $annee=0;
        if($min!=null){
            
                       $annee=$min;
                       $passage = $this->scolarite_modele-> get_decision_passage($annee-1,$matriculeEtudiant);
                     // print_r($passage);
                            if($matriculeEtudiant==13042){
                       $annee=$min+2;
                      
                }
               
                    
           }else{
            $max = $this->scolarite_modele->max_annee_ajournee($matriculeEtudiant,$semestre,$decision="Ajourné(e)");
               $passage = $this->scolarite_modele-> get_decision_passage($max-1,$matriculeEtudiant);
               
     
            if($semestre%2==1){
                       $annee=$max-1;
                       
                       if($matriculeEtudiant==11003 or $matriculeEtudiant==15280){
                       $annee=$max;
                      
                }
                }else{
                     $annee=$max;
                }
                    
            }
        /*if($semestre == null)
            $semestre = 1;
        else if($semestre ='' || $semestre <1 || $semestre > 6)*/
                //$semestre = 1;
        $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
        $anne=0;
        $anne=$annee;
        //echo $annee."****".$i;
       /* if($semestre%2==1){
            $anne=$annee;
        }else{
            $anne=$annee-1;
        }*/
        $niveau = $this->scolarite_modele->getEtudiatNiveauInscrit($matriculeEtudiant,$anne);
        //print_r($niveau);
         $code = $this->scolarite_modele-> info_bulltin($matriculeEtudiant); 
        // print_r($code);
        $infoEtudiant['niveau']="L".$niveau;
        $dataE = $this->scolarite_modele->get_informations_etudiant($matriculeEtudiant);
        //$semRes = $this->scolarite_modele->getSemestreResult($matriculeEtudiant, $semestre);
        //$moduleRes = $this->scolarite_modele->getModulesResult($matriculeEtudiant, $semestre);
        $semRes=null;
        $moduleRes=null;
        $moduleNc=null;
         if($semestre%2==1){
            $semRes = $this->scolarite_modele->getSemestreResult_bis($matriculeEtudiant, $semestre,$annee);
            $moduleRes = $this->scolarite_modele->getModulesResult_bis_h($matriculeEtudiant, $semestre,$annee);
            //$moduleNc= $this->scolarite_modele->get_elt_nc($matriculeEtudiant,$annee,$semestre);
            // print_r($moduleNc['sigle']);
             
         }else{
            $semRes = $this->scolarite_modele->getSemestreResult_bis($matriculeEtudiant, $semestre,$annee);
            $moduleRes = $this->scolarite_modele->getModulesResult_bis_h($matriculeEtudiant, $semestre,$annee);
          //  $moduleNc= $this->scolarite_modele->get_elt_nc($matriculeEtudiant,$annee,$semestre);
            
         }
        
        $moyenne = $this->scolarite_modele-> get_moyenne_niveau($matriculeEtudiant);
       // print_r($moyenne);
        $data = array('max_semestre'=>$s,'moyenne'=>$moyenne,'passage'=>$passage,'code'=>$code,'infoE'=>$dataE,'nc' => $moduleNc,'info' => $infoEtudiant, 'semestre' => $semRes, 'modules' => $moduleRes, 'annee' => $annee,'numSem'=>$semestre);
      $info= $this->load->view('scolarite/bulltin_tous', $data);
        
        }// $this->load->view('scolarite/head_bulletin', $data);
        
         }else{
        $min = $this->scolarite_modele->min_annee_admis($matriculeEtudiant,$semestre,$decision="Admis(e)") ;
        // echo"**************************";
       // print_r($min);
        // echo"**************************";
        $annee=0;
        if($min!=null){
            
                       $annee=$min;
                       $passage = $this->scolarite_modele-> get_decision_passage($annee-1,$matriculeEtudiant);
                      
                            if($matriculeEtudiant==13042){
                       $annee=$min+2;
                      
                }
               
                    
           }else{
            $max = $this->scolarite_modele->max_annee_ajournee($matriculeEtudiant,$semestre,$decision="Ajourné(e)");
               $passage = $this->scolarite_modele-> get_decision_passage($max-1,$matriculeEtudiant);
               
     
            if($semestre%2==1){
                       $annee=$max-1;
                       
                       if($matriculeEtudiant==11003 or $matriculeEtudiant==15280){
                       $annee=$max;
                      
                }
                }else{
                     $annee=$max;
                }
                    
            }
        /*if($semestre == null)
            $semestre = 1;
        else if($semestre ='' || $semestre <1 || $semestre > 6)*/
                //$semestre = 1;
        $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
        $anne=$annee;
       /* if($semestre%2==1){
            $anne=$annee;
        }else{
            $anne=$annee-1;
        }*/
        $niveau = $this->scolarite_modele->getEtudiatNiveauInscrit($matriculeEtudiant,$anne);
         $code = $this->scolarite_modele-> info_bulltin($matriculeEtudiant); 
        // print_r($code);
        $infoEtudiant['niveau']="L".$niveau;
        $dataE = $this->scolarite_modele->get_informations_etudiant($matriculeEtudiant);
        //$semRes = $this->scolarite_modele->getSemestreResult($matriculeEtudiant, $semestre);
        //$moduleRes = $this->scolarite_modele->getModulesResult($matriculeEtudiant, $semestre);
        $semRes=null;
        $moduleRes=null;
        $moduleNc=null;
         if($semestre%2==1){
            $semRes = $this->scolarite_modele->getSemestreResult_bis($matriculeEtudiant, $semestre,$annee);
            $moduleRes = $this->scolarite_modele->getModulesResult_bis_h($matriculeEtudiant, $semestre,$annee);
            //$moduleNc= $this->scolarite_modele->get_elt_nc($matriculeEtudiant,$annee,$semestre);
            // print_r($moduleNc['sigle']);
             
         }else{
            $semRes = $this->scolarite_modele->getSemestreResult_bis($matriculeEtudiant, $semestre,$annee);
            $moduleRes = $this->scolarite_modele->getModulesResult_bis_h($matriculeEtudiant, $semestre,$annee);
          //  $moduleNc= $this->scolarite_modele->get_elt_nc($matriculeEtudiant,$annee,$semestre);
            
         }
        
        $moyenne = $this->scolarite_modele-> get_moyenne_niveau($matriculeEtudiant);
       // print_r($moyenne);
        $data = array('max_semestre'=>$s,'moyenne'=>$moyenne,'passage'=>$passage,'code'=>$code,'infoE'=>$dataE,'nc' => $moduleNc,'info' => $infoEtudiant, 'semestre' => $semRes, 'modules' => $moduleRes, 'annee' => $annee,'numSem'=>$semestre);
        $this->load->view('scolarite/bulltin', $data);
         //$this->load->view('scolarite/bulltin', $data);
        }
    }
    function attribution_places(){
         $this->load->view('scolarite/attribution_places');
    }
    function voir_diplome() 
    {    
         $matriculeE=$_POST['matriculeE'];
         $dip=null; 
//for ($x = 0; $x <  strlen($nom); $x++) {
    //echo mb_substr_count($nom, " "); // affiche 2
  
  //if($x==12)
  
// We can search for the character, ignoring anything before the offset
   // if($x==7)
  //  $pos = strpos($nom, ' ', 1); // $pos = 7, not 0
  //  echo $pos;
//}



         
             $dip=$this->scolarite_modele->get_diplome($matriculeE);
         
         $data=array('diplome'=>$dip);
        //print_r('');
        $this->load->view('scolarite/Diplome', $data);
 
        
    }  
    function lister_diplome() 
    {
       $this->load->view('scolarite/lister_diplome');
    }
    function Max_num() 
    {   
        $num_A = $_POST['num_A'];
        $num_B = $_POST['num_B'];
        $num_C = $_POST['num_C'];
        $num_D = $_POST['num_D'];
        $num_E = $_POST['num_E'];
        $num_F = $_POST['num_F'];
        $num_G = $_POST['num_G'];
        $nb_par_table = $_POST['nb_par_table'];
        
        $n=array(0,0,0,0);

foreach( $num_A as $key => $n_a ) {
  //print "The name is ".$n_a.", email is ".$num_B[$key].
   //     ", and location is ".$num_C[$key].". Thank you\n";

        $num_e=0;
        $num_f=0;
        $num_ef=0;
        $num_u=0;
        if($num_B[$key]<$num_F[$key]){
            $num_f=$num_F[$key]-$num_B[$key];
        }else{
            $num_f=0;
        }
        if($num_C[$key]<$num_E[$key]){
            $num_e=$num_E[$key]-$num_C[$key];
        }else{
            $num_f=0;
        }
        if(($num_e<$num_f) and ($n_a<$num_f)){
            $num_ef=$num_f-$n_a;
        }else if(($num_e<$num_f) and ($num_f<$n_a)){
            $num_ef=0;
        }
         if(($num_f<$num_e) and ($n_a<$num_e)){
            $num_ef=$num_e-$n_a;
        }else if(($num_f<$num_e) and ($num_e<$n_a)){
            $num_ef=0;
        }
        $num_U=$num_ef+$num_G[$key];
        if($n_a<$num_U){
            $num_u=$num_U-$n_a;
        }else{
            $num_u=0;
        }
        
        $n[$key]=$n_a+$num_B[$key]+$num_C[$key]+$num_D[$key]+$num_ef+$num_u;
       

}
$b1=$n[0];$b2=$n[1];$b3=$n[2];$b4=$n[3];
$b=array($b1,$b2,$b3,$b4);
sort($b,SORT_NUMERIC);
$n1=$b[0];$n2=$b[1];$n3=$b[2];$n4=$b[3];
//echo "n1=".$n1."\n";
//echo "n2=".$n2."\n";
//echo "n3=".$n3."\n";
//echo "n4=".$n4."\n";
$c1=0;$c2=0;$c3=0;$c4=0;
$i=1;

while($c1<$n1-1){
    $c1=$c1+1;
    $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $L1[$c1]=$i;
    $L2[$c2]=$i+1;
    $L3[$c3]=$i+2;
    $L4[$c4]=$i+3;
    $i=$i+4;
   
}
 //echo $i.'-';
while($c2<$n2-1){
    
    $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $L2[$c2]=$i;
    $L3[$c3]=$i+1;
    $L4[$c4]=$i+2;
    $i=$i+3;
    
}
 //echo $i.'-';
while($c3<$n3-1){
    
    $c3=$c3+1;
    $c4=$c4+1;
    
    $L3[$c3]=$i;
    $L4[$c4]=$i+1;
    $i=$i+2;
    
}
 //echo $i.'-';
while($c4<$n4-1){
    
    
    $c4=$c4+1;
    
    
    $L4[$c4]=$i;
    
    //if(($i%56)==0){
       $i=$i+1; 
   // }  else {
    //    $i=$i+2;
   // }
    
}
//echo $i.'-';
//$a= ($L4[$c4])/56;
//$r=(($L4[$c4])%56);
$a= $L4[$c4]/$nb_par_table;

$ns=0;
if($i==0){
    $ns=round($a, 0, PHP_ROUND_HALF_ODD);
}else{
    $ns=round($a, 0, PHP_ROUND_HALF_ODD);
}
//echo "NS=".$ns;
$m=$ns;
$l=$L1[$c1];
        $annee=2016;
$a=$this->scolarite_modele->etudant_filiere($annee);
//print_r($a);
//$etud=$this->scolarite_modele->list_num_exam($l,$a,$eb,$fcd,$g);
/*
$csg=0;
$cs=0;
while($c1<$n1){
   $c1=$c1+1; 
   $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $s=($csg%$m) +1;
    $i=70*($s-1)+1+$cs[$s];
    $cs[$s]=$cs[$s]+4;
    $L1[$c1]=$i;
    $L2[$c2]=$i+1;
    $L3[$c3]=$i+2;
    $L4[$c4]=$i+3;
    $csg=$csg+1;
}
while($c2<$n2){
   $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $s=($csg%$m) +1;
    $i=70*($s-1)+1+$cs[$s];
    $cs[$s]=$cs[$s]+3;
    $L2[$c2]=$i;
    $L3[$c3]=$i+1;
    $L4[$c4]=$i+2;
    $csg=$csg+1;
}
while($c3<$n3){
    
    $c3=$c3+1;
    $c4=$c4+1;
    $s=($csg%$m) +1;
    $i=70*($s-1)+1+$cs[$s];
    $cs[$s]=$cs[$s]+2;
    $L3[$c3]=$i;
    $L4[$c4]=$i+1;
    $csg=$csg+1;
}
while($c4<$n4){
   
    $c4=$c4+1;
    $s=($csg%$m) +1;
    $i=70*($s-1)+1+$cs[$s];
    $cs[$s]=$cs[$s]+1;
    $L4[$c4]=$i;
    $csg=$csg+1;
}*/
    
       $this->load->view('scolarite/Max_num');
    }
    function list_num_exam($l,$a,$eb,$fcd) {
       $result= array();
      /* echo 'nbl='.count($l).' nba='.count($a);
       echo 'nbl='.count($l).' neb='.count($eb);
       echo 'nbl='.count($l).' nbfcd='.count($fcd);*/
       /*
       echo '<br>-----------------------------------------------<br>';
       echo 'l=';print_r($l);
       echo 'a='; print_r($a);
         echo 'eb=';print_r($eb);
         echo 'fcd='; print_r($fcd);
          echo '<br>-----------------------------------------------<br>';*/
       if(isset($l)){
           if(isset($a)){
             for($i=0;$i<count($a);$i++)  {
             $result[$a[$i]]=$l[$i];
             }
           }
     /* echo '---------------<br>';
      print_r($eb);
       print_r($l);
       echo '---------------<br>';*/
           if(isset($eb)){
            for($i=0;$i<count($eb);$i++)  {
               $result[$eb[$i]]=$l[$i];
            }
           }
           if(isset($fcd)){
                for($i=0;$i<count($fcd);$i++)  {
                $result[$fcd[$i]]=$l[$i];
                }
           }
      
       }
      
       return $result;
      
      
    }
    function lister_carte() 
    {
       $this->load->view('scolarite/lister_carte');
    }
    function voir_cartes() 
    {   
//        if(isset($_POST['LGTR'])){
//            $LGTR= $_POST['LGTR'];
//         }else{
//             $LGTR= '';
//         }
//         if(isset($_POST['MAEF'])){
//            $MAEF= $_POST['MAEF'];
//         }else{
//             $MAEF= '';
//         }
//         if(isset($_POST['MAN'])){
//            $MAN= $_POST['MAN'];
//         }else{
//             $MAN= '';
//         }
//         if(isset($_POST['RXTL'])){
//            $RXTL= $_POST['RXTL'];
//         }else{
//             $RXTL= '';
//         }
         $debut=$_POST['debut'];
         $fin=$_POST['fin'];
        //begin "add by MedBakar 11-06-2020"
         $post=$this->input->post();
          $idProgramme=array();
         foreach($post as $idProg=>$value){
             if($idProg!='annee' && $idProg!='debut' && $idProg!='fin' && $idProg!='submit')
             $idProgramme[]=$idProg;
         }
         if(count($idProgramme)==0){
                $data['typeBox'] = 'warning_box';
                $data['informations'] = 'Aucune spécialité n’a été choisie.';
                $this->load->view('scolarite/modification_confirme', $data);
         }else{
//         print_r($idProgramme);
//         $idProgramme=$post;
                 //end MedBakar
         $annee =  $_POST['annee'];
 
         $infoCarte=$this->scolarite_modele->get_all_infoCarte($idProgramme,$annee,$debut,$fin);//modified by Med Bakar 11-06-2020
         //print_r($etudiants);
        /* 
        $l1='';
        $l2='';
        $l3='';
        if(($infoCarte['niveau']==1)){
            $l1='L1:S1 S2';
        }if(($infoCarte['niveau']==2)){
            $l2='L2:S3 S4';
        }if(($infoCarte['niveau']==3)){
            $l3='L3:S5 S6';
        }*/
         $param_generaux= $this->scolarite_modele->Recup_Parametre_Generaux();//add by MedBakar 11-06-2020
//         print_r($infoCarte);
        $data = array('infoCarte'=>$infoCarte,'param_genereaux'=>$param_generaux);
        $this->load->view("scolarite/cartes",$data);
            
        
         }
         
      
   }
   function choix_programme_pour_cartes() 
    {
        $controlleur = "scolarite";
    $titre = "";
    
   // $this->load->view("recherche_parametree", $data);
    $annes=$this->scolarite_modele->getAnnee();
    $progs = $this->scolarite_modele->get_programme();
        $data = array('titre' => $titre,'controlleur' => $controlleur,'annee'=>$annes,'programme'=>$progs);
//        print_r($data);
        $this->load->view("scolarite/choix_programme_pour_carte",$data);
    }
     function rapartition_etudiants1($annee) 
    {   /*
         $list_etu=$this->scolarite_modele->get_etudiants_isncrits135($annee);
         $lgtr= $list_etu['LGTR'];
        // print_r($lgtr);
         $rxtl= $list_etu['RXTEL'];
        // print_r($rxtl);
         $man= $list_etu['MAN'];
        // print_r($man);
         $maef= $list_etu['MAEF'];
         //print_r($maef);
        $num_A = array(count($rxtl['s1']),count($man['s1']),count($maef['s1']),count($lgtr['s1']));
        $num_B = array(0,0,0,0);
        $num_C = array(0,0,0,0);
        $num_D = array(0,0,0,0);
        $num_E = array(count($rxtl['s3']),count($man['s3']),count($maef['s3']),count($lgtr['s3']));
        $num_F = array(count($rxtl['s5']),count($man['s5']),count($maef['s5']),count($lgtr['s5']));
        $num_G = array(0,0,0,0);
        $nb_par_table = 4;
        
        $n=array(0,0,0,0);

foreach( $num_A as $key => $n_a ) {
  //print "The name is ".$n_a.", email is ".$num_B[$key].
   //     ", and location is ".$num_C[$key].". Thank you\n";

        $num_e=0;
        $num_f=0;
        $num_ef=0;
        $num_u=0;
        if($num_B[$key]<$num_F[$key]){
            $num_f=$num_F[$key]-$num_B[$key];
        }else{
            $num_f=0;
        }
        if($num_C[$key]<$num_E[$key]){
            $num_e=$num_E[$key]-$num_C[$key];
        }else{
            $num_f=0;
        }
        if(($num_e<$num_f) and ($n_a<$num_f)){
            $num_ef=$num_f-$n_a;
        }else if(($num_e<$num_f) and ($num_f<$n_a)){
            $num_ef=0;
        }
         if(($num_f<$num_e) and ($n_a<$num_e)){
            $num_ef=$num_e-$n_a;
        }else if(($num_f<$num_e) and ($num_e<$n_a)){
            $num_ef=0;
        }
        $num_U=$num_ef+$num_G[$key];
        if($n_a<$num_U){
            $num_u=$num_U-$n_a;
        }else{
            $num_u=0;
        }
        
        $n[$key]=$n_a+$num_B[$key]+$num_C[$key]+$num_D[$key]+$num_ef+$num_u;
       

}
//$b1=$n[0];$b2=$n[1];$b3=$n[2];$b4=$n[3];
$b1=  max($num_A[0],max($num_E[0],$num_F[0]));
$b2=  max($num_A[1],max($num_E[1],$num_F[1]));
$b3=  max($num_A[2],max($num_E[2],$num_F[2]));
$b4=  max($num_A[3],max($num_E[3],$num_F[3]));
$b=array($b1,$b2,$b3,$b4);
rsort($b,SORT_NUMERIC);
$j=0;
$filiere_o = array('a','a','a','a');
$i_rxtel = array_search($b1, $b);
//echo $i_rxtel.'i_rxtel';
$i_man= array_search($b2, $b);
$i_maef = array_search($b3, $b);
$i_lgtr = array_search($b4, $b);
$j=0; $k=0;
while($j<4){
    if($j==$i_rxtel  && $filiere_o[$k]!='RXTEL'){
        $filiere_o[$k]='RXTEL';
        $k++;
    }
    if($j==$i_man && $filiere_o[$k]!='MAN'){
        $filiere_o[$k]='MAN';
        $k++;
    }
    if($j==$i_maef && $filiere_o[$k]!='MAEF'){
        $filiere_o[$k]='MAEF';
        $k++;
    }
    if($j==$i_lgtr && $filiere_o[$k]!='LGTR'){
        $filiere_o[$k]='LGTR';
        $k++;
    }
    $j++;
}

$n1=$b[0];$n2=$b[1];$n3=$b[2];$n4=$b[3];
echo "n1=".$n1."\n";
echo "n2=".$n2."\n";
echo "n3=".$n3."\n";
echo "n4=".$n4."\n";
$c1=-1;$c2=-1;$c3=-1;$c4=-1;
$i=1;
$L1=array();
$L2=array();
$L3=array();
$L4=array();
while($c1<$n1-1){
    $c1=$c1+1;
    $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $L1[$c1]=$i;
    $L2[$c2]=$i+1;
    $L3[$c3]=$i+2;
    $L4[$c4]=$i+3;
    $i=$i+4;
   
}
 echo $i.'-';
 if($c1!=-1)
 $i=$L2[$c2]+3;
while($c2<$n2-1){
    
    $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $L2[$c2]=$i;
    $L3[$c3]=$i+1;
    $L4[$c4]=$i+2;
    $i=$i+3;
    
}
 echo $i.'-';
 if($c2!=-1)
    $i=$L3[$c3]+2;
while($c3<$n3-1){
    
    $c3=$c3+1;
    $c4=$c4+1;
    
    $L3[$c3]=$i;
    $L4[$c4]=$i+1;
    $i=$i+2;
    
}
 echo $i.'-';
 if($c3!=-1)
 $i=$L4[$c4]+2;
while($c4<$n4-1){
    
    
    $c4=$c4+1;
    
    
    $L4[$c4]=$i;
    
    if(($i%4)==0){
       $i=$i+1; 
   }  else {
      $i=$i+2;
   }
    
}
echo $i.'-';
//$a= ($L4[$c4])/56;
//$r=(($L4[$c4])%56);
$a= $L4[$c4]/$nb_par_table;

$ns=0;
if($i==0){
    $ns=round($a, 0, PHP_ROUND_HALF_ODD);
}else{
    $ns=round($a, 0, PHP_ROUND_HALF_ODD);
}
//echo "NS=".$ns;
$m=$ns;
$l = array();
$l= array($L1,$L2,$L3,$L4);
        $str="delete from numero_exam;";
         $this->db->query($str);
        for($i=0;$i<count($filiere_o);$i++){
            if($filiere_o[$i]=='RXTEL') {
                
                $etud=$this->list_num_exam($l[$i],$rxtl['s1'],$rxtl['s3'],$rxtl['s5']);
              //  print_r($etud);
            foreach ($etud as $key => $value) {
                $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
                
            
                
            }else if($filiere_o[$i]=='MAN') {
                $etud=$this->list_num_exam($l[$i],$man['s1'],$man['s3'],$man['s5']);
               // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }else if($filiere_o[$i]=='MAEF') {
                $etud=$this->list_num_exam($l[$i],$maef['s1'],$maef['s3'],$maef['s5']);
                // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }else{
                $etud=$this->list_num_exam($l[$i],$lgtr['s1'],$lgtr['s3'],$lgtr['s5']);
                // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }
        }*/
/*
$csg=0;
$cs=0;
while($c1<$n1){
   $c1=$c1+1; 
   $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $s=($csg%$m) +1;
    $i=70*($s-1)+1+$cs[$s];
    $cs[$s]=$cs[$s]+4;
    $L1[$c1]=$i;
    $L2[$c2]=$i+1;
    $L3[$c3]=$i+2;
    $L4[$c4]=$i+3;
    $csg=$csg+1;
}
while($c2<$n2){
   $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $s=($csg%$m) +1;
    $i=70*($s-1)+1+$cs[$s];
    $cs[$s]=$cs[$s]+3;
    $L2[$c2]=$i;
    $L3[$c3]=$i+1;
    $L4[$c4]=$i+2;
    $csg=$csg+1;
}
while($c3<$n3){
    
    $c3=$c3+1;
    $c4=$c4+1;
    $s=($csg%$m) +1;
    $i=70*($s-1)+1+$cs[$s];
    $cs[$s]=$cs[$s]+2;
    $L3[$c3]=$i;
    $L4[$c4]=$i+1;
    $csg=$csg+1;
}
while($c4<$n4){
   
    $c4=$c4+1;
    $s=($csg%$m) +1;
    $i=70*($s-1)+1+$cs[$s];
    $cs[$s]=$cs[$s]+1;
    $L4[$c4]=$i;
    $csg=$csg+1;
}*/
    
       $this->load->view('scolarite/Max_num');
    }
    function salle_exam() {
        
        $this->load->view('scolarite/salle_exam');
    }
    function numerotation_table() 
    {  
                 
         
         $max_fin=$this->scolarite_modele->get_max_fin();
         //echo 'max_fin=============='.$max_fin;
         $infoTicket=$this->scolarite_modele->get_all_infotick();
          // print_r($infoTicket);
         $infoSalle=$this->scolarite_modele->get_all_infoSalle();
          //print_r($infoSalle);
        $data = array('infoSalle'=>$infoSalle,'maxfin'=>$max_fin,'infoTicket'=>$infoTicket);
        $this->load->view('scolarite/numerotation_table',$data);
            
        
            
         
      
   }/*
    function rapartition_etudiants($annee)  {
        
        $list_etu=$this->scolarite_modele->get_etudiants_isncrits135($annee);
         $lgtr= $list_etu['LGTR'];
        // print_r($lgtr);
         $rxtl= $list_etu['RXTEL'];
        // print_r($rxtl);
         $man= $list_etu['MAN'];
        // print_r($man);
         $maef= $list_etu['MAEF'];
         //print_r($maef);
        $num_A = array(count($rxtl['s1']),count($man['s1']),count($maef['s1']),count($lgtr['s1']));
        $num_B = array(0,0,0,0);
        $num_C = array(0,0,0,0);
        $num_D = array(0,0,0,0);
        $num_E = array(count($rxtl['s3']),count($man['s3']),count($maef['s3']),count($lgtr['s3']));
        $num_F = array(count($rxtl['s5']),count($man['s5']),count($maef['s5']),count($lgtr['s5']));
        $num_G = array(0,0,0,0);
        $nb_par_table = 4;
        
        $n=array(0,0,0,0);

foreach( $num_A as $key => $n_a ) {
  //print "The name is ".$n_a.", email is ".$num_B[$key].
   //     ", and location is ".$num_C[$key].". Thank you\n";

        $num_e=0;
        $num_f=0;
        $num_ef=0;
        $num_u=0;
        if($num_B[$key]<$num_F[$key]){
            $num_f=$num_F[$key]-$num_B[$key];
        }else{
            $num_f=0;
        }
        if($num_C[$key]<$num_E[$key]){
            $num_e=$num_E[$key]-$num_C[$key];
        }else{
            $num_f=0;
        }
        if(($num_e<$num_f) and ($n_a<$num_f)){
            $num_ef=$num_f-$n_a;
        }else if(($num_e<$num_f) and ($num_f<$n_a)){
            $num_ef=0;
        }
         if(($num_f<$num_e) and ($n_a<$num_e)){
            $num_ef=$num_e-$n_a;
        }else if(($num_f<$num_e) and ($num_e<$n_a)){
            $num_ef=0;
        }
        $num_U=$num_ef+$num_G[$key];
        if($n_a<$num_U){
            $num_u=$num_U-$n_a;
        }else{
            $num_u=0;
        }
        
        $n[$key]=$n_a+$num_B[$key]+$num_C[$key]+$num_D[$key]+$num_ef+$num_u;
       

}
//$b1=$n[0];$b2=$n[1];$b3=$n[2];$b4=$n[3];
$b1=  max($num_A[0],max($num_E[0],$num_F[0]));
$b2=  max($num_A[1],max($num_E[1],$num_F[1]));
$b3=  max($num_A[2],max($num_E[2],$num_F[2]));
$b4=  max($num_A[3],max($num_E[3],$num_F[3]));
$b=array($b1,$b2,$b3,$b4);
//rsort($b,SORT_NUMERIC);
for($i=0; $i< 3;$i++) {
    for($j=$i+1; $j< 4;$j++) {
        if($b[$i]> $b[$j]) {
            $temp = $b[$i];
            $b[$i] = $b[$j];
            $b[$j] = $temp;
        }
    }
}
echo'b';
print_r($b);
echo'b';
$j=0;
$filiere_o = array('a','a','a','a');
$i_rxtel = array_search($b1, $b);
//echo $i_rxtel.'i_rxtel';
$i_man= array_search($b2, $b);
$i_maef = array_search($b3, $b);
$i_lgtr = array_search($b4, $b);
$j=0; $k=0;
while($j<4){
    if($j==$i_rxtel  && $filiere_o[$k]!='RXTEL'){
        $filiere_o[$k]='RXTEL';
        $k++;
    }
    if($j==$i_man && $filiere_o[$k]!='MAN'){
        $filiere_o[$k]='MAN';
        $k++;
    }
    if($j==$i_maef && $filiere_o[$k]!='MAEF'){
        $filiere_o[$k]='MAEF';
        $k++;
    }
    if($j==$i_lgtr && $filiere_o[$k]!='LGTR'){
        $filiere_o[$k]='LGTR';
        $k++;
    }
    $j++;
}


$n1=$b[0];$n2=$b[1];$n3=$b[2];$n4=$b[3];
$L1=array();
$L2=array();
$L3=array();
$L4=array();
$delta=$n4-$n3;
echo 'delta='.$delta.'n1='.$n1;
$h_delta = $delta/2;
if($delta %2 == 1) $h_delta = $h_delta-0.5;
$c=1;
    $pos1=0;
     $pos2=0;
if($h_delta <= $n1){
    
     for($i=1;$i<=$h_delta;$i++){
      
    $L4[$pos1]=$c;
    $L4[$pos1+1]=$c+3;
    $L1[$pos2]=$c+1;
    $L2[$pos2]=$c+2;
    $c=$c+4;
    $pos1 +=2;
    $pos2 +=1;
     }
     
}
if($delta%2 == 1 ) $n4 = $n3+1;
else $n4 = $n3;
$n1 = $n1-$h_delta;
$n2 = $n2-$h_delta;
echo "n1=".$n1."\n";
echo "n2=".$n2."\n";
echo "n3=".$n3."\n";
echo "n4=".$n4."\n";
$c1=$pos2-1;$c2=$pos2-1;$c3=-1;$c4=$pos1-1;
$i=$c;

while($c1<$n1-1+$pos2){
    $c1=$c1+1;
    $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $L1[$c1]=$i;
    $L2[$c2]=$i+1;
    $L3[$c3]=$i+2;
    $L4[$c4]=$i+3;
    $i=$i+4;
   
}
 echo $i.'-';
 if($c1!=-1)
 $i=$L2[$c2]+3;
while($c2<$n2-1+$pos2){
    
    $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $L2[$c2]=$i;
    $L3[$c3]=$i+1;
    $L4[$c4]=$i+2;
    $i=$i+3;
    
}
 echo $i.'-';
 if($c2!=-1)
    $i=$L3[$c3]+2;
while($c3<$n3-1){
    
    $c3=$c3+1;
    $c4=$c4+1;
    
    $L3[$c3]=$i;
    $L4[$c4]=$i+1;
    $i=$i+2;
    
}
 echo $i.'-';
 if($c3!=-1)
 $i=$L4[$c4]+2;
while($c4<$n4-1+$pos1){
    
    
    $c4=$c4+1;
    
    
    $L4[$c4]=$i;
    
    if(($i%4)==0){
       $i=$i+1; 
   }  else {
      $i=$i+2;
   }
    
}
echo $i.'-';
//$a= ($L4[$c4])/56;
//$r=(($L4[$c4])%56);
$a= $L4[$c4]/$nb_par_table;

$ns=0;
if($i==0){
    $ns=round($a, 0, PHP_ROUND_HALF_ODD);
}else{
    $ns=round($a, 0, PHP_ROUND_HALF_ODD);
}
//echo "NS=".$ns;
$m=$ns;
$l = array();
$l= array($L1,$L2,$L3,$L4);
$str="delete from numero_exam;";
         $this->db->query($str);
        for($i=0;$i<count($filiere_o);$i++){
            if($filiere_o[$i]=='RXTEL') {
                
                $etud=$this->list_num_exam($l[$i],$rxtl['s2'],$rxtl['s4'],$rxtl['s6']);
              //  print_r($etud);
            foreach ($etud as $key => $value) {
                $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
                
            
                
            }else if($filiere_o[$i]=='MAN') {
                $etud=$this->list_num_exam($l[$i],$man['s2'],$man['s4'],$man['s6']);
               // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }else if($filiere_o[$i]=='MAEF') {
                $etud=$this->list_num_exam($l[$i],$maef['s2'],$maef['s4'],$maef['s6']);
                // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }else{
                $etud=$this->list_num_exam($l[$i],$lgtr['s2'],$lgtr['s4'],$lgtr['s6']);
                // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }
        }
    }*/
      function afficher_pv_elts_noncapit()
    {
        $data['programme'] = $this->scolarite_modele->get_programme();
         $sessionCourante = $this->scolarite_modele->get_session_courante();
         $annee=$sessionCourante['annee'];
         $data['annee'] = $annee;
        $this->load->view("scolarite/elements_non_capit", $data);       
         
    }
    
    function inserer_notes_noncapit()
    {      $programme = $_POST['idProgramme'];
           $sessionCourante = $this->scolarite_modele->get_session_courante();
           $annee=$sessionCourante['annee'][0];
           $semestre =  $_POST['semestre'];
            $data['elements1'] = $this->scolarite_modele-> get_all_elements_nc($semestre,$annee,$programme,$idEvaluation=1);
            $data['elements2'] = $this->scolarite_modele-> get_all_elements_nc($semestre,$annee,$programme,$idEvaluation=2);
            $data['elements4'] = $this->scolarite_modele-> get_all_elements_nc($semestre,$annee,$programme,$idEvaluation=4);
             $data['informations']="Le transfert des notes est effectué avec succes";
              $data['type']="all_sigle";
           // print_r($data['elements']);
        $this->load->view("scolarite/element_non_capit", $data);       
         
    }
     function afficher_cours_exam($option='ex')
    {
         $anonymat=1;
  if(isset($_POST['option']))
        $option=$_POST['option'];
         if(isset($_POST['anonymat'])){
        $anonymat=$_POST['anonymat'];
         }else{
             $anonymat=1;
         }
 $semestre=$this->input->post('semestre');
$session='';
if(($semestre%2)==0)
$session=1;
else
$session=3;
$idProgramme=$this->input->post('idProgramme');
$nomProgramme=$this->scolarite_modele->get_programme_nom($idProgramme);
$sessionCourante = $this->scolarite_modele->get_session_courante();
           $annee=$sessionCourante['annee'][0];
           
        $tables = array("module", "groupe", "unite");
        $join_keys = array("groupe.sigle = module.sigle ", "unite.sigle=module.sigleunite");
        $db_columns = array('groupe.sigle as sigleGroupe', 'module.titre as titreModule', 'unite.sigle as sigleUnite');
        $result_columns = array('sigleGroupe', 'titreModule', 'sigleUnite');
        $grid_columns = array('Sigle Elément', 'Titre', 'Sigle Unite');
        
     
      $sigle='';
 $action = 'rediger_notes_nc/'.$semestre.'/' .$annee.'/' . $idProgramme. '/' . $sigle;
        
    
        $id_action = '';
        //$matriculeProfesseur = $this->session->userdata('matriculeEmploye');


        $where = "WHERE annee = " . $this->input->post('annee') . " AND groupe.semestre = " . $session ;
$where.=" AND module.idDepartement='".$idProgramme."' ";
$where.=" AND unite.semestre=".$this->input->post('semestre')." ";
        
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $where, $result_columns);
        $titre = 'Sélectionnez élément de la filière : <strong>'.$nomProgramme.' (S' .$semestre.')</strong>';
        $controlleur = "professeur";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);

        $this->load->view("recherche_parametree", $data);
    }
   function choix_session(){
       $data=null;
       $choix=1;
       if(isset($_POST['choix']))
           $choix=$_POST['choix'];
           $programme = $_POST['idProgramme'];
           $sessionCourante = $this->scolarite_modele->get_session_courante();
           $annee=$sessionCourante['annee'][0];
           $semestre =  $_POST['semestre'];
       if($choix==0){
           $this->afficher_cours_exam();
       }else{
           
            $data['elements'] = $this->inserer_notes_noncapit();
           // print_r($data['elements']);
      
       }   
         
   }
     function rediger_notes_nc($semestre,$annee, $idProgramme, $sigle){
        $data['elements1'] = $this->scolarite_modele-> transfert_note($semestre,$annee, $idProgramme, $sigle,$idEvaluation=1);
                   // print_r($data['elements']);
      
       $data['informations']="Le transfert des notes a éte effectué avec succes";
              $data['type']="sigle";
         $this->load->view("scolarite/element_non_capit", $data); 
    }
     function rapartition_etudiants_s2_s4_s6($annee)  {
        
        $list_etu=$this->scolarite_modele->get_etudiants_isncrits246($annee);
         $lgtr= $list_etu['LGTR'];
        // print_r($lgtr);
         $rxtl= $list_etu['RXTEL'];
         $man= $list_etu['MAN'];
        // print_r($man);
         $maef= $list_etu['MAEF'];
         //print_r($maef);
        $num_A = array(count($rxtl['s2']),count($man['s2']),count($maef['s2']),count($lgtr['s2']));
        $num_B = array(0,0,0,0);
        $num_C = array(0,0,0,0);
        $num_D = array(0,0,0,0);
        $num_E = array(count($rxtl['s4']),count($man['s4']),count($maef['s4']),count($lgtr['s4']));
        $num_F = array(count($rxtl['s6']),count($man['s6']),count($maef['s6']),count($lgtr['s6']));
        $num_G = array(0,0,0,0);
        $nb_par_table = 4;
        
        $n=array(0,0,0,0);

foreach( $num_A as $key => $n_a ) {
  //print "The name is ".$n_a.", email is ".$num_B[$key].
   //     ", and location is ".$num_C[$key].". Thank you\n";

        $num_e=0;
        $num_f=0;
        $num_ef=0;
        $num_u=0;
        if($num_B[$key]<$num_F[$key]){
            $num_f=$num_F[$key]-$num_B[$key];
        }else{
            $num_f=0;
        }
        if($num_C[$key]<$num_E[$key]){
            $num_e=$num_E[$key]-$num_C[$key];
        }else{
            $num_f=0;
        }
        if(($num_e<$num_f) and ($n_a<$num_f)){
            $num_ef=$num_f-$n_a;
        }else if(($num_e<$num_f) and ($num_f<$n_a)){
            $num_ef=0;
        }
         if(($num_f<$num_e) and ($n_a<$num_e)){
            $num_ef=$num_e-$n_a;
        }else if(($num_f<$num_e) and ($num_e<$n_a)){
            $num_ef=0;
        }
        $num_U=$num_ef+$num_G[$key];
        if($n_a<$num_U){
            $num_u=$num_U-$n_a;
        }else{
            $num_u=0;
        }
        
        $n[$key]=$n_a+$num_B[$key]+$num_C[$key]+$num_D[$key]+$num_ef+$num_u;
       

}
//$b1=$n[0];$b2=$n[1];$b3=$n[2];$b4=$n[3];
$b1=  max($num_A[0],max($num_E[0],$num_F[0]));
$b2=  max($num_A[1],max($num_E[1],$num_F[1]));
$b3=  max($num_A[2],max($num_E[2],$num_F[2]));
$b4=  max($num_A[3],max($num_E[3],$num_F[3]));
$b=array($b1,$b2,$b3,$b4);
//rsort($b,SORT_NUMERIC);
for($i=0; $i< 3;$i++) {
    for($j=$i+1; $j< 4;$j++) {
        if($b[$i]> $b[$j]) {
            $temp = $b[$i];
            $b[$i] = $b[$j];
            $b[$j] = $temp;
        }
    }
}
//echo'b';
//print_r($b);
//echo'b';
$j=0;
$filiere_o = array('a','a','a','a');
$i_rxtel = array_search($b1, $b);
//echo $i_rxtel.'i_rxtel';
$i_man= array_search($b2, $b);
$i_maef = array_search($b3, $b);
$i_lgtr = array_search($b4, $b);
$j=0; $k=0;
while($j<4){
    if($j==$i_rxtel  && $filiere_o[$k]!='RXTEL'){
        $filiere_o[$k]='RXTEL';
        $k++;
    }
    if($j==$i_man && $filiere_o[$k]!='MAN'){
        $filiere_o[$k]='MAN';
        $k++;
    }
    if($j==$i_maef && $filiere_o[$k]!='MAEF'){
        $filiere_o[$k]='MAEF';
        $k++;
    }
    if($j==$i_lgtr && $filiere_o[$k]!='LGTR'){
        $filiere_o[$k]='LGTR';
        $k++;
    }
    $j++;
}
//echo 'c';
//print_r($filiere_o);
//echo 'c';

$n1=$b[0];$n2=$b[1];$n3=$b[2];$n4=$b[3];
$L1=array();
$L2=array();
$L3=array();
$L4=array();
$delta=$n4-$n3;
//echo 'delta='.$delta.'n1='.$n1;
$h_delta = $delta/2;
if($delta %2 == 1) $h_delta = $h_delta-0.5;
$c=1;
    $pos1=0;
     $pos2=0;
if($h_delta <= $n1){
    
     for($i=1;$i<=$h_delta;$i++){
      
    $L4[$pos1]=$c;
    $L4[$pos1+1]=$c+3;
    $L1[$pos2]=$c+1;
    $L2[$pos2]=$c+2;
    $c=$c+4;
    $pos1 +=2;
    $pos2 +=1;
     }
     
}
if($delta%2 == 1 ) $n4 = $n3+1;
else $n4 = $n3;
$n1 = $n1-$h_delta;
$n2 = $n2-$h_delta;
//echo "n1=".$n1."\n";
//echo "n2=".$n2."\n";
//echo "n3=".$n3."\n";
//echo "n4=".$n4."\n";
$c1=$pos2-1;$c2=$pos2-1;$c3=-1;$c4=$pos1-1;
$i=$c;

while($c1<$n1-1+$pos2){
    $c1=$c1+1;
    $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $L1[$c1]=$i;
    $L2[$c2]=$i+1;
    $L3[$c3]=$i+2;
    $L4[$c4]=$i+3;
    $i=$i+4;
   
}
 //echo $i.'-';
 if($c1!=-1)
 $i=$L2[$c2]+3;
while($c2<$n2-1+$pos2){
    
    $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $L2[$c2]=$i;
    $L3[$c3]=$i+1;
    $L4[$c4]=$i+2;
    $i=$i+3;
    
}
 //echo $i.'-';
 if($c2!=-1)
    $i=$L3[$c3]+2;
while($c3<$n3-1){
    
    $c3=$c3+1;
    $c4=$c4+1;
    
    $L3[$c3]=$i;
    $L4[$c4]=$i+1;
    $i=$i+2;
    
}
 //echo $i.'-';
 if($c3!=-1)
 $i=$L4[$c4]+2;
while($c4<$n4-1+$pos1){
    
    
    $c4=$c4+1;
    
    
    $L4[$c4]=$i;
    
    if(($i%4)==0){
       $i=$i+1; 
   }  else {
      $i=$i+2;
   }
    
}
//echo $i.'-';

     
//echo "NS=".$ns;
$m=$ns;
$l = array();
$l= array($L1,$L2,$L3,$L4);
//echo 'l';
//print_r($l);
$str="delete from numero_exam;";
         $this->db->query($str);
        for($i=0;$i<count($filiere_o);$i++){
            if($filiere_o[$i]=='RXTEL') {
                
                $etud=$this->list_num_exam($l[$i],$rxtl['s2'],$rxtl['s4'],$rxtl['s6']);
              //  print_r($etud);
            foreach ($etud as $key => $value) {
                $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
                
            
                
            }else if($filiere_o[$i]=='MAN') {
                $etud=$this->list_num_exam($l[$i],$man['s2'],$man['s4'],$man['s6']);
               // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }else if($filiere_o[$i]=='MAEF') {
                $etud=$this->list_num_exam($l[$i],$maef['s2'],$maef['s4'],$maef['s6']);
                // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }else{
                $etud=$this->list_num_exam($l[$i],$lgtr['s2'],$lgtr['s4'],$lgtr['s6']);
                // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }
        }
    }
    // debut rapartition par salle
    
    function rapartition_par_salle($list_etu,$nombres, $debut,$debut_a,$debut_eb,$debut_fcd)  {
        
        //$list_etu=$this->scolarite_modele->get_etudiants_isncrits246($annee);
         $lgtr= $list_etu['LGTR'];
        // print_r($lgtr);
         $rxtl= $list_etu['RXTEL'];
         $man= $list_etu['MAN'];
        // print_r($man);
         $maef= $list_etu['MAEF'];
         //print_r($maef);
         
          $lgtr1= $nombres['LGTR'];
        // print_r($lgtr);
         $rxtl1= $nombres['RXTEL'];
         $man1= $nombres['MAN'];
        // print_r($man);
         $maef1= $nombres['MAEF'];
        
        $num_A = array($rxtl1['s2'],$man1['s2'],$maef1['s2'],$lgtr1['s2']);
        $num_B = array(0,0,0,0);
        $num_C = array(0,0,0,0);
        $num_D = array(0,0,0,0);
        $num_E = array($rxtl1['s4'],$man1['s4'],$maef1['s4'],$lgtr1['s4']);
        $num_F = array($rxtl1['s6'],$man1['s6'],$maef1['s6'],$lgtr1['s6']);
        $num_G = array(0,0,0,0);
        $nb_par_table = 4;
        
        $n=array(0,0,0,0);

foreach( $num_A as $key => $n_a ) {
  //print "The name is ".$n_a.", email is ".$num_B[$key].
   //     ", and location is ".$num_C[$key].". Thank you\n";

        $num_e=0;
        $num_f=0;
        $num_ef=0;
        $num_u=0;
        if($num_B[$key]<$num_F[$key]){
            $num_f=$num_F[$key]-$num_B[$key];
        }else{
            $num_f=0;
        }
        if($num_C[$key]<$num_E[$key]){
            $num_e=$num_E[$key]-$num_C[$key];
        }else{
            $num_f=0;
        }
        if(($num_e<$num_f) and ($n_a<$num_f)){
            $num_ef=$num_f-$n_a;
        }else if(($num_e<$num_f) and ($num_f<$n_a)){
            $num_ef=0;
        }
         if(($num_f<$num_e) and ($n_a<$num_e)){
            $num_ef=$num_e-$n_a;
        }else if(($num_f<$num_e) and ($num_e<$n_a)){
            $num_ef=0;
        }
        $num_U=$num_ef+$num_G[$key];
        if($n_a<$num_U){
            $num_u=$num_U-$n_a;
        }else{
            $num_u=0;
        }
        
        $n[$key]=$n_a+$num_B[$key]+$num_C[$key]+$num_D[$key]+$num_ef+$num_u;
       

}
//$b1=$n[0];$b2=$n[1];$b3=$n[2];$b4=$n[3];
$b1=  max($num_A[0],max($num_E[0],$num_F[0]));
$b2=  max($num_A[1],max($num_E[1],$num_F[1]));
$b3=  max($num_A[2],max($num_E[2],$num_F[2]));
$b4=  max($num_A[3],max($num_E[3],$num_F[3]));
$b=array($b1,$b2,$b3,$b4);
//rsort($b,SORT_NUMERIC);
for($i=0; $i< 3;$i++) {
    for($j=$i+1; $j< 4;$j++) {
        if($b[$i]> $b[$j]) {
            $temp = $b[$i];
            $b[$i] = $b[$j];
            $b[$j] = $temp;
        }
    }
}
$j=0;
$filiere_o = array('a','a','a','a');
$i_rxtel = array_search($b1, $b);
//echo $i_rxtel.'i_rxtel';
$i_man= array_search($b2, $b);
$i_maef = array_search($b3, $b);
$i_lgtr = array_search($b4, $b);
$j=0; $k=0;
while($j<4){
    if($j==$i_rxtel  && $filiere_o[$k]!='RXTEL'){
        $filiere_o[$k]='RXTEL';
        $k++;
    }
    if($j==$i_man && $filiere_o[$k]!='MAN'){
        $filiere_o[$k]='MAN';
        $k++;
    }
    if($j==$i_maef && $filiere_o[$k]!='MAEF'){
        $filiere_o[$k]='MAEF';
        $k++;
    }
    if($j==$i_lgtr && $filiere_o[$k]!='LGTR'){
        $filiere_o[$k]='LGTR';
        $k++;
    }
    $j++;
}

$n1=$b[0];$n2=$b[1];$n3=$b[2];$n4=$b[3];
$L1=array();
$L2=array();
$L3=array();
$L4=array();
$delta=$n4-$n3;
$h_delta = $delta/2;
if($delta %2 == 1) $h_delta = $h_delta-0.5;
$c=$debut;
    $pos1=0;
     $pos2=0;
if($h_delta <= $n1){
    
     for($i=1;$i<=$h_delta;$i++){
      
    $L4[$pos1]=$c;
    $L4[$pos1+1]=$c+3;
    $L1[$pos2]=$c+1;
    $L2[$pos2]=$c+2;
    $c=$c+4;
    $pos1 +=2;
    $pos2 +=1;
     }
     
}
if($delta%2 == 1 ) $n4 = $n3+1;
else $n4 = $n3;
$n1 = $n1-$h_delta;
$n2 = $n2-$h_delta;
//echo "n1=".$n1."\n";
//echo "n2=".$n2."\n";
//echo "n3=".$n3."\n";
//echo "n4=".$n4."\n";
$c1=$pos2-1;$c2=$pos2-1;$c3=-1;$c4=$pos1-1;
$i=$c;

while($c1<$n1-1+$pos2){
    $c1=$c1+1;
    $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $L1[$c1]=$i;
    $L2[$c2]=$i+1;
    $L3[$c3]=$i+2;
    $L4[$c4]=$i+3;
    $i=$i+4;
   
}
 //echo $i.'-';
 if($c1!=-1)
 $i=$L2[$c2]+3;
while($c2<$n2-1+$pos2){
    
    $c2=$c2+1;
    $c3=$c3+1;
    $c4=$c4+1;
    $L2[$c2]=$i;
    $L3[$c3]=$i+1;
    $L4[$c4]=$i+2;
    $i=$i+3;
    
}
 //echo $i.'-';
 if($c2!=-1)
    $i=$L3[$c3]+2;
while($c3<$n3-1){
    
    $c3=$c3+1;
    $c4=$c4+1;
    
    $L3[$c3]=$i;
    $L4[$c4]=$i+1;
    $i=$i+2;
    
}
 //echo $i.'-';
 if($c3!=-1)
 $i=$L4[$c4]+2;
while($c4<$n4-1+$pos1){
    
    
    $c4=$c4+1;
    
    
    $L4[$c4]=$i;
    
    if(($i%4)==0){
       $i=$i+1; 
   }  else {
      $i=$i+2;
   }
    
}
//echo $i.'-';

     
//echo "NS=".$ns;
//$m=$ns;
$l = array();
$l= array($L1,$L2,$L3,$L4);
/* echo '************* L1---L4';
print_r($l);
echo '************* L1---L4';
echo ' ************** numeros debuts  **************';
print_r($debut_a);
print_r($debut_eb);
print_r($debut_fcd); 
echo ' ************** numeros debuts  **************';
  */
 
    for($i=0;$i<count($filiere_o);$i++){
            if($filiere_o[$i]=='RXTEL') {
               $d_a = $debut_a['RXTEL'];
               $d_eb = $debut_eb['RXTEL'];
               $d_fcd= $debut_fcd['RXTEL'];
               $nba = $rxtl1['s2'];
               $nbeb = $rxtl1['s4'];
               $nbfcd = $rxtl1['s6'];
//               echo 'nba rxtl = '.$nba;
//               echo 'nbeb rxtl = '.$nbeb;
//               echo  'nbfcd rxtl = '.$nbfcd;
                $a = array();
                $eb = array();
                $fcd = array();
                for($k = $d_a; $k<$d_a +$nba; $k++ )
                    $a[] = $rxtl['s2'][$k];
                for($k = $d_eb; $k<$d_eb +$nbeb; $k++ )
                    $eb[] = $rxtl['s4'][$k];
                for($k = $d_fcd; $k<$d_fcd +$nbfcd; $k++ )
                    $fcd[] = $rxtl['s6'][$k];
               
//               
//               echo '$$$$$$$$ nb $rxtl[s2]= '.count($rxtl['s2']);
//               echo '$$$$$$$$ nb $rxtl[s4]= '.count($rxtl['s4']);
//               echo '$$$$$$$$ nb $rxtl[s6]= '.count($rxtl['s6']);
//               
//               echo '$$$$$$$$ nb a= '.count($a);
//               echo '$$$$$$$$ nb eb = '.count($eb);
//               echo '$$$$$$$$ nb fcd= '.count($fcd);
               
               
               $result = $this->attrubuer_numeros($l[$i],$a,$eb,$fcd,$d_a,$d_eb,$d_fcd);
               $etud=$result['liste_etu'];
               $debut_a['RXTEL'] =$result['debut_a'] ;
               $debut_eb['RXTEL']= $result['debut_eb'] ;
               $debut_fcd['RXTEL'] =$result['debut_fcd'] ;
              //  print_r($etud);
            foreach ($etud as $key => $value) {
                $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
                
            
                
            }else if($filiere_o[$i]=='MAN') {
                               
                $d_a = $debut_a['MAN'];
               $d_eb = $debut_eb['MAN'];
               $d_fcd= $debut_fcd['MAN'];
               
               $nba = $man1['s2'];
               $nbeb = $man1['s4'];
               $nbfcd = $man1['s6'];
               $a = array();
                $eb = array();
                $fcd = array();
                
//                 echo 'nba man = '.$nba;
//               echo 'nbeb man = '.$nbeb;
//               echo  'nbfcd man = '.$nbfcd;
                
                for($k = $d_a; $k<$d_a +$nba; $k++ )
                    $a[] = $man['s2'][$k];
                for($k = $d_eb; $k<$d_eb +$nbeb; $k++ )
                    $eb[] = $man['s4'][$k];
                for($k = $d_fcd; $k<$d_fcd +$nbfcd; $k++ )
                    $fcd[] = $man['s6'][$k];
               
               
//               echo '$$$$$$$$ nb $man[s2]= '.count($man['s2']);
//               echo '$$$$$$$$ nb $man[s4]= '.count($man['s4']);
//               echo '$$$$$$$$ nb $man[s6]= '.count($man['s6']);
//               
//               echo '$$$$$$$$ nb a= '.count($a);
//               echo '$$$$$$$$ nb eb = '.count($eb);
//               echo '$$$$$$$$ nb fcd= '.count($fcd);
               
               $result = $this->attrubuer_numeros($l[$i],$a,$eb,$fcd,$d_a,$d_eb,$d_fcd);
               $etud=$result['liste_etu'];
               $debut_a['MAN'] =$result['debut_a'] ;
               $debut_eb['MAN']= $result['debut_eb'] ;
               $debut_fcd['MAN'] =$result['debut_fcd'] ;
                
               // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }else if($filiere_o[$i]=='MAEF') {
                //$etud=$this->list_num_exam($l[$i],$maef['s2'],$maef['s4'],$maef['s6']);
                 $d_a = $debut_a['MAEF'];
               $d_eb = $debut_eb['MAEF'];
               $d_fcd= $debut_fcd['MAEF'];
               
               $nba = $maef1['s2'];
               $nbeb = $maef1['s4'];
               $nbfcd = $maef1['s6'];
               
//                echo 'nba maef = '.$nba;
//               echo 'nbeb  maef = '.$nbeb;
//               echo  'nbfcd maef = '.$nbfcd;
               
               $a = array();
                $eb = array();
                $fcd = array();
                for($k = $d_a; $k<$d_a +$nba; $k++ )
                    $a[] = $maef['s2'][$k];
                for($k = $d_eb; $k<$d_eb +$nbeb; $k++ )
                    $eb[] = $maef['s4'][$k];
                for($k = $d_fcd; $k<$d_fcd +$nbfcd; $k++ )
                    $fcd[] = $maef['s6'][$k];
               
               
//               echo '$$$$$$$$ nb $maef[s2]= '.count($maef['s2']);
//               echo '$$$$$$$$ nb $maef[s4]= '.count($maef['s4']);
//               echo '$$$$$$$$ nb $maef[s6]= '.count($maef['s6']);
//               
//               echo '$$$$$$$$ nb a= '.count($a);
//               echo '$$$$$$$$ nb eb = '.count($eb);
//               echo '$$$$$$$$ nb fcd= '.count($fcd);
//               
               $result = $this->attrubuer_numeros($l[$i],$a,$eb,$fcd,$d_a,$d_eb,$d_fcd);
               $etud=$result['liste_etu'];
               $debut_a['MAEF'] =$result['debut_a'] ;
               $debut_eb['MAEF']= $result['debut_eb'] ;
               $debut_fcd['MAEF'] =$result['debut_fcd'] ;
                // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }else{
                //$etud=$this->list_num_exam($l[$i],$lgtr['s2'],$lgtr['s4'],$lgtr['s6']);
                  $d_a = $debut_a['LGTR'];
               $d_eb = $debut_eb['LGTR'];
               $d_fcd= $debut_fcd['LGTR'];
               
               $nba = $lgtr1['s2'];
               $nbeb = $lgtr1['s4'];
               $nbfcd = $lgtr1['s6'];
               
               $a = array();
                $eb = array();
                $fcd = array();
                for($k = $d_a; $k<$d_a +$nba; $k++ )
                    $a[] = $lgtr['s2'][$k];
                for($k = $d_eb; $k<$d_eb +$nbeb; $k++ )
                    $eb[] = $lgtr['s4'][$k];
                for($k = $d_fcd; $k<$d_fcd +$nbfcd; $k++ )
                    $fcd[] = $lgtr['s6'][$k];
               
//               echo '$$$$$$$$ nb $lgtr[s2]= '.count($lgtr['s2']);
//               echo '$$$$$$$$ nb $lgtr[s4]= '.count($lgtr['s4']);
//               echo '$$$$$$$$ nb $lgtr[s6]= '.count($lgtr['s6']);
//               
//               echo '$$$$$$$$ nb a= '.count($a);
//               echo '$$$$$$$$ nb eb = '.count($eb);
//               echo '$$$$$$$$ nb fcd= '.count($fcd);
               $result = $this->attrubuer_numeros($l[$i],$a,$eb,$fcd,$d_a,$d_eb,$d_fcd);
               $etud=$result['liste_etu'];
               $debut_a['LGTR'] =$result['debut_a'] ;
               $debut_eb['LGTR']= $result['debut_eb'] ;
               $debut_fcd['LGTR'] =$result['debut_fcd'] ;
                // print_r($etud);
                foreach ($etud as $key => $value) {
                
                        $data = array(
                'matriculeEtudiant' => $key,
                'num_exam' => $value
            );
                        //insert the form data into database
            $this->db->insert('numero_exam', $data);
               }
            }
        }
       $result = array();
       $result['debut_a'] = $debut_a;
       $result['debut_eb'] = $debut_eb;
       $result['debut_fcd'] = $debut_fcd;
       return $result;
    }
    
    
    function rapartition_equilibree($annee,$list_etu){
        
         $lgtr= $list_etu['LGTR'];
         $rxtl= $list_etu['RXTEL'];
         $man= $list_etu['MAN'];
         $maef= $list_etu['MAEF'];
         
         $debut_salles = $this->scolarite_modele->getSallesDebut();
         $pourcentages_salles = $this->scolarite_modele-> getPourcentageParSalle();
         $nombres = array();
         
         $str="delete from numero_exam;";
         $this->db->query($str);
        // echo ("pourcentages : ");
        // print_r($pourcentages_salles);
         $j = 0;
         $debut_a = array();
         $debut_eb = array();
         $debut_fcd = array();
         $debut_a['RXTEL'] = 0;
         $debut_a['MAN'] = 0;
         $debut_a['MAEF'] = 0;
         $debut_a['LGTR'] = 0;
         
         $debut_eb['RXTEL'] = 0;
         $debut_eb['MAN'] = 0;
         $debut_eb['MAEF'] = 0;
         $debut_eb['LGTR'] = 0;
         
         $debut_fcd['RXTEL'] = 0;
         $debut_fcd['MAN'] = 0;
         $debut_fcd['MAEF'] = 0;
         $debut_fcd['LGTR'] = 0;
         
        for($j=0;$j<count($pourcentages_salles)-1;$j++){
            $s = 0;
             $i=0;
           foreach($lgtr as $key => $value){
           //for($i=0; $i< count($lgtr); $i++){
      
               $nombres['LGTR'][$key] =round(count($value)*$pourcentages_salles[$j], 0, PHP_ROUND_HALF_ODD);
               $s += round(count($value)*$pourcentages_salles[$j], 0, PHP_ROUND_HALF_ODD);
            }
            // $nombres['LGTR'][$lgtr[$i]] =count($lgtr[$i])- $s;
              $s = 0;
             $i=0;
            foreach($rxtl as $key => $value){
         //  for($i=0; $i< count($rxtl); $i++){
               $nombres['RXTEL'][$key] =round(count($value)*$pourcentages_salles[$j], 0, PHP_ROUND_HALF_ODD);
               $s += round(count($value)*$pourcentages_salles[$j], 0, PHP_ROUND_HALF_ODD);
            }
            // $nombres['RXTEL'][$rxtl[$i].key] =count($rxtl[$i])- $s;
             $s = 0;
             $i=0;
           foreach($man as $key => $value){
           //for($i=0; $i< count($man); $i++){
               $nombres['MAN'][$key] =round(count($value)*$pourcentages_salles[$j], 0, PHP_ROUND_HALF_ODD);
               $s += round(count($value)*$pourcentages_salles[$j], 0, PHP_ROUND_HALF_ODD);
            }
         //    $nombres['MAN'][$man[$i].key] =count($man[$i])- $s;
              $s = 0;
             $i=0;
             foreach($maef as $key => $value){
           //for($i=0; $i< count($maef); $i++){
               $nombres['MAEF'][$key] =round(count($value)*$pourcentages_salles[$j], 0, PHP_ROUND_HALF_ODD);
               $s += round(count($value)*$pourcentages_salles[$j], 0, PHP_ROUND_HALF_ODD);
            }
          //   $nombres['MAEF'][$maef[$i]] =count($maef[$i].key)- $s;
            
            
            $r =  $this->rapartition_par_salle($list_etu, $nombres, $debut_salles[$j],$debut_a,$debut_eb,$debut_fcd);
            $debut_a = $r['debut_a'];
            $debut_eb = $r['debut_eb'];
            $debut_fcd = $r['debut_fcd'];
            
         }
         
       // derniere salle 
          
             $i=0;
             $nombres_d_s = array();
          foreach($lgtr as $key => $value){
           //for($i=0; $i< count($maef); $i++){
              $s = 0;
             for($i=0;$i<count($pourcentages_salles)-1;$i++)
                  $s += round(count($value)*$pourcentages_salles[$i], 0, PHP_ROUND_HALF_ODD);
             $nombres_d_s['LGTR'][$key] = count($value)-$s;
          }
           foreach($rxtl as $key => $value){
           //for($i=0; $i< count($maef); $i++){
              $s = 0;
             for($i=0;$i<count($pourcentages_salles)-1;$i++)
                 $s += round(count($value)*$pourcentages_salles[$i], 0, PHP_ROUND_HALF_ODD);
             $nombres_d_s['RXTEL'][$key] = count($value)-$s;
          }
          foreach($man as $key => $value){
           //for($i=0; $i< count($maef); $i++){
              $s = 0;
             for($i=0;$i<count($pourcentages_salles)-1;$i++)
                 $s += round(count($value)*$pourcentages_salles[$i], 0, PHP_ROUND_HALF_ODD);
             $nombres_d_s['MAN'][$key] = count($value)-$s;
          }
          foreach($maef as $key => $value){
           //for($i=0; $i< count($maef); $i++){
              $s = 0;
             for($i=0;$i<count($pourcentages_salles)-1;$i++)
                 $s += round(count($value)*$pourcentages_salles[$i], 0, PHP_ROUND_HALF_ODD);
             $nombres_d_s['MAEF'][$key] = count($value)-$s;
          }
          //   $nombres['MAEF'][$maef[$i]] =count($maef[$i].key)- $s;
            $r = $this->rapartition_par_salle($list_etu,  $nombres_d_s, $debut_salles[count($debut_salles)-1],$debut_a,$debut_eb,$debut_fcd);



        
    }
    
    
    //attribution des numro par salle
    function attrubuer_numeros($l,$a,$eb,$fcd, $debut_a,$debut_eb,$debut_fcd) {
       $result= '';
       if(isset($l)){
           if(isset($a)){
             for($i=0;$i<count($a);$i++)  {
                 $v = $l[$i];
               $result[$a[$i]] = $v;
             $debut_a++;
             }
           }
     
           if(isset($eb)){
            for($i=0;$i<count($eb);$i++)  {
               $result[$eb[$i]]=$l[$i];
               $debut_eb++;
            }
           }
           if(isset($fcd)){
                for($i=0;$i<count($fcd);$i++)  {
                $result[$fcd[$i]]=$l[$i];
                $debut_fcd++;
            }
           }
      
       }
       $result1= array();
       $result1['liste_etu'] = $result;
      $result1['debut_a'] =  $debut_a;
      $result1['debut_eb'] =  $debut_eb;
      $result1['debut_fcd'] =  $debut_fcd;
      //print_r($result1);
       return $result1; 
       
      
    }
     function etu_salles_exam() 
    {
        
       $data['programme'] = $this->scolarite_modele->get_programme();
       $data['salles'] = $this->scolarite_modele->get_salle_exam();
       $data['niveau'] = $this->scolarite_modele->get_niveau();
       
        $this->load->view("scolarite/etu_salles_exam", $data);
    }
    function list_etu_salle_exam() 
    {
        $programme=$_POST['idProgramme'];
        $niveau=$_POST['niveau'];
        $salle=$_POST['salle'];
        
       $data['info'] = $this->scolarite_modele->list_etu_salle_exam($programme,$niveau,$salle);
       
       
        $this->load->view("scolarite/list_etu_salle_exam", $data);
    }
     function param_statistique() 
    {
      $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['semestres'] = $this->scolarite_modele->get_semestres_courants();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $data['grade'] = $this->scolarite_modele->get_grade();
        $this->load->view("scolarite/param_statistique", $data);      
    }
    public function get_events() 
    {
        // Our Stand and End Dates
        $start = $this->common->nohtml($this->input->get("start"));
        $end = $this->common->nohtml($this->input->get("end"));

        $startdt = new DateTime('now'); // setup a local datetime
        $startdt->setTimestamp($start); // Set the date based on timestamp
        $format = $startdt->format('Y-m-d H:i:s');

        $enddt = new DateTime('now'); // setup a local datetime
        $enddt->setTimestamp($end); // Set the date based on timestamp
        $format2 = $enddt->format('Y-m-d H:i:s');

        $events = $this->scolarite_modele->get_events($format, 
            $format2);

        $data_events = array();

        foreach($events->result() as $r) { 

            $data_events[] = array(
                "id" => $r->ID,
                "title" => $r->title,
                "description" => $r->description,
                "end" => $r->end,
                "start" => $r->start
            );
        }

        echo json_encode(array("events" => $data_events));
        exit();
    }

    public function add_event() 
    {
        /* Our calendar data */
        $name = $this->input->post("name");
        $desc = $this->input->post("description");
        $start_date = $this->input->post("start_date");
        $end_date = $this->input->post("end_date");

        if(!empty($start_date)) {
            $sd = DateTime::createFromFormat("Y/m/d H:i", $start_date);
            $start_date = $sd->format('Y-m-d H:i:s');
            $start_date_timestamp = $sd->getTimestamp();
        } else {
            $start_date = date("Y-m-d H:i:s", time());
            $start_date_timestamp = time();
        }

        if(!empty($end_date)) {
            $ed = DateTime::createFromFormat("Y/m/d H:i", $end_date);
            $end_date = $ed->format('Y-m-d H:i:s');
            $end_date_timestamp = $ed->getTimestamp();
        } else {
            $end_date = date("Y-m-d H:i:s", time());
            $end_date_timestamp = time();
        }

        $this->scolarite_modele->add_event(array(
            "title" => $name,
            "description" => $desc,
            "start" => $start_date,
            "end" => $end_date
            )
        );

        redirect(site_url("calendar"));
    }

    public function edit_event() 
    {
        $eventid = intval($this->input->post("eventid"));
        $event = $this->scolarite_modele->get_event($eventid);
        if($event->num_rows() == 0) {
            echo"Invalid Event";
            exit();
        }

        $event->row();

        /* Our calendar data */
        $name = $this->common->nohtml($this->input->post("name"));
        $desc = $this->common->nohtml($this->input->post("description"));
        $start_date = $this->common->nohtml($this->input->post("start_date"));
        $end_date = $this->common->nohtml($this->input->post("end_date"));
        $delete = intval($this->input->post("delete"));

        if(!$delete) {

            if(!empty($start_date)) {
                $sd = DateTime::createFromFormat("Y/m/d H:i", $start_date);
                $start_date = $sd->format('Y-m-d H:i:s');
                $start_date_timestamp = $sd->getTimestamp();
            } else {
                $start_date = date("Y-m-d H:i:s", time());
                $start_date_timestamp = time();
            }

            if(!empty($end_date)) {
                $ed = DateTime::createFromFormat("Y/m/d H:i", $end_date);
                $end_date = $ed->format('Y-m-d H:i:s');
                $end_date_timestamp = $ed->getTimestamp();
            } else {
                $end_date = date("Y-m-d H:i:s", time());
                $end_date_timestamp = time();
            }

            $this->scolarite_modele->update_event($eventid, array(
                "title" => $name,
                "description" => $desc,
                "start" => $start_date,
                "end" => $end_date,
                )
            );
            
        } else {
            $this->scolarite_modele->delete_event($eventid);
        }

        redirect(site_url("calendar"));
    }
                        
     public function emplois_du_temps() 
    {   
       // $events=null;
      //   $connection = mysqli_connect('127.0.0.1','root','','iup') or die(mysqli_error($connection));
  // set your user id settings
       
         
$datetime_string = date('c',time());    
    $result="hhhh";
      if(isset($_POST['action']) or isset($_GET['view']))
{
    if(isset($_GET['view']))
    {  header('Content-Type: application/json');
     
        
        //$start = mysqli_real_escape_string($connection,$_GET["start"]);
        //$end = mysqli_real_escape_string($connection,$_GET["end"]);
                      $start=$_GET["start"];
                      $end=$_GET["end"];
                      $result = $this->scolarite_modele->get_events1($start,$end,$s=$_GET['semestre'],$p=$_GET['idProgramme'],$chek1=$_GET["chek1"],$local=$_GET["local"],$employe1=$_GET["employe1"]);
                      $events=$result['events'];
        
        echo json_encode($events); 
        //echo json_encode($result['end']); 
       exit;
    }
    
      
      
    elseif($_POST['action'] == "add")
    {    //header('Content-Type: application/x-json; charset=utf-8');
     //  echo(json_encode($this->scolarite->get_groupe($_POST['annee'], $_POST['semestre'], $_POST['sigle'])));
        
        $p=$_POST["chekb"];
        
       if($p=="1"){
        $date_f=date('Y-m-d', strtotime($_POST["dfin"]));
      // echo date('Y-m-d',$date_f);
        // $date_d=strtotime('+7 days', strtotime($_POST["start"]) );
 

     $date1=date('Y-m-d H:i:s', strtotime($_POST["start"]) );
     $date2=date('Y-m-d H:i:s', strtotime($_POST["end"]) );
      $this->scolarite_modele->add_r_events(array(
            "start" => $date1,
            "end" => $date_f
            )
        );
      $id_repeat=$this->scolarite_modele->max_id_r_events();
    //  $this->db->insert("r_events", array('test'=>$id_repeat));
     $i=0;
        while($date1< $date_f){
         //    $date_d = strtotime('+7 days', strtotime($_GET["start"]) );
         // $date_f1 = strtotime('+7 days', strtotime($_GET["end"]) );
            $i++;
            if($i==1){
                $date1=date('Y-m-d H:i:s',strtotime('+0 days', strtotime($date1) ));
        $date2=date('Y-m-d H:i:s',strtotime('+0 days', strtotime($date2 )));
        
            }else{
        $date1=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date1) ));
        $date2=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date2 )));
            }
      //  $diff=date_diff($date1,$date2);
             
       /* mysqli_query($connection,"INSERT INTO `events` (
                    `title` ,
                    `start` ,
                    `end` ,
                     `idProgramme` ,
                      `semestre` ,
                    `id_salle`,
                    `idGroupe`,
                    `matriculeEmploye`,
                    `id_repeat`,
                    `dow`
                    
                    )
                    VALUES (
                    '".mysqli_real_escape_string($connection,$_POST["title"])."',
                    '".mysqli_real_escape_string($connection,$date1)."',
                    '".mysqli_real_escape_string($connection,$date2)."', '".$_POST["idProgramme"]."', '".$_POST["semestre"]."', '".$_POST["local"]."', '".$_POST["idGroupe"]."', '".$_POST["employe"]."', '".$id_repeat."', '".$_POST["typec"]."'
                    )");
        */
               $sql="INSERT INTO `events` (
                    `title` ,
                    `start` ,
                    `end` ,
                    `id_salle`,
                    `idGroupe`,
                    `id_repeat`,
                     `dow`
                    )
                    VALUES (
                    '".$_POST["title"] ."',
                    '".date('Y-m-d H:i:s', strtotime($date1))."',
                    '".date('Y-m-d H:i:s', strtotime($date2)  )."',  '".$_POST["local"]."', '".$_POST["idGroupe"]."', '".$id_repeat."', '".$_POST["typec"]."'
                    )";
        $this->db->query($sql);
         }
         
       // header('Content-Type: application/json');
       // echo '{"id":"'.mysqli_insert_id($connection).'"}';
        
        
         }else{
   
      //  $diff=date_diff($date1,$date2);
             
      /*  mysqli_query($connection,"INSERT INTO `events` (
                    `title` ,
                    `start` ,
                    `end` ,
                     `idProgramme` ,
                      `semestre` ,
                    `id_salle`,
                    `idGroupe`,
                     `dow`
                    )
                    VALUES (
                    '".mysqli_real_escape_string($connection,$_POST["title"] )."',
                    '".mysqli_real_escape_string($connection,date('Y-m-d H:i:s', strtotime($_POST["start"]) ))."',
                    '".mysqli_real_escape_string($connection,date('Y-m-d H:i:s', strtotime($_POST["end"]) ) )."', '".$_POST["idProgramme"]."', '".$_POST["semestre"]."', '".$_POST["local"]."', '".$_POST["idGroupe"]."', '".$_POST["typec"]."'
                    )");
       */   
             $date1=date('Y-m-d H:i:s', strtotime($_POST["start"]) );
     $date2=date('Y-m-d H:i:s', strtotime($_POST["end"]) );
      
   $sql="INSERT INTO `events` (
                    `title` ,
                    `start` ,
                    `end` ,
                    `id_salle`,
                    `idGroupe`,
                     `dow`
                    )
                    VALUES (
                    '".$_POST["title"] ."',
                    '".date('Y-m-d H:i:s', strtotime($date1))."',
                    '".date('Y-m-d H:i:s', strtotime($date2)  )."',  '".$_POST["local"]."', '".$_POST["idGroupe"]."','".$_POST["typec"]."'
                    )";
        $this->db->query($sql);
       // $this->db->insert('r_events', array('test' => $sql));
       
           header('Content-Type: application/json');
        //echo '{"id":"'.mysqli_insert_id($connection).'"}';
         
           }
        // $this->db->insert("r_events", array('test'=>$_POST['chk1']));
           if($_POST['chk1']=="1"){
            $this->scolarite_modele->update_g_employee($matriculeEmploye=$_POST["employe"],$idGroupe=$_POST["idGroupe"]);
           }
        exit;
    }
    elseif($_POST['action'] == "update")
    {
         $date1=date('Y-m-d H:i:s',strtotime('-2 hours', strtotime($_POST["start"]) ));
     $date2=date('Y-m-d H:i:s',strtotime('-2 hours', strtotime($_POST["end"]) ));
        mysqli_query($connection,"UPDATE `events` set 
            `start` = '".mysqli_real_escape_string($connection,$date1)."', 
            `end` = '".mysqli_real_escape_string($connection,$date2)."' 
            where  id = '".mysqli_real_escape_string($connection,$_POST["id"])."'");
        exit;
    }
    elseif($_POST['action'] == "delete") 
    { 
   //     $this->db->insert("r_events", array('test'=>"hh".$_POST['rd']));
     /*   if(($_POST['rd'] == "1")){
        $this->scolarite_modele->update_events($_POST['id'] ,$type=$_POST['rd']  ) ;
    }if(($_POST['rd'] == "2")){
          $this->scolarite_modele->update_events($_POST['id'] ,$type=$_POST['rd']  ) ;
           
    } if(($_POST['rd'] == "3")){
        mysqli_query($connection,"DELETE from `events` where  id = '".mysqli_real_escape_string($connection,$_POST["id"])."'");
        if (mysqli_affected_rows($connection) > 0) {
            echo "1";
       }}*/
        $r_event=$this->scolarite_modele->r_events($id=$_POST['id']);
         $date_f=date('Y-m-d', strtotime($r_event["end"]));
         $date_s=date('Y-m-d', strtotime($r_event["start"]));
         $id_repeat=$r_event["id"];
        
         // $this->scolarite_modele->update_events($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$start=$_POST['start'],$end=$_POST['end'],$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title']); 
          $this->scolarite_modele->update_g_employee($matriculeEmploye=$_POST["prof"],$idGroupe=$_POST["eventIDG"]);
          
         if($_POST['rd']=="1"){
            
          if($_POST['type']=="1"){
           
            $this->scolarite_modele->update_events($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$start=$_POST['start'],$end=$_POST['end'],$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title'],$typecm=$_POST['typecm']); 
            
            } elseif($_POST['type']=="2"){
               $this->scolarite_modele-> delete_events_ulterieurs($id_r=$id_repeat,$start=$_POST['start']);
               $date1=date('Y-m-d H:i:s',strtotime($_POST["start"]));
               $date2=date('Y-m-d H:i:s',strtotime($_POST["end"]));
            //    $this->scolarite_modele->update_unique_event($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$start=$_POST['start'],$end=$_POST['end'],$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title'],$typecm=$_POST['typecm']);
             $i=0;
            //  $this->db->insert('r_events', array('test' => $type1=$_POST['type1']));
        while($date1< $date_f){
         //    $date_d = strtotime('+7 days', strtotime($_GET["start"]) );
         // $date_f1 = strtotime('+7 days', strtotime($_GET["end"]) );
            $i++;
          
        $date1=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date1) ));
        $date2=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date2 )));
      
      //  $diff=date_diff($date1,$date2);
             $this->scolarite_modele->update_events($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$start=$date1,$end=$date2,$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title'],$typecm=$_POST['typecm']); 
       
         }
         }elseif($_POST['type']=="3"){
                $date1=date('Y-m-d H:i:s', strtotime($_POST["start"]));
                $date2=date('Y-m-d H:i:s', strtotime($_POST["end"]) );
                $date3=date('Y-m-d H:i:s',strtotime($_POST["start"]));
                $date4=date('Y-m-d H:i:s',strtotime($_POST["end"]));
      $this->scolarite_modele->delete_all_events($id_r=$id_repeat,$_POST["start"]);
       $this->scolarite_modele->update_unique_event($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$start=$_POST['start'],$end=$_POST['end'],$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title'],$typecm=$_POST['typecm']);
             
   /*   $this->scolarite_modele->add_r_events(array(
            "start" => $date1,
            "end" => $date_f
            )
        );
      $id_repeat=$this->scolarite_modele->max_id_r_events();*/
   
     $i=0;
        while($date1< $date_f){
         //    $date_d = strtotime('+7 days', strtotime($_GET["start"]) );
         // $date_f1 = strtotime('+7 days', strtotime($_GET["end"]) );
            $i++;
          
        $date1=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date1) ));
        $date2=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date2 )));
      
      //  $diff=date_diff($date1,$date2);
             $this->scolarite_modele->update_events($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$date1,$end=$date2,$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title'],$typecm=$_POST['typecm']); 
       
         } $j=0;
        while($date3> $date_s){
         //    $date_d = strtotime('+7 days', strtotime($_GET["start"]) );
         // $date_f1 = strtotime('+7 days', strtotime($_GET["end"]) );
            $j++;
          
        $date3=date('Y-m-d H:i:s',strtotime('-7 days', strtotime($date3) ));
        $date4=date('Y-m-d H:i:s',strtotime('-7 days', strtotime($date4 )));
      
      //  $diff=date_diff($date1,$date2);
            $this->scolarite_modele->update_events($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$start=$date3,$end=$date4,$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title'],$typecm=$_POST['typecm']); 
       
     }
         }    
         }elseif($_POST['rd']=="3"){
            //  $this->db->insert("r_events", array('test'=>"hhy".$_POST['typeD']));
                   if($_POST['typeD']=="1"){
           
            $this->scolarite_modele->update_events($id=$_POST['id_D'],$rd=$_POST['rd'],$type=$_POST['typeD'],$type1=$_POST['type1'],$local=$_POST['localD'],$start=$_POST['startD'],$end=$_POST['endD'],$idGroupe=$_POST['eventIDG_D'],$prof=$_POST['profD'],$title=$_POST['titleD'],$typecm=$_POST['typecmD']); 
            
            } elseif($_POST['typeD']=="2"){
              // $this->scolarite_modele-> delete_events_ulterieurs($id_r=$id_repeat,$start=$_POST['start']);
               $date1=date('Y-m-d H:i:s',strtotime($_POST["startD"]));
               $date2=date('Y-m-d H:i:s',strtotime($_POST["endD"]));
                $this->scolarite_modele->update_events($id=$_POST['id_D'],$rd=$_POST['rd'],$type=$_POST['typeD'],$type1=$_POST['type1'],$local=$_POST['localD'],$start=$_POST['startD'],$end=$_POST['endD'],$idGroupe=$_POST['eventIDG_D'],$prof=$_POST['profD'],$title=$_POST['titleD'],$typecm=$_POST['typecmD']); 
             $i=0;
             // $this->db->insert('r_events', array('test' => $date1));
        while($date1< $date_f){
         //    $date_d = strtotime('+7 days', strtotime($_GET["start"]) );
         // $date_f1 = strtotime('+7 days', strtotime($_GET["end"]) );
            $i++;
          
        $date1=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date1) ));
        $date2=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date2 )));
      
      //  $diff=date_diff($date1,$date2);
             $this->scolarite_modele->update_events($id=$_POST['id_D'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['localD'],$start=$date1,$end=$date2,$idGroupe=$_POST['eventIDG_D'],$prof=$_POST['profD'],$title=$_POST['titleD'],$typecm=$_POST['typecmD']); 
       
         }
         }elseif($_POST['typeD']=="3"){
              $date1=date('Y-m-d H:i:s', strtotime($_POST["startD"]));
     $date2=date('Y-m-d H:i:s', strtotime($_POST["endD"]) );
     $date3=date('Y-m-d H:i:s',strtotime($_POST["startD"]));
               $date4=date('Y-m-d H:i:s',strtotime($_POST["endD"]));
     // $this->scolarite_modele->delete_all_events($id_r=$id_repeat);
   /*   $this->scolarite_modele->add_r_events(array(
            "start" => $date1,
            "end" => $date_f
            )
        );
      $id_repeat=$this->scolarite_modele->max_id_r_events();*/
   
     $i=0;
        while($date1< $date_f){
         //    $date_d = strtotime('+7 days', strtotime($_GET["start"]) );
         // $date_f1 = strtotime('+7 days', strtotime($_GET["end"]) );
            $i++;
          
        $date1=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date1) ));
        $date2=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date2 )));
      
      //  $diff=date_diff($date1,$date2);
             $this->scolarite_modele->update_events($id=$_POST['id_D'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['localD'],$start=$date1,$end=$date2,$idGroupe=$_POST['eventIDG_D'],$prof=$_POST['profD'],$title=$_POST['titleD'],$typecm=$_POST['typecmD']); 
       
         } $j=0;
        while($date1> $date_s){
         //    $date_d = strtotime('+7 days', strtotime($_GET["start"]) );
         // $date_f1 = strtotime('+7 days', strtotime($_GET["end"]) );
            $j++;
          
        $date3=date('Y-m-d H:i:s',strtotime('-7 days', strtotime($date1) ));
        $date4=date('Y-m-d H:i:s',strtotime('-7 days', strtotime($date2 )));
      
      //  $diff=date_diff($date1,$date2);
            $this->scolarite_modele->update_events($id=$_POST['id_D'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['localD'],$start=$date1,$end=$date2,$idGroupe=$_POST['eventIDG_D'],$prof=$_POST['profD'],$title=$_POST['titleD'],$typecm=$_POST['typecmD']); 
       
     }
         }
             
         }elseif($_POST["rd"]=="2"){
             if($_POST['type1']=="1"){
                 
       $this->scolarite_modele-> delete_unique_event($id=$_POST['id']);
               } elseif($_POST['type1']=="2"){
     $this->scolarite_modele-> delete_events_ulterieurs($id_r=$id_repeat,$start=$_POST['start']);
            
             } if($_POST['type1']=="3"){
     $this->scolarite_modele-> delete_all_events($id_r=$id_repeat);
         }
         }
      // echo date('Y-m-d',$date_f);
        // $date_d=strtotime('+7 days', strtotime($_POST["start"]) );
 

    
        
        header('Content-Type: application/json');
        echo '{"id":"'.$this->db->mysql_insert_id().'"}';
        
        
        exit;
    }
}//print_r($listeCours);
                     
$employe = $this->scolarite_modele->get_employee();
 $info_salle = $this->scolarite_modele->recuperer_salle();
  $data_session_courante = $this->scolarite_modele->get_session_courante_calendar();
 $data_courante = $this->scolarite_modele-> get_date_courante_calendar();
//  print_r($data_courante);
$data=array( 'employe'=>$employe,'local' => $info_salle['idLocal'],
                    
                    'annee' => $data_session_courante['annee'][0], 
                    'semestre' => $data_session_courante['semestre'][0],
                    'finCours' => $data_courante['finCours'],
                     'programme'=>$this->scolarite_modele->get_programme());
        //$this->loadData();
                       // $data=array('idProgramme'=>$idProgramme,'semestre'=>$semestre,'listeCours'=>$listeCours);
        $this->load->view("scolarite/emplois_du_temps.php",$data);
    }
     public function emplois_du_temps_avec_date() 
    {   
              // $events=null;
        // $connection = mysqli_connect('127.0.0.1','root','','iup') or die(mysqli_error($connection));
         // set your user id settings
       
         
$datetime_string = date('c',time());    
    $result="hhhh";
      if(isset($_POST['action']) or isset($_GET['view']))
{
    if(isset($_GET['view']))
    {  header('Content-Type: application/json');
     
        
        //$start = mysqli_real_escape_string($connection,$_GET["start"]);
        //$end = mysqli_real_escape_string($connection,$_GET["end"]);
                      $start=$_GET["start"];
                      $end=$_GET["end"];
                      $result = $this->scolarite_modele->get_events1($start,$end,$s=$_GET['semestre'],$p=$_GET['idProgramme'],$chek1=$_GET["chek1"],$local=$_GET["local"],$employe1=$_GET["employe1"]);
                      $events=$result['events'];
        
        echo json_encode($events); 
        //echo json_encode($result['end']); 
       exit;
    }
    
      
      
    elseif($_POST['action'] == "add")
    {    //header('Content-Type: application/x-json; charset=utf-8');
     //  echo(json_encode($this->scolarite->get_groupe($_POST['annee'], $_POST['semestre'], $_POST['sigle'])));
        
        $p=$_POST["chekb"];
        
       if($p=="1"){
        $date_f=date('Y-m-d', strtotime($_POST["dfin"]));
      // echo date('Y-m-d',$date_f);
        // $date_d=strtotime('+7 days', strtotime($_POST["start"]) );
 

     $date1=date('Y-m-d H:i:s', strtotime($_POST["start"]) );
     $date2=date('Y-m-d H:i:s', strtotime($_POST["end"]) );
      $this->scolarite_modele->add_r_events(array(
            "start" => $date1,
            "end" => $date_f
            )
        );
      $id_repeat=$this->scolarite_modele->max_id_r_events();
     $i=0;
        while($date1< $date_f){
         //    $date_d = strtotime('+7 days', strtotime($_GET["start"]) );
         // $date_f1 = strtotime('+7 days', strtotime($_GET["end"]) );
            $i++;
            if($i==1){
                $date1=date('Y-m-d H:i:s',strtotime('+0 days', strtotime($date1) ));
        $date2=date('Y-m-d H:i:s',strtotime('+0 days', strtotime($date2 )));
        
            }else{
        $date1=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date1) ));
        $date2=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date2 )));
            }
      //  $diff=date_diff($date1,$date2);
             
      $sql="INSERT INTO `events` (
                    `title` ,
                    `start` ,
                    `end` ,
                    `id_salle`,
                    `idGroupe`,
                     `dow`,
                     `id_repeat`
                    )
                    VALUES (
                    '".$_POST["title"] ."',
                    '".date('Y-m-d H:i:s', strtotime($date1))."',
                    '".date('Y-m-d H:i:s', strtotime($date2)  )."',  '".$_POST["local"]."', '".$_POST["idGroupe"]."', '".$_POST["typec"]."', '".$id_repeat."'
                    )";
        $this->db->query($sql);
         }
         
        header('Content-Type: application/json');
        echo '{"id":"'.mysqli_insert_id($connection).'"}';
        
        
         }else{
   
      //  $diff=date_diff($date1,$date2);
             
       $sql="INSERT INTO `events` (
                    `title` ,
                    `start` ,
                    `end` ,
                    `id_salle`,
                    `idGroupe`,
                     `dow`
                    )
                    VALUES (
                    '".$_POST["title"] ."',
                    '".date('Y-m-d H:i:s', strtotime($_POST['start']))."',
                    '".date('Y-m-d H:i:s', strtotime($_POST['end'])  )."',  '".$_POST["local"]."', '".$_POST["idGroupe"]."', '".$_POST["typec"]."'
                    )";
        $this->db->query($sql);
       // $this->db->insert('r_events', array('test' => $sql));
       
           header('Content-Type: application/json');
        echo '{"id":"'.mysqli_insert_id($connection).'"}';
         
           }
         $this->db->insert("r_events", array('test'=>$_POST['chk1']));
           if($_POST['chk1']=="1"){
            $this->scolarite_modele->update_g_employee($matriculeEmploye=$_POST["employe"],$idGroupe=$_POST["idGroupe"]);
           }
        exit;
    }
    elseif($_POST['action'] == "update")
    {
         $date1=date('Y-m-d H:i:s',strtotime('-2 hours', strtotime($_POST["start"]) ));
     $date2=date('Y-m-d H:i:s',strtotime('-2 hours', strtotime($_POST["end"]) ));
        mysqli_query($connection,"UPDATE `events` set 
            `start` = '".mysqli_real_escape_string($connection,$date1)."', 
            `end` = '".mysqli_real_escape_string($connection,$date2)."' 
            where  id = '".mysqli_real_escape_string($connection,$_POST["id"])."'");
        exit;
    }
    elseif($_POST['action'] == "delete") 
    { 
     /*   if(($_POST['rd'] == "1")){
        $this->scolarite_modele->update_events($_POST['id'] ,$type=$_POST['rd']  ) ;
    }if(($_POST['rd'] == "2")){
          $this->scolarite_modele->update_events($_POST['id'] ,$type=$_POST['rd']  ) ;
           
    } if(($_POST['rd'] == "3")){
        mysqli_query($connection,"DELETE from `events` where  id = '".mysqli_real_escape_string($connection,$_POST["id"])."'");
        if (mysqli_affected_rows($connection) > 0) {
            echo "1";
       }}*/
        $r_event=$this->scolarite_modele->r_events($id=$_POST['id']);
         $date_f=date('Y-m-d', strtotime($r_event["end"]));
         $date_s=date('Y-m-d', strtotime($r_event["start"]));
         $id_repeat=$r_event["id"];
        
         // $this->scolarite_modele->update_events($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$start=$_POST['start'],$end=$_POST['end'],$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title']); 
          $this->scolarite_modele->update_g_employee($matriculeEmploye=$_POST["prof"],$idGroupe=$_POST["eventIDG"]);
          
         if($_POST['rd']=="1"){
            
          if($_POST['type']=="1"){
           
            $this->scolarite_modele->update_events($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$start=$_POST['start'],$end=$_POST['end'],$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title']); 
            
            } elseif($_POST['type']=="2"){
               $this->scolarite_modele-> delete_events_ulterieurs($id_r=$id_repeat,$start=$_POST['start']);
               $date1=date('Y-m-d H:i:s',strtotime($_POST["start"]));
               $date2=date('Y-m-d H:i:s',strtotime($_POST["end"]));
                $this->scolarite_modele->update_unique_event($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$start=$_POST['start'],$end=$_POST['end'],$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title']);
             $i=0;
              $this->db->insert('r_events', array('test' => $date1));
        while($date1< $date_f){
         //    $date_d = strtotime('+7 days', strtotime($_GET["start"]) );
         // $date_f1 = strtotime('+7 days', strtotime($_GET["end"]) );
            $i++;
          
        $date1=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date1) ));
        $date2=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date2 )));
      
      //  $diff=date_diff($date1,$date2);
             $this->scolarite_modele->update_events($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$start=$date1,$end=$date2,$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title']); 
       
         }
         }elseif($_POST['type']=="3"){
              $date1=date('Y-m-d H:i:s', strtotime($_POST["start"]));
     $date2=date('Y-m-d H:i:s', strtotime($_POST["end"]) );
     $date3=date('Y-m-d H:i:s',strtotime($_POST["start"]));
               $date4=date('Y-m-d H:i:s',strtotime($_POST["end"]));
      $this->scolarite_modele->delete_all_events($id_r=$id_repeat);
   /*   $this->scolarite_modele->add_r_events(array(
            "start" => $date1,
            "end" => $date_f
            )
        );
      $id_repeat=$this->scolarite_modele->max_id_r_events();*/
   
     $i=0;
        while($date1< $date_f){
         //    $date_d = strtotime('+7 days', strtotime($_GET["start"]) );
         // $date_f1 = strtotime('+7 days', strtotime($_GET["end"]) );
            $i++;
          
        $date1=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date1) ));
        $date2=date('Y-m-d H:i:s',strtotime('+7 days', strtotime($date2 )));
      
      //  $diff=date_diff($date1,$date2);
             $this->scolarite_modele->update_events($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$date1,$end=$date2,$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title']); 
       
         } $j=0;
        while($date1> $date_s){
         //    $date_d = strtotime('+7 days', strtotime($_GET["start"]) );
         // $date_f1 = strtotime('+7 days', strtotime($_GET["end"]) );
            $j++;
          
        $date3=date('Y-m-d H:i:s',strtotime('-7 days', strtotime($date1) ));
        $date4=date('Y-m-d H:i:s',strtotime('-7 days', strtotime($date2 )));
      
      //  $diff=date_diff($date1,$date2);
            $this->scolarite_modele->update_events($id=$_POST['id'],$rd=$_POST['rd'],$type=$_POST['type'],$type1=$_POST['type1'],$local=$_POST['local'],$start=$date3,$end=$date4,$idGroupe=$_POST['eventIDG'],$prof=$_POST['prof'],$title=$_POST['title']); 
       
     }
         }    
         }else{
             if($_POST['type1']=="1"){
                 
     $this->scolarite_modele-> delete_unique_event($id=$_POST['id']);
               } elseif($_POST['type1']=="2"){
     $this->scolarite_modele-> delete_events_ulterieurs($id_r=$id_repeat,$start=$_POST['start']);
            
             } if($_POST['type1']=="3"){
     $this->scolarite_modele-> delete_all_events($id_r=$id_repeat);
         }
         }
      // echo date('Y-m-d',$date_f);
        // $date_d=strtotime('+7 days', strtotime($_POST["start"]) );
 

    
        
        header('Content-Type: application/json');
        echo '{"id":"'.$this->db->mysql_insert_id().'"}';
        
        
        exit;
    }
}//print_r($listeCours);
                     
$employe = $this->scolarite_modele->get_employee();
 $info_salle = $this->scolarite_modele->recuperer_salle();
  $data_session_courante = $this->scolarite_modele->get_session_courante_calendar();
 $data_courante = $this->scolarite_modele-> get_date_courante_calendar();
//  print_r($data_courante);
$data=array( 'employe'=>$employe,'local' => $info_salle['idLocal'],
                    
                    'annee' => $data_session_courante['annee'][0], 
                    'semestre' => $data_session_courante['semestre'][0],
                    'finCours' => $data_courante['finCours'],
                     'programme'=>$this->scolarite_modele->get_programme());
        //$this->loadData();
                       // $data=array('idProgramme'=>$idProgramme,'semestre'=>$semestre,'listeCours'=>$listeCours);
        $this->load->view("scolarite/emplois_du_temps_date.php",$data);
    }
    function selection_annee_horaire_emplois() {
		$infoDateAnnee = NULL;
        $this->form_validation->set_rules('annee', 'Numéro du groupe', 'string');
        
        if ($this->form_validation->run()) {

            $infoDateAnnee = $this->input->post();
            $this->choisir_groupe_horaire($infoDateAnnee);
        } else {
             $data_sigle_cours = $this->scolarite_modele->get_cours_actif_enseignant();
            $data_session_courante = $this->scolarite_modele->get_session_courante();
            
            $data_session_courante = $this->scolarite_modele->get_session_courante();
            $listeCours = $this->scolarite_modele->recuperer_cours();
            if ($listeCours == NULL) {
                $data['typeBox'] = 'warning_box';
                $data['informations'] = 'Veuillez créer au moins un module.';
                $this->load->view('scolarite/modification_confirme', $data);
            } else { $employe = $this->scolarite_modele->get_employee();

 $info_salle = $this->scolarite_modele->recuperer_salle();
				$data = array('sigleCours' => $data_sigle_cours['sigleCours'],
                    'titre' => $data_sigle_cours['titre'],
                    'annee' => $data_session_courante['annee'][0], 
                    'semestre' => $data_session_courante['semestre'][0],
                     'programme'=>$this->scolarite_modele->get_programme(),
                     'local' => $info_salle['idLocal'],
                     'employe'=>$employe);
                $this->load->view("scolarite/choisir_annee_horaire_emplois", $data);
            }
        }
    }

   public function loadData()
	{
		$loadType=$_POST['loadType'];
		$loadId=$_POST['loadId'];
                $annee=$_POST['annee'];
                $semestre=$_POST['semestre'];
                $type=$_POST['type'];
                $matriculeEmploye=$_POST['matriculeEmploye'];

		$result=$this->scolarite_modele->getData($loadType,$loadId,$annee,$semestre,$type,$matriculeEmploye);
                
		$HTML="";
		if($loadType=="groupe"){
		if($result->num_rows() > 0){
			foreach($result->result() as $list){
				$HTML.="<option value='".$list->idGroupe."'>".$list->idGroupe."</option>";
			}
                }}elseif($loadType=="employe"){
                    if($result->num_rows() > 0){
			foreach($result->result() as $list){
				$HTML.="<option value='".$list->matriculeEmploye."'>".$list->nom." ".$list->prenom."</option>";
			}
                }
                }elseif($loadType=="infomodule_element"){
                     if($result->num_rows() > 0){
			foreach($result->result() as $list){
				$HTML.="<option value='".$list->sigle."'>".$list->sigle." ".$list->titre."</option>";
			}
                }}
		echo $HTML;
	}
         public function ElementsPlanning()
	{
		$loadType=$_POST['loadType'];
		$loadId=$_POST['loadId'];
                $annee=$_POST['annee'];
                $semestre=$_POST['semestre'];
                $type=$_POST['type'];
                $matriculeEmploye=$_POST['matriculeEmploye'];

		$result=$this->scolarite_modele->getInfoElements($loadType,$loadId,$annee,$semestre,$type,$matriculeEmploye);
             //   $this->db->insert("r_events", array('test'=>"SELECT distinct p.sigle,m.titre FROM `planetudes` p,module m,unite u where p.sigle=m.sigle and u.sigle=m.sigleunite and u.semestre=$semestre and u.idProgramme='".$loadId."' and p.sigle not in(select sigle from infomodule_element)  "));
             // print_r($result);
		$HTML="";
		//if($loadType=="infomodule_element"){
                     if($result->num_rows() > 0){
			foreach($result->result() as $list){
				$HTML.="<option value='".$list->sigle."'>".$list->sigle." ".$list->titre."</option>";
			}
                
             //   }}elseif($loadType=="anc_element"){
              /*       if($result->num_rows() > 0){
			foreach($result->result() as $list){
				$HTML.="<option value='".$list->sigle."'>".$list->sigle." ".$list->titre."</option>";
			}
                }}elseif($loadType=="tous_element"){
                     if($result->num_rows() > 0){
			foreach($result->result() as $list){
				$HTML.="<option value='".$list->sigle."'>".$list->sigle." ".$list->titre."</option>";
			}
                }}elseif($loadType=="etudiant_element"){
                      
                     if($result->num_rows() > 0){
			foreach($result->result() as $list){
				$HTML.="<option value='".$list->matriculeetudiant."'>".$list->matriculeetudiant." ".$list->nom."</option>";
			}
                }*/}
                
		echo $HTML;
	}
        function chevauchement(){
        $chevauchement = $this->scolarite_modele->chevauchement($startTimeM=$_GET['startTimeM'],$endTimeM=$_GET['endTimeM'],$prof=$_GET['prof'],$local2=$_GET['local2'],$id=$_GET['id']);
          
        echo(json_encode($chevauchement));
        }
         function chevauchement2(){
        $chevauchement = $this->scolarite_modele->chevauchement2($startTimeM=$_GET['startTimeM'],$endTimeM=$_GET['endTimeM'],$prof=$_GET['prof'],$local2=$_GET['local2'],$id=$_GET['id']);
          
        echo(json_encode($chevauchement));
        }
        function groupe(){
            $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];
        $groupe = $this->scolarite_modele->get_groupe($anneeCourante, $semestreCourant, $_GET['sigle']);
          
        echo(json_encode($groupe));
        }
         function afficher_etudiant_groupe_ab() {
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];
        //tous les etudiants inscrits dans un groupe specifie
        
        //$data['listeH']=$this->scolarite_modele->getListeHeuresEnseignement_element($_GET['groupe'],$_GET['start'],$_GET['end']);
        $data['detail'] = $this->scolarite_modele->get_detail_groupe($_GET['groupe']);
      //  echo $data['detail']['groupe'];
        //$data['taux_horaire']=$this->scolarite_modele->get_taux_horaire($data['detail'][0]['grade']);
        $data['etudiant'] = $this->scolarite_modele->get_etudiants_groupe($_GET['groupe']);
        $data['annee'] = $anneeCourante;
        $data['session'] = $semestreCourant;
        $data['idGroupe'] = $_GET['groupe'];
         /* Correction 23 février 2013 RM $data['titre'] = 'Liste des étudiants du groupe ' . $idGroupe . ', semestre ' .
                $this->get_session_nom($semestreCourant) . ' ' . $anneeCourante . '
					    . Cocher le ou les étudiants absents.'; */
		$data['titre'] = 'Liste des étudiants du groupe ' . $this->scolarite_modele->corrigerNumGroupe($_GET['groupe']) . ', semestre ' .
                $this->get_session_nom($semestreCourant) . ' ' . $anneeCourante . '
						. Cocher le ou les étudiants absents.';
         echo(json_encode($data));
    }
    function afficher_etudiant_groupe_a() {
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];
        //tous les etudiants inscrits dans un groupe specifie
        $start = date('Y-m-d',strtotime('-1 days', strtotime($_GET["start"]) ));
        $end = date('Y-m-d',strtotime('-1 days', strtotime($_GET["end"]) ));
        $data['listeH']=$this->scolarite_modele->getListeHeuresEnseignement_element($_GET['groupe'],$start,$end);
        $data['detail'] = $this->scolarite_modele->get_detail_groupe($_GET['groupe']);
      //  echo $data['detail']['groupe'];
        $data['taux_horaire']=$this->scolarite_modele->get_taux_horaire($data['detail'][0]['grade']);
        $data['etudiant'] = $this->scolarite_modele->get_etudiants_groupe($_GET['groupe']);
        $data['annee'] = $anneeCourante;
        $data['session'] = $semestreCourant;
        $data['semaine'] = "Période du  ".$start." à ".$end."";
        $data['idGroupe'] = $_GET['groupe'];
         /* Correction 23 février 2013 RM $data['titre'] = 'Liste des étudiants du groupe ' . $idGroupe . ', semestre ' .
                $this->get_session_nom($semestreCourant) . ' ' . $anneeCourante . '
					    . Cocher le ou les étudiants absents.'; */
		$data['titre'] = 'Liste des étudiants du groupe ' . $this->scolarite_modele->corrigerNumGroupe($_GET['groupe']) . ', semestre ' .
                $this->get_session_nom($semestreCourant) . ' ' . $anneeCourante . '
						. Cocher le ou les étudiants absents.';
         echo(json_encode($data));
    }
    
     function control_autorisation(){
        $control_autorisation = $this->scolarite_modele->control_autorisation($num_bac=$_GET['num_bac'],$annee=$_GET['annee']);
          
        echo(json_encode($control_autorisation));
        }
       
         function suivi_ens_etu() 
    {
       $employe = $this->scolarite_modele->get_employee();
       $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
       $data=array( 'employe'=>$employe,'annee'=>$anneeCourante);
        $this->load->view("scolarite/suivi_ens_etu.php", $data);      
    }
     function groupe_d_enseignent(){
            $sessionCourante = $this->scolarite_modele->get_session_courante();
        $anneeCourante = $sessionCourante['annee'][0];
        $semestreCourant = $sessionCourante['semestre'][0];
        //$listeH=$this->scolarite_modele->getListeHeuresEnseignement();
        $groupe = $this->scolarite_modele->get_groupe_ens($anneeCourante, $semestreCourant, $_GET['matricule']);
       // $groupes=array('listeH'=>$listeH,'groupe'=>$groupe);
        echo(json_encode($groupe));
        }
        
        
        function Correspondance(){
        $groupe = $this->scolarite_modele->Correspondance();
          echo'La correspondance est faite avec succé';
        
        }
        function form_planing_exam() 
	{
	
		$data = NULL;
     
                       //$progs = $this->scolarite_modele->get_programme();
                        $data_session_courante = $this->scolarite_modele->get_session_courante();
       
                        $data = array( 'crenau'=>$this->scolarite_modele->get_crenau(),'planningjournee'=>$this->scolarite_modele->get_planningjournee(),'planing'=>$this->scolarite_modele->get_planing(),'programme'=>$this->scolarite_modele->get_programme(),'annee' => $data_session_courante['annee'][0], 
                       'semestre' => $data_session_courante['semestre'][0]);
     
			$this->load->view("scolarite/form_planing_exam",$data);
    		
	
	}
            function planing_exam()
    {

         
            $data = array(
                
                'idPlaning' => $this->input->post('planing'),
                'idJournee' => $this->input->post('journee'),
                'idCrenau' => $this->input->post('crenau'),
                'sigle' => $this->input->post('sigle'),
                
            );

            //insert the form data into database
            $this->db->insert('planningexam', $data);

            //display success message
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">l\'ajout a été éffectué avec succé </div>');
            redirect('scolarite/form_planing_exam');
        

    }
                function ajouter_planing_exam()
    {

         
            $data = array(
                
                'idPlaning' => $_GET['planning'],
                'idJournee' => $_GET['journee'],
                'idCrenau' => $_GET['crenau'],
                'sigle' => $_GET['sigle']
                
            );
                 // print_r($data);
            ///insert the form data into database
            $this->db->insert('planningexam', $data);

            $data = array('planningjournee'=>$this->scolarite_modele->get_planningjournee($_GET['planning']),'crenau'=>$this->scolarite_modele->get_crenau(),'idProgramme'=>$_GET['idProgramme'],'idPlaning'=>$_GET['planing'],'planing'=>$this->scolarite_modele->get_planing(),'programme'=>$this->scolarite_modele->get_programme(),'annee' => $data_session_courante['annee'][0], 
                       'semestre' => $data_session_courante['semestre'][0]);
                           $data_session_courante = $this->scolarite_modele->get_session_courante();
       
     
           $data['planning'] = $this->scolarite_modele->planning_examens($_GET['idProgramme'],$_GET['planning']);
             //$this->load->view("scolarite/planning_examens",$data);   
            // redirect('scolarite/planning_examens',$data);
        //   redirect($this->uri->uri_string());
        

    }
     function planning_exam(){
                      $data_session_courante = $this->scolarite_modele->get_session_courante();
       
                        $data = array('crenau'=>$this->scolarite_modele->get_crenau(),'planing'=>$this->scolarite_modele->get_planing(),'programme'=>$this->scolarite_modele->get_programme(),'annee' => $data_session_courante['annee'][0], 
                       'semestre' => $data_session_courante['semestre'][0]);
           $this->load->view("scolarite/planning_exam",$data);      
       }
        function modifier_planning_exam(){
                      $data_session_courante = $this->scolarite_modele->get_session_courante();
       
                        $data = array( 'crenau'=>$this->scolarite_modele->get_crenau(),'planing'=>$this->scolarite_modele->get_planing(),'programme'=>$this->scolarite_modele->get_programme(),'annee' => $data_session_courante['annee'][0], 
                       'semestre' => $data_session_courante['semestre'][0]);
                        
                      /* Alioune Zeyn 18-06-2019 AZDEBUT modification planning*/
                      $data['planing']=$_GET['planing'];
                      /* AZFIN */
           $this->load->view("scolarite/modifier_planning_exam",$data);      
       }
         function correspondance_elements(){
                      $data_session_courante = $this->scolarite_modele->get_session_courante();
       
                        $data = array( 'crenau'=>$this->scolarite_modele->correspondance_elements(),'programme'=>$this->scolarite_modele->get_programme(),'annee' => $data_session_courante['annee'][0], 
                       'semestre' => $data_session_courante['semestre'][0]);
           $this->load->view("scolarite/correspondance_elements",$data);      
       }
        function pv_fraudes(){
                      $data_session_courante = $this->scolarite_modele->get_session_courante();
       
                        $data = array( 'crenau'=>$this->scolarite_modele->correspondance_elements(),'programme'=>$this->scolarite_modele->get_programme(),'annee' => $data_session_courante['annee'][0], 
                       'semestre' => $data_session_courante['semestre'][0]);
           $this->load->view("scolarite/pv_fraudes",$data);      
       }
       /*
        Alioune Zeyn 19-06-2019 AZDEBUT AZDEBUT_NEW_PLANNING
        * Ajout de planning
        */
       //etape: creation d'un planning 
        function ajouter_planning_etape1(){
            $this->load->view("scolarite/ajouter_planning_etape1");
        }
        // creation d'un planning
        function new_planning1(){
            $annee=$_POST['annee'];
            $semestre=$_POST['semestre'];
            $session=$_POST['session'];
            $data['annee']=$annee;
            $data['semestre']=$semestre;
            $data['session']=$session;
            //verification de l'existence du planning
            $planning_exists=$this->scolarite_modele->planning_exists($annee,$semestre,$session);
            if($planning_exists){
                 ($semestre==1) ?  $sem="pair":$sem="impair";
                $data['messagePlannigExiste']="Le planning de [ <b>$annee</b> , semestre <b>$sem</b> , session <b>$session</b> ] existe déja";
                $this->load->view("scolarite/ajouter_planning_etape1",$data);
            }else{
                $session_courante = $this->scolarite_modele->get_session_courante();
                //comparaision avec la session courante, pour afficher un avertissement
                if($annee!=$session_courante['annee'][0] or $semestre!=$session_courante['semestre'][0]){
                    $messageSessionCourante="Les infos saisies ne correspondent pas a la session courante";
                    $data['messageSessionCourante']=$messageSessionCourante;
                }
                $this->load->view("scolarite/ajouter_planning_etape2",$data);
            }
        }
        public function new_planning2(){
            $annee=$_POST['annee'];
            $semestre=$_POST['semestre'];
            $session=$_POST['session'];
            
            $jours=$_POST['jour']; // $_POST['jour'] est un tableau(Array) regroupant plusieurs champs
            
            //verfication si le planning existe deja ou non
            $planning_exists=$this->scolarite_modele->planning_exists($annee,$semestre,$session);
            if($planning_exists){
                 ($semestre==1) ?  $sem="pair":$sem="impair";
                $data['messagePlannigExiste']="Le planning de [ <b>$annee</b> , semestre <b>$sem</b> , session <b>$session</b> ] existe déja";
                $this->load->view("scolarite/ajouter_planning_etape1",$data);
            }else{
                //insertion du nouveau planning
                $this->scolarite_modele->ajouter_planning($annee,$semestre,$session,$jours);
            }
            $data['annee']=$annee;
            $data['semestre']=$semestre;
            $data['session']=$session;
            
            ($semestre==1) ?  $sem="pair":$sem="impair";
            $data['messagePlannigAjoute']="Le planning de [ <b>$annee</b> , semestre <b>$sem</b> , session <b>$session</b> ] est ajouté correctement";
            $this->load->view("scolarite/ajouter_planning_etape1",$data);
        }
        /*

         * fin : Ajout d'un planning AlZeyn     AZFIN AZFIN_NEW_PLANNING   */
       
        function planning_examens(){
          // $data['totaux']=$this->scolarite_modele->planning_totaux($_POST['idProgramme'],$_POST['planing']);
            $data_session_courante = $this->scolarite_modele->get_session_courante();
       
                        $data = array('planningjournee'=>$this->scolarite_modele->get_planningjournee($_GET['planing']),'crenau'=>$this->scolarite_modele->get_crenau(),'idProgramme1'=>$_GET['idProgramme'],'idPlaning'=>$_GET['planing'],'planing'=>$this->scolarite_modele->get_planing(),'programme'=>$this->scolarite_modele->get_programme(),'annee' => $data_session_courante['annee'][0], 
                       'semestre' => $data_session_courante['semestre'][0]);
                           $data_session_courante = $this->scolarite_modele->get_session_courante();
       
           $data['planning'] = $this->scolarite_modele->planning_examens($_GET['idProgramme'],$_GET['planing']);
           //print_r($data);
            $data['totaux'] = $this->scolarite_modele->planning_totaux($_GET['idProgramme'],$_GET['planing']);
            /* Alioune Zeyn 18-06-2019 AZDEBUT modification planning*/
            $data['planing'] =$_GET['planing'];
            /*AZFIN*/
           $this->load->view("scolarite/planning_examens",$data);      
       }
       
       function m_info_plannig_exam($planing){
           
            $groupe['data'] = $this->scolarite_modele->m_planning_exam($planing);
       // $groupes=array('listeH'=>$listeH,'groupe'=>$groupe);
        echo(json_encode($groupe));   
       }
       function info_correspondance(){
            $info['data'] = $this->scolarite_modele->correspondance_elements();
       // $groupes=array('listeH'=>$listeH,'groupe'=>$groupe);
        echo(json_encode($info));   
       }
         function info_pv_fraudes(){
            $info['data'] = $this->scolarite_modele->pv_fraudes();
       // $groupes=array('listeH'=>$listeH,'groupe'=>$groupe);
        echo(json_encode($info));   
       }
       function info_plannig_exam(){
            $groupe['data'] = $this->scolarite_modele->planning_exam();
       // $groupes=array('listeH'=>$listeH,'groupe'=>$groupe);
        echo(json_encode($groupe));   
       }
       function form_planing_ex() 
	{
	
		$data = NULL;
     
                       //$progs = $this->scolarite_modele->get_programme();
                        $data_session_courante = $this->scolarite_modele->get_session_courante();
       
                        $data = array('planing'=>$this->scolarite_modele->get_planing(),'programme'=>$this->scolarite_modele->get_programme(),'annee' => $data_session_courante['annee'][0], 
                       'semestre' => $data_session_courante['semestre'][0]);
     
			$this->load->view("scolarite/form_planing_ex",$data);
    		
	
	}
      function  delete_palnning_exam(){
          $this->scolarite_modele->delete_palnning_exam($_GET['id']);
      }
       
      function  ajouter_correspondance(){
          $this->scolarite_modele->ajouter_correspondance($_GET['anc_sigle'],$_GET['nouv_sigle']);
      }
       function  delete_correspondance(){
          $this->scolarite_modele->delete_correspondance($_GET['id']);
      }
      
      function  ajouter_pv_fraudes(){
          $this->scolarite_modele->ajouter_pv_fraudes($_GET['matriculeetudiant'],$_GET['sigle'],$_GET['semestre'],$_GET['annee'],$_GET['session'],$_GET['note'],$_GET['statu']);
      }
       function  delete_pv_fraudes(){
          $this->scolarite_modele->delete_pv_fraudes($_GET['id']);
      }
      
      
      function  modifier_pv_fraudes(){
          $this->scolarite_modele->modifier_pv_fraudes($_GET['id'],$_GET['note'],$_GET['session'],$_GET['statu1']);
      }
       function  date_ins_recu(){
            //       $sql="SELECT * FROM date_ins2016 order by RAND() LIMIT 1";
                     $sql = "SELECT `matriculeetudiant`,`annee_ins_l3` FROM `canava_s_2017` where `annee_ins_l3`=2014";
           $query = $this->db->query($sql);
          $date="";
        if ($query->num_rows() > 0) 
        {
            foreach ($query->result_array() as $row) 
            { 
                 $sql1="SELECT date FROM date_ins2014 order by RAND() LIMIT 1";
                 $query1 = $this->db->query($sql1);
                  foreach ($query1->result_array() as $row1) 
            {
                 $date=$row1['date'];
            }
                $q="insert into  recu_inscription(date_saisi,matriculeEtudiant)values('".$date."','".$row['matriculeetudiant']."')";
             //$q="update notespartielles  set sigle='".$row['nouv_sigle']."' where sigle='".$row['anc_sigle']."' and matriculeetudiant=15264 ";
          //echo $q;
            $this->db->query($q);
            // $q="update planetudes set sigle='".$row['nouv_sigle']."' where sigle='".$row['anc_sigle']."'and matriculeetudiant in ( SELECT matriculeEtudiant FROM `redoublant` WHERE `annee`=2016 and niveau=1) ";
            // $q="update planetudes set sigle='".$row['nouv_sigle']."' where sigle='".$row['anc_sigle']."'and matriculeetudiant=15264";
            //$this->db->query($q);
            }
           // return $data;
        }
           }
    function  choix_annee_passage(){
             $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante_calendar();
           // print_r($data);
          $this->load->view("scolarite/choix_annee_passage",$data);
      }
         function  passage_niveau(){
             $session=$_POST['session'];
             $annee=$_POST['annee'];
             $niveau=$_POST['niveau'];
            // $regle=$_POST['regle'];
             if(($session=="01")){
                 $annee=$annee-1;
             }else{
                 $annee=$annee;
             }
            // print_r($annee);
               
             
             
              $data['info'] = $this->scolarite_modele->passage_niveau($annee,$niveau);
             /* if($niveau=="2" or $niveau=="3"){
              $this->scolarite_modele->get_decision_redoublant($annee,$niveau);
              }*/
            $this->load->view("scolarite/confirmation_gen_passage");
      }
       function planning_exams(){
                      $data_session_courante = $this->scolarite_modele->get_session_courante();
                         
                        $data = array('programme'=>$this->scolarite_modele->get_programme(),'salles'=>$this->scolarite_modele->get_salle_exam(),'crenau'=>$this->scolarite_modele->get_crenau(),'planing'=>$this->scolarite_modele->get_planing(),'programme'=>$this->scolarite_modele->get_programme(),'annee' => $data_session_courante['annee'][0], 
                       'semestre' => $data_session_courante['semestre'][0]);
           $this->load->view("scolarite/planning_exams",$data);      
       }
       
       function form_fiche_emarger(){
                       
                $this->load->view("scolarite/form_fiche_emarger",$data);      
       }
       
       function fiche_emarger(){
             $planing=$_GET['planing'];
             $semestre=$_GET['semestre'];
             $annee=$_GET['annee'];
             $session=$_GET['session'];
             $sigleMN=$_GET['sigleMN'];
             $sigleLT=$_GET['sigleLT'];
             $sigleRT=$_GET['sigleRT'];
             $sigleMF=$_GET['sigleMF'];
             $salle=$_GET['salle'];
             $sigle="";
             if($sigleMN!="-1"){
                 $sigle=$sigleMN;
             } if($sigleRT!="-1"){
                 $sigle=$sigleRT;
             } if($sigleLT!="-1"){
                 $sigle=$sigleLT;
             } if($sigleMF!="-1"){
                 $sigle=$sigleMF;
             }
            $planning = $this->scolarite_modele->get_info_planning($planing,$sigle,$session);
                //$this->load->view("scolarite/fiche_emarger",$data);   
             $infoMN = $this->scolarite_modele->info_fiche_emarger($planing,$semestre,$annee,$session,$sigleMN,$salle);
             $infoLT = $this->scolarite_modele->info_fiche_emarger($planing,$semestre,$annee,$session,$sigleLT,$salle);
             $infoRT = $this->scolarite_modele->info_fiche_emarger($planing,$semestre,$annee,$session,$sigleRT,$salle);
             $infoMF = $this->scolarite_modele->info_fiche_emarger($planing,$semestre,$annee,$session,$sigleMF,$salle);
             $info=array_merge($infoMN,$infoLT,$infoRT,$infoMF);
             $infoMF_nb = $this->scolarite_modele->info_fiche_emarger_nb($planing,$semestre,$annee,$session,$sigleMF,$salle);
             $infoLT_nb = $this->scolarite_modele->info_fiche_emarger_nb($planing,$semestre,$annee,$session,$sigleLT,$salle);
             $infoMN_nb = $this->scolarite_modele->info_fiche_emarger_nb($planing,$semestre,$annee,$session,$sigleMN,$salle);
             $infoRT_nb = $this->scolarite_modele->info_fiche_emarger_nb($planing,$semestre,$annee,$session,$sigleRT,$salle);
             $data=array("planning"=>$planning,"info"=>$info,"salle"=>$salle,"session"=>$session,"infoMF_nb"=>$infoMF_nb,"infoLT_nb"=>$infoLT_nb,"infoRT_nb"=>$infoRT_nb,"infoMN_nb"=>$infoMN_nb,"infoMN"=>$infoMN,"infoLT"=>$infoLT,"infoRT"=>$infoRT,"infoMF"=>$infoMF);
             //print_r($infoMN);
              $this->load->view("scolarite/fiche_emarger",$data);  
             }
             
              function liste_et_salle_exam(){
                        $planing=$_GET['planing'];
             $semestre=$_GET['semestre'];
             $annee=$_GET['annee'];
             $session=$_GET['session'];
             $niveau=$_GET['niveau'];
             $programme=$_GET['programme'];
             $salle=$_GET['salle'];
             $data['session']=$session;
             $data['annee']=$annee;
             if($semestre==3){
             $data['semestre']="Semestres Impairs";
             }else{$data['semestre']="Semestres Paires";}
               $data['info']= $this->scolarite_modele->liste_et_salle_exam($planing,$semestre,$annee,$session,$niveau,$programme,$salle);
                $this->load->view("scolarite/liste_et_salle_exam",$data);      
       }
        function fiche_pv_fraude(){
                     
           $this->load->view("scolarite/fiche_pv_fraude",$data);      
       }
       
       function get_info_anonymat(){
                       $planing=$_GET['planing'];
             $semestre=$_GET['semestre'];
             $annee=$_GET['annee'];
             $session=$_GET['session'];
             $niveau=$_GET['niveau'];
             $programme=$_GET['programme'];
              $info=$this->scolarite_modele->get_info_anonymat($annee,$semestre,$programme,$session,$niveau);
            //   print_r($info);
              $nomFichier = '';
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $date = utf8_encode(strftime("%d-%B-%Y"));
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0);
        /// formattage du Titre de la liste.
        $objSheet->getDefaultStyle()->getFont()->setName('Courier New');
        $objSheet->setCellValue('A1', 'INSTITUT UNIVERSITAIRE PROFESSIONNEL');
        $objSheet->getStyle('A1')->getFont()->setBold(TRUE);
   
        
            $objSheet->setCellValue('A3', 'Liste des Anonymats');
            $objSheet->setCellValue('A4','Liste produite le '.$date);
        
            $objSheet->setCellValue('C6','Matricule');
            $objSheet->setCellValue('D6','Code');
            $objSheet->setCellValue('E6','Nom');
            $objSheet->setCellValue('F6','Niveau');
            $objSheet->setCellValue('G6','Programme');
            
            for($i=0;$i<count($info);$i++)
            {
                $objSheet->setCellValueByColumnAndRow(2,7+$i,$info[$i]['matriculeEtudiant']);
                $objSheet->setCellValueByColumnAndRow(3,7+$i,$info[$i]['code_ex']);
                $objSheet->setCellValueByColumnAndRow(4,7+$i,$info[$i]['nom']);
                $objSheet->setCellValueByColumnAndRow(5,7+$i,$info[$i]['niveau']);
                $objSheet->setCellValueByColumnAndRow(6,7+$i,$info[$i]['idProgramme']);
                $nomFichier = 'Anonymat_'.$info[$i]['idProgramme'].'_'.$info[$i]['niveau'];
            }
            for($i=2;$i<8;$i++)
            {
                $objSheet->getStyleByColumnAndRow($i,6)->getFont()->setBold(TRUE);
                for($j=0;$j<=count($info);$j++)
                {
                    $objSheet->getStyleByColumnAndRow( $i,$j+6)->getBorders()->applyFromArray(
                       array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array(
                                    'rgb' => '808080'
                                )
                            )
                        )
                );
                }
            }
            

        /////////////////////////////////////////////////////////////////////////////////////////////////   
        $objXLS->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
        $objXLS->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
        $objXLS->getActiveSheet()->setTitle('Liste_enseignants');
        $objXLS->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');

        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        $objWriter->save('php://output');
          // $this->load->view("scolarite/fiche_pv_fraude",$data);      
       }
        function validation_note_acce() 
	{
	
		$data = NULL;
        $this->clear_output();
		$this->form_validation->set_rules('pass', 'mot de passe', 'required');
		$matriculeEmploye= $this->session->userdata('matriculeEmploye');
                $pass=null;
                if(isset($_POST['pass']))
                $pass=$_POST['pass'];
                $idProfil = $this->connexion_modele->check_id_for_pass($matriculeEmploye,$pass);
	
    if ($idProfil!=null) // Si le mot de passe est bon
    {
                       
     //echo "oui";
     $this->liste_modif_note();
			///$this->load->view("scolarite/autorisation_etudiant",$data);
    }
    else // Sinon, on affiche un message d'erreur
    {     
            $data['typeInterface'] = 'autorisation_etudiant';
			$data['titre'] = ' L\'accès à ce sous-menu est réservé.<br> Entrez le mot de passe.';
            $this->load->view("scolarite/chef_pass_note", $data);
            
    }
    
			
			
			
		
    }
     function liste_modif_note() 
	{
	
		$data = NULL;
     
                       
     
            $this->load->view("scolarite/liste_modif_note",$data);
    		
	
	}
         function info_liste_modification_note(){
            $groupe['data'] = $this->scolarite_modele->info_liste_modification_note();
       // $groupes=array('listeH'=>$listeH,'groupe'=>$groupe);
        echo(json_encode($groupe));   
       }
       
        public function valider_modification_note()
	{
		
                $semestre=$_GET['semestre'];
                $sigle=$_GET['sigle'];
                $matriculeEtudiant=$_GET['matriculeEtudiant'];
                $note=$_GET['note'];
                $idEvaluation=$_GET['idEvaluation'];
                $annee=$_GET['annee'];
                

		$result=$this->scolarite_modele->valider_modification_note($semestre,$sigle,$matriculeEtudiant,$note,$idEvaluation,$annee);
             //   $this->db->insert("r_events", array('test'=>"SELECT distinct p.sigle,m.titre FROM `planetudes` p,module m,unite u where p.sigle=m.sigle and u.sigle=m.sigleunite and u.semestre=$semestre and u.idProgramme='".$loadId."' and p.sigle not in(select sigle from infomodule_element)  "));
        }
             function info_etudiants(){
            $info['data'] = $this->scolarite_modele->info_etudiants();
       // $groupes=array('listeH'=>$listeH,'groupe'=>$groupe);
        echo(json_encode($info));   
       }
         function infos_etudiants(){
                      $data_session_courante = $this->scolarite_modele->get_session_courante();
       
                        $data = array( 'crenau'=>$this->scolarite_modele->correspondance_elements(),'programme'=>$this->scolarite_modele->get_programme(),'annee' => $data_session_courante['annee'][0], 
                       'semestre' => $data_session_courante['semestre'][0]);
           $this->load->view("scolarite/liste_etudiants",$data);      
       }
function maj_note_etudiant(){
     $matricule=$_GET['matricule'];
     $this->scolarite_modele->maj_note_etudiant($matricule);
     $data['retour'] = 'retourProgramme';
                $data['typeBox'] = 'valid_box';
                $data['informations'] = 'Les notes de l\'étudiant ont mis à jour avec succés ';
    $this->load->view("scolarite/modification_confirme",$data);
}
 function trouver_etudiant_new_attestation_Diplome() 
    {
        //$semestre = $_POST['semestre'];
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'new_attestation';
        $id_action ='matriculeEtudiant';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Consulter l\'attestation du diplôme d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    }
    function new_attestation($matriculeEtudiant) 
    {
        //$matriculeEtudiant= $_POST['matriculeEtudiant'];
        //$semestre =  $_POST['semestre'];
        /*if($semestre == null)
            $semestre = 1;
        else if($semestre ='' || $semestre <1 || $semestre > 6)*/
                //$semestre = 1;
        $result = $this->scolarite_modele->getCreditValide($matriculeEtudiant);
        $crdit_val=$result['totalCredit'];
        $anneeD=$result['annee'];
        $semestreD=$result['semestre'];
        //si le dernier semestre est pair l'annee d'obtention est (anneeD-1)/anneeD
        //if($semestreD %2==0){
         //   $anneeD = $anneeD-1;
        //}
        if($crdit_val==180){
           $info_stage=$this->scolarite_modele-> stages_traveaux($matriculeEtudiant);
         //  print_r($info_stage);
           
        $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
        $moyenne = $this->scolarite_modele->getMoyenne($matriculeEtudiant);
         //$anneeD = $this->scolarite_modele->getAnne_obt_diplome($matriculeEtudiant);
       //pourquoi un deuxième appel à cette fonction ??? 
       // $crdit_val = $this->scolarite_modele->getCreditValide($matriculeEtudiant);
        //print_r($infoEtudiant);
        $mention=null;
        if($moyenne<12){
            $mention="Passable";
        }else if(($moyenne>=12)and ($moyenne<14)){
             $mention="Assez Bien";
        }else if(($moyenne>=14)and ($moyenne<16)){
             $mention="Bien";
        }else if(($moyenne>=16)and ($moyenne<18)){
             $mention="Trés Bien";
        }else if(($moyenne>=18)and ($moyenne<=20)){
             $mention="Ex";
        }
        //echo 'annee = '.$anneeD;
        
        $data = array('stages_travaux'=>$info_stage,'anneeD'=>$anneeD,'info' => $infoEtudiant, 'moyenne' => $moyenne, 'crditVal' =>$crdit_val,'mension' =>$mention);
        $this->load->view('scolarite/new_attestation_Diplome', $data);
        }else{
           
           $str= 'Crédits Capitalisés :	'.$crdit_val.'  est inferieur à 180';
           $this->session->set_flashdata('message', 'Crédits Capitalisés :	'.$crdit_val.'  est inferieur à 180');
           $data['informations'] = 'Crédits Capitalisés :	'.$crdit_val.'  est inferieur à 180';
             $this->load->view('scolarite/attestation_diplome_msg', $data);
           
            
        }
    }
    
    function trouver_etudiant_new_Diplome() 
    {
        //$semestre = $_POST['semestre'];
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'new_Diplome';
        $id_action ='matriculeEtudiant';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Consulter l\'attestation du diplôme d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    }
     function new_Diplome($matriculeEtudiant) 
    {
        //$matriculeEtudiant= $_POST['matriculeEtudiant'];
        //$semestre =  $_POST['semestre'];
        /*if($semestre == null)
            $semestre = 1;
        else if($semestre ='' || $semestre <1 || $semestre > 6)*/
                //$semestre = 1;
        $result = $this->scolarite_modele->getCreditValide($matriculeEtudiant);
        $crdit_val=$result['totalCredit'];
        $anneeD=$result['annee'];
        $semestreD=$result['semestre'];
        //si le dernier semestre est pair l'annee d'obtention est (anneeD-1)/anneeD
        //if($semestreD %2==0){
         //   $anneeD = $anneeD-1;
        //}
        if($crdit_val==180){
           $info_stage=$this->scolarite_modele-> stages_traveaux($matriculeEtudiant);
         //  print_r($info_stage);
           
        $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
        $moyenne = $this->scolarite_modele->getMoyenne($matriculeEtudiant);
         //$anneeD = $this->scolarite_modele->getAnne_obt_diplome($matriculeEtudiant);
       //pourquoi un deuxième appel à cette fonction ??? 
       // $crdit_val = $this->scolarite_modele->getCreditValide($matriculeEtudiant);
        //print_r($infoEtudiant);
        $mention=null;
        if($moyenne<12){
            $mention="Passable";
            $mention1="مقبول";
        }else if(($moyenne>=12)and ($moyenne<14)){
             $mention="Assez Bien";
             $mention1="مستحسن";
        }else if(($moyenne>=14)and ($moyenne<16)){
             $mention="Bien";
             $mention1="جيد";
        }else if(($moyenne>=16)and ($moyenne<18)){
             $mention="Trés Bien";
             $mention1="جيد جدا";
        }else if(($moyenne>=18)and ($moyenne<=20)){
             $mention="Ex";
             $mention1="ممتاز";
        }
        //echo 'annee = '.$anneeD;
        
        $data = array('anneeD'=>$anneeD,'info' => $infoEtudiant, 'moyenne' => $moyenne, 'crditVal' =>$crdit_val,'mention' =>$mention,'mention1' =>$mention1);
        $this->load->view('scolarite/new_Diplome', $data);
        }else{
           
           $str= 'Crédits Capitalisés :	'.$crdit_val.'  est inferieur à 180';
           $this->session->set_flashdata('message', 'Crédits Capitalisés :	'.$crdit_val.'  est inferieur à 180');
           $data['informations'] = 'Crédits Capitalisés :	'.$crdit_val.'  est inferieur à 180';
             $this->load->view('scolarite/attestation_diplome_msg', $data);
           
            
        }
    }
    
    
    /*
     * 
     *      ajoutÃ© par Alioune Zeyn le 17/08/2018
     *      
     *  */
    
    /*
     *      une fonction pour genrer un fichier Excel en utilisant le resultat d'une requete SQL - en parametre
     *      $nomFichier = nom du fichier de sortie 
     *      */
    
    function gener_listes_Excel($requete,$nomFichier){
        /* execution de la requete et obtention des donnÃ©es, veuillez vous rendre sur scolarite_modele->get_listes_etats($requete)*/
        $liste=$this->scolarite_modele->get_listes_etats($requete);
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $date = utf8_encode(strftime("%d-%B-%Y"));
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0);
        /* initialisation d'un tableau pour accueillir les nom des champs*/
        $keys=array();
        $i=0;
        foreach ($liste[0] as $key => $value) {
            $keys[$i]= "$key";
            $i++;
        }
        /* fin initialisation */
        /* remplissage des cellules -> entete du document => les noms(titres) des champs(colones)*/
        for ($i = 0; $i < count($keys); $i++) {
            $objSheet->setCellValueByColumnAndRow($i, 1, $keys[$i]);
        }
        
        $k=2;
        
        /*  Remplissage des cellules avec les donnÃ©es (contenue) */
        for($i=0; $i<count($liste); $i++)
        { 
            for($j=0;$j<count($keys);$j++)
                $objSheet->setCellValueByColumnAndRow($j,$k,$liste[$i][$keys[$j]]);
            $k++;
        }
        /* fin de remplissage, le fichier maintenant contient les informations nÃ©cessaires  */
        
        $objWriter = PHPExcel_IOFactory::createwriter($objXLS, 'Excel5');
        header('Content-Type', 'application/msexcel;charset=utf-8');
        /*   presision du nom de fichier   */
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        ob_end_clean();
        $objWriter->save('php://output');
    }
    
    
    
    /*
     *  generation de la liste des autorisatoins   -: admet la vue
     *      */
    function generer_liste_autorisations(){
        // Recuperation des donnees envoyées par le formulaire
        if(isset($_POST['annee'])){
            $annee=$_POST['annee'];
            $semester=$_POST['session'];
            /*          filiere             */
            $idProgramme=$_POST['idProgramme'];
        
            $requete="SELECT 
                    concat(autorisation_e.nom,' ',autorisation_e.prenom) 
                    AS 'NOM ET PRENOM     ',
                    autorisation_e.num_bac AS 'NUM_BAC',
                    autorisation_e.serie AS 'SERIE',
                    autorisation_e.annee AS 'ANEE_BAC',
                    concat(autorisation_e.annee,'-',autorisation_e.annee+1) AS 'ANNEE_UNIV',
                    autorisation_e.idProgramme AS 'FILIERE'
                FROM autorisation_e 
                WHERE (1=1

            ";
            if($annee!="tous"){
                $requete.=" AND autorisation_e.anneeAutorisation = $annee";
            }
            // si (idprogramme) est diffÃ©rent de tous,on ajoute la condition ,sinon on la laisse
            if($idProgramme!="tous"){
                $requete.=" AND (autorisation_e.`idProgramme`='$idProgramme')";
            }
            //le reste de la requete
            $requete.=") 
            ORDER BY autorisation_e.`idProgramme`";
            $nomFichier="Autorisations_$annee";
            $this->gener_listes_Excel($requete,$nomFichier);
        }
        $data['titre'] = 'G&#233;n&#233;rer la liste des etudiants autoris&#233;s';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view("scolarite/liste_autorisations",$data);
    }
    
    
    /*
     *  generation de la liste des inscrits     */
    function generer_liste_inscrits(){
        // Recuperation des donnees envoyÃ©es par le formulaire
        if(isset($_POST['annee'])){
            $annee=$_POST['annee'];
            $niveau=$_POST['niveau'];
            $semestre=$_POST['session'];
            /*          filiere             */
            $idProgramme=$_POST['idProgramme'];
        
            $requete="SELECT `n_i`.`matriculeetudiant` AS `Matricule etudiant`,
                CONCAT(`e`.`nom`,' ',`e`.`prenom`) AS `Nom et prenom`,
                `e`.`dateNaissance` AS `Date de naissance`,
                `e`.`NIN` AS `NIN`,
                `e`.`lieuNaissance` AS `Lieu de naissance`,
                `i`.`num_bac` AS `num_bac`,
                `i`.`infoBac` AS `infoBac`,
                `i`.`anneeObtention` AS `Annee obtention`,
                `n_i`.`niveau` AS `Niveau`,
                `n_i`.`idProgramme` AS `Filiere` 
                FROM ((`niveau_inscrits` `n_i` JOIN `etudiant` `e`) JOIN `etudesanterieures` `i`) 
                WHERE ((`i`.`idInfoBac` = `e`.`infoBac`) 
                        AND (`e`.`matriculeEtudiant` = `n_i`.`matriculeetudiant`) 
                        AND (`n_i`.`niveau` = $niveau ) 
                        ";
            if($semestre=="Tous"){
                $requete.="AND ("
                        . "((`n_i`.`annee` = '$annee' )  AND (`n_i`.`semestre` = 3))"
                        . "OR ((`n_i`.`annee` = '".($annee+1)."' )  AND (`n_i`.`semestre` = 1))"
                        . ")";
            }elseif($semestre=="Pair"){
                $requete.="AND (`n_i`.`annee` = '".($annee+1)."' ) 
                        AND (`n_i`.`semestre` = 1) ";
            }elseif($semestre=="Impair"){
                $requete.="AND (`n_i`.`annee` = '$annee' ) 
                        AND (`n_i`.`semestre` = 3) ";
            }
            if($idProgramme!="tous"){
                $requete.="AND (`n_i`.`idProgramme`='$idProgramme')";
            }
            $requete.=") ORDER BY `n_i`.`idProgramme`";
            
           // if($semester==01)$annee++;
            $nomFichier="Inscrits_".$annee."-".($annee+1)."_".$idProgramme."_L".$niveau."_Semestre_".$semestre;
            $this->gener_listes_Excel($requete,$nomFichier);
        }
        $data['titre'] = 'G&#233;n&#233;rer la liste des etudiants inscrits';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view("scolarite/liste_inscrits",$data);
    }
    
    /*   classement des niveaux    */
    function classement_niveaux(){
        // Recuperation des donnees envoyÃ©es par le formulaire
        if(isset($_POST['annee'])){
            $annee=$_POST['annee'];
            $niveau=$_POST['niveau'];
            $semestre=$_POST['session'];
            /*          filiere             */
            $idProgramme=$_POST['idProgramme'];
            
            $requete="select distinct `c1`.`matriculeEtudiant` AS `Matricule etudiant`, concat(`e`.`prenom`,' ',`e`.`nom`) AS `Nom`, `n`.`niveau` AS `niveau`, `n`.`idProgramme` AS `Filiere`, `c1`.`semestre` AS `s1`, `c1`.`credits_val` AS `cered1`, `c1`.`note` AS `Moys1`, `c2`.`semestre` AS `s2`, `c2`.`credits_val` AS `cered2`, `c2`.`note` AS `Moys2`, `c3`.`semestre` AS `s3`, `c3`.`credits_val` AS `cered3`, `c3`.`note` AS `Moys3`, `c4`.`semestre` AS `s4`, `c4`.`credits_val` AS `cered4`, `c4`.`note` AS `Moys4`, `c5`.`semestre` AS `s5`, `c5`.`credits_val` AS `cered5`, `c5`.`note` AS `Moys5`, `c6`.`semestre` AS `s6`, `c6`.`credits_val` AS `cered6`, `c6`.`note` AS `Moys6`, `t`.`credits_val` AS `totalcred`, `t`.`MoyG` AS `MoyG` from ((((((((`total_credit_valides` `t` left join `credit_valide_sem` `c1` on(((`c1`.`matriculeEtudiant` = `t`.`matriculeEtudiant`) and (`c1`.`semestre` = 1)))) left join `credit_valide_sem` `c2` on(((`t`.`matriculeEtudiant` = `c2`.`matriculeEtudiant`) and (`c1`.`semestre` = 1) and (`c2`.`semestre` = 2)))) left join `credit_valide_sem` `c3` on(((`t`.`matriculeEtudiant` = `c3`.`matriculeEtudiant`) and (`c1`.`semestre` = 1) and (`c2`.`semestre` = 2) and (`c3`.`semestre` = 3)))) left join `credit_valide_sem` `c4` on(((`t`.`matriculeEtudiant` = `c4`.`matriculeEtudiant`) and (`c1`.`semestre` = 1) and (`c2`.`semestre` = 2) and (`c3`.`semestre` = 3) and (`c4`.`semestre` = 4)))) left join `credit_valide_sem` `c5` on(((`t`.`matriculeEtudiant` = `c5`.`matriculeEtudiant`) and (`c1`.`semestre` = 1) and (`c2`.`semestre` = 2) and (`c3`.`semestre` = 3) and (`c4`.`semestre` = 4) and (`c5`.`semestre` = 5)))) left join `credit_valide_sem` `c6` on(((`t`.`matriculeEtudiant` = `c6`.`matriculeEtudiant`) and (`c1`.`semestre` = 1) and (`c2`.`semestre` = 2) and (`c3`.`semestre` = 3) and (`c4`.`semestre` = 4) and (`c5`.`semestre` = 5) and (`c6`.`semestre` = 6)))) join `etudiant` `e` on((`t`.`matriculeEtudiant` = `e`.`matriculeEtudiant`))) join `niveau_inscrits` `n` on((`n`.`matriculeetudiant` = `t`.`matriculeEtudiant`)))
                where (
                        
                          ";
            $requete.="((n.annee=$annee) and (n.semestre=3)) or ((n.annee=".($annee+1).") and (n.semestre=1)) ";
            /*
            if($semestre==1){
                $requete.=" and `n`.`annee` = ".($annee+1);
            }else{
                $requete.=" and `n`.`annee` = $annee";
            }
             */
            $requete.=")";
            // la condition dans le cas d'une filiÃ¨re prÃ©cise
            if($idProgramme!="tous"){
                $requete.=" and idProgramme='$idProgramme'";
            }
            
            if($niveau != "tous" ){
                $requete.=" and niveau = $niveau";
            }
            
            $requete.=" order by `t`.`MoyG` desc";
            
            $nomFichier="Classement_AN:$annee-".($annee+1)."_Niv:$niveau"."_FIL:$idProgramme";
            $this->gener_listes_Excel($requete,$nomFichier);
        }
        
        $data['titre'] = 'Classement pour tous les niveaux';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view("scolarite/classement_pour_tous_les_niveaux",$data);
    }
       #write by Med Bakar 
//list des sortants 22-07-2019
      function liste_sortants(){
        $data['titre'] = 'Liste des sortants';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view("scolarite/liste_sortants",$data);
          if(isset($_POST['annee'])){
            $annee=$_POST['annee']+1;//pour 2017-2018 par ex elle prend 2018 car le view va retourner 2017
            //$semestre=$_POST['semestre'];
            /*          filiere             */
            $idProgramme=$_POST['idProgramme'];
       
            $requete="SELECT
                        p1.matriculeetudiant,
                        e.nom, 
                        e.prenom,
                        de.idprogramme as filiére
                    FROM `passage_t` p1, etudiant e,dossieretudiant de
                    WHERE
                    p1.matriculeetudiant=e.matriculeetudiant
                    and p1.matriculeetudiant=de.matriculeetudiant
                    and
                    p1.niveau=4
                    and p1.annee=".$annee;
                if($idProgramme!="tous"){
                     $requete.=" and de.idProgramme='$idProgramme'";
                }     
                 $requete.=" order by de.idprogramme, p1.matriculeetudiant";

            $nomFichier="Sortant_$annee"."_$idProgramme";
            $this->gener_listes_Excel($requete,$nomFichier);
        }
       
    }  

    


      

    
    
    function liste_sortant_licence(){
        if(isset($_POST['annee'])){
            $annee=$_POST['annee'];
            /*          filiere             */
            $idProgramme=$_POST['idProgramme'];
            $requete="
                select `r`.`matriculeEtudiant` AS `matriculeetudiant`,
                    concat(`e`.`nom`,' ',`e`.`prenom`) AS `nom`,
                    `e`.`dateNaissance` AS `Date de naissance`,
                    `e`.`dateInscription` AS `Date Inscription`,
                    `e`.`sexe` AS `Sexe`,
                    `e`.`NIN` AS `NIN`,
                    `e`.`lieuNaissance` AS `Lieu de naissance`,
                    `i`.`num_bac` AS `num_bac`,
                    `i`.`infoBac` AS `infoBac`,
                    `i`.`anneeObtention` AS `Annee d'Obtention du Bac`,
                    `r`.`idProgramme` AS `Filiere`,
                    `e`.`nationalite` AS `Nationalite`,
                    r.idProgramme as 'filier',
                    concat(`e`.`telephone`,' ',`e`.`email`) AS `contact`,
                    `r`.`annee`+1 AS `annee_obtention_licence`,
                    min(`p`.`annee`) AS `annee_ins_l3` 
                from (((`iup`.`sortant_licence` `r` join `iup`.`etudiant` `e`) join `iup`.`etudesanterieures` `i`) join `iup`.`planetudes` `p`) 
                where (
                    (`i`.`idInfoBac` = `e`.`infoBac`) 
                    and (`e`.`matriculeEtudiant` = `r`.`matriculeEtudiant`) 
                    and (`e`.`matriculeEtudiant` = `p`.`matriculeEtudiant`) 
                    and ((`p`.`sigle` like '___5%') or (`p`.`sigle` like '___6%')) 
                ";
            if($idProgramme!="tous"){
                $requete.=" and idProgramme='$idProgramme'";
            }
            if($annee != "tous" ){
                $requete.=" and r.annee = $annee";
            }
            $requete.="
                )
                group by `r`.`matriculeEtudiant` 
                order by `r`.`matriculeEtudiant`
                    ";

            $nomFichier="Sortants_".($annee+1)."_$niveau"."_$idProgramme";
            $this->gener_listes_Excel($requete,$nomFichier);
        }
        $data['titre'] = 'Liste des sortants de la licence';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view("scolarite/Liste_sortants_licence",$data);
    }

    function liste_redoublants(){
        if(isset($_POST['annee'])){
            $annee=$_POST['annee'];
            $niveau=$_POST['niveau'];
            /*          filiere             */
            $idProgramme=$_POST['idProgramme'];
            $requete="select r.matriculeetudiant AS 'matriculeetudiant',
                concat(e.nom,' ',e.prenom) AS 'Nom et prenom',
                e.dateNaissance AS 'date de Naissance',
                e.NIN AS 'NIN',
                e.lieuNaissance AS 'Lieu de Naissance',
                i.num_bac AS 'Num_bac',
                i.infoBac AS 'InfoBac',
                i.anneeObtention AS 'Annee Obtention du Bac',
                r.idProgramme AS 'Filiere' ,
                r.niveau AS 'Niveau',
                r.annee AS 'Annee'
                from ((redoublant r join etudiant e) join etudesanterieures i)
                where (
                        (i.idInfoBac = e.infoBac) 
                        and (e.matriculeEtudiant = r.matriculeetudiant) 
                        
                       ";
            if($idProgramme!="tous"){
                $requete.=" and idProgramme='$idProgramme'";
            }
            if($annee != "tous" ){
                $requete.=" and r.annee = $annee";
            }
            if($niveau != "tous" ){
                $requete.=" and r.niveau = $niveau";
            }
            $requete.="
                 )
                 order by r.idProgramme";

            $nomFichier="Redoublants_$annee"."_$niveau"."_$idProgramme";
            $this->gener_listes_Excel($requete,$nomFichier);
        }
        $data['titre'] = 'Liste des redoublants';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view("scolarite/liste_redoublants",$data);
    }
    
    function liste_admis_v1(){
        if(isset($_POST['annee'])){
            $annee=$_POST['annee'];
            $semestre=$_POST['semestre'];
            /*          filiere             */
            $idProgramme=$_POST['idProgramme'];
            $requete="SELECT e.matriculeEtudiant 	AS  'Matricule etudiant',
                de.idprogramme				AS 	'Filiere',
                sd_t.decision				AS 	'Decision',
                sd_t.semestre				AS 	'Semestre',
                sd_t.annee 					AS 	'Annee'
                FROM semestre_decision_t_bis sd_t JOIN etudiant e JOIN dossieretudiant de 

                where e.matriculeEtudiant = sd_t.matriculeEtudiant

                and   e.matriculeEtudiant = de.matriculeEtudiant

                and  decision like 'adm%'
                ";
            if($idProgramme!="tous"){
                $requete.=" and de.idProgramme='$idProgramme'";
            }
            if($annee != "tous" ){
                $requete.=" and sd_t.annee = $annee";
            }
            if($semestre != "tous" ){
                $requete.=" and sd_t.semestre = $semestre";
            }
            $requete.="
                 group by e.matriculeetudiant,sd_t.semestre
                 order by de.idProgramme
                ";

            $nomFichier="Admis_$annee"."_S$semestre"."_$idProgramme";
            $this->gener_listes_Excel($requete,$nomFichier);
        }
        $data['titre'] = 'Liste des admits';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view("scolarite/liste_admis",$data);
    }
    function liste_admis(){
        if(isset($_POST['annee'])){
            $annee=$_POST['annee'];
            $semestre=$_POST['semestre'];
            /*          filiere             */
            $idProgramme=$_POST['idProgramme'];
            $requete="SELECT distinct
                    e.matriculeEtudiant 	AS  'Matricule etudiant',
                    de.idprogramme				AS 	'Filiere',
                    sd_t.decision				AS 	'Decision',
                    sd_t.semestre				AS 	'Semestre',
                    CONCAT('$annee','-','".($annee+1)."') 			AS 	'Annee'
                FROM semestre_decision_t_bis sd_t 
                    JOIN etudiant e 
                    JOIN dossieretudiant de 
                    JOIN planetudes pe
                    
                where e.matriculeEtudiant = sd_t.matriculeEtudiant
                    and   e.matriculeEtudiant = de.matriculeEtudiant
                    and   e.matriculeEtudiant = pe.matriculeEtudiant
                    and   ((pe.annee='$annee' and pe.semestre=3) or (pe.annee=".($annee+1)." and pe.semestre=1))
                  and  (decision like 'adm%' or decision like 'comp%')
                    
                    
                    
                ";
            if($idProgramme!="tous"){
                $requete.=" and de.idProgramme='$idProgramme'";
            }
            if($annee != "tous" ){
                if($semestre%2==1)
                    $requete.=" and sd_t.annee = $annee";
                else
                    $requete.=" and sd_t.annee = ".($annee+1);
            }
            if($semestre != "tous" ){
                $requete.=" and sd_t.semestre = $semestre";
            }
            $requete.="
                 group by e.matriculeetudiant,sd_t.semestre
                 order by de.idProgramme
                ";

            $nomFichier="Admis_$annee"."_S$semestre"."_$idProgramme";
            $this->gener_listes_Excel($requete,$nomFichier);
        }
        $data['titre'] = 'Liste des admits';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view("scolarite/liste_admis",$data);
    }
    
    /*
        les orientations , Le nombre d'etudiants orientes Inscrits et non inscrits
     *  Par annee universitaire, filiere,
     * ------------------------------------------------------------------------------------------
     *  Filiere  | Orientes | Crees | Non crees |  Inscrits | Non inscrits | Annee universitaire
     *      */
    
    function generer_etat_orientations(){
        if(isset($_POST['annee'])){
            $annee=$_POST['annee'];
            /*          filiere             */
            $idProgramme=$_POST['idProgramme'];
            $requete="
                SELECT DISTINCT
                    ae.idProgramme AS 'Filiere',
                    (SELECT count(a.num_bac) FROM autorisation_e a
                        WHERE a.idProgramme = de.idprogramme
                            AND a.anneeautorisation= ae.anneeautorisation
                    )
                    AS Orientes ,
                    (SELECT count(a.num_bac) 
                        FROM autorisation_e a 
                        WHERE (a.num_bac,a.annee)  in (select ea1.num_bac,ea1.anneeobtention from etudesanterieures ea1)
                            AND a.idProgramme = de.idprogramme
                            AND a.anneeautorisation= ae.anneeautorisation
                    ) AS Crees,
                    (
                        SELECT Orientes-Crees

                    ) AS Non_Crees,
                    (
                        SELECT count(a.num_bac)
                        FROM autorisation_e a,etudesanterieures ea1,etudiant e
                        WHERE 
                        a.num_bac =ea1.num_bac 
                        AND a.annee=ea1.anneeobtention
                        AND e.infobac = ea1.idinfobac
                        AND e.matriculeetudiant in (SELECT matriculeetudiant FROM planetudes)  AND a.idProgramme = de.idprogramme AND a.anneeautorisation= ae.anneeautorisation
                    ) AS Inscrits,
                    (
                        select Orientes-Inscrits
                    ) AS Non_Inscrits ,
                    concat(ae.anneeautorisation,'-',ae.anneeautorisation+1) AS 'Anneee'
                FROM etudiant e join etudesanterieures ea join autorisation_e ae 
                ON(e.infobac = ea.idinfobac AND ea.num_bac=ae.num_bac), dossieretudiant de
                WHERE de.matriculeetudiant=e.matriculeetudiant AND ae.idProgramme = de.idprogramme";
            if($idProgramme!="tous"){
                $requete.=" and ae.idProgramme='$idProgramme'";
            }
            if($annee != "tous" ){
                $requete.=" and ae.annee = $annee";
            }
            
            $requete.="  ORDER BY anneeautorisation,de.idprogramme";

            $nomFichier="orientations_Annee:$annee"."_S:$semestre"."_$idProgramme";
            $this->gener_listes_Excel($requete,$nomFichier);
        }
        $data['titre'] = 'Les orientations';
        $data['annee'] = $this->scolarite_modele->recuperer_annee();
        $data['courante'] = $this->scolarite_modele->get_session_courante();
        $data['programme'] = $this->scolarite_modele->get_programme();
        $this->load->view("scolarite/etat_orientations",$data);
    }
    
    // fin ajout 
    
    /*

        Ajout d'une fenetre pour imprimer la carte d'etudiant
     *      */
    function trouver_carte(){
        
        $controlleur = "scolarite";
        $titre = "";

        $annes=$this->scolarite_modele->getAnnee();
        $progs = $this->scolarite_modele->get_programme();
        $data = array('titre' => $titre,'controlleur' => $controlleur,'annee'=>$annes[0],'programme'=>$progs);
//        print_r($data);
        $this->load->view("scolarite/formulaire_carte",$data);
    }
    function trouver_etudiant_pour_la_carte(){//a modifier par MedBakar le 11-06-2020
        if(isset($_POST['annee'])){
            $annee=$_POST['annee'];
            $matricule=$_POST['matricule'];
            
            $isExiste=true;
            $verification=$this->scolarite_modele->verifier_existance_etudiant($matricule);
            //si le matricule n'existe pas
            if($verification[0]['nfois']<1){
                $isExiste=false;
                $data['titre']="Le matricule $matricule n'existe pas, veuillez vérifier le matricule.";
                $data['isExiste']=$isExiste;
                $this->load->view("scolarite/carte",$data);
            }else{
                $idProgramme=array();
                
                $data['isExiste']=true;
                $infoCarte=$this->scolarite_modele->get_all_infoCarte($idProgramme,$annee,$matricule,$matricule);//lieu du modif
                $data = array('infoCarte'=>$infoCarte);
                $session=$this->scolarite_modele->get_session_courante();
                
                         
                $annee=$session['annee'][0];
                $semestre=$session['semestre'][0];
                
                $data['annDeb']=$annee;
                if($semestre==1) $data['annDeb']=$annee-1;
//                print_r($data);
                $param_generaux= $this->scolarite_modele->Recup_Parametre_Generaux();//add by MedBakar 13-06-2020
//         print_r($infoCarte);
                $data['param_genereaux']=$param_generaux;
                $this->load->view("scolarite/carte",$data);
                
            }
             
            
        }else{
            $data['isExiste']=false;
            $data['titre']="Manque de donnees";
            $this->load->view("scolarite/carte",$data);
        }
        
    }
    // fin de l'ajout
    
    
    // test traitement des view | with php
    function test_script_view(){
        $params_cour= $this->scolarite_modele->getParametresGenerauxCourants();
       // print_r($params_cour);
        $data['a']="bonjour";
        $data['result']=$this->scolarite_modele->test_script_view_traitement();
        $this->load->view("scolarite/test_script_view",$data);
    }
    //cette fonction est utilisee par le header (inclu dans toutes les pages) appelee par ajax pour recuperer les paramGen pour les afficher dans le header
    function getParametresGenerauxCourants(){
        $params_cour= $this->scolarite_modele->getParametresGenerauxCourants();
        
        echo json_encode($params_cour);            
        
    }
        /*
     * AZ I517F4 */
    /*
     * AZ Alioune 08-05-2019 
     * Debut generation des PVs
    */
    function generer_pv_new() {
        $data['programme'] = $this->scolarite_modele->get_programme();
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $annee=$sessionCourante['annee'][0];
        $data['annee'][0] = $annee;
         if($sessionCourante['semestre'][0]==1)//add 31-07-2020 by MedBakar
        $data['annee'][0] = $annee-1;
         
       // print_r($annee);
        $this->load->view("scolarite/generer_pv_new",$data);
         //$this->load->view("scolarite/generer_pv",$data);
    }
    
    public function afficher_pv_new()
    {
        /* pour tester le temps d'execution
        $milliseconds = round(microtime(true) * 1000);
        $milliseconds5 = round(microtime(true) * 1000);
        echo "<hr><h4>total time: ".($milliseconds5-$milliseconds)/1000;
        echo " <span class='label label-success'>sec</span></h4><hr>";
        */
        
         //Recuperation des parametres genereaux ---Par MedBakar
         $param_generaux= $this->scolarite_modele->Recup_Parametre_Generaux();
//           print_r($_POST);
        $data=$this->bulletin_modele->afficher_pv_new($_POST);
// $data;
         $semestre=$_POST['semestre'];
        $annee=$_POST['annee'];
        $departement=$_POST['idProgramme'];
        if(!empty($_POST['details']))
        $details=true;
        else
            $details=false;
        //recuperer la maquette
       
       $data['param_generaux']=$param_generaux;   //add by MedBakar 08-03-2020

        /*On savoir si le format doit etre Excel ou PDF*/
        $format=$_POST['format'];
        if($format=='PDF'){
          $this->load->view('scolarite/consulter_info_pv', $data);
        }else{
         
            $this->PV_excel($departement,$semestre,$annee,$data,$details);
        }
    }
    /*generer PV_excel*/
    function PV_excel($departement,$semestre,$annee,$data,$details){
//        $details=false;/*a modifier*/      
        
        $semestreP=3;
        $annee_univ=$annee.'-'.($annee+1);
        if($semestre%2==0){
            $semestreP=1;
//        $annee_univ=($annee-1).'-'.($annee);
        $annee++;
        }
        $noteEliminationMatiere = $this->bulletin_modele->get_note_elimination_matiere_courante(4);
//            print_r($data['etudiants'][19205]['modules']);
        $maquette=$this->get_maquette($departement,$semestre,$semestreP,$annee);
         $maquette_credit_coef=$this->get_maquette_credit_coef($departement,$semestre,$semestreP,$annee);
//        print_r($maquette);
//        echo"<br>";
//        print_r($maquette_credit_coef);
//        return;
//        var_dump($data['etudiants'][19205]);
//print_r($data['etudiants'][19205]);
        //-------------------------
        $liste=array();
        $liste_corps=array();
        $i=0;
        if($details){
                 $nbColumn_matiere=5;
             }else
                 $nbColumn_matiere=3;
        foreach($data['etudiants'] as $matriculeEtudiant=>$value){
//            $liste[$matriculeEtudiant]['anonymat']=$data['etudiants'][$matriculeEtudiant]['anonymat'];
//            $liste[$matriculeEtudiant]['nom_prenom']=$data['etudiants'][$matriculeEtudiant]['info']['nom'].' '.$data['etudiants'][$matriculeEtudiant]['info']['prenom'];
        
            //---------------------
            $liste_corps[$matriculeEtudiant][]=$matriculeEtudiant;
            if($details)
            $liste_corps[$matriculeEtudiant][]=$data['etudiants'][$matriculeEtudiant]['anonymat'];
             $liste_corps[$matriculeEtudiant][]=$data['etudiants'][$matriculeEtudiant]['info']['prenom'].' '.$data['etudiants'][$matriculeEtudiant]['info']['nom'];
           
             
             
             foreach($maquette as $sigleUnite=>$value){
                 if(!empty($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite])){
//                        $liste[$matriculeEtudiant][$sigleUnite]['titre_unite']=$data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['titre'];
                        
            foreach($value as $sigle=>$val){
            if($sigle!='exist'){
                 if(!empty($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle])){
            if($details){
//                      $liste_corps[$i]=$data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['titre'];
//                      $liste_corps[$matriculeEtudiant][]=$data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['coefficient'];
//                      $liste_corps[$matriculeEtudiant][]=$data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['ects'];
                      $liste_corps[$matriculeEtudiant][]=number_format($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['ncc'],2,',','');
                      $liste_corps[$matriculeEtudiant][]=number_format($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['nsn'],2,',','');
                      $liste_corps[$matriculeEtudiant][]=number_format($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['nsr'],2,',','');
                      $liste_corps[$matriculeEtudiant][]=number_format($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['nfe'],2,',','');
                      $liste_corps[$matriculeEtudiant][]=$data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['capit'];
//                     $data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['titre'];
            }else{
                 $liste_corps[$matriculeEtudiant][]=number_format($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['nfe'],2,',','');
                 $capit='V';
                 if(strcmp($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['capit'],'CI')==0){
                     $capit='VCI';
                 }else
                     if(strcmp($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['capit'],'CE')==0){
                     $capit='VCE';
                 }else
                     if(strcmp($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['capit'],'NC')==0){
                         if($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['nfe']>=$noteEliminationMatiere)
                         $capit='NV';
                        else
                            $capit='E';
                 }
                 
                 $liste_corps[$matriculeEtudiant][]=$capit;
                      if(strcmp($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['capit'],'NC')!=0)
                      $liste_corps[$matriculeEtudiant][]=$data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['elements'][$sigle]['ects'];
                      else
                      $liste_corps[$matriculeEtudiant][]=0;
                      
            }
                     
                     
                 }
                 else{//dans le cas ou il est inscrits dans l'unite ms il n'est pas inscrit dans un element
                     for($cpt=0;$cpt<$nbColumn_matiere;$cpt++)
                     $liste_corps[$matriculeEtudiant][]='';
                 }
                 
            }
            }
            $liste_corps[$matriculeEtudiant][]=number_format($data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['nm'],2,',','');
            if($details)           
            $liste_corps[$matriculeEtudiant][]=$data['etudiants'][$matriculeEtudiant]['modules'][$sigleUnite]['decision'];
           
            }else{//dans le cas ou il n'est pas inscrit dans une unite
//                print_r($sigleUnite);
                for($nbreElement=0;$nbreElement<count($value)-1;$nbreElement++)//boucler sur le nbe d'element de chaque unite 
                for($cpt=0;$cpt<$nbColumn_matiere;$cpt++)
                     $liste_corps[$matriculeEtudiant][]='';
                //puis on ajoute deux casiers vide pour la moyenne et decision d'unite
                $liste_corps[$matriculeEtudiant][]='';
                $liste_corps[$matriculeEtudiant][]='';
            }
        }
        /* semestre*/
         $liste_corps[$matriculeEtudiant][]=number_format($data['etudiants'][$matriculeEtudiant]['semestre']['note'],2,',','');
         $liste_corps[$matriculeEtudiant][]=$data['etudiants'][$matriculeEtudiant]['semestre']['ects'];
         if($details)
        $liste_corps[$matriculeEtudiant][]=$data['etudiants'][$matriculeEtudiant]['semestre']['decision'];
        }
        //fromer entete
        $entete=array();
        $cpt=3;
        foreach($maquette as $sigleUnite=>$value){
            foreach($value as $sigle=>$val){
            if($sigle!='exist'){
            $entete[$cpt][$sigle]=$sigle;
            $cpt++;}
            }
        }

        
        /*EXCEL*/
//      $liste=$this->scolarite_modele->get_listes_etats($requete);
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
        $date = utf8_encode(strftime("%d-%B-%Y"));
        $objXLS = new PHPExcel();
        $objSheet = $objXLS->setActiveSheetIndex(0)             ;
//        $objSheet->getDefaultColumnDimension()->setWidth('12,14');
//        $objSheet->getDefaultRowDimension()->setRowHeight('57.75');
        
        /*Nom d'Institut*/
         $objSheet->setCellValueByColumnAndRow(0,1,$data['param_generaux'][0]['nom_ins_parent_fr']);
         $objSheet->setCellValueByColumnAndRow(0,2,$data['param_generaux'][0]['nom']);
         
         /*Titre and date*/
         $objSheet->setCellValueByColumnAndRow(38,2,'Date');
         $objSheet->setCellValueByColumnAndRow(39,2,date('d/m/Y'));
        
        
        /*Entete*/
        $objSheet->setCellValueByColumnAndRow(2,5,'Procès Verbal du Semestre '.$semestre.' Filière '.$departement.' '.$annee_univ)->mergeCells('C5:K5');
        $objSheet->getStyle('C5')->getFont()->setBold(true)->setSize(20);
        $cpt=0;
        /* initialisation d'un tableau pour accueillir les nom des champs*/
       $objSheet->setCellValueByColumnAndRow($cpt++,10,'Mat');
     if($details){
       $objSheet->setCellValueByColumnAndRow($cpt++,10,'Anonymat');
       $objSheet->setCellValueByColumnAndRow(2,8,'Crédit');
         $objSheet->setCellValueByColumnAndRow(2,9,'Coef');
       $objSheet->getColumnDimension('C')->setWidth('54.71');
     }$objSheet->setCellValueByColumnAndRow($cpt++,10,'Prénom & Nom');
      
     $objSheet->getColumnDimension('A')->setWidth('9.71');
     if(!$details){
              $objSheet->getColumnDimension('B')->setWidth('54.71');
       }
          
       $ligne=6;
     if(!$details){  
        $objSheet->setCellValueByColumnAndRow(1,8,'Crédit');
         $objSheet->setCellValueByColumnAndRow(1,9,'Coef');
         $ligne=7;
    }
    if($details){
       $lettreDebut='d';
    $lettreFin='d';}
    else{
        $lettreDebut='c';
    $lettreFin='c';
    }
    
       foreach($maquette as $sigleUnite=>$value){
           $j=0;
           if($details)
               $j=-1;
           for($r=$j;$r<(count($value)*$nbColumn_matiere)-$nbColumn_matiere;$r++)
           $lettreFin++;
           
           $objSheet->setCellValueByColumnAndRow($cpt,$ligne,$sigleUnite.': '.$maquette_credit_coef[$sigleUnite]['titre']['titre_unite'])->mergeCells($lettreDebut.$ligne.':'.$lettreFin.$ligne);//->mergeCells($lettreDebut.'6:'.$lettreFin.'6'); 
           $lettreDebut=++$lettreFin;
           foreach($value as $sigle=>$val){
            if($sigle!='exist'){
                $objSheet->setCellValueByColumnAndRow($cpt,8,$maquette_credit_coef[$sigleUnite][$sigle]['credit']);
                      $objSheet->setCellValueByColumnAndRow($cpt,9,$maquette_credit_coef[$sigleUnite][$sigle]['coef']);
                if($details){
            $objSheet->setCellValueByColumnAndRow($cpt,7,$sigle.': '.$maquette_credit_coef[$sigleUnite]['titre'][$sigle]);
            
//            $objSheet->setCellValueByColumnAndRow($cpt++,8,'Credit');
//            $objSheet->setCellValueByColumnAndRow($cpt++,8,'Coef');
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'NCC '.$sigle);
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'NSN '.$sigle);
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'NSR '.$sigle);
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'Moy '.$sigle);
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'Capit '.$sigle);
                }else
                {
                    
                    $objSheet->setCellValueByColumnAndRow($cpt++,10,'Moy '.$sigle);
                    $objSheet->setCellValueByColumnAndRow($cpt++,10,'Capit '.$sigle);
                    $objSheet->setCellValueByColumnAndRow($cpt++,10,'Crédit '.$sigle);
                    
                    
                }
            }
            }
            if(!$details)
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'Moy '.$sigleUnite);
            else{
                $objSheet->setCellValueByColumnAndRow($cpt++,10,'Moy '.$sigleUnite);
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'Décision '.$sigleUnite);
            
            }
            
        }
        if($details){
//            $objSheet->setCellValueByColumnAndRow($cpt,7,'Semestre');
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'Moy Général');
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'Crédit total');
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'Décision');
        }else{
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'Moy Général');
            $objSheet->setCellValueByColumnAndRow($cpt++,10,'Crédit total');
        }
            
            /*Le corps*/
            $row=11;
            foreach($liste_corps as $matricule=>$value){
            $column=0;
            $col='A';
                for($i=0;$i<count($value);$i++){
                     $objSheet->setCellValueByColumnAndRow($column,$row,$value[$i]);
                     //bordure
//                     $objSheet->getStyle($col.$row)->applyFromArray(array('borders'=>array('allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN))));
                     //coloration des decisions
                  if(!$details){   
                     if(strcmp($value[$i],'VCI')==0 || $value[$i]==30)
                             $objSheet->getStyle($col.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'92D050'))));
                     else
                         if(strcmp($value[$i],'VCE')==0)
                             $objSheet->getStyle($col.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FFFF00'))));
                     else
                         if(strcmp($value[$i],'NV')==0)
                             $objSheet->getStyle($col.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FF0000'))));
                     else
                         if(strcmp($value[$i],'E')==0)
                             $objSheet->getStyle($col.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'CC3300'))));
                  }else{
                      if(strcmp($value[$i],'CI')==0 || $value[$i]==30)
                             $objSheet->getStyle($col.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'92D050'))));
                     else
                         if(strcmp($value[$i],'CE')==0)
                             $objSheet->getStyle($col.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FFFF00'))));
                     else
                         if(strcmp($value[$i],'NC')==0)
                             $objSheet->getStyle($col.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'CC3300'))));
                     else
                         if(strcmp($value[$i],'V')==0)
                             $objSheet->getStyle($col.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'92D050'))));
                    else
                         if(strcmp($value[$i],'VC')==0)
                             $objSheet->getStyle($col.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FFE699'))));
                     else
                         if(strcmp($value[$i],'NV')==0)
                             $objSheet->getStyle($col.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FF0000'))));
                  
                  }
                     
//                     if($col=='C')
//                     $objSheet->getStyle($col.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FCD5B4'))));
                         
                          /*Dimension*/
                      if($col!='A' && $col!='B' && $row==11){
                         $objSheet->getColumnDimension($col)->setWidth('12.14');
                      
                      }
                      /* font-size && bold*/
//                      if($col=='A'||$col=='B')
//                            $objSheet->getStyle($col.$row)->getFont()->setSize(16);
//                      else
//                          $objSheet->getStyle($col.$row)->getFont()->setBold(true)->setSize(16);
                     $col++;
                    $column++;
                }
                $objSheet->getRowDimension($row)->setRowHeight('57.75');
                $row++;
            }
           
            $row+=4;
            //liste des acronymes
            if(!$details){
                            $objSheet->setCellValueByColumnAndRow(10,$row,'V'); 
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'EM Validé');
                            
                            $objSheet->getStyle('K'.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'92D050'))));
                            $objSheet->setCellValueByColumnAndRow(10,$row,'VCI');
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'EM Validé par conpensation interne');
                            
                            $objSheet->getStyle('K'.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FFFF00'))));
                            $objSheet->setCellValueByColumnAndRow(10,$row,'VCE');
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'EM Validé par conpensation globale');
                            
                            $objSheet->getStyle('K'.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FF0000'))));
                            $objSheet->setCellValueByColumnAndRow(10,$row,'NV');
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'EM Non validé');
                            
                            $objSheet->getStyle('K'.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'CC3300'))));
                            $objSheet->setCellValueByColumnAndRow(10,$row,'E');
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'Moyenne Eliminatoire');
            }else{
                            $objSheet->setCellValueByColumnAndRow(10,$row,'C'); 
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'EM Capitalisé');
                            
                            $objSheet->getStyle('K'.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'92D050'))));
                            $objSheet->setCellValueByColumnAndRow(10,$row,'CI');
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'EM Capitalisé par conpensation interne');
                            
                            $objSheet->getStyle('K'.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FFFF00'))));
                            $objSheet->setCellValueByColumnAndRow(10,$row,'CE');
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'EM Capitalisé par conpensation globale');
                            
                            $objSheet->getStyle('K'.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'CC3300'))));
                            $objSheet->setCellValueByColumnAndRow(10,$row,'NC');
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'EM Non Capitalisé');
                            
                            $objSheet->getStyle('K'.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'92D050'))));
                            $objSheet->setCellValueByColumnAndRow(10,$row,'V');
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'Unité validé sans compense');
                            
                            $objSheet->getStyle('K'.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FFE699'))));
                            $objSheet->setCellValueByColumnAndRow(10,$row,'VC');
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'Unité validé par compense');
                            
                            $objSheet->getStyle('K'.$row)->applyFromArray(array('fill'=>array('type'=>PHPExcel_Style_Fill::FILL_SOLID,'color'=>array('rgb'=>'FF0000'))));
                            $objSheet->setCellValueByColumnAndRow(10,$row,'NV');
                            $objSheet->setCellValueByColumnAndRow(11,$row++,'Unité Non validé');
                            
            }
        /* fin de remplissage, le fichier maintenant contient les informations nÃ©cessaires  */
        $nomFichier='PV S'.$semestre.'-'.$departement.'_'.$annee_univ;
        $objWriter = PHPExcel_IOFactory::createwriter($objXLS, 'Excel5');
        header('Content-Type', 'application/msexcel;charset=utf-8');
        /*   presision du nom de fichier   */
        header('Content-Disposition: attachment;filename="' . $nomFichier . '.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objXLS, 'Excel5');
        ob_end_clean();
        $objWriter->save('php://output');
      
        
        }  
    /* AZ fin de generation des PV*/
    
    /*AZ 
     * Bulletin historique
     * 2019
     * */
    //selection de l'etudiant
    function trouver_etudiant_bulletin_historique_new()
    {
        //$semestre = $_POST['semestre'];
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'choix_semestre_bulltin_new';
        $id_action ='matriculeEtudiant';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);
        
        $titre = 'Consulter le rélévé des notes d\'un étudiant ';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);
        
        
        $this->load->view("recherche_parametree", $data);
    }
    
    //choix semestre
    function choix_semestre_bulltin_new($matriculeEtudiant){
        $controlleur = "scolarite";
        $titre = "Choisir un semestre";
        $data = array('titre' => $titre,'controlleur' => $controlleur,'matriculeEtudiant'=>$matriculeEtudiant);
        // $this->load->view("recherche_parametree", $data);
        $this->load->view("scolarite/choix_semestre_bulltin_new",$data);
    }
//    public function voir_bulletin_new()
//    {
//        $matriculeEtudiant= $_POST['matriculeEtudiant'];
//        $semestre =  $_POST['semestre'];
//        $annee = 0;
//        $data = "";
//        //infos de l'etudiant
//        $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
//        //
//        $code = $this->scolarite_modele->info_bulltin($matriculeEtudiant);
//        //infos etudiant
//        $dataE = $this->scolarite_modele->get_informations_etudiant($matriculeEtudiant);
//        
//        
//        //calcul et traitement
//        $notespartielles = $this->bulletin_modele->notespartielles_pour($matriculeEtudiant);
//        
//        $inscrits_a_s = $this->bulletin_modele->inscrits_a_s_pour($matriculeEtudiant);
//        $indexation = $this->bulletin_modele->indexation_matricules_notespartielles($notespartielles);
//        $notespartielles_a_s = $this->bulletin_modele->notespartielles_a_s($matriculeEtudiant, $inscrits_a_s, $notespartielles,$indexation);
//        $module = $this->bulletin_modele->module();
//        $unite = $this->bulletin_modele->unite();
//        $coefficients = $this->bulletin_modele->getCoefficients(4);
//        /*notes eliminatoires ici*/
//        $noteElimiationMatiere = $this->bulletin_modele->get_note_elimination_matiere_courante(4);
//        //echo "<h1>$noteElimiationMatiere</h1>";
//        $coef_cc = $coefficients['cc'];
//        $coef_exam = $coefficients['exam'];
//        /*la note de validation */
//        $noteValidationMatiere = 10;
//        $noteValidation_module = 9;
//        $cc = $this->bulletin_modele->cc($matriculeEtudiant, $notespartielles_a_s, $module, $unite);
//        $exam = $this->bulletin_modele->exam($matriculeEtudiant, $notespartielles_a_s, $module, $unite);
//        $examRT = $this->bulletin_modele->examRT($matriculeEtudiant, $notespartielles_a_s, $module, $unite);
//        $cc_exam = $this->bulletin_modele->cc_exam($matriculeEtudiant, $cc, $exam);
//        $notes_globales = $this->bulletin_modele->notes_globales($matriculeEtudiant, $cc_exam, $examRT);
//        $planetudesmoduleelem = $this->bulletin_modele->planetudesmoduleelem($matriculeEtudiant, $notes_globales, $module, $unite, $coef_cc, $coef_exam);
//        $etudiant_sem_note_bis = $this->bulletin_modele->etudiant_sem_note_bis($matriculeEtudiant, $planetudesmoduleelem);
//        $capseul_bis = $this->bulletin_modele->capseul_bis($matriculeEtudiant, $planetudesmoduleelem);
//        $etudiant_mod_note_bis = $this->bulletin_modele->etudiant_mod_note_bis($matriculeEtudiant, $planetudesmoduleelem);
//        $nombreelementselimines_bis = $this->bulletin_modele->nombreelementselimines_bis($matriculeEtudiant, $planetudesmoduleelem, $noteElimiationMatiere);
//        $compense_interne_bis = $this->bulletin_modele->compense_interne_bis($matriculeEtudiant, $etudiant_mod_note_bis, $nombreelementselimines_bis, $noteValidation_module);
//        $capinterne_bis = $this->bulletin_modele->capinterne_bis($matriculeEtudiant, $planetudesmoduleelem, $compense_interne_bis, $noteValidationMatiere);
//        $moduleselimines_bis = $this->bulletin_modele->moduleselimines_bis($matriculeEtudiant, $etudiant_mod_note_bis, $planetudesmoduleelem, $noteValidation_module, $noteElimiationMatiere);
//        $compense_externe_bis = $this->bulletin_modele->compense_externe_bis($matriculeEtudiant, $etudiant_sem_note_bis, $moduleselimines_bis, $noteValidation_module);
//        $capexterne_bis = $this->bulletin_modele->capexterne_bis($matriculeEtudiant, $planetudesmoduleelem, $compense_interne_bis, $compense_externe_bis, $noteValidationMatiere, $noteElimiationMatiere);
//        $noncap_bis = $this->bulletin_modele->noncap_bis($matriculeEtudiant, $planetudesmoduleelem, $capseul_bis, $capinterne_bis, $capexterne_bis);
//        $releve_bis = $this->bulletin_modele->releve_bis($matriculeEtudiant, $capseul_bis, $capinterne_bis, $capexterne_bis, $noncap_bis);
//        $modules_non_valides_bis = $this->bulletin_modele->modules_non_valides_bis($matriculeEtudiant, $releve_bis);
//        $modules_v_sans_compense_bis = $this->bulletin_modele->modules_v_sans_compense_bis($matriculeEtudiant, $releve_bis);
//        $module_v_avec_compense_bis = $this->bulletin_modele->module_v_avec_compense_bis($matriculeEtudiant, $releve_bis);
//        $modules_valides_bis = $this->bulletin_modele->modules_valides_bis($matriculeEtudiant, $modules_v_sans_compense_bis, $module_v_avec_compense_bis);
//        $modules_decision_bis = $this->bulletin_modele->modules_decision_bis($matriculeEtudiant, $planetudesmoduleelem, $modules_valides_bis, $modules_non_valides_bis, $noteValidationMatiere);
//        $semestre_decision_bis = $this->bulletin_modele->semestre_decision_bis($matriculeEtudiant, $modules_decision_bis);
//        
//        $s = $this->bulletin_modele->max_semestre($matriculeEtudiant,$releve_bis);
//        if ($semestre == 0) {
//            
//            $this->load->view('scolarite/head_bulletin', $data);
//            for($i=1;$i<$s+1;$i++){
//                $semestre=$i;
//                $min = $this->bulletin_modele->min_annee_admis($matriculeEtudiant, $semestre_decision_bis,$semestre, $decision = "Admis(e)");
//                
//                $annee = 0;
//                if ($min != null) {
//                    $annee = $min;
//                    $passage = $this->scolarite_modele->get_decision_passage($annee - 1, $matriculeEtudiant);
//                    if ($matriculeEtudiant == 13042) {
//                        $annee = $min + 2;
//                    }
//                } else {
//                    $max = $this->bulletin_modele->max_annee_admis($matriculeEtudiant,$semestre_decision_bis, $semestre);
//                    $passage = $this->scolarite_modele->get_decision_passage($max - 1, $matriculeEtudiant);
//                    if ($semestre % 2 == 1) {
//                        $annee = $max - 1;
//                        if ($matriculeEtudiant == 11003 or $matriculeEtudiant == 15280) {
//                            $annee = $max;
//                        }
//                    } else {
//                        $annee = $max;
//                    }
//                }
//                $semRes = null;
//                $moduleRes = null;
//                $moduleNc = null;
//                $semRes = $this->bulletin_modele->getSemestreResult_bis($matriculeEtudiant, $semestre, $annee,$semestre_decision_bis , $etudiant_sem_note_bis);
//                $moduleRes = $this->bulletin_modele->getModulesResult_bis_h($matriculeEtudiant, $semestre, $annee, $releve_bis, $notes_globales, $module,$modules_decision_bis,$etudiant_mod_note_bis,$unite);
//                
//                $moyenne= $this->bulletin_modele->get_moyenne_niveau($matriculeEtudiant,$semestre_decision_bis,$etudiant_sem_note_bis);
//                $data = array(
//                    'max_semestre' => $s,
//                    'moyenne' => $moyenne,
//                    'passage' => $passage,
//                    'code' => $code,
//                    'infoE' => $dataE,
//                    'nc' => $moduleNc,
//                    'info' => $infoEtudiant,
//                    'semestre' => $semRes,
//                    'modules' => $moduleRes,
//                    'annee' => $annee,
//                    'numSem' => $semestre
//                );
//                $info= $this->load->view('scolarite/bulltin_tous', $data);
//            }
//        } else {
//            $min = $this->bulletin_modele->min_annee_admis($matriculeEtudiant, $semestre_decision_bis,$semestre, $decision = "Admis(e)");
//            
//            $annee = 0;
//            if ($min != null) {
//                $annee = $min;
//                $passage = $this->scolarite_modele->get_decision_passage($annee - 1, $matriculeEtudiant);
//                if ($matriculeEtudiant == 13042) {
//                    $annee = $min + 2;
//                }
//            } else {
//                $max = $this->bulletin_modele->max_annee_admis($matriculeEtudiant,$semestre_decision_bis, $semestre);
//                $passage = $this->scolarite_modele->get_decision_passage($max - 1, $matriculeEtudiant);
//                if ($semestre % 2 == 1) {
//                    $annee = $max - 1;
//                    if ($matriculeEtudiant == 11003 or $matriculeEtudiant == 15280) {
//                        $annee = $max;
//                    }
//                } else {
//                    $annee = $max;
//                }
//            }
//             $semRes = null;
//             $moduleRes = null;
//             $moduleNc = null;
//             $semRes = $this->bulletin_modele->getSemestreResult_bis($matriculeEtudiant, $semestre, $annee,$semestre_decision_bis , $etudiant_sem_note_bis);
//             $moduleRes = $this->bulletin_modele->getModulesResult_bis_h($matriculeEtudiant, $semestre, $annee, $releve_bis, $notes_globales, $module,$modules_decision_bis,$etudiant_mod_note_bis,$unite);
//             $moyenne= $this->bulletin_modele->get_moyenne_niveau($matriculeEtudiant,$semestre_decision_bis,$etudiant_sem_note_bis);
//             $data = array(
//                 'max_semestre' => $s,
//                 'moyenne' => $moyenne,
//                 'passage' => $passage,
//                 'code' => $code,
//                 'infoE' => $dataE,
//                 'nc' => $moduleNc,
//                 'info' => $infoEtudiant,
//                 'semestre' => $semRes,
//                 'modules' => $moduleRes,
//                 'annee' => $annee,
//                 'numSem' => $semestre
//             );
//             $this->load->view('scolarite/bulltin', $data);
//        }
//    }
//    /*AZ fin new bulletin historique*/
//    
    


    public function voir_bulletin_new()
    {
        $matriculeEtudiant= $_POST['matriculeEtudiant'];
        $semestre =  $_POST['semestre'];
        $annee = 0;
        $data = "";
        //infos de l'etudiant
        $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
        //
        $code = $this->scolarite_modele->info_bulltin($matriculeEtudiant);
        //infos etudiant
        $dataE = $this->scolarite_modele->get_informations_etudiant($matriculeEtudiant);
        
        
        //calcul et traitement
        $notespartielles = $this->bulletin_modele->notespartielles_pour($matriculeEtudiant);
        
        $inscrits_a_s = $this->bulletin_modele->inscrits_a_s_pour($matriculeEtudiant);
        
        $indexation = $this->bulletin_modele->indexation_matricules_notespartielles($notespartielles);
        
        $notespartielles_a_s = $this->bulletin_modele->notespartielles_a_s($matriculeEtudiant, $inscrits_a_s, $notespartielles,$indexation);
        
        $module = $this->bulletin_modele->module();
        $unite = $this->bulletin_modele->unite();
        $coefficients = $this->bulletin_modele->getCoefficients(4);
        /*notes eliminatoires ici*/
        $noteElimiationMatiere = $this->bulletin_modele->get_note_elimination_matiere_courante(4);
       $noteElimination_module=$this->bulletin_modele->get_note_elimination_module_courante(4);// add by MedBakar 18-03-2020
        
 // echo "<h1>$noteElimiationMatiere</h1>";
       // print_r($noteElimiationMatiere);
        $coef_cc = $coefficients['cc'];
        $coef_exam = $coefficients['exam'];
        /*la note de validation */
        $noteValidationMatiere = 10;//esq on cree une table dans le bd ou b1 ?!
        $noteValidation_module =10;
        
        $cc = $this->bulletin_modele->cc($matriculeEtudiant, $notespartielles_a_s, $module, $unite);
               
        $exam = $this->bulletin_modele->exam($matriculeEtudiant, $notespartielles_a_s, $module, $unite);
        $examRT = $this->bulletin_modele->examRT($matriculeEtudiant, $notespartielles_a_s, $module, $unite);
        $cc_exam = $this->bulletin_modele->cc_exam($matriculeEtudiant, $cc, $exam);
        $notes_globales = $this->bulletin_modele->notes_globales($matriculeEtudiant, $cc_exam, $examRT);
        $planetudesmoduleelem = $this->bulletin_modele->planetudesmoduleelem($matriculeEtudiant, $notes_globales, $module, $unite, $coef_cc, $coef_exam);
        $etudiant_sem_note_bis = $this->bulletin_modele->etudiant_sem_note_bis($matriculeEtudiant, $planetudesmoduleelem);//moyenSemestre
        $capseul_bis = $this->bulletin_modele->capseul_bis($matriculeEtudiant, $planetudesmoduleelem);
        $etudiant_mod_note_bis = $this->bulletin_modele->etudiant_mod_note_bis($matriculeEtudiant, $planetudesmoduleelem);
        $nombreelementselimines_bis = $this->bulletin_modele->nombreelementselimines_bis($matriculeEtudiant, $planetudesmoduleelem, $noteElimiationMatiere);
        //---------------
        $compense_interne_bis = $this->bulletin_modele->compense_interne_bis($matriculeEtudiant, $etudiant_mod_note_bis, $nombreelementselimines_bis,$noteValidation_module);
        $capinterne_bis = $this->bulletin_modele->capinterne_bis($matriculeEtudiant, $planetudesmoduleelem, $compense_interne_bis, $noteValidationMatiere);
        $moduleselimines_bis = $this->bulletin_modele->moduleselimines_bis($matriculeEtudiant, $etudiant_mod_note_bis, $planetudesmoduleelem, $noteElimination_module, $noteElimiationMatiere);
        $compense_externe_bis = $this->bulletin_modele->compense_externe_bis($matriculeEtudiant, $etudiant_sem_note_bis, $moduleselimines_bis,$noteValidation_module);
        $capexterne_bis = $this->bulletin_modele->capexterne_bis($matriculeEtudiant, $planetudesmoduleelem, $compense_interne_bis, $compense_externe_bis, $noteValidationMatiere, $noteElimiationMatiere);
        $noncap_bis = $this->bulletin_modele->noncap_bis($matriculeEtudiant, $planetudesmoduleelem, $capseul_bis, $capinterne_bis, $capexterne_bis);
        $releve_bis = $this->bulletin_modele->releve_bis($matriculeEtudiant, $capseul_bis, $capinterne_bis, $capexterne_bis, $noncap_bis);
        $modules_non_valides_bis = $this->bulletin_modele->modules_non_valides_bis($matriculeEtudiant, $releve_bis);
        $modules_v_sans_compense_bis = $this->bulletin_modele->modules_v_sans_compense_bis($matriculeEtudiant, $releve_bis);
        $module_v_avec_compense_bis = $this->bulletin_modele->module_v_avec_compense_bis($matriculeEtudiant, $releve_bis);
        $modules_valides_bis = $this->bulletin_modele->modules_valides_bis($matriculeEtudiant, $modules_v_sans_compense_bis, $module_v_avec_compense_bis);
        $modules_decision_bis = $this->bulletin_modele->modules_decision_bis($matriculeEtudiant, $planetudesmoduleelem, $modules_valides_bis, $modules_non_valides_bis, $noteValidationMatiere);
        $semestre_decision_bis = $this->bulletin_modele->semestre_decision_bis($matriculeEtudiant, $modules_decision_bis);
        
        $s = $this->bulletin_modele->max_semestre($matriculeEtudiant,$releve_bis);
        if ($semestre == 0) {
           
            $this->load->view('scolarite/head_bulletin', $data);
            for($i=1;$i<$s+1;$i++){
                $semestre=$i;
                
# **********ce traitement a ete remplace par un autre****************
//          
//                      $min = $this->bulletin_modele->min_annee_admis($matriculeEtudiant, $semestre_decision_bis,$semestre, $decision = "Admis(e)");
//                
//                $annee = 0;
//                if ($min != null) {
//                    $annee = $min;
//                    $passage = $this->scolarite_modele->get_decision_passage($annee - 1, $matriculeEtudiant);
//                    if ($matriculeEtudiant == 13042) {
//                        $annee = $min + 2;
//                    }
//                } else {
//                    $max = $this->bulletin_modele->max_annee_admis($matriculeEtudiant,$semestre_decision_bis, $semestre);
//                    $passage = $this->scolarite_modele->get_decision_passage($max - 1, $matriculeEtudiant);
//                    if ($semestre % 2 == 1) {
//                        $annee = $max - 1;
//                        if ($matriculeEtudiant == 11003 or $matriculeEtudiant == 15280) {
//                            $annee = $max;
//                        }
//                    } else {
//                        $annee = $max;
//                    }
//                }
                $semRes = null;
                $moduleRes = null;
                $moduleNc = null;

                # ce traitement vient remplacer le traitement precedent
                
                $annee=$this->bulletin_modele->get_year($matriculeEtudiant, $semestre);//add by MedBakar 04-05-2020
//                echo"<br>annee ".$annee;
                $passage = $this->scolarite_modele->get_decision_passage($annee - 1, $matriculeEtudiant);
                $semRes = $this->bulletin_modele->getSemestreResult_bis($matriculeEtudiant, $semestre, $annee,$semestre_decision_bis , $etudiant_sem_note_bis);
                $moduleRes = $this->bulletin_modele->getModulesResult_bis_h($matriculeEtudiant, $semestre, $annee, $releve_bis, $notes_globales, $module,$modules_decision_bis,$etudiant_mod_note_bis,$unite);
             
                $moyenne= $this->bulletin_modele->get_moyenne_niveau($matriculeEtudiant,$semestre_decision_bis,$etudiant_sem_note_bis);
//                echo"<br>Moyenne";
//                print_r($moyenne);
//                echo"<br>";
             //Recuperation des parametres genereaux ---Par MedBakar
             $param_generaux= $this->scolarite_modele->Recup_Parametre_Generaux();
            $regle_passage=$this->scolarite_modele->regle_passage($annee);//add by MedBakar 11-09-2020
//            print_r($regle_passage);
            $data = array(
                 'max_semestre' => $s,
                 'moyenne' => $moyenne,
                 'passage' => $passage,
                 'code' => $code,
                 'infoE' => $dataE,
                 'nc' => $moduleNc,
                 'info' => $infoEtudiant,
                 'semestre' => $semRes,
                 'modules' => $moduleRes,
                 'annee' => $annee,
                 'numSem' => $semestre,
                'param_generaux'=>$param_generaux,
                'regle_passage'=> $regle_passage
             );
         // print_r($data['info']['programme']);            
                $info= $this->load->view('scolarite/bulltin_tous', $data);
            }
        } else {
            
  # *************ce traitement a ete remplace par un autre****
//
//            $min = $this->bulletin_modele->min_annee_admis($matriculeEtudiant, $semestre_decision_bis,$semestre, $decision = "Admis(e)");
//            
//            $annee = 0;
//            if ($min != null) {
//               echo"<br>min: $min<br>";
//                $annee = $min;
//                $passage = $this->scolarite_modele->get_decision_passage($annee - 1, $matriculeEtudiant);
//                if ($matriculeEtudiant == 13042) {
//                    $annee = $min + 2;
//                }
//            } else {
//                $max = $this->bulletin_modele->max_annee_admis($matriculeEtudiant,$semestre_decision_bis, $semestre);
//                $passage = $this->scolarite_modele->get_decision_passage($max - 1, $matriculeEtudiant);
//                if ($semestre % 2 == 1) {
//               echo"<br>max-1: $max<br>";
//                    $annee = $max - 1;
//                    if ($matriculeEtudiant == 11003 or $matriculeEtudiant == 15280) {
//                        $annee = $max;
//                    }
//                } else {
//                    $annee = $max;
//                }
//            }
 
            $semRes = null;
             $moduleRes = null;
             $moduleNc = null;
            $annee=$this->bulletin_modele->get_year($matriculeEtudiant, $semestre);//add by MedBAkar 04-05-2020
              $passage = $this->scolarite_modele->get_decision_passage($annee - 1, $matriculeEtudiant);
             $semRes = $this->bulletin_modele->getSemestreResult_bis($matriculeEtudiant, $semestre, $annee,$semestre_decision_bis , $etudiant_sem_note_bis);
            //print_r($semRes);
            // var_dump($semRes);
            // echo"annee: ".$annee;
             //------
             
             $moduleRes = $this->bulletin_modele->getModulesResult_bis_h($matriculeEtudiant, $semestre, $annee, $releve_bis, $notes_globales, $module,$modules_decision_bis,$etudiant_mod_note_bis,$unite);
           
           //  var_dump($moduleRes);
             
             $moyenne= $this->bulletin_modele->get_moyenne_niveau($matriculeEtudiant,$semestre_decision_bis,$etudiant_sem_note_bis);
           
             
//             $data = array(
//                 'max_semestre' => $s,
//                 'moyenne' => $moyenne,
//                 'passage' => $passage,
//                 'code' => $code,  
//                 'infoE' => $dataE,
//                 'nc' => $moduleNc,
//                 'info' => $infoEtudiant,
//                 'semestre' => $semRes,
//                 'modules' => $moduleRes,
//                 ' annee' => $annee,
//                 'numSem' => $semestre
//             );
 
             //Recuperation des parametres genereaux ---Par MedBakar
             $param_generaux= $this->scolarite_modele->Recup_Parametre_Generaux();
             $regle_passage=$this->scolarite_modele->regle_passage($annee);//add by MedBakar 11-09-2020
            $data = array(
                 'max_semestre' => $s,
                 'moyenne' => $moyenne,
                 'passage' => $passage,
                 'code' => $code,
                 'infoE' => $dataE,
                 'nc' => $moduleNc,
                 'info' => $infoEtudiant,
                 'semestre' => $semRes,
                 'modules' => $moduleRes,
                 'annee' => $annee,
                 'numSem' => $semestre,
                'param_generaux'=>$param_generaux,
                'regle_passage'=> $regle_passage
             );
        //  print_r($data['info']['programme']);
            $this->load->view('scolarite/bulltin', $data);
        }
    }
    /*AZ fin new bulletin historique*/




    
    /*AZ fin new bulletin historique*/
    
    //-----debut list ratrappeur par MedBAkar 16-03-2020-----
     function generer_list_ratrappeur() {
        $data['programme'] = $this->scolarite_modele->get_programme();
        $sessionCourante = $this->scolarite_modele->get_session_courante();
        $annee=$sessionCourante['annee'][0];
        $data['annee'][0] = $annee;
        if($sessionCourante['semestre'][0]==1)//add 31-07-2020 by MedBakar
        $data['annee'][0] = $annee-1;
//        print_r($sessionCourante['semestre']);
        $this->load->view("scolarite/generer_list_ratrappeur",$data);
         
    }
    
    public function afficher_list_ratrappeur()//par MedBakar 16-03-2020
    {
        
        $annee=$_POST['annee'];
        $semestre = $_POST['semestre'];
        $idProgramme = $_POST['idProgramme'];
        $departement = $idProgramme;
        $noteEliminationMatiere = $this->bulletin_modele->get_note_elimination_matiere_courante(4);
        $noteEliminationModule =$this->bulletin_modele->get_note_elimination_module_courante(4);// 9;
         //Recuperation des parametres genereaux ---Par MedBakar
         $param_generaux= $this->scolarite_modele->Recup_Parametre_Generaux();
         $data=$this->bulletin_modele->get_list_ratrappeur($_POST);//recuperation des donnees
//print_r($data);
//return;
//echo"hj<br>";print_r($_POST);echo'<br>';
         //debut modif MedBakar 04-08-2020
         //on veut calculer en suite les moyennes de chaque modules
            foreach($data as $key=>$value){
   //            foreach($value1 as $key=>$value1){
                if(empty($moyenModule[$value['matriculeEtudiant']][$value['idModule']])){
                    $moyenModule[$value['matriculeEtudiant']]['coefficient'][$value['idModule']]=0;
                    $moyenModule[$value['matriculeEtudiant']][$value['idModule']]=0;


                }
                   $moyenModule[$value['matriculeEtudiant']][$value['idModule']]+=$value['note']*$value['coefficient'];
                   $moyenModule[$value['matriculeEtudiant']]['coefficient'][$value['idModule']]+=$value['coefficient'];
   //            }
            }
            //on calcule les moyennes du module
            foreach($moyenModule as $matriculeEtudiant=>$value3){
   //             print_r($value3);
                foreach($value3 as $key=>$value4)
                if($key!='coefficient')
                $moyenModule[$matriculeEtudiant][$key]=($moyenModule[$matriculeEtudiant][$key]/$moyenModule[$matriculeEtudiant]['coefficient'][$key]);
            }
         //debut modif MedBakar 04-08-2020
         $matricule=array();
        $a=0;
        for($i=0;$i<Count($data);$i++){//creation une liste contenant les matricules des etudiants
        if($a<$data[$i]['matriculeEtudiant'])//pour eviter le doublon
            {
           
            $matricule[]=array('matricule'=>$data[$i]['matriculeEtudiant']);
           $a=$data[$i]['matriculeEtudiant'];
           
           $nbr_element_valide[$data[$i]['matriculeEtudiant']]=0;//on compte les elements valides par etudiant --by MedBakar 02-08-2020
            $maquette_inscrit[$data[$i]['matriculeEtudiant']]=0;//on compte la maquete inscrit par chaque etudiant--by MedBakar 02-08-2020
            }  
        }
        $t=array();
       for($j=0;$j<Count($matricule);$j++) //on stock dans array le matricule et les sigles avec leur decision par etudiant
        for($i=0;$i<Count($data);$i++){
            
            if($data[$i]['matriculeEtudiant']==$matricule[$j]['matricule']){
                $decision='';
               //debut modif MedBakar 04-08-2020
                if($data[$i]['note']<$noteEliminationMatiere){
                    $decision='RAT-OB';
                }
                else 
                    if($data[$i]['note']<10 &&  $moyenModule[$data[$i]['matriculeEtudiant']][$data[$i]['idModule']]<$noteEliminationModule)
                        $decision='RAT-OB';
                else
                    if($data[$i]['note']<10){
                    $decision='RAT-OP';
                    }
                    else{
                    $decision='V';
                    $nbr_element_valide[$data[$i]['matriculeEtudiant']]=$nbr_element_valide[$data[$i]['matriculeEtudiant']]+1;//--by MedBakar 02-08-2020
                   }
                   //fin modif MedBakar 04-08-2020
//                if($data[$i]['capit']=='NC'){
//                    $decision='RAT-OB';
//                }
//                else
//                    if($data[$i]['note']<10){
//                    $decision='RAT-OP';
//                    }
//                    else{
//                    $decision='V';
//                    $nbr_element_valide[$data[$i]['matriculeEtudiant']]=$nbr_element_valide[$data[$i]['matriculeEtudiant']]+1;//--by MedBakar 02-08-2020
//                   }
                    $maquette_inscrit[$data[$i]['matriculeEtudiant']]++;//--by MedBakar 02-08-2020
//                $t[]=array('matricule'=>$data[$i]['matriculeEtudiant'],'sigle'=>$data[$i]['sigle'],'sigleUnite'=>$data[$i]['idModule'],'decision'=>$decision);
                $t[$data[$i]['matriculeEtudiant']][$data[$i]['idModule']][$data[$i]['sigle']]=array('decision'=>$decision);
            }
        }
        $semestreP=3;
        if($semestre%2==0){
            $semestreP=1;
            $annee++;
        }
        $maquette=$this->get_maquette($departement,$semestre,$semestreP,$annee);
//        print_r($nbr_element_valide);
//        print_r($maquette_inscrit);
        $data=array('matricule'=>$matricule,'donnee'=>$t,'param_gen'=>$param_generaux,'semestre'=>$semestre,'departement'=>$departement,'annee'=>$annee,'maquette'=>$maquette,'nbr_element_valide'=>$nbr_element_valide,'maquette_inscrit'=>$maquette_inscrit);//--Modified by MedBakar 02-08-2020
//        print_r($data);
        $this->load->view('scolarite/afficher_list_ratrappeur', $data);
    }
    /* MedBakar fin generation Liste ratrapeur*/
    function get_maquette($idProgramme,$semestre,$semestreP,$annee){//17-06-2020 add by MedBakar
        $query="select distinct  p.sigle,m.sigleUnite from planetudes p,module m,unite u where u.sigle=m.sigleUnite and m.sigle=p.sigle and p.annee=$annee"
                . " and p.semestre=$semestreP and u.semestre=$semestre and u.idProgramme='$idProgramme' and (m.semestreDesactivation is null or m.semestreDesactivation='')  "
                . " and (m.semestreActivation <=$annee$semestreP or m.semestreActivation is null)";
        $result=$this->db->query($query);
//        echo $query;
        $maquette=array();
        if($result->num_rows()>0){
            foreach($result->result_array() as $row){
                 $maquette[$row['sigleUnite']][$row['sigle']]='v';
                $maquette[$row['sigleUnite']]['exist']=true;
            }
        }
    return $maquette;    
    }
    function get_maquette_credit_coef($idProgramme,$semestre,$semestreP,$annee){//add 06-08-2020 MedBakar
        $query="select distinct  p.sigle,m.sigleUnite,m.nbCredits,m.coefficient,u.titre,m.titre as titreModule from planetudes p,module m,unite u where u.sigle=m.sigleUnite and m.sigle=p.sigle and p.annee=$annee"
                . " and p.semestre=$semestreP and u.semestre=$semestre and u.idProgramme='$idProgramme' and (m.semestreDesactivation is null or m.semestreDesactivation='')  "
                . " and (m.semestreActivation <=$annee$semestreP or m.semestreActivation is null)";
        $result=$this->db->query($query);
//        echo $query;
        $maquette=array();
        if($result->num_rows()>0){
            foreach($result->result_array() as $row){
                 $maquette[$row['sigleUnite']][$row['sigle']]['credit']=$row['nbCredits'];
                 $maquette[$row['sigleUnite']][$row['sigle']]['coef']=$row['coefficient'];
                 $maquette[$row['sigleUnite']]['titre']['titre_unite']=$row['titre'];
                 $maquette[$row['sigleUnite']]['titre'][$row['sigle']]=$row['titreModule'];
               
            }
        }
    return $maquette;    
    }
    //-------------------------------Fin--------
    
    function trouver_unite_a_supprimer() {//add & modified by MedBakar 15-03-2020
        // Sert à Consulter module et à Modifier module.
        	$tables = array("unite", "programme");
        $join_keys = array('unite.idProgramme = programme.idProgramme');
        $db_columns = array('sigle', 'titre','credits','coefficient', 'semestre','anneeAct','if(semestreAct=3,\'Impaire\',if(semestreAct=1,\'Paire\',\'ni paire,ni impaire\')) as semestreAct','programme.nom as programme');
        $db_result =  array('sigle', 'titre','credits','coefficient', 'semestre','anneeAct','semestreAct','programme');
        $grid_columns =  array('Code', 'Intitulé','Credits','Coefficient', 'Semestre d\'études','Année d\'activation','semestre d\'activation','Programme');
        $db_order = 'order by sigle';
        //$action = $to_do_action;
 $action = 'supprimer_unite';
        $id_action = 'sigle';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $db_order, $db_result);
	
//		$tables = array("module", "departement", "cycle","employe");
//        $join_keys = array('module.idDepartement = departement.idDepartement', 'module.idCycle = cycle.idCycle','module.professeurResponsable=employe.matriculeEmploye');   
//        $db_columns = array('sigle','typeModule', 'titre',"concat(employe.prenom,' ',employe.nom) as EnsRespo");
//        $db_result = array('sigle','typeModule', 'titre','EnsRespo');
//        $grid_columns = array('Sigle', 'Type d\'élément', 'Titre d\'élément','Enseignent responsable');
//        $db_order = 'order by sigle';
         $action = 'supprimer_module';
        $id_action = 'sigle';

      //  $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, $db_order, $db_result);
	
        $titre = 'Supprimer un module ';
        $controlleur = "scolarite";
        $confirmation ='Êtes vous sûr de vouloir supprimer ce module? ';
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result,'confirmation'=>$confirmation);
        $this->load->view("recherche_parametree", $data);
    }
    //-----------
     function supprimer_unite($sigle) 
    {
        $confirmation = $this->scolarite_modele->supprimer_unite($sigle);
        $this->load->view('scolarite/modification_confirme', $confirmation);
    }
    
//creation automatique des groupes des elements  add By MedBakar 01-04-2020
    function creer_groupes(){
        $this->form_validation->set_rules('nbGroupeCM', 'nbGroupeCM ', 'NUMBER');
        $this->form_validation->set_rules('nbGroupeTD', 'nbGroupeTD ', 'NUMBER');
        $this->form_validation->set_rules('nbGroupeTP', 'nbGroupeTP ', 'NUMBER');
        if ($this->form_validation->run()) {
        
        $info=$this->input->post();
       $sem=$info['niveau'];//le semestre normal S1->S6 on recup S1 , S3 ou S5 
       $departement=$info['programme'];
       $niveau=0; 
       if($sem==1){
            $niveau=1;
        }else
            if($sem==3){
            $niveau=2;
        }else
            if($sem==5){
            $niveau=3;
        }
       //$nbGroupe=$info['nbGroupe'];
//       if($nbGroupe==0){
//           $nbGroupe=1;
//       }
        //print_r($info);
        $requete='';
        $TypeGroupes=array();
        if(!empty($info['nbGroupeCM'])){
            $TypeGroupes['Groupe-Theorie']=$info['nbGroupeCM'];
        }
        if(!empty($info['nbGroupeTD'])){
          $TypeGroupes['Groupe-TD']=$info['nbGroupeTD'];  
        }
        if(!empty($info['nbGroupeTP'])){
         $TypeGroupes['Groupe-TP']=$info['nbGroupeTP'];   
        }
        
        //print_r($TypeGroupes);
        $infoGroupe['matEmployer']='E0011';
          $infoGroupe['date']=$info['date'];
         //recup matricule && module inscrit
         ///print_r($matricule);
       $query="select module.sigle,unite.semestreAct from module,unite where module.sigleUnite=unite.sigle and unite.idProgramme=module.idDepartement and module.idDepartement like'".$departement."' and (semestredesactivation is null or  semestredesactivation='') and semestreActivation<='".$info['date']."3' and (semestre='".$sem."' or semestre='".($sem+1)."')";
//      echo $query."<br>";
       $result=$this->db->query($query);
       if($result->num_rows()>0){
           foreach($result->result_array() as $rows){
               $infoGroupe['session']=$rows['semestreAct'];                  
                $infoGroupe['sigle']= $rows['sigle'];
                //on recupere les etudiants de se sigle
                $matricule=array();
                $matricule= $this->scolarite_modele->list_etudiant_inscrit_pour_sigle($rows['sigle'],$info['date']);
//                echo"test".$rows['sigle'];print_r($matricule);
          if(empty($matricule)){
              //afficher msg d'echec
             $data['typeBox'] = 'error_box';
            $data['informations'] = 'Les groupes<b> L' . $niveau .'-'.$info['programme']. '-' . $infoGroupe['date'] . ' </b> n\'ont pas été créé car aucun Etudiant est inscrit dans cette date.'; 
            $this->load->view('scolarite/modification_confirme', $data);
                return;
              
          }
          foreach($TypeGroupes as $groupe=>$nbGroupe){
              $infoGroupe['typeGroupe']=$groupe;    
            //$nbGroupe=    $info[$nbGroupe];
            //$info['nbGroupe']=$nbGroupe;
           //on depasse si le nbr de groupe est  negative
              if($nbGroupe<=0){
                continue;
              }
                $borne_duGroupe=number_format(count($matricule)/$nbGroupe,0);
  //              echo$borne_duGroupe.'<br>';
                $group=0;
                $i=0;
                $multiple=1;
                $mat_groupe=array();
    /*Mab*/
          for($cpt=0;$cpt<count($matricule);$cpt++){
             if($cpt==$borne_duGroupe*$multiple && $group<$nbGroupe-1){
                 $group++;
                 $i=0;
                 $multiple++;
             }
             $mat_groupe['G'.$group][$i]=$matricule[$cpt]['matriculeEtudiant']."<br>";
             $i++;
         }
               for($nbGrp=0;$nbGrp<$nbGroupe;$nbGrp++){
               ////creation des groupes
                $numGroupe=$this->scolarite_modele->cree_nouveau_groupe($infoGroupe);
                 $idGroupe = $infoGroupe['sigle'] . '-' . $infoGroupe['date'] . $infoGroupe['session'] . '-' . $infoGroupe['typeGroupe'] . $numGroupe;
               // inscrir des etudiants dans leurs groupes
                 //si un groupe de meme type exist deja on n'inscrit pas les etudiants automatiquement
                $cond=$this->scolarite_modele->est_dans_groupe_de_type($infoGroupe['sigle'],$infoGroupe['date'],$infoGroupe['session'],'', $infoGroupe['typeGroupe'],$idGroupe);
                 if($cond==$idGroupe){
                 $requete.=$this->scolarite_modele->former_req_inscrir_ds_groupe($mat_groupe['G'.$nbGrp],$idGroupe);
                 }
                }
         }     
        }
       
        
        //executer la requete d'inscription des etudiants
        $this->scolarite_modele->inscrir_ds_groupe($requete);
       // echo '<br> la requete'.$requete.'<br>';
        //afficher msg de confirmation
       
      
         //en registrer les informations
//       $req="INSERT INTO ges_groupes ('niveau','programme','CM','TD','TP','annee')values('".$niveau."','".$info['programme']."','".$info['nbGroupeCM']."','".$info['nbGroupeCM']."','".$info['nbGroupeCM']."','".substr($info['date'],2).substr($info['date']+1,2)."')";
      $this->scolarite_modele->ges_groupes($niveau,$info['programme'],$info['nbGroupeCM'],$info['nbGroupeTD'],$info['nbGroupeTP'],$info['date']);
  //     echo $r;
        
        $data['typeBox'] = 'valid_box';
					$data['informations'] = 'Les groupes<b> L' . $niveau .'-'.$info['programme']. '-' . $infoGroupe['date'] . ' </b> ont été créé.'; 
					$this->load->view('scolarite/modification_confirme', $data);
       }
       else{
       //afficher msg d'echec
       $data['typeBox'] = 'error_box';
            $data['informations'] = 'Les groupes<b> L' . $niveau .'-'.$info['programme']. '-' . $infoGroupe['date'] . ' </b> n\'ont pas été créé car la maquete n\'a pas encore créer.'; 
            $this->load->view('scolarite/modification_confirme', $data);
    
           
       }
       
       }else{
        $data['programme'] = $this->scolarite_modele->get_programme();
        $data_session_courante = $this->scolarite_modele->get_session_courante();
        //print_r($data_session_courante['annee'][0]);
        $data['annee']=$data_session_courante['annee'][0];
        //print_r($data);
        $this->load->view('scolarite/creer_groupes',$data);
        }
    }
    //Fin Ajout MedBakar 02-04-2020
    //add by MedBakar 09-04-2020
    function administration_groupes(){
        if (!empty($this->input->post())) {
        
        $info=$this->input->post();
        $sem=$info['niveau'];//le semestre normal S1->S6 on recup S1 , S3 ou S5 
       $departement=$info['programme'];
       $niveau=0; 
       if($sem==1){
            $niveau=1;
        }else
            if($sem==3){
            $niveau=2;
        }else
            if($sem==5){
            $niveau=3;
        }
//on recupere tous les etudiants
        
        //on recupere tous les elements
        $query="select module.sigle,unite.semestreAct from module,unite where module.sigleUnite=unite.sigle and unite.idProgramme=module.idDepartement and module.idDepartement like'".$departement."' and semestredesactivation is null and (semestre='".$sem."' or semestre='".($sem+1)."')";
       $result=$this->db->query($query);
       if($result->num_rows()>0){
           foreach($result->result_array() as $rows){
               $elements[]=$rows['sigle'];
           }
       }
       $data['date']=$info['date'];
       $data['niveau']=$info['niveau'];
       $data['programme']=$info['programme'];
       
       $query="select module.sigle,unite.semestreAct from module,unite where module.sigleUnite=unite.sigle and unite.idProgramme=module.idDepartement and module.idDepartement like'".$departement."' and semestredesactivation is null and (semestre='".$sem."' or semestre='".($sem+1)."')";
       $result=$this->db->query($query);
       $allModules=array();
       if($result->num_rows()>0){
           foreach($result->result_array() as $rows){
              $allModules[]=$rows['sigle'];      
           }
       }   
       $data['allModules'] = $allModules;
      // print_r($data);
        $this->load->view('scolarite/adm_ins_desins_groupe',$data);
        }else{
        $data['programme'] = $this->scolarite_modele->get_programme();
        $data_session_courante = $this->scolarite_modele->get_session_courante();
        //print_r($data_session_courante['annee'][0]);
        $data['annee']=$data_session_courante['annee'][0];
        //print_r($data);
        $this->load->view('scolarite/adm_groupes',$data);
        }
    }
        function afficher_etudiant_ins_ds_module() {
             $data['etudiant']= $this->scolarite_modele->list_etudiant_inscrit_pour_sigle_ajax($_GET['sigle'],$_GET['date']);
        echo(json_encode($data));
    }
    
    //on supprime les etudiants du groupe de type envoyer
    //puis on inscrit ses etudiants dans le groupe choisice(de Meme type supprime)
    //si le chkbox desinscrire seulement est choisice on fait seulement la desinscription(supp de l'etudiant du groupe)
    function administration_groupes_2($niveau,$departement,$date){
         if (!empty($this->input->post())) {
         $post=$this->input->post();
//             print_r($post);
             //recup data
             $sigle=$post['sigle'];
             $groupe=$post['groupe'];
             
            $traitement_parallele=false;
             if(!empty($post['traitement_parallele'])){
                $traitement_parallele=true;  
             }
// echo"$groupe<br>".substr($groupe,-1);
             
             $idGroupe=substr($groupe,0,strrpos($groupe,'T')+2);
//             echo "$idGroupe".substr($groupe,0,strrpos($groupe,'-')+1);
             $Type_groupes=$this->getGroupeTypes($sigle,$date);
             
             /****/
             $matricule=$post['matricule'];
             $query='';
             $where='';
             for($i=0;$i<count($matricule);$i++){
                 //ajout traitement parallele 27-07-2020
              if($traitement_parallele)
                  { 
                  foreach($Type_groupes as $types=>$numbers){
                    if($numbers>=substr($groupe,-1)){
                    //echo "<br>".substr($groupe,0,strrpos($groupe,'-')+1).$types.substr($groupe,-1);
                  $query.=", (".$matricule[$i].",'".substr($groupe,0,strrpos($groupe,'-')+1).substr($types,7).substr($groupe,-1)."')";
                  
                    }
                 }
                }else{
                 $query.=", (".$matricule[$i].",'".$groupe."')";
                 }
                 if($i>0)
                     $where.=",";
                 $where.="$matricule[$i]";
             }
//             echo"<br>$query<br>";
           //  echo $query."<br>".$where;
             ////on supp des groupes de meme type
             if(!$traitement_parallele)
             $this->db->query("DELETE FROM listeetudiants where matriculeEtudiant in (".$where.") and idGroupe like '%".$idGroupe."%'");
             else//on supprime l'etudiant de tous les groupes de meme sigle
                              $this->db->query("DELETE FROM listeetudiants where matriculeEtudiant in (".$where.") and idGroupe like '%".(substr($groupe,0,strrpos($groupe,'-')+1))."%'");
             
               print($this->db->last_query());
             if(empty($post['desins'])){        
             //on l'inscrit ds ce groupe
              $this->scolarite_modele->inscrir_ds_groupe($query);
             }
            //msg de confirmation
              $data['typeBox'] = 'valid_box';
            $data['informations'] = 'Les etudiants ont bien desinscrit/inscrit avec succee '; 
            $this->load->view('scolarite/modification_confirme', $data);
       
         }else{
//             $data['date']=$date;
//             $data['niveau']=$niveau;
//             $data['programme']=$departement;
        redirect('scolarite/administration_groupes');     
         }
    }
    //Fin add By MedBakar 10-04-2020
    function getGroupeTypes($sigle='M011',$annee=2019){
       $result= $this->db->query("select typeGroupe,count(typeGroupe) nbre from groupe where sigle like'$sigle' and annee='$annee' group by typeGroupe");
       $info=array();
       if($result->num_rows()>0) 
       foreach($result->result_array() as $row){
            $info[$row['typeGroupe']]=$row['nbre'];
        }
        print_r($info);
        return $info;
    }
    
    //-------------begin add by MedBakar 12-06-2020 ---------------------------
     function trouver_etudiant_ins_attestation_d_generer_fiche() 
    {
        //$semestre = $_POST['semestre'];
        $tables = array("etudiant");
        $join_keys = null;
        $db_columns = array('matriculeEtudiant', 'nom', 'prenom', "case when actif = 1 then 'O' when actif = 0 then 'N' end as actif ");
        $result_columns = array('matriculeEtudiant', 'nom', 'prenom', "actif");
        $grid_columns = array('Matricule', 'Nom', 'Prénom', 'Actif ?');
        $action = 'choix_programme_annee_d_generer_fiche';
        $id_action ='matriculeEtudiant';
        $result = $this->search_modele->getSearchResult($tables, $db_columns, $grid_columns, $join_keys, $action, $id_action, '', $result_columns);

        $titre = 'Choisir un étudiant pour imprimer la fiche d\'inscription';
        $controlleur = "scolarite";
        $data = array('titre' => $titre, 'controlleur' => $controlleur, 'result' => $result);


        $this->load->view("recherche_parametree", $data);
    
    }
     function choix_programme_annee_d_generer_fiche($matriculeEtudiant){
        $controlleur = "scolarite";
        $titre = "Choisir l'annee";

       // $this->load->view("recherche_parametree", $data);
        $annes=$this->scolarite_modele->getAnnee();
        $progs = $this->scolarite_modele->get_programme();
            $data = array('titre' => $titre,'controlleur' => $controlleur,'matriculeEtudiant'=>$matriculeEtudiant,'annee'=>$annes,'programme'=>$progs);
        $this->load->view("scolarite/choix_programme_annee_d_generer_fiche",$data);
    }
     function voir_attestation_ins_d_generer_fiche() 
    {
         $matriculeEtudiant= $_POST['matriculeEtudiant'];
        //$semestre =  $_POST['semestre'];
         $annee =  $_POST['annee'];
         $semestreA=$this->scolarite_modele->getSemstreInAnne($matriculeEtudiant,$annee);
         $data =null;
         $niveau = $this->scolarite_modele->getEtudiatNiveauInscrit($matriculeEtudiant,$annee);
         $code = $this->scolarite_modele-> info_bulltin($matriculeEtudiant); 
        
        $infoEtudiant['niveau']="L".$niveau;
        $dataE = $this->scolarite_modele->get_informations_etudiant($matriculeEtudiant);
//print_r($semestreA);
         if(empty($semestreA)){
            // echo 'Nouveau etudiant : '.$matriculeEtudiant;
             $semestre=1; 
            $idProgramme=$this->scolarite_modele->get_programme($matriculeEtudiant);
            $modules_a_etudies_impaire = $this->scolarite_modele->get_module_impaire_a_etudies($idProgramme,-1,$matriculeEtudiant,$annee); 
            $modules_a_etudies_paire = $this->scolarite_modele->get_module_paire_a_etudies($idProgramme,-1, $matriculeEtudiant,$annee);
            $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
            $data = array('numSem'=>$semestre,'code'=>$code,'infoE'=>$dataE,'redoublant' => null,'niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelist' => null,'moduleR_impairelist' => null,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee,'nouveau'=>'Y');
         }else{
             $redoublant = $this->scolarite_modele->get_redoublant_Et( $matriculeEtudiant,$annee);
           
            $semestre=$semestreA['semestre'];
           if($matriculeEtudiant==16278){
               $semestre=3;
               $redoublant=null;
           }

            $idProgramme=$this->scolarite_modele->get_programme($matriculeEtudiant);
            $modulesR_impaire = $this->scolarite_modele->get_module_rattrapes_impaire($matriculeEtudiant,$annee); 
            $modulesR_paire = $this->scolarite_modele->get_module_rattrapes_paire($matriculeEtudiant, $annee);
            $modules_a_etudies_impaire = $this->scolarite_modele->get_module_impaire_a_etudies($idProgramme,$semestre,$matriculeEtudiant,$annee); 
           // print_r($modules_a_etudies_impaire);
            $modules_a_etudies_paire = $this->scolarite_modele->get_module_paire_a_etudies($idProgramme,$semestre, $matriculeEtudiant,$annee);
             $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
            $data = array('numSem'=>$semestre,'code'=>$code,'infoE'=>$dataE,'redoublant' => $redoublant,'niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelist' => $modulesR_paire,'moduleR_impairelist' => $modulesR_impaire,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee);
         }
         
      
       
        //$data = array('niveau' => $semestre,'modules_a_etudies_pairelist' => $modules_a_etudies_paire,'modules_a_etudies_impairelist' => $modules_a_etudies_impaire,'moduleR_pairelists2' => $modulesR_paire_s2,'moduleR_pairelists4' => $modulesR_paire_s4,'moduleR_pairelist' => $modulesR_paire,'moduleR_impairelist' => $modulesR_impaire,'moduleR_impairelists1' => $modulesR_impaire_s1,'moduleR_impairelists3' => $modulesR_impaire_s3,'info' => $infoEtudiant, 'semestre' => $semestre, 'annee' => $annee);
//        print_r($data);
        $data['parametres']=(array)$this->scolarite_modele->get_parametres_genreaux();
//       print_r($data['infoE']);
       
        $this->load->view('scolarite/consulter_info_attestation_ins_d_generer_fiche', $data);
        
    }
    
   function generer_fiche_new(){
//       echo"hello";
       $post=$this->input->post();
       if(isset($post)){
//       print_r($post);
       //delete info
       $annee=$post['annee'];
       $matriculeEtudiant=$post['matriculeEtudiant'];
//       $sigle=$post['sigle'];
      $sigle_liste='';
       if(!empty($post['imp'])){
           foreach($post['imp'] as $sigle=>$value){
           $sigle_liste.=" , '".$sigle."' ";
//           $values=",(".$matriculeEtudiant.",".$sigle.",".$sem.",-1,'','AV','professeur',".$annee.")";
           }
       }
       if(!empty($post['paire'])){
           foreach($post['paire'] as $sigle=>$value){
           $sigle_liste.=" , '".$sigle."' ";
           }
       }
       $query="DELETE FROM planetudes where annee=$annee  and matriculeEtudiant=$matriculeEtudiant";
       $this->db->query($query);
       
       if(!empty($sigle_liste)){
       $sigle_liste=substr($sigle_liste,2);
       $values='';
            $result=$this->db->query("SELECT m.sigle,u.semestreAct FROM module m,unite u where m.sigleUnite=u.sigle and m.sigle in($sigle_liste) ");
            if ($result->num_rows() > 0) {
                    foreach ($result->result_array() as $row) {
                        $sigleElement[$row['sigle']] = $row['semestreAct'];
                        $values.=" ,($matriculeEtudiant, '".$row['sigle']."', ".$row['semestreAct'].",-1,'','AV','professeur',$annee) ";
                    }
                }
       
       $query="INSERT into planetudes values".substr($values,2);
       $this->db->query($query);
       }
       $informations['typeBox'] = 'valid_box';
        $informations['informations'] = 'Modifications avec succee.';
        $this->load->view('scolarite/modification_confirme', $informations);
    
       }
    
   }
   
    //--------------------------------END add by MED BAKAR 12-06-2020 ----------
    
   /*
     * Add once by MedBAkar
     * new version by MedBakar 09-2020
     */
      function traitement_element_cache_new($matriculeEtudiant,$annee,$semestreCourant, $anneeCourante,$sigleImpaire,$siglePaire,$niveau,$modules_a_etudies_impaire,$modules_a_etudies_paire){
//          $elementImpChecked;
//          echo"<BR><BR><BR>Traitement Element Cache<BR><BR><BR>";
//          print_r($modules_a_etudies_impaire);
//          echo"<BR><BR>sigle choisie<BR><BR>";
//          print_r($sigleImpaire);
//          echo'<br>';
//          echo"<br>begin traitement element cache<br>";
          $element_cache_P_impaire='';
          $element_cache_N_impaire='';
          $semestreCourant=3;
          for($i=0;$i<count($modules_a_etudies_impaire);$i++){
              $checked=false;
            //  echo"<br>$checked ";
              foreach($sigleImpaire as $number=>$elem){
//                  echo"<br>".$elem."==".$modules_a_etudies_impaire[$i]->sigle."<br>";
                  if($elem==$modules_a_etudies_impaire[$i]->sigle){
                  $checked=true;
                  
//$elementImpChecked[$element]=$element;
                  }
              }
              //echo"--$checked<br>";
              if(!$checked){//donc l'element est caché
                   $element_cache_P_impaire.=",(".$matriculeEtudiant.",'".$modules_a_etudies_impaire[$i]->sigle."',".$semestreCourant.",'-1','','AV','professeur',".$anneeCourante.",'O')";
                   $element_cache_N_impaire.=",(".$matriculeEtudiant.",'".$modules_a_etudies_impaire[$i]->sigle."',1,0,".$semestreCourant.",".$anneeCourante.")".",(".$matriculeEtudiant.",'".$modules_a_etudies_impaire[$i]->sigle."',2,0,".$semestreCourant.",".$anneeCourante.")".",(".$matriculeEtudiant.",'".$modules_a_etudies_impaire[$i]->sigle."',4,0,".$semestreCourant.",".$anneeCourante.")";
            
              }
          }
            if(!empty($element_cache_P_impaire)){
        //insert paln etudes
         $query="INSERT INTO planetudes (matriculeEtudiant,sigle,semestre,note,cote,lien,etatNote,annee,caché) values ".substr($element_cache_P_impaire,1).' ';
         $this->db->query($query);
//echo $query;        
//insert notespartielles
         $query="INSERT INTO notespartielles (matriculeEtudiant,sigle,idEvaluation,note,semestre,annee) values ".substr($element_cache_N_impaire,1).' ';
          $this->db->query($query);
//echo $query;      

    }
    
    //semestre Paire
    $element_cache_P_paire='';//insertion ds planetudes
          $element_cache_N_paire='';//insertion  ds notespartielles
          $semestreCourant=1;
          $anneeCourante=$anneeCourante+1;
          for($i=0;$i<count($modules_a_etudies_paire);$i++){
              $checked=false;
            //  echo"<br>$checked ";
              foreach($siglePaire as $number=>$elem){
//                  echo"<br>".$elem."==".$modules_a_etudies_impaire[$i]->sigle."<br>";
                  if($elem==$modules_a_etudies_paire[$i]->sigle){
                    $checked=true;
                  }
              }
              //echo"--$checked<br>";
              if(!$checked){//donc l'element est caché
                   $element_cache_P_paire.=",(".$matriculeEtudiant.",'".$modules_a_etudies_paire[$i]->sigle."',".$semestreCourant.",'-1','','AV','professeur',".$anneeCourante.",'O')";
                   $element_cache_N_paire.=",(".$matriculeEtudiant.",'".$modules_a_etudies_paire[$i]->sigle."',1,0,".$semestreCourant.",".$anneeCourante.")".",(".$matriculeEtudiant.",'".$modules_a_etudies_paire[$i]->sigle."',2,0,".$semestreCourant.",".$anneeCourante.")".",(".$matriculeEtudiant.",'".$modules_a_etudies_paire[$i]->sigle."',4,0,".$semestreCourant.",".$anneeCourante.")";
           
              }
          }
             if(!empty($element_cache_P_paire)){
               //insert planEtudes
            $query="INSERT INTO planetudes (matriculeEtudiant,sigle,semestre,note,cote,lien,etatNote,annee,caché) values ".substr($element_cache_P_paire,1).'';
              $this->db->query($query);
//              echo"<br>$query<br>";
//              print($this->db->last_query());
//              echo"<br>notespar<br>";
                //insert notespartielles
             $query="INSERT INTO notespartielles (matriculeEtudiant,sigle,idEvaluation,note,semestre,annee) values ".substr($element_cache_N_paire,1).' ';
          $this->db->query($query);
//          echo"<br>$query<br>";
//              print($this->db->last_query());
              }
    
    
//          echo"<br>end traitement element cache<br>";
      }
      //fonction remplace la getCreditVAlide(scolarite modele) Par MedBAkar 08-09-2020
    function get_decision_passsage($matriculeEtudiant){
       
        $annee = 0;
        $data = "";
        //infos de l'etudiant
        $infoEtudiant = $this->scolarite_modele->getInfoBulletinEtudiant($matriculeEtudiant);
//        echo"<br> info etudiants: ".$infoEtudiant['idProgramme'].'<br>';
//        print_r($infoEtudiant);
        //---
        $code = $this->scolarite_modele->info_bulltin($matriculeEtudiant);
//        print_r($code);
        //infos etudiant
        $dataE = $this->scolarite_modele->get_informations_etudiant($matriculeEtudiant);
        //calcul et traitement
        $notespartielles = $this->bulletin_modele->notespartielles_pour($matriculeEtudiant);
        $inscrits_a_s = $this->bulletin_modele->inscrits_a_s_pour($matriculeEtudiant);
//        print_r($inscrits_a_s)         ;return;
        $indexation = $this->bulletin_modele->indexation_matricules_notespartielles($notespartielles);
//print_r($indexation)         ;return;
//----------------
        $notespartielles_a_s = $this->bulletin_modele->notespartielles_a_s($matriculeEtudiant, $inscrits_a_s, $notespartielles,$indexation);
//        echo"<br><br>*********<br><br>notespartiellles_a_S: ";print_r($notespartielles_a_s);
        $module = $this->bulletin_modele->module();
        $unite = $this->bulletin_modele->unite();
        $coefficients = $this->bulletin_modele->getCoefficients(4);
        /*notes eliminatoires ici*/
        $noteElimiationMatiere = $this->bulletin_modele->get_note_elimination_matiere_courante(4);
        $noteElimination_module=$this->bulletin_modele->get_note_elimination_module_courante(4);//add by MedBakar
        //echo "<h1>$noteElimiationMatiere</h1>";
        $coef_cc = $coefficients['cc'];
        $coef_exam = $coefficients['exam'];
       // print( $coef_cc);
        /*la note de validation */
        $noteValidationMatiere = 10;
        $noteValidation_module = 10;
        $cc = $this->bulletin_modele->cc($matriculeEtudiant, $notespartielles_a_s, $module, $unite);
//        echo"CC :";print_r($cc);
        $exam = $this->bulletin_modele->exam($matriculeEtudiant, $notespartielles_a_s, $module, $unite);
        $examRT = $this->bulletin_modele->examRT($matriculeEtudiant, $notespartielles_a_s, $module, $unite);
        $cc_exam = $this->bulletin_modele->cc_exam($matriculeEtudiant, $cc, $exam);
        $notes_globales = $this->bulletin_modele->notes_globales($matriculeEtudiant, $cc_exam, $examRT);
//        echo"<br>****************<br>";
//        print_r($notes_globales);
//        echo"<br>****************<br>"; 
//*---
        $planetudesmoduleelem = $this->bulletin_modele->planetudesmoduleelem($matriculeEtudiant, $notes_globales, $module, $unite, $coef_cc, $coef_exam);
//        echo"<br>****************PlanEtudes<br>";
//        print_r($planetudesmoduleelem);
//        echo"<br>****************<br>";
        
        $etudiant_sem_note_bis = $this->bulletin_modele->etudiant_sem_note_bis($matriculeEtudiant, $planetudesmoduleelem);//moyenSemestre
        $capseul_bis = $this->bulletin_modele->capseul_bis($matriculeEtudiant, $planetudesmoduleelem);
        $etudiant_mod_note_bis = $this->bulletin_modele->etudiant_mod_note_bis($matriculeEtudiant, $planetudesmoduleelem);
        $nombreelementselimines_bis = $this->bulletin_modele->nombreelementselimines_bis($matriculeEtudiant, $planetudesmoduleelem, $noteElimiationMatiere);
        $compense_interne_bis = $this->bulletin_modele->compense_interne_bis($matriculeEtudiant, $etudiant_mod_note_bis, $nombreelementselimines_bis, $noteValidation_module);
//        print_r($compense_interne_bis);
        $capinterne_bis = $this->bulletin_modele->capinterne_bis($matriculeEtudiant, $planetudesmoduleelem, $compense_interne_bis, $noteValidationMatiere);
        $moduleselimines_bis = $this->bulletin_modele->moduleselimines_bis($matriculeEtudiant, $etudiant_mod_note_bis, $planetudesmoduleelem, $noteElimination_module, $noteElimiationMatiere);
        $compense_externe_bis = $this->bulletin_modele->compense_externe_bis($matriculeEtudiant, $etudiant_sem_note_bis, $moduleselimines_bis, $noteValidation_module);
        $capexterne_bis = $this->bulletin_modele->capexterne_bis($matriculeEtudiant, $planetudesmoduleelem, $compense_interne_bis, $compense_externe_bis, $noteValidationMatiere, $noteElimiationMatiere);
        $noncap_bis = $this->bulletin_modele->noncap_bis($matriculeEtudiant, $planetudesmoduleelem, $capseul_bis, $capinterne_bis, $capexterne_bis);
        $releve_bis = $this->bulletin_modele->releve_bis($matriculeEtudiant, $capseul_bis, $capinterne_bis, $capexterne_bis, $noncap_bis);
//        print_r( $releve_bis);
       
        $modules_non_valides_bis = $this->bulletin_modele->modules_non_valides_bis($matriculeEtudiant, $releve_bis);
        $modules_v_sans_compense_bis = $this->bulletin_modele->modules_v_sans_compense_bis($matriculeEtudiant, $releve_bis);
        $module_v_avec_compense_bis = $this->bulletin_modele->module_v_avec_compense_bis($matriculeEtudiant, $releve_bis);
        $modules_valides_bis = $this->bulletin_modele->modules_valides_bis($matriculeEtudiant, $modules_v_sans_compense_bis, $module_v_avec_compense_bis);
        $modules_decision_bis = $this->bulletin_modele->modules_decision_bis($matriculeEtudiant, $planetudesmoduleelem, $modules_valides_bis, $modules_non_valides_bis, $noteValidationMatiere);
        
        $semestre_decision_bis = $this->bulletin_modele->semestre_decision_bis($matriculeEtudiant, $modules_decision_bis);
//        print_r($semestre_decision_bis);echo'hehh';
        $max_semestre=0;
        for($i=0;$i<count($semestre_decision_bis);$i++)
        if($semestre_decision_bis[$i]['semestre'] > $max_semestre){
           if($semestre_decision_bis[$i]['decision']=='Admis(e)')
            $max_semestre = $semestre_decision_bis[$i]['semestre'];
        }
            $moyenne= $this->bulletin_modele->get_moyenne_niveau($matriculeEtudiant,$semestre_decision_bis,$etudiant_sem_note_bis);
        $annee=0;
            if(!empty($max_semestre))
        $annee=$this->bulletin_modele->get_year($matriculeEtudiant, $max_semestre);//add by MedBakar 04-05-2020    
        $regle_passage=$this->scolarite_modele->regle_passage($annee);   
//        print_r($regle_passage);
//            print_r($moyenne);
             //----------------------
        $ratrap=array();//matiere a ratraper
        $capit=array();//matiere capitalise en cas de redoublement
        $niveau=1;//L1 L2 L3
        
        $redoublant=0;//donc n'est pas redoublant
        //REGLE DU PASSAGE *******************************************
          //on vas ajouter les regles de passsage afin de recupere seulement les matieres capitalise s'il est redoublant

//                  if($moyenne['ECTSL1']>=$regle_passage[4][1]['ECTSL1'] && ($moyenne['MGL1']/2)>=$regle_passage[4][1]['MGL1']){//condition de passage en L2 non verifie (redoublant en L1)
//                    $niveau=2;
//                }
//                else
//                    $redoublant=1;
//                
//                if($moyenne['ECTSL1']==$regle_passage[4][2]['ECTSL1'] && ($moyenne['MGL1']/2)>=$regle_passage[4][2]['MGL1']){//condition de passage en L3 non verifie (redoublant en L2)
//                    if($moyenne['ECTSL2']>=$regle_passage[4][2]['ECTSL2'] && ($moyenne['MGL2']/2)>=$regle_passage[4][2]['MGL2']){
//                       $niveau=3;
//                    }
//                   else
//                       $redoublant=2;
//                }
//                else
//                    if(!empty($moyenne['ECTSL2']))
//                        $redoublant=2;
//                
//                if(($moyenne['ECTSL1']+$moyenne['ECTSL2']+$moyenne['ECTSL3'])==$regle_passage[4][3]['credit'] ){//condition de passage en L3 non verifie (redoublant en L2)
//                       $niveau=4;
//                }else
//                    $redoublant=3;

                 if($moyenne['ECTSL1']>=$regle_passage[4][1]['ECTSL1'] ){//condition de passage en L2 non verifie (redoublant en L1)
                    $niveau=2;
                }
                else
                    $redoublant=1;
                
                if($moyenne['ECTSL1']==$regle_passage[4][2]['ECTSL1']){//condition de passage en L3 non verifie (redoublant en L2)
                    if($moyenne['ECTSL2']>=$regle_passage[4][2]['ECTSL2']){
                       $niveau=3;
                    }
                   else
                       $redoublant=2;
                }
                else
                    if(!empty($moyenne['ECTSL2']))
                        $redoublant=2;
                
                if(($moyenne['ECTSL1']+$moyenne['ECTSL2']+$moyenne['ECTSL3'])==$regle_passage[4][3]['credit'] ){//condition de passage en L3 non verifie (redoublant en L2)
                       $niveau=4;
                }else
                    $redoublant=3;

                //FIN REGLE PASSAGE ************************    
//                print_r($releve_bis);
//                echo"<br><br>";
        for($cpt=0;$cpt< count($releve_bis);$cpt++){
            if($releve_bis[$cpt]['capit']=='NC' || !empty($ratrap['Impaire'][$releve_bis[$cpt]['sigle']]) || !empty($ratrap['Paire'][$releve_bis[$cpt]['sigle']])){
                if($releve_bis[$cpt]['semestre']%2!=0)
                    $ratrap['Impaire'][$releve_bis[$cpt]['sigle']]=$releve_bis[$cpt]['capit'];
                else
                    $ratrap['Paire'][$releve_bis[$cpt]['sigle']]=$releve_bis[$cpt]['capit'];
//            print_r($releve_bis[$cpt]);
//            echo"<br> ".$cpt."<br>";
            }
            
            if($releve_bis[$cpt]['capit']!='NC' ){
            //on recupere seulement les matieres capitalise pour le niveau ou il est redoublant
              if($redoublant==1){
                    if($releve_bis[$cpt]['semestre']==1)
                        $capit['Impaire'][$releve_bis[$cpt]['sigle']]=$releve_bis[$cpt]['capit'];
                    else
                        if($releve_bis[$cpt]['semestre']==2)
                            $capit['Paire'][$releve_bis[$cpt]['sigle']]=$releve_bis[$cpt]['capit'];
            
              }else
                   if($redoublant==2){
                       if($releve_bis[$cpt]['semestre']==3)
                            $capit['Impaire'][$releve_bis[$cpt]['sigle']]=$releve_bis[$cpt]['capit'];
                       else
                           if($releve_bis[$cpt]['semestre']==4)
                             $capit['Paire'][$releve_bis[$cpt]['sigle']]=$releve_bis[$cpt]['capit'];
            
              }else{
                  if($redoublant==3){
                      
                       if($releve_bis[$cpt]['semestre']==5)
                            $capit['Impaire'][$releve_bis[$cpt]['sigle']]=$releve_bis[$cpt]['capit'];
                       else
                            if($releve_bis[$cpt]['semestre']==6)
                                $capit['Paire'][$releve_bis[$cpt]['sigle']]=$releve_bis[$cpt]['capit'];
                  }
              }
                  
            }
        }
//        print_r($ratrap);
//        echo'<br>';
//        print_r($capit);
        
        //--------------------------
            $data['niveau']=$niveau;
            $data['redoublant']=$redoublant;
            $data['moyenne']=$moyenne;
            $data['ratrap']=$ratrap;
            $data['capit']=$capit;
//            print_r($data);
            return $data;
    }     
    
/*
 * Add by MedBAkar 09-2020
 * les elements a etre inscrit
 */
function get_module_a_etudie_new($niveau, $matriculeEtudiant,$annee,$info){
    
    $idProgramme=$this->scolarite_modele->get_programme($matriculeEtudiant);
        
            $listeElementCapitPaire='';
            $listeElementCapitImpaire='';    
//debut--21-09-2020
             //on recupere les matieres deja valide pour qu'on l'affiche pas dans la maquette(cas etudiant redoublant ayant valide qlq element)
           if(!empty($info)){
            $liste=$this->get_correspondance_elem_new($info);
             $listeElementCapitImpaire=$liste['Impaire'];
             $listeElementCapitPaire=$liste['Paire'];
           }
            //fin--21-09-2020
           $data=array();  
              $data['impaire'] = $this->scolarite_modele->get_module_impaire_a_etudies_new($idProgramme,$niveau,$matriculeEtudiant,$annee,$listeElementCapitImpaire); //a enlever passage_t
            $data['paire'] = $this->scolarite_modele->get_module_paire_a_etudies_new($idProgramme,$niveau, $matriculeEtudiant,$annee,$listeElementCapitPaire);//a enlever passage_t
           return $data;
}

/*
 * Add by MedBAkar 09-2020
contient les matieres ou l'etudiant na pas valide (ratrapee l'annee procheine)
 *  */
function get_modules_ratrappes($info,$niveau){
     if(!empty($info['Paire'])){
             $liste='';
             foreach($info['Paire'] as $element=>$capit)
                 if($capit=='NC')
                     $liste.=",'$element' ";
                 
                 if(!empty($liste)){//en cas où il a valide tous les matieres où a ete ratrappee dans les annees passee
                    $liste=substr($liste,1);
                    $modulesR_paire=$this->scolarite_modele->get_module_rattrapes_paire_new($liste,$niveau);
                 }
                 else
                     $modulesR_paire=null;
             $data['paire'] = $modulesR_paire;
         }else
            $data['paire'] = null;
         
         if(!empty($info['Impaire'])){
             $liste='';
             foreach($info['Impaire'] as $element=>$capit)
                 if($capit=='NC')
                     $liste.=",'$element' ";
                 if(!empty($liste)){//en cas où il a valide tous les matieres où a ete ratrappee dans les annees passee
                    $liste=substr($liste,1);
                    $modulesR_impaire=$this->scolarite_modele->get_module_rattrapes_impaire_new($liste,$niveau);
                 }
                 else
                     $modulesR_impaire=null;
             $data['impaire'] = $modulesR_impaire;
         }else
            $data['impaire'] = null;
         return $data;
}
function get_correspondance_elem_new($info){//22-09-2020-- add by MedBakar 
            $correspondance=$this->bulletin_modele->get_correspondance("SELECT * from correspondance"); 
            $listeElementCapitImpaireFinal='';
            $listeElementCapitPaireFinal='';
            if(!empty($correspondance)){
                         $correspElement=$this->bulletin_modele->last_maquete($correspondance);
            if(!empty($info['Impaire']))
             foreach($info['Impaire'] as $sigle=>$capit){
                if($capit!='NC'){
                    if(!empty($correspElement[$sigle])){
                        $listeElementCapitImaireFinal.=", '".$correspElement[$sigle]."' ";
                    }else{
                        $listeElementCapitImpaireFinal.=", '".$sigle."' ";
                    }
                
                }
            }
            
            if(!empty($info['Paire']))
             foreach($info['Paire'] as $sigle=>$capit){
                if($capit!='NC'){
                    if(!empty($correspElement[$sigle])){
                        $listeElementCapitPaireFinal.=", '".$correspElement[$sigle]."' ";
                    }else{
                        $listeElementCapitPaireFinal.=", '".$sigle."' ";
                    }
                
                }
            }
            
            }
            $data['Impaire']=substr($listeElementCapitImpaireFinal,1);
            $data['Paire']=substr($listeElementCapitPaireFinal,1);
//            print_r($data);
            return $data;
//            echo"<BR><BR><BR><BR>".$listeElementCapitPaireFinal."<BR><BR><BR>".$listeElementCapitPaire."<BR><BR>";
          
}
    

     /*Add By MedBakar 01-10-2020
    * tous les elements ou l'etudiant a ete inscrit pour un niveau 
    * 
    */
   function maquette_old_inscrit($matriculeEtudiant,$niveau){
       if($niveau==1)
           $sem=1;
       else
           if($niveau==2)
           $sem=3;
       else
           $sem=5;
   $maquette=array();
       $result=$this->db->query("SELECT matriculeEtudiant,p.sigle,p.semestre,note,cote,lien,etatNote,p.annee FROM `planetudes` p,module m,unite u where p.sigle=m.sigle and u.sigle=m.sigleUnite and (u.semestre=".($sem)." or u.semestre=".($sem+1).") and matriculeEtudiant=$matriculeEtudiant");
//    print($this->db->last_query());
//    echo"<br><br>*******<br>";
       if($result->num_rows()>0){
        foreach($result->result_array() as $row){
            $maquette[$row['annee']][$row['sigle']]=$row;
        }
    }
//   print_r($maquette);
   return $maquette;
   }
    /*Add By MedBakar 01-10-2020
    * tous les elements ou l'etudiant a ete inscrit pour un niveau 
    * notespartielles
    */
    function maquette_old_inscrit_notespartielles($matriculeEtudiant,$niveau){
       if($niveau==1)
           $sem=1;
       else
           if($niveau==2)
           $sem=3;
       else
           $sem=5;
   $maquette=array();
       $result=$this->db->query("SELECT matriculeEtudiant,p.sigle,p.semestre,note,idEvaluation,p.annee FROM `notespartielles` p,module m,unite u where p.sigle=m.sigle and u.sigle=m.sigleUnite and  (u.semestre=".($sem)." or u.semestre=".($sem+1).") and matriculeEtudiant=$matriculeEtudiant");
    if($result->num_rows()>0){
        foreach($result->result_array() as $row){
            $maquette[$row['annee']][$row['sigle']][$row['idEvaluation']]=$row;
        }
    }
//   print_r($maquette);
   return $maquette;
   }
   
   /* add by MedBakar  01-10-2020
    * Fonction a pour objective de regrouper les differentes fonction 
    * pour le traitement d'un redoublant
    * voire les autres commentaire pour b1 comprendre
    */
   function prep_old_maquette_redoublants($matriculeEtudiant,$niveau,$redoublant,$annee){
       $data = $this->prep_old_maquette_redoublants_elem_remplacer($matriculeEtudiant,$niveau);
       
       $plus_petite_annee_ins = $data['plus_petite_annee_ins'];
       $maquette_old =$data['maquette_old'];
       $maquette_old_sigle=$data['maquette_old_sigle'];
       
       $maquette_nv_sigle=$this->prep_old_maquette_redoublants_delete_elem_remplacer($redoublant,$matriculeEtudiant,$annee,$maquette_old_sigle);

       $this->prep_old_maquette_redoublants_insert_new_elem($matriculeEtudiant,$maquette_nv_sigle,$maquette_old,$plus_petite_annee_ins);
       
   }
   /* Add by MedBakar 27-09-2020
    * Fonction a pour objective de unifier la maquette de l'etudiant
    * en remplaçant les matieres par ses correspondances en gardant l'historique des notes
    * et elle retourne la 1ere annee d'inscription dans ce niveau
    * et l'ancienne maquette ou l'etudiant a ete inscrit sous deux format une liste et une table qui seront etre utilise par les autres fonctions
    */
   function prep_old_maquette_redoublants_elem_remplacer($matriculeEtudiant,$niveau){
       $maquette=$this->maquette_old_inscrit($matriculeEtudiant,$niveau);
//       print_r($maquette);
        $maquetteNote=$this->maquette_old_inscrit_notespartielles($matriculeEtudiant,$niveau);
         $correspondance=$this->bulletin_modele->get_correspondance("SELECT * from correspondance");
         $correspElement=array();
        if(!empty($correspondance)){
            $correspElement=$this->bulletin_modele->last_maquete($correspondance);//on ordone les correspondances Ex(a=>b;b=>c va etre a=>c;b=>c) 
        }
           $query_planetudes='';
           $query_notespartielles='';
           $maquette_old='';//on vas regrouper les sigles ici
            $maquette_old_sigle='';//on vas regrouper les sigles ici
           $plus_petite_annee_ins=9999;
           foreach($maquette as $annee => $value1 ){
               if($plus_petite_annee_ins > $annee)
                  $plus_petite_annee_ins = $annee;//on cherche la premiere annee d'inscription pour que l'on utilise apres pour l'insertion des nouveaux elements dans cette annee
               foreach($value1 as $sigle => $row ){  
                   $maquette_old_sigle.=", '$sigle' ";//tous les sigles des elements ou l'etudiant a deja inscrit (dans le passe)
                        if(!empty($correspElement[$sigle])){
                                $maquette_old[$correspElement[$sigle]]=$row['semestre'];//on recupere  tous les matieres qui ont des correspondances
                                $query_planetudes.=",( $matriculeEtudiant,'$correspElement[$sigle]','".$row['semestre']."','".$row['note']."','".$row['cote']."','".$row['lien']."','".$row['etatNote']."','".$row['annee']."' )";
                           if(!empty($maquetteNote[$annee][$sigle]))
                             foreach($maquetteNote[$annee][$sigle] as $idEval=>$value3)
                                $query_notespartielles.=",( $matriculeEtudiant,'$correspElement[$sigle]',$idEval, '".$maquetteNote[$annee][$sigle][$idEval]['note']."','".$maquetteNote[$annee][$sigle][$idEval]['semestre']."',$annee' )";
                        }
                        else
                             $maquette_old[$sigle]=$row['semestre'];//on recupere  tous les matieres qui n'ont pas des correspondances
               }
           }
         //on execute les requttes d'insertion
           if(!empty($query_planetudes)){
//                echo"<br>INSERT INTO `planetudes`(`matriculeEtudiant`, `sigle`, `semestre`, `note`, `cote`, `lien`, `etatNote`, `annee`, `caché`) values".substr($query_planetudes,1);
//                echo"<br>";
//                echo"<br>INSERT INTO `notespartielles`(`matriculeEtudiant`, `sigle`, `idEvaluation`, `note`, `semestre`, `annee`, `creePar`, `dateCreation`, `dernModifPar`, `dateModif`) values".substr($query_notespartielles,1);
                $this->db->query("INSERT INTO `planetudes`(`matriculeEtudiant`, `sigle`, `semestre`, `note`, `cote`, `lien`, `etatNote`, `annee`) values".substr($query_planetudes,1));
                $this->db->query("INSERT INTO `notespartielles`(`matriculeEtudiant`, `sigle`, `idEvaluation`, `note`, `semestre`, `annee`) values".substr($query_notespartielles,1));
           
       }
           $data['plus_petite_annee_ins']=$plus_petite_annee_ins;
           $data['maquette_old']= $maquette_old;
           $data['maquette_old_sigle']= $maquette_old_sigle;
           return $data;
   }
   /* Add by MedBakar 30-09-2020
    * Fonction a pour objective de recuperer la nv maquette a fin de supprimer tous les elements ou l'etudiant a ete inscrit dans le passe 
    * sachant que ses elements ne sont pas dans la nv maquette et absolument on parle d'un niveau donnee
    * et elle retourne une table contenant les sigles de la nv maquette 
    */
   function prep_old_maquette_redoublants_delete_elem_remplacer($redoublant,$matriculeEtudiant,$annee,$maquette_old_sigle){
//       echo"<br><br>";
           $maquette=$this->get_module_a_etudie_new($redoublant, $matriculeEtudiant,$annee,'');//on recupere la nv maquette ou l'etudiant va inscrire
            $maquette_impaire =$maquette['impaire'];
            $maquette_paire =$maquette['paire']; 
            $listeElem_nv_maquette='';//cette liste va contenir tous les sigles de la nv maquette
            $maquette_nv_sigle=array();//cette array contenir tous les sigles de la nv maquette
            for($i=0;$i<count($maquette['impaire']);$i++){
                $listeElem_nv_maquette.= ", '".$maquette['impaire'][$i]->sigle."' ";//cette liste contient tous les sigles de la nv maquette
                $maquette_nv_sigle[$maquette['impaire'][$i]->sigle]=3;//semestre impaire
            }
            for($i=0;$i<count($maquette['paire']);$i++){
                $listeElem_nv_maquette.= ", '".$maquette['paire'][$i]->sigle."' ";//cette liste contient tous les sigles de la nv maquette
                $maquette_nv_sigle[$maquette['paire'][$i]->sigle]=1;//semestre paire
            }
           //puis on supprime tous ce qui n'est pas dans la nv maquette d'un niveau donnee
            if(strcasecmp($listeElem_nv_maquette, $maquette_old_sigle)!=0){
//                echo "<br>DELETE FROM planetudes where sigle in(".substr($maquette_old_sigle,1).") and sigle not in(".substr($listeElem_nv_maquette,1).") and matriculeEtudiant=$matriculeEtudiant";
//                echo "<br>DELETE FROM notespartielles where sigle in(".substr($maquette_old_sigle,1).") and sigle not in(".substr($listeElem_nv_maquette,1).") and matriculeEtudiant=$matriculeEtudiant";
                $this->db->query("DELETE FROM planetudes where sigle in(".substr($maquette_old_sigle,1).") and sigle not in(".substr($listeElem_nv_maquette,1).") and matriculeEtudiant=$matriculeEtudiant");
                $this->db->query("DELETE FROM notespartielles where sigle in(".substr($maquette_old_sigle,1).") and sigle not in(".substr($listeElem_nv_maquette,1).") and matriculeEtudiant=$matriculeEtudiant");
          
            //puis on insert des zeros dans les annees precedente si une nouvelle matiere est aparus dans la maquette
            }
            return $maquette_nv_sigle;
           }
           
           /* Add by MedBakar 03-10-2020
            * fonction a pour objective d'inserer des zeros si l'etudiant est redoublant et il ya des nv element dans la maquette
            * notre fonction alors inseerer des zeros pour ces elements dans la 1ere annee d'inscription
            */
    function prep_old_maquette_redoublants_insert_new_elem($matriculeEtudiant,$maquette_nv_sigle,$maquette_old,$plus_petite_annee_ins){
//           echo"<br>Insert new element<br>";
            $query_planetudes='';
            $nv_sigle='';
            $query_notespartielles='';
           //on cherche les nouveaux elements
//           print_r($maquette_old);
//            print_r($maquette_nv_sigle);
            $creePar=$this->session->userdata('matriculeEmploye');//on recupere le matricule d'employe courant
            $date= gmdate('Y-m-d H:i:s');//on recupere la date courante en GMT
            foreach($maquette_nv_sigle as $sigle => $semestre){
                if(empty($maquette_old[$sigle])){
                       if($semestre==1)
                           $annee_ins=$plus_petite_annee_ins+1;
                        else
                            $annee_ins=$plus_petite_annee_ins;
                       $query_planetudes.=",( $matriculeEtudiant,'".$sigle."','".$semestre."','0','','AV','professeur','".$annee_ins."' )";
                       //insertion dans notespartielles
                       $query_notespartielles.=",( $matriculeEtudiant,'$sigle',1, '0','$semestre',$annee_ins )";
                       $query_notespartielles.=",( $matriculeEtudiant,'$sigle',2, '0','$semestre',$annee_ins )";
                       $query_notespartielles.=",( $matriculeEtudiant,'$sigle',4, '0','$semestre',$annee_ins )";
                        
                }
                    
            }
            if(!empty($query_planetudes)){
//                echo" <br><br><br> INSERT INTO `planetudes`(`matriculeEtudiant`, `sigle`, `semestre`, `note`, `cote`, `lien`, `etatNote`, `annee`, `caché`) values".substr($query_planetudes,1);
//                echo" <br><br><br> INSERT INTO `notespartielles`(`matriculeEtudiant`, `sigle`, `idEvaluation`, `note`, `semestre`, `annee`, `creePar`, `dateCreation`, `dernModifPar`, `dateModif`) values".substr($query_notespartielles,1);
            $this->db->query("INSERT INTO `planetudes`(`matriculeEtudiant`, `sigle`, `semestre`, `note`, `cote`, `lien`, `etatNote`, `annee`) values".substr($query_planetudes,1));
            $this->db->query("INSERT INTO `notespartielles`(`matriculeEtudiant`, `sigle`, `idEvaluation`, `note`, `semestre`, `annee`) values".substr($query_notespartielles,1));
            
                
            }
           
   }

}

