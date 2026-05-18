<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require('fpdf/fpdf.php');

/**
 * Cette classe represente un bulletin de notes.
 * En format PDF
 *
 * @author EmimDev group
 */
class Bulletin extends FPDF {
   
    // En-tête
    function Header()
    {       
        //Positionnement à 1,8 cm de la droite
        $this->SetY(1.8);
        $this->SetX(-3.5);
        // Police Arial normal 10
        $this->SetFont('Arial','',10);
        $this->Cell(2.5,0.5,'Page ' .$this->PageNo().' de {nb}',0,0,'C');
    }

    // Pied de page
    function Footer()
    {
        
    }
     
    function Initialiser()
    {
        $this->AliasNbPages();
    }
    
    function NouvellePage($info_personnelles)
    {
        $this->AddPage();
        $this->InformationsPersonnelle($info_personnelles);
    }
    
    
    function InformationsPersonnelle($info_personnelles)
    {
        // Positionnement à 5 cm du haut de la feuille
        $this->SetY(5);
        // Police Arial normal 10
        $this->SetFont('Arial','',10);
        $this->Cell(2.5,0.5,'Nom :',0,0,'R');
        
        // Police Arial gras 12
        $this->SetFont('Arial','B',12);
        $this->Cell(0,0.5, $info_personnelles['prenom'].' '.$info_personnelles['nom'],0,1,'L');
        
        // Police Arial normal 10
        $this->SetFont('Arial','',10);
        if($info_personnelles['sexe'] == 'M')
            $this->Cell(2.5,0.5,'Né le :',0,0,'R');
        else
            $this->Cell(2.5,0.5,'Née le :',0,0,'R');
        
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        $this->Cell(0,0.5, $this->Formatter_date($info_personnelles['dateNaissance']),0,1,'L');
        
        // Police Arial normal 10
        $this->SetFont('Arial','',10);
        $this->Cell(2.5,0.5,'Matricule :',0,0,'R');
        
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        $this->Cell(0,0.5,$info_personnelles['matricule'],0,1,'L');
        
        // Police Arial normal 10
        $this->SetFont('Arial','',10);
        $this->Cell(2.5,0.5,'Programme :',0,0,'R');
        
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        $this->Cell(10,0.5,$info_personnelles['programme'] ,0,0,'L');
        
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        $this->Cell(6.5,0.5,'Date d\'émission : '.$info_personnelles['dateEmission']  ,0,1,'L');
        
         // Positionnement à 7.2 cm du haut de la feuille
        $this->SetY(7.2);
       
    }
    
    function Formatter_date($date)
    {
    $elements = explode("-", $date);
        $mois = array(
            '01' => 'janvier', '02' => 'février', '03' => 'mars', '04' => 'avril', '05' => 'mai', '06' => 'juin',
            '07' => 'juillet', '08' => 'août', '09' => 'septembre', '10' => 'octobre', '11' => 'novembre', '12' => 'décembre');
        return $elements[2].' '.$mois[$elements[1]].' '.$elements[0];
    }


    function AjouterAnnee()
    {
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(191, 191, 191);
        $this->Cell(2.5,0.5,'Semestre',1,0,'C',true);
        $this->Cell(2.5,0.5,'Module',1,0,'C',true);
        $this->Cell(7.5,0.5,'Titre du module',1,0,'C',true);
        $this->Cell(1.5,0.5,'Crédits',1,0,'C',true);
        $this->Cell(1.5,0.5,'Note',1,0,'C',true);
        $this->Cell(1.5,0.5,'Cote',1,0,'C',true);
        $this->Cell(1.5,0.5,'Obs.',1,1,'C',true);	// modif. RM 18 avril 2013 Lien remplacé par Obs.
    }
    function AjouterLigneVierge()
    {
        $this->Cell(0,0.5,'',0,1,'R');
    }
    
	function AjouterSemestre($annee, $semestre, $notes_semestre)
    // ajoute une ligne complète au bulletin
	{
        $index = 0;
        // Police Courier gras 10
        $this->SetFont('Courier', 'B', 10);
    	/* $this->Cell(2.5, 0.5, $annee . '-' . $semestre, 0, 0, 'C'); original */
        /* Ajout RM 27 janvier 2013 */
		if ($semestre == 3)
                    $periode = $annee."-".($annee + 1);
                else 
                    $periode = ($annee-1)."-".($annee);
			
		switch ($semestre)
		{
			case 1; // printemps
				$this->Cell(2.5, 0.5, $periode . '-P', 0, 0, 'C');
				break;
			case 2; // été
				$this->Cell(2.5, 0.5, $periode . '-É', 0, 0, 'C');
				break;
			case 3; // automne
				$this->Cell(2.5, 0.5, $periode . '-A', 0, 0, 'C');
				break;
			default:
				die("Erreur de semestre !");
				break;
		}
		/* fin modification RM 27 janvier 2013 */
		
		foreach($notes_semestre as $note)
		/*{
            if($index != 0)
            {
                $this->Cell(2.5, 0.5, '', 0, 0, 'C');
            }
            $this->Cell(2.5, 0.5, " ".$note['sigle'], 0, 0, 'L');    // RM : ajout d'un espace avant le sigle 
			// $this->Cell(2.5, 0.5, $note['sigle'], 0, 0, 'L'); 
            $this->Cell(7.5, 0.5, $note['titre'], 0, 0, 'L');
			
			if($note['lien'] == 'HP' || $note['lien'] == 'HH' || $note['lien'] == 'EC'|| $note['lien'] == 'AB' || $note['lien'] == 'AV' || $note['lien'] == 'HV' ||$note['lien'] == 'RP' || $note['lien'] == 'HR' || ($note['lien'] == 'HS' && $note['note'] <10))
				if(($note['lien'] == 'RP' && $note['note'] >= 10) ||($note['lien'] == 'HR' && $note['note'] >= 10)|| ($note['lien'] == 'RP' && $note['cote'] == 'EQ'))
                    $this->Cell(1.5, 0.5, '('.$note['credits'].')', 0, 0, 'C');	// entre parenthèses
                else
                    $this->Cell(1.5, 0.5, '['.$note['credits'].']', 0, 0, 'C');	// entre crochets
            else
                $this->Cell(1.5, 0.5, $note['credits'], 0, 0, 'C');	// le nombre de crédits sans parenthèses ni crochets
            
			if($note['note'] == -1)
            {
                //pas de note : écrire À venir
                if($note['lien'] == "AV" || $note['lien'] == "HV") // ajout du OU Roger 18 avril 2013
                    $this->Cell(1.5, 0.5, "À venir", 0, 0, 'L');
                else
                    $this->Cell(1.5, 0.5, "", 0, 0, 'R');
            }
            else
                $this->Cell(1.5, 0.5, number_format($note['note'],2), 0, 0, 'R');
           
           
            if(strtolower(trim($note['lien']))=='hs')
                $note['lien']= 'HR';
            
            $this->Cell(1.5, 0.5, $note['cote'], 0, 0, 'C');
            
			$liens_officiels = array('AB', 'AV', 'EC', 'EQ', 'HP', 'RP', 'RT','HR');
            $liens_reprise = array('AR', 'ER', 'HH','RR');
            if(in_array($note['lien'], $liens_officiels))
                $this->Cell(1.5, 0.5, $note['lien'], 0, 1, 'C');
            else if(in_array($note['lien'], $liens_reprise))
                $this->Cell(1.5, 0.5, 'RP', 0, 1, 'C');
            // ajout RM 18 avril 2013 : les 2 lignes qui suivent
			else if($note['lien'] == 'HV')
                $this->Cell(1.5, 0.5, 'AV', 0, 1, 'C');
			else
                $this->Cell(1.5, 0.5, '', 0, 1, 'C');
            $index++;
        }*/       
        
		{	// modifs RM 18 avril 2013 : j'imprime À venir dans la colonne Cote si les infos ne sont pas encore disponibles
			// au lieu de À venir dans la colonne Note et AV dans la colonne Lien
            if($index != 0)
            {
                $this->Cell(2.5, 0.5, '', 0, 0, 'C');
            }
            $this->Cell(2.5, 0.5, " ".$note['sigle'], 0, 0, 'L');    // RM : ajout d'un espace avant le sigle 
			// $this->Cell(2.5, 0.5, $note['sigle'], 0, 0, 'L'); 
            $this->Cell(7.5, 0.5, $note['titre'], 0, 0, 'L');
			
			if($note['lien'] == 'HP' || $note['lien'] == 'HH' || $note['lien'] == 'EC'|| $note['lien'] == 'AB' || $note['lien'] == 'AV' || $note['lien'] == 'HV' ||$note['lien'] == 'RP' || $note['lien'] == 'HR' || ($note['lien'] == 'HS' && $note['note'] <10))
				if(($note['lien'] == 'RP' && $note['note'] >= 10) ||($note['lien'] == 'HR' && $note['note'] >= 10)|| ($note['lien'] == 'RP' && $note['cote'] == 'EQ'))
                    $this->Cell(1.5, 0.5, '('.$note['credits'].')', 0, 0, 'C');	// entre parenthèses
                else
                    $this->Cell(1.5, 0.5, '['.$note['credits'].']', 0, 0, 'C');	// entre crochets
            else
                $this->Cell(1.5, 0.5, $note['credits'], 0, 0, 'C');	// le nombre de crédits sans parenthèses ni crochets
            
			if($note['note'] == -1)	// note à venir ou équivalence
            {
                // note à venir : écrire -- centré au lieu de la note
                if($note['lien'] == "AV" || $note['lien'] == "HV") // ajout du OU Roger 18 avril 2013
                     $this->Cell(1.5, 0.5, "--", 0, 0, 'C');
				else
                    $this->Cell(1.5, 0.5, "", 0, 0, 'R');
            }
            else
                $this->Cell(1.5, 0.5, number_format($note['note'],2), 0, 0, 'R');
           
           
            if(strtolower(trim($note['lien']))=='hs')
                $note['lien']= 'HR';
            
             if($note['note'] == -1 && $note['cote'] != "EQ")	// cote = EQ : imprimer EQ, sinon À venir
				$this->Cell(1.5, 0.5, "À venir", 0, 0, 'C');
			else
				$this->Cell(1.5, 0.5, $note['cote'], 0, 0, 'C');
			
			$liens_officiels = array('AB', 'EC', 'EQ', 'HP', 'RP', 'RT','HR');
            $liens_reprise = array('AR', 'ER', 'HH','RR');
            if(in_array($note['lien'], $liens_officiels))
                $this->Cell(1.5, 0.5, $note['lien'], 0, 1, 'C');
            else if(in_array($note['lien'], $liens_reprise))
                $this->Cell(1.5, 0.5, 'RP', 0, 1, 'C');
            // ajout RM 18 avril 2013 : les 2 lignes qui suivent
			else 
				if($note['lien'] == 'HV' || $note['lien'] == 'AV')
                $this->Cell(1.5, 0.5, '--', 0, 1, 'C');
			else
                $this->Cell(1.5, 0.5, '', 0, 1, 'C');
            $index++;
        }$this->Cell(18.5, 0, '', 1, 1, 'C');
    }
    

	function AjouterMoyenne($estSemestre, $periode, $moyenne, $nbCredits)
    {
        //Moyenne de l'annee
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        if($estSemestre  == TRUE)
        {
         $this->Cell(12.5,0.5,'Moyenne du semestre '.$periode.':',0,0,'R');   
        }
        else
        {
         $this->Cell(12.5,0.5,'Moyenne de l\'année '.$periode.':',0,0,'R');   
        }
         // Police Courier normal 10
        $this->SetFont('Courier','B',10);
        $this->Cell(1.5,0.5,'',0,0);
        if($moyenne == -1)
            $moyenne = 0;
        $this->Cell(1.5,0.5,number_format($moyenne,2),0,1,'R');
        
        //Credits valides
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        if($estSemestre  == TRUE)
            $this->Cell(12.5,0.5,'Nombre de crédits validés au semestre '.$periode.':',0,0,'R');
        else
            $this->Cell(12.5,0.5,'Nombre de crédits validés en '.$periode.':',0,0,'R');
        
         // Police Courier gras 10
        $this->SetFont('Courier','B',10);
        $this->Cell(1.5,0.5,$nbCredits,0,1,'C');
    	}
	
	function AjouterMention($moyenne)
	// ajoute une mention. Ajout RM 6 février 2013
	{
	$Mention = "Aucune"; // normalement inutile.
		switch ($moyenne)
		{
		case ($moyenne < 13.995):	// 13 à 13,99 : Assez bien
			$Mention = "ASSEZ BIEN";
			break;
		case ($moyenne >= 13.995) && ($moyenne < 15.995):	// 14 à 15,99 : Bien
			$Mention = "BIEN";
			break;		
		case ($moyenne >= 15.995) && ($moyenne < 17.995):	// 16 à 17,99 : Très bien
			$Mention = "TRÈS BIEN";
			break;
		case ($moyenne >= 17.995):	// au moins 18 : Excellence
			$Mention = "EXCELLENCE";
			break;
			}
	$this->Cell(18.5, .5, 'Mention obtenue pour cette année scolaire : '.$Mention, 1, 1, 'C');
	}
	   
    function AjouterLabelSuiteReleve()
    {
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        $this->Cell(18.5,0.5,'Suite du relevé de notes à la page '.($this->PageNo() + 1),'B',0,'R');
    }
    
    function AjouterLabelFinReleve()
    {
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        $this->Cell(18.5,0.5,'Fin du relevé de notes','B',0,'R');
    }
    
    function AjouterSanction($nbCreditsGeneral, $moyenneGenerale, $decision)
    {
        // Positionnement à 4 cm du bas de la feuille
        $this->SetY(-4);
        $this->SetLineWidth(0.05);
        // Police Courier gras italique 12
        $this->SetFont('Courier','BI',12);
        $this->Cell(2.5,0.5,'Sanction','TL',0,'R');
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        $this->Cell(10,0.5,'Nombre total de crédits validés:','T',0,'R');
        // Police Courier gras 10
        $this->SetFont('Courier','B',10);
        $this->Cell(1.5,0.5,$nbCreditsGeneral,'T',0,'C');
        $this->Cell(4.5,0.5,'','TR',1);
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        $this->Cell(12.5,0.5,'Moyenne cumulative:','L',0,'R');
        // Police Courier gras 10
        $this->SetFont('Courier','B',10);
        $this->Cell(1.5,0.5,number_format($moyenneGenerale,2),0,0,'C');
        $this->Cell(4.5,0.5,'','R',1);
        // Police Arial gras 10
        $this->SetFont('Arial','B',10);
        $this->Cell(12.5,0.5,'Décision relative à la poursuite des études:','BL',0,'R');
        // Police Courier gras 10
        $this->SetFont('Arial','BI',10);
        $this->Cell(6,0.5,$decision,'BR',0,'C');
        $this->SetLineWidth(0);
    }
    
    function Generer($matricule, $source)
    {
        if(is_file('Bulletins/Bulletin_'.$matricule.'.pdf'))
                unlink('Bulletins/Bulletin_'.$matricule.'.pdf');
        
        if($source == 'scolarite')
            $this->Output('Bulletins/Bulletin_'.$matricule.'.pdf', 'F' ); 
        else if ($source == 'etudiant')
            $this->Output($matricule.'.pdf', 'I' ); 
    }
    
}

?>
