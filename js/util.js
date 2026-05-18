   function PrintContent()
    {
/*newwin = window.open('','','width=200,height=100');
newwin.document.write("Hello World!");
newwin.document.close();
newwin.focus();newwin.print();
newwin.close();
*/
window.print();
    }
    
    function getData()
    {

       	var ajaxRequest;  // The variable that makes Ajax possible!
	
	try{
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		// Internet Explorer Browsers
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// Something went wrong
				alert("Your browser broke!");
				return false;
			}
		}
	}
	// Create a function that will receive data sent from the server
	ajaxRequest.onreadystatechange = function(){
		if(ajaxRequest.readyState == 4){
                   
                   alert(ajaxRequest.responseText);
                    document.getElementById("data") = ajaxRequest.responseText;
		}
	}
	ajaxRequest.open("GET",' <?= base_url()?>/getData.php', true);
	ajaxRequest.send(null); 
}
   
    function generer_cote(taille)
    {
        var MAX = 0;
        var counter = 0;
        var tableauInitial = new Array();
        for(i=0 ; i<taille;i++)
        {
            tableauInitial[counter] = document.getElementById("note"+i).getAttribute('value');
            counter++;
        }
        MAX = Math.max.apply(Math, tableauInitial);
        var X1 = 10;
        var X2 = X1 + 0.10*(MAX-X1);
        var X3 = X1 + 0.35*(MAX-X1);
        var X4 = X1 + 0.65*(MAX-X1);
        var X5 = X1 + 0.90*(MAX-X1);
        var X6 = MAX;

        for(i=0 ; i<taille;i++)
        {
            var note =  document.getElementById("note"+i).getAttribute('value');
            if(note<8)
            {
                 document.getElementById("cote"+i).setAttribute('value', 'F');
            }
            else if(note>=8 && note<X1)
            {
                 document.getElementById("cote"+i).setAttribute('value', 'FX');
            }
            else if(note>=X1 && note<X2)
            {
                 document.getElementById("cote"+i).setAttribute('value', 'E');
            }
            else if(note>=X2 && note<X3)
            {
                document.getElementById("cote"+i).setAttribute('value', 'D');
            }
            else if(note>=X3 && note<X4)
            {
                document.getElementById("cote"+i).setAttribute('value', 'C');
            }
            else if(note>=X4 && note<X5)
            {
                document.getElementById("cote"+i).setAttribute('value', 'B');
            }
            else if(note>=X5 && note<=X6)
            {
                document.getElementById("cote"+i).setAttribute('value', 'A');
            }
        }
     
    }
    
    function majoration(variableAmajore)
    {
        comparateur = parseInt(variableAmajore)+0.5;
        if(variableAmajore <comparateur)
        {
           return parseInt(variableAmajore);
        }
        else
        {
            return parseInt(variableAmajore)+1;
        }
    }
    var counter = 0;
   function isRattrapage(matricule , idTr, size, note, cote)
   {
       counter = size;
       
        if(document.getElementById(matricule).checked)
        {
            
            var TR = document.getElementById('titreRattrapage');
            if(document.getElementById('headerNote')!= null)
            {
                element = document.getElementById('headerNote');
                element.parentNode.removeChild(element);
            }
            if(document.getElementById('headerCote')!= null)
            {
                element = document.getElementById('headerCote');
                element.parentNode.removeChild(element);
            }
            var thNote = document.createElement('th');
            thNote.setAttribute('id', 'headerNote');
            thNote.innerHTML = "Note Après rattrapage";
            var thCote = document.createElement('th');
            thCote.innerHTML = "Côte Après rattrapage";
            thCote.setAttribute('id', 'headerCote');
            TR.appendChild(thNote);
            TR.appendChild(thCote);
            
            var tds = document.createElement('td');
             tds.setAttribute('id', 'ids' + matricule);
            var input = document.createElement('input');
             input.setAttribute('type', 'text');
             input.setAttribute('value', note);
            input.setAttribute('name','names' + matricule);
            var tdCote = document.createElement('td');
            tdCote.setAttribute('id', 'idCote' + matricule);
           var inputCote = document.createElement('input');
             inputCote.setAttribute('type', 'text');
             inputCote.setAttribute('value', cote);
            inputCote.setAttribute('name','cote' + matricule);
           
            tds.appendChild(input);
            tdCote.appendChild(inputCote);
            document.getElementById(idTr).appendChild(tds);
            document.getElementById(idTr).appendChild(tdCote);
        }
        else
        {
            var TR = document.getElementById('titreRattrapage');
            element = document.getElementById('ids' + matricule);
            element.parentNode.removeChild(element);
            element = document.getElementById('idCote' + matricule);
            element.parentNode.removeChild(element);
            element = document.getElementById('headerNote');
            element.parentNode.removeChild(element);
             element = document.getElementById('headerCote');
            element.parentNode.removeChild(element);
            
            if(document.getElementById('headerNote')== null)
            {
                 var thNote = document.createElement('th');
                thNote.setAttribute('id', 'headerNote')
                thNote.innerHTML = "Note Après rattrapage";
                TR.appendChild(thNote);
            }
            if(document.getElementById('headerCote')== null)
            {
                var thCote = document.createElement('th');
                thCote.innerHTML = "Côte Après rattrapage";
                thCote.setAttribute('id', 'headerCote');
                 TR.appendChild(thCote);
            }
        }
  
   }
   
   function date_abondon()
   {
       alert('Veuillez mettre à jour le champ "Date et raison d\'abandon" puis cliquez sur le bouton "Enregistrer".');
   }