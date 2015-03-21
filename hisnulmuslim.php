<?php
	/*
	Plugin Name: La citadelle du Musulman
	Description: La citadelle du muslman est un plugin sur rappels et d'invocations en islam selon le Coran et la Sunna du Prophète SAWS
	Version: 1.0.0
	Author: Toiha ISSIHACA
	Author URI: toiha.fr
	*/
	session_start() ;


	function initHusnData(){
		global $chapitreData, $metadataFile;
		echo $language;	
		$metadataFile = plugins_url( '/data.xml' , __FILE__ ); 
		$dataItems = Array("index", "start", "parties", "tname");
		$husnData = file_get_contents($metadataFile);
		$parser = xml_parser_create();
		xml_parse_into_struct($parser, $husnData, $values, $index);
		xml_parser_free($parser);

		for ($i=1; $i<=132; $i++) 
		{
			$j = $index['CHAPITRE'][$i-1];
			foreach ($dataItems as $item)
				$chapitreData[$i][$item] = $values[$j]['attributes'][strtoupper($item)]; 
		}
	}

	initHusnData();
	function rendu_hisnulmuslim(){
		global $chapitreData;
		$option = null;
		if( isset($_POST['select_partie']) || isset($_SESSION['select_partie'])){
				if ($_POST['select_partie'] == $chapitre['index'] || $_SESSION['select_partie'] == $chapitre['index']) {
					$option =  'selected="selected"';
				}
			}
		?>
		<form action="" method="post">
			<select name="select_partie" onchange="this.form.submit()" class="form-control">
				<?php foreach ($chapitreData as $key => $chapitre): ?>
					<option value="<?= $chapitre['index'] ?>" <?= $opption ?> ><?= $chapitre['tname'] ?></option>	
				<?php endforeach ?>
			</select>
		</form>
		<hr>
		<?php
		echo "<div class='parties'>";
		if (isset($_POST['select_partie'])) {
			 $_SESSION['select_partie'] = $_POST['select_partie'];
			showChapitre($_POST['select_partie']);
		}else if (isset($_SESSION['select_partie'])) {
			showChapitre($_SESSION['select_partie']);
		}else{
			showChapitre(1);
		}
		echo "</div>";
	}

	function getChapitreData($chapitre, $property) {
		global $chapitreData;
		return $chapitreData[$chapitre][$property]; 
	}


	function getChapitreContents($chapitre, $file) {
		$text = file($file);
		$startPartie = getChapitreData($chapitre, 'start');
		$endPartie = $startPartie+ getChapitreData($chapitre, 'parties');
		$content = array_slice($text, $startPartie, $endPartie- $startPartie); 
		return $content;
	}


	if (@$chapitre < 1) @$chapitre = 1; 
	if (@$chapitre > 132) @$chapitre = 132; 


	function showChapitre($chapitre){
		global $hisnulmuslimFile, $transFile, $language;
		$hisnulmuslimFile =  plugins_url( '/hisnulmuslim-arabe.txt' , __FILE__ );
		$transFile =  plugins_url( '/hisnulmuslim-francais.txt' , __FILE__ );
		$phoneticFile =  plugins_url( '/hisnulmuslim-phonetic.txt' , __FILE__ );
		
		$chapitreName = getChapitreData($chapitre, 'tname');
		$chapitreText = getChapitreContents($chapitre, $hisnulmuslimFile);
		$transText = getChapitreContents($chapitre, $transFile);
		$phoneticText = getChapitreContents($chapitre, $phoneticFile);
		$partiesNum = 1;
		
		echo "<h3 class=partie-name>$chapitreName</h3>";
		foreach ($chapitreText as $partis)
		{
			$trans = $transText[$partiesNum- 1];
			$phon = $phoneticText[$partiesNum- 1];
			$partis = preg_replace('/ ([ۖ-۩])/u', '<span class="sign">&nbsp;$1</span>', $partis);

			echo "<div class=partie>";
				echo "<p class=text_ar><span class=partieNum>$partiesNum #. </span>$partis</p>";
				echo "<p class=trans>$trans </p>";
				echo "<p class=phon>\"$phon\" </p>";
			echo "</div>";
			$partiesNum++;
		}
	}

add_shortcode('hisnulmuslim', 'hisnulmuslim_shortcode');
function hisnulmuslim_shortcode() {
	return rendu_hisnulmuslim();
}